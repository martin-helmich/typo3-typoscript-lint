<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Linter\Sniff;

use Exception;
use Helmich\TypoScriptLint\Linter\LinterConfiguration;

class SniffLocator
{

    /** @var SniffInterface[]|null */
    private $sniffs = null;

    /**
     * @param LinterConfiguration $configuration
     *
     * @return SniffInterface[]
     * @throws Exception
     *
     * @psalm-return array<int, SniffInterface>
     */
    private function loadSniffs(LinterConfiguration $configuration): array
    {
        if ($this->sniffs !== null) {
            return $this->sniffs;
        }

        $this->sniffs = [];
        foreach ($configuration->getSniffConfigurations() as $sniffConfiguration) {
            if (!class_exists($sniffConfiguration['class'])) {
                throw new Exception(
                    'Class "' . $sniffConfiguration['class'] . '" could not be loaded!', 1402948667
                );
            }

            $parameters = isset($sniffConfiguration['parameters']) ? $sniffConfiguration['parameters'] : [];

            /** @var SniffInterface $sniff */
            $sniff = new $sniffConfiguration['class']($parameters);
            $this->sniffs[] = $sniff;
        }

        return $this->sniffs;
    }

    /**
     * @param LinterConfiguration $configuration
     *
     * @return TokenStreamSniffInterface[]
     * @throws Exception
     */
    public function getTokenStreamSniffs(LinterConfiguration $configuration): array
    {
        $sniffs = $this->loadSniffs($configuration);
        $tokenSniffs = [];

        foreach ($sniffs as $sniff) {
            if ($sniff instanceof TokenStreamSniffInterface) {
                $tokenSniffs[] = $sniff;
            }
        }
        return $tokenSniffs;
    }

    /**
     * @param LinterConfiguration $configuration
     *
     * @return SyntaxTreeSniffInterface[]
     * @throws Exception
     */
    public function getSyntaxTreeSniffs(LinterConfiguration $configuration): array
    {
        $sniffs = $this->loadSniffs($configuration);
        $tokenSniffs = [];

        foreach ($sniffs as $sniff) {
            if ($sniff instanceof SyntaxTreeSniffInterface) {
                $tokenSniffs[] = $sniff;
            }
        }
        return $tokenSniffs;
    }
}
