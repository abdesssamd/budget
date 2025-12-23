<?php

use Livewire\Volt\Component;
use App\Models\BdgObj1;
use App\Models\BdgSection; // On a besoin des sections
use Livewire\Attributes\Layout;

new 
#[Layout('layouts.app')] 
class extends Component {
    
    public bool $showModal = false;
    public bool $editMode = false;
    public $sections = []; // Liste des sections

    public array $form = [
        'IDObj1' => '',
        'IDSection' => '', // Ajout du champ section
        'designation' => '',
        'designation_ara' => '',
        'Num' => '',
    ];

    protected $rules = [
        'form.IDSection' => 'required|exists:bdg_section,IDSection', // Validation
        'form.designation' => 'required|string',
        'form.Num' => 'nullable|string',
    ];

    public function mount()
    {
        // Charger les sections pour la liste déroulante
        $this->sections = BdgSection::orderBy('Num_section')->get();
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset('form');

        if ($id) {
            $this->editMode = true;
            $obj = BdgObj1::findOrFail($id);
            $this->form = $obj->toArray();
            $this->form['designation_ara'] = $obj->designation_ara ?? '';
        } else {
            $this->editMode = false;
            // Pré-sélectionner la première section si possible
            if(count($this->sections) > 0) {
                $this->form['IDSection'] = $this->sections->first()->IDSection;
            }
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function save()
    {
        $this->validate();

        if ($this->editMode) {
            BdgObj1::where('IDObj1', $this->form['IDObj1'])->update($this->form);
        } else {
            BdgObj1::create($this->form);
        }

        $this->closeModal();
        session()->flash('success', __('crud.success_op'));
        $this->dispatch('table-updated'); 
    }

    public function delete($id)
    {
        try {
            BdgObj1::findOrFail($id)->delete();
            session()->flash('success', __('crud.item_deleted'));
            $this->dispatch('table-updated');
        } catch (\Exception $e) {
            session()->flash('error', __('crud.error_op'));
        }
    }

    public function with()
    {
        // On charge la relation section pour l'affichage
        return ['obj1' => BdgObj1::with('section')->get()];
    }
}; ?>

<div>
    @section('plugins.Datatables', true)
    @section('plugins.Sweetalert2', true)

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-dark m-0 font-weight-bold">{{ __('menu.chapters') }}</h4>
        <button wire:click="openModal()" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus-circle mr-2"></i>{{ __('crud.new') }}
        </button>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">{{ __('menu.chapters') }}</h3>
        </div>

        <div class="card-body">
            <table id="table-obj1" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th style="width: 10%">{{ __('crud.code') }}</th>
                        <th style="width: 15%">{{ __('crud.number') }}</th>
                        <th>{{ __('menu.sections') }}</th> {{-- Colonne Section --}}
                        <th>{{ __('crud.designation') }} (FR)</th>
                        <th class="text-right">{{ __('crud.designation_ar') }} (AR)</th>
                        <th style="width: 15%" class="text-center">{{ __('crud.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($obj1 as $o)
                    <tr wire:key="row-{{ $o->IDObj1 }}">
                        <td class="text-muted font-weight-bold">#{{ $o->IDObj1 }}</td>
                        <td><span class="badge badge-info">{{ $o->Num }}</span></td>
                        <td>
                            @if($o->section)
                                <span class="badge badge-secondary">{{ $o->section->Num_section }}</span> 
                                <small>{{ Str::limit($o->section->NOM_section, 15) }}</small>
                            @else
                                <span class="text-danger small">Non défini</span>
                            @endif
                        </td>
                        <td class="font-weight-bold">{{ $o->designation }}</td>
                        <td class="text-right font-weight-bold text-muted">{{ $o->designation_ara }}</td>
                        <td class="text-center">
                            <button wire:click="openModal({{ $o->IDObj1 }})" class="btn btn-xs btn-outline-primary mr-1">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button wire:click="delete({{ $o->IDObj1 }})" 
                                    onclick="confirm('{{ __('crud.confirm_delete') }}') || event.stopImmediatePropagation()"
                                    class="btn btn-xs btn-outline-danger">
                                <i class="fas fa-trash-alt"></i>
                            </button>
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
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        {{ $editMode ? __('crud.edit') : __('crud.new') }}
                    </h5>
                    <button type="button" class="close text-white" wire:click="closeModal">&times;</button>
                </div>
                
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        
                        <!-- AJOUT DU CHAMP SECTION -->
                        <div class="form-group">
                            <label>{{ __('menu.sections') }}</label>
                            <select wire:model="form.IDSection" class="form-control font-weight-bold">
                                <option value="">{{ __('crud.select_option') }}</option>
                                @foreach($sections as $s)
                                    <option value="{{ $s->IDSection }}">{{ $s->Num_section }} - {{ $s->NOM_section }}</option>
                                @endforeach
                            </select>
                            @error('form.IDSection') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>{{ __('crud.number') }}</label>
                            <input type="text" wire:model="form.Num" class="form-control">
                            @error('form.Num') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>{{ __('crud.designation') }} (Français)</label>
                            <input type="text" wire:model="form.designation" class="form-control text-left">
                            @error('form.designation') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="float-right">{{ __('crud.designation_ar') }} (Arabe)</label>
                            <input type="text" wire:model="form.designation_ara" class="form-control text-right" dir="rtl">
                        </div>
                    </div>
                    
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">{{ __('crud.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('crud.save') }}</button>
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
                if ($.fn.DataTable.isDataTable('#table-obj1')) { $('#table-obj1').DataTable().destroy(); }
                $('#table-obj1').DataTable({
                    "responsive": true, "lengthChange": true, "autoWidth": false,
                    "language": { "url": "{{ app()->getLocale() == 'ar' ? asset('vendor/datatables/ar.json') : asset('vendor/datatables/fr.json') }}" }
                });
            }
            initDataTable();
            document.addEventListener('livewire:navigated', initDataTable);
            Livewire.on('table-updated', () => { setTimeout(initDataTable, 100); });
        });
    </script>
    @endsection
</div>