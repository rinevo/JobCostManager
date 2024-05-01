<?php
require_once(dirname(__FILE__).'/../../define.php');
require_once(dirname(__FILE__).'/../../Encode.php');
require_once(dirname(__FILE__).'/EncryptSupport.class.php');
require_once(dirname(__FILE__).'/UserList.class.php');
require_once(dirname(__FILE__).'/GroupCommonSQL.class.php');

class UserListSQL extends UserList {

	protected $opts = array('dsn'=>'', 'db_user'=>'', 'db_pwd'=>0);
	protected $usertable = T_USER;
	protected $message = '';
	protected $timeout = 15;

	function __construct($opts = null) {
		parent::__construct($opts);

		// データベース接続に使用するオプション値の初期設定
		foreach ($this->opts as $key => $value) { // オプション設定
			$this->opts[$key] = isset($opts[$key]) ? $opts[$key] : $value;
		}
	}

	function getMessage() {
		return $this->message;
	}

	function getUserList() {
		$db = NULL;
		$list = array();
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$sql = 'SELECT a.uid, a.passwd, a.param, a.mail, a.status, a.name, a.group_no_default, ';
			$sql.= 'b.group_no, c.name AS group_name, b.status AS member_status, b.role_no, d.name AS role_name, ';
			$sql.= 'd.user AS role_user, d.work AS role_work, d.customer AS role_customer, d.process AS role_process, d.todo AS role_todo, d.schedule AS role_schedule, d.cost AS role_cost, d.kintai AS role_kintai ';
			$sql.= 'FROM '.$this->usertable.' a LEFT JOIN '.T_GROUP_MEMBER.' b ON a.uid=b.uid ';
			$sql.= 'LEFT JOIN '.T_GROUP.' c ON b.group_no=c.no LEFT JOIN '.T_ROLE.' d ON b.group_no=d.group_no AND b.role_no=d.no ';
			$sql.= 'WHERE b.group_no=:group_no';

			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no',  isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0);
			$stmt->execute();	 // クエリー実行

			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				foreach ($row as $key => $value) {
					$info[$key] = mb_convert_encoding($value, "UTF-8", "auto");
				}
				$info['passwd'] = $this->enc->DecodeString($info['passwd']);
				$list[] = $info;
			}
		} catch (PDOException $e) {
			$this->message = "ユーザーリストの読込に失敗しました。<br>".$e->getMessage();
		}
		$db = NULL;
		return $list ? $list : false;
	}

	function getParentInfo($uid) {
		$this->setParent($this->getUserInfo($uid));
	}

	function getUserInfo($id, $key = 'uid', $select_group_no = null) {
		$db = NULL;
		$item = array();
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$stmt = null;

			switch ($key) {
				case 'uid':

					$group_no = ($select_group_no !== null) ? $select_group_no : (isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0);

					if ($group_no > 0) {
						$sql = 'SELECT a.uid, a.passwd, a.param, a.mail, a.status, a.name, a.group_no_default, ';
						$sql.= 'b.group_no, c.name AS group_name, b.status AS member_status, b.role_no, d.name AS role_name, ';
						$sql.= 'd.user AS role_user, d.work AS role_work, d.customer AS role_customer, d.process AS role_process, d.todo AS role_todo, d.schedule AS role_schedule, d.cost AS role_cost, d.kintai AS role_kintai ';
						$sql.= 'FROM '.$this->usertable.' a LEFT JOIN '.T_GROUP_MEMBER.' b ON a.uid=b.uid ';
						$sql.= 'LEFT JOIN '.T_GROUP.' c ON b.group_no=c.no LEFT JOIN '.T_ROLE.' d ON b.group_no=d.group_no AND b.role_no=d.no ';
						$sql.= 'WHERE a.uid=:uid AND b.group_no=:group_no';
					} else {
						$sql = 'SELECT a.uid, a.passwd, a.param, a.mail, a.status, a.name, a.group_no_default, ';
						$sql.= '0 AS group_no, "" AS group_name, "" AS member_status, 0 AS role_no, "" AS role_name, ';
						$sql.= '0 AS role_user, 0 AS role_work, 0 AS role_customer, 0 AS role_process, 0 AS role_todo, 0 AS role_schedule, 0 AS role_cost, 0 AS role_kintai ';
						$sql.= 'FROM '.$this->usertable.' a ';
						$sql.= 'WHERE a.uid=:uid ';
					}

					$stmt = $db->prepare($sql);
					$stmt->bindValue(':uid', $id);
					if ($group_no > 0) {
						$stmt->bindValue(':group_no',  $group_no);
					}

					break;

				case 'mail':

					$sql = 'SELECT a.uid, a.passwd, a.param, a.mail, a.status, a.name, a.group_no_default, ';
					$sql.= 'b.group_no, c.name AS group_name, b.status AS member_status, b.role_no, d.name AS role_name, ';
					$sql.= 'd.user AS role_user, d.work AS role_work, d.customer AS role_customer, d.process AS role_process, d.todo AS role_todo, d.schedule AS role_schedule, d.cost AS role_cost, d.kintai AS role_kintai ';
					$sql.= 'FROM '.$this->usertable.' a LEFT JOIN '.T_GROUP_MEMBER.' b ON a.uid=b.uid ';
					$sql.= 'LEFT JOIN '.T_GROUP.' c ON b.group_no=c.no LEFT JOIN '.T_ROLE.' d ON b.group_no=d.group_no AND b.role_no=d.no ';
					$sql.= 'WHERE a.mail=:mail';

					$stmt = $db->prepare($sql);
					$stmt->bindValue(':mail', $id);

					break;

				default:
					return $item;
			}

			$stmt->execute();	 // クエリー実行

			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row) {
				foreach ($row as $key => $value) {
					$item[$key] = $value;
				}
				$item['passwd'] = $this->enc->DecodeString($item['passwd']);
			}
		} catch (PDOException $e) {
			$this->message = "ユーザー情報の読込に失敗しました。<br>".$e->getMessage();
		}
		$db = NULL;
		return $item;
	}

	function addUser() {
		$ret = false;
		$db = NULL;
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$status = isset($_POST['user_edit_status']) ? $_POST['user_edit_status'] : 0;
			$param = ($status == 1) ? md5(uniqid()) : '';

			$sql = 'INSERT INTO '.$this->usertable.'(uid, passwd, param, mail, status, name, update_uid, group_no_default, update_time) ';
			$sql.= 'VALUES(:uid, :passwd, :param, :mail, :status, :name, :update_uid, :group_no_default, NOW())';

			$stmt = $db->prepare($sql);
			$stmt->bindValue(':uid', $_POST['user_edit_uid']);
			$stmt->bindValue(':passwd', $this->enc->EncodeString($_POST['user_edit_passwd']));
			$stmt->bindValue(':param', $param);
			$stmt->bindValue(':mail', isset($_POST['user_edit_mail']) ? $_POST['user_edit_mail'] : '');
			$stmt->bindValue(':status', $status);
			$stmt->bindValue(':name', isset($_POST['user_edit_name']) ? $_POST['user_edit_name'] : $_POST['user_edit_uid']);
			$stmt->bindValue(':update_uid', isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_uid() : '');
			$stmt->bindValue(':group_no_default', 0);
			$stmt->execute();

			// グループへの登録
			$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;
			if ($group_no > 0) {
				$sql = 'INSERT INTO '.T_GROUP_MEMBER.'(group_no, uid, role_no, status) ';
				$sql.= 'VALUES(:group_no, :uid, :role_no, :status)';

				$stmt = $db->prepare($sql);
				$stmt->bindValue(':group_no', $group_no);
				$stmt->bindValue(':uid', $_POST['user_edit_uid']);
				$stmt->bindValue(':role_no', $_POST['user_edit_role']);
				$stmt->bindValue(':status', GROUP_MEMBER_STATUS_ENTRY);
				$stmt->execute();
			}

			$ret = true;
		} catch(PDOException $e) {
			$code = $e->getCode();
			switch ($code) {
				case 23000:
					$this->message = e($_POST['user_edit_uid']).' は既に登録されています。';
					break;
				default:
					$this->message = 'ユーザーの登録に失敗しました。<br>'.$e->getMessage();
					break;
			}
		}
		$db = NULL;
		return $ret;
	}

	function enabledUser() {
		$ret = false;
		$db = NULL;
		if (!isset($_POST['user_edit_uid']) || !isset($_POST['user_edit_newuid']) || !isset($_POST['user_edit_passwd']) || !isset($_POST['user_edit_name'])) {
			$this->message = 'ユーザー情報の更新に失敗しました。<br>不正な要求です。情報が不足しています。';
			return false;
		}
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$db->beginTransaction();

			$sql = 'UPDATE '.$this->usertable.' ';
			$sql.= 'SET update_uid=:update_uid, update_time=NOW(), param="", status=0 ';
			$sql.= ',uid=:newuid ';
			$sql.= ',passwd=:passwd ';
			$sql.= ',name=:name ';
			$sql.= 'WHERE uid=:uid';

			$stmt = $db->prepare($sql);
			$stmt->bindValue(':uid', $_POST['user_edit_uid']);
			$stmt->bindValue(':newuid', $_POST['user_edit_newuid']);
			$stmt->bindValue(':passwd', $this->enc->EncodeString($_POST['user_edit_passwd']));
			$stmt->bindValue(':name', $_POST['user_edit_name']);
			$stmt->bindValue(':update_uid', $_POST['user_edit_newuid']);
			$stmt->execute();

			$sql = 'UPDATE '.T_GROUP_MEMBER.' ';
			$sql.= 'SET uid=:newuid ';
			$sql.= 'WHERE uid=:uid';

			$stmt = $db->prepare($sql);
			$stmt->bindValue(':uid', $_POST['user_edit_uid']);
			$stmt->bindValue(':newuid', $_POST['user_edit_newuid']);
			$stmt->execute();

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$code = $e->getCode();
			switch ($code) {
				case 23000:
					$this->message = 'ユーザー名「'.e($_POST['user_edit_newuid']).'」は既に登録されています。';
					break;
				default:
					$this->message = 'ユーザー情報の更新に失敗しました。<br>'.$e->getMessage();
					break;
			}
			$db->rollBack();
		}
		$db = NULL;
		return $ret;
	}

	function updateUser() {
		$ret = false;
		$db = NULL;
		if (!isset($_POST['user_edit_uid'])) {
			return false;
		}
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$db->beginTransaction();

			$parent_uid = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_uid() : $_POST['user_edit_uid'];
			$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;

			//ロックがタイムアウトしていないかチェック
			if ($group_no > 0) {
				$sql = 'SELECT a.uid AS lock_uid, a.time AS lock_time, b.update_uid ';
				$sql.= 'FROM '.T_LOCK.' a LEFT JOIN '.$this->usertable.' b ON a.data_no=b.uid ';
				$sql.= 'WHERE ';
				$sql.= 'a.group_no=:group_no AND ';
				$sql.= 'a.data='.LOCK_USER.' AND a.data_no=:data_no FOR UPDATE';

				$stmt = $db->prepare($sql);
				$stmt->bindValue(':group_no', $group_no);
				$stmt->bindValue(':data_no', $_POST['user_edit_uid']);
				$stmt->execute();

				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				if ($row['lock_uid'] != $GLOBALS['auth']->getParent_uid()) {
					$alert = $this->timeout.'分経過したため排他制御が解除されました。<br><br>';
					if (strlen($row['lock_uid']) > 0) {
						throw new Exception($alert.e($_POST['user_edit_uid']).' は、'.e($row['lock_uid'])." が編集中です。");
					}
					if (($row['update_uid'] != $GLOBALS['auth']->getParent_uid()) && (strlen($row['update_uid']) > 0)) {
						throw new Exception($alert.e($_POST['user_edit_uid']).' は、'.e($row['update_uid'])." により更新されています。");
					}
				}
			}

			//グループの管理者が居なくなることを防ぐ制御
			if (($group_no > 0) && isset($_POST['user_edit_role'])) {
				if ($_POST['user_edit_role'] <> 1) {
					//現在の権限（入会済のメンバー）
					$sql = 'SELECT role_no FROM '.T_GROUP_MEMBER.' ';
					$sql.= 'WHERE group_no=:group_no AND uid=:uid AND status=:status ';

					$stmt = $db->prepare($sql);
					$stmt->bindValue(':group_no', $group_no);
					$stmt->bindValue(':uid', $_POST['user_edit_uid']);
					$stmt->bindValue(':status', GROUP_MEMBER_STATUS_NOMAL);
					$stmt->execute();
					$row = $stmt->fetch(PDO::FETCH_ASSOC);

					if (isset($row['role_no']) && $row['role_no'] == 1) {
						//管理者の人数（入会済のメンバー）
						$sql = 'SELECT COUNT(role_no) AS kensu FROM '.T_GROUP_MEMBER.' ';
						$sql.= 'WHERE group_no=:group_no AND role_no=1 AND status=:status ';

						$stmt = $db->prepare($sql);
						$stmt->bindValue(':group_no', $group_no);
						$stmt->bindValue(':status', GROUP_MEMBER_STATUS_NOMAL);
						$stmt->execute();
						$row = $stmt->fetch(PDO::FETCH_ASSOC);

						if ($row['kensu'] < 2) {
							throw new Exception('最低１名の管理者は必要です。<br>このユーザーの権限を変更したい場合は、他のユーザーを管理者に設定してから行ってください。');
						}
					}
				}
			}

			$sql = 'UPDATE '.$this->usertable.' ';
			$sql.= 'SET update_uid=:update_uid, update_time=NOW() ';
			if (isset($_POST['user_edit_passwd']) && (strlen($_POST['user_edit_passwd']) > 0)) {
				$sql.= ',passwd=:passwd ';
			}
			if (isset($_POST['user_edit_param'])) {
				$sql.= ',param=:param ';
			}
			if (isset($_POST['user_edit_mail']) && (strlen($_POST['user_edit_mail']) > 0)) {
				$sql.= ',mail=:mail ';
			}
			if (isset($_POST['user_edit_status'])) {
				$sql.= ',status=:status ';
			}
			if (isset($_POST['user_edit_name']) && (strlen($_POST['user_edit_name']) > 0)) {
				$sql.= ',name=:name ';
			}
			if (isset($_POST['user_edit_group_no_default'])) {
				$sql.= ',group_no_default=:group_no_default ';
			}
			$sql.= 'WHERE uid=:uid';

			$stmt = $db->prepare($sql);
			$stmt->bindValue(':uid', $_POST['user_edit_uid']);
			if (isset($_POST['user_edit_passwd']) && (strlen($_POST['user_edit_passwd']) > 0)) {
				$stmt->bindValue(':passwd', $this->enc->EncodeString($_POST['user_edit_passwd']));
			}
			if (isset($_POST['user_edit_param'])) {
				$stmt->bindValue(':param', $_POST['user_edit_param']);
			}
			if (isset($_POST['user_edit_mail']) && (strlen($_POST['user_edit_mail']) > 0)) {
				$stmt->bindValue(':mail', $_POST['user_edit_mail']);
			}
			if (isset($_POST['user_edit_status'])) {
				$stmt->bindValue(':status', $_POST['user_edit_status']);
			}
			if (isset($_POST['user_edit_name']) && (strlen($_POST['user_edit_name']) > 0)) {
				$stmt->bindValue(':name', $_POST['user_edit_name']);
			}
			if (isset($_POST['user_edit_group_no_default'])) {
				$stmt->bindValue(':group_no_default', $_POST['user_edit_group_no_default']);
			}
			$stmt->bindValue(':update_uid', $parent_uid);
			$stmt->execute();

			// グループへの登録
			if (($group_no > 0) && isset($_POST['user_edit_role'])) {
				$sql = 'UPDATE '.T_GROUP_MEMBER.' ';
				$sql.= 'SET role_no=:role_no ';
				$sql.= 'WHERE group_no=:group_no AND uid=:uid';

				$stmt = $db->prepare($sql);
				$stmt->bindValue(':role_no', $_POST['user_edit_role']);
				$stmt->bindValue(':group_no', $group_no);
				$stmt->bindValue(':uid', $_POST['user_edit_uid']);
				$stmt->execute();
			}

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$this->message = 'ユーザー情報の更新に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		} catch(Exception $e) {
			$this->message = $e->getMessage();
			$db->rollBack();
		}
		$db = NULL;
		return $ret;
	}

	function deleteUser($uid = '',$force = false) {
		$ret = false;
		$db = NULL;
		$delete_uid = (strlen($uid) > 0) ? $uid : (isset($_POST['user_edit_uid']) ? $_POST['user_edit_uid'] : '');
		if (strlen($delete_uid) < 1) {
			$this->message = 'ユーザーの削除に失敗しました。<br>'.'ユーザー名が指定されていません。';
			return $ret;
		}
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$db->beginTransaction();

			$sql = 'SELECt a.no AS group_no, a.flg AS group_flg ';
			$sql.= 'FROM '.T_GROUP.' a INNER JOIN '.T_GROUP_MEMBER.' b ON a.no=b.group_no ';
			$sql.= 'WHERE b.uid=:uid ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':uid', $delete_uid);
			$stmt->execute();

			$list = array();
			$group_no = 0;
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$list[] = $row;
			}
			foreach ($list as $row) {
				if ($force || $row['group_flg'] == 0) { //ホーム

					$group_no = $row['group_no'];

					GroupCommonSQL::deleteGroupMember($db, $group_no, $delete_uid);
					if ($row['group_flg'] == 0) {
						GroupCommonSQL::deleteGroup($db, $group_no);
					}

				} else { //ホーム以外
					if (strlen($row['group_no']) > 0) {
						throw new Exception('ホーム以外のグループに参加しているため削除できません。');
					}
				}
			}

			$stmt = $db->prepare('DELETE FROM '.T_INI.' WHERE uid=:uid');
			$stmt->bindValue(':uid', $delete_uid);
			$stmt->execute();

			$stmt = $db->prepare('DELETE FROM '.T_LOCK.' WHERE uid=:uid');
			$stmt->bindValue(':uid', $delete_uid);
			$stmt->execute();

			$stmt = $db->prepare('DELETE FROM '.$this->usertable.' WHERE uid=:uid');
			$stmt->bindValue(':uid', $delete_uid);
			$stmt->execute();

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$this->message = 'ユーザーの削除に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		} catch(Exception $e) {
			$this->message = $e->getMessage();
			$db->rollBack();
		}
		$db = NULL;
		return $ret;
	}

	function entryTimeoutUser() {
		$ret_list = array();
		$db = NULL;
		$list = array();
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$sql = 'SELECT uid, mail, update_time ';
			$sql.= 'FROM '.$this->usertable.' ';
			$sql.= 'WHERE status='.USER_STATUS_ENTRY;

			$stmt = $db->prepare($sql);
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				foreach ($row as $key => $value) {
					$info[$key] = $value;
				}
				$list[] = $info;
			}

			if (count($list)) {
				foreach ($list as $info) {
					//仮登録後の経過時間を求める
					$update_time = new DateTime($info['update_time']);
					$now_time = new DateTime();
					$interval = $now_time->diff($update_time, true);
					//3日以上のとき
					$diff = $interval->format("%d");	//日
					if ($diff > 3) {
						//ユーザー削除
						$ret = $this->deleteUser($info['uid'],true); //招待中のグループも全て削除
						if ($ret) {
							$ret_list[] = array('ret'=>true,'uid'=>$info['uid'],'mail'=>$info['mail']);
						} else {
							$ret_list[] = array('ret'=>false,'uid'=>$info['uid'],'mail'=>$info['mail']);
							break;
						}
					}
				}
			}

		} catch (PDOException $e) {
			$this->message = '仮登録ユーザーのタイムアウト処理に失敗しました。<br>'.$e->getMessage();
		}
		$db = NULL;
		return $ret_list;
	}

	function addMember() {
		$ret = false;
		$db = NULL;
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;

			$sql = 'INSERT INTO '.T_GROUP_MEMBER.'(group_no, uid, role_no, status) ';
			$sql.= 'VALUES(:group_no, :uid, :role_no, :status)';

			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':uid', $_POST['user_edit_uid']);
			$stmt->bindValue(':role_no', $_POST['user_edit_role']);
			$stmt->bindValue(':status', GROUP_MEMBER_STATUS_ENTRY);
			$stmt->execute();

			$ret = true;
		} catch(PDOException $e) {
			$code = $e->getCode();
			switch ($code) {
				case 23000:
					$this->message = e($_POST['user_edit_uid']).' は既に登録されています。';
					break;
				default:
					$this->message = 'ユーザーの登録に失敗しました。<br>'.$e->getMessage();
					break;
			}
		}
		$db = NULL;
		return $ret;
	}

	function enabledMember($uid = '') {
		$ret = false;
		$db = NULL;
		if ($GLOBALS['auth']->getParent_group_no() < 1) {
			$this->message = 'ユーザー情報の更新に失敗しました。<br>不正な要求です。情報が不足しています。';
			return false;
		}
		$uid = !empty($uid) ? $uid : $GLOBALS['auth']->getParent_uid();
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$db->beginTransaction();

			$sql = 'UPDATE '.T_GROUP_MEMBER.' ';
			$sql.= 'SET status=:status ';
			$sql.= 'WHERE group_no=:group_no AND uid=:uid';

			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $GLOBALS['auth']->getParent_group_no());
			$stmt->bindValue(':uid', $uid);
			$stmt->bindValue(':status', GROUP_MEMBER_STATUS_NOMAL);
			$stmt->execute();

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$this->message = 'ユーザー情報の更新に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		}
		$db = NULL;
		return $ret;
	}

	function deleteMember($force = false) {
		$ret = false;
		$db = NULL;
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$db->beginTransaction();

			$group_no = isset($_POST['user_edit_group_no']) ? $_POST['user_edit_group_no'] : $GLOBALS['auth']->getParent_group_no();
			if ($group_no < 1) {
				throw new Exception('メンバーの削除に失敗しました。<br>グループが指定されていません。');
			}

			if (!$force) {
	 			//使用中ならば削除不可とする
				$sql = 'SELECT no FROM '.T_SCHEDULE.' WHERE uid=:uid AND group_no=:group_no ';
				$stmt = $db->prepare($sql);
				$stmt->bindValue(':uid', $_POST['user_edit_uid']);
				$stmt->bindValue(':group_no', $group_no);
				$stmt->execute();

				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				if (!empty($row)) {
					throw new Exception('グループ内に '.e($_POST['user_edit_uid']).' のデータがあるため削除できません。');
				}
			}

			GroupCommonSQL::deleteGroupMember($db, $group_no, $_POST['user_edit_uid']);

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$this->message = 'メンバーの削除に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		} catch(Exception $e) {
			$this->message = $e->getMessage();
			$db->rollBack();
		}
		$db = NULL;

		return $ret;
	}

	function startEdit() {
		$ret = false;
		$db = NULL;
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$db->beginTransaction();

			$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;

			$sql = 'SELECT a.uid AS lock_uid, a.time AS lock_time ';
			$sql.= 'FROM '.T_LOCK.' a ';
			$sql.= 'WHERE ';
			$sql.= 'a.group_no=:group_no AND ';
			$sql.= 'a.data='.LOCK_USER.' AND a.data_no=:data_no FOR UPDATE';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':data_no', $_POST['user_edit_uid']);
			$stmt->execute();

			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row && ($row['lock_uid'] != $GLOBALS['auth']->getParent_uid())) {
				$lock_time = new DateTime($row['lock_time']);
				$now_time = new DateTime();
				$interval = $now_time->diff($lock_time, true);
				$minute = $interval->format("%i");
				//他のユーザーが編集中なのでタイムアウト時間が経過するまではロック中として編集不可
				if ($minute < $this->timeout) {
					throw new Exception(e($_POST['user_edit_uid']).' は、'.e($row['lock_uid'])." が編集中です。");
				}
			}

			$sql = 'INSERT INTO '.T_LOCK.'(group_no, data, data_no, uid, time) ';
			$sql.= 'VALUES(:group_no, :data, :data_no, :uid, NOW())';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':data', LOCK_USER);
			$stmt->bindValue(':data_no', $_POST['user_edit_uid']);
			$stmt->bindValue(':uid', $this->getParent_uid());
			$stmt->execute();

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$this->message = 'ユーザー情報の編集開始に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		} catch(Exception $e) {
			$this->message = $e->getMessage();
			$db->rollBack();
		}
		$db = NULL;
		return $ret;
	}

	function endEdit() {
		$ret = false;
		$db = NULL;
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$sql = 'DELETE FROM '.T_LOCK.' WHERE data='.LOCK_USER.' AND uid=:uid';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':uid', $this->getParent_uid());
			$stmt->execute();

			$ret = true;
		} catch(PDOException $e) {
			$this->message = 'ユーザー情報の編集終了に失敗しました。<br>'.$e->getMessage();
		}
		$db = NULL;
		return $ret;
	}
}