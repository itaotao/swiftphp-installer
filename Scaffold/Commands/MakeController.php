<?php

namespace SwiftPHP\Scaffold\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputOption;

class MakeController extends BaseCommand
{
    protected $commandName = 'make:controller';

    protected function configure(): void
    {
        $this
            ->setName($this->commandName)
            ->setDescription('Create a new controller class')
            ->addArgument('name', InputArgument::REQUIRED, 'The controller name (e.g., User)')
            ->addOption('rest', 'r', InputOption::VALUE_NONE, 'Create a RESTful controller')
            ->addOption('api', 'a', InputOption::VALUE_NONE, 'Create an API controller');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');
        $isRest = $input->getOption('rest');
        $isApi = $input->getOption('api');

        if ($this->validateName($name, $io)) {
            return Command::FAILURE;
        }

        $className = $this->toPascalCase($name);
        $directory = $this->buildPath($this->basePath, 'app', 'controller');
        $this->ensureDirectory($directory);

        $filePath = $this->buildPath($directory, $className . 'Controller.php');

        if (file_exists($filePath)) {
            $io->error("Controller {$className}Controller already exists!");
            return Command::FAILURE;
        }

        $namespace = 'App\\Controller';
        $extends = $isApi ? 'Controller' : 'Controller';
        $content = $this->generateContent($namespace, $className, $extends, $isRest, $isApi);

        file_put_contents($filePath, $content);
        $io->success("Controller {$className}Controller created successfully!");

        return Command::SUCCESS;
    }

    protected function generateContent(string $namespace, string $className, string $extends, bool $isRest, bool $isApi): string
    {
        $methods = $this->generateMethods($className, $isRest, $isApi);

        return <<<PHP
<?php

namespace {$namespace};

use SwiftPHP\Controller\Controller;
use SwiftPHP\Request\Request;
use SwiftPHP\Response\Response;

class {$className}Controller extends {$extends}
{
{$methods}
}
PHP;
    }

    protected function generateMethods(string $className, bool $isRest, bool $isApi): string
    {
        if ($isRest) {
            return <<<PHP

    public function index(?Request \$request = null): Response
    {
        return \$this->json([
            'code' => 200,
            'msg' => 'success',
            'data' => []
        ]);
    }

    public function show(?Request \$request = null): Response
    {
        \$id = \$request->param('id', 0);
        return \$this->json([
            'code' => 200,
            'msg' => 'success',
            'data' => ['id' => \$id]
        ]);
    }

    public function store(?Request \$request = null): Response
    {
        \$data = \$request->post();
        return Response::json([
            'code' => 201,
            'msg' => 'Created successfully',
            'data' => \$data
        ], 201);
    }

    public function update(?Request \$request = null): Response
    {
        \$id = \$request->param('id', 0);
        \$data = \$request->put();
        return \$this->json([
            'code' => 200,
            'msg' => 'Updated successfully',
            'data' => ['id' => \$id] + \$data
        ]);
    }

    public function destroy(?Request \$request = null): Response
    {
        \$id = \$request->param('id', 0);
        return \$this->json([
            'code' => 200,
            'msg' => 'Deleted successfully',
            'data' => ['id' => \$id]
        ]);
    }
PHP;
        }

        if ($isApi) {
            return <<<PHP

    public function index(?Request \$request = null): Response
    {
        return \$this->json([
            'code' => 200,
            'msg' => 'success',
            'data' => []
        ]);
    }

    public function create(?Request \$request = null): Response
    {
        return \$this->json([
            'code' => 200,
            'msg' => 'success',
            'data' => null
        ]);
    }

    public function save(?Request \$request = null): Response
    {
        \$data = \$request->post();
        return Response::json([
            'code' => 201,
            'msg' => 'Created successfully',
            'data' => \$data
        ], 201);
    }

    public function read(?Request \$request = null): Response
    {
        \$id = \$request->param('id', 0);
        return \$this->json([
            'code' => 200,
            'msg' => 'success',
            'data' => ['id' => \$id]
        ]);
    }

    public function update(?Request \$request = null): Response
    {
        \$id = \$request->param('id', 0);
        \$data = \$request->put();
        return \$this->json([
            'code' => 200,
            'msg' => 'Updated successfully',
            'data' => ['id' => \$id] + \$data
        ]);
    }

    public function delete(?Request \$request = null): Response
    {
        \$id = \$request->param('id', 0);
        return \$this->json([
            'code' => 200,
            'msg' => 'Deleted successfully',
            'data' => ['id' => \$id]
        ]);
    }
PHP;
        }

        return <<<PHP

    public function index(?Request \$request = null): Response
    {
        return \$this->json([
            'code' => 200,
            'msg' => 'Welcome to {$className}Controller',
            'data' => null
        ]);
    }
PHP;
    }
}
