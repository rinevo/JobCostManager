//スクロール位置を退避
function getscrolltop(grid_name, pos_name) {
    var ctrlGrd = document.getElementById(grid_name);
    if (ctrlGrd == null) {
        return;
    }
    var ctrlDiv = ctrlGrd.parentElement;

    var ctrlTop = document.getElementById(pos_name);
    if (ctrlTop == null) {
        return;
    }
    ctrlTop.value = ctrlDiv.scrollTop;
}

//スクロール位置を設定
function setscrolltop(grid_name, pos_name) {
    var ctrlGrd = document.getElementById(grid_name);
    if (ctrlGrd == null) {
        return;
    }
    var ctrlDiv = ctrlGrd.parentElement;

    var ctrlTop = document.getElementById(pos_name);
    if (ctrlTop == null) {
        return;
    }
    ctrlDiv.scrollTop = ctrlTop.value;
}
