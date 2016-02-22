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
 * @author Santiago Ramos <sramos@semillasl.com>
 * @copyright (C) Martin Burchert
 * @copyright (C) Martin Juergens
 * @copyright (C) Santiago Ramos
 * @package Modules
 * @version v1.0.9rc1

 */

  define('AREA'                , 'customer');
  define('TABLE_MODULE_MAILMAN', 'modules_mailman');
  require("./lib/init.php");

  /* Evil Hack as, mailman settings are missing */

  $result = Database::query("SELECT `settingid`, `settinggroup`, `varname`, `value` FROM `" . TABLE_PANEL_SETTINGS . "` where settinggroup='mailman'");

  while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['settinggroup']][$row['varname']] = $row['value'];
  }

  /**
   * Search for an provided id
   */
  if(isset($_POST['id'])) {
    $id=intval($_POST['id']);
  } elseif(isset($_GET['id'])) {
    $id=intval($_GET['id']);
  }

 if($page=='overview') {
   $log->logAction(USR_ACTION, LOG_NOTICE, "viewed customer_mailman");
   eval("echo \"".getTemplate("modules/mailman/lists")."\";");
  } elseif($page == 'lists' ) {
    /**
     * Display List of Mailmanlists
     */
    if ($action == '') {
      $mailmanlists = '';
      $query = 'SELECT * FROM `'.TABLE_MODULE_MAILMAN.'` WHERE `customerid` = :customerid AND `type` = "list"';
      $result = Database::prepare($query);
      Database::pexecute($result, array("customerid" => $userinfo['customerid']));
      while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        eval("\$mailmanlists.=\"".getTemplate("modules/mailman/lists_list_row")."\";");
      }

      eval("echo \"".getTemplate("modules/mailman/lists_list")."\";");
    /**         
     * Create New List
     */           
    } elseif ($action == 'add' ) {
      $query = 'SELECT `list_name` FROM `'.TABLE_MODULE_MAILMAN.'` WHERE `list_name`=:listname AND `type`="system" ';
      $stmt = Database::prepare($query);
      $list_exists = Database::pexecute_first($stmt, array("listname" => $_POST['list_name']));

      /*
       *      Only if there is a new list in the database and all params are sent
       */
      if (isset($_POST['send']) && $_POST['send'] == 'send' && !$list_exists && !empty($_POST['list_name']) &&
		($settings['mailman']['system_mailman_allow_empty_subdomain'] == "TRUE" || !empty($_POST['list_subhostname'])) && !empty($_POST['list_password'])) {
        $customerid = $userinfo['customerid'];
        $domainid = $_POST['domainid'];
        $list_name_domainid = $_POST['list_name_domainid'];
        $query_list_name = Database::prepare('SELECT `domain` FROM `'.TABLE_PANEL_DOMAINS.'` WHERE `id` = :list_domainid');
        $list_name = Database::pexecute_first($query_list_name, array("list_domainid" => $list_name_domainid));
        if (empty($_POST['list_subhostname'])) {
          $list_mailhost = $list_name[domain];
          $list_name = $_POST['list_name']."@".$list_name[domain];
        } else {
          $list_subhostname = $_POST['list_subhostname'];
          $list_mailhost = $list_subhostname.".".$list_name[domain];
          $list_name = $_POST['list_name']."@".$list_subhostname.".".$list_name[domain];
        }
        $query_list_owner = Database::prepare('SELECT `email_full` FROM `'.TABLE_MAIL_VIRTUAL.'` WHERE `id` = :list_owner');
        $list_owner = Database::pexecute_first($query_list_owner, array("list_owner" => $_POST['list_owner']));
        $list_owner = $list_owner['email_full'];
        $list_webhost_domainid = $_POST['list_webhost_domainid'];
        $query_list_webhost = Database::prepare('SELECT `list_mailhost` FROM `'.TABLE_MODULE_MAILMAN.'` WHERE `id` = :webhost_domainid');
        $list_webhost = Database::pexecute_first($query_list_webhost, array("webhost_domainid" => $list_webhost_domainid));
        $list_webhost = $list_webhost[list_mailhost];
        $list_password = $_POST['list_password'];

        if ( $_POST['list_webhost_domainid'] == -1) {
          $list_webhost = $list_mailhost;
        }

        $type = "list";
        $log->logAction(USR_ACTION, LOG_WARNING, "creating mailman list: ".$list_name);
        $result = Database::prepare('INSERT INTO `'.TABLE_MODULE_MAILMAN.'` SET
		`type` = :type,
		`customerid` = :customerid,
		`domainid` = :domainid,
		`list_name` = :list_name,
		`list_webhost` = :list_webhost,
		`list_owner` = :list_owner,
		`list_mailhost` = :list_mailhost,
		`list_password` = :list_password,
		`transport` = "mailman:" ');
        Database::pexecute($result, array( "type" => $type, "customerid" => $customerid, "domainid" => $list_name_domainid,
		"list_name" => $list_name, "list_webhost" => $list_webhost, "list_owner" => $list_owner, "list_mailhost" => $list_mailhost,
		"list_password" => $list_password));
        unset($result);


        $listid_query = Database::prepare('SELECT `id` FROM `'.TABLE_MODULE_MAILMAN.'` WHERE `type`="list" AND `list_name` = :list_name ');
        $listid = Database::pexecute_first($listid_query,array("list_name" => $list_name));
        $update_query = Database::prepare('UPDATE `'.TABLE_MODULE_MAILMAN.'` SET `listid` = :list_id WHERE `type`= "list" AND `id` = :list_id');
        Database::pexecute($update_query, array("list_id" => $listid[id]));

        $type = "new";
        $result = Database::prepare('INSERT INTO `'.TABLE_MODULE_MAILMAN.'` SET
		`type` = :type,
		`customerid` = :customerid,
		`domainid` = :domainid,
		`listid` = :listid,
		`list_name` = :list_name,
		`list_webhost` = :list_webhost,
		`list_owner` = :list_owner,
		`list_mailhost` = :list_mailhost,
		`list_password` = :list_password,
		`transport` = "mailman:" ');
        Database::pexecute($result, array( "type" => $type, "customerid" => $customerid, "domainid" => $list_name_domainid, "listid" => $listid[id],
		"list_name" => $list_name, "list_webhost" => $list_webhost, "list_owner" => $list_owner, "list_mailhost" => $list_mailhost,
		"list_password" => $list_password));
        unset($result);

        /*
         *	Create the domain in PANEL_DOMAINS, first checking whether the domain is already in the database
         */

        $query_listid = Database::prepare('SELECT `id` FROM `'.TABLE_PANEL_DOMAINS.'` WHERE `domain` = :domain');
        $listid = Database::pexecute_first($query_listid, array("domain" => $list_webhost));
        $query_ipandport = Database::prepare('SELECT `ipandport` FROM '.TABLE_PANEL_DOMAINS.' WHERE `id` = :domainid');
        $ipandport = Database::pexecute_first($query_ipandport, array("domainid" => $list_name_domainid));
        $ipandport = $ipandport['ipandport'];

        if(! $listid){
          $list_name = substr( $list_name,0, stripos($list_name, '@'));

          $specialsettings = ' ScriptAlias /cgi-bin/mailman/ '.$settings['mailman']['system_mailman_cgi-bin_path'].'\n'.
                'Alias /pipermail/ '.$settings['mailman']['system_mailman_data_path'].'/archives/public/\n'.
                'Alias /images/mailman/ '.$settings['mailman']['system_mailman_images_path'].'\n'.
                '<Directory '.$settings['mailman']['system_mailman_cgi-bin_path'].'>\n'.
                '   AllowOverride None\n'.
                '   Options ExecCGI\n'.
                '   AddHandler cgi-script .cgi\n'.
                '   Order allow,deny\n'.
                '   Allow from all\n'.
                '</Directory>\n'.
                '<Directory '.$settings['mailman']['system_mailman_data_path'].'/archives/public/>\n'.
                '   Options Indexes FollowSymlinks\n'.
                '   AllowOverride None\n'.
                '   Order allow,deny\n'.
                '   Allow from all\n'.
                '</Directory>\n'.
                '<Directory '.$settings['mailman']['system_mailman_images_path'].'/>\n'.
                '   AllowOverride None\n'.
                '   Order allow,deny\n'.
                '   Allow from all\n'.
                '</Directory>\n'.
                'RedirectMatch ^/$ /cgi-bin/mailman/listinfo/'.$list_name.'\n'.
                '<Directory '.$settings['mailman']['system_mailman_data_path'].'/archives/>\n'.
                '    Options Indexes FollowSymLinks\n'.
                '    AllowOverride None\n'.
                '</Directory>\n';
          $result = Database::prepare('INSERT INTO `' . TABLE_PANEL_DOMAINS . '` SET
		`customerid` = :customerid,
		`domain` = :domain,
		`documentroot` = "/var/www/",
		`ipandport` = :ipandport,
		`isbinddomain` = "1",
		`caneditdomain` = "0",
		`parentdomainid` = :parentdomainid,
		`isemaildomain` = "0",
		`openbasedir` = "1",
		`openbasedir_path` = "0",
		`speciallogfile` = "0",
		`specialsettings` = :specialsettings ');
          Database::pexecute($result, array(
		"customerid" => $userinfo['customerid'],
		"domain" => $list_webhost,
		"ipandport" => $ipandport,
		"parentdomainid" => (int)$domain_check['id'],
		"specialsettings" => $specialsettings ));
        }

        /*
         *	Let's set the cronjobs
        */

        inserttask(1);
        inserttask(4);

        header("Location: $filename?page=$page&s=$s");

      /*
       *      New list form 
       */
      } else {
        if(list_exists) {
        }
        if(!empty($_POST['list_name'])){
          $list_name=$_POST['list_name'];
        }
        if(!empty($_POST['list_subhostname'])){
          $list_subhostname=$_POST['list_subhostname'];
        }
        if(!empty($_POST['list_password'])){
          $list_password=$_POST['list_password'];
        }

        $result = Database::prepare('SELECT `id`, `domain`, `customerid` FROM `'.TABLE_PANEL_DOMAINS.'` 
		WHERE `customerid` = :customerid AND `isemaildomain`="1" ORDER BY `domain` ASC');
        Database::pexecute($result, array("customerid" => $userinfo['customerid']));
        $domains='';
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
          if ( stripos($domains, $row['domain'])) {
            continue;
          }
          if($row['domain'] == ""){
            continue;
          }

          $domains.=makeoption($row['domain'],$row['id']);
        }

        $result = Database::prepare('SELECT `id`, `email_full` FROM `'.TABLE_MAIL_VIRTUAL.'` 
		WHERE `customerid` = :customerid ORDER BY `id` ASC');
        Database::pexecute($result, array("customerid" => $userinfo['customerid']));
        $valid_emails.='';
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
          $valid_emails.=makeoption($row['email_full'],$row['id']);
        }

        $result = Database::prepare('SELECT `id`, `domainid`, `customerid`, `list_name` FROM `'.TABLE_MODULE_MAILMAN.'`	
		WHERE `customerid` = :customerid AND `type`="list" ORDER BY `id` ASC');
        Database::pexecute($result, array("customerid" => $userinfo['customerid']));
        $mailman_domains.=makeoption("{$lng['module']['mailman']['using_mailhost']}",-1);
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
          $list_webhost= substr(strstr($row['list_name'], '@'),1);
          $mailman_domains.=makeoption($list_webhost,$row['id']);
        }
        if(empty($list_subhostname)) {
          #$list_subhostname = "listas";
          $list_subhostname = "";
        }
        eval("echo \"".getTemplate("modules/mailman/lists_add")."\";");
      }

    /**
     * Edit List
     */
    } elseif ($action=="edit") {
      /**
      * Update existing list 
      */
      if (isset($_POST['send']) && $_POST['send'] == 'send') {
        // edd an edited a job
        $type = "update";
        $customerid = $userinfo['customerid'];
        $domainid = $_POST['domainid'];
        $listid = $_POST['listid'];
        $list_subhostname = $_POST['list_subhostname'];
        $list_name_domainid = $_POST['list_name_domainid'];
        $list_name = $db->query_first("SELECT `domain` FROM `".TABLE_PANEL_DOMAINS."` WHERE `id`='".$list_name_domainid."'");
        $list_mailhost = $_POST['list_mailhost'];
        $list_name = $_POST['list_name'];
        $list_mailhost = substr($list_name,strpos($list_name, '@')+1);
        $list_owner_domainid = $_POST['list_owner_domainid'];
        $list_owner =  $db->query_first("SELECT `email_full` FROM `".TABLE_MAIL_VIRTUAL."` WHERE `id`='".$_POST['list_owner']."'");
        $list_owner = $list_owner['email_full'];
        $list_webhost_domainid = $_POST['list_webhost_domainid'];
        $list_webhost =  $db->query_first("SELECT `list_webhost` FROM `".TABLE_MODULE_MAILMAN."` WHERE `id`='".$list_webhost_domainid."'");
        $list_webhost = $list_webhost[list_webhost];
        $list_password = $_POST['list_password'];

        $log->logAction(USR_ACTION, LOG_WARNING, "creating mailman list: ".$list_name);

        $new_query = Database::prepare('INSERT INTO `'.TABLE_MODULE_MAILMAN.'` SET
		`type` = :type,
		`customerid` = :customerid,
		`domainid` = :domainid,
		`listid` = :listid,
		`list_name` = :listname,
		`list_webhost` = :list_webhost,
		`list_owner` = :list_owner,
		`list_mailhost` = :list_mailhost,
		`list_password` = :list_password,
		`transport` = "mailman:" ');
        Database::pexecute($new_query, array( "type" => $type, "customerid" => $customerid, "domainid" => $domainid, "listid" => $listid,
		"list_name" => $list_name, "list_webhost" => $list_webhost, "list_owner" => $list_owner, "list_mailhost" => $list_mailhost,
		"list_password" => $list_password));

        
        /*
         *      Let's set the cronjobs
        */
        inserttask(1);
        inserttask(4);

        redirectTo($filename , Array ( 'page' => $page, 's' => $s ));

      /**
      * Edit list form
      */
      } else {
        $result = Database::prepare('SELECT `id`, `email_full` FROM `'.TABLE_MAIL_VIRTUAL.'` WHERE `customerid` = :customerid ORDER BY `id` ASC');
        Database::pexecute($result, array("customerid" => $userinfo['customerid']));
        $valid_emails.='';
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
          $valid_emails.=makeoption($row['email_full'],$row['id']);
        }

        $result = Database::prepare('SELECT * FROM `'.TABLE_MODULE_MAILMAN.'` WHERE `id` = :id');
        $backup_row = Database::pexecute_first($result, array("id" => $id));
        $customerid      = $backup_row['customerid'];
        $domainid      = $backup_row['domainid'];
        $list_name       = $backup_row['list_name'];
        $list_webhost    = $backup_row['list_webhost'];
        $list_owner       = $backup_row['list_owner'];
        $list_owner = substr($backup_row['list_owner'], 0,strpos($backup_row['list_owner'], "@"));
        $list_mailhost       = $backup_row['list_mailhost'];
        $list_password       = $backup_row['list_password'];
        $listid = $backup_row['id'];

        $result = Database::prepare('SELECT `id`, `domain`, `customerid` FROM `'.TABLE_PANEL_DOMAINS.'` WHERE `customerid` = :customerid ORDER BY `domain` ASC');
        Database::pexecute($result, array("customerid" => $userinfo['customerid']));
        $domains = '';
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
          if ( stripos($domains, $row['domain'])) {
            continue;
          }
          if($row['domain'] == "") {
            continue;
          }
          if ($row['id'] == $backup_row['domainid']) {
            $domains .= makeoption($row['domain'],$row['id'],$backup_row['domainid']);
          } else {
            $domains .= makeoption($row['domain'],$row['id']);
          }
        }
        $result = Database::prepare('SELECT `id`, `domainid`, `customerid`, `list_name` FROM `'.TABLE_MODULE_MAILMAN.'` 
		WHERE `customerid` = :customerid AND `type`="list" ORDER BY `id` ASC');
        Database::pexecute($result, array("customerid" => $userinfo['customerid']));
        $mailman_domains='';
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
          $list_webhost= substr(strstr($row['list_name'], '@'),1);
          if ( stripos($mailman_domains, $list_webhost)){
            continue;
          }
          $mailman_domains.=makeoption($list_webhost,$row['id']);
        }
        eval("echo \"".getTemplate("modules/mailman/lists_edit")."\";");
      }

    /**
     * Delete List
     */
    } elseif ($action == "delete"  && ($id != 0) ) {
      echo WOOHOO;
      if(isset($_POST['send']) && $_POST['send']=='send' && isset($_POST['delete_archive'])) {
        $query = Database::prepare('SELECT `domainid`,`listid`,`list_name` FROM `'.TABLE_MODULE_MAILMAN.'` WHERE `id` = :id');
        $listid = Database::pexecute_first($query, array("id" => $id));	
        $log->logAction(USR_ACTION, LOG_WARNING, "deleting mailman list: ".$listid[list_name]);
        $result = Database::prepare('UPDATE `'.TABLE_MODULE_MAILMAN.'` SET `type` = "delete" WHERE `listid` = :listid AND `type`= "list"');
        Database::pexecute($result, array("listid" => $listid[listid])); 
        redirectTo($filename , Array( 'page' => $page, 's' => $s ));
      } else {
        /**
         * ask if really delete
         */
        $params = array( 'page' => $page, 'action' => $action, 'id' => $id );
        if ( isset($_POST['reallydoit']) ) {
          $params['reallydoit'] = 'reallydoit';
        }
        ask_yesno($lng['module']['mailman']['question']['reallydelete'], $filename, $params);
      }

    /**
     * Delete Archives List
     */
    } elseif($action == "del_archiv"  && ($id != 0) ) {
      if(isset($_POST['send']) && $_POST['send']=='send') {
        if(isset($_POST['submitbutton'])) {
          $query = Database::prepare('SELECT `domainid`,`listid`,`list_name`  FROM `'.TABLE_MODULE_MAILMAN.'` WHERE `id` = :id');
          $listid = Database::pexecute_first($query, array("id" => $id)); 
          $log->logAction(USR_ACTION, LOG_WARNING, "deleting mailman list archives: ".$listid[list_name]);
          $domainname = substr(strstr($listid['list_name'], '@'), 1);
          $query = 'UPDATE `'.TABLE_MODULE_MAILMAN.'` SET `type` = "delete" WHERE `type`= "list" AND `listid` = :listid';
          $result = Database::prepare($query);
          Database::pexecute($result, array("listid" => $listid[listid]));
          # Tenemos que ver como se hace esto para eliminar ese domainname
          $query_listid = 'SELECT * FROM `'.TABLE_MODULE_MAILMAN.'` WHERE `list_name` LIKE "%'.$domainname.'" AND `list_name` != :list_name AND `type` != "delete"';
          $result_listid = Database::prepare($query_listid);
          #$listid = Database::pexecute_first($result_listid, array("domain_name" => $domainname, "list_name" => $listid['list_name']));
          $listid = Database::pexecute_first($result_listid, array("list_name" => $listid['list_name']));

          if(strtolower($_POST['delete_archive_checkbox']) == "on") {
            $query_id = Database::prepare('SELECT * FROM `'.TABLE_MODULE_MAILMAN.'` WHERE `id` = :id');
            $row = Database::pexecute_first($query_id, array("id" => $_POST['id'])); 

            $type="del_archive";
            $query = Database::prepare('INSERT INTO `'.TABLE_MODULE_MAILMAN.'` SET
		`type` = :type,
		`customerid` = :customerid,
		`domainid` = :domainid,
		`listid` = :listid,
		`list_name` = :list_name,
		`list_webhost` = :list_webhost,
		`list_owner` = :list_owner,
		`list_mailhost` = :list_mailhost,
		`list_password` = :list_password,
		`transport` = "mailman:" ');
            Database::pexecute($query, array("type" => $type, "customerid" => $row['customerid'], "domainid" => $row['domainid'], "listid" => $row['listid'],
		"list_name" => $row['list_name'], "list_webhost" => $row['list_webhost'], "list_owner" => $row['list_owner'], "list_mailhost" => $row['list_mailhost'],
		"list_password" => $row['list_password']));
          }
          redirectTo($filename , Array( 'page' => $page, 's' => $s ));
        } else {
          $params = array( 'page' => $page, 'action' => $action, 'id' => $id, 'delete_archive_checkbox'=> $_POST['delete_archive_checkbox'] );
          if ( isset($_POST['reallydoit']) ) {
            $params['reallydoit'] = 'reallydoit';
          }
          ask_yesno($lng['module']['mailman']['question']['reallydelete'], $filename, $params);
        }
      } else {
        eval("echo \"".getTemplate("modules/mailman/lists_del")."\";");
      }
    }
  }
?>
