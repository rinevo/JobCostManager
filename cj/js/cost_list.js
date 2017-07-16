var flgRowClick = false;

$(document).ready(function() {
	//集計表示
    showTotal('cost_list_control.php');

    //行クリックでハイライト固定
    $("#list tbody tr").click(function() {
    	$("#list tbody tr").css("background","white");
    	this.style.backgroundColor = "#ffffaa";
    	flgRowClick = true;
    });
    $("#list tbody tr").dblclick(function() {
    	flgRowClick = false;
    });
    $("#list tbody tr").hover(function() {
    	if (!flgRowClick) {
    		this.style.backgroundColor = "#ffffaa";
    	}
    },function() {
    	if (!flgRowClick) {
    		this.style.backgroundColor = "white";
    	}
    });
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

//項目選択
function itemDialog() {

	//入力画面表示
	$("#post_type").val('SET_SELECT');
	$('#item_dialog .validateTips').html('表示する項目を選択してください。');
	$('#item_dialog .validateTips').css('color','blue');
	$('#ui-dialog-title-item_dialog').html('表示する項目の選択');

	$('#item_dialog').dialog('open');
}
$(function() {

	$("#item_dialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 300,
		width: 400,
		modal: true,
		closeOnEscape: true,
		buttons: {
		"OK": function() {

				//既にPOSTデータがあれば削除
				var form = document.getElementById('post_form');
				var obj = getElementByName(form,"post_item_list[]");
				for (var i=obj.length-1; i>=0; i--) {
					form.removeChild(obj[i]);
				}

				//並び順を取得
				var rows = document.getElementById('item_list').rows;

				//POSTデータ追加
				for (i=1; i<rows.length; i++) {
					if (getElementByClassName(rows[i], "chk")[0].checked) {
						obj = document.createElement('input');
						obj.setAttribute('type','hidden');
						obj.name = 'post_item_list[]';
						obj.value = getElementByClassName(rows[i], "id")[0].value;
						form.appendChild(obj);
					}
				}

				$("#item_dialog").dialog('close');
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
