<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\StkProduit;

class ProduitsCrud extends Component
{
    use WithPagination;

    public $LibProd, $Reference, $QteReappro, $QteMini, $Description, $product_id;
    public $search = "";
    public $updateMode = false;

    protected $rules = [
        'LibProd' => 'required|string|max:40',
        'Reference' => 'required|string|max:20',
        'QteReappro' => 'nullable|integer',
        'QteMini' => 'nullable|integer',
        'Description' => 'nullable|string',
    ];

    public function render()
    {
        $produits = StkProduit::where('LibProd', 'LIKE', "%{$this->search}%")
            ->orWhere('Reference', 'LIKE', "%{$this->search}%")
            ->orderBy('id_produit', 'DESC')
            ->paginate(10);

        return view('livewire.produits-crud', compact('produits'));
    }

    public function resetFields()
    {
        $this->LibProd = '';
        $this->Reference = '';
        $this->QteReappro = '';
        $this->QteMini = '';
        $this->Description = '';
        $this->product_id = null;
        $this->updateMode = false;
    }

    public function store()
    {
        $this->validate();

        StkProduit::create([
            'LibProd' => $this->LibProd,
            'Reference' => $this->Reference,
            'QteReappro' => $this->QteReappro ?? 0,
            'QteMini' => $this->QteMini ?? 0,
            'Description' => $this->Description,
        ]);

        $this->resetFields();
    }

    public function edit($id)
    {
        $produit = StkProduit::findOrFail($id);

        $this->product_id = $id;
        $this->LibProd = $produit->LibProd;
        $this->Reference = $produit->Reference;
        $this->QteReappro = $produit->QteReappro;
        $this->QteMini = $produit->QteMini;
        $this->Description = $produit->Description;

        $this->updateMode = true;
    }

    public function update()
    {
        $this->validate();

        $produit = StkProduit::find($this->product_id);

        $produit->update([
            'LibProd' => $this->LibProd,
            'Reference' => $this->Reference,
            'QteReappro' => $this->QteReappro,
            'QteMini' => $this->QteMini,
            'Description' => $this->Description,
        ]);

        $this->resetFields();
    }

    public function delete($id)
    {
        StkProduit::destroy($id);
    }
}
