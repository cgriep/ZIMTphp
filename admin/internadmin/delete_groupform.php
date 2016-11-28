<?php
include_once("include/internadmin/include.inc.php");
include_once("include/helper.inc.php");
include_once("include/oszframe.inc.php");

echo ladeOszKopf_o("Intern - Admin (Gruppe löschen)", "Administration - Interner Bereich (Gruppe löschen)");


if(!readGroupFile($grouplist, $filedate, $errormsg))
    dieMsgLink($errormsg,"index.php","Zurück zum Admin-Bereich");
    
$anzgroup = sizeof($grouplist);

if($anzgroup==0)
    dieMsgLink("Es existieren keine Gruppen!","index.php","Zurück zum Admin-Bereich");
?>
<table  cellpadding = "20" border = "0" width = "100%">
  <tr>
    <td height = "1%" width = "49%">&nbsp;</td>
    <td nowrap align = "center" bgcolor = "#eeeeee">
      <form action="delete_group.php" method="post">
        <p>
          <select name="groupname" size="10">
          <?php
            for($i=0;$i<$anzgroup;$i++)
	      echo "<option>" . $grouplist[$i][0] . "</option>";
          ?>
	  </select>
        </p>
        <input type = "submit" value = "Gruppe löschen">
      </form>
    </td>
    <td width = "49%">
      <span class = "smallmessage_kl">
        <u>Hinweis:</u><br>
	  Wählen Sie die zu löschende Gruppe aus.<br>
          Die Mitglieder der Gruppe sind auch weiterhin<br> 
	  als Benutzer in der Passwortdatei vorhanden!<br><br>
        <u>ACHTUNG:</u><br>Sie sind im Begriff eine Gruppe<br>endgültig zu löschen!!!
      </span>
    </td>
  </tr>
</table>
<?php
echo ladeOszKopf_u();

echo ladeLink("../index.php", "<b>Admin-Bereich</b>");
echo ladeLink("index.php", "Intern-Admin");

echo ladeOszFuss();
?>