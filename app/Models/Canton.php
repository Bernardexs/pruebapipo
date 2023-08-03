<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Canton extends Model
{
    use HasFactory;
    public $timestamps=false;
    protected $table = 'cantones';
     
     protected $fillable = [
        'canton','provincia_id','estado'
    ];
}
