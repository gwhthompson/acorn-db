<?php

namespace AcornDB;

use Illuminate\Database\Eloquent\Factories\Factory as EloquentFactory;
use Faker\Generator;
use Illuminate\Support\Collection;

abstract class Factory extends EloquentFactory
{
    public function __construct(
        $count = null,
        ?Collection $states = null,
        ?Collection $has = null,
        ?Collection $for = null,
        ?Collection $afterMaking = null,
        ?Collection $afterCreating = null,
        $connection = null,
        ?Collection $recycle = null
    ) {
        parent::__construct($count, $states, $has, $for, $afterMaking, $afterCreating, $connection, $recycle);
    }

    public static function construct(Generator $faker, string $path): static
    {
        $factory = new static();
        $factory->withFaker($faker);
        $factory->load($path);
        return $factory;
    }

    protected function load(string $path): static
    {
        if (is_dir($path)) {
            foreach (glob($path . '/*.php') as $file) {
                require $file;
            }
        }

        return $this;
    }
}
