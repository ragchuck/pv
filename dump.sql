-- phpMyAdmin SQL Dump
-- version 3.3.9.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 20. März 2012 um 21:00
-- Server Version: 5.1.41
-- PHP-Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: 'solardb'
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'parameter'
--

CREATE TABLE IF NOT EXISTS parameter (
  feld text NOT NULL,
  wert text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'solardata'
--

CREATE TABLE IF NOT EXISTS solardata (
  `Date` date NOT NULL,
  `TimeStamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  AMsAmp float NOT NULL,
  AMsVol float NOT NULL,
  AMsWatt float NOT NULL,
  BMsAmp float NOT NULL,
  BMsVol float NOT NULL,
  BMsWatt float NOT NULL,
  Error tinyint(4) NOT NULL,
  ETotal float NOT NULL,
  art varchar(4) NOT NULL DEFAULT 'STD',
  PRIMARY KEY (`TimeStamp`),
  KEY `Date` (`Date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'solardata_soll'
--

CREATE TABLE IF NOT EXISTS solardata_soll (
  `date` date NOT NULL,
  wert float NOT NULL,
  PRIMARY KEY (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
