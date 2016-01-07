<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commit extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['user_identity', 'commit', 'last_payword', 'page_number'];

    /**
     * @var array
     */
    protected $casts = ['id' => 'integer', 'page_number' => 'integer'];
}
