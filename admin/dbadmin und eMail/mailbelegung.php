<!doctype html public "-//W3C//DTD HTML 4.0 //EN">
<html>
<head>
<title>Anzeige der Mailbelegung</title>
<meta name="author" content="cGriep">
<meta name="generator" content="Ulli Meybohms HTML EDITOR">
<link rel="stylesheet" href="../styles/grundlage.css" type="text/css">
</head>
<body text="#000000" bgcolor="#FFFFFF" link="#FF0000" alink="#FF0000" vlink="#FF0000">

<table width="90%">
<tr><th class="mitrahmen">User</th><th class="mitrahmen">Größe Postfach</th>
<th class="mitrahmen">Datum/letzter Zugriff</th></tr>
<?php
  $handle = @opendir("/var/mail");
  $belegt = 0;
  $gesamtanz = 0;
  while ($file = @readdir ($handle))
  {
    if (eregi("^\.{1,2}$",$file))
    {
      continue;
    }
    $gesamtanz++;
    echo "<tr><td class=\"mitrahmen\">";
    echo "$file</td><td>";
    $belegt = filesize("$file") + $belegt;
    printf("%d Bytes", filesize("$file"));
    echo "</td><td class=\"mitrahmen\">";
    echo date("d.m.y", fileatime("$dir/$file"));
    echo "</td></tr>";
  }
  @closedir($handle);
?>
</table>
<hr><p align=center><k><?php echo $gesamtanz; ?> Benutzer belegen
<?php echo $belegt; ?> Bytes</k></p><hr>

</body>
</html>