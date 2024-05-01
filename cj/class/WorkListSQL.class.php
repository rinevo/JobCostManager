<?php
require_once(dirname(__FILE__).'/../../define.php');
require_once(dirname(__FILE__).'/../../Encode.php');

class WorkListSQL {

	protected $opts = array('dsn'=>'', 'db_user'=>'', 'db_pwd'=>0);
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

	//業務リスト
	function getWork_list() {
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
			$sql = 'SELECT a.no AS project_no, a.name AS project_name, a.sortno AS project_sortno ';
			$sql.= ',b.no AS work_no, b.name AS work_name, b.sortno AS work_sortno ';
			$sql.= ',(SELECT COUNT(no) FROM '.T_SCHEDULE.' WHERE project_no=a.no) AS project_use ';
			$sql.= ',(SELECT COUNT(no) FROM '.T_SCHEDULE.' WHERE work_no=b.no) AS work_use ';
			$sql.= 'FROM '.T_PROJECT.' a LEFT JOIN '.T_WORK.' b ON a.no=b.project_no ';
			$sql.= 'WHERE a.group_no=:group_no ';
			$sql.= 'ORDER BY a.sortno, b.sortno ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->execute();

			$project_no_bk = 0;
			$project_top = 0;
			$work_count = 0;
			$list_index = 0;
			$list = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$item = array();
				foreach ($row as $key => $value) {
					$item[$key] = mb_convert_encoding($value, "UTF-8", "auto");
				}
				if ($item['project_no'] != $project_no_bk) {
					if ($list_index > 0) {
						$list[$project_top]['rowspan'] = $work_count;
					}
					$project_top = $list_index;
					$work_count = 1;
				} else {
					$work_count++;
				}
				$list[] = $item;
				$list_index++;
				$project_no_bk = $item['project_no'];
			}
			if (!$list) {
				$list = array();
			} else {
				$list[$project_top]['rowspan'] = $work_count;
			}
		} catch (PDOException $e) {
			$this->message = "業務項目の読込に失敗しました。<br>".$e->getMessage();
			return $list;
		}
		$stmt = null;
		$db = null;

		return $list;
	}

	function insertProject($project_name, $sortno = '', $select_group_no = '') {
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
			$sql = 'SELECT CASE (SELECT MIN(no) FROM '.T_PROJECT.') WHEN 1 THEN MIN(A.no)+1 ELSE 1 END AS minvalue ';
			$sql.= 'FROM '.T_PROJECT.' A ';
			$sql.= 'WHERE NOT EXISTS (SELECT no FROM '.T_PROJECT.' AS B WHERE B.no=A.no+1)';
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$project_no = $row['minvalue'];

			//追加
			$sql = 'INSERT INTO '.T_PROJECT.'(no, name, sortno, group_no) ';
			if (!empty($sortno)) {
				$sql.= 'VALUES(:no, :name, :sortno, :group_no)';
				$stmt = $db->prepare($sql);
				$stmt->bindValue(':sortno', $sortno);
			} else {
				$sql.= 'VALUES(:no, :name, (SELECT MAX(sortno)+1 FROM '.T_PROJECT.' WHERE group_no=:group_no), :group_no)';
				$stmt = $db->prepare($sql);
			}
			$stmt->bindValue(':no', $project_no);
			$stmt->bindValue(':name', $project_name);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->execute();

			//挿入した項目以降のsortnoを+1
			if (!empty($sortno)) {
				$sql = 'UPDATE '.T_PROJECT.' SET sortno=sortno+1 WHERE group_no=:group_no AND sortno>=:sortno AND no<>:no ';
				$stmt = $db->prepare($sql);
				$stmt->bindValue(':group_no', $group_no);
				$stmt->bindValue(':sortno', $sortno);
				$stmt->bindValue(':no', $project_no);
				$stmt->execute();
			}

			$db->commit();
			$ret = $project_no;
		} catch(PDOException $e) {
			$this->message = 'プロジェクトの追加に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		}
		$db = NULL;

		return $ret;
	}

	function updateProject($project_no, $project_name) {
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

			$sql = 'UPDATE '.T_PROJECT.' SET name=:name WHERE group_no=:group_no AND no=:no ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':no', $project_no);
			$stmt->bindValue(':name', $project_name);
			$stmt->execute();

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$this->message = 'プロジェクト名の変更に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		}
		$db = NULL;

		return $ret;
	}

	function deleteProject($project_no) {
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
			$sql.= '(SELECT COUNT(no) FROM '.T_SCHEDULE.' WHERE project_no=:project_no1) AS schdule_use ';
			$sql.= ',(SELECT COUNT(no) FROM '.T_TODO.' WHERE project_no=:project_no2) AS todo_use ';
			$sql.= ',(SELECT COUNT(no) FROM '.T_WORK.' WHERE project_no=:project_no3) AS work_use ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':project_no1', $project_no);
			$stmt->bindValue(':project_no2', $project_no);
			$stmt->bindValue(':project_no3', $project_no);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row['schdule_use'] > 0) {
				throw new Exception('行動記録で使用されているため削除できません。');
			}
			if ($row['todo_use'] > 0) {
				throw new Exception('TODOで使用されているため削除できません。');
			}
			if ($row['work_use'] > 0) {
				throw new Exception('業務項目が登録されているため削除できません。');
			}

			$sql = 'DELETE FROM '.T_PROJECT.' WHERE group_no=:group_no AND no=:no ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':no', $project_no);
			$stmt->execute();

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$this->message = 'プロジェクトの削除に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		} catch(Exception $e) {
			$this->message = $e->getMessage();
			$db->rollBack();
		}
		$db = NULL;

		return $ret;
	}

	function moveProject($project_no, $before_sortno, $after_sortno) {
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
			$sql = 'SELECT MAX(sortno) AS maxno FROM '.T_PROJECT.' WHERE group_no=:group_no ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row['maxno'] < $after_sortno) {
				$ret = true;
				throw new Exception('');
			}

			//移動
			$sql = 'UPDATE '.T_PROJECT.' SET sortno=:sortno WHERE group_no=:group_no AND no=:no ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':no', $project_no);
			$stmt->bindValue(':sortno', $after_sortno);
			$stmt->execute();

			//移動後に空いたsortnoを詰める
			if ($before_sortno > $after_sortno) {
				//↑
				$sql = 'UPDATE '.T_PROJECT.' SET sortno=sortno+1 WHERE group_no=:group_no AND sortno>=:after_sortno AND sortno<:before_sortno AND no<>:no ';
			} else {
				//↓
				$sql = 'UPDATE '.T_PROJECT.' SET sortno=sortno-1 WHERE group_no=:group_no AND sortno>:before_sortno AND sortno<=:after_sortno AND no<>:no ';
			}
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':no', $project_no);
			$stmt->bindValue(':before_sortno', $before_sortno);
			$stmt->bindValue(':after_sortno', $after_sortno);
			$stmt->execute();

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$this->message = 'プロジェクトの並び替えに失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		} catch(Exception $e) {
			$this->message = $e->getMessage();
			$db->rollBack();
		}
		$db = NULL;

		return $ret;
	}

	function insertWork($project_no, $work_name, $sortno = '', $select_group_no = '') {
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
			$sql = 'SELECT CASE (SELECT MIN(no) FROM '.T_WORK.') WHEN 1 THEN MIN(A.no)+1 ELSE 1 END AS minvalue ';
			$sql.= 'FROM '.T_WORK.' A ';
			$sql.= 'WHERE NOT EXISTS (SELECT no FROM '.T_WORK.' AS B WHERE B.no=A.no+1)';
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$work_no = $row['minvalue'];

			//追加
			$sql = 'INSERT INTO '.T_WORK.'(no, name, sortno, project_no, group_no) ';
			if (!empty($sortno)) {
				$sql.= 'VALUES(:no, :name, :sortno, :project_no, :group_no)';
				$stmt = $db->prepare($sql);
				$stmt->bindValue(':sortno', $sortno);
			} else {
				$sql.= 'VALUES(:no, :name, (SELECT MAX(sortno)+1 FROM '.T_WORK.' WHERE project_no=:project_no), :project_no, :group_no)';
				$stmt = $db->prepare($sql);
			}
			$stmt->bindValue(':no', $work_no);
			$stmt->bindValue(':name', $work_name);
			$stmt->bindValue(':project_no', $project_no);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->execute();

			//挿入した項目以降のsortnoを+1
			if (!empty($sortno)) {
				$sql = 'UPDATE '.T_WORK.' SET sortno=sortno+1 WHERE project_no=:project_no AND sortno>=:sortno AND no<>:no ';
				$stmt = $db->prepare($sql);
				$stmt->bindValue(':project_no', $project_no);
				$stmt->bindValue(':sortno', $sortno);
				$stmt->bindValue(':no', $work_no);
				$stmt->execute();
			}

			$db->commit();
			$ret = $work_no;
		} catch(PDOException $e) {
			$this->message = '業務の追加に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		}
		$db = NULL;

		return $ret;
	}

	function updateWork($work_no, $work_name) {
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

			$sql = 'UPDATE '.T_WORK.' SET name=:name WHERE group_no=:group_no AND no=:no ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':no', $work_no);
			$stmt->bindValue(':name', $work_name);
			$stmt->execute();

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$this->message = '業務名の変更に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		}
		$db = NULL;

		return $ret;
	}

	function deleteWork($work_no) {
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
			$sql.= '(SELECT COUNT(no) FROM '.T_SCHEDULE.' WHERE work_no=:work_no1) AS schdule_use ';
			$sql.= ',(SELECT COUNT(no) FROM '.T_TODO.' WHERE work_no=:work_no2) AS todo_use ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':work_no1', $work_no);
			$stmt->bindValue(':work_no2', $work_no);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row['schdule_use'] > 0) {
				throw new Exception('行動記録で使用されているため削除できません。');
			}
			if ($row['todo_use'] > 0) {
				throw new Exception('TODOで使用されているため削除できません。');
			}

			$sql = 'DELETE FROM '.T_WORK.' WHERE group_no=:group_no AND no=:no ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':no', $work_no);
			$stmt->execute();

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$this->message = '業務の削除に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		} catch(Exception $e) {
			$this->message = $e->getMessage();
			$db->rollBack();
		}
		$db = NULL;

		return $ret;
	}

	function moveWork($project_no, $work_no, $before_sortno, $after_sortno) {
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
			$sql = 'SELECT MAX(sortno) AS maxno FROM '.T_WORK.' WHERE group_no=:group_no AND project_no=:project_no ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':project_no', $project_no);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row['maxno'] < $after_sortno) {
				$ret = true;
				throw new Exception('');
			}

			//移動
			$sql = 'UPDATE '.T_WORK.' SET sortno=:sortno WHERE group_no=:group_no AND no=:no ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':no', $work_no);
			$stmt->bindValue(':sortno', $after_sortno);
			$stmt->execute();

			//移動後に空いたsortnoを詰める
			if ($before_sortno > $after_sortno) {
				//↑
				$sql = 'UPDATE '.T_WORK.' SET sortno=sortno+1 WHERE group_no=:group_no AND project_no=:project_no AND sortno>=:after_sortno AND sortno<:before_sortno AND no<>:no ';
			} else {
				//↓
				$sql = 'UPDATE '.T_WORK.' SET sortno=sortno-1 WHERE group_no=:group_no AND project_no=:project_no AND sortno>:before_sortno AND sortno<=:after_sortno AND no<>:no ';
			}
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':project_no', $project_no);
			$stmt->bindValue(':no', $work_no);
			$stmt->bindValue(':before_sortno', $before_sortno);
			$stmt->bindValue(':after_sortno', $after_sortno);
			$stmt->execute();

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$this->message = '業務の並び替えに失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		} catch(Exception $e) {
			$this->message = $e->getMessage();
			$db->rollBack();
		}
		$db = NULL;

		return $ret;
	}
}