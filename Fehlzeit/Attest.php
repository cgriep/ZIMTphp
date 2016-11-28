<?php
include_once("include/classes/orga/model/Schuljahr.class.php");
/*
 * Attest.php
 * Erlaubt das Eingeben von Fehlzeiten �ber einen l�ngeren Zeitraum.
 * �ber die Maske k�nnen aus Sicherheitsgr�nden maximal L�ngen von 10 Tagen 
 * eingegeben werden. L�ngere Fehlzeiten m�ssen gesplittet werden.  
 * 
 * Parameter:
 * Schueler - id des Sch�lers, f�r den eine Fehlzeit eingetragen werden soll
 * Schuljahr- Schuljahr, f�r das die Fehlzeit gilt (Langform: I 2005/06)
 * Anfangsdatum - Datum, an dem die Fehlzeit beginnt (dd.mm.jjjj)
 * Enddatum - Datum, an dem die Fehlzeit endet (dd.mm.jjjj)
 * Art - Art der Fehlzeit. G�ltige Eintr�ge: " ", "-", "A", "K", "N", "P", "S" 
 * 
 * Anzeigen - wenn gesetzt wird eine Liste der vorhandenen Atteste angezeigt
 * 
 * Letzte �nderung:
 * 09.01.06 C. Griep
 * 08.02.06 C. Griep: Bugfixes, Name des Sch�lers bleibt nach Abschicken
 * 17.03.06 C. Griep - Symbol f�r Attestpflicht eingef�hrt
 * 
 * (c) 2007 Christoph Griep
 */
 DEFINE('USE_KALENDER', 1);
 $Ueberschrift = 'L�ngerfristige Fehlzeit eintragen';
 $HeaderZusatz = '<link rel="stylesheet" type="text/css" href="https://lehrer.oszimt.de/css/oszimt.css">';
 include('include/header.inc.php');
 include('include/turnus.inc.php');
 include('include/stupla.inc.php');
 include('include/Abteilungen.class.php');
 include('include/Lehrer.class.php');
 echo '<tr><td>';
 $Gruende['A'] = 'Krankheit mit Attest';
 $Gruende['K'] = 'Krankheit ohne Attest';
 $Gruende['S'] = 'Schulische Veranstaltung';
 $Gruende['P'] = 'Private Gr�nde';
 $Gruende['!'] = 'Attestpflicht';
 $Gruende['X'] = 'endg�ltiger Abgang';
    
 if ( isset($_REQUEST['Schueler']) && is_numeric($_REQUEST['Schueler']) &&
      isset($_REQUEST['Schuljahr']) )
 {
   $_REQUEST['Schuljahr'] = mysql_real_escape_string($_REQUEST['Schuljahr']);
   $d1 = explode('.',$_REQUEST['Anfangsdatum']);
   $d2 = explode('.',$_REQUEST['Enddatum']);

   if ( Count($d1) == 3 && Count($d2) == 3 )
   {
     $d1 = mktime(0,0,0,$d1[1],$d1[0],$d1[2]);
     $d2 = mktime(0,0,0,$d2[1],$d2[0],$d2[2]);
   }
   else
   {
   	$d1 = 0;
   	$d2 = 0;
   }

   if ( $d2-$d1 > 86400*30 )
     echo '<div class="Fehler">Die Tagesdifferenz ist gr��er als 30 Tage. Bitte in Einzelschritten eingeben!</div>';

   elseif ( $d1 < strtotime('-3 month') || $d2 < strtotime('-3 month') || $d2 < $d1 )
     echo '<div class="Fehler">Ung�ltige Datumsangaben!</div>';

   else
   {
   // Neueste Version des Stundenplanes feststellen
   $squery = mysql_query('SELECT * FROM T_Schueler WHERE Nr='.$_REQUEST['Schueler']);
   if ( ! $Schueler = mysql_fetch_array($squery))
   {
   	 echo '<div class="Fehler">Ung�ltige Sch�lernummer</div>';
   }
   else
   {  
     //Halbjahreshack
     $arrSchuljahr = Schuljahr::getSchuljahr('OG');
     if ( $arrSchuljahr['halbjahr'] == 2 )
       $sqlSchuljahrAdd = " OR Schuljahr='" . $arrSchuljahr['langform_I'] . "'";

     echo '<div class="Hinweis">Fehlzeiten von '.$Schueler['Vorname'].
         ' '.$Schueler['Name'].' ('.$Schueler['Klasse'].')</div>';

     $Version = getAktuelleVersion($d1);

     $sql = 'SELECT Wochentag, T_StuPla.Fach, Stunde, Turnus FROM T_Kurse INNER JOIN T_StuPla ON T_StuPla.Fach=Kurs WHERE Schueler_id=' . $_REQUEST['Schueler'] . " AND (Schuljahr='"  . mysql_real_escape_string($_REQUEST['Schuljahr']) . "'" . $sqlSchuljahrAdd . ") AND Version=";

     if (!$query = mysql_query($sql.$Version))
         echo mysql_error();
     if ( mysql_num_rows($query) == 0)
         $query= mysql_query($sql.getAktuelleVersion());

     while ( $kurs = mysql_fetch_array($query) )
     {
       for ( $i = $d1; $i<=$d2; $i = strtotime('+1 day', $i))
       {
         if ( date('w', $i) == $kurs['Wochentag'] && TurnusAktuell($kurs['Turnus'], $i, $db))
         {
           echo '<div class="Hinweis">Fehlzeit '.$_REQUEST['Art'].
             ' eintragen am '.date('d.m.Y',$i).' im '.$kurs['Stunde'].' Block</div>';

           $arrSchuljahr = Schuljahr::getSchuljahr('OG', $i);
           $strSchuljahr = $arrSchuljahr['langform'];

           if ( ! mysql_query('INSERT INTO T_Fehlzeiten (Schueler_id, Art, Datum, Block, Kurs, '.
                              'Lehrer, Schuljahr) VALUES ('.$_REQUEST['Schueler'].",'".
                              $_REQUEST['Art']."', '".
                              date('Y-m-d', $i)."', ".$kurs['Stunde'].", '".
                              $kurs['Fach']."','".$_SERVER['REMOTE_USER']."','".
                              $strSchuljahr."')",$db))
           {
           //   Attestpflicht ver�ndert nicht vorhandene Eintr�ge
             if ( $_REQUEST['Art'] != '!')
             {
               $query2 = mysql_query('SELECT Art FROM T_Fehlzeiten WHERE Schueler_id = '.
                      $_REQUEST['Schueler'].' AND Block='.$kurs['Stunde']." AND Datum='".
                    date('Y-m-d',$i)."'",$db);
               $eintrag = mysql_fetch_row($query2);
               mysql_free_result($query2);
             //     Wenn ausgefallen nicht �ndern, sonst Attest
               if ( $eintrag[0] != '-' && $eintrag[0] != $_REQUEST['Art'])
               {
                 echo '<div class="Hinweis">�ndere Fehlzeit von '.$eintrag[0].' auf '.$_REQUEST['Art'].' am '.
                   date('d.m.Y',$i).' im '.$kurs['Stunde'].' Block</div>';
                 if ( ! mysql_query("UPDATE T_Fehlzeiten SET Art = '".$_REQUEST['Art'].
                      "',Lehrer='".
                      $_SERVER['REMOTE_USER']."' WHERE Schueler_id=".$_REQUEST['Schueler'].
                      ' AND Block='.$kurs['Stunde'].' AND '.
                       "Datum='".date('Y-m-d',$i)."'",$db))
                    echo mysql_error($db);
               }
               else
                 echo '<div class="Hinweis">Stunde '.$kurs['Stunde'].' am '.
                   date('d.m.Y',$i).' fiel aus!</div>';
             } // wenn nicht Attestpflicht
           } // eintragen fehlgeschlagen         
         } // Kurs gefunden
       } // durchz�hlen der Tage
     }
     mysql_free_result($query);   
     // Benachrichtigen der betroffenen Kollegen
     $sql = 'SELECT DISTINCT Lehrer, T_StuPla.Fach FROM T_StuPla INNER JOIN '.
           'T_Kurse ON T_StuPla.Fach=Kurs WHERE Schueler_id='.$_REQUEST['Schueler'].
           " AND Version=$Version AND Schuljahr='".$_REQUEST['Schuljahr'].
           "' ORDER BY Name, Vorname";
     $query = mysql_query($sql);
     $xLehrer = new Lehrer($_SERVER['REMOTE_USER'], LEHRERID_EMAIL);
     while ( $lehrer = mysql_fetch_array($query))
     {   	
   	  $Lehrer = new Lehrer($lehrer['Lehrer'], LEHRERID_KUERZEL);
   	  $nachricht = $Lehrer->Anrede($LEHRER_ANREDE, false).",\n";
   	  $nachricht .= 'f�r die Sch�lerin/den Sch�ler '; 
   	  $nachricht .= $Schueler['Vorname'].' '.$Schueler['Name'].' ('.$Schueler['Klasse'].') ';
   	  $nachricht .= 'Ihres Kurses '.$lehrer['Fach'];
   	  $nachricht .= ' gilt vom '.date('d.m.Y',$d1).' bis '.date('d.m.Y',$d2).":\n\n";
   	  $nachricht .= $_REQUEST['Art'].' - '.$Gruende[$_REQUEST['Art']]."\n\n";
   	  $nachricht .= 'Bitte ber�cksichtigen Sie dies in Ihrem Kursbuch.'."\n\n";
   	  $nachricht .= 'mit freundlichen Gr��en'."\n";    
      $nachricht .= $xLehrer->Vorname.' '.$xLehrer->Name."\n\n";
      $nachricht .= '(automatisch erstellt am '.date('d.m.Y H:i').')';
      mail($Lehrer->Username.'@oszimt.de','[OSZIMT] Fehlzeiten von '.$Schueler['Vorname'].' '.
         $Schueler['Name'].' '.
         $Schueler['Klasse'], $nachricht, 'From: '.$xLehrer->Vorname.' '.
         $xLehrer->Name.' <'.$xLehrer->Username.'@oszimt.de>',
         '-f'.$_SERVER['REMOTE_USER'].'@oszimt.de');

     }
     mysql_free_result($query);         
   } // g�ltige Sch�lernummer
   mysql_free_result($squery);
   } // Tagesdifferenz zu gro�
 }
 echo '</td></tr>';
 if ( isset($_REQUEST['Anzeigen']) )
 {
    $arrSchuljahr = Schuljahr::getSchuljahr('OG');
    $strSchuljahr = $arrSchuljahr['langform'];
    
    echo '<tr><td>Atteste im Schuljahr ' . $strSchuljahr . '</td></tr>';
    echo '<tr><td><table class="Liste">';
    //if ( isset($_REQUEST["ZSchuljahr"]) )
    {
      $query = mysql_query('SELECT * FROM T_Fehlzeiten INNER JOIN '.
        "T_Schueler ON Schueler_id=Nr WHERE (Art='A') AND Schuljahr='$strSchuljahr' ".
        ' ORDER BY Name, Vorname, Schueler_id, Datum, Block');
      $s = 0;
      $d = '';      
      echo '<tr><th>Name, Vorname</th><td>Datum';
      while ( $fz = mysql_fetch_array($query) )
      {
        if ( $s != $fz['Schueler_id'] )
        {
          echo '</td></tr><tr><td valign="top">'.$fz['Name'].', '.$fz['Vorname'].
            ' ('.$fz['Tutor'].')</td><td>'.$fz['Datum'];          
        }
        elseif ( $d != $fz['Datum'] )
        {
          echo ', '.$fz['Datum'];
        }
        $d = $fz['Datum'];
        $s = $fz['Schueler_id'];
      }
      if ( mysql_num_rows($query) > 0 )
        echo "</td><td>\n";
      mysql_free_result($query);
    }
    echo "</table><hr /></td></tr>\n";
    echo '<tr><td>Attestpflicht im Schuljahr '.$strSchuljahr.'</td></tr>';
    echo '<tr><td><table class="Liste">';
    //if ( isset($_REQUEST["ZSchuljahr"]) )
    {
      $query = mysql_query('SELECT * FROM T_Fehlzeiten INNER JOIN '.
        "T_Schueler ON Schueler_id=Nr WHERE (Art='!') AND Schuljahr='$strSchuljahr' ".
        ' ORDER BY Name, Vorname, Schueler_id, Datum, Block');
      $s = 0;
      $d = '';
      echo '<tr><th>Name, Vorname</th><td>Datum';
      while ( $fz = mysql_fetch_array($query) )
      {
        if ( $s != $fz['Schueler_id'] )
        {
          echo '</td></tr><tr><td valign="top">'.$fz['Name'].', '.$fz['Vorname'].
            ' ('.$fz['Tutor'].')</td><td>'.$fz['Datum'];
        }
        elseif ( $d != $fz['Datum'] )
          echo ', '.$fz['Datum'];
        $d = $fz['Datum'];
        $s = $fz['Schueler_id'];
      }
      if ( mysql_num_rows($query) > 0 )
        echo "</td><td>\n";
      mysql_free_result($query);
    }
    echo "</table><hr /></td></tr>\n";
  } // anzeigen
?>
<tr><td>
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="Attest" class="Verhinderung">

 <?php
 $Lehrer = new Lehrer($_SERVER['REMOTE_USER'], LEHRERID_EMAIL);
 $Tutor = "AND Tutor='{$Lehrer->Kuerzel}'";
 // Abteilungsleitung darf alle Tutanden sehen
 $Abteilung = new Abteilungen($db);
 if ( $Abteilung->isAbteilungsleitung())
   $Tutor = '';
 
$strSql = "SELECT DISTINCT Nr, Name, Vorname, Klasse FROM T_Schueler INNER JOIN T_Kurse ON T_Schueler.Nr = T_Kurse.Schueler_id WHERE Klasse LIKE 'OG _' $Tutor ORDER BY Name, Vorname";

if ( ! $query = mysql_query($strSql, $db))
  echo mysql_error($db);

echo 'Fehlzeit von Sch�ler/in <select name="Schueler">';
while ($s = mysql_fetch_array($query) )
{
  echo '<option value="'.$s['Nr'].'" ';
  if ( isset($_REQUEST['Schueler']) && $s['Nr'] == $_REQUEST['Schueler']) 
    echo 'selected="selected"';
  echo '>'.$s['Name'].', '.$s['Vorname'].' ('.$s['Klasse'].")</option>\n";
}
mysql_free_result($query);
echo "</select> \n";
echo '<span class="small">In der Liste Anfangsbuchstaben eingeben, um hinzuspringen</span>';
echo '<br />';
if ( $Tutor != '' )
  echo "Es werden nur Tutanden von {$Lehrer->Vorname} {$Lehrer->Name} ($Lehrer->Kuerzel) angezeigt!<br />\n";
echo ' Schuljahr ';

$arrSchuljahr = Schuljahr::getSchuljahr('OG');
$strSchuljahr = $arrSchuljahr['langform'];

echo '<select name="Schuljahr">';
$query = mysql_query('SELECT DISTINCT Schuljahr FROM T_Kurse ORDER BY Schuljahr', $db);
 while ( $fach = mysql_fetch_row($query) )
 {
   echo '<option';
   if ( ! ( strpos($fach[0], $strSchuljahr) === false) )
     echo ' selected="selected"';
   echo '>'.$fach[0]."</option>\n";
 }
 echo '</select><br />';
?>
Beginn (Datum) <input type="text" name="Anfangsdatum" maxlength="10" size="8"
onClick="popUpCalendar(this,Attest['Anfangsdatum'],'dd.mm.yyyy')"
onBlur="autoCorrectDate('Attest','Anfangsdatum' , false )" /> (erster Block)<br />
Ende (Datum) <input type="text" name="Enddatum" maxlength="10" size="8"
onClick="popUpCalendar(this,Attest['Enddatum'],'dd.mm.yyyy')"
onBlur="autoCorrectDate('Attest','Enddatum' , false )" /> (letzter Block)<br />
Fehlzeit der Art <select name="Art">

<?php
$sel = 'selected="selected" ';
foreach ($Gruende as $Zeichen => $Beschreibung)
{
  echo '<option '.$sel.' value="'.$Zeichen.'">'.$Beschreibung.'</option>';
  $sel = '';
}
?>

</select>
<input type="Submit" value="eintragen" /><br />
<a href="<?=$_SERVER['PHP_SELF']?>?Anzeigen=1">vorhandene Atteste/Attestpflicht anzeigen</a>
</form>

</td></tr>
<?php
 include('include/footer.inc.php');
?>
