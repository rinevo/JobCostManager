<?php require_once(dirname(__FILE__).'/define.php'); ?>
<?php require_once(dirname(__FILE__).'/Encode.php'); ?>
<?php require_once(dirname(__FILE__).'/db_config.php'); ?>
<?php
// アクセスログ
require_once(dirname(__FILE__).'/class/AccessLogSQL.class.php');
$log = new AccessLogSQL($GLOBALS['dbopts']);
$log->Write(0, '', __FILE__, __FUNCTION__, __LINE__);
$list = $log->getList();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo APP_TITLE; ?> | 利用規約</title>
<meta name="viewport" content="width=980px">
<link rel="shortcut icon" href="<?php echo PROJECT_ROOT ?>/favicon.ico">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/style.css" media="all">
<link rel="stylesheet" href="<?php echo PROJECT_ROOT ?>/css/riyoukiyaku_page.css" media="all">

</head>
<body>

<?php $GLOBALS['header_arg'] = false; ?>
<?php require_once(dirname(__FILE__).'/header.php'); ?>

<div id="content" class="clearfix">
<div class="inner">

	<?php require_once(dirname(__FILE__).'/riyoukiyaku.php'); ?>

</div><!-- inner -->
</div><!-- content -->

<?php require_once(dirname(__FILE__).'/footer.php'); ?>

</body>
</html>
