<?php
/*
 * Created on 06.09.2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 $Ueberschrift = 'Exkursionen vorbereiten';
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszimt.css">';
define('USE_KALENDER', 1);
include('include/header.inc.php');
include('include/Vertretungen.inc.php');

echo '<tr><td>';

echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
echo '<label>Klasse</label>';
echo '<label>Lehrer</label>';
echo '<input type="submit" value="hinzufügen" />';
echo '</form>';

echo '</td></tr>';
include('include/footer.inc.php');
?>
