<?php
/* Skript zum Anzeigen aller Dateien und Bilder in einem Verzeichnis inkl. Unterverzeichnisse

  Name: Bilderlisting.php
  Parameter: keine
  Autor: Griep
  Version: 22.10.04
*/

function VerzeichnisAuflisten($Verzeichnis)
{
  $handle=opendir ($Verzeichnis);
  while (false !== ($file = readdir ($handle))) {
    if ( $file != "." && $file != ".." )
    {
      if ( is_dir($file) )
      {
        echo '<tr><td colspan="2"><strong>Verzeichnis '."$file</strong></td></tr>";
        VerzeichnisAuflisten($Verzeichnis."/".$file);
      }
      else
      {
        echo "<tr><td>$file</td><td>";
        echo '<img src="'.$Verzeichnis."/".$file.'" /></td></tr>';
      }
    }
  }
  closedir($handle);
}
?>
<html>
<body>
<table border="1">
<tr><th>Name</th><th>Bild</th></tr>
<?php
   VerzeichnisAuflisten(".");
?>
</table>
</body>
</html>


