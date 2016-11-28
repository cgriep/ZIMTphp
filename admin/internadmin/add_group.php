<?php
include_once("include/internadmin/include.inc.php");
include_once("include/helper.inc.php");
include_once("include/oszframe.inc.php");

echo ladeOszKopf_o("Intern - Admin (Neue Gruppe anlegen)", "Administration - Interner Bereich (Neue Gruppe anlegen)");

$groupname = trim($_POST['groupname']);

if($groupname == "")
  dieMsgLink("Sie müssen einen Gruppennamen angeben!","add_groupform.php","Zurück zum Eingabeformular");

if(!addGroup($groupname, $errormsg))
  dieMsgLink($errormsg,"add_groupform.php","Zurück zum Eingabeformular");

Msg("Gruppe <b>" . $groupname . "</b> erfolgreich angelegt");

echo ladeOszKopf_u();

echo ladeLink("../index.php", "<b>Admin-Bereich</b>");
echo ladeLink("index.php", "Intern-Admin");

echo ladeOszFuss();
?>