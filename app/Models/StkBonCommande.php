<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StkBonCommande extends Model
{
    use HasFactory;

    protected $table = 'stk_bon_commande';
    protected $primaryKey = 'IDBON';
    public $timestamps = false;

    protected $fillable = [
        'IDBON', 'Num_bon', 'date', 'designation', 'NumFournisseur',
        'prixtotal', 'valider', 'Etat_commande', 'IDExercice',
        'Type_bon', 'SaisiPar', 'SaisiLe', 'Observations'
        // On retire IDOperation_Budg car le lien est inversé
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->Num_bon)) {
                $year = $model->IDExercice ?? date('Y');
                $count = static::where('IDExercice', $year)->count() + 1;
                $model->Num_bon = $year . '/' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function fournisseur() { return $this->belongsTo(StkFournisseur::class, 'NumFournisseur', 'NumFournisseur'); }
    public function pjs() { return $this->hasMany(StkBonCommandePj::class, 'IDBON', 'IDBON'); }
    
    // Relation pour voir si ce BC a été engagé
    public function engagement() {
        return $this->hasOne(BdgOperationBudg::class, 'IDBON', 'IDBON');
    }
}