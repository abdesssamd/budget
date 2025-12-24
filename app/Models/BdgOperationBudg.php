<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BdgOperationBudg extends Model
{
    protected $table = 'bdg_operation_budg';
    protected $primaryKey = 'IDOperation_Budg';
    public $timestamps = false;

    protected $fillable = [
        'Montant_anc',
        'Num_operation',
        'Creer_le',
        'IDLogin',
        'IDObj1',
        'IDObj2',
        'IDObj3',
        'IDObj4',
        'IDObj5',
        'Type_operation',
        'type_incorp',
        'decouvert',
        'IDbdg_rel_niveau',
        'Mont_operation',
        'IDBON',
        'designation',
        'EXERCICE',
        'IDBudjet',
        'IDSection',
        'Observations',
    ];

    protected $casts = [
        'Creer_le' => 'datetime',
        'Mont_operation' => 'decimal:6',
    ];

    // ==========================================
    // RELATIONS EXISTANTES
    // ==========================================

    public function budget(): BelongsTo
    {
        return $this->belongsTo(BdgBudget::class, 'IDBudjet', 'IDBudjet');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(BdgSection::class, 'IDSection', 'IDSection');
    }

    public function obj1(): BelongsTo
    {
        return $this->belongsTo(BdgObj1::class, 'IDObj1', 'IDObj1');
    }

    public function obj2(): BelongsTo
    {
        return $this->belongsTo(BdgObj2::class, 'IDObj2', 'IDObj2');
    }

    public function obj3(): BelongsTo
    {
        return $this->belongsTo(BdgObj3::class, 'IDObj3', 'IDObj3');
    }

    public function obj4(): BelongsTo
    {
        return $this->belongsTo(BdgObj4::class, 'IDObj4', 'IDObj4');
    }

    public function obj5(): BelongsTo
    {
        return $this->belongsTo(BdgObj5::class, 'IDObj5', 'IDObj5');
    }

    public function cf(): HasOne
    {
        return $this->hasOne(BdgCf::class, 'IDOperation_Budg', 'IDOperation_Budg');
    }

    public function factures(): HasMany
    {
        return $this->hasMany(BdgFacture::class, 'IDOperation_Budg', 'IDOperation_Budg');
    }

    public function bonCommande(): BelongsTo
    {
        return $this->belongsTo(StkBonCommande::class, 'IDBON', 'IDBON');
    }

    // ==========================================
    // NOUVELLES RELATIONS POUR BUDGETS SUPPLÉMENTAIRES
    // ==========================================

    /**
     * Relation vers l'opération source (pour les répartitions)
     * IDbdg_rel_niveau pointe vers l'incorporation dont on répartit les crédits
     */
    public function operationSource(): BelongsTo
    {
        return $this->belongsTo(BdgOperationBudg::class, 'IDbdg_rel_niveau', 'IDOperation_Budg');
    }

    /**
     * Relation inverse : les répartitions issues de cette incorporation
     */
    public function repartitions(): HasMany
    {
        return $this->hasMany(BdgOperationBudg::class, 'IDbdg_rel_niveau', 'IDOperation_Budg');
    }

    /**
     * Historique des répartitions (comme source)
     */
    public function historiqueRepartitionsSource(): HasMany
    {
        return $this->hasMany(BdgRepartitionHistory::class, 'IDOperation_Source', 'IDOperation_Budg');
    }

    /**
     * Historique des répartitions (comme cible)
     */
    public function historiqueRepartitionsCible(): HasMany
    {
        return $this->hasMany(BdgRepartitionHistory::class, 'IDOperation_Cible', 'IDOperation_Budg');
    }

    // ==========================================
    // SCOPES UTILES
    // ==========================================

    /**
     * Scope pour les incorporations
     */
    public function scopeIncorporations($query)
    {
        return $query->where('Type_operation', 1);
    }

    /**
     * Scope pour les budgets primitifs
     */
    public function scopePrimitif($query)
    {
        return $query->where('Type_operation', 1)
                     ->where('designation', 'LIKE', '%Primitif%');
    }

    /**
     * Scope pour les budgets supplémentaires
     */
    public function scopeSupplementaires($query)
    {
        return $query->where('Type_operation', 1)
                     ->where('designation', 'NOT LIKE', '%Primitif%');
    }

    /**
     * Scope pour les répartitions
     */
    public function scopeRepartitions($query)
    {
        return $query->where('Type_operation', 2);
    }

    /**
     * Scope pour les engagements
     */
    public function scopeEngagements($query)
    {
        return $query->where('Type_operation', 3);
    }

    /**
     * Scope par exercice
     */
    public function scopeExercice($query, $exercice)
    {
        return $query->where('EXERCICE', $exercice);
    }

    // ==========================================
    // MÉTHODES UTILITAIRES
    // ==========================================

    /**
     * Calculer le montant déjà réparti
     */
    public function getMontantRepartiAttribute()
    {
        return $this->repartitions()->sum('Mont_operation');
    }

    /**
     * Calculer le montant disponible pour répartition
     */
    public function getMontantDisponibleAttribute()
    {
        return $this->Mont_operation - $this->montant_reparti;
    }

    /**
     * Vérifier si c'est un budget supplémentaire
     */
    public function estBudgetSupplementaire(): bool
    {
        return $this->Type_operation == 1 && 
               !str_contains(strtolower($this->designation), 'primitif');
    }

    /**
     * Obtenir le type de budget supplémentaire
     */
    public function getTypeBudgetSupplementaire(): ?string
    {
        if (!$this->estBudgetSupplementaire()) {
            return null;
        }

        $designation = strtolower($this->designation);
        
        if (str_contains($designation, 'supplémentaire')) return 'supplementaire';
        if (str_contains($designation, 'rectificatif')) return 'rectificatif';
        if (str_contains($designation, 'virement')) return 'virement';
        if (str_contains($designation, 'report')) return 'report';
        
        return 'supplementaire'; // Par défaut
    }

    /**
     * Obtenir les informations de la décision (depuis JSON Observations)
     */
    public function getInfosDecision(): array
    {
        if (!$this->Observations) {
            return [];
        }

        try {
            $data = json_decode($this->Observations, true);
            return $data ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Vérifier si la répartition est complète
     */
    public function estRepartitionComplete(): bool
    {
        if ($this->Type_operation != 1) {
            return false;
        }

        return $this->montant_disponible <= 0;
    }

    /**
     * Obtenir le pourcentage de répartition
     */
    public function getPourcentageRepartition(): float
    {
        if ($this->Mont_operation <= 0) {
            return 0;
        }

        return ($this->montant_reparti / $this->Mont_operation) * 100;
    }
}
