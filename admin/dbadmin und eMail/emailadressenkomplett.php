<!doctype html public "-//W3C//DTD HTML 4.0 //EN">
<html>
<head>
<title>eMail-Liste OSZIMT</title>
<meta name="author" content="cGriep">
<meta name="generator" content="Ulli Meybohms HTML EDITOR">
<link rel="stylesheet" href="../formate.css" type="text/css">
</head>
<body text="#000000" bgcolor="#F8F8F8" link="#6699cc" alink="#6699cc" vlink="#6699cc">
<table height = "1%" cellpadding = "5" border = "0" width = "100%" bgcolor = "#eeeeee">
<tr>
 <td width = "1%">
          <a href="http://www.oszimt.de/"><img src="/image/logo.gif" alt="OSZ IMT (Logo)" border="0"></a>
 </td>
 <td>
         <span class = "osz">Oberstufenzentrum<br>Informations- und Medizintechnik</span>
 </td>
</tr>
</table>
<!-- Ende OSZIMT Kopf -->
<table cellpadding = "20" border = "0" width = "100%" bgcolor = "#dddddd">
<tr>
 <td align="center" class="link"><a href="/">Zurück zur Startseite des Lehrerbereichs.</a></td>
</tr>
<tr>
 <td align = "center">
 <span class = "ueberschrift"><b>eMail-Liste OSZ IMT</b><br />(nur für internen Gebrauch)<br />
 Stand: <?=date("d.m.Y")?></span>
 </td>
</tr>
<tr><td>
<table border="1" style="border-style:solid;border-collapse:collapse;border-color:black;
border-width=1pt;border-spacing=0pt" bgcolor="#dddddd">
<?php

  if (! $db = mysql_connect("localhost", "confixx", "SZrkMphG") )
  {
     die("Keine Datenbankverbindung: ".mysql_error()."<br />Ohne Datenbankverbindung kann nicht weitergearbeitet werden!");
  }
  mysql_select_db("confixx", $db);
  echo '<tr style="border-color:black">
  <th style="border-color:black"';
  echo 'colspan="2" align="center" class="ueberschrift">';
  echo "</th></tr>";
  echo '<tr class="link-ueberschrift"><td valign="top"
  style="border-color:black"><b>Name</b></td><td
  style="border-color:black">
  <b>eMail</b><br /><i>(wenn mehrere Adressen aufgeführt werden,
  kann eine beliebige verwendet werden)</i></td>';
  echo "</tr>";
  // Sortierkriterium angegeben?
  $sql = "select kommentar, pop3, prefix, domain from email left join pop3 on email.pop3 = " .
      "pop3.account where email.kunde = 'web2' ORDER BY kommentar, prefix";
  if ( ! $query = mysql_query($sql, $db))
  {
    die("Fehler bei Datenbankabfrage: ".mysql_error());
  }
  $farbe = "#dddddd";
  $Letztes = "";
  while ( $data = mysql_fetch_array($query))
  {
    if ( $data[pop3] != $Letztes )
    {
      if ( $Letztes != "" ) {
        echo "</select></td></tr>";
      }
      if ( $farbe == "#dddddd" ) $farbe = "#afafaf";
      else $farbe = "#dddddd";
      $Letztes = $data[pop3];
      echo '<tr bgcolor="'.$farbe.'" style="border-color:black">';
      echo '<td style="border-color:black">';
      if ( trim($data[kommentar]) == "" )
        echo "-Weiterleitung an ".$data[prefix]."-";
      else
        echo stripslashes($data[kommentar]);
      echo '</td><td style="border-color:black;border-width=1pt;border-spacing=0pt">';
    }
    else
      echo ", ";
    echo $data[prefix]."@".$data[domain];
  }
  echo "</select></td></tr>";
  mysql_free_result($query);
?>
</table>
<span class="link-tipp"><small><small>Fehler bitte <a href="mailto:griep@oszimt.de">
mailen</a>. Seite automatisch erstellt für <?=$_SERVER["REMOTE_USER"]?> am <?php echo date("d.m.y") ?>
</small></small>&nbsp; </span>
</td></tr>
<tr>
 <td align="center" class="link"><a href="/">Zurück zur Startseite des Lehrerbereichs.</a></td>
</tr>
</table>

</body>
</html>