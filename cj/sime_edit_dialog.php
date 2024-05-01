<?php
// 呼び出し元で、次のコードが必要
// "/cj/css/sime_edit_dialog.css"
// "/cj/js/sime_edit_dialog.js
// $sime1 = $cmn->getSime1($select_uid);
// $sime2 = $cmn->getSime2($select_uid);
?>
<div id="sime_dialog" title="締日の設定" style="display:none">
<p class="validateTips"></p>
<table>
<tr>
<td>
<label>月の中計日<br>
<select class="edit-sime1">
		<?php for ($i = 1; $i <= 31; $i++) { ?>
			<option value="<?php echo $i; ?>" <?php if ($sime1 == $i) { ?>selected<?php } ?>><?php echo $i; ?></option>
		<?php } ?>
		</select>
		</label>
	</td>
<tr>
<tr>
	<td>
		<label>月の締日<br>
		<select class="edit-sime2">
		<?php for ($i = 1; $i <= 31; $i++) { ?>
			<option value="<?php echo $i; ?>" <?php if ($sime2 == $i) { ?>selected<?php } ?>><?php echo $i; ?></option>
		<?php } ?>
		</select>
		</label>
	</td>
<tr>
</table>
</div>
