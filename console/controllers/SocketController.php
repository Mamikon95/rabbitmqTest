<?php
namespace console\controllers;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Created by PhpStorm.
 * User: mike
 * Date: 30.10.2018
 * Time: 14:34
 */

class SocketController extends \yii\console\Controller {


	public function actionReceive() {
		$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
		$channel = $connection->channel();

		$callback = function ($msg) {
			$data = json_decode($msg->body);

			$queue_accept = true;
			if($data->type != 'email') {
				$queue_accept = false;
			}

			if(!$this->sendMail($data)) {
				$queue_accept = false; //TODO:: возможно дальше пригодится для отмены выхода из очереди
			}
		};

		$channel->basic_consume('test_queue', '', false, true, false, false, $callback);

		while (count($channel->callbacks)) {
			$channel->wait();
		}
	}

	public function actionSend() {
		$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
		$channel = $connection->channel();
		$data = ['type' => 'email', 'to' => 'to@test.org', 'from' => 'from@test.com', 'subject' => 'Some subject', 'message' => 'Hello world!'];
		$channel->queue_declare('test_queue', false, false, false, false);

		$msg = new AMQPMessage(json_encode($data));
		$channel->basic_publish($msg, '', 'test_queue');

		$channel->close();
		$connection->close();
	}

	private function sendMail($data) {
		$headers = "From: " . $data->from . "\r\n";
		return mail($data->to,$data->subject,$data->message,$headers); //TODO:: можео и использовать mail от фреймворка
	}
}