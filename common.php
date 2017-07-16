<?php
require_once(dirname(__FILE__).'/define.php');

// ログイン、トップページ表示
function go_login($message = '') {

	//メッセージがあればセッションに格納して下の画面で表示
	if (strlen($message)) {
		$_SESSION[S_MESSAGE] = $message;
	}

	//認証処理、ログインできなかったらログイン画面を表示
	require_once(dirname(__FILE__).'/login/myauth.php');

	//ログインできたらトップページを表示
	if (isset($auth) && $auth->getAuth()) {
		//header('Location: http://'.$_SERVER['HTTP_HOST'].PROJECT_ROOT.'/');
		preg_match('|^(https?://.+?)/|i', $_SERVER['HTTP_REFERER'], $url);
		$url = $url[1].PROJECT_ROOT.'/';
		header('Location: '.$url);
	}

	exit();
}

// 呼び出し元のページへ戻る
function go_referer($message = '') {

	//メッセージがあればセッションに格納して下の画面で表示
	if (strlen($message)) {
		if (!isset($_SESSION)) {
			session_start();
		}
		$_SESSION[S_MESSAGE] = $message;
	}

	//呼び出し元のページ表示
	header('Location: '.$_SERVER['HTTP_REFERER']);
	exit();
}
