<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StkTva extends Model
{
    protected $table = 'stk_tva';
    protected $primaryKey = 'TauxTVA';
    public $incrementing = false;
    protected $keyType = 'float';
    public $timestamps = false;

    protected $fillable = [
        'TauxTVA'
    ];
}
