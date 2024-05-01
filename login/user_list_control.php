<?php
require_once(dirname(__FILE__).'/myauth.php');
require_once(dirname(__FILE__).'/../define.php');
require_once(dirname(__FILE__).'/../db_config.php');
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
		go_referer('メールアドレスを入力してください。');
	}
	if (strlen($to) > 100) {
		go_referer('メールアドレスは100文字以内で入力してください。');
	}
	if (!preg_match('/^\w+([-+.\']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/iD', $to)) {
		go_referer('不正なメールアドレスです。<br/>正しいメールアドレスで登録してください。');
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
	$subject = '['.$domain.'] グループ「'.$GLOBALS['auth']->getParent_group_name().'」へのご招待';
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

{$GLOBALS['auth']->getParent_name()} 様より、グループ名「{$GLOBALS['auth']->getParent_group_name()}」へのご招待です。
以下のURLからユーザー登録してグループに参加してください。

{$url}

--------------------------------------------------
{$domain}
BODY;

	//メール配信
	mb_language('ja');
	$ret = mb_send_mail($to, $subject, $body, $headers);

	if ($ret == true) {

	} else {

		//メール配信に失敗したら仮登録したユーザーを削除
		$edit->deleteUser();

		//登録画面に戻る
		go_referer('メール配信できません。');

	}

	return;
}

// POST処理
if (isset($_POST['user_edit_type'])) {

	global $edit;

	switch ($_POST["user_edit_type"]) {
		case "UPDATE":
			$ret = $edit->updateUser();
			if ($ret) {
				$edit->endEdit();
			}
			unset($_SESSION[S_USER_EDIT_UID]);
			break;
		case "DELETE":
			$ret = $edit->deleteMember();
			break;
		case "EDIT_START":
			$ret = $edit->endEdit();
			if ($ret) {
				$ret = $edit->startEdit();
				if ($ret) {
					$_SESSION[S_USER_EDIT_UID] = $_POST['user_edit_uid'];
				}
			}
			break;
		case "EDIT_END":
			$ret = $edit->endEdit();
			if ($ret) {
				unset($_SESSION[S_USER_EDIT_UID]);
			}
			break;
		case "INVITE":
			//招待メール送信
			user_request();
			$_SESSION[S_MESSAGE2] = 'メールアドレス「'.e($_POST['user_edit_mail']).'」へグループへの招待メールを送信しました。';
			break;
		case 'INSERT':
			$userinfo = $edit->getUserInfo($_POST['user_edit_uid'],'uid',0);
			if (!$userinfo) {
				//未登録ならばユーザー登録とメンバー追加
				$_POST['user_edit_status'] = USER_STATUS_NOMAL;
				$ret = $edit->addUser();
				if ($ret) {
					//ホームグループを作成
					$group = new GroupListSQL($GLOBALS['dbopts']);
					$group_no = $group->addItem(0, $_POST['user_edit_uid'], 'ホーム');
					if ($group_no > 0) {
						//ホームグループをデフォルトにする
						$_POST['user_edit_group_no_default'] = $group_no;
						$ret = $edit->updateUser();
						if ($ret) {
							//ユーザー追加したグループへ参加
							$ret = $edit->enabledMember($_POST['user_edit_uid']);
						}
					}
				}
				if (!$ret) {
					//失敗したら削除
					$edit->deleteMember();
					$edit->deleteUser($_POST['user_edit_uid']);
				}
			} else {
				//ユーザー登録済
				if ($_POST['user_edit_mail'] == $userinfo['mail']) {
					//グループへ無効な権限で仮登録する
					$_POST['user_edit_uid'] = $userinfo['uid'];
					$_POST['user_edit_role'] = 0; //「無効」で登録
					$ret = $edit->addMember();
					if ($ret) {
						//ユーザー追加したグループへ参加
						//$ret = $edit->enabledMember($_POST['user_edit_uid']); //氏名（個人情報）を晒すことになるのでダメ
						if ($ret) {
							$user_name = ($userinfo['status'] == USER_STATUS_ENTRY) ? '(仮登録ユーザー)' : $userinfo['uid'];
							$tmp = 'ユーザー名「'.e($userinfo['uid']).'」で、既に登録済みの「'.e($user_name).'」さんを、このグループにアクセスできない権限（無効）で仮登録しました。<br>';
							$tmp.= 'アクセス可能な権限に変更されるまで、「'.e($user_name).'」さんのグループ一覧に、このグループは表示されません。<br>';
							$tmp.= '追加したいユーザーに間違いが無いことを確認してから権限をアクセス可能なものに変更してください。<br>';
							$tmp.= 'アクセス可能な権限になると、「'.e($user_name).'」さんのグループ一覧に、このグループが表示されるようになります。';
							$_SESSION[S_MESSAGE2] = $tmp;
						}
					}
				} else {
					$_SESSION[S_MESSAGE] = 'ユーザー名「'.e($_POST['user_edit_uid']).'」は、他のユーザーが使用中のため、登録できません。';
					$ret = true;
				}
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
