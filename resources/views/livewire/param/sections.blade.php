<?php

use Livewire\Volt\Component;
use App\Models\BdgSection;
use Livewire\Attributes\Layout;

new 
#[Layout('layouts.app')] 
class extends Component {
    // Pas de pagination manuelle, DataTables gère tout
    
    public bool $showModal = false;
    public bool $editMode = false;

    public array $form = [
        'IDSection' => '',
        'Num_section' => '',
        'NOM_section' => '',
        'NOM_section_ara' => '',
        'Estmateriel' => false,
    ];

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset('form');

        if ($id) {
            $this->editMode = true;
            $section = BdgSection::findOrFail($id);
            $this->form = $section->toArray();
            $this->form['Estmateriel'] = (bool) $section->Estmateriel;
        } else {
            $this->editMode = false;
        }
        $this->showModal = true;
    }

    public function closeModal() { $this->showModal = false; }

    public function save()
    {
        $this->validate([
            'form.Num_section' => 'required|string|max:50',
            'form.NOM_section' => 'required|string|max:100',
            'form.NOM_section_ara' => 'nullable|string|max:100',
            'form.Estmateriel' => 'boolean',
        ]);

        $data = $this->form;
        
        if ($this->editMode) {
            BdgSection::where('IDSection', $this->form['IDSection'])->update([
                'Num_section' => $data['Num_section'],
                'NOM_section' => $data['NOM_section'],
                'NOM_section_ara' => $data['NOM_section_ara'],
                'Estmateriel' => $data['Estmateriel'] ? 1 : 0,
            ]);
        } else {
            unset($data['IDSection']); 
            $data['Estmateriel'] = $data['Estmateriel'] ? 1 : 0;
            BdgSection::create($data);
        }

        $this->closeModal();
        session()->flash('success', __('crud.success_op'));
        $this->dispatch('table-updated'); 
    }

    public function delete($id)
    {
        try {
            BdgSection::findOrFail($id)->delete();
            session()->flash('success', __('crud.item_deleted'));
            $this->dispatch('table-updated');
        } catch (\Exception $e) {
            session()->flash('error', __('crud.error_op'));
        }
    }

    public function with()
    {
        return [
            'sections' => BdgSection::all(), // DataTables fera le tri
        ];
    }
}; ?>

<div>
    {{-- Plugins AdminLTE --}}
    @section('plugins.Datatables', true)
    @section('plugins.Sweetalert2', true)

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-dark m-0 font-weight-bold">{{ __('menu.sections') }}</h4>
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
            <h3 class="card-title">{{ __('menu.sections') }}</h3>
        </div>

        <div class="card-body">
            <table id="table-sections" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th style="width: 15%">{{ __('crud.code') }}</th>
                        <th>{{ __('crud.designation') }} (FR)</th>
                        <th class="text-right">{{ __('crud.designation_ar') }} (AR)</th>
                        <th class="text-center">Type</th>
                        <th style="width: 15%" class="text-center">{{ __('crud.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sections as $s)
                    <tr wire:key="row-{{ $s->IDSection }}">
                        <td><span class="badge badge-dark">{{ $s->Num_section }}</span></td>
                        <td class="font-weight-bold">{{ $s->NOM_section }}</td>
                        <td class="text-right font-weight-bold text-muted">{{ $s->NOM_section_ara }}</td>
                        <td class="text-center">
                            @if($s->Estmateriel)
                                <span class="badge badge-success"><i class="fas fa-cubes mr-1"></i> Stock</span>
                            @else
                                <span class="badge badge-light border">Admin</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button wire:click="openModal({{ $s->IDSection }})" class="btn btn-xs btn-outline-primary mr-1" title="{{ __('crud.edit') }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button wire:click="delete({{ $s->IDSection }})" 
                                    onclick="confirm('{{ __('crud.confirm_delete') }}') || event.stopImmediatePropagation()"
                                    class="btn btn-xs btn-outline-danger" title="{{ __('crud.delete') }}">
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
                <div class="modal-header {{ $editMode ? 'bg-warning' : 'bg-primary' }}">
                    <h5 class="modal-title text-white">
                        {{ $editMode ? __('crud.edit') : __('crud.new') }}
                    </h5>
                    <button type="button" class="close text-white" wire:click="closeModal">&times;</button>
                </div>
                
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ __('crud.code') }}</label>
                            <input type="text" wire:model="form.Num_section" class="form-control" placeholder="EX: RH, TECH...">
                            @error('form.Num_section') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>{{ __('crud.designation') }} (Français)</label>
                            <input type="text" wire:model="form.NOM_section" class="form-control text-left">
                            @error('form.NOM_section') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label class="float-right">{{ __('crud.designation_ar') }} (Arabe)</label>
                            <input type="text" wire:model="form.NOM_section_ara" class="form-control text-right" dir="rtl">
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="checkMateriel" wire:model="form.Estmateriel">
                                <label class="custom-control-label" for="checkMateriel">Section Matériel / Stock ?</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">{{ __('crud.cancel') }}</button>
                        <button type="submit" class="btn {{ $editMode ? 'btn-warning' : 'btn-primary' }}">
                            {{ __('crud.save') }}
                        </button>
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
                if ($.fn.DataTable.isDataTable('#table-sections')) { $('#table-sections').DataTable().destroy(); }
                $('#table-sections').DataTable({
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