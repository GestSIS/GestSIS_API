<?php

use Illuminate\Database\Seeder;

class CoursTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        DB::table('cours')->insert([
            'id' => 1,
            'precedent_id' => null,
            'grade_id' => null,
            'fonction_id' => null,
            'abreviation' => 'BA',
            'designation' => 'Cours de base --> 2014',
            'tri' => 10,
            'status' => 1
//            'annee_fin' => 2014
        ]);

//        {"COU_ID":"1","COU_Prec":"0","GRA_ID":"0","GRA_ID2004":"0","FON_ID":"22","COU_Fon_Cumul":"0","COU_Abr":"BA","COU_Designation":"Cours de base --> 2014","COU_Tri":"10","COU__Status":"0"},
//        {"COU_ID":"2","COU_Prec":"1","GRA_ID":"0","GRA_ID2004":"0","FON_ID":"19","COU_Fon_Cumul":"0","COU_Abr":"PAR","COU_Designation":"Porteur","COU_Tri":"65","COU__Status":"1"},
//        {"COU_ID":"3","COU_Prec":"18","GRA_ID":"7","GRA_ID2004":"6","FON_ID":"16","COU_Fon_Cumul":"0","COU_Abr":"CG 1","COU_Designation":"Chef de groupe 1 --> 2016","COU_Tri":"11","COU__Status":"0"},
//        {"COU_ID":"4","COU_Prec":"3","GRA_ID":"6","GRA_ID2004":"5","FON_ID":"14","COU_Fon_Cumul":"0","COU_Abr":"CG 2","COU_Designation":"Chef de groupe 2 --> 2016","COU_Tri":"12","COU__Status":"0"},
//        {"COU_ID":"5","COU_Prec":"4","GRA_ID":"5","GRA_ID2004":"3","FON_ID":"13","COU_Fon_Cumul":"1","COU_Abr":"CI 1","COU_Designation":"Chef d'intervention 1","COU_Tri":"90","COU__Status":"1"},
//        {"COU_ID":"6","COU_Prec":"5","GRA_ID":"3","GRA_ID2004":"0","FON_ID":"12","COU_Fon_Cumul":"0","COU_Abr":"CI 2","COU_Designation":"Chef d'intervention 2","COU_Tri":"95","COU__Status":"1"},
//        {"COU_ID":"7","COU_Prec":"0","GRA_ID":"0","GRA_ID2004":"0","FON_ID":"0","COU_Fon_Cumul":"0","COU_Abr":"Cdt","COU_Designation":"Commandant","COU_Tri":"100","COU__Status":"1"},
//        {"COU_ID":"8","COU_Prec":"3","GRA_ID":"0","GRA_ID2004":"0","FON_ID":"15","COU_Fon_Cumul":"1","COU_Abr":"Ech rem","COU_Designation":"CG \u00e9chelles remorquables","COU_Tri":"80","COU__Status":"1"},
//        {"COU_ID":"9","COU_Prec":"18","GRA_ID":"0","GRA_ID2004":"0","FON_ID":"18","COU_Fon_Cumul":"0","COU_Abr":"MACH","COU_Designation":"Machiniste","COU_Tri":"60","COU__Status":"1"},
//        {"COU_ID":"10","COU_Prec":"0","GRA_ID":"4","GRA_ID2004":"4","FON_ID":"5","COU_Fon_Cumul":"1","COU_Abr":"Four","COU_Designation":"Fourrier","COU_Tri":"45","COU__Status":"1"},
//        {"COU_ID":"11","COU_Prec":"1","GRA_ID":"0","GRA_ID2004":"0","FON_ID":"20","COU_Fon_Cumul":"1","COU_Abr":"ELEC","COU_Designation":"Electricien","COU_Tri":"70","COU__Status":"1"},
//        {"COU_ID":"12","COU_Prec":"1","GRA_ID":"0","GRA_ID2004":"0","FON_ID":"21","COU_Fon_Cumul":"0","COU_Abr":"GC","COU_Designation":"Garde et circulation","COU_Tri":"50","COU__Status":"1"},
//        {"COU_ID":"13","COU_Prec":"18","GRA_ID":"0","GRA_ID2004":"0","FON_ID":"17","COU_Fon_Cumul":"0","COU_Abr":"PAM","COU_Designation":"Pr\u00e9pos\u00e9 aux mat\u00e9riel","COU_Tri":"55","COU__Status":"1"},
//        {"COU_ID":"14","COU_Prec":"5","GRA_ID":"0","GRA_ID2004":"0","FON_ID":"0","COU_Fon_Cumul":"1","COU_Abr":"DCH","COU_Designation":"D\u00e9fense hydrocarbure","COU_Tri":"92","COU__Status":"1"},
//        {"COU_ID":"15","COU_Prec":"2","GRA_ID":"0","GRA_ID2004":"0","FON_ID":"27","COU_Fon_Cumul":"1","COU_Abr":"PAR","COU_Designation":"Pr\u00e9pos\u00e9 app respiratoire","COU_Tri":"66","COU__Status":"1"},
//        {"COU_ID":"16","COU_Prec":"0","GRA_ID":"0","GRA_ID2004":"0","FON_ID":"0","COU_Fon_Cumul":"0","COU_Abr":"BLS","COU_Designation":"BLS-AED","COU_Tri":"41","COU__Status":"1"},
//        {"COU_ID":"17","COU_Prec":"16","GRA_ID":"0","GRA_ID2004":"0","FON_ID":"0","COU_Fon_Cumul":"0","COU_Abr":"BA 1","COU_Designation":"FGB\/PR (formation  g\u00e9n\u00e9ral de base+PR)","COU_Tri":"42","COU__Status":"1"},
//        {"COU_ID":"18","COU_Prec":"17","GRA_ID":"0","GRA_ID2004":"0","FON_ID":"19","COU_Fon_Cumul":"0","COU_Abr":"BA 2","COU_Designation":"FTB (formation technique de base)","COU_Tri":"43","COU__Status":"1"},
//        {"COU_ID":"20","COU_Prec":"0","GRA_ID":"0","GRA_ID2004":"0","FON_ID":"0","COU_Fon_Cumul":"0","COU_Abr":"PR","COU_Designation":"Rattrapage","COU_Tri":"45","COU__Status":"1"},
//        {"COU_ID":"19","COU_Prec":"0","GRA_ID":"6","GRA_ID2004":"0","FON_ID":"14","COU_Fon_Cumul":"0","COU_Abr":"CG","COU_Designation":"Chef de groupe","COU_Tri":"85","COU__Status":"1"}
    }
}
