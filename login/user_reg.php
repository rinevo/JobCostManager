<?php
require_once(dirname(__FILE__).'/../define.php');
require_once(dirname(__FILE__).'/../db_config.php');
require_once(dirname(__FILE__).'/../Encode.php');
require_once(dirname(__FILE__).'/../common.php');
require_once(dirname(__FILE__).'/class/UserListSQL.class.php');
require_once(dirname(__FILE__).'/class/GroupListSQL.class.php');

// アクセスログ
require_once(dirname(__FILE__).'/../class/AccessLogSQL.class.php');
$log = new AccessLogSQL($GLOBALS['dbopts']);
$log->Write(0, '', __FILE__, __FUNCTION__, __LINE__);

$edit = new UserListSQL($dbopts);

// ユーザー登録申請
function user_request() {

	global $edit;

	//入力値のチェック
	$to = e(isset($_POST['user_edit_mail']) ? $_POST['user_edit_mail'] : '');

	if (strlen($to) < 1) {
		go_login('メールアドレスを入力してください。');
	}

	if (strlen($to) > 100) {
		go_login('メールアドレスは100文字以内で入力してください。');
	}
	if (!preg_match('/^\w+([-+.\']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/iD', $to)) {
		go_login('不正なメールアドレスです。<br/>正しいメールアドレスで登録してください。');
	}

	//ユーザー仮登録
	$_POST['user_edit_uid'] =  md5(uniqid());
	$_POST['user_edit_passwd'] = '';
	$_POST['user_edit_name'] = '';
	$_POST['user_edit_status'] = USER_STATUS_ENTRY;
	$edit->addUser();

	//ユーザー情報を取得
	$userinfo = $edit->getUserInfo($_POST['user_edit_uid']);

	//メール文書生成
	$domain = MYDOMAIN;
	$subject = '['.$domain.'] ユーザー登録用URLお知らせ';
	$from = MAIL_FROM;
	$to = $userinfo['mail'];
	$uid = e($userinfo['uid']);
	//$url = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/user_entry.php?uid='.$uid.'&param='.$userinfo['param'];
	preg_match('|^(https?://.+?)/|i', $_SERVER['HTTP_REFERER'], $url);
	$url = $url[1].dirname($_SERVER['PHP_SELF']).'/user_entry.php?uid='.$uid.'&param='.$userinfo['param'];

	$headers = <<<HEAD
From: {$from}
Return-Path: {$from}
HEAD;

	$body = <<<BODY
{$to} 様

ユーザー登録のお申込みありがとうございます。
登録を完了するために以下のURLをクリックしてユーザー登録処理を続けてください。

{$url}

--------------------------------------------------
{$domain}
BODY;

	//メール配信
	mb_language('ja');
	$ret = mb_send_mail($to, $subject, $body, $headers);

	if ($ret == true) {

		//POSTされたユーザー情報をクリア
		$_POST['user_edit_uid'] = '';
		$_POST['user_edit_mail'] = '';
		$_POST['user_edit_passwd'] = '';

		//メール配信の通知
		go_login('入力されたメールアドレスにメールを配信しました。<br/>メールの内容を確認して、アカウントを有効にしてください。');

	} else {

		//メール配信に失敗したら仮登録したユーザーを削除
		$edit->deleteUser();

		//登録画面に戻る
		go_login('メール配信できません。');

	}

	return;
}

//ユーザー登録
function user_entry() {

	global $edit;

	//入力値のチェック
	$uid = isset($_POST['user_edit_uid']) ? $_POST['user_edit_uid'] : '';
	$newuid = isset($_POST['user_edit_newuid']) ? $_POST['user_edit_newuid'] : '';
	$passwd = isset($_POST['user_edit_passwd']) ? $_POST['user_edit_passwd'] : '';
	$name = isset($_POST['user_edit_name']) ? $_POST['user_edit_name'] : '';

	if (strlen($newuid) < 1) {
		go_referer('ユーザー名を入力してください。');
	}
	if (!preg_match("/[\@-\~]/", $newuid)) {
		go_referer('ユーザー名は半角英数字及び記号のみ入力してください。');
	}
	if (strlen($newuid) > 50) {
		go_referer('ユーザー名は50文字以内で入力してください。');
	}

	if (strlen($passwd) < 1) {
		go_referer('パスワードを入力してください。');
	}
	if (!preg_match("/[\@-\~]/", $passwd)) {
		go_referer('パスワードは半角英数字及び記号のみ入力してください。');
	}
	if (strlen($passwd) > 50) {
		go_referer('パスワードは50文字以内で入力してください。');
	}

	if (strlen($name) < 1) {
		go_referer('名前を入力してください。');
	}
	if (strlen($name) > 50) {
		go_referer('名前は50文字以内で入力してください。');
	}

	$userinfo = $edit->getUserInfo($uid);
	if (!$userinfo) {
		header("HTTP/1.0 404 Not Found");
		return;
	}

	//ユーザーを有効にする
	$ret = $edit->enabledUser();
	if (!$ret) {
		go_referer($edit->getMessage());
	}

	//ホームグループを作成
	$group = new GroupListSQL($GLOBALS['dbopts']);
	$group_no = $group->addItem(0, $newuid, 'ホーム');
	if ($group_no < 1) {
		go_referer($edit->getMessage());
	}

	//ホームグループをデフォルトにする
	$_POST['user_edit_group_no_default'] = $group_no;
	$_POST['user_edit_uid'] = $newuid;
	$ret = $edit->updateUser();
	if (!$ret) {
		go_referer($edit->getMessage());
	}

	$userinfo = $edit->getUserInfo($newuid);
	if ($userinfo) {
		//認証情報を格納
		session_start();
		$_SESSION[S_TOKEN] = md5(uniqid());
		$_POST['username'] = $userinfo['uid'];
		$_POST['password'] = hash_hmac('sha256', $userinfo['uid'].':'.$userinfo['passwd'], $_SESSION[S_TOKEN]);
		$_POST['hash'] = 1;
	}

	//登録完了の通知
	go_login('ユーザー登録が完了しました。');

	return;
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {

	if (isset($_POST['user_edit_uid']) && isset($_POST['user_edit_passwd'])) {
		//ユーザー登録
		user_entry();
	} else {
		//ユーザー登録申請
		user_request();
	}

	return;
}

//ユーザー登録
if ($_SERVER['REQUEST_METHOD'] == "GET") {

	global $edit;

	$uid = isset($_GET['uid']) ? $_GET['uid'] : '';
	$param = isset($_GET['param']) ? $_GET['param'] : '';

	$userinfo = $edit->getUserInfo($uid);

	if (!isset($userinfo['status']) || ($userinfo['status'] != 1) || ($userinfo['param'] != $param)) {
		header("HTTP/1.0 404 Not Found");
		return;
	}

	//新規ユーザー登録画面を表示
	require_once(dirname(__FILE__).'/user_entry.php');
	exit();
}

//POST、GETのアクセスで無ければ不正なアクセスとして扱う
header("HTTP/1.0 404 Not Found");
