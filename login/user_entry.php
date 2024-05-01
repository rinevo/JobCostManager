<?php require_once(dirname(__FILE__).'/../define.php'); ?>
<?php require_once(dirname(__FILE__).'/../Encode.php'); ?>
<?php
// アクセスログ
require_once(dirname(__FILE__).'/../db_config.php');
require_once(dirname(__FILE__).'/../class/AccessLogSQL.class.php');
$log = new AccessLogSQL($GLOBALS['dbopts']);
$log->Write(0, '', __FILE__, __FUNCTION__, __LINE__);
?>
<?php
// パラメータ確認
require_once(dirname(__FILE__).'/class/UserListSQL.class.php');
$edit = new UserListSQL($dbopts);

$uid = isset($_GET['uid']) ? $_GET['uid'] : '';
$param = isset($_GET['param']) ? $_GET['param'] : '';

if ((strlen($uid) > 0) && (strlen($param) > 0)) {
	$userinfo = $edit->getUserInfo($uid);
}

if (!isset($userinfo['status']) || ($userinfo['status'] != USER_STATUS_ENTRY) || ($userinfo['param'] != $param)) {
	header("HTTP/1.0 404 Not Found");
	exit();
}

session_start();
?>
<!DOCTYPE html>
<html>
<head>

<meta charset="utf-8">
<title><?php echo APP_TITLE; ?> | 新規ユーザー登録</title>
<meta name="viewport" content="width=980px">

<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/style.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/redmond/jquery-ui-1.8.20.custom.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/login/css/user_entry.css" media="all">

<script src="<?php echo PROJECT_ROOT ?>/login/js/sha256-min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery-1.7.2.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery-ui-1.8.20.custom.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery.bgiframe.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/alert_dialog.js"></script>

<script>
function userentry(p) {
	var btn = document.getElementById('user_edit_submit');
	btn.disabled = true;
	return true;
}
</script>

</head>
<body>

<?php $GLOBALS['header_arg'] = false; ?>
<?php require(dirname(__FILE__).'/../header.php'); ?>

<div id="content" class="clearfix">
	<div class="inner">

	<div id="annai">
		<div class="hyoudai">
			右のフォームでユーザーの情報を入力してください。<br>
			「登録する」ボタンを押すと、下の利用規約を承諾したことになります。
		</div>
		<div class="setumei">
		</div>
		<br>
		<div class="waku">
			<?php require_once(dirname(__FILE__).'/../riyoukiyaku.php'); ?>
		</div>
	</div>

	<div id="user_edit">
	<h1>新規ユーザー登録 (無料)</h1>
	<form name="user_edit_form" id="user_edit_form" action="<?php echo PROJECT_ROOT ?>/login/user_reg.php" onSubmit="return userentry(this);" method="post">
		<div>
			<p>
				<label for="user_edit_mail">メールアドレス<br />
				<input type="text" name="user_edit_mail" id="user_edit_mail" size="20"
					value="<?php echo e($userinfo['mail']); ?>" disabled style="background: #eeeeee;"/></label>
			</p>
			<p>
				<label for="login-user">ログイン ユーザー名<br />
				<input type="text" name="user_edit_newuid" id="user_edit_newuid" value="" size="20" /></label>
			</p>
			<p>
				<label for="login-pass">ログイン パスワード<br />
				<input type="password" name="user_edit_passwd" id="user_edit_passwd" value="" size="20" /></label>
			</p>
			<p>
				<label for="login-user">名前<br />
				<input type="text" name="user_edit_name" id="user_edit_newuid" value="" size="20" /></label>
			</p>
			<p class="submit">
				<input type="submit" name="user_edit_submit" id="user_edit_submit" value="登録する" />
			</p>
		</div>
		<input type="hidden" name="user_edit_uid" value="<?php echo e($userinfo['uid']); ?>" />
		<br>
		<div class="setumei">
			「登録する」ボタンを押すと、入力された情報でユーザー登録が完了します。
		</div>
	</form>
	</div>

	</div>
</div>

<?php require(dirname(__FILE__).'/../footer.php'); ?>

<div id="alert_dialog" title="確認" style="display:none">
	<label class="label"></label>
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

			// ユーザー名にフォーカスをあてる
			var d;
			d = document.getElementById('user_edit_newuid');
			d.focus();
			d.select();

		} catch(e){}
	}, 200);
}
attempt_focus();
</script>

</body>
</html>
