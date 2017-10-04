<?php

namespace Spitchee\Util\Rabbit;

use Container;
use Monolog\Logger;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Silex\Tests\Application\MonologApplication;

class RabbitClient
{
    const EXCHANGE_NAME = 'spitchee';
    const DELIVERY_MODE = 'topic';

    /** @var array $config */
    private $config;

    /** @var \Monolog\Logger $logger */
    private $logger;

    /** @var AMQPChannel $channel */
    private $channel;

    /** @var AMQPStreamConnection $connection */
    private $connection;

    public function __construct($config, Logger $logger)
    {
        $this->config = $config;
        $this->logger = $logger;

        $this->instanciate();
    }

    private function instanciate()
    {
        $this->connection = new AMQPStreamConnection(
            $this->config['host'],
            $this->config['port'],
            $this->config['user'],
            $this->config['password']
        );

        $this->channel = $this->connection->channel();

        $this->channel->exchange_declare(
            self::EXCHANGE_NAME, self::DELIVERY_MODE,
            false, false, false
        );
    }

    public function publish(AMQPMessage $message, $routingKey)
    {
        $this->channel->basic_publish($message, self::EXCHANGE_NAME, $routingKey);
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}