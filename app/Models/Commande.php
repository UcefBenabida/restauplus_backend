<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_server',
        'id_cook',
        'label',
        'table',
        'payed',
        'prepared',
        'state',
        'total',
        'date',
        'order_time',
        'cooked_time',
        'delivered_time',
        'payed_time',

    ];
}
