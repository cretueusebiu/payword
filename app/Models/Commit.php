<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commit extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['user_identity', 'commit', 'last_payword', 'page_id'];

    /**
     * @var array
     */
    protected $casts = ['id' => 'integer', 'page_id' => 'integer'];

    /**
     * Find commits by user identity.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function findByUserIdentity($identity)
    {
        return static::where('user_identity', $identity)->get();
    }
}
