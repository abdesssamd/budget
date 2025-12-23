<?php

use Livewire\Volt\Component;
use App\Models\StkBonCommande;
use App\Models\StkBonCommandePj;
use App\Models\StkFournisseur;
use App\Models\BdgOperationBudg; // Pour lister les engagements
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

new 
#[Layout('layouts.app')] 
class extends Component {
    use WithPagination;
    use WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    public $showModal = false;
    public $showSupplierModal = false;
    public $editMode = false;
    
    public $fournisseurs = [];
    public $engagementsDisponibles = []; // Liste des engagements non soldés

    public $pjFiles = [];
    public $currentPjs = [];

    public array $form = [
        'IDBON' => '',
        'Num_bon' => '',
        'date' => '',
        'designation' => '',
        'NumFournisseur' => '',
        'prixtotal' => 0,
        'valider' => 0,
        'Observations' => '',
        'IDOperation_Budg' => '', // <--- Champ de liaison
    ];

    public array $newSupplier = ['Nom' => '', 'Societe' => '', 'Telephone' => ''];

    public function mount()
    {
        $this->refreshFournisseurs();
        $this->form['date'] = date('Y-m-d');
        // Charger les engagements récents (Type 3) pour la liste déroulante
        $this->engagementsDisponibles = BdgOperationBudg::where('Type_operation', 3)
            ->orderByDesc('Creer_le')
            ->limit(50) // Limite pour la performance, idéalement ajouter une recherche ajax
            ->get();
    }

    // QUAND ON CHOISIT UN ENGAGEMENT -> ON REMPLIT LES CHAMPS
    public function updatedFormIDOperation_Budg($value)
    {
        if ($value) {
            $eng = BdgOperationBudg::find($value);
            if ($eng) {
                $this->form['designation'] = $eng->designation;
                $this->form['prixtotal'] = $eng->Mont_operation;
            }
        }
    }

    public function refreshFournisseurs() {
        $this->fournisseurs = StkFournisseur::orderBy('Nom')->get();
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset('form', 'pjFiles', 'currentPjs');
        
        if ($id) {
            $this->editMode = true;
            $bc = StkBonCommande::with('pjs')->findOrFail($id);
            $this->form = $bc->toArray();
            $this->currentPjs = $bc->pjs;
        } else {
            $this->editMode = false;
            $this->form['date'] = date('Y-m-d');
            $this->form['valider'] = 0;
            $this->form['Num_bon'] = 'AUTO';
        }
        $this->showModal = true;
    }

    public function openSupplierModal() {
        $this->newSupplier = ['Nom' => '', 'Societe' => '', 'Telephone' => ''];
        $this->showSupplierModal = true;
    }

    public function saveSupplier() {
        $this->validate([
            'newSupplier.Nom' => 'required_without:newSupplier.Societe',
            'newSupplier.Societe' => 'required_without:newSupplier.Nom',
        ]);
        $fr = StkFournisseur::create($this->newSupplier);
        $this->refreshFournisseurs();
        $this->form['NumFournisseur'] = $fr->NumFournisseur;
        $this->showSupplierModal = false;
    }

    public function save()
    {
        $rules = [
            'form.date' => 'required|date',
            'form.NumFournisseur' => 'required|exists:stk_fournisseur,NumFournisseur',
            'form.designation' => 'required|string|max:50',
            'form.prixtotal' => 'required|numeric|min:0',
            'form.IDOperation_Budg' => 'nullable|exists:bdg_operation_budg,IDOperation_Budg', // Validation lien
            'pjFiles.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];

        if ($this->editMode) $rules['form.Num_bon'] = 'required|string';

        $this->validate($rules);

        $data = $this->form;
        $data['IDExercice'] = date('Y', strtotime($this->form['date']));

        if ($this->editMode) {
            StkBonCommande::where('IDBON', $this->form['IDBON'])->update($data);
            $bcId = $this->form['IDBON'];
        } else {
            unset($data['IDBON']);
            unset($data['Num_bon']);
            $data['SaisiPar'] = Auth::user()->name ?? 'System';
            $data['SaisiLe'] = now();
            $bc = StkBonCommande::create($data);
            $bcId = $bc->IDBON;
        }

        if (!empty($this->pjFiles)) {
            foreach ($this->pjFiles as $file) {
                $path = $file->store('pieces_jointes_bc', 'public');
                StkBonCommandePj::create([
                    'IDBON' => $bcId,
                    'chemin_fichier' => $path,
                    'nom_fichier' => $file->getClientOriginalName(),
                    'created_at' => now()
                ]);
            }
        }

        $this->showModal = false;
        session()->flash('success', __('crud.success_op'));
    }

    public function delete($id)
    {
        $bc = StkBonCommande::findOrFail($id);
        foreach($bc->pjs as $pj) {
            Storage::disk('public')->delete($pj->chemin_fichier);
            $pj->delete();
        }
        $bc->delete();
        session()->flash('success', __('crud.item_deleted'));
    }
    
    public function deletePj($pjId)
    {
        $pj = StkBonCommandePj::findOrFail($pjId);
        Storage::disk('public')->delete($pj->chemin_fichier);
        $pj->delete();
        $this->currentPjs = $this->currentPjs->reject(fn($i) => $i->ID_PJ === $pjId);
    }

    public function toggleValidation($id)
    {
        $bc = StkBonCommande::findOrFail($id);
        $bc->valider = !$bc->valider;
        $bc->save();
    }

    public function with()
    {
        return [
            'bons' => StkBonCommande::with(['fournisseur', 'pjs', 'engagement'])
                ->orderByDesc('date')
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
        $providerLabel = $isRtl ? 'المورد / المستفيد' : 'Fournisseur / Bénéficiaire';
    @endphp

    @section('plugins.Select2', true)

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-dark m-0 font-weight-bold">
            <i class="fas fa-shopping-cart text-warning {{ $margin }}"></i>{{ __('operations.purchase_order') }}
        </h4>
        <button wire:click="openModal" class="btn btn-warning shadow-sm text-dark font-weight-bold">
            <i class="fas fa-plus-circle {{ $margin }}"></i>{{ __('operations.new_purchase_order') }}
        </button>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="icon fas fa-check {{ $margin }}"></i> {{ session('success') }} 
            <button class="close" data-dismiss="alert" style="{{ $closeBtnStyle }}">&times;</button>
        </div>
    @endif

    <div class="card card-outline card-warning">
        <div class="card-header">
            <h3 class="card-title">{{ __('operations.po_history') }}</h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover">
                <thead class="bg-light">
                    <tr>
                        <th class="{{ $alignText }}">{{ __('operations.po_num') }}</th>
                        <th class="{{ $alignText }}">{{ __('operations.po_date') }}</th>
                        <th class="{{ $alignText }}">{{ __('operations.provider') }}</th>
                        <th class="{{ $alignText }}">{{ __('crud.designation') }}</th>
                        <th class="text-right">{{ __('operations.total_amount') }}</th>
                        <th class="text-center">Lien Engagement</th> <!-- Colonne Lien -->
                        <th class="text-center">{{ __('operations.order_status') }}</th>
                        <th class="text-right">{{ __('crud.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bons as $bc)
                    <tr>
                        <td class="font-weight-bold text-dark">{{ $bc->Num_bon }}</td>
                        <td>{{ \Carbon\Carbon::parse($bc->date)->format('d/m/Y') }}</td>
                        <td class="text-primary font-weight-bold">
                            {{ $bc->fournisseur->Nom ?? ($bc->fournisseur->Societe ?? '?') }}
                        </td>
                        <td>{{ $bc->designation }}</td>
                        <td class="text-right font-weight-bold" dir="ltr">
                            {{ number_format($bc->prixtotal, 2, ',', ' ') }} DA
                        </td>
                        
                        {{-- Affichage du lien vers l'engagement --}}
                        <td class="text-center">
                            @if($bc->engagement)
                                <span class="badge badge-light border" title="Engagement N° {{ $bc->engagement->Num_operation }}">
                                    <i class="fas fa-link text-info"></i> Eng. {{ $bc->engagement->Num_operation }}
                                </span>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>

                        <td class="text-center">
                            <button wire:click="toggleValidation({{ $bc->IDBON }})" 
                                    class="btn btn-xs {{ $bc->valider ? 'btn-success' : 'btn-outline-secondary' }} shadow-sm">
                                @if($bc->valider)
                                    <i class="fas fa-check-circle"></i> {{ __('operations.status_validated') }}
                                @else
                                    <i class="fas fa-hourglass-half"></i> {{ __('operations.status_pending') }}
                                @endif
                            </button>
                        </td>
                        <td class="text-right">
                            <button class="btn btn-xs btn-default border mr-1"><i class="fas fa-print"></i></button>
                            <button wire:click="openModal({{ $bc->IDBON }})" class="btn btn-xs btn-outline-primary mr-1" @if($bc->valider) disabled @endif><i class="fas fa-edit"></i></button>
                            <button wire:click="delete({{ $bc->IDBON }})" class="btn btn-xs btn-outline-danger" onclick="confirm('{{ __('crud.confirm_delete') }}') || event.stopImmediatePropagation()" @if($bc->valider) disabled @endif><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-5 text-muted">{{ __('crud.no_results') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix"><div class="float-right">{{ $bons->links() }}</div></div>
    </div>

    @if($showModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5); z-index: 1050;" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title font-weight-bold text-dark">{{ __('operations.new_purchase_order') }}</h5>
                    <button type="button" class="close" wire:click="$set('showModal', false)" style="{{ $closeBtnStyle }}">&times;</button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        
                        <!-- CHAMP DE LIAISON ENGAGEMENT -->
                        <div class="form-group bg-light p-2 rounded border">
                            <label class="text-primary font-weight-bold {{ $alignText }} d-block">
                                <i class="fas fa-link {{ $margin }}"></i> Lier à un Engagement existant
                            </label>
                            <select wire:model.live="form.IDOperation_Budg" class="form-control">
                                <option value="">-- Sélectionner un engagement (facultatif) --</option>
                                @foreach($engagementsDisponibles as $eng)
                                    <option value="{{ $eng->IDOperation_Budg }}">
                                        N° {{ $eng->Num_operation }} | {{ number_format($eng->Mont_operation, 0, ',', ' ') }} DA | {{ Str::limit($eng->designation, 40) }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">Sélectionner un engagement remplira automatiquement l'objet et le montant.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="{{ $alignText }} d-block">{{ __('operations.po_num') }}</label>
                                @if($editMode)
                                    <input type="text" wire:model="form.Num_bon" class="form-control font-weight-bold">
                                @else
                                    <input type="text" value="Génération Automatique" class="form-control bg-light" disabled>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="{{ $alignText }} d-block">{{ __('operations.po_date') }}</label>
                                <input type="date" wire:model="form.date" class="form-control">
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <label class="{{ $alignText }} d-block">{{ __('operations.provider') }}</label>
                            <div class="input-group">
                                <select wire:model="form.NumFournisseur" class="form-control">
                                    <option value="">{{ __('crud.select_option') }}</option>
                                    @foreach($fournisseurs as $fr) 
                                        <option value="{{ $fr->NumFournisseur }}">{{ $fr->Nom }} {{ $fr->Prénom }} {{ $fr->Societe ? '('.$fr->Societe.')' : '' }}</option> 
                                    @endforeach
                                </select>
                                <div class="input-group-append">
                                    <button type="button" wire:click="openSupplierModal" class="btn btn-success" title="Nouveau Fournisseur"><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                            @error('form.NumFournisseur') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="{{ $alignText }} d-block">{{ __('crud.designation') }} (Objet)</label>
                            <input type="text" wire:model="form.designation" class="form-control" placeholder="Achat de...">
                            @error('form.designation') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="{{ $alignText }} d-block">{{ __('visa.observations') }}</label>
                            <textarea wire:model="form.Observations" class="form-control" rows="2"></textarea>
                        </div>

                        <!-- UPLOAD PJ -->
                        <div class="form-group bg-light p-2 rounded border-dashed">
                            <label class="{{ $alignText }} d-block mb-1 font-weight-bold"><i class="fas fa-paperclip {{ $margin }}"></i> Pièces Jointes</label>
                            <input type="file" wire:model="pjFiles" class="form-control-file" multiple accept="image/*,application/pdf">
                            
                            @if(!empty($currentPjs))
                                <div class="mt-2">
                                    @foreach($currentPjs as $pj)
                                        <div class="badge badge-light border p-1 mr-1">
                                            <a href="{{ Storage::url($pj->chemin_fichier) }}" target="_blank">{{ $pj->nom_fichier }}</a>
                                            <i class="fas fa-times text-danger cursor-pointer ml-1" wire:click="deletePj({{ $pj->ID_PJ }})"></i>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-md-4 offset-md-8">
                                <label class="{{ $alignText }} d-block text-warning font-weight-bold">{{ __('operations.total_amount') }}</label>
                                <input type="number" step="0.01" wire:model="form.prixtotal" class="form-control form-control-lg border-warning text-right font-weight-bold" dir="ltr">
                                @error('form.prixtotal') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">{{ __('crud.cancel') }}</button>
                        <button type="submit" class="btn btn-warning font-weight-bold">{{ __('crud.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    <!-- ... (Modal Fournisseur reste inchangée) ... -->
    @if($showSupplierModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.7); z-index: 1060;" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-success">
                <div class="modal-header bg-success text-white py-2">
                    <h6 class="modal-title"><i class="fas fa-user-plus mr-2"></i>Nouveau Fournisseur</h6>
                    <button type="button" class="close text-white" wire:click="$set('showSupplierModal', false)">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nom / Raison Sociale</label>
                        <input type="text" wire:model="newSupplier.Societe" class="form-control" placeholder="EURL...">
                    </div>
                    <div class="form-group">
                        <label>Nom du contact</label>
                        <input type="text" wire:model="newSupplier.Nom" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Téléphone</label>
                        <input type="text" wire:model="newSupplier.Telephone" class="form-control">
                    </div>
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-secondary" wire:click="$set('showSupplierModal', false)">Annuler</button>
                    <button type="button" class="btn btn-sm btn-success" wire:click="saveSupplier">Ajouter</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>