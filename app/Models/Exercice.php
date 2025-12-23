<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exercice extends Model
{
    protected $table = 'stk_Exercice';
    protected $primaryKey = 'IDExercice';
    public $timestamps = false;

    protected $fillable = ['Libellé', 'anne', 'Ouvert'];
}