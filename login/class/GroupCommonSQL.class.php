<?php
require_once(dirname(__FILE__).'/../../define.php');
require_once(dirname(__FILE__).'/../../Encode.php');

class GroupCommonSQL {

	public static function deleteGroupMember($db, $group_no, $uid) {

		$stmt = $db->prepare('DELETE FROM '.T_SCHEDULE.' WHERE group_no=:group_no AND uid=:uid');
		$stmt->bindValue(':group_no', $group_no);
		$stmt->bindValue(':uid', $uid);
		$stmt->execute();

		$stmt = $db->prepare('DELETE FROM '.T_TODO.' WHERE group_no=:group_no AND uid=:uid');
		$stmt->bindValue(':group_no', $group_no);
		$stmt->bindValue(':uid', $uid);
		$stmt->execute();

		$stmt = $db->prepare('DELETE FROM '.T_INI.' WHERE group_no=:group_no AND uid=:uid');
		$stmt->bindValue(':group_no', $group_no);
		$stmt->bindValue(':uid', $uid);
		$stmt->execute();

		$stmt = $db->prepare('DELETE FROM '.T_GROUP_MEMBER.' WHERE group_no=:group_no AND uid=:uid');
		$stmt->bindValue(':group_no', $group_no);
		$stmt->bindValue(':uid', $uid);
		$stmt->execute();

		$stmt = $db->prepare('DELETE FROM '.T_LOCK.' WHERE group_no=:group_no AND uid=:uid');
		$stmt->bindValue(':group_no', $group_no);
		$stmt->bindValue(':uid', $uid);
		$stmt->execute();

	}

	public static function deleteGroup($db, $group_no) {

		$stmt = $db->prepare('DELETE FROM '.T_PROJECT.' WHERE group_no=:group_no');
		$stmt->bindValue(':group_no', $group_no);
		$stmt->execute();

		$stmt = $db->prepare('DELETE FROM '.T_WORK.' WHERE group_no=:group_no');
		$stmt->bindValue(':group_no', $group_no);
		$stmt->execute();

		$stmt = $db->prepare('DELETE FROM '.T_PROCESS.' WHERE group_no=:group_no');
		$stmt->bindValue(':group_no', $group_no);
		$stmt->execute();

		$stmt = $db->prepare('DELETE FROM '.T_CUSTOMER.' WHERE group_no=:group_no');
		$stmt->bindValue(':group_no', $group_no);
		$stmt->execute();

		$stmt = $db->prepare('DELETE FROM '.T_ROLE.' WHERE group_no=:group_no');
		$stmt->bindValue(':group_no', $group_no);
		$stmt->execute();

		$stmt = $db->prepare('DELETE FROM '.T_GROUP.' WHERE no=:no');
		$stmt->bindValue(':no', $group_no);
		$stmt->execute();

	}

}
