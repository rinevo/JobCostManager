<?php
require_once(dirname(__FILE__).'/../../define.php');
require_once(dirname(__FILE__).'/EncryptSupport.class.php');
require_once(dirname(__FILE__).'/GroupListSQL.class.php');
if (DB_TYPE == 'SQL') require_once(dirname(__FILE__).'/UserListSQL.class.php');

abstract class Auth {

	protected $opts = array('dsn'=>'', 'db_user'=>'', 'db_pwd'=>0);
									// データベース接続プロパティ
	protected $data;				// 認証情報のセッション格納有無
	protected $user;				// ユーザー情報クラス
	const TIMEOUT = 1, NO_DATA = 0, INVALID = 3;

	/**
	 * コンストラクタ
	 * @param unknown_type $opts
	 */
	function __construct($opts = null) {

		foreach ($this->opts as $key => $value) { // オプション設定
			$this->opts[$key] = isset($opts[$key]) ? $opts[$key] : $value;
		}

		// セッション制御
		if (!isset($_SESSION) && isset($opts)) {

			// セッション開始
			//session_cache_limiter("nocache");	// キャッシュを許可しない（戻るボタン不可）
			$this->opts['idle'] = 3600;			// 1時間後にタイムアウト
			session_start();

		}

		// ユーザー操作クラス準備
		$this->user = new UserListSQL($opts);
	}

	/**
	 * 認証情報を取得
	 * @param unknown_type $username
	 * @return Ambigous <boolean, mixed>
	 */
	function getCredential($username) {
		$this->user->getParentInfo($username);
		return $this->user->getParent_passwd();
	}

	/**
	 * ログイン状態時にtrueを返す
	 * @return Ambigous <boolean, unknown>
	 */
	function getAuth() {
		return isset($_SESSION[S_LOGIN]) ? $_SESSION[S_LOGIN] : false;
	}

	/**
	 * ログインユーザーのuidを取得
	 * @return uid
	 */
	function getParent_uid() {
		return $this->user->getParent_uid();
	}

	/**
	 * ログインユーザー名を取得
	 * @return name
	 */
	function getParent_name() {
		return $this->user->getParent_name();
	}

	/**
	 * ログインユーザーのgroup_no_defaultを取得
	 * @return group_no_default
	 */
	function getParent_group_no_default() {
		return $this->user->getParent_group_no_default();
	}

	/**
	 * ログインユーザーのgroup_noを取得
	 * @return group_no
	 */
	function getParent_group_no() {
		return $this->user->getParent_group_no();
	}

	/**
	 * ログインユーザーのgroup_nameを取得
	 * @return group_name
	 */
	function getParent_group_name() {
		return $this->user->getParent_group_name();
	}

	/**
	 * ログインユーザーのmember_statusを取得
	 * @return group_name
	 */
	function getParent_member_status() {
		return $this->user->getParent_member_status();
	}

	/**
	 * ログインユーザーの権限を取得
	 * @return 権限
	 */
	function getParent_role_user() {
		return $this->user->getParent_role_user();
	}
	function getParent_role_work() {
		return $this->user->getParent_role_work();
	}
	function getParent_role_customer() {
		return $this->user->getParent_role_customer();
	}
	function getParent_role_process() {
		return $this->user->getParent_role_process();
	}
	function getParent_role_todo() {
		return $this->user->getParent_role_todo();
	}
	function getParent_role_schedule() {
		return $this->user->getParent_role_schedule();
	}
	function getParent_role_cost() {
		return $this->user->getParent_role_cost();
	}
	function getParent_role_kintai() {
		return $this->user->getParent_role_kintai();
	}

	/**
	 * ログインユーザーのメールアドレスを取得
	 * @return メールアドレス
	 */
	function getParent_mail() {
		return $this->user->getParent_mail();
	}

	/**
	 * ログインユーザーのstatusを取得
	 * @return status
	 */
	function getParent_status() {
		return $this->user->getParent_status();
	}

	/**
	 * ログインユーザーのgroup_no_defaultを設定（ＤＢ保存はしない。現在の状態変更のみ）
	 * @return boolean
	 */
	function setParent_group_no_default($group_no) {
		$ret = false;
		$info = $this->user->getUserInfo($this->user->getParent_uid(),'uid',$group_no);
		if ($info) {
			if ($group_no > 0) {
				//グループ内へは自分のユーザーの閲覧権限が無ければ移動不可
				if ($info['role_user'] & ROLE_VIW) {
					$ret = true;
				}
			}
		}
		if ($ret) {
			//ユーザー情報を取得できたら現在の状態に設定
			$this->user->setParent_group_no_default($group_no);
			$this->user->setParent($this->user->getParent());
			return true;
		} else {
			return false;
		}
	}

	/**
	 * ログインユーザーのgroup_noを設定
	 * @return boolean
	 */
	function setParent_group_no($group_no) {
		$ret = false;
		$info = $this->user->getUserInfo($this->user->getParent_uid(),'uid',$group_no);
		if ($info) {
			if ($group_no > 0) {
				//グループ内へは自分のユーザーの閲覧権限が無ければ移動不可
				if ($info['role_user'] & ROLE_VIW) {
					$ret = true;
				}
			}
		}
		if ($ret) {
			//ユーザー情報を取得できたら現在の状態に設定
			$this->user->setParent($info);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * ログアウト処理
	 */
	function logout() {
		$this->logout_before();
		$_SESSION = array();
		if (isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time() - 3600);
		}
		session_destroy();
	}

	/**
	 * 認証処理を開始
	 */
	function start() {
		$this->loadData();
		if (isset($_SESSION[S_LOGIN]) && $_SESSION[S_LOGIN] && !isset($_COOKIE[C_LONGLOGIN])) {
			if (isset($this->opts['idle']) && (($this->opts['idle'] > 0) && (time() - $_SESSION[S_IDLE] > $this->opts['idle']))) {
				$this->login(self::TIMEOUT);	// 有効期限切れの場合はログアウト
				return;
			} else {
				$_SESSION[S_IDLE] = time();	// アイドル時間を更新
			}
		}
		if (!$this->data) {
			$this->login(self::NO_DATA);	// 認証画面表示
		} else if (!$this->check()) {	// 認証情報の有効・無効を判定
			$this->login(self::INVALID);	// 認証情報が無効な場合は認証画面表示
		} else {
			if (!isset($_SESSION[S_LOGIN]) || !$_SESSION[S_LOGIN]) {
				// セッションハイジャック対策として、ログイン直後にセッションIDを変更
				$this->session_regenerate_before();
				$uid = $this->user->getParent_uid();
				session_regenerate_id(true);	// セッションIDを変更
				$_SESSION[S_USERNAME] = $uid;
				$this->user->getParentInfo($uid);
				$this->session_regenerate_after();
				//デフォルトグループへ接続
				$ret = $this->setParent_group_no($this->getParent_group_no_default());
				if (!$ret) {
					//接続できないときはホームへ移動
					$group = new GroupListSQL($this->opts);
					$home = $group->getHome($uid);
					if ($home > 0) {
						$ret = $this->setParent_group_no($home);
						if ($ret) {
							//ホームグループをデフォルトにする
							$_POST['user_edit_uid'] = $uid;
							$_POST['user_edit_group_no_default'] = $home;
							$ret = $this->user->updateUser();
							if ($ret) {
								$this->user->getParentInfo($uid);
								if ($this->user->getParent_group_no() != $home) {
									$ret = false;
								}
							}
						}
					}
					if (!$ret) {
						//ホームへも接続できないときは、アクセス不可
						$this->login(self::INVALID);	// 認証情報が無効な場合は認証画面表示
					}
				}
			}
			$_SESSION[S_LOGIN] = true;
			$_SESSION[S_IDLE] = time();
		}
		return;
	}

	/**
	 * 認証情報を検証
	 */
	abstract function check();

	/**
	 * 認証情報入力を要求
	 * @param unknown_type $status
	 */
	abstract function login($status);

	/**
	 * 認証情報を読み込み
	 */
	abstract function loadData();

	/**
	 * 認証直後、セッションハイジャック対策としてセッションIDの変更前処理
	 */
	abstract function session_regenerate_before();

	/**
	 * 認証直後、セッションハイジャック対策としてセッションIDの変更後処理
	 */
	abstract function session_regenerate_after();

	/**
	 * ログオフ直前
	 */
	abstract function logout_before();
}