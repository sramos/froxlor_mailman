$header
	<form method="post" action="$filename">
		<input type="hidden" name="s" value="$s">
		<input type="hidden" name="page" value="$page">
		<input type="hidden" name="action" value="$action">
		<input type="hidden" name="id" value="$id">
                <input type="hidden" name="domainid" value="$domainid">
		<table cellpadding="2" cellspacing="2" border="0" align="center" class="maintable_80">
	    	<tr>
	    		<td colspan="2" class="maintitle"><b><img src="images/modules/mailman/mm-icon.png" alt="" />&nbsp;{$lng['module']['mailman']['list_add']}</b></td>
	    	</tr>
		<if ($list_exists!='')><tr>
                        <td colspan="2" class="main_field_name"><b>{$lng['module']['mailman']['list_exists']}</b></td>
                </tr></if>
		<tr>
			<td class="main_field_name"><b>{$lng['module']['mailman']['list_name']}</b></td>
			<td class="main_field_display"><input type="text" name="list_name" value="{$list_name}" size=10>
                        @<input type="text" name="list_subhostname" value="{$list_subhostname}" size=10>.<select name="list_name_domainid">$domains</select></td>
		</tr>
		<tr>
			<td class="main_field_name">{$lng['module']['mailman']['list_owner']}</td>
			<td class="main_field_display"><select name="list_owner">$valid_emails</select></td>
		</tr>
                <tr>
                        <td class="main_field_name">{$lng['module']['mailman']['list_webhost']}</td>
                        <td class="main_field_display"><if $mailman_domains != ''><select name="list_webhost_domainid">$mailman_domains</select><else>{$lng['module']['mailman']['using_mailhost']}</if>
                </tr>
		<tr>
			<td class="main_field_name">{$lng['module']['mailman']['list_password']}</td>
			<td class="main_field_display"><input type="password" name="list_password" value="{$list_password}"></td>
		</tr>
		<tr>
			<td class="main_field_confirm" colspan="2"><input type="hidden" name="send" value="send" /><input class="bottom" type="submit" value="{$lng['module']['mailman']['list_add_submit']}" /></td>
		</tr>
    	</table>
    </form>
$footer
