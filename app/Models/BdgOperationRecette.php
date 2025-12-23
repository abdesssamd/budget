<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BdgOperationRecette extends Model
{
    use HasFactory;

    protected $table = 'bdg_operation_recette';
    protected $primaryKey = 'IDOperation_Budg';
    
    // On garde ceci car la base de données ne gère pas l'auto-incrément pour cette table
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'IDOperation_Budg', 
        'Num_operation', 
        'designation', 
        'Mont_operation', 
        'EXERCICE', 
        'IDBudjet', 
        'IDSection',
        'IDObj1', 'IDObj2', 'IDObj3', 'IDObj4', 'IDObj5',
        'date_perception', 
        'NumFournisseur',
        'Observations',
        'Creer_le', 
        'IDLogin',
        
        // Anciens champs conservés
        'Montant_anc',
        'Montant_verser',
        'A_compter',
        'IDbdg_rel_niveau'
    ];

    /**
     * Boot : Génération automatique de l'ID et du Numéro
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            // 1. Génération ID Primaire (Max + 1)
            // C'est ici que se fait l'ID Automatique manuel
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (int) static::max($model->getKeyName()) + 1;
            }

            // 2. Génération Numéro Opération si vide
            if (empty($model->Num_operation)) {
                $model->Num_operation = rand(100000, 999999);
            }
        });
    }

    // --- RELATIONS ---

    public function obj1() { return $this->belongsTo(BdgObj1::class, 'IDObj1', 'IDObj1'); }
    public function obj2() { return $this->belongsTo(BdgObj2::class, 'IDObj2', 'IDObj2'); }
    public function obj3() { return $this->belongsTo(BdgObj3::class, 'IDObj3', 'IDObj3'); }
    public function obj4() { return $this->belongsTo(BdgObj4::class, 'IDObj4', 'IDObj4'); }
    public function obj5() { return $this->belongsTo(BdgObj5::class, 'IDObj5', 'IDObj5'); }

    public function budget() {
        return $this->belongsTo(BdgBudget::class, 'IDBudjet', 'IDBudjet');
    }

    public function section() {
        return $this->belongsTo(BdgSection::class, 'IDSection', 'IDSection');
    }

    public function niveauRelation() {
        return $this->belongsTo(BdgRelNiveau::class, 'IDbdg_rel_niveau');
    }

    // Tiers (Organisme payeur)
    public function fournisseur() {
        return $this->belongsTo(StkFournisseur::class, 'NumFournisseur', 'NumFournisseur');
    }
    
    // Alias pour la vue
    public function tiers() {
        return $this->fournisseur();
    }
}