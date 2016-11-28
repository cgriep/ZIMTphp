<?php
/*
 * Created on 28.09.2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 $dbUser = 'stellenboerse';
 $dbPassword = 'ePGBR9LEh,,Vf3Wz';
 $db = mysql_connect("localhost", $dbUser, $dbPassword);
 mysql_select_db('Stellenboerse');


include_once("include/oszframe.inc.php");

echo ladeOszKopf_o('Stellenangebote', 'Stellenangebote');
echo '<style type="text/css">
@import url(http://css.oszimt.de/oszimt.css) screen, print;
</style>';
echo '<p>Die hier aufgeführten Stellenangebote werden von externen Anbietern angeboten. Das 
OSZ IMT hat übernimmt keinerlei Verantwortung für den Inhalt und die 
Richtigkeit der hier angebotenen Stellen. Bei Fragen setzen Sie sich bitte 
direkt mit der anbietenden Firma in Verbindung, deren Adresse Sie aus der
jeweiligen Ausschreibung entnehmen können.</p>';

echo '<p>Wenn Sie selbst eine Stelle anbieten möchten, die für unsere Schüler
geeignet sein könnte, nehmen Sie bitte 
Kontakt zur passenden <a href="http://www.oszimt.de/0-schule/kontakte/funktion.html">
Abteilungsleitung</a> auf.</p>';

// zu alte Stellenangebote löschen 
mysql_query('DELETE FROM T_Stellenangebote WHERE Datum<'.time());

$query = mysql_query('SELECT * FROM T_Stellenangebote ORDER BY Datum DESC');
if ( mysql_num_rows($query) > 0)
{
  echo '<table class="Liste">';
  echo '<tr><th>Titel</th><th>Beschreibung</th><th>Link zur Ausschreibung</th></tr>';
  while ( $row = mysql_fetch_array($query))
  {
    echo '<tr><td>';
    echo $row['Titel'].'</td><td>';
    echo nl2br($row['Beschreibung']).'</td><td>';
    echo '<a href="Stellenangebotzeigen.php?id='.$row['Stellen_id'].
      '" target="_blank">'.$row['Mime'].'</a></td></tr>';
  } // while 
  echo '</table>';
}
else
  echo '<div class="Hinweis">Zur Zeit sind keine Stellenangebote eingetragen.</div>';

mysql_free_result($query);

echo ladeOszKopf_u();

echo ladeLink("http://www.oszimt.de","<b>Home</b>");
echo ladeOszFuss();


?>

