<?php
/************************************************************************************************************/
/************************************************************************************************************/
//Liefert den Mittelwert fuer eine Frage einer bestimmten Umfrage
function ergFrage($ID_Umfrage, $ID_Frage)
{
  $sql = "SELECT SUM(AnzStimmen * NumSel), SUM(AnzStimmen) FROM T_eval_Ergebnisse WHERE F_ID_Umfrage = $ID_Umfrage AND F_ID_Frage = $ID_Frage;";
  $rs = mysql_query($sql);
  $row = mysql_fetch_row($rs);
  mysql_free_result($rs);
  if($row[1] == 0)
    return 0;
  return $row[0] / $row[1];
}
/************************************************************************************************************/
/************************************************************************************************************/
//Liefert den Mittelwert aller Fragen einer bestimmten Umfrage
function ergUmfrage($ID_Umfrage)
{
  $sql = "SELECT F_ID_Frage FROM T_eval_Ergebnisse WHERE F_ID_Umfrage = $ID_Umfrage AND F_ID_Frage <> 99;";
  $rs = mysql_query($sql);
  $AnzFragen = 0;
  $SumWert = 0;
  while($row = mysql_fetch_row($rs))
  {
    $SumWert += ergFrage($ID_Umfrage, $row[0]);
    $AnzFragen++;
  }
  mysql_free_result($rs);
  if($AnzFragen == 0)
    return 0;
  return $SumWert / $AnzFragen;
}
/************************************************************************************************************/
/************************************************************************************************************/
//Liefert den Mittelwert aller Fragen bezogen auf alle Umfragen (ohne Gewichtung der jeweiligen Stimmabgaben)
//Wird bisher nicht verwendet
function ergUmfragen_1()
{
  $sql = "SELECT ID_Umfrage FROM T_eval_Umfragen;";
  $rs = mysql_query($sql);
  $AnzUmfragen = 0;
  $SumWert = 0;
  while($row = mysql_fetch_row($rs))
  {
    $SumWert += ergUmfrage($row[0]);
    $AnzUmfragen++;
  }
  mysql_free_result($rs);
  if($AnzUmfragen == 0)
    return 0;
  return $SumWert / $AnzUmfragen;
}
/************************************************************************************************************/
/************************************************************************************************************/
//Liefert den Mittelwert aller Fragen bezogen auf  alle Umfragen/alle Umfragen eines bestimmten Lehrers (mit Gewichtung der jeweiligen Stimmabgaben)
function ergUmfragen_2($Lehrer = "")
{
  $SumWert = 0;
  if($Lehrer)
    $sql = "SELECT SUM(E.AnzStimmen) FROM T_eval_Ergebnisse AS E, T_eval_Umfragen AS U WHERE U.ID_Umfrage = E.F_ID_Umfrage AND U.Lehrer = \"$Lehrer\";";
  else
    $sql = "SELECT SUM(AnzStimmen) FROM T_eval_Ergebnisse;";
  $rs = mysql_query($sql);
  $row = mysql_fetch_row($rs);
  $AnzStimmenGesamt = $row[0];
  if($AnzStimmenGesamt == 0)
    return 0;
  mysql_free_result($rs);
  if($Lehrer)
    $sql = "SELECT U.ID_Umfrage, SUM(E.AnzStimmen) FROM T_eval_Ergebnisse AS E, T_eval_Umfragen AS U WHERE U.ID_Umfrage = E.F_ID_Umfrage AND U.Lehrer = \"$Lehrer\" GROUP BY U.ID_Umfrage;";
  else
    $sql = "SELECT F_ID_Umfrage, SUM(AnzStimmen) FROM T_eval_Ergebnisse GROUP BY F_ID_Umfrage;";
  $rs = mysql_query($sql);
  while($row = mysql_fetch_row($rs))
    $SumWert += ergUmfrage($row[0]) * $row[1];
  mysql_free_result($rs);

  return $SumWert / $AnzStimmenGesamt;
}
/************************************************************************************************************/
/************************************************************************************************************/
//Liefert den Durchschnitt der Benotung fuer einen Lehrer/eine Umfrage/alle Umfragen
//Status = 1 -> Key = Lehrer_id
//Status = 2 -> Key = Umfrage_id
//Status = 3 -> alle Umfragen
function ergUmfrageNotenschnitt($Status, $Key = "")
{
  switch($Status)
  {
  case 1:
    $sql = "SELECT SUM(Anz_Note_1), SUM(Anz_Note_2), SUM(Anz_Note_3), SUM(Anz_Note_4), SUM(Anz_Note_5), SUM(Anz_Note_6) FROM T_eval_Umfragen WHERE Lehrer = \"$Key\";";	
    break;
  case 2:
    $sql = "SELECT Anz_Note_1, Anz_Note_2, Anz_Note_3, Anz_Note_4, Anz_Note_5, Anz_Note_6 FROM T_eval_Umfragen WHERE ID_Umfrage = $Key;";
    break;
  case 3:
    $sql = "SELECT SUM(Anz_Note_1), SUM(Anz_Note_2), SUM(Anz_Note_3), SUM(Anz_Note_4), SUM(Anz_Note_5), SUM(Anz_Note_6) FROM T_eval_Umfragen;";
  }
  $rs = mysql_query($sql);
  $row = mysql_fetch_row($rs);
  mysql_free_result($rs);
  $AnzStimmen = $row[0] + $row[1] + $row[2] + $row[3] + $row[4] + $row[5];
  if($AnzStimmen == 0)
    return 0;
  return ($row[0] + $row[1]*2 + $row[2]*3 + $row[3]*4 + $row[4]*5 + $row[5]*6) / $AnzStimmen;
}
/************************************************************************************************************/
/************************************************************************************************************/
//Liefert die Standardabweichung einer Liste mit Umfrageergebnissen
//listWerte[0] = x  -> x ist die Anzahl aller Stimmen fuer das Ergebnis 1
//listWerte[1] = x  -> x ist die Anzahl aller Stimmen fuer das Ergebnis 2 (usw)
function getStandardAbweichung($listWerte)
{  
  //Berechnen des arith. Mittelwerts
  $aritMittel = getAritMittel($listWerte);
  if($aritMittel == 0)
    return 0;
  
  //Berechnen der Standardabweichung
  $sumWerte   = 0;
  $anzStimmen = 0;
  $i          = 1;
  foreach($listWerte as $werte)
  {
    $sumWerte += $werte * pow($i - $aritMittel, 2);
    $anzStimmen += $werte;
    $i++;
  }
  if($anzStimmen > 1)
    return sqrt($sumWerte / ($anzStimmen - 1));
  return 0;
}
/************************************************************************************************************/
/************************************************************************************************************/
//Liefert den arithmetischen Mittelwert einer Liste mit Umfrageergebnissen
//listWerte[0] = x  -> x ist die Anzahl aller Stimmen fuer das Ergebnis 1
//listWerte[1] = x  -> x ist die Anzahl aller Stimmen fuer das Ergebnis 2 (usw)
function getAritMittel($listWerte)
{  
  //Berechnen des arith. Mittelwerts
  $anzStimmen = 0;
  $sumWerte   = 0;
  $i          = 1;
  foreach($listWerte as $werte)
  {
    if(!is_numeric($werte))
      return 0;
    $anzStimmen += $werte;
    $sumWerte   += $werte * $i;
    $i++;
  }
  if($anzStimmen != 0)
    return $sumWerte / $anzStimmen;
  else
    return 0;
}
/************************************************************************************************************/
/************************************************************************************************************/
?>