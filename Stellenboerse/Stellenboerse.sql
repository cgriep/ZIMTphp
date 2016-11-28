-- phpMyAdmin SQL Dump
-- version 2.8.2.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Erstellungszeit: 13. November 2006 um 21:07
-- Server Version: 4.1.18
-- PHP-Version: 5.0.3
-- 
-- Datenbank: `Stellenboerse`
-- 

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `T_Stellenangebote`
-- 

CREATE TABLE `T_Stellenangebote` (
  `Stellen_id` int(11) NOT NULL auto_increment,
  `Titel` varchar(50) character set latin1 NOT NULL default '',
  `Beschreibung` text character set latin1 NOT NULL,
  `Datum` int(11) NOT NULL default '0',
  `Datei` longblob NOT NULL,
  `Mime` varchar(50) character set latin1 NOT NULL default '',
  `Herkunft` text collate latin1_german1_ci NOT NULL,
  PRIMARY KEY  (`Stellen_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci COMMENT='Tabelle mit den aktuellen Stellenangeboten' AUTO_INCREMENT=6 ;
