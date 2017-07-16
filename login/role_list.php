<?php require_once(dirname(__FILE__).'/myauth.php'); ?>
<?php require_once(dirname(__FILE__).'/../define.php'); ?>
<?php if (($auth->getParent_role_user() & ROLE_MEMBER_VIW) == 0) { header("HTTP/1.0 404 Not Found"); exit(); } ?>
<?php require_once(dirname(__FILE__).'/../Encode.php'); ?>
<?php require_once(dirname(__FILE__).'/../db_config.php'); ?>
<?php require_once(dirname(__FILE__).'/class/RoleListSQL.class.php'); ?>
<?php if (($auth->getParent_role_user() & ROLE_VIW) == 0) { header("HTTP/1.0 404 Not Found"); exit(); } ?>

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
<title><?php echo APP_TITLE; ?> | 権限マスタ</title>
<meta name="viewport" content="width=980px">
<link rel="shortcut icon" href="<?php echo PROJECT_ROOT ?>/favicon.ico">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/style.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/redmond/jquery-ui-1.8.20.custom.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/login/css/role_list.css" media="all">

<script src="<?php echo PROJECT_ROOT ?>/js/jquery-1.7.2.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery-ui-1.8.20.custom.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery.bgiframe.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/alert_dialog.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/cj/js/common.js"></script>

</head>
<body>

<?php require_once(dirname(__FILE__).'/../header.php'); ?>

<?php
	//権限リスト
	$role = new RoleListSQL($dbopts);
	$role_list = $role->getList();
?>

<div id="content" class="clearfix">
<div class="inner">

<?php if (isset($_SESSION[S_MESSAGE2]) && strlen($_SESSION[S_MESSAGE2])) {
	echo '<div class="info">'.$_SESSION[S_MESSAGE2].'</div>';
	$_SESSION[S_MESSAGE2] = '';
} ?>

	<div class="item_list">

		<form id="post_form" method="post" action="role_list_control.php" >
			<input type="hidden" name="post_type" id="post_type" value="" />
			<input type="hidden" name="post_no" id="post_no" value="" />
			<input type="hidden" name="post_sortno" id="post_sortno" value="" />
			<input type="hidden" name="post_sortno_after" id="post_sortno_after" value="" />
			<input type="hidden" name="post_name" id="post_name" value="" />
			<input type="hidden" name="post_user" id="post_user" value="" />
			<input type="hidden" name="post_work" id="post_work" value="" />
			<input type="hidden" name="post_customer" id="post_customer" value="" />
			<input type="hidden" name="post_process" id="post_process" value="" />
			<input type="hidden" name="post_todo" id="post_todo" value="" />
			<input type="hidden" name="post_schedule" id="post_schedule" value="" />
			<input type="hidden" name="post_cost" id="post_cost" value="" />
			<input type="hidden" name="post_kintai" id="post_kintai" value="" />
		</form>

		<table id="list">
			<thead>
			<tr>
				<th>権限</th><th>メンバー</th><th>作業マスタ</th><th>顧客マスタ</th><th>工程マスタ</th><th>TODO</th><th>行動記録</th><th>工数表</th><th>勤怠</th><th></th>
			</tr>
			</thead>
			<tbody>
			<?php if ($role_list) { foreach ($role_list as $row) { ?>
				<tr class="tr_item">
					<td class="td_name">
						<?php echo e($row['name']); ?>
					</td>
					<td class="td_user">
						<?php
						$attr_list = $role->getAttributeName($row['user']);
						if ($row['user'] & 0xF) {
							echo '<label>自';
							foreach ($attr_list as $attr_row) {
								if ($attr_row['attr'] & 0xF) {
									echo e($attr_row['name']);
								}
							}
							echo '</label>';
						}
						if ($row['user'] & 0xF0) {
							echo '<label>他';
							foreach ($attr_list as $attr_row) {
								if ($attr_row['attr'] & 0xF0) {
									echo e($attr_row['name']);
								}
							}
							echo '</label>';
						}
						?>
					</td>
					<td class="td_work">
						<?php
						$attr_list = $role->getAttributeName($row['work']);
						if ($row['work'] & 0xF) {
							echo '<label>自';
							foreach ($attr_list as $attr_row) {
								if ($attr_row['attr'] & 0xF) {
									echo e($attr_row['name']);
								}
							}
							echo '</label>';
						}
						if ($row['work'] & 0xF0) {
							echo '<label>他';
							foreach ($attr_list as $attr_row) {
								if ($attr_row['attr'] & 0xF0) {
									echo e($attr_row['name']);
								}
							}
							echo '</label>';
						}
						?>
					</td>
					<td class="td_customer">
						<?php
						$attr_list = $role->getAttributeName($row['customer']);
						if ($row['customer'] & 0xF) {
							echo '<label>自';
							foreach ($attr_list as $attr_row) {
								if ($attr_row['attr'] & 0xF) {
									echo e($attr_row['name']);
								}
							}
							echo '</label>';
						}
						if ($row['customer'] & 0xF0) {
							echo '<label>他';
							foreach ($attr_list as $attr_row) {
								if ($attr_row['attr'] & 0xF0) {
									echo e($attr_row['name']);
								}
							}
							echo '</label>';
						}
						?>
					</td>
					<td class="td_process">
						<?php
						$attr_list = $role->getAttributeName($row['process']);
						if ($row['process'] & 0xF) {
							echo '<label>自';
							foreach ($attr_list as $attr_row) {
								if ($attr_row['attr'] & 0xF) {
									echo e($attr_row['name']);
								}
							}
							echo '</label>';
						}
						if ($row['process'] & 0xF0) {
							echo '<label>他';
							foreach ($attr_list as $attr_row) {
								if ($attr_row['attr'] & 0xF0) {
									echo e($attr_row['name']);
								}
							}
							echo '</label>';
						}
						?>
					</td>
					<td class="td_todo">
						<?php
						$attr_list = $role->getAttributeName($row['todo']);
						if ($row['todo'] & 0xF) {
							echo '<label>自';
							foreach ($attr_list as $attr_row) {
								if ($attr_row['attr'] & 0xF) {
									echo e($attr_row['name']);
								}
							}
							echo '</label>';
						}
						if ($row['todo'] & 0xF0) {
							echo '<label>他';
							foreach ($attr_list as $attr_row) {
								if ($attr_row['attr'] & 0xF0) {
									echo e($attr_row['name']);
								}
							}
							echo '</label>';
						}
						?>
					</td>
					<td class="td_schedule">
						<?php
						$attr_list = $role->getAttributeName($row['schedule']);
						if ($row['schedule'] & 0xF) {
							echo '<label>自';
							foreach ($attr_list as $attr_row) {
								if ($attr_row['attr'] & 0xF) {
									echo e($attr_row['name']);
								}
							}
							echo '</label>';
						}
						if ($row['schedule'] & 0xF0) {
							echo '<label>他';
							foreach ($attr_list as $attr_row) {
								if ($attr_row['attr'] & 0xF0) {
									echo e($attr_row['name']);
								}
							}
							echo '</label>';
						}
						?>
					</td>
					<td class="td_cost">
						<?php
						$attr_list = $role->getAttributeName($row['cost']);
						if ($row['cost'] & 0xF) {
							echo '<label>自';
							foreach ($attr_list as $attr_row) {
								if ($attr_row['attr'] & 0xF) {
									echo e($attr_row['name']);
								}
							}
							echo '</label>';
						}
						if ($row['cost'] & 0xF0) {
							echo '<label>他';
							foreach ($attr_list as $attr_row) {
								if ($attr_row['attr'] & 0xF0) {
									echo e($attr_row['name']);
								}
							}
							echo '</label>';
						}
						?>
					</td>
					<td class="td_kintai">
						<?php
						$attr_list = $role->getAttributeName($row['kintai']);
						if ($row['kintai'] & 0xF) {
							echo '<label>自';
							foreach ($attr_list as $attr_row) {
								if ($attr_row['attr'] & 0xF) {
									echo e($attr_row['name']);
								}
							}
							echo '</label>';
						}
						if ($row['kintai'] & 0xF0) {
							echo '<label>他';
							foreach ($attr_list as $attr_row) {
								if ($attr_row['attr'] & 0xF0) {
									echo e($attr_row['name']);
								}
							}
							echo '</label>';
						}
						?>
					</td>
					<td class="td_ope">
						<input type="hidden" class="no" value="<?php echo $row['no']; ?>" />
						<input type="hidden" class="name" value="<?php echo $row['name']; ?>" />
						<input type="hidden" class="sortno" value="<?php echo $row['sortno']; ?>" />
						<input type="hidden" class="user" value="<?php echo $row['user']; ?>" />
						<input type="hidden" class="work" value="<?php echo $row['work']; ?>" />
						<input type="hidden" class="customer" value="<?php echo $row['customer']; ?>" />
						<input type="hidden" class="process" value="<?php echo $row['process']; ?>" />
						<input type="hidden" class="todo" value="<?php echo $row['todo']; ?>" />
						<input type="hidden" class="schedule" value="<?php echo $row['schedule']; ?>" />
						<input type="hidden" class="cost" value="<?php echo $row['cost']; ?>" />
						<input type="hidden" class="kintai" value="<?php echo $row['kintai']; ?>" />
						<?php if (($auth->getParent_role_user() & ROLE_MEMBER_EDT) != 0) { ?>
							<input type="button" value="▲" class="btn_up" onclick="upRow(this)"/>
							<input type="button" value="▼" class="btn_dw" onclick="dwRow(this)"/>
							<input type="button" value="編集" class="btn_edit" onclick="updateRow(this)"/>
						<?php } ?>
						<?php if (($auth->getParent_role_user() & ROLE_MEMBER_ADD) != 0) { ?>
							<input type="button" value="追加" class="btn_add" onclick="insertRow(this)"/>
						<?php } ?>
						<?php if (($auth->getParent_role_user() & ROLE_MEMBER_DEL) != 0) { ?>
							<input type="button" value="削除" class="btn_del" onclick="deleteRow(this)"/>
						<?php } ?>
					</td>
				</tr>
			<?php }} ?>
			</tbody>
		</table>

	</div><!-- item_list -->
</div><!-- inner -->
</div><!-- content -->

<?php require_once(dirname(__FILE__).'/../footer.php'); ?>

<div id="edit_dialog" title="変更" style="display:none;">
<p id="validateTips"></p>
<table>
<tr>
	<td>
		<label id="label-editstr" ></label>
		<input type="text" name="editstr" id="editstr"/>
		<label id="editstr-disabled"></label>
	</td>
</tr>
<tr class="tr_label">
	<td class="td_label"><label>メンバー</label></td>
</tr>
<tr class="tr_attr">
	<td>
		<div>
		<label class="lbl_item">ユーザー自身のデータ</label>
		<input type="checkbox" id="chk_user_viw" value="<?php echo ROLE_VIW; ?>"><label for="chk_user_viw">閲覧</label>
		<input type="checkbox" id="chk_user_edt" value="<?php echo ROLE_EDT; ?>"><label for="chk_user_edt">編集</label>
		<input type="checkbox" id="chk_user_add" value="<?php echo ROLE_ADD; ?>"><label for="chk_user_add">追加</label>
		<input type="checkbox" id="chk_user_del" value="<?php echo ROLE_DEL; ?>"><label for="chk_user_del">削除</label>
		</div>
	</td>
</tr>
<tr class="tr_attr">
	<td>
		<label class="lbl_item">他ユーザーのデータ</label>
		<input type="checkbox" id="chk_user_member_viw" value="<?php echo ROLE_MEMBER_VIW; ?>"><label for="chk_user_member_viw">閲覧</label>
		<input type="checkbox" id="chk_user_member_edt" value="<?php echo ROLE_MEMBER_EDT; ?>"><label for="chk_user_member_edt">編集</label>
		<input type="checkbox" id="chk_user_member_add" value="<?php echo ROLE_MEMBER_ADD; ?>"><label for="chk_user_member_add">追加</label>
		<input type="checkbox" id="chk_user_member_del" value="<?php echo ROLE_MEMBER_DEL; ?>"><label for="chk_user_member_del">削除</label>
	</td>
</tr>
<tr class="tr_label tr_master">
	<td class="td_label"><label>作業マスタ</label></td>
</tr>
<tr class="tr_attr">
	<td>
		<div>
		<label class="lbl_item">ユーザー自身のデータ</label>
		<input type="checkbox" id="chk_work_viw" value="<?php echo ROLE_VIW; ?>"><label for="chk_work_viw">閲覧</label>
		<input type="checkbox" id="chk_work_edt" value="<?php echo ROLE_EDT; ?>"><label for="chk_work_edt">編集</label>
		<input type="checkbox" id="chk_work_add" value="<?php echo ROLE_ADD; ?>"><label for="chk_work_add">追加</label>
		<input type="checkbox" id="chk_work_del" value="<?php echo ROLE_DEL; ?>"><label for="chk_work_del">削除</label>
		</div>
	</td>
</tr>
<tr class="tr_attr">
	<td>
		<label class="lbl_item">他ユーザーのデータ</label>
		<input type="checkbox" id="chk_work_member_viw" value="<?php echo ROLE_MEMBER_VIW; ?>"><label for="chk_work_member_viw">閲覧</label>
		<input type="checkbox" id="chk_work_member_edt" value="<?php echo ROLE_MEMBER_EDT; ?>"><label for="chk_work_member_edt">編集</label>
		<input type="checkbox" id="chk_work_member_add" value="<?php echo ROLE_MEMBER_ADD; ?>"><label for="chk_work_member_add">追加</label>
		<input type="checkbox" id="chk_work_member_del" value="<?php echo ROLE_MEMBER_DEL; ?>"><label for="chk_work_member_del">削除</label>
	</td>
</tr>
<tr class="tr_label tr_master">
	<td class="td_label"><label>顧客マスタ</label></td>
</tr>
<tr class="tr_attr">
	<td>
		<div>
		<label class="lbl_item">ユーザー自身のデータ</label>
		<input type="checkbox" id="chk_customer_viw" value="<?php echo ROLE_VIW; ?>"><label for="chk_customer_viw">閲覧</label>
		<input type="checkbox" id="chk_customer_edt" value="<?php echo ROLE_EDT; ?>"><label for="chk_customer_edt">編集</label>
		<input type="checkbox" id="chk_customer_add" value="<?php echo ROLE_ADD; ?>"><label for="chk_customer_add">追加</label>
		<input type="checkbox" id="chk_customer_del" value="<?php echo ROLE_DEL; ?>"><label for="chk_customer_del">削除</label>
		</div>
	</td>
</tr>
<tr class="tr_attr">
	<td>
		<label class="lbl_item">他ユーザーのデータ</label>
		<input type="checkbox" id="chk_customer_member_viw" value="<?php echo ROLE_MEMBER_VIW; ?>"><label for="chk_customer_member_viw">閲覧</label>
		<input type="checkbox" id="chk_customer_member_edt" value="<?php echo ROLE_MEMBER_EDT; ?>"><label for="chk_customer_member_edt">編集</label>
		<input type="checkbox" id="chk_customer_member_add" value="<?php echo ROLE_MEMBER_ADD; ?>"><label for="chk_customer_member_add">追加</label>
		<input type="checkbox" id="chk_customer_member_del" value="<?php echo ROLE_MEMBER_DEL; ?>"><label for="chk_customer_member_del">削除</label>
	</td>
</tr>
<tr class="tr_label tr_master">
	<td class="td_label tr_master"><label>工程マスタ</label></td>
</tr>
<tr class="tr_attr">
	<td>
		<div>
		<label class="lbl_item">ユーザー自身のデータ</label>
		<input type="checkbox" id="chk_process_viw" value="<?php echo ROLE_VIW; ?>"><label for="chk_process_viw">閲覧</label>
		<input type="checkbox" id="chk_process_edt" value="<?php echo ROLE_EDT; ?>"><label for="chk_process_edt">編集</label>
		<input type="checkbox" id="chk_process_add" value="<?php echo ROLE_ADD; ?>"><label for="chk_process_add">追加</label>
		<input type="checkbox" id="chk_process_del" value="<?php echo ROLE_DEL; ?>"><label for="chk_process_del">削除</label>
		</div>
	</td>
</tr>
<tr class="tr_attr">
	<td>
		<label class="lbl_item">他ユーザーのデータ</label>
		<input type="checkbox" id="chk_process_member_viw" value="<?php echo ROLE_MEMBER_VIW; ?>"><label for="chk_process_member_viw">閲覧</label>
		<input type="checkbox" id="chk_process_member_edt" value="<?php echo ROLE_MEMBER_EDT; ?>"><label for="chk_process_member_edt">編集</label>
		<input type="checkbox" id="chk_process_member_add" value="<?php echo ROLE_MEMBER_ADD; ?>"><label for="chk_process_member_add">追加</label>
		<input type="checkbox" id="chk_process_member_del" value="<?php echo ROLE_MEMBER_DEL; ?>"><label for="chk_process_member_del">削除</label>
	</td>
</tr>
<tr class="tr_label tr_cj">
	<td class="td_label"><label>TODO</label></td>
</tr>
<tr class="tr_attr">
	<td>
		<div>
		<label class="lbl_item">ユーザー自身のデータ</label>
		<input type="checkbox" id="chk_todo_viw" value="<?php echo ROLE_VIW; ?>"><label for="chk_todo_viw">閲覧</label>
		<input type="checkbox" id="chk_todo_edt" value="<?php echo ROLE_EDT; ?>"><label for="chk_todo_edt">編集</label>
		<input type="checkbox" id="chk_todo_add" value="<?php echo ROLE_ADD; ?>"><label for="chk_todo_add">追加</label>
		<input type="checkbox" id="chk_todo_del" value="<?php echo ROLE_DEL; ?>"><label for="chk_todo_del">削除</label>
		</div>
	</td>
</tr>
<tr class="tr_attr">
	<td>
		<label class="lbl_item">他ユーザーのデータ</label>
		<input type="checkbox" id="chk_todo_member_viw" value="<?php echo ROLE_MEMBER_VIW; ?>"><label for="chk_todo_member_viw">閲覧</label>
		<input type="checkbox" id="chk_todo_member_edt" value="<?php echo ROLE_MEMBER_EDT; ?>"><label for="chk_todo_member_edt">編集</label>
		<input type="checkbox" id="chk_todo_member_add" value="<?php echo ROLE_MEMBER_ADD; ?>"><label for="chk_todo_member_add">追加</label>
		<input type="checkbox" id="chk_todo_member_del" value="<?php echo ROLE_MEMBER_DEL; ?>"><label for="chk_todo_member_del">削除</label>
	</td>
</tr>
<tr class="tr_label tr_cj">
	<td class="td_label"><label>行動記録</label></td>
</tr>
<tr class="tr_attr">
	<td>
		<div>
		<label class="lbl_item">ユーザー自身のデータ</label>
		<input type="checkbox" id="chk_schedule_viw" value="<?php echo ROLE_VIW; ?>"><label for="chk_schedule_viw">閲覧</label>
		<input type="checkbox" id="chk_schedule_edt" value="<?php echo ROLE_EDT; ?>"><label for="chk_schedule_edt">編集</label>
		<input type="checkbox" id="chk_schedule_add" value="<?php echo ROLE_ADD; ?>"><label for="chk_schedule_add">追加</label>
		<input type="checkbox" id="chk_schedule_del" value="<?php echo ROLE_DEL; ?>"><label for="chk_schedule_del">削除</label>
		</div>
	</td>
</tr>
<tr class="tr_attr">
	<td>
		<label class="lbl_item">他ユーザーのデータ</label>
		<input type="checkbox" id="chk_schedule_member_viw" value="<?php echo ROLE_MEMBER_VIW; ?>"><label for="chk_schedule_member_viw">閲覧</label>
		<input type="checkbox" id="chk_schedule_member_edt" value="<?php echo ROLE_MEMBER_EDT; ?>"><label for="chk_schedule_member_edt">編集</label>
		<input type="checkbox" id="chk_schedule_member_add" value="<?php echo ROLE_MEMBER_ADD; ?>"><label for="chk_schedule_member_add">追加</label>
		<input type="checkbox" id="chk_schedule_member_del" value="<?php echo ROLE_MEMBER_DEL; ?>"><label for="chk_schedule_member_del">削除</label>
	</td>
</tr>
<tr class="tr_label tr_cj">
	<td class="td_label"><label>工数表</label></td>
</tr>
<tr class="tr_attr">
	<td>
		<div>
		<label class="lbl_item">ユーザー自身のデータ</label>
		<input type="checkbox" id="chk_cost_viw" value="<?php echo ROLE_VIW; ?>"><label for="chk_cost_viw">閲覧</label>
		<input type="checkbox" id="chk_cost_edt" value="<?php echo ROLE_EDT; ?>"><label for="chk_cost_edt">編集</label>
		<input type="checkbox" id="chk_cost_add" value="<?php echo ROLE_ADD; ?>"><label for="chk_cost_add">追加</label>
		<input type="checkbox" id="chk_cost_del" value="<?php echo ROLE_DEL; ?>"><label for="chk_cost_del">削除</label>
		</div>
	</td>
</tr>
<tr class="tr_attr">
	<td>
		<label class="lbl_item">他ユーザーのデータ</label>
		<input type="checkbox" id="chk_cost_member_viw" value="<?php echo ROLE_MEMBER_VIW; ?>"><label for="chk_cost_member_viw">閲覧</label>
		<input type="checkbox" id="chk_cost_member_edt" value="<?php echo ROLE_MEMBER_EDT; ?>"><label for="chk_cost_member_edt">編集</label>
		<input type="checkbox" id="chk_cost_member_add" value="<?php echo ROLE_MEMBER_ADD; ?>"><label for="chk_cost_member_add">追加</label>
		<input type="checkbox" id="chk_cost_member_del" value="<?php echo ROLE_MEMBER_DEL; ?>"><label for="chk_cost_member_del">削除</label>
	</td>
</tr>
<tr class="tr_label tr_cj">
	<td class="td_label"><label>勤怠</label></td>
</tr>
<tr class="tr_attr">
	<td>
		<div>
		<label class="lbl_item">ユーザー自身のデータ</label>
		<input type="checkbox" id="chk_kintai_viw" value="<?php echo ROLE_VIW; ?>"><label for="chk_kintai_viw">閲覧</label>
		<input type="checkbox" id="chk_kintai_edt" value="<?php echo ROLE_EDT; ?>"><label for="chk_kintai_edt">編集</label>
		<input type="checkbox" id="chk_kintai_add" value="<?php echo ROLE_ADD; ?>"><label for="chk_kintai_add">追加</label>
		<input type="checkbox" id="chk_kintai_del" value="<?php echo ROLE_DEL; ?>"><label for="chk_kintai_del">削除</label>
		</div>
	</td>
</tr>
<tr class="tr_attr">
	<td>
		<label class="lbl_item">他ユーザーのデータ</label>
		<input type="checkbox" id="chk_kintai_member_viw" value="<?php echo ROLE_MEMBER_VIW; ?>"><label for="chk_kintai_member_viw">閲覧</label>
		<input type="checkbox" id="chk_kintai_member_edt" value="<?php echo ROLE_MEMBER_EDT; ?>"><label for="chk_kintai_member_edt">編集</label>
		<input type="checkbox" id="chk_kintai_member_add" value="<?php echo ROLE_MEMBER_ADD; ?>"><label for="chk_kintai_member_add">追加</label>
		<input type="checkbox" id="chk_kintai_member_del" value="<?php echo ROLE_MEMBER_DEL; ?>"><label for="chk_kintai_member_del">削除</label>
	</td>
</tr>
</table>
</div>

<div id="alert_dialog" title="確認" style="display:none">
	<label class="label"></label>
</div>

<script>
//画面表示後の処理
function attempt_focus(){
	setTimeout(function(){
		try{

			// アラートメッセージがあれば表示
			<?php if (isset($_SESSION[S_MESSAGE]) && strlen($_SESSION[S_MESSAGE])) { ?>
			alert_dialog("<?php echo $_SESSION[S_MESSAGE]; ?>");
			$(".ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset button").focus();
			<?php $_SESSION[S_MESSAGE] = ''; ?>
			<?php } ?>

		} catch(e){}
	}, 200);
}
attempt_focus();

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


	$('#editstr').keydown(function(e) {
		if (e.which == 13) {
			edit_ok();
		}
	});

});

function edit_ok() {

	if ($("#editstr").val() < 1) {
		$("#alert_dialog .label").html($('#ui-dialog-title-edit_dialog').html() + 'を入力してください。');
		$('#alert_dialog').dialog('open');
		return false;
	}

	switch ($("#post_type").val()) {
	case 'INSERT':
	case 'UPDATE':
		$('#post_name').val($("#editstr").val());
		break;
	}

	//メンバー
	setAttrPost('user');

	//作業マスタ
	setAttrPost('work');

	//顧客マスタ
	setAttrPost('customer');

	//工程マスタ
	setAttrPost('process');

	//TODO
	setAttrPost('todo');

	//行動記録
	setAttrPost('schedule');

	//工数表
	setAttrPost('cost');

	//勤怠
	setAttrPost('kintai');

	$("#edit_dialog").dialog('close');
	$('#post_form').submit();
}

function setAttrPost(name) {
	var attr = 0;

	//メンバー
	if ($('#chk_' + name + '_viw').attr('checked')) {
		attr += parseInt($('#chk_' + name + '_viw').val());
	}
	if ($('#chk_' + name + '_edt').attr('checked')) {
		attr += parseInt($('#chk_' + name + '_edt').val());
	}
	if ($('#chk_' + name + '_add').attr('checked')) {
		attr += parseInt($('#chk_' + name + '_add').val());
	}
	if ($('#chk_' + name + '_del').attr('checked')) {
		attr += parseInt($('#chk_' + name + '_del').val());
	}

	if ($('#chk_' + name + '_member_viw').attr('checked')) {
		attr += parseInt($('#chk_' + name + '_member_viw').val());
	}
	if ($('#chk_' + name + '_member_edt').attr('checked')) {
		attr += parseInt($('#chk_' + name + '_member_edt').val());
	}
	if ($('#chk_' + name + '_member_add').attr('checked')) {
		attr += parseInt($('#chk_' + name + '_member_add').val());
	}
	if ($('#chk_' + name + '_member_del').attr('checked')) {
		attr += parseInt($('#chk_' + name + '_member_del').val());
	}

	$('#post_' + name).val(attr);
}

function insertRow(obj) {

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;
	var no = getElementByClassName(tr,'no')[0].value;
	var name = getElementByClassName(tr,'name')[0].value;
	var sortno = getElementByClassName(tr,'sortno')[0].value;

	//選択行の情報を格納
	$('#post_no').val(no);
	$('#post_sortno').val(sortno);
	$('#post_sortno_after').val(sortno);
	$('#post_name').val(name);

	$('#edit_dialog .tr_attr input').removeAttr('disabled');

	//メンバー
	setAttrCheck(tr,'user');

	//作業マスタ
	setAttrCheck(tr,'work');

	//顧客マスタ
	setAttrCheck(tr,'customer');

	//工程マスタ
	setAttrCheck(tr,'process');

	//TODO
	setAttrCheck(tr,'todo');

	//行動記録
	setAttrCheck(tr,'schedule');

	//工数表
	setAttrCheck(tr,'cost');

	//勤怠
	setAttrCheck(tr,'kintai');

	//入力画面表示
	$("#post_type").val('INSERT');
	$("#editstr").val('');
	$('#editstr-disabled').html($("#editstr").val());
	$('#editstr').css('display','inline');
	$('#editstr-disabled').css('display','none');
	$('#validateTips').html('追加する顧客名を入力してください。');
	$('#validateTips').css('color','blue');
	$('#ui-dialog-title-edit_dialog').html('顧客追加');
	$('#label-editstr').html('顧客名');
	$('#edit_dialog').dialog('open');
}

function updateRow(obj) {

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;
	var no = getElementByClassName(tr,'no')[0].value;
	var name = getElementByClassName(tr,'name')[0].value;
	var sortno = getElementByClassName(tr,'sortno')[0].value;

	//選択行の情報を格納
	$('#post_no').val(no);
	$('#post_sortno').val(sortno);
	$('#post_sortno_after').val(sortno);
	$('#post_name').val(name);

	if (no <= 1) {
		$('#edit_dialog .tr_attr input').attr('disabled','disabled');
	} else {
		$('#edit_dialog .tr_attr input').removeAttr('disabled');
	}

	//メンバー
	setAttrCheck(tr,'user');

	//作業マスタ
	setAttrCheck(tr,'work');

	//顧客マスタ
	setAttrCheck(tr,'customer');

	//工程マスタ
	setAttrCheck(tr,'process');

	//TODO
	setAttrCheck(tr,'todo');

	//行動記録
	setAttrCheck(tr,'schedule');

	//工数表
	setAttrCheck(tr,'cost');

	//勤怠
	setAttrCheck(tr,'kintai');

	//入力画面表示
	$("#post_type").val('UPDATE');
	$("#editstr").val(name);
	$('#editstr-disabled').html($("#editstr").val());
	$('#editstr').css('display','inline');
	$('#editstr-disabled').css('display','none');
	$('#validateTips').html('権限を編集してください。');
	$('#validateTips').css('color','blue');
	$('#ui-dialog-title-edit_dialog').html('権限の編集');
	$('#label-editstr').html('権限名');
	$('#edit_dialog').dialog('open');
}

function setAttrCheck(tr,name) {
	var attr = getElementByClassName(tr,name)[0].value;

	if (attr & <?php echo ROLE_VIW; ?>) {
		$('#chk_' + name + '_viw').attr('checked','checked');
	} else {
		$('#chk_' + name + '_viw').removeAttr('checked');
	}
	if (attr & <?php echo ROLE_EDT; ?>) {
		$('#chk_' + name + '_edt').attr('checked','checked');
	} else {
		$('#chk_' + name + '_edt').removeAttr('checked');
	}
	if (attr & <?php echo ROLE_ADD; ?>) {
		$('#chk_' + name + '_add').attr('checked','checked');
	} else {
		$('#chk_' + name + '_add').removeAttr('checked');
	}
	if (attr & <?php echo ROLE_DEL; ?>) {
		$('#chk_' + name + '_del').attr('checked','checked');
	} else {
		$('#chk_' + name + '_del').removeAttr('checked');
	}

	if (attr & <?php echo ROLE_MEMBER_VIW; ?>) {
		$('#chk_' + name + '_member_viw').attr('checked','checked');
	} else {
		$('#chk_' + name + '_member_viw').removeAttr('checked');
	}
	if (attr & <?php echo ROLE_MEMBER_EDT; ?>) {
		$('#chk_' + name + '_member_edt').attr('checked','checked');
	} else {
		$('#chk_' + name + '_member_edt').removeAttr('checked');
	}
	if (attr & <?php echo ROLE_MEMBER_ADD; ?>) {
		$('#chk_' + name + '_member_add').attr('checked','checked');
	} else {
		$('#chk_' + name + '_member_add').removeAttr('checked');
	}
	if (attr & <?php echo ROLE_MEMBER_DEL; ?>) {
		$('#chk_' + name + '_member_del').attr('checked','checked');
	} else {
		$('#chk_' + name + '_member_del').removeAttr('checked');
	}
}

function deleteRow(obj) {

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;
	var no = getElementByClassName(tr,'no')[0].value;
	var name = getElementByClassName(tr,'name')[0].value;
	var sortno = getElementByClassName(tr,'sortno')[0].value;

	//選択行の情報を格納
	$('#post_no').val(no);
	$('#post_sortno').val(sortno);
	$('#post_sortno_after').val(sortno);
	$('#post_name').val(name);

	$('#edit_dialog .tr_attr input').attr('disabled','disabled');

	//メンバー
	setAttrCheck(tr,'user');

	//作業マスタ
	setAttrCheck(tr,'work');

	//顧客マスタ
	setAttrCheck(tr,'customer');

	//工程マスタ
	setAttrCheck(tr,'process');

	//TODO
	setAttrCheck(tr,'todo');

	//行動記録
	setAttrCheck(tr,'schedule');

	//工数表
	setAttrCheck(tr,'cost');

	//勤怠
	setAttrCheck(tr,'kintai');

	//入力画面表示
	$("#post_type").val('DELETE');
	$("#editstr").val(name);
	$('#editstr-disabled').html($("#editstr").val());
	$('#editstr').css('display','none');
	$('#editstr-disabled').css('display','inline');
	$('#validateTips').html('この権限を削除しますか？');
	$('#validateTips').css('color','red');
	$('#ui-dialog-title-edit_dialog').html('権限削除');
	$('#label-editstr').html('権限名');
	$('#edit_dialog').dialog('open');
}

function upRow(obj) {

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;
	var no = getElementByClassName(tr,'no')[0].value;
	var sortno = getElementByClassName(tr,'sortno')[0].value;

	//選択行の情報を格納
	$('#post_no').val(no);
	$('#post_sortno').val(sortno);
	$('#post_sortno_after').val(parseInt(sortno)-1);

	$("#post_type").val('MOVE');
	$('#post_form').submit();
}

function dwRow(obj) {

	//objの親の親のノード（tr）を取得
	var tr = obj.parentNode.parentNode;
	var no = getElementByClassName(tr,'no')[0].value;
	var sortno = getElementByClassName(tr,'sortno')[0].value;

	//選択行の情報を格納
	$('#post_no').val(no);
	$('#post_sortno').val(sortno);
	$('#post_sortno_after').val(parseInt(sortno)+1);

	$("#post_type").val('MOVE');
	$('#post_form').submit();
}
</script>

</body>
</html>
