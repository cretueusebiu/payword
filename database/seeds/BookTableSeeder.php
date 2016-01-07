<?php

use Illuminate\Database\Seeder;

class BookTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Models\Book::class, 10)->create()->each(function ($book) {
            for ($i=0; $i < 10; $i++) {
                $book->pages()->save(factory(App\Models\Page::class)->make());
            }
        });
    }
}
