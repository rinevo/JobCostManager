<?php
require_once(dirname(__FILE__).'/../login/myauth.php');
require_once(dirname(__FILE__).'/../define.php');
require_once(dirname(__FILE__).'/../db_config.php');
require_once(dirname(__FILE__).'/../Encode.php');
require_once(dirname(__FILE__).'/class/CjCommonSQL.class.php');
require_once(dirname(__FILE__).'/class/CostListSQL.class.php');

// アクセスログ
require_once(dirname(__FILE__).'/../class/AccessLogSQL.class.php');
$log = new AccessLogSQL($GLOBALS['dbopts']);
$log->Write(0, '', __FILE__, __FUNCTION__, __LINE__);

// POST処理
if (isset($_POST['post_type'])) {

	$ret = false;
	$select_uid = isset($_POST['post_uid']) ? $_POST['post_uid'] : $_SESSION[S_SELECT_UID];
	$uid = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_uid() : '';

	switch ($_POST['post_type']) {
		case 'SET_SELECT':

			$select_list = isset($_POST['post_item_list']) ? $_POST['post_item_list'] : array();

			$edit = new CostListSQL($dbopts);
			$ret = $edit->saveSelect($auth->getParent_uid(), $select_list);

			break;
		case 'SET_SIME':

			$sime1 = $_POST['post_sime1'];
			$sime2 = $_POST['post_sime2'];

			$edit = new CjCommonSQL($dbopts);
			$ret = $edit->setIni_data(INI_COST_SIME1, $sime1, $uid);
			if ($ret) {
				$ret = $edit->setIni_data(INI_COST_SIME2, $sime2, $uid);
			}

			break;
		case 'CHECK_SHOW_USER':

			$inidata = $_POST['post_show_user'];

			$edit = new CjCommonSQL($dbopts);
			$ret = $edit->setIni_data(INI_SHOW_USER, $inidata, $uid);

			break;
		case 'SELECT_DATE':

			$date = $_POST['post_date'];
			$_SESSION[S_SELECT_DATE] = $date;
			$ret = true;

			break;
		case 'SELECT_USER':

			$_SESSION[S_SELECT_UID] = $select_uid;
			$ret = true;

			break;
		case 'GET_TOTAL':

			$date = $_POST['post_date'];

			$edit = new CjCommonSQL($dbopts);
			$edit->setDefSime(INI_COST_SIME1, INI_COST_SIME2);

			$list = $edit->getTotal($uid, $select_uid, $date);

			if ($list !== false) {
				array_splice($list, 0, 0, array(0));
			} else {
				$list = array($edit->getMessage());
			}

			header("Content-Type: text/html; charset=UTF-8");
			echo json_encode($list);
			return;

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
