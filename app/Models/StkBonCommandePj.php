<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StkBonCommandePj extends Model
{
    use HasFactory;

    protected $table = 'stk_bon_commande_pj';
    protected $primaryKey = 'ID_PJ';
    public $timestamps = false;

    protected $fillable = [
        'IDBON',
        'chemin_fichier',
        'nom_fichier',
        'created_at'
    ];

    public function bonCommande()
    {
        return $this->belongsTo(StkBonCommande::class, 'IDBON', 'IDBON');
    }
}