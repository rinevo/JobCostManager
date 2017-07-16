<?php
require_once(dirname(__FILE__).'/../define.php');
require_once(dirname(__FILE__).'/../db_config.php');
require_once(dirname(__FILE__).'/class/Form_Digest_Auth.class.php');

// アクセスログ
require_once(dirname(__FILE__).'/../class/AccessLogSQL.class.php');
$log = new AccessLogSQL($GLOBALS['dbopts']);
$log->Write(0, '', __FILE__, __FUNCTION__, __LINE__);

global $auth;
global $dbopts;

if (!isset($auth)) {
	$auth = new Form_Digest_Auth($dbopts);
}

if ($auth->getAuth()) {
	// ログイン中のとき

	// Cookie削除
	$auth->delete_cookie();	// ここでは消せないっぽい。ログインページへ遷移したときに消せる。

	// ログアウトページ
	header('Location: '.PROJECT_ROOT.'/login/logout.php');
} else {
	// 既にログアウトしていたらログインページへ遷移
	header('Location: '.PROJECT_ROOT.'/');
}
