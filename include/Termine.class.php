<?php

class Termine
{
  var $Klassifikationen;
  var $Betroffen;
  var $Filter;
  var $db;

  function istGefiltert()
  {
    if ( Count($this->Betroffen) != Count($this->Filter) )
      return true;
    else
      return false;
  }
  function getKlassifikation($id)
  {
    if ( isset($this->Klassifikationen[$id]))
      return $this->Klassifikationen[$id];
    else
      return '';
  }
  function getKlassifikationen()
  {
    return $this->Klassifikationen;
  }
  function getBetroffen()
  {
    return $this->Betroffen;
  }
  function holeStand($vorlaeufig = false)
  {
     if ( $vorlaeufig )
       $v = '1';
     else
       $v = 'NOT Vorlaeufig';
     $query = mysql_query("SELECT DATE_FORMAT(MAX(Stand),'%d.%m.%Y %H:%i') FROM T_Termin_Termine ".
       "WHERE $v", $this->db);
     $stand = mysql_fetch_row($query);
     mysql_free_result($query);
     return $stand[0];
  }
  function holeKlassifikationen()
  {
    $Klassifikation = array();
    $query = mysql_query('SELECT * FROM T_Termin_Klassifikationen ORDER BY Klassifikation',
      $this->db);
    while ( $row = mysql_fetch_array($query) )
      $Klassifikation[$row['Klassifikation_id']] = $row['Klassifikation'];
    mysql_free_result($query);
    return $Klassifikation;
  }
  function holeBetroffen()
  {
    $Betroffen = array();
    $query = mysql_query('SELECT * FROM T_Termin_Betroffene ORDER BY Betroffen', $this->db);
    while ( $row = mysql_fetch_array($query) )
    {
      if ( trim($row['Berechtigte']) == '' ||
           in_array($_SERVER['REMOTE_USER'], explode(',',$row['Berechtigte'])) )
      {
        $Betroffen[$row['Betroffen_id']] = $row['Betroffen'];
      }
    }
    mysql_free_result($query);
    return $Betroffen;
  }
  function sichereFilter()
  {
   // Filter speichern
   if ( ! isset($_REQUEST['ProfilFiltern']) && ! isset($_REQUEST['ProfilSave']) &&
        ! isset($_REQUEST['ProfilFilterAlle']) ) return;
   if ( isset($_REQUEST['ProfilSave']) && trim($_REQUEST['Profil']) != '' )
     $Art = mysql_real_escape_string($_REQUEST['Profil']);
   else
     $Art = '(Standard)';
   if ( isset($_REQUEST['ProfilFilterAlle']) )
   {
     $_REQUEST['Filter'] = array_keys($this->Betroffen);
   }
   if ( isset($_REQUEST['Filter']) )
   {
     $Filter = mysql_real_escape_string(implode(',',$_REQUEST['Filter']));
     if ( ! mysql_query("UPDATE T_Termin_UserFilter SET Filter='".$Filter."' WHERE User='".
       $_SERVER['REMOTE_USER']."' AND Art='$Art'",$this->db)) 
         echo '<div class="Fehler">'.mysql_error($this->db).'</div>';
     if ( mysql_affected_rows($this->db) == 0 )
       if ( ! mysql_query("INSERT INTO T_Termin_UserFilter (User, Filter,Art) VALUES ('".
         $_SERVER['REMOTE_USER']."','".$Filter."','$Art')",$this->db))
           echo ''; //I ($Filter):".mysql_error($this->db);
   }
  }
  function holeFilter()
  {
     // Filter laden
     $Art = '(Standard)';
     if ( isset($_REQUEST['ProfilLaden']) && $_REQUEST['Profile'] != '' )
       $Art = mysql_real_escape_string($_REQUEST['Profile']);
     if ( ! $query = mysql_query("SELECT Filter FROM T_Termin_UserFilter WHERE User='".
       $_SERVER['REMOTE_USER']."' AND Art='$Art'", $this->db)) echo mysql_error($this->db);
     if ( $row = mysql_fetch_array($query) )
       $Filter = explode(',',$row[0]);
     else
       $Filter = array_keys($this->Betroffen);
     mysql_free_result($query);
     $Alle = array_keys($this->Betroffen);
     foreach ( $Filter as $key=>$value )
       if ( ! in_array($value, $Alle) )
         unset($Filter[$key]);
     unset($_REQUEST['Filter']);
     return $Filter;
  }
  /*
   * gibt an ob der eingeloggte Benutzer den Termin bearbeiten darf
   * Entweder der Bearbeiter ist der Erzeuger des Termins, oder er steht in der
   * Veraenderung-Spalte der Tabelle.
   */
  function istBearbeiter($wer, $Betroffene = '')
  {  	
    if ( $wer == $_SERVER['REMOTE_USER'] )         
       return true;
    if ( $Betroffene == '' ) return false;
    $query = mysql_query('SELECT Veraenderung FROM T_Termin_Betroffene ' .
    		"WHERE Betroffen_id IN ($Betroffene) AND Veraenderung != ''");
    $darf = false;
    while ( $betroffen = mysql_fetch_row($query))
    {
    	if ( in_array($_SERVER['REMOTE_USER'], explode(',',$betroffen[0]) ))
    	  $darf = true;
    }
    mysql_free_result($query);
    return $darf;
  }
  
  function istBetroffen($wer)
  {
    if ( !is_array($wer) )
      $wer = explode(',',$wer);
    if ( Count(array_intersect($wer, $this->Filter) )>0 )
      return true;
    else
      return false;
  }
  function InhaltAnfuegen($Inhalt, $Wert, $Uhrzeit = '')
  {
    if ( $Uhrzeit != '' )
    {
      $Uhrzeit = date('H:i',$Uhrzeit);
      if ( $Uhrzeit != '00:00' )
        $Wert .= ' <span class="Uhrzeit">('.$Uhrzeit.')</span>';
    }
    if ( trim($Inhalt) == '' )
      return $Wert;
    else
      return $Inhalt.'<br />'.$Wert;
  }

  function holeFilterNamen($dieFilter = -1)
  {
     if ( !is_array($dieFilter) )
       $dieFilter = $this->Filter;
     $b = array();
     foreach ( $dieFilter as $value )
       $b[] = $this->Betroffen[$value];
     return implode(',',$b);
  }
  function BetroffeneAnzeigen($Betroffene, $separator = ', ', $klein = true)
  {
     $x = '';
     if ( is_array($Betroffene) )
       $a = $Betroffene;
     else
       $a = explode(',',$Betroffene);
     foreach ( $a as $value )
       if ( isset($this->Betroffen[$value]))
         $x .= $separator.$this->Betroffen[$value];
     if ( strlen($x) > 0 )
     {
       $x = substr($x, strlen($separator)); // führenden Separator wegschneiden
       $x = trim($x);
       if ( $klein )
         $x = '<br /><span class=Betroffene>'.$x.'</span>';
     }
     return $x;
  }

  function holeTermineAlsHTML($anzahl = 10)
  {
    $s = '<h1 style="margin:0;padding:0;">nächste Termine</h1><span class="small">';
    $s .= '(Stand: '.$this->holeStand();
    if ( $this->Filter != array_keys($this->Betroffen) ) //$this->Alle )
      $s .= ' <strong>gefiltert</strong>';
    $s .= ')<br />';
    $s .= '<div class="small">Bewegen Sie die Maus auf einen Termin um näheres zu Erfahren!</div><br />';
    if ( ! $query = mysql_query("SELECT *,DATE_FORMAT(Stand,'%d.%m.%Y %H:%i') AS St ".
           'FROM T_Termin_Termine WHERE NOT Vorlaeufig AND Datum >= '.mktime(0,0,0,date('m'),date('d'),
             date('y')).' ORDER BY Datum')) echo '<div class="Fehler">'.mysql_error().'</div>';
    $anz = 0;
    while ( ($termin = mysql_fetch_array($query)) && $anz < 10)
    {
      $art = explode(',',$termin['Betroffene']);
      if ( Count(array_intersect($art, $this->Filter)) > 0 )
      {
        $s .= '<span onMouseOver="return overlib('."'";
        $s .= htmlentities(stripslashes(str_replace("\n",'',
               str_replace("\r",'',nl2br($termin['Beschreibung'])))));
        $s .= $this->BetroffeneAnzeigen($termin['Betroffene']);
        $s .= '<br /><span class=Termininfo>'.$termin['Bearbeiter'].' / Stand: ';
        $s .= $termin['St'].'</span>';
        $s .= "',CAPTION,'".$termin['Bezeichnung'].' ('.
              $this->getKlassifikation($termin['F_Klassifikation']).")');";
        $s .= '" onMouseOut="return nd();">';

        $s .= '<em>'.date('d.m.Y', $termin['Datum']);
        $Uhrzeit = date('H:i',$termin['Datum']);
        if ( $Uhrzeit != '00:00' )
          $s .= ' '.$Uhrzeit;
        $s .= '</em><br />';
        $s .= stripslashes($termin['Bezeichnung']).' ('.
          str_replace('<br />','',$this->BetroffeneAnzeigen($termin['Betroffene'])).')</span><br /><br />';
        $anz++;
      }
    }
    mysql_free_result($query);
    $s .= '<a href="/Termin/TermineWoche.php">';
    $s .= '<img src="http://img.oszimt.de/nav/link.gif" width="25" height="13" border="0">';
    $s .= '</a> ';
    $s .= '<a href="/Termin/TermineWoche.php">Wochenübersicht</a><br />';

    $s .= '<a href="/Termin/Termine.php">';
    $s .= '<img src="http://img.oszimt.de/nav/link.gif" width="25" height="13" border="0">';
    $s .= '</a> ';
    $s .= '<a href="/Termin/Termine.php">konfigurieren</a></span><br />';
    return $s;
  }

  function CheckScriptEinfuegen()
  {
  ?>
  <script type="text/javascript">
  <!--
  function CheckAll(wert)
  {
    for (var i=0;i<document.Formular.elements.length;i++)
    {
      var e = document.Formular.elements[i];
      if (e.name == "Filter[]" )
        if ( wert == -1 )
          e.checked = ! e.checked;
        else
          e.checked = wert;
    }
  }
  //-->
  </script>
  <?php
  }

  function vorlaeufigPerson()
  {
  	include_once('include/Abteilungen.class.php');
  	$dieAbteilungen = new Abteilungen($this->db);
    return $dieAbteilungen->isAbteilungsleitung();  	  	
  }
  /* Konstruktor */
  function Termine($db, $sichern=false)
  {
    $this->db = $db;
    $this->Klassifikationen = $this->holeKlassifikationen();
    $this->Betroffen = $this->holeBetroffen(); //, & $this->Alle);
    if ( $sichern ) $this->sichereFilter();
    $this->Filter = $this->holeFilter();
  }

  function zeigeTerminfilter()
  {
    // Filtermöglichkeiten
   echo '<table><form action="'.$_SERVER['PHP_SELF'].'" name="Formular" method="post">';
   if ( isset($_REQUEST['Woche']))
     echo '<input type="hidden" name="Woche" value="'.$_REQUEST['Woche'].'" />';
   if ( isset($_REQUEST['Jahr']))
     echo '<input type="hidden" name="Jahr" value="'.$_REQUEST['Jahr'].'" />';
   echo '<tr><td colspan="8" align="center"><strong>Nur Termine anzeigen für</strong></td>';
   $Nr = 0;
   foreach ( $this->Betroffen as $key => $value )
   {
     if ($Nr % 4 == 0)
       echo '</tr><tr>';
     echo '<td><input type="Checkbox" name="Filter[]" value="'.$key.'" ';
     if ( $this->istBetroffen($key) ) //in_array($key, $this->Filter) )
       echo 'checked="checked"';
     echo '></td><td>'.$value.'</td>';
     $Nr++;
   }
   echo '</tr><tr>';
   echo '<td colspan="8"><input type="Submit" name="ProfilFiltern" value="Termine filtern">';
   echo '&nbsp;&nbsp;&nbsp;[ <a href="'.$_SERVER["PHP_SELF"].'?ProfilFilterAlle=1">Alle Termine anzeigen</a> ]';
   echo '&nbsp;&nbsp;&nbsp;[ <a href="javascript:CheckAll(-1);">Häkchen umschalten</a> ]';
   echo '&nbsp;&nbsp;&nbsp;[ <a href="javascript:CheckAll(1);">alle Häkchen ein</a> ]';
   echo '&nbsp;&nbsp;&nbsp;[ <a href="javascript:CheckAll(0);">alle Häkchen aus</a> ]';
   echo '</td></tr>';
   echo "</tr>\n";
   echo '<tr><td colspan="4">Profil <select name="Profile">';
   $query = mysql_query("SELECT * FROM T_Termin_UserFilter WHERE User='{$_SERVER['REMOTE_USER']}' ORDER BY Art");
   while ( $art = mysql_fetch_array($query) )
   {
     echo '<option>'.$art['Art'];
     echo "</option>\n";
   }
   mysql_free_result($query);
   echo '</select> <input type="Submit" name="ProfilLaden" value="Laden">';
   echo '&nbsp;&nbsp;&nbsp;Profil als <input type="Text" name="Profil" size="10" maxlength="10"/>';
   echo '<input type="Submit" name="ProfilSave" value="Speichern" />';
   echo "</td></tr></form>\n";
   echo "</table>\n";
  }

} // Klasse Termine
?>
