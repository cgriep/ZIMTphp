<?php
if ( ! isset($_REQUEST["Print"]))
{
?>
<tr><td align="right"><table><tr><td>
<a href="<?php
echo $_SERVER["PHP_SELF"];
$pre = "?";
foreach ( $_REQUEST as $key => $value )
{
  if ( is_array($value) )
    foreach ( $value as $kkey => $vvalue )
    {
      echo $pre.urlencode($kkey).'='.urlencode($vvalue);
      $pre = '&';
    }
  else
    echo $pre.urlencode($key).'='.urlencode($value);
  $pre = '&';
}
echo $pre."Print=1";
?>" target="printerversion">
<img src="http://img.oszimt.de/button/drucken_blau.gif" width="20"
height="20" border="0" alt="Druckansicht"></a></td>
<td valign="middle"><span class="funktion">Druckansicht</span></td><td width="25"></td><td>
<a href="#top"><img src="http://img.oszimt.de/nav/pfeitop_blau.gif"
width="20" height="20" border="0"
alt="zum Seitenbeginn"></a></td></tr></table>
</td></tr>
<?php
} // Ende wenn nicht Print
?>
</table>
</td>

<?php
// rechtsseitiges Navigationsmenü anzeigen.
// Navigationsmenü ist in der Datei menu.txt enthalten
// (Format: Text;Linkname)
// Parameter Print zeigt an, dass das Menü nicht angezeigt werden soll
if ( is_file("menu.txt") && ! isset($_REQUEST["Print"]) )
{
  echo '<td valign="top">';
  echo '<span class="home-rubrik">Navigationsmenü</span><br/>';
  echo "\n<table>\n";
  $file = fopen("menu.txt", "r");
  while ( $zeile = trim(fgets($file)) )
  {
    if ( $zeile != "" )
    {
      $z = explode(";", $zeile);
      echo '<tr><td valign="top">';
      echo '<a class="navlink" href="'.$z[1].'">';
      echo '<img width="10pt" height="12pt" border="0" ';
      echo 'src="http://img.oszimt.de/nav/link-klein.gif" />';
      echo $z[0]."</a></td></tr>\n";
    }
  }
  fclose($file);
  echo '</table></td>';
}
echo '</tr>';
echo '<tr><td align="right" class="small" ';
if( is_file("menu.txt") && ! isset($_REQUEST["Print"]) )
  echo ' colspan="2"';
?>
>
<?="zuletzt geändert<br/>".date ("d.m.Y H:i", filemtime(basename($_SERVER["PHP_SELF"])))?>
<br/>
Fehler bitte an <a href="mailto:binz@oszimt.de">Koll. Binz</a> mailen.
</td></tr>
</table>

</body>
</html>
<?php
  mysql_close($db);
?>
