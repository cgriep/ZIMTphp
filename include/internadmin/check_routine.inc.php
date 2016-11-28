<?php
//INHALT:
//(1) checkUserExist()
//(2) checkGroupExist()
//(3) checkGroupAdminExist()
//(4) checkPwd()
//(5) checkUserName()
//(6) checkGroupName()
//(7) checkUserIsGroupmember()
/*===========================================================================*/
//checkUserExist() ueberprueft ob der Benutzername in der uebergebenen Liste existiert.
//Uebergabewerte: Benutzername, Benutzerliste//Rueckgabewert: true (Benutzer existiert) oder false (Benutzer existiert nicht)
function checkUserExist($username, $userlist)	
{	
  $anzuser = sizeof($userlist);	
  for($i = 0; $i < $anzuser; $i++)		
  if(strtolower($username) == strtolower($userlist[$i][0]))			
    return true;	
  return false;	
}
/*===========================================================================*/
//checkGroupExist() ueberprueft ob der Benutzername in der uebergebenen Liste existiert.
//Uebergabewerte: Gruppename, Gruppenliste//Rueckgabewert: true (Gruppe existiert) oder false (Gruppe existiert nicht)
function checkGroupExist($groupname, $grouplist)	
{
  $anzgroup = sizeof($grouplist);
  for($i = 0; $i < $anzgroup; $i++)		
    if(strtolower($groupname) == strtolower($grouplist[$i][0]))			
      return true;	
  return false;	
}
/*===========================================================================*/
//checkGroupAdminExist() ueberprueft ob die Gruppe schon einen Admin hat.
//Uebergabewerte: Gruppename, GruppenAdmin//Rueckgabewert: true (Gruppe hat Admin) oder false (kein Admin)
function checkGroupAdminExist($groupname, $groupadminlist)	
{	
  $anzgroup = sizeof($groupadminlist);	
  for($i = 0; $i < $anzgroup; $i++)		
  if(strtolower($groupname) == strtolower($groupadminlist[$i][0]))			
    return true;	
  return false;	
}
/*===========================================================================*/
//checkPwd() ueberprueft, ob das Passwort unzulaessige Zeichen enthaelt etc.
//Uebergabewerte: Passwort
//Rueckgabewerte:	
  //true: Passwort o.k.	
  //false: Passwort unzulaessig	
  //per Referenz: Formatierte Fehlermeldung
function checkPwd($pwd, &$errormsg)	
{	
  if(strlen($pwd)==0 || strlen($pwd)>12)		
  {		
    $errormsg = "Ungültige Eingabe im Feld \"Passwort\" !";		
    return false;		
  }	
  if(($count = strlen($pwd)) < 6)		
  {		
    $errormsg = "Ihr neues Passwort hat nur $count Zeichen (Minimum: 6)!";		
    return false;		
  }	
  /*Passwort auf unzulaessige Zeichen ueberpruefen*/	
  $sonder = false;	
  $leer = false;	
  for($i=0; $i < strlen($pwd); $i++)		
  {		
    if(ord($pwd[$i]) > 127)			
    {			
      $nogood[$i] = $pwd[$i];			
      $sonder = true;/*Passwort enthält Sonderzeichen*/			
    }		
    if($pwd[$i] == " ")			
    $leer = true;/*Passwort enthält Leerzeichen*/		
  }//for	
  if($sonder)		
  {		
    $errormsg = "Das Passwort enthält unzulässige Zeichen: ";		
    foreach($nogood as $value)			
      $errormsg = $errormsg . " " . $value;		
    return false;		
  }	
  if($leer)		
  {		
    $errormsg = "Das Passwort enthält Leerzeichen.";		
    return false;		
  }	
  return true;	
}
/*===========================================================================*/
//checkUserName() ueberprueft, ob der Benutzername gueltig ist.
//Uebergabewerte: Benutzername
//Rueckgabewerte:	
  //true: Benutzername o.k.	
  //false: Benutzername unzulaessig	
  //per Referenz: Formatierte Fehlermeldung
function checkUserName($username, &$errormsg)	
{
  if (strlen($username)==0 || strlen($username)>30)		
  {		
    $errormsg = "Ungültige Eingabe im Feld \"Benutzername\" !";		
    return false;		
  }	
  /*Benutzername auf unzulaessige Zeichen ueberpruefen*/	
  $sonder = false;	
  $leer = false;	
  for($i=0; $i < strlen($username); $i++)			
  {    
    if(ord($username[$i]) > 127)			
    {			
      $nogood[$i] = $username[$i];			
      $sonder = true;/*Benutzername enthält Sonderzeichen*/			
    }
    if($username[$i] == " ")			
    $leer = true;/*Benutzername enthält Leerzeichen*/		
  }//for
  if($sonder)		
  {		
    $errormsg = "Der Benutzername enthält unzulässige Zeichen: ";		
    foreach($nogood as $value)		$errormsg = $errormsg . " " . $value;		
    return false;		
  }	
  if($leer)		
  {		
    $errormsg = "Der Benutzername enthält Leerzeichen.";		
    return false;		
  }
  return true;
}
/*===========================================================================*/
//checkGroupName() ueberprueft, ob der Gruppenname zulaessig ist.
//Uebergabewerte: Gruppename
//Rueckgabewerte:	
  //true: Gruppename o.k.	
  //false: Gruppename unzulaessig	
  //per Referenz: Formatierte Fehlermeldung
function checkGroupName($groupname, &$errormsg)	
{	
  if(strlen($groupname)==0 || strlen($groupname)>12)		
  {		
    $errormsg = "Ungültige Eingabe im Feld \"Gruppenname\" !";		
    return false;		
  }	
  /*Benutzername auf unzulaessige Zeichen ueberpruefen*/	
  $sonder = false;	
  $leer = false;	
  for($i=0; $i < strlen($groupname); $i++)		
  {  		
    if(ord($groupname[$i]) > 127)			
    {			
      $nogood[$i] = $groupname[$i];			
      $sonder = true;/*Gruppenname enthält Sonderzeichen*/			
    }		
    if($groupname[$i] == " ")			
    $leer = true;/*Gruppenname enthält Leerzeichen*/		
  }//for
  if($sonder)		
  {		
    $errormsg = "Der Gruppenname enthält unzulässige Zeichen: ";		
    foreach($nogood as $value)		
      $errormsg = $errormsg . " " . $value;		
    return false;
  }
  if($leer)		
  {		
    $errormsg = "Der Gruppenname enthält Leerzeichen.";		
    return false;		
  }	
  return true;	
}
/*===========================================================================*/
//checkUserIsGroupmember() ueberprueft ob eine Benutzer Mitglied eienr Gruppe ist
  //Uebergabewerte:	
  //Benutzername	
  //Gruppenanme	
  //Gruppenliste
function checkUserIsGroupmember($username,$groupname,$grouplist)	
{	
  $anzgroup = sizeof($grouplist);	
  for($group = 0; $group < $anzgroup; $group++)//Schleife ueber alle Gruppen		
    if($groupname == $grouplist[$group][0])			
      for($member = 1; $member < sizeof($grouplist[$group]); $member++)//Schleife ueber alle Mitglieder der Gruppe				
        if($username == $grouplist[$group][$member])//User in Gruppe gefunden					
          return true;	
  return false;	
}
/*===========================================================================*/
?>