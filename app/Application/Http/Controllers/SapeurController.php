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
        $data['type'] = $data['type'] ?? 0;

        return $data;
    }

    public function index(Request $request)
    {
        $actif = $request->input('actif', false) === 'true';
        $actifOuAvecMateriel = $request->input('avec-materiel', false) === 'true';
        $types = $request->validate([
            'type' => 'array',
            'type.*' => 'integer|min:0|max:2',
        ])['type'] ?? null;

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

        if ($types !== null) {
            $query = $query->whereIn('type', $types);
        }
        if ($actif) {
            $query = $query->where('actif', '=', 1);
        }
        if ($actifOuAvecMateriel) {
            $query = $query->where('actif', '=', 1)->orWhereHas('articles');
        }

        $columns = ['id', 'nom', 'prenom', 'civilite_id', 'email', 'suffixe', 'type', 'date_naissance', 'actif', 'fonction_id', 'grade_id', 'localite_id', 'annee_incorporation'];
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
            ->where('actif', '=', '1')
            ->where('type', '=', SapeurBusiness::TYPE_SAPEUR)
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
            'data' => Sapeur::with('telephones')
                ->where('actif', '=', '1')
                ->get(['id', 'nom', 'prenom'])->toArray()
        ]);
    }

    public function store(Request $request)
    {
        $type = $request->validate([
            'type' => 'required|integer|min:0|max:1',
        ])['type'];

        switch ($type) {
            case SapeurBusiness::TYPE_SAPEUR:
                $data = $request->validate([
                    'type' => 'required|integer|min:0|max:1',
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
                return response()->json(['data' => $this->serializeSapeur(SapeurBusiness::createSapeur($data))]);

            case SapeurBusiness::TYPE_CIVIL:
                $data = $request->validate([
                    'type' => 'required|integer|min:0|max:1',
                    'nom' => 'required|string|min:2',
                    'prenom' => 'required|string|min:2',
                    'rue' => 'required|string|min:3',
                    'no_rue' => 'required|string',
                    'no_avs' => 'string|nullable',
                    'cotisation_avs' => 'boolean',
                    'email' => 'email|nullable',
                    'iban' => 'string|max:100|nullable',
                    'remarque' => 'string|max:300|nullable',
                    'localite_id' => 'required|integer|min:1',
                    'civilite_id' => 'required|integer|min:1'
                ]);
                return response()->json(['data' => $this->serializeSapeur(SapeurBusiness::createCivil($data))]);

            default:
        }

        return response()->json(['error' => ['message' => 'Type invalid']]);
    }

    public function autreStatut(Request $request, $sapeurId)
    {
        $data = $request->validate([
            'actif' => 'required|boolean',
        ]);
        return response()->json(['data' => SapeurBusiness::updateNonSapeurStatut($sapeurId, $data)]);
    }

    public function show(int $id)
    {
        if (!$sapeur = Sapeur::find($id)) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
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
            'actif' => 'integer',
            'iban' => 'string|max:100|nullable',
            'remarque' => 'string|max:300|nullable',
            'porteur' => 'boolean|nullable',
            'localite_id' => 'integer|exists:localites,id',
            'civilite_id' => 'integer|min:1'
        ]);

        if (!Sapeur::whereId($id)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        $sapeur = SapeurBusiness::updateSapeurById($id, $data);
        return response()->json(['data' => $this->serializeSapeur($sapeur)]);
    }

    public function destroy(int $id)
    {
        if (!Sapeur::whereId($id)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        SapeurBusiness::deleteSapeurById($id);
        return response()->json(['data' => "success"]);
    }
}
