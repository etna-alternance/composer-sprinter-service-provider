<?php

use Symfony\Component\Console\Tester\CommandTester;
use ETNA\Sprinter\Services\SprinterService;

use ETNA\FeatureContext\BaseContext;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends BaseContext
{
    public function __construct()
    {
    }

    /**
     * @BeforeScenario @Sprinter
     */
    public function createSprinterQueue()
    {
        $channel = $this->getContainer()->get("old_sound_rabbit_mq.SPrinter_producer")->getChannel();
        $channel->exchange_declare('SPrinter', 'direct', false, true, false);

        $channel->queue_declare('sprinter.lefran_f', false, true, false, false);

        $channel->queue_bind('sprinter.lefran_f', 'SPrinter', 'sprinter.lefran_f');
    }

    /**
     * @Given je veux récupérer la routing_key par defaut
     */
    public function jeVeuxRecupererLaRoutingKeyParDefaut() {
        $container = $this->getKernel()->getContainer();

        $this->getContext("ETNA\FeatureContext\ExceptionContainerContext")->try(
            function () use ($container) {
                $sprinter = $container->get('sprinter.sprinter_service');
                $sprinter->getDefaultRoutingKey();
            }
        );
    }

    /**
     * @Given je veux envoyer le template :template avec le student :student
     */
    public function jsVeuxEnvoyerLeTemplateAvecLeStudent($template, $student_array) {
        $container = $this->getKernel()->getContainer();
        $path      = $this->requests_path . "/templates";
        $uploaded  = new UploadedFile($path . "/" . $template, $template);
        $file      = file_get_contents($uploaded);
        $path_to_student = "$this->requests_path/$student_array";
        $student   = include $path_to_student;
        $this->getContext("ETNA\FeatureContext\ExceptionContainerContext")->try(
            function () use ($container, $template, $file, $student) {
                $sprinter = $container->get('sprinter.sprinter_service');
                $routing_key = $sprinter->getDefaultRoutingKey();
                $sprinter->sendPrint($template, $file, $routing_key, false, $student);
            }
        );
    }

    /**
     * @Given /le producer "([^"]*)" devrait avoir publié un message dans la queue de la routing_key par defaut$/
     */
    public function leProducerDevraitAvoirPublieUnMessageDansLaQueueDeLaRoutingKeyParDefaut($producer)
    {
        $this->fetchMessage(
            $producer,
            $this->getContainer()->getParameter('sprinter.default.routing_key')
        );
    }

    // Méthode présente dans le RabbitMQ context
    private function fetchMessage($producer, $queue)
    {
        $channel = $this->getContainer()->get("old_sound_rabbit_mq.{$producer}_producer")->getChannel();
        $message = $channel->basic_get($queue, true);
        $channel->close();

        if (null === $message) {
            throw new \Exception("Queue {$queue} is empty");
        }

        return $message->body;
    }
}

