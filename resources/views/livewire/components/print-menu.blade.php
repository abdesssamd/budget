<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\File;

new class extends Component {
    
    // Paramètres passés au composant
    public string $folder;          // Ex: 'engagement', 'mandat'
    public $id = null;              // ID de l'enregistrement (ou null pour global)
    public string $type = 'individual'; // 'individual' (ligne) ou 'global' (entête)
    public string $icon = 'fas fa-print'; // Icône du bouton
    public string $btnClass = 'btn-default btn-xs border'; // Style du bouton
    public string $label = '';      // Texte du bouton (optionnel)

    public array $templates = [];

    public function mount()
    {
        // Chemin vers les vues d'impression
        $path = resource_path("views/imp/{$this->folder}");
        
        if (File::exists($path)) {
            $files = File::files($path);
            
            foreach ($files as $file) {
                $filename = $file->getFilename();
                
                // On ne traite que les fichiers .blade.php
                if (!str_ends_with($filename, '.blade.php')) continue;

                // Nom propre sans extension
                $cleanName = str_replace('.blade.php', '', $filename);
                
                // On ignore les partiels (commençant par _)
                if (str_starts_with($cleanName, '_')) continue;

                // FILTRAGE SELON LE TYPE (Global vs Individuel)
                // Convention : Les fichiers globaux contiennent 'liste' dans leur nom
                $isList = str_contains(strtolower($cleanName), 'liste');
                
                if ($this->type === 'global' && !$isList) continue; // Si bouton global, on veut que les listes
                if ($this->type === 'individual' && $isList) continue; // Si bouton ligne, on ne veut pas les listes

                // Extraction du Titre personnalisé dans le fichier
                // Cherche : {{-- TITLE: Mon Titre --}}
                $content = file_get_contents($file->getRealPath());
                $title = ucfirst(str_replace('_', ' ', $cleanName));
                
                if (preg_match('/\{\{--\s*TITLE:\s*(.*?)\s*--\}\}/', $content, $matches)) {
                    $title = trim($matches[1]);
                }

                $this->templates[$cleanName] = $title;
            }
        }
    }
}; ?>

<div class="d-inline-block">
    @if(count($templates) > 0)
        <div class="btn-group">
            <button type="button" class="btn {{ $btnClass }} dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Imprimer">
                <i class="{{ $icon }}"></i> {{ $label }}
            </button>
            <div class="dropdown-menu">
                @foreach($templates as $file => $title)
                    {{-- Si c'est global et pas d'ID fourni, on utilise l'année en cours --}}
                    @php $printId = ($type === 'global' && !$id) ? date('Y') : $id; @endphp
                    
                    <a class="dropdown-item" href="{{ route('print.generique', ['dossier' => $folder, 'fichier' => $file, 'id' => $printId]) }}" target="_blank">
                        <i class="fas fa-file-pdf mr-2 text-danger"></i> {{ $title }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>