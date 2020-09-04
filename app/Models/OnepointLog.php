<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnepointLog extends Model
{
    /*
     * Table Name Specified
     */
    protected $table = 'onepoint_log';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user', 'point'
    ];
}
