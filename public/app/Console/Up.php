<?php

declare(strict_types=1);

namespace App\Console;

use App\Builds;
use App\GetAccessToken;
use App\Repo;
use Error;
use Exception;
use KhsCI\KhsCI;
use KhsCI\Support\Cache;
use KhsCI\Support\CI;
use KhsCI\Support\Env;

class Up
{
    /**
     * @throws Exception
     */
    public static function up(): void
    {
        while (1) {
            try {
                if (1 === Cache::connect()->get('khsci_up_status')) {
                    echo "Wait sleep 10s ...\n\n";

                    sleep(10);

                    continue;
                }

                $status = Cache::connect()->set('khsci_up_status', 1);

                // Queue::queue();

                self::updateGitHubStatus();

                self::updateGitHubAppChecks();

                echo "Finished sleep 10s ...\n\n";

                sleep(10);
            } catch (Exception | Error $e) {
                echo $e->getMessage();
                echo '';

                echo $e->getCode();

                echo '';
            }
        }
    }

    /**
     * @throws Exception
     */
    private static function updateGitHubStatus(): void
    {
        $build_key_id = Cache::connect()->rPop('github_status');

        if (!$build_key_id) {
            return;
        }

        $rid = Builds::getRidByBuildKeyId((int) $build_key_id);

        $repo_full_name = Repo::getRepoFullName('github', (int) $rid);

        list($repo_prefix, $repo_name) = explode('/', $repo_full_name);

        $build_output_array = Builds::get((int) $build_key_id);

        $khsci = new KhsCI(['github_access_token' => GetAccessToken::byRepoFullName($repo_full_name)]);

        $status = $khsci->repo_status->create(
            $repo_prefix,
            $repo_name,
            $build_output_array['commit_id'],
            'pending',
            Env::get('CI_HOST').'/github/'.$repo_full_name.'/builds/'.$build_key_id,
            'continuous-integration/'.Env::get('CI_NAME').'/'.$build_output_array['event_type']
        );

        var_dump($status);

        Cache::connect()->set('khsci_up_status', 0);
    }

    /**
     * @throws Exception
     */
    private static function updateGitHubAppChecks()
    {
        $build_key_id = Cache::connect()->rpop('github_app_checks');

        if (!$build_key_id) {
            return 0;
        }

        $rid = Builds::getRidByBuildKeyId((int) $build_key_id);

        $repo_full_name = Repo::getRepoFullName('github_app', (int) $rid);

        $installation_id = Repo::getGitHubInstallationIdByRepoFullName($repo_full_name);

        $khsci = new KhsCI();

        $access_token = $khsci->github_apps_installations->getAccessToken(
            (int) $installation_id,
            __DIR__.'/../../'.Env::get('CI_GITHUB_APP_PRIVATE_FILE')
        );

        $khsci = new KhsCI(['github_app_access_token' => $access_token], 'github_app');

        $output_array = Builds::get((int) $build_key_id);

        $branch = $output_array['branch'];

        $commit_id = $output_array['commit_id'];

        $details_url = Env::get('CI_HOST').'/github_app/'.$repo_full_name.'/builds/'.$build_key_id;

        return $khsci->check_run->create(
            $repo_full_name,
            'Chinese First Support GitHub Checks API CI System',
            $branch,
            $commit_id,
            $details_url,
            $build_key_id,
            CI::GITHUB_CHECK_SUITE_STATUS_QUEUED,
            time(), null, null,
            'testTitle',
            'testSummary',
            'test text'
        );
    }
}
