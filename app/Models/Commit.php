<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commit extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['user_identity', 'commit', 'last_payword', 'page_id', 'book_id'];

    /**
     * @var array
     */
    protected $casts = ['id' => 'integer', 'page_id' => 'integer', 'book_id' => 'integer'];

    /**
     * Find commits by user identity.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function findByUserIdentity($identity, $bookId)
    {
        return static::where('user_identity', $identity)
                ->where('book_id', $bookId)
                ->get();
    }
}
