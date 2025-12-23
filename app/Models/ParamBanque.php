<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParamBanque extends Model
{
    use HasFactory;

    protected $table = 'param_banq';
    protected $primaryKey = 'IDParam_banq';
    public $timestamps = false;

    protected $fillable = [
        'IDParam_banq',
        'Banq', // Nom de la banque
        'ABV'   // Abréviation (ex: BNA, CPA)
    ];
}