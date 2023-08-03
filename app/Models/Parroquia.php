<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parroquia extends Model
{
    use HasFactory;
    public $timestamps=false;
    protected $table = 'parroquias';
     
     protected $fillable = [
        'parroquia','canton_id','estado'
    ];
}
