<?php

declare(strict_types=1);

namespace App\Console\PCIT;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LogoutCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('logout');

        $this->setDescription('Deletes the stored API token');

        $this->addOption(...PCITCommand::getGitTypeOptionArray());

        $this->addOption(...PCITCommand::getAPIEndpointOptionArray());
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file_name = PCITCommand::getConfigFileName();

        ['git-type' => $git_type, 'api-endpoint' => $api_endpoint] = $input->getOptions();

        if (is_file($file_name)) {
            $tokenContent = json_decode(file_get_contents($file_name), true);

            unset($tokenContent['endpoints'][$api_endpoint][$git_type]);

            file_put_contents($file_name, json_encode($tokenContent, \JSON_PRETTY_PRINT));

            $output->writeln('<info>Successfully logged out!</info>');
        } else {
            $output->writeln('<error>This User Not Found</error>>');
        }

        return 0;
    }
}
