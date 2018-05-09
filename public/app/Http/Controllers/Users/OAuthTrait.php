<?php

declare(strict_types=1);

namespace App\Http\Controllers\Users;

use Error;
use Exception;
use KhsCI\KhsCI;
use KhsCI\Service\OAuth\{
    Coding,
    GitHub,
    GitHubApp,
    Gitee
};
use KhsCI\Support\Response;
use KhsCI\Support\Session;

trait OAuthTrait
{
    /**
     * @var GitHubApp|Coding|GitHub|Gitee
     */
    protected static $oauth;

    /**
     * @param null|string $state
     *
     * @throws Exception
     */
    public function getAccessTokenCommon(?string $state): void
    {
        $code = $_GET['code'] ?? false;

        if (false === $code) {
            throw new Exception('code not found');
        }

        try {
            $access_token = static::$oauth->getAccessToken((string)$code, $state)
                ?? false;

            $git_type = self::$git_type;

            false !== $access_token && Session::put($git_type.'.access_token', $access_token);

            $khsci = new KhsCI(['github_access_token' => $access_token]);

            $userInfoArray = $khsci->user_basic_info->getUserInfo();
        } catch (Error $e) {
            throw new Exception($e->getMessage(), 500);
        }

        $uid = $userInfoArray['uid'];
        $name = $userInfoArray['name'];
        $pic = $userInfoArray['pic'];
        $email = $userInfoArray['email'];

        Session::put($git_type.'.uid', $uid);
        Session::put($git_type.'.username', $name);
        Session::put($git_type.'.pic', $pic);
        Session::put($git_type.'.email', $email);

        Response::redirect(getenv('CI_HOST').'/profile/'.$git_type.'/'.$name);
    }
}
