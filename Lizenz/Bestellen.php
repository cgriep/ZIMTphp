<?php
/** 
 * Bestellung von MSDNAA-Lizenzen
 * (c) 2006 Christoph Griep
 * 
 */
  $Ueberschrift = 'MSDNAA-Lizenzanforderung';
  $HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszimt.css">';
  include('include/header.inc.php');
  $Odb = $db;
  include('../Lizenzverwaltung/msdnaaconfig.inc.php');

 function erzeugeAntrag($Nr, $Name, $Vorname, $Klasse, $Anzeigen)
 {
   global $Vertragsnummern;
   global $db;
   $vorhanden = false;
   $Name = mysql_real_escape_string(trim($Name));
   $Vorname = mysql_real_escape_string(trim($Vorname));
   $Klasse = mysql_real_escape_string(trim($Klasse));
   $sql = 'INSERT INTO T_Antraege (Schueler_Nr, Name, Vorname, Art, Eingang, ' .
   		'Ansprechpartner, Vertragsnummer, Bemerkungen, Produkt) VALUES (';
   $sql .= "$Nr, '$Name','$Vorname','$Klasse','".date('Y-m-d H:i')."','".
     mysql_real_escape_string($_SERVER['REMOTE_USER'])."',";
    // Vertragsnummer feststellen
    $Vertragsnummer = -1;
    $fehler = '';
    if ( $Nr < 0 )
      $Where = "Name='$Name' AND Vorname ='$Vorname' AND Art = '$Klasse'";
    else
      $Where = "Schueler_Nr = $Nr";
    if ( ! $qu = mysql_query('SELECT Vertragsnummer, ProduktID FROM T_Lizenznehmer ' .
    		"WHERE $Where", $db))
      echo '<div class="Fehler">'.mysql_error($db).'</div>';
    while ( $r = mysql_fetch_object($qu) ) {
      $Vertragsnummer = $r->Vertragsnummer;
      $vorhanden = true;
      if ( in_array($r->ProduktID, $_REQUEST['Produkt']))
        $fehler  = 'Fehler - Lizenz schon vorhanden!';
    }
    mysql_free_result($qu);
    if ( ! $qu = mysql_query("SELECT Vertragsnummer, Produkt FROM T_Antraege WHERE $Where", $db))
      echo '<div class="Fehler">'.mysql_error($db).'</div>';
    while ( $r = mysql_fetch_object($qu) ) {
        $Vertragsnummer = $r->Vertragsnummer;
        if ( in_array($r->Produkt, $_REQUEST["Produkt"]))
          $fehler  = "Fehler - Antrag schon vorhanden!";
    }
    mysql_free_result($qu);
    if ($Vertragsnummer == -1 && $fehler == "" )
    {
      // Neue Vertragsnummer erzeugen
      $qu = mysql_query('SELECT Max(Vertragsnummer) As MA FROM T_Lizenznehmer', $db);
      if ( $r = mysql_fetch_object($qu) )
        $Vertragsnummer = $r->MA+1;
      mysql_free_result($qu);
      $qu = mysql_query('SELECT Max(Vertragsnummer) As MA FROM T_Antraege', $db);
      if ( $r = mysql_fetch_object($qu) )
        if ( $Vertragsnummer < $r->MA+1 ) $Vertragsnummer = $r->MA+1;
      mysql_free_result($qu);
    }
    $message = 'Ansprechpartner: '.
          $_SERVER['REMOTE_USER']."\nName: ".$Name.",".$Vorname.
          " (".$Klasse.", ".$Nr.")\nVertragsnummer: $Vertragsnummer\nAngeforderte Lizenzanzahl: ".
          count($_REQUEST["Produkt"])."\nDatum: ".date("d-m-Y H:i");
    if ( $fehler != '' )
    {
      $message .= "\n$fehler";
      echo '<div class="Fehler">'.$Name.','.$Vorname.' - '.$fehler.'</div>';
    }
    else
    {
      $sql .= $Vertragsnummer.",'".mysql_real_escape_string($_REQUEST['Bemerkungen']);
      $sql .= "',";
      foreach ($_REQUEST['Produkt'] as $key => $produkt )
        if ( is_numeric($produkt) )
          if ( ! mysql_query($sql.$produkt.')', $db))
            echo '<div class="Fehler">Fehler Produkt: '.mysql_error($db).'</div>';
      echo '<span class="titel">Anfrage gespeichert:</span><br />';
      echo "<i>Kurzfassung:</i><br />".nl2br($message)."<br />";
      if ( $Anzeigen ) {
        echo 'Ihre Anfrage hat die Nummer '.$Vertragsnummer.'.<br /><br />';
        if ( ! $vorhanden )
        {
          echo 'Bitte drucken Sie diese <a href="';
          echo 'Antragsformular.php?Antragnr='.$Vertragsnummer;
          echo '" target="_blank">Nutzungsvereinbarung</a> aus';
        }
        else
          echo "Es liegt bereits eine Nutzungsvereinbarung für Nr. $Vertragsnummer ".
               "vor, keine weitere Unterschrift notwendig.<br />";
      }
      if ( ! $vorhanden )
        $Vertragsnummern .= $Vertragsnummer.',';
      else
      {
        $Vertragsnummern = ' '.$Vertragsnummern;
        $message .= "\nVereinbarung für $Vertragsnummer liegt vor - " .
        		"kann direkt zugewiesen werden!\n";
      }
    }
    return $message."\n";
  } // function

  echo '<tr><td>';
  if ( isset($_REQUEST['Klasse']))
    $Klasse = str_replace(' ','',strtoupper($_REQUEST['Klasse']));
  else 
    $Klasse = '';
  $Produkte = holeProdukte(' WHERE sichtbar');

  // Anträge erzeugen
  if ( isset($_REQUEST['Produkt']) && is_array($_REQUEST['Produkt']) ) {
    $Vertragsnummern = '';
    $message = '';
    if ( isset($_REQUEST['Lehrer']) ) {
      $query = mysql_query('SELECT Kuerzel, Name, Vorname FROM T_Lehrer ' .
      		"WHERE Kuerzel='".$_REQUEST['Lehrer']."'", $Odb);
      $Lehrer = mysql_fetch_array($query);
      mysql_free_result($query);
      $message = erzeugeAntrag(-1,$Lehrer['Name'], $Lehrer['Vorname'], 'LEHRER', true);
    }
    else if ( (in_array(3, $_REQUEST['Produkt']) && in_array(6, $_REQUEST['Produkt'])) ||
       (in_array(3, $_REQUEST['Produkt']) && in_array(6, $_REQUEST['Produkt'])) ||
       (in_array(5, $_REQUEST['Produkt']) && in_array(38, $_REQUEST['Produkt'])) ||
        Count($_REQUEST['Produkt']) > 2 ) {
       // Server 2003 und 2000 oder WinXP und 2000
       echo '<div class="Fehler">Ungültige Anforderung. Sie haben mehr als zwei Produkte oder '.
        'zwei Produkte ausgewählt, die die gleichen Aufgaben erledigen '.
        "und nur unterschiedliche Versionen abbilden (z.B. WinXP und 2000). Bitte korrigieren Sie die Anfrage ".
        ' und geben Sie nur das Produkt an, das im Unterricht eingesetzt wird.</div>';
        unset($_REQUEST['Produkt']);
        unset($_REQUEST['Name']);
        unset($_REQUEST['Vorname']);
    }
    else if ( isset($_REQUEST['Schueler']) && Count($_REQUEST['Schueler']) > 0 )
    {
      $SchuelerNr = implode(',',$_REQUEST['Schueler']);
      if ( ! $query = mysql_query('SELECT Nr, Name, Vorname, Klasse FROM T_Schueler ' .
      		"WHERE Nr IN ($SchuelerNr)", $Odb))
        echo '<div class="Fehler">'.mysql_error($Odb).'</div>';
      while ( $row = mysql_fetch_array($query) )
        $message .= erzeugeAntrag($row['Nr'], $row['Name'], $row['Vorname'], $row['Klasse'], false);
      mysql_free_result($query);
      if ( trim($Vertragsnummern) != $Vertragsnummern && trim($Vertragsnummern) == '' )
        echo '<div class="Hinweis">Die Nutzungsvereinbarungen liegen vor. Die Bearbeitung erfolgt automatisch.</div>';
      elseif ( trim($Vertragsnummern) != '' ) {
        $Vertragsnummern = trim($Vertragsnummern);
        $Vertragsnummern = substr($Vertragsnummern, 0, strlen($Vertragsnummern)-1);
        echo '<div class="Hinweis">Bitte drucken Sie die ';
        echo '<a href="Antragsformular.php?Antragnr='.$Vertragsnummern.
             '" target="_blank">gesammelten Anträge</a> aus.</div>';
      }
      else
        echo '<div class="Fehler">Aufgrund von Fehlern konnten keine Anfragen gespeichert werden.</div>';
    }
    else
      die ('Fehler: Keine Angaben gemacht!');
    if ( $message != '' && trim($Vertragsnummern) != '' ) {
      if ( isset($_REQUEST['Lehrer'] ))
        echo 'Bitte ';
      else
        echo 'Lassen Sie die/den SchülerIn unterschreiben und ';
      echo 'legen Sie den unterschriebenen ';
      echo 'Antrag ins Fach von Koll. <a href="mailto:eiben@oszimt.de">Eiben</a>. ' .
      		'<b>Bei mehreren Anträgen sortieren Sie ' .
      		'die Anträge zur schnelleren Abwicklung bitte nach der Nummer.</b><br />';
      echo "Sie erhalten nach Eingang der unterschriebenen Nutzungsvereinbarungen die ".
        "Lizenzen per Mail,";
      echo ' sobald Lizenzen frei sind. Beachten Sie die ' .
      		'<a href="#Schema">schematische Darstellung</a> des ';
      echo 'Bestellvorganges.</p>';    
      echo '<table class="Liste">';
      echo '<tr><th>Produkt</th><th>Verfügbarkeit</th></tr>';
      $Anz = 0;
      foreach ($_REQUEST['Produkt'] as $produktid )
      {
        $query = mysql_query("SELECT Count(*) FROM T_Lizenznummern WHERE ProduktID=$produktid", $db);
        if ( ! $row = mysql_fetch_row($query))
          $AnzahlFrei = 0;
        else
          $AnzahlFrei = $row[0];
        mysql_free_result($query);
        $query = mysql_query("SELECT Count(*) FROM T_Antraege WHERE Produkt=$produktid", $db);
        if ( ! $row = mysql_fetch_row($query))
          $AnzahlBestellt = 0;
        else
          $AnzahlBestellt = $row[0];
        mysql_free_result($query);
        echo "<tr><td>".$Produkte[$produktid].'</td>';
        if ( $AnzahlFrei-$AnzahlBestellt > 0 || $AnzahlFrei == 1 ) // ohne Aktivierung (vereinfacht)
          echo '<td bgcolor="green">verfügbar';
        elseif ( $AnzahlFrei-$AnzahlBestellt > -100 && $AnzahlFrei > 0 )
          echo '<td bgcolor="yellow">z.Zt. nur im Einzelfall verfügbar';
        else
          echo '<td bgcolor="red">Lizenzen z.Zt. nicht verfügbar (Warteliste: '.
            $AnzahlBestellt.' Bestellungen bisher)';
        echo '</td></tr>';
        $Anz++;
      }
      echo "</table><hr />";
      $message .= "\n".addslashes($_REQUEST["Bemerkungen"]);
      mail("eiben@oszimt.de", "[MSDNAA] Neuer Antrag", $message, "From: eiben@oszimt.de",
      '-f'.$_SERVER['REMOTE_USER'].'@oszimt.de');
    }
    elseif ( trim($Vertragsnummern) == '' )
      mail("eiben@oszimt.de", "[MSDNAA] Neuer Antrag", 
   'Es liegen Anträge zur sofortigen Bearbeitung vor.', "From: eiben@oszimt.de");
  }
?>
  </td></tr>
  <tr>
 <td align="center"><a href="/">Zurück zur Startseite des Lehrerbereichs.</a><br />
 <a href=".">Andere Klasse auswählen</a></td>
</tr>
<tr><td>&nbsp;</td></tr>
  <tr>
  <td align="center">
 <span class = "home-content-titel">Antrag auf Erteilung einer Lizenz aus dem MSDNAA-Programm</span> (für Lehrer oder Schüler des OSZ IMT)
  </td></tr><tr><td>
<tr><td><hr /></td></tr>
<tr><td>
<table>
<form action="<?=$_SERVER["PHP_SELF"]?>" method="post" enctype="multipart/form-data">
<tr>
<td>
<?php
  if ( isset($_REQUEST["Lehrer"] ) )
  {
    $query = mysql_query("SELECT Kuerzel, Name, Vorname FROM T_Lehrer " .
    		"WHERE Kuerzel='".$_REQUEST["Lehrer"]."'", $Odb);
    $Lehrer = mysql_fetch_array($query);
    mysql_free_result($query);
    echo 'Lizenz für</td><td>';
    echo '<input type="hidden" name="Lehrer" value="'.$_REQUEST["Lehrer"].'" />';
    echo $Lehrer["Vorname"]." ".$Lehrer["Name"];
  }
  else
  {  // Klasse
    $query = mysql_query("SELECT Nr, Name, Vorname FROM T_Schueler ".
        "WHERE BINARY Klasse = '".$_REQUEST["Klasse"].
        "' ORDER BY Name, Vorname", $Odb);
    echo 'Klasse <strong>'.$_REQUEST["Klasse"].'</strong><br />';
    echo '<input type="hidden" name="Klasse" value="'.$_REQUEST["Klasse"].'" />';
    echo '<span class="content-small">(mehrere Schüler mit gehaltener Strg-Taste auswählen)</span>';
    echo '</td><td>';
    echo '<select name="Schueler[]" multiple="multiple" size="8">';
    while ( $schueler = mysql_fetch_array($query) )
    {
      echo '<option value="'.$schueler["Nr"].'">'.$schueler["Name"].", ";
      echo $schueler["Vorname"].'</option>';
    }
    mysql_free_result($query);
    echo '</select>';
  }
?>
</td><td>Bitte wählen Sie die SchülerInnen, die Lizenzen erhalten sollen.</td>
</tr>
<tr>
 <td valign="top"><label for="produkt">gewünschtes Produkt</label><br /><small>
 (Mehrere Produkte mit gehaltener
 <br /> Strg-Taste auswählen)</small><option value=""></td>
 <td><select id="produkt" name="Produkt[]" multiple="multiple" size="8">
 <?php
 foreach ( $Produkte as $key => $value)
 {
   echo '<option value="'.$key.'"';
   if ( isset($_REQUEST["Produkt"]))
     if ( in_array($key,$_REQUEST["Produkt"]) ) echo ' selected="selected"';
   echo '>'.$value.'</option>';
 }
 ?>
</select></td> <td>Wählen Sie hier das Produkt oder die Produkte, für die eine Lizenz
gewünscht wird. (Maximal können zwei Produkte auf ein Mal gewählt werden)</td>
</tr>
 <td>Ansprechpartner<br />(Wer betreut die Anfrage)</td>
 <td align="center"><font color="red" size="4"><b><?=$_SERVER["REMOTE_USER"]?></b></font></td>
 <td>Name der Kollegin/des Kollegen, der die Lizenzen per Mail erhält und die Ausgabe der
 Lizenzformulare übernimmt. </td>
</tr>
<tr>
<td><label for="bemerkungen">Sonstige Bemerkungen</label></td>
<td><textarea id="bemerkungen" name="Bemerkungen" cols="40" rows="5"></textarea></td>
<td><b>bei Schülerlizenzen bitte den Turnus angeben, in dem sie in der Schule sind!</b>
</td>
</tr>
<tr>
 <td align="center" colspan="3">
<?php
 if ( $_SERVER["REMOTE_USER"] != "" )
   echo '<input type="Submit" value="Abschicken">';
 else
   echo 'Benutzer konnte nicht erkannt werden. Bitte später noch einmal versuchen.';
?>
 </td>
</tr>
</table></td></tr>
<tr>
 <td align="center"><hr /><a id="Schema" name="Schema"><img src="MSDNAA-Ausleihvorgang.jpg" border="0" alt="(Darstellung des Ausleihvorgangs)" align="center"></a> </td>
</tr>
<tr>
 <td align="center"><a href="/">Zurück zur Startseite des Lehrerbereichs.</a><br />
 <a href=".">Andere Klasse auswählen</a></td>
</tr>
</form>

<?php
  mysql_close($Odb);
  include("include/footer.inc.php");
?>