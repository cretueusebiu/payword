<?php

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'password' => bcrypt(str_random(10)),
        'remember_token' => str_random(10),
    ];
});

$factory->define(App\Models\Book::class, function (Faker\Generator $faker) {
    $name = $faker->name;
    $title = $faker->text(23);
    $title = substr($title, 0, strlen($title) - 1);
    $category = $faker->randomElement(['abstract', 'business', 'city', 'nightlife', 'nature', 'technics']);

    return [
        'title' => $title,
        'author' => $name,
        'cover' => $faker->imageUrl(320, 430, $category),
        'description' => $faker->text(500),
    ];
});

$factory->define(App\Models\Page::class, function (Faker\Generator $faker) {
    return [
        'price' => $faker->randomElement([1, 2, 3]),
        'content' => $faker->realText(2000),
    ];
});
