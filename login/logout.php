<?php
require_once(dirname(__FILE__).'/../define.php');
require_once(dirname(__FILE__).'/../db_config.php');
require_once(dirname(__FILE__).'/class/Form_Digest_Auth.class.php');

// アクセスログ
require_once(dirname(__FILE__).'/../class/AccessLogSQL.class.php');
$log = new AccessLogSQL($GLOBALS['dbopts']);
$log->Write(0, '', __FILE__, __FUNCTION__, __LINE__);

// このページへ遷移する前にセッションをクリアしておく
// ここでは、Cookieの削除とログアウトメッセージの表示を行う
error_reporting(E_ALL & ~E_DEPRECATED);

global $auth;
global $dbopts;

if (!isset($auth)) {
	$auth = new Form_Digest_Auth($dbopts);
}

// メッセージ取得
$message = '';
if (isset($_SESSION[S_MESSAGE]) && strlen($_SESSION[S_MESSAGE])) {
	$message = $_SESSION[S_MESSAGE];
	$_SESSION[S_MESSAGE] = '';
}

// ログアウト
$auth->logout();
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="utf-8">
<title><?php echo APP_TITLE; ?> | ログアウト</title>
<meta name="viewport" content="width=980px">
<link rel="shortcut icon" href="<?php echo PROJECT_ROOT ?>/favicon.ico">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/style.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/login/css/logout.css" media="all">

</head>
<body>

<?php $GLOBALS['header_arg'] = false; ?>
<?php require(dirname(__FILE__).'/../header.php'); ?>

<div id="content" class="clearfix">
	<div class="inner">

		<h1 class="logout">ログアウトしました。</h1><br/>

		<div class="message">
			<?php echo $message; ?>
		</div>

		<div class="login">
			<a href="<?php echo PROJECT_ROOT.'/'; ?>">[トップページへ戻る]</a>
		</div>

	</div>
</div>

<?php require(dirname(__FILE__).'/../footer.php'); ?>

</body>
</html>
