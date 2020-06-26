<?php

declare(strict_types=1);

namespace PCIT\GPI\Webhooks\Parser\UserBasicInfo;

use PCIT\GPI\Webhooks\Context\Components\Kernel;

/**
 * User or Org info.
 */
class Owner extends Kernel
{
    public $uid;

    public $name;

    public $username;

    public $email;

    public $pic;

    public $org;

    /**
     * @var "User"|"Organization"
     */
    public $type;

    public function __construct($repository_owner, bool $org = false)
    {
        $this->uid = $repository_owner->id;
        $this->username = $repository_owner->login;
        $this->name = $repository_owner->name ?? null;
        $this->email = $repository_owner->email ?? null;
        $this->pic = $repository_owner->avatar_url;
        $this->org = $org;
    }
}
