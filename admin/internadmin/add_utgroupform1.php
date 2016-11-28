<?php
include_once("include/internadmin/include.inc.php");
include_once("include/helper.inc.php");
include_once("include/oszframe.inc.php");

echo ladeOszKopf_o("Intern - Admin (Benutzer zu Gruppe hinzuf�gen)", "Administration - Interner Bereich (Benutzer zu Gruppe hinzuf�gen)");

if(!readGroupFile($grouplist, $filedate, $errormsg))
    dieMsgLink($errormsg,"index.php","Zur�ck zum Admin-Bereich");

$anzgroup = sizeof($grouplist);

if($anzgroup==0)
    dieMsgLink("Es existieren keine Gruppen!","index.php","Zur�ck zum Admin-Bereich");
?>

<table  cellpadding = "20" border = "0" width = "100%">
  <tr>
    <td  height = "1%" width = "49%">&nbsp;</td>
    <td nowrap align = "center" bgcolor = "#eeeeee">
      <form action="add_utgroupform2.php" method="post">
        <p>
          <select name="groupname" size="10">
          <?php
            for($i=0;$i<$anzgroup;$i++)
              echo "<option>" . $grouplist[$i][0] . "</option>";
          ?>
          </select>
        </p>
        <input type = "submit" value = "Gruppe laden">
      </form>
    </td>
    <td width = "49%">
      <span class = "smallmessage_kl"><u>Hinweis:</u><br>Laden Sie die Gruppe, der ein <br>Mitglied hinzugef�gt werden soll.</span>
    </td>
  </tr>
</table>
<?php
echo ladeOszKopf_u();

echo ladeLink("../index.php", "<b>Admin-Bereich</b>");
echo ladeLink("index.php", "Intern-Admin");

echo ladeOszFuss();
?>