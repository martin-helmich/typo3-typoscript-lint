<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Linter\Sniff;

use Exception;
use Helmich\TypoScriptLint\Linter\LinterConfiguration;

class SniffLocator
{

    private $sniffs = null;

    private function loadSniffs(LinterConfiguration $configuration)
    {
        if ($this->sniffs === null) {
            $this->sniffs = [];
            foreach ($configuration->getSniffConfigurations() as $sniffConfiguration) {
                if (!class_exists($sniffConfiguration['class'])) {
                    throw new Exception(
                        'Class "' . $sniffConfiguration['class'] . '" could not be loaded!', 1402948667
                    );
                }

                $parameters = isset($sniffConfiguration['parameters']) ? $sniffConfiguration['parameters'] : [];
                $this->sniffs[] = new $sniffConfiguration['class']($parameters);
            }
        }
    }

    /**
     * @param LinterConfiguration $configuration
     * @return TokenStreamSniffInterface[]
     * @throws Exception
     */
    public function getTokenStreamSniffs(LinterConfiguration $configuration): array
    {
        $this->loadSniffs($configuration);

        $tokenSniffs = [];
        foreach ($this->sniffs as $sniff) {
            if ($sniff instanceof TokenStreamSniffInterface) {
                $tokenSniffs[] = $sniff;
            }
        }
        return $tokenSniffs;
    }

    /**
     * @param LinterConfiguration $configuration
     * @return SyntaxTreeSniffInterface[]
     * @throws Exception
     */
    public function getSyntaxTreeSniffs(LinterConfiguration $configuration): array
    {
        $this->loadSniffs($configuration);

        $tokenSniffs = [];
        foreach ($this->sniffs as $sniff) {
            if ($sniff instanceof SyntaxTreeSniffInterface) {
                $tokenSniffs[] = $sniff;
            }
        }
        return $tokenSniffs;
    }
}
