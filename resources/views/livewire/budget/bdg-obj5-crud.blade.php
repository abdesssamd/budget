<?php

use Livewire\Volt\Component;
use App\Models\BdgObj5;
use App\Models\BdgObj4;
use App\Models\BdgObj3;
use App\Models\BdgObj2;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;

new 
#[Layout('layouts.app')] 
class extends Component {
    public bool $showModal = false;
    public bool $editMode = false;

    public $niveauLabel = 'Niveau 5'; 
    public $parentLabel = 'Niveau 4';
    
    public $listeObj2 = [];
    public $listeObj3 = [];
    public $listeParents = []; 

    public $selectedObj2 = ''; 
    public $selectedObj3 = ''; 

    public array $form = [
        'IDObj5' => '',
        'IDObj4' => '', 
        'designation' => '',
        'designation_ara' => '', // Ajout du champ Arabe
        'Num' => '',
    ];

    public function mount()
    {
        $params = DB::table('bdg_param_general_bdg')->first();
        if ($params) {
            $this->niveauLabel = $params->LIBellé_niveau5fr ?? 'Niveau 5';
            $this->parentLabel = $params->LIBellé_niveau4_fr ?? 'Niveau 4';
        }
        $this->listeObj2 = BdgObj2::orderBy('Num')->get(['IDObj2', 'Num', 'designation']);
    }

    public function updatedSelectedObj2($value)
    {
        $this->selectedObj3 = '';
        $this->form['IDObj4'] = '';
        $this->listeObj3 = [];
        $this->listeParents = [];

        if ($value) {
            $this->listeObj3 = BdgObj3::where('IDObj2', $value)->orderBy('Num')->get(['IDObj3', 'Num', 'designation']);
        }
    }

    public function updatedSelectedObj3($value)
    {
        $this->form['IDObj4'] = '';
        $this->listeParents = [];

        if ($value) {
            $this->listeParents = BdgObj4::where('IDObj3', $value)->orderBy('Num')->get(['IDObj4', 'Num', 'designation']);
        }
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset('form', 'selectedObj2', 'selectedObj3', 'listeObj3', 'listeParents');
        
        if(empty($this->listeObj2)) {
            $this->listeObj2 = BdgObj2::orderBy('Num')->get(['IDObj2', 'Num', 'designation']);
        }

        if ($id) {
            $this->editMode = true;
            $obj5 = BdgObj5::with('obj4.obj3.obj2')->findOrFail($id);
            $this->form = $obj5->toArray();

            // S'assurer que le champ arabe est initialisé si null en BDD
            $this->form['designation_ara'] = $obj5->designation_ara ?? '';

            if ($obj5->obj4 && $obj5->obj4->obj3) {
                $this->selectedObj2 = $obj5->obj4->obj3->IDObj2;
                $this->updatedSelectedObj2($this->selectedObj2);
                $this->selectedObj3 = $obj5->obj4->IDObj3;
                $this->updatedSelectedObj3($this->selectedObj3);
                $this->form['IDObj4'] = $obj5->IDObj4;
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
            'form.IDObj4' => 'required|exists:bdg_obj4,IDObj4',
            'form.designation' => 'required|string|max:100',
            'form.designation_ara' => 'nullable|string|max:100', // Validation Arabe
            'form.Num' => 'nullable|string|max:50',
        ]);

        if ($this->editMode) {
            BdgObj5::where('IDObj5', $this->form['IDObj5'])->update([
                'designation' => $this->form['designation'],
                'designation_ara' => $this->form['designation_ara'], // Mise à jour Arabe
                'Num' => $this->form['Num'],
                'IDObj4' => $this->form['IDObj4'],
            ]);
        } else {
            $data = $this->form;
            unset($data['IDObj5']); 
            BdgObj5::create($data);
        }

        $this->closeModal();
        session()->flash('success', 'Opération réussie !');
        $this->dispatch('table-updated');
    }

    public function delete($id)
    {
        BdgObj5::findOrFail($id)->delete();
        session()->flash('success', 'Supprimé avec succès.');
        $this->dispatch('table-updated');
    }

    public function with()
    {
        return [
            'obj5' => BdgObj5::with('obj4.obj3')->get(),
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

    <div class="card card-outline card-danger">
        <div class="card-header">
            <h3 class="card-title">Liste des {{ $niveauLabel }}</h3>
        </div>

        <div class="card-body">
            <table id="table-obj5" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Num</th>
                        <th>Désignation (Fr)</th>
                        <th class="text-right">Désignation (Ar)</th>
                        <th>Parent ({{ $parentLabel }})</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($obj5 as $o)
                    <tr wire:key="row-{{ $o->IDObj5 }}">
                        <td><span class="badge badge-danger">{{ $o->Num }}</span></td>
                        <td class="font-weight-bold">{{ $o->designation }}</td>
                        <td class="text-right font-weight-bold text-muted">{{ $o->designation_ara }}</td>
                        <td>
                            @if($o->obj4)
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-light border mr-2">{{ $o->obj4->Num }}</span>
                                    <span class="small">{{ Str::limit($o->obj4->designation, 25) }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="text-right">
                            <button wire:click="openModal({{ $o->IDObj5 }})" class="btn btn-xs btn-outline-primary mr-1"><i class="fas fa-edit"></i></button>
                            <button wire:click="delete({{ $o->IDObj5 }})" 
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
                            <label class="small text-muted font-weight-bold text-uppercase">1. Filtre Article (Niv 2)</label>
                            <select wire:model.live="selectedObj2" class="form-control">
                                <option value="">-- Choisir Article --</option>
                                @foreach($listeObj2 as $i) <option value="{{ $i->IDObj2 }}">{{ $i->Num }} - {{ $i->designation }}</option> @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="small text-muted font-weight-bold text-uppercase">2. Filtre S/Article (Niv 3)</label>
                            <select wire:model.live="selectedObj3" class="form-control" {{ empty($listeObj3) ? 'disabled' : '' }}>
                                <option value="">-- Choisir S/Article --</option>
                                @foreach($listeObj3 as $i) <option value="{{ $i->IDObj3 }}">{{ $i->Num }} - {{ $i->designation }}</option> @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="small text-primary font-weight-bold text-uppercase">3. Rattachement : {{ $parentLabel }}</label>
                            <select wire:model="form.IDObj4" class="form-control" {{ empty($listeParents) ? 'disabled' : '' }}>
                                <option value="">-- Sélectionner le parent --</option>
                                @foreach($listeParents as $p) <option value="{{ $p->IDObj4 }}">{{ $p->Num }} - {{ $p->designation }}</option> @endforeach
                            </select>
                            @error('form.IDObj4') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <hr>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label>Numéro</label>
                                    <input type="text" wire:model="form.Num" class="form-control">
                                </div>
                            </div>
                            <div class="col-8">
                                <div class="form-group">
                                    <label>Désignation (Français)</label>
                                    <input type="text" wire:model="form.designation" class="form-control">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Champ Arabe -->
                        <div class="form-group">
                            <label class="d-block text-right">Désignation (Arabe)</label>
                            <input type="text" wire:model="form.designation_ara" class="form-control text-right" dir="rtl" placeholder="التعيين بالعربية">
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
                if ($.fn.DataTable.isDataTable('#table-obj5')) { $('#table-obj5').DataTable().destroy(); }
                $('#table-obj5').DataTable({
                    "responsive": true, "lengthChange": true, "autoWidth": false,
                    "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json" },
                    "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
                }).buttons().container().appendTo('#table-obj5_wrapper .col-md-6:eq(0)');
            }
            initDataTable();
            document.addEventListener('livewire:navigated', initDataTable);
            Livewire.on('table-updated', () => { setTimeout(initDataTable, 100); });
        });
    </script>
    @endsection
</div>