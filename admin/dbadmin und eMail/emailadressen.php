<?php
$Ueberschrift = "eMail-Liste OSZIMT";
include("include/header.inc.php");

?>
<tr>
 <td align="center"><a href="/">Zurück zur Startseite des Lehrerbereichs.</a></td>
</tr>
<tr>
 <td align = "center">(nur für internen Gebrauch)<br />
 Stand: <?=date("d.m.Y")?>
 </td>
</tr>
<tr><td align="center">
<table border="1" style="border-style:solid;border-collapse:collapse;border-color:black;
border-width=1pt;border-spacing=0pt" bgcolor="#dddddd">
<tr class="link-ueberschrift"><th valign="top" style="border-color:black">Name</th>
<th style="border-color:black">
eMail<br /><em>(alternative Adressen werden nach Aufklappen des Feldes angezeigt)</em></th>
<th>letzer Zugriff<br />(in Tagen)</th>
</tr>
<?php

  mysql_select_db("confixx", $db);
  // Sortierkriterium angegeben?
  $sql = "select kommentar, pop3, prefix, domain from (email left join email_forward ".
    "on ident=email_ident) left join pop3 on email_forward.pop3 = " .
      "pop3.account where email.kunde = 'web2' ORDER BY kommentar, pop3, prefix, domain DESC";
  if ( ! $query = mysql_query($sql, $db))
  {
    die("Fehler bei Datenbankabfrage: ".mysql_error());
  }
  $farbe = "#dddddd";
  $Letztes = "";
  $da = false;
  while ( $data = mysql_fetch_array($query))
  {
    if ( $data["pop3"] != $Letztes )
    {
      if ( $Letztes != "" ) {
        echo '<td align="center"><img src="http://skripte.oszimt.de/mailsize.pl?wer='.$Letztes.'"></td>';
        echo "</select></td></tr>";
        $da = false;
      }
      if ( $farbe == "#dddddd" ) $farbe = "#afafaf";
      else $farbe = "#dddddd";
      $Letztes = $data["pop3"];
      echo '<tr bgcolor="'.$farbe.'" style="border-color:black">';
      echo '<td style="border-color:black">';
      echo '<a href="mailto:'.$data["prefix"]."@".$data["domain"].'">';
      if ( trim($data["kommentar"]) == "" )
        echo "-Weiterleitung an ".$data["prefix"]."-";
      else
        echo stripslashes($data["kommentar"]);
      echo '</a>';
      echo '</td><td style="border-color:black;border-width=1pt;border-spacing=0pt">';
      echo '<select>';
    }
    echo '<option ';
    $name = strtoupper($data["kommentar"]);
    $name = str_replace("ä", "ae", $name);
    $name = str_replace("ü", "ue", $name);
    $name = str_replace("ö", "oe", $name);
    $name = str_replace("Ä", "AE", $name);
    $name = str_replace("Ü", "UE", $name);
    $name = str_replace("Ö", "OE", $name);
    if ( strpos($name, strtoupper($data["prefix"])) === false || $da ) echo "";
    else { echo ' selected="selected"'; $da = true; }
    echo '>'.$data["prefix"]."@".$data["domain"].'</option>';
  }
  echo "</select></td>";
  echo '<td align="center"><img src="http://skripte.oszimt.de/mailsize.pl?wer='.$Letztes.'"></td>';
  echo "</tr>";
  mysql_free_result($query);
?>
</table>
</td></tr>
<tr><td class="funktion" align="center">Fehler bitte <a href="mailto:griep@oszimt.de">
mailen</a>. Seite automatisch erstellt für <?=$_SERVER["REMOTE_USER"]?> am <?php echo date("d.m.y") ?>
</td></tr>
<tr>
 <td align="center"><a href="/">Zurück zur Startseite des Lehrerbereichs.</a></td>
</tr>
<?php
include("include/footer.inc.php");
?>