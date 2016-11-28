<?php
/**
 * Zeigt die bestellten Lizenzen an und ermöglicht das Löschen von Bestellungen
 * (c) 2006 Christoph Griep
 * 
 */
  $Ueberschrift = "Bestellübersicht";
  include("include/header.inc.php");
  include("msdnaaconfig.inc.php");
  echo '<tr><td>';
  $Produkte = holeProdukte();
?>
<div class="titel">Bestellungen (Stand: <?=date("d-m-Y H:i")?>)</div>

<?php
  if ( isset($_REQUEST["Delete"]) && is_array($_REQUEST["Del"]))
  {
    // Eintrag löschen
    foreach ( $_REQUEST["Del"] as $key => $value )
      mysql_query("DELETE FROM T_Antraege WHERE id = ".$key);
  }
  if ( ! isset($_REQUEST["Sort"] )) $Sort = "Vertragsnummer, Produkt";
    else
  $Sort = mysql_real_escape_string($_REQUEST["Sort"]);
  $Where = "";
  if ( isset($_REQUEST["Wer"]) && is_numeric ( $_REQUEST["Wer"] )) 
    $Where = "WHERE Vertragsnummer = ".mysql_real_escape_string($_REQUEST["Wer"]);
  $query = mysql_query("SELECT * FROM T_Antraege $Where ORDER BY $Sort");
  echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
  echo '<input type="Submit" name="Delete" value="Markierte Löschen"/>';
  echo '&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER["PHP_SELF"].'?Mark=1">Zum Löschen gekennzeichnete markieren</a><br />';
  echo '<table border="1">';
  echo '<tr><th><a href="?Sort=Vertragsnummer">Vertrag</a></th>'.
    '<th><a href="?Sort=Name">Name</a></th><th><a href="?Sort=Art">Klasse</a>'.
    '</th><th><a href="?Sort=Eingang">Eingang</a></th><th><a href="?Sort=Produkt">Produkt</a>'.
    '</th><th>Hinweise</th></tr>';
  while ( $row = mysql_fetch_object($query))
  {
    echo '<tr><td><a href="index.php?AntragNr='.$row->Vertragsnummer.'" title="Antrag bearbeiten">'.
      $row->Vertragsnummer.'</a> (<a href="../Lizenz/Antragsformular.php?Antragnr='.$row->Vertragsnummer.'" title="Antragsformular anzeigen">A</a>)</td><td>';
    echo $row->Name.", ".$row->Vorname;
    echo "</td><td>".$row->Art."</td>";
    echo "<td>".$row->Eingang."</td>";
    echo "<td>".$Produkte[$row->Produkt] ."</td>";
    echo "<td>";
    if ( $row->Bemerkungen != "" ) echo $row->Bemerkungen."<br />";
    echo "<small>".$row->Ansprechpartner."</small></td>";
    echo '<td><input type="Checkbox" name="Del['.$row->id.']" value="v" ';
    if ( isset($_REQUEST["Mark"]) &&
         strpos($row->Bemerkungen, "Kann gelöscht werden,") !== false )
           echo 'checked="checked"';
    echo '/>';
    echo "</td>\n";
    echo "</tr>\n";
  }
  echo mysql_num_rows($query)." Produkte bestellt.<br />";
  mysql_free_result($query);
  echo "</table><br /><br />";
  if ( isset($_REQUEST["Wer"]) )
    echo '<input type="hidden" name="Wer" value="'.$_REQUEST["Wer"].'" />';
  echo '<input type="Submit" name="Delete" value="Markierte Löschen"/>';
  echo '&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER["PHP_SELF"].'?Mark=1">Zum Löschen gekennzeichnete markieren</a><br />';
  echo '</form>';
  echo '</td></tr>';
  include("include/footer.inc.php");
?>