<?php

declare(strict_types=1);

namespace PCIT\Service\Build\Events;

use Exception;
use PCIT\PCIT as PCIT;
use PCIT\Service\Build\BuildData;
use PCIT\Service\Build\Client;
use PCIT\Service\Build\Conditional\Branch;
use PCIT\Service\Build\Conditional\Event;
use PCIT\Service\Build\Conditional\Platform;
use PCIT\Service\Build\Conditional\Status;
use PCIT\Service\Build\Conditional\Tag;
use PCIT\Service\Build\Parse;
use PCIT\Support\Cache;
use PCIT\Support\Log;

class Pipeline
{
    private $pipeline;

    private $matrix_config;

    private $build;

    private $client;

    public function __construct($pipeline, BuildData $build, Client $client, ?array $matrix_config)
    {
        $this->pipeline = $pipeline;
        $this->matrix_config = $matrix_config;
        $this->build = $build;
        $this->client = $client;
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $docker_container = (new PCIT())->docker->container;

        $job_id = $this->client->job_id;

        $workdir = $this->client->workdir;

        // push
        $cache = Cache::store();
        $cache->lPush((string) $job_id.'_pipeline', 'end');
        $cache->lPush((string) $job_id.'_success', 'end');
        $cache->lPush((string) $job_id.'_failure', 'end');
        $cache->lPush((string) $job_id.'_changed', 'end');

        foreach ($this->pipeline as $setup => $array) {
            Log::debug(__FILE__, __LINE__, 'Handle pipeline', ['pipeline' => $setup], Log::EMERGENCY);

            $image = $array->image;
            $commands = $array->commands ?? null;
            $env = $array->environment ?? [];
            $status = $array->when->status ?? null;
            $shell = $array->shell ?? 'sh';

            $when_platform = $array->when->platform ?? null;

            // tag pull_request
            $when_event = $array->when->event ?? null;

            $when_branch = $array->when->branch ?? null;
            $when_tag = $array->when->tag ?? null;

            $this->client->build->tag;
            $this->client->build->pull_request_number;

            if (!(new Platform($when_platform, 'linux/amd64'))->regHandle()) {
                Log::connect()->emergency('skip by platform check');
                continue;
            }

            if (!(new Event($when_event, $this->build->event_type))->handle()) {
                Log::connect()->emergency('skip by event check');
                continue;
            }

            if (!(new Branch($when_branch, $this->build->branch))->regHandle()) {
                Log::connect()->emergency('skip by branch check');
                continue;
            }

            if (!(new Tag($when_tag, $this->build->tag))->regHandle()) {
                Log::connect()->emergency('skip by tag check');
                continue;
            }

            $failure = (new Status())->handle($status, 'failure');
            $success = (new Status())->handle($status, 'success');
            $changed = (new Status())->handle($status, 'changed');

            $no_status = $status ? false : true;

            $image = Parse::image($image, $this->matrix_config);
            $ci_script = Parse::command($setup, $image, $commands);

            if ($env) {
                $env = array_merge(["CI_SCRIPT=$ci_script"], $env, $this->client->system_env);
            } else {
                $env = array_merge(["CI_SCRIPT=$ci_script"], $this->client->system_env);
            }

            Log::debug(__FILE__, __LINE__, json_encode($env), [], Log::INFO);

            $cmd = $commands ? ['echo $CI_SCRIPT | base64 -d | '.$shell.' -e'] : null;
            $entrypoint = $commands ? ['/bin/sh', '-c'] : null;

            $container_config = $docker_container
                ->setEnv($env)
                ->setBinds(["$job_id:$workdir", 'tmp:/tmp'])
                ->setEntrypoint($entrypoint)
                ->setLabels([
                    'com.khs1994.ci.pipeline' => "$job_id",
                    'com.khs1994.ci.pipeline.name' => $setup,
                    'com.khs1994.ci.pipeline.status.no_status' => (string) $no_status,
                    'com.khs1994.ci.pipeline.status.failure' => (string) $failure,
                    'com.khs1994.ci.pipeline.status.success' => (string) $success,
                    'com.khs1994.ci.pipeline.status.changed' => (string) $changed,
                    'com.khs1994.ci' => (string) $job_id,
                ])
                ->setWorkingDir($workdir)
                ->setCmd($cmd)
                ->setImage($image)
                ->setNetworkingConfig([
                    'EndpointsConfig' => [
                        "$job_id" => [
                            'Aliases' => [
                                $setup,
                            ],
                        ],
                    ],
                ])
                ->setCreateJson(null)
                ->getCreateJson();

            $is_status = false;

            if ($failure) {
                $is_status = true;
                $cache->lPush((string) $job_id.'_failure', $container_config);
            }

            if ($success) {
                $is_status = true;
                $cache->lPush((string) $job_id.'_success', $container_config);
            }

            if ($changed) {
                $is_status = true;
                $cache->lPush((string) $job_id.'_changed', $container_config);
            }

            if (true === $is_status) {
                return;
            }

            $cache->lPush((string) $job_id.'_pipeline', $container_config);
        }
    }
}
