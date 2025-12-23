<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BdgFacture extends Model
{
    use HasFactory;

    protected $table = 'bdg_facture';
    protected $primaryKey = 'IDbdg_facture';
    
    // Auto-Increment standard
    public $timestamps = false;

    protected $fillable = [
        'IDbdg_facture',
        'Reference',
        'num_facture',
        'date_facture',
        'Montant',
        'Observations',
        'NumFournisseur',
        'IDOperation_Budg', 
        'IDMandat',         
        'IDBudjet', 
        'IDSection',
        'IDExercice',       
        'IDBON',            
        'Type',
        'id_detail_operation', 
        'IDObj1', 'IDObj2', 'IDObj3', 'IDObj4', 'IDObj5'
    ];

    // --- RELATIONS ---

    public function engagement() {
        return $this->belongsTo(BdgOperationBudg::class, 'IDOperation_Budg', 'IDOperation_Budg');
    }
    
    public function operationBudg() {
        return $this->engagement();
    }

    public function fournisseur() {
        // CORRECTION : Utilisation de StkFournisseur
        return $this->belongsTo(StkFournisseur::class, 'NumFournisseur', 'NumFournisseur');
    }
    
    public function mandat() {
        return $this->belongsTo(BdgMandat::class, 'IDMandat', 'IDMandat');
    }

    public function budget() {
        return $this->belongsTo(BdgBudget::class, 'IDBudjet', 'IDBudjet');
    }

    public function obj1() { return $this->belongsTo(BdgObj1::class, 'IDObj1', 'IDObj1'); }
    public function obj2() { return $this->belongsTo(BdgObj2::class, 'IDObj2', 'IDObj2'); }
    public function obj3() { return $this->belongsTo(BdgObj3::class, 'IDObj3', 'IDObj3'); }
    public function obj4() { return $this->belongsTo(BdgObj4::class, 'IDObj4', 'IDObj4'); }
    public function obj5() { return $this->belongsTo(BdgObj5::class, 'IDObj5', 'IDObj5'); }

    public function bonCommande() {
        return null; 
    }
}