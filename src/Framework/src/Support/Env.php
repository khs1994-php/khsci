<?php

declare(strict_types=1);

namespace PCIT\Framework\Support;

class Env
{
    /**
     * @param string $default
     *
     * false bool
     * true  bool
     * 'true' bool
     * 'false' bool
     * K= null
     * '' null
     * null null
     *
     * @return null|bool|string
     */
    public static function get(string $key, $default = null)
    {
        try {
            $value = getenv($key);

            if (false === $value || '' === $value) {
                $value = $default;
            }

            'false' === $value && $value = false;
            'true' === $value && $value = true;
        } catch (\Exception $e) {
            $value = $default;
        }

        return $value;
    }
}
