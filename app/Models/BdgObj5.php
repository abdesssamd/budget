<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BdgObj5 extends Model
{
    use HasFactory;

    protected $table = 'bdg_obj5';
    protected $primaryKey = 'IDObj5';
    public $timestamps = false;

    protected $fillable = [
        'IDObj5',
        'IDObj4', // Clé étrangère vers OBJ4
        'designation', 
        'Num',
        'Reference',
        'Creer_le'
    ];

    public function obj4()
    {
        return $this->belongsTo(BdgObj4::class, 'IDObj4', 'IDObj4');
    }
}