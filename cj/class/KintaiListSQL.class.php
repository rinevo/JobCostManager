<?php
require_once(dirname(__FILE__).'/../../define.php');
require_once(dirname(__FILE__).'/../../Encode.php');

class KintaiListSQL {

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

	function getList($select_uid, $date) {

		//選択中のグループ
		$group_no = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_group_no() : 0;

		//データベース接続
		$db = $this->DBOpen();
		if (!$db) {
			return false;
		}

		//対象年月のカレンダー取得
		$cmn = new CjCommonSQL($GLOBALS['dbopts']);
		$cmn->setDefSime(INI_KINTAI_SIME1, INI_KINTAI_SIME2);
		$day_list = $cmn->getDayList($select_uid, $date);

		//日の範囲
		$date_top = $day_list[0]['date'];
		$date_end = $day_list[count($day_list) - 1]['date'];

		//読込
		try {
			$sql = 'SELECT a.date, a.section_no, a.time, a.kintai_no, b.name AS kintai_name, a.costin, a.costout ';
			$sql.= 'FROM '.T_SCHEDULE.' a ';
			$sql.= 'LEFT JOIN '.T_KINTAI.' b ON b.no=a.kintai_no ';
			$sql.= 'WHERE group_no=:group_no AND uid=:uid AND date>=:date_top AND date<=:date_end ';
			$sql.= 'ORDER BY date, sortno ';
			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':uid', $select_uid);
			$stmt->bindValue(':date_top', $date_top);
			$stmt->bindValue(':date_end', $date_end);
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
			$this->message = "設定の読込に失敗しました。<br>".$e->getMessage();
			return false;
		}
		$stmt = null;
		$db = null;

		//勤怠リスト生成
		$kintai_list = array();
		foreach ($day_list as $day_row) {
			$item = array();
			$item['date'] = $day_row['date'];
			$item['section_top_no'] = '';
			$item['section_end_no'] = '';
			$item['section_top_name'] = '';
			$item['section_end_name'] = '';
			$item['time_top'] = '';
			$item['time_end'] = '';
			$item['costin'] = 0;
			$item['costout'] = 0;
			$costin = 0;
			$costout = 0;
			$kintai_start = false;
			foreach ($list as $data) {
				//日ごとに集計
				if (format($data['date'],'Y/m/d') == format($day_row['date'],'Y/m/d')) {
					if (!$kintai_start) {
						//出勤～
						if ($data['section_no'] == 1) {
							$kintai_start = true;
							if (empty($item['section_top_no'])) {
								$item['section_top_no'] = $data['kintai_no'];
								$item['section_top_name'] = $data['kintai_name'];
								$item['time_top'] = !empty($data['time']) ? format($data['time'],'H:i') : '';
							}
						}
					} else {
						//～退勤
						if ($data['section_no'] == 3) {
							$item['section_end_no'] = $data['kintai_no'];
							$item['section_end_name'] = $data['kintai_name'];
							$item['time_end'] = !empty($data['time']) ? format($data['time'],'H:i') : '';
							$item['costin'] += $costin;
							$item['costout'] += $costout;
						} else {
							$costin += $data['costin'];
							$costout += $data['costout'];
						}
					}
				} else {
					if (!empty($item['section_top_no'])) {
						break;
					}
				}
			}
			$kintai_list[] = $item;
		}

		return $kintai_list;
	}
}
