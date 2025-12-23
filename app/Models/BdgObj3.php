<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BdgObj3 extends Model
{
    use HasFactory;

    protected $table = 'bdg_obj3';
    protected $primaryKey = 'IDObj3';
    public $timestamps = false; // "Creer_le" est géré par la BDD ou manuellement

    protected $fillable = [
        'IDObj3', // Auto-increment géré par la BDD normalement, mais on le garde fillable au cas où
        'IDObj2', // Clé étrangère
        'designation', 
        'Num',
        'Reference',
        'Creer_le'
    ];

    // Relation : Un OBJ3 appartient à un OBJ2
    public function obj2()
    {
        return $this->belongsTo(BdgObj2::class, 'IDObj2', 'IDObj2');
    }
}