<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BdgOperationRest extends Model
{
    protected $table = 'bdg_operation_rest';
    protected $primaryKey = 'IDre_Operation_Budg';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'IDre_Operation_Budg','Num_operation','Creer_le','IDLogin','IDObj1','IDObj2',
        'IDObj3','IDObj4','IDbdg_rel_niveau','IDObj5','EXERCICE','IDBudjet','IDSection',
        'Mt_genr','Mt_projet','Mt_projet_Nv','Mt_Total','niveau','Mt_Budget_total',
        'Mt_Budget_sup','Mt_projet_sup','Num_BS','Num_PS'
    ];

    // Relations
    public function obj1() { return $this->belongsTo(BdgObj1::class, 'IDObj1'); }
    public function obj2() { return $this->belongsTo(BdgObj2::class, 'IDObj2'); }
    public function obj3() { return $this->belongsTo(BdgObj3::class, 'IDObj3'); }
    public function obj4() { return $this->belongsTo(BdgObj4::class, 'IDObj4'); }
    public function obj5() { return $this->belongsTo(BdgObj5::class, 'IDObj5'); }

    public function budget()
    {
        return $this->belongsTo(BdgBudget::class, 'IDBudjet');
    }

    public function section()
    {
        return $this->belongsTo(BdgSection::class, 'IDSection');
    }

    public function niveauRelation()
    {
        return $this->belongsTo(BdgRelNiveau::class, 'IDbdg_rel_niveau');
    }
}
