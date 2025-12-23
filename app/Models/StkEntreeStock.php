<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StkEntreeStock extends Model
{
    protected $table = 'stk_entree_stock'; // PAS d'accent !
    protected $primaryKey = 'IDEntree';
    public $incrementing = false; // bigint au lieu d’incrément ?
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'Reference','PrixAchat','Quantite','DateAppro','IDEntree','Observations',
        'SaisiPar','SaisiLe','Ver_tva','TauxTVA','PrixHT','Qunite_unitaire',
        'prixUnitHt','IDBON','Date_perom','IDUnite','Idunite_type','Retenu_Tva',
        'MOuv_change','id_produit','neuf','IDmarque','FraisTransport',
        'FraisTransportUni'
    ];

    // Relations
    public function produit()
    {
        return $this->belongsTo(StkProduit::class, 'id_produit');
    }

    public function unite()
    {
        return $this->belongsTo(StkUnite::class, 'IDUnite');
    }

    public function bonCommande()
    {
        return $this->belongsTo(StkBonCommande::class, 'IDBON');
    }
}
