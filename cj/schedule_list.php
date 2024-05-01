<?php require_once(dirname(__FILE__).'/../login/myauth.php'); ?>
<?php require_once(dirname(__FILE__).'/../define.php'); ?>
<?php if (($auth->getParent_role_schedule() & ROLE_VIW) == 0) { header("HTTP/1.0 404 Not Found"); exit(); } ?>
<?php require_once(dirname(__FILE__).'/../Encode.php'); ?>
<?php require_once(dirname(__FILE__).'/../db_config.php'); ?>
<?php require_once(dirname(__FILE__).'/../login/class/UserListSQL.class.php'); ?>
<?php require_once(dirname(__FILE__).'/class/ScheduleListSQL.class.php'); ?>
<?php require_once(dirname(__FILE__).'/class/CjCommonSQL.class.php'); ?>

<?php
// アクセスログ
require_once(dirname(__FILE__).'/../class/AccessLogSQL.class.php');
$log = new AccessLogSQL($GLOBALS['dbopts']);
$log->Write(0, '', __FILE__, __FUNCTION__, __LINE__);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo APP_TITLE; ?></title>
<meta name="viewport" content="width=980px">
<link rel="shortcut icon" href="<?php echo PROJECT_ROOT ?>/favicon.ico">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/style.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/green/jquery-ui-1.9.2.custom.min.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/cj/css/fixedheadertable.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/cj/css/schedule_list.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/cj/css/schedule_edit_dialog.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/cj/css/sime_edit_dialog.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/cj/css/total_list.css" media="all">

<script src="<?php echo PROJECT_ROOT ?>/js/jquery-1.7.2.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery-ui-1.8.20.custom.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery.bgiframe.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/alert_dialog.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/browser.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/cj/js/jquery.fixedheadertable.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/cj/js/savescrollposition.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/cj/js/common.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/cj/js/schedule_edit_dialog.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/cj/js/sime_edit_dialog.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/cj/js/schedule_list.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/cj/js/total_list.js"></script>

</head>
<body>

<?php require_once(dirname(__FILE__).'/../header.php'); ?>

<?php
	$cmn = new CjCommonSQL($dbopts);
	$cmn->setDefSime(INI_SCH_SIME1, INI_SCH_SIME2);

	//選択ユーザー
	$select_uid = isset($_SESSION[S_SELECT_UID]) ? $_SESSION[S_SELECT_UID] : $auth->getParent_uid();
	$_SESSION[S_SELECT_UID] = $select_uid;

	//年月（自分の設定を使う）
	$select_date = isset($_SESSION[S_SELECT_DATE]) ? $_SESSION[S_SELECT_DATE] : $cmn->getNowDate($auth->getParent_uid());
	$date_list = $cmn->getYearMonthList($select_uid, $select_date);

	//データの無いユーザーは表示しない（自分の設定を使う）
	$ini_item_user =  $cmn->getIni_data(INI_SHOW_USER, $auth->getParent_uid());

	//ユーザー名
	if ($ini_item_user == 1) {
		$user_list = $cmn->getUser_list($select_date);
	} else {
		$user_list = $cmn->getUser_list();
	}

	//他のユーザーを選択しているときは、権限チェック
	if ($auth->getParent_uid() != $select_uid) {
		if (($auth->getParent_role_schedule() & ROLE_MEMBER_VIW) == 0) {
			header("HTTP/1.0 404 Not Found");
			return;
		}
		//権限の属性
		$role_viw = ROLE_MEMBER_VIW;
		$role_edt = ROLE_MEMBER_EDT;
		$role_add = ROLE_MEMBER_ADD;
		$role_del = ROLE_MEMBER_DEL;
	} else {
		$role_viw = ROLE_VIW;
		$role_edt = ROLE_EDT;
		$role_add = ROLE_ADD;
		$role_del = ROLE_DEL;
	}

	//他のユーザーの参照権限が無ければ選択から削除
	if (($auth->getParent_role_schedule() & ROLE_MEMBER_VIW) == 0) {
		for ($i = count($user_list) - 1; $i > 0; $i--) {
			if ($user_list[$i]['uid'] != $auth->getParent_uid()) {
				unset($user_list[$i]);
			}
		}
	}

	//日（自分の設定を使う）
	$day_list = $cmn->getDayList($auth->getParent_uid(), $select_date);
	//$select_day = isset($_SESSION[S_SELECT_DAY]) ? $_SESSION[S_SELECT_DAY] : $cmn->getNowDay();
	//$_SESSION[S_SELECT_DAY] = $select_day;
	$select_day = $cmn->getNowDay();

	//スケジュールリスト
	$sch = new ScheduleListSQL($dbopts);
	$sch_list = $sch->getSchedule_month($select_uid, $select_date);
	$sch_list = $sch->getSchedule_table_list($sch_list);

	//締日（自分の設定を使う）
	$sime1 = $cmn->getSime1($auth->getParent_uid());
	$sime2 = $cmn->getSime2($auth->getParent_uid());
?>

<?php require(dirname(__FILE__).'/schedule_edit_dialog.php'); ?>

<div id="content" class="clearfix">
<div class="inner">

<?php if (isset($_SESSION[S_MESSAGE2]) && strlen($_SESSION[S_MESSAGE2])) {
	echo '<div class="info">'.$_SESSION[S_MESSAGE2].'</div>';
	$_SESSION[S_MESSAGE2] = '';
} ?>

	<div id="schedule_header">
		<div class="total_list">
			<table class="total" id="total">
				<thead>
					<tr class="header">
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
			<div id="tatal_busy" style="display:none">
				<img alt="処理中" src="images/loading-28.gif" width="16" height="16">
			</div>
		</div>
		<div class="date">
			<label>対象年月：<select name="cboDate" id="cboDate" onchange="selectDate()">
			<?php if ($date_list) { foreach ($date_list as $row) { ?>
				<option value="<?php echo e($row['date']); ?>" <?php if ($row['date'] == $select_date) { ?>selected<?php } ?>><?php echo e($row['name']); ?></option>
			<?php } } ?>
			</select></label>
			<select name="cboDay" id="cboDay" onchange="selectday()">
			<option value=""></option>
			<?php if ($day_list) { foreach ($day_list as $row) { ?>
				<option value="<?php echo e($row['date']); ?>"><?php echo e($row['name']); ?></option>
			<?php } } ?>
			</select>
			<?php if (($auth->getParent_role_schedule() & ROLE_EDT) != 0) { ?>
				<input type="button" value="締日" class="btn_sime" onclick="simeDialog()">
			<?php } ?>
		</div>
		<div class="user">
			<label>ユーザー：<select name="cboUser" id="cboUser" onchange="selectUser()">
			<?php if ($user_list) { foreach ($user_list as $row) { if ($row['member_status'] == GROUP_MEMBER_STATUS_NOMAL) { ?>
				<option value="<?php echo e($row['uid']); ?>" <?php if ($row['uid'] == $select_uid) { ?>selected<?php } ?>><?php echo e($row['name']); ?></option>
			<?php } } } ?>
			</select></label>
			<div class="show_user">
				<input type="checkbox" id="chkShowUser" onchange="checkShowUser()" <?php if ($ini_item_user == 1) { ?>checked<?php } ?>><label for="chkShowUser">データの無いユーザーは表示しない</label>
			</div>
		</div>
		<input type="hidden" id="scrollPosition" value="<?php echo isset($_SESSION[S_SCROLLPOS]) ? $_SESSION[S_SCROLLPOS] : ''; ?>" />
		<form id="post_form" method="post" action="schedule_list_control.php" >
			<input type="hidden" name="post_type" class="post_type" value="" />
			<input type="hidden" name="post_date" class="post_date" value="" />
			<input type="hidden" name="post_uid" class="post_uid" value="" />
			<input type="hidden" name="post_sime1" class="post_sime1" value="" />
			<input type="hidden" name="post_sime2" class="post_sime2" value="" />
			<input type="hidden" name="post_show_user" id="post_show_user" value="<?php echo e($ini_item_user); ?>" />
		</form>
	</div>

	<div class="schedule_list clearfix">

		<table class="schedule" id="schedule">
			<thead>
			<tr class="header" style="border-color: #cccccccc; border-width: 1px; border-style: solid; text-align: center; background: green; color: white;">
				<th>年月日</th><th>時刻</th><th>行動</th><th>時間内</th><th>時間外</th><th>操作</th>
			</tr>
			</thead>
			<tbody id="schedule-tbody">
			<?php $row_no = 0; ?>
			<?php foreach ($sch_list as $row) { ?>
			<?php $row_no++; ?>
			<?php if ($row['rowspan'] != '') { ?>
			<?php if ($row_no > 1) { ?>
				<tr class="subtotal">
					<td class="td_date"></td>
					<td class="td_time"></td>
					<td class="td_action"></td>
					<td class="td_costin"><label class="lbl_costin">0</label></td>
					<td class="td_costout"><label class="lbl_costout">0</label></td>
					<td class="td_control"></td>
				</tr>
			<?php } ?>
			<tr id="tr_<?php echo $row['date']; ?>">
				<td class="td_date" rowspan="<?php echo $row['rowspan']; ?>">
					<label class="lbl_date"><?php echo $row['date'].'('.week($row['date']).')'; ?></label>
					<div class="busy" style="display:none">
						<img alt="処理中" src="images/loading-28.gif" width="16" height="16">
					</div>
					<br>
					<?php if (($auth->getParent_role_schedule() & $role_edt) != 0) { ?>
						<input type="button" value="編集" class="btn_edit_start" onclick="showEditMode(this); setFocus_topRowObj(this,'text_time');">
						<input type="button" value="保存" class="btn_edit_save" onclick="save(this)">
						<input type="button" value="取消" class="btn_edit_cancel" onclick="reload(this)">
					<?php } ?>
					<input type="hidden" name="date" class="date" value="<?php echo $row['date']; ?>" />
				</td>
			<?php } else { ?>
			<tr>
			<?php } ?>
				<td class="td_time">
					<input type="text" value="<?php echo $row['time']; ?>" class="text_time">
					<label class="lbl_time"><?php echo $row['time']; ?></label>
					<input type="hidden" class="no" value="<?php echo $row['no']; ?>" />
					<input type="hidden" class="section_no" value="<?php echo $row['section_no']; ?>" />
					<input type="hidden" class="project_no" value="<?php echo isset($row['project_no']) ? $row['project_no'] : 0; ?>" />
					<input type="hidden" class="work_no" value="<?php echo isset($row['work_no']) ? $row['work_no'] : 0; ?>" />
					<input type="hidden" class="process_no" value="<?php echo isset($row['process_no']) ? $row['process_no'] : 0; ?>" />
					<input type="hidden" class="customer_no" value="<?php echo isset($row['customer_no']) ? $row['customer_no'] : 0; ?>" />
				</td>
				<td class="td_action">
					<select class="cbo_action_start" onchange="selectKintai(this)">
					<option value="0"></option>
					<?php if ($kintai_start_list) { foreach ($kintai_start_list as $kintai) { ?>
						<option value="<?php echo e($kintai['no']); ?>"
							<?php if ($kintai['no'] == $row['kintai_no']) { ?>selected<?php } ?>><?php echo e($kintai['name']); ?></option>
					<?php } } ?>
					</select>
					<select class="cbo_action_end" onchange="selectKintai(this)">
					<option value="0"></option>
					<?php if ($kintai_end_list) { foreach ($kintai_end_list as $kintai) { ?>
						<option value="<?php echo e($kintai['no']); ?>"
							<?php if ($kintai['no'] == $row['kintai_no']) { ?>selected<?php } ?>><?php echo e($kintai['name']); ?></option>
					<?php } } ?>
					</select>
					<div class="sublabel">
						<label class="lbl_project"><?php echo e($row['project_name']); ?></label>
						<label class="lbl_work"><?php echo e($row['work_name']); ?></label>
						<label class="lbl_customer"><?php echo e($row['customer_name']); ?></label>
						<label class="lbl_process"><?php echo e($row['process_name']); ?></label>
					</div>
					<label class="lbl_action"><?php echo $row['action']; ?></label>
				</td>
				<td class="td_costin">
					<input type="text" value="<?php echo !empty($row['costin']) ? $row['costin'] : 0; ?>" class="text_costin">
					<label class="lbl_costin"><?php echo $row['costin']; ?></label>
				</td>
				<td class="td_costout">
					<input type="text" value="<?php echo !empty($row['costout']) ? $row['costout'] : 0; ?>" class="text_costout">
					<label class="lbl_costout" ><?php echo $row['costout']; ?></label>
				</td>
				<td class="td_control">
					<?php if (($auth->getParent_role_schedule() & $role_edt) != 0) { ?>
						<input type="button" value="▲" class="btn_up" onclick="upRow(this)">
						<input type="button" value="▼" class="btn_dw" onclick="dwRow(this)">
					<?php } ?>
					<?php if (($auth->getParent_role_schedule() & $role_add) != 0) { ?>
						<input type="button" value="＋" class="btn_add" onclick="insertDialog(this)">
					<?php } ?>
					<?php if (($auth->getParent_role_schedule() & $role_del) != 0) { ?>
						<input type="button" value="－" class="btn_del" onclick="schedule_removeRow(this)">
					<?php } ?>
					<?php if (($auth->getParent_role_schedule() & $role_edt) != 0) { ?>
						<input type="button" value="編集" class="btn_edit" onclick="editDialog(this)">
					<?php } ?>
				</td>
			</tr>
			<?php } //foreach ?>
			<tr class="subtotal">
				<td class="td_date"></td>
				<td class="td_time"></td>
				<td class="td_action"></td>
				<td class="td_costin"><label class="lbl_costin">0</label></td>
				<td class="td_costout"><label class="lbl_costout">0</label></td>
				<td class="td_control"></td>
			</tr>
			</tbody>
		</table>

	</div><!-- schedule_list -->
</div><!-- inner -->
</div><!-- content -->

<?php require_once(dirname(__FILE__).'/../footer.php'); ?>

<?php require_once(dirname(__FILE__).'/sime_edit_dialog.php'); ?>

<div id="alert_dialog" title="確認" style="display:none">
	<label class="label"></label>
</div>

<script>
//画面表示後の処理
function attempt_focus(){
	setTimeout(function(){
		try{

			// アラートメッセージがあれば表示
			<?php if (isset($_SESSION[S_MESSAGE]) && strlen($_SESSION[S_MESSAGE])) { ?>
			alert_dialog("<?php echo $_SESSION[S_MESSAGE]; ?>");
			$(".ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset button").focus();
			<?php $_SESSION[S_MESSAGE] = ''; ?>
			<?php } ?>

		} catch(e){}
	}, 200);
}
attempt_focus();
</script>

</body>
</html>
