<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtreanModel extends Model
{
    use HasFactory;
    protected $connection = 'atrean_pgsql';
    protected $table = 'antriansoal';
    protected $primaryKey = 'nomorkartu';
    // protected $fillable = [
    //     'employee_number',
    //     'position',
    //     'name',
    //     'phone_number',
    //     'email'
    // ];
}
