<?php

use Livewire\Volt\Component;
use App\Models\BdgObj4;
use App\Models\BdgObj3;
use App\Models\BdgObj2;
use App\Models\BdgObj1;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;

new 
#[Layout('layouts.app')] 
class extends Component {
    public bool $showModal = false;
    public bool $editMode = false;

    public $niveauLabel = 'Niveau 4'; 
    public $parentLabel = 'Niveau 3';
    
    public $listeObj1 = [];    
    public $listeObj2 = [];    
    public $listeParents = []; 

    public $selectedObj1 = ''; 
    public $selectedObj2 = ''; 

    public array $form = [
        'IDObj4' => '',
        'IDObj3' => '',
        'designation' => '',
        'Num' => '',
    ];

    public function mount()
    {
        $params = DB::table('bdg_param_general_bdg')->first();
        if ($params) {
            $this->niveauLabel = $params->LIBellé_niveau4_fr ?? 'Niveau 4';
            $this->parentLabel = $params->LIBellé_niveau3_fr ?? 'Niveau 3';
        }
        $this->listeObj1 = BdgObj1::orderBy('Num')->get(['IDObj1', 'Num', 'designation']);
    }

    public function updatedSelectedObj1($value)
    {
        $this->selectedObj2 = '';
        $this->form['IDObj3'] = '';
        $this->listeObj2 = [];
        $this->listeParents = [];

        if ($value) {
            $this->listeObj2 = BdgObj2::where('IDObj1', $value)->orderBy('Num')->get(['IDObj2', 'Num', 'designation']);
        }
    }

    public function updatedSelectedObj2($value)
    {
        $this->form['IDObj3'] = '';
        $this->listeParents = [];

        if ($value) {
            $this->listeParents = BdgObj3::where('IDObj2', $value)->orderBy('Num')->get(['IDObj3', 'Num', 'designation']);
        }
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset('form', 'selectedObj1', 'selectedObj2', 'listeObj2', 'listeParents');
        
        if(empty($this->listeObj1)) {
            $this->listeObj1 = BdgObj1::orderBy('Num')->get(['IDObj1', 'Num', 'designation']);
        }

        if ($id) {
            $this->editMode = true;
            $obj4 = BdgObj4::with('obj3.obj2.obj1')->findOrFail($id);
            $this->form = $obj4->toArray();

            if ($obj4->obj3 && $obj4->obj3->obj2) {
                $this->selectedObj1 = $obj4->obj3->obj2->IDObj1;
                $this->updatedSelectedObj1($this->selectedObj1);
                $this->selectedObj2 = $obj4->obj3->IDObj2;
                $this->updatedSelectedObj2($this->selectedObj2);
                $this->form['IDObj3'] = $obj4->IDObj3;
            }
        } else {
            $this->editMode = false;
        }
        $this->showModal = true;
    }

    public function closeModal() { $this->showModal = false; }

    public function save()
    {
        $this->validate([
            'form.IDObj3' => 'required|exists:bdg_obj3,IDObj3',
            'form.designation' => 'required|string|max:100',
            'form.Num' => 'nullable|string|max:50',
        ]);

        if ($this->editMode) {
            BdgObj4::where('IDObj4', $this->form['IDObj4'])->update([
                'designation' => $this->form['designation'],
                'Num' => $this->form['Num'],
                'IDObj3' => $this->form['IDObj3'],
            ]);
        } else {
            $data = $this->form;
            unset($data['IDObj4']);
            BdgObj4::create($data);
        }

        $this->closeModal();
        session()->flash('success', 'Opération réussie !');
        $this->dispatch('table-updated');
    }

    public function delete($id)
    {
        try {
            BdgObj4::findOrFail($id)->delete();
            session()->flash('success', 'Supprimé avec succès.');
            $this->dispatch('table-updated');
        } catch (\Exception $e) {
            session()->flash('error', 'Impossible de supprimer.');
        }
    }

    public function with()
    {
        return [
            'obj4' => BdgObj4::with('obj3.obj2')->get(),
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
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check mr-2"></i> {{ session('success') }} <button class="close" data-dismiss="alert">&times;</button></div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }} <button class="close" data-dismiss="alert">&times;</button></div>
    @endif

    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Liste des {{ $niveauLabel }}</h3>
        </div>

        <div class="card-body">
            <table id="table-obj4" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Num</th>
                        <th>Désignation</th>
                        <th>Parent ({{ $parentLabel }})</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($obj4 as $o)
                    <tr wire:key="row-{{ $o->IDObj4 }}">
                        <td><span class="badge badge-success">{{ $o->Num }}</span></td>
                        <td class="font-weight-bold">{{ $o->designation }}</td>
                        <td>
                            @if($o->obj3)
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-light border mr-2">{{ $o->obj3->Num }}</span>
                                    <span class="small">{{ Str::limit($o->obj3->designation, 25) }}</span>
                                </div>
                            @else
                                <span class="text-danger small">Non lié</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <button wire:click="openModal({{ $o->IDObj4 }})" class="btn btn-xs btn-outline-primary mr-1"><i class="fas fa-edit"></i></button>
                            <button wire:click="delete({{ $o->IDObj4 }})" 
                                    onclick="confirm('Confirmer suppression ?') || event.stopImmediatePropagation()"
                                    class="btn btn-xs btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($showModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title font-weight-bold">{{ $editMode ? 'Modifier' : 'Ajouter' }} {{ $niveauLabel }}</h5>
                    <button type="button" class="close" wire:click="closeModal">&times;</button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="small text-muted font-weight-bold text-uppercase">1. Chapitre</label>
                            <select wire:model.live="selectedObj1" class="form-control">
                                <option value="">-- Filtre Niveau 1 --</option>
                                @foreach($listeObj1 as $i) <option value="{{ $i->IDObj1 }}">{{ $i->Num }} - {{ $i->designation }}</option> @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="small text-muted font-weight-bold text-uppercase">2. Article</label>
                            <select wire:model.live="selectedObj2" class="form-control" {{ empty($listeObj2) ? 'disabled' : '' }}>
                                <option value="">-- Filtre Niveau 2 --</option>
                                @foreach($listeObj2 as $i) <option value="{{ $i->IDObj2 }}">{{ $i->Num }} - {{ $i->designation }}</option> @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="small text-primary font-weight-bold text-uppercase">3. Rattachement : {{ $parentLabel }}</label>
                            <select wire:model="form.IDObj3" class="form-control" {{ empty($listeParents) ? 'disabled' : '' }}>
                                <option value="">-- Sélectionner le parent --</option>
                                @foreach($listeParents as $p) <option value="{{ $p->IDObj3 }}">{{ $p->Num }} - {{ $p->designation }}</option> @endforeach
                            </select>
                            @error('form.IDObj3') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <hr>
                        <div class="row">
                            <div class="col-4">
                                <label>Numéro</label>
                                <input type="text" wire:model="form.Num" class="form-control">
                            </div>
                            <div class="col-8">
                                <label>Désignation</label>
                                <input type="text" wire:model="form.designation" class="form-control">
                            </div>
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
                if ($.fn.DataTable.isDataTable('#table-obj4')) { $('#table-obj4').DataTable().destroy(); }
                $('#table-obj4').DataTable({
                    "responsive": true, "lengthChange": true, "autoWidth": false,
                    "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json" },
                    "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
                }).buttons().container().appendTo('#table-obj4_wrapper .col-md-6:eq(0)');
            }
            initDataTable();
            document.addEventListener('livewire:navigated', initDataTable);
            Livewire.on('table-updated', () => { setTimeout(initDataTable, 100); });
        });
    </script>
    @endsection
</div>