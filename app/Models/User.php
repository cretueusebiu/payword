<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /**
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'balance', 'api_token',
    ];

    /**
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'api_token',
    ];

    /**
     * @var array
     */
    protected $casts = ['id' => 'integer', 'balance' => 'integer' ];

    /**
     * @return float
     */
    public function balanceInDollars()
    {
        return $this->balance / 100;
    }
}
