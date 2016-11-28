<?php
/** 
 * Gibt eine Lizenz wieder frei. Dabei wird sie in die Liste der freien Lizenzen
 * aufgenommen, wenn es sich um eine Einzellizenz handelt
 * (c) 2006 Christoph Griep
 * 
 */
$Ueberschrift = "Lizenzen freigeben";
include("include/header.inc.php");
include("msdnaaconfig.inc.php");
?>
<form action="<?=$_SERVER["PHP_SELF"]?>" method="post">
<tr><td>
Vertragsnummer <input type="text" name="VN" ><br />
<input type="Submit" value="Anzeigen">
</td></tr>
</form>
<tr><td>
<?php
if ( isset($_REQUEST["VN"]) && is_numeric($_REQUEST["VN"]) ) {
  if ( is_numeric($_REQUEST["ID"]) ) {
    if ( $qu = mysql_query("SELECT ProduktID, Serialkey " .
    		"FROM T_Lizenznehmer " .
    "WHERE id = ".$_REQUEST["ID"]) ) {
    $row = mysql_fetch_array($qu);
    echo "Freie Lizenznummer: ".$row["Serialkey"]." (id ".$_REQUEST["ID"].")<br />";
    if ( mysql_query("DELETE FROM T_Lizenznehmer WHERE id=".$_REQUEST["ID"]))
      if ( ! mysql_query("INSERT INTO T_Lizenznummern (ProduktID, Serialkey, Art) VALUES (".
        $row[ProduktID].",'".$row["Serialkey"]."','Student')") )
        echo "Fehler beim Einfügen der freien Lizenznummer: ".mysql_error();
      else echo "Seriennummer eingefügt.<br />";
    }
    else echo mysql_error();
    mysql_free_result($qu);
  }
  $qu = mysql_query("SELECT T_Lizenznehmer.id, Produkt, Serialkey " .
  		"FROM T_Lizenznehmer INNER JOIN T_Produkte ON " .
    "T_Lizenznehmer.ProduktID = T_Produkte.id WHERE Vertragsnummer = ".$_REQUEST["VN"]);
  echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
  echo '<select name="ID">';
  while ( $row = mysql_fetch_array($qu) )
  {
    echo '<option value="'.$row["id"].'">'.$row["Produkt"].' ('.$row["Serialkey"].')</option>';
  }
  mysql_free_result($qu);
  echo "</select><br />";
  echo '<input type="hidden" name="VN" value="'.$_REQUEST["VN"].'">';
  echo '<input type="Submit" value="Freigeben"><br />';
  echo '</form>';
}
echo '</td></tr>';
include("include/footer.inc.php");
?>