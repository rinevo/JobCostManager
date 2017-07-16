<?php
require_once(dirname(__FILE__).'/../../define.php');
require_once(dirname(__FILE__).'/Form_Auth.class.php');

class Form_Digest_Auth extends Form_Auth {

	function __construct($opts = null) {
		parent::__construct($opts);
	}

	function check() {
		if (isset($_SESSION[S_LOGIN]) && $_SESSION[S_LOGIN]) {
			return true;
		}
		// パスワードのハッシュ化有無
		if (isset($_POST['hash']) && ($_POST['hash'] == 1)) {
			// ハッシュ化されていたら、パスワードをハッシュ化して認証
			if (isset($_SESSION[S_TOKEN]) && isset($_SESSION[S_USERNAME]) && isset($_SESSION[S_PASSWORD])) {
				// ユーザーの認証情報を取得
				$password = $this->getCredential($_SESSION[S_USERNAME]);
				// 未登録のユーザーはログイン拒否
				if ($this->getParent_status() == USER_STATUS_ENTRY) {
					return false;
				}
				if ($password !== false) {
					// XSS対策として、直前に発行したユニークキーを使ってパスワードをハッシュ値に変換
					$expected = hash_hmac('sha256', $_SESSION[S_USERNAME].':'.$password, $_SESSION[S_TOKEN]);
					// ハッシュ値でパスワードを検証して、結果を返す
					return ($_SESSION[S_PASSWORD] == $expected);
				}
			}
		} else {
			// ハッシュ化されてなければ、cookieに保存されたパスワードで認証
			// ＤＢに期限を持って、期限切れなら許可しないようにする処理も必要
			if (isset($_COOKIE[C_USERNAME]) && isset($_COOKIE[C_PARAM])) {
				$this->user->getParentInfo($_COOKIE[C_USERNAME]);
				// 未登録のユーザーはログイン拒否
				if ($this->getParent_status() == USER_STATUS_ENTRY) {
					return false;
				}
				// パスワードを検証して、結果を返す
				return ($_COOKIE[C_PARAM] == $this->user->getParent_param());
			}
		}
		return false;
	}

	function login($status) {

		// 盗聴対策として、パスワードはユニークキーを使ってクライアント側JavaScriptで暗号化
		$_SESSION[S_TOKEN] = md5(uniqid());

		// エラーメッセージ
		if (strlen($this->message[$status])) {
			if (isset($_SESSION[S_MESSAGE]) && strlen($_SESSION[S_MESSAGE])) {
				$_SESSION[S_MESSAGE] .= '<br>';
			}
			$_SESSION[S_MESSAGE] = $this->message[$status];
		}

		// タイムアウトならばログアウトする
		if ($status == self::TIMEOUT) {
			require_once(dirname(__FILE__).'/../logout_control.php');
			exit();
		}

		// cookieから自動ログインの情報を削除
		if (isset($_COOKIE[C_PARAM])) {
			$this->delete_cookie();
			$_SESSION[S_MESSAGE] = 'ログイン状態の保存を解除しました。<br>ログイン状態の保存は、1つのパソコン及びブラウザでのみ利用可能です。';
		}

		// ログイン画面を表示
		require_once(dirname(__FILE__).'/../login.php');
		exit();
	}

	function loadData() {
		parent::loadData();
		if (!$this->data) {
			if (isset($_COOKIE[C_USERNAME]) && isset($_COOKIE[C_PARAM])) {
				$this->data = true;
			}
		}
	}

	function delete_cookie() {
		// cookieから自動ログインの情報を削除
		$expire = time() - 3600;
		if (isset($_COOKIE[C_USERNAME])) {
			setcookie(C_USERNAME, '', $expire, PROJECT_ROOT.'/');
		}
		if (isset($_COOKIE[C_PARAM])) {
			setcookie(C_PARAM, '', $expire, PROJECT_ROOT.'/');
		}
		if (isset($_COOKIE[C_LONGLOGIN])) {
			setcookie(C_LONGLOGIN, '', $expire, PROJECT_ROOT.'/');
		}
		if (isset($_COOKIE[C_MESSAGE])) {
			setcookie(C_MESSAGE, '', $expire, PROJECT_ROOT.'/');
		}
	}

	function session_regenerate_before() {}

	function session_regenerate_after() {

		// パスワードのハッシュ化有無
		if (isset($_POST['hash']) && ($_POST['hash'] == 1) && !isset($_POST['longlogin'])) {

			// cookieから自動ログインの情報を削除
			$this->delete_cookie();

		} else {
			// ハッシュ化されてなければ、cookieに保存されたパスワードで認証
			// したので、cookieのハッシュ化パスワードを更新する

			// ユーザー情報が格納されてなかったら何もしない
			if (!$this->user->getParent_uid() || !$this->user->getParent_passwd()) {
				return;
			}

			// ログイン直後、新しいハッシュ化パスワードを再生成
			$_SESSION[S_TOKEN] = md5(uniqid());

			// ハッシュ化パスワードをデータベースに保存
			$uid = $this->user->getParent_uid();
			$password = $this->user->getParent_passwd();
			$expected = hash_hmac('sha256', $uid.':'.$password, $_SESSION[S_TOKEN]);
			$_POST['user_edit_uid'] = $uid;
			$_POST['user_edit_param'] = $expected;
			$this->user->updateUser();

			// 更新後のユーザー情報を取得
			$this->user->getParentInfo($uid);

			// ユーザー名、新しいハッシュ化パスワードをcookieに保存
			$expire = time() + 60 * 60 * 24 * 14; //有効期限14日
			setcookie(C_USERNAME, $_SESSION[S_USERNAME], $expire, PROJECT_ROOT.'/');
			setcookie(C_PARAM, $expected, $expire, PROJECT_ROOT.'/');
			setcookie(C_LONGLOGIN, '1', $expire, PROJECT_ROOT.'/');
		}
	}

	function logout_before() {

		// ハッシュ化パスワードをデータベースから削除
		if ($this->user->getParent_uid() && $this->user->getParent_passwd() && $this->user->getParent_param()) {
			$uid = $this->user->getParent_uid();

			$_POST['user_edit_uid'] = $uid;
			$_POST['user_edit_param'] = '';
			$this->user->updateUser();

			// 更新後のユーザー情報を取得
			$this->user->getParentInfo($uid);
		}

		// cookieから自動ログインの情報を削除
		//$this->delete_cookie(); //セッションクリアと同時にCookieの削除はできないので後でやる
	}
}
