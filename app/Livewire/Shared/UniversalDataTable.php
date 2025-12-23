<?php

namespace App\Livewire\Shared;

use Livewire\Component;
use Livewire\WithPagination;

class UniversalDataTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // CONFIGURATION
    public $model;
    public $columns = [];
    public $primaryKey = 'id';
    
    // NOUVEAU : Configuration du formulaire
    // Format: ['nom_colonne' => ['label' => 'Nom', 'type' => 'text', 'required' => true]]
    public $fields = []; 

    public $canCreate = false;
    public $canEdit = false;
    public $canDelete = false;

    // ETAT
    public $search = '';
    public $sortCol = '';
    public $sortAsc = false;
    public $perPage = 10;

    // GESTION DU FORMULAIRE & MODAL
    public $form = [];          // Stocke les données saisies
    public $currentId = null;   // ID en cours d'édition (null si création)
    public $isEditMode = false;
    public $modalTitle = '';

   public function mount()
{
    // 1. Initialiser le tri par défaut
    if (empty($this->sortCol)) {
        $this->sortCol = $this->primaryKey;
    }

    // 2. CORRECTION : Remettre ceci pour que la recherche fonctionne !
    if (empty($this->searchable)) {
        $this->searchable = array_keys($this->columns);
    }
    
    // 3. Initialiser le formulaire
    $this->resetForm();
}

    public function resetForm()
    {
        $this->form = [];
        $this->currentId = null;
        $this->isEditMode = false;
        
        // On pré-remplit les clés du tableau pour éviter les erreurs "undefined index"
        foreach ($this->fields as $key => $config) {
            $this->form[$key] = '';
        }
    }

    // OUVRIR POUR CREATION
    public function create()
    {
        $this->resetForm();
        $this->modalTitle = 'Nouvel Enregistrement';
        $this->dispatch('open-modal'); // Événement JS pour ouvrir Bootstrap Modal
    }

    // OUVRIR POUR EDITION
  public function edit($id)
{
    $this->resetForm();
    
    // 1. On cherche l'enregistrement avec la clé primaire
    $record = $this->model::where($this->primaryKey, $id)->first();

    if ($record) {
        $this->currentId = $record->{$this->primaryKey};
        $this->isEditMode = true;
        $this->modalTitle = 'Modifier Enregistrement';

        // 2. Conversion des données brutes en tableau
        $dbData = $record->toArray();

        // 3. MAPPING INTELLIGENT (C'est ici que ça corrige votre problème)
        // On parcourt les champs que VOUS avez définis dans web.php
        foreach ($this->fields as $configKey => $config) {
            
            // On cherche cette clé dans les données de la BD (insensible à la casse)
            // Ex: On cherche 'Designation' ou 'designation' ou 'DESIGNATION'
            foreach ($dbData as $dbKey => $dbValue) {
                if (strtolower($dbKey) === strtolower($configKey)) {
                    // On force l'utilisation de la clé de config ($configKey)
                    // pour que le wire:model="form.Designation" retrouve ses petits
                    $this->form[$configKey] = $dbValue;
                    break; 
                }
            }
        }

        // 4. On ouvre le modal
        $this->dispatch('open-modal');
    }
}

    // SAUVEGARDER (CREATE OU UPDATE)
    public function save()
    {
        // 1. Construire les règles de validation dynamiquement
        $rules = [];
        foreach ($this->fields as $field => $config) {
            if (isset($config['required']) && $config['required']) {
                $rules['form.' . $field] = 'required';
            }
        }
        $this->validate($rules);

        // 2. Sauvegarde
        if ($this->isEditMode) {
            // Update
            $record = $this->model::where($this->primaryKey, $this->currentId)->first();
            $record->update($this->form);
            $message = 'Modifié avec succès';
        } else {
            // Create
            $this->model::create($this->form);
            $message = 'Créé avec succès';
        }

        // 3. Fermer et Notifier
        $this->dispatch('close-modal');
        $this->dispatch('notify', message: $message); // Si vous avez un système de notif
        $this->resetForm();
    }

    public function delete($id)
    {
        if (!$this->canDelete) return;
        $this->model::where($this->primaryKey, $id)->delete();
    }

    // ... (Méthodes de tri et render restent identiques)
    public function sortBy($column) { /* ... comme avant ... */ }
    public function updatedSearch() { $this->resetPage(); }

  public function render()
{
    // 1. Initialiser la requête
    $query = $this->model::query();

    // 2. Déterminer les colonnes de recherche (Sécurité)
    // Si $searchable est vide, on prend les clés de $columns
    $fieldsToSearch = !empty($this->searchable) ? $this->searchable : array_keys($this->columns);

    // 3. Appliquer la recherche si le champ n'est pas vide
    if (!empty($this->search)) {
        // On utilise "use" pour passer la variable $fieldsToSearch dans la fonction anonyme
        $query->where(function($q) use ($fieldsToSearch) {
            foreach ($fieldsToSearch as $field) {
                // On force la conversion en string pour éviter les bugs sur les nombres
                $q->orWhere($field, 'like', '%' . $this->search . '%');
            }
        });
    }

    // 4. Tri
    if (array_key_exists($this->sortCol, $this->columns) || $this->sortCol === $this->primaryKey) {
        $query->orderBy($this->sortCol, $this->sortAsc ? 'asc' : 'desc');
    }

    return view('livewire.shared.universal-data-table', [
        'rows' => $query->paginate($this->perPage)
    ]);
}
}