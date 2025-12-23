<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BdgCompte extends Model
{
    use HasFactory;

    protected $table = 'bdg_compte';
    protected $primaryKey = 'IDBdg_Compte';
    public $timestamps = false;

    protected $fillable = [
        'IDBdg_Compte',
        'Num_Compte',
        'designation',
        'EXERCICE',    // Année
        'dep_recette', // 0 = Dépense, 1 = Recette
        'Creer_le'
    ];
}