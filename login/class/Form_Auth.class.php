<?php
require_once(dirname(__FILE__).'/../../define.php');
require_once(dirname(__FILE__).'/Auth.class.php');

class Form_Auth extends Auth {

	protected $message = array('','有効期限切れとなりました。',
			'ユーザ名とパスワードを入力してください。',
			'ユーザ名またはパスワードが正しくありません。');

	function check() {	// 認証情報を検証
		if (isset($_SESSION[S_LOGIN]) && $_SESSION[S_LOGIN]) {
			return true;
		}
		$password = $this->getCredential($_SESSION[S_USERNAME]);
		return ($_SESSION[S_PASSWORD] == $password);
	}

	function login($status) {	// 認証紹鴎入力を要求
		echo <<<EOS
		{$this->message[$status]}
<form method="POST" action="">
	ユーザ名：<input type="text" name="username" /><br/>
	パスワード：<input type="password" name="password" /><br/>
	<input type="submit" />
</form>
EOS;
		exit();
	}

	function loadData() {	// リクエスト中の認証情報をパース
		if (isset($_POST['username']) && isset($_POST['password'])) {
			$_SESSION[S_USERNAME] = $_POST['username'];
			$_SESSION[S_PASSWORD] = $_POST['password'];
			$this->data = true;
		} else if (isset($_SESSION[S_LOGIN]) && $_SESSION[S_LOGIN]) {
			$this->data = true;
		} else {
			$this->data = null;
		}
	}

	function session_regenerate_before() {}
	function session_regenerate_after() {}
	function logout_before() {}

}