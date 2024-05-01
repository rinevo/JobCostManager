<?php require_once(dirname(__FILE__).'/../login/myauth.php'); ?>
<?php require_once(dirname(__FILE__).'/../define.php'); ?>
<?php if (($auth->getParent_role_todo() & ROLE_VIW) == 0) { header("HTTP/1.0 404 Not Found"); exit(); } ?>
<?php require_once(dirname(__FILE__).'/../Encode.php'); ?>
<?php require_once(dirname(__FILE__).'/../db_config.php'); ?>
<?php require_once(dirname(__FILE__).'/../login/class/UserListSQL.class.php'); ?>
<?php require_once(dirname(__FILE__).'/class/CjCommonSQL.class.php'); ?>
<?php require_once(dirname(__FILE__).'/class/TodoListSQL.class.php'); ?>

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
<title><?php echo APP_TITLE; ?> | ToDo</title>
<meta name="viewport" content="width=980px">
<link rel="shortcut icon" href="<?php echo PROJECT_ROOT ?>/favicon.ico">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/style.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/green/jquery-ui-1.9.2.custom.min.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/cj/css/fixedheadertable.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/cj/css/todo_list.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/cj/css/schedule_edit_dialog.css" media="all">

<script src="<?php echo PROJECT_ROOT ?>/js/jquery-1.7.2.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery-ui-1.8.20.custom.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery.bgiframe.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/cj/js/jquery.fixedheadertable.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/alert_dialog.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/browser.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/cj/js/common.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/cj/js/schedule_edit_dialog.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/cj/js/todo_list.js"></script>

</head>
<body>

<?php require_once(dirname(__FILE__).'/../header.php'); ?>

<?php
	$cmn = new CjCommonSQL($dbopts);
	$cmn->setDefSime(INI_SCH_SIME1, INI_SCH_SIME2);

	//選択ユーザー
	$select_uid = isset($_SESSION[S_SELECT_UID]) ? $_SESSION[S_SELECT_UID] : $auth->getParent_uid();
	$_SESSION[S_SELECT_UID] = $select_uid;

	//年月
	$select_date = isset($_SESSION[S_SELECT_DATE]) ? $_SESSION[S_SELECT_DATE] : $cmn->getNowDate($select_uid);
	$_SESSION[S_SELECT_DATE] = $select_date;
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
		if (($auth->getParent_role_todo() & ROLE_MEMBER_VIW) == 0) {
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
	if (($auth->getParent_role_todo() & ROLE_MEMBER_VIW) == 0) {
		for ($i = count($user_list) - 1; $i > 0; $i--) {
			if ($user_list[$i]['uid'] != $auth->getParent_uid()) {
				unset($user_list[$i]);
			}
		}
	}

	//日
	$day_list = $cmn->getDayList($select_uid, $select_date);
	$select_day = isset($_SESSION[S_SELECT_DAY]) ? $_SESSION[S_SELECT_DAY] : $cmn->getNowDay();
	$_SESSION[S_SELECT_DAY] = $select_day;

	//ＴＯＤＯリスト
	$todo = new TodoListSQL($dbopts);
	$main_list = $todo->getList($select_uid);

	//期間（自分の設定を使う）
	$period_list = $todo->getPeriod_list();
	$select_period = $cmn->getIni_data(INI_TODO_PERIOD,$auth->getParent_uid());

	//[終了][中止]でない項目はすべて表示（自分の設定を使う）
	$check_past = $cmn->getIni_data(INI_TODO_PAST,$auth->getParent_uid());

	//状況リスト
	$status_list = $todo->getStatus_list();

	//ＴＯＤＯ項目リスト
	$todofield_list = $todo->getTodoField_list($select_uid);

	//ＴＯＤＯ括り項目
	$todobundle =  $cmn->getIni_data(INI_TODO_BUNDLE,$select_uid);
	$bundle_list = !empty($todobundle) ? str_getcsv($todobundle) : array();
	$bk_name = array();
	foreach ($bundle_list as $bundle_row) {
		$bk_name[$bundle_row] = '';
	}
?>
<script>
var todobundle = "<?php echo $todobundle; ?>";
</script>

<div id="content" class="clearfix">
<div class="inner">

<?php if (isset($_SESSION[S_MESSAGE2]) && strlen($_SESSION[S_MESSAGE2])) {
	echo '<div class="info">'.$_SESSION[S_MESSAGE2].'</div>';
	$_SESSION[S_MESSAGE2] = '';
} ?>

	<div id="list_header">
		<div class="menu">
		</div>
		<div class="date">
			<label>対象年月：<select name="cboDate" id="cboDate" onchange="selectDate()">
			<?php if ($date_list) { foreach ($date_list as $row) { ?>
				<option value="<?php echo e($row['date']); ?>" <?php if ($row['date'] == $select_date) { ?>selected<?php } ?>><?php echo e($row['name']); ?></option>
			<?php } } ?>
			</select></label>
			<select name="cboDay" id="cboDay" onchange="selectday()">
			<?php if ($day_list) { foreach ($day_list as $row) { ?>
				<option value="<?php echo e($row['date']); ?>" <?php if ($row['date'] == $select_day) { ?>selected<?php } ?>><?php echo e($row['name']); ?></option>
			<?php } } ?>
			</select>
			<select name="cboPeriod" id="cboPeriod" onchange="selectPeriod()">
			<?php if ($period_list) { foreach ($period_list as $row) { ?>
				<option value="<?php echo e($row['no']); ?>" <?php if ($row['no'] == $select_period) { ?>selected<?php } ?>><?php echo e($row['name']); ?></option>
			<?php } } ?>
			</select>
			<div class="past">
				<input type="checkbox" id="chkPast" onchange="checkPast()" <?php if ($check_past == 1) { ?>checked<?php } ?>><label for="chkPast">[終了][中止]でない項目はすべて表示</label>
			</div>
			<?php if (($auth->getParent_role_todo() & $role_edt) != 0) { ?>
				<input type="button" value="並べ替え" class="btn_sort" onclick="sortDialog()"/>
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
		<form id="post_form" method="post" action="todo_list_control.php" >
			<input type="hidden" name="post_type" id="post_type" value="" />
			<input type="hidden" name="post_date" id="post_date" value="<?php echo e($select_date); ?>" />
			<input type="hidden" name="post_day" id="post_day" value="<?php echo e($select_day); ?>" />
			<input type="hidden" name="post_period" id="post_period" value="<?php echo e($select_period); ?>" />
			<input type="hidden" name="post_past" id="post_past" value="<?php echo e($check_past); ?>" />
			<input type="hidden" name="post_show_user" id="post_show_user" value="<?php echo e($ini_item_user); ?>" />
			<input type="hidden" name="post_sort_list[]" value="" />
			<input type="hidden" name="post_bundle_list[]" value="" />
			<input type="hidden" name="post_uid" id="post_uid" value="<?php echo e($select_uid); ?>" />
			<input type="hidden" name="post_no" id="post_no" value="" />
			<input type="hidden" name="post_sortno" id="post_sortno" value="" />
			<input type="hidden" name="post_sortno_after" id="post_sortno_after" value="" />
			<input type="hidden" name="post_project_no" id="post_project_no" value="" />
			<input type="hidden" name="post_work_no" id="post_work_no" value="" />
			<input type="hidden" name="post_customer_no" id="post_customer_no" value="" />
			<input type="hidden" name="post_process_no" id="post_process_no" value="" />
			<input type="hidden" name="post_action" id="post_action" value="" />
			<input type="hidden" name="post_status_no" id="post_status_no" value="" />
		</form>
	</div>

	<div class="item_list">

		<table class="list" id="list">
			<thead>
			<tr class="header" style="border-color: #cccccccc; border-width: 1px; border-style: solid; text-align: center; background: green; color: white;">
				<th>TODO</th><th>記録始</th><th>記録終</th><th class="th_costin">時間内</th><th class="th_costout">時間外</th><th>状況</th><th></th>
			</tr>
			</thead>
			<tbody id="list_tbody">
			<?php if ($main_list) { foreach ($main_list as $row) { //データがあるとき
				//括り項目を比較
				$bundle_hit = true;
				$bundle_string = '';
				foreach ($bundle_list as $bundle_row) {
					if ($bk_name[$bundle_row] != $row[$bundle_row]) {	//括り項目の名称が一致するか？
						$bundle_hit = false;
						$bk_name[$bundle_row] = $row[$bundle_row];
					}
					foreach ($todofield_list as $todofield_row) {		//括り項目名を生成
						if ($todofield_row['sort_id'] == $bundle_row) {
							$bundle_string.= e($row[$todofield_row['show_id']]).' ';
							break;
						}
					}
				}
				if (!$bundle_hit) {	//前の括り項目と一致しなければ、括り行を表示
				?>
				<tr class="tr_project">
					<td class="td_todo">
						<?php echo $bundle_string; ?>
					</td>
					<td class="td_date_top">
					</td>
					<td class="td_date_end">
					</td>
					<td class="td_costin">
					</td>
					<td class="td_costout">
					</td>
					<td class="td_status">
					</td>
					<td class="td_ope" nowrap>
					</td>
				</tr>
				<?php } ?>
				<tr class="tr_item">
					<td class="td_todo">
						<div class="div_icon">
							<?php
								$icon = '';
								switch ($row['status_no']) {
									case 1:
										$icon = 'pict04_10.png';
										break;
									case 2:
										$icon = 'pict06_15.png';
										break;
									case 3:
										$icon = 'pict12_09.png';
										break;
									case 4:
										$icon = 'pict01_01.png';
										break;
									default:
										$icon = 'pict03_09.png';
										break;
								}
							?>
							<img class="img_icon" src="images/<?php echo $icon; ?>" width="14" height="14">
						</div>
						<div class="div_action">
							<div class="sublabel">
								<?php if (!isset($bk_name['project_sortno'])) { ?>
									<label class="lbl_project"><?php echo e($row['project_name']); ?></label>
								<?php } ?>
								<?php if (!isset($bk_name['work_sortno'])) { ?>
									<label class="lbl_work"><?php echo e($row['work_name']); ?></label>
								<?php } ?>
								<?php if (!isset($bk_name['customer_sortno'])) { ?>
									<label class="lbl_customer"><?php echo e($row['customer_name']); ?></label>
								<?php } ?>
								<?php if (!isset($bk_name['process_sortno'])) { ?>
									<label class="lbl_process"><?php echo e($row['process_name']); ?></label>
								<?php } ?>
							</div>
							<label class="lbl_action"><?php echo e($row['action']); ?></label>
						</div>
						<div class="busy" style="display:none;">
							<img alt="処理中" src="images/loading-28.gif" width="16" height="16">
						</div>
					</td>
					<td class="td_date_top">
						<?php echo e($row['date_top']); ?>
					</td>
					<td class="td_date_end">
						<?php echo e($row['date_end']); ?>
					</td>
					<td class="td_costin">
						<?php echo e($row['costin']); ?>
					</td>
					<td class="td_costout">
						<?php echo e($row['costout']); ?>
					</td>
					<td class="td_status">
						<select class="cbo_status" onchange="selectStatus(this)"
							<?php if (($auth->getParent_role_todo() & $role_edt) == 0) { ?>disabled="disabled"<?php } ?>>
						<?php if ($status_list) { foreach ($status_list as $status_row) { ?>
							<option value="<?php echo e($status_row['no']); ?>"
								<?php if ($row['status_no'] == $status_row['no']) { ?>selected<?php } ?>><?php echo e($status_row['name']); ?></option>
						<?php } } ?>
						</select>
					</td>
					<td class="td_ope" nowrap>
						<input type="hidden" class="bundle_string" value="<?php echo $bundle_string; ?>" />
						<input type="hidden" class="no" value="<?php echo $row['no']; ?>" />
						<input type="hidden" class="sortno" value="<?php echo $row['sortno']; ?>" />
						<input type="hidden" class="section_no" value="<?php echo $row['section_no']; ?>" />
						<input type="hidden" class="project_no" value="<?php echo $row['project_no']; ?>" />
						<input type="hidden" class="work_no" value="<?php echo $row['work_no']; ?>" />
						<input type="hidden" class="customer_no" value="<?php echo $row['customer_no']; ?>" />
						<input type="hidden" class="process_no" value="<?php echo $row['process_no']; ?>" />
						<input type="hidden" class="cbo_action_start" value="<?php echo $row['cbo_action_start']; ?>" />
						<input type="hidden" class="cbo_action_end" value="<?php echo $row['cbo_action_end']; ?>" />
						<input type="hidden" class="project_name" value="<?php echo $row['project_name']; ?>" />
						<input type="hidden" class="work_name" value="<?php echo $row['work_name']; ?>" />
						<input type="hidden" class="customer_name" value="<?php echo $row['customer_name']; ?>" />
						<input type="hidden" class="process_name" value="<?php echo $row['process_name']; ?>" />
						<?php if (($auth->getParent_role_todo() & $role_edt) != 0) { ?>
							<input type="button" value="▲" class="btn_up" onclick="upRow(this)"/>
							<input type="button" value="▼" class="btn_dw" onclick="dwRow(this)"/>
							<input type="button" value="編集" class="btn_edit" onclick="editDialog(this)"/>
						<?php } ?>
						<?php if (($auth->getParent_role_todo() & $role_add) != 0) { ?>
							<input type="button" value="追加" class="btn_add" onclick="insertDialog(this)"/>
						<?php } ?>
						<?php if (($auth->getParent_role_todo() & $role_del) != 0) { ?>
							<input type="button" value="削除" class="btn_del" onclick="deleteRow(this)"/>
						<?php } ?>
					</td>
				</tr>
			<?php } } else { //データが無いとき ?>
				<tr class="tr_item">
					<td class="td_todo">
						<div class="div_icon">
							<img class="img_icon" src="" width="14" height="14" style="display:none;">
						</div>
						<div class="div_action">
							<div class="sublabel">
								<?php if (!isset($bk_name['project_sortno'])) { ?>
									<label class="lbl_project"></label>
								<?php } ?>
								<?php if (!isset($bk_name['work_sortno'])) { ?>
									<label class="lbl_work"></label>
								<?php } ?>
								<?php if (!isset($bk_name['customer_sortno'])) { ?>
									<label class="lbl_customer"></label>
								<?php } ?>
								<?php if (!isset($bk_name['process_sortno'])) { ?>
									<label class="lbl_process"></label>
								<?php } ?>
							</div>
							<label class="lbl_action"></label>
						</div>
						<div class="busy" style="display:none;">
							<img alt="処理中" src="images/loading-28.gif" width="16" height="16">
						</div>
					</td>
					<td class="td_date_top">
					</td>
					<td class="td_date_end">
					</td>
					<td class="td_costin">
					</td>
					<td class="td_costout">
					</td>
					<td class="td_status">
						<select class="cbo_status" onchange="selectStatus(this)" style="display:none;">
						<?php if ($status_list) { foreach ($status_list as $status_row) { ?>
							<option value="<?php echo e($status_row['no']); ?>"><?php echo e($status_row['name']); ?></option>
						<?php } } ?>
						</select>
					</td>
					<td class="td_ope" nowrap>
						<input type="hidden" class="bundle_string" value="" />
						<input type="hidden" class="no" value="" />
						<input type="hidden" class="sortno" value="" />
						<input type="hidden" class="section_no" value="" />
						<input type="hidden" class="project_no" value="" />
						<input type="hidden" class="work_no" value="" />
						<input type="hidden" class="customer_no" value="" />
						<input type="hidden" class="process_no" value="" />
						<input type="hidden" class="cbo_action_start" value="" />
						<input type="hidden" class="cbo_action_end" value="" />
						<input type="hidden" class="project_name" value="" />
						<input type="hidden" class="work_name" value="" />
						<input type="hidden" class="customer_name" value="" />
						<input type="hidden" class="process_name" value="" />
						<?php if (($auth->getParent_role_todo() & $role_edt) != 0) { ?>
							<input type="button" value="▲" class="btn_up" onclick="upRow(this)" style="display:none;"/>
							<input type="button" value="▼" class="btn_dw" onclick="dwRow(this)" style="display:none;"/>
							<input type="button" value="編集" class="btn_edit" onclick="editDialog(this)" style="display:none;"/>
						<?php } ?>
						<?php if (($auth->getParent_role_todo() & $role_add) != 0) { ?>
							<input type="button" value="追加" class="btn_add" onclick="insertDialog(this)"/>
						<?php } ?>
						<?php if (($auth->getParent_role_todo() & $role_del) != 0) { ?>
							<input type="button" value="削除" class="btn_del" onclick="deleteRow(this)" style="display:none;"/>
						<?php } ?>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>

	</div><!-- item_list -->
</div><!-- inner -->
</div><!-- content -->

<?php require_once(dirname(__FILE__).'/../footer.php'); ?>

<?php require(dirname(__FILE__).'/schedule_edit_dialog.php'); ?>

<div id="sort_dialog" title="並べ替え" style="display:none">
<p class="validateTips"></p>
<table id="sort_list" class="sort_list">
	<thead>
		<tr>
			<th>括り</th><th>ソート順</th><th></th>
		</tr>
	</thead>
	<tbody id="sort_list_tbody">
	<?php if ($todofield_list) { foreach ($todofield_list as $todofield_row) { ?>
		<tr class="tr_item">
			<td class="td_bundle">
				<input type="checkbox" class="chk_bundle" <?php if ($todofield_row['bundle']) { ?>checked<?php } ?>>
			</td>
			<td class="td_name">
				<label class="name"><?php echo e($todofield_row['name']); ?></label>
			</td>
			<td class="td_ope" nowrap>
				<input type="hidden" class="id" value="<?php echo $todofield_row['sort_id']; ?>" />
				<input type="button" value="▲" class="btn_up" onclick="sort_list_upRow(this)"/>
				<input type="button" value="▼" class="btn_dw" onclick="sort_list_dwRow(this)"/>
			</td>
		</tr>
	<?php }} ?>
	</tbody>
</table>
</div>

<div id="delete_dialog" title="削除" style="display:none">
<p class="validateTips"></p>
<table>
<tr>
<td>
	<label class="label-editstr" ></label><br>
	<label class="editstr-disabled"></label>
</td>
<tr>
</table>
</div>

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

			//区分を変更不可にする
			$("#edit_section").attr('disabled','disabled');

			//編集画面のＴＯＤＯ選択項目を更新
			refreshTodoList();

		} catch(e){}
	}, 200);
}
attempt_focus();
</script>

</body>
</html>
