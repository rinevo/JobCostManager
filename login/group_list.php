<?php require_once(dirname(__FILE__).'/myauth.php'); ?>
<?php require_once(dirname(__FILE__).'/../define.php'); ?>
<?php require_once(dirname(__FILE__).'/../Encode.php'); ?>
<?php require_once(dirname(__FILE__).'/../db_config.php'); ?>
<?php require_once(dirname(__FILE__).'/class/GroupListSQL.class.php'); ?>

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
<title><?php echo APP_TITLE; ?> | グループ</title>
<meta name="viewport" content="width=980px">
<link rel="shortcut icon" href="<?php echo PROJECT_ROOT ?>/favicon.ico">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/style.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/redmond/jquery-ui-1.8.20.custom.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/login/css/group_list.css" media="all">

<script src="<?php echo PROJECT_ROOT ?>/js/jquery-1.7.2.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery-ui-1.8.20.custom.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery.bgiframe.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/alert_dialog.js"></script>

</head>
<body>

<?php require_once(dirname(__FILE__).'/../header.php'); ?>

<?php
	$group = new GroupListSQL($dbopts);
	$list = $group->getList();
?>

<div id="content" class="clearfix">
<div class="inner">

<?php if (isset($_SESSION[S_MESSAGE2]) && strlen($_SESSION[S_MESSAGE2])) {
	echo '<div class="info">'.$_SESSION[S_MESSAGE2].'</div>';
	$_SESSION[S_MESSAGE2] = '';
} ?>

	<div class="item_list">

		<form id="edit_form" method="post" action="" >
			<input type="hidden" name="group_edit_type" id="edit_type" value="" />
			<input type="hidden" name="group_edit_no" id="edit_no" value="" />
			<input type="hidden" name="group_edit_name" id="edit_name" value="" />
			<input type="hidden" name="group_edit_passwd" id="edit_passwd" value="" />
		</form>

		<input type="hidden" id="edit_okcancel" value="" />

		<a href="#" onclick="edit_insert()">グループを作成する</a>

		<table id="list">
			<tr>
				<th>デフォルト</th><th>グループ名</th><th>権限</th><th></th>
			</tr>

			<?php if ($list) { foreach ($list as $row) { if ($row['role_user'] & ROLE_VIW) { //自分のユーザーへ閲覧権限があるグループを表示 ?>
				<tr class="tr_item">
					<td class="td_default">
						<?php if ($row['group_no'] == $GLOBALS['auth']->getParent_group_no_default()) { ?>
							○
						<?php } ?>
					</td>
					<td class="td_name">
						<?php if ($row['member_status'] == GROUP_MEMBER_STATUS_ENTRY) { ?>
							<?php echo e($row['group_name']); ?>（招待中）
						<?php } else { ?>
							<a href="#" onclick="edit_access('<?php echo e($row['group_no']); ?>','<?php echo e($row['group_name']); ?>')" class="group_access"><?php echo e($row['group_name']); ?></a>
						<?php } ?>
					</td>
					<td class="td_role">
						<?php echo e($row['role_name']); ?>
					</td>
					<td class="td_ope" nowrap>
						<?php if ($row['role_no'] == 1) { //管理者 ?>

							<?php if ($row['member_status'] == GROUP_MEMBER_STATUS_ENTRY) { ?>
								<a href="#" onclick="edit_access('<?php echo e($row['group_no']); ?>','<?php echo e($row['group_name']); ?>')">グループに参加する</a>
								<a href="#" onclick="edit_unsub2('<?php echo e($row['group_no']); ?>','<?php echo e($row['group_name']); ?>')">辞退</a>
							<?php } else { ?>

								<?php if ($row['group_no'] == $GLOBALS['auth']->getParent_group_no()) { ?>
									<?php if ($row['group_no'] != $GLOBALS['auth']->getParent_group_no_default()) { ?>
										<a href="#" onclick="edit_default('<?php echo e($row['group_no']); ?>','<?php echo e($row['group_name']); ?>')">デフォルトにする</a>
									<?php } ?>
									<?php if ($row['group_flg'] != 0) { //ホームにメンバー追加は不可 ?>
										<a href="<?php echo PROJECT_ROOT ?>/login/user_list.php">メンバー</a>
										<a href="<?php echo PROJECT_ROOT ?>/login/role_list.php">権限</a>
									<?php } ?>
								<?php } ?>

								<?php if ($row['group_no'] == $GLOBALS['auth']->getParent_group_no()) { if ($row['group_flg'] != 0) { ?>
									<a href="#" onclick="edit_update('<?php echo e($row['group_no']); ?>','<?php echo e($row['group_name']); ?>')">編集</a>
									<a href="#" onclick="edit_delete('<?php echo e($row['group_no']); ?>','<?php echo e($row['group_name']); ?>')">削除</a>
								<?php } } ?>

							<?php } ?>

						<?php } else { //メンバー ?>

							<?php if ($row['member_status'] == GROUP_MEMBER_STATUS_ENTRY) { ?>
								<a href="#" onclick="edit_access('<?php echo e($row['group_no']); ?>','<?php echo e($row['group_name']); ?>')">グループに参加する</a>
								<a href="#" onclick="edit_unsub2('<?php echo e($row['group_no']); ?>','<?php echo e($row['group_name']); ?>')">辞退</a>
							<?php } else { ?>
								<?php if ($row['group_no'] == $GLOBALS['auth']->getParent_group_no()) { ?>

									<?php if ($row['group_no'] != $GLOBALS['auth']->getParent_group_no_default()) { ?>
										<a href="#" onclick="edit_default('<?php echo e($row['group_no']); ?>','<?php echo e($row['group_name']); ?>')">デフォルトにする</a>
									<?php } ?>
									<?php if (($row['role_user'] & ROLE_MEMBER_VIW) != 0) { ?>
										<a href="<?php echo PROJECT_ROOT ?>/login/user_list.php">メンバー</a>
										<a href="<?php echo PROJECT_ROOT ?>/login/role_list.php">権限</a>
									<?php } ?>

									<?php if (($row['role_user'] & ROLE_DEL) != 0) { ?>
										<a href="#" onclick="edit_unsub('<?php echo e($row['group_no']); ?>','<?php echo e($row['group_name']); ?>')">退会</a>
									<?php } ?>

								<?php } ?>
							<?php } ?>

						<?php } ?>
					</td>
				</tr>
			<?php } } } ?>
		</table>

	</div><!-- item_list -->
</div><!-- inner -->
</div><!-- content -->

<?php require_once(dirname(__FILE__).'/../footer.php'); ?>

<div id="okcancel_dialog" title="確認" style="display:none">
	<label class="label"></label>
</div>

<div id="alert_dialog" title="確認" style="display:none">
	<label class="label"></label>
</div>

<div id="edit_dialog" title="変更" style="display:none">
	<p id="validateTips"></p>
	<table>
	<tr>
	<td>
		<label id="label-editstr" ></label>
		<input type="text" name="editstr" id="editstr" class="text ui-widget-content ui-corner-all" />
		<input type="password" name="editstr_pw" id="editstr_pw" class="text ui-widget-content ui-corner-all" />
	</td>
	<tr>
	</table>
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

function edit_access(no,name) {
	$("#edit_type").val('ACCESS');
	$("#edit_no").val(no);
	$("#edit_name").val(name);
	$("#edit_form").attr("action","group_list_control.php");
	$("#edit_form").submit();
}

function edit_default(no,name) {
	$("#edit_type").val('DEFAULT');
	$("#edit_no").val(no);
	$("#edit_name").val(name);
	$("#edit_form").attr("action","group_list_control.php");
	$("#edit_form").submit();
}

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

	$("#edit_dialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 240,
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

	$('#editstr_pw').keydown(function(e) {
		if (e.which == 13) {
			edit_ok();
		}
	});

	function edit_ok() {
		var editstr = '';
		if ($("#editstr").css('display') == 'block') {
			editstr = $("#editstr").val();
		}
		if ($("#editstr_pw").css('display') == 'block') {
			editstr = $("#editstr_pw").val();
		}
		if (editstr.length < 1) {
			$("#alert_dialog .label").html($('#label-editstr').html() + 'を入力してください。');
			$('#alert_dialog').dialog('open');
			return false;
		}
		switch ($("#edit_type").val()) {
		case 'INSERT':
			$("#edit_name").val(editstr);
			$("#edit_no").val('');
			break;
		case 'UPDATE':
			$("#edit_name").val(editstr);
			break;
		case 'DELETE':
		case 'UNSUB':
			$("#edit_passwd").val(editstr);
			break;
		default:
			return false;
		}
		$("#edit_dialog").dialog('close');
		$("#edit_form").attr("action","group_list_control.php");
		$("#edit_form").submit();
		return true;
	}
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
		$("#edit_name").val('');
		$("#edit_form").attr("action","");
	}
}

function edit_insert() {
	$("#edit_type").val('INSERT');
	$("#editstr").val('');
	$("#editstr").css('display','block');
	$("#editstr_pw").css('display','none');
	$('#validateTips').html('作成するグループの情報を設定してください。');
	$('#validateTips').css('color','blue');
	$('#ui-dialog-title-edit_dialog').html('グループ作成');
	$('#label-editstr').html('グループ名');
	$('#edit_dialog').dialog('open');
}

function edit_update(no,name) {
	$("#edit_type").val('UPDATE');
	$("#edit_no").val(no);
	$("#editstr").val(name);
	$("#editstr").css('display','block');
	$("#editstr_pw").css('display','none');
	$('#validateTips').html('グループの情報を設定してください。');
	$('#validateTips').css('color','blue');
	$('#ui-dialog-title-edit_dialog').html('グループ編集');
	$('#label-editstr').html('グループ名');
	$('#edit_dialog').dialog('open');
}

function edit_delete(no,name) {
	$("#edit_type").val('DELETE');
	$("#edit_no").val(no);
	$("#edit_name").val(name);
	$("#editstr").css('display','none');
	$("#editstr_pw").css('display','block');
	$('#validateTips').html('削除するとグループで共有している情報は全て削除されます。<br>グループ名「' + name + '」の削除を続けるにはパスワードを入力してください。');
	$('#validateTips').css('color','red');
	$('#ui-dialog-title-edit_dialog').html('削除');
	$('#label-editstr').html('パスワード');
	$('#edit_dialog').dialog('open');
}

function edit_unsub(no,name) {
	$("#edit_type").val('UNSUB');
	$("#edit_no").val(no);
	$("#edit_name").val(name);
	$("#editstr").css('display','none');
	$("#editstr_pw").css('display','block');
	$('#validateTips').html('退会するとグループで共有している情報は見れなくなります。<br>グループ名「' + name + '」の退会を続けるにはパスワードを入力してください。');
	$('#validateTips').css('color','red');
	$('#ui-dialog-title-edit_dialog').html('退会');
	$('#label-editstr').html('パスワード');
	$('#edit_dialog').dialog('open');
}

function edit_unsub2(no,name) {
	$("#edit_type").val('UNSUB');
	$("#edit_no").val(no);
	$("#edit_name").val(name);
	$("#editstr").css('display','none');
	$("#editstr_pw").css('display','block');
	$('#validateTips').html('辞退すると再び招待されるまで参加できなくなります。<br>グループ名「' + name + '」の参加を辞退するにはパスワードを入力してください。');
	$('#validateTips').css('color','red');
	$('#ui-dialog-title-edit_dialog').html('辞退');
	$('#label-editstr').html('パスワード');
	$('#edit_dialog').dialog('open');
}
</script>

</body>
</html>
