<?php
require_once(dirname(__FILE__).'/../../define.php');
require_once(dirname(__FILE__).'/../../Encode.php');

class RoleListSQL {

	protected $opts = array('dsn'=>'', 'db_user'=>'', 'db_pwd'=>0);
	protected $usertable = T_ROLE;
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

	//権限リスト
	function getList() {
		$list = array();

		//選択中のグループ
		$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;

		//データベース接続
		$db = $this->DBOpen();
		if (!$db) {
			return false;
		}

		try {
			$sql = 'SELECT no, name, sortno, user, work, customer, process, todo, schedule, cost, kintai ';
			$sql.= 'FROM '.$this->usertable.' WHERE group_no=:group_no ORDER BY sortno ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				foreach ($row as $key => $value) {
					$item[$key] = mb_convert_encoding($value, "UTF-8", "auto");
				}
				$list[] = $item;
			}
		} catch (PDOException $e) {
			$this->message = "権限リストの読込に失敗しました。<br>".$e->getMessage();
		}
		$db = null;

		return $list ? $list : false;
	}

	//属性名
	function getAttributeName($attr) {
		$list = array();

		$item = array();
		$item['attr'] = 0;
		$item['name'] = '';

		if (($attr & ROLE_VIW) != 0) {
			$attr = $attr - ROLE_VIW;
			$item['attr'] = ROLE_VIW;
			$item['name'] = 'R';
			$list[] = $item;
		}
		if (($attr & ROLE_EDT) != 0) {
			$attr = $attr - ROLE_EDT;
			$item['attr'] = ROLE_EDT;
			$item['name'] = 'W';
			$list[] = $item;
		}
		if (($attr & ROLE_ADD) != 0) {
			$attr = $attr - ROLE_ADD;
			$item['attr'] = ROLE_ADD;
			$item['name'] = '+';
			$list[] = $item;
		}
		if (($attr & ROLE_DEL) != 0) {
			$attr = $attr - ROLE_DEL;
			$item['attr'] = ROLE_DEL;
			$item['name'] = '-';
			$list[] = $item;
		}

		if (($attr & ROLE_MEMBER_VIW) != 0) {
			$attr = $attr - ROLE_MEMBER_VIW;
			$item['attr'] = ROLE_MEMBER_VIW;
			$item['name'] = 'R';
			$list[] = $item;
		}
		if (($attr & ROLE_MEMBER_EDT) != 0) {
			$attr = $attr - ROLE_MEMBER_EDT;
			$item['attr'] = ROLE_MEMBER_EDT;
			$item['name'] = 'W';
			$list[] = $item;
		}
		if (($attr & ROLE_MEMBER_ADD) != 0) {
			$attr = $attr - ROLE_MEMBER_ADD;
			$item['attr'] = ROLE_MEMBER_ADD;
			$item['name'] = '+';
			$list[] = $item;
		}
		if (($attr & ROLE_MEMBER_DEL) != 0) {
			$attr = $attr - ROLE_MEMBER_DEL;
			$item['attr'] = ROLE_MEMBER_DEL;
			$item['name'] = '-';
			$list[] = $item;
		}

		return $list;
	}

	function insertItem($name, $sortno = '') {
		$ret = false;

		//選択中のグループ
		$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;

		//POSTデータ
		$user = $_POST['post_user'] & 0xFF;
		$work = $_POST['post_work'] & 0xFF;
		$customer = $_POST['post_customer'] & 0xFF;
		$process = $_POST['post_process'] & 0xFF;
		$todo = $_POST['post_todo'] & 0xFF;
		$schedule = $_POST['post_schedule'] & 0xFF;
		$cost = $_POST['post_cost'] & 0xFF;
		$kintai = $_POST['post_kintai'] & 0xFF;

		//データベース接続
		$db = $this->DBOpen();
		if (!$db) {
			return false;
		}

		try {
			$db->beginTransaction();

			//空きno
			//$sql = 'SELECT CASE (SELECT MIN(no) FROM '.$this->usertable.' WHERE group_no=A.group_no) WHEN 1 THEN MIN(A.no)+1 ELSE 1 END AS minvalue ';
			//$sql.= 'FROM '.$this->usertable.' A ';
			//$sql.= 'WHERE NOT EXISTS (SELECT no FROM '.$this->usertable.' AS B WHERE B.no=A.no+1 AND B.group_no=A.group_no) AND A.group_no=:group_no';
			//$stmt = $db->prepare($sql);
			//$stmt->bindValue(':group_no', $group_no);
			//$stmt->execute();
			//$row = $stmt->fetch(PDO::FETCH_ASSOC);
			//$no = $row['minvalue'];

			//グループごとのno(最大値＋１)
			$sql = 'SELECT MAX(no)+1 AS minvalue ';
			$sql.= 'FROM '.$this->usertable.' A ';
			$sql.= 'WHERE A.group_no=:group_no';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
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
			$this->message = '権限マスタの追加に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		}
		$db = NULL;

		return $ret;
	}

	function updateItem($no, $name) {
		$ret = false;

		//選択中のグループ
		$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;

		//POSTデータ
		$user = $_POST['post_user'] & 0xFF;
		$work = $_POST['post_work'] & 0xFF;
		$customer = $_POST['post_customer'] & 0xFF;
		$process = $_POST['post_process'] & 0xFF;
		$todo = $_POST['post_todo'] & 0xFF;
		$schedule = $_POST['post_schedule'] & 0xFF;
		$cost = $_POST['post_cost'] & 0xFF;
		$kintai = $_POST['post_kintai'] & 0xFF;

		//管理者の権限は削除できないようにする
		if ($no == 1) {
			$user = 0xFF;
			$work = 0xFF;
			$customer = 0xFF;
			$process = 0xFF;
			$todo = 0xFF;
			$schedule = 0xFF;
			$cost = 0xFF;
			$kintai = 0xFF;
		}

		//データベース接続
		$db = $this->DBOpen();
		if (!$db) {
			return false;
		}

		try {
			$db->beginTransaction();

			$sql = 'UPDATE '.$this->usertable.' ';
			$sql.= 'SET name=:name, user=:user, work=:work, customer=:customer, process=:process, todo=:todo, schedule=:schedule, cost=:cost, kintai=:kintai ';
			$sql.= 'WHERE group_no=:group_no AND no=:no ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':no', $no);
			$stmt->bindValue(':name', $name);
			$stmt->bindValue(':user', $user);
			$stmt->bindValue(':work', $work);
			$stmt->bindValue(':customer', $customer);
			$stmt->bindValue(':process', $process);
			$stmt->bindValue(':todo', $todo);
			$stmt->bindValue(':schedule', $schedule);
			$stmt->bindValue(':cost', $cost);
			$stmt->bindValue(':kintai', $kintai);
			$stmt->execute();

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$this->message = '権限マスタの更新に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		}
		$db = NULL;

		return $ret;
	}

	function deleteItem($no) {
		$ret = false;

		if ($no <= 2) {
			$this->message = 'この権限は削除できません。';
			return false;
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

			$sql = 'SELECT ';
			$sql.= '(SELECT COUNT(group_no) FROM '.T_GROUP_MEMBER.' WHERE group_no=:group_no AND role_no=:role_no) AS member_use ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':role_no', $no);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row['member_use'] > 0) {
				throw new Exception('メンバーで使用されているため削除できません。');
			}

			$sql = 'DELETE FROM '.$this->usertable.' WHERE group_no=:group_no AND no=:no ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':no', $no);
			$stmt->execute();

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$this->message = '権限マスタの削除に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		} catch(Exception $e) {
			$this->message = $e->getMessage();
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
			$this->message = '権限マスタの並び替えに失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		}
		$db = NULL;

		return $ret;
	}
}