<?php

require __DIR__ . '/../../../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

//(new Sender())($argv, $argc);
$s = new Sender();
$s($argv, $argc);

class Sender
{
    public function __invoke($argv, $argc)
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->exchange_declare('spitchee', 'topic', false, false, false);

        $routingKey = $argc < 2 ? 'conf.all' : $argv[1];

        $data = $argc < 3 ? 'message' : $argv[2];

        $message = new AMQPMessage($data);

        $channel->basic_publish($message, 'spitchee', $routingKey);

        echo "$data envoyé en $routingKey\n";

        $channel->close();
        $connection->close();
    }
}