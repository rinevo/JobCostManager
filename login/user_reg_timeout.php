<?php
require_once(dirname(__FILE__).'/../db_config.php');
require_once(dirname(__FILE__).'/class/UserListSQL.class.php');
require_once(dirname(__FILE__).'/../class/AccessLogSQL.class.php');

// アクセスログ
$log = new AccessLogSQL($GLOBALS['dbopts']);
$log->Write(0, '', __FILE__, __FUNCTION__, __LINE__);

// 仮登録ユーザーのタイムアウト処理
$edit = new UserListSQL($dbopts);
$list = $edit->entryTimeoutUser();

foreach ($list as $info) {
	if ($info['ret']) {
		$log->Write(0, 'タイムアウトしたため、仮登録ユーザー「'.$info['mail'].'」を削除しました。', __FILE__, __FUNCTION__, __LINE__);
	} else {
		$log->Write(1, '仮登録ユーザー「'.$info['mail'].'」の削除に失敗しました。'.$edit->getMessage(), __FILE__, __FUNCTION__, __LINE__);
	}
}

exit(0);
