//集計の表示
function showTotal(post_url) {

	var cboDate = document.getElementById("cboDate");
	var cboUser = document.getElementById("cboUser");
	var total = document.getElementById("total");
	var total_header = getElementByClassName(total, "header")[0];
	var total_tbody = total.getElementsByTagName('tbody')[0];

	//レスポンス待ちの表示
	var busy = document.getElementById('tatal_busy');
	busy.setAttribute('style','display:inline;');
	total.setAttribute('visibility','hidden');

	var param = {};
	param['post_type'] = 'GET_TOTAL';
	param['post_uid'] = cboUser.value;
	param['post_date'] = cboDate.value;

	//非同期でPOST
	$.post(post_url, param, function(response) {

		//処理結果判断
		if (response[0] != 0) {
			alert_dialog(response[0]);
		} else {
			//各値の設定
			if (response.length > 1) {
				//行削除
				removeChildrenAll(total_header);
				removeChildrenAll(total_tbody);
				var row = null;
				var col = null;
				for (var i = 1; i < response.length; i++) {
					//行追加
					if (i == 1) {
						row = total_header;
					} else {
						row = total_tbody.insertRow(-1);
					}
					for (var j = 0; j < response[i].length; j++) {
						//列追加
						col = row.insertCell(-1);
						//値設定
						col.innerHTML = response[i][j];
					}
				}
			}
		}

	},"json")
	.error(function(XMLHttpRequest, textStatus, errorThrown) {

		//エラー
		alert_dialog("サーバーに接続できません。<br>再読込してください。");

	})
	.complete(function(xhr, status) {

		//レスポンス待ちの解除
		busy.setAttribute('style','display:none;');
		total.setAttribute('visibility','visible');

	});

}

