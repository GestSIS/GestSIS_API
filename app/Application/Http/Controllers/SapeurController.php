<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SapeurBusiness;
use App\Collections\ListeFoadExport;
use App\Collections\ListeFsspExport;
use App\Models\Sapeur;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SapeurController extends Controller
{
    private function serializeSapeur(Sapeur $sapeur): array
    {
        $data = $sapeur->toArray();
        $data['fonction_id'] = $data['fonction_id'] ?? 0;
        $data['grade_id'] = $data['grade_id'] ?? 0;
        // FIXME: à supprimer une fois les apps mobiles migrées sur statut_sapeur / statut_civil
        $data['type'] = SapeurBusiness::typePrincipal($sapeur->statut_sapeur, $sapeur->statut_civil);
        // FIXME: à supprimer une fois les apps mobiles migrées sur statut_sapeur / statut_civil
        $data['actif'] = $data['statut_sapeur'] === SapeurBusiness::STATUT_ACTIF
            || $data['statut_civil'] === SapeurBusiness::STATUT_ACTIF;

        return $data;
    }

    public function index(Request $request)
    {
        $actif = $request->input('actif', false) === 'true';
        $actifOuAvecMateriel = $request->input('avec-materiel', false) === 'true';

        $now = Carbon::now();
        $oneMonthFurther = Carbon::now()->addMonths(1);
        $query = Sapeur::with([
            'permis',
            'fonctions' => function ($query) use ($oneMonthFurther, $now) {
                $query->where('debut', '<=', $oneMonthFurther)->where(function ($query) use ($now) {
                    $query->where('fin', '=', null)
                        ->orWhere('fin', '>=', $now);
                });
            }
        ]);

        // Actif : sapeur ou civil actif
        $estActif = fn($query) => $query->where('statut_sapeur', SapeurBusiness::STATUT_ACTIF)
            ->orWhere('statut_civil', SapeurBusiness::STATUT_ACTIF);
        if ($actif) {
            $query = $query->where($estActif);
        }
        if ($actifOuAvecMateriel) {
            $query = $query->where(fn($query) => $estActif($query)->orWhereHas('articles'));
        }

        $columns = ['id', 'nom', 'prenom', 'civilite_id', 'email', 'suffixe', 'statut_sapeur', 'statut_civil', 'date_naissance', 'fonction_id', 'grade_id', 'localite_id', 'annee_incorporation'];
        $sapeurs = $query->get([...$columns, DB::raw("CONCAT(nom, ' ', prenom) AS nom_prenom")])
            ->sortBy('nom_prenom')
            ->map(fn($sapeur) => $this->serializeSapeur($sapeur))
            ->values();

        return response()->json(["data" => $sapeurs]);
    }

    public function trombinoscope(Request $request)
    {
        $sisKey = $request->header('Sis-Key', Null);
        return SapeurBusiness::trombinoscope($sisKey);
    }

    public function fiche(Request $request, $sapeurId)
    {
        $sisKey = $request->header('Sis-Key', Null);
        return SapeurBusiness::fiche($sapeurId, $sisKey);
    }

    public function effectif()
    {
        $sapeurs = Sapeur::with('telephones', 'permis', 'fonctions', 'groupes')
            ->where('statut_sapeur', SapeurBusiness::STATUT_ACTIF)
            ->get(['id', 'nom', 'prenom', 'email', 'annee_incorporation', 'rue', 'no_rue', 'date_naissance', 'fonction_id', 'grade_id', 'civilite_id', 'localite_id'])
            ->toArray();
        return response()->json(['data' => $sapeurs]);
    }

    public function listeFssp(Request $request)
    {
        $date = $request->input('date', Carbon::now());
        return Excel::download(new ListeFsspExport($date), 'liste_fssp.xlsx');
    }

    public function listeFoad(Request $request)
    {
        $date = $request->input('date', Carbon::now());
        return Excel::download(new ListeFoadExport($date), 'liste_foad.xlsx');
    }

    public function sapeursTelephones()
    {
        return response()->json(['data' => Sapeur::with('telephones')->get(['id'])->toArray()]);
    }

    public function convocationSms()
    {
        return response()->json([
            // Sapeurs et civils actifs : un civil peut aussi être convoqué
            'data' => Sapeur::with('telephones')
                ->where(fn($query) => $query->where('statut_sapeur', SapeurBusiness::STATUT_ACTIF)
                    ->orWhere('statut_civil', SapeurBusiness::STATUT_ACTIF))
                ->get(['id', 'nom', 'prenom'])->toArray()
        ]);
    }

    public function store(Request $request)
    {
        // Nature de la première mutation, créée avec la personne
        $rule = 'required|integer|in:' . SapeurBusiness::NATURE_SAPEUR . ',' . SapeurBusiness::NATURE_CIVIL;
        $nature = $request->validate(['nature' => $rule])['nature'];

        switch ($nature) {
            case SapeurBusiness::NATURE_SAPEUR:
                $data = $request->validate([
                    'nom' => 'string|min:2',
                    'prenom' => 'string|min:2',
                    'suffixe' => 'string|nullable',
                    'rue' => 'string|min:3',
                    'no_rue' => 'string',
                    'date_naissance' => 'date|before:' . date('Y-m-d'),
                    'incorporation' => 'date|required',
                    'no_avs' => 'string|nullable',
                    'cotisation_avs' => 'boolean',
                    'profession' => 'string|max:80|nullable',
                    'employeur' => 'string|max:150|nullable',
                    'lieu_de_travail' => 'string|max:100|nullable',
                    'email' => 'email|nullable',
                    'iban' => 'string|max:100|nullable',
                    'remarque' => 'string|max:300|nullable',
                    'localite_id' => 'integer|min:1',
                    'civilite_id' => 'integer|min:1'
                ]);
                return response()->json(['data' => $this->serializeSapeur(SapeurBusiness::createSapeur($data))], 201);

            case SapeurBusiness::NATURE_CIVIL:
                $data = $request->validate([
                    'nom' => 'required|string|min:2',
                    'prenom' => 'required|string|min:2',
                    'rue' => 'required|string|min:3',
                    'no_rue' => 'required|string',
                    'no_avs' => 'string|nullable',
                    'cotisation_avs' => 'boolean',
                    'email' => 'email|nullable',
                    'iban' => 'string|max:100|nullable',
                    'remarque' => 'string|max:300|nullable',
                    'incorporation' => 'date|required',
                    'localite_id' => 'required|integer|min:1',
                    'civilite_id' => 'required|integer|min:1'
                ]);
                return response()->json(['data' => $this->serializeSapeur(SapeurBusiness::createCivil($data))], 201);

            default:
        }

        return response()->json(['message' => 'Nature invalide'], 422);
    }

    /**
     * Fiche d'un sapeur, numéros de téléphone inclus (affichés dans l'en-tête de la fiche
     * côté APP, sans requête séparée).
     */
    public function show(int $id)
    {
        if (!$sapeur = Sapeur::with('telephones')->find($id)) {
            return response()->json(['message' => 'Sapeur non trouvé'], 404);
        }
        return response()->json(['data' => $this->serializeSapeur($sapeur)]);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'nom' => 'string|min:2',
            'prenom' => 'string|min:2',
            'suffixe' => 'string|nullable',
            'rue' => 'string|min:3',
            'no_rue' => 'string',
            'date_naissance' => 'date|before:' . date('Y-m-d'),
            'no_avs' => 'string|nullable',
            'cotisation_avs' => 'boolean',
            'profession' => 'string|max:80|nullable',
            'employeur' => 'string|max:150|nullable',
            'lieu_de_travail' => 'string|max:100|nullable',
            'email' => 'email|nullable',
            'iban' => 'string|max:100|nullable',
            'remarque' => 'string|max:300|nullable',
            'porteur' => 'boolean|nullable',
            'localite_id' => 'integer|exists:localites,id',
            'civilite_id' => 'integer|min:1'
        ]);

        if (!Sapeur::whereId($id)->exists()) {
            return response()->json(['message' => 'Sapeur non trouvé'], 404);
        }
        $sapeur = SapeurBusiness::updateSapeurById($id, $data);
        return response()->json(['data' => $this->serializeSapeur($sapeur)]);
    }

    public function destroy(int $id)
    {
        if (!Sapeur::whereId($id)->exists()) {
            return response()->json(['message' => 'Sapeur non trouvé'], 404);
        }
        SapeurBusiness::deleteSapeurById($id);
        return response()->json(null, 204);
    }
}
