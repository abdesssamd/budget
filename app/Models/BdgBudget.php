<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BdgBudget extends Model
{
    protected $table = 'bdg_budget';
    protected $primaryKey = 'IDBudjet';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

   protected $fillable = [
        'IDBudjet', 'Reference', 'designation', 'EXERCICE', 'Archive', 'Creer_le', 'IDLogin',
        'Montant_Global',  // <--- Nouveau
        'Montant_Restant'  // <--- Nouveau
    ];

    // Relations
    public function recettesRest()
    {
        return $this->hasMany(BdgRecetteRest::class, 'IDBudjet');
    }

    public function operationsRest()
    {
        return $this->hasMany(BdgOperationRest::class, 'IDBudjet');
    }

    public function operationsBudg()
    {
        return $this->hasMany(BdgOperationBudg::class, 'IDBudjet');
    }

    public function operationsRecette()
    {
        return $this->hasMany(BdgOperationRecette::class, 'IDBudjet');
    }

    public function factures()
    {
        return $this->hasMany(BdgFacture::class, 'IDBudjet');
    }
}
