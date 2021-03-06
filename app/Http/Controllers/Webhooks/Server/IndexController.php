<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks\Server;

use PCIT\Framework\Attributes\Route;
use PCIT\Framework\Support\StringSupport;
use PCIT\PCIT;

class IndexController
{
    #[Route('post', 'webhooks/${git_type}')]
    public function __invoke($gitType)
    {
        // custom_provider
        $provider = StringSupport::camelize($gitType);

        // CustomProvider
        $class = 'PCIT\Provider\\'.ucfirst($provider).'\\WebhooksServer';

        if (class_exists($class)) {
            return (new $class())->server();
        }

        /** @var \PCIT\PCIT */
        $pcit = app(PCIT::class)->git($gitType);

        $result = $pcit->webhooks->server();

        return [$result];
    }
}
