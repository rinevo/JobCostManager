<?php
require_once(dirname(__FILE__).'/../../define.php');
require_once(dirname(__FILE__).'/EncryptSupport.class.php');

abstract class UserList {

	private $parent;	// ログイン中のユーザー情報
	protected $enc;		// 暗号化クラス

	function __construct($opts = null) {
		// 暗号化クラス準備
		$this->enc = new EncryptSupport();
		// 現在ログイン中のユーザー情報
		if (isset($_SESSION[S_PARENT_USER])) {
			$this->parent = $_SESSION[S_PARENT_USER];
		} else {
			$this->parent = $this->getDefault();
		}
	}

	function getDefault() {
		return array('uid'=>'', 'passwd'=>'', 'param'=>'', 'mail'=>'', 'status'=>'', 'name'=>'', 'group_no_default'=>''
				, 'lock_uid'=>'', 'lock_time'=>''
				, 'group_no'=>'', 'group_name'=>'', 'role_no'=>'', 'role_name'=>'', 'role_user'=>'');
	}

	function setParent($userinfo) {
		$this->parent = $userinfo;
		$_SESSION[S_PARENT_USER] = $this->parent;
	}
	function getParent() {
		return $this->parent;
	}

	function getParent_uid() {
		return isset($this->parent['uid']) ? $this->parent['uid'] : false;
	}
	function getParent_passwd() {
		return isset($this->parent['passwd']) ? $this->parent['passwd'] : false;
	}
	function getParent_param() {
		return isset($this->parent['param']) ? $this->parent['param'] : '';
	}
	function getParent_mail() {
		return isset($this->parent['mail']) ? $this->parent['mail'] : '';
	}
	function getParent_status() {
		return isset($this->parent['status']) ? $this->parent['status'] : '';
	}
	function getParent_name() {
		return isset($this->parent['name']) ? $this->parent['name'] : '';
	}
	function getParent_group_no_default() {
		return isset($this->parent['group_no_default']) ? $this->parent['group_no_default'] : 0;
	}
	function getParent_group_no() {
		return isset($this->parent['group_no']) ? $this->parent['group_no'] : 0;
	}
	function getParent_group_name() {
		return isset($this->parent['group_name']) ? $this->parent['group_name'] : '';
	}
	function getParent_member_status() {
		return isset($this->parent['member_status']) ? $this->parent['member_status'] : '';
	}
	function getParent_role_no() {
		return isset($this->parent['role_no']) ? $this->parent['role_no'] : 0;
	}
	function getParent_role_name() {
		return isset($this->parent['role_name']) ? $this->parent['role_name'] : '';
	}
	function getParent_role_user() {
		return isset($this->parent['role_user']) ? $this->parent['role_user'] : 0;
	}
	function getParent_role_work() {
		return isset($this->parent['role_work']) ? $this->parent['role_work'] : 0;
	}
	function getParent_role_customer() {
		return isset($this->parent['role_customer']) ? $this->parent['role_customer'] : 0;
	}
	function getParent_role_process() {
		return isset($this->parent['role_process']) ? $this->parent['role_process'] : 0;
	}
	function getParent_role_todo() {
		return isset($this->parent['role_todo']) ? $this->parent['role_todo'] : 0;
	}
	function getParent_role_schedule() {
		return isset($this->parent['role_schedule']) ? $this->parent['role_schedule'] : 0;
	}
	function getParent_role_cost() {
		return isset($this->parent['role_cost']) ? $this->parent['role_cost'] : 0;
	}
	function getParent_role_kintai() {
		return isset($this->parent['role_kintai']) ? $this->parent['role_kintai'] : 0;
	}

	function setParent_group_no_default($group_no) {
		$this->parent['group_no_default'] = $group_no;
	}
	function setParent_group_no($group_no) {
		$this->parent['group_no'] = $group_no;
	}

	abstract function getUserList();
	abstract function getParentInfo($uid);
	abstract function getUserInfo($uid);
	abstract function addUser();
	abstract function updateUser();
	abstract function deleteUser();
}