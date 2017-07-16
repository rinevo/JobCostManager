<?php
require_once(dirname(__FILE__).'/../db_config.php');
require_once(dirname(__FILE__).'/class/Form_Digest_Auth.class.php');

if (isset($nonauth) && $nonauth == 1) {
	return;
}

global $auth;
$auth = new Form_Digest_Auth($GLOBALS['dbopts']);
$auth->start();

//if ($auth->getAuth()) {
//	echo "認証成功！";
//}
