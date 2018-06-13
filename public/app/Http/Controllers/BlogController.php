<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class BlogController
{
    public function __invoke()
    {
        return [
            'code' => 200,
            'data' => 'https://ci.khs1994.com/blog',
        ];
    }
}
