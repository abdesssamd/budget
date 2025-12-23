<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BdgOperationBudg extends Model
{
    use HasFactory;

    protected $table = 'bdg_operation_budg';
    protected $primaryKey = 'IDOperation_Budg';
    public $timestamps = false;

    protected $fillable = [
        'IDOperation_Budg', 'Num_operation', 'designation', 'Mont_operation', 
        'Type_operation', 'EXERCICE', 'IDBudjet', 'IDSection', 
        'IDObj1', 'IDObj2', 'IDObj3', 'IDObj4', 'IDObj5', 
        'Creer_le', 'IDLogin', 'piece_jointe',
        'IDBON' // Nouveau champ (Pour le cas : BC avant Engagement)
    ];

    // Relations Budgétaires
    public function budget() { return $this->belongsTo(BdgBudget::class, 'IDBudjet', 'IDBudjet'); }
    public function section() { return $this->belongsTo(BdgSection::class, 'IDSection', 'IDSection'); }
    
    // Relations Nomenclature
    public function obj1() { return $this->belongsTo(BdgObj1::class, 'IDObj1', 'IDObj1'); }
    public function obj2() { return $this->belongsTo(BdgObj2::class, 'IDObj2', 'IDObj2'); }
    public function obj3() { return $this->belongsTo(BdgObj3::class, 'IDObj3', 'IDObj3'); }
    public function obj4() { return $this->belongsTo(BdgObj4::class, 'IDObj4', 'IDObj4'); }
    public function obj5() { return $this->belongsTo(BdgObj5::class, 'IDObj5', 'IDObj5'); }

    // Relation Visa CF
    public function cf() { return $this->hasOne(BdgCf::class, 'IDOperation_Budg', 'IDOperation_Budg'); }
    
    // Relation Pièces Jointes
    public function pjs() { return $this->hasMany(BdgPj::class, 'IDOperation_Budg', 'IDOperation_Budg'); }

    // --- GESTION HYBRIDE (LES DEUX SENS) ---

    // Cas 1 : BC avant Engagement (L'engagement pointe vers un BC parent)
    public function bonCommande() {
        return $this->belongsTo(StkBonCommande::class, 'IDBON', 'IDBON');
    }

    // Cas 2 : Engagement avant BC (L'ancien code : Un engagement a plusieurs BC enfants)
    public function bonsCommande() {
        return $this->hasMany(StkBonCommande::class, 'IDOperation_Budg', 'IDOperation_Budg');
    }
}