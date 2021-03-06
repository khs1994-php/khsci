<?php

declare(strict_types=1);

namespace App\Console\PCIT;

use JsonSchema\Constraints\BaseConstraint;
use PCIT\Config\Validator as ConfigValidator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class ValidateCommand extends Command
{
    public function configure(): void
    {
        $this->setName('validate');
        $this->setDescription('Validate and view the .pcit.yml file');
        $this->addArgument('pcit_file', InputArgument::OPTIONAL, 'pcit file or dir include pcit file', '.pcit.yml');
        $this->addOption('table', null, InputOption::VALUE_NONE, 'displays data as a table');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var string */
        $pcit_file = $input->getArgument('pcit_file');

        if (!file_exists($pcit_file)) {
            $output->writeln("<error>$pcit_file not exists</error>");

            return 1;
        }

        if (is_file($pcit_file)) {
            return $this->validate($input, $output, getcwd().'/'.$pcit_file);
        }

        $finder = Finder::create()
            ->in(realpath(getcwd().'/'.('.' === $pcit_file ? './' : $pcit_file)))
            ->files()
            ->ignoreDotFiles(false)
            ->exclude(['vendor', 'node_modules'])
            ->name(
                '.pcit' === $pcit_file
                  ? ['*.yaml', '*.yml']
                  : ['.pcit.yml', '.pcit.yaml']
            );

        foreach ($finder as $file) {
            $this->validate($input, $output, $file->getRealPath());
        }

        return 0;
    }

    public function validate(InputInterface $input, OutputInterface $output, string $pcit_file): int
    {
        $yaml = file_get_contents($pcit_file);

        $data = BaseConstraint::arrayToObjectRecursive(Yaml::parse($yaml));

        $result = (new ConfigValidator())->validate($data);

        if ([] === $result) {
            $output->writeln("<info>==> The supplied $pcit_file validates against the schema.</info>");

            return 0;
        }

        $output->writeln("<error>==> The supplied $pcit_file does not validate.</error>");

        if (!$input->getOption('table')) {
            foreach ($result as $error) {
                // echo sprintf("[%s] %s\n", $error['property'], $error['message']);
                $output->writeln(sprintf('<info>%s</info> <error>%s</error>', $error['property'], $error['message']));
            }

            return 1;
        }
        $table = new Table($output);
        $table->setHeaders(['position', 'mesage']);
        $table->setColumnMaxWidth(0, 11);
        $rows = [];

        foreach ($result as $error) {
            $rows[] = [$error['property'], $error['message']];
            $rows[] = new TableSeparator();

            // echo sprintf("[%s] %s\n", $error['property'], $error['message']);
        }

        unset($rows[\count($rows) - 1]);

        $table->setRows(
            $rows
        );
        $table->render();

        return 1;
    }
}
