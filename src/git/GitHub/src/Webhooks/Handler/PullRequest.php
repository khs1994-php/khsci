<?php

declare(strict_types=1);

namespace PCIT\GitHub\Webhooks\Handler;

use App\Build;
use App\GetAccessToken;
use PCIT\PCIT;

class PullRequest
{
    /**
     * Action.
     *
     * "assigned", "unassigned", "review_requested", "review_request_removed",
     * "labeled", "unlabeled", "opened", "synchronize", "edited", "closed", or "reopened"
     *
     * @param $json_content
     *
     * @throws \Exception
     */
    public static function handle($json_content): void
    {
        $result = \PCIT\GitHub\Webhooks\Parser\PullRequest::handle($json_content);

        $action = $result['action'];

        if ('assigned' === $action) {
            self::assigned($result);

            return;
        }

        if ('labeled' === $action) {
            self::labeled($result);

            return;
        }

        [
            'installation_id' => $installation_id,
            'action' => $action,
            'rid' => $rid,
            'repo_full_name' => $repo_full_name,
            'commit_id' => $commit_id,
            'event_time' => $event_time,
            'commit_message' => $commit_message,
            'committer_username' => $committer_username,
            'committer_uid' => $committer_uid,
            'pull_request_number' => $pull_request_number,
            'branch' => $branch,
            'internal' => $internal,
            'pull_request_source' => $pull_request_source,
            'account' => $account,
        ] = $result;

        $subject = new Subject();

        $subject->register(new UpdateUserInfo($account, (int) $installation_id, (int) $rid, $repo_full_name));

        $config_array = $subject->register(new GetConfig($rid, $commit_id))->handle()->config_array;

        $config = json_encode($config_array);

        $last_insert_id = Build::insertPullRequest(
            $event_time, $action, $commit_id, $commit_message,
            (int) $committer_uid, $committer_username, $pull_request_number,
            $branch, $rid, $config, $internal, $pull_request_source
        );

        $subject->register(new Skip($commit_message, (int) $last_insert_id, $branch, $config))
            ->handle();

        if ('opened' !== $action) {
            return;
        }

        $comment_body = <<<'EOF'
Repo administrator can comment `/LGTM`, I will merge this Pull_request.

---

This Comment has been generated by [PCIT Bot](https://github.com/pcit-ce/pcit).

EOF;

        self::sendComment((int) $rid, $repo_full_name, $pull_request_number, $comment_body);
    }

    /**
     * @param $repo_full_name
     * @param $pull_request_number
     * @param $comment_body
     *
     * @throws \Exception
     */
    private static function sendComment(int $rid, $repo_full_name, $pull_request_number, $comment_body): void
    {
        (new PCIT(['github_access_token' => GetAccessToken::getGitHubAppAccessToken($rid)]))
            ->issue_comments
            ->create($repo_full_name, $pull_request_number, $comment_body);
    }

    /**
     * @param array $content
     *
     * @throws \Exception
     */
    public static function assigned($content): void
    {
        [
            'rid' => $rid,
            'repo_full_name' => $repo_full_name,
            'pull_request_number' => $pull_request_number
        ] = $content;
    }

    /**
     * @param array $content
     *
     * @throws \Exception
     */
    public static function labeled($content): void
    {
        // 创建一条评论
        [
            'rid' => $rid,
            'repo_full_name' => $repo_full_name,
            'pull_request_number' => $pull_request_number,
        ] = $content;
    }

    public static function merge(
        string $repo_full_name,
        int $pull_request_number,
        ?string $commit_title = null,
        ?string $commit_message = null,
        ?string $commit_id = null,
        int $method = 1
        ): void {
        \Log::info('merge pull_request by pcit auto');

        $pcit = app(PCIT::class)->setGitType('github')
        ->setAccessToken(GetAccessToken::getGitHubAppAccessToken(null, $repo_full_name));

        $repo_array = explode('/', $repo_full_name);

        try {
            $pcit->pull_request
                    ->merge(
                        $repo_array[0],
                        $repo_array[1],
                        $pull_request_number,
                        $commit_title,
                        $commit_message,
                        $commit_id,
                        (int) $method
                    );
            \Log::info('auto merge success, method is '.$method);
        } catch (\Throwable $e) {
            \Log::debug($e->__toString());
        }
    }
}
