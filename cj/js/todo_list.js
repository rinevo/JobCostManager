//OK処理
function edit_ok() {

	var section_no = $("#edit_section").val();

	//入力チェック
	if (section_no == 2) {
		if ($("#edit_action").val().length < 1) {
			$("#alert_dialog .label").html($('#edit_dialog .tr_action label').html() + 'を入力してください。');
			$('#alert_dialog').dialog('open');
			return false;
		}
	}

	var edit_type = $("#edit_dialog .edit_type").val();
	var rowIndex = target_obj.parentNode.parentNode.rowIndex;
	if (rowIndex < 0) {
		return false;
	}

	$("#post_type").val(edit_type);

	var rows = document.getElementById('list').rows;
	var row = rows[rowIndex];
	var input =null;

	var sortno = getElementByClassName(row,'sortno')[0].value;
	$("#post_sortno").val(sortno);
	$('#post_sortno_after').val(sortno);

	input = getElementByClassName(row,"no")[0];
	$("#post_no").val(input.value);

	$("#post_project_no").val($("#edit_project").val());
	$("#post_work_no").val($("#edit_work").val());
	$("#post_customer_no").val($("#edit_customer").val());
	$("#post_process_no").val($("#edit_process").val());
	$("#post_action").val($("#edit_action").val());

	$("#edit_dialog").dialog('close');

	// フォーム内の情報を配列にまとめる
	var form = $("#post_form");
	var param = {};
	$(form.serializeArray()).each(function(i, v) {
		param[v.name] = v.value;
	});

	//レスポンス待ちの表示
	var busy = getElementByClassName(row, "busy")[0];
	busy.setAttribute('style','display:inline;');
	target_obj.disabled = true;

	//非同期でPOST
	$.post("todo_list_control.php", param, function(response) {

		//処理結果判断
		if (response[0] != 0) {
			alert_dialog(response[0]);
		} else {

			switch (edit_type) {
			case 'INSERT':
				list_insertRow(row, response[1]);
				break;
			case 'EDIT':
				show_data(row, response[1]);
				break;
			}

			//tr_project行の表示を更新
			reset_tr_project();

			//編集画面のＴＯＤＯ選択項目を更新
			refreshTodoList();
		}

	},"json")
	.complete(function() {

		//レスポンス待ちの解除
		busy.setAttribute('style','display:none;');
		target_obj.disabled = false;

	});

	//$('#post_form').submit();
	return true;
}

function deleteRow(obj) {

	target_obj = obj;

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;
	var no = getElementByClassName(tr,'no')[0].value;
	var sortno = getElementByClassName(tr,'sortno')[0].value;
	var action = getElementByClassName(tr,'lbl_action')[0].innerHTML;

	//選択行の情報を格納
	$('#post_no').val(no);
	$('#post_sortno').val(sortno);
	$('#post_sortno_after').val(sortno);

	//入力画面表示
	$("#post_type").val('DELETE');
	$("#delete_dialog .editstr-disabled").html(action);
	$('#delete_dialog .validateTips').html('このTODOを削除しますか？');
	$('#delete_dialog .validateTips').css('color','red');
	$('#ui-dialog-title-delete_dialog').html('TODO削除');
	$('#delete_dialog .label-editstr').html('TODO');

	$('#delete_dialog').dialog('open');
}
$(function() {

	$("#delete_dialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 200,
		width: 400,
		modal: true,
		closeOnEscape: true,
		buttons: {
		"OK": function() {
				$("#delete_dialog").dialog('close');
				//$('#post_form').submit();

				// フォーム内の情報を配列にまとめる
				var form = $("#post_form");
				var param = {};
				$(form.serializeArray()).each(function(i, v) {
					param[v.name] = v.value;
				});

				//レスポンス待ちの表示
				var row = target_obj.parentNode.parentNode;
				var busy = getElementByClassName(row, "busy")[0];
				busy.setAttribute('style','display:inline;');
				target_obj.disabled = true;

				//非同期でPOST
				$.post("todo_list_control.php", param, function(response) {

					//処理結果判断
					if (response[0] != 0) {
						alert_dialog(response[0]);
					} else {
						//行の表示削除
						list_removeRow(row);

						//tr_project行の表示を更新
						reset_tr_project();

						//編集画面のＴＯＤＯ選択項目を更新
						refreshTodoList();
					}

				},"json")
				.complete(function() {

					//レスポンス待ちの解除
					busy.setAttribute('style','display:none;');
					target_obj.disabled = false;

				});

				return;
			},
		"キャンセル": function() {
				$(this).dialog('close');
			}
		},
		close: function() {
		}
	});

});

function list_removeRow(row) {

	//他にもデータがあるか？
	var exists_todo = false;
	var rows = document.getElementById('list').rows;
	for (var i = rows.length - 1; i > 0; i--) {
		if (rows[i].className == 'tr_item' && rows[i] != row) {
			exists_todo = true;
			break;
		}
	}

	if (exists_todo) {

		//他にもデータがあれば、行の表示削除
		removeRow(row, 'list_tbody');

	} else {

		//他にデータが無ければ、対象行を空欄行にする
		show_data(row, null);

	}
}

//編集画面のＴＯＤＯ選択項目を更新
function refreshTodoList() {

	//全ての項目を削除
	var edit_todo = document.getElementById('edit_todo');
	removeChildrenAll(edit_todo);

	var optgroup = null;

	//空項目を先頭に追加
	var option = document.createElement('option');
	option.setAttribute('value', 0);
	option.text = '';
	edit_todo.appendChild(option);

	//グリッドの表示項目を追加
	var rows = document.getElementById('list').rows;
	for (var i = 1; i < rows.length; i++) {
		if (rows[i].className == 'tr_project') {
			var title = getElementByClassName(rows[i],'td_todo')[0].innerHTML;
			optgroup = document.createElement('optgroup');
			optgroup.setAttribute('label', title);
			edit_todo.appendChild(optgroup);
		}
		if (rows[i].className == 'tr_item') {
			var no = getElementByClassName(rows[i],'no')[0].value;
			var action = getElementByClassName(rows[i],'lbl_action')[0].innerHTML;

			option = document.createElement('option');
			option.setAttribute('value', no);
			option.innerHTML = action;

			if (optgroup != null) {
				optgroup.appendChild(option);	//括り有り
			} else {
				edit_todo.appendChild(option);	//括り無し
			}
		}
	}
}

function upRow(obj) {

	target_obj = obj;

	//全ての行
	var rows = document.getElementById('list').rows;

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;
	var no = getElementByClassName(tr,'no')[0].value;
	var sortno = getElementByClassName(tr,'sortno')[0].value;

	//1つ上の行
	var tr_after = getAfterRow(rows, tr);
	if (tr_after.className == "tr_project") {
		tr_after = getAfterRow(rows, tr_after);
	}
	var sortno_after = parseInt(sortno) - 1;
	var input = getElementByClassName(tr_after,'sortno');
	if (input.length > 0) {
		sortno_after = input[0].value;
	}

	//選択行の情報を格納
	$('#post_no').val(no);
	$('#post_sortno').val(sortno);
	$('#post_sortno_after').val(sortno_after);

	$("#post_type").val('MOVE');

	//1つ上の上に移動
	list_moveRow(tr, tr.rowIndex, tr.rowIndex-1);

	//$('#post_form').submit();
}

function dwRow(obj) {

	target_obj = obj;

	//全ての行
	var rows = document.getElementById('list').rows;

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;
	var no = getElementByClassName(tr,'no')[0].value;
	var sortno = getElementByClassName(tr,'sortno')[0].value;

	//1つ下の行
	var tr_after = getBeforeRow(rows, tr);
	if (tr_after.className == "tr_project") {
		tr_after = getBeforeRow(rows, tr_after);
	}
	var sortno_after = parseInt(sortno) + 1;
	var input = getElementByClassName(tr_after,'sortno');
	if (input.length > 0) {
		sortno_after = input[0].value;
	}

	//選択行の情報を格納
	$('#post_no').val(no);
	$('#post_sortno').val(sortno);
	$('#post_sortno_after').val(sortno_after);

	$("#post_type").val('MOVE');

	//2つ下の上に移動
	list_moveRow(tr, tr.rowIndex, tr.rowIndex+2);

	//$('#post_form').submit();
}

//対象年月選択
function selectDate() {

	$('#post_type').val('SELECT_DATE');
	$('#post_date').val($('#cboDate').val());

	$('#post_form').submit();
}

//日選択
function selectday() {

	$('#post_type').val('SELECT_DAY');
	$('#post_day').val($('#cboDay').val());

	$('#post_form').submit();
}

//ユーザー選択
function selectUser() {

	$('#post_type').val('SELECT_USER');
	$('#post_uid').val($('#cboUser').val());

	$('#post_form').submit();
}

//状況選択
function selectStatus(obj) {

	var edit_type = 'EDIT_STATUS';
	var rowIndex = obj.parentNode.parentNode.rowIndex;
	if (rowIndex < 0) {
		return false;
	}

	var rows = document.getElementById('list').rows;
	var row = rows[rowIndex];
	var input =null;

	$("#post_type").val(edit_type);

	input = getElementByClassName(row,"no")[0];
	$("#post_no").val(input.value);

	$("#post_status_no").val(obj.value);

	// フォーム内の情報を配列にまとめる
	var form = $("#post_form");
	var param = {};
	$(form.serializeArray()).each(function(i, v) {
		param[v.name] = v.value;
	});

	//レスポンス待ちの表示
	var tr = obj.parentNode.parentNode;
	var busy = getElementByClassName(tr, "busy")[0];
	busy.setAttribute('style','display:inline;');
	obj.disabled = true;

	//非同期でPOST
	$.post("todo_list_control.php", param, function(response) {

		//処理結果判断
		if (response[0] != 0) {
			alert_dialog(response[0]);
		} else {
			//再読込
			var cbo_status = getElementByClassName(tr,"cbo_status")[0];
			var status_no = cbo_status.options[cbo_status.selectedIndex].value;
			show_icon(tr, status_no);
		}

	},"json")
	.complete(function() {

		//レスポンス待ちの解除
		busy.setAttribute('style','display:none;');
		obj.disabled = false;

	});
}

//状況の表示
function show_icon(tr, status_no) {

	var icon = '';
	switch (parseInt(status_no)) {
		case 0:
			icon = 'pict03_09.png';
			break;
		case 1:
			icon = 'pict04_10.png';
			break;
		case 2:
			icon = 'pict06_15.png';
			break;
		case 3:
			icon = 'pict12_09.png';
			break;
		case 4:
			icon = 'pict01_01.png';
			break;
		default:
			break;
	}

	var img_icon = getElementByClassName(tr,"img_icon")[0];
	if (icon.length) {
		img_icon.src = "images/" + icon;
		img_icon.style.display = "block";
	} else {
		img_icon.src = '';
		img_icon.style.display = "none";
	}

}

//行データ表示
function show_data(tr, data) {

	var ctrl = null;

	show_icon(tr, data ? data['status_no'] : -1);

	ctrl = getElementByClassName(tr,"no")[0];
	ctrl.value = data ? data['no'] : '';

	ctrl = getElementByClassName(tr,"sortno")[0];
	ctrl.value = data ? data['sortno'] : '';

	ctrl = getElementByClassName(tr,"section_no")[0];
	ctrl.value = data ? data['section_no'] : '';

	ctrl = getElementByClassName(tr,"project_no")[0];
	ctrl.value = data ? data['project_no'] : '';

	ctrl = getElementByClassName(tr,"work_no")[0];
	ctrl.value = data ? data['work_no'] : '';

	ctrl = getElementByClassName(tr,"customer_no")[0];
	ctrl.value = data ? data['customer_no'] : '';

	ctrl = getElementByClassName(tr,"process_no")[0];
	ctrl.value = data ? data['process_no'] : '';

	ctrl = getElementByClassName(tr,"cbo_action_start")[0];
	ctrl.value = data ? data['cbo_action_start'] : '';

	ctrl = getElementByClassName(tr,"cbo_action_end")[0];
	ctrl.value = data ? data['cbo_action_end'] : '';

	ctrl = getElementByClassName(tr,"project_name")[0];
	ctrl.value = data ? data['project_name'] : '';

	ctrl = getElementByClassName(tr,"work_name")[0];
	ctrl.value = data ?  data['work_name'] : '';

	ctrl = getElementByClassName(tr,"customer_name")[0];
	ctrl.value = data ?  data['customer_name'] : '';

	ctrl = getElementByClassName(tr,"process_name")[0];
	ctrl.value = data ? data['process_name'] : '';

	ctrl = getElementByClassName(tr,"lbl_project")[0];
	if (ctrl != undefined) ctrl.innerHTML = data ? data['project_name'] : '';

	ctrl = getElementByClassName(tr,"lbl_work")[0];
	if (ctrl != undefined) ctrl.innerHTML = data ? data['work_name'] : '';

	ctrl = getElementByClassName(tr,"lbl_customer")[0];
	if (ctrl != undefined) ctrl.innerHTML = data ? data['customer_name'] : '';

	ctrl = getElementByClassName(tr,"lbl_process")[0];
	if (ctrl != undefined) ctrl.innerHTML = data ? data['process_name'] : '';

	ctrl = getElementByClassName(tr,"lbl_action")[0];
	if (ctrl != undefined) ctrl.innerHTML = data ? data['action'] : '';

	ctrl = getElementByClassName(tr,"td_date_top")[0];
	ctrl.innerHTML = data ? data['date_top'] : '';

	ctrl = getElementByClassName(tr,"td_date_end")[0];
	ctrl.innerHTML = data ? data['date_end'] : '';

	ctrl = getElementByClassName(tr,"td_costin")[0];
	ctrl.innerHTML = data ? data['costin'] : '';

	ctrl = getElementByClassName(tr,"td_costout")[0];
	ctrl.innerHTML = data ? data['costin'] : '';

	ctrl = getElementByClassName(tr,"cbo_status")[0];
	select_combobox_value(ctrl,data ? data['status_no'] : -1);
	ctrl.disabled = false;
	if (data) {
		ctrl.setAttribute('style','display:inline;');
	} else {
		ctrl.setAttribute('style','display:none;');
	}

	ctrl = getElementByClassName(tr,"bundle_string")[0];
	ctrl.value = data ? get_bundle_string(tr) : '';

	ctrl = getElementByClassName(tr, "busy")[0];
	ctrl.setAttribute('style','display:none;');

	ctrl = getElementByClassName(tr, "btn_up")[0];
	ctrl.disabled = false;
	if (data) {
		ctrl.setAttribute('style','display:inline;');
	} else {
		ctrl.setAttribute('style','display:none;');
	}

	ctrl = getElementByClassName(tr, "btn_dw")[0];
	ctrl.disabled = false;
	if (data) {
		ctrl.setAttribute('style','display:inline;');
	} else {
		ctrl.setAttribute('style','display:none;');
	}

	ctrl = getElementByClassName(tr, "btn_edit")[0];
	ctrl.disabled = false;
	if (data) {
		ctrl.setAttribute('style','display:inline;');
	} else {
		ctrl.setAttribute('style','display:none;');
	}

	ctrl = getElementByClassName(tr, "btn_add")[0];
	ctrl.disabled = false;
	ctrl.setAttribute('style','display:inline;');

	ctrl = getElementByClassName(tr, "btn_del")[0];
	ctrl.disabled = false;
	if (data) {
		ctrl.setAttribute('style','display:inline;');
	} else {
		ctrl.setAttribute('style','display:none;');
	}
}

//タイトル行の文字列を生成
function get_bundle_string(tr) {

	var str = '';

	var project_name = getElementByClassName(tr,"project_name")[0];
	var work_name = getElementByClassName(tr,"work_name")[0];
	var customer_name = getElementByClassName(tr,"customer_name")[0];
	var process_name = getElementByClassName(tr,"process_name")[0];
	var lbl_action = getElementByClassName(tr,"lbl_action")[0];
	var cbo_status = getElementByClassName(tr,"cbo_status")[0];
	var td_date_top = getElementByClassName(tr,"td_date_top")[0];
	var td_date_end = getElementByClassName(tr,"td_date_end")[0];
	var td_costin = getElementByClassName(tr,"td_costin")[0];
	var td_costout = getElementByClassName(tr,"td_costout")[0];

	var todobundle_list = str_getcsv(todobundle);
	for (var i = 0; i < todobundle_list.length; i++) {

		switch (todobundle_list[i]) {
		case 'project_sortno':
			str += project_name.value;
			break;
		case 'work_sortno':
			str += work_name.value;
			break;
		case 'status_sortno':
			str += cbo_status.options[cbo_status.selectedIndex].value;
			break;
		case 'date_top':
			str += td_date_top.innerHTML;
			break;
		case 'date_end':
			str += td_date_end.innerHTML;
			break;
		case 'customer_sortno':
			str += customer_name.value;
			break;
		case 'action':
			str += lbl_action.innerHTML;
			break;
		case 'process_sortno':
			str += process_name.value;
			break;
		case 'costin':
			str += td_costin.innerHTML;
			break;
		case 'costout':
			str += td_costout.innerHTML;
			break;
		}

		str += ' ';
	}

	return str;
}

//行追加
function list_insertRow(tr, data) {

	//全ての行
	var rows = document.getElementById('list').rows;

	//1つ下の行を取得
	var rowIndex = insertBeforeRow(tr, 'list', 'list_tbody', 'tr_item');

	//初期値設定
	var row = rows[rowIndex];

	//行データ表示
	show_data(row, data);
}

//行移動
function list_moveRow(tr, beforeRowIndex, afterRowIndex) {

	// フォーム内の情報を配列にまとめる
	var form = $("#post_form");
	var param = {};
	$(form.serializeArray()).each(function(i, v) {
		param[v.name] = v.value;
	});

	//レスポンス待ちの表示
	var busy = getElementByClassName(tr, "busy")[0];
	busy.setAttribute('style','display:inline;');

	//非同期でPOST
	$.post("todo_list_control.php", param, function(response) {

		//処理結果判断
		if (response[0] != 0) {
			alert_dialog(response[0]);
		} else {
			//行の表示移動

			//全ての行
			var rows = document.getElementById('list').rows;
			if (afterRowIndex <= rows.length) {

				if (beforeRowIndex > afterRowIndex) {
					//↑

					//上がtr_projectでなければ単純に移動
					if (rows[beforeRowIndex-1].className != 'tr_project') {
						moveRow(beforeRowIndex, afterRowIndex, 'list', 'list_tbody', 'tr_item', callBack_moveRow_copyData);
					} else {
						//上がtr_projectのときは、その上へ移動
						afterRowIndex--;
						moveRow(beforeRowIndex, afterRowIndex, 'list', 'list_tbody', 'tr_item', callBack_moveRow_copyData);
					}
				} else {
					//↓

					//下がtr_projectでなければ単純に移動
					if (rows[beforeRowIndex+1].className != 'tr_project') {
						moveRow(beforeRowIndex, afterRowIndex, 'list', 'list_tbody', 'tr_item', callBack_moveRow_copyData);
					} else {
						//下がtr_projectのときは、その下へ移動
						afterRowIndex++;
						moveRow(beforeRowIndex, afterRowIndex, 'list', 'list_tbody', 'tr_item', callBack_moveRow_copyData);
					}
				}

				//tr_project行の表示を更新
				reset_tr_project();

				//編集画面のＴＯＤＯ選択項目を更新
				refreshTodoList();
			}
		}

	},"json")
	.complete(function() {

		//レスポンス待ちの解除
		busy.setAttribute('style','display:none;');

	});
}

//tr_project行の表示を更新
function reset_tr_project() {

	var exists_todo = false; //データ有無

	//tr_projectを全て削除
	var rows = document.getElementById('list').rows;
	for (var i = rows.length - 1; i > 0; i--) {
		if (rows[i].className == 'tr_project') {
			removeRow(rows[i], 'list_tbody');
		} else if (!exists_todo && rows[i].className == 'tr_item') {
			if (getElementByClassName(rows[i], 'no')[0].value > 0) {
				exists_todo = true; //データ有
			}
		}
	}

	//tr_projectの追加
	rows = document.getElementById('list').rows;
	var tbody = document.getElementById('list_tbody');
	var bundle_string = '';
	var bundle_string_bk = '';
	for (var i = rows.length - 1; i >= 0; i--) {
		if (i > 0) {
			bundle_string = getElementByClassName(rows[i], 'bundle_string')[0].value;
			if (bundle_string_bk == '') {
				bundle_string_bk = bundle_string;
			}
		} else {
			bundle_string = '';
		}
		if (bundle_string != bundle_string_bk) {

			var tr_project = tbody.insertRow(i);
			tr_project.className = 'tr_project';
			for (var j = 0; j < rows[i+2].cells.length; j++) {
				//列追加
				var col = tr_project.insertCell(-1);
				//値設定
				col.className = rows[i+2].cells[j].className;
				if (col.className == 'td_todo') {
					col.innerHTML = bundle_string_bk;
				}
			}

			bundle_string_bk = bundle_string;
		}
		if (exists_todo) { //データ有ならば、空欄行を削除
			var elm_no = getElementByClassName(rows[i], 'no');
			if (elm_no.length > 0 && elm_no[0].value == '') {
				removeRow(rows[i], 'list_tbody');
			}
		}
	}
}

//行移動時のデータコピー
function callBack_moveRow_copyData(beforeRow, afterRow) {

	var before_input = getElementByClassName(beforeRow, "img_icon")[0];
	var after_input = getElementByClassName(afterRow, "img_icon")[0];
	after_input.src = before_input.src;

	before_input = getElementByClassName(beforeRow, "bundle_string")[0];
	after_input = getElementByClassName(afterRow, "bundle_string")[0];
	after_input.value = before_input.value;

	before_input = getElementByClassName(beforeRow, "no")[0];
	after_input = getElementByClassName(afterRow, "no")[0];
	after_input.value = before_input.value;

	before_input = getElementByClassName(beforeRow, "sortno")[0];
	after_input = getElementByClassName(afterRow, "sortno")[0];
	after_input.value = before_input.value;

	before_input = getElementByClassName(beforeRow, "section_no")[0];
	after_input = getElementByClassName(afterRow, "section_no")[0];
	after_input.value = before_input.value;

	before_input = getElementByClassName(beforeRow, "project_no")[0];
	after_input = getElementByClassName(afterRow, "project_no")[0];
	after_input.value = before_input.value;

	before_input = getElementByClassName(beforeRow, "work_no")[0];
	after_input = getElementByClassName(afterRow, "work_no")[0];
	after_input.value = before_input.value;

	before_input = getElementByClassName(beforeRow, "customer_no")[0];
	after_input = getElementByClassName(afterRow, "customer_no")[0];
	after_input.value = before_input.value;

	before_input = getElementByClassName(beforeRow, "process_no")[0];
	after_input = getElementByClassName(afterRow, "process_no")[0];
	after_input.value = before_input.value;

	before_input = getElementByClassName(beforeRow, "cbo_action_start")[0];
	after_input = getElementByClassName(afterRow, "cbo_action_start")[0];
	after_input.value = before_input.value;

	before_input = getElementByClassName(beforeRow, "cbo_action_end")[0];
	after_input = getElementByClassName(afterRow, "cbo_action_end")[0];
	after_input.value = before_input.value;

	before_input = getElementByClassName(beforeRow, "lbl_project")[0];
	after_input = getElementByClassName(afterRow, "lbl_project")[0];
	if (before_input != undefined && after_input != undefined) {
		after_input.innerHTML = before_input.innerHTML;
	}

	before_input = getElementByClassName(beforeRow, "lbl_work")[0];
	after_input = getElementByClassName(afterRow, "lbl_work")[0];
	if (before_input != undefined && after_input != undefined) {
		after_input.innerHTML = before_input.innerHTML;
	}

	before_input = getElementByClassName(beforeRow, "lbl_customer")[0];
	after_input = getElementByClassName(afterRow, "lbl_customer")[0];
	if (before_input != undefined && after_input != undefined) {
		after_input.innerHTML = before_input.innerHTML;
	}

	before_input = getElementByClassName(beforeRow, "lbl_process")[0];
	after_input = getElementByClassName(afterRow, "lbl_process")[0];
	if (before_input != undefined && after_input != undefined) {
		after_input.innerHTML = before_input.innerHTML;
	}

	before_input = getElementByClassName(beforeRow, "lbl_action")[0];
	after_input = getElementByClassName(afterRow, "lbl_action")[0];
	if (before_input != undefined && after_input != undefined) {
		after_input.innerHTML = before_input.innerHTML;
	}

	before_input = getElementByClassName(beforeRow, "td_date_top")[0];
	after_input = getElementByClassName(afterRow, "td_date_top")[0];
	after_input.innerHTML = before_input.innerHTML;

	before_input = getElementByClassName(beforeRow, "td_date_end")[0];
	after_input = getElementByClassName(afterRow, "td_date_end")[0];
	after_input.innerHTML = before_input.innerHTML;

	before_input = getElementByClassName(beforeRow, "td_costin")[0];
	after_input = getElementByClassName(afterRow, "td_costin")[0];
	after_input.innerHTML = before_input.innerHTML;

	before_input = getElementByClassName(beforeRow, "td_costout")[0];
	after_input = getElementByClassName(afterRow, "td_costout")[0];
	after_input.innerHTML = before_input.innerHTML;

	before_input = getElementByClassName(beforeRow, "cbo_status")[0];
	after_input = getElementByClassName(afterRow, "cbo_status")[0];
	select_combobox_value(after_input, before_input.value);

	var busy = getElementByClassName(afterRow, "busy")[0];
	busy.setAttribute('style','display:none;');
}

//期間選択
function selectPeriod() {

	$('#post_type').val('SELECT_PERIOD');
	$('#post_period').val($('#cboPeriod').val());

	$('#post_form').submit();
}

//[終了][中止]でない項目はすべて表示
function checkPast() {

	$('#post_type').val('CHECK_PAST');

	var checked = 0;
	if ($('#chkPast').attr('checked')) {
		checked = 1;
	}
	$('#post_past').val(checked);

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

//並べ替え
function sortDialog() {

	//入力画面表示
	$("#post_type").val('SORT');
	$('#sort_dialog .validateTips').html('ソート順を設定して、並び替えを実行してください。');
	$('#sort_dialog .validateTips').css('color','blue');
	$('#ui-dialog-title-sort_dialog').html('TODO並び替え');

	$('#sort_dialog').dialog('open');
}
$(function() {

	$("#sort_dialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 450,
		width: 400,
		modal: true,
		closeOnEscape: true,
		buttons: {
		"OK": function() {

				//既にPOSTデータがあれば削除
				var form = document.getElementById('post_form');
				var obj = getElementByName(form,"post_sort_list[]");
				for (var i=obj.length-1; i>=0; i--) {
					form.removeChild(obj[i]);
				}
				obj = getElementByName(form,"post_bundle_list[]");
				for (var i=obj.length-1; i>=0; i--) {
					form.removeChild(obj[i]);
				}

				//並び順を取得
				var rows = document.getElementById('sort_list').rows;

				//POSTデータ追加
				for (i=1; i<rows.length; i++) {
					//post_sort_list[]
					obj = document.createElement('input');
					obj.setAttribute('type','hidden');
					obj.name = 'post_sort_list[]';
					obj.value = getElementByClassName(rows[i], "id")[0].value;
					form.appendChild(obj);
					//post_bundle_list[]
					if (getElementByClassName(rows[i], "chk_bundle")[0].checked) {
						obj = document.createElement('input');
						obj.setAttribute('type','hidden');
						obj.name = 'post_bundle_list[]';
						obj.value = getElementByClassName(rows[i], "id")[0].value;
						form.appendChild(obj);
					}
				}

				$("#sort_dialog").dialog('close');
				$('#post_form').submit();
				return;
			},
		"キャンセル": function() {
				$(this).dialog('close');
			}
		},
		close: function() {
		}
	});

});

//↑
function sort_list_upRow(obj) {

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;

	//1つ上の上に移動
	sort_list_moveRow(tr.rowIndex, tr.rowIndex-1);
}

//↓
function sort_list_dwRow(obj) {

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;

	//2つ下の上に移動
	sort_list_moveRow(tr.rowIndex, tr.rowIndex+2);
}

//行移動
function sort_list_moveRow(beforeRowIndex, afterRowIndex) {

	var rowIndex = -1;

	//全ての行
	var rows = document.getElementById('sort_list').rows;

	if (afterRowIndex < 1) {
		return rowIndex;
	}
	if (afterRowIndex > rows.length) {
		return rowIndex;
	}

	//現在の行
	var tr_before = rows[beforeRowIndex];
	var before_obj = getElementByClassName(tr_before, "id")[0];

	//移動先の行
	var after_obj = null;
	if (afterRowIndex < rows.length) {
		var tr_after = rows[afterRowIndex];
		after_obj = getElementByClassName(tr_after, "id")[0];
	}

	//行追加
	var rowIndex = sort_list_insertRow(after_obj);
	rows = document.getElementById('sort_list').rows;
	var row = rows[rowIndex];

	//コピー
	var before_input = getElementByClassName(tr_before, "id")[0];
	var after_input = getElementByClassName(row, "id")[0];
	after_input.value = before_input.value;

	before_input = getElementByClassName(tr_before, "name")[0];
	after_input = getElementByClassName(row, "name")[0];
	after_input.innerHTML = before_input.innerHTML;

	before_input = getElementByClassName(tr_before, "chk_bundle")[0];
	after_input = getElementByClassName(row, "chk_bundle")[0];
	after_input.checked = before_input.checked;

	//移動前の行を削除
	sort_list_removeRow(before_obj);

	return rowIndex;
}

//行追加
function sort_list_insertRow(obj) {

	var rowIndex = -1;

	//全ての行
	var rows = document.getElementById('sort_list').rows;

	//tbody要素を取得
	var tbody = document.getElementById("sort_list_tbody");

	//tbodyタグ直下のノード（tr）を複製
	var row = get_cloneElement(tbody.getElementsByTagName("tr")[0]);

	if (obj !== null) {
		//objの親の親のノード（tr）を取得
		var tr = obj.parentNode.parentNode;

		//1つ上の行を取得
		var afterRow = getAfterRow(rows, tr);

		//複製したtrを1つ上の行に挿入
		if (afterRow != tr) {
			tbody.insertBefore(row , afterRow.nextSibling);
			rowIndex = afterRow.rowIndex + 1;
		} else {
			tbody.insertBefore(row , tbody.firstChild);
			rowIndex = 1;
		}
	} else {
		//末尾に追加
		tbody.insertBefore(row);
		rowIndex = rows.length - 1;
	}

	return rowIndex;
}

//行削除
function sort_list_removeRow(obj) {

	//tbody要素を取得
	var tbody = document.getElementById("sort_list_tbody");

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;

	//行削除
	tbody.removeChild(tr);
}
