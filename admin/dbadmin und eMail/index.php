<?php
include("header.inc.php");
echo '<tr><td>';
if ( isset($_POST["DB"]) && isset($_POST["KW"]) && isset($_POST["User"]) && isset($_POST["EMail"]))
{
  $User = trim($_POST["User"]);
  $KW = trim($_POST["KW"]);
  $DBName = trim($_POST["DB"]);
  $EMail = trim($_POST["EMail"]);
  if ( !$db )
  {
    echo "Es konnte keine Datenbankverbindung erstellt werden!";
  }
  else
  {
    $Fehler = false;
    $db_list = mysql_list_dbs($db);
    while ($row = mysql_fetch_object($db_list)) {
      if ( $row->Database == $DBName )
      {
        echo "Die Datenbank $DBName gibt es schon!";
        $Fehler = true;
        break;
      }
    }
    mysql_free_result($db_list);
    if ( ! $Fehler )
    if ( mysql_create_db($DBName, $db) )
    {
      echo "Datenbank $DBName erfolgreich angelegt.<br>";
      mysql_select_db("mysql", $db);
      // Neuen Benutzer ´ohne Rechte einfügen
      $sql = "INSERT INTO user (Host, User, Password) VALUES ('localhost','$User',PASSWORD('$KW'))";
      if ( mysql_query($sql, $db) )
      {
        echo "Benutzer $User erfolgreich eingerichtet.<br>";
        if ( mysql_query("FLUSH PRIVILEGES", $db) )
            echo "Rechte aktualisiert<br>";
          else
        {
            echo "Rechte konnten nicht aktualisiert werden!<br>";
            echo mysql_error();
            $Fehler = true;
        }
        // Rechte aufdie Datenbank setzen
        $sql = "GRANT ALL PRIVILEGES ON $DBName.* TO '$User'@localhost";
        if ( mysql_query($sql, $db) )
        {
          echo "Datenbankrechte erfolgreich gesetzt.<br>";
          // Rechteverwaltung aktualisieren
          if ( mysql_query("FLUSH PRIVILEGES", $db) )
            echo "Rechte aktualisiert<br>";
          else
          {
            echo "Rechte konnten nicht aktualisiert werden!<br>";
            echo mysql_error();
            $Fehler = true;
          }
        }
        else {
          echo "Datenbankrechte konnten nicht vergeben werden!<br>";
          echo mysql_error();
          $Fehler = true;
        }
      }
      else
      {
        echo "Benutzer $User konnte nicht eingerichtet werden!<br>";
        echo mysql_error();
        $Fehler = true;
      }
    }
    else
    {
      echo "Datenbank konnte nicht angelegt werden!<br>";
      echo mysql_error();
      $Fehler = true;
    }
    if ( ! $Fehler )
    {
      if ( mail($EMail, "Datenbankeinrichtung $DBName",
        "Lieber zukünftiger Datenbanknutzer,\n" .
        "die von dir gewünschte Datenbank '$DBName' ist eingerichtet worden. Der " .
        "Benutzername zum Zugriff auf die Datenbank lautet '$User', das Kennwort " .
        "ist '$KW'.\n" .
        "Bitte beachte, dass der Benutzername nur zum Zugriff auf die Datenbank " .
          "geeignet ist, und nicht mit dem Benutzernamen zum Login auf dem Rechner " .
          "verwechselt werden sollte. Dennoch ist selbstverständlich Nutzername " .
          "und Kennwort geheim zu halten, da sonst anderen Nutzer auf dem Rechner " .
          "Zugriff auf die Datenbank erhalten könnten.\n" .
          "Bitte beachte ebenfalls, dass ein Zugriff in der Standardkonfiguration " .
          "nur vom Server der Schule möglich ist, d.h. Zugriffe von anderen Rechnern " .
          "auf die Datenbank sind nicht möglich. Zum Bearbeiten der Datenbank steht " .
          "phpMyAdmin zur Verfügung. Aus vorgenannten Gründen sind Frontends wie " .
          "MyCC o.ä. nicht zu verwenden, da sie auf anderen Rechnern laufen. " .
          "Solltest du unbedingt Zugriff von anderen Rechnern auf die Datenbank " .
          "brauchen, bitte ich um Mitteilung und kurze Begründung.\n\n" .
          "phpMyAdmin wird unter http://confixx.p15097502.pureserver.info/phpMyAdmin " .
          "aufgerufen. Bei der erscheinenden Kennwortabfrage wird der o.g. " .
          "Benutzername und das Kennwort der Datenbank eingegeben, nicht etwa Benutzername " .
          "oder Kennwort des Webspaces.\n\n" .
          "Weitere Fragen bitte im Forum (http://forum.oszimt.de) stellen. " .
          "Eine zusätzliche Mail an mich mit Verweis auf das Forum gewährleistet " .
          "eine schnelle Antwort.\n" .
          "mit freundlichen Grüßen \n".
          "Christoph Griep",
          "From: datenbank@oszimt.de\nReply-To: griep@oszimt.de\n" .
          "Cc: webmaster@oszimt.de") ) echo "Benachrichtigung an $EMail verschickt<br>";
        else echo "Benachrichtigung an $EMail konnte nicht versendet werden!<br>";
    }
  }
}
?>
</td></tr>
<tr><td>
<form action="<?php echo $PHP_SELF; ?>" method="post">
<table >
<tr class=beige>
 <td> Datenbankname</td>
 <td> <input type="Text" name="DB" value="" size="64" maxlength="64"></td>
</tr>
<tr class=braun>
 <td> Benutzername</td>
 <td> <input type="Text" name="User" value="" size="16" maxlength="16"></td>
</tr>
<tr class=beige>
 <td> Kennwort</td>
 <td> <input type="Text" name="KW" value="<?php echo basename(tempnam("",""));?>" size="16" maxlength="16"></td>
</tr>
<tr class=braun>
 <td> EMail</td>
 <td> <input type="Text" name="EMail" value="" size="30" maxlength="30"></td>
</tr>
<tr class=beige>
 <td> <input type="Submit" name="" value="Datenbank anlegen"></td>
 <td> <input type="Submit" name="" value="Felder leeren"></td>
</tr>
</table>
</form>
</td></tr>
<?php
include("footer.inc.php");
?>