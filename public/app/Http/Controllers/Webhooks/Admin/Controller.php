<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks\Admin;

use Error;
use Exception;
use KhsCI\Support\Cache;
use KhsCI\Support\CIConst;
use KhsCI\Support\DB;
use KhsCI\Support\Env;
use KhsCI\Support\Request;
use KhsCI\Support\Session;

class Controller
{
    private static $gitType;

    /**
     * 设置 Git 类型.
     *
     * @param mixed ...$arg
     *
     * @return array
     */
    private static function setGitType(...$arg)
    {
        $gitType = $arg[0];
        self::$gitType = $gitType;
        unset($arg[0]);

        return $arg;
    }

    /**
     * 检查 AccessToken.
     *
     * @return bool|null
     *
     * @throws Exception
     */
    private static function checkAccessToken()
    {
        $header = Request::getHeader('Authorization') ?? '';

        $access_token = (explode(' ', $header))[1]
            ?? Session::get(self::$gitType.'.access_token')
            ?? false;

        if (false === $access_token) {
            throw new Exception('access_token not found || Requires authentication || 401 Unauthorized', 401);
        }

        return $access_token;
    }

    /**
     * 获取类.
     *
     * @return string
     */
    private static function getObj()
    {
        if ('github' === self::$gitType) {
            $obj = 'KhsCI\\Service\\OAuth\\GitHub';
        } else {
            $obj = 'KhsCI\\Service\\OAuth\\'.ucfirst(self::$gitType);
        }

        return $obj;
    }

    /**
     * 获取 Webhooks 列表.
     *
     * @param mixed ...$arg
     *
     * @return mixed
     *
     * @throws Exception
     */
    public static function list(...$arg)
    {
        $raw = false;

        $arg = self::setGitType(...$arg);

        $access_token = self::checkAccessToken();

        $obj = self::getObj();

        $json = $obj::getWebhooks($access_token, $raw, ...$arg);

        return json_decode($json, true);
    }

    /**
     * 增加 Webhooks，增加之前必须先判断是否已存在，GitHub 除外.
     *
     * @param mixed ...$arg
     *
     * @return mixed
     *
     * @throws Exception
     */
    public static function add(...$arg)
    {
        $arg = self::setGitType(...$arg);

        $gitType = self::$gitType;

        $method = $gitType.'Json';

        try {
            $data = self::$method();
        } catch (Exception | Error $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        $dataObj = json_decode($data);

        if ((!$data) or (!is_object($dataObj)) or 0 !== json_last_error()) {
            throw new Exception('Invalid request, must include JSON', 422);
        }

        $webhooksUrl = Env::get('CI_HOST').'/webhooks/'.$gitType;

        $access_token = self::checkAccessToken();

        $obj = self::getObj();

        $getWebhooksStatus = $obj::getWebhooksStatus($access_token, $webhooksUrl, ...$arg);

        if (1 === $getWebhooksStatus) {
            $sql = "UPDATE repo SET webhooks_status=? WHERE git_type=? AND repo_full_name=?";

            DB::update($sql, [1, $gitType, '$arg[1]/$arg[2]']);

            return ['code' => 200, 'message' => 'Success, But hook already exists on this repository'];
        }

        try {
            $json = $obj::setWebhooks($access_token, $data, ...$arg);
        } catch (Exception $e) {
            if (422 === $e->getCode()) {

                $sql = "UPDATE repo SET webhooks_status=1 WHERE git_type=? AND repo_full_name=?";

                DB::update($sql, [$gitType, '$arg[1]/$arg[2]']);

                return ['code' => 200, 'message' => 'Success, But hook already exists on this repository'];
            } else {
                return [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ];
            }
        }

        return array_merge([
            'code' => 200,
        ], json_decode($json, true));
    }

    /**
     * 删除 Webhooks.
     *
     * @param mixed ...$arg
     *
     * @return mixed
     *
     * @throws Exception
     */
    public static function delete(...$arg)
    {
        $arg = self::setGitType(...$arg);

        $access_token = self::checkAccessToken();

        $obj = self::getObj();

        return $obj::unsetWebhooks($access_token, ...$arg);
    }

    /**
     * 设置 Webhooks 状态缓存.
     *
     * @param int $status
     * @param mixed ...$arg
     * @throws Exception
     */
    private static function setBuildStatusCache(int $status = 0, ...$arg): void
    {
        $gitType = self::$gitType;
        $uid = Session::get($gitType.'.uid');
        $redis = Cache::connect();

        $repoFullName = $arg[0].'/'.$arg[1];

        $sql = "UPDATE repo SET build_activate = ? WHERE git_type=? AND repo_full_name=?";

        DB::update($sql, [$status, $gitType, $repoFullName]);

        $redis->hSet($uid.'_repo', $repoFullName, $status);
    }

    /**
     * 开启构建.
     *
     * @param mixed ...$arg
     *
     * @return array
     *
     * @throws Exception
     */
    public static function activate(...$arg)
    {
        $arg = self::setGitType(...$arg);
        /*
         * 首先保证 Webhooks 已设置
         */
        $array = self::add(self::$gitType, ...$arg);
        /*
         * 更新缓存 + 更新数据库
         */
        self::setBuildStatusCache(CIConst::BUILD_ACTIVATE, ...$arg);

        return $array;
    }

    /**
     * 停止构建，暂时不主动删除 Webhooks.
     *
     * @param array $arg
     *
     * @return array
     * @throws Exception
     */
    public static function deactivate(...$arg)
    {
        $arg = self::setGitType(...$arg);

        self::setBuildStatusCache(CIConst::BUILD_DEACTIVATE, ...$arg);

        return [
            'code' => 200,
        ];
    }

    public static function codingJson()
    {
        $url = Env::get('CI_HOST').'/webhooks/coding';

        $token = Env::get('WEBHOOKS_TOKEN', md5('khsci'));

        return <<<EOF
{
  "hook_url": "$url",
  "token": "$token",
  "type_push": true,
  "type_mr_pr": true,
  "type_topic": true,
  "type_member": true,
  "type_comment": true,
  "type_document": true,
  "type_watch": true,
  "type_star": true,
  "type_task": true
}
EOF;
    }

    public static function giteeJson()
    {
        $url = Env::get('CI_HOST').'/webhooks/gitee';

        $token = Env::get('WEBHOOKS_TOKEN', md5('khsci'));

        return <<<EOF
{
  "issues_events": true,
  "merge_requests_events":true,
  "note_events": true,
  "project_id": true,
  "push_events": true,
  "tag_push_events": true,
  "url": "$url",
  "password": "$token"
}
EOF;
    }

    public static function githubJson()
    {
        $url = Env::get('CI_HOST').'/webhooks/github';

        $token = Env::get('WEBHOOKS_TOKEN', md5('khsci'));

        return <<<EOF
{
  "name": "web",
  "active": true,
  "events": [
    "*"
  ],
  "config": {
    "url": "$url",
    "content_type": "json",
    "secret": "$token",
    "insecure_ssl": "0"
  }
}
EOF;
    }
}
