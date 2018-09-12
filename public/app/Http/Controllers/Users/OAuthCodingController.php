<?php

declare(strict_types=1);

namespace App\Http\Controllers\Users;

use Exception;
use KhsCI\Service\OAuth\CodingClient;

class OAuthCodingController extends OAuthGitHubController
{
    use OAuthTrait;

    /**
     * @var CodingClient
     */
    protected static $oauth;

    /**
     * @var CodingClient
     */
    protected static $git_type = 'coding';

    /**
     * OAuth 第二步在回调地址发起 POST 请求，返回 Access_Token.
     *
     * @throws Exception
     */
    public function getAccessToken(): void
    {
        $this->getAccessTokenCommon(null);
    }
}
