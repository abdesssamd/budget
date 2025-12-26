<?php

use Livewire\Volt\Component;
use App\Models\BdgBudget;
use App\Models\BdgSection;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

new 
#[Layout('layouts.app')] 
class extends Component {
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $showModal = false;
    
    // Listes
    public $budgets = [];
    public $sections = [];

    public array $form = [
        'IDBudjet' => '',
        'IDSection' => '',
        'type_budget' => 'supplementaire',
        'designation' => 'Budget Supplémentaire',
        'Montant_BS' => 0,
        'EXERCICE' => '',
        'numero_decision' => '',
        'date_decision' => '',
        'source_financement' => '',
        'observations' => '',
    ];

    // Historique des augmentations
    public $historique = [];

    public function mount()
    {
        $this->budgets = BdgBudget::where('Archive', 0)->orderByDesc('EXERCICE')->get();
        $this->sections = BdgSection::orderBy('Num_section')->get();
        $this->form['EXERCICE'] = date('Y');
        $this->form['date_decision'] = date('Y-m-d');
        
        $this->chargerHistorique();
    }

    public function chargerHistorique()
    {
        $exercice = $this->form['EXERCICE'] ?: date('Y');
        
        // Récupérer l'historique des augmentations depuis la table bdg_budget_augmentation
        $this->historique = DB::table('bdg_budget_augmentation')
            ->where('EXERCICE', $exercice)
            ->orderByDesc('Date_augmentation')
            ->get();
    }

    public function updatedFormIDBudjet($value)
    {
        $b = $this->budgets->find($value);
        if($b) {
            $this->form['EXERCICE'] = $b->EXERCICE;
            $this->chargerHistorique();
        }
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->form['Montant_BS'] = 0;
        $this->form['type_budget'] = 'supplementaire';
        $this->form['designation'] = 'Budget Supplémentaire';
        $this->form['date_decision'] = date('Y-m-d');
        $this->form['numero_decision'] = '';
        $this->form['source_financement'] = '';
        $this->form['observations'] = '';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'form.IDBudjet' => 'required',
            'form.IDSection' => 'required',
            'form.Montant_BS' => 'required|numeric|min:0.01',
            'form.designation' => 'required|string',
            'form.type_budget' => 'required|in:supplementaire,rectificatif,virement,report',
            'form.numero_decision' => 'required|string|max:50',
            'form.date_decision' => 'required|date',
            'form.source_financement' => 'required|string|max:255',
        ], [
            'form.numero_decision.required' => 'Le numéro de décision est obligatoire',
            'form.date_decision.required' => 'La date de décision est obligatoire',
            'form.source_financement.required' => 'La source de financement est obligatoire',
        ]);

        DB::beginTransaction();
        try {
            // 1. Enregistrer l'historique de l'augmentation
            $idAugmentation = DB::table('bdg_budget_augmentation')->insertGetId([
                'IDBudjet' => $this->form['IDBudjet'],
                'IDSection' => $this->form['IDSection'],
                'Type_budget' => $this->form['type_budget'],
                'Numero_decision' => $this->form['numero_decision'],
                'Date_decision' => $this->form['date_decision'],
                'Source_financement' => $this->form['source_financement'],
                'Designation' => $this->form['designation'],
                'Montant_augmentation' => $this->form['Montant_BS'],
                'EXERCICE' => $this->form['EXERCICE'],
                'Observations' => $this->form['observations'],
                'Date_augmentation' => now(),
                'IDLogin' => auth()->id() ?? 0,
            ]);

            // 2. Augmenter le Montant_Restant du budget global (envelope actuelle)
            DB::table('bdg_budget')
                ->where('IDBudjet', $this->form['IDBudjet'])
                ->increment('Montant_Restant', $this->form['Montant_BS']);

            // 3. Mettre à jour aussi le montant total du budget
            DB::table('bdg_budget')
                ->where('IDBudjet', $this->form['IDBudjet'])
                ->increment('Montant_Global', $this->form['Montant_BS']);

            DB::commit();
            
            $this->showModal = false;
            $this->chargerHistorique();
            session()->flash('success', 'Budget supplémentaire de ' . number_format($this->form['Montant_BS'], 2, ',', ' ') . ' DA ajouté avec succès à l\'enveloppe budgétaire !');
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            // Récupérer l'augmentation
            $augmentation = DB::table('bdg_budget_augmentation')->where('IDAugmentation', $id)->first();
            
            if (!$augmentation) {
                throw new \Exception('Augmentation non trouvée');
            }

            // Vérifier si le montant a été réparti (logique à implémenter selon votre besoin)
            // Pour l'instant on permet la suppression directe

            // Diminuer le Montant_Restant du budget
            DB::table('bdg_budget')
                ->where('IDBudjet', $augmentation->IDBudjet)
                ->decrement('Montant_Restant', $augmentation->Montant_augmentation);

            // Diminuer aussi le montant total
            DB::table('bdg_budget')
                ->where('IDBudjet', $augmentation->IDBudjet)
                ->decrement('Montant_Global', $augmentation->Montant_augmentation);

            // Supprimer l'enregistrement
            DB::table('bdg_budget_augmentation')->where('IDAugmentation', $id)->delete();

            DB::commit();
            $this->chargerHistorique();
            session()->flash('success', 'Budget supplémentaire annulé');
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function with()
    {
        $exercice = $this->form['EXERCICE'] ?: date('Y');
        
        // Statistiques pour l'exercice
        $budgetPrimitif = BdgBudget::where('EXERCICE', $exercice)
            ->where('Archive', 0)
            ->sum('Montant_Primitif'); // Colonne à vérifier dans votre BD
        
        $totalAugmentations = DB::table('bdg_budget_augmentation')
            ->where('EXERCICE', $exercice)
            ->sum('Montant_augmentation');
        
        $budgetTotal = BdgBudget::where('EXERCICE', $exercice)
            ->where('Archive', 0)
            ->sum('Montant_Global');

        return [
            'stats' => [
                'budget_primitif' => $budgetPrimitif,
                'total_augmentations' => $totalAugmentations,
                'budget_total' => $budgetTotal,
            ]
        ];
    }
}; ?>

<div>
    {{-- Statistiques --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="info-box bg-gradient-primary">
                <span class="info-box-icon"><i class="fas fa-wallet"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Budget Primitif</span>
                    <span class="info-box-number">{{ number_format($stats['budget_primitif'], 2, ',', ' ') }} DA</span>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="info-box bg-gradient-success">
                <span class="info-box-icon"><i class="fas fa-plus-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Budgets Supplémentaires</span>
                    <span class="info-box-number">{{ number_format($stats['total_augmentations'], 2, ',', ' ') }} DA</span>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="info-box bg-gradient-warning">
                <span class="info-box-icon"><i class="fas fa-coins"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Enveloppe Totale</span>
                    <span class="info-box-number">{{ number_format($stats['budget_total'], 2, ',', ' ') }} DA</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Alerte d'information --}}
    <div class="alert alert-info">
        <i class="fas fa-info-circle mr-2"></i>
        <strong>Information :</strong> Les budgets supplémentaires augmentent l'enveloppe budgétaire globale. 
        Le montant sera disponible pour répartition dans la fenêtre "Répartition" normale.
    </div>

    {{-- Bouton et Entête --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-dark m-0 font-weight-bold">
            <i class="fas fa-plus-square text-success mr-2"></i>
            Incorporation Budget Supplémentaire
        </h4>
        <button wire:click="openModal()" class="btn btn-success shadow-sm">
            <i class="fas fa-plus-circle mr-2"></i>Augmenter l'Enveloppe
        </button>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="icon fas fa-check"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="icon fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- Tableau historique --}}
    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Historique des Augmentations Budgétaires</h3>
            <div class="card-tools">
                <span class="badge badge-success">{{ count($historique) }} augmentations</span>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>N° Décision</th>
                        <th>Section</th>
                        <th>Source</th>
                        <th class="text-right">Montant</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historique as $aug)
                    <tr>
                        <td class="small">
                            {{ \Carbon\Carbon::parse($aug->Date_augmentation)->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            @php
                                $badge = 'secondary';
                                $icon = 'fa-question';
                                $label = 'Autre';
                                
                                if($aug->Type_budget == 'supplementaire') {
                                    $badge = 'success';
                                    $icon = 'fa-plus-circle';
                                    $label = 'Supplémentaire';
                                } elseif($aug->Type_budget == 'rectificatif') {
                                    $badge = 'info';
                                    $icon = 'fa-edit';
                                    $label = 'Rectificatif';
                                } elseif($aug->Type_budget == 'virement') {
                                    $badge = 'primary';
                                    $icon = 'fa-exchange-alt';
                                    $label = 'Virement';
                                } elseif($aug->Type_budget == 'report') {
                                    $badge = 'warning';
                                    $icon = 'fa-arrow-right';
                                    $label = 'Report';
                                }
                            @endphp
                            <span class="badge badge-{{ $badge }}">
                                <i class="fas {{ $icon }}"></i> {{ $label }}
                            </span>
                        </td>
                        <td>
                            <strong>{{ $aug->Numero_decision }}</strong>
                            <div class="small text-muted">{{ \Carbon\Carbon::parse($aug->Date_decision)->format('d/m/Y') }}</div>
                        </td>
                        <td class="small">
                            @php
                                $section = \App\Models\BdgSection::find($aug->IDSection);
                            @endphp
                            {{ $section->Num_section ?? '' }} - {{ Str::limit($section->NOM_section ?? '', 20) }}
                        </td>
                        <td class="small">
                            <i class="fas fa-money-bill-wave text-success"></i>
                            {{ Str::limit($aug->Source_financement, 30) }}
                        </td>
                        <td class="text-right font-weight-bold text-success">
                            +{{ number_format($aug->Montant_augmentation, 2, ',', ' ') }} DA
                        </td>
                        <td class="text-right">
                            <div class="btn-group btn-group-sm">
                                <button 
                                    class="btn btn-outline-info" 
                                    title="Détails"
                                    data-toggle="tooltip"
                                    onclick="alert('Désignation: {{ $aug->Designation }}\n\nObservations: {{ $aug->Observations }}')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button 
                                    wire:click="delete({{ $aug->IDAugmentation }})" 
                                    class="btn btn-outline-danger"
                                    title="Annuler"
                                    data-toggle="tooltip"
                                    onclick="return confirm('Annuler cette augmentation budgétaire ? Le montant sera retiré de l\'enveloppe.')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 d-block text-secondary"></i>
                            Aucun budget supplémentaire pour cet exercice
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL --}}
    @if($showModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle mr-2"></i>
                        Augmenter l'Enveloppe Budgétaire
                    </h5>
                    <button type="button" class="close text-white" wire:click="$set('showModal', false)">&times;</button>
                </div>
                
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        
                        {{-- TYPE --}}
                        <div class="alert alert-light border mb-3">
                            <h6 class="mb-2"><i class="fas fa-tag mr-2"></i>Type de Budget</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="type1" wire:model.live="form.type_budget" value="supplementaire" class="custom-control-input" checked>
                                        <label class="custom-control-label" for="type1">
                                            <strong>Supplémentaire</strong><br>
                                            <small class="text-muted">Crédits additionnels</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="type2" wire:model.live="form.type_budget" value="rectificatif" class="custom-control-input">
                                        <label class="custom-control-label" for="type2">
                                            <strong>Rectificatif</strong><br>
                                            <small class="text-muted">Ajustement</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="type3" wire:model.live="form.type_budget" value="virement" class="custom-control-input">
                                        <label class="custom-control-label" for="type3">
                                            <strong>Virement</strong><br>
                                            <small class="text-muted">Transfert</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="type4" wire:model.live="form.type_budget" value="report" class="custom-control-input">
                                        <label class="custom-control-label" for="type4">
                                            <strong>Report</strong><br>
                                            <small class="text-muted">Année précédente</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- INFOS ADMINISTRATIVES --}}
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-file-alt mr-2"></i>Informations Administratives</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="font-weight-bold">N° Décision <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="form.numero_decision" class="form-control" placeholder="Ex: 2025/BS/001">
                                        @error('form.numero_decision') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="font-weight-bold">Date Décision <span class="text-danger">*</span></label>
                                        <input type="date" wire:model="form.date_decision" class="form-control">
                                        @error('form.date_decision') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="font-weight-bold">Source Financement <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="form.source_financement" class="form-control" placeholder="Ex: Subvention État">
                                        @error('form.source_financement') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- BUDGET ET SECTION --}}
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-sitemap mr-2"></i>Affectation</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="font-weight-bold">Budget <span class="text-danger">*</span></label>
                                        <select wire:model.live="form.IDBudjet" class="form-control">
                                            <option value="">-- Choisir le budget à augmenter --</option>
                                            @foreach($budgets as $b)
                                                <option value="{{ $b->IDBudjet }}">
                                                    {{ $b->EXERCICE }} - {{ $b->designation }}
                                                    (Actuel: {{ number_format($b->Montant_Restant, 2, ',', ' ') }} DA)
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('form.IDBudjet') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="font-weight-bold">Section <span class="text-danger">*</span></label>
                                        <select wire:model="form.IDSection" class="form-control">
                                            <option value="">-- Choisir Section --</option>
                                            @foreach($sections as $s)
                                                <option value="{{ $s->IDSection }}">{{ $s->Num_section }} - {{ $s->NOM_section }}</option>
                                            @endforeach
                                        </select>
                                        @error('form.IDSection') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- MONTANT --}}
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-money-bill-wave mr-2"></i>Montant et Observations</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="font-weight-bold">Libellé <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="form.designation" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-success font-weight-bold">Montant Augmentation (DA) <span class="text-danger">*</span></label>
                                        <input 
                                            type="number" 
                                            step="0.01" 
                                            wire:model="form.Montant_BS" 
                                            class="form-control form-control-lg border-success text-right font-weight-bold"
                                            placeholder="0.00">
                                        @error('form.Montant_BS') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="form-group mt-3 mb-0">
                                    <label class="font-weight-bold">Observations</label>
                                    <textarea wire:model="form.observations" class="form-control" rows="3" placeholder="Notes complémentaires..."></textarea>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">
                            <i class="fas fa-times mr-1"></i> Annuler
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check mr-1"></i> Augmenter l'Enveloppe
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>