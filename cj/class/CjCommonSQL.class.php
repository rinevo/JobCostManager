<?php
require_once(dirname(__FILE__).'/../../define.php');
require_once(dirname(__FILE__).'/../../Encode.php');

class CjCommonSQL {

	protected $opts = array('dsn'=>'', 'db_user'=>'', 'db_pwd'=>0);
	protected $message = '';
	protected $def_sime1 = INI_SCH_SIME1;
	protected $def_sime2 = INI_SCH_SIME2;

	function __construct($opts = null) {
		// データベース接続に使用するオプション値の初期設定
		foreach ($this->opts as $key => $value) { // オプション設定
			$this->opts[$key] = isset($opts[$key]) ? $opts[$key] : $value;
		}
	}

	function getMessage() {
		return $this->message;
	}

	function setDefSime($def1, $def2) {
		$this->def_sime1 = $def1;
		$this->def_sime2 = $def2;
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

	function getIni_data($inino, $select_uid) {
		$item = null;

		//選択中のグループ
		$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;

		//データベース接続
		$db = $this->DBOpen();
		if ($db) {
			//読込
			try {
				$sql = 'SELECT inidata ';
				$sql.= 'FROM '.T_INI.' ';
				$sql.= 'WHERE inino=:inino AND uid=:uid AND group_no=:group_no ';
				$stmt = $db->prepare($sql);
				$stmt->bindValue(':inino', $inino);
				$stmt->bindValue(':uid', $select_uid);
				$stmt->bindValue(':group_no', $group_no);
				$stmt->execute();

				if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$item = mb_convert_encoding($row['inidata'], "UTF-8", "auto");
				}
			} catch (PDOException $e) {
				$this->message = "設定の読込に失敗しました。<br>".$e->getMessage();
			}
			$stmt = null;
			$db = null;
		}

		//設定が無いときは、初期値を返す
		if ($item === null) {
			switch ($inino) {
				case INI_TODO_PERIOD:
					$item = 2;		//期間：月間
					break;
				case INI_TODO_PAST:
					$item = 1;		//[終了][中止]でない項目はすべて表示
					break;
				case INI_TODO_SORT:
					$item = '';		//並び順：
					break;
				case INI_SCH_SIME1:
				case INI_COST_SIME1:
				case INI_KINTAI_SIME1:
					$item = 15;		//月の中計日
					break;
				case INI_SCH_SIME2:
				case INI_COST_SIME2:
				case INI_KINTAI_SIME2:
					$item = 31;		//月の締日
					break;
				case INI_COST_FIELD:
					$item = 'project_no,work_no';
					break;
				case INI_TODO_BUNDLE:
					$item = 'project_sortno,work_sortno';
					break;
				case INI_SHOW_USER:
					$item = 1;
					break;
				default:
					$item = 0;
					break;
			}
		}

		return $item;
	}

	function setIni_data($inino, $inidata, $select_uid) {
		$ret = false;

		//選択中のグループ
		$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;

		//データベース接続
		$db = $this->DBOpen();
		if (!$db) {
			return $ret;
		}

		try {
			$db->beginTransaction();

			//削除
			$sql = 'DELETE FROM '.T_INI.' ';
			$sql.= 'WHERE  uid=:uid AND group_no=:group_no AND inino=:inino ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':inino', $inino);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':uid', $select_uid);
			$stmt->execute();

			//空きno
			$sql = 'SELECT CASE (SELECT MIN(no) FROM '.T_INI.') WHEN 1 THEN MIN(A.no)+1 ELSE 1 END AS minvalue ';
			$sql.= 'FROM '.T_INI.' A ';
			$sql.= 'WHERE NOT EXISTS (SELECT no FROM '.T_INI.' AS B WHERE B.no=A.no+1)';
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$no = $row['minvalue'];

			//追加
			$sql = 'INSERT INTO '.T_INI.'(no, inino, inidata, group_no, uid) VALUES(:no, :inino, :inidata, :group_no, :uid) ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':no', $no);
			$stmt->bindValue(':inino', $inino);
			$stmt->bindValue(':inidata', $inidata);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':uid', $select_uid);
			$stmt->execute();

			$db->commit();
			$ret = true;
		} catch(PDOException $e) {
			$this->message = '設定の更新に失敗しました。<br>'.$e->getMessage();
			$db->rollBack();
		}
		$stmt = null;
		$db = NULL;

		return $ret;
	}

	//締日１
	function getSime1($select_uid) {
		return ($select_uid != '') ? $this->getIni_data($this->def_sime1,$select_uid) : 15;
	}

	//締日２
	function getSime2($select_uid) {
		return ($select_uid != '') ? $this->getIni_data($this->def_sime2,$select_uid) : 31;
	}

	//現在の年月
	function getNowDate($select_uid = '', $nowdate = '') {

		$dt_now = null;
		try {
			$dt_now = new DateTime($nowdate);
		} catch (Exception $e) {
			$dt_now = new DateTime(); //取得できなかったら現在の日時を取得
		}

		//締日を超えていたら翌月にする
		$sime = ($select_uid != '') ? $this->getIni_data($this->def_sime2,$select_uid) : 31;
		$dt_sime = $this->getDateRangeMonth($dt_now->format('Y/m/01'), $sime);
		if ($dt_now > $dt_sime) {
			$dt_now = new DateTime($dt_now->format('Y/m/01'));
			$dt_now->modify('+1 month');
		}

		return $dt_now->format('Y/m/01');
	}

	//現在の日
	function getNowDay($nowday = '') {

		$dt_now = null;
		try {
			$dt_now = new DateTime($nowday);
		} catch (Exception $e) {
			$dt_now = new DateTime(); //取得できなかったら現在の日時を取得
		}

		return $dt_now->format('Y/m/d');
	}

	//月末
	function getLastday($nowday = '') {

		$dt_now = new datetime($this->getNowDay($nowday));
		$dt_now->modify('+1 month');
		$dt_now->modify('-1 day');

		return $dt_now = $dt_now->format('Y/m/d');
	}

	//選択に表示する年月取得
	function getYearMonthList($select_uid, $select_date = '') {

		$list = array();

		//現在の年月
		$dt_now = new DateTime($this->getNowDate());

		//選択している年月
		$dt_select = new DateTime($this->getNowDate($select_uid, $select_date));

		//選択している年月～現在の年月までを求める
		if ($dt_select > $dt_now) {
			$dt_min = new DateTime($dt_now->format('Y/m/01'));
			$dt_max = new DateTime($dt_select->format('Y/m/01'));
		} else {
			$dt_min = new DateTime($dt_select->format('Y/m/01'));
			$dt_max = new DateTime($dt_now->format('Y/m/01'));
		}

		//最小の年月
		$dt_min->modify('-1 month');

		//最大の年月
		$dt_max->modify('+1 month');

		//選択中のグループ
		$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;

		//データベース接続
		$db = $this->DBOpen();
		if (!$db) {
			return $ret;
		}

		//保存データの最小と最大
		try {
			$sql = 'SELECT MIN(date) AS mindate, MAX(date) AS maxdate ';
			$sql.= 'FROM '.T_SCHEDULE.' ';
			$sql.= 'WHERE ';
			$sql.= 'group_no=:group_no AND uid=:uid';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':uid', $select_uid);
			$stmt->execute();

			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row) {
				$tmp = new datetime($row['mindate']);
				$tmp = new datetime($tmp->format('Y/m/01'));
				if ($dt_min > $tmp) {
					$dt_min = $tmp;
				}
				$tmp = new datetime($row['maxdate']);
				$tmp = new datetime($tmp->format('Y/m/01'));
				if ($dt_max < $tmp) {
					$dt_max = $tmp;
				}
			}
		} catch (PDOException $e) {
			$this->message = "年月取得に失敗しました。<br>".$e->getMessage();
			return $list;
		}
		$stmt = null;
		$db = null;

		//年月リスト生成
		$tmp = $dt_min;
		while ($tmp <= $dt_max) {
			$data = array('date'=>'','name'=>'');
			$data['date'] = $tmp->format('Y/m/01');
			$data['name'] = $tmp->format('Y年m月');
			$list[] = $data;
			$tmp->modify('+1 month');
		}

		return $list;
	}

	//対象月内の有効な日を求める
	function getDateRangeMonth($date, $day) {

		$dt_month = new DateTime($date);

		//月末
		$dt_last = new DateTime($dt_month->format('Y/m/01'));
		$dt_last->modify('+1 month');
		$dt_last->modify('-1 day');
		$now_last = $dt_last->format('d');

		$dt_ret = null;
		if ($now_last < $day) {
			//指定日は月末を超えるので、月末とする
			$dt_ret = $dt_last;
		} else {
			//有効な日なので、指定日の年月日を求める
			$format = 'Y/m/'.((strlen($day)>1) ? $day : '0'.$day);
			$dt_ret = new DateTime($dt_last->format($format));
		}

		return $dt_ret;
	}

	//選択に表示する日取得
	function getDayList($select_uid, $nowdate = '') {
		$list = array();

		//締日２（集計日）
		$sime = ($select_uid != '') ? $this->getIni_data($this->def_sime2,$select_uid) : 31;

		//現在の年月
		$dt_now = new DateTime($this->getNowDate($select_uid, $nowdate));

		//前月の月末
		$dt_before_last = new DateTime($dt_now->format('Y/m/01'));
		$dt_before_last->modify('-1 day');
		$before_last = $dt_before_last->format('d');

		//先頭日
		$dt_min = null;
		if ($before_last < $sime) {
			$dt_min = new DateTime($dt_now->format('Y/m/01'));
		} else {
			$format = 'Y/m/'.((strlen($sime)>1) ? $sime : '0'.$sime);
			$dt_min = new DateTime($dt_before_last->format($format));
			$dt_min->modify('+1 day');
		}

		//今月の月末
		$dt_max = $this->getDateRangeMonth($dt_now->format('Y/m/01'), $sime);

		//日リスト生成
		$tmp = $dt_min;
		while ($tmp <= $dt_max) {
			$data = array('date'=>'','name'=>'');
			$data['date'] = $tmp->format('Y/m/d');
			$data['name'] = $tmp->format('d日').'('.week($data['date']).')';
			$list[] = $data;
			$tmp->modify('+1 day');
		}

		return $list;
	}

	//勤怠リスト取得
	function getKintai_list($flg) {

		$list = array();

		//データベース接続
		$db = $this->DBOpen();
		if (!$db) {
			return $ret;
		}

		//スケジュール読込
		$sch_list = array();
		try {
			$sql = 'SELECT no, name, flg, total_flg ';
			$sql.= 'FROM '.T_KINTAI.' ';
			$sql.= 'ORDER BY no ';
			$stmt = $db->prepare($sql);
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$item = array();
				if (($row['flg'] & $flg) != 0) {
					foreach ($row as $key => $value) {
						$item[$key] = mb_convert_encoding($value, "UTF-8", "auto");
					}
					$list[] = $item;
				}
			}
		} catch (PDOException $e) {
			$this->message = "スケジュール読込に失敗しました。<br>".$e->getMessage();
			return $list;
		}
		$stmt = null;
		$db = null;

		return $list;
	}

	//集計
	function getTotal($uid, $select_uid, $date) {
		$total_list = false;

		//選択中のグループ
		$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;

		//日リスト
		$day_list = $this->getDayList($uid, $date);
		$date_top = $day_list[0]['date'];
		$date_end = $day_list[count($day_list) - 1]['date'];

		//締日
		$sime1 = $this->getSime1($uid);
		$sime2 = $this->getSime2($uid);

		//締日１
		if ($sime1 == $sime2) {
			$sime_day1 = $date_end;
		} elseif ($sime1 < $sime2) {
			if (format($date_end,'d') > $sime1) {
				$format = 'Y/m/'.((strlen($sime1)>1) ? $sime1 : '0'.$sime1);
				$sime_day1 = format($date_end, $format);
			} else {
				$sime_day1 = $date_end;
			}
		} else {
			$sime_day1 = $this->getDateRangeMonth($date_top, $sime1)->format('Y/m/d');
		}

		//締日２
		$sime_day2 = $date_end;

		//勤怠リスト
		$kintai_list = $this->getKintai_list(3);

		//データベース接続
		$db = $this->DBOpen();
		if (!$db) {
			return $ret;
		}

		//読込
		$list = array();
		try {
			$sql = 'SELECT date, section_no, kintai_no, costin, costout ';
			$sql.= 'FROM '.T_SCHEDULE.' ';
			$sql.= 'WHERE group_no=:group_no AND uid=:uid AND date>=:date_top AND date<=:date_end ';
			$sql.= 'ORDER BY date, sortno ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':uid', $select_uid);
			$stmt->bindValue(':date_top', $date_top);
			$stmt->bindValue(':date_end', $date_end);
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$item = array();
				foreach ($row as $key => $value) {
					$item[$key] = mb_convert_encoding($value, "UTF-8", "auto");
				}
				$list[] = $item;
			}
		} catch (PDOException $e) {
			$this->message = "TODO項目の読込に失敗しました。<br>".$e->getMessage();
			return $total_list;
		}
		$stmt = null;
		$db = null;

		$index = 0;
		$total_list = array();
		$total_list[] = array();
		$sec_costin = 0;
		$sec_costout = 0;
		$costin = 0;
		$costout = 0;
		$total_start = false;
		$cal = array();

		if (!empty($list)) {
			//データの先頭が締日１よりも後ならば、次の集計タイミングは締日２にする
			$sime_day = new DateTime($sime_day1);
			$day_date = new DateTime(format($list[0]['date'],'Y/m/d'));
			if ($sime_day < $day_date) {
				$index = 1;
				$total_list[] = array();
				$sime_day = new DateTime($sime_day2);
			}

			//締日ごとに集計
			$day_top = true;
			foreach ($list as $row) {
				$day = format($row['date'],'Y/m/d');
				$day_date = new DateTime($day);
				$section_no = $row['section_no'];
				$kintai_no = $row['kintai_no'];

				//日が変わったら先頭行フラグON
				if (!empty($day_bk) && ($day != $day_bk)) {
					$day_top = true;
					$total_start = false;
					$sec_costin = 0;
					$sec_costout = 0;
				} else {
					$day_top = false;
				}

				//日ごとの加算
				if (!empty($day_bk) && ($day != $day_bk)) {
					foreach ($cal as $key => $value) {
						if (!isset($total_list[$index][(string)$key])) {
							$total_list[$index][(string)$key] = 0;
						}
						$total_list[$index][(string)$key] += $value;
						$cal[$key] = 0;
					}
				}

				//区分が勤怠ならば集計方法を取得
				$total_flg = 0;
				if ($section_no != 2) {
					foreach ($kintai_list as $kintai) {
						if ($kintai['no'] == $kintai_no) {
							$total_flg = $kintai['total_flg'];
							break;
						}
					}
				}

				//区分が出勤～退勤の時間内と時間外を集計
				if ($section_no == 1) {
					$total_start = true;
					$sec_costin = 0;
					$sec_costout = 0;
				}
				if ($section_no == 3) {
					$total_start = false;
					$costin += $sec_costin;
					$costout += $sec_costout;
					$sec_costin = 0;
					$sec_costout = 0;
				}
				if ($section_no == 2) {
					if ($total_start) {
						$sec_costin += $row['costin'];
						$sec_costout += $row['costout'];
					}
				} else {
					switch ($total_flg) {
						case 1:
							//日ごとの件数
							$cal[$kintai_no] = 1;
							break;
						case 2:
							//時間内の合計
							if (!isset($cal[$kintai_no])) {
								$cal[$kintai_no] = 0;
							}
							$cal[$kintai_no] += $row['costin'];
							break;
					}
				}

				//締日で中計
				if (!empty($day_bk) && !empty($sime_day) && $day_top && ($day_date > $sime_day)) {
					$total_list[$index]['costin'] = $costin;
					$total_list[$index]['costout'] = $costout;
					$costin = 0;
					$costout = 0;
					if ($index == 0) {
						$sime_day = new DateTime($sime_day2);
						$index++;
					} else {
						$sime_day = null;
					}
				}

				$day_bk = $day;
			}

			//中計以降の集計結果を格納
			foreach ($cal as $key => $value) {
				if (!isset($total_list[$index][(string)$key])) {
					$total_list[$index][(string)$key] = 0;
				}
				$total_list[$index][(string)$key] += $value;
			}
			$total_list[$index]['costin'] = $costin;
			$total_list[$index]['costout'] = $costout;
		}

		//タイトル行
		$list = array();
		$list[0] = array();
		$list[0][0] = '期間';
		foreach ($kintai_list as $row) {
			$list[0][] = $row['name'];
		}
		$list[0][] = '時間内';
		$list[0][] = '時間外';

		//集計結果を格納
		$list[1] = array();
		$list[1][0] = format($date_top,'d').'～'.format($sime_day1,'d');
		foreach ($kintai_list as $row) {
			if (isset($total_list[0][$row['no']])) {
				$list[1][$row['no']] = $total_list[0][$row['no']];
			} else {
				$list[1][$row['no']] = 0;
			}
		}
		$list[1][] = isset($total_list[0]['costin']) ? $total_list[0]['costin'] : 0 ;
		$list[1][] = isset($total_list[0]['costout']) ? $total_list[0]['costout'] : 0;

		if ($sime_day1 != $sime_day2) {
			$list[2] = array();
			$dt_sime2 = new DateTime($sime_day1);
			$dt_sime2->modify('+1 day');
			$list[2][0] = $dt_sime2->format('d').'～'.format($sime_day2,'d');
			foreach ($kintai_list as $row) {
				if (isset($total_list[1][$row['no']])) {
					$list[2][$row['no']] = $total_list[1][$row['no']];
				} else {
					$list[2][$row['no']] = 0;
				}
			}
			$list[2][] = isset($total_list[1]['costin']) ? $total_list[1]['costin'] : 0 ;
			$list[2][] = isset($total_list[1]['costout']) ? $total_list[1]['costout'] : 0;
		}

		$list[] = array();
		$i = count($list) - 1;
		$list[$i][0] = '合計';
		for ($j = 1; $j<$i; $j++) {
			$row = $list[$j];
			foreach ($row as $key => $value) {
				if ($key > 0) {
					if (!isset($list[$i][$key])) {
						$list[$i][$key] = 0;
					}
					$list[$i][$key] += $value;
				}
			}
		}

		return $list;
	}

	//ユーザーリスト
	function getUser_list($date = '') {
		$list = array();

		//選択中のグループ
		$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;

		//ログイン中のユーザー
		$uid = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_uid() : '';

		//日リスト
		if (!empty($date)) {
			$day_list = $this->getDayList($uid, $date);
			$date_top = $day_list[0]['date'];
			$date_end = $day_list[count($day_list) - 1]['date'];
		}

		//データベース接続
		$db = $this->DBOpen();
		if (!$db) {
			return $ret;
		}

		//スケジュール読込
		$sch_list = array();
		try {
			$sql = 'SELECT a.uid, b.name, a.status AS member_status ';
			$sql.= 'FROM '.T_GROUP_MEMBER.' a ';
			$sql.= 'INNER JOIN '.T_USER.' b ON a.uid=b.uid ';
			$sql.= 'WHERE a.group_no=:group_no AND a.status=:status ';

			if (!empty($date)) {
				$sql.= 'AND (EXISTS ( ';
				$sql.= 'SELECT no FROM '.T_SCHEDULE.' ';
				$sql.= 'WHERE group_no=a.group_no AND uid=a.uid AND date>=:date_top AND date<=:date_end ';
				$sql.= ') OR a.uid=:uid) ';
			}

			$sql.= 'ORDER BY b.uid ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':status', GROUP_MEMBER_STATUS_NOMAL);

			if (!empty($date)) {
				$stmt->bindValue(':date_top', $date_top);
				$stmt->bindValue(':date_end', $date_end);
				$stmt->bindValue(':uid', $uid);
			}

			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$item = array();
				foreach ($row as $key => $value) {
					$item[$key] = mb_convert_encoding($value, "UTF-8", "auto");
				}
				$list[] = $item;
			}
		} catch (PDOException $e) {
			$this->message = "ユーザーリストの読込に失敗しました。<br>".$e->getMessage();
			return $list;
		}
		$stmt = null;
		$db = null;

		return $list;
	}
}
