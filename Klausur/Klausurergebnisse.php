<?php
/**
 * Zeigt die Klausurergebnisse an
 * (c) 2006 Christoph Griep
 */
define('USE_KALENDER', 1);
$Ueberschrift = 'Klausurergebnis mitteilen';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/Vertretung.css">';
include('include/header.inc.php');
include('include/Klausur.inc.php');
include('include/turnus.inc.php');
include('include/Abteilungen.class.php');
include('include/Lehrer.class.php');
$dieAbteilungen = new Abteilungen($db);

if ( isset($_REQUEST['Klasse']) )
  if ( !is_numeric($_REQUEST['Abteilung']) )
    $_REQUEST['Abteilung'] = AbteilungFeststellen($_REQUEST['Klasse'], $db);

if ( isset($_REQUEST['DelKlausur']) && is_numeric($_REQUEST['DelKlausur']) )
{
  // Klausur löschen
  if ( $dieAbteilungen->isAbteilungsleitung() )
  {
    mysql_query('DELETE FROM T_Klausurergebnisse WHERE Klausur_id='.$_REQUEST['DelKlausur']);
    echo "<tr><td>Klausur {$_REQUEST['DelKlausur']} gelöscht.</td></tr>";
  }
}

if ( isset($_REQUEST['Klausur_id']) )
{
  if ( isset($_REQUEST['Datum']) )
  {
    $dat = explode('.',$_REQUEST['Datum']);
    $_REQUEST['Datum'] = $dat[2].'-'.$dat[1].'-'.$dat[0];
  }
  // Update
  if ( is_numeric($_REQUEST['Klausur_id']) )
  {
      if ( isset($_REQUEST['E']) && $dieAbteilungen->isAbteilungsleitung() )
      {
          $sql = 'SELECT Kenntnisnahme FROM T_Klausurergebnisse WHERE Klausur_id = '.$_REQUEST['Klausur_id'];
          if ( ! $query = mysql_query($sql,$db))
            echo 'Fehler: '.mysql_error($db);
          $Klausur = mysql_fetch_array($query);
          mysql_free_result($query);
          //if ( substr($Klausur[0],0,strlen($_SERVER["REMOTE_USER"])+1) == $_SERVER["REMOTE_USER"]." " )
            mysql_query('UPDATE T_Klausurergebnisse SET Kenntnisnahme=NULL WHERE Klausur_id='.
              $_REQUEST['Klausur_id'],$db);
          //else
          //  echo '&gt;&gt;&gt; Die Klausur wurde nicht von Ihnen kontrolliert.<br />';
      }
      if ( isset($_REQUEST['Kenntnisnahme']) && $_REQUEST['Wer'] == '' )
      {
        if ( ! mysql_query("UPDATE T_Klausurergebnisse SET Kenntnisnahme='".$_SERVER['REMOTE_USER'].
        ' '.date('d.m.Y')."' WHERE Klausur_id = ".$_REQUEST['Klausur_id'],$db)) echo mysql_error();
      }
      if ( isset($_REQUEST['Einser']) && is_numeric($_REQUEST['Einser']) && 
        is_numeric($_REQUEST['Zweier']) &&
        is_numeric($_REQUEST['Dreier']) && is_numeric($_REQUEST['Vierer']) &&
        is_numeric($_REQUEST['Fuenfer']) && is_numeric($_REQUEST['Sechser']) &&
        is_numeric($_REQUEST['Dauer'])) {
        $sql = "UPDATE T_Klausurergebnisse SET Lehrer='".
          mysql_real_escape_string($_REQUEST['Lehrer'])."',Datum='".
         $_REQUEST['Datum']."',Fach='".mysql_real_escape_string($_REQUEST['Fach']).
         "',Abteilung='".mysql_real_escape_string($_REQUEST['Abteilung']).
         "',Klasse='".mysql_real_escape_string($_REQUEST["Klasse"])."',Schuljahr='".
         mysql_real_escape_string($_REQUEST["Schuljahr"]).
         "',Dauer=".$_REQUEST["Dauer"].
         ",Einser=".$_REQUEST["Einser"].",Zweier=".
         $_REQUEST["Zweier"].",Dreier=".$_REQUEST["Dreier"].",Vierer=".$_REQUEST["Vierer"].
         ",Fuenfer=".$_REQUEST["Fuenfer"].",Sechser=".$_REQUEST["Sechser"].
         " WHERE Klausur_id = ".$_REQUEST["Klausur_id"];
        if ( ! mysql_query($sql, $db) ) echo "Fehler: ".mysql_error($db);
        else "&gt;&gt;&gt; Datensatz geschrieben.<br />";
        $Message = "Ein Klausurergebnis wurde korrigiert.\n".
         'Von '.$_REQUEST['Lehrer']."\n".
         'Klasse: '.$_REQUEST['Klasse'].' ('.
           $dieAbteilungen->getAbteilung($_REQUEST['Abteilung']).")\n".
         'Fach : '.$_REQUEST['Fach']."\n".
         'Datum der Klausur: '.$_REQUEST['Datum']."\n".
         'Dauer: '.$_REQUEST['Dauer']." min\n".
         '1: '.$_REQUEST['Einser']."\n".
         '2: '.$_REQUEST['Zweier']."\n".
         '3: '.$_REQUEST['Dreier']."\n".
         '4: '.$_REQUEST['Vierer']."\n".
         '5: '.$_REQUEST['Fuenfer']."\n".
         '6: '.$_REQUEST['Sechser']."\n".
         'Schnitt: '.Durchschnitt($_REQUEST)."\n";
         $Anzahl = Teilnehmeranzahl($_REQUEST);
         if ( substr($_REQUEST["Klasse"],0,2) != "OG" && $Anzahl > 0 &&
              $Anzahl < ($_REQUEST["Fuenfer"]+$_REQUEST["Sechser"])*3 )
           $Message .= "\nMehr als 1/3 unterm Strich! Genehmigung erforderlich!\n\n";
         $Message .= "Zur Kenntnisnahme: https://lehrer.oszimt.org/Klausur/Klausurergebnisse.php?Klausur_id=".$_REQUEST["Klausur_id"].
         "\n\nWebGroup OSZ IMT\n".date("d.m.Y H:i");
         foreach ($dieAbteilungen->getEmpfaenger($_REQUEST['Abteilung']) as $e)
        if ( ! mail($e,'[OSZIMT] Klausurergebnis korrigiert', $Message, 
          'From: '.$_SERVER['REMOTE_USER'].'@oszimt.de',
          '-f'.$_SERVER['REMOTE_USER'].'@oszimt.de') )
            echo '<div class="Fehler">&gt;&gt&;gt&; Fehler beim Mailversenden</div>';

      }
      //else echo "&gt;&gt;&gt; Fehler: Sie müssen numerische Werte eingeben!<br />";
    }
    elseif ( isset($_REQUEST['Datum']) )  // Neue Klausur
    {
      $query = mysql_query('SELECT * FROM T_Klausurergebnisse WHERE Abteilung='.
        $_REQUEST['Abteilung']." AND Klasse='".$_REQUEST['Klasse']."' AND Fach='".
          $_REQUEST['Fach']."' AND Datum='".date('Y-m-d',strtotime($_REQUEST['Datum']))."'");
      if ( $klausur = mysql_fetch_array($query) )
        echo '<strong style="background-color:red">&gt;&gt;&gt; Für diesen Tag wurde in diesem Fach bereits ".
          "eine Klausur eingetragen (von Koll. '.$klausur['Lehrer'].')!</strong><br />';
      else
      {
        $sql = 'INSERT INTO T_Klausurergebnisse (Lehrer, Datum, Fach, Abteilung, Klasse, Schuljahr,'.
         "Einser,Zweier,Dreier,Vierer,Fuenfer,Sechser,Dauer) VALUES ('".
         mysql_real_escape_string($_REQUEST['Lehrer'])."','".
         date('Y-m-d',strtotime($_REQUEST['Datum']))."','".
         mysql_real_escape_string($_REQUEST['Fach'])."','".
         mysql_real_escape_string($_REQUEST['Abteilung'])."','".
         mysql_real_escape_string($_REQUEST['Klasse'])."','".
         mysql_real_escape_string($_REQUEST['Schuljahr'])."',".$_REQUEST['Einser'].','.
         $_REQUEST['Zweier'].','.$_REQUEST['Dreier'].','.$_REQUEST['Vierer'].','.
         $_REQUEST['Fuenfer'].','.$_REQUEST['Sechser'].','.$_REQUEST['Dauer'].')';
         if ( ! mysql_query($sql,$db) ) echo '<div class="Fehler">Fehler: '.mysql_error($db).'</div>';
         else '&gt;&gt;&gt; Datensatz geschrieben.<br />';
         $_REQUEST['Klausur_id'] = mysql_insert_id($db);
         $Message = "Es wurde ein neues Klausurergebnis eingetragen.\n".
           "Von ".$_REQUEST["Lehrer"]."\n".
           "Klasse: ".$_REQUEST["Klasse"]." (".
             $dieAbteilungen->getAbteilung($_REQUEST["Abteilung"]).")\n".
           "Fach : ".$_REQUEST["Fach"]."\n".
           "Datum der Klausur: ".$_REQUEST["Datum"]."\n".
           "Dauer: ".$_REQUEST["Dauer"]." min\n".
           "1: ".$_REQUEST["Einser"]."\n".
           "2: ".$_REQUEST["Zweier"]."\n".
           "3: ".$_REQUEST["Dreier"]."\n".
           "4: ".$_REQUEST["Vierer"]."\n".
           "5: ".$_REQUEST["Fuenfer"]."\n".
           "6: ".$_REQUEST["Sechser"]."\n".
           "Schnitt: ".Durchschnitt($_REQUEST)."\n";          
           $Anzahl = Teilnehmeranzahl($_REQUEST);
           if ( substr($_REQUEST["Klasse"],0,2) != "OG" && $Anzahl > 0 &&
              $Anzahl < ($_REQUEST["Fuenfer"]+$_REQUEST["Sechser"])*3 )
             $Message .= "\nMehr als 1/3 unterm Strich! Genehmigung erforderlich!\n\n";
           $Message .= "Zur Kenntnisnahme: https://lehrer.oszimt.org/Klausur/Klausurergebnisse.php?Klausur_id=".$_REQUEST["Klausur_id"].
           "\n\nWebGroup OSZ IMT\n".date("d.m.Y H:i");
         foreach ( $dieAbteilungen->getEmpfaenger($_REQUEST['Abteilung']) as $e)
         if ( ! mail($e,'[OSZIMT] Klausurergebnis eingetragen',
           $Message, 'From: '.$_SERVER['REMOTE_USER'].'@oszimt.de',
           '-f'.$_SERVER['REMOTE_USER'].'@oszimt.de') ) echo "Fehler beim Mailversenden";
    } // Klausur noch nicht existent
  }
  if ( is_numeric($_REQUEST["Klausur_id"]) )
  {
    $query = mysql_query("SELECT *, DATE_FORMAT(Aenderung,'%d.%m.%Y %H:%i') AS LA FROM T_Klausurergebnisse WHERE Klausur_id = ".
      $_REQUEST["Klausur_id"],$db);
    if ( ! $Klausur = mysql_fetch_array($query) ) echo "Fehler: ".mysql_error($db);
    mysql_free_result($query);
  }
} // isset Klausur_id
if ( ! isset($Klausur) )
{
  if ( ! isset($_REQUEST['Lehrer']) || trim($_REQUEST['Lehrer']) == '') 
    $Klausur['Lehrer'] = $_SERVER['REMOTE_USER'];
  else	 
    $Klausur['Lehrer'] = $_REQUEST['Lehrer'];
  $Klausur['Datum'] = date('Y-m-d');
  $Klausur['Einser'] = 0;
  $Klausur['Zweier'] = 0;
  $Klausur['Dreier'] = 0;
  $Klausur['Vierer'] = 0;
  $Klausur['Fuenfer'] = 0;
  $Klausur['Sechser'] = 0;
  $Klausur['Dauer'] = 90;
  $Klausur['Fach'] = '';
  if ( !isset($_REQUEST['Klasse']))
  {
    $Klausur['Klasse'] = '';
    $Klausur['Schuljahr'] = Schuljahr(false);
    $Klausur['Abteilung'] = '';
  }
  else
  {
    $Klausur['Klasse'] = $_REQUEST['Klasse'];
  }
  if ( isset($_REQUEST['Schuljahr']))
  {
  	$Klausur['Schuljahr'] = $_REQUEST['Schuljahr'];
  }
  if ( isset($_REQUEST['Abteilung']))
  {
  	$Klausur['Abteilung'] = $_REQUEST['Abteilung'];
  }
  if ( isset($_REQUEST['Fach']))
  {
  	$Klausur['Fach'] = $_REQUEST['Fach'];
  }
  echo '<tr><td><div class="Hinweis">Neue Meldung</div></td></tr>';
  $Neu = true;
}
else
  $Neu = false;

  $check = '';
  $checks = '';
  if ( isset($Klausur['Kenntnisnahme']) && trim($Klausur['Kenntnisnahme']) != '' )
  {
    $check = ' readonly="readonly"';
    $checks = ' disabled="disabled"';
  }
?>
<tr><td>
<form action="<?=$_SERVER["PHP_SELF"]?>" name="Eingabe" method="post">
<input type="hidden" name="Klausur_id" value="<?=$Klausur["Klausur_id"]?>" />
<input type="hidden" name="Wer" value="<?=$Klausur["Kenntnisnahme"]?>" />
<table cellpadding="5">
<tr>
 <td><label for="lehrer">Lehrer/in</label></td>
 <td><input id="lehrer" type="Text" name="Lehrer" value="<?=$Klausur["Lehrer"]?>"
 size="21" maxlength="20" <?=$check?> /> </td>
  <td><label for="abteilung">Abteilung</label></td>
 <td>
<?php
 if ( isset($Klausur["Abteilung"]) && $Klausur["Abteilung"] != 99 && is_numeric($Klausur["Abteilung"]) )
   echo '<strong>'.$dieAbteilungen->getAbteilung($Klausur["Abteilung"]).'</strong>';
 else
 {
   if ( isset($Klausur["Abteilung"]) && $Klausur["Abteilung"] == 99 )
   {
     echo '<select id="abteilung" name="Abteilung">';
     echo '<option value = "1">I</option>';
     echo '<option value = "2">II</option>';   
     echo '<option value = "3">III</option>';   
     echo '<option value = "4">IV</option>';
     echo '</select>';   
   }
   else
     echo '<em>(automatisch ermittelt)</em>';
     //echo $Klausur["Abteilung"];
 }
 ?>
 </td>
 <td colspan="2">
 <?php if ( isset($Klausur["Klausur_id"]) && is_numeric($Klausur["Klausur_id"]))
    echo 'Letzte Änderung: '.$Klausur["LA"];
 ?>
 </td>
 </tr>
<tr>
 <td><label for="klasse">Klasse</label> </td>
 <td>
 <?php
 if ( trim($Klausur["Klasse"]) != "" )
 {
   echo '<input type="Text" name="Klasse" value="'.$Klausur["Klasse"].'" ';
   if ( !$dieAbteilungen->isAbteilungsleitung() )
     echo 'readonly="readonly"';
   echo ' />';
 }
 else
 {
   echo '<select id="klasse" name="Klasse" '.$checks;
   echo ">";
   $Anzahl = 0;
   $query = mysql_query("SELECT DISTINCT Klasse FROM T_StuPla ORDER BY Klasse",$db);
   while ( $art = mysql_fetch_row($query) )
   {
     echo '<option';
     if ( $Klausur["Klasse"] == $art[0] ) 
       echo  ' selected="selected"';
     echo '>'.$art[0].'</option>';
   }
   mysql_free_result($query);
   // Klassen von Außerhalb
   $query = mysql_query(" SELECT Klasse FROM T_Auswaertsklassen ORDER BY Klasse",$db);
   while ( $art = mysql_fetch_row($query) )
   {
     echo '<option';
     if ( $Klausur["Klasse"] == $art[0] ) echo  ' selected="selected"';
     echo '>'.$art[0].'</option>';
   }
   mysql_free_result($query);
   echo '</select>';
 }
 ?>
 <br />
 <?php
 if ( trim($Klausur['Klasse']) == '' )
  echo 'Im Kurssystem bitte Abi-Jahrgang (OG x) wählen!';
 ?>
  </td>
  <td><label for="schuljahr">Schuljahr</label> </td>
 <td><select id="schuljahr" name="Schuljahr" size="1" <?=$checks?>>
<? if ( isset($Klausur["Schuljahr"]))
     echo '<option selected="selected">'.$Klausur["Schuljahr"].'</option>';
  $sj1 = Schuljahr(false);
  $sj2 = Schuljahr(false, strtotime("-6 month"));
  $sj3 = Schuljahr(false, strtotime("+6 month"));
  if ( $Klausur["Schuljahr"] != $sj2 )
    echo '<option>'.$sj2.'</option>';
  if ( ! isset($Klausur["Schuljahr"]) )
    echo '<option selected="selected">'.$sj1.'</option>';
  elseif ( $Klausur["Schuljahr"] != $sj1 )
    echo '<option>'.$sj1.'</option>';
  if ( $Klausur["Schuljahr"] != $sj3 )
    echo '<option>'.$sj3.'</option>';
?>
</select></td>
<?php
if ( isset($Klausur["Klausur_id"]) && is_numeric($Klausur["Klausur_id"]))
{
  echo '<td colspan="2">';
  $sql = "SELECT Count(*) FROM T_Klausurergebnisse WHERE Klasse = '".
    $Klausur["Klasse"]."' AND Schuljahr = '".$Klausur["Schuljahr"]."' AND Fach = '".
    $Klausur["Fach"]."' AND Datum < '".$Klausur["Datum"]."'";
  $query = mysql_query($sql,$db);
  $Anz = mysql_fetch_row($query);
  mysql_free_result($query);
  echo ($Anz[0]+1).'. Klausur im Halbjahr</td>';
}
echo '</tr>';

if ( trim($Klausur['Klasse']) != '' )
{
  if ( isset($Klausur['Fach']) && trim($Klausur['Fach']) == '' )
  {
  	// Fach feststellen
    $Lehrername = new Lehrer($Klausur['Lehrer'],LEHRERID_EMAIL);
    //$Lehrer = explode(',',$Lehrername);
    //if ( Count($Lehrer) == 2 )
    //  $Kuerzel = LehrerToKuerzel($Lehrer[0],$Lehrer[1],$db);
    //else
    //  $Kuerzel = $Lehrer[0];
    $sql = 'SELECT DISTINCT Fach FROM T_StuPla WHERE Lehrer="'.$Lehrername->Kuerzel.'"'.
      ' AND Klasse="'.$Klausur['Klasse'].'" AND Fach NOT LIKE "%_L" AND Fach NOT LIKE "????L"';
    $query = mysql_query($sql);
    if ( $fach = mysql_fetch_array($query) )
      $Klausur['Fach'] = $fach[0];
    mysql_free_result($query);
  }
  echo '<tr><td><label for="fach">Fach/Kurs</label></td><td><select id="fach" ';
  echo 'name="Fach" size="1" '.$checks.'>';
  $query = mysql_query("SELECT DISTINCT Fach FROM T_StuPla WHERE Klasse='".
    $Klausur["Klasse"]."' ORDER BY Fach", $db);
  $Vorhanden = false;
  while ( $fach = mysql_fetch_array($query) )
  {
    echo '<option ';
    if ( isset($Klausur["Fach"]) && $fach["Fach"] == $Klausur["Fach"] ) 
    {
    	echo ' selected="selected"';
    	$Vorhanden = true;
    }
    echo '>'.$fach["Fach"]."</option>\n";
  }
  mysql_free_result($query);
  if ( ! $Vorhanden )
    echo '<option selected="selected">'.$Klausur["Fach"]."</option>\n";
  ?>
  </select><br /><span class="">im Kurssystem bitte den richtigen Kurs auswählen!</span>
  </td><td><label for="termin">Termin der Klausur</label></td><td>
  <input id="termin" type="Text" name="Datum"
  value="<?=date("d.m.Y",strtotime($Klausur["Datum"]))?>"
  size="10" maxlength="10"
  <?php
  if ( !isset($Klausur["Kenntnisnahme"]) || ! $Klausur["Kenntnisnahme"] )
  {
  ?>
  onClick="popUpCalendar(this,Eingabe['termin'],'dd.mm.yyyy')"
  onBlur="autoCorrectDate('Eingabe','termin' , false )"
  <?php
  }
  ?>
  <?=$check?>>(tt.mm.jjjj)
  </td><td><label for="dauer">Dauer in Minuten</label></td><td>
  <input type="Text" name="Dauer" id="dauer"
  value="<?=$Klausur["Dauer"]?>" size="3" maxlength="3" <?=$check?>>
  </td></tr>
  </table> <br />
  <table>
  <tr><td>
  <table class="Liste">
  <tr><th colspan="8">Anzahl der Noten</th></tr>
  <tr>
   <td align="center">1 </td>
   <td align="center">2 </td>
   <td align="center">3 </td>
   <td align="center">4 </td>
   <td align="center">5 </td>
   <td align="center">6 </td>
   <td align="center"> &Oslash; </td>
   <td align="center">Teilnehmer</td>
  </tr>
  <tr>
   <td><input type="Text" name="Einser" value="<?=$Klausur["Einser"]?>"
   size="2" maxlength="2" <?=$check?>></td>
   <td><input type="Text" name="Zweier" value="<?=$Klausur["Zweier"]?>"
   size="2" maxlength="2" <?=$check?>> </td>
   <td><input type="Text" name="Dreier" value="<?=$Klausur["Dreier"]?>"
   size="2" maxlength="2" <?=$check?>> </td>
   <td><input type="Text" name="Vierer" value="<?=$Klausur["Vierer"]?>"
   size="2" maxlength="2" <?=$check?>> </td>
   <td><input type="Text" name="Fuenfer" value="<?=$Klausur["Fuenfer"]?>"
   size="2" maxlength="2" <?=$check?>> </td>
   <td><input type="Text" name="Sechser" value="<?=$Klausur["Sechser"]?>"
   size="2" maxlength="2" <?=$check?>> </td>
  <td>
   <?php
     echo Durchschnitt($Klausur);
     $Anzahl = Teilnehmeranzahl($Klausur);
     echo '</td><td align="center">'.$Anzahl;
  ?>
  </td>
  </tr>
  </table>
  </td><td>
  <?php
    if ( ! $Neu )
    {
      echo 'Kenntnisnahme durch die Abteilungsleitung: ';
      if ($Klausur["Kenntnisnahme"] == "" )
      {
        echo 'noch nicht erfolgt';
        if ( $dieAbteilungen->isAbteilungsleitung() )
          echo ', <a href="'.$_SERVER["PHP_SELF"].'?Klausur_id='.$_REQUEST["Klausur_id"].
            '&Kenntnisnahme=1">Abteilungsleiter: klicken wenn ok</a>';
      }
      if ($Klausur["Kenntnisnahme"] != "" )
      {
        echo '<br /><em>'.$Klausur["Kenntnisnahme"].'</em>';
        if ( substr($Klausur["Kenntnisnahme"],0,strlen($_SERVER["REMOTE_USER"])+1) ==
          $_SERVER["REMOTE_USER"]." " || $dieAbteilungen->isAbteilungsleitung()
          )
           echo '&nbsp;&nbsp;<a href="'.$_SERVER["PHP_SELF"].'?Klausur_id='.$_REQUEST["Klausur_id"].
         '&E=1">Entfernen</a>';
      }
    }
    echo '</td></tr>';
  } // Klasse vorhanden
  echo '</table><br />';
  if ( $Anzahl > 0 && $Anzahl < ($Klausur["Fuenfer"]+$Klausur["Sechser"])*3 )
  {
    echo '<span class="home-content-titel">Mehr als 1/3 unterm Strich! ';
    if ( substr($Klausur["Klasse"],0,2) != "OG" )
    {
      echo '<span class="home-content-titel">Die Klausur ist genehmigungspflichtig! ';
      echo '<a href="Wertungsformular.php?Klausur_id='.$Klausur["Klausur_id"].
        '" target="_blank">Formular zur Genehmigung</a>';
    }
    echo '</span><br /><br />';
  }
  if ( !isset($Klausur["Kenntnisnahme"]) || trim($Klausur["Kenntnisnahme"]) == "" )
  {
    if ( $dieAbteilungen->isAbteilungsleitung() && is_numeric($Klausur["Klausur_id"]) )
      echo '<a href="'.$_SERVER["PHP_SELF"].'?DelKlausur='.$Klausur["Klausur_id"].
        '">Klausurergebnis unwiderruflich löschen</a><br /><br />';
    echo '<input type="Submit" value="';
    if( $Neu && $Klausur["Klasse"] != "" )
      echo 'Speichern';
    elseif ( $Klausur["Klasse"] == "" )
      echo 'Weiter';
    else
    {
      echo 'diese Klausur aktualisieren';
      if ( $Klausur["Lehrer"] != $_SERVER["REMOTE_USER"] 
           && ! $dieAbteilungen->isAbteilungsleitung() 
           && ! $_SERVER["REMOTE_USER"] == "GRIEP") 
        echo '" disabled="disabled';
    }
    echo '" />';
  }
  echo "</form>\n";
  if ( $Klausur["Klasse"] != "" )
  {
    echo '<form action="'.
      $_SERVER["PHP_SELF"].'"><input type="submit" value="Neues Klausurergebnis eingeben" />';
    echo '&nbsp;&nbsp;&nbsp;<a href="Klausuranzeigen.php?Abteilung='.
      $Klausur["Abteilung"].'&Schuljahr='.$Klausur["Schuljahr"].'">';
    echo 'zur Liste der aktuellen Klausuren von '.$dieAbteilungen->getAbteilung($Klausur["Abteilung"])."</a>";
    echo '</form>';
  }

?>
</td></tr>
<tr><td><hr /></td></tr><tr><td>
<h2>Vorhandene Klausurergebnisse von <?=$_SERVER["REMOTE_USER"]?></h2></td></tr><tr><td>
<?
 $query = mysql_query("SELECT * FROM T_Klausurergebnisse WHERE Lehrer = '".
   $_SERVER["REMOTE_USER"]."' ORDER BY Datum DESC",$db);
 while ( $Klausur = mysql_fetch_array($query) )
 {
   echo '<a href="'.$_SERVER["PHP_SELF"].'?Klausur_id='.$Klausur["Klausur_id"].'">'.
     $Klausur["Klasse"]." - ".$Klausur["Fach"]." vom ".$Klausur["Datum"]."</a>";
   if ( $Klausur["Kenntnisnahme"] != "" )
     echo ' ('.$Klausur["Kenntnisnahme"].")";
   else
     echo ' - Abteilungsleitung hat noch keine Kenntnis genommen';
   echo "<br />";
 }
 if ( mysql_num_rows($query) == 0 ) echo "-- keine --";
 mysql_free_result($query);
 echo '</td></tr>';
 include("include/footer.inc.php");
?>
