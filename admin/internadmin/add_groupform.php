<?php
include_once("include/internadmin/include.inc.php");
include_once("include/helper.inc.php");
include_once("include/oszframetest.inc.php");

echo ladeOszKopf_o("Intern - Admin (Neue Gruppe anlegen)", "Administration - Interner Bereich (Neue Gruppe anlegen)");

if(!readGroupFile($grouplist, $filedate, $errormsg))
    dieMsgLink($errormsg,"index.php","Zurück zum Admin-Bereich");

$anzgroup = sizeof($grouplist);
?>
<table  cellpadding = "20" border = "0" width = "100%">
  <tr>
    <td>&nbsp;</td>  
    <td nowrap bgcolor = "#d1d1d1" height = 1% width = "1%">
      <?php
        echo "<span class = \"line1\"><u>Vorhandene Gruppen</u>:</span><br>";
        for($i=1;$i<=$anzgroup;$i++)
          echo "<span class = \"linemargin1\">(" . $i . ") " . $grouplist[$i-1][0] . "</span><br>";
      ?>
    </td>
    <td nowrap bgcolor = "#eeeeee" align = "center" width = "1%">
      <form action="add_group.php" method="post">
        <p>
          <b>Gruppenname:</b><br>
          <input name="groupname" type="text" size="12" maxlength="12">
        </p>
        <input type = "submit" value = "Gruppe erzeugen">
        <input type = "reset" value = "Eingaben löschen">
      </form>
    </td>
    <td>&nbsp;</td>
  </tr>
</table>
<?php
echo ladeOszKopf_u();

echo ladeLink("../index.php", "<b>Admin-Bereich</b>");
echo ladeLink("index.php", "Intern-Admin");

echo ladeOszFuss();
?>