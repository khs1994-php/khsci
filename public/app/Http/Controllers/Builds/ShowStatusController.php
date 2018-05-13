<?php

declare(strict_types=1);

namespace App\Http\Controllers\Builds;

use App\Build;
use App\Repo;
use Exception;
use KhsCI\Support\Env;

class ShowStatusController
{
    /**
     * @param mixed ...$arg
     *
     * @throws Exception
     */
    public function __invoke(...$arg): void
    {
        $branch = $_GET['branch'] ?? null;

        if (!$branch) {
            $branch = Repo::getDefaultBranch(...$arg) ?? 'master';
        }

        $rid = Repo::getRid(...$arg);

        $status = Build::getBuildStatus((int) $rid, $branch);

        if (null === $status) {
            header('Content-Type: image/svg+xml;charset=utf-8');
            require __DIR__.'/../../../../public/ico/unknown.svg';
            exit;
        }

        header('Content-Type: image/svg+xml;charset=utf-8');

        require __DIR__.'/../../../../public/ico/'.$status.'.svg';
    }

    /**
     * @param mixed ...$arg
     *
     * @return string
     */
    public function getStatus(...$arg)
    {
        list($git_type, $username, $repo) = $arg;
        $host = Env::get('CI_HOST');

        return <<<EOF
<pre>

<h1>IMAGE</h1>

$host/$git_type/$username/$repo/status?branch=master

<h1>MARKDOWN</h1>

[![Build Status]($host/$git_type/$username/$repo/status?branch=master)]($host/$git_type/$username/$repo)

</pre>
EOF;
    }
}
