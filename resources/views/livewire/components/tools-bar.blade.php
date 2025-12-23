<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DynamicTableExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Schema;

new class extends Component {
    use WithFileUploads;

    // --- PARAMETRES RECUS DU PARENT ---
    public string $model;           // Ex: 'App\Models\ParamFonction'
    public array $columns = [];     // Ex: ['designation', 'code']
    public array $headers = [];     // Ex: ['Désignation', 'Code']
    public string $title = 'Export';// Nom du fichier
    
    // NOUVEAU PARAMÈTRE : Gère la visibilité du bouton Import
    public bool $canImport = true; 

    // --- ETAT INTERNE ---
    public $importFile;
    public bool $showImportModal = false;

    // --- EXPORT EXCEL ---
    public function exportExcel()
    {
        $data = $this->getData();
        return Excel::download(new DynamicTableExport($data, $this->headers), $this->title . '_' . date('Y-m-d') . '.xlsx');
    }

    // --- EXPORT PDF ---
    public function exportPdf()
    {
        $data = $this->getData();
        
        $pdf = Pdf::loadView('layouts.pdf-export', [
            'title' => $this->title,
            'headers' => $this->headers,
            'data' => $data
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, $this->title . '.pdf');
    }

    // --- IMPORT CSV UNIVERSEL ---
    public function import()
    {
        // Sécurité : On bloque l'import si l'option est désactivée
        if (!$this->canImport) {
            return;
        }

        $this->validate(['importFile' => 'required|file|max:10240']);

        $path = $this->importFile->getRealPath();
        $file = fopen($path, 'r');
        $count = 0;

        while (($line = fgetcsv($file, 1000, ";")) !== FALSE) {
            if (count($line) == 1 && strpos($line[0], ',') !== false) $line = explode(',', $line[0]);
            
            $insertData = [];
            $isEmpty = true;

            foreach ($this->columns as $index => $colName) {
                if (isset($line[$index])) {
                    $val = trim(preg_replace('/^\xEF\xBB\xBF/', '', $line[$index]));
                    if (!empty($val)) {
                        $insertData[$colName] = $val;
                        $isEmpty = false;
                    }
                }
            }

            if ($isEmpty || (isset($insertData[$this->columns[0]]) && strtolower($insertData[$this->columns[0]]) == strtolower($this->headers[0]))) {
                continue;
            }

            $firstCol = $this->columns[0];
            if (!$this->model::where($firstCol, $insertData[$firstCol])->exists()) {
                $this->model::create($insertData);
                $count++;
            }
        }
        fclose($file);

        $this->showImportModal = false;
        $this->dispatch('refresh-list'); 
        session()->flash('success', "$count enregistrements importés !");
    }

    private function getData()
    {
        return $this->model::select($this->columns)->get();
    }
}; ?>

<div class="d-inline-block">
    <div class="btn-group shadow-sm" role="group">
        
        <!-- EXCEL -->
        <button wire:click="exportExcel" class="btn btn-outline-success btn-sm" title="Exporter Excel">
            <i class="fas fa-file-excel me-1"></i> Excel
        </button>

        <!-- PDF -->
        <button wire:click="exportPdf" class="btn btn-outline-danger btn-sm" title="Exporter PDF">
            <i class="fas fa-file-pdf me-1"></i> PDF
        </button>

        <!-- IMPRIMER -->
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm" title="Imprimer la page">
            <i class="fas fa-print me-1"></i> Print
        </button>

        <!-- IMPORTER (Affiché seulement si $canImport est true) -->
        @if($canImport)
        <button wire:click="$set('showImportModal', true)" class="btn btn-outline-primary btn-sm" title="Importer CSV">
            <i class="fas fa-file-import me-1"></i> Import
        </button>
        @endif
    </div>

    <!-- MODAL IMPORT -->
    @if($canImport && $showImportModal)
    <div class="modal-backdrop fade show" style="z-index: 1060;"></div>
    <div class="modal fade show d-block" style="z-index: 1070;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h6 class="modal-title">Importer des données ({{ $title }})</h6>
                    <button wire:click="$set('showImportModal', false)" class="btn-close btn-close-white"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small">
                        <strong>Format CSV :</strong> Les colonnes doivent être dans cet ordre : <br>
                        @foreach($headers as $h) <span class="badge bg-light text-dark border">{{ $h }}</span> @endforeach
                    </div>
                    <input type="file" wire:model="importFile" class="form-control" accept=".csv">
                    <div wire:loading wire:target="importFile" class="text-muted small mt-2">Chargement...</div>
                </div>
                <div class="modal-footer">
                    <button wire:click="import" class="btn btn-primary btn-sm" wire:loading.attr="disabled" wire:target="importFile">Lancer l'import</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>