-- phpMyAdmin SQL Dump
-- version 2.8.2.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Erstellungszeit: 13. November 2006 um 21:05
-- Server Version: 4.1.18
-- PHP-Version: 5.0.3
-- 
-- Datenbank: `oszimt`
-- 

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Abteilungen`
-- 

CREATE TABLE `T_Abteilungen` (
  `Abteilung_id` int(11) NOT NULL auto_increment,
  `Abteilung` varchar(20) collate latin1_german1_ci NOT NULL default '',
  `Ansprechpartner` varchar(100) collate latin1_german1_ci NOT NULL default '',
  PRIMARY KEY  (`Abteilung_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='Abteilungen des OSZ IMT' AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Aufsichten`
-- 

CREATE TABLE `T_Aufsichten` (
  `Aufsicht_id` int(10) unsigned NOT NULL auto_increment,
  `Lehrer` varchar(10) collate latin1_german1_ci NOT NULL default '',
  `Wochentag` tinyint(4) unsigned NOT NULL default '0',
  `VorStunde` tinyint(4) unsigned NOT NULL default '0',
  `F_Ort_id` int(10) unsigned NOT NULL default '0',
  `GueltigAb` int(11) NOT NULL default '0',
  PRIMARY KEY  (`Aufsicht_id`),
  KEY `Index_2` (`Lehrer`),
  KEY `Gueltig` (`GueltigAb`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='Aufsichtsplan' AUTO_INCREMENT=6459 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Aufsichtsorte`
-- 

CREATE TABLE `T_Aufsichtsorte` (
  `Ort_id` int(10) unsigned NOT NULL auto_increment,
  `Ort` varchar(45) collate latin1_german1_ci NOT NULL default '',
  `Bereich` varchar(45) collate latin1_german1_ci NOT NULL default '',
  PRIMARY KEY  (`Ort_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=39 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Auswaertsklassen`
-- 

CREATE TABLE `T_Auswaertsklassen` (
  `Klasse` varchar(10) collate latin1_german1_ci NOT NULL default '',
  PRIMARY KEY  (`Klasse`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Bildungsgaenge`
-- 

CREATE TABLE `T_Bildungsgaenge` (
  `ID_Bg` tinyint(4) NOT NULL auto_increment,
  `F_ID_SArt` tinyint(4) NOT NULL default '0' COMMENT 'Schulart',
  `Bezeichnung` text NOT NULL,
  PRIMARY KEY  (`ID_Bg`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Bildungsgaenge am OSZ-IMT' AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_EMailGruppen`
-- 

CREATE TABLE `T_EMailGruppen` (
  `Gruppe_id` int(10) unsigned NOT NULL auto_increment,
  `Gruppe` varchar(45) collate latin1_german1_ci NOT NULL default '',
  `Kuerzel` varchar(45) collate latin1_german1_ci NOT NULL default '',
  `Name` varchar(45) collate latin1_german1_ci NOT NULL default '',
  `Vorname` varchar(45) collate latin1_german1_ci NOT NULL default '',
  `prefix` varchar(45) collate latin1_german1_ci NOT NULL default '',
  `domain` varchar(45) collate latin1_german1_ci NOT NULL default '',
  `Beschreibung` text collate latin1_german1_ci NOT NULL,
  PRIMARY KEY  (`Gruppe_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='E-Mail-Gruppen' AUTO_INCREMENT=33 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Faecher`
-- 

CREATE TABLE `T_Faecher` (
  `Fach` varbinary(15) NOT NULL default '',
  `HJ1` int(11) NOT NULL default '0',
  `HJ2` int(11) NOT NULL default '0',
  `Art` varchar(10) collate latin1_german1_ci NOT NULL default '',
  PRIMARY KEY  (`Fach`,`Art`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='Anzahl der Klausuren pro Fach in den Bildungsg�ngen';

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Fehlzeiten`
-- 

CREATE TABLE `T_Fehlzeiten` (
  `Datum` date NOT NULL default '0000-00-00',
  `Block` int(11) NOT NULL default '0',
  `Schueler_id` int(11) NOT NULL default '0',
  `Art` char(1) collate latin1_german1_ci NOT NULL default '',
  `Kurs` varchar(10) collate latin1_german1_ci NOT NULL default '',
  `Aenderung` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `Lehrer` varchar(25) collate latin1_german1_ci NOT NULL default '',
  `Schuljahr` varchar(15) collate latin1_german1_ci NOT NULL default '',
  PRIMARY KEY  (`Block`,`Datum`,`Schueler_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Fehlzeitenstand`
-- 

CREATE TABLE `T_Fehlzeitenstand` (
  `Kurs` varbinary(10) NOT NULL default '',
  `Bearbeitetbis` int(11) NOT NULL default '0',
  `Lehrer` varchar(25) collate latin1_german1_ci NOT NULL default '',
  `Mahnung` int(11) NOT NULL default '0',
  PRIMARY KEY  (`Kurs`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_FreiTage`
-- 

CREATE TABLE `T_FreiTage` (
  `ID_FreiTage` int(11) NOT NULL auto_increment,
  `ersterTag` int(11) NOT NULL default '0',
  `letzterTag` int(11) NOT NULL default '0',
  `Kommentar` varchar(30) collate latin1_german1_ci NOT NULL default '',
  PRIMARY KEY  (`ID_FreiTage`),
  KEY `Index_2` (`ersterTag`,`letzterTag`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=62 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Inventar`
-- 

CREATE TABLE `T_Inventar` (
  `Inventar_id` int(11) NOT NULL auto_increment,
  `Bezeichnung` varchar(100) collate latin1_german1_ci NOT NULL default '',
  `F_Lieferant_id` int(11) NOT NULL default '0',
  `Inventar_Nr` varchar(20) collate latin1_german1_ci NOT NULL default '',
  `Stand` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `Bemerkung` text collate latin1_german1_ci NOT NULL,
  `Anschaffungsdatum` int(11) NOT NULL default '0',
  `Entsorgungsdatum` int(11) NOT NULL default '0',
  `Seriennummer` varchar(50) collate latin1_german1_ci NOT NULL default '',
  `Rechnungsnummer` varchar(50) collate latin1_german1_ci NOT NULL default '',
  `Anschaffungskosten` double NOT NULL default '0',
  `Herstellungsjahr` int(11) NOT NULL default '0',
  `Bearbeiter` varchar(20) collate latin1_german1_ci NOT NULL default '',
  `Zustand` varchar(20) collate latin1_german1_ci NOT NULL default '',
  `F_Art_id` int(11) NOT NULL default '0',
  `Gewaehrleistung` int(11) NOT NULL default '0',
  `F_Raum_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`Inventar_id`),
  KEY `Inventar_Nr` (`Inventar_Nr`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='Inventar am OSZ IMT' AUTO_INCREMENT=2916 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Inventararten`
-- 

CREATE TABLE `T_Inventararten` (
  `Art_id` int(11) NOT NULL auto_increment,
  `Art` varchar(20) collate latin1_german1_ci NOT NULL default '',
  `Meldungmail` varchar(45) collate latin1_german1_ci NOT NULL default '' COMMENT 'E-Mail für Meldung',
  `Inventarnummerprefix` varchar(5) collate latin1_german1_ci default NULL COMMENT 'Prefix für neue Inventarnummern',
  PRIMARY KEY  (`Art_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Inventardaten`
-- 

CREATE TABLE `T_Inventardaten` (
  `Daten_id` int(11) NOT NULL auto_increment,
  `F_Art_id` int(11) NOT NULL default '0',
  `F_Inventar_id` int(11) NOT NULL default '0',
  `Bemerkung` text collate latin1_german1_ci NOT NULL,
  `Inhalt` varchar(40) collate latin1_german1_ci NOT NULL default '',
  PRIMARY KEY  (`Daten_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=4059 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Inventardatenarten`
-- 

CREATE TABLE `T_Inventardatenarten` (
  `Art_id` int(11) NOT NULL auto_increment,
  `Art` varchar(20) collate latin1_german1_ci NOT NULL default '',
  `Maske` varchar(50) collate latin1_german1_ci NOT NULL default '',
  PRIMARY KEY  (`Art_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Klausurergebnisse`
-- 

CREATE TABLE `T_Klausurergebnisse` (
  `Klausur_id` int(11) NOT NULL auto_increment,
  `Lehrer` varchar(20) collate latin1_german1_ci NOT NULL default '',
  `Datum` date NOT NULL default '0000-00-00',
  `Abteilung` int(11) NOT NULL default '0',
  `Schuljahr` varchar(10) collate latin1_german1_ci NOT NULL default '',
  `Fach` varbinary(20) NOT NULL default '',
  `Einser` int(11) NOT NULL default '0',
  `Zweier` int(11) NOT NULL default '0',
  `Dreier` int(11) NOT NULL default '0',
  `Vierer` int(11) NOT NULL default '0',
  `Fuenfer` int(11) NOT NULL default '0',
  `Sechser` int(11) NOT NULL default '0',
  `Klasse` varchar(10) collate latin1_german1_ci NOT NULL default '',
  `Aenderung` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `Dateiname` varchar(100) collate latin1_german1_ci NOT NULL default '',
  `Dauer` int(11) NOT NULL default '45',
  `Kenntnisnahme` varchar(100) collate latin1_german1_ci NOT NULL default '',
  PRIMARY KEY  (`Klausur_id`),
  KEY `Schuljahrindex` (`Schuljahr`),
  KEY `Abteilungsindex` (`Abteilung`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='Ergebnisse der Klausuren am OSZ IMT' AUTO_INCREMENT=1993 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Kurse`
-- 

CREATE TABLE `T_Kurse` (
  `Schueler_id` int(11) NOT NULL default '0',
  `Kurs` varbinary(10) NOT NULL default '',
  `Schuljahr` varchar(15) collate latin1_german1_ci NOT NULL default '',
  `Fach` varchar(10) collate latin1_german1_ci NOT NULL default '',
  `Art` varchar(10) collate latin1_german1_ci NOT NULL default '',
  PRIMARY KEY  (`Schueler_id`,`Schuljahr`,`Kurs`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='Schueler-Kurszuordnungen';

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Lehrer`
-- 

CREATE TABLE `T_Lehrer` (
  `Kuerzel` varchar(10) NOT NULL default '',
  `Name` varchar(50) NOT NULL default '',
  `Vorname` varchar(25) NOT NULL default '',
  `EMail` varchar(25) NOT NULL default '',
  `Sollstunden` double default '0',
  `ErteilteStunden` double default '0',
  `Ermaessigungsstunden` double default '0',
  `Taetigkeit` varchar(100) default NULL,
  `Geschlecht` char(1) NOT NULL default '',
  `Telefon` varchar(25) NOT NULL default '',
  `Bild` mediumblob NOT NULL,
  PRIMARY KEY  (`EMail`),
  KEY `Kuerzel` (`Kuerzel`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Namen und Kürzel der Lehrer (EMail=BSCW-Login!)';

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Lieferanten`
-- 

CREATE TABLE `T_Lieferanten` (
  `Lieferant_id` int(11) NOT NULL auto_increment,
  `Name` varchar(100) collate latin1_german1_ci NOT NULL default '',
  `PLZ` varchar(20) collate latin1_german1_ci NOT NULL default '',
  `Ort` varchar(50) collate latin1_german1_ci NOT NULL default '',
  `Strasse` varchar(100) collate latin1_german1_ci NOT NULL default '',
  `Telefon` varchar(25) collate latin1_german1_ci NOT NULL default '',
  `Telefax` varchar(25) collate latin1_german1_ci NOT NULL default '',
  `Ansprechpartner` text collate latin1_german1_ci NOT NULL,
  `Stand` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `Bearbeiter` varchar(25) collate latin1_german1_ci NOT NULL default '',
  PRIMARY KEY  (`Lieferant_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Logins`
-- 

CREATE TABLE `T_Logins` (
  `User` varchar(20) collate latin1_german1_ci NOT NULL default '',
  `Login` int(10) unsigned NOT NULL default '0',
  `IP` varchar(15) collate latin1_german1_ci NOT NULL default '',
  `Referer` varchar(150) collate latin1_german1_ci NOT NULL default '',
  `Args` text collate latin1_german1_ci NOT NULL,
  `Method` varchar(4) collate latin1_german1_ci NOT NULL default '',
  `Agent` varchar(45) collate latin1_german1_ci NOT NULL default '',
  `Seite` varchar(45) collate latin1_german1_ci NOT NULL default '',
  KEY `User` (`User`,`Login`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='Login-Verfolgung';

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Raeume`
-- 

CREATE TABLE `T_Raeume` (
  `Raum_id` int(11) NOT NULL auto_increment,
  `Raumbezeichnung` varchar(50) collate latin1_german1_ci NOT NULL default '',
  `Raumnummer` varchar(10) collate latin1_german1_ci NOT NULL default '',
  `Verantwortlich` varchar(20) collate latin1_german1_ci NOT NULL default '',
  `Stand` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `Bearbeiter` varchar(20) collate latin1_german1_ci NOT NULL default '',
  `Beschreibung` text collate latin1_german1_ci NOT NULL,
  `Kapazitaet` int(11) NOT NULL default '0',
  `Verwendung` varchar(100) collate latin1_german1_ci NOT NULL default '',
  `Reservierbar` tinyint(3) unsigned NOT NULL default '1',
  `Langfristig` tinyint(1) NOT NULL default '0' COMMENT 'true wenn Reservierungsbeschränkung aufgehoben',
  `Reservierungsberechtigung` text collate latin1_german1_ci NOT NULL COMMENT 'Kürzel der Lehrer die reservieren dürfen',
  PRIMARY KEY  (`Raum_id`),
  KEY `Index_2` (`Raumnummer`),
  KEY `Reservierbar` (`Reservierbar`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=860 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Reparaturen`
-- 

CREATE TABLE `T_Reparaturen` (
  `Reparatur_id` int(11) NOT NULL auto_increment,
  `Grund` varchar(40) collate latin1_german1_ci NOT NULL default '',
  `Bemerkung` text collate latin1_german1_ci NOT NULL,
  `Datum` int(11) NOT NULL default '0',
  `F_Status_id` int(11) NOT NULL default '0',
  `Kosten` double NOT NULL default '0',
  `F_Inventar_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`Reparatur_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=203 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Reparaturstatus`
-- 

CREATE TABLE `T_Reparaturstatus` (
  `Status_id` int(11) NOT NULL auto_increment,
  `Status` varchar(20) collate latin1_german1_ci NOT NULL default '',
  PRIMARY KEY  (`Status_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Schueler`
-- 

CREATE TABLE `T_Schueler` (
  `Nr` int(11) NOT NULL default '0',
  `Name` varchar(40) collate latin1_german1_ci NOT NULL default '',
  `Vorname` varchar(35) collate latin1_german1_ci NOT NULL default '',
  `Klasse` varchar(10) collate latin1_german1_ci NOT NULL default '',
  `Tutor` varchar(20) collate latin1_german1_ci NOT NULL default '',
  `Spenden` text collate latin1_german1_ci NOT NULL,
  `Geburtsdatum` int(11) NOT NULL default '0',
  `Bemerkung` varchar(100) collate latin1_german1_ci NOT NULL default '',
  `Abteilung` varchar(10) collate latin1_german1_ci NOT NULL default '',
  PRIMARY KEY  (`Nr`),
  KEY `Klasse` (`Klasse`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='Sch�ler des OSZ IMT';

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Schularten`
-- 

CREATE TABLE `T_Schularten` (
  `ID_SArt` tinyint(4) NOT NULL auto_increment,
  `Bezeichnung` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`ID_SArt`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Schularten am OSZ-IMT' AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Stand`
-- 

CREATE TABLE `T_Stand` (
  `Stand` int(11) NOT NULL default '0',
  `Bearbeiter` varchar(30) collate latin1_german1_ci NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='Stand der Sch�ler- und Kursdaten';

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_StuPla`
-- 

CREATE TABLE `T_StuPla` (
  `ID_StuPla` int(11) NOT NULL auto_increment,
  `Lehrer` varbinary(10) NOT NULL default '',
  `Wochentag` tinyint(4) NOT NULL default '0',
  `Stunde` tinyint(4) NOT NULL default '0',
  `Raum` varbinary(10) NOT NULL default '',
  `Fach` varbinary(10) NOT NULL default '',
  `Klasse` varbinary(10) NOT NULL default '',
  `Turnus` varbinary(20) NOT NULL default '',
  `Version` int(11) NOT NULL default '0',
  `GueltigAb` int(11) NOT NULL default '0',
  `Name` varchar(40) collate latin1_german1_ci NOT NULL default '',
  `Vorname` varchar(35) collate latin1_german1_ci NOT NULL default '',
  PRIMARY KEY  (`ID_StuPla`),
  KEY `Version` (`Version`),
  KEY `Klasse` (`Klasse`,`Version`),
  KEY `Lehrer` (`Lehrer`,`Version`),
  KEY `Raum` (`Raum`,`Version`),
  KEY `Tag` (`Wochentag`,`Stunde`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=520174 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_StuPlaDaten`
-- 

CREATE TABLE `T_StuPlaDaten` (
  `AnzVersionen` tinyint(4) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Termin_Betroffene`
-- 

CREATE TABLE `T_Termin_Betroffene` (
  `Betroffen_id` int(11) NOT NULL auto_increment,
  `Betroffen` varchar(50) collate latin1_german1_ci NOT NULL default '',
  `Berechtigte` text collate latin1_german1_ci NOT NULL COMMENT 'Wer darf diese Termine sehen',
  `Veraenderung` text collate latin1_german1_ci NOT NULL COMMENT 'Wer darf diese Termine ändern',
  PRIMARY KEY  (`Betroffen_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='Mögliche Betroffene für Termine (zur Filterung)' AUTO_INCREMENT=33 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Termin_Klassifikationen`
-- 

CREATE TABLE `T_Termin_Klassifikationen` (
  `Klassifikation_id` int(11) NOT NULL auto_increment,
  `Klassifikation` varchar(100) collate latin1_german1_ci NOT NULL default '',
  PRIMARY KEY  (`Klassifikation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='Klassifikationen für Termine' AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Termin_Termine`
-- 

CREATE TABLE `T_Termin_Termine` (
  `Termin_id` int(11) NOT NULL auto_increment,
  `Bezeichnung` varchar(30) collate latin1_german1_ci NOT NULL default '',
  `Datum` int(11) NOT NULL default '0',
  `F_Klassifikation` int(11) NOT NULL default '0',
  `Beschreibung` text collate latin1_german1_ci NOT NULL,
  `Bearbeiter` varchar(20) collate latin1_german1_ci NOT NULL default '',
  `Stand` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `Betroffene` varchar(100) collate latin1_german1_ci NOT NULL default '',
  `Vorlaeufig` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`Termin_id`),
  KEY `Datumindex` (`Datum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='Termine des OSZ IMT' AUTO_INCREMENT=713 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Termin_UserFilter`
-- 

CREATE TABLE `T_Termin_UserFilter` (
  `User` varchar(20) collate latin1_german1_ci NOT NULL default '',
  `Filter` varchar(100) collate latin1_german1_ci NOT NULL default '',
  `Art` varchar(10) collate latin1_german1_ci NOT NULL default '',
  PRIMARY KEY  (`User`,`Art`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='Aktuelle Einstellungen des Terminfilters pro Benutzer';

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Turnus`
-- 

CREATE TABLE `T_Turnus` (
  `ID_Turnus` int(11) NOT NULL auto_increment,
  `Turnus` varbinary(10) NOT NULL default '',
  `SJahr` varchar(5) collate latin1_german1_ci NOT NULL default '',
  `F_ID_Gruppe` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID_Turnus`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=219 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_TurnusGruppe`
-- 

CREATE TABLE `T_TurnusGruppe` (
  `ID_Gruppe` tinyint(4) NOT NULL auto_increment,
  `Name` varchar(30) collate latin1_german1_ci NOT NULL default '',
  `Nummer` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID_Gruppe`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_URLClicks`
-- 

CREATE TABLE `T_URLClicks` (
  `URL` varchar(100) NOT NULL default '',
  `Clicks` bigint(20) NOT NULL default '0',
  `LastClick` int(11) NOT NULL default '0',
  `Referer` varchar(100) NOT NULL default '',
  KEY `URL` (`URL`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Zähler für die URL-Klicks';

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Verhinderung_Sperrungen`
-- 

CREATE TABLE `T_Verhinderung_Sperrungen` (
  `Sperrung_id` int(10) unsigned NOT NULL auto_increment,
  `Bezeichnung` varchar(45) collate latin1_german1_ci NOT NULL default '',
  `Lehrerkuerzel` varchar(5) collate latin1_german1_ci NOT NULL default '',
  `Wochentag` int(10) unsigned NOT NULL default '0',
  `StundeVon` int(10) unsigned NOT NULL default '0',
  `StundeBis` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`Sperrung_id`),
  KEY `Index_2` (`Wochentag`,`StundeVon`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='Regelmäßige Sperrungen' AUTO_INCREMENT=55 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Verhinderungen`
-- 

CREATE TABLE `T_Verhinderungen` (
  `Verhinderung_id` int(10) unsigned NOT NULL auto_increment,
  `Art` varchar(10) collate latin1_german1_ci NOT NULL default '',
  `Wer` varbinary(45) NOT NULL default '',
  `Von` int(10) unsigned NOT NULL default '0',
  `Bis` int(10) unsigned NOT NULL default '0',
  `Hinweis` text collate latin1_german1_ci NOT NULL,
  `Grund` int(10) unsigned NOT NULL default '0',
  `Bearbeiter` varchar(45) collate latin1_german1_ci NOT NULL default '',
  `Stand` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `UE1` tinyint(3) unsigned NOT NULL default '1',
  `UE2` tinyint(3) unsigned NOT NULL default '1',
  `UE3` tinyint(3) unsigned NOT NULL default '1',
  `UE4` tinyint(3) unsigned NOT NULL default '1',
  `UE5` tinyint(3) unsigned NOT NULL default '1',
  `UE6` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`Verhinderung_id`),
  KEY `VonIndex_2` (`Von`),
  KEY `Index_3` (`Art`,`Wer`),
  KEY `BisIndex` (`Bis`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='Verhinderungen mit Datum' AUTO_INCREMENT=1815 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Vertretung_Liste`
-- 

CREATE TABLE `T_Vertretung_Liste` (
  `F_Verhinderung_id` int(11) NOT NULL default '0',
  `Aenderungen` text collate latin1_german1_ci NOT NULL,
  `Betroffen` text collate latin1_german1_ci NOT NULL,
  `Woche` int(11) NOT NULL default '0',
  `Stand` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`F_Verhinderung_id`),
  FULLTEXT KEY `Betroffen` (`Betroffen`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='Temporäre Tabelle zur Speicherung der Vertretungsliste';

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Vertretungen`
-- 

CREATE TABLE `T_Vertretungen` (
  `Vertretung_id` int(10) unsigned NOT NULL auto_increment,
  `Raum` varchar(10) collate latin1_german1_ci NOT NULL default '',
  `Klasse` varchar(10) collate latin1_german1_ci NOT NULL default '',
  `Lehrer` varbinary(10) NOT NULL default '',
  `Fach` varbinary(10) NOT NULL default '',
  `Raum_Neu` varchar(10) collate latin1_german1_ci NOT NULL default '',
  `Klasse_Neu` varchar(10) collate latin1_german1_ci NOT NULL default '',
  `Lehrer_Neu` varbinary(10) NOT NULL default '',
  `Fach_Neu` varbinary(10) NOT NULL default '',
  `Datum` int(10) unsigned NOT NULL default '0',
  `Stunde` int(10) unsigned NOT NULL default '0',
  `Bemerkung` text collate latin1_german1_ci,
  `iKey` varbinary(20) default '',
  `Bearbeiter` varchar(20) collate latin1_german1_ci NOT NULL default '',
  `Stand` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `F_Verhinderung_id` int(10) unsigned default '0',
  `GrundLehrer` int(10) unsigned NOT NULL default '0',
  `GrundKlasse` int(10) unsigned NOT NULL default '0',
  `GrundRaum` int(10) unsigned NOT NULL default '0',
  `Art` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`Vertretung_id`),
  KEY `Datum` (`Datum`,`Stunde`),
  KEY `Raum` (`Raum`),
  KEY `Klasse` (`Klasse`),
  KEY `Lehrer` (`Lehrer`),
  KEY `iKey` (`iKey`),
  KEY `DatumID` (`F_Verhinderung_id`,`Datum`),
  KEY `Stand` (`Stand`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='Vertretungen von->Nach mit Grund' AUTO_INCREMENT=14986 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Werdegang_EMails`
-- 

CREATE TABLE `T_Werdegang_EMails` (
  `EMail` varchar(50) collate latin1_german1_ci NOT NULL default '',
  `Klasse` varchar(10) collate latin1_german1_ci NOT NULL default '',
  `Name` varchar(40) collate latin1_german1_ci NOT NULL default '',
  `Vorname` varchar(35) collate latin1_german1_ci NOT NULL default '',
  `Stand` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`EMail`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='EMailadressen ehemaliger Schüler';

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Woche`
-- 

CREATE TABLE `T_Woche` (
  `ID_Woche` int(11) NOT NULL auto_increment,
  `Woche` tinyint(4) NOT NULL default '0',
  `Montag` int(11) NOT NULL default '0',
  `SJahr` varchar(5) collate latin1_german1_ci NOT NULL default '0',
  `Jahr` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID_Woche`),
  KEY `Montag` (`Montag`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=1324 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_WocheBemerkungen`
-- 

CREATE TABLE `T_WocheBemerkungen` (
  `Schuljahr` varchar(5) collate latin1_german1_ci NOT NULL default '' COMMENT 'Schuljahr',
  `Halbjahr1` text collate latin1_german1_ci NOT NULL COMMENT 'Bemerkung zum 1. Halbjahr',
  `Halbjahr2` text collate latin1_german1_ci NOT NULL COMMENT 'Bemerkung zum 2. Halbjahr',
  PRIMARY KEY  (`Schuljahr`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='Bemerkungen zu den Wochenplänen';

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_WocheTurnus`
-- 

CREATE TABLE `T_WocheTurnus` (
  `F_ID_Turnus` int(11) NOT NULL default '0',
  `F_ID_Woche` int(11) NOT NULL default '0',
  PRIMARY KEY  (`F_ID_Turnus`,`F_ID_Woche`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_eval_Ergebnisse`
-- 

CREATE TABLE `T_eval_Ergebnisse` (
  `F_ID_Umfrage` int(11) NOT NULL default '0',
  `F_ID_Frage` int(11) NOT NULL default '0',
  `NumSel` tinyint(4) NOT NULL default '0',
  `AnzStimmen` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='Die Ergebnisse der einzelnen Fragen der Online-Befragung';

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_eval_Fragen`
-- 

CREATE TABLE `T_eval_Fragen` (
  `ID_Frage` int(11) NOT NULL auto_increment,
  `F_ID_Gruppe` int(11) NOT NULL default '0',
  `Fragennummer` int(11) NOT NULL default '0',
  `FrageText` varchar(200) collate latin1_german1_ci NOT NULL default '',
  `FrageSel` tinyint(4) NOT NULL default '0',
  `LinksText` varchar(30) collate latin1_german1_ci NOT NULL default '',
  `RechtsText` varchar(30) collate latin1_german1_ci NOT NULL default '',
  `IstAktiv` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID_Frage`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='Beinhaltet die Fragen f�r die Online-Umfrage' AUTO_INCREMENT=100 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_eval_Gruppen`
-- 

CREATE TABLE `T_eval_Gruppen` (
  `ID_Gruppe` smallint(6) NOT NULL auto_increment,
  `GruppeText` varchar(50) collate latin1_german1_ci NOT NULL default '',
  `Gruppennummer` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`ID_Gruppe`),
  UNIQUE KEY `Gruppennummer` (`Gruppennummer`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='DIe Fragen-Gruppen der Online-Befragung' AUTO_INCREMENT=100 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_eval_Umfragen`
-- 

CREATE TABLE `T_eval_Umfragen` (
  `ID_Umfrage` int(11) NOT NULL auto_increment,
  `Fach` varbinary(10) NOT NULL default '0',
  `Lehrer` varbinary(20) NOT NULL default '',
  `Klasse` varbinary(20) NOT NULL default '',
  `AnzStimmen` int(11) NOT NULL default '0',
  `Datum` int(11) NOT NULL default '0',
  `Passwort` varbinary(10) NOT NULL default '',
  `Gesperrt` tinyint(4) NOT NULL default '0',
  `Anz_Note_1` int(11) NOT NULL default '0',
  `Anz_Note_2` int(11) NOT NULL default '0',
  `Anz_Note_3` int(11) NOT NULL default '0',
  `Anz_Note_4` int(11) NOT NULL default '0',
  `Anz_Note_5` int(11) NOT NULL default '0',
  `Anz_Note_6` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID_Umfrage`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='Alle Umfragen der Online-Befragung' AUTO_INCREMENT=190 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_umfrage_Ergebnisse`
-- 

CREATE TABLE `T_umfrage_Ergebnisse` (
  `F_ID_Umfrage` int(11) NOT NULL default '0',
  `F_ID_Frage` int(11) NOT NULL default '0',
  `NumSel` tinyint(4) NOT NULL default '0',
  `AnzStimmen` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_umfrage_Ergebnisse_mc`
-- 

CREATE TABLE `T_umfrage_Ergebnisse_mc` (
  `F_ID_Umfrage` int(11) NOT NULL default '0',
  `F_ID_Frage` int(11) NOT NULL default '0',
  `AnzStimmen` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_umfrage_Fragen`
-- 

CREATE TABLE `T_umfrage_Fragen` (
  `ID_Frage` int(11) NOT NULL auto_increment,
  `F_ID_Gruppe` tinyint(4) NOT NULL default '0',
  `Fragennummer` tinyint(11) NOT NULL default '0',
  `FrageText` varchar(200) character set latin1 collate latin1_german1_ci NOT NULL default '',
  `FrageSel` tinyint(4) NOT NULL default '0',
  `LinksText` varchar(30) character set latin1 collate latin1_german1_ci NOT NULL default '',
  `RechtsText` varchar(30) character set latin1 collate latin1_german1_ci NOT NULL default '',
  PRIMARY KEY  (`ID_Frage`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=29 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_umfrage_Fragen_mc`
-- 

CREATE TABLE `T_umfrage_Fragen_mc` (
  `ID_Frage` int(11) NOT NULL auto_increment,
  `F_ID_Gruppe` tinyint(4) NOT NULL default '0',
  `Fragennummer` tinyint(4) NOT NULL default '0',
  `FrageText` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`ID_Frage`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Fuer ''multiple choice'' Fragen' AUTO_INCREMENT=25 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_umfrage_Gruppen`
-- 

CREATE TABLE `T_umfrage_Gruppen` (
  `ID_Gruppe` smallint(6) NOT NULL auto_increment,
  `GruppeText` varchar(200) character set latin1 collate latin1_german1_ci NOT NULL default '',
  `Gruppennummer` smallint(6) NOT NULL default '0',
  `Typ` tinyint(4) NOT NULL default '0' COMMENT '0: Einfach-Bewertung / 1: Multiple Choice (single) / 2: Multiple Choice (multi)',
  PRIMARY KEY  (`ID_Gruppe`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_umfrage_GruppenBildungsgaenge`
-- 

CREATE TABLE `T_umfrage_GruppenBildungsgaenge` (
  `F_ID_Gruppe` tinyint(4) NOT NULL default '0',
  `F_ID_Bg` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`F_ID_Gruppe`,`F_ID_Bg`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_umfrage_Umfragen`
-- 

CREATE TABLE `T_umfrage_Umfragen` (
  `ID_Umfrage` int(11) NOT NULL auto_increment,
  `Lehrer` varchar(20) NOT NULL default '',
  `Klasse` varbinary(20) NOT NULL default '',
  `AnzStimmen` int(11) NOT NULL default '0',
  `Datum` int(11) NOT NULL default '0',
  `Passwort` varbinary(10) NOT NULL default '',
  `Gesperrt` tinyint(4) NOT NULL default '0',
  `F_ID_Bg` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID_Umfrage`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=19 ;
