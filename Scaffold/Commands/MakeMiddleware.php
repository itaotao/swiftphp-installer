<?php

namespace SwiftPHP\Scaffold\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputOption;

class MakeMiddleware extends BaseCommand
{
    protected $commandName = 'make:middleware';

    protected function configure(): void
    {
        $this
            ->setName($this->commandName)
            ->setDescription('Create a new middleware class')
            ->addArgument('name', InputArgument::REQUIRED, 'The middleware name (e.g., Auth)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');

        if ($this->validateName($name, $io)) {
            return Command::FAILURE;
        }

        $className = $this->toPascalCase($name);
        $directory = $this->buildPath($this->basePath, 'app', 'middleware');
        $this->ensureDirectory($directory);

        $filePath = $this->buildPath($directory, $className . '.php');

        if (file_exists($filePath)) {
            $io->error("Middleware {$className} already exists!");
            return Command::FAILURE;
        }

        $content = $this->generateContent($className);

        file_put_contents($filePath, $content);
        $io->success("Middleware {$className} created successfully!");

        return Command::SUCCESS;
    }

    protected function generateContent(string $className): string
    {
        return <<<PHP
<?php

namespace App\Middleware;

use SwiftPHP\Middleware\Middleware;
use SwiftPHP\Request\Request;
use SwiftPHP\Response\Response;

class {$className} extends Middleware
{
    public function handle(Request \$request, callable \$next): Response
    {
        // Add your middleware logic here

        return \$next(\$request);
    }
}
PHP;
    }
}
