<?php require_once(dirname(__FILE__).'/../define.php'); ?>
<?php require_once(dirname(__FILE__).'/../Encode.php'); ?>
<?php require_once(dirname(__FILE__).'/myauth.php'); ?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo APP_TITLE; ?> | アカウント情報</title>
<meta name="viewport" content="width=980px">

<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/style.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/login/css/user_info.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/redmond/jquery-ui-1.8.20.custom.css" media="all">

<script src="<?php echo PROJECT_ROOT ?>/js/jquery-1.7.2.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery-ui-1.8.20.custom.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery.bgiframe.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/alert_dialog.js"></script>

</head>
<body>

<?php require_once(dirname(__FILE__).'/../header.php'); ?>

<div id="content" class="clearfix">
	<div class="inner">

<?php if (isset($_SESSION[S_MESSAGE2]) && strlen($_SESSION[S_MESSAGE2])) {
	echo '<div class="info">'.$_SESSION[S_MESSAGE2].'</div>';
	$_SESSION[S_MESSAGE2] = '';
} ?>

	<table class="userinfo">
		<tr>
			<th>ユーザー名</th>
			<td class="userinfo-uid"><?php echo e($auth->getParent_uid()); ?></td>
		</tr>
		<tr>
			<th>名前</th>
			<td class="userinfo-name"><?php echo e($auth->getParent_name()); ?></td>
		</tr>
		<tr class="userinfo-tr">
			<th>メールアドレス</th>
			<td class="userinfo-mail"><?php echo e($auth->getParent_mail()); ?></td>
		</tr>
		<tr>
			<th>パスワード</th>
			<td class="userinfo-pass">***</td>
		</tr>
	</table>

	<ul class="userinfo-menu">
		<li><a href="#" onclick="edit_name();">名前変更</a></li>
		<li><a href="#" onclick="edit_mail();">メールアドレス変更</a></li>
		<li><a href="#" onclick="edit_passwd();">パスワード変更</a></li>
		<li><a href="#" onclick="edit_unsub();" class="userinfo-unsub">退会</a></li>
	</ul>

	<form id="edit_form" method="post" action="" >
		<input type="hidden" name="user_edit_type" id="edit_type" value="" />
		<input type="hidden" name="user_edit_passwd" id="edit_passwd" value="" />
		<input type="hidden" name="user_edit_mail" id="edit_mail" value="" />
		<input type="hidden" name="user_edit_name" id="edit_name" value="" />
	</form>

	</div>
</div>

<?php require_once(dirname(__FILE__).'/../footer.php'); ?>

<div id="alert_dialog" title="確認" style="display:none">
	<label class="label"></label>
</div>

<div id="edit_dialog" title="変更" style="display:none">
	<p id="validateTips"></p>
	<table>
	<tr>
	<td>
		<label id="label-editstr" ></label>
		<input type="text" name="editstr" id="editstr"/>
		<input type="password" name="editstr_pw" id="editstr_pw"/>
	</td>
	<tr>
	<tr>
	<td>
		<label id="label-verifystr" ></label>
		<input type="text" name="verifystr" id="verifystr" value=""/>
		<input type="password" name="verifystr_pw" id="verifystr_pw" value="" />
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
</script>

<script>
$(function() {

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

	$('#verifystr').keydown(function(e) {
		if (e.which == 13) {
			edit_ok();
		}
	});

	$('#editstr_pw').keydown(function(e) {
		if (e.which == 13) {
			edit_ok();
		}
	});

	$('#verifystr_pw').keydown(function(e) {
		if (e.which == 13) {
			edit_ok();
		}
	});

	function edit_ok() {
		var editstr = '';
		var verifystr = '';
		var bverify = false;
		if ($("#editstr_pw").css('display') == 'block') {
			editstr = $("#editstr_pw").val();
		}
		if ($("#verifystr_pw").css('display') == 'block') {
			verifystr = $("#verifystr_pw").val();
			bverify = true;
		}
		if ($("#editstr").css('display') == 'block') {
			editstr = $("#editstr").val();
		}
		if ($("#verifystr").css('display') == 'block') {
			verifystr = $("#verifystr").val();
			bverify = true;
		}
		if (editstr.length < 1) {
			$("#alert_dialog .label").html($('#label-editstr').html() + 'を入力してください。');
			$('#alert_dialog').dialog('open');
			return false;
		}
		if (bverify) {
			if (verifystr.length < 1) {
				$("#alert_dialog .label").html($('#label-verifystr').html() + 'を入力してください。');
				$('#alert_dialog').dialog('open');
				return false;
			}
			if (editstr != verifystr) {
				$("#alert_dialog .label").html('確認用の入力と一致しません。');
				$('#alert_dialog').dialog('open');
				return false;
			}
		}
		switch ($("#edit_type").val()) {
		case 'NAME':
			$("#edit_type").val('UPDATE');
			$("#edit_name").val(editstr);
			break;
		case 'MAIL':
			$("#edit_type").val('UPDATE');
			$("#edit_mail").val(editstr);
			break;
		case 'PASSWD':
			$("#edit_type").val('UPDATE');
			$("#edit_passwd").val(editstr);
			break;
		case 'UNSUB':
			$("#edit_type").val('DELETE');
			$("#edit_passwd").val(editstr);
			break;
		default:
			return false;
		}
		$("#edit_dialog").dialog('close');
		$("#edit_form").attr("action","user_info_control.php");
		$("#edit_form").submit();
		return true;
	}
});
function edit_name() {
	$("#edit_type").val('NAME');
	$("#editstr").val('');
	$("#verifystr").val('');
	$("#editstr_pw").val('');
	$("#verifystr_pw").val('');
	$("#editstr").css('display','block');
	$("#verifystr").css('display','none');
	$("#editstr_pw").css('display','none');
	$("#verifystr_pw").css('display','none');
	$('#validateTips').html('登録されている名前を変更します。');
	$('#validateTips').css('color','blue');
	$('#ui-dialog-title-edit_dialog').html('名前変更');
	$('#label-editstr').html('新しいメール名前');
	$('#label-verifystr').css('display','none');
	$('#edit_dialog').dialog('open');
}
function edit_mail() {
	$("#edit_type").val('MAIL');
	$("#editstr").val('');
	$("#verifystr").val('');
	$("#editstr_pw").val('');
	$("#verifystr_pw").val('');
	$("#editstr").css('display','block');
	$("#verifystr").css('display','block');
	$("#editstr_pw").css('display','none');
	$("#verifystr_pw").css('display','none');
	$('#validateTips').html('登録されているメールアドレスを変更します。');
	$('#validateTips').css('color','blue');
	$('#ui-dialog-title-edit_dialog').html('メールアドレス変更');
	$('#label-editstr').html('新しいメールアドレス');
	$('#label-verifystr').html('新しいメールアドレス（確認用）');
	$('#label-verifystr').css('display','block');
	$('#edit_dialog').dialog('open');
}
function edit_passwd() {
	$("#edit_type").val('PASSWD');
	$("#editstr").val('');
	$("#verifystr").val('');
	$("#editstr_pw").val('');
	$("#verifystr_pw").val('');
	$("#editstr").css('display','none');
	$("#verifystr").css('display','none');
	$("#editstr_pw").css('display','block');
	$("#verifystr_pw").css('display','block');
	$('#validateTips').html('登録されているパスワードを変更します。');
	$('#validateTips').css('color','blue');
	$('#ui-dialog-title-edit_dialog').html('パスワード変更');
	$('#label-editstr').html('新しいパスワード');
	$('#label-verifystr').html('新しいパスワード（確認用）');
	$('#label-verifystr').css('display','block');
	$('#edit_dialog').dialog('open');
}
function edit_unsub() {
	$("#edit_type").val('UNSUB');
	$("#editstr").val('');
	$("#verifystr").val('');
	$("#editstr_pw").val('');
	$("#verifystr_pw").val('');
	$("#editstr").css('display','none');
	$("#verifystr").css('display','none');
	$("#editstr_pw").css('display','block');
	$("#verifystr_pw").css('display','none');
	$('#validateTips').html('退会すると貴方のアカウント情報は全て削除されます。<br>操作を続けるにはパスワードを入力してください。');
	$('#validateTips').css('color','red');
	$('#ui-dialog-title-edit_dialog').html('退会');
	$('#label-editstr').html('パスワード');
	$('#label-verifystr').css('display','none');
	$('#edit_dialog').dialog('open');
}
</script>

</body>
</html>
