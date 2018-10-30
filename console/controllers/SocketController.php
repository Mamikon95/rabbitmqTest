<?php
namespace console\controllers;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use yii\helpers\ArrayHelper;

/**
 * Created by PhpStorm.
 * User: mike
 * Date: 30.10.2018
 * Time: 14:34
 */

class SocketController extends \yii\console\Controller {

	/**
	 * @throws \ErrorException
	 */
	public function actionReceive() {

		$connection = self::getConnection();
		$channel = self::getChannel($connection);

		$callback = function($msg) {
			self::runCallback($msg->body);
		};

		$channel->basic_consume('test_queue', '', false, true, false, false, $callback);

		while (count($channel->callbacks)) {
			$channel->wait();
		}
	}

	/**
	 *
	 */
	public function actionSend() {
		$connection = self::getConnection();
		$channel = self::getChannel($connection);
		$data = ['type' => 'email', 'to' => 'to@test.org', 'from' => 'from@test.com', 'subject' => 'Some subject', 'message' => 'Hello world!'];
		$channel->queue_declare('test_queue', false, false, false, false);

		$msg = new AMQPMessage(json_encode($data));
		$channel->basic_publish($msg, '', 'test_queue');

		$channel->close();
		$connection->close();
	}

	/**
	 * @param $data
	 * @return bool
	 */
	public static function sendMail($data): bool {
		if (!filter_var($data->from , FILTER_VALIDATE_EMAIL) || !filter_var($data->to , FILTER_VALIDATE_EMAIL)) {
			return false;
		}

		$headers = "From: " . $data->from . "\r\n";
		return mail($data->to,$data->subject,$data->message,$headers); //TODO:: можео и использовать mail от фреймворка
	}

	public static function getChannel(AMQPStreamConnection $connection) {
		return $connection->channel();
	}

	public static function getConnection() {
		return new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
	}

	public static function runCallback($msg = ''): bool {
		$queue_accept = false;

		if($msg) {
			$data = json_decode($msg);

			if($data) {
				$queue_accept = true;

				if($data->type != 'email') {
					$queue_accept = false;
				}

				if(!self::sendMail($data)) {
					$queue_accept = false; //TODO:: возможно дальше пригодится для отмены выхода из очереди
				}
			}
		}

		return $queue_accept;
	}
}