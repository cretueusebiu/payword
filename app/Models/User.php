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

    /**
     * @return float
     */
    public function blockedBalanceInDollars()
    {
        return $this->blocked_balance / 100;
    }

    public function blockMoney($amount)
    {
        $this->decrement('balance', $amount);
        $this->increment('blocked_balance', $amount);
    }

    public function unblockMoney($amount)
    {
        $this->increment('balance', $amount);
        $this->decrement('blocked_balance', $amount);
    }

    public function withdrawBlockedMoney($amount)
    {
        $this->decrement('blocked_balance', $amount);
    }

    public function deposit($amount)
    {
        $this->increment('balance', $amount);
    }

    public function isVendor()
    {
        return $this->email == 'vendor@venor.payword.app';
    }
}
