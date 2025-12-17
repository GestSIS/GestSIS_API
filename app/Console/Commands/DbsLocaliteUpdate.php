<?php

namespace App\Console\Commands;

use App\Infrastructure\Models\Localite;
use Illuminate\Console\Command;

class DbsLocaliteUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbs:sync:localites';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $localites = [
            ['id' => '1', 'commune_id' => '22', 'npa' => '2942', 'designation' => 'Alle'],
            ['id' => '2', 'commune_id' => '66', 'npa' => '2954', 'designation' => 'Asuel'],
            ['id' => '3', 'commune_id' => '65', 'npa' => '2854', 'designation' => 'Bassecourt'],
            ['id' => '4', 'commune_id' => '23', 'npa' => '2935', 'designation' => 'Beurnevésin'],
            ['id' => '5', 'commune_id' => '2', 'npa' => '2856', 'designation' => 'Boécourt'],
            ['id' => '6', 'commune_id' => '57', 'npa' => '2926', 'designation' => 'Boncourt'],
            ['id' => '7', 'commune_id' => '24', 'npa' => '2944', 'designation' => 'Bonfol'],
            ['id' => '8', 'commune_id' => '17', 'npa' => '2803', 'designation' => 'Bourrignon'],
            ['id' => '9', 'commune_id' => '50', 'npa' => '2904', 'designation' => 'Bressaucourt'],
            ['id' => '10', 'commune_id' => '59', 'npa' => '2925', 'designation' => 'Buix'],
            ['id' => '11', 'commune_id' => '30', 'npa' => '2915', 'designation' => 'Bure'],
            ['id' => '12', 'commune_id' => '66', 'npa' => '2947', 'designation' => 'Charmoille'],
            ['id' => '13', 'commune_id' => '10', 'npa' => '2843', 'designation' => 'Châtillon'],
            ['id' => '14', 'commune_id' => '36', 'npa' => '2345', 'designation' => 'La Chaux-des-Breuleux'],
            ['id' => '15', 'commune_id' => '67', 'npa' => '2906', 'designation' => 'Chevenez'],
            ['id' => '16', 'commune_id' => '9', 'npa' => '2830', 'designation' => 'Choindez'],
            ['id' => '17', 'commune_id' => '25', 'npa' => '2932', 'designation' => 'Coeuve'],
            ['id' => '18', 'commune_id' => '61', 'npa' => '2826', 'designation' => 'Corban'],
            ['id' => '19', 'commune_id' => '64', 'npa' => '2952', 'designation' => 'Cornol'],
            ['id' => '20', 'commune_id' => '29', 'npa' => '2823', 'designation' => 'Courcelon'],
            ['id' => '21', 'commune_id' => '47', 'npa' => '2825', 'designation' => 'Courchapoix'],
            ['id' => '22', 'commune_id' => '58', 'npa' => '2922', 'designation' => 'Courchavon'],
            ['id' => '23', 'commune_id' => '65', 'npa' => '2853', 'designation' => 'Courfaivre'],
            ['id' => '24', 'commune_id' => '63', 'npa' => '2950', 'designation' => 'Courgenay'],
            ['id' => '25', 'commune_id' => '9', 'npa' => '2830', 'designation' => 'Courrendlin'],
            ['id' => '26', 'commune_id' => '29', 'npa' => '2822', 'designation' => 'Courroux'],
            ['id' => '27', 'commune_id' => '73', 'npa' => '2905', 'designation' => 'Courtedoux'],
            ['id' => '28', 'commune_id' => '59', 'npa' => '2923', 'designation' => 'Courtemaîche'],
            ['id' => '29', 'commune_id' => '63', 'npa' => '2950', 'designation' => 'Courtemautruy'],
            ['id' => '30', 'commune_id' => '8', 'npa' => '2852', 'designation' => 'Courtételle'],
            ['id' => '31', 'commune_id' => '26', 'npa' => '2933', 'designation' => 'Damphreux'],
            // ['id' => '32', 'commune_id' => NULL, 'npa' => '2933', 'designation' => 'Damphreux-Lugnez'],
            ['id' => '33', 'commune_id' => '67', 'npa' => '2914', 'designation' => 'Damvant'],
            ['id' => '34', 'commune_id' => '48', 'npa' => '2800', 'designation' => 'Delémont'],
            ['id' => '35', 'commune_id' => '21', 'npa' => '2802', 'designation' => 'Develier'],
            ['id' => '36', 'commune_id' => '18', 'npa' => '2813', 'designation' => 'Ederswiler'],
            ['id' => '37', 'commune_id' => '43', 'npa' => '2885', 'designation' => 'Epauvillers'],
            ['id' => '38', 'commune_id' => '43', 'npa' => '2886', 'designation' => 'Epiquerez'],
            ['id' => '39', 'commune_id' => '72', 'npa' => '2916', 'designation' => 'Fahy'],
            ['id' => '40', 'commune_id' => '50', 'npa' => '2902', 'designation' => 'Fontenais'],
            ['id' => '41', 'commune_id' => '70', 'npa' => '2718', 'designation' => 'Fornet-Dessus'],
            ['id' => '42', 'commune_id' => '66', 'npa' => '2953', 'designation' => 'Fregiécourt'],
            // ['id' => '43', 'commune_id' => '66', 'npa' => '2953', 'designation' => 'Fregiécourt-Pleujouse'],
            ['id' => '44', 'commune_id' => '65', 'npa' => '2855', 'designation' => 'Glovelier'],
            ['id' => '45', 'commune_id' => '40', 'npa' => '2354', 'designation' => 'Goumois'],
            ['id' => '46', 'commune_id' => '77', 'npa' => '2908', 'designation' => 'Grandfontaine'],
            ['id' => '47', 'commune_id' => '70', 'npa' => '2718', 'designation' => 'Lajoux'],
            ['id' => '48', 'commune_id' => '37', 'npa' => '2360', 'designation' => 'Le Bémont'],
            ['id' => '49', 'commune_id' => '39', 'npa' => '2336', 'designation' => 'Le Boéchet'],
            ['id' => '50', 'commune_id' => '42', 'npa' => '2345', 'designation' => 'Le Cerneux-Veusil'],
            ['id' => '51', 'commune_id' => '38', 'npa' => '2340', 'designation' => 'Le Noirmont'],
            ['id' => '52', 'commune_id' => '42', 'npa' => '2345', 'designation' => 'Le Peuchapatte'],
            ['id' => '53', 'commune_id' => '71', 'npa' => '2714', 'designation' => 'Le Prédame'],
            ['id' => '54', 'commune_id' => '39', 'npa' => '2336', 'designation' => 'Les Bois'],
            ['id' => '55', 'commune_id' => '41', 'npa' => '2345', 'designation' => 'Les Breuleux'],
            ['id' => '56', 'commune_id' => '42', 'npa' => '2338', 'designation' => 'Les Emibois'],
            ['id' => '57', 'commune_id' => '74', 'npa' => '2363', 'designation' => 'Les Enfers'],
            ['id' => '58', 'commune_id' => '71', 'npa' => '2714', 'designation' => 'Les Genevez'],
            ['id' => '59', 'commune_id' => '40', 'npa' => '2353', 'designation' => 'Les Pommerats'],
            ['id' => '60', 'commune_id' => '15', 'npa' => '2807', 'designation' => 'Lucelle'],
            ['id' => '61', 'commune_id' => '26', 'npa' => '2933', 'designation' => 'Lugnez'],
            ['id' => '62', 'commune_id' => '62', 'npa' => '2827', 'designation' => 'Mervelier'],
            ['id' => '63', 'commune_id' => '16', 'npa' => '2806', 'designation' => 'Mettembert'],
            ['id' => '64', 'commune_id' => '66', 'npa' => '2946', 'designation' => 'Miécourt'],
            ['id' => '65', 'commune_id' => '2', 'npa' => '2857', 'designation' => 'Montavon'],
            ['id' => '66', 'commune_id' => '43', 'npa' => '2884', 'designation' => 'Montenol'],
            ['id' => '67', 'commune_id' => '68', 'npa' => '2362', 'designation' => 'Montfaucon'],
            ['id' => '68', 'commune_id' => '68', 'npa' => '2362', 'designation' => 'Montfavergier'],
            ['id' => '69', 'commune_id' => '59', 'npa' => '2924', 'designation' => 'Montignez'],
            ['id' => '70', 'commune_id' => '43', 'npa' => '2883', 'designation' => 'Montmelon'],
            ['id' => '71', 'commune_id' => '60', 'npa' => '2828', 'designation' => 'Montsevelier'],
            ['id' => '72', 'commune_id' => '20', 'npa' => '2812', 'designation' => 'Movelier'],
            ['id' => '73', 'commune_id' => '42', 'npa' => '2338', 'designation' => 'Muriaux'],
            ['id' => '74', 'commune_id' => '43', 'npa' => '2889', 'designation' => 'Ocourt'],
            ['id' => '75', 'commune_id' => '15', 'npa' => '2807', 'designation' => 'Pleigne'],
            ['id' => '76', 'commune_id' => '66', 'npa' => '2953', 'designation' => 'Pleujouse'],
            ['id' => '77', 'commune_id' => '76', 'npa' => '2900', 'designation' => 'Porrentruy'],
            ['id' => '78', 'commune_id' => '9', 'npa' => '2832', 'designation' => 'Rebeuvelier'],
            ['id' => '79', 'commune_id' => '67', 'npa' => '2912', 'designation' => 'Réclère'],
            ['id' => '80', 'commune_id' => '67', 'npa' => '2912', 'designation' => 'Réclère-Roche-d\'Or'],
            ['id' => '81', 'commune_id' => '67', 'npa' => '2912', 'designation' => 'Roche-d\'Or'],
            ['id' => '82', 'commune_id' => NULL, 'npa' => '2907', 'designation' => 'Rocourt'],
            ['id' => '83', 'commune_id' => '11', 'npa' => '2842', 'designation' => 'Rossemaison'],
            ['id' => '84', 'commune_id' => '40', 'npa' => '2350', 'designation' => 'Saignelégier'],
            ['id' => '85', 'commune_id' => '5', 'npa' => '2873', 'designation' => 'Saulcy'],
            ['id' => '86', 'commune_id' => '43', 'npa' => '2888', 'designation' => 'Seleute'],
            ['id' => '87', 'commune_id' => '2', 'npa' => '2857', 'designation' => 'Séprais'],
            ['id' => '88', 'commune_id' => '44', 'npa' => '2887', 'designation' => 'Soubey'],
            ['id' => '89', 'commune_id' => '65', 'npa' => '2864', 'designation' => 'Soulce'],
            ['id' => '90', 'commune_id' => '14', 'npa' => '2805', 'designation' => 'Soyhières'],
            ['id' => '91', 'commune_id' => '75', 'npa' => '2364', 'designation' => 'St-Brais'],
            ['id' => '92', 'commune_id' => '43', 'npa' => '2882', 'designation' => 'St-Ursanne'],
            ['id' => '93', 'commune_id' => '65', 'npa' => '2863', 'designation' => 'Undervelier'],
            ['id' => '94', 'commune_id' => '9', 'npa' => '2830', 'designation' => 'Vellerat'],
            ['id' => '95', 'commune_id' => '28', 'npa' => '2943', 'designation' => 'Vendlincourt'],
            ['id' => '96', 'commune_id' => '60', 'npa' => '2829', 'designation' => 'Vermes'],
            ['id' => '97', 'commune_id' => '60', 'npa' => '2824', 'designation' => 'Vicques'],
            ['id' => '98', 'commune_id' => '50', 'npa' => '2903', 'designation' => 'Villars-sur-Fontenais'],
            ['id' => '99', 'commune_id' => NULL, 'npa' => '', 'designation' => '-'],
            ['id' => '100', 'commune_id' => '31', 'npa' => '2053', 'designation' => 'Cernier'],
            ['id' => '101', 'commune_id' => '35', 'npa' => '2054', 'designation' => 'Chézard-St-Martin'],
            ['id' => '102', 'commune_id' => '31', 'npa' => '2056', 'designation' => 'Dombresson'],
            ['id' => '103', 'commune_id' => '33', 'npa' => '2052', 'designation' => 'Fontainemelon'],
            ['id' => '104', 'commune_id' => '34', 'npa' => '2046', 'designation' => 'Fontaines NE'],
            ['id' => '105', 'commune_id' => '32', 'npa' => '2208', 'designation' => 'Les Hauts-Geneveys'],
            ['id' => '106', 'commune_id' => '35', 'npa' => '2054', 'designation' => 'Les Vieux-Prés'],
            ['id' => '107', 'commune_id' => NULL, 'npa' => '2108', 'designation' => 'Couvet'],
            ['id' => '108', 'commune_id' => NULL, 'npa' => '2525', 'designation' => 'Le Landeron'],
            ['id' => '109', 'commune_id' => NULL, 'npa' => '2000', 'designation' => 'Neuchâtel'],
            ['id' => '110', 'commune_id' => NULL, 'npa' => '2300', 'designation' => 'La Chaux-de-Fonds'],
            ['id' => '111', 'commune_id' => NULL, 'npa' => '2207', 'designation' => 'Coffrane'],
            ['id' => '113', 'commune_id' => NULL, 'npa' => '2057', 'designation' => 'Villiers'],
            ['id' => '114', 'commune_id' => NULL, 'npa' => '2043', 'designation' => 'Boudevilliers'],
            ['id' => '115', 'commune_id' => NULL, 'npa' => '2065', 'designation' => 'Savagnier'],
            ['id' => '116', 'commune_id' => NULL, 'npa' => '2058', 'designation' => 'Le Pâquier NE'],
            ['id' => '117', 'commune_id' => NULL, 'npa' => '2063', 'designation' => 'Vilars'],
            ['id' => '118', 'commune_id' => NULL, 'npa' => '2063', 'designation' => 'Saules NE'],
            ['id' => '119', 'commune_id' => NULL, 'npa' => '2042', 'designation' => 'Valangin'],
            ['id' => '120', 'commune_id' => NULL, 'npa' => '2206', 'designation' => 'Les Geneveys-s-Coffrane'],
            ['id' => '121', 'commune_id' => NULL, 'npa' => '2205', 'designation' => 'Montmollin'],
            ['id' => '122', 'commune_id' => NULL, 'npa' => '2063', 'designation' => 'Fenin'],
            ['id' => '112', 'commune_id' => NULL, 'npa' => '2063', 'designation' => 'Engollon'],
            ['id' => '123', 'commune_id' => NULL, 'npa' => '2720', 'designation' => 'Tramelan'],
            ['id' => '124', 'commune_id' => NULL, 'npa' => '2710', 'designation' => 'Tavannes'],
            ['id' => '125', 'commune_id' => '54', 'npa' => '2713', 'designation' => 'Bellelay'],
            ['id' => '126', 'commune_id' => '51', 'npa' => '2715', 'designation' => 'Châtelat'],
            ['id' => '127', 'commune_id' => '51', 'npa' => '2717', 'designation' => 'Fornet-Dessous'],
            ['id' => '128', 'commune_id' => '54', 'npa' => '2712', 'designation' => 'Le Fuet'],
            ['id' => '129', 'commune_id' => '56', 'npa' => '2748', 'designation' => 'Les Ecorcheresses'],
            ['id' => '130', 'commune_id' => '52', 'npa' => '2715', 'designation' => 'Monible'],
            ['id' => '131', 'commune_id' => '53', 'npa' => '2717', 'designation' => 'Rebévelier'],
            ['id' => '132', 'commune_id' => '54', 'npa' => '2732', 'designation' => 'Saicourt'],
            ['id' => '133', 'commune_id' => '55', 'npa' => '2716', 'designation' => 'Sornetan'],
            ['id' => '134', 'commune_id' => '56', 'npa' => '2748', 'designation' => 'Souboz'],
            ['id' => '135', 'commune_id' => NULL, 'npa' => '2500', 'designation' => 'Bienne'],
            ['id' => '136', 'commune_id' => NULL, 'npa' => '3294', 'designation' => 'Büren an der Aare'],
            ['id' => '137', 'commune_id' => NULL, 'npa' => '2606', 'designation' => 'Corgémont'],
            ['id' => '138', 'commune_id' => NULL, 'npa' => '2738', 'designation' => 'Court '],
            ['id' => '139', 'commune_id' => NULL, 'npa' => '2608', 'designation' => 'Courtelary'],
            ['id' => '140', 'commune_id' => NULL, 'npa' => '2735', 'designation' => 'Malleray'],
            ['id' => '141', 'commune_id' => NULL, 'npa' => '2740', 'designation' => 'Moutier'],
            ['id' => '142', 'commune_id' => NULL, 'npa' => '2534', 'designation' => 'Orvin'],
            ['id' => '143', 'commune_id' => NULL, 'npa' => '2603', 'designation' => 'Péry'],
            ['id' => '144', 'commune_id' => NULL, 'npa' => '2605', 'designation' => 'Sonceboz'],
            ['id' => '145', 'commune_id' => NULL, 'npa' => '2610', 'designation' => 'St-Imier'],
            ['id' => '146', 'commune_id' => '69', 'npa' => '4710', 'designation' => 'Balsthal'],
            ['id' => '147', 'commune_id' => '60', 'npa' => '2829', 'designation' => 'Envelier'],
            ['id' => '148', 'commune_id' => NULL, 'npa' => '25150', 'designation' => 'Goux-Lès-Dambelin'],
            ['id' => '149', 'commune_id' => NULL, 'npa' => '2523', 'designation' => 'Lignières NE'],
            ['id' => '150', 'commune_id' => NULL, 'npa' => '2827', 'designation' => 'La Scheulte'],
            ['id' => '151', 'commune_id' => NULL, 'npa' => '1642', 'designation' => 'Sorens FR'],
            ['id' => '152', 'commune_id' => NULL, 'npa' => '1473', 'designation' => 'Châtillon FR'],
        ];
        $dbs = config('database.dbs');
        foreach ($dbs as $db) {
            printf("Fix db=db_" . $db . "\n");

            foreach ($localites as $localite) {
                Localite::on("db_" . $db)->updateOrCreate(
                    ['id' => $localite['id']],
                    [
                        'commune_id' => $localite['commune_id'],
                        'npa' => $localite['npa'],
                        'designation' => $localite['designation'],
                    ]
                );
            }

            printf("\n");
        }
        printf("Migrating done\n");
        return 0;
    }
}
