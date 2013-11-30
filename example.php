<?php

require_once __DIR__ . '/coinotron.php';

try {
	$c = new Coinotron(array(
		Coinotron::COIN_LTC,
		Coinotron::COIN_FTC
	));
	$c->login('YOUR_USER', 'YOUR_PASSWORD');
	$data = $c->getAccountData();

	var_dump($data);
} catch (Exception $e) {
	echo $e->getMessage();
}

/*
header('Content-Type: application/json');
echo json_encode($data);
 */