<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Linter\Sniff;

use Exception;
use Helmich\TypoScriptLint\Linter\LinterConfiguration;

class SniffLocator
{

    /** @var SniffInterface[]|null */
    private ?array $sniffs = null;

    /**
     * @param LinterConfiguration $configuration
     *
     * @return SniffInterface[]
     * @throws Exception
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
                    'Class "' . $sniffConfiguration['class'] . '" could not be loaded!', 1_402_948_667
                );
            }

            $parameters = $sniffConfiguration['parameters'] ?? [];

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
