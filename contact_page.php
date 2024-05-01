<?php require_once(dirname(__FILE__).'/define.php'); ?>
<?php require_once(dirname(__FILE__).'/Encode.php'); ?>
<?php require_once(dirname(__FILE__).'/db_config.php'); ?>
<?php
// アクセスログ
require_once(dirname(__FILE__).'/class/AccessLogSQL.class.php');
$log = new AccessLogSQL($GLOBALS['dbopts']);
$log->Write(0, '', __FILE__, __FUNCTION__, __LINE__);
$list = $log->getList();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo APP_TITLE; ?> | お問い合わせ</title>
<meta name="viewport" content="width=980px">
<link rel="shortcut icon" href="<?php echo PROJECT_ROOT ?>/favicon.ico">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/style.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/green/jquery-ui-1.9.2.custom.min.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/contact_page.css" media="all">

<script src="<?php echo PROJECT_ROOT ?>/js/jquery-1.7.2.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery-ui-1.8.20.custom.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery.bgiframe.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/alert_dialog.js"></script>
<?php
session_start();
$_SESSION[S_TOKEN] = md5(uniqid());
?>
</head>
<body>

<?php $GLOBALS['header_arg'] = false; ?>
<?php require_once(dirname(__FILE__).'/header.php'); ?>

<div id="content" class="clearfix">
<div class="inner">

	<div id="div_contact">
		<form id="contact_form" method="post" action="<?php echo PROJECT_ROOT ?>/contact_control.php">
			<div class="setumei">
				当サイトに関することなど、お問い合わせは下のフォームからお願い致します。<br>
				「送信する」ボタンを押すと、当サイト管理者に記入内容をメール送信します。
			</div>
			<input type="hidden" name="token" id="token" value="<?php echo $_SESSION[S_TOKEN]; ?>" />
			<table>
				<tr>
					<th>お名前</th>
					<td><input type="text" name="name" id="name"></td>
				</tr>
				<tr>
					<th>メールアドレス</th>
					<td><input type="text" name="mail" id="mail"></td>
				</tr>
				<tr>
					<th>お問い合わせ内容</th>
					<td><textarea name="detail" id="detail" cols="80" rows="5"></textarea></td>
				</tr>
				<tr class="tr_submit">
					<td colspan="2">
						<input type="submit" name="form_submit" id="form_submit" value="送信する" />
					</td>
				</tr>
			</table>
		</form>
	</div><!-- div_contact  -->

</div><!-- inner -->
</div><!-- content -->

<?php require_once(dirname(__FILE__).'/footer.php'); ?>

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

		} catch(e){}
	}, 200);
}
attempt_focus();
</script>

</body>
</html>
