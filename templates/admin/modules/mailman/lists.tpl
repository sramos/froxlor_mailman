$header

        <form method="post" action="$filename">
                <input type="hidden" name="s" value="$s">
                <input type="hidden" name="page" value="$page">
                <input type="hidden" name="action" value="$action">
                <table cellpadding="5" cellspacing="4" border="0" align="center" class="maintable">
                        <tr>
                                <td class="maintitle" colspan="5"><b><img src="images/modules/mailman/mm-icon.png" alt="" />&nbsp;{$lng['module']['mailman']['admin']['existing_lists_syscp']}</b></td>
                        </tr>
        	        <tr>
        	                <td class="maintitle" >{$lng['login']['username']}
        	                <td class="maintitle">{$lng['module']['mailman']['list_owner']}</td>
        	                <td class="maintitle">{$lng['module']['mailman']['list_webhost']}</td>
        	                <td class="maintitle">{$lng['module']['mailman']['list_mailhost']}</td>
        	        </tr>

		$syscp_lists
		</table>
                <br />
                <table cellpadding="5" cellspacing="4" border="0" align="center" class="maintable">
                        <tr>
                                <td class="maintitle" colspan="5"><b><img src="images/modules/mailman/mm-icon.png" alt="" />&nbsp;{$lng['module']['mailman']['admin']['existing_lists_system']}</b></td>
                        </tr>
	                $system_lists
                </table>
                <br />
                <table cellpadding="5" cellspacing="4" border="0" align="center" class="maintable">
                        <tr>
                                <td class="maintitle" colspan="2"><b><img src="images/modules/mailman/mm-icon.png" alt="" />&nbsp;{$lng['admin']['mailman']}</b></td>
                        </tr>
                        <tr>
                                <td class="main_field_name">{$lng['module']['mailman']['admin']['newlist_path']}</td>
                                <td class="main_field_display" nowrap="nowrap"><input type="text" name="system_newlist_path" value="{$command['system_newlist_path']}"  size=30/></td>
                        </tr>
                        <tr>
                                <td class="main_field_name">{$lng['module']['mailman']['admin']['rmlist_path']}</td>
                                <td class="main_field_display" nowrap="nowrap"><input type="text" name="system_rmlist_path" value="{$command['system_rmlist_path']}"  size=30/></td>
                        </tr>
                        <tr>
                                <td class="main_field_name">{$lng['module']['mailman']['admin']['config_list_path']}</td>
                                <td class="main_field_display" nowrap="nowrap"><input type="text" name="system_config_list_path" value="{$command['system_config_list_path']}"  size=30/></td>
                        </tr>
                        <tr>
                                <td class="main_field_name">{$lng['module']['mailman']['admin']['list_lists_path']}</td>
                                <td class="main_field_display" nowrap="nowrap"><input type="text" name="system_list_lists_path" value="{$command['system_list_lists_path']}"  size=30/></td>
                        </tr>
                        <tr>
                                <td class="main_field_name">{$lng['module']['mailman']['admin']['genaliases_path']}</td>
                                <td class="main_field_display" nowrap="nowrap"><input type="text" name="system_genaliases_path" value="{$command['system_genaliases_path']}"  size=30/></td>
                        </tr>
                        <tr>
                                <td class="main_field_name">{$lng['module']['mailman']['admin']['newaliases_path']}</td>
                                <td class="main_field_display" nowrap="nowrap"><input type="text" name="system_newaliases_path" value="{$command['system_newaliases_path']}"  size=30/></td>
                        </tr>
                        <tr>
                                <td class="main_field_name">{$lng['module']['mailman']['admin']['change_pw_path']}</td>
                                <td class="main_field_display" nowrap="nowrap"><input type="text" name="system_change_pw_path" value="{$command['system_change_pw_path']}"  size=30/></td>
                        </tr>
                        <tr>
                                <td class="main_field_name">{$lng['module']['mailman']['admin']['withlist_path']}</td>
                                <td class="main_field_display" nowrap="nowrap"><input type="text" name="system_withlist_path" value="{$command['system_withlist_path']}"  size=30/></td>
                        </tr>        
                        <tr>
                                <td class="main_field_name">{$lng['module']['mailman']['admin']['mailman_data_path']}</td>
                                <td class="main_field_display" nowrap="nowrap"><input type="text" name="system_mailman_data_path" value="{$command['system_mailman_data_path']}"  size=30/></td>
                        </tr>
                        <tr>
                                <td class="main_field_name">{$lng['module']['mailman']['admin']['cgi-bin_path']}</td>
                                <td class="main_field_display" nowrap="nowrap"><input type="text" name="system_mailman_cgi-bin_path" value="{$command['system_mailman_cgi-bin_path']}"  size=30/></td>
                        </tr>
                        <tr>
                                <td class="main_field_name">{$lng['module']['mailman']['admin']['images_path']}</td>
                                <td class="main_field_display" nowrap="nowrap"><input type="text" name="system_mailman_images_path" value="{$command['system_mailman_images_path']}" size=30/></td>
                        </tr>
                        <tr>
                                <td class="main_field_name">{$lng['module']['mailman']['admin']['multidomain_patch']}</td>
                                <td class="main_field_display" nowrap="nowrap"><input type="text" name="system_mailman_multidomain_patch" value="{$command['system_mailman_multidomain_patch']}" size=30/></td>
                        </tr>
                        <tr>
                                <td class="main_field_name">{$lng['module']['mailman']['admin']['allow_empty_subdomain']}</td>
                                <td class="main_field_display" nowrap="nowrap"><input type="text" name="system_mailman_allow_empty_subdomain" value="{$command['system_mailman_allow_empty_subdomain']}" size=30/></td>
                        </tr>
                        <tr>
                                <td class="main_field_confirm" colspan="2"><input type="hidden" name="send" value="send"><input class="bottom" type="submit" value="{$lng['panel']['save']}" /></td>
                        </tr>
                </table>
                <br />


        </form>


$footer
 
