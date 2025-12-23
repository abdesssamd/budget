<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeneralSetting;

class SettingController extends Controller
{
    public function index()
    {
        // Charger la ligne unique (ID=1)
        $settings = GeneralSetting::firstOrNew(['IDParam_general_bdg' => 1]);

        return view('parametres.index', compact('settings'));
    }

    public function update(Request $request)
    {
        // Récupérer ou créer la ligne ID=1
        $settings = GeneralSetting::firstOrNew(['IDParam_general_bdg' => 1]);

        // IMPORTANT : sans ça MySQL refuse l'INSERT
        $settings->IDParam_general_bdg = 1;

        // Remplir automatiquement tous les champs
        $settings->fill($request->all());

        // Sauvegarder
        $settings->save();

        return back()->with('success', 'Paramètres enregistrés avec succès !');
    }
}
