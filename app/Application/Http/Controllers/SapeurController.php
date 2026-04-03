<?php

namespace App\Application\Http\Controllers;

use App\Domaine\Business\SapeurBusiness;
use App\Domaine\SPI\SapeurRepository;
use App\Infrastructure\Collections\ListeFoadExport;
use App\Infrastructure\Collections\ListeFsspExport;
use App\Infrastructure\Models\Sapeur;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SapeurController extends Controller
{
    protected $repository;
    protected $business;

    public function __construct(SapeurRepository $repository, SapeurBusiness $business)
    {
        $this->repository = $repository;
        $this->business = $business;
    }

    public function index(Request $request)
    {
        $actif = $request->input('actif', false) === 'true';
        $actifOuAvecMateriel = $request->input('avec-materiel', false) === 'true';
        return response()->json(["data" => $this->repository->listeSapeurLight($actif, $actifOuAvecMateriel)]);
    }

    public function trombinoscope(Request $request)
    {
        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));
        return $this->business->trombinoscope($sisKey);
    }

    public function fiche(Request $request, $sapeurId)
    {
        $sisKey = $request->header('Sis-Id', $request->header('Sis-Key', Null));
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
        $date = $request->get('date', Carbon::now());
        return Excel::download(new ListeFsspExport($date), 'liste_fssp.xlsx');
    }

    public function listeFoad(Request $request)
    {
        $date = $request->get('date', Carbon::now());
        return Excel::download(new ListeFoadExport($date), 'liste_foad.xlsx');
    }

    public function sapeursTelephones()
    {
        return response()->json(['data' => Sapeur::with('telephones')->get(['id'])->toArray()]);
    }

    public function convocationSms()
    {
        return response()->json(['data' => Sapeur::with('telephones')
            ->where('actif', '=', '1')
            ->get(['id', 'nom', 'prenom'])->toArray()]);
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
                return response()->json(['data' => $this->business->createSapeur($data)]);

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
                return response()->json(['data' => $this->business->createCivil($data)]);

            default:
        }

        return response()->json(['error' => ['message' => 'Type invalid']]);
    }

    public function autreStatut(Request $request, $sapeurId)
    {
        $data = $request->validate([
            'actif' => 'required|integer|min:0|max:1',
        ]);
        return response()->json(['data' => $this->business->updateNonSapeurStatut($sapeurId, $data)]);
    }

    public function show(int $id)
    {
        if (!$sapeur = $this->repository->getSapeurDetailsById($id)) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        return response()->json(['data' => $sapeur]);
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

        if (!$sapeur = $this->business->updateSapeurById($id, $data)) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        return response()->json(['data' => $sapeur]);
    }

    public function destroy(int $id)
    {
        if (!Sapeur::where('id', $id)->exists()) {
            return response()->json(['error' => 'Sapeur non trouvé'], 404);
        }
        $this->business->deleteSapeurById($id);
        return response()->json(['data' => "success"]);
    }
}
