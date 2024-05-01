<?php
require_once(dirname(__FILE__).'/../../define.php');
require_once(dirname(__FILE__).'/../../Encode.php');
require_once(dirname(__FILE__).'/GroupCommonSQL.class.php');
require_once(dirname(__FILE__).'/../../cj/class/WorkListSQL.class.php');
require_once(dirname(__FILE__).'/../../cj/class/CustomerListSQL.class.php');
require_once(dirname(__FILE__).'/../../cj/class/ProcessListSQL.class.php');

class GroupListSQL {

	protected $opts = array('dsn'=>'', 'db_user'=>'', 'db_pwd'=>0);
	protected $usertable = T_GROUP;
	protected $message = '';
	protected $timeout = 15;

	function __construct($opts = null) {
		// データベース接続に使用するオプション値の初期設定
		foreach ($this->opts as $key => $value) { // オプション設定
			$this->opts[$key] = isset($opts[$key]) ? $opts[$key] : $value;
		}
	}

	function getMessage() {
		return $this->message;
	}

	function getList() {
		$db = NULL;
		$list = array();
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$sql = 'SELECt a.no AS group_no, a.name AS group_name, a.flg AS group_flg, b.status AS member_status, ';
			$sql.= 'c.no AS role_no, c.name AS role_name, c.user AS role_user ';
			$sql.= 'FROM '.$this->usertable.' a INNER JOIN '.T_GROUP_MEMBER.' b ON a.no=b.group_no ';
			$sql.= 'INNER JOIN '.T_ROLE.' c ON b.group_no=c.group_no AND b.role_no=c.no ';
			$sql.= 'WHERE b.uid=:uid';

			$stmt = $db->prepare($sql);
			$stmt->bindValue(':uid', $GLOBALS['auth']->getParent_uid());
			$stmt->execute();	 // クエリー実行

			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				foreach ($row as $key => $value) {
					$item[$key] = mb_convert_encoding($value, "UTF-8", "auto");
				}
				$list[] = $item;
			}
		} catch (PDOException $e) {
			$this->message = "グループリストの読込に失敗しました。<br>".$e->getMessage();
		}
		$db = NULL;
		return $list ? $list : false;
	}

	function getItem($no) {
		$db = NULL;
		$item = array();
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$sql = 'SELECt a.no AS group_no, a.name AS group_name, a.flg AS group_flg, c.no AS role_no, c.name AS role_name ';
			$sql.= 'FROM '.$this->usertable.' a INNER JOIN '.T_GROUP_MEMBER.' b ON a.no=b.group_no ';
			$sql.= 'INNER JOIN '.T_ROLE.' c ON b.group_no=c.group_no AND b.role_no=c.no ';
			$sql.= 'WHERE a.no=:no';

			$stmt = $db->prepare($sql);
			$stmt->bindValue(':no', $no);
			$stmt->execute();	 // クエリー実行

			$row = $stmt->fetch(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			$this->message = "グループ情報の読込に失敗しました。<br>".$e->getMessage();
		}
		if ($row) {
			foreach ($row as $key => $value) {
				$item[$key] = mb_convert_encoding($value, "UTF-8", "auto");
			}
		}
		$db = NULL;
		return $item;
	}

	function getHome($uid) {
		$ret = 0;
		$db = NULL;
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$sql = 'SELECt a.no AS group_no ';
			$sql.= 'FROM '.$this->usertable.' a INNER JOIN '.T_GROUP_MEMBER.' b ON a.no=b.group_no ';
			$sql.= 'WHERE b.uid=:uid AND a.flg=0';

			$stmt = $db->prepare($sql);
			$stmt->bindValue(':uid', $uid);
			$stmt->execute();	 // クエリー実行

			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$ret = $row['group_no'];
		} catch (PDOException $e) {
			$this->message = "ホームグループの取得に失敗しました。<br>".$e->getMessage();
		}
		$db = NULL;
		return $ret;
	}

	function addItem($flg, $uid = '', $group_name = '') {
		$ret = 0;
		$db = NULL;
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$db->beginTransaction();

			//空きnoを取得
			$sql = 'SELECT CASE (SELECT MIN(no) FROM '.$this->usertable.') WHEN 1 THEN MIN(A.no)+1 ELSE 1 END AS minvalue ';
			$sql.= 'FROM '.$this->usertable.' A ';
			$sql.= 'WHERE NOT EXISTS (SELECT no FROM '.$this->usertable.' AS B WHERE B.no=A.no+1)';
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$group_no = $row['minvalue'];

			//グループ
			$sql = 'INSERT INTO '.$this->usertable.'(no, name, flg) ';
			$sql.= 'VALUES(:group_no, :name, :flg)';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':name', $group_name);
			$stmt->bindValue(':flg', $flg);
			$stmt->execute();

			//メンバー
			$sql = 'INSERT INTO '.T_GROUP_MEMBER.'(group_no, uid, role_no, status) ';
			$sql.= 'VALUES(:group_no, :uid, 1, 0)';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':uid', $uid);
			$stmt->execute();

			//権限
			$sql = 'INSERT INTO '.T_ROLE.'(group_no, no, name, sortno, user, work, customer, process, todo, schedule, cost, kintai) ';
			$sql.= 'VALUES(:group_no, 0, "無効", 1, 0, 0, 0, 0, 0, 0, 0, 0)';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->execute();

			//権限(管理者)
			$sql = 'INSERT INTO '.T_ROLE.'(group_no, no, name, sortno, user, work, customer, process, todo, schedule, cost, kintai) ';
			$sql.= 'VALUES(:group_no, 1, "管理者", 2 ';
			$sql.= ',CAST(0xFF AS UNSIGNED) ';
			$sql.= ',CAST(0xFF AS UNSIGNED) ';
			$sql.= ',CAST(0xFF AS UNSIGNED) ';
			$sql.= ',CAST(0xFF AS UNSIGNED) ';
			$sql.= ',CAST(0xFF AS UNSIGNED) ';
			$sql.= ',CAST(0xFF AS UNSIGNED) ';
			$sql.= ',CAST(0xFF AS UNSIGNED) ';
			$sql.= ',CAST(0xFF AS UNSIGNED)) ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->execute();

			//権限(一般)
			$sql = 'INSERT INTO '.T_ROLE.'(group_no, no, name, sortno, user, work, customer, process, todo, schedule, cost, kintai) ';
			$sql.= 'VALUES(:group_no, 2, "一般", 3 ';
			$sql.= ',CAST(0x1F AS UNSIGNED) ';
			$sql.= ',CAST(0x11 AS UNSIGNED) ';
			$sql.= ',CAST(0x11 AS UNSIGNED) ';
			$sql.= ',CAST(0x11 AS UNSIGNED) ';
			$sql.= ',CAST(0x1F AS UNSIGNED) ';
			$sql.= ',CAST(0x1F AS UNSIGNED) ';
			$sql.= ',CAST(0x1F AS UNSIGNED) ';
			$sql.= ',CAST(0x1F AS UNSIGNED)) ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->execute();

			//プロジェクトマスタの初期値を登録
			$work = new WorkListSQL($GLOBALS['dbopts']);
			$project_no = $work->insertProject('プロジェクト', 1, $group_no);
			if (!$project_no) {
				throw new Exception($work->getMessage());
			}

			//作業マスタの初期値を登録
			$work->insertWork($project_no, '作業', 1, $group_no);

			//顧客マスタの初期値を登録
			$customer = new CustomerListSQL($GLOBALS['dbopts']);
			$customer->insertItem('顧客', 1, $group_no);

			//工程マスタの初期値を登録
			$process = new ProcessListSQL($GLOBALS['dbopts']);
			$process->insertItem('移動', 1, $group_no);
			$process->insertItem('営業', 2, $group_no);
			$process->insertItem('調査', 3, $group_no);
			$process->insertItem('設計', 4, $group_no);
			$process->insertItem('製造', 5, $group_no);
			$process->insertItem('試験', 6, $group_no);
			$process->insertItem('出荷', 7, $group_no);
			$process->insertItem('保守', 8, $group_no);
			$process->insertItem('会議', 9, $group_no);
			$process->insertItem('事務', 10, $group_no);
			$process->insertItem('雑務', 11, $group_no);

			$db->commit();
			$ret = $group_no;
		} catch(PDOException $e) {
			$this->message = 'グループの登録に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		}
		$db = NULL;
		return $ret;
	}

	function updateItem() {
		$ret = false;
		$db = NULL;
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$db->beginTransaction();

			$sql = 'UPDATE '.$this->usertable.' SET name=:name WHERE no=:no';

			$stmt = $db->prepare($sql);
			$stmt->bindValue(':no', $_POST['group_edit_no']);
			$stmt->bindValue(':name', $_POST['group_edit_name']);
			$stmt->execute();

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$this->message = 'グループの更新に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		}
		$db = NULL;
		return $ret;
	}

	function deleteItem() {
		$ret = false;
		$db = NULL;
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$db->beginTransaction();

			// 他にメンバーが居れば削除不可とする
			$sql = 'SELECT uid FROM '.T_GROUP_MEMBER.' WHERE group_no=:group_no AND uid<>:uid FOR UPDATE';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $_POST['group_edit_no']);
			$stmt->bindValue(':uid',  $GLOBALS['auth']->getParent_uid());
			$stmt->execute();

			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row) {
				throw new Exception('グループ名「'.$_POST['group_edit_name'].'」は、他にメンバーが居るため削除できません。');
			}

			GroupCommonSQL::deleteGroupMember($db, $_POST['group_edit_no'], $GLOBALS['auth']->getParent_uid());
			GroupCommonSQL::deleteGroup($db, $_POST['group_edit_no']);

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$this->message = 'グループの削除に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		} catch(Exception $e) {
			$this->message = $e->getMessage();
			$db->rollBack();
		}
		$db = NULL;
		return $ret;
	}
}