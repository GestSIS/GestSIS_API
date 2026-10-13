<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Controle;
use App\Models\ControleExec;
use App\Models\ControleMaterielType;
use App\Models\Lavage;
use App\Models\MaterielType;
use App\Models\Sapeur;
use App\Support\Sis;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DbsMigrateLavages extends Command
{
    private const TAILLE_PAQUET = 500;

    protected $signature = 'dbs:migrate-lavages {--sis=* : SIS à traiter (tous par défaut)}';

    protected $description = "Reprend l'ancien suivi des lavages sous forme d'exécutions d'un contrôle « Lavage », SIS par SIS";

    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('Cette commande est interactive : relancez-la sans --no-interaction.');

            return self::FAILURE;
        }

        /** @var array<int, string> $sisDemandes */
        $sisDemandes = $this->option('sis');
        foreach ($sisDemandes as $sis) {
            if (! Sis::isValid($sis)) {
                $this->error("SIS inconnu : {$sis}");

                return self::FAILURE;
            }
        }

        Sis::each(fn (string $sis) => $this->migrerSis($sis), $sisDemandes === [] ? null : $sisDemandes);

        return self::SUCCESS;
    }

    private function migrerSis(string $sis): void
    {
        $dejaMigre = Lavage::query()
            ->whereExists(function ($query): void {
                $query->select(DB::raw(1))
                    ->from('controle_execs')
                    ->whereColumn('controle_execs.article_id', 'lavages.article_id')
                    ->whereColumn('controle_execs.executed_at', 'lavages.date')
                    ->whereRaw('controle_execs.created_at <=> lavages.created_at');
            })
            ->exists();

        if ($dejaMigre) {
            $this->info("SIS {$sis} : déjà migré");

            return;
        }

        $nbLavages = Lavage::count();
        $typesLavables = MaterielType::where('est_lavable', true)->pluck('id');
        if ($nbLavages === 0 && $typesLavables->isEmpty()) {
            $this->info("SIS {$sis} : rien à migrer");

            return;
        }

        $typeIds = $typesLavables
            ->merge(
                Article::whereIn('id', Lavage::select('article_id'))->pluck('materiel_type_id')
            )
            ->unique()
            ->values();

        $nbArticles = Lavage::distinct()->count('article_id');
        $designations = MaterielType::whereIn('id', $typeIds)->orderBy('designation')->pluck('designation');

        $this->line("SIS {$sis} : {$nbLavages} lavage(s) sur {$nbArticles} article(s) à reprendre.");
        $this->line('Types de matériel à relier au contrôle : ' . $designations->implode(', '));
        if (Controle::where('nom', 'Lavage')->exists()) {
            $this->warn('Un contrôle nommé « Lavage » existe déjà : un nouveau contrôle sera tout de même créé.');
        }

        $sapeur = null;
        while ($sapeur === null) {
            $reponse = trim((string) $this->ask('Sapeur à qui attribuer les lavages repris (sapeur_id)'));
            $sapeur = ctype_digit($reponse) ? Sapeur::find((int) $reponse) : null;
            if ($sapeur === null) {
                $this->error("Sapeur introuvable : {$reponse}");
            }
        }

        if (! $this->confirm("Attribuer les lavages à {$sapeur->prenom} {$sapeur->nom} (#{$sapeur->id}) et lancer la migration ?")) {
            $this->info("SIS {$sis} : migration annulée");

            return;
        }

        $controle = null;
        $nbExecutions = 0;
        DB::transaction(function () use ($sapeur, $typeIds, &$controle, &$nbExecutions): void {
            $controle = Controle::create([
                'nom' => 'Lavage',
                'description' => "Créé automatiquement depuis l'ancien suivi des lavages",
                'recurrence_type' => 'NON_PERIODIQUE',
                'recurrence_value' => null,
                'nb_execution_max' => 50,
                'nb_execution_preavis' => 40,
                'externe' => false,
                'reparateur' => null,
            ]);

            foreach ($typeIds as $typeId) {
                ControleMaterielType::create([
                    'controle_id' => $controle->id,
                    'materiel_type_id' => $typeId,
                ]);
            }

            DB::table('lavages')->orderBy('id')->chunkById(
                self::TAILLE_PAQUET,
                function ($lavages) use ($controle, $sapeur, &$nbExecutions): void {
                    $lignes = $lavages->map(fn ($lavage): array => [
                        'controle_id' => $controle->id,
                        'article_id' => $lavage->article_id,
                        'executed_at' => $lavage->date . ' 00:00:00',
                        'executed_by' => $sapeur->id,
                        'trigger_type' => 'NON_PERIODIQUE',
                        'date_echeance' => null,
                        'remarque_globale' => null,
                        'created_at' => $lavage->created_at,
                        'updated_at' => $lavage->updated_at,
                    ])->all();

                    ControleExec::insert($lignes);
                    $nbExecutions += count($lignes);
                }
            );
        });

        $this->info("SIS {$sis} : contrôle #{$controle->id} créé, {$nbExecutions} exécution(s) insérée(s)");
    }
}
