<?php
require_once(dirname(__FILE__).'/myauth.php');
require_once(dirname(__FILE__).'/../define.php');
require_once(dirname(__FILE__).'/../db_config.php');
require_once(dirname(__FILE__).'/class/UserListSQL.class.php');

// アクセスログ
require_once(dirname(__FILE__).'/../class/AccessLogSQL.class.php');
$log = new AccessLogSQL($GLOBALS['dbopts']);
$log->Write(0, '', __FILE__, __FUNCTION__, __LINE__);

if (isset($_POST['user_edit_type'])) {

	$edit = new UserListSQL($dbopts);
	$info = '';

	$_POST['user_edit_uid'] = $GLOBALS['auth']->getParent_uid();
	if (strlen($_POST['user_edit_passwd']) < 1) {
		unset($_POST['user_edit_passwd']);
	} else {
		$info = 'パスワード';
	}
	if (strlen($_POST['user_edit_mail']) < 1) {
		unset($_POST['user_edit_mail']);
	} else {
		$info = 'メールアドレス';
	}
	if (strlen($_POST['user_edit_name']) < 1) {
		unset($_POST['user_edit_name']);
	} else {
		$info = '名前';
	}
	unset($_POST['user_edit_param']);
	unset($_POST['user_edit_status']);
	unset($_POST['user_edit_role']);

	switch ($_POST["user_edit_type"]) {
		case "UPDATE":
			$ret = $edit->startEdit();
			if ($ret) {
				$ret = $edit->updateUser();
				if ($ret) {
					$edit->getParentInfo($_POST['user_edit_uid']);
					$_SESSION[S_MESSAGE2] = $info.'を変更しました。';
				}
				$edit->endEdit();
			}
			break;
		case "DELETE":
			//パスワード確認
			if ($edit->getParent_passwd() == $_POST['user_edit_passwd']) {
				//アカウント削除
				$ret = $edit->deleteUser();
				if ($ret) {
					require_once(dirname(__FILE__).'/logout_control.php');
					exit();
				}
			} else {
				$_SESSION[S_MESSAGE] = '正しいパスワードを入力してください。';
				$ret = true;
			}
			break;
		default:
			header("HTTP/1.0 404 Not Found");
			return;
	}

	if (!$ret) {
		$_SESSION[S_MESSAGE] = $edit->getMessage();
	}

	header('Location: '.$_SERVER['HTTP_REFERER']);
	return;
}

//上記処理が実行されなければ不正なアクセスとして扱う
header("HTTP/1.0 404 Not Found");
