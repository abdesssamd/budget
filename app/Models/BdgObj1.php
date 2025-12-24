<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BdgObj1 extends Model
{
    protected $table = 'bdg_obj1';
    protected $primaryKey = 'IDObj1';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'designation','Num','Creer_le','IDLogin','IDSection',
        'designation_ara','Reference','Observations','dep_recette','Mt_genr',
        'Mt_projet','Mt_projet_Nv','Mt_Total','EXERCICE'
    ];

    // Relations
    public function section()
    {
        return $this->belongsTo(BdgSection::class, 'IDSection');
    }

    public function obj2()
    {
        return $this->hasMany(BdgObj2::class, 'IDObj1');
    }

    public function recettes()
    {
        return $this->hasMany(BdgRecetteRest::class, 'IDObj1');
    }
}
