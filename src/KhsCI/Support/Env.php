<?php

declare(strict_types=1);

namespace KhsCI\Support;

class Env
{
    /**
     * @param string $key
     * @param        $default
     *
     * @return array|false|string
     */
    public static function get(string $key, $default = null)
    {
        try {
            $value = getenv($key);

            if (false === $value or '' === $value) {
                $value = $default;
            }
        } catch (\Exception $e) {
            $value = $default;
        }

        return $value;
    }
}
