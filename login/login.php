<?php require_once(dirname(__FILE__).'/myauth.php'); ?>
<?php require_once(dirname(__FILE__).'/../define.php'); ?>
<?php require_once(dirname(__FILE__).'/../Encode.php'); ?>
<?php require_once(dirname(__FILE__).'/../cj/class/CjCommonSQL.class.php'); ?>

<?php
// アクセスログ
require_once(dirname(__FILE__).'/../db_config.php');
require_once(dirname(__FILE__).'/../class/AccessLogSQL.class.php');
$log = new AccessLogSQL($GLOBALS['dbopts']);
$log->Write(0, '', __FILE__, __FUNCTION__, __LINE__);
?>

<?php
$cmn = new CjCommonSQL($GLOBALS['dbopts']);
$now_date = $cmn->getNowDay();
$now_date = format($now_date,'Y年m月d日').'<br>'.week($now_date).'曜日';
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="utf-8">
<title><?php echo APP_TITLE; ?></title>
<meta name="viewport" content="width=980px">
<link rel="shortcut icon" href="<?php echo PROJECT_ROOT ?>/favicon.ico">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/style.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/green/jquery-ui-1.9.2.custom.min.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/login/css/login.css" media="all">

<script src="<?php echo PROJECT_ROOT ?>/login/js/sha256-min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery-1.7.2.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery-ui-1.8.20.custom.min.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/jquery.bgiframe.js"></script>
<script src="<?php echo PROJECT_ROOT ?>/js/alert_dialog.js"></script>

<script>
function login(p) {
	var key = document.getElementById('token').value;
	p['password'].value = hex_hmac_sha256(key, p['username'].value + ':' + p['password_ctrl'].value);
	p['password_ctrl'].value = '';
	p['hash'].value = 1;
	p['submit'].disabled = true;
	return true;
}

function userreg(p) {
	var btn = document.getElementById('userreg-submit');
	btn.disabled = true;
	return true;
}
</script>

</head>
<body>

<?php require(dirname(__FILE__).'/../header.php'); ?>

<div id="content" class="clearfix">
	<div class="inner">

	<div id="annai">
		<div class="hyoudai">ＴｏＤｏと行動の記録ができます。</div>
		<div class="setumei">個人的に行動を記録して分析、グループのプロジェクト管理、お仕事の効率化など、どのように活用するかは、あなた次第。<br>
		ユーザー登録は無料です。今すぐ右の「無料ユーザー登録」からメール送信してお試しください。<br><br>
		当サイトには、下の機能があります。<br>
		</div>
		<div class="waku">
			<div class="kinou">
				<img src="<?php echo PROJECT_ROOT ?>/images/todo64.png" width="64" height="64">
				<div class="hyoudai">ＴｏＤｏ</div>
				<div class="setumei">やるべきことを記録できます。<br>
					記録した項目は一覧表示し、状況（未着手、作業中、終了、保留、中止）を管理することができます。
				</div>
			</div>
			<div class="kinou">
				<img src="<?php echo PROJECT_ROOT ?>/images/sche64.png" width="64" height="64">
				<div class="hyoudai">行動記録</div>
				<div class="setumei">
					いつ、何を、どれだけの時間（工数）したかを記録できます。<br>
					ＴｏＤｏを登録しておけば、選択するだけで簡単に行動の記録ができます。事前に予定を登録しておくことも可能です。
					出勤と退勤を入力すれば、勤怠管理も可能です。
				</div>
			</div>
			<div class="kinou">
				<img src="<?php echo PROJECT_ROOT ?>/images/cost64.png" width="64" height="64">
				<div class="hyoudai">工数表</div>
				<div class="setumei">
					行動記録の時間（工数）を集計して工数表を作成できます。<br>
					集計結果は、表と折線グラフで視覚的に表示し、いつ、何を、どれだけの時間したかを把握できます。
				</div>
			</div>
			<div class="kinou">
				<img src="<?php echo PROJECT_ROOT ?>/images/kint64.png" width="64" height="64">
				<div class="hyoudai">勤怠表</div>
				<div class="setumei">
					行動記録の出勤～退勤の時間を集計して勤怠表を作成できます。
				</div>
			</div>
			<div class="kinou">
				<img src="<?php echo PROJECT_ROOT ?>/images/grou64.png" width="64" height="64">
				<div class="hyoudai">グループ管理</div>
				<div class="setumei">
					他のユーザーと記録を共有できます。<br>
					各ユーザーはプライベートな記録ができる「ホーム」を持ちますが、他のユーザーと記録を共有できる「グループ」の作成もできます。
					プロジェクト管理など、記録を共有したいようなときに活用できるしょう。
				</div>
			</div>
		</div>
		<div class="business clearfix">
			<img src="<?php echo PROJECT_ROOT ?>/images/contact128.png" width="128" height="128">
			<div class="arrow_box">
				当サイト管理者へのお問い合わせは右のボタンからどうぞ。
				<a href="<?php echo PROJECT_ROOT ?>/contact_page.php" id="btn_contact">お問い合わせ</a>
			</div>
		</div>
		<div>
			当サイトは、Internet Explorerでの使用を推奨します。
		</div>
	</div>

	<div id="login">
	<h1><?php echo APP_TITLE; ?></h1>
	<input type="hidden" id="token" value="<?php echo @$_SESSION[S_TOKEN] ?>" />
	<form name="loginform" id="login-form" action="<?php $_SERVER['REQUEST_URI'] ?>" onSubmit="return login(this);" method="post">
		<div>
			<p>
				<label for="login-user">ユーザー名<br />
				<input type="text" name="username" id="login-user" value="" size="20" /></label>
			</p>
			<p>
				<label for="login-pass">パスワード<br />
				<input type="password" name="password_ctrl" id="login-pass" value="" size="20" /></label>
			</p>
			<p class="submit">
				<input type="submit" name="submit" id="login-submit" value="ログイン" />
				<input type="hidden" name="password" value="" />
				<input type="hidden" name="hash" value="0" />
			</p>
		</div>
		<div class="clearfix"></div>
		<div>
			<label for="login-longlogin"><input name="longlogin" type="checkbox" id="login-longlogin" value="1" /> ログイン状態を保存する</label>
		</div>
	</form>
	</div>

	<div id="userreg">
	<h1>無料ユーザー登録</h1>
	<form name="userregform" id="userreg-form" action="<?php echo PROJECT_ROOT ?>/login/user_reg.php" onSubmit="return userreg(this);" method="post">
		<p>
			<label for="user_edit_mail">メールアドレス<br />
			<input type="text" name="user_edit_mail" id="user_edit_mail" size="20"
				value="<?php echo isset($_POST['user_edit_mail']) ? e($_POST['user_edit_mail']) : ''; ?>"/></label>
		</p>
		<p class="submit">
			<input type="submit" name="userreg-submit" id="userreg-submit" value="送信する" />
		</p>
		<br>
		<div class="setumei">
			「送信する」ボタンを押すと、当サイトから「ユーザー登録用URLお知らせ」を送信します。<br><br>
			メールに記載している「新規ユーザー登録」ページから、登録を行うと、当サイトにログインして、当サイトの機能を利用できるようになります。
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
			d = document.getElementById('user_login');
			d.focus();
			d.select();

		} catch(e){}
	}, 200);
}
attempt_focus();
</script>

</body>
</html>
