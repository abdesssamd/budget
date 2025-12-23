<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StkUnite extends Model
{
    protected $table = 'stk_unite';
    protected $primaryKey = 'IDUnite';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'Libellé','ABV','Type','unitecoff'
    ];

    // Relations
    public function produits()
    {
        return $this->hasMany(StkProduit::class, 'IDUnite');
    }

    public function entrees()
    {
        return $this->hasMany(StkEntreeStock::class, 'IDUnite');
    }
}
