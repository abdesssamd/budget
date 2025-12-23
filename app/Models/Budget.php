<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $table = 'bdg_budget';
    protected $primaryKey = 'IDBudjet'; // Attention à l'orthographe SQL "Budjet"
    public $timestamps = false;

    protected $fillable = [
        'IDBudjet',
        'Reference',
        'designation',
        'EXERCICE', // Année (ex: 2025)
        'Archive',  // 0 ou 1
        'Creer_le'
    ];
}