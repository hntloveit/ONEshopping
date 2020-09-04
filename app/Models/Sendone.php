<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sendone extends Model
{
    /*
     * Table Name Specified
     */
    protected $table = 'sendone';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fromuser', 'touser', 'one', 'code'
    ];
}
