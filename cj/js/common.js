//子要素をclassNameで探す
function getElementByClassName(parent, className) {
	var classElements = new Array();
	var allElements = parent.getElementsByTagName("*");
	var j = 0;
	for (var i = 0; i < allElements.length; i++) {
		if (allElements[i].className == className) {
			classElements[j] = allElements[i];
			j++;
		}
	}
	return classElements;
}

//子要素をnameで探す
function getElementByName(parent, name) {
	var classElements = new Array();
	var allElements = parent.getElementsByTagName("*");
	var j = 0;
	for (var i = 0; i < allElements.length; i++) {
		if (allElements[i].name == name) {
			classElements[j] = allElements[i];
			j++;
		}
	}
	return classElements;
}

//1つ上の行を取得
function getAfterRow(rows, tr) {
	var row = tr;
    for (var i = 0; i < rows.length; i++) {
    	if (tr == rows[i]) {
            break;
        }
        if (i > 0) {	// タイトル行を除く
    		row = rows[i];
        }
    }
    return row;
}

//1つ下の行を取得
function getBeforeRow(rows, tr) {
	var row = tr;
	var hit = false;
    for (var i = 0; i < rows.length; i++) {
        if (hit) {
    		row = rows[i];
    		break;
        }
    	if (tr == rows[i]) {
            hit = true;
        }
    }
    return row;
}

//1つ上の行に行追加（先頭行をコピー）
function insertAfterRow(obj, table_id, tbody_id, className) {

	var rowIndex = -1;

	//全ての行
	var rows = document.getElementById(table_id).rows;

	//tbody要素を取得
	var tbody = document.getElementById(tbody_id);

	//tbodyタグ直下のノード（tr）を複製
	var row = null;
	if (className != undefined) {
		for (var i = 0; i < rows.length; i++) {
			if (rows[i].className == className) {
				row = get_cloneElement(rows[i]);
				break;
			}
		}
	} else {
		row = get_cloneElement(tbody.getElementsByTagName("tr")[0]);
	}

	if (obj != undefined) {
		//objの親の親のノード（tr）を取得
		var tr = null;
		if (obj.tagName.toUpperCase() == "TR") {
			tr = obj;
		} else {
			tr = obj.parentNode.parentNode;
		}

		//1つ上の行を取得
		var afterRow = getAfterRow(rows, tr);

		//複製したtrを1つ上の行に挿入
		if (afterRow != tr) {
			tbody.insertBefore(row , afterRow.nextSibling);
			rowIndex = afterRow.rowIndex + 1;
		} else {
			//先頭に追加
			tbody.insertBefore(row , tbody.firstChild);
			rowIndex = 1;
		}
	} else {
		//先頭に追加
		tbody.insertBefore(row , tbody.firstChild);
		rowIndex = 1;
	}

	return rowIndex;
}

//1つ下の行に行追加（先頭行をコピー）
function insertBeforeRow(obj, table_id, tbody_id, className) {

	var rowIndex = -1;

	//全ての行
	var rows = document.getElementById(table_id).rows;

	//tbody要素を取得
	var tbody = document.getElementById(tbody_id);

	//tbodyタグ直下のノード（tr）を複製
	var row = null;
	if (className != undefined) {
		for (var i = 0; i < rows.length; i++) {
			if (rows[i].className == className) {
				row = get_cloneElement(rows[i]);
				break;
			}
		}
	} else {
		row = get_cloneElement(tbody.getElementsByTagName("tr")[0]);
	}

	if (obj != undefined) {
		//objの親の親のノード（tr）を取得
		var tr = null;
		if (obj.tagName.toUpperCase() == "TR") {
			tr = obj;
		} else {
			tr = obj.parentNode.parentNode;
		}

		//1つ下の行を取得
		var beforeRow = getBeforeRow(rows, tr);

		//複製したtrを1つ下の行に挿入
		if (beforeRow != tr) {
			tbody.insertBefore(row , tr.nextSibling);
			rowIndex = tr.rowIndex + 1;
		} else {
			//末尾に追加
			tbody.insertBefore(row);
			rowIndex = rows.length - 1;
		}
	} else {
		//末尾に追加
		tbody.insertBefore(row);
		rowIndex = rows.length - 1;
	}

	return rowIndex;
}

//行削除
function removeRow(obj, tbody_id) {

	//tbody要素を取得
	var tbody = document.getElementById(tbody_id);

	//objの親の親のノード（tr）を取得
	var tr = null;
	if (obj.tagName.toUpperCase() == "TR") {
		tr = obj;
	} else {
		tr = obj.parentNode.parentNode;
	}

	//行削除
	tbody.removeChild(tr);
}

//行移動
function moveRow(beforeRowIndex, afterRowIndex, table_id, tbody_id, className, copy_func) {

	var rowIndex = -1;

	//全ての行
	var rows = document.getElementById(table_id).rows;

	if (afterRowIndex < 1) {
		return rowIndex;
	}
	if (afterRowIndex > rows.length) {
		return rowIndex;
	}

	//現在の行
	var tr_before = rows[beforeRowIndex];

	//移動先の行
	var tr_after = null;
	if (afterRowIndex < rows.length) {
		tr_after = rows[afterRowIndex];
	}

	//行追加
	var row = null;
	if (afterRowIndex < rows.length) {
		rowIndex = insertAfterRow(tr_after, table_id, tbody_id, className);
	} else {
		rowIndex = insertBeforeRow(tr_after, table_id, tbody_id, className);
	}
	rows = document.getElementById(table_id).rows;
	row = rows[rowIndex];

	//コピー
	copy_func(tr_before, row);

	//移動前の行を削除
	removeRow(tr_before, tbody_id);

	return rowIndex;
}

//コンボボックスの選択
function select_combobox_value(obj, select_value) {
	obj.selectedIndex = 0;
	for(var i = 0; i < obj.options.length; i++) {
		if(obj.options[i].value == select_value) {
			obj.selectedIndex = i;
			break;
		}
	}
}

//コンボボックスの項目設定
function set_combobox_options(obj, list, item_value, item_name) {
	//現在の項目を全削除
	for (var i = obj.options.length; i >= 0; i--) {
		obj.options[i] = null;
	}
	//項目追加
	obj.options[0] = new Option('','0');
	for (var i = 1; i < list.length; i++) {
		obj.options[i] = new Option(list[i][item_name], list[i][item_value]);
	}
}

//Element削除
function removeElementName(parent_name, delete_name) {
	var form = document.getElementById(parent_name);
	var obj = getElementByName(form,delete_name);
	for (var i=obj.length-1; i>=0; i--) {
		form.removeChild(obj[i]);
	}
}

//type="hidden"のinputをparent_objに追加
function appendHiddenInput(parent_name, append_name) {
	var form = document.getElementById(parent_name);
	obj = document.createElement('input');
	obj.setAttribute('type','hidden');
	obj.name = append_name;
	form.appendChild(obj);
	return obj;
}

//子要素を全て削除
function removeChildrenAll(parent) {
	var obj = parent.children;
	for (var i=obj.length-1; i>=0; i--) {
		parent.removeChild(obj[i]);
	}
}

//CSVを配列に変換
function str_getcsv(csv) {

	var list = new Array();
	var index = 0;
	var value = '';
	var i = 0;

	while (csv.length > 0) {
		index = csv.indexOf(',');
		if (index >= 0) {
			value = csv.substring(0, index);
			csv = csv.substring(index+1);
		} else {
			value = csv;
			csv = '';
		}
		list[i] = value;
		i++;
	}

	return list;
}

//Elementを複製（IEはcloneNodeの仕様が他のブラウザと異なるため自前で複製）
function get_cloneElement(obj) {
	var clone = document.createElement(obj.tagName);
	clone.id = obj.id;
	clone.className = obj.className;
	if (obj.tagName.toUpperCase() == "TR") { //IEだとTD要素をきちんと複製してくれないので更に子要素を複製
		var tds = obj.getElementsByTagName('td');
		for (var i = 0; i < tds.length; i++) {
			var td = get_cloneElement(tds[i]);
			clone.appendChild(td);
		}
	} else {
		clone.innerHTML = obj.innerHTML;
	}
	return clone;
}

//現在フォーカスしているElementを返す
function get_focusElement() {
	return (document.activeElement || window.getSelection().focusNode);
}
