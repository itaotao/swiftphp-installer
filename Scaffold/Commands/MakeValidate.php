<?php

namespace SwiftPHP\Scaffold\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputOption;

class MakeValidate extends BaseCommand
{
    protected $commandName = 'make:validate';

    protected function configure(): void
    {
        $this
            ->setName($this->commandName)
            ->setDescription('Create a new validation class')
            ->addArgument('name', InputArgument::REQUIRED, 'The validation name (e.g., User)')
            ->addOption('scene', 's', InputOption::VALUE_REQUIRED, 'Create a validation with scene (e.g., create,update)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');
        $scene = $input->getOption('scene');

        if ($this->validateName($name, $io)) {
            return Command::FAILURE;
        }

        $className = $this->toPascalCase($name);
        $directory = $this->buildPath($this->basePath, 'app', 'validate');
        $this->ensureDirectory($directory);

        $filePath = $this->buildPath($directory, $className . '.php');

        if (file_exists($filePath)) {
            $io->error("Validate {$className} already exists!");
            return Command::FAILURE;
        }

        $content = $this->generateContent($className, $scene);

        file_put_contents($filePath, $content);
        $io->success("Validate {$className} created successfully!");

        return Command::SUCCESS;
    }

    protected function generateContent(string $className, ?string $scene): string
    {
        $sceneCode = '';
        if ($scene) {
            $scenes = explode(',', $scene);
            $sceneArray = [];
            foreach ($scenes as $s) {
                $sceneArray[] = "        '{$s}' => []";
            }
            $sceneCode = "\n    protected \$scene = [\n" . implode(",\n", $sceneArray) . "\n    ];";
        }

        return <<<PHP
<?php

namespace App\Validate;

use SwiftPHP\Validate\Validate;

class {$className} extends Validate
{
    protected \$rule = [
        // Define your validation rules here
    ];

    protected \$message = [
        // Define your error messages here
    ];{$sceneCode}
}
PHP;
    }
}
