<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StkProduit extends Model
{
    protected $table = 'stk_produit';
    protected $primaryKey = 'id_produit';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'LibProd','Photo','QteReappro','QteMini','Reference','Description',
        'PlusAuCatalogue','GenCode','CodeBarre','SaisiPar','SaisiLe','CodeFamille',
        'CodePort','Ver_perime','Ver_condition','unite','Stock_Sec','IDUnite',
        'Ver_immo','IDFamille_Prod','Ver_tva','TauxTVA','IDmagasin','Archive',
        'ver_balance'
    ];

    // Relations
    public function unite()
    {
        return $this->belongsTo(StkUnite::class, 'IDUnite');
    }

    public function entrees()
    {
        return $this->hasMany(StkEntreeStock::class, 'id_produit');
    }
}
