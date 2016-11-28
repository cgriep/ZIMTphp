<?php
//INHALT:
//(1) addUserToUserList()
//(2) delUserFromUserList()
//(3) addUserToGroupList()
//(3) delUserFromGroupList()
//(5) addGroupToGroupList()
//(6) delGroupFromGroupList()
//(7) addAdminToGroupAdminList()
/*===========================================================================*/
//addUserToUserList() fuegt einen User in die Benutzerliste ein
//Uebergabewerte:
  //Benutzername
  //Passwort
  //Referenz auf Benutzerliste
  //Referenz auf Fehlermeldung
function addUserToUserList($username, $pwd, &$userlist,&$errormsg)
{
  $anzuser = sizeof($userlist);
  $userlist[$anzuser][0] = $username;
  $userlist[$anzuser][1] = cryptPwd($pwd);
  usort($userlist,"cmp");//Benutzernamen alphabetisch sortieren
  return true;
}
/*===========================================================================*/
//delUserFromUserList() loescht einen User aus der Benutzerliste
//Uebergabewerte:
  //Benutzername
  //Referenz auf Benutzerliste
  //Referenz auf Fehlermeldung
function delUserFromUserList($username,&$userlist,&$errormsg)
{
  $anzuser = sizeof($userlist);
  $gefunden = false;
  for($i = 0; $i < $anzuser; $i++)
  {
    if($username == $userlist[$i][0])
    {
      $gefunden = true;
       for($j = $i; $j < $anzuser-1; $j++)
        $userlist[$j] = $userlist[$j+1];
      array_pop($userlist);//letztes Arrayelement loeschen
    }//if
  }//for
  if(!$gefunden)
  {
    $errormsg = "Ein Benutzer <b>" . $username . "</b> existiert nicht.";
    return false;
  }
  return true;
}
/*===========================================================================*/
//addUserToGroupList() fuegt einen User in eine bestimmte Gruppe der Gruppenliste ein
//Uebergabewerte:
  //Benutzername
  //Gruppename
  //Referenz auf Gruppenliste
  //Referenz auf Fehlermeldung
function addUserToGroupList($username,$groupname,&$grouplist,&$errormsg)
{
  $anzgroup = sizeof($grouplist);
  for($group = 0; $group < $anzgroup; $group++)
    if($groupname == $grouplist[$group][0])//Gruppe gefunden
    {
      $anzmember = sizeof($grouplist[$group]);
      if($anzmember == 2 && $grouplist[$group][1] == "[LEER]")//Noch kein Mitglied vorhanden
      {
        $grouplist[$group][1] = $username;
        return true;
      }
      $grouplist[$group][$anzmember] = $username;
      //Mitglieder sortieren
      for($member = 1; $member <= $anzmember; $member++)
        $tmparray[$member-1] = $grouplist[$group][$member];
      usort($tmparray,"cmp1");
      for($member = 1; $member <= $anzmember; $member++)
        $grouplist[$group][$member] = $tmparray[$member-1];
      //Ende sortieren
      return true;
    }//if
}
/*===========================================================================*/
//delUserFromGroupList() loescht einen User aus einer bestimmten Gruppe in der Gruppenliste
//Uebergabewerte:
  //Benutzername
  //Gruppename
  //Referenz auf Gruppenliste
  //Referenz auf Fehlermeldung
function delUserFromGroupList($username,$groupname,&$grouplist,&$errormsg)
{
  $anzgroup = sizeof($grouplist);
  for($group = 0; $group < $anzgroup; $group++)
    if($groupname == $grouplist[$group][0])//Gruppe gefunden
    {
      $anzmember = sizeof($grouplist[$group]);
      for($member = 1; $member < $anzmember; $member++)//Schleife ueber Mitglieder
        if($username == $grouplist[$group][$member])//Mitglied gefunden
        {
          if($anzmember == 2)//Gruppe loeschen
          {
            for($j = $group; $j < $anzgroup-1; $j++)
              $grouplist[$j] = $grouplist[$j+1];
            array_pop($grouplist);
            return true;
          }//if
          for($j = $member; $j < $anzmember-1; $j++)
            $grouplist[$group][$j] = $grouplist[$group][$j+1];
          array_pop($grouplist[$group]);//letztes Arrayelement loeschen
          return true;
        }//if
    }//if
  return true;
}
/*===========================================================================*/
//addGroupToGroupList() fuegt eine Gruppe der Gruppenliste an.
//Uebergabewerte:
  //Gruppename
  //Referenz auf Gruppenliste
  //Referenz auf Fehlermeldung
function addGroupToGroupList($groupname, &$grouplist, &$errormsg)
{
  $anzgroup = sizeof($grouplist);
  $grouplist[$anzgroup][0] = $groupname;
  $grouplist[$anzgroup][1] = "[LEER]";
  usort($grouplist,"cmp");//Gruppennamen alphabetisch sortieren
  return true;
}
/*===========================================================================*/
//delGroupFromGroupList() loescht eine Gruppe aus der Gruppenliste
//Uebergabewerte:
  //Gruppename
  //Referenz auf Gruppenliste
  //Referenz auf Fehlermeldung
function delGroupFromGroupList($groupname, &$grouplist, &$errormsg)
{
  $groupnumber = getGroupNumber($groupname, $grouplist);
  if($groupnumber == -1)
  {
    $errormsg = "Die Gruppe <b>" . $groupname . "</b> konnte nicht gelöscht werden!";
    return false;
  }
  //echo "Nummer der Gruppe: " . $groupnumber;
  for($i = $groupnumber; $i < sizeof($grouplist)-1; $i++)
    $grouplist[$i] = $grouplist[$i+1];
  array_pop($grouplist);
  return true;
}
/*===========================================================================*/
//addAdminToGroupAdminList() fuegt einen Admin in die GroupAdminliste ein
//Uebergabewerte:
  //Benutzername
  //Gruppenname
  //Referenz auf GroupAdminliste
  //Referenz auf Fehlermeldung
function addAdminToGroupAdminList($username, $groupname, &$groupadminlist, &$errormsg)
{
  $anzgroup = sizeof($groupadminlist);
  $groupadminlist[$anzgroup][0] = $groupname;
  $groupadminlist[$anzgroup][1] = $username;
  usort($groupadminlist,"cmp");//Gruppennamen alphabetisch sortieren
  return true;
}
/*===========================================================================*/
?>