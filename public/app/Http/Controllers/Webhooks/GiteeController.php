<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use Exception;
use KhsCI\KhsCI;

class GiteeController
{
    /**
     * @throws Exception
     */
    public function __invoke()
    {
        $khsci = new KhsCI([],'gitee');

        $output = $khsci->webhooks->Server();

        return [$output];
    }
}
