<?php

declare(strict_types=1);

namespace PCIT\GPI\Webhooks\Handler\Abstracts;

use PCIT\GPI\Webhooks\Context\MemberContext;
use PCIT\GPI\Webhooks\Handler\Handler;

abstract class MemberAbstract extends Handler
{
    public function pustomize(MemberContext $context, string $git_type): void
    {
        $context->git_type = $git_type;

        $this->callPustomize('Member', $context);
    }
}
