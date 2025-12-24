<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BdgRepartitionHistory extends Model
{
    protected $table = 'bdg_repartition_history';
    protected $primaryKey = 'IDHistory';

    protected $fillable = [
        'IDOperation_Source',
        'IDOperation_Cible',
        'Montant_reparti',
        'Type_source',
        'Date_repartition',
        'IDLogin',
        'Commentaire',
    ];

    protected $casts = [
        'Date_repartition' => 'datetime',
        'Montant_reparti' => 'decimal:6',
    ];

    /**
     * Relation vers l'opération source (budget supplémentaire)
     */
    public function operationSource(): BelongsTo
    {
        return $this->belongsTo(BdgOperationBudg::class, 'IDOperation_Source', 'IDOperation_Budg');
    }

    /**
     * Relation vers l'opération cible (répartition)
     */
    public function operationCible(): BelongsTo
    {
        return $this->belongsTo(BdgOperationBudg::class, 'IDOperation_Cible', 'IDOperation_Budg');
    }

    /**
     * Relation vers l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'IDLogin');
    }
}
