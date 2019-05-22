<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

use Symfony\Component\Console\Tester\CommandTester;
use ETNA\Sprinter\Services\Sprinter;

use ETNA\FeatureContext\BaseContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends BaseContext
{
    /** @var CommandTester Le tester de command symfony */
    private $command_tester;

    public function __construct()
    {
    }

    /**
     * @Given je veux récupérer la routing_key par defaut
     */
    public function jeVeuxRecupererLaRoutingKeyParDefaut() {
        $this->getContext("ETNA\FeatureContext\ExceptionContainerContext")->try(
            function (Sprinter $sprinter) {
                $sprinter->getDefaultRoutingKey();
            }
        );
    }
}
