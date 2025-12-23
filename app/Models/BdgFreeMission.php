<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BdgFreeMission extends Model
{
    protected $table = 'bdg_free_mission';
    protected $primaryKey = 'IDbdg_Free_mission';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'IDbdg_Free_mission','Reference','Date_mission','Num','b_Objet',
        'IDPersonel','IDparam_wilaya','Etablissemen_des','Itineraire',
        'date_depart','date_arrive','frais_sup','IDparam_Fo_Moyen_tronsport',
        'IDSection','IDObj1','IDObj2','IDObj3','IDObj4','IDObj5','IDBudjet',
        'MontantFM','MontantFM_total','Creer_le','IDLogin','IDExercice'
    ];

    // Relations
    public function section()
    {
        return $this->belongsTo(BdgSection::class, 'IDSection');
    }

    public function obj1() { return $this->belongsTo(BdgObj1::class, 'IDObj1'); }
    public function obj2() { return $this->belongsTo(BdgObj2::class, 'IDObj2'); }
    public function obj3() { return $this->belongsTo(BdgObj3::class, 'IDObj3'); }
    public function obj4() { return $this->belongsTo(BdgObj4::class, 'IDObj4'); }
    public function obj5() { return $this->belongsTo(BdgObj5::class, 'IDObj5'); }

    public function budget()
    {
        return $this->belongsTo(BdgBudget::class, 'IDBudjet');
    }
}
