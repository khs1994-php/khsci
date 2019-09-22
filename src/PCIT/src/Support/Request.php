<?php

declare(strict_types=1);

namespace PCIT\Support;

class Request extends \Symfony\Component\HttpFoundation\Request
{
    public static function getAllHeaders()
    {
        if (!\function_exists('getallheaders')) {
            $headers = [];

            foreach ($_SERVER as $name => $value) {
                if ('HTTP_' === substr($name, 0, 5)) {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }

            return $headers;
        }

        return getallheaders();
    }

    public static function getHeader($header = null, $default = null)
    {
        $headers = self::getAllHeaders();

        if ($header) {
            return $headers["$header"] ?? $default;
        }

        return $headers;
    }

    /**
     * @param string|null $link
     *
     * @return array
     */
    public static function parseLink(string $link = null)
    {
        if (!$link) {
            $link = self::getHeader('Link');
        } else {
            $link = explode('Link:', $link)[1] ?? null;
        }

        if (!$link) {
            return null;
        }

        $return_array = [];

        foreach (explode(',', $link) as $k) {
            if (preg_match('/https.*[0-9]/', trim($k), $result)) {
                $url = $result[0];
                preg_match('#rel=".*#', trim($k), $result);
                $rel = explode('=', $result[0])[1];
                $return_array[str_replace('"', '', $rel)] = $url;
            }
        }

        return $return_array;
    }
}
