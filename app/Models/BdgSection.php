<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BdgSection extends Model
{
    use HasFactory;

    protected $table = 'bdg_section';
    protected $primaryKey = 'IDSection';
    public $timestamps = false;

    protected $fillable = [
        'IDSection',
        'Num_section',
        'NOM_section',
        'NOM_section_ara', // Nom en arabe
        'Estmateriel',     // Booléen (0 ou 1)
        'Creer_le'
    ];
}