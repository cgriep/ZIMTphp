<?php

//INHALT:
//(1) getAnzahlUser()
//(2) getAnzahlGroups()
//(3) getGroupNumber()
//(4) cmp()
//(5) cmp1()

/*===========================================================================*/
/*getAnzahlUser() ermittelt die Anzahl der Benutzer*/
/*aus der Passwortdatei.*/
/*Rueckgabewert: Anzahl der Benutzer*/
function getAnzahlUser()
{
  global $userfile;
  if (!file_exists($userfile))
     dieMsg("Ein interner Verarbeitungsfehler (01) ist aufgetreten!");
  $userarray = file($userfile);
  $AnzUser = sizeof($userarray);
  return $AnzUser;
}
/*===========================================================================*/
/*getAnzahlGroups() ermittelt die Anzahl der Gruppen*/
/*aus der Gruppendatei.*/
/*Rueckgabewert: Anzahl der Gruppen*/
function getAnzahlGroups()
{
  global $groupfile;
  if (!file_exists($groupfile))
     dieMsg("Ein interner Verarbeitungsfehler (01) ist aufgetreten!");
  $grouparray = file($groupfile);
  $AnzGroups = sizeof($grouparray);
  return $AnzGroups;
}
/*===========================================================================*/
//getGroupNumber() ermittelt den Index einer Gruppe
//Uebergabewerte: Benutzername, Benutzerliste
//Rueckgabewert: true (Benutzer existiert) oder false (Benutzer existiert nicht)
function getGroupNumber($groupname, $grouplist)
{
  $anzgroup = sizeof($grouplist);
  for($i = 0; $i < $anzgroup; $i++)
    if ($groupname == $grouplist[$i][0])
      return $i;
  return -1;
}
/*===========================================================================*/
//cmp() ist eine Hilfsfunktion fuer usort, zum Sortieren der Userliste
//nach dem Benutzernamen
function cmp($a, $b)
{
  return strcmp(strtolower($a[0]), strtolower($b[0]));
}

/*===========================================================================*/
//cmp1() ist eine Hilfsfunktion fuer usort, zum Sortieren der Userliste
//nach dem Benutzernamen
function cmp1($a, $b)
{
  return strcmp(strtolower($a), strtolower($b));
}
