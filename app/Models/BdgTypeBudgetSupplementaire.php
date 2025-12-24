<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BdgTypeBudgetSupplementaire extends Model
{
    protected $table = 'bdg_type_budget_supplementaire';
    protected $primaryKey = 'IDType';

    protected $fillable = [
        'Code',
        'Designation',
        'Designation_ara',
        'Description',
        'Actif',
        'Ordre',
    ];

    protected $casts = [
        'Actif' => 'boolean',
    ];

    /**
     * Scope pour récupérer seulement les types actifs
     */
    public function scopeActif($query)
    {
        return $query->where('Actif', true)->orderBy('Ordre');
    }

    /**
     * Obtenir la désignation selon la langue
     */
    public function getDesignationLocalized()
    {
        $locale = app()->getLocale();
        
        if ($locale === 'ar' && $this->Designation_ara) {
            return $this->Designation_ara;
        }
        
        return $this->Designation;
    }
}
