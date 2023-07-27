<?php

namespace App\Domaine\Business;

use App\Domaine\Exceptions\ArrayException;
use App\Infrastructure\Models\LocaliteSis;
use App\Infrastructure\Models\SisContact;
use App\Infrastructure\Models\SisParam;
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
        LocaliteSis::insert(array_map(fn ($localite_id) => (['localite_id' => $localite_id]), $data));
        return LocaliteSis::pluck('localite_id')->toArray();
    }

    public static function supprimerLocalitesSis($data)
    {
        LocaliteSis::whereIn('localite_id', $data)->delete();
        return LocaliteSis::pluck('localite_id')->toArray();
    }

    public static function ajouterContactSis($data)
    {
        if (SisContact::where([
            ['email', '=', $data['email']],
            ['liste', '=', $data['liste']],
        ])->exists()) {
            throw new ArrayException(['email' => 'Email déjà saisi pour cette liste de diffusion'], 'Saisie à double');
        }
        $contact = new SisContact($data);
        $contact->fill($data);
        $contact->save();
        return $contact;
    }

    public static function supprimerContactSis(int $id)
    {
        SisContact::where('id', '=', $id)->delete();
    }

    public function getLogo($sisKey)
    {
        $directory = "documents/" . $sisKey . "/logo";
        $exist = Storage::exists($directory);
        if (!$exist) {
            return null;
        }

        $files = Storage::files($directory);
        if (count($files) == 1) {
            return $files[0];
        }
        return null;
    }

    public function updateLogo($sisKey, $file)
    {
        $directory = "documents/" . $sisKey . "/logo";
        Storage::deleteDirectory($directory);
        Storage::makeDirectory($directory);

        Storage::disk('public')->files($directory);
        // Then add the new one
        $path = $file->store($directory);
        return $path;
    }
}
