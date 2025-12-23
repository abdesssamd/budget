<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParamFonction extends Model
{
    use HasFactory;

    protected $table = 'param_fonction';
    protected $primaryKey = 'IDParam_fonction';
    public $timestamps = false;

    protected $fillable = ['designation'];
}