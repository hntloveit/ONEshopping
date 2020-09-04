<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    /*
     * Table Name Specified
     */
    protected $table = 'commission';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user', 'fromuser', 'onepoint_logid', 'point'
    ];
}
