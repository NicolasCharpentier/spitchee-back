<?php

require __DIR__ . '/../../../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

//(new Receiver())($argc, $argv);

$r = new Receiver();
$r($argc, $argv);

class Receiver
{
    public function __invoke($argc, $argv)
    {
        //$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $connection = new AMQPStreamConnection('51.254.119.247', 5672, 'toubabmechant', 'tls');
        $channel = $connection->channel();

        $channel->exchange_declare('spitchee', 'topic', false, false, false);

        $queueName = $channel->queue_declare('', false, false, true, false)[0];

        $bindingKeys = array_slice($argv, 1);
        if( empty($bindingKeys )) {
            file_put_contents('php://stderr', "Usage: $argv[0] [binding_key]\n");
            exit(1);
        }

        foreach ($bindingKeys as $bindingKey) {
            $channel->queue_bind($queueName, 'spitchee', $bindingKey);
        }

        echo 'En attente des messages', PHP_EOL;

        $channel->basic_consume($queueName, '', false, true, false, false, function ($msg) {
            $date = (new \DateTime())->format('H:i:s');

            echo "[$date] ", $msg->delivery_info['routing_key'], ':', $msg->body, PHP_EOL;
        });

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}