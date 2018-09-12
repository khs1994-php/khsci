<?php

declare(strict_types=1);

namespace App;

use Exception;
use KhsCI\Support\CI;
use KhsCI\Support\DB;
use KhsCI\Support\DBModel;

class Setting extends DBModel
{
    protected static $table = 'settings';

    protected static $setting_array = CI::CI_SETTING_ARRAY;

    /**
     * 返回某仓库的设置列表.
     *
     * @param string $git_type
     * @param int    $rid
     *
     * @return array
     *
     * @throws Exception
     */
    public static function list(int $rid, string $git_type = 'github')
    {
        $sql = <<<'EOF'
SELECT 
build_pushes,
build_pull_requests,
maximum_number_of_builds,
auto_cancel_branch_builds,
auto_cancel_pull_request_builds
FROM settings WHERE git_type=? AND rid=?
EOF;

        return DB::select($sql, [$git_type, $rid]);
    }

    /**
     * 返回某个设置的值
     *
     * @param string $git_type
     * @param int    $rid
     * @param string $setting_name
     *
     * @return string
     *
     * @throws Exception
     */
    public static function get(int $rid, string $setting_name, string $git_type = 'github')
    {
        if (!\in_array($setting_name, self::$setting_array, true)) {
            throw new Exception('Not Found', 404);
        }

        $sql = "SELECT $setting_name FROM repo WHERE git_type=? AND rid=? LIMIT 1";

        return DB::select($sql, [$git_type, $rid], true);
    }

    /**
     * @param string $git_type
     * @param int    $rid
     * @param string $setting_name
     * @param string $setting_value
     *
     * @return int
     *
     * @throws Exception
     */
    public static function update(int $rid, string $setting_name, string $setting_value, string $git_type = 'github')
    {
        if (!\in_array($setting_name, self::$setting_array, true)) {
            throw new Exception('Not Found', 404);
        }

        $sql = "UPDATE repo SET $setting_name=? WHERE git_type=? AND rid=? LIMIT 1";

        return DB::update($sql, [$setting_value, $git_type, $rid]);
    }
}
