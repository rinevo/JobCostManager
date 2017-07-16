<?php
require_once(dirname(__FILE__).'/../login/myauth.php');
require_once(dirname(__FILE__).'/../define.php');
require_once(dirname(__FILE__).'/../db_config.php');
require_once(dirname(__FILE__).'/../Encode.php');
require_once(dirname(__FILE__).'/class/TodoListSQL.class.php');
require_once(dirname(__FILE__).'/class/CjCommonSQL.class.php');

// アクセスログ
require_once(dirname(__FILE__).'/../class/AccessLogSQL.class.php');
$log = new AccessLogSQL($GLOBALS['dbopts']);
$log->Write(0, '', __FILE__, __FUNCTION__, __LINE__);

// POST処理
if (isset($_POST['post_type'])) {

	$ret = false;
	$edit = new TodoListSQL($dbopts);

	$select_uid = isset($_POST['post_uid']) ? $_POST['post_uid'] : $_SESSION[S_SELECT_UID];
	$uid = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_uid() : '';

	$no = $_POST['post_no'];
	$sortno = $_POST['post_sortno'];
	$sortno_after = $_POST['post_sortno_after'];
	$sort_list = isset($_POST['post_sort_list']) ? $_POST['post_sort_list'] : array();
	$bundle_list = isset($_POST['post_bundle_list']) ? $_POST['post_bundle_list'] : array();

	$project_no = $_POST['post_project_no'];
	$work_no = $_POST['post_work_no'];
	$customer_no = $_POST['post_customer_no'];
	$process_no = $_POST['post_process_no'];
	$action = $_POST['post_action'];
	$status_no = $_POST['post_status_no'];

	switch ($_POST['post_type']) {
		case 'SELECT_DATE':
			$date = $_POST['post_date'];
			$_SESSION[S_SELECT_DATE] = $date;
			$ret = true;
			break;
		case 'SELECT_DAY':
			$day = $_POST['post_day'];
			$_SESSION[S_SELECT_DAY] = $day;
			$ret = true;
			break;
		case 'SELECT_USER':
			$select_uid = $_POST['post_uid'];
			$_SESSION[S_SELECT_UID] = $select_uid;
			$ret = true;
			break;
		case 'SELECT_PERIOD':
			$inidata = $_POST['post_period'];
			$edit = new CjCommonSQL($dbopts);
			$ret = $edit->setIni_data(INI_TODO_PERIOD, $inidata, $uid);
			break;
		case 'CHECK_PAST':
			$inidata = $_POST['post_past'];
			$edit = new CjCommonSQL($dbopts);
			$ret = $edit->setIni_data(INI_TODO_PAST, $inidata, $uid);
			break;
		case 'CHECK_SHOW_USER':
			$inidata = $_POST['post_show_user'];
			$edit = new CjCommonSQL($dbopts);
			$ret = $edit->setIni_data(INI_SHOW_USER, $inidata, $uid);
			break;
		case 'SORT':
			$ret = $edit->saveSort($select_uid, $sort_list, $bundle_list);
			break;
		case 'INSERT':
			$ret = $edit->insertItem($select_uid, $project_no, $work_no, $customer_no, $process_no, $action, $sortno + 1);
			if ($ret) {
				$list = $edit->getList($select_uid, $ret);
				if ($list) {
					array_splice($list, 0, 0, array(0));
				} else {
					$list = array($edit->getMessage());
				}
				echo json_encode($list);
			}
			return;
			break;
		case 'EDIT':
			$ret = $edit->updateItem($no, $project_no, $work_no, $customer_no, $process_no, $action);
			if ($ret) {
				$list = $edit->getList($select_uid, $no);
				if ($list) {
					array_splice($list, 0, 0, array(0));
				} else {
					$list = array($edit->getMessage());
				}
				echo json_encode($list);
			}
			return;
			break;
		case 'EDIT_STATUS':
			$ret = $edit->updateStatus($no, $status_no);
			$list = array();
			if ($ret) {
				array_splice($list, 0, 0, array(0));
			} else {
				$list = array($edit->getMessage());
			}
			echo json_encode($list);
			return;
			break;
		case 'DELETE':
			$ret = $edit->deleteItem($no);
			$list = array();
			if ($ret) {
				array_splice($list, 0, 0, array(0));
			} else {
				$list = array($edit->getMessage());
			}
			echo json_encode($list);
			return;
			break;
		case 'MOVE':
			$ret = $edit->moveItem($select_uid, $no, $sortno, $sortno_after);
			$list = array();
			if ($ret) {
				array_splice($list, 0, 0, array(0));
			} else {
				$list = array($edit->getMessage());
			}
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
