<?php require_once(dirname(__FILE__).'/define.php'); ?>

<script>
function header_login(p) {
	var key = document.getElementById('header-token').value;
	p['password'].value = hex_hmac_sha256(key, p['username'].value + ':' + p['password_ctrl'].value);
	p['password_ctrl'].value = '';
	p['hash'].value = 1;
	p['submit'].disabled = true;
	return true;
}
</script>

<div id="header">
	<div class="inner">

		<div class="logo">
			<a href="<?php echo PROJECT_ROOT ?>/"><img alt="sample site" src="<?php echo PROJECT_ROOT ?>/images/logo.png"></a>
		</div>

<?php if (isset($auth) && $auth->getAuth()) { ?>

	<div class="div_logoff">
		<ul class="logoff">
			<li class="name"><?php echo e($auth->getParent_group_name()); ?></li>
			<li><a href="<?php echo PROJECT_ROOT ?>/login/group_list.php">[グループ]</a></li>
			<li class="name"><?php echo e($auth->getParent_name()); ?></li>
			<li><a href="<?php echo PROJECT_ROOT ?>/login/logout_control.php">[ログアウト]</a></li>
		</ul>
	</div>

	<div class="dv_menu">
		<ul class="menu">
			<?php if ($auth->getParent_role_todo() & ROLE_VIW) { ?>
				<li><a href="<?php echo PROJECT_ROOT ?>/cj/todo_list.php">ToDo</a></li>
			<?php } ?>
			<?php if ($auth->getParent_role_schedule() & ROLE_VIW) { ?>
				<li><a href="<?php echo PROJECT_ROOT ?>/cj/schedule_list.php">行動記録</a></li>
			<?php } ?>
			<?php if ($auth->getParent_role_cost() & ROLE_VIW) { ?>
				<li><a href="<?php echo PROJECT_ROOT ?>/cj/cost_list.php">工数表</a></li>
			<?php } ?>
			<?php if ($auth->getParent_role_kintai() & ROLE_VIW) { ?>
				<li><a href="<?php echo PROJECT_ROOT ?>/cj/kintai_list.php">勤怠表</a></li>
			<?php } ?>
			<li><a href="#">設定</a>
				<ul>
					<?php if ($auth->getParent_role_work() & ROLE_MEMBER_VIW) { ?>
						<li><a href="<?php echo PROJECT_ROOT ?>/cj/work_list.php">作業マスタ</a></li>
					<?php } ?>
					<?php if ($auth->getParent_role_customer() & ROLE_MEMBER_VIW) { ?>
						<li><a href="<?php echo PROJECT_ROOT ?>/cj/customer_list.php">顧客マスタ</a></li>
					<?php } ?>
					<?php if ($auth->getParent_role_process() & ROLE_MEMBER_VIW) { ?>
						<li><a href="<?php echo PROJECT_ROOT ?>/cj/process_list.php">工程マスタ</a></li>
					<?php } ?>
					<li><a href="<?php echo PROJECT_ROOT ?>/login/user_info.php">アカウント</a></li>
				</ul>
			</li>

		</ul>
	</div>

<?php } elseif (!isset($GLOBALS['header_arg']) || (isset($GLOBALS['header_arg']) && $GLOBALS['header_arg'])) { ?>

	<div id="header-login">
	<h1>ログイン</h1>
	<input type="hidden" id="header-token" value="<?php echo @$_SESSION[S_TOKEN] ?>" />
	<form name="loginform" id="header-login-form" action="<?php $_SERVER['REQUEST_URI'] ?>" onSubmit="return header_login(this);" method="post">
		<div>
			<p>
				<label for="header-login-user">ユーザー名<br />
				<input type="text" name="username" id="header-login-user" value="" size="20" /></label>
			</p>
			<p>
				<label for="header-login-pass">パスワード<br />
				<input type="password" name="password_ctrl" id="header-login-pass" value="" size="20" /></label>
			</p>
			<p class="submit">
				<input type="submit" name="submit" id="header-login-submit" value="ログイン" />
				<input type="hidden" name="password" value="" />
				<input type="hidden" name="hash" value="0" />
			</p>
		</div>
		<div class="clearfix"></div>
		<div>
			<label for="header-login-longlogin"><input name="longlogin" type="checkbox" id="header-login-longlogin" value="1" /> ログイン状態を保存する</label>
		</div>
	</form>
	</div>

<?php } ?>

	</div>
</div>
