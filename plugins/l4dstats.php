<?php
/**************************************************
* Sourcemod Webadmin Script                       *
*                                                 *
* advertisements.php                              *
*                                                 *
* Copyright (C) 2008-20XX By Andreas Dahl         *
*                                                 *
* www.forum.sourceserver.info                     *
* www.hsfighter.net                               *
*                                                 *
**************************************************/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

For support and installation notes visit:
EN: http://forums.alliedmods.net/showthread.php?t=60174
DE: http://www.sourceserver.info/viewtopic.php?f=48&t=451
*/

### Some Funtions can be found in ../inc/funktion.php

/*
================================================================================
--------------------------=[Section Plugnin Management]=------------------------
================================================================================
*/
$l4dstats_text0 = "Text 0";
$l4dstats_text1 = "SteamID";
$l4dstats_text2 = "IP";
$l4dstats_text3 = "Points";
$l4dstats_text4 = "Type";
$l4dstats_text5 = "Name";
$l4dstats_text6 = "Steam Name";
$l4dstats_text7 = "Play Time";
$l4dstats_text8 = "Melee Kills";
$l4dstats_text9 = "Kills";
$l4dstats_text10 = "Headshots";
$l4dstats_text11 = "Edit User Stats";
$l4dstats_text12 = "Delete User Stats";
$l4dstats_text13 = "Add Stats";
$l4dstats_text14 = "Infected Killed";
$l4dstats_text15 = "Text 15";
$l4dstats_text16 = "Text 16";
$l4dstats_text17 = "%statstext%<br><span style='color:green;'>have been deleted!</span>";
$l4dstats_text18 = "%statstext%<br><span style='color:green;'>have been added!</span>";
$l4dstats_text19 = "<span style='color:red;'>Do you want delete the stats for the SteamID<br></span> %statstext% <span style='color:red;'>?</span>";

### ============================================================================

// This value is the GET parameter SteamID.
$getprm_steamid = (isset($_GET['id']) && preg_match('/^STEAM_\d:\d:(\d*+)$/i', $_GET['id']) ? $_GET['id'] : null);

//echo "<pre>";
//print_r($Serversteamids);
// echo "</pre>";

if ($area == ''){ // Begin if Area = ''
  
  // Define text for all mysqlbans templatefiles
  $tpl->set_var(array(
	"advertisements_header_text"          => $plugin['Name'],
	"advertisements_text_discription" => $l4dstats_text5,
	"advertisements_visible_discription" => $l4dstats_text2,
	"advertisements_id_discription" => $l4dstats_text1,
    "advertisements_type_discription" => $l4dstats_text3,
    "advertisements_game_discription" => $l4dstats_text4,
    "advertisements_gamesteamid_discription" => $viewtext['System'][67],
    "section"                  => $section,
    "plugin"                   => $plugin['Systemname'],
    "id"                       => $id,
    "page"                     => $current_site
  ));


// ---------------------[ If action "empty" show maintable]---------------------
if ($action == ""){

    // ---> START move entry section <---
    // Read move variable from adresslist
    $move = (isset($_GET['move'])) ? $_GET['move']:'';
    // Check if move varable is set
    if (($move == "up") OR ($move == "down")){
      // Select move up or move down
      if ($move == "up") $new_steamid = $id +1;
      if ($move == "down") $new_steamid = $id -1;
      //Send SQL-Query
      sqlexec("UPDATE ".$plugin['Tablename']." SET steamid=-1 WHERE steamid='".$id."'");
      sqlexec("UPDATE ".$plugin['Tablename']." SET steamid='".$id."' WHERE steamid='".$new_steamid."'");
      sqlexec("UPDATE ".$plugin['Tablename']." SET steamid='".$new_steamid."' WHERE steamid=-1");
    }
    // ---> END move entry section <---

  // Define text for all advertisements templatefiles when action = empty
  $tpl->set_var(array(
    "advertisements_options_discription" => $viewtext['System'][65],
    "advertisements_steamid_discription" => $viewtext['System'][68],
    "advertisements_arrowup_pic_infotext" => $viewtext['System'][75],
    "advertisements_arrowdown_pic_infotext" => $viewtext['System'][76],
    "advertisements_edit_pic_infotext" =>$l4dstats_text11,
    "advertisements_delete_pic_infotext" => $l4dstats_text12,
	"add_advertisements_button_text" => $l4dstats_text13,
	"advertisements_no_entrys"       => $viewtext['System'][50]
    ));

  // Define templatefile
  $tpl->set_file("inhalt", "templates/plugins/advertisements.tpl.htm");

  // Define blocks to schow entrys in the loop's
  $tpl->set_block("inhalt", "advertisementsview", "advertisementsview_handle");
  $tpl->set_block("inhalt", "advertisementsviewempty", "advertisementsviewempty_handle");

  // ---> START show search section <---
  // Create empty array
  $allsearchcategories = array();
  // Define values in the serachscrollbox
  $allsearchcategories['value'] = array('advertisement', 'steamid');
  // Define showtext in the serachscrollbox
  $allsearchcategories['view'] = array($viewtext['System'][118], $viewtext['System'][68]);
  // Define serach table column's
  $allsearchcategories['table'] = array('text', 'steamid');
  // Start funktion show search area
  $tpl = show_search($tpl, $allsearchcategories, $searchcat, $searchstring, $viewtext, $plugin['Systemname'], '');
  // Search filter for sql injections
  $searcquery = search_sql_injection_filter ($allsearchcategories, $searchcat, $searchstring, '');

  // ---> START show page section <---
  // Check Entry Count for Nextpage-Funktion
  //$all_entrys = mysql_num_rows(mysql_query("SELECT steamid FROM ".$plugin['Tablename']));
  $all_entrys = mysql_num_rows(mysql_query("SELECT * FROM ".$plugin['Tablename']." ".$searcquery));
  // If current page > Count of all pages set current page to count of pages
  if ($current_site > ceil($all_entrys / $settings['advertisements_show_advertisements'])) $current_site = 1;
  // Get Startentry
  $start_entry = $current_site * $settings['advertisements_show_advertisements'] - $settings['advertisements_show_advertisements'];
  // Start funktion show entry ... to ... from ...!
  $tpl = showentrys($tpl, $viewtext['System'][21] ,$l4dstats_text10, $start_entry, $all_entrys, $settings['advertisements_show_advertisements']);
  // Start funktion page-select-links
  $tpl = site_links($tpl, $all_entrys, $settings['advertisements_show_advertisements'], $current_site, $section, '', $plugin['Systemname'], $searchcat, $searchstring, '', $viewtext);
  // ---> END Show Page Section <---

  //Set startcount of errow handlecount
  $handle_count = $start_entry;

  $sql = "SELECT * FROM ".$plugin['Tablename']." ".$searcquery." ORDER BY steamid DESC LIMIT ".$start_entry.", ".$settings['advertisements_show_advertisements']."";
  //$sql = "SELECT * FROM ".$plugin['Tablename']." ORDER BY steamid ASC";

  $result = mysql_query($sql) OR die(mysql_error());
  if(mysql_num_rows($result)){
    while($row = mysql_fetch_assoc($result)){

        // ++ handlecount for errows
        $handle_count++;

		
// Start to get Mod Icon from Mod Database
$tpl = PluginGameIcon($tpl, $table, 'advert', $row['lastgamemode'], $handle_count, 'All');

// If serach string nothing: Call function to show and manage arrows
if ($searcquery == "") $tpl = ParseArrows($tpl, $start_entry, $all_entrys, $settings['advertisements_show_advertisements'], $handle_count,$current_site, $row['steamid'], $viewtext);

      // Replace { }  to asci code!
	  //$row['text'] = str_replace('{', '&#123;', $row['text']);
	  //$row['text'] = str_replace('}', '&#125;', $row['text']);
	  
	    $row['name'] = BadStringFilterShow($row['name']);

 // Templatevars advertisement text to the loop.
      $tpl->set_var(array(
        "advertisements_id" => $row['steamid'],
        "advertisements_text" => $row['name'],
        "advertisements_visible" => $row['ip'],
        "advertisements_type" => $row['points']
      ));
      // Parse all templatevars to the advertisementsview block.
      $tpl->parse("advertisementsview_handle", "advertisementsview", true);


    }
    }else{
      // Parse all templatevars to the advertisementsviewempty block.
      $tpl->parse("advertisementsviewempty_handle", "advertisementsviewempty", true);
    }

}

// -------------------[ If action addads or editads show add/edit window]----------------------

if (($action == "addads") or ($action == "editads")){


  // Define templatefile
  $tpl->set_file("inhalt", "templates/plugins/l4dstats_add_edit.tpl.htm");
  $tpl->set_file("colorinfo", "templates/plugins/l4dstats_info.tpl.htm");
  
  // Set Templatevars for colorinfo
  $tpl->set_var(array("advertisements_not_aviable_text"   => $viewtext['System'][127]));
    
  // Define block to schow last edit entry
  $tpl->set_block("inhalt", "lastedit", "lastedit_handle");

  // Set Templatevars for addads and editads form
      $tpl->set_var(array(
        
		"advertisements_not_aviable_text"   => $viewtext['System'][127],
        "advertisements_info_general"       => $viewtext['System'][71],
        "add_edit_das_button_text"          => $viewtext['System'][7],
        "back_ads_button_text"              => $viewtext['System'][4],
		"colorhelp" 						=> $tpl->parse("out", "colorinfo")
		
		));
}

//

// -----------------------------[ If action addads ]----------------------------

if ($action == "addads") {

  // Call Function to Show Mod-Gamelist
  $tpl = PluginGameScrollbox($tpl, $table."_mods", "advert", "");
        // Set Templatevars for addads form
        $tpl->set_var(array(
          "advertisements_header_action_text" => $l4dstats_text13,
          "action"                            => 'execaddads',
          "checkedsay"                        => 'checked',
          "checkedall"                        => 'checked',
          "advertisements_text"               => ''
        ));
  
}

// ----------------------------[ If action editads ]----------------------------

if ($action == "editads") {

 // Read Advertment Entry
 $row = ReadAdvertEntry($tpl, $viewtext, $plugin['Name'], $plugin, $id);
  if ($row <> False){

  // Call Function to Show Mod-Gamelist
  $tpl = PluginGameScrollbox($tpl, $table."_mods", "advert", $row['lastgamemode']);
   
   $row['steamid'] = BadStringFilterShow($row['steamid']);
   
  // Add text Templatevars
  $tpl->set_var(array(
    "advertisements_user"                 => $row['name'],
    "advertisements_visible"                 => $row['ip'],
    "advertisements_lastedit_discription" => $l4dstats_text6,
    "advertisements_header_action_text"   => $l4dstats_text11,
    "advertisements_playtime_discription"   => $l4dstats_text7,
    "advertisements_melee_discription"   => $l4dstats_text8,
    "advertisements_melee"   => $row['melee_kills'],
    "advertisements_kills_discription"   => $l4dstats_text9,
    "advertisements_kills"   => $row['kills'],
    "advertisements_headshots_discription"   => $l4dstats_text10,
    "advertisements_headshots"   => $row['headshots'],
    "advertisements_killinfected_discription"   => $l4dstats_text14,
    "advertisements_killinfected"   => $row['kill_infected'],
    "action"                              => 'execeditads',
    "advertisements_id"                   => $row['steamid'],
    "advertisements_type"                 => $row['points']
  ));

  // Parse lastedit block
  $tpl->parse("lastedit_handle", "lastedit", true);

  }
}

// ------------------[ If action execeditads or execeditads ]-------------------


if (($action == "execaddads") OR ($action == "execeditads")){
   
  // Read html inputs
  $statstext  = (isset($_POST['text'])) ? $_POST['text']:'';
  $adstype  = (isset($_POST['adstype'])) ? $_POST['adstype']:'';
  $adsflag = (isset($_POST['adsflag'])) ? $_POST['adsflag']:'';
  $adsgame  = (isset($_POST['adsgame'])) ? $_POST['adsgame']:'';
  
  $statstext = BadStringFilterSave($statstext);
  
  // Replace forbiten letters
  //$statstext = str_replace("'", "", $statstext);
  
  // Check textbox input are empty
  if ($statstext == ""){
    // Show Infobox
	$tpl = Infobox($tpl, $plugin['Name'], $viewtext['System'][25], $viewtext['System'][4], 'javascript:history.back()'); 
	$go = 0;
  }else{ // If not empty
    $go = 1;  
  }
}
  
  
// ----------------------------[ If action exeaddads ]--------------------------
if (($action == "execaddads") and ($go == 1)){

  // Get next free steamid
  $checksteamid = 0;
  $sql = "SELECT steamid FROM ".$plugin['Tablename']." ORDER BY steamid ASC";
  $result = mysql_query($sql) OR die(mysql_error());
  if(mysql_num_rows($result)){
    while($row = mysql_fetch_assoc($result)){
	 $checksteamid++;
	 $l4dstats_id = $checksteamid + 1;
      if ($row['steamid'] != $checksteamid){
  	    $l4dstats_id = $checksteamid;
		break;
      }
    }
  }else{
    $l4dstats_id = 1;
  }
  
   sqlexec("INSERT INTO ".$plugin['Tablename']." SET steamid='".$l4dstats_id."', text='".$statstext."', lastgamemode='".$adstype."', flags='".$adsflag."', game='".$adsgame."', gamesrvsteamid='".$adsgamesteamid."'", mysql_real_escape_string($statstext));
  // Replace %statstext% from langeuagefile to dvertisement-text
  $l4dstats_text18 = str_replace('%statstext%', $statstext, $l4dstats_text18);
  // Replace \' to '
  $l4dstats_text18 = str_replace("\'", "'", $l4dstats_text18);
  // Show Infobox
  $tpl = Infobox($tpl, $plugin['Name'], $l4dstats_text18, $viewtext['System'][3], "index.php?section=plugins&plugin=".$plugin['Systemname']."&page=".$current_site."");

}

// ---------------------------[ If action execeditads ]--------------------------
if (($action == "execeditads")  and ($go == 1)){
 // Update Entry
 
 sqlexec("UPDATE ".$plugin['Tablename']." SET text='".$statstext."', lastgamemode='".$adstype."', flags='".$adsflag."', game='".$adsgame."' , gamesrvsteamid='".$adsgamesteamid."' WHERE steamid = '".$steamid."'", mysql_real_escape_string($statstext));
 // Show Infobox
 $tpl = Infobox($tpl, $plugin['Name'], $viewtext['System'][74], $viewtext['System'][3], "index.php?section=plugins&plugin=".$plugin['Systemname']."&page=".$current_site."");
}

// ---------------------------[ If action delads ]--------------------------
if ($action == "delads"){
  // Read Advertment Entry
  $row = ReadAdvertEntry($tpl, $viewtext, $plugin['Name'], $plugin, $id);
  if ($row <> False){
    // Replace %statstext% from langeuagefile to dvertisement-text
    $l4dstats_text19 = str_replace('%statstext%', $row['steamid'], $l4dstats_text19);
    // Show Select box Yes/No
    $tpl = Infoboxselect($tpl, $plugin['Name'], $l4dstats_text19, $viewtext['System'][2], $viewtext['System'][1],  "index.php?section=plugins&plugin=".$plugin['Systemname']."&page=".$current_site."", "index.php?section=plugins&plugin=".$plugin['Systemname']."&action=execdelads&page=".$current_site."&steamid=".$id."" );
  }
}

// ---------------------------[ If action execdelads ]--------------------------
if ($action == "execdelads"){
  // Read Advertment Entry
  $row = ReadAdvertEntry($tpl, $viewtext, $plugin['Name'], $plugin, $steamid);
  if ($row <> False){
  // Delete Entry
  sqlexec("DELETE FROM ".$plugin['Tablename']." WHERE steamid = ".$steamid."");
  // Show Infobox
  $tpl = Infobox($tpl, $plugin['Name'], $l4dstats_text17, $viewtext['System'][3], "index.php?section=plugins&plugin=".$plugin['Systemname']."&page=".$current_site."");
  }
}

//------------------------------------------------------------------------------

} // End if Area = '

//==============================================================================
// ----------------------[ Plugin specific functions  ]-------------------------
//==============================================================================

// Function to read Advertisment Entry.
function ReadAdvertEntry($tpl, $viewtext, $boxheader, $plugin, $steamid){

$sql = "SELECT * FROM ".$plugin['Tablename']." WHERE steamid = ".$steamid."";

  $result = mysql_query($sql) OR die(mysql_error());
  if(mysql_num_rows($result)){
    while($row = mysql_fetch_assoc($result)){
       $return = $row;
    }
  }else{
    $viewtext['System'][73] = str_replace('%steamid%', $steamid, $viewtext['System'][73]);
    $tpl = Infobox($tpl, $boxheader, $viewtext['System'][73], $viewtext['System'][4], 'javascript:history.back()');
    return false;
  }

  return $return;
}


//==============================================================================
//--------------------------=[Section Plugnin Settings]=------------------------
//==============================================================================

// ---> BEGIN Create Table for ChatLog Extended Plugin
if ($area == 'createtable'){

  sqlexec("CREATE TABLE ".$plugintable."(
	  `name` varchar(255) character set utf8 collate utf8_bin NOT NULL,
	  `gamemode` int(1) NOT NULL default '0',
	  `custom` bit(1) NOT NULL default '\0',
	  `playtime_nor` int(11) NOT NULL default '0',
	  `playtime_adv` int(11) NOT NULL default '0',
	  `playtime_exp` int(11) NOT NULL default '0',
	  `restarts_nor` int(11) NOT NULL default '0',
	  `restarts_adv` int(11) NOT NULL default '0',
	  `restarts_exp` int(11) NOT NULL default '0',
	  `points_nor` int(11) NOT NULL default '0',
	  `points_adv` int(11) NOT NULL default '0',
	  `points_exp` int(11) NOT NULL default '0',
	  `points_infected_nor` int(11) NOT NULL default '0',
	  `points_infected_adv` int(11) NOT NULL default '0',
	  `points_infected_exp` int(11) NOT NULL default '0',
	  `kills_nor` int(11) NOT NULL default '0',
	  `kills_adv` int(11) NOT NULL default '0',
	  `kills_exp` int(11) NOT NULL default '0',
	  `survivor_kills_nor` int(11) NOT NULL default '0',
	  `survivor_kills_adv` int(11) NOT NULL default '0',
	  `survivor_kills_exp` int(11) NOT NULL default '0',
	  `infected_win_nor` int(11) NOT NULL default '0',
	  `infected_win_adv` int(11) NOT NULL default '0',
	  `infected_win_exp` int(11) NOT NULL default '0',
	  `survivors_win_nor` int(11) NOT NULL default '0',
	  `survivors_win_adv` int(11) NOT NULL default '0',
	  `survivors_win_exp` int(11) NOT NULL default '0',
	  `infected_smoker_damage_nor` bigint(20) NOT NULL default '0',
	  `infected_smoker_damage_adv` bigint(20) NOT NULL default '0',
	  `infected_smoker_damage_exp` bigint(20) NOT NULL default '0',
	  `infected_jockey_damage_nor` bigint(20) NOT NULL default '0',
	  `infected_jockey_damage_adv` bigint(20) NOT NULL default '0',
	  `infected_jockey_damage_exp` bigint(20) NOT NULL default '0',
	  `infected_jockey_ridetime_nor` double NOT NULL default '0',
	  `infected_jockey_ridetime_adv` double NOT NULL default '0',
	  `infected_jockey_ridetime_exp` double NOT NULL default '0',
	  `infected_charger_damage_nor` bigint(20) NOT NULL default '0',
	  `infected_charger_damage_adv` bigint(20) NOT NULL default '0',
	  `infected_charger_damage_exp` bigint(20) NOT NULL default '0',
	  `infected_tank_damage_nor` bigint(20) NOT NULL default '0',
	  `infected_tank_damage_adv` bigint(20) NOT NULL default '0',
	  `infected_tank_damage_exp` bigint(20) NOT NULL default '0',
	  `infected_boomer_vomits_nor` int(11) NOT NULL default '0',
	  `infected_boomer_vomits_adv` int(11) NOT NULL default '0',
	  `infected_boomer_vomits_exp` int(11) NOT NULL default '0',
	  `infected_boomer_blinded_nor` int(11) NOT NULL default '0',
	  `infected_boomer_blinded_adv` int(11) NOT NULL default '0',
	  `infected_boomer_blinded_exp` int(11) NOT NULL default '0',
	  `infected_spitter_damage_nor` int(11) NOT NULL default '0',
	  `infected_spitter_damage_adv` int(11) NOT NULL default '0',
	  `infected_spitter_damage_exp` int(11) NOT NULL default '0',
	  `infected_spawn_1_nor` int(11) NOT NULL default '0' COMMENT 'Spawn as Smoker',
	  `infected_spawn_1_adv` int(11) NOT NULL default '0' COMMENT 'Spawn as Smoker',
	  `infected_spawn_1_exp` int(11) NOT NULL default '0' COMMENT 'Spawn as Smoker',
	  `infected_spawn_2_nor` int(11) NOT NULL default '0' COMMENT 'Spawn as Boomer',
	  `infected_spawn_2_adv` int(11) NOT NULL default '0' COMMENT 'Spawn as Boomer',
	  `infected_spawn_2_exp` int(11) NOT NULL default '0' COMMENT 'Spawn as Boomer',
	  `infected_spawn_3_nor` int(11) NOT NULL default '0' COMMENT 'Spawn as Hunter',
	  `infected_spawn_3_adv` int(11) NOT NULL default '0' COMMENT 'Spawn as Hunter',
	  `infected_spawn_3_exp` int(11) NOT NULL default '0' COMMENT 'Spawn as Hunter',
	  `infected_spawn_4_nor` int(11) NOT NULL default '0' COMMENT 'Spawn as Spitter',
	  `infected_spawn_4_adv` int(11) NOT NULL default '0' COMMENT 'Spawn as Spitter',
	  `infected_spawn_4_exp` int(11) NOT NULL default '0' COMMENT 'Spawn as Spitter',
	  `infected_spawn_5_nor` int(11) NOT NULL default '0' COMMENT 'Spawn as Jockey',
	  `infected_spawn_5_adv` int(11) NOT NULL default '0' COMMENT 'Spawn as Jockey',
	  `infected_spawn_5_exp` int(11) NOT NULL default '0' COMMENT 'Spawn as Jockey',
	  `infected_spawn_6_nor` int(11) NOT NULL default '0' COMMENT 'Spawn as Charger',
	  `infected_spawn_6_adv` int(11) NOT NULL default '0' COMMENT 'Spawn as Charger',
	  `infected_spawn_6_exp` int(11) NOT NULL default '0' COMMENT 'Spawn as Charger',
	  `infected_spawn_8_nor` int(11) NOT NULL default '0' COMMENT 'Spawn as Tank',
	  `infected_spawn_8_adv` int(11) NOT NULL default '0' COMMENT 'Spawn as Tank',
	  `infected_spawn_8_exp` int(11) NOT NULL default '0' COMMENT 'Spawn as Tank',
	  `infected_hunter_pounce_counter_nor` int(11) NOT NULL default '0',
	  `infected_hunter_pounce_counter_adv` int(11) NOT NULL default '0',
	  `infected_hunter_pounce_counter_exp` int(11) NOT NULL default '0',
	  `infected_hunter_pounce_damage_nor` int(11) NOT NULL default '0',
	  `infected_hunter_pounce_damage_adv` int(11) NOT NULL default '0',
	  `infected_hunter_pounce_damage_exp` int(11) NOT NULL default '0',
	  `infected_tanksniper_nor` int(11) NOT NULL default '0',
	  `infected_tanksniper_adv` int(11) NOT NULL default '0',
	  `infected_tanksniper_exp` int(11) NOT NULL default '0',
	  `caralarm_nor` int(11) NOT NULL default '0',
	  `caralarm_adv` int(11) NOT NULL default '0',
	  `caralarm_exp` int(11) NOT NULL default '0',
	  `jockey_rides_nor` int(11) NOT NULL default '0',
	  `jockey_rides_adv` int(11) NOT NULL default '0',
	  `jockey_rides_exp` int(11) NOT NULL default '0',
	  `charger_impacts_nor` int(11) NOT NULL default '0',
	  `charger_impacts_adv` int(11) NOT NULL default '0',
	  `charger_impacts_exp` int(11) NOT NULL default '0',
	  `mutation` varchar(64) NOT NULL default '',
	  PRIMARY KEY  (`name`,`gamemode`,`mutation`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
}

//------------------------------------------------------------------------------

// ---> BEGIN Delete Table for ChatLog Extended Plugin
if ($area == 'deltable'){
  sqlexec("DROP TABLE IF EXISTS ".$plugintable."");
}
//------------------------------------------------------------------------------

// ---> BEGIN to show the Specific Settings from the Settings Table.

if ($area == 'readspecificsettings'){

  // Set Specific Settings Template file
  $tpl->set_file("advertisements_specific_settings", "templates/plugins/l4dstats_specific_settings.tpl.htm");

  // Settingsaray from Settingstable and Description sending to Template var.
  $tpl->set_var(array(
     "advertisements_specific_settings_show_advertisements_discription"  => $l4dstats_text0,
     "advertisements_specific_settings_show_advertisements"  => $settings['advertisements_show_advertisements'],
	 "advertisements_specific_settings_dateformat_discription"  => $l4dstats_text1,
     "advertisements_specific_settings_dateformat"  => $settings['advertisements_date_format']
	));
	
  // Parse advertisements_specific_settings.tpl.htm to plugins_settings.tpl.htm
  $tpl->set_var(array("specific_settings" => $tpl->parse("out", "advertisements_specific_settings"),));
}

//------------------------------------------------------------------------------

// ---> BEGIN to save the Specific Settings in the Settings Table.

if ($area == 'savepecificsettings'){

  // Read inputfields from html template (Array must be $plugin['...']).
  $plugin['showadvertisements']   = (isset($_POST['showadvertisements'])) ? $_POST['showadvertisements']:'';
  $plugin['dateformat']   = (isset($_POST['dateformat'])) ? $_POST['dateformat']:''; 

  // Checkt type is Integer, when not or empty set to "0".
  if(isset($plugin['showadvertisements'])) settype($plugin['showadvertisements'], "integer");

  // When var is "0" set to Default
  if ($plugin['showadvertisements'] == 0) $plugin['showadvertisements'] = 30;

  // When var is empty set to default.
  if ($plugin['showadvertisements'] == '') $plugin['showadvertisements'] = 30;
  if ($plugin['dateformat'] == '') $plugin['dateformat'] = 'm-d-Y H:i:s';

  // Save var in settings-table.
  sqlexec("UPDATE ".$table."_settings SET Value='".$plugin['showadvertisements']."' WHERE Name = 'advertisements_show_advertisements'");
  sqlexec("UPDATE ".$table."_settings SET Value='".$plugin['dateformat']."' WHERE Name = 'advertisements_date_format'");
}

//==============================================================================
//==============================================================================
//==============================================================================
?>
