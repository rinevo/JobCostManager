<?php
function e($str, $charset = 'UTF-8') {
	return (htmlspecialchars($str, ENT_QUOTES, $charset));
}

function encode($var) {
	return is_array($var) ? array_map('encode',$var) : mb_convert_encoding($var, "UTF-8", "auto");
}

function format($datetime, $format = 'yyyy/MM/dd') {
	$ts = strtotime($datetime);
	return (date($format, $ts));
}

function week($day) {
	$week_list = array('日','月','火','水','木','金','土');
	$date = new DateTime($day);
	return $week_list[$date->format('w')];
}

//ランダム文字列生成
function getRandString($length) {
	$strinit = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$arr_str = str_split($strinit);
	$str = "";
	for ($i = 0; $i < $length; $i++) {
		$rand_key = array_rand($arr_str, 1);
		$str .= $arr_str[$rand_key];
	}
	return $str;
}
