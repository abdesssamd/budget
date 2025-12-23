<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BdgMandat extends Model
{
    use HasFactory;

    protected $table = 'bdg_mandat';
    protected $primaryKey = 'IDMandat';
    public $timestamps = false;

    protected $fillable = [
        'IDMandat', 
        'Num_mandat', 
        'date_mandate', 
        'designation',
        'NumFournisseur', 
        'EXERCICE', 
        'IDBudjet', 
        'IDSection',
        'IDObj1', 'IDObj2', 'IDObj3', 'IDObj4', 'IDObj5',
        'Creer_le', 'IDLogin',
        'Date_envoi',
        'Date_retour',
        'Document_jointe'
    ];

    // --- RELATIONS ---

    public function budget() {
        return $this->belongsTo(BdgBudget::class, 'IDBudjet', 'IDBudjet');
    }

    public function section() {
        return $this->belongsTo(BdgSection::class, 'IDSection', 'IDSection');
    }
    
    public function obj1() { return $this->belongsTo(BdgObj1::class, 'IDObj1', 'IDObj1'); }
    public function obj2() { return $this->belongsTo(BdgObj2::class, 'IDObj2', 'IDObj2'); }
    public function obj3() { return $this->belongsTo(BdgObj3::class, 'IDObj3', 'IDObj3'); }
    public function obj4() { return $this->belongsTo(BdgObj4::class, 'IDObj4', 'IDObj4'); }
    public function obj5() { return $this->belongsTo(BdgObj5::class, 'IDObj5', 'IDObj5'); }

    public function fournisseur() {
        // CORRECTION : Utilisation de StkFournisseur
        return $this->belongsTo(StkFournisseur::class, 'NumFournisseur', 'NumFournisseur');
    }

    public function details() {
        return $this->hasMany(BdgDetailOpBud::class, 'IDMandat', 'IDMandat');
    }
}