<?php
require_once(dirname(__FILE__).'/myauth.php');
require_once(dirname(__FILE__).'/../define.php');
require_once(dirname(__FILE__).'/../db_config.php');
require_once(dirname(__FILE__).'/../Encode.php');
require_once(dirname(__FILE__).'/class/RoleListSQL.class.php');

// アクセスログ
require_once(dirname(__FILE__).'/../class/AccessLogSQL.class.php');
$log = new AccessLogSQL($GLOBALS['dbopts']);
$log->Write(0, '', __FILE__, __FUNCTION__, __LINE__);

// POST処理
if (isset($_POST['post_type'])) {

	$ret = false;
	$edit = new RoleListSQL($dbopts);

	$no = $_POST['post_no'];
	$name = $_POST['post_name'];
	$sortno = $_POST['post_sortno'];
	$sortno_after = $_POST['post_sortno_after'];

	switch ($_POST['post_type']) {
		case 'INSERT':
			$ret = $edit->insertItem($name, $sortno + 1);
			break;
		case 'UPDATE':
			$ret = $edit->updateItem($no, $name);
			if ($ret) {
				$ret = $GLOBALS['auth']->setParent_group_no($GLOBALS['auth']->getParent_group_no());
			}
			break;
		case 'DELETE':
			$ret = $edit->deleteItem($no);
			break;
		case 'MOVE':
			$ret = $edit->moveItem($no, $sortno, $sortno_after);
			break;
		default:
			header("HTTP/1.0 404 Not Found");
			break;
	}

	if (!$ret) {
		$_SESSION[S_MESSAGE] = $edit->getMessage();
	}

	header('Location: '.$_SERVER['HTTP_REFERER']);
	return;
}

//上記処理が実行されなければ不正なアクセスとして扱う
header("HTTP/1.0 404 Not Found");
