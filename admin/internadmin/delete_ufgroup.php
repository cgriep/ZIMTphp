<?php
include_once("include/internadmin/include.inc.php");
include_once("include/helper.inc.php");
include_once("include/oszframe.inc.php");

echo ladeOszKopf_o("Intern - Admin (Benutzer aus Gruppe entfernen)", "Administration - Interner Bereich (Benutzer aus Gruppe entfernen)");

$username = trim($_POST['username']);
$groupname = trim($_POST['groupname']);

if($username == "")
    dieMsgLink("Sie m�ssen einen Benutzernamen angeben!","delete_ufgroupform1.php","Zur�ck zum Eingabeformular");

if($groupname == "")
    dieMsgLink("Sie m�ssen einen Gruppennamen angeben!","delete_ufgroupform1.php","Zur�ck zum Eingabeformular");

if(!deleteUserFromGroup($username, $groupname, $errormsg))
    dieMsgLink($errormsg,"delete_ufgroupform1.php","Zur�ck zum Eingabeformular");

Msg("Benutzer <b>" . $username . "</b> wurde aus der Gruppe <b>" . $groupname . "</b> gel�scht.");

echo ladeOszKopf_u();

echo ladeLink("../index.php", "<b>Admin-Bereich</b>");
echo ladeLink("index.php", "Intern-Admin");

echo ladeOszFuss();
?>