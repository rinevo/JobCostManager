<?php
require_once(dirname(__FILE__).'/db_config.php');
require_once(dirname(__FILE__).'/class/AccessLogSQL.class.php');

$log = new AccessLogSQL($dbopts);
