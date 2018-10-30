<?php

namespace console\tests\unit;
use console\controllers\SocketController;

/**
 * Created by PhpStorm.
 * User: mike
 * Date: 30.10.2018
 * Time: 23:19
 */

class SocketTest extends \yii\codeception\TestCase {
	public $appConfig = '@console/tests/unit/_config.php';


	public function testConsumer() {
		$data = [
			'type' => 'email',
			'to' => 'to@test.org',
			'from' => 'from@test.com',
			'subject' => 'Some subject',
			'message' => 'Hello world!'
		];
		$data = json_encode($data);

		$connection = SocketController::getConnection();
		$this->assertTrue($connection->isConnected(),'Соединение с сервером не работает.');
		$this->assertTrue(SocketController::sendMail(json_decode($data)),'Почта не отправляется.');
		$this->assertTrue(SocketController::runCallback($data),'Коллбек канала не работает.');
	}
}