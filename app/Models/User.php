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

    /**
     * Block money from balance.
     *
     * @param  int $amount
     * @return void
     */
    public function blockMoney($amount)
    {
        $this->decrement('balance', $amount);
        $this->increment('blocked_balance', $amount);
    }

    /**
     * Ublock money from balance.
     *
     * @param  int $amount
     * @return void
     */
    public function unblockMoney($amount)
    {
        $this->increment('balance', $amount);
        $this->decrement('blocked_balance', $amount);
    }

    /**
     * Withdraw blocked money.
     *
     * @param  int $amount
     * @return void
     */
    public function withdrawBlockedMoney($amount)
    {
        $this->decrement('blocked_balance', $amount);
    }

    /**
     * Deposit money.
     *
     * @param  int $amount
     * @return void
     */
    public function deposit($amount)
    {
        $this->increment('balance', $amount);
    }

    /**
     * Determinte if the user is vendor.
     *
     * @return bool
     */
    public function isVendor()
    {
        return $this->email == 'vendor@venor.payword.app';
    }
}
