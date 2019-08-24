<?php

require dirname(__DIR__). '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

$host = 'eagle-01.rmq.cloudamqp.com'; 
$user = 'qmftefgy'; 
$pass = 'WsT0_0QNnIuMKHotuqG01uNE8RUNi6mS'; 
$port = '5672'; 
$vhost = 'qmftefgy';
$exchange = 'subscribers';
$queue = 'gurucoder_subscribers';

$connection = new AMQPStreamConnection($host, $port, $user, $pass, $vhost);
$channel = $connection->channel();

$channel->queue_declare($queue, false, true, false, false);


$channel->exchange_declare($exchange, AMQPExchangeType::DIRECT, false, true, false);
$channel->queue_bind($queue, $exchange);


function process_message(AMQPMessage $message)
{
    $messageBody = json_decode($message->body);
    $email = $messageBody->email;
    file_put_contents(dirname(__DIR__). '/data/'. $email.'.json', $message->body);
    $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
}

$consumerTag = 'local.imac.consumer';
$channel->basic_consume($queue, $consumerTag, false, false, false, false, 'process_message');


function shutdown($channel, $connection)
{
    $channel->close();
    $connection->close();
}
register_shutdown_function('shutdown', $channel, $connection);

while ($channel ->is_consuming()) {
    $channel->wait();
}