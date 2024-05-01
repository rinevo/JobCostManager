<?php
class AccessLogSQL {

	const STATUS_NORMAL = 0;
	const STATUS_ERROR = 1;

	protected $opts = array('dsn'=>'', 'db_user'=>'', 'db_pwd'=>0);
	protected $db = null;
	protected $message = '';

	function __construct($opts = null) {

		// データベース接続に使用するオプション値の初期設定
		foreach ($this->opts as $key => $value) { // オプション設定
			$this->opts[$key] = isset($opts[$key]) ? $opts[$key] : $value;
		}

		// データベース接続
		try {
			$this->db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$this->db->exec('SET NAMES utf8');
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(Exception $e) {
			$this->message = "データベース接続に失敗しました：".$e->getMessage();
		}
	}

	function __destruct() {
		$this->db = null;
	}

	function getMessage() {
		return $this->message;
	}

	function ArrayToString($var) {
		$result = '';
		if (is_array($var)) {
			foreach ($var as $key => $val) {
				if (strlen($result)) {
					$result .= ", ";
				}
				if (is_array($val)) {
					$result .= $key.'={'.$this->ArrayToString($val).'}';
				} else {
					$result .= $key.'='.$val;
				}
			}
		} else {
			$result = $var;
		}
		return $result;
	}

	function getList() {
		$db = NULL;
		$list = array();
		try {
			$sql = 'SELECT * FROM '.T_LOG.' ORDER BY time DESC LIMIT 100;';
			$stmt = $this->db->prepare($sql);
			$stmt->execute();	 // クエリー実行

			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				foreach ($row as $key => $value) {
					$item[$key] = mb_convert_encoding($value, "UTF-8", "auto");
				}
				$list[] = $item;
			}
		} catch (PDOException $e) {
			$this->message = "顧客リストの読込に失敗しました。<br>".$e->getMessage();
		}
		$db = NULL;
		return $list ? $list : false;
	}

	function Write($status, $message, $file, $func, $line) {
		// ログ出力
		if (!isset($this->db)) return;
		try {
			$sql = 'INSERT INTO '.T_LOG.'(time, status, message, file, func, line, uid, group_no, address, agent, lang, uri, request_method, request_value, session, cookie)';
			$sql .= ' VALUES(NOW(), :status, :message, :file, :func, :line, :uid, :group_no, :address, :agent, :lang, :uri, :request_method, :request_value, :session, :cookie)';
			$stmt = $this->db->prepare($sql);
			$stmt->bindValue(':status', $status);
			$stmt->bindValue(':message', $this->ArrayToString($message));
			$stmt->bindValue(':file', $file);
			$stmt->bindValue(':func', $func);
			$stmt->bindValue(':line', $line);
			$stmt->bindValue(':uid', isset($_SESSION[S_PARENT_USER]['uid']) ? $_SESSION[S_PARENT_USER]['uid'] : '');
			$stmt->bindValue(':group_no', isset($_SESSION[S_PARENT_USER]['group_no']) ? $_SESSION[S_PARENT_USER]['group_no'] : '');
			$stmt->bindValue(':address', isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
			$stmt->bindValue(':agent', isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
			$stmt->bindValue(':lang', isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '');
			$stmt->bindValue(':uri', isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '');
			$stmt->bindValue(':request_method', isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '');
			if (@$_SERVER['REQUEST_METHOD'] == 'POST') {
				$stmt->bindValue(':request_value', $this->ArrayToString($_POST));
			} elseif (@$_SERVER['REQUEST_METHOD'] == 'GET') {
				$stmt->bindValue(':request_value', $this->ArrayToString($_GET));
			} else {
				$stmt->bindValue(':request_value', '');
			}
			$stmt->bindValue(':session', $this->ArrayToString(isset($_SESSION) ? $_SESSION : ''));
			$stmt->bindValue(':cookie', $this->ArrayToString(isset($_COOKIE) ? $_COOKIE : ''));
			$stmt->execute();
		} catch(PDOException $e) {
			$this->message = "出力に失敗しました：".$e->getMessage();
		}
	}
}