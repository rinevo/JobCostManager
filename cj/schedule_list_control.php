<?php
require_once(dirname(__FILE__).'/../login/myauth.php');
require_once(dirname(__FILE__).'/../define.php');
require_once(dirname(__FILE__).'/../db_config.php');
require_once(dirname(__FILE__).'/../Encode.php');
require_once(dirname(__FILE__).'/../login/class/UserListSQL.class.php');
require_once(dirname(__FILE__).'/class/ScheduleListSQL.class.php');
require_once(dirname(__FILE__).'/class/CjCommonSQL.class.php');

// アクセスログ
require_once(dirname(__FILE__).'/../class/AccessLogSQL.class.php');
$log = new AccessLogSQL($GLOBALS['dbopts']);
$log->Write(0, '', __FILE__, __FUNCTION__, __LINE__);

// POST処理
if (isset($_POST['post_type'])) {

	$ret = false;
	$sch = new ScheduleListSQL($dbopts);
	$cmn = new CjCommonSQL($dbopts);
	$cmn->setDefSime(INI_SCH_SIME1, INI_SCH_SIME2);

	$select_uid = isset($_POST['post_uid']) ? $_POST['post_uid'] : $_SESSION[S_SELECT_UID];
	$uid = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_uid() : '';

	switch ($_POST['post_type']) {
		case 'SET_SIME':

			$sime1 = $_POST['post_sime1'];
			$sime2 = $_POST['post_sime2'];

			$ret = $cmn->setIni_data(INI_SCH_SIME1, $sime1, $uid);
			if ($ret) {
				$ret = $cmn->setIni_data(INI_SCH_SIME2, $sime2, $uid);
			}

			header('Location: '.$_SERVER['HTTP_REFERER']);

			break;
		case 'CHECK_SHOW_USER':

			$inidata = $_POST['post_show_user'];
			$ret = $cmn->setIni_data(INI_SHOW_USER, $inidata, $uid);

			header('Location: '.$_SERVER['HTTP_REFERER']);

			break;
		case 'SELECT_DATE':

			$date = $_POST['post_date'];
			$_SESSION[S_SELECT_DATE] = $date;

			header('Location: '.$_SERVER['HTTP_REFERER']);

			break;
		case 'SELECT_USER':

			$_SESSION[S_SELECT_UID] = $select_uid;

			header('Location: '.$_SERVER['HTTP_REFERER']);

			break;
		case 'SAVE':

			$ret = $sch->setShedule_day($select_uid);

			if (!$ret) {
				$_SESSION[S_MESSAGE] = $sch->getMessage();
			}

			//header('Location: '.$_SERVER['HTTP_REFERER']);

			$list = array(0);
			if ($ret) {
				array_splice($list, 0, 0, array(0));
			} else {
				$list = array($sch->getMessage());
			}

			echo json_encode($list);

			break;
		case 'GET_WORK':

			$project_no = $_POST['post_project_no'];

			$list = $sch->getWork_list($project_no);

			if ($list !== false) {
				array_splice($list, 0, 0, array(0));
			} else {
				$list = array($sch->getMessage());
			}

			echo json_encode($list);

			break;
		case 'GET_TODO':

			$todo_no = $_POST['post_todo_no'];

			$list = $sch->getTodo_list($select_uid, $todo_no);

			if ($list !== false) {
				//項目を取得できたら業務リストも取得する
				if (isset($list[0]['project_no'])) {
					$project_no = $list[0]['project_no'];
					$work_list = $sch->getWork_list($project_no);
					if ($work_list !== false) {
						array_splice($work_list, 0, 0, array(0));
					} else {
						array($sch->getMessage());
					}
					$list[0]['work_list'] = $work_list;
				} else {
					$list[0]['work_list'] = array();
				}
				//先頭要素に戻り値=0を設定
				array_splice($list, 0, 0, array(0));
			} else {
				$list = array($sch->getMessage());
			}

			echo json_encode(encode($list));

			break;
		case 'GET_SCHEDULE_DAY':

			$date = $_POST['post_date'];

			$list = $sch->getSchedule_day($select_uid, $date);
			$list = $sch->getSchedule_table_list($list);

			if ($list !== false) {
				array_splice($list, 0, 0, array(0));
			} else {
				$list = array($sch->getMessage());
			}

			echo json_encode($list);

			break;
		case 'GET_TOTAL':

			$date = $_POST['post_date'];

			$list = $cmn->getTotal($uid, $select_uid, $date);

			if ($list !== false) {
				array_splice($list, 0, 0, array(0));
			} else {
				$list = array($cmn->getMessage());
			}

			header("Content-Type: text/html; charset=UTF-8");
			echo json_encode($list);

			break;
		default:
			header("HTTP/1.0 404 Not Found");
			break;
	}

	return;
}

//上記処理が実行されなければ不正なアクセスとして扱う
header("HTTP/1.0 404 Not Found");
