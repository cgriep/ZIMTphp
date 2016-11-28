<?php
/**
 * Lieferantenverwaltung für Inventarisierungssystem
 * (c) 2006 Christoph Griep
 */
$Ueberschrift = 'Lieferanten';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszimt.css">';
include('include/header.inc.php');
include('inventar.inc.php');

if ( isset($_REQUEST['ip'] ))
{
  // Speichern
  $ip = $_REQUEST['ip'];
  if ( isset($ip['Lieferant_id']) && is_numeric($ip['Lieferant_id']) )
  {
    // update
    $sql = "UPDATE T_Lieferanten SET Name='".mysql_real_escape_string($ip["Name"])."',";
    $sql .= "Strasse='".mysql_real_escape_string($ip["Strasse"])."',";
    $sql .= "PLZ='".mysql_real_escape_string($ip["PLZ"])."',";
    $sql .= "Ort='".mysql_real_escape_string($ip["Ort"])."',";
    $sql .= "Telefon='".mysql_real_escape_string($ip["Telefon"])."',";
    $sql .= "Telefax='".v($ip["Telefax"])."',";
    $sql .= "Bearbeiter='".mysql_real_escape_string($_SERVER["REMOTE_USER"])."',";
    $sql .= "Ansprechpartner='".mysql_real_escape_string($ip["Ansprechpartner"])."' ";
    $sql .= " WHERE Lieferant_id=".$ip["Lieferant_id"];
  }
  else
  {
    // Neuer Lieferant
    $sql = "INSERT INTO T_Lieferanten (Name,Strasse,PLZ,Ort,Telefon,Telefax,";
    $sql .= "Ansprechpartner, Bearbeiter) VALUES ('";
    $sql .= mysql_real_escape_string($ip["Name"])."','".mysql_real_escape_string($ip["Strasse"])."','";
    $sql .= mysql_real_escape_string($ip["PLZ"])."','".mysql_real_escape_string($ip["Ort"])."','";
    $sql .= mysql_real_escape_string($ip["Telefon"])."','".mysql_real_escape_string($ip["Telefax"])."','";
    $sql .= mysql_real_escape_string($ip["Ansprechpartner"])."','".$_SERVER["REMOTE_USER"]."')";
  }
  if (! mysql_query($sql) ) echo "Fehler $sql: ".mysql_error();
  if ( ! is_numeric($ip["Lieferant_id"] ) )
    $_REQUEST["id"] = mysql_insert_id();
  else
    $_REQUEST["id"] = $ip["Lieferant_id"];
}

if ( isset($_REQUEST["id"]) )
{
  $query = mysql_query("SELECT * FROM T_Lieferanten WHERE Lieferant_id = ".$_REQUEST["id"]);
  if ( ! $lieferant = mysql_fetch_array($query) )
    unset($lieferant);
  mysql_free_result($query);
  echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post" name="Lieferant">';
  if ( isset($lieferant) && $lieferant["Lieferant_id"] > 0)
  {
    echo '<input type="hidden" name="ip[Lieferant_id]" value="'.$lieferant["Lieferant_id"];
    echo '" />';
  }
  echo '<tr><td>';
  echo '<table>';
  echo '<tr><td>Name</td><td>';
  echo '<input type="Text" name="ip[Name]" value="'.stripslashes($lieferant["Name"]);
  echo '" size="60" maxlength="100" />';
  echo '</td></tr><tr><td>';
  echo 'Adresse</td><td>';
  echo '<input type="Text" name="ip[Strasse]" value="'.
    stripslashes($lieferant["Strasse"]);
  echo '" size="40" maxlength="100" />';
  echo '</td></tr><tr><td>';
  echo 'PLZ, Ort</td><td>';
  echo '<input type="Text" name="ip[PLZ]" value="'.stripslashes($lieferant["PLZ"]);
  echo '" size="5" maxlength="5" />';
  echo ' <input type="Text" name="ip[Ort]" value="'.stripslashes($lieferant["Ort"]);
  echo '" size="40" maxlength="50" />';
  echo '</td></tr><tr><td>';
  echo 'Telefon</td><td>';
  echo '<input type="Text" name="ip[Telefon]" value="'.stripslashes($lieferant["Telefon"]);
  echo '" size="25" maxlength="25" />';
  echo '</td></tr><tr><td>';
  echo 'Telefax</td><td>';
  echo '<input type="Text" name="ip[Telefax]" value="'.stripslashes($lieferant["Telefax"]);
  echo '" size="25" maxlength="25" />';
  echo '</td></tr><tr><td valign="top">';
  echo 'Ansprechpartner / Bemerkungen</td><td>';
  echo '<textarea name="ip[Ansprechpartner]" cols="60" rows="5">';
  echo stripslashes($lieferant["Ansprechpartner"]);
  echo '</textarea>';
  echo '</td></tr><tr><td colspan="2">';
  echo 'Stand '.$lieferant["Stand"].' / Bearbeiter '.$lieferant["Bearbeiter"];
  echo '</td></tr><tr><td colspan="2">';
  if ( ! user_berechtigt($lieferant["Bearbeiter"]) )
    echo "Sie sind berechtigt, diese Daten zu ändern.";
  else
    echo '<input type="Submit" value="Speichern" />';
  echo '&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER["PHP_SELF"].'?id=-1">Neuer Lieferant</a> / ';
  echo '</td></tr></table></td></tr>';
  echo '</form>';
  // Zugehörige Informationen speichern
  if ( is_numeric($lieferant["Lieferant_id"]) )
  {
    echo '<tr><td>';
    echo '<h2>Zugehöriges Inventar</h2>';
    $sql = "SELECT * FROM T_Inventar INNER JOIN T_Inventararten ON F_Art_id=Art_id WHERE F_Lieferant_id=".$lieferant["Lieferant_id"]." ORDER BY Bezeichnung";
    if ( ! $query = mysql_query($sql)) echo "Fehler $sql: ".mysql_error();
    echo '<table class="Liste">';
    echo '<tr><th>Bezeichnung</th><th>Art</th><th>Anschaffung</th><th>Inventarnr</th><th>Seriennummer</th>';
    echo '<th>Gewährleistung<br />bis</th><th>Reparaturen</th></tr>';
    while ( $daten = mysql_fetch_array($query) )
    {
      echo '<tr><td><a href="Inventar.php?id='.$daten["Inventar_id"].'">';
      echo stripslashes($daten["Bezeichnung"])."</a></td><td>";
      echo stripslashes($daten["Art"]).'</td><td>';
      if ( $daten["Anschaffungsdatum"] != 0 )
        echo date("d.m.Y",$daten["Anschaffungsdatum"]);
      echo "</td><td>";
      echo stripslashes($daten["Inventar_Nr"])."</td><td>";
      echo stripslashes($daten["Seriennummer"])."</td><td ";
      if ( isset($daten["Gewaehrleistungsdatum"]) && $daten["Gewaehrleistungsdatum"] != 0 )
      {
        if ( $daten["Gewaehrleistungsdatum"] < time() )
          echo ' bgcolor="red">';
        else
          echo ' bgcolor="green">';
        echo date("d.m.Y",$daten["Gewaehrleistungsdatum"]);
      }
      else echo '>';
      echo '</td><td align="center">';
      $qu = mysql_query("SELECT Count(*) FROM T_Reparaturen WHERE F_Inventar_id=".$daten["Inventar_id"]);
      $anz = mysql_fetch_row($qu);
      mysql_free_result($qu);
      if ( ! is_numeric($anz[0]) ) $anz[0] = 0;
      echo $anz[0];
      echo "</td></tr>";
    }
    mysql_free_result($query);
    echo '</table>';
    echo '<tr><td><hr /></td></tr>';
    echo '<tr><td align="center"><a href="'.$_SERVER["PHP_SELF"];
    echo '">zur Übersichtsliste</a></td></tr>';
  }
}
else
{
  // Liste der Lieferanten
  echo '<tr><td>';
  echo '<table class="Liste">';
  echo '<tr><th>Lieferant</th><th>Adresse</th><th>Telefon</th><th>Telefax</th><th>Ansprechpartner</th></tr>';
  $Search = "";
  if ( isset($_REQUEST["Search"] ) && $_REQUEST["Search"] != "" )
  {
    $Search = "WHERE Name REGEXP '".addslashes($_REQUEST["Search"])."'";
  }
  else
    $_REQUEST["Search"] = "";
  $query = mysql_query("SELECT * FROM T_Lieferanten $Search ORDER BY Name");
  while ( $inv = mysql_fetch_array($query) )
  {
    echo '<tr><td><a href="'.$_SERVER["PHP_SELF"].'?id='.$inv["Lieferant_id"].'">';
    if ( trim(stripslashes($inv["Name"])) != "" )
      echo stripslashes($inv["Name"]);
    else
      echo "(unbekannt)";
    echo '</a></td><td>'.stripslashes($inv["Strasse"]).", ".stripslashes($inv["PLZ"]);
    echo " ".stripslashes($inv["Ort"])."</td><td>".stripslashes($inv["Telefon"]).'</td>';
    echo '<td>'.stripslashes($inv["Telefax"]).'</td>';
    echo '<td>'.nl2br(stripslashes($inv["Ansprechpartner"]));
    echo '</td></tr>';
  }
  mysql_free_result($query);
  echo '</table>';
  echo '<a href="'.$_SERVER["PHP_SELF"].'?id=-1">Neuen Lieferant hinzufügen</a> / ';
  echo '<form action="'.$_SERVER["PHP_SELF"].'" method="post">';
  if ( isset($_REQUEST["Liste"]) )
    echo '<input type="hidden" name="Liste" value="'.$_REQUEST["Liste"].'" />';
  echo 'Inventar <input type="Text" name="Search" value="'.$_REQUEST["Search"].'" /> ';
  echo '<input type="Submit" value="Suchen" />';
  echo '</form>';
  echo '</td></tr>';
}
include('include/footer.inc.php');
?>