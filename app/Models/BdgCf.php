<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BdgCf extends Model
{
    use HasFactory;

    protected $table = 'bdg_cf';
    protected $primaryKey = 'IDbdg_CF';
    
    // Par défaut, Laravel suppose que la clé primaire est incrémentée automatiquement.
    // D'après votre SQL (AUTO_INCREMENT), c'est correct.
    public $timestamps = false;

    protected $fillable = [
        'IDbdg_CF',
        'IDOperation_Budg',
        'Date_cf',      // Champ de l'ancien modèle
        'Date_envoi',
        'Date_retour',
        'VISA_cf',
        'Observations',
        'Photo',        // Champ de l'ancien modèle (BLOB)
        'scan_path',    // Nouveau champ pour le fichier (Chemin)
        'Creer_le',
        'IDLogin'
    ];

    // Relation vers l'opération budgétaire
    public function operation()
    {
        return $this->belongsTo(BdgOperationBudg::class, 'IDOperation_Budg', 'IDOperation_Budg');
    }

    // Alias pour compatibilité avec l'ancien code si nécessaire
    public function operationBudg()
    {
        return $this->operation();
    }

    // Relation vers l'utilisateur
    public function user()
    {
        return $this->belongsTo(User::class, 'IDLogin');
    }
}