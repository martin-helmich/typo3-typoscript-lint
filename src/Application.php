<?php
namespace Helmich\TypoScriptLint;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\DependencyInjection\Container;

class Application extends SymfonyApplication
{

    /** @var Container */
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

    /**
     * Gets the currently installed version number.
     *
     * In contrast to the overridden parent method, this variant is Composer-aware and
     * will its own version from the first-best composer.lock file that it can find.
     *
     * @see https://github.com/martin-helmich/typo3-typoscript-lint/issues/35
     * @return string
     */
    public function getVersion()
    {
        $current = dirname(__FILE__);
        while($current !== '/') {
            if (file_exists($current . '/composer.lock')) {
                $contents = file_get_contents($current . '/composer.lock');
                $data = json_decode($contents);
                $packages = array_filter($data->packages, function($package) {
                    return $package->name === "helmich/typo3-typoscript-lint";
                });

                if (count($packages) > 0) {
                    return $packages[0]->version;
                }
            }

            $current = dirname($current);
        }

        return parent::getVersion();
    }


}
