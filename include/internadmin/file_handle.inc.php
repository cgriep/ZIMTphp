<?php
//INHALT:
//(1) readGroupFile()
//(2) readUserFile()
//(3) readGroupAdminFile()
//(4) writeUserFile()
//(5) writeGroupFile()
//(6) writeGroupAdminFile()
//(7) logUserDat()
//(8) logGroupDat()
//(9) logGroupAdminDat()
/*===========================================================================*/
/*readGroupFile() liest die Gruppendatei in ein Array ein*/
//Uebergabepwerte:
//Referenz auf UserArray
//Rueckgabewert:
//Datum der letzten Dateiaenderung
/*grouplist[Index Gruppen][Index Mitglieder]*/
/*grouplist[Index Gruppen][0] ist immer der Name der Gruppe*/
function readGroupFile(&$grouplist, &$filedate, &$errormsg)
{
  global $groupfile;
  if (!file_exists($groupfile))
  {
    $errormsg = "Ein interner Verarbeitungsfehler (01) ist aufgetreten!";
    return false;
  }
  $fp = fopen($groupfile,"r");
  if (!$fp)
  {
    $errormsg = "Ein interner Verarbeitungsfehler (02) ist aufgetreten!";
    return false;
  }
  flock($fp,2);//Datei für andere Programme sperren
  $filedate = filemtime($groupfile);
  $countgroups=0;
  while (!feof($fp))
  {
    $line = fgets($fp,4096);
    if(trim($line) != "")
    {
      $tmpline = explode(":",$line);
      $grouplist[$countgroups][0] = trim($tmpline[0]);//Gruppenname
      $tmparray = explode(" ",trim($tmpline[1]));
      if($tmparray[0]!="")//Gruppe muss mindstens ein Mitglied haben
      {
        $countmember = 1;
        foreach($tmparray as $member)//Schleife ueber alle Mitglieder einer Gruppe
        {
          $grouplist[$countgroups][$countmember] = trim($member);
          $countmember++;
        }
      }
      $countgroups++;
    }
  }
  flock($fp,3);/*Datei freigeben*/
  fclose($fp);
  return true;
}
/*===========================================================================*/
/*readUserFile() liest die Userdatei in ein Array ein*/
//Uebergabewerte:
//Referenz auf Array
//Referenz auf Dateidatum (letzte Aenderung)
//Referenz auf Fehlermeldung
//Rueckgabewert:
//true: Lesevorgang erfolgreich
//false: Lesevorgang fehlgeschlagen
/*userlist[Index User][0] = Benutzername*/
/*userlist[Index User][1] = Passwort*/
function readUserFile(&$userlist, &$filedate, &$errormsg)
{
  global $userfile;
  if(!file_exists($userfile))
  {
    $errormsg = "Ein interner Verarbeitungsfehler (01) ist aufgetreten!";
    return false;
  }
  $fp = fopen($userfile,"r");
  if(!$fp)
  {
    $errormsg = "Ein interner Verarbeitungsfehler (02) ist aufgetreten!";
    return false;
  }
  flock($fp,2);//Datei für andere Programme sperren
  $filedate = filemtime($userfile);
  $countusers=0;
  while(!feof($fp))
  {

    $line = fgets($fp,4096);
    if(trim($line) != "")    
    {
      $tmp_string = explode(":",$line);
      $userlist[$countusers][0] = trim($tmp_string[0]);
      $userlist[$countusers][1] = trim($tmp_string[1]);
      $countusers++;
    }
  }
  flock($fp,3);/*Datei freigeben*/
  fclose($fp);
  return true;
}
/*===========================================================================*/
/*readGroupAdminFile() liest die Admindatei (Admins der Gruppen) in ein Array ein*/
//Uebergabewerte:
//Referenz auf Array
//Referenz auf Dateidatum (letzte Aenderung)
//Referenz auf Fehlermeldung
//Rueckgabewert:
//true: Lesevorgang erfolgreich
//false: Lesevorgang fehlgeschlagen
/*groupadminlist[Index][0] = Name der Gruppe*/
/*groupadminlist[Index][1] = Benutzername des Admin*/
function readGroupAdminFile(&$groupadminlist, &$filedate, &$errormsg)
{
  global $groupadminfile;
  if (!file_exists($groupadminfile))
  {
    $errormsg = "Ein interner Verarbeitungsfehler (01) ist aufgetreten!";
    return false;
  }
  $fp = fopen($groupadminfile,"r");
  if(!$fp)
  {
    $errormsg = "Ein interner Verarbeitungsfehler (02) ist aufgetreten!";
    return false;
  }
  flock($fp,2);//Datei für andere Programme sperren
  $filedate = filemtime($groupadminfile);
  $countgroups=0;
  while (!feof($fp))
  {
    $line = fgets($fp,4096);
    $tmp_string = explode(":",$line);
    $groupadminlist[$countgroups][0] = trim($tmp_string[0]);
    $groupadminlist[$countgroups][1] = trim($tmp_string[1]);
    $countgroups++;
  }
  if($countgroups==1 && $groupadminlist[0][0]=="")//Wenn die Datei leer ist, wird eine Leerzeile eingelesen!!!
  {
    $groupadminlist[0][0]="DUMMY";
    $groupadminlist[0][1]="DUMMY";
    array_pop($groupadminlist);
  }
  flock($fp,3);/*Datei freigeben*/
  fclose($fp);
  return true;
}
/*===========================================================================*/
//writeUserFile() erzeugt aus den UserArray eine neue Passwortdatei.
//Uebergabewerte:
//Datum der letzten Dateiaenderung
//Liste der Benutzer
//Fehlermeldung als Referenz
//Rueckgabewert:
//true: Schreibvorgang erfolgreich
//false: Schreibvorgang fehlgeschlagen
function writeUserFile($filedate_old, $userlist, &$errormsg)
{
  global $userfile;
  global $BAKuserfile;
  $anzuser = sizeof($userlist);
  if(!file_exists($userfile))
  {
    $errormsg = "Ein interner Verarbeitungsfehler (01) ist aufgetreten!";
    return false;
  }
  $filedate_new = filemtime($userfile);
  //BACKUP ANLEGEN
  $BAKuserfile = $BAKuserfile . date("_d_F_H_i") . ".bak";
  copy($userfile,$BAKuserfile);
  $fp = fopen($userfile,"w");
  if(!$fp)
  {
    $errormsg = "Ein interner Verarbeitungsfehler (02) ist aufgetreten!";
    return false;
  }
  flock($fp,2);//Datei für andere Programme sperren
  if($filedate_new != $filedate_old)//Datei wurde seit dem Lesen geaendert!
  {
    flock($fp,3);/*Datei wieder freigeben*/
    fclose($fp);
    $errormsg = "Ein interner Verarbeitungsfehler (03) ist aufgetreten!";
    //BACKUP ZURUECKSPIELEN
    copy($BAKuserfile,$userfile);
    return false;
  }
  for ($i=0;$i<$anzuser;$i++)
  {
    $tmp_string =  $userlist[$i][0] . ":" .  $userlist[$i][1];
    if($i != $anzuser-1)
      $tmp_string = $tmp_string . chr(13) . chr(10);
    fputs($fp,$tmp_string);
  }
  flock($fp,3);/*Datei wieder freigeben*/
  fclose($fp);
  return true;
}
/*===========================================================================*/
//writeGroupFile() erzeugt aus dem GroupArray eine neue Gruppendatei (.htgroups).
//Uebergabewerte:
//Datum der letzten Dateiaenderung
//Liste der Gruppen
//Fehlermeldung als Referenz
//Rueckgabewert:
//true: Schreibvorgang erfolgreich
//false: Schreibvorgang fehlgeschlagen
function writeGroupFile($filedate_old, $grouplist, &$errormsg)
{
  global $groupfile;
  global $BAKgroupfile;
  $anzgroup = sizeof($grouplist);
  if(!file_exists($groupfile))
  {
    $errormsg = "Ein interner Verarbeitungsfehler (01) ist aufgetreten!";
    return false;
  }
  $filedate_new = filemtime($groupfile);
  //BACKUP ANLEGEN
  $BAKgroupfile = $BAKgroupfile . date("_d_F_H_i") . ".bak";
  copy($groupfile,$BAKgroupfile);
  $fp = fopen($groupfile,"w");
  if(!$fp)
  {
    $errormsg = "Ein interner Verarbeitungsfehler (02) ist aufgetreten!";
    return false;
  }
  flock($fp,2);//Datei für andere Programme sperren
  if($filedate_new != $filedate_old)//Datei wurde seit dem Lesen geaendert!
  {
    flock($fp,3);/*Datei wieder freigeben*/
    fclose($fp);
    $errormsg = "Ein interner Verarbeitungsfehler (03) ist aufgetreten!";
    //BACKUP ZURUECKSPIELEN
    copy($BAKgroupfile,$groupfile);
    return false;
  }
  for($group=0;$group<$anzgroup;$group++)
  {
    $anzmember = sizeof($grouplist[$group]);
    if(trim($grouplist[$group][0]) != "")
    {
      $tmp_string =  $grouplist[$group][0] . ":";
      for($member=1;$member<$anzmember;$member++)
      {
        $tmp_string =  $tmp_string . " " . $grouplist[$group][$member];
        if($member == $anzmember-1 && $group != $anzgroup-1)
          $tmp_string = $tmp_string . chr(13) . chr(10);//Zeilenumbruch einfuegen
      }//for
      fputs($fp,$tmp_string);
    }//if
  }//for
  flock($fp,3);/*Datei wieder freigeben*/
  fclose($fp);
  return true;
}
/*===========================================================================*/
//writeGroupAdminFile() erzeugt aus den GroupAdminArray eine neue GroupAdmindatei.
//Uebergabewerte:
//Datum der letzten Dateiaenderung
//GroupAdminArray
//Fehlermeldung als Referenz
//Rueckgabewert:
//true: Schreibvorgang erfolgreich
//false: Schreibvorgang fehlgeschlagen
function writeGroupAdminFile($filedate_old, $groupadminlist, &$errormsg)
{
  global $groupadminfile;
  global $BAKgroupadminfile;
  $anzgroups = sizeof($groupadminlist);
  if(!file_exists($groupadminfile))
  {
    $errormsg = "Ein interner Verarbeitungsfehler (01) ist aufgetreten!";
    return false;
  }
  $filedate_new = filemtime($groupadminfile);
  //BACKUP ANLEGEN
  $BAKgroupadminfile = $BAKgroupadminfile . date("_d_F_H_i") . ".bak";
  copy($groupadminfile,$BAKgroupadminfile);

  $fp = fopen($groupadminfile,"w");
  if(!$fp)
  {
    $errormsg = "Ein interner Verarbeitungsfehler (02) ist aufgetreten!";
    return false;
  }
  flock($fp,2);//Datei für andere Programme sperren
  if ($filedate_new != $filedate_old)//Datei wurde seit dem Lesen geaendert!
  {
    flock($fp,3);/*Datei wieder freigeben*/
    fclose($fp);
    $errormsg = "Ein interner Verarbeitungsfehler (03) ist aufgetreten!";
    //BACKUP ZURUECKSPIELEN
    copy($BAKgroupadminfile,$groupadminfile);
    return false;
  }
  for ($i=0;$i<$anzgroups;$i++)
  {
    $tmp_string =  $groupadminlist[$i][0] . ": " .  $groupadminlist[$i][1];
    if($i != $anzgroups-1)
      $tmp_string = $tmp_string . chr(13) . chr(10);
    fputs($fp,$tmp_string);
  }
  flock($fp,3);/*Datei wieder freigeben*/
  fclose($fp);
  return true;
}
/*===========================================================================*/
//logUserDat(), schreibt die Userdaten in das Logfile
//Uebergabewerte:
//Array mit Benutzerdaten
//String fuer Backupinformation
//Referenz auf Fehlermeldung
function logUserDat($userlist, $bustring, &$errormsg)
{
  global $LOGuserfile;
  $anzuser = sizeof($userlist);
  if (!file_exists($LOGuserfile))
  {
    $errormsg = "Ein interner Verarbeitungsfehler (01) ist aufgetreten!";
    return false;
  }
  $fp = fopen($LOGuserfile,"a");
  if(!$fp)
  {
    $errormsg = "Ein interner Verarbeitungsfehler (02) ist aufgetreten!";
    return false;
  }
  flock($fp,2);
  $tmp_string = chr(13) . chr(10) . chr(13) . chr(10) . $bustring . " | Datum: " . date("d.F Y H:i:s") . chr(13) . chr(10);
  fputs($fp,$tmp_string);
  for($i=0;$i<$anzuser;$i++)
  {
    $tmp_string =  $userlist[$i][0] . ":" .  $userlist[$i][1];
    if($i != $anzuser-1)
      $tmp_string = $tmp_string . chr(13) . chr(10);
    fputs($fp,$tmp_string);
  }
  flock($fp,3);/*Datei freigeben*/
  fclose($fp);
  return true;
}
/*===========================================================================*/
//logGroupDat(), schreibt die Gruppendaten in das Logfile
//Uebergabewerte:
//Array mit Gruppendaten
//String fuer Backupinformation
//Referenz auf Fehlermeldung
function logGroupDat($grouplist, $bustring, &$errormsg)
{
  global $LOGgroupfile;
  $anzgroup = sizeof($grouplist);
  if(!file_exists($LOGgroupfile))
  {
    $errormsg = "Ein interner Verarbeitungsfehler (01) ist aufgetreten!";
    return false;
  }
  $fp = fopen($LOGgroupfile,"a");
  if(!$fp)
  {
    $errormsg = "Ein interner Verarbeitungsfehler (02) ist aufgetreten!";
    return false;
  }
  flock($fp,2);
  $tmp_string = chr(13) . chr(10) . chr(13) . chr(10) . $bustring . " | Datum: " . date("d.F Y H:i:s") . chr(13) . chr(10);
  fputs($fp,$tmp_string);
  for($group=0;$group<$anzgroup;$group++)
  {
    $anzmember = sizeof($grouplist[$group]);
    $tmp_string =  $grouplist[$group][0] . ":";
    for($member=1;$member<$anzmember;$member++)
    {
      $tmp_string =  $tmp_string . " " . $grouplist[$group][$member];
      if($member == $anzmember-1 && $group != $anzgroup-1)
        $tmp_string = $tmp_string . chr(13) . chr(10);
    }//for
    fputs($fp,$tmp_string);
  }//for
  flock($fp,3);/*Datei freigeben*/
  fclose($fp);
  return true;
}
/*===========================================================================*/
//logGroupAdminDat(), schreibt die Gruppendaten in das Logfile
//Uebergabewerte:
//Array mit Gruppendaten
//String fuer Backupinformation
//Referenz auf Fehlermeldung
function logGroupAdminDat($groupadminlist, $bustring, &$errormsg)
{
  global $LOGgroupadminfile;
  $anzgroup = sizeof($groupadminlist);
  if(!file_exists($LOGgroupadminfile))
  {
    $errormsg = "Ein interner Verarbeitungsfehler (01) ist aufgetreten!";
    return false;
  }
  $fp = fopen($LOGgroupadminfile,"a");
  if(!$fp)
  {
    $errormsg = "Ein interner Verarbeitungsfehler (02) ist aufgetreten!";
    return false;
  }
  flock($fp,2);
  $tmp_string = chr(13) . chr(10) . $bustring . " | Datum: " . date("d.F Y H:i:s") . chr(13) . chr(10);
  fputs($fp,$tmp_string);
  for($group=0;$group<$anzgroup;$group++)
  {
    $tmp_string =  $groupadminlist[$group][0] . ": " . $groupadminlist[$group][1] . chr(13) . chr(10);
    fputs($fp,$tmp_string);
  }//for
  flock($fp,3);/*Datei freigeben*/
  fclose($fp);
  return true;
}
/*===========================================================================*/
?>