<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint;

use Exception;
use Helmich\TypoScriptLint\Command\LintCommand;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\DependencyInjection\Container;

class Application extends SymfonyApplication
{
    public const APP_NAME = 'typoscript-lint';
    public const APP_VERSION = 'dev';

    /** @var Container */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        parent::__construct(static::APP_NAME, static::APP_VERSION);
    }

    protected function getCommandName(InputInterface $input): string
    {
        return 'lint';
    }

    /**
     * @return Command[]
     * @throws Exception
     */
    protected function getDefaultCommands(): array
    {
        /** @var LintCommand $lintCommand */
        $lintCommand = $this->container->get("lint_command");

        $defaultCommands = parent::getDefaultCommands();
        $defaultCommands[] = $lintCommand;

        return $defaultCommands;
    }

    public function getDefinition(): InputDefinition
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
     */
    public function getVersion(): string
    {
        $current = dirname(__FILE__);
        while ($current !== '/') {
            if (file_exists($current . '/composer.lock')) {
                $contents = file_get_contents($current . '/composer.lock');
                if ($contents === false) {
                    continue;
                }

                $data = json_decode($contents);
                $packages = array_values(array_filter($data->packages, function (\stdClass $package): bool {
                    return $package->name === "helmich/typo3-typoscript-lint";
                }));

                if (count($packages) > 0) {
                    return $packages[0]->version;
                }
            }

            $current = dirname($current);
        }

        return parent::getVersion();
    }
}
