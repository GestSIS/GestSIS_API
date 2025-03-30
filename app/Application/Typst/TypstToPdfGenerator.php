<?php

namespace App\Application\Typst;

use App\Domaine\Exceptions\ArrayException;
use File;
use Process;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Storage;

enum TypstTemplate: string
{
    case Convocations = 'convocations';
    case DecompteParSapeur = 'decompte-par-sapeur';
    case Decompte = 'decompte';
    case FicheSapeur = 'fiche_sapeur';
    case ListeAppel = 'liste_appel';
    case ListePresence = 'liste_presence';
    case RapportIntervention = 'rapport-intervention';
}

class TypstToPdfGenerator
{
    public static function generateDocument(TypstTemplate $template, array $data, string $logoPath)
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

        $result = Process::run(config('typst.bin_path') . " compile $typstFile");

        if ($result->failed()) {
            $directory->delete();
            throw new ArrayException(['input' => json_encode($data)], "Une erreur est survenue lors de la generation du pdf");
        }

        $pdfPath = $directory->path("$template->value.pdf");
        $pdf = File::get($pdfPath);

        $directory->delete();
        return $pdf;
    }
}