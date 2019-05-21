<?php


namespace App\Repository;


use App\Models\SapeursTelephone;
use App\Models\Sapeur;
use App\Models\Permis;

use Validator;
use Exception;

class SapeurBusiness
{

    protected $sapeur;

    public function __construct(Sapeur $sapeur)
    {
        $this->sapeur = $sapeur;
    }

    /**
     * Get's a sapeur by it's ID
     *
     * @param int
     * @return SapeurBusiness
     */
    public static function get($sapeur_id)
    {
        return new SapeurBusiness(Sapeur::findOrFail($sapeur_id));
    }

    /**
     * Create a sapeur
     *
     * @param $data
     * @return SapeurBusiness
     */
    public static function createSapeur($data)
    {
        $validation = Validator::make($data,
            array(
                'nom' 	    	    => 'string|min:2',
                'prenom'    	    => 'string|min:2',
                'suffixe' 		    => 'string|min:3',
                'rue'               => 'string|min:3',
                'no_rue'		    => 'string',
                'date_naissance'	=> 'date',
                'no_avs'		    => 'string',
                'profession'	    => 'string|max:80',
                'employeur'	        => 'string|max:150',
                'lieu_de_travail'	=> 'string|max:100',
                'email'	            => 'email',
                'actif' 	        => 'numeric',
                'iban'	            => 'string|max:100',
                'iban_status'	    => 'numeric',
                'remarque'	        => 'string|max:300',
                'porteur'	        => 'boolean',
                'localite_id'       => 'numeric|min:0'
            ));

        if($validation->fails()) {
            throw new Exception($validation->messages());
        }

        $sapeur = new Sapeur();
        $sapeur->fill($data);
        $sapeur->save();

        return new SapeurBusiness($sapeur);
    }

    /**
     * Delete a sapeur.
     *
     * @param int
     */
    public static function delete($sapeur_id)
    {
        //TODO: Check
        Sapeur::destroy($sapeur_id);
    }

    /**
     * Updates a post.
     *
     * @param int
     * @param array
     */
    public function update($data)
    {
        //Post::find($post_id)-&gt;update($post_data);
        //TODO update sapeur data

        $validation = Validator::make($data,
            array(
                'nom' 	    	    => 'string|min:2',
                'prenom'    	    => 'string|min:2',
                'suffixe' 		    => 'string|min:3',
                'rue'               => 'string|min:3',
                'no_rue'		    => 'string',
                'date_naissance'	=> 'date',
                'no_avs'		    => 'string',
                'profession'	    => 'string|max:80',
                'employeur'	        => 'string|max:150',
                'lieu_de_travail'	=> 'string|max:100',
                'email'	            => 'email',
                'actif' 	        => 'numeric',
                'iban'	            => 'string|max:100',
                'iban_status'	    => 'numeric',
                'remarque'	        => 'string|max:300',
                'porteur'	        => 'boolean',
                'localite_id'       => 'numeric|min:0'
            ));

        if($validation->fails()) {
            throw new Exception($validation->messages());
        } else {
            $this->sapeur->update($data);
        }

        //TODO Generate a migration
        // localite_id
    }

    public function addCours()
    {
        //Add Grade

        //Add Fonction

    }

    /**
     * Add a Telephone
     *
     * @param array $data
     * @throws Exception
     */
    public function addTelephone($data)
    {
        //TODO
        $validation = Validator::make($data,
            array(
                'telephone_id'      => 'required|numeric|min:1',
                'telephone_type_id' => 'required|numeric',
                'numero'            => 'required|string|min:2',
                'order'             => 'required|numeric',
                'rta'               => 'required|boolean',
            )
        );

        if($validation->fails()) {
            throw new Exception($validation->messages());
        }

        //TODO: Check if this numero already exist

        //Create permis
        $telephone = new SapeursTelephone();
        $telephone->fill($data);

        //Ajout du permis au sapeur
        $this->sapeur->telephones()->save($telephone);
    }

    /**
     * Update a Telephone informations
     *
     * @param array $data
     * @throws Exception
     */
    public function updateTelephone($data)
    {
        //TODO
        $validation = Validator::make($data,
            array(
                'telephone_id'      => 'required|numeric|min:1',
                'telephone_type_id' => 'numeric',
                'numero'            => 'string|min:2',
                'order'             => 'numeric',
                'rta'               => 'boolean',
            )
        );

        if($validation->fails()) {
            throw new Exception($validation->messages());
        }

        $telephone = $this->sapeur->telephones()->where('telephone_id', $data['telephone_id'])->first();

        //Search for the telephone
        if($telephone === null) {
            throw new Exception("Unable to find telephone");
        } else {
            //Update telephone
            $telephone->update($data);
            $telephone->save();
        }
    }

    /**
     * Remove a Telephone
     *
     * @param int $permis_id
     */
    public function removeTelephone($telephone_id)
    {
        $this->sapeur->telephones()->where('telephone_id', $telephone_id)->delete();
    }

    /**
     * Add a permis
     *
     * @param array $data
     * @throws Exception
     */
    public function addPermis($data)
    {
        $validation = Validator::make($data,
            array(
                'permis_type_id'    => 'required|numeric|min:1',
                'date'              => 'required|date',
            )
        );

        if($validation->fails()) {
            throw new Exception($validation->messages());
        }

        $permis = $this->sapeur->permis()->where('permis_type_id', $data['permis_type_id'])->first();

        //Check si sapeur as déjà ce permis
        if($permis !== null) {
            throw new Exception("Unable to find permis");

        } else {
            //Create permis
            $permis = new Permis();
            $permis->date = $data['date'];
            $permis->permis_type_id = $data['permis_type_id'];

            //Ajout du permis au sapeur
            $this->sapeur->permis()->save($permis);
        }
    }

    /**
     * Update a permis informations
     *
     * @param array $data
     * @throws Exception
     */
    public function updatePermis($data)
    {
        $validation = Validator::make($data,
            array(
                'permis_id' => 'required|numeric|min:1',
                'date'      => 'required|date',
            )
        );

        if($validation->fails()) {
            throw new Exception($validation->messages());
        }

        $permis = $this->sapeur->permis()->where('permis_type_id', $data['permis_id'])->first();

        //Check si sapeur as déjà ce permis
        if($permis === null) {
            throw new Exception("Duplicated permis type for sapeur");
        } else {
            //Update permis
            $permis->date = $data['date'];
            $permis->save();
        }
    }

    /**
     * Remove a Permis
     *
     * @param int $permis_id
     */
    public function removePermis($permis_id)
    {
        $this->sapeur->permis()->where('permis_id', $permis_id)->delete();
    }
}
