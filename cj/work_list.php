<?php require_once(dirname(__FILE__).'/../login/myauth.php'); ?>
<?php require_once(dirname(__FILE__).'/../define.php'); ?>
<?php if (($auth->getParent_role_work() & ROLE_MEMBER_VIW) == 0) { header("HTTP/1.0 404 Not Found"); exit(); } ?>
<?php require_once(dirname(__FILE__).'/../Encode.php'); ?>
<?php require_once(dirname(__FILE__).'/../db_config.php'); ?>
<?php require_once(dirname(__FILE__).'/class/WorkListSQL.class.php'); ?>

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
<title><?php echo APP_TITLE; ?> | 作業マスタ</title>
<meta name="viewport" content="width=980px">
<link rel="shortcut icon" href="<?php echo PROJECT_ROOT ?>/favicon.ico">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/style.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/green/jquery-ui-1.9.2.custom.min.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/cj/css/work_list.css" media="all">

<script src="<?php echo PROJECT_ROOT ?>/js/jquery-1.7.2.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery-ui-1.8.20.custom.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery.bgiframe.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/alert_dialog.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/browser.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/cj/js/common.js"></script>

</head>
<body>

<?php require_once(dirname(__FILE__).'/../header.php'); ?>

<?php
	$work = new WorkListSQL($dbopts);
	$list = $work->getWork_list();
?>

<div id="content" class="clearfix">
<div class="inner">

<?php if (isset($_SESSION[S_MESSAGE2]) && strlen($_SESSION[S_MESSAGE2])) {
	echo '<div class="info">'.$_SESSION[S_MESSAGE2].'</div>';
	$_SESSION[S_MESSAGE2] = '';
} ?>

	<div class="item_list">

		<form id="post_form" method="post" action="work_list_control.php" >
			<input type="hidden" name="post_type" id="post_type" value="" />
			<input type="hidden" name="post_project_no" id="post_project_no" value="" />
			<input type="hidden" name="post_project_name" id="post_project_name" value="" />
			<input type="hidden" name="post_project_sortno" id="post_project_sortno" value="" />
			<input type="hidden" name="post_project_sortno_after" id="post_project_sortno_after" value="" />
			<input type="hidden" name="post_work_no" id="post_work_no" value="" />
			<input type="hidden" name="post_work_name" id="post_work_name" value="" />
			<input type="hidden" name="post_work_sortno" id="post_work_sortno" value="" />
			<input type="hidden" name="post_work_sortno_after" id="post_work_sortno_after" value="" />
		</form>

		<input type="hidden" id="edit_okcancel" value="" />

		<table id="list">
			<tr>
				<th>プロジェクト</th><th>作業</th><th></th>
			</tr>

			<?php if ($list) { foreach ($list as $row) { ?>
				<tr class="tr_item">
					<?php if (isset($row['rowspan'])) { ?>
					<td class="td_project" rowspan="<?php echo $row['rowspan']; ?> ?>">
						<?php echo e($row['project_name']); ?><br/>
						<?php if (($auth->getParent_role_work() & ROLE_MEMBER_EDT) != 0) { ?>
							<input type="button" value="▲" class="btn_project_up" onclick="project_upRow(this)"/>
							<input type="button" value="▼" class="btn_project_dw" onclick="project_dwRow(this)"/>
							<input type="button" value="編集" class="btn_project_edit" onclick="project_update(this)"/>
						<?php } ?>
						<?php if (($auth->getParent_role_work() & ROLE_MEMBER_ADD) != 0) { ?>
							<input type="button" value="追加" class="btn_project_add" onclick="project_insert(this)"/>
						<?php } ?>
						<?php if (($auth->getParent_role_work() & ROLE_MEMBER_DEL) != 0) { ?>
							<input type="button" value="削除" class="btn_project_del" onclick="project_delete(this)"/>
						<?php } ?>
					</td>
					<?php } ?>
					<td class="td_work">
						<?php echo e($row['work_name']); ?>
					</td>
					<td class="td_ope" nowrap>
						<input type="hidden" class="project_no" value="<?php echo $row['project_no']; ?>" />
						<input type="hidden" class="project_name" value="<?php echo $row['project_name']; ?>" />
						<input type="hidden" class="project_sortno" value="<?php echo $row['project_sortno']; ?>" />
						<input type="hidden" class="work_no" value="<?php echo $row['work_no']; ?>" />
						<input type="hidden" class="work_name" value="<?php echo $row['work_name']; ?>" />
						<input type="hidden" class="work_sortno" value="<?php echo $row['work_sortno']; ?>" />
						<?php if (($auth->getParent_role_work() & ROLE_MEMBER_EDT) != 0) { ?>
						<?php if (!empty($row['work_no'])) { ?>
							<input type="button" value="▲" class="btn_work_up" onclick="work_upRow(this)"/>
							<input type="button" value="▼" class="btn_work_dw" onclick="work_dwRow(this)"/>
							<input type="button" value="編集" class="btn_work_edit" onclick="work_update(this)"/>
						<?php } } ?>
						<?php if (($auth->getParent_role_work() & ROLE_MEMBER_ADD) != 0) { ?>
							<input type="button" value="追加" class="btn_work_add" onclick="work_insert(this)"/>
						<?php } ?>
						<?php if (($auth->getParent_role_work() & ROLE_MEMBER_DEL) != 0) { ?>
						<?php if (!empty($row['work_no'])) { ?>
							<input type="button" value="削除" class="btn_work_del" onclick="work_delete(this)"/>
						<?php } } ?>
					</td>
				</tr>
			<?php } } else { ?>
				<tr class="tr_item">
					<td class="td_project">
						<?php if (($auth->getParent_role_work() & ROLE_MEMBER_ADD) != 0) { ?>
							<input type="button" value="追加" class="btn_project_add" onclick="project_insert(this)"/>
						<?php } ?>
					</td>
					<td class="td_work">
					</td>
					<td class="td_ope" nowrap>
						<input type="hidden" class="project_no" value="" />
						<input type="hidden" class="work_no" value="" />
						<input type="hidden" class="project_name" value="" />
						<input type="hidden" class="work_name" value="" />
						<input type="hidden" class="project_sortno" value="" />
						<input type="hidden" class="work_sortno" value="" />
					</td>
				</tr>
			<?php } ?>
		</table>

	</div><!-- item_list -->
</div><!-- inner -->
</div><!-- content -->

<?php require_once(dirname(__FILE__).'/../footer.php'); ?>

<div id="edit_dialog" title="変更" style="display:none">
<p id="validateTips"></p>
<table>
<tr>
<td>
	<label id="label-editstr" ></label>
	<input type="text" name="editstr" id="editstr"/>
	<label id="editstr-disabled"></label>
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

		} catch(e){}
	}, 200);
}
attempt_focus();

$(function() {

	$("#edit_dialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 200,
		width: 400,
		modal: true,
		closeOnEscape: true,
		buttons: {
		"OK": function() {
				if (!edit_ok()) {
					return;
				}
			},
		"キャンセル": function() {
				$(this).dialog('close');
			}
		},
		close: function() {
		}
	});


	$('#editstr').keydown(function(e) {
		if (e.which == 13) {
			edit_ok();
		}
	});

});

function edit_ok() {

	if ($("#editstr").val() < 1) {
		$("#alert_dialog .label").html($('#ui-dialog-title-edit_dialog').html() + 'を入力してください。');
		$('#alert_dialog').dialog('open');
		return false;
	}

	switch ($("#post_type").val()) {
	case 'PROJECT_INSERT':
	case 'PROJECT_UPDATE':
		$('#post_project_name').val($("#editstr").val());
		break;
	case 'WORK_INSERT':
	case 'WORK_UPDATE':
		$('#post_work_name').val($("#editstr").val());
		break;
	}

	$("#edit_dialog").dialog('close');
	$('#post_form').submit();
}

function project_insert(obj) {

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;
	var project_no = getElementByClassName(tr,'project_no')[0].value;
	var project_name = getElementByClassName(tr,'project_name')[0].value;
	var project_sortno = getElementByClassName(tr,'project_sortno')[0].value;

	//選択行の情報を格納
	$('#post_project_no').val(project_no);
	$('#post_project_sortno').val(project_sortno);
	$('#post_project_sortno_after').val(project_sortno);
	$('#post_project_name').val(project_name);

	//入力画面表示
	$("#post_type").val('PROJECT_INSERT');
	$("#editstr").val('');
	$('#editstr').css('display','inline');
	$('#editstr-disabled').css('display','none');
	$('#validateTips').html('追加するプロジェクト名を入力してください。');
	$('#validateTips').css('color','blue');
	$('#ui-dialog-title-edit_dialog').html('プロジェクト追加');
	$('#label-editstr').html('プロジェクト名');
	$('#edit_dialog').dialog('open');
}

function project_update(obj) {

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;
	var project_no = getElementByClassName(tr,'project_no')[0].value;
	var project_name = getElementByClassName(tr,'project_name')[0].value;
	var project_sortno = getElementByClassName(tr,'project_sortno')[0].value;

	//選択行の情報を格納
	$('#post_project_no').val(project_no);
	$('#post_project_sortno').val(project_sortno);
	$('#post_project_sortno_after').val(project_sortno);
	$('#post_project_name').val(project_name);

	//入力画面表示
	$("#post_type").val('PROJECT_UPDATE');
	$("#editstr").val(project_name);
	$('#editstr').css('display','inline');
	$('#editstr-disabled').css('display','none');
	$('#validateTips').html('プロジェクト名を編集してください。');
	$('#validateTips').css('color','blue');
	$('#ui-dialog-title-edit_dialog').html('プロジェクト名の変更');
	$('#label-editstr').html('プロジェクト名');
	$('#edit_dialog').dialog('open');
}

function project_delete(obj) {

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;
	var project_no = getElementByClassName(tr,'project_no')[0].value;
	var project_name = getElementByClassName(tr,'project_name')[0].value;
	var project_sortno = getElementByClassName(tr,'project_sortno')[0].value;

	//選択行の情報を格納
	$('#post_project_no').val(project_no);
	$('#post_project_sortno').val(project_sortno);
	$('#post_project_sortno_after').val(project_sortno);
	$('#post_project_name').val(project_name);

	//入力画面表示
	$("#post_type").val('PROJECT_DELETE');
	$("#editstr").val(project_name);
	$("#editstr-disabled").html(project_name);
	$('#editstr').css('display','none');
	$('#editstr-disabled').css('display','inline');
	$('#validateTips').html('このプロジェクトを削除しますか？');
	$('#validateTips').css('color','red');
	$('#ui-dialog-title-edit_dialog').html('プロジェクト削除');
	$('#label-editstr').html('プロジェクト名');
	$('#edit_dialog').dialog('open');
}

function project_upRow(obj) {

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;
	var project_no = getElementByClassName(tr,'project_no')[0].value;
	var project_sortno = getElementByClassName(tr,'project_sortno')[0].value;

	//選択行の情報を格納
	$('#post_project_no').val(project_no);
	$('#post_project_sortno').val(project_sortno);
	$('#post_project_sortno_after').val(parseInt(project_sortno)-1);

	$("#post_type").val('PROJECT_MOVE');
	$('#post_form').submit();
}

function project_dwRow(obj) {

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;
	var project_no = getElementByClassName(tr,'project_no')[0].value;
	var project_sortno = getElementByClassName(tr,'project_sortno')[0].value;

	//選択行の情報を格納
	$('#post_project_no').val(project_no);
	$('#post_project_sortno').val(project_sortno);
	$('#post_project_sortno_after').val(parseInt(project_sortno)+1);

	$("#post_type").val('PROJECT_MOVE');
	$('#post_form').submit();
}

function work_insert(obj) {

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;
	var project_no = getElementByClassName(tr,'project_no')[0].value;
	var work_no = getElementByClassName(tr,'work_no')[0].value;
	var work_name = getElementByClassName(tr,'work_name')[0].value;
	var work_sortno = getElementByClassName(tr,'work_sortno')[0].value;

	//選択行の情報を格納
	$('#post_project_no').val(project_no);
	$('#post_work_no').val(work_no);
	$('#post_work_sortno').val(work_sortno);
	$('#post_work_sortno_after').val(work_sortno);
	$('#post_work_name').val(work_name);

	//入力画面表示
	$("#post_type").val('WORK_INSERT');
	$("#editstr").val('');
	$("#editstr-disabled").html('');
	$('#editstr').css('display','inline');
	$('#editstr-disabled').css('display','none');
	$('#validateTips').html('追加する業務名を入力してください。');
	$('#validateTips').css('color','blue');
	$('#ui-dialog-title-edit_dialog').html('業務追加');
	$('#label-editstr').html('業務名');
	$('#edit_dialog').dialog('open');
}

function work_update(obj) {

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;
	var project_no = getElementByClassName(tr,'project_no')[0].value;
	var work_no = getElementByClassName(tr,'work_no')[0].value;
	var work_name = getElementByClassName(tr,'work_name')[0].value;
	var work_sortno = getElementByClassName(tr,'work_sortno')[0].value;

	//選択行の情報を格納
	$('#post_project_no').val(project_no);
	$('#post_work_no').val(work_no);
	$('#post_work_sortno').val(work_sortno);
	$('#post_work_sortno_after').val(work_sortno);
	$('#post_work_name').val(work_name);

	//入力画面表示
	$("#post_type").val('WORK_UPDATE');
	$("#editstr").val(work_name);
	$("#editstr-disabled").html(work_name);
	$('#editstr').css('display','inline');
	$('#editstr-disabled').css('display','none');
	$('#validateTips').html('業務名を編集してください。');
	$('#validateTips').css('color','blue');
	$('#ui-dialog-title-edit_dialog').html('業務名の変更');
	$('#label-editstr').html('業務名');
	$('#edit_dialog').dialog('open');
}

function work_delete(obj) {

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;
	var project_no = getElementByClassName(tr,'project_no')[0].value;
	var work_no = getElementByClassName(tr,'work_no')[0].value;
	var work_name = getElementByClassName(tr,'work_name')[0].value;
	var work_sortno = getElementByClassName(tr,'work_sortno')[0].value;

	//選択行の情報を格納
	$('#post_project_no').val(project_no);
	$('#post_work_no').val(work_no);
	$('#post_work_sortno').val(work_sortno);
	$('#post_work_sortno_after').val(work_sortno);
	$('#post_work_name').val(work_name);

	//入力画面表示
	$("#post_type").val('WORK_DELETE');
	$("#editstr").val(work_name);
	$("#editstr-disabled").html(work_name);
	$('#editstr').css('display','none');
	$('#editstr-disabled').css('display','inline');
	$('#validateTips').html('この業務を削除しますか？');
	$('#validateTips').css('color','red');
	$('#ui-dialog-title-edit_dialog').html('業務削除');
	$('#label-editstr').html('業務名');
	$('#edit_dialog').dialog('open');
}

function work_upRow(obj) {

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;
	var project_no = getElementByClassName(tr,'project_no')[0].value;
	var work_no = getElementByClassName(tr,'work_no')[0].value;
	var work_sortno = getElementByClassName(tr,'work_sortno')[0].value;

	//選択行の情報を格納
	$('#post_project_no').val(project_no);
	$('#post_work_no').val(work_no);
	$('#post_work_sortno').val(work_sortno);
	$('#post_work_sortno_after').val(parseInt(work_sortno)-1);

	$("#post_type").val('WORK_MOVE');
	$('#post_form').submit();
}

function work_dwRow(obj) {

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;
	var project_no = getElementByClassName(tr,'project_no')[0].value;
	var work_no = getElementByClassName(tr,'work_no')[0].value;
	var work_sortno = getElementByClassName(tr,'work_sortno')[0].value;

	//選択行の情報を格納
	$('#post_project_no').val(project_no);
	$('#post_work_no').val(work_no);
	$('#post_work_sortno').val(work_sortno);
	$('#post_work_sortno_after').val(parseInt(work_sortno)+1);

	$("#post_type").val('WORK_MOVE');
	$('#post_form').submit();
}
</script>

</body>
</html>
