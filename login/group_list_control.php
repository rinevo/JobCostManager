<?php
require_once(dirname(__FILE__).'/myauth.php');
require_once(dirname(__FILE__).'/../define.php');
require_once(dirname(__FILE__).'/../db_config.php');
require_once(dirname(__FILE__).'/../Encode.php');
require_once(dirname(__FILE__).'/class/GroupListSQL.class.php');
require_once(dirname(__FILE__).'/class/UserListSQL.class.php');

// アクセスログ
require_once(dirname(__FILE__).'/../class/AccessLogSQL.class.php');
$log = new AccessLogSQL($GLOBALS['dbopts']);
$log->Write(0, '', __FILE__, __FUNCTION__, __LINE__);

$edit = new GroupListSQL($dbopts);

// POST処理
if (isset($_POST['group_edit_type'])) {

	$user = new UserListSQL($dbopts);
	$info = '';

	if (strlen($_POST['group_edit_name']) < 1) {
		unset($_POST['group_edit_name']);
	} else {
		$info = 'グループ名';
	}

	switch ($_POST["group_edit_type"]) {
		case "INSERT":
			$ret = $edit->addItem(1, $GLOBALS['auth']->getParent_uid(), $_POST['group_edit_name']);
			if ($ret > 0) {
				$_SESSION[S_MESSAGE2] = $info.'「'.e($_POST['group_edit_name']).'」を作成しました。';
			}
			break;
		case "UPDATE":
			$ret = $edit->updateItem();
			if ($ret) {
				$_SESSION[S_MESSAGE2] = $info.'「'.e($_POST['group_edit_name']).'」を更新しました。';
			}
			break;
		case "DELETE":
			if ($user->getParent_passwd() == $_POST['group_edit_passwd']) {
				$ret = $edit->deleteItem();
				if ($ret) {
					$_SESSION[S_MESSAGE2] = $info.'「'.e($_POST['group_edit_name']).'」を削除しました。';
					if ($GLOBALS['auth']->getParent_group_no() == $_POST['group_edit_no']) {
						$ret = $GLOBALS['auth']->setParent_group_no($edit->getHome($GLOBALS['auth']->getParent_uid()));
					}
				}
			} else {
				$_SESSION[S_MESSAGE] = '正しいパスワードを入力してください。';
				$ret = true;
			}
			break;
		case "ACCESS":
			$ret = $GLOBALS['auth']->setParent_group_no($_POST['group_edit_no']);
			if ($ret) {
				if ($GLOBALS['auth']->getParent_member_status() == GROUP_MEMBER_STATUS_ENTRY) {
					$ret = $user->enabledMember();
					if ($ret) {
						$_SESSION[S_MESSAGE2] = $info.'「'.e($_POST['group_edit_name']).'」へ参加しました。';
					}
				} else {
					$_SESSION[S_MESSAGE2] = $info.'「'.e($_POST['group_edit_name']).'」へ移動しました。';
				}
			} else {
				$_SESSION[S_MESSAGE2] = '<font color="red">'.$info.'「'.e($_POST['group_edit_name']).'」へ移動できません。</font>';
			}
			break;
		case "UNSUB":
			if ($user->getParent_passwd() == $_POST['group_edit_passwd']) {
				$_POST['user_edit_group_no'] = $_POST['group_edit_no'];
				$_POST['user_edit_uid'] = $GLOBALS['auth']->getParent_uid();
				$ret = $user->deleteMember();
				if ($ret) {
					$_SESSION[S_MESSAGE2] = $info.'「'.e($_POST['group_edit_name']).'」を退会しました。';
				}
				if ($GLOBALS['auth']->getParent_group_no() == $_POST['group_edit_no']) {
					$ret = $GLOBALS['auth']->setParent_group_no($edit->getHome($GLOBALS['auth']->getParent_uid()));
				}
			} else {
				$_SESSION[S_MESSAGE] = '正しいパスワードを入力してください。';
				$ret = true;
			}
			break;
		case "DEFAULT":
			$_POST['user_edit_group_no_default'] = $_POST['group_edit_no'];
			$_POST['user_edit_uid'] = $GLOBALS['auth']->getParent_uid();
			$group_no_default = $GLOBALS['auth']->getParent_group_no_default(); //現在の設定を退避
			$ret = $GLOBALS['auth']->setParent_group_no_default($_POST['group_edit_no']);
			if ($ret) {
				$ret = $user->updateUser();
				if ($ret) {
					$_SESSION[S_MESSAGE2] = $info.'「'.e($_POST['group_edit_name']).'」をデフォルトにしました。';
				} else {
					$GLOBALS['auth']->setParent_group_no_default($group_no_default); //元に戻す
				}
			}
			break;
		default:
			header("HTTP/1.0 404 Not Found");
			return;
	}

	if (!$ret) {
		if (strlen($edit->getMessage())) {
			$_SESSION[S_MESSAGE] = $edit->getMessage();
		}
		if (strlen($user->getMessage())) {
			$_SESSION[S_MESSAGE] = $user->getMessage();
		}
	}

	header('Location: '.$_SERVER['HTTP_REFERER']);
	return;
}

//上記処理が実行されなければホームへ移動
$group_no = $edit->getHome($GLOBALS['auth']->getParent_uid());
if (!$group_no) {
	$_SESSION[S_MESSAGE] = $edit->getMessage();
} else {
	$ret = $GLOBALS['auth']->setParent_group_no($group_no);
	if ($ret) {
		$_SESSION[S_MESSAGE2] = '「ホーム」へ移動しました。';
	} else {
		$_SESSION[S_MESSAGE2] = '<font color="red">「ホーム」へ移動できません。</font>';
	}
}
header('Location: '.PROJECT_ROOT.'/login/group_list.php');
