$header
	<form method="post" action="$filename">
		<input type="hidden" name="s" value="$s">
		<input type="hidden" name="page" value="$page">
		<input type="hidden" name="action" value="$action">
		<input type="hidden" name="id" value="$id">
		<table cellpadding="5" cellspacing="4" border="0" align="center" class="maintable_60">
	    	<tr>
	    		<td colspan="2" class="maintitle"><b><img src="images/modules/mailman/mm-icon.png" alt="" />&nbsp;{$lng['module']['mailman']['list_del']}</b></td>
	    	</tr>
		<tr>
			<td class="main_field_name"><b>{$lng['module']['mailman']['list_del_archive']}</b></td>
			<td class="main_field_display"><input type="checkbox" name="delete_archive_checkbox"/></td>
		</tr>
		<tr>
			<td class="main_field_confirm" colspan="2"><input type="hidden" name="send" value="send" /><input class="bottom" type="submit" value="{$lng['module']['mailman']['list_del_archives_submit']}" /></td>
		</tr>
    	</table>
    </form>
$footer
