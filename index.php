<?php
$f3=require('lib/base.php');
$f3->config('config/config.ini');
$f3->config('config/routes.ini', true);

$f3->session = new Session();
$f3->set('ONERROR',function($f3)
{
	$db=new DB\SQL(
		$f3->get('db_dns') . $f3->get('db_name'),
		$f3->get('db_user'),
		$f3->get('db_pass')
	);
	$logger = new Log('logs/'.date("Ymd").'error.log');
	$menu=new \Page($db, $f3->get("table_prefix"));
	$f3->set("layout", $menu->getMenu());
	$f3->set("activemenulink", "");
	
	echo \Template::instance()->render('error.htm');
	$e = $f3->get('EXCEPTION');
	if(!$e instanceof Throwable) {// There is no exception when calling Base->error()
		$logger->write($f3->get('ERROR.code').": ".$f3->get('ERROR.text')." trace: ". $f3->get('ERROR.trace'), 'r');
	}
});

$f3->logger = new Log($f3->LOG_DIR.date("Ymd").'.log');
$f3->run();
