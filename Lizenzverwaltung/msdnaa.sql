-- phpMyAdmin SQL Dump
-- version 2.8.2.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Erstellungszeit: 13. November 2006 um 21:06
-- Server Version: 4.1.18
-- PHP-Version: 5.0.3
-- 
-- Datenbank: `msdnaa`
-- 

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `CD`
-- 

CREATE TABLE `CD` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ProduktID` int(11) unsigned NOT NULL default '0',
  `Datum` date default '0000-00-00',
  `VertragID` int(11) unsigned default '0',
  `Bezeichnung` varchar(60) character set latin1 collate latin1_german1_ci NOT NULL default '',
  `Ansprechpartner` varchar(20) character set latin1 collate latin1_german1_ci default NULL,
  `aufProdukt` int(1) default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_AktuelleAntraege`
-- 

CREATE TABLE `T_AktuelleAntraege` (
  `Vertragsnummer` int(10) unsigned NOT NULL auto_increment,
  `Lehrer` varchar(45) character set latin1 collate latin1_german1_ci NOT NULL default '',
  `Klasse` varchar(45) character set latin1 collate latin1_german1_ci NOT NULL default '',
  `Produkte` varchar(45) character set latin1 collate latin1_german1_ci NOT NULL default '',
  PRIMARY KEY  (`Vertragsnummer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Anträge die per Mail zu verteilen sind' AUTO_INCREMENT=4067 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Antraege`
-- 

CREATE TABLE `T_Antraege` (
  `Name` varchar(30) character set latin1 collate latin1_german1_ci NOT NULL default '',
  `Vorname` varchar(30) character set latin1 collate latin1_german1_ci NOT NULL default '',
  `Art` varchar(20) character set latin1 collate latin1_german1_ci NOT NULL default '',
  `Eingang` datetime NOT NULL default '0000-00-00 00:00:00',
  `Ansprechpartner` varchar(20) character set latin1 collate latin1_german1_ci NOT NULL default '',
  `Produkt` int(10) unsigned NOT NULL default '0',
  `id` int(10) unsigned NOT NULL auto_increment,
  `Bemerkungen` text character set latin1 collate latin1_german1_ci,
  `Vertragsnummer` int(11) NOT NULL default '0',
  `Schueler_Nr` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `Schueler_Nr` (`Schueler_Nr`),
  KEY `Vertragsnummer` (`Vertragsnummer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1713 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Lizenznehmer`
-- 

CREATE TABLE `T_Lizenznehmer` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `Name` varchar(30) character set latin1 collate latin1_german1_ci NOT NULL default '',
  `Vorname` varchar(30) character set latin1 collate latin1_german1_ci default '',
  `Vertragsnummer` int(11) NOT NULL default '0',
  `Art` varchar(20) character set latin1 collate latin1_german1_ci NOT NULL default 'Labor',
  `ProduktID` int(10) unsigned NOT NULL default '0',
  `Datum` date NOT NULL default '0000-00-00',
  `Serialkey` varchar(40) character set latin1 collate latin1_german1_ci NOT NULL default '',
  `Ansprechpartner` varchar(20) character set latin1 collate latin1_german1_ci NOT NULL default '',
  `Schueler_Nr` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `Vertragsnummer` (`Vertragsnummer`),
  KEY `Schueler_Nr` (`Schueler_Nr`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=18356 ;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Lizenznummern`
-- 

CREATE TABLE `T_Lizenznummern` (
  `ProduktID` int(11) unsigned NOT NULL default '0',
  `Serialkey` varchar(40) character set latin1 collate latin1_german1_ci NOT NULL default '',
  `Art` varchar(20) character set latin1 collate latin1_german1_ci NOT NULL default 'Volume',
  PRIMARY KEY  (`Serialkey`,`ProduktID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Produkte`
-- 

CREATE TABLE `T_Produkte` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `Produkt` varchar(30) character set latin1 collate latin1_german1_ci NOT NULL default '',
  `sichtbar` int(11) default '0',
  `Gesperrt` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=41 ;
