<?php
include_once("include/internadmin/include.inc.php");
include_once("include/helper.inc.php");
include_once("include/oszframe.inc.php");

$groupname = trim($_POST['groupname']);

echo ladeOszKopf_o("Intern - Admin (Benutzer aus Gruppe entfernen)", "Administration - Interner Bereich (Benutzer aus Gruppe \"$groupname\" entfernen)");

if($groupname == "")
  dieMsgLink("Sie müssen einen Gruppennamen auswählen!","delete_ufgroupform1.php","Zurück zum Eingabeformular");

if(!readGroupFile($grouplist, $filedate, $errormsg))
  dieMsgLink($errormsg,"index.php","Zurück zum Admin-Bereich");

if (!checkGroupExist($groupname, $grouplist))
  dieMsgLink("Eine Gruppe <b>" . $groupname . "</b> existiert nicht!","index.php","Zurück zum Admin-Bereich");

$anzgroup = sizeof($grouplist);
?>

<table cellpadding = "20" border = "0" width = "100%">
  <tr>
    <td height = "1%" width = "49%">&nbsp;</td>
    <td nowrap align = "center" bgcolor = "#eeeeee">
      <form action="delete_ufgroup.php" method="post">
        <p>
          <select name="username" size="10">
            <?php
              for($i=0;$i<$anzgroup;$i++)
                if($grouplist[$i][0] == $groupname)
                  for($j=1;$j<sizeof($grouplist[$i]);$j++)
                    echo "<option>" . $grouplist[$i][$j] . "</option>";
            ?>
          </select>
        </p>
        <input type="hidden" name="groupname" value="<?php echo $groupname;?>">
        <input type = "submit" value = "Benutzer löschen">
      </form>
    </td>
    <td width = "49%">
      <span class = "smallmessage_kl"><u>Hinweis:</u><br>Wählen Sie das zu <br>löschende Mitglied aus.<br><br>
      <u>ACHTUNG:</u><br>Wenn das letzte Mitglied einer
      <br>Gruppe gelöscht wird, wird auch <br>die Gruppe gelöscht!</span>
    </td>
  </tr>
</table>

<?php
echo ladeOszKopf_u();

echo ladeLink("../index.php", "<b>Admin-Bereich</b>");
echo ladeLink("index.php", "Intern-Admin");

echo ladeOszFuss();
?>