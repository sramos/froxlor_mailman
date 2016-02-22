$header

        <table cellpadding="5" cellspacing="0" border="0" align="center" class="maintable">
                <tr>
                        <td colspan="5" class="maintitle"><b><img src="images/modules/mailman/mm-icon.png" alt="" />&nbsp;{$lng['module']['mailman']['existing_lists']}</b></td>
                </tr>
                <tr>
                        <td class="field_display_border_left">{$lng['module']['mailman']['list_name']}@{$lng['module']['mailman']['list_mailhost']}
                        <td class="field_display">{$lng['module']['mailman']['list_owner']}</td>
                        <td class="field_display">{$lng['module']['mailman']['list_webhost']}</td>
                        <td class="field_display" colspan="2">&nbsp;</td>
                </tr>
$mailmanlists
                <tr>
                        <td colspan="5" class="field_display_border_left"><a href="$filename?page=lists&amp;action=add&amp;s=$s">{$lng['module']['mailman']['list_add']}</a></td>
                </tr>
        </table>

$footer

