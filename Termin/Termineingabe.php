<?php
/**
 * Eingabe von Terminen
 * (c) 2006 Christoph Griep
 * 
 */
 DEFINE('USE_KALENDER', 1);
 $Ueberschrift = 'Termin eingeben';
 include('include/header.inc.php');
 include('include/Termine.class.php');
 $Termine = new Termine($db, true);
 $Termine->CheckScriptEinfuegen();

DEFINE('BETROFFEN_OSZIMT', 21); // Id für 'alle betroffen'

 echo '<tr><td align="center"><a href="Termine.php">Zur Terminübersicht</a></td></tr>';
 echo '<tr><td align="center"><a href="Terminliste.php">Termine als Liste</a></td></tr>';
 echo '<tr><td align="center"><a href="/">Zurück zum internen Lehrerbereich</a></td></tr>';
 if ( isset($_REQUEST['DelTermin'] ) && is_numeric($_REQUEST['DelTermin']) )
 {
   // Termin löschen
   $query = mysql_query('SELECT * FROM T_Termin_Termine WHERE Termin_id='.$_REQUEST['DelTermin']);
   $termin = mysql_fetch_array($query);
   if ( $Termine->istBearbeiter($termin['Bearbeiter'],$termin['Betroffene']) )
   {
     mysql_query('DELETE FROM T_Termin_Termine WHERE Termin_id='.$_REQUEST['DelTermin']);
     echo '<tr><td><strong>Termin '.$termin['Bezeichnung'].' wurde gelöscht!</strong></td></tr>';
   }
   else
     echo '<tr><td><strong>Sie dürfen diesen Termin nicht löschen!</strong></td></tr>';
 }
 if ( isset($_REQUEST['Save'] ) )
 {
   $dat = explode('.',$_REQUEST['Datum']);
   if ( Count($dat) == 3 )
   {
     $dat[2] = trim($dat[2]);
     if ( ! is_numeric($dat[2]) )
     {
       $Uhrzeit = trim(substr($dat[2],strpos($dat[2],' ')));
       $dat[2] = trim(substr($dat[2],0,strpos($dat[2],' ')));
     }
     else 
       $Uhrzeit = '';
   }
   if ( is_array($_REQUEST['Betroffene']) )
     $_REQUEST['Betroffene'] = implode(',',$_REQUEST['Betroffene']);
   else
     $_REQUEST['Betroffene'] = BETROFFEN_OSZIMT; // OSZ IMT (alle)
   if ( Count($dat) == 3 && is_numeric($dat[2]) && is_numeric($dat[0]) && 
        is_numeric($dat[1]))
   {
     $Datum = trim($dat[2].'-'.$dat[1].'-'.$dat[0].' '.$Uhrzeit);
     $Datum = strtotime($Datum);
     if ( isset($_REQUEST['Vorlaeufig']) && $_REQUEST['Vorlaeufig']=='v' )
         $Vorlaeufig = 1;
     else
         $Vorlaeufig = 0;
     if ( isset($_REQUEST['Termin_id'] ) && is_numeric($_REQUEST['Termin_id']))
     {
       // Update
       $sql = "UPDATE T_Termin_Termine SET Bezeichnung='".
         mysql_real_escape_string($_REQUEST['Bezeichnung'])."',";
       $sql .= "Beschreibung='".mysql_real_escape_string($_REQUEST['Beschreibung'])."',";
       $sql .= 'F_Klassifikation='.$_REQUEST['Klassifikation'].',';
       $sql .= 'Datum='.$Datum.", Betroffene='";
       $sql .= $_REQUEST['Betroffene']."',";
       $sql .= 'Vorlaeufig='.$Vorlaeufig;
       $sql .= ' WHERE Termin_id='.$_REQUEST['Termin_id'];
       if ( ! mysql_query($sql) )
         echo '<div class="Fehler">Fehler beim Termineintragen: '.mysql_error().'</div>';
       else
         echo '<strong>&gt;&gt;&gt; Termin geändert.</strong><br />';
     }
     else
     {
       // Neuer Termin
       if ( trim($_REQUEST['Bezeichnung']) == '' ) $_REQUEST['Bezeichnung'] = '(unbekannt)';
       $sql = 'INSERT INTO T_Termin_Termine (Datum, Bezeichnung, Beschreibung, Bearbeiter, ';
       $sql .= 'F_Klassifikation,Betroffene,Vorlaeufig) VALUES ';
       $sql .= '('.$Datum.",'".mysql_real_escape_string($_REQUEST['Bezeichnung'])."','";
       $sql .= mysql_real_escape_string($_REQUEST['Beschreibung'])."','".
         $_SERVER['REMOTE_USER']."',";
       $sql .= $_REQUEST['Klassifikation'].",'".$_REQUEST['Betroffene']."',";
       $sql .= $Vorlaeufig.')';
       if ( ! mysql_query($sql) )
         echo '<div class="Fehler">Fehler beim Termineintragen: '.mysql_error().'</div>';
       else
       {
         $_REQUEST['Termin_id'] = mysql_insert_id();
         echo '<strong>&gt;&gt;&gt; Termin gespeichert.</strong><br />';
       }
     }
   }
   else
   {
     echo '<strong>&gt;&gt;&gt; Fehler: Falsches Datum!</strong><br />';
   }
 } // isset Save
 if ( isset($_REQUEST['Termin_id'] ) && is_numeric($_REQUEST['Termin_id']))
 {
   $query = mysql_query('SELECT * FROM T_Termin_Termine WHERE Termin_id='.$_REQUEST['Termin_id']);
   $Termin = mysql_fetch_array($query);
   mysql_free_result($query);
   $Termin['Datum'] = date('d.m.Y H:i', $Termin['Datum']);
   $Termin['Betroffene'] = explode(',',$Termin['Betroffene']);
   if ( substr($Termin['Datum'],10) == ' 00:00') $Termin['Datum'] = substr($Termin['Datum'],0,10);
   if ( ! $Termine->istBearbeiter($Termin['Bearbeiter'],implode(',',$Termin['Betroffene'])) )
   {
      unset($Termin);
   }
 }
 if ( ! isset($Termin['Betroffene']) )
   $Termin['Betroffene'] = array();
 echo '<tr><td>';
 echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post" name="Eingabe">';
 if ( isset($Termin['Termin_id']) && is_numeric($Termin['Termin_id']) )
   echo '<input type="hidden" name="Termin_id" value="'.$Termin['Termin_id'].'" />';
 echo '<table>';
 echo '<tr><td><label for="Datum">Datum (ggf. Uhrzeit)</label></td>';
 echo '<td><input type="Text" name="Datum" value="';
 if ( isset($Termin['Datum'])) 
   echo $Termin['Datum'];
 echo '" id="Datum" size="17" maxlength="16" ';
?>
onClick="popUpCalendar(this,Eingabe['Datum'],'dd.mm.yyyy')"
onBlur="autoCorrectDate('Eingabe','Datum' , false )"
 /> DD.MM.JJJJ HH:mm</td></tr>
 <?php
 echo '<tr>';
 echo '<td><label for="Bezeichnung">Titel</label></td>';
 echo '<td><input type="Text" name="Bezeichnung" id="Bezeichnung" value="';
 if ( isset($Termin['Bezeichnung']))
   echo htmlentities($Termin['Bezeichnung']);
 echo '" size="30" maxlength="30"></td>';
 echo '</tr>';
 echo '<tr>';
 echo '<td><label for="Beschreibung">Beschreibung</label></td>';
 echo '<td><textarea id="Beschreibung" name="Beschreibung" cols="60" rows="5">';
 if ( isset($Termin['Beschreibung']) ) 
   echo $Termin['Beschreibung'];
 echo '</textarea></td>';
 echo '</tr>';
 echo '<tr>';
 echo '<td>Bearbeiter</td>';
 echo '<td>'.$_SERVER['REMOTE_USER'].'</td>';
 echo '</tr>';
 echo '<tr>';
 echo '<td><label for="Klassifikation">Klassifikation</label></td>';
 echo '<td><select name="Klassifikation" id="Klassifikation">';
 foreach ( $Termine->getKlassifikationen() as $key => $value )
 {
   echo '<option value="'.$key.'" ';
   if ( isset($Termin['F_Klassifikation']) && $Termin['F_Klassifikation'] == $key ) 
     echo 'selected="selected"';
   echo '>'.$value."</option>\n";
 }
 echo '</select>';
 if ( $Termine->vorlaeufigPerson() )
 {
   echo '&nbsp;&nbsp;&nbsp;&nbsp;Vorläufiger Termin ';
   echo '<input type="Checkbox" name="Vorlaeufig" value="v" ';
   if ( isset($Termin["Vorlaeufig"]) && $Termin["Vorlaeufig"] ) 
     echo 'checked="checked"';
   echo '/>';
 }
 echo '</td>';
 echo "</tr>\n";
 echo '<tr>';
 echo '<td colspan="2">';
 echo '<table><tr><td colspan="4" align="center" class="home-content-titel">Betroffene</td>';

 $Nr = 0;
 foreach ( $Termine->getBetroffen() as $key => $value )
 {
   if ($Nr % 3 == 0)
     echo '</tr><tr>';
   echo '<td><input type="Checkbox" name="Betroffene[]" value="'.$key.'" ';
   if ( isset($Termin['Betroffene']) && in_array($key, $Termin['Betroffene']) )
     echo 'checked="checked"';
   echo '></td><td>'.$value.'</td>';
   $Nr++;
 }
 echo "</tr>\n";
 echo '</table>';
 echo '</td>';
 echo '</tr>';
 echo '<tr><td colspan="2" align="center"><input type="Submit" name="Save" value="';
 if ( isset($Termin['Termin_id']) )
   echo 'Änderungen speichern';
 else
   echo 'Termin speichern';
 echo '">';
 if ( isset($Termin['Termin_id']) )
 {
   echo '&nbsp;&nbsp;&nbsp;&nbsp;[ <a href="'.$_SERVER['PHP_SELF'].'">Neuer Termin</a> ]';
   echo '&nbsp;&nbsp;&nbsp;&nbsp;[ <a href="'.$_SERVER['PHP_SELF'].'?DelTermin='.
     $Termin['Termin_id'].
     '" onClick="javascript:return window.confirm(\'Termin wirklich löschen?\');"> '.
     'Termin löschen</a> ]';
 }
 echo '</td></tr>';
 echo '</table>';
 echo '</form>';
 echo '</td></tr>';
 echo '<tr><td align="center"><a href="Termine.php">Zur Terminübersicht</a></td></tr>';
 echo '<tr><td align="center"><a href="Terminliste.php">Termine als Liste</a></td></tr>';
 echo '<tr><td align="center"><a href="/">Zurück zum internen Lehrerbereich</a></td></tr>';
  echo '<tr><td align="center">&nbsp;</td></tr>';
  echo '<tr><td align="center"><a href="Terminkopieren.php">vorhandene Termine ins nächste Jahr kopieren</a></td></tr>';
 include('include/footer.inc.php');
?>