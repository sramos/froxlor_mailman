<?php

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. This program is distributed in the
 * hope that it will be useful, but WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * @author Martin Burchert <eremit@adm1n.de>
 * @author Martin Juergens <martin@gamesplace.info>
 * @author Santiago Ramos <sramos@sitiodistinto.net>
 * @copyright (C) Martin Burchert
 * @copyright (C) Martin Juergens
 * @copyright (C) Santiago Ramos
 * @package Modules
 * @version v1.0.9rc1

 */

  define('AREA', 'admin');
  define('TABLE_MODULE_MAILMAN', 'modules_mailman');
  define('TABLE_PANEL_CUSTOMER', 'panel_customers');

  /**
  * Include our init.php, which manages Sessions, Language etc.
  */
  require("./lib/init.php");

  /* Evil Hack as, mailman settings are missing */

  $result = Database::query("SELECT `settingid`, `settinggroup`, `varname`, `value` FROM `" . TABLE_PANEL_SETTINGS . "` where settinggroup='mailman'");

  $log->logAction(ADM_ACTION, LOG_WARNING, "viewing mailman config");

  while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['settinggroup']][$row['varname']] = $row['value'];
  }


  $needed_commands =array("newlist","rmlist","config_list","list_lists","genaliases","newaliases","change_pw","withlist","cgi-bin","images","mailman_data");
  $suggest_commands=array("/usr/lib/mailman/bin/newlist","/usr/lib/mailman/bin/rmlist","/usr/lib/mailman/bin/config_list","/usr/lib/mailman/bin/list_lists","/usr/lib/mailman/bin/genaliases","/usr/bin/newaliases","/usr/lib/mailman/bin/change_pw","/usr/lib/mailman/bin/withlist","/usr/lib/mailman/cgi-bin/","/usr/lib/mailman/icons/","/var/lib/mailman/","TRUE", "TRUE");
  $mysql_commands=array("system_newlist_path","system_rmlist_path","system_config_list_path","system_list_lists_path","system_genaliases_path","system_newaliases_path","system_change_pw_path","system_withlist_path","system_mailman_cgi-bin_path","system_mailman_images_path","system_mailman_data_path","system_mailman_multidomain_patch","system_mailman_allow_empty_subdomain");


  if ( $page == 'lists' ){
    if (isset($_POST['send']) 
	&& !empty($_POST['system_newlist_path']) 
	&& !empty($_POST['system_rmlist_path']) 
	&& !empty($_POST['system_config_list_path']) 
	&& !empty($_POST['system_list_lists_path']) 
	&& !empty($_POST['system_genaliases_path']) 
	&& !empty($_POST['system_newaliases_path'])  
	&& !empty($_POST['system_change_pw_path']) 
	&& !empty($_POST['system_withlist_path'])
	&& !empty($_POST['system_mailman_cgi-bin_path'])
	&& !empty($_POST['system_mailman_images_path'])
	&& !empty($_POST['system_mailman_data_path'])) {

      $_POST['system_mailman_multidomain_patch'] = strtoupper($_POST['system_mailman_multidomain_patch']);
      if (!empty($_POST['system_mailman_multidomain_patch']) && ( $_POST['system_mailman_multidomain_patch'] == "TRUE" || $_POST['system_mailman_multidomain_patch'] == "YES" || $_POST['system_mailman_multidomain_patch'] == "SI" || $_POST['system_mailman_multidomain_patch'] == "SÍ") ) {
        $_POST['system_mailman_multidomain_patch'] = "TRUE";
      } else {
        $_POST['system_mailman_multidomain_patch'] = "FALSE";
      }

      $_POST['system_mailman_allow_empty_subdomain'] = strtoupper($_POST['system_mailman_allow_empty_subdomain']);
      if (!empty($_POST['system_mailman_allow_empty_subdomain']) && ( $_POST['system_mailman_allow_empty_subdomain'] == "TRUE" || $_POST['system_mailman_allow_empty_subdomain'] == "YES" || $_POST['system_mailman_allow_empty_subdomain'] == "SI" || $_POST['system_mailman_allow_empty_subdomain'] == "SÍ") ) {
        $_POST['system_mailman_allow_empty_subdomain'] = "TRUE";
      } else {
        $_POST['system_mailman_allow_empty_subdomain'] = "FALSE";
      }

      foreach($mysql_commands as $value) {
        $query = 'SELECT `varname`, `value` FROM `'.TABLE_PANEL_SETTINGS.'` WHERE `settinggroup` = "mailman" AND `varname`=:value';
        $stmt = Database::prepare($query);
        $result = Database::pexecute_first($stmt, array("value" => $value));
        if($result && (!empty($result['value']) || isset($result['value']))) {
          $fillin_value = $_POST[$value];
          $log->logAction(ADM_ACTION, LOG_WARNING, "Guardamos en " . $value . " el valor " . $fillin_value);
          $stmt2 = Database::prepare('UPDATE `'. TABLE_PANEL_SETTINGS .'` SET `value` = :fillin_value
		WHERE `settinggroup` = "mailman" AND `varname` = :value');
          Database::pexecute($stmt2, array("fillin_value" => $fillin_value, "value" => $value));
        } else {
          $stmt2 = Database::prepare('INSERT INTO `'. TABLE_PANEL_SETTINGS .'` SET
		`settinggroup` = "mailman",
		`varname` = :varname,
		`value` = :value' );
          Database::pexecute($stmt2, array("varname" => $value, "value" => $_POST[$value]));	
        }
      }
      header("Location: $filename?page=$page&s=$s");

    } else {
      $result = Database::query('SELECT `varname`, `value` FROM `'.TABLE_PANEL_SETTINGS.'` WHERE `settinggroup` = "mailman"');
      while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        if(isset($row['value']) && isset($row['varname']) && $row['value']!="" && $row['varname']!="") {
          $value=$row['value'];
          $varname=$row['varname'];
          $command[$varname]=$value;
          unset($value);
          unset($varname);
        }
      }
      $counter=0;
      foreach($mysql_commands as $key => $value) {
        if(empty($command[$value])){
          $stmt = Database::prepare('INSERT INTO `'. TABLE_PANEL_SETTINGS .'` SET
		`settinggroup` = "mailman",
		`varname` = :varname,
		`value` = :value' );
          Database::pexecute($stmt, array("varname" => $mysql_commands[$key], "value" => $suggest_commands[$key]));
          $command[$value]=$suggest_commands[$key];
        }
        $counter++;
      }
      unset($counter);

      $syscp_lists = '';
      $result = Database::query('SELECT * FROM `'.TABLE_MODULE_MAILMAN.'` WHERE `type` = "list"');
      while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $owner_query = Database::prepare('SELECT `loginname` FROM '.TABLE_PANEL_CUSTOMER.' WHERE `customerid` = :customer_id ');
        $owner_user = Database::pexecute_first($owner_query, array("customer_id" => (int)$row['customerid']));
        $owner_user = $owner_user['loginname'];
        eval("\$syscp_lists.=\"".getTemplate("modules/mailman/lists_list_row")."\";");
      }

      $system_lists = '';
      $result = Database::query('SELECT * FROM `'.TABLE_MODULE_MAILMAN.'` WHERE `type` = "system"');
      while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        eval("\$system_lists.=\"".getTemplate("modules/mailman/lists_list_system")."\";");
      }

      eval("echo \"".getTemplate("modules/mailman/lists")."\";");
    }
  }

?>

