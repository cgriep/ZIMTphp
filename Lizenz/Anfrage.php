<?php
/**
3 * Anfrage wegen Lizenzen
4 * (c) 2006 Christoph Griep
5 *
6 */
  $Ueberschrift = "MSDNAA-Lizenzanforderung";
  include("include/header.inc.php");
  $StuPlaDB = $db;
  include("../Lizenzverwaltung/msdnaaconfig.inc.php");
?>
</td></tr>
<tr>
<td align="center"><a href="/">Zur�ck zur Startseite des Lehrerbereichs.</a></td>
</tr>
<tr><td>&nbsp;</td></tr>
<tr><td><strong>Hinweis:</strong> Das OSZ IMT bietet auch StarOffice (Ersatz f�r Microsoft
Office)und eine Freeware-Kollektion mit Programmen und Tutorials f�r den TI- und
AS-Unterricht an.
Diese Produkte werden im IMTernetcaf� gegen einen Unkostenbeitrag von 2 Eur f�r den
Rohling ausgegeben.
</td></tr>
<tr><td class="home-content-titel" bgcolor="lightgray" align="center">
Vorhandene Antr�ge <a href="Ansehen.php">ansehen und erneut ausdrucken</a>.
</td></tr>
<tr><td>
<?php
$query = mysql_query("SELECT Count(*), ProduktID FROM T_Lizenznummern GROUP BY ProduktID");
  $Produkte=holeProdukte(' WHERE Sichtbar=1');
  echo '<table>';
  echo '<tr><th>Produkt</th><th>Verf�gbarkeit</th></tr>';
  while ( $prow = mysql_fetch_array($query) )
  {
    if ( isset($Produkte[$prow['ProduktID']]) && 
         trim($Produkte[$prow["ProduktID"]]) != '' )
    {
      echo '<tr><td>'.$Produkte[$prow["ProduktID"]]."</td>";
      $lquery = mysql_query("SELECT Count(*) FROM T_Antraege WHERE Produkt={$prow["ProduktID"]}");
      if ( ! $row = mysql_fetch_row($lquery))
        $AnzahlBestellt = 0;
      else
        $AnzahlBestellt = $row[0];
      mysql_free_result($lquery);
      if ( $prow[0] == 1 )
      {
        $lquery = mysql_query("SELECT Count(*) FROM T_Lizenznummern ".
          "WHERE Art='Volume' AND ProduktID={$prow["ProduktID"]} ");
        $prow = mysql_fetch_array($lquery);
        if ( $prow[0] == 1 ) $prow[0] = 10000000;
        mysql_free_result($lquery);
      }
      if ( $prow[0]-$AnzahlBestellt > 0 ) // ohne Aktivierung (vereinfacht)
        echo '<td style="background-color:green">verf�gbar';
      elseif ( $prow[0]-$AnzahlBestellt > -100 && $prow[0] > 0 )
        echo '<td style="background-color:yellow">z.Zt. nur im Einzelfall verf�gbar';
      else
        echo '<td style="background-color:red">Lizenzen z.Zt. nicht verf�gbar (Warteliste: '.
          $AnzahlBestellt.' Bestellungen bisher)';
      echo '</td></tr>';
    }
  }
  echo '</table>';
  mysql_free_result($query);
?>
</td></tr>
<tr><td>&nbsp;</td></tr>
<tr>
  <td align="center">
 <span class = "home-content-titel">Antrag auf Erteilung einer Lizenz aus dem MSDNAA-Programm</span> (f�r Lehrer oder Sch�ler des OSZ IMT)
  </td></tr>
  <tr><td class="home-content">
Wir weisen noch einmal darauf hin, dass in den o.g. Vereinbarungen steht,
dass nur <strong>f�r den Unterricht n�tige</strong> (d.h. dort verwendete) Software weitergegeben
werden darf! Sie best�tigen durch Ihre Bestellung, dass Sie die Software im Unterricht
einsetzen!!!
</td></tr>
<tr><td class="home-content">
Bestandteil der Nutzungsvereinbarung ist der <a href="EULA.TXT" target="_blank">
ENDBENUTZER-LIZENZVERTRAG</a> (EULA) f�r Microsoft-Software und die
<a href="http://www.msdnaa.net/EULA/EMEA/German.aspx#amendment" target="_blank">
Zusatzvereinbarung</a>.<br />
Weitere Informationen auf der <a href="http://www.msdnaa.net" target="_blank">Microsoft-Website</a>
oder in der <a href="MSDNAA-Erlaeuterung.pdf" target="_blank">�bersicht</a>
�ber das MSDNAA-Programm am OSZ IMT (PDF-Datei, 13 kB).
</td></tr>
<tr><td></td></tr>
<tr><td></td></tr>
<tr><td class="home-content">
Fragen bitte an Koll. <a href="mailto:eiben@oszimt.de">Eiben</a>.<br /><br />
<em>Bitte beantragen Sie nur solche Lizenzen, die tats�chlich ben�tigt werden</em>. Je mehr
Lizenzen beantragt sind, desto l�nger kann es dauern, bis alle CD's bereitliegen, da die
Antr�ge nur vollst�ndig bearbeitet werden.<br />
Abgesehen davon liefert Microsoft neue Lizenznummern nur sehr unregelm��ig, so dass es
durch unn�tige Bestellungen zu langen Wartezeiten kommen kann. Wir behalten uns vor,
Bestellungen mit Programmen in verschiedenen Versionen (z.B. WinXP und Win2K) abzulehnen.
<br />
Pro Bestellvorgang k�nnen maximal zwei Produkte bestellt werden. Wollen Sie mehr Produkte
bestellen, m�ssen Sie den Bestellvorgang mehrmals hintereinander durchlaufen. Die
Nutzungsvereinbarungen m�ssen in diesem Fall nur ein Mal ausgedruckt werden.
<br />
Sofern Sie f�r eine ganze Klasse bestellen, nutzen Sie bitte die M�glichkeit die
Nutzungsvereinbarungen papiersparend im A5-Format mit zwei Vereinbarungen pro Seite
auszudrucken. Bitte trennen Sie die Vereinbarungen in diesem Falle und lassen Sie die
R�ckseite unbedruckt.
</td></tr>
<tr><td><hr /></td></tr>
<tr><td>
<table>
<form action="Bestellen.php" method="post">
<tr>
<td>F�r Klasse</td>
<td><select name="Klasse" size="1">
<?php
 $query = mysql_query("SELECT DISTINCT Klasse FROM T_StuPla ORDER BY Klasse",$StuPlaDB);
 while ( $art = mysql_fetch_row($query) )
 {
   echo '<option>'.$art[0].'</option>';
 }
 mysql_free_result($query);
 /*
 $query = mysql_query("SELECT DISTINCT Klasse FROM T_Auswaertsklassen ORDER BY Klasse",$StuPlaDB);
 while ( $art = mysql_fetch_row($query) )
 {
   echo '<option>'.$art[0].'</option>';
 }
 mysql_free_result($query);
*/
?>
</select></td>
<td>
<input type="Submit" name="Schueler" value="Lizenzen bestellen">
</td></tr>
</form>
<tr><td colspan="3"><hr /></td></tr>
<form action="Bestellen.php" method="post">
<tr>
<td>F�r LehrerIn</td>
<td><select name="Lehrer" size="1">
<?php
if ( ! $query = mysql_query("SELECT DISTINCT Kuerzel, Name, Vorname FROM T_Lehrer ORDER BY Name, Vorname", $StuPlaDB))
 echo mysql_error($StuPlaDB);
while ( $lehrer = mysql_fetch_array($query) )
{
  $dieLehrer[trim($lehrer["Kuerzel"])]["Name"] = $lehrer["Name"];
  $dieLehrer[trim($lehrer["Kuerzel"])]["Vorname"] = $lehrer["Vorname"];
}
mysql_free_result($query);

foreach ( $dieLehrer as $lehrer => $werte )
{
  echo '<option value="'.$lehrer.'"';
  similar_text($werte["Name"], $_SERVER["REMOTE_USER"], $p);
  if ( $p > 95 )
    echo ' selected="selected"';
  echo '>'.$werte["Name"].", ".$werte["Vorname"]."</option>";
}
?>
</select></td>
<td>
<input type="Submit" value="Lizenzen bestellen">
</td>
</tr>
</form>
</table>
</td></tr>
<?php
  mysql_close($StuPlaDB);
  include("include/footer.inc.php");
?>