<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StkFournisseur extends Model
{
    protected $table = 'stk_fournisseur';
    protected $primaryKey = 'NumFournisseur';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'Nom','Societe','Adresse','Telephone','Fax','EMail','Pays','Mobile',
        'Observations','CodePostal','Ville','Civilite','Prénom',
        'num_carte_fiscale','num_registre_commerce','NIS'
    ];

    // Relations
    public function bons()
    {
        return $this->hasMany(StkBonCommande::class, 'NumFournisseur');
    }

    public function entrees()
    {
        return $this->hasMany(StkEntreeStock::class, 'NumFournisseur');
    }


      // Accesseur pour le nom complet
    public function getNomCompletAttribute()
    {
        return $this->Societe ? $this->Societe : ($this->Nom . ' ' . $this->Prénom);
    }

    public function factures()
    {
        return $this->hasMany(BdgFacture::class, 'NumFournisseur');
    }

    public function operationsRecettes()
    {
        return $this->hasMany(BdgOperationRecette::class, 'NumFournisseur');
    }
}
