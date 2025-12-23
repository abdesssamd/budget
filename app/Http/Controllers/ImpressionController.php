<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\BdgOperationBudg;
use App\Models\BdgMandat;

class ImpressionController extends Controller
{
    public function print($dossier, $fichier, $id = null)
    {
        $data = [];
        $viewPath = "imp.{$dossier}.{$fichier}";
        $filename = "Document.pdf";

        // --- GESTION DES ENGAGEMENTS ---
        if ($dossier === 'engagement') {
            
            // 1. Fiche Individuelle
            if ($fichier === 'fiche') {
                $data['op'] = BdgOperationBudg::with(['budget', 'section', 'obj1', 'obj2', 'obj3', 'obj4', 'obj5', 'cf'])
                    ->findOrFail($id);
                $filename = "Engagement_N" . $data['op']->Num_operation . ".pdf";
            }
            
            // 2. Liste Globale (Par année)
            elseif ($fichier === 'liste') {
                $year = $id ?? date('Y');
                $data['engagements'] = BdgOperationBudg::with(['section', 'obj1', 'obj2'])
                    ->where('Type_operation', 3)
                    ->where('EXERCICE', $year)
                    ->orderByDesc('Creer_le')
                    ->get();
                $data['annee'] = $year;
                $filename = "Liste_Engagements_" . $year . ".pdf";
            }
        }

        // --- GESTION DES MANDATS ---
        elseif ($dossier === 'mandat') {
            if (in_array($fichier, ['model1', 'model2', 'bordereau'])) {
                $data['mandat'] = BdgMandat::with(['budget', 'section', 'fournisseur', 'details'])->findOrFail($id);
                $filename = "Mandat_" . $data['mandat']->Num_mandat . ".pdf";
            }
        }

        $pdf = Pdf::loadView($viewPath, $data);
        
        // Orientation Paysage pour les listes
        if ($fichier === 'liste') {
            $pdf->setPaper('a4', 'landscape');
        } else {
            $pdf->setPaper('a4', 'portrait');
        }

        return $pdf->stream($filename);
    }
}