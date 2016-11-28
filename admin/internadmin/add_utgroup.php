<?php
include_once("include/internadmin/include.inc.php");
include_once("include/helper.inc.php");
include_once("include/oszframe.inc.php");

echo ladeOszKopf_o("Intern - Admin (Benutzer zu Gruppe hinzuf�gen)", "Administration - Interner Bereich (Benutzer zu Gruppe hinzuf�gen)");

$username = trim($_POST['username']);
$groupname = trim($_POST['groupname']);

if($username == "")
  dieMsgLink("Sie m�ssen einen Benutzernamen angeben!","add_utgroupform1.php","Zur�ck zum Eingabeformular");

if($groupname == "")
 dieMsgLink("Sie m�ssen einen Gruppennamen angeben!","add_utgroupform1.php","Zur�ck zum Eingabeformular");

if(!addUserToGroup($username, $groupname, $errormsg))
  dieMsgLink($errormsg,"add_utgroupform1.php","Zur�ck zum Eingabeformular");

Msg("Benutzer <b>" . $username . "</b> wurde zu der Gruppe <b>" . $groupname . "</b> hinzugef�gt.");

echo ladeOszKopf_u();

echo ladeLink("../index.php", "<b>Admin-Bereich</b>");
echo ladeLink("index.php", "Intern-Admin");

echo ladeOszFuss();
?>