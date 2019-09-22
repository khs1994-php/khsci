<?php

declare(strict_types=1);

namespace PCIT\Support;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class Response extends BaseResponse
{
    const HTTP_CODE = [
        200,
        304,
        401,
        402,
        403,
        404,
        422,
        500,
    ];

    /**
     * @param array $content
     * @param float $startedAt
     *
     * @return false|string
     */
    public static function json(array $content)
    {
        if (\defined('PCIT_START')) {
            $time = microtime(true) - PCIT_START;
        }

        $code = $content['code'] ?? 200;

        if (\in_array($code, self::HTTP_CODE, true)) {
            http_response_code($code);

            unset($content['code']);
        }

        return new JsonResponse($content, $code, ['X-Runtime-rack' => $time]);
    }

    /**
     * @param string $url
     */
    public static function redirect(string $url): void
    {
        header('Location: '.$url);
        exit;
    }
}
