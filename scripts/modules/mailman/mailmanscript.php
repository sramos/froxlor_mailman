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

  $DEBUG=TRUE;

  $aliases_file = "/var/lib/mailman/data/aliases";

  if ($DEBUG) {
    $log = fopen("/tmp/froxlor_mailman_cron.log", 'a');
  }

  define('MASTER_CRONJOB', 1);
  include dirname(__FILE__) . '/../../../lib/cron_init.php';

  if(@php_sapi_name() != 'cli' && @php_sapi_name() != 'cgi') {
    die('This script will only work in the shell.');
  }

  $settings = array(
    'mailman' => array(
      'system_mailman_multidomain_patch' => "TRUE",
      'system_list_lists_path' => '/usr/lib/mailman/bin/list_lists',
      'system_config_list_path' => '/usr/lib/mailman/bin/config_list',
      'system_newlist_path' => '/usr/lib/mailman/bin/newlist',
      'system_change_pw_path' => '/usr/lib/mailman/bin/change_pw',
      'system_withlist_path' => '/usr/lib/mailman/bin/withlist',
      'system_genaliases_path' => '/usr/lib/mailman/bin/genaliases',
      'system_newaliases_path' => '/usr/bin/newaliases',
      'system_rmlist_path' => '/usr/lib/mailman/bin/rmlist'
    )
  );

  define('TABLE_MODULE_MAILMAN','modules_mailman');

  /*
   *	Read in the existing lists from the system
  */

  $cmd = $settings['mailman']['system_list_lists_path'].' -b';
  $existing_lists = shell_exec("$cmd");
  strtolower($existing_lists);
  $existing_lists_array = explode("\n", $existing_lists);

  if ($DEBUG) {
    fwrite($log, "Entrando en cron de mailman...\n");
  }

  /*
   *	Create all new lists
  */

  /*
   *	Create the List in mailman
  */
  $result = Database::query("SELECT * FROM `".TABLE_MODULE_MAILMAN."` WHERE type = 'new'");
  $new_created=0;
  // foreach dataset
  while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $new_created=1;
    $id=$row['id'];

    # Hack para dominios virtuales (parche de mailman)
    if ($settings['mailman']['system_mailman_multidomain_patch'] == "TRUE") {
      $list_name = $row['list_name'];
    } else {
      $list_name = substr($row['list_name'],0,strpos($row['list_name'],"@"));
    }

    $list_mailhost = substr($row['list_name'],strpos($row['list_name'],"@")+1);
    $list_webhost = $row['list_webhost'];
    $list_owner = $row['list_owner'];
    $list_password = $row['list_password'];
    $cmd=$settings['mailman']['system_newlist_path']." -q --urlhost=".$list_webhost." --emailhost=".$list_mailhost." ".$list_name." ".$list_owner." ".$list_password; 
    if ($DEBUG) {
      fwrite($log, "    Ejecutando: " . $cmd . "\n");
    }
    $output=shell_exec("$cmd");
    $cmd = $settings['mailman']['system_withlist_path'].' -l -r fix_url '.$list_name.' -u '.$list_mailhost.' >/dev/null 2>&1';
    $output = shell_exec($cmd);
    #fwrite($log, "    Ejecutando: " . $cmd . "\n");
  }
  if(!is_null($new_created) && $new_created== "1"){
    if(!is_file($aliases_file)){
      touch($aliases_file);
    }
    $cmd=$settings['mailman']['system_genaliases_path']." -q";
    $output=shell_exec($cmd);
    $aliases_file_handler = fopen($aliases_file, 'w');
    fwrite($aliases_file_handler, $output);
    fclose($aliases_file_handler);
    $cmd=$settings['mailman']['system_newaliases_path'];
    $output=shell_exec($cmd);
    if ($DEBUG) {
      fwrite($log, "    Ejecutando: " . $cmd . "\n");
    }
  }
  unset($new_created);


  /*
   *      Reread in the existing lists from the system
  */
  $cmd = $settings['mailman']['system_list_lists_path'].' -b';
  $existing_lists = shell_exec("$cmd");
  strtolower($existing_lists);
  $existing_lists_array = explode("\n", $existing_lists);


  /*
   *	Delete the entry, the list is created
  */
  if(isset($id)) {
    $query = 'DELETE FROM `'.TABLE_MODULE_MAILMAN.'` WHERE `type`="new" AND `id` = :id';
    $result = Database::prepare($query);
    Database::pexecute($result, array("id" => $id));
    unset($result);
  }

  /*
   *      Updating an existing list
  */
  $result = Database::query("SELECT * FROM `".TABLE_MODULE_MAILMAN."` WHERE type = 'update'");
  // foreach dataset
  while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $id=$row['id'];

    # Hack para dominios virtuales (parche de mailman)
    if ($settings['mailman']['system_mailman_multidomain_patch'] == "TRUE") {
      $list_name = $row['list_name'];
    } else {
      $list_name = substr($row['list_name'],0,strpos($row['list_name'],"@"));
    }

    $list_mailhost = substr($row['list_name'],strpos($row['list_name'],"@")+1);
    $list_webhost = $row['list_webhost'];
    $list_owner = $row['list_owner'];
    $tmpfname = tempnam("/tmp", "syscp_mailman_");
    $handle = fopen($tmpfname, "w");
    fwrite($handle, "owner = ['".$list_owner."']\n".
        "host_name = '".$list_mailhost."'\n".
        "web_page_url = '".$list_webhost."'\n");
    $cmd = $settings['mailman']['system_config_list_path']."  -i ".$tmpfname." ".$list_name." >/dev/null 2>&1";
    if ($DEBUG) {
      fwrite($log, "    Ejecutando: " . $cmd . "\n");
    }
    $output=shell_exec($cmd);
    fclose($handle);
    unlink($tmpfname);
    $cmd = $settings['mailman']['system_change_pw_path']." -q  -l ".$list_name." -p ".$row['list_password']."  >/dev/null 2>&1";
    if ($DEBUG) {
      fwrite($log, "    Ejecutando: " . $cmd . "\n");
    }
    $output = shell_exec($cmd);
    $query2 = "DELETE FROM `".TABLE_MODULE_MAILMAN."` WHERE type = 'update' AND `id` = :id";
    $result2 = Database::prepare($query2);
    Database::pexecute($result2, array("id" => $id));
    $cmd = $settings['mailman']['system_withlist_path'].' -l -r fix_url '.$list_name.' -u '.$list_mailhost.' >/dev/null 2>&1';
    if ($DEBUG) {
      fwrite($log, "    Ejecutando: " . $cmd . "\n");
    }
    $output = shell_exec($cmd);
  }

  /*
   *	Delete the lists
  */
  $result = Database::query("SELECT * FROM `".TABLE_MODULE_MAILMAN."` WHERE type = 'delete'");
  // foreach dataset
  while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $id=$row['id'];

    # Hack para dominios virtuales (parche de mailman)
    if ($settings['mailman']['system_mailman_multidomain_patch'] == "TRUE") {
      #$list_name = $row['list_name'];
      $list_name = str_replace("@", "-", $row['list_name']);
    } else {
      $list_name = substr($row['list_name'],0,strpos($row['list_name'],"@"));
    }

    $list_mailhost = substr($row['list_name'],strpos($row['list_name'],"@")+1);
    $list_webhost = $row['list_webhost'];
    $cmd=$settings['mailman']['system_rmlist_path']." ".$list_name." ";
    if ($DEBUG) {
      fwrite($log, "    Ejecutando: " . $cmd . "\n");
    }
    $output=shell_exec("$cmd");
    $query2 = "DELETE FROM `".TABLE_MODULE_MAILMAN."` WHERE type = 'delete' AND `id` = :id";
    $result2 = Database::prepare($query2);
    Database::pexecute($result2, array("id" => $id));
    $cmd=$settings['mailman']['system_genaliases_path']." -q";
    if ($DEBUG) {
      fwrite($log, "    Ejecutando: " . $cmd . "\n");
    }
    $output=shell_exec($cmd);
    $aliases_file_handler = fopen($aliases_file, 'w');
    fwrite($aliases_file_handler, $output);
    fclose($aliases_file_handler);
    $cmd=$settings['mailman']['system_newaliases_path'];
    if ($DEBUG) {
      fwrite($log, "    Ejecutando: " . $cmd . "\n");
    }
    $output=shell_exec($cmd);
  }

  /*
   *      Remove archives
  */
  $result = Database::query("SELECT * FROM `".TABLE_MODULE_MAILMAN."` WHERE type = 'del_archive'");
  // foreach dataset
  while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    # Hack para dominios virtuales (parche de mailman)
    if ($settings['mailman']['system_mailman_multidomain_patch'] == "TRUE") {
      #$list_name = $row['list_name'];
      $list_name = str_replace("@", "-", $row['list_name']);
    } else {
      $list_name = substr($row['list_name'],0,strpos($row['list_name'],"@"));
    }
    $cmd = $settings['mailman']['system_rmlist_path']." -a ".$list_name." ";
    if ($DEBUG) {
      fwrite($log, "    Ejecutando: " . $cmd . "\n");
    }
    $output = shell_exec($cmd);
    $query2 = 'DELETE FROM `'.TABLE_MODULE_MAILMAN.'` WHERE `id` = :list_id OR ( type = "system" AND `list_name` = :list_name )';
    $result2 = Database::prepare($query2);
    Database::pexecute($result2, array("list_id" => $row['id'], "list_name" => $list_name));
  }



  /*
   *	Check whether there are unused vhosts
  */
  $result = Database::query("SELECT * FROM `".TABLE_PANEL_DOMAINS."` WHERE specialsettings != ''");
  // foreach dataset
  while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    if(stristr($row['specialsettings'], "mailman")){
      $keep=0;
      $result2 = Database::query("SELECT list_webhost FROM `".TABLE_MODULE_MAILMAN."` WHERE type = 'list' OR type = 'new' OR type = 'del_archive'");
      while ($list_webhost = $result->fetch(PDO::FETCH_ASSOC)) {
        $list_webhost = $list_webhost['list_webhost'];
        if($row['domain'] == $list_webhost ){
          $keep=1;
        }
      }
      if($keep == 0 ){
        $result3 = Database::prepare("DELETE FROM `".TABLE_PANEL_DOMAINS."` WHERE `id` = :id");
        Database::pexecute($result3, array("id" => $row['id']));
        inserttask(1);
        inserttask(4);
      }
    }
  }


  /*
   *      Reread in the existing lists from the system
  */
  $cmd = $settings['mailman']['system_list_lists_path'].' -b';
  $existing_lists = shell_exec("$cmd");
  strtolower($existing_lists);
  $existing_lists_array = explode("\n", $existing_lists);


  /*
   *      Sync mailman --> database
  */
  foreach ($existing_lists_array as $value){
    if($value!=""){
      $result = Database::prepare('SELECT *  FROM `'.TABLE_MODULE_MAILMAN.'` WHERE `type` ="system" AND `list_name` = :list_name');
      Database::pexecute($result, array("list_name" => $value));
      if (!$result){
        $result2 = Database::prepare('INSERT INTO `'.TABLE_MODULE_MAILMAN.'` SET
		`list_name` = :list_name,
		`type` = "system", `transport` = "mailman",
		`list_mailhost` = :list_mailhost');
        Database::pexecute($result2, array("list_name" => $value, "list_mailhost" => $settings['system']['hostname'])); 
      }
    }
  }


  if ($DEBUG) {
    fclose($log);
  }
  #$output = shell_exec("/etc/init.d/postfix reload");

  // shutdown cron
  include_once FROXLOR_INSTALL_DIR . '/lib/cron_shutdown.php';

?>
