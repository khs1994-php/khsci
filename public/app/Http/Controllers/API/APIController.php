<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use KhsCI\Support\Response;

class APIController
{
    public function __invoke(): void
    {
        $host = getenv('CI_HOST');
        Response::json(['oauth' => [
            'coding' => $host.'/oauth/coding/login',
            'gitee' => $host.'/oauth/gitee/login',
            'github' => $host.'/oauth/github/login',
        ],
            'webhooks' => [
                'coding' => $host.'/webhooks/coding',
                'gitee' => $host.'/webhooks/gitee',
                'github' => $host.'/webhooks/github',
            ],
            'repo' => [
                'main' => $host.'/{git_type}/{user}/{repo}',
                'branches' => $host.'/{git_type}/{user}/{repo}/branches',
                'builds' => [
                    'main' => $host.'/{git_type}/{user}/{repo}/builds',
                    'id' => $host.'/{git_type}/{user}/{repo}/builds/{id}',
                ],
                'pull_requests' => $host.'/{git_type}/{user}/{repo}/builds',
                'settings' => $host.'/{git_type}/{user}/{repo}/settings',
                'caches' => $host.'/{git_type}/{user}/{repo}/caches',
            ],
            'queue' => [
                'coding' => '',
                'gitee' => '',
                'github' => '',
            ],
            'profile' => $host.'/profile/{user_org}',
            'dashboard' => $host.'/dashboard',
            'api' => $host.'/api',
            'about' => $host.'/about',
            'team' => $host.'/team',
            'blog' => $host.'/blog',
            'status' => $host.'/status',
            'feedback' => 'https://github.com/khs1994-php/khsci/issues',
        ]);
    }

    public function __call($name, $arguments): void
    {
        var_dump($name, $arguments);
    }
}
