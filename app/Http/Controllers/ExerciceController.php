<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Exercice;

class ExerciceController extends Controller
{
    public function index()
    {
        $exercices = Exercice::orderBy('anne', 'desc')->get();
        return view('parametres.exercices.index', compact('exercices'));
    }

    public function store(Request $request)
    {
        $request->validate(['anne' => 'required|numeric', 'Libellé' => 'required']);
        
        // Création simple
        $ex = new Exercice();
        $ex->IDExercice = rand(1000, 9999); // Génération ID manuel comme WinDev (ou auto-increment si configuré)
        $ex->fill($request->all());
        $ex->save();

        return back()->with('success', 'Exercice ajouté !');
    }
}