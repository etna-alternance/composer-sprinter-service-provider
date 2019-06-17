<?php
/**
 * PHP version 7.1
 * @author BLU <dev@etna-alternance.net>
 */

declare(strict_types=1);

namespace ETNA\Sprinter\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * La classe Sprinter est le service déclaré dans config/service.yaml
 * Cette classe accède au container du projet et stocke le producer rabbitMQ et la routing_key.
 */
class SprinterService
{
    /** @var ContainerInterface
     *  Conteneur de l'application symfony ou sont référencés les paramètres */
    private $container;

    /**
     * décrit un producer rabbitMQ, un producer permet de publier sur une queue */
    private $producer;

    /** @var string décrit le nom du channel par défaut * */
    private $routing_key;

    /**
     * Constructeur du service.
     *
     * @param ContainerInterface $container Le container de l'application symfony
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container   = $container;
        $this->producer    = $container->get('old_sound_rabbit_mq.SPrinter_producer');
        $this->routing_key = $container->getParameter('sprinter.default.routing_key');
    }

    /**
     * Getter sur routing_key.
     *
     * @return string
     */
    public function getDefaultRoutingKey()
    {
        return $this->routing_key;
    }

    /**
     * sendPrint est la fonction principale du service.
     * Elle compose un groupe de paramètres nécessaires à une impression
     * sur Sprinter et publie ces paramètres sur le producer Sprinter de rabbitMQ.
     *
     * @param string $template_title le nom du fichier template
     * @param string $template       le contenu du template extrait avec file_get_content
     * @param bool   $print_flag     paramètre permettant de déterminer si l'on souhaite une impression papier
     * @param string $routing_key    la routing_key sur laquelle la publication doit avoir lieu
     * @param string $sprinter_data  l'entité servant à la composition des tokens, string en format CSV
     *
     * @return string
     */
    public function sendPrint(
        $template_title,
        $template,
        // true | false
        $print_flag,
        $routing_key = null,
        // string on csv format
        $sprinter_data = ''
    ) {
        if (!\is_string($sprinter_data) || empty($sprinter_data)) {
            throw new \Exception('Bad data provided for printing', 400);
        }
        $template_b64 = base64_encode($template);
        $template     = [
            'Name'    => $template_title,
            'Content' => $template_b64,
        ];
        $sprinter_data64 = base64_encode($sprinter_data);
        $data            = [
            'Name'    => 'data.csv',
            'Content' => $sprinter_data64,
        ];
        $params       = [
            'template'   => $template,
            'data'       => $data,
            'printflag'  => $print_flag,
        ];
        $routing_key      = $routing_key ?: $this->routing_key;
        $msgBody          = json_encode($params);
        if (false === $msgBody) {
            throw new \Exception(
                'Encoding message to producer failed',
                500
            );
        }
        $routing_key = $routing_key ?: $this->routing_key;
        $this->producer->publish(json_encode($params), $routing_key);

        return $routing_key;
    }
}
