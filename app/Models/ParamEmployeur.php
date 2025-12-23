<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParamEmployeur extends Model
{
    use HasFactory;

    protected $table = 'param_employeur';
    protected $primaryKey = 'IDParam_Employeur';
    public $timestamps = false;

    protected $fillable = [
        'Code',
        'designation',
        'ABV', // Abréviation
        'Jour_veressement',
        'Adresse',
        'Tel',
        'Fax',
        'EMail',
        'Observations'
    ];
}