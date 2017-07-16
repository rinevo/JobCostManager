$(function() {

	$("#alert_dialog").dialog({
		bgiframe: true,
		autoOpen: false,
		width: 320,
		modal: true,
		buttons: {
			"OK": function() {
				$(this).dialog("close");
			}
		},
		close: function() {
		}
	});

});

function alert_dialog(message) {
	$("#alert_dialog .label").html(message);
	$('#alert_dialog').dialog('open');
}
