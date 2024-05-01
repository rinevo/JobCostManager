//更新
function edit_update(uid) {
	$("#user_edit_type").val('UPDATE');
	$("#user_edit_uid").val(uid);

	removeElementName("edit_form", "user_edit_role");

	var user_edit_role = appendHiddenInput("edit_form", "user_edit_role");
	user_edit_role.value = $("#edit_role_update").val();

	$("#edit_form").submit();
}
//削除
function edit_delete(uid, obj) {
	$("#user_edit_type").val('DELETE');
	$("#user_edit_uid").val(uid);
	var tr = obj.parentNode.parentNode;
	var name = '';
	if (parseInt(getElementByClassName(tr, 'status')[0].value) == 0) {
		name = jQuery.trim(getElementByClassName(tr, 'td_name')[0].innerHTML);
	} else {
		name = '招待中の ' + jQuery.trim(getElementByClassName(tr, 'td_mail')[0].innerHTML);
	}
	okcancel_dialog(name + ' をメンバーから削除しますか？');
}
//編集
function edit_update_start(uid) {
	$("#user_edit_type").val('EDIT_START');
	$("#user_edit_uid").val(uid);
	$("#edit_form").submit();
}
//キャンセル
function edit_cancel() {
	$("#user_edit_type").val('EDIT_END');
	$("#edit_form").submit();
}

//確認ダイアログ
function okcancel_dialog(message) {
	$("#okcancel_dialog .label").html(message);
	$('#okcancel_dialog').dialog('open');
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

});
function okcancel_dialog_control() {
	if ($("#edit_okcancel").val() == 'OK') {
		$("#edit_form").submit();
	} else {
		$("#edit_okcancel").val('');
		$("#user_edit_type").val('');
		$("#user_edit_uid").val('');
	}
}

//招待
function edit_invite() {
	$("#user_edit_type").val('INVITE');
	$("#edit_dialog .tr_uid").css('display','none');
	$("#edit_dialog .tr_name").css('display','none');
	$("#edit_dialog .tr_passwd").css('display','none');
	$("#edit_uid").val('');
	$("#edit_name").val('');
	$("#edit_mail").val('');
	$("#edit_passwd").val('');
	$("#edit_role").val('2');
	$('#validateTips').html('グループに招待する知人のメールアドレスを入力してください。<br>OKボタンを押すと、招待メールを送信します。');
	$('#validateTips').css('color','blue');
	$('#ui-dialog-title-edit_dialog').html('グループへの招待');
	$('#edit_dialog').dialog('open');
}
//追加
function edit_add() {
	$("#user_edit_type").val('INSERT');
	$("#edit_dialog .tr_uid").css('display','table-row');
	$("#edit_dialog .tr_name").css('display','table-row');
	$("#edit_dialog .tr_passwd").css('display','table-row');
	$("#edit_uid").val('');
	$("#edit_name").val('');
	$("#edit_mail").val('');
	$("#edit_role").val('2');
	$("#edit_passwd").val('');
	$('#validateTips').html('グループに追加するユーザーの情報を入力してください。');
	$('#validateTips').css('color','blue');
	$('#ui-dialog-title-edit_dialog').html('ユーザーの追加');
	$('#edit_dialog').dialog('open');
}
$(function() {

	$("#edit_dialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 350,
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

	$('#edit_uid').keydown(function(e) {
		if (e.which == 13) {
			edit_ok();
		}
	});

	$('#edit_name').keydown(function(e) {
		if (e.which == 13) {
			edit_ok();
		}
	});

	$('#edit_mail').keydown(function(e) {
		if (e.which == 13) {
			edit_ok();
		}
	});

	$('#edit_passwd').keydown(function(e) {
		if (e.which == 13) {
			edit_ok();
		}
	});

	$('#edit_role').keydown(function(e) {
		if (e.which == 13) {
			edit_ok();
		}
	});

});
function edit_ok() {

	removeElementName("edit_form", "user_edit_uid");
	removeElementName("edit_form", "user_edit_name");
	removeElementName("edit_form", "user_edit_mail");
	removeElementName("edit_form", "user_edit_passwd");
	removeElementName("edit_form", "user_edit_role");

	switch ($("#user_edit_type").val()) {
	case 'INVITE':

		var edit_mail = $("#edit_mail").val();
		if (edit_mail.length < 1) {
			$("#alert_dialog .label").html($('#edit_dialog .tr_mail label').html() + 'を入力してください。');
			$('#alert_dialog').dialog('open');
			return false;
		}
		var edit_role = $("#edit_role").val();
		if (edit_role.length < 1) {
			$("#alert_dialog .label").html($('#edit_dialog .tr_role label').html() + 'を入力してください。');
			$('#alert_dialog').dialog('open');
			return false;
		}

		var user_edit_mail = appendHiddenInput("edit_form", "user_edit_mail");
		user_edit_mail.value = edit_mail;

		var user_edit_role = appendHiddenInput("edit_form", "user_edit_role");
		user_edit_role.value = edit_role;

		break;
	case 'INSERT':

		var edit_uid = $("#edit_uid").val();
		if (edit_uid.length < 1) {
			$("#alert_dialog .label").html($('#edit_dialog .tr_uid label').html() + 'を入力してください。');
			$('#alert_dialog').dialog('open');
			return false;
		}
		var edit_name = $("#edit_name").val();
		if (edit_name.length < 1) {
			$("#alert_dialog .label").html($('#edit_dialog .tr_name label').html() + 'を入力してください。');
			$('#alert_dialog').dialog('open');
			return false;
		}
		var edit_mail = $("#edit_mail").val();
		var edit_passwd = $("#edit_passwd").val();
		if (edit_passwd.length < 1) {
			$("#alert_dialog .label").html($('#edit_dialog .tr_passwd label').html() + 'を入力してください。');
			$('#alert_dialog').dialog('open');
			return false;
		}
		var edit_role = $("#edit_role").val();
		if (edit_role.length < 1) {
			$("#alert_dialog .label").html($('#edit_dialog .tr_role label').html() + 'を入力してください。');
			$('#alert_dialog').dialog('open');
			return false;
		}

		var user_edit_uid = appendHiddenInput("edit_form", "user_edit_uid");
		user_edit_uid.value = edit_uid;

		var user_edit_name = appendHiddenInput("edit_form", "user_edit_name");
		user_edit_name.value = edit_name;

		var user_edit_mail = appendHiddenInput("edit_form", "user_edit_mail");
		user_edit_mail.value = edit_mail;

		var user_edit_passwd = appendHiddenInput("edit_form", "user_edit_passwd");
		user_edit_passwd.value = edit_passwd;

		var user_edit_role = appendHiddenInput("edit_form", "user_edit_role");
		user_edit_role.value = edit_role;

		break;
	default:
		return false;
	}

	$("#edit_dialog").dialog('close');
	$("#edit_form").submit();
	return true;
}
