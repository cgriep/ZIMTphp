<?php

include_once("include/oszframe.inc.php");
include_once("include/evaluation.inc.php");
include_once("include/evaluation.vars.inc.php");
include_once("include/helper.inc.php");
include_once("include/stupla.inc.php");

echo ladeOszKopf_o("OSZ IMT - Unterrichtsbewertung","OSZ IMT - Unterrichtsbewertung");
echo "\n\n<div align = \"center\"><b>[Hilfe]</b></div>";
//--------------------------------------------------------------------------
//-------------------------------INHALT-ANFANG------------------------------
//--------------------------------------------------------------------------
?>

<h4>&nbsp;Fragen und Antworten</h4>
<p><b><a href="hilfe_umfrage.php">&nbsp;1. Wie sieht der Fragebogen für die Schüler aus?</a></b></p>
<p><b><a href="#02">&nbsp;2. Was zeigt die Übersichtsseite im Einzelnen?</a></b></p>
<p><b><a href="#03">&nbsp;3. Wie kann ich eine neue Umfrage anlegen?</a></b></p>
<p><b><a href="#04">&nbsp;4. Wie können Schüler ein Votum abgeben?</a></b></p>
<p><b><a href="#05">&nbsp;5. Können andere meine Ergebnisse einsehen?</a></b></p>

<p>&nbsp;</p>
<hr>
<p>&nbsp;</p>

<h4><a name="02" style="color:black">&nbsp;Was zeigt die Übersichtsseite im Einzelnen?</a></h4>

<table border = "0">
<tr>
<td>
<p><img src="image/hilfe_AlleUmfragen.jpg" alt = "Alle Umfragen" border = "0"></p>
</td>
<td>
<b>1. Spalte (Umf.)</b>
<br>
Zeigt die Anzahl aller vorliegenden Umfragen.
<br><br>
<b>2. Spalte (Le.)</b>
<br>
Zeigt die Anzahl der teilnehmenden Lehrer.
<br><br>
<b>3. Spalte (Sü.)</b>
<br>
Zeigt die Anzahl aller von Schülern abgegebenen Voten.
<br><br>
<b>4. Spalte (&#216;)</b>
<br>
Die Antworten aller Fragen werden von 1-5 gewichtet, wobei eine kleinere Zahl
ein besseres Ergebnis bedeutet. Der Wert in dieser Spalte gibt den 
Durchschnittswert über alle Fragen<sup>*</sup> an.
<br>
<sup>*</sup>Frage Nr.17 bildet eine Ausnahme, sie wird separat ausgewertet.
<br><br>
<b>5. Spalte (Notenspiegel)</b>
<br>
Der Notenspiegel bezieht sich ausschließlich auf Frage Nr.17, hier wird 
der Unterricht insgesamt mit einer Schulnote bewertet.
<br><br>
<b>i-Symbol</b>
<br>
Durch das Anklicken dieses Symbols erhält man eine Übersicht über die
Ergebnisse der einzelnen Fragen (Detailauswertung).
</td>
</tr>
</table>

<p>&nbsp;</p>

<table border = "0">
<tr>
<td>
<p><img src="image/hilfe_MeineUmfragen.jpg" alt = "Meine Umfragen" border = "0"></p>
</td>
<td>
Der Aufbau ist analog zur Tabelle "Alle Umfragen", die Werte beziehen
sich aber nur auf die eigenen Umfrageergebnisse. 
</td>
</tr>
</table>

<p>&nbsp;</p>

<table border = "0">
<tr>
<td>
<p><img src="image/hilfe_MeineUmfragen_einzeln.jpg" alt = "Meine Umfragen im Einzelnen" border = "0"></p>
<td>
Diese Tabelle zeigt die Ergebnisse der eigenen Umfragen im Einzelnen.
<br><br>
<b>Passwort</b>
<br>
Das Passwort  wird beim Anlegen einer neuen Umfrage automatisch generiert.
Mit diesem Passwort melden sich die Schüler für die Teilnahme an einer Umfrage 
an. Achtung: Es wird Groß- und Kleinschreibung unterschieden.
<br><br>
<b>Stop/Go-Symbol</b>
<br>
Wird das Stop-Symbol angezeigt, ist die Teilnahme an einer Umfrage gesperrt.
Nachdem man eine Umfrage durchgeführt hat, sollte man die Sperre aktivieren, 
damit nicht beliebig viele Stimmen abgegeben werden können.
Das grüne Go-Symbol zeigt eine Umfrage an, für die eine Stimmabgabe möglich ist.
Die Umschaltung der Sperre erfolgt über das Anklicken des Symbols. Gesperrte 
Umfragen können jederzeit wieder freigeschaltet werden.
<br><br>
<b>i-Symbol</b>
<br>
Durch das Anklicken dieses Symbols erhält man eine Übersicht über die
Ergebnisse der einzelnen Fragen (Detailauswertung).
<br><br>
<b>Mülleimer-Symbol</b>
<br>
Durch das Anklicken dieses Symbols wird eine Umfrage <u>vollständig</u> und <u>unwiderruflich</u>
gelöscht!
</td>
</tr>
</table>

<p>&nbsp;</p>
<hr>
<p>&nbsp;</p>

<h4><a name="03" style="color:black">&nbsp;Wie kann ich eine neue Umfrage anlegen?</a></h4>

Eine neue Umfrage ist in weniger als 10 Sekunden angelegt!
<br><br>
<b>1. Schritt</b>
<br>
Über den Link "Neue Umfrage" kommen Sie zur Auswahl der Klassen (OG: Kursjahre). Wählen Sie 
die Klasse (OG: Kursjahr) aus für die eine Umfrage angelegt werden soll.
<br><br>
<b>2. Schritt</b>
<br>
Jetzt wird eine klassenbezogene Auswahl der Fächer (OG: Kurse) angezeigt. Wählen Sie
das entsprechende Fach (OG: Kurs) aus. 
<br><br>
<b>3. Schritt</b>
<br>
Es erfolgt noch eine Sicherheitsabfrage, die sie nur noch bestätigen müssen. Das
wars schon!

<p>&nbsp;</p>
<hr>
<p>&nbsp;</p>

<h4><a name="04" style="color:black">&nbsp;Wie können Schüler ein Votum abgeben?</a></h4>

Hierzu muss zunächst einmal eine Umfrage vom Lehrer angelegt werden. Die 
Schüler benötigen dann das Passwort der Umfrage (z.B. im Labor an die Tafel
schreiben, Groß- und Kleinschreibung beachten). Dann rufen die Schüler folgenden 
Link auf:
<br><br>
<b>www.oszimt.de/3-unterricht/evaluation</b>
<br><br>
Auf der nun erscheinenden Seite muss sich jeder Schüler mit dem Passwort anmelden,
danach erscheint sofort der Fragebogen.
<br>
Eine Stimmabgabe ist nur möglich, wenn alle Fragen beantwortet werden!

<p>&nbsp;</p>
<hr>
<p>&nbsp;</p>

<h4><a name="05" style="color:black">&nbsp;Können andere meine Ergebnisse einsehen?</a></h4>

<b>Nein!</b>
<br><br>
Die Anzeige der teilnehmenden Lehrer jeweils mit der Anzahl der von ihnen
durchgeführten Umfragen (über den Link "Teilnehmer") bildet die einzige persönliche Information, 
die für alle Teilnehmer einsehbar ist. 
<br><br>
Die Ergebnisse der
von ihnen durchgeführten Umfragen sind nur Ihnen persönlich zugänglich.
Die Webgroup legt großen Wert auf die Sicherheit Ihrer Daten und gibt 
sie niemals ohne Ihr ausdrückliches Einverständnis weiter. 
<br><br>
Über das Lösch-Symbol
haben Sie jederzeit die Möglichkeit Ihre gespeicherten Daten vollständig 
zu löschen.
<p>&nbsp;</p>

<?php
//--------------------------------------------------------------------------
//-------------------------------INHALT_ENDE--------------------------------
//--------------------------------------------------------------------------

echo ladeOszKopf_u();

echo ladeLink("../","<b>Interner&nbsp;Bereich</b>");
echo ladeLink("neuUmfrage.php","Neue&nbsp;Umfrage");
echo ladeLink("index.php","Meine&nbsp;Umfragen");
echo ladeLink("teilnehmer.php","Teilnehmer");

echo ladeOszFuss();
?>