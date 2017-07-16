<?php require_once(dirname(__FILE__).'/myauth.php'); ?>
<?php require_once(dirname(__FILE__).'/../define.php'); ?>
<?php if (($auth->getParent_role_user() & ROLE_MEMBER_VIW) == 0) { header("HTTP/1.0 404 Not Found"); exit(); } ?>
<?php require_once(dirname(__FILE__).'/../Encode.php'); ?>
<?php require_once(dirname(__FILE__).'/../db_config.php'); ?>
<?php require_once(dirname(__FILE__).'/class/UserListSQL.class.php'); ?>
<?php require_once(dirname(__FILE__).'/class/RoleListSQL.class.php'); ?>

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
<title><?php echo APP_TITLE; ?> | メンバー</title>
<meta name="viewport" content="width=980px">
<link rel="shortcut icon" href="<?php echo PROJECT_ROOT ?>/favicon.ico">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/style.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/redmond/jquery-ui-1.8.20.custom.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/login/css/user_list.css" media="all">

<script src="<?php echo PROJECT_ROOT ?>/js/jquery-1.7.2.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery-ui-1.8.20.custom.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery.bgiframe.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/alert_dialog.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/cj/js/common.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/login/js/user_list.js"></script>

</head>
<body>

<?php require_once(dirname(__FILE__).'/../header.php'); ?>

<?php
	$user = new UserListSQL($dbopts);
	$list = $user->getUserList();

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

		<form id="edit_form" method="post" action="user_list_control.php">
			<input type="hidden" name="user_edit_type" id="user_edit_type" value="" />
			<input type="hidden" name="user_edit_uid" id="user_edit_uid" value="" />
		</form>

		<input type="hidden" id="edit_okcancel" value="" />

		<?php if ($auth->getParent_role_user() & ROLE_MEMBER_ADD) { ?>
			<a href="#" onclick="edit_invite()">招待する</a>
		<?php } ?>
		<?php if ($auth->getParent_role_user() & ROLE_MEMBER_ADD) { ?>
			<a href="#" onclick="edit_add()">追加する</a>
		<?php } ?>

		<table id="list">
			<tr>
				<th>ユーザー名</th><th>名前</th><th>メールアドレス</th><th>権限</th><th></th>
			</tr>

			<?php if ($list) { foreach ($list as $row) { ?>
				<tr class="tr_item">
					<?php if (isset($_SESSION[S_USER_EDIT_UID]) && ($_SESSION[S_USER_EDIT_UID] == $row['uid'])) { //編集モード ?>

						<td class="td_uid">
							<?php if ($row['member_status'] == GROUP_MEMBER_STATUS_ENTRY) { ?>
								（招待中）
							<?php } ?>
							<?php if ($row['status'] != USER_STATUS_ENTRY) { ?>
								<?php echo e($row['uid']); ?>
							<?php } ?>
							<input type="hidden" class="status" value="<?php echo $row['status']; ?>" />
						</td>
						<td class="td_name">
							<?php if ($row['member_status'] != GROUP_MEMBER_STATUS_ENTRY) { ?>
								<?php echo e($row['name']); ?>
							<?php } ?>
						</td>
						<td class="td_mail">
							<?php echo e($row['mail']); ?>
						</td>
						<td class="td_role">
							<select id="edit_role_update" class="text ui-widget-content ui-corner-all">
							<option value="">選択してください</option>
							<?php if ($role_list) { foreach ($role_list as $role_row) { ?>
								<option value="<?php echo e($role_row['no']); ?>" <?php echo ($role_row['no'] == $row['role_no']) ? 'selected' : ''; ?>><?php echo e($role_row['name']); ?></option>
							<?php } } ?>
							</select>
						</td>
						<td class="td_ope" nowrap>
							<?php if ($auth->getParent_role_user() & ROLE_MEMBER_EDT) { ?>
								<a href="#" onclick="edit_update('<?php echo e($row['uid']); ?>')">更新</a>
							<?php } ?>
							<a href="#" onclick="edit_cancel()">キャンセル</a>
						</td>

					<?php } else { //閲覧モード ?>

						<td class="td_uid">
							<?php if ($row['member_status'] == GROUP_MEMBER_STATUS_ENTRY) { ?>
								（招待中）
							<?php } ?>
							<?php if ($row['status'] != USER_STATUS_ENTRY) { ?>
								<?php echo e($row['uid']); ?>
							<?php } ?>
							<input type="hidden" class="status" value="<?php echo $row['status']; ?>" />
						</td>
						<td class="td_name">
							<?php if ($row['member_status'] != GROUP_MEMBER_STATUS_ENTRY) { ?>
								<?php echo e($row['name']); ?>
							<?php } ?>
						</td>
						<td class="td_mail">
							<?php echo e($row['mail']); ?>
						</td>
						<td class="td_role">
							<?php echo e($row['role_name']); ?>
						</td>
						<td class="td_ope" nowrap>
							<?php if (!isset($_SESSION[S_USER_EDIT_UID])) { ?>
								<?php if ($auth->getParent_role_user() & ROLE_MEMBER_EDT) { ?>
									<a href="#" onclick="edit_update_start('<?php echo e($row['uid']); ?>')">編集</a>
								<?php } ?>
								<?php if (($auth->getParent_role_user() & ROLE_MEMBER_DEL) && ($auth->getParent_uid() != $row['uid'])) { ?>
									<a href="#" onclick="edit_delete('<?php echo e($row['uid']); ?>',this)">削除</a>
								<?php } ?>
							<?php } ?>
						</td>

					<?php } ?>
				</tr>
			<?php } } ?>
		</table>

	</div>
</div>
</div>

<?php require_once(dirname(__FILE__).'/../footer.php'); ?>

<div id="okcancel_dialog" title="確認" style="display:none">
	<label class="label"></label>
</div>

<div id="alert_dialog" title="確認" style="display:none">
	<label class="label"></label>
</div>

<div id="edit_dialog" title="変更" style="display:none">
	<p id="validateTips"></p>
	<table>
	<tr class="tr_uid">
		<td>
			<label for="edit_uid" >ユーザー名</label>
			<input type="text" name="edit_uid" id="edit_uid">
		</td>
	</tr>
	<tr class="tr_name">
		<td>
			<label for="edit_name" >名前</label>
			<input type="text" name="edit_name" id="edit_name">
		</td>
	</tr>
	<tr class="tr_mail">
		<td>
			<label for="edit_mail" >メールアドレス</label>
			<input type="text" name="edit_mail" id="edit_mail">
		</td>
	</tr>
	<tr class="tr_passwd">
		<td>
			<label for="edit_passwd" >パスワード</label>
			<input type="text" name="edit_passwd" id="edit_passwd">
		</td>
	</tr>
	<tr class="tr_role">
		<td>
			<label for="edit_role" >権限</label>
			<select id="edit_role">
			<?php if ($role_list) { foreach ($role_list as $role_row) { ?>
				<option value="<?php echo e($role_row['no']); ?>" <?php echo ($role_row['no'] == 2) ? 'selected' : ''; ?>><?php echo e($role_row['name']); ?></option>
			<?php } } ?>
			</select>
		</td>
	</tr>
	</table>
</div>

<script>
function attempt_focus(){
	// 画面表示後の処理
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
</script>

</body>
</html>
