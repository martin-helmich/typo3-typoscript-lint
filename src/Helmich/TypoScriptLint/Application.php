<?php
namespace Helmich\TypoScriptLint;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\DependencyInjection\Container;

class Application extends SymfonyApplication
{

    /** @var \Symfony\Component\DependencyInjection\Container */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        parent::__construct(APP_NAME, APP_VERSION);
    }

    protected function getCommandName(InputInterface $input)
    {
        return 'lint';
    }

    protected function getDefaultCommands()
    {
        $defaultCommands   = parent::getDefaultCommands();
        $defaultCommands[] = $this->container->get('lint_command');

        return $defaultCommands;
    }

    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}
