<?php

declare(strict_types=1);

namespace App;

use Exception;
use KhsCI\Support\CI;
use KhsCI\Support\DB;
use KhsCI\Support\DBModel;

class Build extends DBModel
{
    /**
     * @param int $build_key_id
     *
     * @return int
     *
     * @throws Exception
     */
    public static function updateStartAt(int $build_key_id)
    {
        $sql = 'UPDATE builds SET create_time = ? WHERE id=?';

        return DB::update($sql, [time(), $build_key_id]);
    }

    /**
     * @param int $build_key_id
     *
     * @return int
     *
     * @throws Exception
     */
    public static function updateStopAt(int $build_key_id)
    {
        $sql = 'UPDATE builds SET end_time = ? WHERE id=?';

        return DB::update($sql, [time(), $build_key_id]);
    }

    /**
     * @param int $build_key_id
     *
     * @return array|string
     *
     * @throws Exception
     */
    public static function getGitType(int $build_key_id)
    {
        $sql = 'SELECT git_type FROM builds WHERE id=?';

        return DB::select($sql, [$build_key_id], true);
    }

    /**
     * @param int $build_key_id
     *
     * @return array|string
     *
     * @throws Exception
     */
    public static function getRid(int $build_key_id)
    {
        $sql = 'SELECT rid FROM builds WHERE id=?';

        return DB::select($sql, [$build_key_id], true);
    }

    /**
     * @param int    $build_key_id
     * @param string $status
     *
     * @return int
     *
     * @throws Exception
     */
    public static function updateBuildStatus(int $build_key_id, string $status)
    {
        $sql = 'UPDATE builds SET build_status=? WHERE id=?';

        return DB::update($sql, [$status, $build_key_id]);
    }

    /**
     * @param int    $rid
     * @param string $branch
     *
     * @return array|string
     *
     * @throws Exception
     */
    public static function getBuildStatus(int $rid, string $branch)
    {
        $sql = 'SELECT build_status FROM builds WHERE rid=? AND branch=? ORDER BY id DESC LIMIT 1';

        return DB::select($sql, [$rid, $branch], true);
    }

    /**
     * @param string $git_type
     * @param int    $rid
     *
     * @return array|string
     *
     * @throws Exception
     */
    public static function getBranches(string $git_type, int $rid)
    {
        $sql = 'SELECT DISTINCT branch FROM builds WHERE git_type=? AND rid=?';

        return $branches = DB::select($sql, [$git_type, $rid]);
    }

    /**
     * @param string $git_type
     * @param int    $rid
     * @param string $branch
     *
     * @return array|string
     *
     * @throws Exception
     */
    public static function getPushAndTagEvent(string $git_type, int $rid, string $branch)
    {
        $sql = <<<'EOF'
SELECT 

id,
build_status,
commit_id,
committer_name,
end_time

FROM builds WHERE

git_type=? AND rid=? AND branch=? AND event_type IN (?,?) ORDER BY id DESC LIMIT 5

EOF;

        return DB::select($sql, [$git_type, $rid, $branch, CI::BUILD_EVENT_PUSH, CI::BUILD_EVENT_TAG]);
    }

    /**
     * @param string $git_type
     * @param int    $rid
     *
     * @return array|string
     *
     * @throws Exception
     */
    public static function getLastBuildId(string $git_type, int $rid)
    {
        $sql = <<<EOF
SELECT id FROM builds 

WHERE 

git_type=? AND rid=? AND build_status NOT IN (?,?,?) ORDER BY id DESC LIMIT 1
EOF;

        return DB::select($sql, [
            $git_type, $rid,
            CI::BUILD_STATUS_PENDING,
            CI::BUILD_STATUS_SKIP,
            CI::BUILD_STATUS_INACTIVE,
        ], true);
    }
}
