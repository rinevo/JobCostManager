var timer = false;
var target_obj = null;

$(function() {

	$("#edit_dialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 400,
		width: 400,
		modal: true,
		closeOnEscape: true,
		buttons: {
		"OK": function() {
				if (!edit_ok()) {
					//$('#content').css('visibility','visible');
					return;
				}
			},
		"キャンセル": function() {
				//$('#content').css('visibility','visible');
				$(this).dialog('close');
			}
		},
		close: function() {
		}
	});


	$('#edit_action').keydown(function(e) {
		if (e.which == 13) {
			edit_ok();
		}
	});

});

//追加（画面表示）
function insertDialog(obj) {

	target_obj = obj;

	$("#edit_dialog .edit_type").val('INSERT');
	$('#ui-dialog-title-edit_dialog').html('追加');
	$('#edit_dialog .validateTips').html('追加する内容を入力してください。');
	$('#edit_dialog .validateTips').css('color','blue');

	var objSection = document.getElementById('edit_section');
	select_combobox_value(objSection, '2');
	select_edit_Section(objSection);

	$("#edit_todo").val('0');
	$("#edit_project").val('0');

	var objWork = document.getElementById('edit_work');
	set_combobox_options(objWork, new Array(), '', '');

	$("#edit_customer").val('0');
	$("#edit_process").val('0');
	$("#edit_action").val('');

	$('#edit_dialog').dialog('open');
	//$('#content').css('visibility','hidden');
}

//編集（画面表示）
function editDialog(obj) {

	target_obj = obj;

	$("#edit_dialog .edit_type").val('EDIT');
	$('#ui-dialog-title-edit_dialog').html('編集');
	$('#edit_dialog .validateTips').html('内容を編集してください。');
	$('#edit_dialog .validateTips').css('color','blue');

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;

	var section_no = getElementByClassName(tr,"section_no")[0].value;
	var project_no = getElementByClassName(tr,"project_no")[0].value;
	var work_no = getElementByClassName(tr,"work_no")[0].value;
	var customer_no = getElementByClassName(tr,"customer_no")[0].value;
	var process_no = getElementByClassName(tr,"process_no")[0].value;
	var lbl_action = getElementByClassName(tr,"lbl_action")[0].innerHTML;
	var cbo_action_start = getElementByClassName(tr,"cbo_action_start")[0].value;
	var cbo_action_end = getElementByClassName(tr,"cbo_action_end")[0].value;

	var objSection = document.getElementById('edit_section');
	select_combobox_value(objSection, section_no);
	select_edit_Section(objSection);

	$("#edit_todo").val('0');

	$("#edit_kintai_start").val(cbo_action_start);
	$("#edit_kintai_end").val(cbo_action_end);

	$("#edit_project").val(project_no);
	var objProject = document.getElementById('edit_project');
	select_edit_Project(objProject);
	work_auto_select(work_no);
	$("#edit_customer").val(customer_no);
	$("#edit_process").val(process_no);
	$("#edit_action").val(lbl_action);

	$('#edit_dialog').dialog('open');
	//$('#content').css('visibility','hidden');
}

//業務の自動選択
function work_auto_select(work_no) {
	if (timer !== false) {
		clearTimeout(timer);
	}
	timer = setTimeout(function() {
		if ("none" == document.getElementById("edit_work").style.display) {
			work_auto_select(work_no); //まだ読込が終わってなければ再帰呼び出しでリトライ
		} else {
			$("#edit_work").val(work_no); //読込が終わっていれば選択
		}
	}, 200);
}

//区分選択
function select_edit_Section(obj) {

	//選択した区分
	var section_no = obj.value;

	if (section_no != 2) {
		$("#edit_dialog .tr_project").css('visibility', 'hidden');
		$("#edit_dialog .tr_work").css('visibility', 'hidden');
		$("#edit_dialog .tr_customer").css('visibility', 'hidden');
		$("#edit_dialog .tr_process").css('visibility', 'hidden');
		$("#edit_dialog .tr_action").css('visibility', 'hidden');
		if (section_no == 1) {
			$("#edit_kintai_start").css('display', 'inline');
			$("#edit_kintai_end").css('display', 'none');
			$("#edit_dialog .tr_kintai .lbl_kintai_start").css('display', 'inline');
			$("#edit_dialog .tr_kintai .lbl_kintai_end").css('display', 'none');
		}
		if (section_no == 3) {
			$("#edit_kintai_start").css('display', 'none');
			$("#edit_kintai_end").css('display', 'inline');
			$("#edit_dialog .tr_kintai .lbl_kintai_start").css('display', 'none');
			$("#edit_dialog .tr_kintai .lbl_kintai_end").css('display', 'inline');
		}
	} else {
		$("#edit_dialog .tr_project").css('visibility', 'visible');
		$("#edit_dialog .tr_work").css('visibility', 'visible');
		$("#edit_dialog .tr_customer").css('visibility', 'visible');
		$("#edit_dialog .tr_process").css('visibility', 'visible');
		$("#edit_dialog .tr_action").css('visibility', 'visible');
		$("#edit_kintai_start").css('display', 'none');
		$("#edit_kintai_end").css('display', 'none');
		$("#edit_dialog .tr_kintai .lbl_kintai_start").css('display', 'none');
		$("#edit_dialog .tr_kintai .lbl_kintai_end").css('display', 'none');
	}
}

//プロジェクト選択
function select_edit_Project(obj) {

	//選択したプロジェクト
	var project_no = obj.value;

	//項目設定する業務コントロール
	var objWork = document.getElementById('edit_work');

	//レスポンス待ちの表示
	objWork.setAttribute('style', 'display:none');
	$('#edit_work_busy').css("display","inline");

	var param = {};
	param['post_type'] = 'GET_WORK';
	param['post_project_no'] = project_no;

	//非同期でPOST
	$.post("schedule_list_control.php", param, function(response) {

		//処理結果判断
		if (response[0] != 0) {
			alert_dialog(response[0]);
		} else {
			//業務項目の読込
			set_combobox_options(objWork, response, 'no', 'name');
		}

	},"json")
	.complete(function() {

		//レスポンス待ちの解除
		$('#edit_work_busy').css("display","none");
		objWork.setAttribute('style', 'display:inline');

	});
}

//ＴＯＤＯ選択
function select_edit_Todo(obj) {

	//選択したＴＯＤＯ
	var todo_no = obj.value;

	//項目設定する区分コントロール
	var objSection = document.getElementById('edit_section');
	select_combobox_value(objSection, '2');
	select_edit_Section(objSection);

	//項目設定するプロジェクトコントロール
	var objProject = document.getElementById('edit_project');

	//項目設定する業務コントロール
	var objWork = document.getElementById('edit_work');

	//項目設定する顧客コントロール
	var objCustomer = document.getElementById('edit_customer');

	//項目設定する工程コントロール
	var objProcess = document.getElementById('edit_process');

	//項目設定する行動コントロール
	var objAction = document.getElementById('edit_action');

	var cboUser = document.getElementById('cboUser');

	var param = {};
	param['post_type'] = 'GET_TODO';
	param['post_uid'] = cboUser.value;
	param['post_todo_no'] = todo_no;

	//レスポンス待ちの表示
	obj.setAttribute('style', 'display:none');
	$('#edit_todo_busy').css("display","inline");

	//非同期でPOST
	$.post("schedule_list_control.php", param, function(response) {

		//処理結果判断
		if (response[0] != 0) {
			alert_dialog(response[0]);
		} else {
			//各値の設定
			if (response.length > 1) {
				select_combobox_value(objProject, response[1]['project_no']);
				set_combobox_options(objWork, response[1]['work_list'], 'no', 'name');
				select_combobox_value(objWork, response[1]['work_no']);
				select_combobox_value(objCustomer, response[1]['customer_no']);
				select_combobox_value(objProcess, response[1]['process_no']);
				objAction.value = response[1]['action'];
			}
		}

	},"json")
	.complete(function() {

		//レスポンス待ちの解除
		$('#edit_todo_busy').css("display","none");
		obj.setAttribute('style', 'display:inline');

	});
}
