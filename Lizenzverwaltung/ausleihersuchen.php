<?php
/**
 * Zeigt Ausleiher nach Namen oder Klasse an 
 * (c) 2006 Christoph Griep
 */
$Ueberschrift = "Ausleiher suchen";
include("include/header.inc.php");
include("msdnaaconfig.inc.php");
$Produkte = holeProdukte();
if ( ! isset($_REQUEST["AusName"] ))
{
?>
<tr><td>
<form action="<?=$_SERVER["PHP_SELF"]?>" method="post">
Daten für <em>namentlich bekannten</em> Ausleiher oder <em>Klasse</em> anzeigen
<input type="Text" name="AusName">
<input type="Submit" value="suchen">
</td></tr></form>
<?php
}
else
{
  echo '<tr><td>';
  if ( isset($_REQUEST["MailVN"]) && isset($_REQUEST["An"]) )
  {
  	// Mail mit Lizenz senden  
  	include_once("Lizenz.inc.php");
  	VerschickeMail($_REQUEST["An"], $_REQUEST["AusName"], explode(",",$_REQUEST["MailVN"]));
  }
  echo '<h2>Suche nach '.$_REQUEST["AusName"].'</h2>';
  if ( ! isset($_REQUEST["Sort"] )) $Sort = "Art, Name";
    else
  $Sort = mysql_real_escape_string($_REQUEST["Sort"]);
  $Suche = mysql_real_escape_string($_REQUEST["AusName"]);
  $query = mysql_query("SELECT * FROM T_Lizenznehmer INNER JOIN T_Produkte ON " .
     "T_Lizenznehmer.ProduktID = T_Produkte.id  ".
     " WHERE (Name LIKE '%$Suche%' OR Vorname LIKE '%$Suche%' OR Art LIKE '%$Suche%') ".
     "ORDER BY $Sort");
  echo '<table border="1">';
  echo '<tr><th><a href="?Sort=Name">Name</a></th><th><a href="?Sort=Art">Klasse</a>'.
    '</th><th><a href="?Sort=Datum">Datum</a></th><th><a href="?Sort=Produkt">Produkt</a>'.
    '</th><th>Key</th><th><a href="?Sort=Vertragsnummer">Vertrag</a></th><th>Lehrer</th></tr>';
  $Antraege = array();
  $Lehrer = array();
  while ( $row = mysql_fetch_object($query))
  {
    echo "<tr><td>";
    echo $row->Name.", ".$row->Vorname;
    echo "</td><td>".$row->Art."</td>";
    echo "<td>".$row->Datum."</td>";
    echo "<td>".$row->Produkt."</td>";
    echo "<td>".$row->Serialkey."</td>";
    echo '<td align="center"><a href="Lizenznummern.php?VN='.$row->Vertragsnummer.'">'.$row->Vertragsnummer."</a></td>";
    echo '<td><a href="'.$_SERVER["PHP_SELF"].'?MailVN='.$row->Vertragsnummer."&An=".$row->Ansprechpartner;
    echo '&AusName='.$_REQUEST["AusName"];
    echo '" title="Lizenz mailen">'.$row->Ansprechpartner.'</a></td>';
    if ( ! in_array($row->Vertragsnummer, $Antraege))   
      $Antraege[] = $row->Vertragsnummer;
    if ( ! in_array($row->Ansprechpartner, $Lehrer))
      $Lehrer[] = $row->Ansprechpartner;
    echo "</tr>";
  }
  foreach ( $Lehrer as $L)
    echo '<tr><td colspan="7"><a href="'.$_SERVER["PHP_SELF"].
      '?MailVN='.implode(",",$Antraege)."&AusName=".$_REQUEST["AusName"].
      "&An=".$L.'">Alle an '.$L.' mailen</a></td></tr>';
  echo "</table>\n";
  echo "<br /><br />";
  echo "<strong>".mysql_num_rows($query)." Datensätze gefunden.</strong>";
  mysql_free_result($query);
  echo '</td></tr>';
}
include("include/footer.inc.php");
?>