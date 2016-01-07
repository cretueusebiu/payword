<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    /**
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
    public function price()
    {
        return $this->pages()
                    ->selectRaw('book_id, sum(price) as aggregate')
                    ->groupBy('book_id');
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function prices()
    {
        return $this->pages()
                    ->selectRaw('book_id, price, count(price) as count')
                    ->groupBy('price');
    }

    /**
     * Get the book total price.
     *
     * @return int
     */
    public function getPrice()
    {
        return $this->pages->sum('price');
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $attributes = parent::toArray();

        if (isset($attributes['price'])) {
            $attributes['price'] = (int) $attributes['price'][0]['aggregate'];
        }

        if (isset($attributes['prices'])) {
            $prices = [];

            foreach ($attributes['prices'] as $price) {
                $prices[$price['price']] = $price['count'];
            }

            $attributes['prices'] = $prices;

            $attributes['price'] = array_sum(array_map(function ($price) {
                return $price;
            }, $prices));
        }

        return $attributes;
    }
}
