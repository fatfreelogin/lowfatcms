<?php

require_once "vendor/autoload.php";

$f3 = \Base::instance();
$f3->config('config/config.ini');
$f3->config('config/routes.ini', true);

$f3->session = new Session();
$f3->set('ONERROR', function ($f3) {
	$logger = new Log('logs/' . date("Ymd") . 'error.log');
	echo \Template::instance()->render('error.htm');
	$e = $f3->get('EXCEPTION');
	if (!$e instanceof Throwable) { // There is no exception when calling Base->error()
		$logger->write($f3->get('ERROR.code') . ": " . $f3->get('ERROR.text') . " trace: " . $f3->get('ERROR.trace'), 'r');
	}
});

$f3->logger = new Log($f3->LOG_DIR . date("Ymd") . '.log');
$f3->run();
