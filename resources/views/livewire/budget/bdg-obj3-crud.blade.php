<?php

use Livewire\Volt\Component;
use App\Models\BdgObj3;
use App\Models\BdgObj2;
use App\Models\BdgObj1;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;

new 
#[Layout('layouts.app')] 
class extends Component {
    // Pas de pagination, DataTables gère tout
    
    public bool $showModal = false;
    public bool $editMode = false;

    // Variables pour les libellés dynamiques
    public $niveauLabel = 'Niveau 3'; 
    public $parentLabel = 'Niveau 2';
    public $grandParentLabel = 'Niveau 1';

    // Listes pour les selects
    public $listeObj1 = [];    
    public $listeParents = []; 

    public $selectedObj1 = ''; 

    public array $form = [
        'IDObj3' => '',
        'IDObj2' => '', 
        'designation' => '',
        'Num' => '',
    ];

    public function mount()
    {
        $params = DB::table('bdg_param_general_bdg')->first();
        if ($params) {
            $this->niveauLabel = $params->LIBellé_niveau3_fr ?? 'Niveau 3';
            $this->parentLabel = $params->LIBellé_niveau2_fr ?? 'Niveau 2';
            $this->grandParentLabel = $params->LIBellé_niveau1_fr ?? 'Niveau 1';
        }

        $this->listeObj1 = BdgObj1::orderBy('Num')->get(['IDObj1', 'Num', 'designation']);
        $this->listeParents = [];
    }

    public function updatedSelectedObj1($value)
    {
        $this->form['IDObj2'] = '';
        
        if ($value) {
            $this->listeParents = BdgObj2::where('IDObj1', $value)
                                         ->orderBy('Num')
                                         ->get(['IDObj2', 'Num', 'designation']);
        } else {
            $this->listeParents = [];
        }
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset('form', 'selectedObj1', 'listeParents'); 

        if(empty($this->listeObj1)) {
            $this->listeObj1 = BdgObj1::orderBy('Num')->get(['IDObj1', 'Num', 'designation']);
        }

        if ($id) {
            $this->editMode = true;
            
            $obj3 = BdgObj3::with('obj2')->findOrFail($id);
            $this->form = $obj3->toArray();

            // Pré-remplissage cascade
            if ($obj3->obj2) {
                $this->selectedObj1 = $obj3->obj2->IDObj1;
                $this->updatedSelectedObj1($this->selectedObj1);
                $this->form['IDObj2'] = $obj3->IDObj2;
            }

        } else {
            $this->editMode = false;
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function save()
    {
        $this->validate([
            'selectedObj1' => 'required', 
            'form.IDObj2' => 'required|exists:bdg_obj2,IDObj2',
            'form.designation' => 'required|string|max:100',
            'form.Num' => 'nullable|string|max:50',
        ]);

        if ($this->editMode) {
            BdgObj3::where('IDObj3', $this->form['IDObj3'])->update([
                'designation' => $this->form['designation'],
                'Num' => $this->form['Num'],
                'IDObj2' => $this->form['IDObj2'],
            ]);
        } else {
            $data = $this->form;
            unset($data['IDObj3']); 
            BdgObj3::create($data);
        }

        $this->closeModal();
        session()->flash('success', 'Opération réussie !');
        $this->dispatch('table-updated');
    }

    public function delete($id)
    {
        try {
            BdgObj3::findOrFail($id)->delete();
            session()->flash('success', 'Élément supprimé.');
            $this->dispatch('table-updated');
        } catch (\Exception $e) {
            session()->flash('error', 'Impossible de supprimer (lié à un niveau inférieur).');
        }
    }

    public function with()
    {
        // On charge tout pour DataTables
        return [
            'obj3' => BdgObj3::with('obj2.obj1')->get(),
        ];
    }
}; ?>

<div>
    @section('plugins.Datatables', true)
    @section('plugins.Sweetalert2', true)

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-dark m-0 font-weight-bold">Gestion {{ $niveauLabel }}</h4>
        <button wire:click="openModal()" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus-circle mr-2"></i>Nouveau
        </button>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <div class="card card-outline card-purple">
        <div class="card-header">
            <h3 class="card-title">Liste des {{ $niveauLabel }}</h3>
        </div>

        <div class="card-body">
            <table id="table-obj3" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Num</th>
                        <th>Désignation</th>
                        <th>Rattachement</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($obj3 as $o)
                    <tr wire:key="row-{{ $o->IDObj3 }}">
                        <td>#{{ $o->IDObj3 }}</td>
                        <td><span class="badge bg-purple">{{ $o->Num }}</span></td>
                        <td class="font-weight-bold">{{ $o->designation }}</td>
                        <td>
                            @if($o->obj2)
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-light border mr-2">{{ $o->obj2->Num }}</span>
                                    <span class="text-muted small">{{ Str::limit($o->obj2->designation, 20) }}</span>
                                </div>
                                @if($o->obj2->obj1)
                                    <div class="small text-muted ml-1 mt-1">
                                        <i class="fas fa-level-up-alt fa-rotate-90 mr-1"></i> 
                                        {{ $o->obj2->obj1->Num }}
                                    </div>
                                @endif
                            @else
                                <span class="text-danger small">Non lié</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <button wire:click="openModal({{ $o->IDObj3 }})" class="btn btn-xs btn-outline-primary mr-1"><i class="fas fa-edit"></i></button>
                            <button wire:click="delete({{ $o->IDObj3 }})" 
                                    onclick="confirm('Supprimer ?') || event.stopImmediatePropagation()"
                                    class="btn btn-xs btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($showModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title font-weight-bold">
                        {{ $editMode ? 'Modifier' : 'Ajouter' }} {{ $niveauLabel }}
                    </h5>
                    <button type="button" class="close" wire:click="closeModal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="small text-uppercase text-primary font-weight-bold">1. Filtrer par {{ $grandParentLabel }}</label>
                            <select wire:model.live="selectedObj1" class="form-control">
                                <option value="">-- Choisir un Chapitre --</option>
                                @foreach($listeObj1 as $gp)
                                    <option value="{{ $gp->IDObj1 }}">{{ $gp->Num }} - {{ $gp->designation }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="small text-uppercase text-primary font-weight-bold">2. Rattachement : {{ $parentLabel }}</label>
                            <select wire:model="form.IDObj2" class="form-control" {{ empty($listeParents) ? 'disabled' : '' }}>
                                <option value="">{{ empty($listeParents) ? '-- Sélectionnez niveau supérieur --' : '-- Choisir le parent --' }}</option>
                                @foreach($listeParents as $parent)
                                    <option value="{{ $parent->IDObj2 }}">{{ $parent->Num }} - {{ $parent->designation }}</option>
                                @endforeach
                            </select>
                            @error('form.IDObj2') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <hr>

                        <div class="form-group">
                            <label>Numéro / Code</label>
                            <input type="text" wire:model="form.Num" class="form-control">
                            @error('form.Num') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Désignation</label>
                            <input type="text" wire:model="form.designation" class="form-control">
                            @error('form.designation') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @section('js')
    <script>
        $(function () {
            function initDataTable() {
                if ($.fn.DataTable.isDataTable('#table-obj3')) {
                    $('#table-obj3').DataTable().destroy();
                }
                $('#table-obj3').DataTable({
                    "responsive": true, "lengthChange": true, "autoWidth": false,
                    "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json" },
                    "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
                }).buttons().container().appendTo('#table-obj3_wrapper .col-md-6:eq(0)');
            }
            initDataTable();
            document.addEventListener('livewire:navigated', initDataTable);
            Livewire.on('table-updated', () => { setTimeout(initDataTable, 100); });
        });
    </script>
    @endsection
</div>