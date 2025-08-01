<?php

// app/Models/DiaAntiguedad.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiaAntiguedad extends Model
{
    use HasFactory;

    protected $table = 'dias_antiguedad';
    protected $fillable = ['antiguedad_min', 
                            'antiguedad_max', 
                            'dias'
                          ];
}