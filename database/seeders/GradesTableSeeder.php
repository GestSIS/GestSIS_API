<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('grades')->insert([
            ['id' => '1',  'designation' => 'Capitaine',          'abreviation' => 'Cap',  'groupe' => '1', 'tri' => '98'],
            ['id' => '2',  'designation' => 'Premier-Lieutenant', 'abreviation' => 'Plt',  'groupe' => '1', 'tri' => '95'],
            ['id' => '3',  'designation' => 'Lieutenant',         'abreviation' => 'Lt',   'groupe' => '1', 'tri' => '90'],
            ['id' => '4',  'designation' => 'Fourrier',           'abreviation' => 'Four', 'groupe' => '2', 'tri' => '85'],
            ['id' => '5',  'designation' => 'Sergent',            'abreviation' => 'Sgt',  'groupe' => '2', 'tri' => '80'],
            ['id' => '6',  'designation' => 'Caporal',            'abreviation' => 'Cpl',  'groupe' => '2', 'tri' => '75'],
            ['id' => '7',  'designation' => 'Appointé',           'abreviation' => 'App',  'groupe' => '3', 'tri' => '70'],
            ['id' => '8',  'designation' => 'Sergent-major',      'abreviation' => 'Sgtm', 'groupe' => '2', 'tri' => '82'],
            ['id' => '9',  'designation' => 'Major',              'abreviation' => 'Maj',  'groupe' => '1', 'tri' => '99'],
            ['id' => '10', 'designation' => 'Soldat',             'abreviation' => 'Sdt',  'groupe' => '3', 'tri' => '65'],
            ['id' => '11', 'designation' => 'Candidats',          'abreviation' => 'Cand', 'groupe' => '3', 'tri' => '60'],
            ['id' => '12', 'designation' => 'Adjudant',           'abreviation' => 'Adj',  'groupe' => '1', 'tri' => '87'],
        ]);
    }
}
