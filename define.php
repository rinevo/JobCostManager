<?php

/* テキスト
 ---------------------------------------------------- */
define('APP_TITLE','行動記録');
define('APP_COPYRIGHT','Copyright&copy; rinevo.com All Rights Reserved.');

/* アドレス
 ---------------------------------------------------- */
define('MAIL_FROM','user@domain'); // 送信メールアドレス
define('MYDOMAIN','rinevo.com');

/* パス
 ---------------------------------------------------- */
define('PROJECT_ROOT','/JobCostManager');

/* 権限フラグ
 ---------------------------------------------------- */
define('ROLE_VIW',1);			// 0000 0001 参照(自分)
define('ROLE_ADD',2);			// 0000 0010 追加(自分)
define('ROLE_EDT',4);			// 0000 0100 編集(自分)
define('ROLE_DEL',8);			// 0000 1000 削除(自分)

define('ROLE_MEMBER_VIW',16);	// 0001 0000 参照(他メンバー)
define('ROLE_MEMBER_ADD',32);	// 0010 0000 追加(他メンバー)
define('ROLE_MEMBER_EDT',64);	// 0100 0000 編集(他メンバー)
define('ROLE_MEMBER_DEL',128);	// 1000 0000 削除(他メンバー)

/* テーブル名
 ---------------------------------------------------- */
define('T_USER','jc_user');
define('T_ROLE','jc_role');
define('T_GROUP','jc_group');
define('T_GROUP_MEMBER','jc_group_member');
define('T_LOCK','jc_lock');
define('T_LOG','jc_log');
define('T_SCHEDULE','jc_schedule');
define('T_TODO','jc_todo');
define('T_SECTION','jc_section');
define('T_KINTAI','jc_kintai');
define('T_PROJECT','jc_project');
define('T_WORK','jc_work');
define('T_PROCESS','jc_process');
define('T_CUSTOMER','jc_customer');
define('T_STATUS','jc_status');
define('T_PERIOD','jc_period');
define('T_TODOFIELD','jc_todofield');
define('T_COSTFIELD','jc_costfield');
define('T_INI','jc_ini');

/* ロック対象データ(data)
 ---------------------------------------------------- */
define('LOCK_USER','1');

/* ユーザー状態(status)
 ---------------------------------------------------- */
define('USER_STATUS_NOMAL','0');
define('USER_STATUS_ENTRY','1');

/* グループメンバー状態(status)
 ---------------------------------------------------- */
define('GROUP_MEMBER_STATUS_NOMAL','0');
define('GROUP_MEMBER_STATUS_ENTRY','1');

/* 設定(inino)
 ---------------------------------------------------- */
define('INI_TODO_PERIOD','1');		//ＴＯＤＯ期間
define('INI_TODO_PAST','2');		//ＴＯＤＯ過去の[終了][中止]は表示しない
define('INI_TODO_SORT','3');		//ＴＯＤＯ並び替え
define('INI_SCH_SIME1','4');		//行動記録の締日１（月の中計日）
define('INI_SCH_SIME2','5');		//行動記録の締日２（月の集計日）
define('INI_COST_FIELD','6');		//工数項目
define('INI_COST_SIME1','7');		//工数表の締日１（月の中計日）
define('INI_COST_SIME2','8');		//工数表の締日２（月の集計日）
define('INI_KINTAI_SIME1','9');		//勤怠表の締日１（月の中計日）
define('INI_KINTAI_SIME2','10');	//勤怠表の締日２（月の集計日）
define('INI_TODO_BUNDLE','11');		//ＴＯＤＯ括り項目
define('INI_SHOW_USER','12');		//データの無いユーザーは表示しない

/* COOKIE名
 ---------------------------------------------------- */
define('C_USERNAME','jc_username');
define('C_PARAM','jc_param');
define('C_LONGLOGIN','jc_longlogin');
define('C_MESSAGE','jc_message');

/* SESSION名
 ---------------------------------------------------- */
define('S_LOGIN','jc_login');
define('S_IDLE','jc_idle');
define('S_USERNAME','jc_username');
define('S_PASSWORD','jc_password');
define('S_TOKEN','jc_token');
define('S_PARENT_USER','jc_parent_user');
define('S_MESSAGE','jc_message');
define('S_MESSAGE2','jc_message2');

define('S_USER_EDIT_UID','jc_user_edit_uid');

define('S_SELECT_UID','jc_select_uid');
define('S_SELECT_DATE','jc_select_date');
define('S_SELECT_DAY','jc_select_day');
define('S_SCROLLPOS','jc_scrollposition');
