<!doctype html public "-//W3C//DTD HTML 4.0 //EN">
<html>
<head>
<title>eMail-Liste OSZIMT</title>
<meta name="author" content="cGriep">
<meta name="generator" content="Ulli Meybohms HTML EDITOR">
<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/style.css">
</head>
<body text="#000000" bgcolor="#FFFFFF" link="#FF0000" alink="#FF0000" vlink="#FF0000">

<p><table width=90% align=center>
<tr>
<?php if( !isset($_REQUEST["Druck"])) echo '<th class=gelb>eMaillisten OSZIMT</th>'; ?>
</tr>
<?php
  //if ( $_SERVER["REMOTE_USER"] != "" )
  //  echo "User: " . $_SERVER["REMOTE_USER"];

  if (! $db = mysql_connect("localhost", "confixx", "SZrkMphG") )
  {
     die("Keine Datenbankverbindung: ".mysql_error()."<br />Ohne Datenbankverbindung kann nicht weitergearbeitet werden!");
  }
  mysql_select_db("confixx", $db);
  $Veranstaltung = "EMailliste";
  if ( !isset($_REQUEST["Druck"] ))
  {
    echo "<tr ><td class=mitrahmen>";
    echo '<a href="' . $_SERVER["PHP_SELF"] . '?Liste=eMail">eMailliste</a> ';
    echo "</td></tr>";
    echo '<tr ><form action="'.$_SERVER["PHP_SELF"].'" method="post"><td class=mitrahmen>';
    echo 'Kunde <input type="text" name="konto" value="web2" /> <br />';
    echo '<input type="hidden" name="Liste" value="Kom"/> <br />';
    echo '<input type="submit" value="anzeigen"/> <br />';
    echo "</td></form></tr>";
  }
?>
</table>
</p>
<p>
<?php
if ( isset($_REQUEST["Liste"]))
{
  echo "<table width=90% align=center>";
  echo "<tr><th class=blau ";
  $Auswahlkriterien = "";
  $Summe = "";
  unset($Markierung);
  if ( $_REQUEST['konto'] != "" ) $Auswahlkriterien = " email.kunde = '".$_REQUEST["konto"]."'";
  switch ( $_REQUEST["Liste"] )
  {
    case "eMail": $Ueberschrift = "eMailliste";
               $Felder = "prefix|domain|kommentar|pop3";
               $Sort = "pop3,prefix";
               //$Markierung = "NSC";
               //$MarkierungsWert = true;
               break;
    case "Kom": $Ueberschrift = "eMailliste nach Kommentar";
               $Felder = "kommentar|pop3|prefix|domain";
               $Sort = "kommentar,pop3,prefix";
               //$Markierung = "NSC";
               //$MarkierungsWert = true;
               break;
    default:
      $Ueberschrift = "Keine Liste gewählt";
      $Felder = "prefix|domain|kommentar|pop3";
      $Sort = "prefix";
  }
  if ( $Auswahlkriterien != "" )
    $Auswahlkriterien = " WHERE " . $Auswahlkriterien;

  $Feld =  strtok($Felder, "|");
  $Feldanzahl = 0;
  while ($Feld )
  {
    // ausgeblendete Felder
    if ( strpos($Feld, "-") === false ) $Feldanzahl++;
    if ( $Feld == $Summe ) $Summenfeld = $Feldanzahl;
    $Feld = strtok("|");
  }
  echo "colspan=$Feldanzahl align=center>$Ueberschrift<br><big><big>$Veranstaltung</big></big>";
  echo "</th></tr>";
  echo "<tr>";
  $Feld =  strtok($Felder, "|");
  $Feldnamen = "";
  $Feldanzahl = 0;
  while ($Feld)
  {
    if ( ! (strpos($Feld, "-") === false) )
    {
       $Feldnamen .= substr($Feld, 1) . ", ";
    }
    else if ( strpos($Feld,">") === false )
    {
      $Feldanzahl++;
      $Feldnamen .= $Feld . ",";
      if ( strpos($Feld, ".") > 0 ) $Feld = substr($Feld,strpos($Feld,".")+1);
      echo "<th align=center class=mitrahmen>";
      if ( !isset($_REQUEST["Druck"]))
      {
        $s = $_SERVER["QUERY_STRING"];
        if ( strpos($s, "&Sort=" ) === false )
        {
          // kein Sortierfeld gefunden
          if ( $_POST['konto'] != "" )
            $s = "konto=" . $_POST['konto'] . "&Liste=Kom";
        }
        else
        {
          $anf = substr($s, 0, strpos($s, "&Sort="));
          $s = strstr($s, "&Sort=");
          $s = substr($s,1); // = entfernen
          if ( strpos($s, "&") === false )
            $s = "";
          else
            substr($s, strpos($s, "&"));
          $s = $anf . $s;
        }
        echo '<a href="' . $_SERVER["PHP_SELF"] . "?" . $s . "&Sort=$Feld\">";
      }
      echo $Feld;
      if ( !isset($_REQUEST["Druck"]))
        echo "</a>";
      echo "</td>";
    }
    else
    {
       $Feld = substr($Feld, 1);
       $Len = substr($Feld, 0, strpos($Feld, ">"));
       $Feld = substr($Feld, strpos($Feld, ">"));
       echo "<th align=center class=mitrahmen width=$Len%>" . substr($Feld, 1). "</td>";
    }
    $Feld = strtok("|");
  }
  //$Feldnamen .= "Spieler.ID";
  $Feldnamen = substr($Feldnamen, 0, strlen($Feldnamen)-1);
  echo "</tr>";
  // Sortierkriterium angegeben?
  if ( isset($_GET["Sort"]) )
    $Sort = $_GET["Sort"];
  $sql = "select $Feldnamen from (email left join email_forward ".
    "on ident=email_ident) left join pop3 on email_forward.pop3 = " .
      "pop3.account $Auswahlkriterien ORDER BY $Sort";
  //echo $sql."<br>";
  if ( ! $query = mysql_query($sql, $db))
  {
    die("Fehler bei Datenbankabfrage: ".mysql_error());
  }
  $Summen = 0;
  $farbe = "#ffffff";
  $Letztes = "";
  while ( $data = mysql_fetch_array($query))
  {
    if ( $data[$Feld] != $Letztes )
    {
      if ( $farbe == "#ffffff" ) $farbe = "#afafaf";
      else $farbe = "#ffffff";
      $Letztes = $data[$Feld];
    }
    echo '<tr bgcolor="'.$farbe.'"';
    echo ">";
    $Feld = strtok($Felder, "|");
    $Feldanzahl = 0;

    while ( $Feld )
    {
      if ( strpos($Feld, ".") > 0 ) $Feld = substr($Feld,strpos($Feld,".")+1);
      if ( $Feld == $Summe ) $Summen += $data[$Feld];
      if ( strpos($Feld, "-") === false )
      {
        echo '<td class=mitrahmen';
        if ( strpos($Feld, ">" ) === false && strpos($Feld, "*") === false )
        {
          echo ">";
          echo stripslashes($data[$Feld]);
        }
        else
        {
          echo ">"; // >- Feld (nur Überschrift
        }
        echo '</td>';
        $Feldanzahl++;
      }
      $Feld = strtok("|");
    }
    echo "</tr>";
  }
  mysql_free_result($query);
  if ( $Summe != "" )
  {
    echo "<tr>";
    for ($i = 0 ; $i <= $Feldanzahl; $i++)
    {
      echo "<td";
      if ( $i == $Summenfeld )
        echo " align=right class=mitrahmen><b>$Summen</b>";
      else
        echo ">";
      echo "</td>";
    }
    echo "</tr>";
  }
  echo "</table>";
  echo "</p>";
  // Bei Unterbringung Gesamtanzahl anzeigen
  if ( !isset($_REQUEST["Druck"]))
  {
  echo '<div align="center"><a href="' . $_SERVER["PHP_SELF"].
    '?Druck=J&' . $s.'">Druckversion</a>';
  echo " / ";
  echo '</div>';
  }
}
?>

<address><small><small>Fehler bitte <a href="mailto:griep@oszimt.de">
mailen</a>. Seite automatisch erstellt am <?php echo date("d.m.y") ?>
</small></small>&nbsp; </address>

</body>
</html>