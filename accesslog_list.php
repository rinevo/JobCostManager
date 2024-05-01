<?php require_once(dirname(__FILE__).'/login/myauth.php'); ?>
<?php require_once(dirname(__FILE__).'/define.php'); ?>
<?php require_once(dirname(__FILE__).'/Encode.php'); ?>
<?php require_once(dirname(__FILE__).'/db_config.php'); ?>
<?php if ($auth->getParent_uid() != 'admin') { header("HTTP/1.0 404 Not Found"); exit(); } ?>

<?php
// アクセスログ
require_once(dirname(__FILE__).'/class/AccessLogSQL.class.php');
$log = new AccessLogSQL($GLOBALS['dbopts']);
$log->Write(0, '', __FILE__, __FUNCTION__, __LINE__);
$list = $log->getList();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo APP_TITLE; ?> | アクセスログ</title>
<meta name="viewport" content="width=980px">
<link rel="shortcut icon" href="<?php echo PROJECT_ROOT ?>/favicon.ico">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/style.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/redmond/jquery-ui-1.8.20.custom.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/accesslog_list.css" media="all">

<script src="<?php echo PROJECT_ROOT ?>/js/jquery-1.7.2.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery-ui-1.8.20.custom.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery.bgiframe.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/alert_dialog.js"></script>

</head>
<body>

<?php require_once(dirname(__FILE__).'/header.php'); ?>

<div id="content" class="clearfix">
<div class="inner">

<?php echo dirname(__FILE__); ?>

	<div class="item_list">

		<table id="list">
			<?php if ($list) { $list_keys = array_keys($list[0]); ?>
				<tr>
					<?php foreach ($list_keys as $title) { ?>
					<th class="<?php echo $title; ?>"><?php echo $title; ?></th>
					<?php } ?>
				</tr>
				<?php if ($list) { foreach ($list as $row) { ?>
					<tr>
						<?php foreach ($row as $key => $value) { ?>
						<td class="<?php echo $key; ?>">
						<?php echo e($value); ?>
						</td>
						<?php } ?>
					</tr>
				<?php } } ?>
			<?php } ?>
		</table>

	</div><!-- item_list -->

</div><!-- inner -->
</div><!-- content -->

<?php require_once(dirname(__FILE__).'/footer.php'); ?>

<div id="okcancel_dialog" title="確認" style="display:none">
	<label class="label"></label>
</div>

<div id="alert_dialog" title="確認" style="display:none">
	<label class="label"></label>
</div>

<script>
function attempt_focus(){
	// 画面表示後の処理
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

	$("#okcancel_dialog").dialog({
		bgiframe: true,
		autoOpen: false,
		width: 320,
		modal: true,
		buttons: {
			"OK": function() {
				$("#edit_okcancel").val('OK');
				$(this).dialog("close");
				okcancel_dialog_control();
			},
			"キャンセル": function() {
				$("#edit_okcancel").val('CANCEL');
				$(this).dialog('close');
				okcancel_dialog_control();
			}
		}
	});

});

function alert_dialog(message) {
	$("#alert_dialog .label").html(message);
	$('#alert_dialog').dialog('open');
}

function okcancel_dialog(message) {
	$("#okcancel_dialog .label").html(message);
	$('#okcancel_dialog').dialog('open');
}

function okcancel_dialog_control() {
	if ($("#edit_okcancel").val() == 'OK') {
		$("#edit_form").submit();
	} else {
		$("#edit_okcancel").val('');
		$("#edit_type").val('');
		$("#edit_no").val('');
		$("#edit_customer_no").val('');
		$("#edit_uid").val('');
		$("#edit_bikou").val('');
		$("#edit_redirect").val('');
		$("#edit_form").attr("action","");
	}
}

</script>

</body>
</html>
