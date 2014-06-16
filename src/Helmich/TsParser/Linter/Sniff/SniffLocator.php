<?php
namespace Helmich\TsParser\Linter\Sniff;


use Helmich\TsParser\Linter\LinterConfiguration;

class SniffLocator
{



    private $sniffs = NULL;



    private function loadSniffs(LinterConfiguration $configuration)
    {
        if ($this->sniffs === NULL)
        {
            $this->sniffs = [];
            foreach ($configuration->getSniffConfigurations() as $sniffConfiguration)
            {
                if (!class_exists($sniffConfiguration['class']))
                {
                    throw new \Exception('Class "' . $sniffConfiguration['class'] . '" could not be loaded!', 1402948667);
                }

                $parameters = isset($sniffConfiguration['parameters']) ? $sniffConfiguration['parameters'] : [];
                $this->sniffs[] = new $sniffConfiguration['class']($parameters);
            }
        }
    }



    /**
     * @param LinterConfiguration $configuration
     * @return \Helmich\TsParser\Linter\Sniff\TokenStreamSniffInterface[]
     * @throws \Exception
     */
    public function getTokenStreamSniffs(LinterConfiguration $configuration)
    {
        $this->loadSniffs($configuration);

        $tokenSniffs = [];
        foreach ($this->sniffs as $sniff)
        {
            if ($sniff instanceof TokenStreamSniffInterface)
            {
                $tokenSniffs[] = $sniff;
            }
        }
        return $tokenSniffs;
    }

}