<?php
require_once(dirname(__FILE__).'/../../define.php');
require_once(dirname(__FILE__).'/../../Encode.php');
require_once(dirname(__FILE__).'/CjCommonSQL.class.php');

class TodoListSQL {

	protected $opts = array('dsn'=>'', 'db_user'=>'', 'db_pwd'=>0);
	protected $usertable = T_TODO;
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

	//ＴＯＤＯリストＳＱＬ
	function getTodoListSQL($where_date = true) {

		$sql = 'SELECT * FROM ( ';
		$sql.= 'SELECT ';

		$sql.= ' a.no, a.sortno, a.action, a.status_no, f.name AS status_name, a.group_no, a.uid ';
		$sql.= ',( ';
		$sql.= 'CASE a.status_no ';
		$sql.= 'WHEN 2 THEN 1 ';
		$sql.= 'WHEN 1 THEN 2 ';
		$sql.= 'WHEN 0 THEN 3 ';
		$sql.= 'WHEN 3 THEN 4 ';
		$sql.= 'ELSE 5 END ';
		$sql.= ' ) AS status_sortno ';
		$sql.= ',2 AS section_no, 0 AS cbo_action_start, 0 AS cbo_action_end ';
		$sql.= ',a.project_no, b.sortno AS project_sortno, b.name AS project_name ';
		$sql.= ',a.work_no, c.sortno AS work_sortno, c.name AS work_name ';
		$sql.= ',a.customer_no, d.sortno AS customer_sortno, d.name AS customer_name ';
		$sql.= ',a.process_no, e.sortno AS process_sortno, e.name AS process_name ';
		$sql.= ',( ';
		$sql.= 'SELECT MIN(date) FROM '.T_SCHEDULE.' ';
		$sql.= 'WHERE group_no=a.group_no AND uid=a.uid ';
		if ($where_date) $sql.= 'AND date between :date_top1 AND :date_end1 ';
		$sql.= 'AND ((project_no=a.project_no) OR (a.project_no=0)) ';
		$sql.= 'AND ((work_no=a.work_no) OR (a.work_no=0)) ';
		$sql.= 'AND ((customer_no=a.customer_no) OR (a.customer_no=0)) ';
		$sql.= 'AND ((process_no=a.process_no) OR (a.process_no=0)) ';
		$sql.= "AND action LIKE CONCAT('%',a.action,'%') ";
		$sql.= ' ) AS date_top ';
		$sql.= ',( ';
		$sql.= 'SELECT MAX(date) FROM '.T_SCHEDULE.' ';
		$sql.= 'WHERE group_no=a.group_no AND uid=a.uid ';
		if ($where_date) $sql.= 'AND date between :date_top2 AND :date_end2 ';
		$sql.= 'AND ((project_no=a.project_no) OR (a.project_no=0)) ';
		$sql.= 'AND ((work_no=a.work_no) OR (a.work_no=0)) ';
		$sql.= 'AND ((customer_no=a.customer_no) OR (a.customer_no=0)) ';
		$sql.= 'AND ((process_no=a.process_no) OR (a.process_no=0)) ';
		$sql.= "AND action LIKE CONCAT('%',a.action,'%') ";
		$sql.= ' ) AS date_end ';
		$sql.= ',( ';
// 		$sql.= 'SELECT SUM(costin) FROM '.T_SCHEDULE.' ';
// 		$sql.= 'WHERE group_no=a.group_no AND uid=a.uid ';
// 		$sql.= 'AND ((project_no=a.project_no) OR (a.project_no=0)) ';
// 		$sql.= 'AND ((work_no=a.work_no) OR (a.work_no=0)) ';
// 		$sql.= 'AND ((customer_no=a.customer_no) OR (a.customer_no=0)) ';
// 		$sql.= 'AND ((process_no=a.process_no) OR (a.process_no=0)) ';
// 		$sql.= "AND action LIKE CONCAT('%',a.action,'%') ";
		$sql.= "''";
		$sql.= ' ) AS costin ';
		$sql.= ',( ';
// 		$sql.= 'SELECT SUM(costout) FROM '.T_SCHEDULE.' ';
// 		$sql.= 'WHERE group_no=a.group_no AND uid=a.uid ';
// 		$sql.= 'AND ((project_no=a.project_no) OR (a.project_no=0)) ';
// 		$sql.= 'AND ((work_no=a.work_no) OR (a.work_no=0)) ';
// 		$sql.= 'AND ((customer_no=a.customer_no) OR (a.customer_no=0)) ';
// 		$sql.= 'AND ((process_no=a.process_no) OR (a.process_no=0)) ';
// 		$sql.= "AND action LIKE CONCAT('%',a.action,'%') ";
		$sql.= "''";
		$sql.= ' ) AS costout ';

		$sql.= 'FROM '.$this->usertable.' a ';
		$sql.= 'LEFT JOIN '.T_PROJECT.' b ON a.project_no=b.no ';
		$sql.= 'LEFT JOIN '.T_WORK.' c ON a.work_no=c.no ';
		$sql.= 'LEFT JOIN '.T_CUSTOMER.' d ON a.customer_no=d.no ';
		$sql.= 'LEFT JOIN '.T_PROCESS.' e ON a.process_no=e.no ';
		$sql.= 'LEFT JOIN '.T_STATUS.' f ON a.status_no=f.no ';
		$sql.= 'WHERE a.group_no=:group_no AND a.uid=:uid ';
		$sql.= ') a ';

		return $sql;
	}

	//ＴＯＤＯリスト
	function getList($select_uid, $no = 0) {
		$list = false;

		//選択中のグループ
		$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;

		//ログイン中のユーザー
		$uid = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_uid() : '';

		//設定の取得（自分の設定を使う）
		$cmn = new CjCommonSQL($GLOBALS['dbopts']);
		$select_date = $_SESSION[S_SELECT_DATE];
		$select_day = $_SESSION[S_SELECT_DAY];
		$select_period = $cmn->getIni_data(INI_TODO_PERIOD,$uid);
		$check_past = $cmn->getIni_data(INI_TODO_PAST,$uid);

		$date_top = '';
		$date_end = '';
		switch ($select_period) {
			case 1:
				//年間
				$date_top = format($select_date,'Y/01/01');
				$tmp = new DateTime($date_top);
				$tmp->modify('+1 year');
				$tmp->modify('-1 day');
				$date_end = $tmp->format('Y/m/d');
				break;
			case 2:
				//月間
				$date_top = format($select_date,'Y/m/01');
				$tmp = new DateTime($date_top);
				$tmp->modify('+1 month');
				$tmp->modify('-1 day');
				$date_end = $tmp->format('Y/m/d');
				break;
			case 3:
				//週間
				$tmp = new DateTime($select_day);
				while ($tmp->format('w') != 1) {
					$tmp->modify('-1 day');
				}
				$date_top = $tmp->format('Y/m/d');
				while ($tmp->format('w') != 0) {
					$tmp->modify('+1 day');
				}
				$date_end = $tmp->format('Y/m/d');
				break;
			case 4:
				//日間
				$date_top = format($select_day,'Y/m/d');
				$date_end = $date_top;
				break;
			default:
				//期間に関わらず全て
				break;
		}

		//データベース接続
		$db = $this->DBOpen();
		if (!$db) {
			return false;
		}

		//読込
		try {
			$sql = $this->getTodoListSQL();

			$where = '';
			$date_max = 3;
			if ($no > 0) {

				$where.= 'no=:no ';
				$date_max = 2;

			} else {

				//対象期間にあるもの
				if ($date_top != '' && $date_end != '') {
					$where.= 'EXISTS (SELECT no FROM '.T_SCHEDULE.' ';
					$where.= 'WHERE group_no=a.group_no AND uid=a.uid ';
					$where.= 'AND date between :date_top3 AND :date_end3 ';
					$where.= 'AND ((project_no=a.project_no) OR (a.project_no=0)) ';
					$where.= 'AND ((work_no=a.work_no) OR (a.work_no=0)) ';
					$where.= 'AND ((customer_no=a.customer_no) OR (a.customer_no=0)) ';
					$where.= 'AND ((process_no=a.process_no) OR (a.process_no=0)) ';
					$where.= "AND action LIKE CONCAT('%',a.action,'%') ";
					$where.= ') ';
				} else {
					$date_max = 2;
				}

				//[終了][中止]でない項目はすべて表示
				if ($check_past == 1) {
					if (strlen($where) > 0) {
						$where.= 'OR ';
					}
					$where.= '(status_no!=2 AND status_no!=4) ';
				}

			}
			if (strlen($where) > 0) {
				$sql.= 'WHERE '.$where.' ';
			}

			$sql.= 'ORDER BY sortno ';

			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':uid', $select_uid);
			if ($no > 0) {
				$stmt->bindValue(':no', $no);
			}
			for ($i=1; $i<=$date_max; $i++) {
				$stmt->bindValue(':date_top'.$i, $date_top);
				$stmt->bindValue(':date_end'.$i, $date_end);
			}

			$stmt->execute();

			$list = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$item = array();
				foreach ($row as $key => $value) {
					$item[$key] = mb_convert_encoding($value, "UTF-8", "auto");
				}
				$item['date_top'] = !empty($item['date_top']) ? format($item['date_top'],'Y/m/d') : '';
				$item['date_end'] = !empty($item['date_end']) ? format($item['date_end'],'Y/m/d') : '';
				$list[] = $item;
			}
		} catch (PDOException $e) {
			$this->message = "ＴＯＤＯリストの読込に失敗しました。<br>".$e->getMessage();
			return false;
		}
		$stmt = null;
		$db = null;

		return $list;
	}

	function insertItem($select_uid, $project_no, $work_no, $customer_no, $process_no, $action, $sortno = '') {
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

			//空きno
			$sql = 'SELECT CASE (SELECT MIN(no) FROM '.$this->usertable.') WHEN 1 THEN MIN(A.no)+1 ELSE 1 END AS minvalue ';
			$sql.= 'FROM '.$this->usertable.' A ';
			$sql.= 'WHERE NOT EXISTS (SELECT no FROM '.$this->usertable.' AS B WHERE B.no=A.no+1)';
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$no = $row['minvalue'];

			//追加
			$sql = 'INSERT INTO '.$this->usertable.'(no, sortno, project_no, work_no, customer_no, process_no, action, status_no, uid, group_no) ';
			if (!empty($sortno)) {
				$sql.= 'VALUES(:no, :sortno, :project_no, :work_no, :customer_no, :process_no, :action, :status_no, :uid, :group_no)';
				$stmt = $db->prepare($sql);
				$stmt->bindValue(':sortno', $sortno);
			} else {
				$sql.= 'VALUES(:no, (SELECT MAX(sortno)+1 FROM '.$this->usertable.' WHERE uid=:uid AND group_no=:group_no) ';
				$sql.= ',:project_no, :work_no, :customer_no, :process_no, :action, :status, :uid, :group_no)';
				$stmt = $db->prepare($sql);
			}
			$stmt->bindValue(':no', $no);
			$stmt->bindValue(':project_no', $project_no);
			$stmt->bindValue(':work_no', $work_no);
			$stmt->bindValue(':customer_no', $customer_no);
			$stmt->bindValue(':process_no', $process_no);
			$stmt->bindValue(':action', $action);
			$stmt->bindValue(':status_no', 0);
			$stmt->bindValue(':uid', $select_uid);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->execute();

			//挿入した項目以降のsortnoを+1
			if (!empty($sortno)) {
				$sql = 'UPDATE '.$this->usertable.' SET sortno=sortno+1 WHERE uid=:uid AND group_no=:group_no AND sortno>=:sortno AND no<>:no ';
				$stmt = $db->prepare($sql);
				$stmt->bindValue(':uid', $select_uid);
				$stmt->bindValue(':group_no', $group_no);
				$stmt->bindValue(':sortno', $sortno);
				$stmt->bindValue(':no', $no);
				$stmt->execute();
			}

			$db->commit();
			$ret = $no;
		} catch(PDOException $e) {
			$this->message = 'TODOの追加に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		}
		$db = NULL;

		return $ret;
	}

	function updateItem($no, $project_no, $work_no, $customer_no, $process_no, $action) {
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

			$sql = 'UPDATE '.$this->usertable.' ';
			$sql.= 'SET project_no=:project_no, work_no=:work_no, customer_no=:customer_no, process_no=:process_no, action=:action ';
			$sql.= 'WHERE group_no=:group_no AND no=:no ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':project_no', $project_no);
			$stmt->bindValue(':work_no', $work_no);
			$stmt->bindValue(':customer_no', $customer_no);
			$stmt->bindValue(':process_no', $process_no);
			$stmt->bindValue(':action', $action);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':no', $no);
			$stmt->execute();

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$this->message = 'TODOの更新に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		}
		$db = NULL;

		return $ret;
	}

	function updateStatus($no, $status_no) {
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

			$sql = 'UPDATE '.$this->usertable.' ';
			$sql.= 'SET status_no=:status_no ';
			$sql.= 'WHERE group_no=:group_no AND no=:no ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':status_no', $status_no);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':no', $no);
			$stmt->execute();

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$this->message = 'TODOの更新に失敗しました。<br>'.$e->getMessage();
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

			$sql = 'DELETE FROM '.$this->usertable.' WHERE group_no=:group_no AND no=:no ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':no', $no);
			$stmt->execute();

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$this->message = 'TODOの削除に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		}
		$db = NULL;

		return $ret;
	}

	function moveItem($select_uid, $no, $before_sortno, $after_sortno) {
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
			$sql = 'SELECT MAX(sortno) AS maxno FROM '.$this->usertable.' WHERE uid=:uid AND group_no=:group_no ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':uid', $select_uid);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row['maxno'] < $after_sortno) {
				$ret = true;
				throw new Exception('');
			}

			//移動
			$sql = 'UPDATE '.$this->usertable.' SET sortno=:sortno WHERE uid=:uid AND group_no=:group_no AND no=:no ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':uid', $select_uid);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':no', $no);
			$stmt->bindValue(':sortno', $after_sortno);
			$stmt->execute();

			//移動後に空いたsortnoを詰める
			if ($before_sortno > $after_sortno) {
				//↑
				$sql = 'UPDATE '.$this->usertable.' SET sortno=sortno+1 WHERE uid=:uid AND group_no=:group_no AND sortno>=:after_sortno AND sortno<:before_sortno AND no<>:no ';
			} else {
				//↓
				$sql = 'UPDATE '.$this->usertable.' SET sortno=sortno-1 WHERE uid=:uid AND group_no=:group_no AND sortno>:before_sortno AND sortno<=:after_sortno AND no<>:no ';
			}
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':uid', $select_uid);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':no', $no);
			$stmt->bindValue(':before_sortno', $before_sortno);
			$stmt->bindValue(':after_sortno', $after_sortno);
			$stmt->execute();

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$this->message = 'TODOの並び替えに失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		} catch(Exception $e) {
			$this->message = $e->getMessage();
			$db->rollBack();
		}
		$db = NULL;

		return $ret;
	}

	function getStatus_list() {
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
			$sql = 'SELECT no, name ';
			$sql.= 'FROM '.T_STATUS.' ';
			$sql.= 'ORDER BY sortno ';
			$stmt = $db->prepare($sql);
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
			$this->message = "状況項目の読込に失敗しました。<br>".$e->getMessage();
			return $list;
		}
		$stmt = null;
		$db = null;

		return $list;
	}

	function getPeriod_list() {
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
			$sql = 'SELECT no, name ';
			$sql.= 'FROM '.T_PERIOD.' ';
			$sql.= 'ORDER BY sortno ';
			$stmt = $db->prepare($sql);
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
			$this->message = "期間項目の読込に失敗しました。<br>".$e->getMessage();
			return $list;
		}
		$stmt = null;
		$db = null;

		return $list;
	}

	function getTodoField_list($select_uid) {
		$list = false;

		//選択中のグループ
		$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;

		//設定の取得
		$cmn = new CjCommonSQL($GLOBALS['dbopts']);
		$todosort =  $cmn->getIni_data(INI_TODO_SORT,$select_uid);
		$todobundle =  $cmn->getIni_data(INI_TODO_BUNDLE,$select_uid);

		//データベース接続
		$db = $this->DBOpen();
		if (!$db) {
			return false;
		}

		//読込
		try {
			$sql = 'SELECT sort_id, name, show_id ';
			$sql.= 'FROM '.T_TODOFIELD.' ';
			$sql.= 'ORDER BY sortno ';
			$stmt = $db->prepare($sql);
			$stmt->execute();

			$list = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$item = array();
				foreach ($row as $key => $value) {
					$item[$key] = mb_convert_encoding($value, "UTF-8", "auto");
				}
				$list[$item['sort_id']] = $item;
			}
		} catch (PDOException $e) {
			$this->message = "期間項目の読込に失敗しました。<br>".$e->getMessage();
			return $list;
		}
		$stmt = null;
		$db = null;

		//並び替え
		$field_list = array();
		if (!empty($todosort)) {
			$sort_list = str_getcsv($todosort);
			foreach ($sort_list as $sort_row) {
				foreach ($list as $row) {
					if ($row['sort_id'] == $sort_row) {
						$field_list[] = $row;
						unset($list[$sort_row]);
						break;
					}
				}
			}
			if (count($list) > 0) {
				$field_list = array_merge($field_list, $list);
			}
		} else {
			$field_list = $list;
		}

		//括り列bundleを追加
		$bundle_list = str_getcsv($todobundle);
		$list = array();
		foreach ($field_list as $row) {
			$row['bundle'] = 0;
			foreach ($bundle_list as $bundle_row) {
				if ($row['sort_id'] == $bundle_row) {
					$row['bundle'] = 1;
				}
			}
			$list[] = $row;
		}

		return $list;
	}

	function saveSort($select_uid, $sort_list, $bundle_list) {

		//設定の保存
		$cmn = new CjCommonSQL($GLOBALS['dbopts']);

		//CSV形式にする
		$sortcsv = '';
		foreach ($sort_list as $row) {
			if (strlen($sortcsv) > 0) {
				$sortcsv.= ',';
			}
			$sortcsv.= $row;
		}
		$cmn->setIni_data(INI_TODO_SORT, $sortcsv, $select_uid);

		$bundlecsv = '';
		foreach ($bundle_list as $row) {
			if (strlen($bundlecsv) > 0) {
				$bundlecsv.= ',';
			}
			$bundlecsv.= $row;
		}
		$cmn->setIni_data(INI_TODO_BUNDLE, $bundlecsv, $select_uid);

		//選択中のグループ
		$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;

		//データベース接続
		$db = $this->DBOpen();
		if (!$db) {
			return false;
		}

		try {
			$db->beginTransaction();

			//並び替えたレコードを取得
			$sql = $this->getTodoListSQL(false);
			$sql.= 'ORDER BY '.$sortcsv.' ';

			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':uid', $select_uid);
			$stmt->execute();

			$list = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$list[] = $row['no'];
			}

			//sortnoを更新
			$sortno = 1;
			foreach ($list as $no) {
				$sql = 'UPDATE '.T_TODO.' ';
				$sql.= 'SET sortno=:sortno ';
				$sql.= 'WHERE no=:no AND group_no=:group_no AND uid=:uid ';

				$stmt = $db->prepare($sql);
				$stmt->bindValue(':no', $no);
				$stmt->bindValue(':group_no', $group_no);
				$stmt->bindValue(':uid', $select_uid);
				$stmt->bindValue(':sortno', $sortno);
				$stmt->execute();

				$sortno++;
			}

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$this->message = 'TODOの更新に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		}
		$db = NULL;

		return $ret;
	}
}
