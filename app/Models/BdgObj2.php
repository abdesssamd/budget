<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BdgObj2 extends Model
{
    protected $table = 'bdg_obj2';
    protected $primaryKey = 'IDObj2';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'IDObj2','designation','Num','Creer_le','IDLogin','IDSection',
        'IDObj1','designation_ara','Reference','Observations','dep_recette',
        'Mt_genr','Mt_projet','Mt_projet_Nv','Mt_Total','EXERCICE','IDBdg_Compte'
    ];

    // Relations
    public function obj1()
    {
        return $this->belongsTo(BdgObj1::class, 'IDObj1');
    }

    public function obj3()
    {
        return $this->hasMany(BdgObj3::class, 'IDObj2');
    }

    public function recettes()
    {
        return $this->hasMany(BdgRecetteRest::class, 'IDObj2');
    }
}
