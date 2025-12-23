<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Budget;
use App\Models\Exercice; // Pour la liste déroulante

class BudgetController extends Controller
{
    // Liste des budgets
    public function index()
    {
        $budgets = Budget::orderBy('EXERCICE', 'desc')->get();
        $exercices = Exercice::where('Ouvert', 1)->orderBy('anne', 'desc')->get();
        
        return view('parametres.budgets.index', compact('budgets', 'exercices'));
    }

    // Créer un budget
    public function store(Request $request)
    {
        $request->validate(['designation' => 'required', 'EXERCICE' => 'required']);

        $bg = new Budget();
        $bg->IDBudjet = time(); // ID unique basé sur le temps (style WinDev)
        $bg->fill($request->all());
        $bg->Creer_le = now();
        $bg->save();

        return back()->with('success', 'Budget créé avec succès !');
    }

    // === FONCTION CLÉ : SÉLECTIONNER UN BUDGET ===
    public function selectionner($id)
    {
        $budget = Budget::find($id);
        
        if($budget) {
            // On stocke l'ID et le Nom dans la SESSION de l'utilisateur
            session([
                'budget_id' => $budget->IDBudjet,
                'budget_nom' => $budget->designation,
                'exercice_actuel' => $budget->EXERCICE
            ]);
            return back()->with('success', "Le budget [{$budget->designation}] est maintenant ACTIF.");
        }

        return back()->with('error', 'Budget introuvable.');
    }
}