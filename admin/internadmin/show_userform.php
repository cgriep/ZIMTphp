<?php
include_once("include/internadmin/include.inc.php");
include_once("include/helper.inc.php");
include_once("include/oszframe.inc.php");

echo ladeOszKopf_o("Intern - Admin (Benutzeranzeige)", "Administration - Interner Bereich (Anzeige aller Benutzer)");

if(!readUserFile($userlist, $filedate, $errormsg))
  dieMsgLink($errormsg,"index.php","Zurück zum Admin-Bereich");

usort($userlist,"cmp");
$anzuser = sizeof($userlist);

if($anzuser==0)
  dieMsgLink("Es existieren keine Benutzer!","index.php","Zurück zum Admin-Bereich");
?>
<table  cellpadding = "20" border = "0" width = "100%">
  <tr>
    <td height = 1%>&nbsp;</td>
    <td nowrap align = "center">
      <?php
        echo "<b>[$anzuser Benutzer insgesamt]</b>";
        echo "<form>";
        echo "<select name=\"username\" size=\"25\">";
        for($i=0;$i<$anzuser;$i++)
          if($userlist[$i][0] != "")
          {   
            echo "<option>";
            if($i < 10)
              echo "000";
            elseif($i >= 10 && $i < 100)
              echo "00";
            elseif($i >= 100 && $i < 1000)
              echo "0";
            echo $i + 1 . ": " . $userlist[$i][0] . "</option>";
          }
        echo "</select>";
        echo "</p>";
        echo "</form>";
      ?>
    </td>
    <td  height = 1%>&nbsp;</td>
  </tr>
</table>
<?php
echo ladeOszKopf_u();

echo ladeLink("../index.php", "<b>Admin-Bereich</b>");
echo ladeLink("index.php", "Intern-Admin");

echo ladeOszFuss();
?>