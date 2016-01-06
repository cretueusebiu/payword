<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['public_key', 'credit_limit', 'expires_at'];

    /**
     * @var array
     */
    protected $dates = ['expires_at'];
}
