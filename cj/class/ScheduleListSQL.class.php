<?php
require_once(dirname(__FILE__).'/../../define.php');
require_once(dirname(__FILE__).'/../../Encode.php');
require_once(dirname(__FILE__).'/CjCommonSQL.class.php');

class ScheduleListSQL {

	protected $opts = array('dsn'=>'', 'db_user'=>'', 'db_pwd'=>0);
	protected $usertable = T_SCHEDULE;
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

	//対象年月のスケジュールを読込
	function getSchedule_month($select_uid, $date) {

		//ログイン中のユーザー
		$uid = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_uid() : '';

		//1ヶ月の日を取得（自分の設定を使う）
		$cmn = new CjCommonSQL($GLOBALS['dbopts']);
		$cmn->setDefSime(INI_SCH_SIME1, INI_SCH_SIME2);
		$day_list = $cmn->getDayList($uid, $date);

		if (!$day_list) {
			return array();
		}

		$date_top = $day_list[0]['date'];
		$date_end = $day_list[count($day_list) - 1]['date'];

		return $this->getSchedule_day($select_uid, $date_top, $date_end);
	}

	//対象日のスケジュールを読込
	function getSchedule_day($select_uid, $date_top, $date_end = '') {

		$list = array();

		//対象日の期間
		$dt_top = '';
		$dt_end = '';
		try {
			$dt_top = new DateTime($date_top);
			if ($date_end == '') {
				$date_end = $date_top;
			}
			$dt_end = new DateTime($date_end);
		} catch (Exception $e) {
			$this->message = "日付の指定が不正です。<br>".$e->getMessage();
			return $list;
		}

		//選択中のグループ
		$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;
		if ($group_no < 1) {
			$this->message = "グループの指定が不正です。<br>".$e->getMessage();
			return $list;
		}

		//データベース接続
		$db = null;
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			$this->message = "データベースの接続に失敗しました。<br>".$e->getMessage();
			return $list;
		}

		//スケジュール読込
		$sch_list = array();
		try {
			$sql = 'SELECT a.no, a.date, a.sortno, a.time, a.action, a.costin, a.costout, a.section_no, a.kintai_no, a.project_no, a.work_no, a.process_no, customer_no, a.uid, a.group_no ';
			$sql.= ',b.name AS section_name, c.name AS kintai_name, d.name AS project_name, e.name AS work_name, f.name AS process_name, g.name AS customer_name ';
			$sql.= 'FROM '.$this->usertable.' a ';
			$sql.= 'LEFT JOIN '.T_SECTION.' b ON b.no=a.section_no ';
			$sql.= 'LEFT JOIN '.T_KINTAI.' c ON c.no=a.kintai_no ';
			$sql.= 'LEFT JOIN '.T_PROJECT.' d ON d.no=a.project_no ';
			$sql.= 'LEFT JOIN '.T_WORK.' e ON e.no=a.work_no ';
			$sql.= 'LEFT JOIN '.T_PROCESS.' f ON f.no=a.process_no ';
			$sql.= 'LEFT JOIN '.T_CUSTOMER.' g ON g.no=a.customer_no ';
			$sql.= 'WHERE ';
			$sql.= 'a.group_no=:group_no AND a.uid=:uid AND a.date>=:date_top AND a.date<=:date_end ';
			$sql.= 'ORDER BY a.date, a.sortno ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':uid', $select_uid);
			$stmt->bindValue(':date_top', $date_top);
			$stmt->bindValue(':date_end', $date_end);
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$item = array();
				foreach ($row as $key => $value) {
					if ($key == 'date') {
						$value = format($value,'Y/m/d');
					}
					if ($key == 'time' && !empty($value)) {
						$value = format($value,'H:i');
					}
					$item[$key] = mb_convert_encoding($value, "UTF-8", "auto");
				}
				$sch_list[] = $item;
			}
		} catch (PDOException $e) {
			$this->message = "スケジュール読込に失敗しました。<br>".$e->getMessage();
			return $list;
		}
		$stmt = null;
		$db = null;

		//スケジュールリスト生成
		$dt_tmp = $dt_top;
		while ($dt_tmp <= $dt_end) {
			//データベースから対象日のデータを取得
			$hit = false;
			foreach ($sch_list as $row) {
				$sch_date = new Datetime($row['date']);
				if ($sch_date == $dt_tmp) {
					$hit = true;
					$list[] = $row;
				}
				if ($sch_date > $dt_tmp) {
					break;
				}
			}
			//データが無ければ出勤と退勤の空レコードだけ追加
			if (!$hit) {
				$list[] = array('date'=>$dt_tmp->format('Y/m/d'),'sortno'=>'1','section_no'=>'1','uid'=>$select_uid,'group_no'=>$group_no);
				$list[] = array('date'=>$dt_tmp->format('Y/m/d'),'sortno'=>'2','section_no'=>'3','uid'=>$select_uid,'group_no'=>$group_no);
			}
			//翌日へ進む
			$dt_tmp->modify('+1 day');
		}

		return $list;
	}

	//tableタグへ展開するデータに整形
	function getSchedule_table_list($sch_list) {

		$list = array();

		$cnt = 0;
		$date = '';
		foreach ($sch_list as $row) {

			$item = array('no'=>'','date'=>'','rowspan'=>'','time'=>'','section_no'=>'','kintai_no'=>'','action'=>'','costin'=>'','costout'=>'',
						'project_no'=>'','work_no'=>'','customer_no'=>'','process_no'=>'',
						'project_name'=>'','work_name'=>'','customer_name'=>'','process_name'=>'');

			foreach ($row as $key => $value) {
				if (isset($item[$key])) {
					if ($key == 'action' && ($row['section_no'] == 1 || $row['section_no'] == 3)) {
						$value = $row['kintai_name'];
					}
					$item[$key] = $value;
				}
			}

			//日ごとの先頭行
			if ($date != $row['date']) {
				$date = $row['date'];

				//同じ日付の行数をrowspanに設定
				$hit = 1;
				for ($i=$cnt+1; $i<count($sch_list); $i++) {
					if ($sch_list[$i]['date'] == $date) {
						$hit++;
					} else {
						break;
					}
				}
				$item['rowspan'] = $hit;

				//日の行数が１行しかないときは、後ろに空行を追加してrowspan=2にする
				if ($hit == 1) {
					$item['rowspan'] = 2;
					$list[] = $item;
					foreach ($item as $key => $value) {
						if ($key != 'date') {
							$item[$key] = '';
						}
					}
					$item['section_no'] = 3;	//退勤
				}
			}

			$list[] = $item;
			$cnt++;
		}


		return $list;
	}

	//区分リスト取得
	function getSection_list() {

		$list = array();

		//データベース接続
		$db = null;
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			$this->message = "データベースの接続に失敗しました。<br>".$e->getMessage();
			return $list;
		}

		//スケジュール読込
		$sch_list = array();
		try {
			$sql = 'SELECT no, name ';
			$sql.= 'FROM '.T_SECTION.' ';
			$sql.= 'ORDER BY no ';
			$stmt = $db->prepare($sql);
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$item = array();
				foreach ($row as $key => $value) {
					$item[$key] = mb_convert_encoding($value, "UTF-8", "auto");
				}
				$list[] = $item;
			}
		} catch (PDOException $e) {
			$this->message = "区分項目の読込に失敗しました。<br>".$e->getMessage();
			return $list;
		}
		$stmt = null;
		$db = null;

		return $list;
	}

	//対象日のスケジュールを保存
	function setShedule_day($select_uid) {

		$date = $_POST['post_date'];

		//選択中のグループ
		$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;
		if ($group_no < 1) {
			$this->message = "グループの指定が不正です。<br>".$e->getMessage();
			return false;
		}

		//データベース接続
		$db = null;
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			$this->message = "データベースの接続に失敗しました。<br>".$e->getMessage();
			return $list;
		}

		try {
			$db->beginTransaction();

			//対象日のデータを削除
			$sql = ' DELETE FROM '.$this->usertable.' WHERE uid=:uid AND group_no=:group_no AND date=:date ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':uid', $select_uid);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':date', $date);
			$stmt->execute();

			//対象日のデータを保存
			$time = $_POST['post_time'];
			$action = $_POST['post_action'];
			$costin = $_POST['post_costin'];
			$costout = $_POST['post_costout'];
			$section_no = $_POST['post_section_no'];
			$kintai_no = $_POST['post_kintai_no'];
			$project_no = $_POST['post_project_no'];
			$work_no = $_POST['post_work_no'];
			$process_no = $_POST['post_process_no'];
			$customer_no = $_POST['post_customer_no'];

			$sortno = 1;
			$imax = count($time);
			for ($i = 0; $i < $imax; $i++) {
				//空データを除く（出勤と退勤は空データも保存する）
				if ($section_no[$i] != 2 || !empty($time[$i]) || !empty($action[$i]) || !empty($action[$i]) || !empty($kintai_no[$i])) {

					//空きnoを求める
					$sql = 'SELECT CASE (SELECT MIN(no) FROM '.$this->usertable.') WHEN 1 THEN MIN(A.no)+1 ELSE 1 END AS minvalue ';
					$sql.= 'FROM '.$this->usertable.' A ';
					$sql.= 'WHERE NOT EXISTS (SELECT no FROM '.$this->usertable.' AS B WHERE B.no=A.no+1)';
					$stmt = $db->prepare($sql);
					$stmt->execute();
					$row = $stmt->fetch(PDO::FETCH_ASSOC);
					$no = $row['minvalue'];

					//レコード追加
					$sql = 'INSERT INTO '.$this->usertable.'(no, date, sortno, time, action, costin, costout';
					$sql.= ', section_no, kintai_no, project_no, work_no, process_no, customer_no, uid, group_no) ';
					$sql.= 'VALUES(:no, :date, :sortno, :time, :action, :costin, :costout';
					$sql.= ', :section_no, :kintai_no, :project_no, :work_no, :process_no, :customer_no';
					$sql.= ', :uid, :group_no)';
					$stmt = $db->prepare($sql);
					$stmt->bindValue(':no', $no);
					$stmt->bindValue(':date', $date);
					$stmt->bindValue(':sortno', $sortno);
					$sortno++;

					$time[$i] = trim($time[$i]);
					$len = strlen($time[$i]);
					if ($len < 1) {
						$time[$i] = null;
					} else {
						$pos = strpos($time[$i], ":");
						if (!$pos && ($len >= 3)) {
							$time[$i] = '0'.$time[$i];
							$len = strlen($time[$i]);
							$time[$i] = substr($time[$i],$len-4,2).':'.substr($time[$i],$len-2,2);
						}
						$time[$i] = format($time[$i],'H:i');
					}

					$stmt->bindValue(':time', $time[$i]);
					$stmt->bindValue(':action', $action[$i]);
					$stmt->bindValue(':costin', $costin[$i]);
					$stmt->bindValue(':costout', $costout[$i]);
					$stmt->bindValue(':section_no', $section_no[$i]);
					$stmt->bindValue(':kintai_no', $kintai_no[$i]);
					$stmt->bindValue(':project_no', $project_no[$i]);
					$stmt->bindValue(':work_no', $work_no[$i]);
					$stmt->bindValue(':process_no', $process_no[$i]);
					$stmt->bindValue(':customer_no', $customer_no[$i]);
					$stmt->bindValue(':uid', $select_uid);
					$stmt->bindValue(':group_no', $group_no);
					$stmt->execute();

				}
			}

			$db->commit();
		} catch (PDOException $e) {
			$this->message = "スケジュール保存に失敗しました。<br>".$e->getMessage();
			$db->rollBack();
			return false;
		}
		$stmt = null;
		$db = null;

		return true;
	}

	//プロジェクトリスト
	function getProject_list() {
		$list = false;

		//選択中のグループ
		$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;

		//データベース接続
		$db = null;
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			$this->message = "データベースの接続に失敗しました。<br>".$e->getMessage();
			return $list;
		}

		//読込
		try {
			$sql = 'SELECT no, name ';
			$sql.= 'FROM '.T_PROJECT.' ';
			$sql.= 'WHERE group_no=:group_no ';
			$sql.= 'ORDER BY sortno ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$item = array();
				foreach ($row as $key => $value) {
					$item[$key] = mb_convert_encoding($value, "UTF-8", "auto");
				}
				$list[] = $item;
			}
			if (!$list) {
				$list = array();
			}
		} catch (PDOException $e) {
			$this->message = "プロジェクト項目の読込に失敗しました。<br>".$e->getMessage();
			return $list;
		}
		$stmt = null;
		$db = null;

		return $list;
	}

	//業務リスト
	function getWork_list($project_no) {
		$list = false;

		//選択中のグループ
		$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;

		//データベース接続
		$db = null;
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			$this->message = "データベースの接続に失敗しました。<br>".$e->getMessage();
			return $list;
		}

		//読込
		try {
			$sql = 'SELECT no, name ';
			$sql.= 'FROM '.T_WORK.' ';
			$sql.= 'WHERE group_no=:group_no AND project_no=:project_no ';
			$sql.= 'ORDER BY sortno ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':project_no', $project_no);
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$item = array();
				foreach ($row as $key => $value) {
					$item[$key] = mb_convert_encoding($value, "UTF-8", "auto");
				}
				$list[] = $item;
			}
			if (!$list) {
				$list = array();
			}
		} catch (PDOException $e) {
			$this->message = "業務項目の読込に失敗しました。<br>".$e->getMessage();
			return $list;
		}
		$stmt = null;
		$db = null;

		return $list;
	}

	//顧客リスト
	function getCustomer_list() {
		$list = false;

		//選択中のグループ
		$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;

		//データベース接続
		$db = null;
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			$this->message = "データベースの接続に失敗しました。<br>".$e->getMessage();
			return $list;
		}

		//読込
		try {
			$sql = 'SELECT no, name ';
			$sql.= 'FROM '.T_CUSTOMER.' ';
			$sql.= 'WHERE group_no=:group_no ';
			$sql.= 'ORDER BY sortno ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$item = array();
				foreach ($row as $key => $value) {
					$item[$key] = mb_convert_encoding($value, "UTF-8", "auto");
				}
				$list[] = $item;
			}
			if (!$list) {
				$list = array();
			}
		} catch (PDOException $e) {
			$this->message = "顧客項目の読込に失敗しました。<br>".$e->getMessage();
			return $list;
		}
		$stmt = null;
		$db = null;

		return $list;
	}

	//工程リスト
	function getProcess_list() {
		$list = false;

		//選択中のグループ
		$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;

		//データベース接続
		$db = null;
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			$this->message = "データベースの接続に失敗しました。<br>".$e->getMessage();
			return $list;
		}

		//読込
		try {
			$sql = 'SELECT no, name ';
			$sql.= 'FROM '.T_PROCESS.' ';
			$sql.= 'WHERE group_no=:group_no ';
			$sql.= 'ORDER BY sortno ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$item = array();
				foreach ($row as $key => $value) {
					$item[$key] = mb_convert_encoding($value, "UTF-8", "auto");
				}
				$list[] = $item;
			}
			if (!$list) {
				$list = array();
			}
		} catch (PDOException $e) {
			$this->message = "工程項目の読込に失敗しました。<br>".$e->getMessage();
			return $list;
		}
		$stmt = null;
		$db = null;

		return $list;
	}

	//ＴＯＤＯリスト
	function getTodo_list($select_uid, $select_todo_no = null) {
		$list = false;

		//選択中のグループ
		$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;

		//データベース接続
		$db = null;
		try {
			$db = new PDO($this->opts['dsn'], $this->opts['db_user'], $this->opts['db_pwd'], array(PDO::ATTR_EMULATE_PREPARES => false));
			$db->exec('SET NAMES utf8');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			$this->message = "データベースの接続に失敗しました。<br>".$e->getMessage();
			return $list;
		}

		//読込
		try {
			$sql = 'SELECT a.no, a.project_no, a.work_no, a.customer_no, a.process_no, a.action ';
			$sql.= ',b.name AS project_name, c.name AS work_name, d.name AS customer_name, e.name AS process_name, f.name AS status_name ';
			$sql.= ',b.sortno AS project_sortno, c.sortno AS work_sortno, d.sortno AS customer_sortno, e.sortno AS process_sortno ';
			$sql.= ',( ';
			$sql.= 'CASE a.status_no ';
			$sql.= 'WHEN 2 THEN 1 ';
			$sql.= 'WHEN 1 THEN 2 ';
			$sql.= 'WHEN 0 THEN 3 ';
			$sql.= 'WHEN 3 THEN 4 ';
			$sql.= 'ELSE 5 END ';
			$sql.= ' ) AS status_sortno ';
			$sql.= 'FROM '.T_TODO.' a ';
			$sql.= 'LEFT JOIN '.T_PROJECT.' b ON a.project_no=b.no ';
			$sql.= 'LEFT JOIN '.T_WORK.' c ON a.work_no=c.no ';
			$sql.= 'LEFT JOIN '.T_CUSTOMER.' d ON a.customer_no=d.no ';
			$sql.= 'LEFT JOIN '.T_PROCESS.' e ON a.process_no=e.no ';
			$sql.= 'LEFT JOIN '.T_STATUS.' f ON a.status_no=f.no ';
			$sql.= 'WHERE a.group_no=:group_no AND a.uid=:uid ';
			if (!empty($select_todo_no)) {
				$sql.= ' AND a.no=:no ';
			} else {
				$sql.= ' AND a.status_no<=1 ';
			}
			$sql.= 'ORDER BY a.sortno ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':uid', $select_uid);
			if (!empty($select_todo_no)) {
				$stmt->bindValue(':no', $select_todo_no);
			}
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$item = array();
				foreach ($row as $key => $value) {
					$item[$key] = mb_convert_encoding($value, "UTF-8", "auto");
				}
				$list[] = $item;
			}
			if (!$list) {
				$list = array();
			}
		} catch (PDOException $e) {
			$this->message = "TODO項目の読込に失敗しました。<br>".$e->getMessage();
			return $list;
		}
		$stmt = null;
		$db = null;

		return $list;
	}
}