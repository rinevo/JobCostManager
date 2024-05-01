<?php require_once(dirname(__FILE__).'/../login/myauth.php'); ?>
<?php require_once(dirname(__FILE__).'/../define.php'); ?>
<?php if (($auth->getParent_role_kintai() & ROLE_VIW) == 0) { header("HTTP/1.0 404 Not Found"); exit(); } ?>
<?php require_once(dirname(__FILE__).'/../Encode.php'); ?>
<?php require_once(dirname(__FILE__).'/../db_config.php'); ?>
<?php require_once(dirname(__FILE__).'/../login/class/UserListSQL.class.php'); ?>
<?php require_once(dirname(__FILE__).'/class/CjCommonSQL.class.php'); ?>
<?php require_once(dirname(__FILE__).'/class/KintaiListSQL.class.php'); ?>

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
<title><?php echo APP_TITLE; ?> | 勤怠表</title>
<meta name="viewport" content="width=980px">

<link rel="shortcut icon" href="<?php echo PROJECT_ROOT ?>/favicon.ico">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/style.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/green/jquery-ui-1.9.2.custom.min.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/cj/css/sime_edit_dialog.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/cj/css/total_list.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/cj/css/kintai_list.css" media="all">

<script src="<?php echo PROJECT_ROOT ?>/js/jquery-1.7.2.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery-ui-1.8.20.custom.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery.bgiframe.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/alert_dialog.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/cj/js/common.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/cj/js/sime_edit_dialog.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/cj/js/total_list.js"></script>

</head>
<body>

<?php require_once(dirname(__FILE__).'/../header.php'); ?>

<?php
	$cmn = new CjCommonSQL($dbopts);
	$cmn->setDefSime(INI_KINTAI_SIME1, INI_KINTAI_SIME2);

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
		if (($auth->getParent_role_kintai() & ROLE_MEMBER_VIW) == 0) {
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
	if (($auth->getParent_role_kintai() & ROLE_MEMBER_VIW) == 0) {
		for ($i = count($user_list) - 1; $i > 0; $i--) {
			if ($user_list[$i]['uid'] != $auth->getParent_uid()) {
				unset($user_list[$i]);
			}
		}
	}

	//月末
	$last_day = $cmn->getLastday($select_date);

	//締日
	$sime1 = $cmn->getSime1($select_uid);
	$sime2 = $cmn->getSime2($select_uid);

	//勤怠リスト
	$kintai = new KintaiListSQL($dbopts);
	$list = $kintai->getList($select_uid, $select_date);
?>

<div id="content" class="clearfix">
<div class="inner">

<?php if (isset($_SESSION[S_MESSAGE2]) && strlen($_SESSION[S_MESSAGE2])) {
	echo '<div class="info">'.$_SESSION[S_MESSAGE2].'</div>';
	$_SESSION[S_MESSAGE2] = '';
} ?>

	<div id="list_header">
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
			<?php if (($auth->getParent_role_kintai() & $role_edt) != 0) { ?>
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
		<form id="post_form" method="post" action="kintai_list_control.php" >
			<input type="hidden" name="post_type" id="post_type" value="" />
			<input type="hidden" name="post_date" id="post_date" value="<?php echo e($select_date); ?>" />
			<input type="hidden" name="post_sime1" id="post_sime1" value="<?php echo e($sime1); ?>" />
			<input type="hidden" name="post_sime2" id="post_sime2" value="<?php echo e($sime2); ?>" />
			<input type="hidden" name="post_uid" id="post_uid" value="<?php echo e($select_uid); ?>" />
			<input type="hidden" name="post_show_user" id="post_show_user" value="<?php echo e($ini_item_user); ?>" />
		</form>
	</div>

	<div class="item_list">

		<table class="list" id="list">
			<thead>
			<tr class="header" style="border-color: #cccccccc; border-width: 1px; border-style: solid; text-align: center; background: green; color: white;">
				<th>年月日</th><th>出勤区分</th><th>退勤区分</th><th>出勤</th><th>退勤</th><th>時間内</th><th>時間外</th>
			</tr>
			</thead>
			<tbody>
			<?php if ($list) { foreach ($list as $row) { ?>
				<tr class="tr_item">
					<td class="td_date">
						<?php echo e($row['date'].'('.week($row['date']).')'); ?>
					</td>
					<td class="td_section_top">
						<?php echo e($row['section_top_name']); ?>
					</td>
					<td class="td_section_end">
						<?php echo e($row['section_end_name']); ?>
					</td>
					<td class="td_time_top">
						<?php echo e($row['time_top']); ?>
					</td>
					<td class="td_time_end">
						<?php echo e($row['time_end']); ?>
					</td>
					<td class="td_costin">
						<?php echo e($row['costin']); ?>
					</td>
					<td class="td_costout">
						<?php echo e($row['costout']); ?>
					</td>
				</tr>
			<?php } } ?>
			</tbody>
		</table>

	</div><!-- item_list -->
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

$(document).ready(function() {
    //集計表示
    showTotal('kintai_list_control.php');
});

//締日の設定
function sime_dialog_ok() {

	$("#post_type").val('SET_SIME');
	$("#post_sime1").val($("#sime_dialog .edit-sime1").val());
	$("#post_sime2").val($("#sime_dialog .edit-sime2").val());

	$("#sime_dialog").dialog('close');
	$('#post_form').submit();
}

//対象年月選択
function selectDate() {

	$("#post_type").val('SELECT_DATE');
	$("#post_date").val($("#cboDate").val());

	$('#post_form').submit();
}

//ユーザー選択
function selectUser() {

	$("#post_type").val('SELECT_USER');
	$("#post_uid").val($("#cboUser").val());

	$('#post_form').submit();
}

//データの無いユーザーは表示しない
function checkShowUser() {

	$('#post_type').val('CHECK_SHOW_USER');

	var checked = 0;
	if ($('#chkShowUser').attr('checked')) {
		checked = 1;
	}
	$('#post_show_user').val(checked);

	$('#post_form').submit();
}
</script>

</body>
</html>
