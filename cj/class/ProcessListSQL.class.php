<?php
require_once(dirname(__FILE__).'/../../define.php');
require_once(dirname(__FILE__).'/../../Encode.php');

class ProcessListSQL {

	protected $opts = array('dsn'=>'', 'db_user'=>'', 'db_pwd'=>0);
	protected $usertable = T_PROCESS;
	protected $message = '';

	function __construct($opts = null) {
		// データベース接続に使用するオプション値の初期設定
		foreach ($this->opts as $key => $value) { // オプション設定
			$this->opts[$key] = isset($opts[$key]) ? $opts[$key] : $value;
		}
	}

	function getMessage() {
		return $this->message;
	}

	function DBOpen() {
		$db = null;
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			$this->message = "データベースの接続に失敗しました。<br>".$e->getMessage();
			return false;
		}
		return $db;
	}

	//工程リスト
	function getList() {
		$list = false;

		//選択中のグループ
		$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;

		//データベース接続
		$db = $this->DBOpen();
		if (!$db) {
			return false;
		}

		//読込
		try {
			$sql = 'SELECT a.no, a.name, a.sortno ';
			$sql.= ',(SELECT COUNT(no) FROM '.T_SCHEDULE.' WHERE process_no=a.no) AS schedule_use ';
			$sql.= ',(SELECT COUNT(no) FROM '.T_TODO.' WHERE process_no=a.no) AS todo_use ';
			$sql.= 'FROM '.$this->usertable.' a ';
			$sql.= 'WHERE a.group_no=:group_no ';
			$sql.= 'ORDER BY a.sortno ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->execute();

			$list = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$item = array();
				foreach ($row as $key => $value) {
					$item[$key] = mb_convert_encoding($value, "UTF-8", "auto");
				}
				$list[] = $item;
			}
		} catch (PDOException $e) {
			$this->message = "工程項目の読込に失敗しました。<br>".$e->getMessage();
			return $list;
		}
		$stmt = null;
		$db = null;

		return $list;
	}

	function insertItem($name, $sortno = '', $select_group_no = '') {
		$ret = false;

		//選択中のグループ
		$group_no = !empty($select_group_no) ? $select_group_no : (isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0);

		//データベース接続
		$db = $this->DBOpen();
		if (!$db) {
			return false;
		}

		try {
			$db->beginTransaction();

			//空きno
			$sql = 'SELECT CASE (SELECT MIN(no) FROM '.$this->usertable.') WHEN 1 THEN MIN(A.no)+1 ELSE 1 END AS minvalue ';
			$sql.= 'FROM '.$this->usertable.' A ';
			$sql.= 'WHERE NOT EXISTS (SELECT no FROM '.$this->usertable.' AS B WHERE B.no=A.no+1)';
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$no = $row['minvalue'];

			//追加
			$sql = 'INSERT INTO '.$this->usertable.'(no, name, sortno, group_no) ';
			if (!empty($sortno)) {
				$sql.= 'VALUES(:no, :name, :sortno, :group_no)';
				$stmt = $db->prepare($sql);
				$stmt->bindValue(':sortno', $sortno);
			} else {
				$sql.= 'VALUES(:no, :name, (SELECT MAX(sortno)+1 FROM '.$this->usertable.' WHERE group_no=:group_no), :group_no)';
				$stmt = $db->prepare($sql);
			}
			$stmt->bindValue(':no', $no);
			$stmt->bindValue(':name', $name);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->execute();

			//挿入した項目以降のsortnoを+1
			if (!empty($sortno)) {
				$sql = 'UPDATE '.$this->usertable.' SET sortno=sortno+1 WHERE group_no=:group_no AND sortno>=:sortno AND no<>:no ';
				$stmt = $db->prepare($sql);
				$stmt->bindValue(':group_no', $group_no);
				$stmt->bindValue(':sortno', $sortno);
				$stmt->bindValue(':no', $no);
				$stmt->execute();
			}

			$db->commit();
			$ret = $no;
		} catch(PDOException $e) {
			$this->message = '工程の追加に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		}
		$db = NULL;

		return $ret;
	}

	function updateItem($no, $name) {
		$ret = false;

		//選択中のグループ
		$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;

		//データベース接続
		$db = $this->DBOpen();
		if (!$db) {
			return false;
		}

		try {
			$db->beginTransaction();

			$sql = 'UPDATE '.$this->usertable.' SET name=:name WHERE group_no=:group_no AND no=:no ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':no', $no);
			$stmt->bindValue(':name', $name);
			$stmt->execute();

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$this->message = '工程名の変更に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		}
		$db = NULL;

		return $ret;
	}

	function deleteItem($no) {
		$ret = false;

		//選択中のグループ
		$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;

		//データベース接続
		$db = $this->DBOpen();
		if (!$db) {
			return false;
		}

		try {
			$db->beginTransaction();

			$sql = 'SELECT ';
			$sql.= '(SELECT COUNT(no) FROM '.T_SCHEDULE.' WHERE process_no=:no1) AS schdule_use ';
			$sql.= ',(SELECT COUNT(no) FROM '.T_TODO.' WHERE process_no=:no2) AS todo_use ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':no1', $no);
			$stmt->bindValue(':no2', $no);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row['schdule_use'] > 0) {
				$this->message = '行動記録で使用されているため削除できません。';
				return false;
			}
			if ($row['todo_use'] > 0) {
				$this->message = 'TODOで使用されているため削除できません。';
				return false;
			}

			$sql = 'DELETE FROM '.$this->usertable.' WHERE group_no=:group_no AND no=:no ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':no', $no);
			$stmt->execute();

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$this->message = '工程の削除に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		}
		$db = NULL;

		return $ret;
	}

	function moveItem($no, $before_sortno, $after_sortno) {
		$ret = false;

		//移動前と移動後のsortnoが同じならば何もしない
		if ($before_sortno == $after_sortno || $after_sortno < 1) {
			return true;
		}

		//選択中のグループ
		$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;

		//データベース接続
		$db = $this->DBOpen();
		if (!$db) {
			return false;
		}

		try {
			$db->beginTransaction();

			//最大sortnoより大きければ何もしない
			$sql = 'SELECT MAX(sortno) AS maxno FROM '.$this->usertable.' WHERE group_no=:group_no ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row['maxno'] < $after_sortno) {
				$ret = true;
				throw new Exception('');
			}

			//移動
			$sql = 'UPDATE '.$this->usertable.' SET sortno=:sortno WHERE group_no=:group_no AND no=:no ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':no', $no);
			$stmt->bindValue(':sortno', $after_sortno);
			$stmt->execute();

			//移動後に空いたsortnoを詰める
			if ($before_sortno > $after_sortno) {
				//↑
				$sql = 'UPDATE '.$this->usertable.' SET sortno=sortno+1 WHERE group_no=:group_no AND sortno>=:after_sortno AND sortno<:before_sortno AND no<>:no ';
			} else {
				//↓
				$sql = 'UPDATE '.$this->usertable.' SET sortno=sortno-1 WHERE group_no=:group_no AND sortno>:before_sortno AND sortno<=:after_sortno AND no<>:no ';
			}
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':no', $no);
			$stmt->bindValue(':before_sortno', $before_sortno);
			$stmt->bindValue(':after_sortno', $after_sortno);
			$stmt->execute();

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$this->message = '工程の並び替えに失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		} catch(Exception $e) {
			$this->message = $e->getMessage();
			$db->rollBack();
		}
		$db = NULL;

		return $ret;
	}

}