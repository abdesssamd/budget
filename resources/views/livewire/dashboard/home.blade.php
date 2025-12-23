<?php

use Livewire\Volt\Component;
use App\Models\BdgOperationBudg; // Engagements
use App\Models\BdgMandat;        // Mandats
use App\Models\BdgBudget;        // Budgets Globaux
use App\Models\BdgDetailOpBud;   // Détails mandatés
use App\Models\StkBonCommande;   // Pour le nouveau KPI
use App\Models\BdgSection;
use App\Models\BdgObj1;
use App\Models\BdgObj2;
use App\Models\BdgObj3;
use App\Models\BdgObj4;
use App\Models\BdgObj5;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Illuminate\Support\Str;

new 
#[Layout('layouts.app')] 
class extends Component {
    
    public $exercice;
    public $years = [];
    public $maxLevel = 1;

    // --- FILTRES ---
    public $sections = [];
    public $listeObj1 = [];
    public $listeObj2 = [];
    public $listeObj3 = [];
    public $listeObj4 = [];
    public $listeObj5 = [];

    public $filterSection = '';
    public $filterObj1 = '';
    public $filterObj2 = '';
    public $filterObj3 = '';
    public $filterObj4 = '';
    public $filterObj5 = '';

    // Chiffres clés
    public $totalBudget = 0;
    public $totalEngage = 0;
    public $totalMandate = 0;
    public $totalRestant = 0;
    public $totalBCNonEngage = 0; 
    public $tauxConsommation = 0;

    // Données pour les graphiques
    public $chartLabels = [];
    public $chartDataEngage = [];
    public $chartDataMandate = [];
    public $pieLabels = [];
    public $pieData = [];

    // Listes récentes & Stats
    public $lastEngagements = [];
    public $lastMandats = [];
    public $sectionStats = [];

    public function mount()
    {
        $this->exercice = date('Y');
        
        $params = DB::table('bdg_param_general_bdg')->first();
        $this->maxLevel = $params->nombre_niveau ?? 1;

        $this->years = BdgOperationBudg::select('EXERCICE')
            ->distinct()
            ->orderByDesc('EXERCICE')
            ->pluck('EXERCICE')
            ->toArray();
        if(empty($this->years)) $this->years = [date('Y')];

        $this->sections = BdgSection::orderBy('Num_section')->get();

        $this->refreshDashboard();
    }

    // --- LOGIQUE DES FILTRES (CASCADE) ---
    public function updatedFilterSection($value) {
        $this->filterObj1 = ''; $this->resetLevels(1);
        $this->listeObj1 = $value ? BdgObj1::where('IDSection', $value)->orderBy('Num')->get() : [];
        $this->refreshDashboard();
    }
    public function updatedFilterObj1($value) {
        $this->resetLevels(2);
        if ($this->maxLevel >= 2 && $value) $this->listeObj2 = BdgObj2::where('IDObj1', $value)->orderBy('Num')->get();
        $this->refreshDashboard();
    }
    public function updatedFilterObj2($value) {
        $this->resetLevels(3);
        if ($this->maxLevel >= 3 && $value) $this->listeObj3 = BdgObj3::where('IDObj2', $value)->orderBy('Num')->get();
        $this->refreshDashboard();
    }
    public function updatedFilterObj3($value) {
        $this->resetLevels(4);
        if ($this->maxLevel >= 4 && $value) $this->listeObj4 = BdgObj4::where('IDObj3', $value)->orderBy('Num')->get();
        $this->refreshDashboard();
    }
    public function updatedFilterObj4($value) {
        $this->resetLevels(5);
        if ($this->maxLevel >= 5 && $value) $this->listeObj5 = BdgObj5::where('IDObj4', $value)->orderBy('Num')->get();
        $this->refreshDashboard();
    }
    public function updatedFilterObj5() { $this->refreshDashboard(); }

    private function resetLevels($fromLevel) {
        if($fromLevel <= 1) { $this->filterObj1 = ''; $this->listeObj2 = []; }
        if($fromLevel <= 2) { $this->filterObj2 = ''; $this->listeObj3 = []; }
        if($fromLevel <= 3) { $this->filterObj3 = ''; $this->listeObj4 = []; }
        if($fromLevel <= 4) { $this->filterObj4 = ''; $this->listeObj5 = []; }
        if($fromLevel <= 5) { $this->filterObj5 = ''; }
    }

    public function updatedExercice()
    {
        $this->refreshDashboard();
    }

    public function refreshDashboard()
    {
        // 1. Budget Global (CORRECTION IMPORTANTE)
        // Si aucun filtre n'est actif, on prend l'enveloppe globale définie dans BdgBudget
        if (empty($this->filterSection) && empty($this->filterObj1)) {
            $this->totalBudget = BdgBudget::where('EXERCICE', $this->exercice)
                ->where('Archive', 0)
                ->sum('Montant_Global');
        } else {
            // Si on filtre (par section...), on prend la somme des crédits RÉPARTIS (Type 2)
            $qBudget = BdgOperationBudg::where('EXERCICE', $this->exercice)->where('Type_operation', 2);
            $this->applyFilters($qBudget);
            $this->totalBudget = $qBudget->sum('Mont_operation');
        }

        // 2. Total Engagé (Type 3)
        $qEngage = BdgOperationBudg::where('EXERCICE', $this->exercice)->where('Type_operation', 3);
        $this->applyFilters($qEngage);
        $this->totalEngage = $qEngage->sum('Mont_operation');

        // 3. Total Mandaté
        $qMandate = DB::table('bdg_detail_op_bud')
            ->join('bdg_mandat', 'bdg_detail_op_bud.IDMandat', '=', 'bdg_mandat.IDMandat')
            ->join('bdg_operation_budg', 'bdg_detail_op_bud.IDOperation_Budg', '=', 'bdg_operation_budg.IDOperation_Budg')
            ->where('bdg_mandat.EXERCICE', $this->exercice);
            
        if ($this->filterSection) $qMandate->where('bdg_operation_budg.IDSection', $this->filterSection);
        if ($this->filterObj1) $qMandate->where('bdg_operation_budg.IDObj1', $this->filterObj1);
        if ($this->filterObj2) $qMandate->where('bdg_operation_budg.IDObj2', $this->filterObj2);
        
        $this->totalMandate = $qMandate->sum('bdg_detail_op_bud.Montant');

        // 4. Calculs Ratios
        $this->totalRestant = $this->totalBudget - $this->totalEngage;
        
        if($this->totalBudget > 0) {
            $this->tauxConsommation = round(($this->totalEngage / $this->totalBudget) * 100, 1);
        } else {
            $this->tauxConsommation = 0;
        }

        // 5. Total BC Non Engagés
        $this->totalBCNonEngage = StkBonCommande::where('valider', 1)
            ->where('IDExercice', $this->exercice)
            ->doesntHave('engagement')
            ->sum('prixtotal');

        // --- CHARGEMENT DES DONNÉES ---

        $this->lastEngagements = $qEngage->with('section')->orderByDesc('Creer_le')->limit(5)->get();

        $this->lastMandats = BdgMandat::where('EXERCICE', $this->exercice)
            ->orderByDesc('date_mandate')
            ->limit(5)
            ->get();

        // Graphique Mois
        $engByMonth = BdgOperationBudg::selectRaw('MONTH(Creer_le) as mois, SUM(Mont_operation) as total')
            ->where('EXERCICE', $this->exercice)
            ->where('Type_operation', 3);
        $this->applyFilters($engByMonth);
        $engByMonth = $engByMonth->groupBy('mois')->get();

        $this->chartLabels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
        $this->chartDataEngage = array_fill(0, 12, 0);
        foreach($engByMonth as $item) {
            $this->chartDataEngage[$item->mois - 1] = $item->total;
        }
        
        // Graphique Camembert
        if (!$this->filterSection) {
            $sectionsData = BdgOperationBudg::join('bdg_section', 'bdg_operation_budg.IDSection', '=', 'bdg_section.IDSection')
                ->selectRaw('bdg_section.Num_section as code, SUM(Mont_operation) as total')
                ->where('bdg_operation_budg.EXERCICE', $this->exercice)
                ->where('Type_operation', 3)
                ->groupBy('code')
                ->orderByDesc('total')
                ->limit(6)
                ->get();
            $this->pieLabels = $sectionsData->pluck('code')->toArray();
            $this->pieData = $sectionsData->pluck('total')->toArray();
        } else {
            $this->pieLabels = []; $this->pieData = [];
        }

        // --- STATS DÉTAILLÉES PAR SECTION ---
        if (!$this->filterSection) {
            $this->sectionStats = [];
            foreach($this->sections as $s) {
                // Budget Alloué (Type 2)
                $b = BdgOperationBudg::where('EXERCICE', $this->exercice)
                    ->where('Type_operation', 2) 
                    ->where('IDSection', $s->IDSection)
                    ->sum('Mont_operation');
                
                // Consommé (Type 3)
                $e = BdgOperationBudg::where('EXERCICE', $this->exercice)
                    ->where('Type_operation', 3)
                    ->where('IDSection', $s->IDSection)
                    ->sum('Mont_operation');

                if($b > 0 || $e > 0) {
                    $this->sectionStats[] = [
                        'label' => $s->Num_section . ' - ' . Str::limit($s->NOM_section, 40),
                        'budget' => $b,
                        'engage' => $e,
                        'ratio' => $b > 0 ? round(($e / $b) * 100, 1) : 0
                    ];
                }
            }
        } else {
            $this->sectionStats = [];
        }

        $this->dispatch('update-charts', [
            'labels' => $this->chartLabels,
            'dataEngage' => $this->chartDataEngage,
            'pieLabels' => $this->pieLabels,
            'pieData' => $this->pieData
        ]);
    }

    private function applyFilters($query) {
        if ($this->filterSection) $query->where('IDSection', $this->filterSection);
        if ($this->filterObj1) $query->where('IDObj1', $this->filterObj1);
        if ($this->filterObj2) $query->where('IDObj2', $this->filterObj2);
        if ($this->filterObj3) $query->where('IDObj3', $this->filterObj3);
        if ($this->filterObj4) $query->where('IDObj4', $this->filterObj4);
        if ($this->filterObj5) $query->where('IDObj5', $this->filterObj5);
    }
}; ?>

<div>
    @php
        $isRtl = app()->getLocale() == 'ar';
        $floatRight = $isRtl ? 'float-left' : 'float-right';
        $floatLeft = $isRtl ? 'float-right' : 'float-left';
        $textAlignLeft = $isRtl ? 'text-right' : 'text-left';
    @endphp

    @section('plugins.Chartjs', true)
    
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">
                        <i class="fas fa-tachometer-alt mr-2 text-primary"></i>{{ __('menu.dashboard') }}
                    </h1>
                </div>
                <div class="col-sm-6">
                    <div class="{{ $floatRight }} d-flex align-items-center">
                        <label class="mr-2 mb-0 text-muted">{{ __('menu.exercises') }} :</label>
                        <select wire:model.live="exercice" class="form-control form-control-sm font-weight-bold border-primary" style="width: 100px;">
                            @foreach($years as $y) <option value="{{ $y }}">{{ $y }}</option> @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BARRE DE FILTRES -->
    <div class="card collapsed-card mb-3 shadow-sm">
        <div class="card-header py-1">
            <h3 class="card-title" style="font-size: 1rem;"><i class="fas fa-filter text-muted mr-1"></i> {{ __('crud.filter_by') }}</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
            </div>
        </div>
        <div class="card-body p-2 bg-light">
            <div class="row">
                <div class="col-md-2">
                    <select wire:model.live="filterSection" class="form-control form-control-sm">
                        <option value="">{{ __('menu.sections') }}</option>
                        @foreach($sections as $s) <option value="{{ $s->IDSection }}">{{ $s->Num_section }}</option> @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="filterObj1" class="form-control form-control-sm" {{ empty($filterSection) ? 'disabled' : '' }}>
                        <option value="">{{ __('menu.chapters') }}</option>
                        @foreach($listeObj1 as $o1) <option value="{{ $o1->IDObj1 }}">{{ $o1->Num }}</option> @endforeach
                    </select>
                </div>
                @if($maxLevel >= 2)
                <div class="col-md-2">
                    <select wire:model.live="filterObj2" class="form-control form-control-sm" {{ empty($filterObj1) ? 'disabled' : '' }}>
                        <option value="">{{ __('menu.articles') }}</option>
                        @foreach($listeObj2 as $o2) <option value="{{ $o2->IDObj2 }}">{{ $o2->Num }}</option> @endforeach
                    </select>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- CHIFFRES CLÉS -->
    <div class="row">
        <!-- Budget Global -->
        <div class="col-lg-2 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    {{-- CORRECTION : Ajout de la virgule et des 2 décimales --}}
                    <h4 class="font-weight-bold" dir="ltr">{{ number_format($totalBudget, 2, ',', ' ') }}</h4>
                    <p>{{ __('operations.available') }}</p>
                </div>
                <div class="icon"><i class="fas fa-sack-dollar"></i></div>
                <a href="{{ route('ops.global') }}" class="small-box-footer"><i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- Engagements -->
        <div class="col-lg-2 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h4 class="font-weight-bold" dir="ltr">{{ number_format($totalEngage, 2, ',', ' ') }}</h4>
                    <p>{{ __('menu.expenses') }} ({{ $tauxConsommation }}%)</p>
                </div>
                <div class="icon"><i class="fas fa-file-signature"></i></div>
                <a href="{{ route('engagement.create') }}" class="small-box-footer"><i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- Mandats -->
        <div class="col-lg-2 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4 class="font-weight-bold" dir="ltr">{{ number_format($totalMandate, 2, ',', ' ') }}</h4>
                    <p>{{ __('menu.payment_mandates') }}</p>
                </div>
                <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <a href="{{ route('operations.mandat') }}" class="small-box-footer"><i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        
        <!-- Reste -->
        <div class="col-lg-2 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4 class="font-weight-bold" dir="ltr">{{ number_format($totalRestant, 2, ',', ' ') }}</h4>
                    <p>{{ __('operations.credit_available') }}</p>
                </div>
                <div class="icon"><i class="fas fa-chart-pie"></i></div>
            </div>
        </div>

        <!-- KPI : BC NON ENGAGÉS -->
        <div class="col-lg-2 col-12">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h4 class="font-weight-bold" dir="ltr">{{ number_format($totalBCNonEngage, 2, ',', ' ') }}</h4>
                    <p class="text-xs">BC Validés (Non Engagés)</p>
                </div>
                <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                <a href="{{ route('operations.bc') }}" class="small-box-footer"><i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <!-- GRAPHIQUES -->
    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title">Évolution des Dépenses ({{ $exercice }})</h3></div>
                <div class="card-body">
                    <canvas id="expensesChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-outline card-warning">
                <div class="card-header"><h3 class="card-title">Top Sections</h3></div>
                <div class="card-body">
                    <canvas id="sectionsChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- LISTES RÉCENTES -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header border-transparent">
                    <h3 class="card-title text-warning"><i class="fas fa-history mr-2"></i>Derniers Engagements</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table m-0 table-sm table-striped">
                            <thead>
                            <tr>
                                <th>N°</th>
                                <th>Objet</th>
                                <th>Section</th>
                                <th class="text-right">Montant</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($lastEngagements as $op)
                            <tr>
                                <td><span class="badge badge-warning">{{ $op->Num_operation }}</span></td>
                                <td>{{ Str::limit($op->designation, 30) }}</td>
                                <td><small>{{ $op->section->Num_section }}</small></td>
                                <td class="text-right font-weight-bold" dir="ltr">{{ number_format($op->Mont_operation, 2, ',', ' ') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted">Aucun engagement.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header border-transparent">
                    <h3 class="card-title text-success"><i class="fas fa-check-double mr-2"></i>Derniers Mandats</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table m-0 table-sm table-striped">
                            <thead>
                            <tr>
                                <th>N°</th>
                                <th>Date</th>
                                <th>Objet</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($lastMandats as $m)
                            <tr>
                                <td><span class="badge badge-success">{{ $m->Num_mandat }}</span></td>
                                <td>{{ \Carbon\Carbon::parse($m->date_mandate)->format('d/m/Y') }}</td>
                                <td>{{ Str::limit($m->designation, 40) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted">Aucun mandat.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SITUATION PAR SECTION (NOUVEAU TABLEAU) -->
    @if(!empty($sectionStats))
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-teal">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-building mr-2"></i> Situation Budgétaire par Section
                    </h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-striped table-valign-middle">
                        <thead>
                        <tr>
                            <th class="{{ $textAlignLeft }}">Section</th>
                            <th class="text-right">Budget Alloué</th>
                            <th class="text-right">Engagé</th>
                            <th class="text-center">Taux</th>
                            <th>Progression</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($sectionStats as $stat)
                            <tr>
                                <td class="font-weight-bold {{ $textAlignLeft }}">{{ $stat['label'] }}</td>
                                <td class="text-right" dir="ltr">{{ number_format($stat['budget'], 2, ',', ' ') }} DA</td>
                                <td class="text-right text-primary" dir="ltr">{{ number_format($stat['engage'], 2, ',', ' ') }} DA</td>
                                <td class="text-center">
                                    <span class="badge {{ $stat['ratio'] > 90 ? 'bg-danger' : ($stat['ratio'] > 50 ? 'bg-warning' : 'bg-success') }}">
                                        {{ number_format($stat['ratio'], 1) }}%
                                    </span>
                                </td>
                                <td>
                                    <div class="progress progress-xs">
                                        <div class="progress-bar {{ $stat['ratio'] > 90 ? 'bg-danger' : ($stat['ratio'] > 50 ? 'bg-warning' : 'bg-success') }}" style="width: {{ $stat['ratio'] }}%"></div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Scripts ChartJS -->
    @section('js')
    <script>
        document.addEventListener('livewire:initialized', () => {
            
            var ctxBar = document.getElementById('expensesChart').getContext('2d');
            var expensesChart = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Engagements (DA)',
                        backgroundColor: 'rgba(60, 141, 188, 0.9)',
                        borderColor: 'rgba(60, 141, 188, 0.8)',
                        data: @json($chartDataEngage)
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                callback: function(value) { return value.toLocaleString() + ' DA'; }
                            }
                        }]
                    }
                }
            });

            var ctxPie = document.getElementById('sectionsChart').getContext('2d');
            var sectionsChart = new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: @json($pieLabels),
                    datasets: [{
                        data: @json($pieData),
                        backgroundColor: ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de'],
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                }
            });

            Livewire.on('update-charts', (data) => {
                expensesChart.data.labels = data[0].labels;
                expensesChart.data.datasets[0].data = data[0].dataEngage;
                expensesChart.update();

                sectionsChart.data.labels = data[0].pieLabels;
                sectionsChart.data.datasets[0].data = data[0].pieData;
                sectionsChart.update();
            });
        });
    </script>
    @endsection
</div>