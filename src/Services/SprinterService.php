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
     * @param array  $sprinter_data  l'entité servant à la composition des tokens
     * @param array  $sprinter_opt   les options de l'impression
     *
     * @return string
     */
    public function sendPrint(
        $template_title,
        $template,
        // true | false
        $print_flag,
        $routing_key = null,
        // student or contract, entities
        array $sprinter_data = [],
        // exemple: ["printFlag" => $paper]
        array $sprinter_opt = []
    ) {
        if (!\is_array($sprinter_data) || empty($sprinter_data)) {
            throw new \Exception('Bad data provided for printing', 400);
        }
        $template_b64 = base64_encode($template);
        $csv          = $this->arrayToCsv($sprinter_data, $sprinter_opt['csv_rows']);
        $csv_base64   = base64_encode($csv);
        $template     = [
            'Name'    => $template_title,
            'Content' => $template_b64,
        ];
        $data         = [
            'Name'    => 'data.csv',
            'Content' => $csv_base64,
        ];
        $queue_opt    = [
            'passive'     => false,
            'durable'     => true,
            'exclusive'   => false,
            'auto_delete' => false,
        ];
        $params       = [
            'template'   => $template,
            'data'       => $data,
            'printflag'  => $print_flag,
        ];
        $queue_and_params = array_merge($params, $queue_opt);
        $routing_key      = $routing_key ?: $this->routing_key;
        $msgBody          = json_encode($queue_and_params);
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

    /**
     * Cette fonction appartient à la lib etna/CSVUtils
     * Elle est donc destinée à bouger quand la lib sera migrée.
     *
     * Prends un tableau PHP et en génère un csv
     *
     * @param array    $array    Array a transformer
     * @param int|null $csv_rows Nombre de rows générées
     *
     * @return string
     */
    private function arrayToCsv(array $array, int &$csv_rows = null)
    {
        // impossible que $array soit vide, la méthode est privée
        // et la l.77 vérifie déjà que les data sont correctes
        if (true === empty($array)) {
            $csv_rows = 0;

            return '';
        }
        $headers = array_keys($array[0]);
        $tokens  = array_values($array);
        $csv     = self::sputcsv($headers, ';', '"', "\n");
        foreach ($tokens as $value) {
            if (!empty(array_diff(array_keys($value), $headers))) {
                throw new \Exception('Bad csv', 400);
            }
            $clean_array = str_replace("\n", ' ', array_values($value));
            $csv .= self::sputcsv($clean_array, ';', '"', "\n");
        }
        if (false === $csv) {
            throw new \Exception('Convert string to csv failed', 500);
        }
        $csv      = substr_replace($csv, '', -1);
        $csv_rows = \count($tokens);

        return $csv;
    }

    /**
     * Cette fonction appartient à la lib etna/CSVUtils
     * Elle est donc destinée à bouger quand la lib sera migrée.
     *
     * Vu que sputcsv n'existe pas dans php :'(
     * fonction qui retourne une string csv a partir de l'array fourni
     *
     * @param array  $row       Le tableau contenant les données à csvifier
     * @param string $delimiter Le caractère délimitant les champs csv
     * @param string $enclosure Le caractère à utiliser pour echapper
     * @param string $eol       Le caractère d'EndOfFile
     *
     * @return false|string
     */
    private function sputcsv(array $row, $delimiter = ',', $enclosure = '"', $eol = "\n")
    {
        static $file_pointer = false;

        if (false === $file_pointer) {
            $file_pointer = fopen('php://temp', 'r+');
        } else {
            rewind($file_pointer);
        }
        if (false === fputcsv($file_pointer, $row, $delimiter, $enclosure)) {
            return false;
        }
        rewind($file_pointer);

        if (false === ($csv = fgets($file_pointer))) {
            return false;
        }
        $csv = (string) $csv;
        if (PHP_EOL !== $eol) {
            $csv = substr($csv, 0, (0 - \strlen(PHP_EOL))) . $eol;
        }

        return $csv;
    }
}
