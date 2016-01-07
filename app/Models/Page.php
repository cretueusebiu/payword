<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['content', 'price'];

    /**
     * @var array
     */
    protected $casts = ['id' => 'integer', 'price' => 'integer' ];
}
