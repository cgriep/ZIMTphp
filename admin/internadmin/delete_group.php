<?php
include_once("include/internadmin/include.inc.php");
include_once("include/helper.inc.php");
include_once("include/oszframe.inc.php");

echo ladeOszKopf_o("Intern - Admin (Gruppe löschen)", "Administration - Interner Bereich (Gruppe löschen)");

$groupname = trim($_POST['groupname']);

if($groupname == "")
    dieMsgLink("Sie müssen einen Gruppennamen angeben!","delete_groupform.php","Zurück zum Eingabeformular");

if(!deleteGroup($groupname, $errormsg))
    dieMsgLink($errormsg,"delete_groupform.php","Zurück zum Eingabeformular");

Msg("Die Gruppe <b>" . $groupname . "</b> wurde gelöscht.");

echo ladeOszKopf_u();

echo ladeLink("../index.php", "<b>Admin-Bereich</b>");
echo ladeLink("index.php", "Intern-Admin");

echo ladeOszFuss();
?>