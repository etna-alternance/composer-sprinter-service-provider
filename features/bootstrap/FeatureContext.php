<?php

use TestApp\Kernel;
use Symfony\Component\Console\Tester\CommandTester;
use ETNA\Sprinter\Services\SprinterService;

use ETNA\FeatureContext\BaseContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends BaseContext
{
    /** @var CommandTester Le tester de command symfony */
    private $command_tester;

    /** @var SprinterService le service Sprinter */
    private $sprinter_service;

    public function __construct(Kernel $kernel)
    {
        $testContainer = $kernel->getContainer()->get('test.service_container');
        $this->sprinter_service = $testContainer->get(ETNA\Sprinter\Services\SprinterService::class);
    }

    /**
     * @Given je veux récupérer la routing_key par defaut
     */
    public function jeVeuxRecupererLaRoutingKeyParDefaut() {
        $sprinter = $this->sprinter_service;
        $this->getContext("ETNA\FeatureContext\ExceptionContainerContext")->try(
            function () use ($sprinter) {
                $sprinter->getDefaultRoutingKey();
            }
        );
    }
}
