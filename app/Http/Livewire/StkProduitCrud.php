<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\StkProduit;

class StkProduitCrud extends Component
{
    public $LibProd, $Reference, $QteReappro, $QteMini, $Description;

    public function render()
    {
        // ENVOYER LA VARIABLE A LA VUE
        $produits = StkProduit::orderBy('id_produit', 'DESC')->get();

        return view('livewire.stk-produit-crud', [
            'produits' => $produits,
        ]);
    }

    public function save()
    {
        $this->validate([
            'LibProd' => 'required',
            'Reference' => 'required',
        ]);

        StkProduit::create([
            'LibProd' => $this->LibProd,
            'Reference' => $this->Reference,
            'QteReappro' => $this->QteReappro ?? 0,
            'QteMini' => $this->QteMini ?? 0,
            'Description' => $this->Description,
        ]);

        $this->reset();
    }
}
