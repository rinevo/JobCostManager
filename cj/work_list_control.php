<?php
require_once(dirname(__FILE__).'/../login/myauth.php');
require_once(dirname(__FILE__).'/../define.php');
require_once(dirname(__FILE__).'/../db_config.php');
require_once(dirname(__FILE__).'/../Encode.php');
require_once(dirname(__FILE__).'/../login/class/UserListSQL.class.php');
require_once(dirname(__FILE__).'/class/WorkListSQL.class.php');

// アクセスログ
require_once(dirname(__FILE__).'/../class/AccessLogSQL.class.php');
$log = new AccessLogSQL($GLOBALS['dbopts']);
$log->Write(0, '', __FILE__, __FUNCTION__, __LINE__);

// POST処理
if (isset($_POST['post_type'])) {

	$ret = false;
	$edit = new WorkListSQL($dbopts);

	$project_no = $_POST['post_project_no'];
	$project_sortno = $_POST['post_project_sortno'];
	$project_name = $_POST['post_project_name'];
	$project_sortno_after = $_POST['post_project_sortno_after'];

	$work_no = $_POST['post_work_no'];
	$work_sortno = $_POST['post_work_sortno'];
	$work_name = $_POST['post_work_name'];
	$work_sortno_after = $_POST['post_work_sortno_after'];

	switch ($_POST['post_type']) {
		case 'PROJECT_INSERT':
			$edit->insertProject($project_name, $project_sortno + 1);
			break;
		case 'PROJECT_UPDATE':
			$edit->updateProject($project_no, $project_name);
			break;
		case 'PROJECT_DELETE':
			$edit->deleteProject($project_no);
			break;
		case 'PROJECT_MOVE':
			$edit->moveProject($project_no, $project_sortno, $project_sortno_after);
			break;
		case 'WORK_INSERT':
			$edit->insertWork($project_no, $work_name, $work_sortno + 1);
			break;
		case 'WORK_UPDATE':
			$edit->updateWork($work_no, $work_name);
			break;
		case 'WORK_DELETE':
			$edit->deleteWork($work_no);
			break;
		case 'WORK_MOVE':
			$edit->moveWork($project_no, $work_no, $work_sortno, $work_sortno_after);
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
