<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Onepoint extends Model
{
    /*
     * Table Name Specified
     */
    protected $table = 'one_to_point';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user', 'num', 'onein', 'point', 'oneout','updatecron'
    ];
}
