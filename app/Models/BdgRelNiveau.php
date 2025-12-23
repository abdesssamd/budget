<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BdgRelNiveau extends Model
{
    protected $table = 'bdg_rel_niveau';
    protected $primaryKey = 'IDbdg_rel_niveau';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'IDbdg_rel_niveau','idbdg_niveau1','idbdg_niveau2','Creer_le','IDLogin',
        'dep_recette','niveau','Mt_genr','Mt_Total','Mt_projet',
        'Mt_projet_Nv','IDBdg_Compte'
    ];

    // Relations
    public function compte()
    {
        return $this->belongsTo(BdgCompte::class, 'IDBdg_Compte');
    }

    public function recettes()
    {
        return $this->hasMany(BdgRecetteRest::class, 'IDbdg_rel_niveau');
    }

    public function operationsRest()
    {
        return $this->hasMany(BdgOperationRest::class, 'IDbdg_rel_niveau');
    }

    public function operationsBudg()
    {
        return $this->hasMany(BdgOperationBudg::class, 'IDbdg_rel_niveau');
    }

    public function operationsRecette()
    {
        return $this->hasMany(BdgOperationRecette::class, 'IDbdg_rel_niveau');
    }
}
