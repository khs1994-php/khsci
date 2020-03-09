<?php

declare(strict_types=1);

namespace App\Http\Controllers\WeChat;

use PCIT\PCIT;

class MessageServer
{
    /**
     * @return array|string|null
     *
     * @throws \Exception
     */
    public function __invoke(PCIT $pcit)
    {
        return $pcit->wechat->server->pushHandler(function ($message) {
            return null;
        })->register();
    }
}
