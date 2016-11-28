<?php session_start(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Interner Lehrerbereich</title>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" type="text/css" href="http://css.oszimt.de/oszimt.css">
<link rel="shortcut icon" href="favicon.ico">
<script src="http://js.oszimt.de/overlib.js" type="text/javascript"></script>
</head>

<!-- Beginn OSZIMT Kopf -->
<body>
<div class="header">
  <a id="top" name="top"></a>
  <div class="logo">
    <a href="http://www.oszimt.de/"><img src="http://img.oszimt.de/logo/oszimt-logo.gif"
      alt="OSZ IMT (Logo)" title="Logo" /></a>
  </div>
  <div id="LetzterLogin">
<?php
  include_once('include/config.php');
  include_once('include/Lehrer.class.php');
  include_once('include/Abteilungen.class.php');
  include_once('include/stupla.inc.php');
  include('include/Logins.inc.php');
  echo getLastLogin();
  SaveLogin();
?>
    <br /><img src="http://img.oszimt.de/logo/internLehrer.gif" width="165"
         height="20" alt="interner Lehrerbereich" title="interner Lehrerbereich" />
  </div>
  <div class="Schulname">
  Oberstufenzentrum<br />Informations- und Medizintechnik
  <div style="color:rgb(211,12,41);text-align:right;font-size:7pt;">
  Berlins größte IT-Schule</div>
  </div> 
  
</div>

<!-- Ende OSZIMT Kopf -->
<div class="Seite">
<div class="contentMitKalender">
  <h1>Interner Lehrerbereich</h1>
    
       <div class="Sicherheitshinweis">Sicherheitshinweis</div>
     <p>Bitte achten Sie auf die Sicherheit Ihres Passwortes und ändern Sie es regelmäßig.
      In der obersten Zeile können Sie Ihren letzten Login ablesen - sollten Sie zu diesem
      Zeitpunkt nicht im internen Bereich gearbeitet haben, bitten wir um dringende Rückmeldung
      bei der WebGroup (Koll. Binz oder Salner)!</p>
      <p>Für die Änderung Ihres Passwortes melden Sie sich auf dem BSCW-Server an und gehen zum
      Menüpunkt: <em>Optionen->Passwort&nbsp;ändern</em></p>      
    
    <div class="Mitteilung">
      <b>Der Förderverein bittet um Ihre Unterstützung!</b> Sie können den Förderverein 
      unterstützen, indem Sie Ihre 
      <a href="http://www.amazon.de/exec/obidos/redirect-home?tag=oberstufenzei-21&site=home">
      Amazon-Einkäufe</a> über diesen <a href="Amazonpartner.php">Link</a> beginnen.  
      Weitere Informationen <a href="Amazonpartner.php">hier</a>.</p>
    </div>
    
    <br/>
<!--    <div class="Sicherheitshinweis">Wartungsarbeiten</div>
    <div><font color="#ff0000"><br/>heute, bis ca. 13:00h: Elektronische Dienste stehen für kurze Zeit nur eingeschränkt zur Verfügung!!!</font></div><br/>
    <div class="Sicherheitshinweis">************</div>-->
<!--	Liebe KollegInnen,<br/>
    der Umzug des internen Lehrerbereichs ist nun fast abgeschlossen - es sind nur noch Kleinigkeiten, die noch nicht zufriedenstellend funktionieren.<br />
    
    Sollten Sie noch Funktions-Beeintr&auml;chtigungen feststellen, so w&auml;ren wir f&uuml;r Ihre Mitteilung sehr dankbar: <a href="mailto:webgroup@oszimt.de">WebGroup</a><br/>
    WebGroup am OSZ IMT
    </font></div>-->
        
<div class="internKasten">
	<h1>Pläne</h1>  
      <ul>
		<li>     
			<a href="/StuPla/LPlan.php" 
				onMouseOver="return overlib('Unterrichtspläne der Lehrer mit Aufsichten',CAPTION,'Lehrerstundenpläne');" 
				onMouseOut="return nd();">Lehrerpläne
			</a>
		</li>
      <li><a href="http://www.oszimt.de/0-schule/stundenplan/KPlan.php" target="_blank"
        onMouseOver="return overlib('Unterrichtspläne der Klasse',CAPTION,'Klassenstundenpläne');" 
          onMouseOut="return nd();">
       Klassenpläne</a></li>
      <li><a href="/StuPla/RPlan.php" 
        onMouseOver="return overlib('Unterrichtspläne der Räume mit Reservierungsmöglichkeit.<br />Neben der Anzeige der aktuellen Raumbelegung kann ein Raumübersichtsplan für alle Turnusse erstellt werden.',CAPTION,'Raumstundenpläne');" 
        onMouseOut="return nd();">Raumpläne</a>
       </li>
       <li><a href="/StuPla/RSuche.php"
         onMouseOver="return overlib('Suche nach freien Räumen zur Reservierung.',CAPTION,'Raumsuche');" 
        onMouseOut="return nd();">Suche nach freien Räumen</a>
      </li>
      <li>
      <a href="/StuPla/Reservierungen.php" 
        onMouseOver="return overlib('Die Liste der von Ihnen reservierten Räume mit der Möglichkeit, die Reservierung aufzuheben.',CAPTION,'Aktuelle Raumreservierungen');" 
        onMouseOut="return nd();">Liste der Reservierten Räume</a>
      <li>
      <a href="/StuPla/Vertretungsplan.php"
        onMouseOver="return overlib('Der aktuelle Vertretungsplan mit allen Änderungen für Klassen, Lehrer und Räume. Raumreservierungen werden hier ebenfalls angezeigt.',CAPTION,'Vertretungsplan');" 
        onMouseOut="return nd();">Vertretungsplan</a>
      </li>
      </ul>
    </div>           
    <div class="internKasten">
    <h1>Termine</h1>
    <ul>
    <li><a href="/Termin/Termine.php"
    onMouseOver="return overlib('Jahresübersicht über alle Termine. Hier sollen alle Termine des OSZ IMT aufgenommen werden. In dieser Maske können die Termine auch Konfiguriert werden, damit nur Termine angezeigt werden, die Sie interessieren.',CAPTION,'Jahresübersicht');" 
        onMouseOut="return nd();">Jahresplan</a></li>
    <li><a href="/Termin/TermineWoche.php"
       onMouseOver="return overlib('Übersicht über die Termine der nächsten sieben Tage.',CAPTION,'Wochenübersicht');" 
       onMouseOut="return nd();">Wochenplan</a></li>
     <li>
     <a href="/Termin/Terminliste.php"
     onMouseOver="return overlib('Übersicht über alle Termine in Listenform zum Ausdrucken.',CAPTION,'Terminliste');" 
        onMouseOut="return nd();">Terminliste</a></li>
      <li><a href="/Termin/Termineingabe.php"
      onMouseOver="return overlib('Neueingabe eines Termins.<br />Gruppe, die Ihre Termine hier erfassen möchten, wenden sich bitte an die WebGroup.',CAPTION,'Neuer Termin');" 
        onMouseOut="return nd();">Neuen Termin eingeben</a></li>
      </ul>
    </div>    
    <div class="internKasten">
    <h1>Listen</h1>
    <ul>
     <li><a href="/Service/Klassenliste.php"
        onMouseOver="return overlib('Anzeige von Listen der Schülerinnen und Schüler nebst Geburtsdatum, Bemerkungen (Ausbildungsbetrieb und Tutor).<br /> Sie können die Liste zum Einkleben ins Klassenbuch ausdrucken und in Form einer Exceltabelle exportieren (z.B. für Notenlisten).<br />Für die OG steht die Kursbelegungsübersicht sowie eine Liste der Tutanden zur Verfügung.',CAPTION,'Schülerlisten');" 
        onMouseOut="return nd();">Klassen- und Kurslisten</a></li>
    <li><a href="/StuPla/AufsichtPDF.php"
        onMouseOver="return overlib('Aufsichtsplan mit allen Aufsichten als PDF-Dokument.',CAPTION,'Aufsichtsplan');" 
        onMouseOut="return nd();">Aufsichtsplan</a></li>
    <li><a href="/StuPla/Turnusliste.php"
        onMouseOver="return overlib('Übersicht über alle Turnusse am OSZ IMT in den bekannten Turnuslisten und eine Gesamtübersicht über alle Turnusse',CAPTION,'Wochenpläne');" 
        onMouseOut="return nd();">Wochenpläne</a></li>
    <li><a href="/Service/Kollegenliste.php"
       onMouseOver="return overlib('Liste der KollegInnen einer Abteilung, Klasse, eines Fachs, Bildungsganges oder schulweiten Gruppe. Hier kann auch eine eine E-Mail an eine solche Gruppe gesendet werden. Es besteht zusätzlich die Möglichkeit, die Liste in Excel zu exportieren (z.B. für Anwesenheitslisten) oder zum Import in E-Mail-Adressbücher.',CAPTION,'KollegInnenliste');" 
       onMouseOut="return nd();">KollegInnenliste</a></li>
    <li><a href="/Service/Bildliste.php"
       onMouseOver="return overlib('Liste aller KollegInnen mit Bildern. Hier kann auch eine eine E-Mail versendet und der Stundenplan aufgerufen werden.',CAPTION,'KollegInnenliste mit Bildern');" 
       onMouseOut="return nd();">KollegInnenliste mit Bildern</a></li>
    <li><a href="/Service/emailadressen.php"
        onMouseOver="return overlib('Dienstliste E-Mail-Adressen des Kollegiums und der nichtpädagogischen Mitarbeiter.',CAPTION,'E-Mail-Liste');" 
        onMouseOut="return nd();">E-Mail-Liste OSZIMT</a></li>
    <li><a href="pdf/gremienvertreter.pdf"
        onMouseOver="return overlib('Aktuelle Liste aller VertreterInnen in den schulischen Gremien am OSZ IMT',CAPTION,'GremienvertreterInnen');" 
        onMouseOut="return nd();">GremienvertreterInnen (pdf)</a></li>
    <li><img src="http://img.oszimt.de/nav/neu.gif" title="Neu" alt="Neu"/><a href="pdf/telefon.pdf"
        onMouseOver="return overlib('Aktuelle Liste der Telefonnummern',CAPTION,'Telefonliste');" 
        onMouseOut="return nd();">Telefonliste (pdf)</a></li>
    <li><a href="/Service/Fortbildungsliste.php"
        onMouseOver="return overlib('Liste aller vom Kollegium durchgeführten Fortbildungen.<br />Bitte melden Sie Ihre Fortbildungen der Abteilungsleitung, damit sie eingetragen werden.',CAPTION,'Fortbildungsliste');" 
        onMouseOut="return nd();">Fortbildungsliste</a></li>
      <li><a href="Service/Buecherliste.php"
        onMouseOver="return overlib('Liste der Bücher am OSZIMT mit Anzahl und Lagerort.<br />Bitte prüfen Sie vor Neubestellungen, ob die gewünschten Inhalte nicht in Büchern anderer Fachbereiche zur Verfügung stehen.',CAPTION,'Bücherliste');" 
        onMouseOut="return nd();">Bücherliste</a></li>     

      </ul>
    </div>
    <div class="internKasten">
    <h1>Verwaltung</h1>
    <ul>
    <li><a href="/Problem/Problemmeldung.php"
       onMouseOver="return overlib('Meldung von Problemen mit der Computerhardware im Hause. Bitte diese Maske benutzen, wenn ein Computer, Monitor, Drucker oder Beamer defekt ist. Alle anderen Probleme können mit dieser Maske <strong>nicht</strong> gemeldet werden. Bitte wenden Sie sich ggf. an die Techniker (Raum 2.4.12) oder schreiben Sie ihnen eine E-Mail an techniker@oszimt.de.',CAPTION,'Technische Probleme');" 
       onMouseOut="return nd();">Problemmeldung Technik</a></li>
    </li>
    <li><a href="/Klausur"
       onMouseOver="return overlib('digitale Meldung von Klausurergebnissen statt des entsprechenden Papierblattes. Bei genehmigungspflichtigen Klausuren erhält man das entsprechende Blatt zum Ausdruck. Mit einer Liste der fehlenden Ergebnisse und einer Liste aller bisherigen Klausurergebnisse.',CAPTION,'Klausurergebnisse');" 
       onMouseOut="return nd();">Klausurergebnisse</a></li>
    <li><a href="/Fehlzeit"
       onMouseOver="return overlib('Meldung von Fehlzeiten für SchülerInnen der OG. <b>Zugang nur für KollegInnen der OG!</b>',CAPTION,'Fehlzeiten OG');" 
       onMouseOut="return nd();">Fehlzeiten in der OG</a></li>
    <li><a href="/Fehlzeit/Schuelersuche.php"
       onMouseOver="return overlib('Personengebundener Stundenplan der Sch&uuml;ler/innen in der OG',CAPTION,'Sch&uuml;ler-Suche');" 
       onMouseOut="return nd();">Sch&uuml;ler-Suche</a><br />&nbsp;</li>
    <li><a href="/ute"
       onMouseOver="return overlib('Durchführung einer Schülerbefragung zur Evaluation des Unterrichts. Betreut wird die Umfrage von der Humboldt-Universität.',CAPTION,'UTE');" 
       onMouseOut="return nd();">UTE (Humboldt-Universität)</a></li>    
    <li><a href="/evaluation"
       onMouseOver="return overlib('Durchführung und Verwaltung von persönlichen Umfragen zur Güte des Unterrichts. Bitte führen Sie jedes Jahr eine Umfrage mit Ihren Schülern durch!',CAPTION,'Unterrichtsevaluation');" 
       onMouseOut="return nd();">Unterrichtsevaluation</a></li>    
    <li><a href="/Inventar"
       onMouseOver="return overlib('Zugang zur Inventardatenbank (separate Freischaltung notwendig). Hier werden die Gerätschaftes des OSZIMT, Bücher und ähnliches von den Fachbereichsleitungen verwaltet.',CAPTION,'Inventardatenbank');" 
       onMouseOut="return nd();">Inventardatenbank</a></li>
    <!-- 
    <li><a href="http://testcenter.oszimt.de/admin"
       onMouseOver="return overlib('Zugang zum Verwaltungsbereich für Multiple-Choice-Tests. Diese werden Online von den Schülern bearbeitet und automatisch ausgewertet.',CAPTION,'Multiple-Choice-Tests');" 
       onMouseOut="return nd();">Multiple-Choice-Tests</a><br />&nbsp;</li>
    -->
    <li><a href="/Lizenz"
       onMouseOver="return overlib('Beantragung von Lizenzen für Microsoft-Software im Rahmen des MSDNAA-Lizenzprogrammes (nur für Lehrer und Schüler des OSZ IMT).',CAPTION,'Softwarelizenzen');" 
       onMouseOut="return nd();">Software-Lizenzbeantragung</a></li>
    <li><a href="pdf/norman-lizenz.pdf"
       onMouseOver="return overlib('Kostenloses Antiviren- und Antispyware-Programm Norman-Virus-Control als Work-at-Home-Lizenz für alle Lehrkräfte am OSZ IMT',CAPTION,'Norman-Virus-Control');" 
       onMouseOut="return nd();">Antiviren-Programm (pdf)</a><br />&nbsp;</li>
<!--    <li><a href="/oo/Arbeitsplan.php"
       onMouseOver="return overlib('Raster fpr Arbeitspläne mit wochenweisem Datum und Turnus zum Eintragen der individuellen Daten. Das Raster wird in Form einer Star/OpenOffice-Datei angeboten und kann mit Word nicht geöffnet werden.',CAPTION,'Arbeitsplanvorlage');" 
       onMouseOut="return nd();">Arbeitsplanvorlage</a></li>-->
	<li><a href="/Service/Ausleihliste.php"
	   onMouseOver="return overlib('Raster für Ausleihlisten zum Eintragen. Das Datum wird automatisch eingetragen. Die Liste wird in Form einer PDF-Datei angeboten.',CAPTION,'Ausleihlistenvorlage');" 
       onMouseOut="return nd();">Ausleihlistenvorlage</a></li>
    </ul>
    </div>
    
<div class="internKasten">
	<h1>Webdienste</h1>
	<ul>
		<li><a href="https://bscw.oszimt.de">BSCW-Server</a></li>
		<li><a href="https://mail.oszimt.de/">WebMailer (E-Mail)</a>
			<form action="https://mail.oszimt.de/index.php?logon" method="post">
				<!-- Store action attributes to hidden variable to pass it to index page -->
				<!-- or else in the URL -->
				<input type="hidden" name="action_url" value=""></input>
				<div id="login_data">
					<table id="form_fields" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<th align="right" width="50" class="small">eMail-Adresse</th>
							<td width="10"> </td>
							<td><input type="text" name="username" id="username" class="inputelement" /></td>
						</tr>
						<tr>
							<th align="right" class="small">Passwort</th>
							<td> </td>
							<td><input type="password" name="password" id="password" class="inputelement" /></td>
						</tr>
<!--						<tr>
							<th align="right" class="small">Sprache</th>
							<td> </td>
							<td>
								<select name="language" id="language" class="inputelement">
									<option value="last">letzte verwendete Sprache</option>
									<option value="es_CA.UTF-8">Català</option>
									<option value="da_DK.UTF-8">Dansk</option>
									<option value="de_DE.UTF-8">Deutsch</option>
									<option value="en_EN">English</option>
									<option value="en_US.UTF-8">English (US)</option>
									<option value="es_ES.UTF-8">Español</option>
									<option value="fr_FR.UTF-8">Français</option>
									<option value="it_IT.UTF-8">Italiano</option>
									<option value="nl_NL.UTF-8">Nederlands</option>
									<option value="no_NO.UTF-8">Norsk</option>
									<option value="pt_PT.UTF-8">Português</option>
									<option value="pt_BR.UTF-8">Português brasileiro</option>
									<option value="fi_FI.UTF-8">Suomi</option>
									<option value="sv_SE.UTF-8">Svenska</option>
									<option value="nl_BE.UTF-8">Vlaams</option>
									<option value="fr_BE.UTF-8">Wallon</option>
								</select>
							</td>
						</tr>-->
						<tr>
							<td colspan="2"> </td>
							<td colspan="2"><input id="submitbutton" type="submit" class="button" value="LogIn"/></td>
						</tr>
					</table>
				</div>
			</form>
		</li>
	</ul>
</div>

<div class="internKasten">
	<h1>Links</h1>
	<ul>
		<li>
			<a href="http://www.content-office.de/SLBE_20_1html/app/web?action=loadIndexTemplate" target="_blank"
				onMouseOver="return overlib('Datenbank für Schulmanagement - <b>Schulrecht in Berlin.</b> Bitte Link-Adresse nicht weitergeben, denn unsere Schule besitzt Schullizenz <i>nur</i> für <i>unsere</i> Mitarbeiter/innen.<br/> Online-Zugang: www.schullink.de, user SLLBE, Passwort 4474BE20',CAPTION,'www.schullink.de');" 
				onMouseOut="return nd();"><img src="http://img.oszimt.de/nav/neu.gif" title="Neu" alt="Neu"/>SchulLINK Berlin
			</a><br/>&nbsp;
		</li>
		<li>
			<a href="http://fortbildung-regional.de" target="_blank"
				onMouseOver="return overlib('Online Portal von SenBWF.',CAPTION,'Regionale Fortbildungen');" 
				onMouseOut="return nd();"><!--<img src="http://img.oszimt.de/nav/neu.gif" title="Neu" alt="Neu"/>-->Regionale Fortbildungen
			</a><br/>&nbsp;
		</li>
		<li>
			<a href="https://bscw.oszimt.de/bscw/bscw.cgi/d306083/Anleitung%20digitale%20Dienste.pdf"
				onMouseOver="return overlib('Anleitung für die digitalen Dienste im internen Bereich.',CAPTION,'Anleitung');" 
				onMouseOut="return nd();">Anleitung interner Bereich
			</a>
		</li>
		<li>
			<a href="http://forum.oszimt.de/cgi-bin/yabb2/YaBB.pl?board=www-eMail"
				onMouseOver="return overlib('Anleitung für den Zugang und die Einrichtng der dienstlichen E-Mail.',CAPTION,'E-Mail');" 
				onMouseOut="return nd();">Anleitung E-Mail
			</a>
		</li>
		<li>
			<a href="https://bscw.oszimt.de/bscw/bscw.cgi/313845"
				onMouseOver="return overlib('Hilfe-Ordner mit diversen Hilfedateien zu BSCW und anderen Diensten des OSZIMT',CAPTION,'Hilfe');" 
				onMouseOut="return nd();">Hilfe-Ordner auf BSCW
			</a>
		</li>
	</ul>
</div>


<div class="abschluss"></div>
<div class="rightmargin">
	<a href="#top"><img src="http://img.oszimt.de/nav/pfeitop_blau.gif" width="20" height="20" alt="zum Seitenbeginn" title="zum Seitenbeginn"></a>
</div>
<div class="abschluss">&nbsp;</div>
</div>

<!-- content -->

<div class="rightKalender">
<!-- neue Spalte für Termine -->
  <br />
  <?php
      /**
       * Der Abteilungsleitung sollen die unbearbeiteten Fehlzeiten angezeigt werden.
       */
      // Datum des nächsten Schultages festlegen
      if ( date('H') > 16 ) 
        $Plus=1;
      else 
        $Plus=0;
      if ( date('w',strtotime('+'.$Plus.'day')) == 0 ) 
        $Plus += 1;
      elseif ( date('w',strtotime('+'.$Plus.'day')) == 6 )
        $Plus += 2;      
      $Datum = strtotime(date('Y-m-d',strtotime('+'.$Plus.' day')));
      
      // Fehlende Vertretungen
      $Abteilungen = new Abteilungen($db); 
      if ( $Abteilungen->isAbteilungsleitung() )
      {
      	$nr = $Abteilungen->abteilungsZugehoerigkeit();      	
      	if ( isset($_REQUEST['Nr'])) $nr = $_REQUEST['Nr'];
      	$query = mysql_query('SELECT * FROM T_Vertretung_Liste INNER JOIN ' .
      			'T_Verhinderungen ON Verhinderung_id=' .
      			'F_Verhinderung_id ' .
      			'WHERE Betroffen LIKE "%AC'.date('dm',$Datum).'%"'.      			
                ' ORDER BY Art, Wer');
      	$leer = true;
      	if ( mysql_num_rows($query) > 0 )
      	{
      	  
      	  echo '<div class="Mitteilung">';
      	  $span = 'name="AC'.date('dm',$Datum).'">';
      	  echo '<h2>Abt. '.$nr.'! Unbearbeitete Vertretungen am '.date('d.m.Y',$Datum).'</h2>';
      	  echo '<p class="small">';
      	  while ( $zeile = mysql_fetch_array($query))
      	  {
      		$s = $zeile['Betroffen'];
      		
      		$pos = strpos($s, $span);
      		// Extrahieren der unbearbeiteten Vertretung
      		while ( $pos !== false )
      		{
      			$s = substr($s, $pos+strlen($span));
      			// Achtung: Anpassung an VertretungPlanen!
      			$pos = strpos($s, '</span><br />');
      			$link = substr($s, 0, $pos);
      			$s = substr($s, $pos+7);
      			$pos = strpos($s, $span);
      			$link = str_replace(date('d.m.Y',$Datum).'</a',
      			                    $zeile['Art'].' '.$zeile['Wer'].'</a',
      			                    $link);
      			if ( $nr != 0 ) // Sonderabteilung
      			{
      				// prüfen, ob ein <span name="AbtX" vorhanden ist
      				// X <> nr => löschen bis </span>
      				$fertig = '';
      				$pos = strpos($link, '<span name="Abt');
      				while ($pos !== false)
      				{
      					$spanstring = substr($link, $pos+15);
      					$fertig .= substr($link, 0, $pos);
      					$link = substr($link, $pos);
      					$p = strpos($spanstring, '"');
      					if ($p > 0)
      					{
      					  $nummer = substr($spanstring, 0, $p);
      					  if ( $nr != $nummer )
      					  {
      						// entfernen
      						$p = strpos($spanstring, '</span>');
      						if ( $p !== false )
      						  $link = substr($spanstring, $p+7);
      						else
      						  $link = substr($spanstring, $p);      						      						      						 
      					  }
      					  else
      					  {
      					    $fertig .= substr($link,0,15);
      					    $link = $spanstring;
      					  }
      					}
      					else
      					  $link = $spanstring;
      					$pos = strpos($link, '<span name="Abt');
      				} // while
      				$fertig .= $link;
      				// Führendes Komma weg
      				$fertig = str_replace(': , ',': ',$fertig);      				
      				while ( strpos($fertig, ', ,') !== false )
      				  $fertig = str_replace(', ,',', ',$fertig);
      				$fertig = str_replace('  ',' ',$fertig);      				
      				//echo 'FERTIG:'.htmlentities($fertig).'//';
      				if ( substr($fertig, -2) == ', ') $fertig = substr($fertig, 0, -2);
      				if ( substr($fertig, -2) == ': ') $fertig = '';
      				$link = $fertig;
      			}
      			echo $link; // br schon im Text
      			if ( $link != '' ) 
      			{
      				$leer = false;
      				echo '<br />';
      			}
      		}      		
          }
          if ( $leer )
            echo '<div class="Hinweis">Keine offenen Verhinderungen für '.
              $Abteilungen->Abteilungen[$nr]['Abteilung'].', nur für ' .
              		'andere Abteilungen</div>';
          echo '</p></div>';
      	}
      	mysql_free_result($query);
      }
      /**
       * Aktuelle Stundenplanänderungen für den Eingeloggten zeigen.
       */
      if ( isset($_REQUEST['Lehrer'])) 
        $L = $_REQUEST['Lehrer'];
      else
        $L = $_SERVER['REMOTE_USER'];
      $Lehrer = new Lehrer($L,LEHRERID_EMAIL);
      if ( $Lehrer->Kuerzel != '' )
      {
        $query = mysql_query('SELECT * FROM T_Vertretungen WHERE (Lehrer_Neu="'.
         $Lehrer->Kuerzel.'" OR Lehrer="'.$Lehrer->Kuerzel.'") AND Datum='.$Datum);
      
        if ( mysql_num_rows($query) != 0 )
        {
        echo '<div class="Mitteilung">';
        echo '<h2>Stundenplanänderungen am '.date('d.m.Y',$Datum).' für '.
          $Lehrer->Anrede($LEHRER_LEHRER).'</h2>';
        echo '<p class="small">';
        while ( $vertretung = mysql_fetch_array($query))
        {
        	echo $vertretung['Stunde'].'. Block: ';
        	echo $vertretung['Klasse'];
        	if ( $vertretung['Lehrer'] != $Lehrer->Kuerzel && $vertretung['Lehrer'] != '')
        	  echo '('.$vertretung['Lehrer'].')';
        	echo ' &rarr; '.$vertretung['Klasse_Neu'];
        	if ( $vertretung['Klasse_Neu'] == '') 
        	  echo 'entfällt';
        	else
        	{
        	  if ( $vertretung['Lehrer_Neu'] != $Lehrer->Kuerzel )
        	    echo '('.$vertretung['Lehrer_Neu'].')';        	        	
        	  echo ' in '.$vertretung['Raum_Neu'];
        	}
        	echo '<br />'; 
        }
        echo '</p>';
        echo '<a href="/StuPla/Vertretungsplan.php">';
        echo '<img src="http://img.oszimt.de/nav/link.gif" width="25" height="13" border="0">';
        echo '</a> ';
        echo '<a href="/StuPla/Vertretungsplan.php">Vertretungsplan</a><br />';
        $s = '<a href="/StuPla/LPlan1.php?Lehrer='.$Lehrer->Kuerzel.'&KW=true&ID_Woche='.
            getID_Woche($Datum).'">';
        echo '<img src="http://img.oszimt.de/nav/link.gif" width="25" height="13" border="0">';
        echo '</a> ';
        echo $s.'Lehrerstundenplan</a><br />';
        echo '</div>';
        }
        mysql_free_result($query);              
        /**
         * Aktualität der Fehlzeiteneintragungen zeigen
         */
        $Version = getAktuelleVersion();
        /*
        Version fuer Januar (Vetretungsplan != Stundenplan)
        $strSql = 'SELECT Kurs, BearbeitetBis FROM T_Fehlzeitenstand WHERE Lehrer="' . $Lehrer->Username . '"';
        if ( !$query = mysql_query($strSql) ) echo mysql_error();
        */
        if ( !$query = mysql_query('SELECT DISTINCT Fach, BearbeitetBis FROM T_StuPla LEFT JOIN T_Fehlzeitenstand ON Fach=Kurs WHERE Klasse LIKE "OG _" AND Version='.$Version.' AND T_StuPla.Lehrer="'.$Lehrer->Kuerzel.'"'))
          echo mysql_error();
        if ( mysql_num_rows($query) > 0 )
        {
        	echo '<div class="Mitteilung"><h2>OG - Fehlzeitenaktualität</h2>';
        	echo '<ul>';
        	while ( $kurs = mysql_fetch_array($query))
        	{
        		if ( $kurs['BearbeitetBis'] == '' )
        		{
        			$kurs['BearbeitetBis'] = '<span class="Achtung">noch nicht bearbeitet!</span>';
        		}
        		elseif ( $kurs['BearbeitetBis']<strtotime('-30 day'))
        		{
        			$kurs['BearbeitetBis'] = '<span class="Achtung">'.date('d.m.Y',$kurs['BearbeitetBis']).' Eintragung notwendig!</span>';
        		}
        		elseif ( $kurs['BearbeitetBis'] < strtotime('-20 day'))
        		{
        		  $kurs['BearbeitetBis'] = '<span class="Warnung">'.date('d.m.Y',$kurs['BearbeitetBis']).'</span>';        		 
        		}
        		else
        		{
        			$kurs['BearbeitetBis'] = '<span class="Ok">'.date('d.m.Y',$kurs['BearbeitetBis']).'</span>';
        		}
        		echo '<li><a href="/Fehlzeit/Kursliste.php?Kurs='.$kurs['Fach'].'">'.
        		  $kurs['Fach'].':</a> '.$kurs['BearbeitetBis'].'</li>';
        	}
        	echo '</ul>';
        	echo '</div>';
        }
        mysql_free_result($query);
        /**
         * Fehlende Klausurergebnisse zeigen
         */
        include_once('include/turnus.inc.php');
        $Version = getAktuelleVersion();
        $Schuljahr= schuljahr(false);
     
        $sql = 'SELECT Fach, Klasse, Count(Datum) FROM '.
          '(SELECT DISTINCT T_StuPla.Fach, T_StuPla.Klasse, Datum '.
          'FROM T_StuPla LEFT JOIN T_Klausurergebnisse '.
         'ON T_StuPla.Klasse=T_Klausurergebnisse.Klasse AND '.
        'T_StuPla.Fach=T_Klausurergebnisse.Fach AND Schuljahr="'.$Schuljahr.'" WHERE '.
        'T_StuPla.Lehrer="'.$Lehrer->Kuerzel.'" '.
           'AND Version='.$Version.') AS T GROUP BY Klasse, Fach';
        if ( !$query = mysql_query($sql)) echo mysql_error();
        if ( mysql_num_rows($query) > 0 )
        {
          $Jahrgang = explode(" ",$Schuljahr);
	  // 20.12.2011 - Anpassung, da Jahrgang nun Feld, muss Teil 2 (Jahr) ausgewertet werden
          $Jahr = substr($Jahrgang[1], 3, 1); // Jahr ausfiltern
          /* 20.01.2012 Anpassung - Jahr wird nicht mehr pro Halbjahr gerechnet 
          */
          if ( substr($Schuljahr,0,2) == "II" ) {
		$Jahr--;
		$Halbjahr = 2;
	  }
	  else
	  $Halbjahr = 1;
          // Stattdessen nur einstellig machen 
          $Jahr = $Jahr % 10;
          $Header = '<div class="Mitteilung"><h2>Klausurergebnisse '.$Schuljahr.'</h2><ul>';
          $Klasse = '';
          while ( $fach = mysql_fetch_row($query))
          {
          	// Ziffern wegwerfen (wichtig für OG-Kurse) 
                $dasfach = preg_replace('/1|2|3|4|5|6|7|8|9|0/','',$fach[0]);
          	$mquery = mysql_query('SELECT * FROM T_Faecher WHERE Fach="'.$dasfach.'"');
            while ( $anzahl = mysql_fetch_array($mquery))
            {
		// in einen regulären Ausdruck verwandeln
            	$anzahl['Art']= str_replace('_','.',$anzahl['Art']);
            	$anzahl['Art'] = str_replace("1",$Jahr,$anzahl['Art']);
                if ( strpos($anzahl['Art'],"2") !== false )
                { 
                   $J = ($Jahr + 9 ) % 10;
                   $anzahl['Art'] = str_replace("2",$J,$anzahl['Art']);
                }
                elseif ( strpos($anzahl['Art'],"3") !== false )
                {
                   $J = ($Jahr + 8 ) % 10;
                   $anzahl['Art'] = str_replace("3",$J,$anzahl['Art']);
                } 
                elseif ( strpos($anzahl['Art'],"4") !== false )
                {
                   $J = ($Jahr + 7 ) % 10;
                   $anzahl['Art'] = str_replace("4",$J,$anzahl['Art']);
                }        
                if ( ereg('^'.$anzahl['Art'].'$',$fach[1]))
            	{
            		// gefunden
            		if ( $fach[2] < $anzahl['HJ'.$Halbjahr])
            		{
          		  echo $Header;
            		  $Header = '';
            		  echo '<li><a href="/Klausur/Klausurergebnisse.php?Klasse='.$fach[1].
            		   '&Fach='.$fach[0].'">'.$fach[1].', ';            		
            		  echo $fach[0].'</a> '.$fach[2].' von '.$anzahl['HJ'.$Halbjahr].' eingetragen';
            		}
            	}
            }          
            mysql_free_result($mquery);            
          }
          if ( $Header == '' )
          {
            	echo '</ul></div>';
          }
        }                      
        mysql_free_result($query);
      } // wenn ein Lehrerkürzel vorhanden ist    
      ?>  
  <br />
  <?php
  include('include/Termine.class.php');
  $Termine = new Termine($db);
  if ( ! in_array(21, $Termine->Filter)) $Termine->Filter[] = 21;
  echo  $Termine->holeTermineAlsHTML();
  mysql_close($db);
?>
</div>
</div>

<div class="footer">
  zuletzt geändert<br />
  <?=date ("d.m.Y H:i", filemtime(basename($_SERVER["PHP_SELF"])))?>
  <br />
  Fehler bitte an <a href="mailto:binz@oszimt.de">Koll. Binz</a> mailen.

</div>

<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

</body>
</html>
