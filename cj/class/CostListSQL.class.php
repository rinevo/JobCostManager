<?php
require_once(dirname(__FILE__).'/../../define.php');
require_once(dirname(__FILE__).'/../../Encode.php');

class CostListSQL {

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

		//ログイン中のユーザー
		$uid = isset($GLOBALS['auth']) ? $GLOBALS['auth']->getParent_uid() : '';

		//設定の取得
		$cmn = new CjCommonSQL($GLOBALS['dbopts']);
		$cmn->setDefSime(INI_COST_SIME1, INI_COST_SIME2);
		$cost_field =  $cmn->getIni_data(INI_COST_FIELD,$uid);

		//選択項目を配列に格納
		$select_list = array('project_no');
		if (!empty($cost_field)) {
			$select_list = str_getcsv($cost_field);
		}

		//データベース接続
		$db = $this->DBOpen();
		if (!$db) {
			return false;
		}

		//対象年月のカレンダー取得
		$day_list = $cmn->getDayList($uid, $date);

		//日の範囲
		$date_top = $day_list[0]['date'];
		$date_end = $day_list[count($day_list) - 1]['date'];

		//表示項目
		$select_field = '';
		foreach ($select_list as $item) {
			$select_field.= ('a.'.$item.',');
		}
		$select_field = substr($select_field, 0, strlen($select_field)-1).' ';

		//読込
		try {
			//項目を取得
			$sql = 'SELECT ';

			$sql.= $select_field;

			$tmp = '';
			foreach ($select_list as $item) {
				switch ($item) {
					case 'project_no':
						$sql.= ',b.name AS project_name ';
						break;
					case 'work_no':
						$sql.= ',c.name AS work_name ';
						break;
					case 'customer_no':
						$sql.= ',d.name AS customer_name ';
						break;
					case 'process_no':
						$sql.= ',e.name AS process_name ';
						break;
					case 'action':
						$sql.= ',a.action ';
						break;
				}
			}

			$sql.= 'FROM '.T_SCHEDULE.' a ';
			$sql.= 'LEFT JOIN '.T_PROJECT.' b ON a.project_no=b.no ';
			$sql.= 'LEFT JOIN '.T_WORK.' c ON a.work_no=c.no ';
			$sql.= 'LEFT JOIN '.T_CUSTOMER.' d ON a.customer_no=d.no ';
			$sql.= 'LEFT JOIN '.T_PROCESS.' e ON a.process_no=e.no ';
			$sql.= 'LEFT JOIN '.T_TODO.' f ON a.project_no=f.project_no AND a.work_no=f.work_no AND a.customer_no=f.customer_no AND a.process_no=f.process_no AND a.action=f.action ';

			$sql.= 'WHERE a.section_no=2 AND a.group_no=:group_no AND a.uid=:uid AND a.date>=:date_top AND a.date<=:date_end ';
			$sql.= 'GROUP BY ';

			$sql.= $select_field;

			$sql.= 'ORDER BY ';

			$actionflg = false;
			$tmp = '';
			foreach ($select_list as $item) {
				switch ($item) {
					case 'project_no':
						$tmp.= 'b.sortno,';
						break;
					case 'work_no':
						$tmp.= 'c.sortno,';
						break;
					case 'customer_no':
						$tmp.= 'd.sortno,';
						break;
					case 'process_no':
						$tmp.= 'e.sortno,';
						break;
					case 'action':
						$actionflg = true;
						break;
				}
			}
			$sql.= $tmp.'f.sortno ';
			if ($actionflg) {
				$tmp.= 'a.action '; //行動のソートは、ＴＯＤＯの並びでソートした後にする
			}

			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':uid', $select_uid);
			$stmt->bindValue(':date_top', $date_top);
			$stmt->bindValue(':date_end', $date_end);
			$stmt->execute();

			$item_list = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$item = array();
				foreach ($row as $key => $value) {
					$item[$key] = mb_convert_encoding($value, "UTF-8", "auto");
				}
				$item_list[] = $item;
			}

			//工数を取得
			$sql = 'SELECT date, project_no, work_no, customer_no, process_no, action, costin, costout ';
			$sql.= 'FROM '.T_SCHEDULE.' ';
			$sql.= 'WHERE section_no=2 AND group_no=:group_no AND uid=:uid AND date>=:date_top AND date<=:date_end ';
			$sql.= 'ORDER BY date ';

			$stmt = $db->prepare($sql);
			$stmt->bindValue(':group_no', $group_no);
			$stmt->bindValue(':uid', $select_uid);
			$stmt->bindValue(':date_top', $date_top);
			$stmt->bindValue(':date_end', $date_end);
			$stmt->execute();

			$cost_list = array();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$item = array();
				foreach ($row as $key => $value) {
					$item[$key] = mb_convert_encoding($value, "UTF-8", "auto");
				}
				$item['date'] = format($item['date'],'Y/m/d');
				$cost_list[] = $item;
			}
		} catch (PDOException $e) {
			$this->message = "工数の読込に失敗しました。<br>".$e->getMessage();
			return false;
		}
		$stmt = null;
		$db = null;

		//項目名
		$view_list = array();
		foreach ($select_list as $item) {
			switch ($item) {
				case 'project_no':
					$view_list['project_name'] = 'プロジェクト';
					break;
				case 'work_no':
					$view_list['work_name'] = '作業';
					break;
				case 'customer_no':
					$view_list['customer_name'] = '顧客';
					break;
				case 'process_no':
					$view_list['process_name'] = '工程';
					break;
				case 'action':
					$view_list['action'] = '行動';
					break;
			}
		}
		$view_length = count($view_list);

		//工数リスト生成
		$list = array();
		$list[] = array();

		foreach ($view_list as $key => $value) {	//タイトルの項目名
			$list[0][$key] = $value;
		}
		foreach ($day_list as $row) {				//タイトルの日
			$list[0][$row['date']] = $row['date'];
		}

		$row_index = 1;
		foreach ($item_list as $row) {
			foreach ($row as $key => $value) {		//項目
				$list[$row_index][$key] = $value;
			}
			foreach ($day_list as $day) {			//日ごとの格納枠を確保
				$list[$row_index][$day['date']]['costin'] = 0;
				$list[$row_index][$day['date']]['costout'] = 0;
				$list[$row_index][$day['date']]['action'] = 0;
			}
			$row_index++;
		}

		//集計
		$list_length = count($list);
		foreach ($cost_list as $row) {
			for ($i = 1; $i < $list_length; $i++) {
				//対象行の検索
				$hit = true;
				if ($hit && isset($list[$i]['project_no'])) {
					if ($list[$i]['project_no'] != $row['project_no']) {
						$hit = false;
					}
				}
				if ($hit && isset($list[$i]['work_no'])) {
					if ($list[$i]['work_no'] != $row['work_no']) {
						$hit = false;
					}
				}
				if ($hit && isset($list[$i]['customer_no'])) {
					if ($list[$i]['customer_no'] != $row['customer_no']) {
						$hit = false;
					}
				}
				if ($hit && isset($list[$i]['process_no'])) {
					if ($list[$i]['process_no'] != $row['process_no']) {
						$hit = false;
					}
				}
				if ($hit && isset($list[$i]['action'])) {
					if ($list[$i]['action'] != $row['action']) {
						$hit = false;
					}
				}
				//対象列に加算
				if ($hit) {
					$list[$i][$row['date']]['costin'] += $row['costin'];
					$list[$i][$row['date']]['costout'] += $row['costout'];
					$list[$i][$row['date']]['action'] = 1;
				}
			}
		}

		return $list;
	}

	//表示項目と選択を読込
	function getCostField_list($select_uid) {
		$list = false;

		//設定の取得
		$cmn = new CjCommonSQL($GLOBALS['dbopts']);
		$cost_field =  $cmn->getIni_data(INI_COST_FIELD,$select_uid);

		//データベース接続
		$db = $this->DBOpen();
		if (!$db) {
			return false;
		}

		//読込
		$list = array();
		try {
			$sql = 'SELECT id, name, 0 AS chk ';
			$sql.= 'FROM '.T_COSTFIELD.' ';
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
			$this->message = "表示項目の読込に失敗しました。<br>".$e->getMessage();
			return false;
		}
		$stmt = null;
		$db = null;

		//選択項目を配列に格納
		$select_list = array('project_no');
		if (!empty($cost_field)) {
			$select_list = str_getcsv($cost_field);
		}

		//選択フラグ列を追加
		$list_length = count($list);
		for ($i = 0; $i < $list_length; $i++) {
			foreach ($select_list as $select_row) {
				if ($list[$i]['id'] == $select_row) {
					$list[$i]['chk'] = 1;
				}
			}
		}

		return $list;
	}

	//項目の選択を保存
	function saveSelect($select_uid, $select_list) {

		//CSV形式にする
		$inidata = '';
		foreach ($select_list as $row) {
			if (strlen($inidata) > 0) {
				$inidata.= ',';
			}
			$inidata.= $row;
		}

		//設定の保存
		$cmn = new CjCommonSQL($GLOBALS['dbopts']);
		$cmn->setIni_data(INI_COST_FIELD, $inidata, $select_uid);

		return true;
	}
}
