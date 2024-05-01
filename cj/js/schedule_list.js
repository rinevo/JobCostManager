var select_date_id = '';

$(document).ready(function() {
    //閲覧モード表示、小計
    var cboDay = document.getElementById('cboDay');
    for (var i = 1; i < cboDay.options.length; i++) {
    	var date = cboDay.options[i].value;
    	showBrowseMode(date);	//閲覧モード
    	subtotal(date);			//小計
    }

    //集計表示
    showTotal('schedule_list_control.php');

    //行操作イベントを設定
    entryRowEvents();
});

//行操作イベントを設定
function entryRowEvents() {

    //行クリック
    $("#schedule tbody tr").live("click", function() {
    	if (this.rowIndex < 1) {
    		return;
    	}

    	//編集ボタンにフォーカスをあわせる
    	setFocus_topRowObj(this, "btn_edit_start");

    	//対象日の背景色をハイライト
    	var rows = document.getElementById('schedule').rows;
		var date = getDate(rows, this);
    	if (date.length < 1) return;
    	var date_id = 'tr_' + date;
    	setDayFocus(rows, date_id);
    });

    //編集ボタンの入力操作
    $("#schedule .btn_edit_start").live("keydown", function(e) {

    	var rows = document.getElementById('schedule').rows;

    	var tr = this.parentNode.parentNode;
    	var date = getDate(rows, tr);
    	var date_id = 'tr_' + date;

    	var row = getTargetDayRowArea(rows, date_id);
    	var top_row = row['top_row'];
    	var end_row = row['end_row'];

    	var row_before = top_row - 1;
    	var row_after = end_row + 1;

    	switch (e.keyCode) {
    	case 40:	//↓
    		var visible = setFocus_topRowObj(rows[row_after], "btn_edit_start");
			if (!visible) {
				setFocus_topRowObj(rows[row_after], "text_time");
			}
        	date = getDate(rows, rows[row_after]);
        	date_id = 'tr_' + date;
    		setDayFocus(rows, date_id);
    		e.preventDefault();
    		break;
    	case 38:	//↑
    		var visible = setFocus_topRowObj(rows[row_before], "btn_edit_start");
			if (!visible) {
				setFocus_topRowObj(rows[row_before], "text_time");
			}
        	date = getDate(rows, rows[row_before]);
        	date_id = 'tr_' + date;
    		setDayFocus(rows, date_id);
    		e.preventDefault();
    		break;
    	}

    });

    //時刻の入力操作
    $("#schedule .text_time").live("keydown", function(e) {

    	var rows = document.getElementById('schedule').rows;
    	var tr = this.parentNode.parentNode;

    	switch (e.keyCode) {
    	case 13:	//Enter
    		save(tr);
    		break;
    	case 27:	//ESC
    		reload(tr);
    		break;
    	case 40:	//↓
    		var tr_focus = getBeforeRow(rows, tr);
    		setFocus_rowObj(tr_focus, "text_time");
    		e.preventDefault();
    		break;
    	case 38:	//↑
    		var tr_focus = getAfterRow(rows, tr);
    		setFocus_rowObj(tr_focus, "text_time");
    		e.preventDefault();
    		break;
    	case 39:	//→
    		if ("none" != getElementByClassName(tr, "cbo_action_start")[0].style.display) {
    			setFocus_rowObj(tr, "cbo_action_start");
    		} else if ("none" != getElementByClassName(tr, "cbo_action_end")[0].style.display) {
    			setFocus_rowObj(tr, "cbo_action_end");
    		} else {
    			setFocus_rowObj(tr, "text_costin");
    			e.preventDefault();
    		}
    		break;
    	}

    });

    //行動（出勤）の入力操作
    $("#schedule .cbo_action_start").live("keydown", function(e) {

    	var tr = this.parentNode.parentNode;

    	switch (e.keyCode) {
    	case 13:	//Enter
    		save(tr);
    		break;
    	case 27:	//ESC
    		reload(tr);
    		break;
    	case 39:	//→
    		if ("none" != getElementByClassName(tr, "text_costin")[0].style.display) {
    			setFocus_rowObj(tr, "text_costin");
    			e.preventDefault();
    		}
    		e.preventDefault();
    		break;
    	case 37:	//←
    		setFocus_topRowObj(this, "text_time");
    		e.preventDefault();
    		break;
    	}

    });

    //行動（退勤）の入力操作
    $("#schedule .cbo_action_end").live("keydown", function(e) {

    	var tr = this.parentNode.parentNode;

    	switch (e.keyCode) {
    	case 13:	//Enter
    		save(tr);
    		break;
    	case 27:	//ESC
    		reload(tr);
    		break;
    	case 39:	//→
    		if ("none" != getElementByClassName(tr, "text_costin")[0].style.display) {
    			setFocus_rowObj(tr, "text_costin");
    			e.preventDefault();
    		}
    		e.preventDefault();
    		break;
    	case 37:	//←
    		setFocus_rowObj(this, "text_time");
    		e.preventDefault();
    		break;
    	}

    });

    //時間内の入力操作
    $("#schedule .text_costin").live("keydown", function(e) {

    	var rows = document.getElementById('schedule').rows;
    	var tr = this.parentNode.parentNode;

    	switch (e.keyCode) {
    	case 13:	//Enter
    		save(tr);
    		break;
    	case 27:	//ESC
    		reload(tr);
    		break;
    	case 40:	//↓
    		var tr_focus = getBeforeRow(rows, tr);
    		setFocus_rowObj(tr_focus, "text_costin");
    		e.preventDefault();
    		break;
    	case 38:	//↑
    		var tr_focus = getAfterRow(rows, tr);
    		setFocus_rowObj(tr_focus, "text_costin");
    		e.preventDefault();
    		break;
    	case 39:	//→
    		setFocus_rowObj(this, "text_costout");
    		e.preventDefault();
    		break;
    	case 37:	//←
    		if ("none" != getElementByClassName(tr, "cbo_action_start")[0].style.display) {
    			setFocus_rowObj(tr, "cbo_action_start");
    		} else if ("none" != getElementByClassName(tr, "cbo_action_end")[0].style.display) {
    			setFocus_rowObj(tr, "cbo_action_end");
    		} else {
    			setFocus_rowObj(tr, "text_time");
    			e.preventDefault();
    		}
    		break;
    	}

    });

    //時間外の入力操作
    $("#schedule .text_costout").live("keydown", function(e) {

    	var rows = document.getElementById('schedule').rows;
    	var tr = this.parentNode.parentNode;

    	switch (e.keyCode) {
    	case 13:	//Enter
    		save(tr);
    		break;
    	case 27:	//ESC
    		reload(tr);
    		break;
    	case 40:	//↓
    		var tr_focus = getBeforeRow(rows, tr);
    		setFocus_rowObj(tr_focus, "text_costout");
    		e.preventDefault();
    		break;
    	case 38:	//↑
    		var tr_focus = getAfterRow(rows, tr);
    		setFocus_rowObj(tr_focus, "text_costout");
    		e.preventDefault();
    		break;
    	case 37:	//←
    		setFocus_rowObj(this, "text_costin");
    		e.preventDefault();
    		break;
    	}

    });
}

//対象行の日付を取得
function getDate(rows, tr) {

	var topRow = getTargetDayRow(rows, tr);
	var date = getElementByClassName(topRow, "date");
	if (date.length < 1) {
		return '';
	}

	return date[0].value;
}

//対象日の背景色をハイライト
function setDayFocus(rows, date_id) {
	if (select_date_id != date_id) {
		if (select_date_id.length) {
			setDayBackgroundColor(rows, select_date_id, "white");
		}
		setDayBackgroundColor(rows, date_id, "#ffffaa");
		select_date_id = date_id;
	}
}

//対象日の背景色を変更（tr_日付のidで指定）
function setDayBackgroundColor(rows, date_id, color) {
	var row = getTargetDayRowArea(rows, date_id);
	var top_row = row['top_row'];
	var end_row = row['end_row'];

    for (var i = top_row; i < end_row; i++) {
    	var tr = rows[i];
    	tr.style.backgroundColor = color;
	}
}

//先頭行のコントロールににフォーカスをあわせる
function setFocus_topRowObj(obj, className) {

	var ret = false;

	var tr = null;
	if (obj.tagName.toUpperCase() == "TR") {
		tr = obj;
	} else {
		tr = obj.parentNode.parentNode;
	}
	var rows = document.getElementById('schedule').rows;
	var topRow = getTargetDayRow(rows, tr);
	var set_obj = getElementByClassName(topRow, className);

	if (set_obj.length) {
		var style = set_obj[0].style.display;
		if (style != "none") {
			if (set_obj[0] != get_focusElement()) {
				set_obj[0].focus();
			}
			if (set_obj[0].tagName.toUpperCase() == "INPUT" && set_obj[0].type.toUpperCase() == "TEXT" && set_obj[0].value.length > 0) {
				set_text_selection(set_obj[0], 0, set_obj[0].value.length);
			}
			ret = true;
		}
	}

	return ret;
}

//対象行のコントロールにフォーカスをあてる
function setFocus_rowObj(obj, className) {

	var tr = null;
	if (obj.tagName.toUpperCase() == "TR") {
		tr = obj;
	} else {
		tr = obj.parentNode.parentNode;
	}
	var set_obj = getElementByClassName(tr, className);

	if (set_obj.length) {
    	var style = set_obj[0].style.display;
    	if (style != "none") {
			if (set_obj[0] != get_focusElement()) {
	    		set_obj[0].focus();
			}
			if (set_obj[0].tagName.toUpperCase() == "INPUT" && set_obj[0].type.toUpperCase() == "TEXT" && set_obj[0].value.length > 0) {
				set_text_selection(set_obj[0], 0, set_obj[0].value.length);
			}
    	}
	}

}

//文字入力領域の選択
function set_text_selection(obj, start_pos, end_pos)
{
	//Mozilla and DOM 3.0
	if(obj.selectionStart)
	{
		if (obj != get_focusElement()) {
			obj.focus();
		}
		obj.selectionStart = start_pos;
		obj.selectionEnd = end_pos;
	}
	//IE
	else if(obj.createTextRange)
	{
		if (obj != get_focusElement()) {
			obj.focus();
		}
		var tr = obj.createTextRange();

		//Fix IE from counting the newline characters as two seperate characters
		var stop_it = start_pos;
		for (var i=0; i < stop_it; i++) if( obj.value[i].search(/[\r\n]/) != -1 ) start_pos = start_pos - .5;
		stop_it = end_pos;
		for (var i=0; i < stop_it; i++) if( obj.value[i].search(/[\r\n]/) != -1 ) end_pos = end_pos - .5;

		tr.moveEnd('textedit',-1);
		tr.moveStart('character',start_pos);
		tr.moveEnd('character',end_pos - start_pos);
		tr.select();
	}
}

//対象日付を表示
function selectday() {
    var ctrlCbo = document.getElementById('cboDay');
    if (ctrlCbo != null) {
        var name = 'tr_' + ctrlCbo.value;
        var ctrlRow = document.getElementById(name);
        if (ctrlRow != null) {
            var ctrlTop = document.getElementById('scrollPosition');
            if (ctrlTop != null) {
                ctrlTop.value = ctrlRow.offsetTop - ctrlRow.parentElement.offsetTop;
                setscrolltop('schedule','scrollPosition');
            }
        	//編集ボタンにフォーカスをあわせる
        	setFocus_topRowObj(ctrlRow, "btn_edit_start");

        	//対象日の背景色をハイライト
        	var rows = document.getElementById('schedule').rows;
        	setDayFocus(rows, name);
        }
    }
}

//対象日の行範囲を取得(小計行を含む)
function getTargetDayRowArea(rows, id) {

    var top_row = 0;
    var end_row = 0;

    for (var i = 0; i < rows.length; i++) {
        if (top_row > 0 && rows[i].id.length > 0) {
            end_row = i - 1;
            break;
        }
    	if (id == rows[i].id) {
            top_row = i;
            end_row = rows.length - 1;
        }
    }

    return { top_row: top_row, end_row: end_row };
}

//小計
function subtotal(date) {

	var id = 'tr_' + date;
	var rows = document.getElementById('schedule').rows;

	var row = getTargetDayRowArea(rows, id);
	var top_row = row['top_row'];
	var end_row = row['end_row'];

	var total_in = parseFloat(0.0);
	var total_out = parseFloat(0.0);

    for (var i = top_row; i < end_row; i++) {
        var tr = rows[i];

        var section_no = getElementByClassName(tr,"section_no")[0].value;
        var text_costin = getElementByClassName(tr,"text_costin")[0];
        var text_costout = getElementByClassName(tr,"text_costout")[0];

        if (section_no == 2) {
	        try {
	            if (text_costin.value == parseFloat(text_costin.value)) {
		        	total_in = total_in + parseFloat(text_costin.value);
	            }
	            if (text_costout.value == parseFloat(text_costout.value)) {
		        	total_out = total_out + parseFloat(text_costout.value);
	            }
	        } catch (e) {
	        }
        }
    }

    tr = rows[end_row];
    var lbl_costin = getElementByClassName(tr,"lbl_costin")[0];
    var lbl_costout = getElementByClassName(tr,"lbl_costout")[0];
    lbl_costin.innerHTML = total_in;
    lbl_costout.innerHTML = total_out;
}

//編集モード（日単位）
function showEditMode(obj) {

	var row = null;
	if (obj.tagName.toUpperCase() == "TR") {
		row = obj;
	} else {
		row = obj.parentNode.parentNode;
	}

	//全ての行
	var rows = document.getElementById('schedule').rows;

	//先頭行
	var topRow = getTargetDayRow(rows, row);

	//tr_年月日
	var date = getElementByClassName(topRow,"date")[0].value;
	var id = 'tr_' + date;

	//対象日の範囲
	var row = getTargetDayRowArea(rows, id);
	var top_row = row['top_row'];
	var end_row = row['end_row'];

    for (var i = top_row; i < end_row; i++) {
        var tr = rows[i];

        //編集モード（行単位）
        showEditModeRow(tr);
	}
}

//編集モード（行単位）
function showEditModeRow(tr) {

    var section_no = 0;
    var tmp = getElementByClassName(tr,"section_no");
    if (tmp.length > 0) {
    	section_no = tmp[0].value;
    }

    var input = tr.getElementsByTagName("*");
	for (var j = 0; j < input.length; j++) {
		switch (input[j].className) {
		case 'btn_edit_start':
		case 'lbl_time':
		case 'lbl_costin':
		case 'lbl_costout':
			input[j].setAttribute("style","display:none;");
			break;
		case 'cbo_action_start':
    		if (section_no == 1) {
    			input[j].setAttribute("style","display:inline;");
    			selectKintai(input[j]);
			}
			break;
		case 'cbo_action_end':
    		if (section_no == 3) {
    			input[j].setAttribute("style","display:inline;");
    			selectKintai(input[j]);
			}
			break;
		case 'lbl_action':
    		if (section_no == 2) {
    			input[j].setAttribute("style","display:inline;");
    		} else {
    			input[j].setAttribute("style","display:none;");
    		}
			break;
		case 'text_costin':
		case 'text_costout':
    		if (section_no == 2) {
    			input[j].setAttribute("style","display:inline;");
    		}
			break;
		case 'sublabel':
			if (section_no == 2) {
				input[j].setAttribute("style","display:block;");
			} else {
				input[j].setAttribute("style","display:none;");
			}
			break;
    	case 'btn_edit_save':
		case 'btn_edit_cancel':
		case 'text_time':
		case 'btn_up':
		case 'btn_dw':
		case 'btn_add':
		case 'btn_edit':
		case 'btn_del':
			input[j].setAttribute("style","display:inline;");
			break;
		}
	}
}

//閲覧モード
function showBrowseMode(date) {

	var id = 'tr_' + date;
	var rows = document.getElementById('schedule').rows;

	var row = getTargetDayRowArea(rows, id);
	var top_row = row['top_row'];
	var end_row = row['end_row'];

    for (var i = top_row; i < end_row; i++) {
        var tr = rows[i];

        var section_no = 0;
        var tmp = getElementByClassName(tr,"section_no");
        if (tmp.length > 0) {
        	section_no = tmp[0].value;
        }

        var input = tr.getElementsByTagName("*");
    	for (var j = 0; j < input.length; j++) {
    		switch (input[j].className) {
    		case 'btn_edit_start':
    		case 'lbl_time':
    		case 'lbl_action':
    			input[j].setAttribute("style","display:inline;");
    			break;
    		case 'cbo_action_start':
       			input[j].setAttribute("style","display:none;");
       			if (section_no == 1) {
        			selectKintai(input[j]);
    			}
    			break;
    		case 'cbo_action_end':
       			input[j].setAttribute("style","display:none;");
       			if (section_no == 3) {
        			selectKintai(input[j]);
    			}
    			break;
    		case 'lbl_costin':
    		case 'lbl_costout':
    			if (section_no == 2) {
    				input[j].setAttribute("style","display:inline;");
    			}
    			break;
    		case 'sublabel':
    			if (section_no == 2) {
    				input[j].setAttribute("style","display:block;");
    			} else {
    				input[j].setAttribute("style","display:none;");
    			}
    			break;
    		case 'btn_edit_save':
    		case 'btn_edit_cancel':
    		case 'text_time':
    		case 'text_costin':
    		case 'text_costout':
    		case 'btn_up':
    		case 'btn_dw':
    		case 'btn_add':
    		case 'btn_edit':
    		case 'btn_del':
    			input[j].setAttribute("style","display:none;");
    			break;
    		}
    	}
	}
}

//勤怠選択時の処理
function selectKintai(obj) {
	var tr = obj.parentNode.parentNode;

	var style = obj.style.display;
	if (style != "none") {
		//勤怠
		var text_costin = getElementByClassName(tr, "text_costin")[0];
		var text_costout = getElementByClassName(tr, "text_costout")[0];

		switch (obj.value) {
		case "4":
			text_costin.setAttribute("style", "display:inline;");
			text_costout.setAttribute("style", "display:none;");
			break;
		default:
			text_costin.setAttribute("style", "display:none;");
			text_costout.setAttribute("style", "display:none;");
			break;
		}
	} else {
		//行動
		var lbl_costin = getElementByClassName(tr, "lbl_costin")[0];
		var lbl_costout = getElementByClassName(tr, "lbl_costout")[0];

		switch (obj.value) {
		case "4":
			lbl_costin.setAttribute("style", "display:inline;");
			lbl_costout.setAttribute("style", "display:none;");
			break;
		default:
			lbl_costin.setAttribute("style", "display:none;");
			lbl_costout.setAttribute("style", "display:none;");
			break;
		}
	}
}

//日の先頭行を取得
function getTargetDayRow(rows, tr) {
	var row = tr;
    for (var i = 0; i < rows.length; i++) {
        if (rows[i].id.length > 0) {
    		row = rows[i];
        }
    	if (tr == rows[i]) {
            break;
        }
    }
    return row;
}

//移動
function shedule_moveRow(beforeRowIndex, afterRowIndex) {

	var rowIndex = -1;

	//全ての行
	var rows = document.getElementById('schedule').rows;

	if (afterRowIndex < 1) {
		return rowIndex;
	}
	if (afterRowIndex > rows.length - 1) {
		return rowIndex;
	}

	//現在の行
	var tr_before = rows[beforeRowIndex];
	var before_lbl_costin = getElementByClassName(tr_before, "lbl_costin")[0];
	var before_topRow = getTargetDayRow(rows, tr_before);

	//移動先の行
	var tr_after = rows[afterRowIndex];
	var after_lbl_costin = getElementByClassName(tr_after, "lbl_costin")[0];
	var after_topRow = getTargetDayRow(rows, tr_after);

	//異なる日付へ移動する場合
	if (before_topRow.id != after_topRow.id) {
		var tmp = getTargetDayRowArea(rows, before_topRow.id);
		var top_row = tmp['top_row'];
		var end_row = tmp['end_row'];
		if (top_row == end_row-1) {
			alert_dialog('移動できません。<br>日ごとに最低１行は必要です。');
			return rowIndex;
		}
	}

	//行追加
	var rowIndex = insertRow(after_lbl_costin);
	rows = document.getElementById('schedule').rows;
	var row = rows[rowIndex];

	//コピー
	var before_input = getElementByClassName(tr_before, "date")[0];
	var after_input = getElementByClassName(row, "date")[0];
	if (before_input && after_input) {
		after_input.value = before_input.value;
	}

	before_input = getElementByClassName(tr_before, "text_time")[0];
	after_input = getElementByClassName(row, "text_time")[0];
	after_input.value = before_input.value;

	before_input = getElementByClassName(tr_before, "lbl_time")[0];
	after_input = getElementByClassName(row, "lbl_time")[0];
	after_input.innerHTML = before_input.innerHTML;

	before_input = getElementByClassName(tr_before, "no")[0];
	after_input = getElementByClassName(row, "no")[0];
	after_input.value = before_input.value;

	before_input = getElementByClassName(tr_before, "section_no")[0];
	after_input = getElementByClassName(row, "section_no")[0];
	after_input.value = before_input.value;

	before_input = getElementByClassName(tr_before, "project_no")[0];
	after_input = getElementByClassName(row, "project_no")[0];
	after_input.value = before_input.value;

	before_input = getElementByClassName(tr_before, "work_no")[0];
	after_input = getElementByClassName(row, "work_no")[0];
	after_input.value = before_input.value;

	before_input = getElementByClassName(tr_before, "process_no")[0];
	after_input = getElementByClassName(row, "process_no")[0];
	after_input.value = before_input.value;

	before_input = getElementByClassName(tr_before, "customer_no")[0];
	after_input = getElementByClassName(row, "customer_no")[0];
	after_input.value = before_input.value;

	before_input = getElementByClassName(tr_before, "cbo_action_start")[0];
	after_input = getElementByClassName(row, "cbo_action_start")[0];
	select_combobox_value(after_input, before_input.value);

	before_input = getElementByClassName(tr_before, "cbo_action_end")[0];
	after_input = getElementByClassName(row, "cbo_action_end")[0];
	select_combobox_value(after_input, before_input.value);

	before_input = getElementByClassName(tr_before, "lbl_action")[0];
	after_input = getElementByClassName(row, "lbl_action")[0];
	after_input.innerHTML = before_input.innerHTML;

	before_input = getElementByClassName(tr_before, "text_costin")[0];
	after_input = getElementByClassName(row, "text_costin")[0];
	after_input.value = before_input.value;

	before_input = getElementByClassName(tr_before, "lbl_costin")[0];
	after_input = getElementByClassName(row, "lbl_costin")[0];
	after_input.innerHTML = before_input.innerHTML;

	before_input = getElementByClassName(tr_before, "text_costout")[0];
	after_input = getElementByClassName(row, "text_costout")[0];
	after_input.value = before_input.value;

	before_input = getElementByClassName(tr_before, "lbl_costout")[0];
	after_input = getElementByClassName(row, "lbl_costout")[0];
	after_input.innerHTML = before_input.innerHTML;

	before_input = getElementByClassName(tr_before, "lbl_project")[0];
	after_input = getElementByClassName(row, "lbl_project")[0];
	after_input.innerHTML = before_input.innerHTML;

	before_input = getElementByClassName(tr_before, "lbl_work")[0];
	after_input = getElementByClassName(row, "lbl_work")[0];
	after_input.innerHTML = before_input.innerHTML;

	before_input = getElementByClassName(tr_before, "lbl_customer")[0];
	after_input = getElementByClassName(row, "lbl_customer")[0];
	after_input.innerHTML = before_input.innerHTML;

	before_input = getElementByClassName(tr_before, "lbl_process")[0];
	after_input = getElementByClassName(row, "lbl_process")[0];
	after_input.innerHTML = before_input.innerHTML;

	rowIndex = row.rowIndex;

	//移動先の日を編集モードにする
	showEditMode(row);

	//移動前の行を削除
	schedule_removeRow(before_lbl_costin);

	return rowIndex;
}

//↑
function upRow(obj) {

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;

	//1つ上の上に移動
	shedule_moveRow(tr.rowIndex, tr.rowIndex-1);
}

//↓
function dwRow(obj) {

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;

	//2つ下の上に移動
	shedule_moveRow(tr.rowIndex, tr.rowIndex+2);
}

//追加
function insertRow(obj) {

	var rowIndex = -1;

	//全ての行
	var rows = document.getElementById('schedule').rows;

	//tbody要素を取得
	var tbody = document.getElementById("schedule-tbody");

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;

	//先頭行か、それ以外で、セル数が異なるので、コピー元の行を切替
	var copy_no = 1;
	if (tr.className != 'subtotal') {
		var td = getElementByClassName(tr, "td_date");
		if (td.length > 0) {
			copy_no = 0;	//先頭行
		}
	}

	//tbodyタグ直下のノード（tr）を複製
	var row = get_cloneElement(tbody.getElementsByTagName("tr")[0]);

	//日の先頭行を取得
	var topRow = getTargetDayRow(rows, tr);
	var td_date = getElementByClassName(topRow, "td_date")[0];
	var rowSpan = td_date.getAttribute("rowSpan");
	var date = getElementByClassName(topRow,"date")[0];
	var input = null;
	td = row.getElementsByTagName("td")[0];	//td_date

	//先頭行に追加する場合の処理
	if (copy_no == 0) {
		input = getElementByClassName(td,"lbl_date")[0];	//td_dateの内容をコピー
		input.innerHTML = getElementByClassName(td_date,"lbl_date")[0].innerHTML;
		row.setAttribute("id",topRow.id);		//追加行のidを設定
		td.setAttribute("rowspan","1");			//追加行のrowSpan=1
		topRow.removeChild(td_date);			//元先頭行の先頭列を削除
		topRow.setAttribute("id","");			//元先頭行のid削除
		input = getElementByClassName(row,"date")[0];
		input.value = date.value;
	} else {
		row.removeChild(td);					//元先頭行の先頭列を削除
		td.setAttribute("rowspan","");			//追加行のrowSpan削除
		row.setAttribute("id","");				//追加行のid削除
	}

	//追加行の初期化
	input = getElementByClassName(row,"section_no")[0];
	input.value = 2;
	input = getElementByClassName(row,"text_time")[0];
	input.setAttribute("style","display:inline;");
	input.value = "";
	input = getElementByClassName(row,"lbl_time")[0];
	input.setAttribute("style","display:none;");
	input.innerHTML = "";
	input = getElementByClassName(row,"cbo_action_start")[0];
	input.setAttribute("style","display:none;");
	input.selectedIndex = 0;
	input = getElementByClassName(row,"cbo_action_end")[0];
	input.setAttribute("style","display:none;");
	input.selectedIndex = 0;
	input = getElementByClassName(row,"lbl_action")[0];
	input.setAttribute("style","display:inline;");
	input.innerHTML = "";
	input = getElementByClassName(row,"text_costin")[0];
	input.setAttribute("style","display:inline;");
	input.value = "0";
	input = getElementByClassName(row,"lbl_costin")[0];
	input.setAttribute("style","display:none;");
	input.innerHTML = "";
	input = getElementByClassName(row,"text_costout")[0];
	input.setAttribute("style","display:inline;");
	input.value = "0";
	input = getElementByClassName(row,"lbl_costout")[0];
	input.setAttribute("style","display:none;");
	input.innerHTML = "";
	input = getElementByClassName(row,"lbl_project")[0];
	input.setAttribute("style","display:inline;");
	input.innerHTML = "";
	input = getElementByClassName(row,"lbl_work")[0];
	input.setAttribute("style","display:inline;");
	input.innerHTML = "";
	input = getElementByClassName(row,"lbl_customer")[0];
	input.setAttribute("style","display:inline;");
	input.innerHTML = "";
	input = getElementByClassName(row,"lbl_process")[0];
	input.setAttribute("style","display:inline;");
	input.innerHTML = "";
	input = getElementByClassName(row,"btn_up")[0];
	input.setAttribute("style","display:inline;");
	input = getElementByClassName(row,"btn_dw")[0];
	input.setAttribute("style","display:inline;");
	input = getElementByClassName(row,"btn_add")[0];
	input.setAttribute("style","display:inline;");
	input = getElementByClassName(row,"btn_edit")[0];
	input.setAttribute("style","display:inline;");
	input = getElementByClassName(row,"btn_del")[0];
	input.setAttribute("style","display:inline;");

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

	//背景色を選択色にする
	row.style.backgroundColor = "#ffffaa";

	//日の先頭行を取得
	topRow = getTargetDayRow(rows, tr);
	td_date = getElementByClassName(topRow, "td_date")[0];

	//rowSpanを+1する
	rowSpan++;
	td_date.setAttribute("rowSpan", rowSpan);

    //行操作イベントを設定
    entryRowEvents();

	return rowIndex;
}

//削除
function schedule_removeRow(obj) {

	//全ての行
	var rows = document.getElementById('schedule').rows;

	//tbody要素を取得
	var tbody = document.getElementById("schedule-tbody");

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;

	//1つ下の行を取得
	var row = getBeforeRow(rows, tr);

	//先頭行か、それ以外か
	var etc_row = 0;
	var td = getElementByClassName(tr, "td_date");
	if (td.length < 1) {
		etc_row = 1;
	}

	//日の先頭行を取得
	var topRow = getTargetDayRow(rows, tr);
	var td_date = getElementByClassName(topRow, "td_date")[0];
	var rowSpan = td_date.getAttribute("rowSpan");
	var date = getElementByClassName(topRow,"date")[0];
	var input = null;

	//日の範囲
	var id = topRow.id;
	var tmp = getTargetDayRowArea(rows, id);
	var top_row = tmp['top_row'];
	var end_row = tmp['end_row'];
	if (top_row == end_row-1) {
		alert_dialog('削除できません。<br>日ごとに最低１行は必要です。');
		return;
	}

	//先頭行を削除する場合の処理
	if (etc_row == 0) {
		var col = get_cloneElement(topRow.getElementsByTagName("td")[0]);
		col.setAttribute("rowspan",rowSpan-1);		//rowSpan-1
		row.insertBefore(col, row.firstChild);		//先頭列追加
		col.innerHTML = td_date.innerHTML;			//td_dateの内容をコピー
		row.setAttribute("id",tr.id);				//idを設定
		input = getElementByClassName(row,"date")[0];
		input.value = date.value;
	} else {
		td_date.setAttribute("rowspan",rowSpan-1);	//rowSpan-1
	}

	//行削除
	tbody.removeChild(tr);
}

//OK処理
function edit_ok() {

	var section_no = $("#edit_section").val();

	//入力チェック
	if (section_no == 2) {
		if ($("#edit_project").val() < 1) {
			$("#alert_dialog .label").html($('#edit_dialog .tr_project label').html() + 'を選択してください。');
			$('#alert_dialog').dialog('open');
			return false;
		}
		if ($("#edit_work").val() < 1) {
			$("#alert_dialog .label").html($('#edit_dialog .tr_work label').html() + 'を選択してください。');
			$('#alert_dialog').dialog('open');
			return false;
		}
		if ($("#edit_customer").val() < 1) {
			$("#alert_dialog .label").html($('#edit_dialog .tr_customer label').html() + 'を選択してください。');
			$('#alert_dialog').dialog('open');
			return false;
		}
		if ($("#edit_process").val() < 1) {
			$("#alert_dialog .label").html($('#edit_dialog .tr_process label').html() + 'を選択してください。');
			$('#alert_dialog').dialog('open');
			return false;
		}
		if ($("#edit_action").val().length < 1) {
			$("#alert_dialog .label").html($('#edit_dialog .tr_action label').html() + 'を入力してください。');
			$('#alert_dialog').dialog('open');
			return false;
		}
	}

	var rowIndex = -1;
	switch ($("#edit_dialog .edit_type").val()) {
	case 'INSERT':
		rowIndex = insertRow(target_obj);
		break;
	case 'EDIT':
		rowIndex = target_obj.parentNode.parentNode.rowIndex;
		break;
	}
	target_obj = null;
	if (rowIndex < 0) {
		return false;
	}

	var rows = document.getElementById('schedule').rows;
	var row = rows[rowIndex];
	var input =null;

	input = getElementByClassName(row,"no")[0];
	input.value = 0;

	input = getElementByClassName(row,"section_no")[0];
	input.value = section_no;

	if (section_no == 2) {
		input = getElementByClassName(row,"project_no")[0];
		input.value = $("#edit_project").val();

		input = getElementByClassName(row,"work_no")[0];
		input.value = $("#edit_work").val();

		input = getElementByClassName(row,"customer_no")[0];
		input.value = $("#edit_customer").val();

		input = getElementByClassName(row,"process_no")[0];
		input.value = $("#edit_process").val();

		input = getElementByClassName(row,"text_time")[0];
		//input.value = "";

		input = getElementByClassName(row,"cbo_action_start")[0];
		input.setAttribute("style","display:none;");
		input.selectedIndex = 0;

		input = getElementByClassName(row,"cbo_action_end")[0];
		input.setAttribute("style","display:none;");
		input.selectedIndex = 0;

		input = getElementByClassName(row,"lbl_action")[0];
		input.setAttribute("style","display:inline;");
		input.innerHTML = $("#edit_action").val();

		input = getElementByClassName(row, "lbl_project")[0];
		input.innerHTML = $("#edit_project option:selected").text();

		input = getElementByClassName(row, "lbl_work")[0];
		input.innerHTML = $("#edit_work option:selected").text();

		input = getElementByClassName(row, "lbl_customer")[0];
		input.innerHTML = $("#edit_customer option:selected").text();

		input = getElementByClassName(row, "lbl_process")[0];
		input.innerHTML = $("#edit_process option:selected").text();

		//input = getElementByClassName(row,"text_costin")[0];
		//input.value = "0";

		//input = getElementByClassName(row,"text_costout")[0];
		//input.value = "0";
	} else {
		input = getElementByClassName(row,"project_no")[0];
		input.value = 0;

		input = getElementByClassName(row,"work_no")[0];
		input.value = 0;

		input = getElementByClassName(row,"customer_no")[0];
		input.value = 0;

		input = getElementByClassName(row,"process_no")[0];
		input.value = 0;

		input = getElementByClassName(row,"text_time")[0];
		input.value = "";

		input = getElementByClassName(row,"cbo_action_start")[0];
		input.setAttribute("style","display:none;");
		if (section_no == 1) {
			select_combobox_value(input, $("#edit_kintai_start").val());
		} else {
			input.selectedIndex = 0;
		}

		input = getElementByClassName(row,"cbo_action_end")[0];
		input.setAttribute("style","display:none;");
		if (section_no == 3) {
			select_combobox_value(input, $("#edit_kintai_end").val());
		} else {
			input.selectedIndex = 0;
		}

		input = getElementByClassName(row,"lbl_action")[0];
		input.innerHTML = '';

		input = getElementByClassName(row,"text_costin")[0];
		input.value = "0";

		input = getElementByClassName(row,"text_costout")[0];
		input.value = "0";
	}

	showEditModeRow(row);

	$("#edit_dialog").dialog('close');
	setFocus_rowObj(row, "text_time");
	return true;
}

//保存
function save(obj) {

	var row = null;
	if (obj.tagName.toUpperCase() == "TR") {
		row = obj;
	} else {
		row = obj.parentNode.parentNode;
	}

	//全ての行
	var rows = document.getElementById('schedule').rows;

	//先頭行
	var topRow = getTargetDayRow(rows, row);

	//tr_年月日
	var date = getElementByClassName(topRow,"date")[0].value;
	var id = 'tr_' + date;

	//対象日の範囲
	var row = getTargetDayRowArea(rows, id);
	var top_row = row['top_row'];
	var end_row = row['end_row'];

	var cboUser = document.getElementById('cboUser');

	var input = null;
	var section_no = "0";

	var param = {};
	param['post_type'] = 'SAVE';
	param['post_uid'] = cboUser.value;
	param['post_date'] = date;
	param['post_time'] = new Array();
	param['post_section_no'] = new Array();
	param['post_project_no'] = new Array();
	param['post_work_no'] = new Array();
	param['post_process_no'] = new Array();
	param['post_customer_no'] = new Array();
	param['post_kintai_no'] = new Array();
	param['post_action'] = new Array();
	param['post_costin'] = new Array();
	param['post_costout'] = new Array();

	var index = 0;

	for (var i = top_row; i < end_row; i++) {
		row = rows[i];

		input = getElementByClassName(row, "text_time")[0];
		param['post_time'][index] = input.value;

		input = getElementByClassName(row, "lbl_time")[0];
		input.innerHTML = param['post_time'][index];

		input = getElementByClassName(row, "section_no")[0];
		section_no = input.value;
		param['post_section_no'][index] = section_no;

		input = getElementByClassName(row, "project_no")[0];
		param['post_project_no'][index] = input.value;

		input = getElementByClassName(row, "work_no")[0];
		param['post_work_no'][index] = input.value;

		input = getElementByClassName(row, "process_no")[0];
		param['post_process_no'][index] = input.value;

		input = getElementByClassName(row, "customer_no")[0];
		param['post_customer_no'][index] = input.value;

		var kintai_no = 0;
		var kintai_name = '';
		switch (section_no) {
		case "1":
			input = getElementByClassName(row, "cbo_action_start")[0];
			kintai_no = input.value;
			kintai_name = input.options[input.selectedIndex].text;
			break;
		case "3":
			input = getElementByClassName(row, "cbo_action_end")[0];
			kintai_no = input.value;
			kintai_name = input.options[input.selectedIndex].text;
			break;
		}
		param['post_kintai_no'][index] = kintai_no;

		input = getElementByClassName(row, "lbl_action")[0];
		if (section_no != 2) {
			input.innerHTML = kintai_name;
		}
		param['post_action'][index] = input.innerHTML;

		input = getElementByClassName(row, "text_costin")[0];
		param['post_costin'][index] = input.value;

		input = getElementByClassName(row, "lbl_costin")[0];
		input.innerHTML = param['post_costin'][index];

		input = getElementByClassName(row, "text_costout")[0];
		param['post_costout'][index] = input.value;

		input = getElementByClassName(row, "lbl_costout")[0];
		input.innerHTML = param['post_costin'][index];

		index++;
	}

	//レスポンス待ちの表示
	var topRow = document.getElementById(id);
	var busy = getElementByClassName(topRow, "busy")[0];
	busy.setAttribute('style','display:inline;');

	//非同期でPOST
	$.post("schedule_list_control.php", param, function(response) {

		//処理結果判断
		if (response[0] != 0) {
			alert_dialog(response[0]);
		} else {
		}

		//閲覧モード
		showBrowseMode(date);

	},"json")
	.complete(function() {

		//レスポンス待ちの解除
		busy.setAttribute('style','display:none;');

		//再読込
		reload(row);

		showTotal('schedule_list_control.php');

	});
}

//再読込（日単位）
function reload(obj) {

	var row = null;
	if (obj.tagName.toUpperCase() == "TR") {
		row = obj;
	} else {
		row = obj.parentNode.parentNode;
	}

	//全ての行
	var rows = document.getElementById('schedule').rows;

	//先頭行
	var topRow = getTargetDayRow(rows, row);

	//tr_年月日
	var date = getElementByClassName(topRow,"date")[0].value;
	var id = 'tr_' + date;

	//対象日の範囲
	var tmp = getTargetDayRowArea(rows, id);
	var top_row = tmp['top_row'];
	var end_row = tmp['end_row'];

	var cboUser = document.getElementById('cboUser');

	var param = {};
	param['post_type'] = 'GET_SCHEDULE_DAY';
	param['post_uid'] = cboUser.value;
	param['post_date'] = date;

	//レスポンス待ちの表示
	var topRow = document.getElementById(id);
	var busy = getElementByClassName(topRow, "busy")[0];
	busy.setAttribute('style','display:inline;');

	//非同期でPOST
	$.post("schedule_list_control.php", param, function(response) {

		//処理結果判断
		if (response[0] != 0) {
			alert_dialog(response[0]);
		} else {
			//各値の設定
			if (response.length > 1) {
				rowIndex = top_row;
				for (var i = 1; i < response.length; i++) {
					//行が足りなかったら追加
					if (rowIndex >= end_row) {
						var lbl_costin = getElementByClassName(row, "lbl_costin")[0];
						insertRow(lbl_costin);
						shedule_moveRow(rowIndex, rowIndex-1);
					}

					var row = rows[rowIndex];
					var input = null;

					input = getElementByClassName(row, "text_time")[0];
					input.value = response[i]['time'];

					input = getElementByClassName(row, "lbl_time")[0];
					input.innerHTML = response[i]['time'];

					input = getElementByClassName(row, "no")[0];
					input.value = response[i]['no'];

					input = getElementByClassName(row, "section_no")[0];
					input.value = response[i]['section_no'];

					input = getElementByClassName(row, "project_no")[0];
					input.value = response[i]['project_no'];

					input = getElementByClassName(row, "work_no")[0];
					input.value = response[i]['work_no'];

					input = getElementByClassName(row, "process_no")[0];
					input.value = response[i]['process_no'];

					input = getElementByClassName(row, "customer_no")[0];
					input.value = response[i]['customer_no'];

					if (response[i]['section_no'] == 1) {
						input = getElementByClassName(row, "cbo_action_start")[0];
						select_combobox_value(input, response[i]['kintai_no']);
						input = getElementByClassName(row, "cbo_action_end")[0];
						select_combobox_value(input, 0);
					}
					if (response[i]['section_no'] == 3) {
						input = getElementByClassName(row, "cbo_action_start")[0];
						select_combobox_value(input, 0);
						input = getElementByClassName(row, "cbo_action_end")[0];
						select_combobox_value(input, response[i]['kintai_no']);
					}

					input = getElementByClassName(row, "lbl_project")[0];
					input.innerHTML = response[i]['project_name'];

					input = getElementByClassName(row, "lbl_work")[0];
					input.innerHTML = response[i]['work_name'];

					input = getElementByClassName(row, "lbl_customer")[0];
					input.innerHTML = response[i]['customer_name'];

					input = getElementByClassName(row, "lbl_process")[0];
					input.innerHTML = response[i]['process_name'];

					input = getElementByClassName(row, "lbl_action")[0];
					input.innerHTML = response[i]['action'];

					input = getElementByClassName(row, "text_costin")[0];
					input.value = response[i]['costin'];

					input = getElementByClassName(row, "lbl_costin")[0];
					input.innerHTML = response[i]['costin'];

					input = getElementByClassName(row, "text_costout")[0];
					input.value = response[i]['costout'];

					input = getElementByClassName(row, "lbl_costout")[0];
					input.innerHTML = response[i]['costout'];

					rowIndex++;
				}
				//行が多かったら削除
				for (var i = rowIndex; i < end_row; i++) {
					var row = rows[rowIndex];
					var lbl_costin = getElementByClassName(row, "lbl_costin")[0];
					schedule_removeRow(lbl_costin);
				}
			}
		}

		//閲覧モード
		showBrowseMode(date);

		//小計
		subtotal(date);

	},"json")
	.error(function() {

		//エラー
		alert_dialog("サーバーに接続できません。<br>再読込してください。");

	})
	.complete(function() {

		//レスポンス待ちの解除
		topRow = document.getElementById(id);
		busy = getElementByClassName(topRow, "busy")[0];
		busy.setAttribute('style','display:none;');

		//編集ボタンにフォーカスをあわせる
		setFocus_topRowObj(topRow, "btn_edit_start");

	});
}

//対象年月選択
function selectDate() {

	var form = document.getElementById("post_form");
	var cboDate = document.getElementById("cboDate");
	var cboUser = document.getElementById("cboUser");

	var post_type = getElementByClassName(form, "post_type")[0];
	var post_date = getElementByClassName(form, "post_date")[0];
	var post_uid = getElementByClassName(form, "post_uid")[0];

	post_type.value = 'SELECT_DATE';
	post_date.value = cboDate.value;
	post_uid.value = cboUser.value;

	form.submit();
}

//ユーザー選択
function selectUser() {

	var form = document.getElementById("post_form");
	var cboDate = document.getElementById("cboDate");
	var cboUser = document.getElementById("cboUser");

	var post_type = getElementByClassName(form, "post_type")[0];
	var post_date = getElementByClassName(form, "post_date")[0];
	var post_uid = getElementByClassName(form, "post_uid")[0];

	post_type.value = 'SELECT_USER';
	post_date.value = cboDate.value;
	post_uid.value = cboUser.value;

	form.submit();
}

//締日の設定
function sime_dialog_ok() {

	$("#post_form .post_type").val('SET_SIME');
	$("#post_form .post_sime1").val($("#sime_dialog .edit-sime1").val());
	$("#post_form .post_sime2").val($("#sime_dialog .edit-sime2").val());

	$("#sime_dialog").dialog('close');
	$('#post_form').submit();
}

//データの無いユーザーは表示しない
function checkShowUser() {

	$('#post_form .post_type').val('CHECK_SHOW_USER');

	var checked = 0;
	if ($('#chkShowUser').attr('checked')) {
		checked = 1;
	}
	$('#post_show_user').val(checked);

	$('#post_form').submit();
}
