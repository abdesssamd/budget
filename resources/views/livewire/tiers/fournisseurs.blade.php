<?php

use Livewire\Volt\Component;
use App\Models\StkFournisseur;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads; // Pour gérer les fichiers
use Illuminate\Support\Facades\Storage; // Pour l'affichage des liens

new 
#[Layout('layouts.app')] 
class extends Component {
    use WithPagination;
    use WithFileUploads; // Activation de l'upload

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    
    // Variable temporaire pour le fichier uploadé
    public $scanFile;

    public array $form = [
        'NumFournisseur' => '',
        'Civilite' => 'M.',
        'Nom' => '',
        'Prénom' => '',
        'Societe' => '',
        'Adresse' => '',
        'CodePostal' => '',
        'Ville' => '',
        'Pays' => 'Algérie',
        'Telephone' => '',
        'Mobile' => '',
        'Fax' => '',
        'EMail' => '',
        'num_registre_commerce' => '',
        'num_carte_fiscale' => '', 
        'NIS' => '',
        'Observations' => '',
        'scan_path' => '', // Nouveau champ pour le chemin du fichier
    ];

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset('form', 'scanFile'); // Reset du fichier aussi
        $this->form['Pays'] = 'Algérie'; 

        if ($id) {
            $this->editMode = true;
            $this->form = StkFournisseur::findOrFail($id)->toArray();
        } else {
            $this->editMode = false;
        }
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'form.Nom' => 'required_without:form.Societe|nullable|string|max:40',
            'form.Societe' => 'required_without:form.Nom|nullable|string|max:40',
            'form.EMail' => 'nullable|email',
            'form.num_registre_commerce' => 'nullable|string|max:20',
            'form.num_carte_fiscale' => 'nullable|string|max:20',
            'scanFile' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240', // Max 10MB
        ]);

        // Gestion de l'upload
        if ($this->scanFile) {
            // Stockage dans 'public/fournisseurs_docs'
            $path = $this->scanFile->store('fournisseurs_docs', 'public');
            $this->form['scan_path'] = $path;
        }

        if ($this->editMode) {
            StkFournisseur::where('NumFournisseur', $this->form['NumFournisseur'])->update($this->form);
        } else {
            $data = $this->form;
            unset($data['NumFournisseur']);
            StkFournisseur::create($data);
        }

        $this->showModal = false;
        session()->flash('success', __('crud.success_op'));
    }

    public function delete($id)
    {
        try {
            $fournisseur = StkFournisseur::findOrFail($id);
            // Suppression du fichier associé si existe
            if ($fournisseur->scan_path) {
                Storage::disk('public')->delete($fournisseur->scan_path);
            }
            $fournisseur->delete();
            session()->flash('success', __('crud.item_deleted'));
        } catch (\Exception $e) {
            session()->flash('error', __('crud.error_op'));
        }
    }

    public function with()
    {
        return [
            'fournisseurs' => StkFournisseur::where('Nom', 'like', "%{$this->search}%")
                ->orWhere('Prénom', 'like', "%{$this->search}%")
                ->orWhere('Societe', 'like', "%{$this->search}%")
                ->orWhere('num_registre_commerce', 'like', "%{$this->search}%")
                ->orderByDesc('NumFournisseur')
                ->paginate(10)
        ];
    }
}; ?>

<div>
    {{-- Helpers RTL --}}
    @php
        $isRtl = app()->getLocale() == 'ar';
        $alignText = $isRtl ? 'text-right' : 'text-left';
        $margin = $isRtl ? 'ml-2' : 'mr-2';
        $closeBtnStyle = $isRtl ? 'margin: -1rem auto -1rem -1rem; float:left;' : '';
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-dark m-0 font-weight-bold">{{ __('menu.suppliers') }}</h4>
        <button wire:click="openModal()" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus-circle {{ $margin }}"></i>{{ __('crud.new') }}
        </button>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check {{ $margin }}"></i> {{ session('success') }} <button class="close" data-dismiss="alert" style="{{ $closeBtnStyle }}">&times;</button></div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ __('menu.suppliers') }}</h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 200px;">
                    <input type="text" wire:model.live="search" class="form-control float-right" placeholder="{{ __('crud.search') }}">
                    <div class="input-group-append"><button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button></div>
                </div>
            </div>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-hover text-nowrap">
                <thead class="bg-light">
                    <tr>
                        <th>{{ __('crud.code') }}</th>
                        <th>Société / Nom</th>
                        <th>Coordonnées</th>
                        <th>Matricules (RC/NIF)</th>
                        <th class="text-right">{{ __('crud.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fournisseurs as $fr)
                    <tr>
                        <td><span class="badge badge-secondary">{{ $fr->NumFournisseur }}</span></td>
                        <td>
                            @if($fr->Societe)
                                <div class="font-weight-bold text-primary"><i class="fas fa-building {{ $margin }}"></i> {{ $fr->Societe }}</div>
                            @endif
                            @if($fr->Nom)
                                <div class="small text-dark">{{ $fr->Civilite }} {{ $fr->Nom }} {{ $fr->Prénom }}</div>
                            @endif
                        </td>
                        <td class="small">
                            @if($fr->Mobile) <div><i class="fas fa-mobile-alt {{ $margin }}"></i> {{ $fr->Mobile }}</div> @endif
                            @if($fr->EMail) <div><i class="fas fa-envelope {{ $margin }}"></i> {{ $fr->EMail }}</div> @endif
                            @if($fr->Ville) <div><i class="fas fa-map-marker-alt {{ $margin }}"></i> {{ $fr->Ville }}</div> @endif
                        </td>
                        <td class="small text-muted">
                            @if($fr->num_registre_commerce) <div><strong>RC:</strong> {{ $fr->num_registre_commerce }}</div> @endif
                            @if($fr->num_carte_fiscale) <div><strong>NIF:</strong> {{ $fr->num_carte_fiscale }}</div> @endif
                            
                            {{-- Bouton pour voir le scan si disponible --}}
                            @if($fr->scan_path)
                                <div class="mt-1">
                                    <a href="{{ Storage::url($fr->scan_path) }}" target="_blank" class="badge badge-info p-1">
                                        <i class="fas fa-paperclip"></i> Voir Dossier
                                    </a>
                                </div>
                            @endif
                        </td>
                        <td class="text-right">
                            <button wire:click="openModal({{ $fr->NumFournisseur }})" class="btn btn-xs btn-outline-primary {{ $margin }}"><i class="fas fa-edit"></i></button>
                            <button wire:click="delete({{ $fr->NumFournisseur }})" class="btn btn-xs btn-outline-danger" onclick="confirm('{{ __('crud.confirm_delete') }}') || event.stopImmediatePropagation()"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-5 text-muted">{{ __('crud.no_results') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix"><div class="float-right">{{ $fournisseurs->links() }}</div></div>
    </div>

    @if($showModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">{{ $editMode ? __('crud.edit') : __('crud.new') }}</h5>
                    <button type="button" class="close text-white" wire:click="$set('showModal', false)" style="{{ $closeBtnStyle }}">&times;</button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label class="{{ $alignText }} d-block">Société / Raison Sociale</label>
                                <input type="text" wire:model="form.Societe" class="form-control font-weight-bold" placeholder="EURL...">
                            </div>
                             <div class="col-md-2">
                                <label class="{{ $alignText }} d-block">Civilité</label>
                                <select wire:model="form.Civilite" class="form-control">
                                    <option>M.</option><option>Mme</option><option>Mlle</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="{{ $alignText }} d-block">Nom & Prénom</label>
                                <div class="d-flex">
                                    <input type="text" wire:model="form.Nom" class="form-control mr-1" placeholder="Nom">
                                    <input type="text" wire:model="form.Prénom" class="form-control" placeholder="Prénom">
                                </div>
                            </div>
                        </div>

                        <h6 class="text-primary mt-3 border-bottom pb-1"><i class="fas fa-file-contract {{ $margin }}"></i> Informations Fiscales & Dossier</h6>
                        <div class="row bg-light p-2 rounded">
                            <div class="col-md-4">
                                <label class="{{ $alignText }} d-block small">N° Registre Commerce (RC)</label>
                                <input type="text" wire:model="form.num_registre_commerce" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="{{ $alignText }} d-block small">NIF (Carte Fiscale)</label>
                                <input type="text" wire:model="form.num_carte_fiscale" class="form-control form-control-sm">
                            </div>
                            
                            <!-- Zone Upload Scan -->
                            <div class="col-md-4">
                                <label class="{{ $alignText }} d-block small font-weight-bold text-success">
                                    <i class="fas fa-camera"></i> Scan Dossier (RC/NIF)
                                </label>
                                <input type="file" wire:model="scanFile" class="form-control-file small" accept="image/*,application/pdf" capture="environment">
                                <div wire:loading wire:target="scanFile" class="text-info small"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>
                                @if(!empty($form['scan_path']))
                                    <div class="small text-success mt-1"><i class="fas fa-check"></i> Fichier existant</div>
                                @endif
                                @error('scanFile') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <h6 class="text-primary mt-3 border-bottom pb-1"><i class="fas fa-address-card {{ $margin }}"></i> Coordonnées</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="{{ $alignText }} d-block">Adresse</label>
                                    <textarea wire:model="form.Adresse" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-6"><input type="text" wire:model="form.CodePostal" class="form-control" placeholder="CP"></div>
                                    <div class="col-6"><input type="text" wire:model="form.Ville" class="form-control" placeholder="Ville"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group"><label class="{{ $alignText }} d-block">Mobile</label><input type="text" wire:model="form.Mobile" class="form-control"></div>
                                <div class="form-group"><label class="{{ $alignText }} d-block">Tél Fixe</label><input type="text" wire:model="form.Telephone" class="form-control"></div>
                                <div class="form-group"><label class="{{ $alignText }} d-block">Email</label><input type="email" wire:model="form.EMail" class="form-control"></div>
                            </div>
                        </div>

                        <div class="form-group mt-2">
                            <label class="{{ $alignText }} d-block">Observations</label>
                            <textarea wire:model="form.Observations" class="form-control" rows="1"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">{{ __('crud.cancel') }}</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="scanFile">{{ __('crud.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>