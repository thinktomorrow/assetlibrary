<?php

use Faker\Generator as Faker;
use Thinktomorrow\AssetLibrary\Test\stubs\Article;
use Thinktomorrow\AssetLibrary\Models\Asset;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Article::class, function (Faker $faker) {
    return [
    ];
});
