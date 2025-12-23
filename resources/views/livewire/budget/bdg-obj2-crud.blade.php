<?php

use Livewire\Volt\Component;
use App\Models\BdgObj2;
use App\Models\BdgObj1;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;

new 
#[Layout('layouts.app')] 
class extends Component {
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public bool $showModal = false;
    public bool $editMode = false;
    public $listeObj1 = []; 

    public array $form = [
        'IDObj2' => '',
        'IDObj1' => '',
        'designation' => '',
        'Num' => '',
    ];

    public function mount()
    {
        $this->listeObj1 = BdgObj1::orderBy('Num')->get(['IDObj1', 'Num', 'designation']);
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset('form');

        if(empty($this->listeObj1)) {
            $this->listeObj1 = BdgObj1::orderBy('Num')->get(['IDObj1', 'Num', 'designation']);
        }

        if ($id) {
            $this->editMode = true;
            $this->form = BdgObj2::findOrFail($id)->toArray();
        } else {
            $this->editMode = false;
            $this->form['IDObj1'] = $this->listeObj1->first()->IDObj1 ?? '';
        }
        $this->showModal = true;
    }

    public function closeModal() { $this->showModal = false; }

    public function save()
    {
        $this->validate([
            'form.IDObj1' => 'required|exists:bdg_obj1,IDObj1',
            'form.designation' => 'required|string|max:100',
            'form.Num' => 'nullable|string|max:50',
        ]);

        if ($this->editMode) {
            BdgObj2::where('IDObj2', $this->form['IDObj2'])->update([
                'designation' => $this->form['designation'],
                'Num' => $this->form['Num'],
                'IDObj1' => $this->form['IDObj1'],
            ]);
        } else {
            $data = $this->form;
            unset($data['IDObj2']); 
            BdgObj2::create($data);
        }

        $this->closeModal();
        session()->flash('success', 'Article (OBJ2) enregistré.');
    }

    public function delete($id)
    {
        try {
            BdgObj2::findOrFail($id)->delete();
            session()->flash('success', 'Supprimé avec succès.');
        } catch (\Exception $e) {
            session()->flash('error', 'Impossible de supprimer (lié à OBJ3).');
        }
    }

    public function with()
    {
        return [
            'obj2' => BdgObj2::with('obj1')
                    ->where('designation', 'like', "%{$this->search}%")
                    ->orWhere('Num', 'like', "%{$this->search}%")
                    ->paginate(10),
        ];
    }
}; ?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-dark m-0 font-weight-bold">Gestion OBJ2 (Articles)</h4>
        <button wire:click="openModal()" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus-circle mr-2"></i>Nouveau
        </button>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title">Liste des Articles</h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" wire:model.live="search" class="form-control float-right" placeholder="Rechercher...">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th class="pl-4">ID</th>
                        <th>Num</th>
                        <th>Désignation</th>
                        <th>Chapitre (OBJ1)</th>
                        <th class="text-right pr-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($obj2 as $o)
                    <tr>
                        <td class="pl-4 font-weight-bold">#{{ $o->IDObj2 }}</td>
                        <td><span class="badge badge-info">{{ $o->Num }}</span></td>
                        <td>{{ $o->designation }}</td>
                        <td>
                            @if($o->obj1)
                                <small class="text-muted d-block">{{ $o->obj1->Num }}</small>
                                <span>{{ Str::limit($o->obj1->designation, 30) }}</span>
                            @else
                                <span class="text-danger small">Non lié</span>
                            @endif
                        </td>
                        <td class="text-right pr-4">
                            <button wire:click="openModal({{ $o->IDObj2 }})" class="btn btn-sm btn-outline-primary mr-1"><i class="fas fa-edit"></i></button>
                            <button wire:click="delete({{ $o->IDObj2 }})" 
                                    onclick="confirm('Confirmer ?') || event.stopImmediatePropagation()"
                                    class="btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-5 text-muted">Aucun résultat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer clearfix">
            <div class="float-right">
                {{ $obj2->links() }}
            </div>
        </div>
    </div>

    <!-- MODAL BS4 -->
    @if($showModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header {{ $editMode ? 'bg-warning' : 'bg-info' }}">
                    <h5 class="modal-title text-white">
                        {{ $editMode ? 'Modifier' : 'Ajouter' }} OBJ2
                    </h5>
                    <button type="button" class="close text-white" wire:click="closeModal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <!-- Select BS4 (form-control au lieu de form-select) -->
                        <div class="form-group">
                            <label>Chapitre de rattachement</label>
                            <select wire:model="form.IDObj1" class="form-control">
                                <option value="">-- Sélectionner --</option>
                                @foreach($listeObj1 as $parent)
                                    <option value="{{ $parent->IDObj1 }}">
                                        {{ $parent->Num }} - {{ $parent->designation }}
                                    </option>
                                @endforeach
                            </select>
                            @error('form.IDObj1') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Numéro</label>
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
                        <button type="submit" class="btn {{ $editMode ? 'btn-warning' : 'btn-info' }}">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>