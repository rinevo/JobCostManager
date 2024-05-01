<?php require_once(dirname(__FILE__).'/../login/myauth.php'); ?>
<?php require_once(dirname(__FILE__).'/../define.php'); ?>
<?php if (($auth->getParent_role_cost() & ROLE_VIW) == 0) { header("HTTP/1.0 404 Not Found"); exit(); } ?>
<?php require_once(dirname(__FILE__).'/../Encode.php'); ?>
<?php require_once(dirname(__FILE__).'/../db_config.php'); ?>
<?php require_once(dirname(__FILE__).'/../login/class/UserListSQL.class.php'); ?>
<?php require_once(dirname(__FILE__).'/class/CjCommonSQL.class.php'); ?>
<?php require_once(dirname(__FILE__).'/class/CostListSQL.class.php'); ?>

<?php
// アクセスログ
require_once(dirname(__FILE__).'/../class/AccessLogSQL.class.php');
$log = new AccessLogSQL($GLOBALS['dbopts']);
$log->Write(0, '', __FILE__, __FUNCTION__, __LINE__);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo APP_TITLE; ?> | 工数表</title>
<meta name="viewport" content="width=980px">

<link rel="shortcut icon" href="<?php echo PROJECT_ROOT ?>/favicon.ico">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/style.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/green/jquery-ui-1.9.2.custom.min.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/cj/css/fixedheadertable.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/cj/css/sime_edit_dialog.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/cj/css/total_list.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/cj/css/cost_list.css" media="all">

<script src="<?php echo PROJECT_ROOT ?>/js/jquery-1.7.2.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery-ui-1.8.20.custom.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery.bgiframe.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/cj/js/jquery.fixedheadertable.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/cj/js/ccchart.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/alert_dialog.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/browser.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/cj/js/common.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/cj/js/sime_edit_dialog.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/cj/js/total_list.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/cj/js/cost_list.js"></script>

</head>
<body>

<?php require_once(dirname(__FILE__).'/../header.php'); ?>

<?php
	$cmn = new CjCommonSQL($dbopts);
	$cmn->setDefSime(INI_COST_SIME1, INI_COST_SIME2);

	//選択ユーザー
	$select_uid = isset($_SESSION[S_SELECT_UID]) ? $_SESSION[S_SELECT_UID] : $auth->getParent_uid();
	$_SESSION[S_SELECT_UID] = $select_uid;

	//年月（自分の設定を使う）
	$select_date = isset($_SESSION[S_SELECT_DATE]) ? $_SESSION[S_SELECT_DATE] : $cmn->getNowDate($auth->getParent_uid());
	$_SESSION[S_SELECT_DATE] = $select_date;
	$date_list = $cmn->getYearMonthList($auth->getParent_uid(), $select_date);

	//データの無いユーザーは表示しない（自分の設定を使う）
	$ini_item_user =  $cmn->getIni_data(INI_SHOW_USER, $auth->getParent_uid());

	//ユーザー名
	if ($ini_item_user == 1) {
		$user_list = $cmn->getUser_list($select_date);
	} else {
		$user_list = $cmn->getUser_list();
	}

	//他のユーザーを選択しているときは、権限チェック
	if ($auth->getParent_uid() != $select_uid) {
		if (($auth->getParent_role_cost() & ROLE_MEMBER_VIW) == 0) {
			header("HTTP/1.0 404 Not Found");
			return;
		}
		//権限の属性
		$role_viw = ROLE_MEMBER_VIW;
		$role_edt = ROLE_MEMBER_EDT;
		$role_add = ROLE_MEMBER_ADD;
		$role_del = ROLE_MEMBER_DEL;
	} else {
		$role_viw = ROLE_VIW;
		$role_edt = ROLE_EDT;
		$role_add = ROLE_ADD;
		$role_del = ROLE_DEL;
	}

	//他のユーザーの参照権限が無ければ選択から削除
	if (($auth->getParent_role_cost() & ROLE_MEMBER_VIW) == 0) {
		for ($i = count($user_list) - 1; $i > 0; $i--) {
			if ($user_list[$i]['uid'] != $auth->getParent_uid()) {
				unset($user_list[$i]);
			}
		}
	}

	//日（自分の設定を使う）
	$day_list = $cmn->getDayList($auth->getParent_uid(), $select_date);

	//月末
	$last_day = $cmn->getLastday($select_date);

	//締日（自分の設定を使う）
	$sime1 = $cmn->getSime1($auth->getParent_uid());
	$sime2 = $cmn->getSime2($auth->getParent_uid());

	//工数リスト
	$cost = new CostListSQL($dbopts);
	$list = $cost->getList($select_uid, $select_date);

	//表示項目の取得（自分の設定を使う）
	$select_list = $cost->getCostField_list($auth->getParent_uid());
?>

<div id="content" class="clearfix">
<div class="inner">

<?php if (isset($_SESSION[S_MESSAGE2]) && strlen($_SESSION[S_MESSAGE2])) {
	echo '<div class="info">'.$_SESSION[S_MESSAGE2].'</div>';
	$_SESSION[S_MESSAGE2] = '';
} ?>

	<div id="list_header">
		<div class="total_list" style="height:72px;">
			<table class="total" id="total">
				<thead>
					<tr class="header">
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
			<div id="tatal_busy" style="display:none">
				<img alt="処理中" src="images/loading-28.gif" width="16" height="16">
			</div>
		</div>
		<div class="date">
			<label>対象年月：<select name="cboDate" id="cboDate" onchange="selectDate()">
			<?php if ($date_list) { foreach ($date_list as $row) { ?>
				<option value="<?php echo e($row['date']); ?>" <?php if ($row['date'] == $select_date) { ?>selected<?php } ?>><?php echo e($row['name']); ?></option>
			<?php } } ?>
			</select></label>
			<?php if (($auth->getParent_role_cost() & ROLE_EDT) != 0) { ?>
				<input type="button" value="締日" class="btn_sime" onclick="simeDialog()">
			<?php } ?>
			<?php if (($auth->getParent_role_cost() & ROLE_EDT) != 0) { ?>
				<input type="button" value="項目" class="btn_item" onclick="itemDialog()">
			<?php } ?>
		</div>
		<div class="user">
			<label>ユーザー：<select name="cboUser" id="cboUser" onchange="selectUser()">
			<?php if ($user_list) { foreach ($user_list as $row) { if ($row['member_status'] == GROUP_MEMBER_STATUS_NOMAL) { ?>
				<option value="<?php echo e($row['uid']); ?>" <?php if ($row['uid'] == $select_uid) { ?>selected<?php } ?>><?php echo e($row['name']); ?></option>
			<?php } } } ?>
			</select></label>
			<div class="show_user">
				<input type="checkbox" id="chkShowUser" onchange="checkShowUser()" <?php if ($ini_item_user == 1) { ?>checked<?php } ?>><label for="chkShowUser">データの無いユーザーは表示しない</label>
			</div>
		</div>
		<form id="post_form" method="post" action="cost_list_control.php" >
			<input type="hidden" name="post_type" id="post_type" value="" />
			<input type="hidden" name="post_date" id="post_date" value="<?php echo e($select_date); ?>" />
			<input type="hidden" name="post_sime1" id="post_sime1" value="<?php echo e($sime1); ?>" />
			<input type="hidden" name="post_sime2" id="post_sime2" value="<?php echo e($sime2); ?>" />
			<input type="hidden" name="post_uid" id="post_uid" value="<?php echo e($select_uid); ?>" />
			<input type="hidden" name="post_show_user" id="post_show_user" value="<?php echo e($ini_item_user); ?>" />
		</form>
	</div>

	<div class="item_list clearfix">

		<table class="list" id="list">
			<thead>
			<tr class="header" style="border-color: #cccccccc; border-width: 1px; border-style: solid; text-align: center; background: green; color: white;">
				<?php $item_col = 0; ?>
				<?php if ($list) { $row = $list[0]; ?>
					<th class="td_no">No</th>
					<?php if (isset($row['project_name'])) { ?>
						<th class="td_project">
							<?php echo e($row['project_name']); ?>
							<?php $item_col++; ?>
						</th>
					<?php } ?>
					<?php if (isset($row['work_name'])) { ?>
						<th class="td_work">
							<?php echo e($row['work_name']); ?>
							<?php $item_col++; ?>
						</th>
					<?php } ?>
					<?php if (isset($row['customer_name'])) { ?>
						<th class="td_customer">
							<?php echo e($row['customer_name']); ?>
							<?php $item_col++; ?>
						</th>
					<?php } ?>
					<?php if (isset($row['process_name'])) { ?>
						<th class="td_process">
							<?php echo e($row['process_name']); ?>
							<?php $item_col++; ?>
						</th>
					<?php } ?>
					<?php if (isset($row['action'])) { ?>
						<th class="td_action">
							<?php echo e($row['action']); ?>
							<?php $item_col++; ?>
						</th>
					<?php } ?>
					<?php foreach ($day_list as $day) { ?>
						<th class="td_date">
							<?php echo format($row[$day['date']],'d').'<br>('.week($row[$day['date']]).')'; ?>
						</th>
						<?php if (format($day['date'],'d') == $sime1) { ?>
						<th class="td_sime1">中計</th>
						<?php } ?>
					<?php } ?>
					<th class="td_sime2">中計</th>
					<th class="td_total">合計</th>
				<?php } ?>
			</tr>
			</thead>
			<tbody>
			<?php
			$col_total = array();
			foreach ($day_list as $day) {
				$col_total[$day['date']]['costin'] = 0;
				$col_total[$day['date']]['costout'] = 0;
				if (format($day['date'],'d') == $sime1) {
					$col_total['sime1']['costin'] = 0;
					$col_total['sime1']['costout'] = 0;
				}
			}
			$col_total['sime2']['costin'] = 0;
			$col_total['sime2']['costout'] = 0;
			$col_total['total']['costin'] = 0;
			$col_total['total']['costout'] = 0;
			?>
			<?php $index = 0; ?>
			<?php if ($list) { foreach ($list as $row) { if ($index > 0) { ?>
				<tr class="tr_item">
				<td class="td_no"><?php echo ($index); ?></td>
				<?php if (isset($row['project_name'])) { ?>
					<td class="td_project">
						<?php echo e($row['project_name']); ?>
					</td>
				<?php } ?>
				<?php if (isset($row['work_name'])) { ?>
					<td class="td_work">
						<?php echo e($row['work_name']); ?>
					</td>
				<?php } ?>
				<?php if (isset($row['customer_name'])) { ?>
					<td class="td_customer">
						<?php echo e($row['customer_name']); ?>
					</td>
				<?php } ?>
				<?php if (isset($row['process_name'])) { ?>
					<td class="td_process">
						<?php echo e($row['process_name']); ?>
					</td>
				<?php } ?>
				<?php if (isset($row['action'])) { ?>
					<td class="td_action">
						<?php echo e($row['action']); ?>
					</td>
				<?php } ?>
				<?php $row_costin = 0; $row_costout = 0; $row_sime_costin = 0; $row_sime_costout = 0; ?>
				<?php foreach ($day_list as $day) { ?>
					<td class="td_date" <?php
					if ($row[$day['date']]['costin'] >0 || $row[$day['date']]['costout'] > 0) {
						echo 'style="background:royalblue; color:white;">';
						echo '内'.e($row[$day['date']]['costin']).'<br>';
						echo '外'.e($row[$day['date']]['costout']);
						$row_costin += $row[$day['date']]['costin'];
						$row_costout += $row[$day['date']]['costout'];
						$row_sime_costin += $row[$day['date']]['costin'];
						$row_sime_costout += $row[$day['date']]['costout'];
						$col_total[$day['date']]['costin'] += $row[$day['date']]['costin'];
						$col_total[$day['date']]['costout'] += $row[$day['date']]['costout'];
					} elseif ($row[$day['date']]['action'] == 1) {
						echo 'style="background:paleturquoise;">△';
					} else {
						echo '>';
					}
					?></td>
					<?php if (format($day['date'],'d') == $sime1) { ?>
					<td class="td_sime1"><?php
						echo '内'.e($row_sime_costin).'<br>';
						echo '外'.e($row_sime_costout);
						$col_total['sime1']['costin'] += $row_sime_costin;
						$col_total['sime1']['costout'] += $row_sime_costout;
						$row_sime_costin = 0; $row_sime_costout = 0;
					?></td>
					<?php } ?>
				<?php } ?>
				<td class="td_sime2"><?php
					echo '内'.e($row_sime_costin).'<br>';
					echo '外'.e($row_sime_costout);
					$col_total['sime2']['costin'] += $row_sime_costin;
					$col_total['sime2']['costout'] += $row_sime_costout;
				?></td>
				<td class="td_total"><?php
					echo '内'.e($row_costin).'<br>';
					echo '外'.e($row_costout);
					$col_total['total']['costin'] += $row_costin;
					$col_total['total']['costout'] += $row_costout;
				?></td>
				</tr>
			<?php } $index++; } } ?>
			<tr class="tr_total">
				<td></td>
				<td colspan="<?php echo $item_col; ?>">合計</td>
				<?php foreach ($col_total as $row) { ?>
					<td><?php
					if ($row['costin'] >0 || $row['costout'] > 0) {
						echo '内'.e($row['costin']).'<br>';
						echo '外'.e($row['costout']);
					} ?></td>
				<?php } ?>
			</tr>
			</tbody>
		</table>

	</div><!-- item_list -->

	<div class="graph">
		<canvas id="graph"></canvas>
	</div>

</div><!-- inner -->
</div><!-- content -->

<?php require_once(dirname(__FILE__).'/../footer.php'); ?>

<?php require_once(dirname(__FILE__).'/sime_edit_dialog.php'); ?>

<div id="item_dialog" title="項目" style="display:none;">
<p class="validateTips"></p>
<table id="item_list" class="item_list">
	<thead>
		<tr>
			<th>表示</th><th>項目名</th>
		</tr>
	</thead>
	<tbody id="item_list_tbody">
	<?php if ($select_list) { foreach ($select_list as $row) { ?>
		<tr class="tr_item">
			<td class="td_chk">
				<input type="hidden" class="id" value="<?php echo $row['id']; ?>" />
				<input type="checkbox" class="chk" value="1" <?php if ($row['chk']) { ?>checked<?php } ?>>
			</td>
			<td class="td_name">
				<label class="name"><?php echo e($row['name']); ?></label>
			</td>
		</tr>
	<?php }} ?>
	</tbody>
</table>
</div>

<div id="alert_dialog" title="確認" style="display:none;">
	<label class="label"></label>
</div>

<script>
//グラフの設定
var chartdata2 = {

  "config": {
    "title": "",
    "subTitle": "",
    "type": "line",
    "xColor": "#ccc",
    "yColor": "#ccc",
    "lineWidth": 1,
    "bg": "white",
    "textColor": "black",
    "useMarker": "css-ring",
    "markerWidth": 10,
    "hanreiLineHeight": "1",
    "shadows": { "hanrei":['white',0,0,0] },
    "width": $(window).width() * 0.95,
    "height": $(window).height() * 0.95,
    "colorSet": [<?php $count=0; $hex=0xf; $index=0; if ($list) { foreach ($list as $row) {
    	if ($index > 0) echo ',';
    	$color = 0;
    	switch ($count) {
    		case 0:
    			$color = dechex($hex)."00";
    			break;
    		case 1:
    			$color = "0".dechex($hex)."0";
    			break;
    		case 2:
    			$color = "00".dechex($hex);
    			break;
    		case 3:
    			$color = "0".dechex($hex).dechex($hex);
    			break;
    		case 4:
    			$color = dechex($hex).dechex($hex)."0";
    			break;
    	}
    	if ($count < 3) {
    		$count++;
    	} else {
    		$count = 0;
    		$hex--;
    	}
    	echo '"#'.$color.'"';
    	$index++;
	} } ?>]
  },

  "data": [
    ["d(w)"<?php foreach ($day_list as $day) {
    	echo ',"'.format($day['date'],'d').'('.week($day['date']).')"';
    } ?>]
    <?php $index=0; if ($list) { foreach ($list as $row) { if ($index > 0) {
    	$cost = 0;
    	$name = '';
    	$name .= (isset($row['project_name']) ? e($row['project_name']).' ' : '');
    	$name .= (isset($row['work_name']) ? e($row['work_name']).' ' : '');
    	$name .= (isset($row['customer_name']) ? e($row['customer_name']).' ' : '');
    	$name .= (isset($row['process_name']) ? e($row['process_name']).' ' : '');
    	$name .= (isset($row['action']) ? e($row['action']).' ' : '');
    	echo ',["'.$index.'.'.trim($name).'"';
    	foreach ($day_list as $day) {
    		$cost += $row[$day['date']]['costin'] + $row[$day['date']]['costout'];
    		echo ','.$cost;
    	}
    	echo ']';
    } $index++; } } ?>
  ]
};
//画面表示後の処理
function attempt_focus(){
	setTimeout(function(){
		try{
			//データがあればグラフ表示
		    <?php if ($list) { ?>
			ccchart.init('graph', chartdata2);
			<?php } ?>
		} catch(e){}
	}, 1000); //集計表の表示でグラフがずれて見えるため少し遅らせて表示
	setTimeout(function(){
		try{
			//アラートメッセージがあれば表示
			<?php if (isset($_SESSION[S_MESSAGE]) && strlen($_SESSION[S_MESSAGE])) { ?>
			alert_dialog("<?php echo $_SESSION[S_MESSAGE]; ?>");
			$(".ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset button").focus();
			<?php $_SESSION[S_MESSAGE] = ''; ?>
			<?php } ?>
		} catch(e){}
	}, 1000);
}
attempt_focus();
</script>

</body>
</html>
