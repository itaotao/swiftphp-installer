<?php

namespace SwiftPHP\Scaffold\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputOption;

class MakeModel extends BaseCommand
{
    protected $commandName = 'make:model';

    protected function configure(): void
    {
        $this
            ->setName($this->commandName)
            ->setDescription('Create a new model class')
            ->addArgument('name', InputArgument::REQUIRED, 'The model name (e.g., User)')
            ->addOption('table', 't', InputOption::VALUE_REQUIRED, 'Specify the table name')
            ->addOption('migration', 'm', InputOption::VALUE_NONE, 'Create a migration file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');
        $table = $input->getOption('table');
        $migration = $input->getOption('migration');

        if ($this->validateName($name, $io)) {
            return Command::FAILURE;
        }

        $className = $this->toPascalCase($name);
        $tableName = $table ?: $this->snakeCase($this->pluralize($name));

        $directory = $this->buildPath($this->basePath, 'app', 'model');
        $this->ensureDirectory($directory);

        $filePath = $this->buildPath($directory, $className . '.php');

        if (file_exists($filePath)) {
            $io->error("Model {$className} already exists!");
            return Command::FAILURE;
        }

        $content = $this->generateContent($className, $tableName);

        file_put_contents($filePath, $content);
        $io->success("Model {$className} created successfully!");

        if ($migration) {
            $io->note("Migration creation is not implemented yet.");
        }

        return Command::SUCCESS;
    }

    protected function generateContent(string $className, string $tableName): string
    {
        return <<<PHP
<?php

namespace App\Model;

use SwiftPHP\Model\Model;

class {$className} extends Model
{
    protected \$table = '{$tableName}';
    protected \$primaryKey = 'id';
    protected \$fillable = [
        // Define your fillable fields here
    ];
}
PHP;
    }

    protected function snakeCase(string $value): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $value));
    }

    protected function pluralize(string $value): string
    {
        // Simple pluralization logic
        if (substr($value, -1) === 'y') {
            return substr($value, 0, -1) . 'ies';
        }
        if (substr($value, -1) === 's' || substr($value, -2) === 'ss' || substr($value, -1) === 'x' || substr($value, -2) === 'ch' || substr($value, -2) === 'sh') {
            return $value . 'es';
        }
        return $value . 's';
    }
}
