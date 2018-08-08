<?php

declare(strict_types=1);

namespace App\Console\Webhooks\GitHub;

use App\Build;
use App\Console\Webhooks\GetConfig;
use App\Console\Webhooks\Skip;
use App\Notifications\GitHubAppChecks;
use App\Repo;
use App\User;
use KhsCI\Support\Log;

class Push
{
    /**
     * @param $json_content
     *
     * @throws \Exception
     */
    public static function handle($json_content): void
    {
        $array = \KhsCI\Support\Webhooks\GitHub\Push::handle($json_content);

        $tag = $array['tag'] ?? null;

        if ($tag) {
            self::tag($array);

            return;
        }

        [
            'installation_id' => $installation_id,
            'rid' => $rid,
            'repo_full_name' => $repo_full_name,
            'branch' => $branch,
            'commit_id' => $commit_id,
            'commit_message' => $commit_message,
            'committer' => $committer,
            'author' => $author,
            'compare' => $compare,
            'event_time' => $event_time,
            'account' => $account,
        ] = $array;

        // user table not include user info
        User::updateUserInfo($account);
        User::updateInstallationId((int) $installation_id, $account->username);
        Repo::updateRepoInfo((int) $rid, $repo_full_name, null, null);

        $config_array = GetConfig::handle((int) $rid, $commit_id);

        $config = json_encode($config_array);

        $last_insert_id = Build::insert('push', $branch, $compare, $commit_id,
            $commit_message, $committer->name, $committer->email, $committer->username,
            $author->name, $author->email, $author->username,
            $rid, $event_time, $config);

        if (Skip::handle($commit_message, (int) $last_insert_id, $branch, $config)) {
            Skip::writeSkipToDB((int) $last_insert_id);

            throw new \Exception('skip', 200);
        }

        GitHubAppChecks::send((int) $last_insert_id);

        Log::debug(__FILE__, __LINE__, 'exec push event success', [], Log::INFO);
    }

    /**
     * @param array $array
     *
     * @throws \Exception
     */
    public static function tag(array $array): void
    {
        [
            'installation_id' => $installation_id,
            'rid' => $rid,
            'repo_full_name' => $repo_full_name,
            'branch' => $branch,
            'tag' => $tag,
            'commit_id' => $commit_id,
            'commit_message' => $commit_message,
            'committer' => $committer,
            'author' => $author,
            'event_time' => $event_time,
            'account' => $account,
        ] = $array;

        User::updateUserInfo($account);
        User::updateInstallationId((int) $installation_id, $account->username);
        Repo::updateRepoInfo((int) $rid, $repo_full_name, null, null);

        $config_array = GetConfig::handle((int) $rid, $commit_id);

        $config = json_encode($config_array);

        $last_insert_id = Build::insertTag(
            $branch, $tag, $commit_id, $commit_message,
            $committer->name, $committer->email, $committer->username,
            $author->name, $author->email, $author->username,
            $rid, $event_time, $config
        );

        GitHubAppChecks::send((int) $last_insert_id);
    }
}
