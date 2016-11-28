<?php

  $dbName = "GriepC";
  $dbUser = "mctest";
  $dbPassword = "testmc";

  $Bewertungen[0] = "unbewertet";
  $Bewertungen[1] = "OG";
  $Bewertungen[2] = "IHK";

  // Datenbank öffnen
  $db = mysql_connect("localhost", $dbUser, $dbPassword);
  mysql_select_db($dbName, $db);

function AnzahlTestFragen($testnummer)
{
  $Anzahl = 0;
  $query = mysql_query("SELECT Count(*) FROM T_Testfragen WHERE Test_id = $testnummer");
  if ( $row = mysql_fetch_row($query) )
  {
    $Anzahl = $row[0];
  }
  mysql_free_result($query);
  return $Anzahl;
}

function AnzahlFrageAntworten($fragenummer)
{
  $Anzahl = 0;
  if ( is_numeric($fragenummer) )
  {
    $query = mysql_query("SELECT Count(*) FROM T_Antwortmoeglichkeiten WHERE Frage_id = $fragenummer");
    if ( $row = mysql_fetch_row($query) )
    {
      $Anzahl = $row[0];
    }
    mysql_free_result($query);
  }
  return $Anzahl;
}

function AnzahlFrageAntwortenRichtig($fragenummer)
{
  $Anzahl = 0;
  $query = mysql_query("SELECT Count(*) FROM T_Antwortmoeglichkeiten WHERE Frage_id = $fragenummer AND richtig");
  if ( $row = mysql_fetch_row($query) )
  {
    $Anzahl = $row[0];
  }
  mysql_free_result($query);
  return $Anzahl;
}

function AnzahlPunkteTest($testnummer)
{
  $Anzahl = 0;
  $query = mysql_query("SELECT Count(*) FROM T_Antwortmoeglichkeiten INNER JOIN ".
   "T_Testfragen ON T_Antwortmoeglichkeiten.Frage_id = T_Testfragen.Frage_id WHERE ".
   "Test_id = $testnummer AND richtig");
  if ( $row = mysql_fetch_row($query) )
  {
    $Anzahl = $row[0];
  }
  mysql_free_result($query);
  return $Anzahl;
}
function istFrageRichtig($fragenummer, $antwortnummer)
{
  $richtig = false;
  $query = mysql_query("SELECT richtig FROM T_Antwortmoeglichkeiten WHERE " .
   "Antwort_id = $antwortnummer AND Frage_id = $fragenummer");
  if ( $row = mysql_fetch_row($query) )
    $richtig = $row[0];
  mysql_free_result($query);
  return $richtig;
}

function Wertung($Art, $Punkte)
{
  if ( $Art == 1 ) {
    if ( $Punkte < 10 ) return "0 Punkte / 6";
    else if ( $Punkte < 20 ) return "1 Punkt / 5-";
    else if ( $Punkte < 35 ) return "2 Punkte / 5";
    else if ( $Punkte < 45 ) return "3 Punkte / 5+";
    else if ( $Punkte < 50 ) return "4 Punkte / 4-";
    else if ( $Punkte < 55 ) return "5 Punkte / 4";
    else if ( $Punkte < 60 ) return "6 Punkte / 4+";
    else if ( $Punkte < 65 ) return "7 Punkte / 3-";
    else if ( $Punkte < 70 ) return "8 Punkte / 3";
    else if ( $Punkte < 75 ) return "9 Punkte / 3+";
    else if ( $Punkte < 80 ) return "10 Punkte / 2-";
    else if ( $Punkte < 85 ) return "11 Punkte / 2";
    else if ( $Punkte < 90 ) return "12 Punkte / 2+";
    else if ( $Punkte < 95 ) return "13 Punkte / 1-";
    else if ( $Punkte < 100 ) return "14 Punkte / 1";
    else return "15 Punkte / 1+";
  }
  else if ( $Art == 2 ) { // Berufsbildende Oberstufe
    if ( $Punkte < 10 ) return "6";
    else if ( $Punkte < 20 ) return "5,8";
    else if ( $Punkte < 30 ) return "5,6";
    else if ( $Punkte < 34 ) return "5,4";
    else if ( $Punkte < 38 ) return "5,2";
    else if ( $Punkte < 42 ) return "5,0";
    else if ( $Punkte < 46 ) return "4,8";
    else if ( $Punkte < 50 ) return "4,6";
    else if ( $Punkte < 53 ) return "4,4";
    else if ( $Punkte < 57 ) return "4,2";
    else if ( $Punkte < 60 ) return "4,0";
    else if ( $Punkte < 64 ) return "3,8";   
    else if ( $Punkte < 67 ) return "3,6";
    else if ( $Punkte < 70 ) return "3,4";
    else if ( $Punkte < 73 ) return "3,2";
    else if ( $Punkte < 75 ) return "3,0";
    else if ( $Punkte < 78 ) return "2,8";
    else if ( $Punkte < 81 ) return "2,6";
    else if ( $Punkte < 83 ) return "2,4";
    else if ( $Punkte < 85 ) return "2,2";
    else if ( $Punkte < 88 ) return "2,0";
    else if ( $Punkte < 90 ) return "1,8";
    else if ( $Punkte < 92 ) return "1,6";
    else if ( $Punkte < 95 ) return "1,4";
    else if ( $Punkte < 98 ) return "1,2";
    else return "1,0";
  }
  else if ( $Art == 4 ) {
    if ( $Punkte < 20 ) return "6";
    else if ( $Punkte < 30 ) return "5,3";
    else if ( $Punkte < 40 ) return "5,0";
    else if ( $Punkte < 50 ) return "4,7";
    else if ( $Punkte < 55 ) return "4,3";
    else if ( $Punkte < 60 ) return "4,0";
    else if ( $Punkte < 65 ) return "3,7";
    else if ( $Punkte < 70 ) return "3,3";
    else if ( $Punkte < 75 ) return "3,0";
    else if ( $Punkte < 80 ) return "2,7";
    else if ( $Punkte < 85 ) return "2,3";
    else if ( $Punkte < 90 ) return "2,0";
    else if ( $Punkte < 95 ) return "1,7";
    else if ( $Punkte < 100 ) return "1,3";
    else return "1,0";
  }
  /*
  else if ( $Art == 3 ) {
    if ( $Punkte < 20 ) return "0 Punkte / 6";
    else if ( $Punkte < 30 ) return "5-";
    else if ( $Punkte < 40 ) return "5";
    else if ( $Punkte < 50 ) return "5+";
    else if ( $Punkte < 55 ) return "4-";
    else if ( $Punkte < 60 ) return "4";
    else if ( $Punkte < 65 ) return "4+";
    else if ( $Punkte < 70 ) return "3-";
    else if ( $Punkte < 75 ) return "3";
    else if ( $Punkte < 80 ) return "3+";
    else if ( $Punkte < 85 ) return "2-";
    else if ( $Punkte < 90 ) return "2";
    else if ( $Punkte < 95 ) return "2+";
    else if ( $Punkte < 100 ) return "1-";
    else return "1";
  }
  */
  else
    return "unbewertet";
}

function ErsetzeCodeLeerzeichen($String)
{
  while ( ! (strpos($String,"\n") === false) )
  {
    $S .= substr($String, 0, strpos($String,"\n"));
    $String = substr($String, strpos($String,"\n")+1);
    while ( substr($String,0,1) == "\r" ) $String = substr($String,1);
    while ( substr($String,0,1) == " " )
    {
      $S .= "&nbsp;";
      $String = substr($String,1);
    }
  }
  $S .= $String;
  return $S;
}
?>