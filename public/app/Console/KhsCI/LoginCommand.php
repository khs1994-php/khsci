<?php

declare(strict_types=1);

namespace App\Console\KhsCI;

use Curl\Curl;
use Exception;
use KhsCI\Support\JSON;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LoginCommand extends Command
{
    public function configure(): void
    {
        $this->setName('login');

        $this->setDescription('Authenticates against the API and stores the token');

        $this->addOption('username', 'u', InputOption::VALUE_REQUIRED, 'Git username');

        $this->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'Git password or Personal-API-Tokens');

        $this->addOption(...KhsCICommand::getGitTypeOptionArray());

        $this->addOption(...KhsCICommand::getAPIEndpointOptionArray());
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return mixed
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        list(
            'username' => $username,
            'password' => $password,
            'git_type' => $git_type,
            'api-endpoint' => $api_endpoint
            ) = $input->getOptions();

        $request = json_encode([
                'git_type' => $git_type,
                'username' => $username,
                'password' => $password,
            ]
        );

        $curl = new Curl();

        $token = $curl->post($api_endpoint.'/api/user/token', $request);

        $http_return_code = $curl->getCode();

        if (200 !== $http_return_code) {
            throw new Exception('Incorrect username or password or git_type', $http_return_code);
        }

        $file_name = KhsCICommand::getConfigFileName();

        if (file_exists($file_name)) {
            $array = json_decode(file_get_contents($file_name), true);

            $array['endpoints'][$api_endpoint][$git_type] = $token;

            file_put_contents($file_name, JSON::beautiful(json_encode($array)));

            $output->writeln('Login Success');

            return;
        }

        file_put_contents($file_name, JSON::beautiful(
            json_encode(
                [
                    'endpoints' => [
                        $api_endpoint => [
                            $git_type => $token,
                        ],
                    ],
                ]
            )
        ));

        $output->writeln('Login Success');
    }
}
