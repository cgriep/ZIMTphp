<?php
/*
 * OSZIMTSmarty
 * Erstellt 05.02.2006 Christoph Griep 
 */
require 'Smarty/Smarty.class.php';

/* Datenbankzugriff - Konstanten */
// include_once("include/config.php");
define("DATENBANKNAME", "oszimt");
define("DATENBANKUSER", "oszintern");
define("DATENBANKPASSWORD", "qBEj8h");
/*
 * Stellt eine Seite per Smarty-Template zur Verfügung.
 * Dabei wird automatisch Login, Footer (geändert am) eingetragen
 * Verwendet:
 * SESSION[Login] - Zeitpunkt des Logins dieser Session
 *   
 */
class OSZIMTSmarty extends Smarty {
	/*
	 * Das Datenbankhandle
	 */
	var $db;
	/*
	 * Bereitet den Schulrahmen vor.
	 * @param $Ueberschrift die Überschrift der Seite
	 * @param $ContentArt gibt an, wie die Seite aufgebaut ist: wenn leer, 
	 *        dann bleibt die Seite allein
	 *        wenn "MitMenu" wird rechts das Menü anzeigt (aus Datei menu.txt)
	 */
	/*
	 * @param $Ueberschrift die Überschrift der Seite
	 * @param $ContentArt der Aufbau der Seite: MitMenu, MitKalender, ""
	 * @param $DruckSymbol gibt an ob das Drucksymbol angezeigt wird oder nicht
	 */
	function OSZIMTSmarty($Ueberschrift = "", $ContentArt = "MitMenu", $DruckSymbol = true)
	{
      session_start();
	  parent::Smarty();
      // Datenbank öffnen
      $this->db = mysql_connect("localhost", DATENBANKUSER, DATENBANKPASSWORD);
      mysql_select_db(DATENBANKNAME, $this->db);
      
      // Login festhalten
	  $this->SaveLogin();
      // Footer bereitstellen
      // Header: Login
      $this->Assign("getLastLogin", $this->getLastLogin());	

      $PrintLink = $_SERVER["PHP_SELF"];
      $pre = "?";
      foreach ( $_REQUEST as $key => $value )
      {
        if ( is_array($value) )
          foreach ( $value as $kkey => $vvalue )
          {
            $PrintLink .= $pre.urlencode($kkey).'='.urlencode($vvalue);
            $pre = '&';
          }
        else
          $PrintLink .= $pre.urlencode($key).'='.urlencode($value);
        $pre = '&';
      }
      $PrintLink .= $pre."Print=1";
      $this->Assign("PrintLink",$PrintLink); 
      $this->Assign("DruckSymbol", $DruckSymbol);
      $this->Assign("LastChange", date ("d.m.Y H:i", filemtime(basename($_SERVER["PHP_SELF"]))));
      // rechtsseitiges Navigationsmenü anzeigen.
      // Navigationsmenü ist in der Datei menu.txt enthalten
      // (Format: Text;Linkname)
      // Parameter Print zeigt an, dass das Menü nicht angezeigt werden soll
      if ( is_file("menu.txt") )
      {
        $menue = array();
        $file = fopen("menu.txt", "r");
        while ( $zeile = fgets($file) )
        {
          if ( trim($zeile) != "" )
          {
            $z = explode(";", $zeile);
            $m = array();
            $m[0] = trim($z[1]);
            $m[1] = trim($z[0]);
            $menue[] = $m;
          }
        }
        fclose($file);
        $this->Assign("NavMenu", $menue);
      } // Menü anzeigen
      $this->Assign("ContentArt", $ContentArt); // Leer, MitMenu, MitKalender
      $this->Assign("Ueberschrift", $Ueberschrift);    
	} // Konstruktor
	
	/*
	 * Schließt die Datenbank
	 */  
	function schliesseDatenbank()
	{
      mysql_close($this->db);		
	}
	
	/*
	 * Sichert den aktuellen Zugriff in der Login-Datenbank
	 * Muss nach getLastlogin aufgerufen werden, damit nicht der aktuelle 
	 * Zugriff gezählt wird.
	 */	
	function SaveLogin()
    {
      $args = array();
      foreach ( $_REQUEST as $key => $value )
        $args[] = $key."=".$value;
      if ( ! session_is_registered("Login") )
        $_SESSION["Login"] = time();
      if ( ! isset($_SERVER["HTTP_REFERER"])) $_SERVER["HTTP_REFERER"] = "";
      mysql_query("INSERT INTO T_Logins (User, Login, IP, Referer,Args,Agent," .
      		"Method,Seite) VALUES ('".$_SERVER["REMOTE_USER"]."',".time().",'".
      		$_SERVER["REMOTE_ADDR"]."','".$_SERVER["HTTP_REFERER"]."','".
            mysql_real_escape_string(implode(";",$args))."','".
            $_SERVER["HTTP_USER_AGENT"]."','".$_SERVER["REQUEST_METHOD"]."','".
            $_SERVER["PHP_SELF"]."')");
    }
    /* 
     * Bestimmt den Zeitpunkt des letzten Logins, sofern eine Authorisierung
     * stattgefunden hat.
     */
    function getLastLogin()
    {
      $s = "";      
      if ( $_SERVER["REMOTE_USER"] != "" )
      {
        $last = "";
        if ( session_is_registered("Login") ) 
          $last = "AND Login < ".$_SESSION["Login"];
        $query = mysql_query("SELECT Max(Login) FROM T_Logins WHERE User='".
            $_SERVER["REMOTE_USER"]."' $last ");
        if ( $l = mysql_fetch_row($query) )
        {
          $s = "<i>".$_SERVER["REMOTE_USER"].
               '</i>, Sie haben<br />den internen Bereich<br />zuletzt am '.
               date("d.m.Y H:i",$l[0])."<br />betreten.\n";
        }
        mysql_free_result($query);
      }
      return $s;
    }
} // Klasse OSZIMTSmarty
?>