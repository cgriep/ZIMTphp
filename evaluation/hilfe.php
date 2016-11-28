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
<p><b><a href="hilfe_umfrage.php">&nbsp;1. Wie sieht der Fragebogen f�r die Sch�ler aus?</a></b></p>
<p><b><a href="#02">&nbsp;2. Was zeigt die �bersichtsseite im Einzelnen?</a></b></p>
<p><b><a href="#03">&nbsp;3. Wie kann ich eine neue Umfrage anlegen?</a></b></p>
<p><b><a href="#04">&nbsp;4. Wie k�nnen Sch�ler ein Votum abgeben?</a></b></p>
<p><b><a href="#05">&nbsp;5. K�nnen andere meine Ergebnisse einsehen?</a></b></p>

<p>&nbsp;</p>
<hr>
<p>&nbsp;</p>

<h4><a name="02" style="color:black">&nbsp;Was zeigt die �bersichtsseite im Einzelnen?</a></h4>

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
<b>3. Spalte (S�.)</b>
<br>
Zeigt die Anzahl aller von Sch�lern abgegebenen Voten.
<br><br>
<b>4. Spalte (&#216;)</b>
<br>
Die Antworten aller Fragen werden von 1-5 gewichtet, wobei eine kleinere Zahl
ein besseres Ergebnis bedeutet. Der Wert in dieser Spalte gibt den 
Durchschnittswert �ber alle Fragen<sup>*</sup> an.
<br>
<sup>*</sup>Frage Nr.17 bildet eine Ausnahme, sie wird separat ausgewertet.
<br><br>
<b>5. Spalte (Notenspiegel)</b>
<br>
Der Notenspiegel bezieht sich ausschlie�lich auf Frage Nr.17, hier wird 
der Unterricht insgesamt mit einer Schulnote bewertet.
<br><br>
<b>i-Symbol</b>
<br>
Durch das Anklicken dieses Symbols erh�lt man eine �bersicht �ber die
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
Mit diesem Passwort melden sich die Sch�ler f�r die Teilnahme an einer Umfrage 
an. Achtung: Es wird Gro�- und Kleinschreibung unterschieden.
<br><br>
<b>Stop/Go-Symbol</b>
<br>
Wird das Stop-Symbol angezeigt, ist die Teilnahme an einer Umfrage gesperrt.
Nachdem man eine Umfrage durchgef�hrt hat, sollte man die Sperre aktivieren, 
damit nicht beliebig viele Stimmen abgegeben werden k�nnen.
Das gr�ne Go-Symbol zeigt eine Umfrage an, f�r die eine Stimmabgabe m�glich ist.
Die Umschaltung der Sperre erfolgt �ber das Anklicken des Symbols. Gesperrte 
Umfragen k�nnen jederzeit wieder freigeschaltet werden.
<br><br>
<b>i-Symbol</b>
<br>
Durch das Anklicken dieses Symbols erh�lt man eine �bersicht �ber die
Ergebnisse der einzelnen Fragen (Detailauswertung).
<br><br>
<b>M�lleimer-Symbol</b>
<br>
Durch das Anklicken dieses Symbols wird eine Umfrage <u>vollst�ndig</u> und <u>unwiderruflich</u>
gel�scht!
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
�ber den Link "Neue Umfrage" kommen Sie zur Auswahl der Klassen (OG: Kursjahre). W�hlen Sie 
die Klasse (OG: Kursjahr) aus f�r die eine Umfrage angelegt werden soll.
<br><br>
<b>2. Schritt</b>
<br>
Jetzt wird eine klassenbezogene Auswahl der F�cher (OG: Kurse) angezeigt. W�hlen Sie
das entsprechende Fach (OG: Kurs) aus. 
<br><br>
<b>3. Schritt</b>
<br>
Es erfolgt noch eine Sicherheitsabfrage, die sie nur noch best�tigen m�ssen. Das
wars schon!

<p>&nbsp;</p>
<hr>
<p>&nbsp;</p>

<h4><a name="04" style="color:black">&nbsp;Wie k�nnen Sch�ler ein Votum abgeben?</a></h4>

Hierzu muss zun�chst einmal eine Umfrage vom Lehrer angelegt werden. Die 
Sch�ler ben�tigen dann das Passwort der Umfrage (z.B. im Labor an die Tafel
schreiben, Gro�- und Kleinschreibung beachten). Dann rufen die Sch�ler folgenden 
Link auf:
<br><br>
<b>www.oszimt.de/3-unterricht/evaluation</b>
<br><br>
Auf der nun erscheinenden Seite muss sich jeder Sch�ler mit dem Passwort anmelden,
danach erscheint sofort der Fragebogen.
<br>
Eine Stimmabgabe ist nur m�glich, wenn alle Fragen beantwortet werden!

<p>&nbsp;</p>
<hr>
<p>&nbsp;</p>

<h4><a name="05" style="color:black">&nbsp;K�nnen andere meine Ergebnisse einsehen?</a></h4>

<b>Nein!</b>
<br><br>
Die Anzeige der teilnehmenden Lehrer jeweils mit der Anzahl der von ihnen
durchgef�hrten Umfragen (�ber den Link "Teilnehmer") bildet die einzige pers�nliche Information, 
die f�r alle Teilnehmer einsehbar ist. 
<br><br>
Die Ergebnisse der
von ihnen durchgef�hrten Umfragen sind nur Ihnen pers�nlich zug�nglich.
Die Webgroup legt gro�en Wert auf die Sicherheit Ihrer Daten und gibt 
sie niemals ohne Ihr ausdr�ckliches Einverst�ndnis weiter. 
<br><br>
�ber das L�sch-Symbol
haben Sie jederzeit die M�glichkeit Ihre gespeicherten Daten vollst�ndig 
zu l�schen.
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