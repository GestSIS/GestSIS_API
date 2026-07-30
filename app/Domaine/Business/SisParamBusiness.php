<?php

namespace App\Domaine\Business;

use App\Domaine\Exceptions\ArrayException;
use App\Models\LocaliteSis;
use App\Models\SisContact;
use App\Models\SisParam;
use Illuminate\Support\Facades\Storage;

class SisParamBusiness
{
    public static function updateParams($data)
    {
        SisParam::updateOrCreate([], $data);
        return SisParam::first();
    }

    public static function ajouterLocalitesSis($data)
    {
        LocaliteSis::insert(collect($data)->map(fn($localite_id) => ['localite_id' => $localite_id])->toArray());
        return LocaliteSis::pluck('localite_id')->toArray();
    }

    public static function supprimerLocalitesSis($data)
    {
        LocaliteSis::whereIn('localite_id', $data)->delete();
        return LocaliteSis::pluck('localite_id')->toArray();
    }

    public static function ajouterContactSis($data)
    {
        if (
            SisContact::where([
                ['email', $data['email']],
                ['liste', $data['liste']],
            ])->exists()
        ) {
            throw new ArrayException(['email' => 'Email déjà saisi pour cette liste de diffusion'], 'Saisie à double');
        }
        $contact = new SisContact($data);
        $contact->fill($data);
        $contact->save();
        return $contact;
    }

    public static function supprimerContactSis(int $id)
    {
        SisContact::whereId($id)->delete();
    }

    public static function getLogo($sisKey)
    {
        if (!in_array($sisKey, config('database.dbs'), true)) {
            return null;
        }

        $directory = "documents/$sisKey/logo";
        $exist = Storage::exists($directory);
        if (!$exist) {
            return null;
        }

        $files = Storage::files($directory);
        if (count($files) === 1) {
            return $files[0];
        }
        return null;
    }

    public static function updateLogo($sisKey, $file)
    {
        $directory = "documents/$sisKey/logo";
        Storage::deleteDirectory($directory);
        Storage::makeDirectory($directory);

        Storage::disk('public')->files($directory);
        // Then add the new one
        $path = $file->store($directory);
        return $path;
    }
}
