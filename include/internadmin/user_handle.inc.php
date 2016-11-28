<?php
//INHALT:
//(1) addUser()
//(2) deleteUser()
//(3) addUserToGroup()
//(4) deleteUserFromGroup()
//(5) addGroup()
//(6) deleteGroup()
//(1) addAdminToGroup()
/*===========================================================================*/
//addUser() legt einen neuen Benutzer in der Passwortdatei an.
//Uebergabewerte:
  //Benutzername
  //Passwort
  //Referenz auf Fehlermeldung
//Rueckgabewert:
  //true (Benutzer wurde angelegt)
  //false (Benutzer nicht angelegt)
function addUser($username, $pwd ,&$errormsg)
{
  if(!checkPwd($pwd, $errormsg))
    return false;//Passwort unzulaessig
  if(!checkUserName($username, $errormsg))
    return false;//Benutzername unzulaessig
  if(!readUserFile($userlist, $filedate, $errormsg))
    return false;//Fehler beim Lesen der Datei
  if(checkUserExist($username, $userlist))
  {
    $errormsg = "Ein Benutzer mit dem Namen <b>" . $username . "</b> existiert bereits";
    return false;//Benutzer existiert bereits
  }
  if(!addUserToUserList($username, $pwd, $userlist, $errormsg))
    return false;
  if(!writeUserFile($filedate, $userlist, $errormsg))
    return false;//Fehler beim Schreiben der Datei
  $skriptuser = chop($_SERVER['REMOTE_USER']);
  $bustring = "SKRIPTAUFRUF VON: " . $skriptuser . " | NEUER BENUTZER ANGELEGT: " . $username;
  logUserDat($userlist,$bustring,$errormsg);//Logdatei schreiben
  return true;
}
/*===========================================================================*/
//deleteUser() löscht einen vorhandenen Benutzer aus .htpasswd, aber nur wenn
//der Benutzer keiner Gruppe angehoert.
//Uebergabewerte:
  //Benutzername
  //Referenz auf Fehlermeldung
//Rueckgabewert:
  //true: Benutzer geloescht
  //true: Loeschvorgang fehlgeschlagen
function deleteUser($username, &$errormsg)
{
  if(!readUserFile($userlist, $filedateuser, $errormsg))
    return false;//Fehler beim Lesen der Datei
  if(!checkUserExist($username, $userlist))
  {
    $errormsg = "Es existiert kein Benutzer <b>" . $username . "</b>!";
    return false;//Benutzer existiert bereits
  }//if
  if(!readGroupFile($grouplist, $filedategroup, $errormsg))
    return false;//Fehler beim Lesen der Datei
  $anzgroup = sizeof($grouplist);
  $errormsg = "Der Benutzer <b>" . $username . "</b> gehört folgenden Gruppen an: <br><b>";
  $isgroupmember = false;
  for($group = 0; $group < $anzgroup; $group++)//Schleife ueber alle Gruppen
  {
    $anzmember = sizeof($grouplist[$group]);
    for($member = 1; $member < $anzmember; $member++)//Schleife ueber alle Mitglieder einer Gruppe
    {
      if($username == $grouplist[$group][$member])//User in Gruppe gefunden
      {
        $errormsg = $errormsg . "[". $grouplist[$group][0] . "] ";
        $isgroupmember = true;
      }//if
    }//for
  }//for
  if($isgroupmember)
  {
    $errormsg = $errormsg . "</b><br>Löschen Sie den Benutzer erst aus den Gruppen!";
    return false;
  }
  else//Benutzer wird geloescht
  {
    if(!readUserFile($userlist, $filedateuser, $errormsg))
      return false;//Fehler beim Lesen der Datei
     if(!delUserFromUserList($username,$userlist,$errormsg))
      return false;
    if(!writeUserFile($filedateuser, $userlist, $errormsg))
      return false;//Fehler beim Schreiben der Datei
  }//else
  $skriptuser = chop($_SERVER['REMOTE_USER']);
  $bustring = "SKRIPTAUFRUF VON: " . $skriptuser . " | BENUTZER GELÖSCHT: " . $username;
  logUserDat($userlist,$bustring,$errormsg);//Logdatei schreiben
  return true;
}
/*===========================================================================*/
//addUserToGroup() fuegt einer Gruppe ein Mitglied hinzu.
//Uebergabewerte:
  //Benutzername
  //Gruppenname
  //Referenz auf Fehlermeldung
//Rueckgabewert:
  //true: Benutzer zur Gruppe hinzugefuegt
  //true: Vorgang fehlgeschlagen
function addUserToGroup($username, $groupname, &$errormsg)
{
  if(!readGroupFile($grouplist, $filedate, $errormsg))
    return false;//Fehler beim Lesen der Datei
  if(!checkGroupExist($groupname, $grouplist))
  {
    $errormsg = "Eine Gruppe <b>" . $groupname . "</b> existiert nicht!";
    return false;
  }
  if(checkUserIsGroupmember($username,$groupname,$grouplist))
  {
    $errormsg = "Der Benutzer <b>" . $username . "</b> ist bereits Mitglied von <b>" . $groupname . "</b>!";
    return false;
  }
  if(!readUserFile($userlist, $filedateuser, $errormsg))
    return false;//Fehler beim Lesen der Datei
  if(!checkUserExist($username, $userlist))
  {
    $errormsg = "Ein Benutzer <b>" . $username . "</b> existiert nicht.";
    return false;
  }
  if(!addUserToGroupList($username,$groupname,$grouplist,$errormsg))
    return false;
  if(!writeGroupFile($filedate, $grouplist, $errormsg))
    return false;//Fehler beim Schreiben der Datei
  $skriptuser = chop($_SERVER['REMOTE_USER']);
  $bustring = "SKRIPTAUFRUF VON: " . $skriptuser . " | BENUTZER " . $username . " IN GRUPPE " . $groupname . " AUFGENOMMEN.";
  logGroupDat($grouplist,$bustring,$errormsg);//Logdatei schreiben
  return true;
}
/*===========================================================================*/
//deleteUserFromGroup() löscht ein Mitglied einer Gruppe aus .htgroups
//Uebergabewerte:
  //Benutzername
  //Gruppenname
  //Referenz auf Fehlermeldung
//Rueckgabewert:
  //true: Benutzer aus Gruppe geloescht
  //true: Loeschvorgang fehlgeschlagen
function deleteUserFromGroup($username, $groupname, &$errormsg)
{
  if(!readGroupFile($grouplist, $filedate, $errormsg))
    return false;//Fehler beim Lesen der Datei
  if(!checkGroupExist($groupname, $grouplist))
  {
    $errormsg = "Eine Gruppe <b>" . $groupname . "</b> existiert nicht!";
    return false;
  }
  if(!checkUserIsGroupmember($username,$groupname,$grouplist))
  {
    $errormsg = "<b>" . $username . "</b> ist kein Mitglied von <b>" . $groupname . "</b>!";
    return false;
  }
  if(!delUserFromGroupList($username,$groupname,$grouplist,$errormsg))
    return false;
  if(!writeGroupFile($filedate, $grouplist, $errormsg))
    return false;//Fehler beim Schreiben der Datei
  $skriptuser = chop($_SERVER['REMOTE_USER']);
  $bustring = "SKRIPTAUFRUF VON: " . $skriptuser . " | BENUTZER " . $username . " AUS GRUPPE " . $groupname . " GELÖSCHT.";
  logGroupDat($grouplist,$bustring,$errormsg);//Logdatei schreiben
  return true;
}
/*===========================================================================*/
//addGroup() legt eine neue Gruppe in .htgroups an.
//Uebergabewerte:
  //Gruppename
  //Referenz auf Fehlermeldung
//Rueckgabewert:
  //true (Gruppe wurde angelegt)
  //false (Gruppe nicht angelegt)
function addGroup($groupname, &$errormsg)
{
  if(!readGroupFile($grouplist, $filedate, $errormsg))
    return false;//Fehler beim Lesen der Datei
  if(!checkGroupName($groupname, $errormsg))
    return false;
  if(checkGroupExist($groupname, $grouplist))
  {
    $errormsg = "Eine Gruppe <b>" . $groupname . "</b> existiert bereits!";
    return false;}
  if(!addGroupToGroupList($groupname, $grouplist, $errormsg))
    return false;
  if(!writeGroupFile($filedate, $grouplist, $errormsg))
    return false;//Fehler beim Schreiben der Datei
  $skriptuser = chop($_SERVER['REMOTE_USER']);
  $bustring = "SKRIPTAUFRUF VON: " . $skriptuser . " | NEUE GRUPPE ANGELEGT: " . $groupname;
  logGroupDat($grouplist,$bustring,$errormsg);//Logdatei schreiben
  return true;
}
/*===========================================================================*/
//deleteGroup() loescht eine Gruppe in .htgroups.
//Uebergabewerte:
  //Gruppename
  //Referenz auf Fehlermeldung
//Rueckgabewert:
  //true (Gruppe wurde geloescht)
  //false (Gruppe nicht geloescht)
function deleteGroup($groupname, &$errormsg)
{
  if(!readGroupFile($grouplist, $filedate, $errormsg))
    return false;//Fehler beim Lesen der Datei
  if(!checkGroupExist($groupname, $grouplist))
  {
     $errormsg = "Eine Gruppe <b>" . $groupname . "</b> existiert nicht!";
    return false;
  }
  if(!delGroupFromGroupList($groupname, $grouplist, $errormsg))
    return false;
  if(!writeGroupFile($filedate, $grouplist, $errormsg))
    return false;//Fehler beim Schreiben der Datei
  $skriptuser = chop($_SERVER['REMOTE_USER']);
  $bustring = "SKRIPTAUFRUF VON: " . $skriptuser . " | GRUPPE GELOESCHT: " . $groupname;
  logGroupDat($grouplist,$bustring,$errormsg);//Logdatei schreiben
  return true;
}
/*===========================================================================*/
//addAdminToGroup() fuegt einer Gruppe einen Admin zu.
//Uebergabewerte:
  //Benutzername
  //Gruppenname
  //Referenz auf Fehlermeldung
//Rueckgabewert:
  //true: Admin zur Gruppe hinzugefuegt
  //true: Vorgang fehlgeschlagen
function addAdminToGroup($username, $groupname, &$errormsg)
{
  if(!readGroupFile($grouplist, $filedate, $errormsg))
    return false;//Fehler beim Lesen der Datei
  if(!checkGroupExist($groupname, $grouplist))
  {
    $errormsg = "Eine Gruppe <b>" . $groupname . "</b> existiert nicht.";
    return false;
  }
  if(!readUserFile($userlist, $filedateuser, $errormsg))
    return false;//Fehler beim Lesen der Datei
  if(!checkUserExist($username, $userlist))
  {
    $errormsg = "Ein Benutzer <b>" . $username . "</b> existiert nicht.";
    return false;
  }
  if(!readGroupAdminFile($groupadminlist, $filedateadmin, $errormsg))
    return false;//Fehler beim Lesen der Datei
  if(checkGroupAdminExist($groupname, $groupadminlist))
  {
    $errormsg = "Die Gruppe <b>" . $groupname . "</b> hat bereits einen Betreuer!";
    return false;
  }
  if(!addAdminToGroupAdminList($username,$groupname,$groupadminlist,$errormsg))
    return false;
  if(!writeGroupAdminFile($filedateadmin, $groupadminlist, $errormsg))
     return false;//Fehler beim Schreiben der Datei
  $skriptuser = chop($_SERVER['REMOTE_USER']);
  $bustring = "SKRIPTAUFRUF VON: " . $skriptuser . " | BENUTZER " . $username . " ALS BETREUER FUER GRUPPE " . $groupname . " GESETZT!";
  logGroupAdminDat($groupadminlist, $bustring, $errormsg);//Logdatei schreiben
  return true;
}
/*===========================================================================*/
?>