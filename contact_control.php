<?php

// require_once("Mail.php");
require_once(dirname(__FILE__) . '/define.php');
require_once(dirname(__FILE__) . '/Encode.php');
require_once(dirname(__FILE__) . '/db_config.php');
require_once(dirname(__FILE__) . '/common.php');

// アクセスログ
require_once(dirname(__FILE__) . '/class/AccessLogSQL.class.php');
$log = new AccessLogSQL($GLOBALS['dbopts']);
$log->Write(0, '', __FILE__, __FUNCTION__, __LINE__);

// メール送信
function contact_send() {
	
    // 入力値のチェック
    $name = e($_POST['name']);
    if (strlen($name) < 1) {
        go_referer('お名前を入力してください。');
    }

    $mail = isset($_POST['mail']) ? e($_POST['mail']) : '';
    if (strlen($mail) < 1) {
        go_referer('メールアドレスを入力してください。');
    }
    if (strlen($mail) > 100) {
        go_referer('メールアドレスは100文字以内で入力してください。');
    }
    if (!preg_match('/^\w+([-+.\']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/iD', $mail)) {
        go_referer('不正なメールアドレスです。<br/>正しいメールアドレスで登録してください。');
    }

    $detail = e($_POST['detail']);
    if (strlen($detail) < 1) {
        go_referer('お問い合わせ内容を入力してください。');
    }

    // メール文書生成
    $domain = MYDOMAIN;
    $subject = '[' . $domain . '] お問い合わせ';
    $to = MAIL_FROM;
    $from = $mail;

    $headers = <<<HEAD
From: {$from}
Return-Path: {$from}
HEAD;

    $body = <<<BODY
{$name} 様からのお問い合わせ

{$detail}

--------------------------------------------------
{$domain}
BODY;

    // メール配信
    mb_language('ja');
    $ret = mb_send_mail($to, $subject, $body, $headers);

    if ($ret == true) {

    } else {

        // 登録画面に戻る
        go_referer('申し訳ありませんメール配信できませんでした。しばらくしてから再度お試しください。');

    }

    return;
}

// POST処理
if (isset($_POST['token'])) {

    session_start();
    if ($_POST['token'] !== $_SESSION[S_TOKEN]) {
        header("HTTP/1.0 404 Not Found");
    }

    // 送信
    contact_send();

    go_login('当サイト管理者へお問い合わせ内容を送信しました。');
    return;
}

// 上記処理が実行されなければ不正なアクセスとして扱う
header("HTTP/1.0 404 Not Found");