<?php

$Ueberschrift = 'Umfrage zum beruflichen Werdegang';
define('USE_KALENDER', 1);
$HeaderZusatz = '<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszimt.css">';
include('include/header.inc.php');
echo '<tr><td>';
mysql_close($db);
// Rechte f�r den Benutzer
// Einf�gen in T_Werdegang_EMails
// Select auf T_Schueler (Name, Vorname, Klasse, Geburtsdatum)
$dbUser = "werdegang";
$dbPassword = 'g&7rtG4$';

// Datenbank �ffnen
$db = mysql_connect('localhost', $dbUser, $dbPassword);
mysql_select_db($dbName, $db);
/*
if ( ! isset($_REQUEST['UID']))
{
	echo '<div class="Fehler">Es wurde keine Identifikation �bergeben!</div>';
}
else
{
	$query = mysql_query('SELECT * FROM T_Werdegang_Einladungen WHERE Einladung_id="'.
	mysql_real_escape_string('UID').'"');
	/* TODO
	Einladung_id vergleichen
	Datum
	EMail rausfiltern
	�ber EMail aus Werdegang den Namen herausfinden
	*/
	?>
	
<p>Liebe ehemalige Sch�lerin, lieber ehemaliger Sch�ler,<br />
vielen Dank dass Sie bereit sind, uns bei unserer Arbeit zu
unterst�tzen. Es ist uns sehr wichtig etwas �ber Ihren weiteren
beruflichen Werdegang zu erfahren um daraus f�r unsere Arbeit Schl�sse
zu ziehen und m�glicherweise Ver�nderungen einzuleiten. Deswegen bitten
wir Sie, folgende Fragen zu beantworten:</p>

<fieldset>
<legend>Aktuelle Besch�ftigung</legend>
<input type="radio" name="Beschaeftigung" value="S" /><label>Studium</label>
<input type="radio" name="Beschaeftigung" value="B" /><label>Berufsoberschule</label>
<input type="radio" name="Beschaeftigung" value="E" /><label>Bundeswehr/Zivildienst</label>
<input type="radio" name="Beschaeftigung" value="A" /><label>arbeitslos</label>
</fieldset>

Haben Ihnen die in unserer Schule erworbenen Kenntnisse, Fertigkeiten und Kompetenzen bisher geholfen?
<fieldset>
<input type="radio" name="Hilfe" value="J" /><label>Ja</label>
<input type="radio" name="Hilfe" value="N" /><label>Nein</label>
</fieldset>

Haben Ihnen die in unserer Schule erworbenen methodischen Kompetenzen
bisher geholfen? 
<fieldset>
<input type="radio" name="Methode" value="N" /><label>nein</label>
<input type="radio" name="Methode" value="J" /><label>ja, besonders</label>
<input type="checkbox" name="Methode" value="P" /><label>Pr�sentieren</label>
<input type="checkbox" name="Methode" value="T" /><label>Teamarbeit</label>
<input type="checkbox" name="Methode" value="E" /><label>Eigenst�ndiges Arbeiten</label>
<input type="checkbox" name="Methode" value="S" /><label>Sonstige</label>
</fieldset>

<?php
echo '</td></tr>';
include('include/footer.inc.php'); 

?>
