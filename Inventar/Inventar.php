<?php
/**
 * (c) 2006 Christoph Griep
 * Maske für Inventarisierungssystem
 */
DEFINE('USE_KALENDER',1);
$Ueberschrift = 'Inventar eingeben';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszimt.css">';
include('include/header.inc.php');
include('inventar.inc.php');
?>
<script language="javascript" src="ajax.js">
</script>
<script language="javascript">
function handleAJAXError( xmlHttp, intID )
{
    // hide image that indicates search processing
    //hideElement( "searchanim" );
    alert( "Leider ist ein Fehler beim Übersetzen des Textes aufgetreten." ) ;
}
// process data from server
function processData( xmlHttp, intID )
{
  // process text data
  document.getElementById('Inventarnummer').value = xmlHttp.responseText;  
}
// Aufruf der AJAX-Funktion 
function getInventarNr( )
{
    var strURL = "holeNeueInventarNr.php"; 
    //data,  + objSelect.options[ objSelect.options.selectedIndex ].value + ".txt";
    var inhalt = document.getElementById('Inventarnummer').value;
    if ( inhalt == '' )
    {
      inhalt = 'Art='+document.getElementById('Art').value;
    }
    else
    {
      inhalt = 'INr='+inhalt;
    }
    sendRequest( strURL, inhalt );
}
</script>
<?php

echo '<tr><td>';

if ( isset($_REQUEST['ip']) )
{
  $inv = $_REQUEST['ip'];
  // Prüfen, ob die Inventarnummer eindeutig ist!!  
  if ( isset($inv['Inventar_id']) && 
        istInventarNrVergeben($inv['Inventar_Nr'], $inv['Inventar_id']) ||
        !isset($inv['Inventar_id']) &&
        istInventarNrVergeben($inv['Inventar_Nr']) )
  {
    echo '<div class="Fehler">Die Inventarnummer '.$inv['Inventar_Nr'].' ist bereits vergeben!</div>';
    $inv['Inventar_Nr'] = '';
  }
         
  $inv['Anschaffungskosten'] = str_replace(',','.',$inv['Anschaffungskosten']);  
   
  if ( isset($inv['Inventar_id']) && is_numeric($inv['Inventar_id']) )
  {
    // update
    $sql = 'UPDATE T_Inventar SET ';
    $sql .= "Bezeichnung='".mysql_real_escape_string($inv["Bezeichnung"])."',";
    $sql .= "Inventar_Nr='".mysql_real_escape_string($inv["Inventar_Nr"])."',";
    $sql .= "Bemerkung='".mysql_real_escape_string($inv["Bemerkung"])."',";
    $sql .= "Seriennummer='".mysql_real_escape_string($inv["Seriennummer"])."',";
    $sql .= "F_Art_id=".$inv["F_Art_id"].",";
    $sql .= "F_Raum_id=".$inv["F_Raum_id"].",";
    $datum = explode(".", $inv["Anschaffungsdatum"]);
    if ( Count($datum) == 3 && checkdate($datum[1],$datum[0],$datum[2]) )
      $sql .= "Anschaffungsdatum=".mktime(0,0,0,$datum[1],$datum[0],$datum[2]).",";
    else
      $sql .= "Anschaffungsdatum=0,";
    if ( Count($datum) == 3 && checkdate($datum[1],$datum[0],$datum[2]) )
      $sql .= "Gewaehrleistung=".mktime(0,0,0,$datum[1],$datum[0],$datum[2]).",";
    else
      $sql .= "Gewaehrleistung=0,";
    if ( is_numeric($inv["Herstellungsjahr"]) )
      $sql .= "Herstellungsjahr=".addslashes($inv["Herstellungsjahr"]).",";
    else if ( is_numeric($datum[2]) )
      $sql .= "Herstellungsjahr=".$datum[2].",";
    $datum = explode(".", $inv["Entsorgungsdatum"]);
    if ( Count($datum) == 3 && checkdate($datum[1],$datum[0],$datum[2]) )
      $sql .= "Entsorgungsdatum=".mktime(0,0,0,$datum[1],$datum[0],$datum[2]).",";
    else
      $sql .= "Entsorgungsdatum=0,";
    if ( is_numeric($inv["Anschaffungskosten"]) )
      $sql .= "Anschaffungskosten=".$inv["Anschaffungskosten"].",";
    else
      $sql .= "Anschaffungskosten=0,";
    $sql.="Rechnungsnummer='".addslashes($inv["Rechnungsnummer"])."',";
    $sql .= "Bearbeiter='".$_SERVER["REMOTE_USER"]."',";
    $sql .= "F_Lieferant_id=".$inv["F_Lieferant_id"];
    $sql .= " WHERE Inventar_id=".$inv["Inventar_id"];
  }
  else
  {
    // insert
    $sql = "INSERT INTO T_Inventar (Bezeichnung, Bemerkung, Seriennummer, ";
    $sql .= "Anschaffungsdatum, Herstellungsjahr, Entsorgungsdatum, Anschaffungskosten,";
    $sql .= "Rechnungsnummer, Bearbeiter, F_Lieferant_id,Gewaehrleistung,F_Raum_id,".
      "F_Art_id, Inventar_Nr) VALUES (";
    $sql .= "'".mysql_real_escape_string($inv["Bezeichnung"])."',";
    $sql .= "'".mysql_real_escape_string($inv["Bemerkung"])."',";
    $sql .= "'".mysql_real_escape_string($inv["Seriennummer"])."',";
    $datum = explode(".", $inv["Anschaffungsdatum"]);
    if ( Count($datum) == 3 && checkdate($datum[1],$datum[0],$datum[2]) )
      $sql .= mktime(0,0,0,$datum[1],$datum[0],$datum[2]).",";
    else
      $sql .= "0,";
    if ( is_numeric($inv["Herstellungsjahr"]) )
      $sql .= mysql_real_escape_string($inv["Herstellungsjahr"]).",";
    else if ( is_numeric($datum[2]) )
      $sql .= $datum[2].",";
    else
      $sql .= "0,";
    $datum = explode(".", $inv["Entsorgungsdatum"]);
    if ( Count($datum) == 3 && checkdate($datum[1],$datum[0],$datum[2]) )
      $sql .= mktime(0,0,0,$datum[1],$datum[0],$datum[2]).",";
    else
      $sql .= "0,";
    if ( is_numeric($inv["Anschaffungskosten"]) )
      $sql .= $inv["Anschaffungskosten"].",";
    else
      $sql .= "0,";
    $sql.="'".mysql_real_escape_string($inv["Rechnungsnummer"])."',";
    $sql .= "'".$_SERVER["REMOTE_USER"]."',";
    $sql .= $inv["F_Lieferant_id"].",";
    $datum = explode(".", $inv["Gewaehrleistungsdatum"]);
    if ( Count($datum) == 3 && checkdate($datum[1],$datum[0],$datum[2]) )
      $sql .= mktime(0,0,0,$datum[1],$datum[0],$datum[2]).",";
    else
      $sql .= "0,";
    $sql .= $inv["F_Raum_id"].",";
    $sql .= $inv["F_Art_id"].",'";
    $sql .= $inv["Inventar_Nr"]."'";
    $sql .= ")";
  }
  if ( ! mysql_query($sql) )
    echo '<div class="Fehler">&gt;&gt;&gt; Fehler '.$sql.': '.mysql_error().'</div>';
  if ( isset($inv["Inventar_id"]) && is_numeric($inv["Inventar_id"]) )
    $_REQUEST['id'] = $inv['Inventar_id'];
  else
  {
    $_REQUEST['id'] = mysql_insert_id();
    if ( $inv['F_Art_id'] == 4 )
    {
      // Bücher
      mysql_query('INSERT INTO T_Inventardaten (F_Art_id,F_Inventar_id) VALUES (6,'.
        $_REQUEST['id'].'),(7,'.
        $_REQUEST['id'].'),(8,'.
        $_REQUEST['id'].')');
    }
    if ( $inv['F_Art_id'] == 1 )
    {
      // Computer
      mysql_query('INSERT INTO T_Inventardaten (F_Art_id,F_Inventar_id) VALUES (5,'.
        $_REQUEST['id'].'),(1,'.
        $_REQUEST['id'].')');
    }
    if ( $inv['F_Art_id'] == 7 )
    {
      // Software
      mysql_query('INSERT INTO T_Inventardaten (F_Art_id,F_Inventar_id) VALUES (2,'.
        $_REQUEST['id'].')');
    }
  }  
}
if ( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) )
{
  // prüfen ob Zusatzdaten gespeichert werden sollen
  if ( isset($_REQUEST['daten']) )
  {
    $daten = $_REQUEST['daten'];
    foreach( $daten as $key => $value )
    {
      if ( is_numeric($key) )
      {
        $sql = '';
        if ( $key < 0 && trim($value['Inhalt']) != '' )
        {
          // neuer Eintrag
          $sql = 'INSERT INTO T_Inventardaten (F_Inventar_id, F_Art_id, Bemerkung, Inhalt) VALUES (';
          $sql .= $_REQUEST['id'].',';
          $sql .= $value['F_Art_id'].',"';
          $sql .= mysql_real_escape_string(trim($value['Bemerkung'])).'","';
          $sql .= mysql_real_escape_string(trim($value['Inhalt'])).'")';
        }
        else
        {
          if ( isset($value['l']) && $value['l'] == 'v' )
          {
            // löschen
            $sql = 'DELETE FROM T_Inventardaten WHERE F_Inventar_id='.$_REQUEST['id'];
            $sql .= ' AND Daten_id='.$key;
          }
          else
          {
            // update
            $sql = 'UPDATE T_Inventardaten SET F_Art_id='.$value['F_Art_id'];
            $sql .= ",Bemerkung='".mysql_real_escape_string(trim($value["Bemerkung"]))."',";
            $sql .= "Inhalt='".mysql_real_escape_string(trim($value["Inhalt"]))."' WHERE Daten_id=";
            $sql .= $key;
          }
        }
        if ( $sql != '' )
          if ( ! mysql_query($sql) )
          {
            echo '<div class="Fehler">&gt;&gt;&gt; Fehler '.$sql.': '.mysql_error().'</div>';            
          }
      }
    }
  } // isset Daten
  if ( isset($_REQUEST['rep']) )
  {
    $daten = $_REQUEST['rep'];
    foreach( $daten as $key => $value )
    {
      if ( is_numeric($key) )
      {
        $sql = '';
        if ( ! is_numeric($value['Kosten']) ) $value['Kosten'] = 0;
        $datum = explode('.', $value['Datum']);
        if ( Count($datum) == 3 && checkdate($datum[1],$datum[0],$datum[2]) )
          $value['Datum'] = mktime(0,0,0,$datum[1],$datum[0],$datum[2]);
        else
          $value['Datum'] = 0;
        if ( $key < 0 && trim($value['Grund']) != '' )
        {
          // neuer Eintrag
          $sql = 'INSERT INTO T_Reparaturen (F_Inventar_id, Grund, Bemerkung, Kosten, '.
            'F_Status_id,Datum) VALUES (';
          $sql .= $_REQUEST['id'].",'";
          $sql .= mysql_real_escape_string($value["Grund"])."','";
          $sql .= mysql_real_escape_string(trim($value["Bemerkung"]))."',";
          $sql .= $value["Kosten"].",".$value["F_Status_id"].",".$value["Datum"].")";
        }
        else
        {
          // update
          $sql = "UPDATE T_Reparaturen SET F_Status_id=".$value["F_Status_id"];
          $sql .= ",Bemerkung='".mysql_real_escape_string(trim($value["Bemerkung"]))."',";
          $sql .= " Kosten=";
          $sql .= $value["Kosten"]." WHERE Reparatur_id=";
          $sql .= $key;
        }
        if ( $sql != "" )
          if ( ! mysql_query($sql) )
          {
            echo '<div class="Fehler">&gt;&gt;&gt; Fehler '.$sql.': '.mysql_error().'</div>';
          }
      }
    }
  } // isset Reparatur
  // Auswahl
  $query = mysql_query('SELECT * FROM T_Inventararten ORDER BY Art');
  $Arten = array();
  while ( $art = mysql_fetch_array($query) )
  {
    $Arten[$art['Art_id']] = $art['Art'];
  }
  mysql_free_result($query);
  $query = mysql_query('SELECT * FROM T_Reparaturstatus ORDER BY Status');
  $Status = array();
  while ( $art = mysql_fetch_array($query) )
  {
    $Status[$art['Status_id']] = $art['Status'];
  }
  mysql_free_result($query);
  $query = mysql_query('SELECT * FROM T_Inventardatenarten ORDER BY Art');
  $DatenArten = array();
  while ( $art = mysql_fetch_array($query) )
  {
    $DatenArten[$art['Art_id']] = $art['Art'];
  }
  mysql_free_result($query);
  $query = mysql_query('SELECT * FROM T_Inventar WHERE Inventar_id = '.$_REQUEST['id']);
  if ( ! $inventar = mysql_fetch_array($query) )
    unset($inventar);
  mysql_free_result($query);
  // Kopie erstellen?
  if ( isset($_REQUEST['Kopie'] ) )
  {
    $inventar['Inventar_id'] = -1;
    while ( strlen($inventar['Inventar_Nr']) > 0 &&
            is_numeric(substr($inventar['Inventar_Nr'],strlen($inventar['Inventar_Nr'])-1)) )
          $inventar['Inventar_Nr'] = substr($inventar['Inventar_Nr'], 0,
            strlen($inventar['Inventar_Nr'])-1);
    $inventar['Inventar_Nr'] = '';
    if ( $inventar['F_Art_id'] != 4 ) // kein Buch
      $inventar['Seriennummer'] = '';
  }
  echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post" name="Inv" class="Formular">';
  echo '<label>Bezeichnung</label>';
  echo '<input type="Text" name="ip[Bezeichnung]" value="';
  if ( isset($inventar['Bezeichnung']))
    echo stripslashes($inventar['Bezeichnung']);
  echo '" size="60" maxlength="100" />';
  echo '<br />';
  echo '<label for="Art">Art</label>';
  echo '<select name="ip[F_Art_id]" id="Art">';
  foreach ( $Arten as $key => $value )
  {
    echo '<option value="'.$key.'"';
    if ( isset($inventar['F_Art_id']) && $inventar['F_Art_id'] == $key )
      echo ' selected="selected"';
    echo '>'.$value."</option>\n";
  }
  echo '</select>';
  echo '<br/>';
  echo '<label for="Standort">Standort</label>';
  echo '<select name="ip[F_Raum_id]" id="Standort">';
  $Raumname = "";
  $query = mysql_query("SELECT Raum_id, Raumnummer, Raumbezeichnung FROM T_Raeume ORDER BY Raumnummer");
  while ( $raum = mysql_fetch_array($query) )
  {
    echo '<option value="'.$raum["Raum_id"].'"';
    if ( isset($inventar["F_Raum_id"]) && $inventar["F_Raum_id"] == $raum["Raum_id"] )
    {
      echo ' selected="selected"';
      $Raumname = stripslashes($raum["Raumnummer"]);
    }
    echo '>'.stripslashes($raum['Raumnummer']).' ('.
      stripslashes($raum['Raumbezeichnung']).')</option>'."\n";
  }
  echo '</select>';
  mysql_free_result($query);
  if ( $Raumname != '' )
    echo ' <a href="Raeume.php?id='.$inventar['F_Raum_id'].'">'.$Raumname.' anzeigen</a>';
  echo '<br/>';
  echo '<label>Lieferant</label>';
  echo '<select name="ip[F_Lieferant_id]">';
  $query = mysql_query('SELECT * FROM T_Lieferanten ORDER BY Name');
  while ( $lieferant = mysql_fetch_array($query) )
  {
    echo '<option value="'.$lieferant['Lieferant_id'].'"';
    if ( isset($inventar['F_Lieferant_id']) && 
         $inventar['F_Lieferant_id'] == $lieferant['Lieferant_id'] )
    {
      echo 'selected="selected"';
      $Lieferant = $lieferant['Name'];
    }
    echo '>'.$lieferant['Name']."</option>\n";
  }
  mysql_free_result($query);
  echo '</select>';
  echo '&nbsp;&nbsp;<a href="Lieferant.php?id=';
  if ( isset($inventar['F_Lieferant_id'])) 
    echo $inventar['F_Lieferant_id'].'">';
  echo  $Lieferant.' bearbeiten</a>&nbsp;&nbsp;';
  echo '<a href="Lieferant.php?id=-1">Neuer Lieferant</a>';
  echo '<br/>';
  echo '<label for="Inventarnummer">Inventarnummer</label>';
  echo '<input type="Text" name="ip[Inventar_Nr]" id="Inventarnummer" value="';
  if ( isset($inventar['Inventar_Nr'])) 
    echo stripslashes($inventar['Inventar_Nr']);
  echo '" size="20" maxlength="20" />';
  if ( $inventar['Inventar_Nr'] == '')
    echo ' <a href="javascript:getInventarNr();">Neue Inventarnummer vergeben</a>';
  echo '</br>';
  echo '<label>Bemerkung</label>';
  echo '<textarea name="ip[Bemerkung]" cols="60" rows="5">';
  if ( isset($inventar['Bemerkung']))
    echo stripslashes($inventar['Bemerkung']);
  echo "</textarea>\n";
  echo '<br/>'."\n";
  echo '<label>Seriennummer</label>';
  echo '<input type="Text" name="ip[Seriennummer]" value="';
  if ( isset($inventar['Seriennummer'])) 
    echo stripslashes($inventar['Seriennummer']);
  echo '" size="50" maxlength="50" />';
  echo '<br/>';
  echo '<label>Herstellungsjahr</label>';
  echo '<input type="Text" name="ip[Herstellungsjahr]" value="';
  if ( isset($inventar['Herstellungsjahr']))
    echo $inventar['Herstellungsjahr'];
  echo '" size="4" maxlength="4" />';
  echo '<br/>';
  echo '<label>Anschaffungsdatum</label>';
  echo '<input type="Text" id="AD" name="ip[Anschaffungsdatum]" value="';
  if ( isset($inventar['Anschaffungsdatum']) && $inventar['Anschaffungsdatum'] != 0 )
    echo date('d.m.Y',$inventar['Anschaffungsdatum']);
  echo '" size="10" maxlength="10" ';
?>onClick="popUpCalendar(this,Inv['AD'],'dd.mm.yyyy')"
onBlur="autoCorrectDate('Inv','AD' , false )"
<?php
  echo ' />';
  echo '<br/>';
  echo '<label>Anschaffungskosten</label>';
  echo '<input type="Text" name="ip[Anschaffungskosten]" value="';
  if ( isset($inventar['Anschaffungskosten'])) echo $inventar['Anschaffungskosten'];
  echo '" size="10" maxlength="10" />';
  echo '<br/>';
  echo '<label>Rechnungsnummer</label>';
  echo '<input type="Text" name="ip[Rechnungsnummer]" value="';
  if ( isset($inventar['Rechnungsnummer'])) 
    echo stripslashes($inventar['Rechnungsnummer']);
  echo '" size="50" maxlength="50" />';
  echo '<br/>';
  echo '<label>Gewährleistung bis</label>';
  echo '<input type="Text" id="GD" name="ip[Gewaehrleistungsdatum]" value="';
  if ( isset($inventar['Gewaehrleistungsdatum']) && $inventar['Gewaehrleistungsdatum'] != 0 )
    echo date('d.m.Y',$inventar['Gewaehrleistungsdatum']);
  echo '" size="10" maxlength="10" ';
?>onClick="popUpCalendar(this,Inv['GD'],'dd.mm.yyyy')"
onBlur="autoCorrectDate('Inv','GD' , false )"
<?php
  echo ' />';
  echo '<br/>';
  echo '<label>Entsorgungsdatum</label>';
  echo '<input type="Text" id="ED" name="ip[Entsorgungsdatum]" value="';
  if ( isset($inventar['Entsorgungsdatum']) && $inventar['Entsorgungsdatum'] != 0 )
    echo date('d.m.Y',$inventar['Entsorgungsdatum']);
  echo '" size="10" maxlength="10" ';
  ?>onClick="popUpCalendar(this,Inv['ED'],'dd.mm.yyyy')"
onBlur="autoCorrectDate('Inv','ED' , false )"
<?php
  echo ' />';
  echo '<br/>';
  echo 'Stand ';
  if ( isset($inventar['Stand']))
    echo $inventar['Stand'].' / Bearbeiter '.$inventar['Bearbeiter'];
  else
    echo '--noch nicht gespeichert--';
  echo '<br/>';
  if ( isset($inventar['Bearbeiter']) && ! user_berechtigt($inventar['Bearbeiter']) )
    echo 'Dieses Inventar wurde nicht von Ihnen eingegeben.'.
         'Sie dürfen keine Veränderungen vornehmen.<br />';
  else
    echo '<input type="Submit" value="Speichern" />';
  echo '&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?id=-1">Neues Inventar</a> / ';
  if ( isset($inventar['Inventar_id'])) 
    echo '<a href="'.$_SERVER['PHP_SELF'].'?id='.
      $inventar['Inventar_id'].'&Kopie=1">Kopie erzeugen</a> / ';
  if ( isset($inventar) && $inventar['Inventar_id'] > 0)
  {
    echo '<input type="hidden" name="ip[Inventar_id]" value="'.$inventar['Inventar_id'];
    echo '" />';
  }  
  echo '</form>';
  // Zugehörige Informationen speichern
  if ( isset($inventar) && is_numeric($inventar['Inventar_id']) && 
       $inventar['Inventar_id'] > 0 )
  {
    echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
    echo '<input type="hidden" name="id" value="'.$inventar['Inventar_id'].'" />';
    echo '<tr><td>';
    echo '<h2>Zugehörige Informationen</h2>';
    $sql = "SELECT * FROM T_Inventardaten WHERE F_Inventar_id=".$inventar['Inventar_id'];
    $query = mysql_query($sql);
    echo '<table class="Liste">';
    echo '<tr><th></th><th>Art/Wert</th><th>Bemerkungen</th><th></th></tr>';
    while ( $daten = mysql_fetch_array($query) )
    {
      echo '<tr><td><input type="Checkbox" name="daten['.$daten["Daten_id"];
      echo '][l]" value="v" title="Eintrag löschen"/>';
      echo '</td><td><select name="daten['.$daten["Daten_id"].'][F_Art_id]" size="1">';
      reset($DatenArten);
      foreach ( $DatenArten as $key => $value )
      {
        echo '<option value="'.$key.' "';
        if ( $daten["F_Art_id"] == $key )
          echo 'selected="selected"';
        echo '">'.$value.'</option>';
      }
      echo '</select> ';
      echo "<br />\n";
      echo '<input type="Text" name="daten['.$daten["Daten_id"].'][Inhalt]" value="';
      echo stripslashes($daten['Inhalt']);
      echo '" size="40" maxlength="40" />';
      $q = mysql_query('SELECT F_Inventar_id FROM T_Inventardaten WHERE F_Art_id='.
        $daten['F_Art_id'].' AND F_Inventar_id <> '.$inventar['Inventar_id'].' AND Inhalt="'.
        $daten['Inhalt'].'"');
      if ( mysql_num_rows($q) >= 1 )
      {
        $art = mysql_fetch_row($q);
        echo '<br /><a href="Inventarliste.php?Art_id='.$daten["F_Art_id"].'&Inhalt='.
          $daten['Inhalt'].'">Liste anzeigen</a>';
      }
      mysql_free_result($q);
      echo '</td>';
      echo '<td><textarea cols="40" rows="5" name="daten[';
      echo $daten['Daten_id'].'][Bemerkung]">';
      echo stripslashes($daten['Bemerkung']);
      echo '</textarea></td></tr>';
    }
    mysql_free_result($query);
    if ( user_berechtigt($inventar['Bearbeiter']) )
    {
      echo '<tr><td>Neu:</td><td>';
      echo '<select name="daten[-1][F_Art_id]" size="1">';
      reset($DatenArten);
      foreach ( $DatenArten as $key => $value )
      {
        echo '<option value="'.$key.' "';
        echo '">'.$value.'</option>';
      }
      echo '</select><br />';
      echo '<input type="Text" name="daten[-1][Inhalt]" size="40" maxlength="40" />';
      echo '</td>';
      echo '<td><textarea name="daten[-1][Bemerkung]" cols="40" rows="5"></textarea></td></tr>';      
    }
    echo '</table>';
    echo '<input type="Submit" value="Zusatzinformationen speichern" />';
    echo '</td></tr>';
    echo '</form>';
    echo '<tr><td><hr /></td></tr>';
    // Reparaturen
    echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post" name="Reparatur">';
    echo '<input type="hidden" name="id" value="'.$inventar['Inventar_id'].'" />';
    echo '<tr><td><a id="Reparaturen" name="Reparaturen"></a>';
    $sql = "SELECT * FROM T_Reparaturen WHERE F_Inventar_id=".$inventar['Inventar_id'];
    if ( ! $query = mysql_query($sql)) echo "Fehler $sql: ".mysql_error();
    echo '<h2>Reparaturen</h2>';
    echo '<table class="Liste">';
    echo '<tr><th></th><th>Datum</th><th>Status</th><th>Grund</th><th>Kosten</th></tr>';
    if ( mysql_num_rows($query) > 0 )
    {
      while ( $daten = mysql_fetch_array($query) )
      {
        echo '<tr><td>';
        echo '</td><td>'.date("d.m.Y",$daten["Datum"]).'</td>';
        echo '<td><select name="rep['.$daten["Reparatur_id"].'][F_Status_id]">';
        foreach ( $Status as $key => $value )
        {
          echo '<option value="'.$key.'" ';
          if ( $daten["F_Status_id"] == $key )
            echo 'selected="selected"';
          echo '>'.$value.'</option>';
        }
        echo '</select></td>';
        echo '<td>'.stripslashes($daten['Grund']).'</td>';
        echo '<td align="right">';
        echo $daten["Kosten"]."</td></tr>";
        echo '<tr><td colspan="5">';
        echo 'Bemerkungen<br /><textarea cols="60" rows="5" name="rep['.$daten["Reparatur_id"].'][Bemerkung]">';
        echo stripslashes($daten["Bemerkung"]);
        echo '</textarea>';
        echo '</td></tr>';
      }
    }
    mysql_free_result($query);
    if ( user_berechtigt($inventar['Bearbeiter']) )
    {
      echo '<tr><td>Neu:</td><td>';
      echo '<input type="Text" id="RD" name="rep[-1][Datum]" value="'.date("d.m.Y");
      echo '" size="10" maxlength="10" ';
      ?>onClick="popUpCalendar(this,Reparatur['RD'],'dd.mm.yyyy')"
  onBlur="autoCorrectDate('Reparatur','RD' , false )"
  <?php
      echo ' />';
      echo '</td><td>';
      echo '<select name="rep[-1][F_Status_id]" size="1">';
      reset($Status);
      foreach ( $Status as $key => $value )
      {
        echo '<option value="'.$key.' "';
        echo '">'.$value.'</option>';
      }
      echo '</select>';
      echo '</td><td><input type="Text" name="rep[-1][Grund]" size="40" maxlength="40" />';
      echo '</td><td><input type="Text" name="rep[-1][Kosten]" size="5" maxlength="8">';
      echo '</td></tr>';
      echo '<tr><td colspan="5">Bemerkungen<br /><textarea name="rep[-1][Bemerkung]" cols="40" rows="5"></textarea></td></tr>';      
    }
    echo '</form>';
    echo '</table>';
    echo '<input type="Submit" value="Reparaturen speichern" />';
    echo '</td></tr>';
    echo '<tr><td><hr /></td></tr>';
    echo '<tr><td align="center"><a href="'.$_SERVER["PHP_SELF"];
    echo '">zur Übersichtsliste</a></td></tr>';
  }
}
else
{
  // Liste des Inventars
  $Arten = array();
  $query = mysql_query("SELECT DISTINCT Art FROM T_Inventar INNER JOIN T_Inventararten ON F_Art_id=Art_id ORDER BY Art");
  while ( $art = mysql_fetch_row($query) )
  {
    $Arten[] = $art[0];
  }
  mysql_free_result($query);
  echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post" class="Formular">';
  if ( ! isset($_REQUEST['Search'])) $_REQUEST['Search'] = '';
  echo '<label for="Inventar">Inventar</label> ' .
  		'<input type="Text" id="Inventar" name="Search" value="'.$_REQUEST['Search'].'" /> ';
  echo '<input type="Submit" value="Suchen" />';
  if ( isset($_REQUEST['Liste']) )
    echo '<input type="hidden" name="Liste" value="'.$_REQUEST['Liste'].'" />';  
  echo '</form>';    
  foreach ( $Arten as $key => $value )
    echo '[ <a href="'.$_SERVER['PHP_SELF'].'?Art='.urlencode($value).'">'.stripslashes($value).'</a> ] ';
  echo '[ <a href="'.$_SERVER['PHP_SELF'].'">Gesamtliste</a> ] <br />';
  $Param = '';
  $Search = '1';
  $Order = '';
  if ( isset($_REQUEST['Sort']) )
  {
    $Order = mysql_real_escape_string($_REQUEST['Sort']);
    $Param .= '&Sort='.urlencode($_REQUEST['Sort']);
  }
  else
    $Order = 'Bezeichnung, Inventar_Nr';
  if ( isset($_REQUEST['Liste']) )
  {
    $Param .= '&Liste='.urlencode($_REQUEST['Liste']);
    switch ( $_REQUEST['Liste'] )
    {
     case 'E':
        $Search = 'Entsorgungsdatum>0';
        break;
     case 'O':
        $Search = '(Inventar_Nr="" OR Inventar_Nr IS NULL)';
        break;
    }
  }
  if ( isset($_REQUEST['Art']) )
  {
    $Search .= ' AND Art="'.mysql_real_escape_string($_REQUEST['Art']).'"';
    $Param .= '&Art='.urlencode($_REQUEST['Art']);
  }
  if ( isset($_REQUEST['Search'] ) && $_REQUEST['Search'] != '' )
  {
    $Search .= " AND (Bezeichnung LIKE '%".mysql_real_escape_string($_REQUEST["Search"])."%' OR ".
      'Inventar_Nr="'.mysql_real_escape_string($_REQUEST['Search']).'" OR '.
      "Seriennummer LIKE '".mysql_real_escape_string($_REQUEST['Search'])."' OR Bemerkung LIKE '%".
      mysql_real_escape_string($_REQUEST['Search'])."%')";
    $Param .= "&Search=".urlencode($_REQUEST["Search"]);
  }
  if ( ! $query = mysql_query('SELECT Count(*) FROM T_Inventar INNER JOIN '.
   'T_Inventararten ON F_Art_id=Art_id WHERE '.$Search)) 
  {
  	echo '<div class="Fehler">Fehler: '.mysql_error().'</div>';
  }
  $Gesamtanzahl = mysql_fetch_row($query);
  mysql_free_result($query);
  $Gesamtanzahl = $Gesamtanzahl[0];
  $AnzahlProSeite = 25;
  if ( isset($_REQUEST["Seite"]) && is_numeric($_REQUEST["Seite"]) )
  {
    $Seite = $_REQUEST["Seite"];
  }
  else
    $Seite = 1;
  $anf = ($Seite-1)*$AnzahlProSeite;
  echo "Seite ";
  for ( $i = 1; $i <= ($Gesamtanzahl/$AnzahlProSeite)+1; $i++)
    if ( $i != $Seite )
      echo '<a href="'.$_SERVER["PHP_SELF"].'?Seite='.$i.$Param.'">'.$i.'</a> ';
      else
        echo '<strong>'.$i.'</strong> ';
  echo '<br />';
  if ( ! $query = mysql_query("SELECT * FROM ((T_Inventar INNER JOIN T_Lieferanten ".
    "ON F_Lieferant_id = Lieferant_id) INNER JOIN T_Inventararten ON F_Art_id=Art_id)".
    " INNER JOIN T_Raeume ON F_Raum_id=Raum_id WHERE $Search ".
    "ORDER BY $Order LIMIT $anf, $AnzahlProSeite")) echo "Fehler: ".mysql_error();
  echo '<table class="Liste">';
  echo '<tr><th>Inventarnr</th><th><a href="'.$_SERVER["PHP_SELF"].
    '?Sort=Bezeichnung" title="Sortieren nach Bezeichnung">';
  echo 'Bezeichnung</a></th><th><a href="'.$_SERVER["PHP_SELF"].
    '?Sort=Art" title="Sortieren nach Art">Art</a></th><th>';
  echo '<a href="'.$_SERVER["PHP_SELF"].'?Sort=Lieferant" title="Sortieren nach Lieferant">Lieferant</a></th>';
  echo '<th>Raum</th></tr>';
  while ( $inv = mysql_fetch_array($query) )
  {
    echo '<tr><td><a href="'.$_SERVER["PHP_SELF"].'?id='.$inv["Inventar_id"].'">';
    if ( trim(stripslashes($inv["Inventar_Nr"])) != "" )
      echo stripslashes($inv["Inventar_Nr"]);
    else
      echo "(unbekannt)";
    echo '</a></td><td>';
    echo stripslashes($inv["Bezeichnung"]).'</td>';
    echo '<td>'.stripslashes($inv["Art"]).'</td>';
    echo '<td><a href="Lieferant.php?id='.$inv["Lieferant_id"].'">';
    echo stripslashes($inv["Name"]).'</a></td>';
    echo '<td><a href="Raeume.php?id='.$inv["Raum_id"].'">'.
      $inv["Raumnummer"].'</a></td></tr>';
  }
  echo '<tr><td colspan="5">'.$Gesamtanzahl.' Einträge</td></tr>';
  mysql_free_result($query);
  echo '</table>';
  echo "Seite ";
  for ( $i = 1; $i <= ($Gesamtanzahl/$AnzahlProSeite)+1; $i++)
    if ( $i != $Seite )
      echo '<a href="'.$_SERVER["PHP_SELF"].'?Seite='.$i.$Param.'">'.$i.'</a> ';
      else
        echo '<strong>'.$i.'</strong> ';
  echo '<br />';
  echo '<a href="'.$_SERVER["PHP_SELF"].'?id=-1">Neues Inventar hinzufügen</a> / ';
  echo '<a href="'.$_SERVER["PHP_SELF"].'?Liste=E">Entsorgtes Inventar</a> / ';
  echo '<a href="'.$_SERVER["PHP_SELF"].'?Liste=O">unnummeriertes Inventar</a> <br />';
  foreach ( $Arten as $key => $value )
    echo '[ <a href="'.$_SERVER["PHP_SELF"].'?Art='.$value.'">'.stripslashes($value).'</a> ] ';
  echo '[ <a href="'.$_SERVER["PHP_SELF"].'">Gesamtliste</a> ] ';  
}
echo '</td></tr>';
include('include/footer.inc.php');
?>