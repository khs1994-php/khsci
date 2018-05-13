<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use Exception;
use KhsCI\KhsCI;
use KhsCI\Support\Cache;

class GitHubController
{
    /**
     * @throws Exception
     */
    public function __invoke()
    {
        $khsci = new KhsCI();

        $khsci->webhooks->github();
    }

    /**
     * @throws Exception
     */
    public function githubApp()
    {
        $khsci = new KhsCI();

        $khsci->webhooks->github_app();
    }
}
