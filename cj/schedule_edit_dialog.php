<?php
// 呼び出し元で、次のコードが必要
// "/cj/css/schedule_edit_dialog.css"
// "/cj/js/schedule_edit_dialog.js
// $select_uid
require_once(dirname(__FILE__).'/class/ScheduleListSQL.class.php');
require_once(dirname(__FILE__).'/class/CjCommonSQL.class.php');
require_once(dirname(__FILE__).'/class/TodoListSQL.class.php');
if (!isset($sch)) {
	$sch = new ScheduleListSQL($dbopts);
}
if (!isset($cmn)) {
	$cmn = new CjCommonSQL($dbopts);
}
if (!isset($todo)) {
	$todo = new TodoListSQL($dbopts);
}

//勤怠リスト
$kintai_start_list = $cmn->getKintai_list(1);
$kintai_end_list = $cmn->getKintai_list(2);
$kintai_end_list = array_reverse($kintai_end_list);

//区分リスト
$section_list = $sch->getSection_list();

//作業リスト
$todo_list = $sch->getTodo_list($select_uid);

//プロジェクトリスト
$project_list = $sch->getProject_list();

//業務リスト
$work_list = array();

//顧客リスト
$customer_list = $sch->getCustomer_list();

//工程リスト
$process_list = $sch->getProcess_list();

//ＴＯＤＯ項目リスト
$todofield_list = $todo->getTodoField_list($select_uid);

//ＴＯＤＯ括り項目
$todobundle =  $cmn->getIni_data(INI_TODO_BUNDLE,$select_uid);
$bundle_list = !empty($todobundle) ? str_getcsv($todobundle) : array();
$bk_name = array();
foreach ($bundle_list as $bundle_row) {
	$bk_name[$bundle_row] = '';
}
?>
<div id="edit_dialog" title="入力" style="display:none">
	<p class="validateTips"></p>
	<input type="hidden" class="edit_type" value="" />
	<table>
	<tr class="tr_todo">
		<td>
			<label for="edit_todo" >TODO選択</label>
		</td>
		<td>
			<select id="edit_todo" onchange="select_edit_Todo(this)">
			<option value="0"></option>
			<?php if ($todo_list) { $bundle_count = 0; foreach ($todo_list as $row) {
				//括り項目を比較
				$bundle_hit = true;
				$bundle_string = '';
				foreach ($bundle_list as $bundle_row) {
					if (isset($row[$bundle_row])) {
						if ($bk_name[$bundle_row] != $row[$bundle_row]) {	//括り項目の名称が一致するか？
							$bundle_hit = false;
							$bk_name[$bundle_row] = $row[$bundle_row];
						}
						foreach ($todofield_list as $todofield_row) {		//括り項目名を生成
							if ($todofield_row['sort_id'] == $bundle_row) {
								$bundle_string.= e($row[$todofield_row['show_id']]).' ';
								break;
							}
						}
					}
				}
				if (!$bundle_hit) {	//前の括り項目と一致しなければ、括り行を表示
				?>
					<?php if ($bundle_count > 0) { ?>
					</optgroup>
					<?php } ?>
					<optgroup label="<?php echo $bundle_string; ?>">
				<?php $bundle_count++; } ?>
				<option value="<?php echo e($row['no']); ?>"><?php echo e($row['action']); ?></option>
			<?php } } ?>
			<?php if ($bundle_count > 0) { ?>
				</optgroup>
			<?php } ?>
			</select>
			<div id="edit_todo_busy" style="display:none">
				<img alt="処理中" src="images/loading-28.gif" width="16" height="16">
			</div>
		</td>
	</tr>
	<tr class="tr_section">
		<td>
			<label for="edit_section" >区分</label>
		</td>
		<td>
			<select id="edit_section" onchange="select_edit_Section(this)">
			<?php if ($section_list) { foreach ($section_list as $row) { ?>
				<option value="<?php echo e($row['no']); ?>"><?php echo e($row['name']); ?></option>
			<?php } } ?>
			</select>
		</td>
	</tr>
	<tr class="tr_kintai">
		<td>
			<label class="lbl_kintai_start" for="edit_kintai_start" >勤怠</label>
			<label class="lbl_kintai_end" for="edit_kintai_end" >勤怠</label>
		</td>
		<td>
			<select id="edit_kintai_start">
			<option value="0"></option>
			<?php if ($project_list) { foreach ($kintai_start_list as $row) { ?>
				<option value="<?php echo e($row['no']); ?>"><?php echo e($row['name']); ?></option>
			<?php } } ?>
			</select>
			<select id="edit_kintai_end">
			<option value="0"></option>
			<?php if ($project_list) { foreach ($kintai_end_list as $row) { ?>
				<option value="<?php echo e($row['no']); ?>"><?php echo e($row['name']); ?></option>
			<?php } } ?>
			</select>
		</td>
	</tr>
	<tr class="tr_project">
		<td>
			<label for="edit_project" >プロジェクト</label>
		</td>
		<td>
			<select id="edit_project" onchange="select_edit_Project(this)">
			<option value="0"></option>
			<?php if ($project_list) { foreach ($project_list as $row) { ?>
				<option value="<?php echo e($row['no']); ?>"><?php echo e($row['name']); ?></option>
			<?php } } ?>
			</select>
		</td>
	</tr>
	<tr class="tr_work">
		<td>
			<label for="edit_work" >業務</label>
		</td>
		<td>
			<select id="edit_work" onchange="">
			<option value="0"></option>
			<?php if ($work_list) { foreach ($work_list as $row) { ?>
				<option value="<?php echo e($row['no']); ?>"><?php echo e($row['name']); ?></option>
			<?php } } ?>
			</select>
			<div id="edit_work_busy" style="display:none">
				<img alt="処理中" src="images/loading-28.gif" width="16" height="16">
			</div>
		</td>
	</tr>
	<tr class="tr_customer">
		<td>
			<label for="edit_customer" >顧客</label>
		</td>
		<td>
			<select id="edit_customer" onchange="">
			<option value="0"></option>
			<?php if ($customer_list) { foreach ($customer_list as $row) { ?>
				<option value="<?php echo e($row['no']); ?>"><?php echo e($row['name']); ?></option>
			<?php } } ?>
			</select>
		</td>
	</tr>
	<tr class="tr_process">
		<td>
			<label for="edit_process" >工程</label>
		</td>
		<td>
			<select id="edit_process" onchange="">
			<option value="0"></option>
			<?php if ($process_list) { foreach ($process_list as $row) { ?>
				<option value="<?php echo e($row['no']); ?>"><?php echo e($row['name']); ?></option>
			<?php } } ?>
			</select>
		</td>
	</tr>
	<tr class="tr_action">
		<td>
			<label for="edit_action">行動</label>
		</td>
		<td>
			<input type="text" id="edit_action">
		</td>
	</tr>
	<tr class="tr_todoentry">
		<td>
			<label for="edit_todoentry"></label>
		</td>
		<td>
			<input type="checkbox" id="edit_todoentry"><label for="edit_todoentry">作業登録</label>
		</td>
	</tr>
	</table>
</div>
