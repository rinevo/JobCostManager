//締日の設定
function simeDialog() {

	$('#sime_dialog .validateTips').html('月の中計日と締日を設定してください。');
	$('#sime_dialog .validateTips').css('color','blue');
	$('#ui-dialog-title-sime_dialog').html('締日の設定');

	$('#sime_dialog').dialog('open');
}
$(function() {

	$("#sime_dialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 250,
		width: 300,
		modal: true,
		closeOnEscape: true,
		buttons: {
		"OK": function() {
				if (!sime_dialog_ok()) {
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

});
