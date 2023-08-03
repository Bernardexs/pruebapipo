<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecintoElectoral extends Model
{
    use HasFactory;
    public $timestamps=false;
    protected $table = 'recintoselectorales';
     
     protected $fillable = [
        'recinto','parroquia_id','estado'
    ];
}
