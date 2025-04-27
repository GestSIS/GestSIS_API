<?php

namespace App\Application\Typst;

use App\Domaine\Exceptions\ArrayException;
use File;
use Process;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Storage;

/**
 * Typst template disponibles
 */
enum TypstTemplate: string
{
    case Comptes = 'comptes';
    case Convocations = 'convocations';
    case Decompte = 'decompte';
    case DecompteParSapeur = 'decompte-par-sapeur';
    case ResumeParSapeur = 'resume-par-sapeur';
    case FicheSapeur = 'fiche-sapeur';
    case ListeAppel = 'liste-appel';
    case ListePresence = 'liste-presence';
    case RapportIntervention = 'rapport-intervention';
    case Trombinoscope = 'trombinoscope';
}

class TypstToPdfGenerator
{
    public static function generateDocument(TypstTemplate $template, array $data, string $logoPath, array $extraStorageFiles = [])
    {
        $directory = (new TemporaryDirectory())->create();

        $commonFile = $directory->path("common.typ");
        $typstFile = $directory->path("$template->value.typ");
        $jsonFile = $directory->path("$template->value.json");
        $logoFile = trim($directory->path("logo.png"), ".png");
        $commonPath = resource_path("/typst/common.typ");
        $templatePath = resource_path("/typst/$template->value.typ");

        File::copy($commonPath, $commonFile);
        File::copy($templatePath, $typstFile);
        File::put($jsonFile, json_encode($data));
        File::put($logoFile, Storage::get($logoPath));

        foreach ($extraStorageFiles as $file) {
            File::put($directory->path($file), Storage::get($file));
        }

        $result = Process::run(config('typst.bin_path') . " compile $typstFile --font-path=" . config('typst.font_path'));

        if ($result->failed()) {
            $directory->delete();
            throw new ArrayException(['output' => $result->errorOutput(), 'input' => json_encode($data)], "Une erreur est survenue lors de la generation du pdf");
        }

        $pdfPath = $directory->path("$template->value.pdf");
        $pdf = File::get($pdfPath);

        $directory->delete();
        return $pdf;
    }
}