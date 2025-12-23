<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\StkFournisseur;

class FournisseursCrud extends Component
{
    use WithPagination;

    public $Nom, $Societe, $Adresse, $Telephone, $Mobile, $Email, $Ville;
    public $num_carte_fiscale, $num_registre_commerce, $NIS, $Observations;
    public $fournisseur_id, $search = "";
    public $updateMode = false;

    protected $rules = [
        'Nom' => 'required|string|max:40',
        'Societe' => 'nullable|string|max:40',
        'Telephone' => 'nullable|string|max:20',
        'Mobile' => 'nullable|string|max:20',
        'Email' => 'nullable|email|max:40',
        'Ville' => 'nullable|string|max:40',
        'num_carte_fiscale' => 'required|string|max:20',
        'num_registre_commerce' => 'required|string|max:20',
    ];

    public function render()
    {
        $fournisseurs = StkFournisseur::where('Nom', 'LIKE', "%{$this->search}%")
            ->orWhere('Societe', 'LIKE', "%{$this->search}%")
            ->orderBy('NumFournisseur', 'DESC')
            ->paginate(10);

        return view('livewire.fournisseurs-crud', compact('fournisseurs'));
    }

    public function resetFields()
    {
        $this->Nom = $this->Societe = $this->Adresse = $this->Telephone = $this->Mobile =
        $this->Email = $this->Ville = $this->num_carte_fiscale = $this->num_registre_commerce = 
        $this->Observations = $this->NIS = "";

        $this->fournisseur_id = null;
        $this->updateMode = false;
    }

    public function store()
    {
        $this->validate();

        StkFournisseur::create([
            'Nom' => $this->Nom,
            'Societe' => $this->Societe,
            'Adresse' => $this->Adresse,
            'Telephone' => $this->Telephone,
            'Mobile' => $this->Mobile,
            'Email' => $this->Email,
            'Ville' => $this->Ville,
            'num_carte_fiscale' => $this->num_carte_fiscale,
            'num_registre_commerce' => $this->num_registre_commerce,
            'Observations' => $this->Observations,
            'NIS' => $this->NIS,
        ]);

        $this->resetFields();
    }

    public function edit($id)
    {
        $data = StkFournisseur::findOrFail($id);

        $this->fournisseur_id = $id;
        $this->Nom = $data->Nom;
        $this->Societe = $data->Societe;
        $this->Adresse = $data->Adresse;
        $this->Telephone = $data->Telephone;
        $this->Mobile = $data->Mobile;
        $this->Email = $data->Email;
        $this->Ville = $data->Ville;
        $this->num_carte_fiscale = $data->num_carte_fiscale;
        $this->num_registre_commerce = $data->num_registre_commerce;
        $this->Observations = $data->Observations;

        $this->updateMode = true;
    }

    public function update()
    {
        $this->validate();

        StkFournisseur::find($this->fournisseur_id)->update([
            'Nom' => $this->Nom,
            'Societe' => $this->Societe,
            'Adresse' => $this->Adresse,
            'Telephone' => $this->Telephone,
            'Mobile' => $this->Mobile,
            'Email' => $this->Email,
            'Ville' => $this->Ville,
            'num_carte_fiscale' => $this->num_carte_fiscale,
            'num_registre_commerce' => $this->num_registre_commerce,
            'Observations' => $this->Observations,
            'NIS' => $this->NIS,
        ]);

        $this->resetFields();
    }

    public function delete($id)
    {
        StkFournisseur::destroy($id);
    }
}
