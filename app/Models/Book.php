<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'author', 'cover', 'description'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function pagesPrice()
    {
        return $this->pages()->selectRaw('sum(price) as total_price');
    }

    /**
     * Get the book total price.
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->pages->sum('price');
    }
}
