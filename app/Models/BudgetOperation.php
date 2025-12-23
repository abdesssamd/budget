<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetOperation extends Model
{
    protected $table = 'bdg_operation_budg';
    protected $primaryKey = 'IDOperation_Budg';
    public $timestamps = false; // MySQL gère le 'Creer_le'

    protected $fillable = [
        'Num_operation',
        'Date_operation',   // Assurez-vous d'avoir ce champ ou 'date_mandate' dans votre BDD, sinon retirez-le
        'designation',      // Objet de la dépense
        'Mont_operation',
        'IDObj1',           // Chapitre
        'IDObj2',           // Article
        'IDSection',
        'IDBudjet',
        'EXERCICE',
        'IDLogin'
    ];

    // Relation : Une opération appartient à un Chapitre
    public function chapitre()
    {
        return $this->belongsTo(BudgetNiveau1::class, 'IDObj1', 'IDObj1');
    }

    // Relation : Une opération appartient à un Article
    public function article()
    {
        return $this->belongsTo(BudgetNiveau2::class, 'IDObj2', 'IDObj2');
    }
}