<?php
/*
 * Kollegenliste.php
 * Zeigt eine Liste aller Kollegen an.
 * Wahlweise nach Abteilung, Bildungsgang, Klasse oder Gruppe
 * Dabei besteht die Möglichkeit, der angezeigten Personengruppe eine E-Mail
 * zu senden.
 * Letzte Änderung:
 * 16.02.06 C. Griep - Abteilungsklasse eingesetzt
 */
$Ueberschrift = 'KollegInnen auflisten';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszimt.css">';
if ( ! isset($_REQUEST['Excel'] ))
{
  include('include/header.inc.php');
  echo '<tr><td>'; // Beginn Body
}
else
{
  include('include/config.php');
}
include('include/Klausur.inc.php');
include('include/stupla.inc.php');
include('include/Abteilungen.class.php');
require_once('include/Lehrer.class.php');
      
$Abteilungen = new Abteilungen($db);

function sende_mail($empfaenger, $attachment, $db, $mitBCC = false)
{
  $mail = new mime_mail();
  $mail->to = $empfaenger;
  $Lehrer = new Lehrer($_SERVER['REMOTE_USER'], LEHRERID_EMAIL);
  // Mime-Mail klärt nicht die vollständige Form der Namensangabe!
  //$mail->from = '"'.$Lehrer->Vorname.' '.$Lehrer->Name.'" <'.
  $mail->from =  $_SERVER['REMOTE_USER'].'@oszimt.de'; // >';
  $mail->headers = 'Errors-To: '.$_SERVER['REMOTE_USER'].'@oszimt.de';
  if ( $mitBCC ) $mail->headers .= "\nbcc: ".$_SERVER['REMOTE_USER']."@oszimt.de";
  $mail->subject = '[OSZIMT] '.$_REQUEST['mailsubject'];
  $body = stripslashes($_REQUEST['mailbody']); //."\ngesendet an:".$Mail."\n";
  if (strlen($attachment) > 0 )
    $mail->add_attachment($attachment, $_FILES['Anhang']['name'],$_FILES['Anhang']['type']);
  $mail->body = $body;
  if ( isset($_REQUEST['senden'] ) )
  {
    if ( ! $mail->send() )
    {
      echo 'Fehler beim Mailen!<br />Empfänger: '.$mail->to;
    }
    else
    {
      echo '<span class="unterlegt">Mail an '.$mail->to.' gesendet.</span><br />';
    }
  }
  else
    echo 'Mail würde an '.$mail->to.' gesendet.<br />';  
}

$Zusatz = '';
$Version = getAktuelleVersion();
$sql = 'SELECT DISTINCT Lehrer FROM T_StuPla WHERE '.
    "%ZUSATZ% AND Version=$Version ORDER BY Name, Vorname";
$header = '';
$unterheader = '';
if ( isset($_REQUEST['Abteilung']) )
{
  $Art = 'Abteilung';
  $Wert = $_REQUEST['Abteilung'];
  switch ( $_REQUEST['Abteilung'] )
  {
    case 1: $Abteilung = 'I';  break;
    case 2: $Abteilung = 'II'; break;
    case 3: $Abteilung = 'III'; break;
    case 4: $Abteilung = 'IV'; break;
    default: $Abteilung = 'I';
  }
  $Klassen = array();
  $Version = getAktuelleVersion();
  if ( ! $query = mysql_query('SELECT DISTINCT T_Schueler.Klasse FROM T_Schueler ' .
  		'INNER JOIN T_StuPla ON T_Schueler.Klasse=T_StuPla.Klasse ' .
  		"WHERE Abteilung='$Abteilung' AND Version=$Version",$db))
    echo mysql_error();
  while ( $Klasse = mysql_fetch_row($query) )
  {
    $Klassen[] = $Klasse[0];
  }
  mysql_free_result($query);
  $header = 'KollegInnen der '.$Abteilungen->getAbteilung($_REQUEST['Abteilung']);
  $unterheader = '<em>Klassen der Abteilung:</em> '.implode(' ',$Klassen);
  $Zusatz = "Klasse IN ('".implode("','",$Klassen)."')";
}
if ( isset($_REQUEST['Schueler'] ) )
{
  $Art = 'Schueler';
  $Wert = $_REQUEST['Schueler'];
  $Klassen = mysql_real_escape_string($_REQUEST['Schueler']);
  $header = 'KollegInnen die den Schüler '.$_REQUEST['Schueler'].' unterrichten';
  $sql = 'SELECT DISTINCT Lehrer FROM T_StuPla INNER JOIN '.
         'T_Kurse ON T_StuPla.Fach=Kurs WHERE Schueler_id='.$Wert.
         " AND Version=$Version ORDER BY Name, Vorname";
}
if ( isset($_REQUEST['Klasse'] ) )
{
  $Art = 'Klasse';
  $Wert = $_REQUEST['Klasse'];
  $Klassen = mysql_real_escape_string($_REQUEST['Klasse']);
  $header = 'KollegInnen der Klasse '.$_REQUEST['Klasse'];
  $unterheader = '<a href="Klassenliste.php?Klasse='.$_REQUEST['Klasse'].
      '">SchülerInnen der Klasse anzeigen</a>';
  $Zusatz = "Klasse ='$Klassen'";
}
if ( isset($_REQUEST['Gang'] ) )
{
  $Art = 'Gang';
  $Wert = $_REQUEST['Gang'];
  $Klassen = mysql_real_escape_string($_REQUEST['Gang']);
  $header = 'KollegInnen des Bildungsgangs '.$_REQUEST['Gang'];
  $Zusatz = "Klasse LIKE '$Klassen%'";
}

if ( isset($_REQUEST['Fach'] ) )
{
  $Art = 'Fach';
  $Wert = $_REQUEST['Fach'];
  $Klassen = array();
  $Zusatz = " Fach LIKE '".mysql_real_escape_string($_REQUEST["Fach"]).
    "%' COLLATE 'latin1_german1_ci'"; // case-insensitive
  $header = 'KollegInnen mit dem Fach '.$_REQUEST['Fach'];
}
if ( isset($_REQUEST['Alle'] ) )
{
  $Art = 'Alle';
  $Wert = $_REQUEST['Alle'];
  $Klassen = array();
  $Zusatz = ' 1';
  $header = 'KollegInnen des OSZ IMT';
}

$dieLehrer = array();
$Gruppen = array();
$groupfile = '/home/htaccess/htgroups';
$userfile = '/home/htaccess/htpasswd';
include_once('include/internadmin/file_handle.inc.php');
readGroupFile($grouplist, $filedate, $errormsg);

//$query = mysql_query('SELECT DISTINCT Gruppe FROM T_EMailGruppen ORDER BY Gruppe');
//while ( $row = mysql_fetch_array($query) )
//  $Gruppen[] = $row['Gruppe'];
//mysql_free_result($query);
if ( isset($_REQUEST['Gruppe'] ) && is_array($grouplist))
{
  $Art = 'Gruppe';
  $Wert = $_REQUEST['Gruppe'];

  //$query = mysql_query("SELECT * FROM T_EMailGruppen WHERE Gruppe='".$_REQUEST['Gruppe']."'");
  $Zusatz = 'Lehrer IN (';
  /*
  while ( $row = mysql_fetch_array($query) )
  {
    if ( trim($row['Kuerzel']) != '' )
      $Zusatz .= "'".$row['Kuerzel']."',";
    else
    {
      // Schulfremde Personen
      $dieLehrer[] = $row;
      echo 'Fremd'; // Ausgabe der Person 
    }
  }
  mysql_free_result($query);
  */
  foreach ( $grouplist as $Gruppe )
  {
  	if ( $Gruppe[0] == $_REQUEST['Gruppe'])
  	foreach ( $Gruppe as $key => $Mitglied)
  	{
  		if ( $key != 0 ) // Gruppenname
  		  $Zusatz .= "'".userToKuerzel($Mitglied)."',";  		
  	}
  }
  
  $Zusatz =substr($Zusatz,0,strlen($Zusatz)-1);
  $Zusatz .= ')';
  $Klassen = mysql_real_escape_string($_REQUEST['Gruppe']);
  $header = 'KollegInnen der Gruppe '.$_REQUEST['Gruppe'];
}
if ( isset($Klassen) )
{
  if (! isset($_REQUEST['Excel'] ))
  {
    echo '<h2>'.$header.'</h2>';
    echo $unterheader;
    $sql = str_replace('%ZUSATZ%',$Zusatz,$sql);
    //echo $sql;
    $query = mysql_query($sql, $db);
    echo '<table class="Liste">';
    echo '<tr><th>Name</th><th>Vorname</th><th>Kürzel</th><th>';
    if ( isset($_REQUEST['Bild']))
      echo 'Bild';
    else
      echo 'Einsatzklassen';
    echo '</th></tr>';
    while ( $Lehrer = mysql_fetch_array($query) )
    {
      $L = new Lehrer($Lehrer['Lehrer'], LEHRERID_KUERZEL);
      echo '<tr><td><a href="http://skripte.oszimt.de/MailIt.php?id='.$L->Username;
      echo '&sender='.$_SERVER['REMOTE_USER'].'" title="E-Mail schreiben">';
      echo $L->Name.'</a></td><td>'.$L->Vorname.'</td><td>';
      echo '<a href="/StuPla/LPlan1.php?Lehrer='.$Lehrer['Lehrer'].
        '&ID_Woche=-1&KW=true" title="Stundenplan anzeigen">';
      echo $Lehrer['Lehrer'].'</a></td><td>';
      if ( isset($_REQUEST['Bild']))
      {
        if ( $L->Bild != '' )
          echo '<img src="/Service/Lehrerbild.php?Kuerzel='.$Lehrer['Lehrer'].
           '&Groesse=50" alt="Bild" />';
        else
          echo 'n/a';
      }
      elseif ( trim($Lehrer['Lehrer']) != '' )
      {
        $qu = mysql_query("SELECT DISTINCT Klasse, Fach FROM T_StuPla WHERE Lehrer='".$Lehrer['Lehrer'].
          "' AND Version=$Version ORDER BY Klasse", $db);
        while ( $klasse = mysql_fetch_row($qu) )
          echo '<a href="'.$_SERVER['PHP_SELF'].'?Klasse='.$klasse[0].'">'.$klasse[0].
            '</a> <span class="small">('.
            $klasse[1].')</span> ';
        mysql_free_result($qu);
      }
      else
      {
        // Person von außerhalb
        echo nl2br($Lehrer['Beschreibung']);
      }
      $dieLehrer[] = $L;
      echo '</td></tr>';
    }
    mysql_free_result($query);
    echo '</table>';
    if ( ! isset($_REQUEST['Print']) )
    {
      echo '<a href="'.$_SERVER['PHP_SELF'].'?Excel=1&'.$Art."=$Wert".
        '">Als Excel-Datei speichern</a> ';
      echo '<a href="'.$_SERVER['PHP_SELF'].'?Bild=1&'.$Art.'='.$Wert.
        '">Mit Bildern anzeigen</a>';
    }
    echo '<hr />';
    flush(); // Ausgabepuffer leeren
    if ( isset($_REQUEST['sendmail']) )
    {
      echo '<a id="Senden" name="Senden"></a>Suche Mailadressen...<br />';
      flush();
      // mail versenden
      require_once('phpmail.php');
      $attachment = '';
      if ( isset($_FILES['Anhang']['name']) && $_FILES['Anhang']['name'] != '' )
      {
        //move_uploaded_file($_FILES['Anhang']['tmp_name'],'mailtmp/'.basename($_FILES['Anhang']['name']));
        $attachment = file_get_contents($_FILES['Anhang']['tmp_name']);
        //@unlink('mailtmp/'.basename($_FILES['Anhang']['name']));
      }
      $eMails = array();
      /* Alter Server
      mysql_select_db('confixx', $db);
      foreach ($dieLehrer as $Lehrer)
      {
        $prefix = LehrerToUser($Lehrer['Name'], $Lehrer['Vorname'], $db);
        if ( $prefix != '-unbekannt-' )
          $eMails[] = $prefix.'@oszimt.de';
        else
          echo '<span class='unterlegt'>Keine Mailadresse für '.$Lehrer['Name'].
              ','.$Lehrer['Vorname'].' gefunden.</span>';
        echo '.'; flush();
      }
      echo '</td></tr>';
      echo '<tr><td>Sende Mails...</td></tr>'; flush();
      mysql_select_db('oszimt', $db);
      */
      mysql_select_db('psa');      	  
      foreach ($dieLehrer as $Lehrer)
      {
      	if ( $Lehrer->Username != '' ) 
      	{
      	  $sql = 'SELECT mail_name FROM mail INNER JOIN domains ';
      	  $sql .= 'ON dom_id=domains.id WHERE name="oszimt.de" AND mail_name="';
      	  $sql .= $Lehrer->Username.'"';
      	  // Plesk-Datenbank auswählen
      	  if ( ! $query = mysql_query($sql));
          if ( mysql_num_rows($query) > 0 )
          {
      	    // E-Mail auf Plesk prüfen
       	    $eMails[] = $Lehrer->Username.'@oszimt.de';
      	    echo '.';
          }
          else
          {
          	echo '<div class="Fehler">Keine E-Mail-Adresse für '.
          	  $Lehrer->Anrede($LEHRER_LEHRER,true).'</div>';
          }
          mysql_free_result($query);
          
      	}
      	else
          echo '<div class="Fehler">Keine Mailadresse für '.$Lehrer->Anrede($LEHRER_LEHRER,false).
              ' gefunden.</div>';
        flush();
      }
      mysql_select_db($dbName);
      echo 'Sende Mails...<br />'; flush();
      $empfaenger = '';
      if ( ! in_array($_SERVER['REMOTE_USER'].'@oszimt.de', $eMails)) 
        $mitBCC = true;
      else
        $mitBCC = false;
      foreach ($eMails as $Mail)
      {
        if ( $empfaenger == '' )
          $empfaenger = $Mail;
        else
          $empfaenger .= ', '.$Mail;
        if ( strlen($empfaenger) > 400 )
        {
          sende_mail($empfaenger, $attachment,$db, $mitBCC);
          $empfaenger = '';
          flush();
        } // Empfänger > 400
      }
      if ( strlen($empfaenger) > 0 )
        sende_mail($empfaenger, $attachment, $db, $mitBCC);
    }
    if ( ! isset($_REQUEST['Print']) && ! isset($_REQUEST['Bild']) )
    {
        echo Count($dieLehrer).' KollegInnen gefunden.';
        ?>
<form action="<?=$_SERVER["PHP_SELF"]?>#Senden" method="post" enctype="multipart/form-data">
Betreff
<input type="hidden" name="<?=$Art?>" value="<?=$Wert?>" />
<input type="Text" name="mailsubject" value="" size="50" maxlength="50" /><br />
Nachricht<br />
<textarea name="mailbody" rows="5" cols="60">

mit freundlichen Grüßen
  <?=$_SERVER["REMOTE_USER"];?>

--
Oberstufenzentrum Informations- und Medizintechnik
(Berufliches Gymnasium, Berufsoberschule, Fachoberschule, Berufsfachschule, Fachschule und Berufsschule)
Haarlemer Straße 23-27
12359 Berlin-Neukölln
Tel.: 030-606-4097     Fax: 030-606-2808
http://www.oszimt.de
SchulNr.: 08B04
</textarea><br />
        <br />
        Anhängen <input type="file" name="Anhang" /> (Achtung, nicht zu große Datei!)<br />
        Real senden <input type="Checkbox" name="senden" value="v" /> (wenn nicht markiert nur Test)<br />
        <input type="Submit" name="sendmail" value="Abschicken" />
        </form>
        <hr />
  <?php
    }
  } // nicht Excel
  else // Excel
  {
    $sql = str_replace("%ZUSATZ%",$Zusatz,$sql);
    $query = mysql_query($sql, $db);
    $header = '"'.str_replace('"','""',$header).'"'."\n";
    $header .= "\"Name\"\t\"Vorname\"\t\"Kürzel\"\t\"E-Mail\"\t\"Einsatzklassen\"\n";
    while ( $Lehrer = mysql_fetch_array($query) )
    {
      $L = new Lehrer($Lehrer['Lehrer'], LEHRERID_KUERZEL);
      $header .= '"'.trim(str_replace('"','""',$L->Name)).'"';
      $header .= "\t";
      $header .= '"'.trim(str_replace('"','""',$L->Vorname)).'"';
      $header .= "\t";
      $header .= '"'.trim(str_replace('"','""',$Lehrer["Lehrer"])).'"';
      $header .= "\t\"";
      if ( $L->Username != '' )
      {
        $header .= $L->Username.'@oszimt.de';
      }
      else
      {
      	$header .= '';
      }
      $header .= "\"\t";
      if ( trim($Lehrer['Lehrer']) != '' )
      {
          $qu = mysql_query("SELECT DISTINCT Klasse, Fach FROM T_StuPla WHERE Lehrer='".$Lehrer["Lehrer"].
          "' AND Version=$Version ORDER BY Klasse", $db);
          while ( $klasse = mysql_fetch_row($qu) )
            $header .= '"'.str_replace('"','""',$klasse[0]."/".$klasse[1]).'"'."\t";
          mysql_free_result($qu);
      }
      else
      {
          // Person von außerhalb
          $header .= '"'.str_replace('"','""',$Lehrer["Beschreibung"]);
      }
      $header .= "\n";
    } // while 
    mysql_free_result($query);
    $header = str_replace("\r", '', $header);
    # This line will stream the file to the user rather than spray it across the screen
    header('Content-type: application/octet-stream');
    # replace excelfile.xls with whatever you want the filename to default to
    header('Content-Disposition: inline; filename=Lehrer'.str_replace(' ','',
      $Art.$Wert.'.xls'));
    echo $header;
  }
} // isset Klassen
if ( ! isset($_REQUEST['Excel'] ) )
{
  echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post" >';
  echo 'Alle KollegInnen ';
  echo '<input type="hidden" name="Alle" value="1"/>';
  echo '<input type="Submit" value="Anzeigen">';
  echo "</form>\n";
  echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
  echo '<label for="Abteilung">KollegInnen der Abteilung</label> ';
  echo '<select id="Abteilung" name="Abteilung">';
  foreach ( $Abteilungen->Abteilungen as $key => $value )
    echo '<option value="'.$key.'">'.$value["Abteilung"].'</option>';
  echo '</select> ';
  echo '<input type="Submit" value="Anzeigen">';
  echo "</form>\n";
  echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
  echo '<label for="Klasse">KollegInnen der Klasse</label> ';
  echo '<select id="Klasse" name="Klasse">';
  if ( ! $query = mysql_query("SELECT DISTINCT Klasse FROM T_StuPla WHERE Version=$Version ORDER BY Klasse"))
   echo mysql_error();
  $Klassen = array();
  while ( $fach = mysql_fetch_row($query) )
  {
    $klasse = strtoupper($fach[0]);
    while ( ereg("[0-9]", $klasse) ) // is_numeric(substr($klasse,strlen($klasse)-1)) )
    {
      //echo $fach[0]."//".substr($fach[0],0,strlen($fach[0])-2);
      $klasse = substr($klasse,0,strlen($klasse)-2);
    }
    if ( strpos($klasse,"_") !== false )
      $klasse = substr($klasse,0,strpos("_",$klasse)-2);
    $klasse = trim($klasse);
    if ( ! in_array($klasse,$Klassen))
      $Klassen[] = $klasse;
    echo '<option>'.$fach[0]."</option>\n";
  }
  mysql_free_result($query);
  echo '</select> ';
  echo '<input type="Submit" value="Anzeigen">';
  echo "</form>\n";
  echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
  echo '<label for="Fach">KollegInnen des Fachs</label> ';
  echo '<select id="Fach" name="Fach">';
  if ( ! $query = mysql_query("SELECT DISTINCT Fach FROM T_StuPla WHERE Version=$Version ORDER BY Fach"))
    echo mysql_error();
  $faecher = array();
  while ( $fach = mysql_fetch_row($query) )
  {
    $fach[0] = strtoupper($fach[0]);
    while (is_numeric(substr($fach[0],strlen($fach[0])-1)))
    {
      //echo $fach[0]."//".substr($fach[0],0,strlen($fach[0])-2);
      $fach[0] = substr($fach[0],0,strlen($fach[0])-2);
    }
    if ( strpos($fach[0],"_") !== false )
      $fach[0] = substr($fach[0],0,strpos("_",$fach[0])-2);
    if ( ! in_array($fach[0],$faecher))
      $faecher[] = $fach[0];
  }
  mysql_free_result($query);
  foreach ($faecher as $value )
    echo '<option>'.$value."</option>\n";
  echo '</select> ';
  echo '<input type="Submit" value="Anzeigen">';
  echo '</form>';
  echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
  echo '<label for="Gang">KollegInnen des Bildungsgangs</label> ';
  echo '<select id="Gang" name="Gang">';
  foreach ( $Klassen as $value )
    echo '<option>'.$value."</option>\n";
  echo '</select> ';
  echo '<input type="Submit" value="Anzeigen">';
  echo '</form>';
  echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
  echo '<label for="Gang">KollegInnen die einen OG-Schüler der Kursphase unterrichten</label> ';
  echo '<select id="Schueler" name="Schueler">';
  $query = mysql_query("SELECT Nr, Name, Vorname FROM T_Schueler WHERE Klasse LIKE 'OG _'".
    " ORDER BY Name, Vorname", $db);
  while ( $schueler = mysql_fetch_array($query) )
  {
    echo '<option value="'.$schueler["Nr"].'">'.$schueler["Name"].", ".$schueler["Vorname"]."</option>\n";
  }
  mysql_free_result($query);
  echo '</select> ';
  echo '<input type="Submit" value="Anzeigen">';
  echo '</form>';
    
  if ( is_array($grouplist))
  {
    echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
    echo '<label for="Gang">KollegInnen der Gruppe</label> ';
    echo '<select id="Gruppe" name="Gruppe">';
    foreach ( $grouplist as $value ) // $Gruppen 
      echo '<option>'.$value[0]."</option>\n";
    echo '</select> ';
    echo '<input type="Submit" value="Anzeigen">';
    echo '</form>';
  }  
  echo '</td></tr>';
  include('include/footer.inc.php');
}
?>
