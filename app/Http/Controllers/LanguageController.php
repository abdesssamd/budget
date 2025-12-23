<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switch($locale)
    {
        // Liste des langues autorisées
        if (in_array($locale, ['fr', 'ar'])) {
            Session::put('locale', $locale);
        }
        
        // Redirection vers la page précédente
        return redirect()->back();
    }
}