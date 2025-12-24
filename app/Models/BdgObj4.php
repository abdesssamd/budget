<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BdgObj4 extends Model
{
    use HasFactory;

    protected $table = 'bdg_obj4';
    protected $primaryKey = 'IDObj4';
    public $timestamps = false;

    protected $fillable = [
        
        'IDObj3', // Clé étrangère vers OBJ3
        'designation', 
        'Num',
        'Reference',
        'Creer_le'
    ];

    // Relation : Un OBJ4 appartient à un OBJ3
    public function obj3()
    {
        return $this->belongsTo(BdgObj3::class, 'IDObj3', 'IDObj3');
    }
}