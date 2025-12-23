<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BdgDetailOpBud extends Model
{
    protected $table = 'bdg_detail_op_bud';
    protected $primaryKey = 'IDDetail_op_bud';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'IDDetail_op_bud','Montant','designation','Observations','Creer_le',
        'IDLogin','IDOperation_Budg','IDMandat','NumFournisseur','mantant_mandat'
    ];

    // Relations
    public function operationBudg()
    {
        return $this->belongsTo(BdgOperationBudg::class, 'IDOperation_Budg');
    }

    public function mandat()
    {
        return $this->belongsTo(BdgMandat::class, 'IDMandat');
    }

    public function fournisseur()
    {
        return $this->belongsTo(StkFournisseur::class, 'NumFournisseur', 'NumFournisseur');
    }
}
