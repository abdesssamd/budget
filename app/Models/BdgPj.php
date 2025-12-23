<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BdgPj extends Model
{
    use HasFactory;

    protected $table = 'bdg_pj';
    protected $primaryKey = 'ID_PJ';
    public $timestamps = false; // On gère created_at manuellement si besoin

    protected $fillable = [
        'IDOperation_Budg',
        'chemin_fichier',
        'nom_fichier',
        'created_at'
    ];

    public function operation()
    {
        return $this->belongsTo(BdgOperationBudg::class, 'IDOperation_Budg', 'IDOperation_Budg');
    }
}