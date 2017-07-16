<?php require_once(dirname(__FILE__).'/../login/myauth.php'); ?>
<?php require_once(dirname(__FILE__).'/../define.php'); ?>
<?php if (($auth->getParent_role_customer() & ROLE_MEMBER_VIW) == 0) { header("HTTP/1.0 404 Not Found"); exit(); } ?>
<?php require_once(dirname(__FILE__).'/../Encode.php'); ?>
<?php require_once(dirname(__FILE__).'/../db_config.php'); ?>
<?php require_once(dirname(__FILE__).'/class/CustomerListSQL.class.php'); ?>

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
<title><?php echo APP_TITLE; ?> | 顧客マスタ</title>
<meta name="viewport" content="width=980px">
<link rel="shortcut icon" href="<?php echo PROJECT_ROOT ?>/favicon.ico">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/style.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/green/jquery-ui-1.9.2.custom.min.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/cj/css/customer_list.css" media="all">

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
	$customer = new CustomerListSQL($dbopts);
	$list = $customer->getList();
?>

<div id="content" class="clearfix">
<div class="inner">

<?php if (isset($_SESSION[S_MESSAGE2]) && strlen($_SESSION[S_MESSAGE2])) {
	echo '<div class="info">'.$_SESSION[S_MESSAGE2].'</div>';
	$_SESSION[S_MESSAGE2] = '';
} ?>

	<div class="item_list">

		<form id="post_form" method="post" action="customer_list_control.php" >
			<input type="hidden" name="post_type" id="post_type" value="" />
			<input type="hidden" name="post_no" id="post_no" value="" />
			<input type="hidden" name="post_name" id="post_name" value="" />
			<input type="hidden" name="post_sortno" id="post_sortno" value="" />
			<input type="hidden" name="post_sortno_after" id="post_sortno_after" value="" />
		</form>

		<input type="hidden" id="edit_okcancel" value="" />

		<table id="list">
			<tr>
				<th>顧客</th><th></th>
			</tr>

			<?php if ($list) { foreach ($list as $row) { ?>
				<tr class="tr_item">
					<td class="td_name">
						<?php echo e($row['name']); ?>
					</td>
					<td class="td_ope" nowrap>
						<input type="hidden" class="no" value="<?php echo $row['no']; ?>" />
						<input type="hidden" class="name" value="<?php echo $row['name']; ?>" />
						<input type="hidden" class="sortno" value="<?php echo $row['sortno']; ?>" />
						<?php if (($auth->getParent_role_customer() & ROLE_MEMBER_EDT) != 0) { ?>
							<input type="button" value="▲" class="btn_up" onclick="upRow(this)"/>
							<input type="button" value="▼" class="btn_dw" onclick="dwRow(this)"/>
							<input type="button" value="編集" class="btn_edit" onclick="updateRow(this)"/>
						<?php } ?>
						<?php if (($auth->getParent_role_customer() & ROLE_MEMBER_ADD) != 0) { ?>
							<input type="button" value="追加" class="btn_add" onclick="insertRow(this)"/>
						<?php } ?>
						<?php if (($auth->getParent_role_customer() & ROLE_MEMBER_DEL) != 0) { ?>
							<input type="button" value="削除" class="btn_del" onclick="deleteRow(this)"/>
						<?php } ?>
					</td>
				</tr>
			<?php } } else { ?>
				<tr class="tr_item">
					<td class="td_name">
					</td>
					<td class="td_ope" nowrap>
						<input type="hidden" class="no" value="" />
						<input type="hidden" class="name" value="" />
						<input type="hidden" class="sortno" value="" />
						<?php if (($auth->getParent_role_customer() & ROLE_MEMBER_ADD) != 0) { ?>
							<input type="button" value="追加" class="btn_add" onclick="insertRow(this)"/>
						<?php } ?>
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
	case 'INSERT':
	case 'UPDATE':
		$('#post_name').val($("#editstr").val());
		break;
	}

	$("#edit_dialog").dialog('close');
	$('#post_form').submit();
}

function insertRow(obj) {

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;
	var no = getElementByClassName(tr,'no')[0].value;
	var name = getElementByClassName(tr,'name')[0].value;
	var sortno = getElementByClassName(tr,'sortno')[0].value;

	//選択行の情報を格納
	$('#post_no').val(no);
	$('#post_sortno').val(sortno);
	$('#post_sortno_after').val(sortno);
	$('#post_name').val(name);

	//入力画面表示
	$("#post_type").val('INSERT');
	$("#editstr").val('');
	$('#editstr-disabled').html($("#editstr").val());
	$('#editstr').css('display','inline');
	$('#editstr-disabled').css('display','none');
	$('#validateTips').html('追加する顧客名を入力してください。');
	$('#validateTips').css('color','blue');
	$('#ui-dialog-title-edit_dialog').html('顧客追加');
	$('#label-editstr').html('顧客名');
	$('#edit_dialog').dialog('open');
}

function updateRow(obj) {

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;
	var no = getElementByClassName(tr,'no')[0].value;
	var name = getElementByClassName(tr,'name')[0].value;
	var sortno = getElementByClassName(tr,'sortno')[0].value;

	//選択行の情報を格納
	$('#post_no').val(no);
	$('#post_sortno').val(sortno);
	$('#post_sortno_after').val(sortno);
	$('#post_name').val(name);

	//入力画面表示
	$("#post_type").val('UPDATE');
	$("#editstr").val(name);
	$('#editstr-disabled').html($("#editstr").val());
	$('#editstr').css('display','inline');
	$('#editstr-disabled').css('display','none');
	$('#validateTips').html('顧客名を編集してください。');
	$('#validateTips').css('color','blue');
	$('#ui-dialog-title-edit_dialog').html('顧客名の変更');
	$('#label-editstr').html('顧客名');
	$('#edit_dialog').dialog('open');
}

function deleteRow(obj) {

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;
	var no = getElementByClassName(tr,'no')[0].value;
	var name = getElementByClassName(tr,'name')[0].value;
	var sortno = getElementByClassName(tr,'sortno')[0].value;

	//選択行の情報を格納
	$('#post_no').val(no);
	$('#post_sortno').val(sortno);
	$('#post_sortno_after').val(sortno);
	$('#post_name').val(name);

	//入力画面表示
	$("#post_type").val('DELETE');
	$("#editstr").val(name);
	$('#editstr-disabled').html($("#editstr").val());
	$('#editstr').css('display','none');
	$('#editstr-disabled').css('display','inline');
	$('#validateTips').html('この顧客を削除しますか？');
	$('#validateTips').css('color','red');
	$('#ui-dialog-title-edit_dialog').html('顧客削除');
	$('#label-editstr').html('顧客名');
	$('#edit_dialog').dialog('open');
}

function upRow(obj) {

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;
	var no = getElementByClassName(tr,'no')[0].value;
	var sortno = getElementByClassName(tr,'sortno')[0].value;

	//選択行の情報を格納
	$('#post_no').val(no);
	$('#post_sortno').val(sortno);
	$('#post_sortno_after').val(parseInt(sortno)-1);

	$("#post_type").val('MOVE');
	$('#post_form').submit();
}

function dwRow(obj) {

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;
	var no = getElementByClassName(tr,'no')[0].value;
	var sortno = getElementByClassName(tr,'sortno')[0].value;

	//選択行の情報を格納
	$('#post_no').val(no);
	$('#post_sortno').val(sortno);
	$('#post_sortno_after').val(parseInt(sortno)+1);

	$("#post_type").val('MOVE');
	$('#post_form').submit();
}
</script>

</body>
</html>
