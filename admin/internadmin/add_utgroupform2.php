<?php
include_once("include/internadmin/include.inc.php");
include_once("include/helper.inc.php");
include_once("include/oszframe.inc.php");

echo ladeOszKopf_o("Intern - Admin (Benutzer zu Gruppe hinzufügen)", "Administration - Interner Bereich (Benutzer zu Gruppe hinzufügen)");

$groupname = trim($_POST['groupname']);

if($groupname=="")
  dieMsgLink("Sie müssen einen Gruppennamen angeben!","add_utgroupform1.php","Zurück zur Auswahl der Gruppen");

if(!readUserFile($userlist, $filedate, $errormsg))
  dieMsgLink($errormsg,"index.php","Zurück zum Admin-Bereich");

$anzuser = sizeof($userlist);

if($anzuser==0)
  dieMsgLink("Es existieren keine Benutzer!","index.php","Zurück zum Admin-Bereich");

if(!readGroupFile($grouplist, $filedate, $errormsg))
  dieMsgLink($errormsg,"index.php","Zurück zum Admin-Bereich");

if (!checkGroupExist($groupname, $grouplist))
  dieMsgLink("Eine Gruppe <b>" . $groupname . "</b> existiert nicht!","index.php","Zurück zum Admin-Bereich");

$anzgroup = sizeof($grouplist);

if($anzgroup==0)
  dieMsgLink("Es existieren keine Gruppen!","index.php","Zurück zum Admin-Bereich");
?>

<table  cellpadding = "20" border = "0" width = "100%">
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td height = "1%" width = "49%">&nbsp;</td>
    <td nowrap bgcolor = "#eeeeee" valign = "top">
      <?php
        for($i=0;$i<$anzgroup;$i++)
          if($grouplist[$i][0] == $groupname)
          {
            $groupnumber = $i;
            $groupsize = sizeof($grouplist[$groupnumber]);
          }
        if($grouplist[$groupnumber][1] != "[LEER]" && $groupsize > 0)
        {
          echo "<b>Gruppe: \"$groupname\"</b> (" . ($groupsize - 1) . " Mitglieder)<br><br>";
          for($j=1;$j<$groupsize;$j++)
          {
            if($j < 10)
              echo "000";
            elseif($j >= 10 && $j < 100)
              echo "00";
            elseif($j >= 100 && $j < 1000)
              echo "0";
            echo $j . ": " . $grouplist[$groupnumber][$j] . "<br>";
          }
        }
        else
          echo "<b>Gruppe: \"$groupname\"</b> hat noch keine Mitglieder!";
      ?>
    </td>
    <td nowrap align = "center" bgcolor = "#eeeeee" valign = "top">
      <form action="add_utgroup.php" method="post">
        <p>
          <select name="username" size="20">
          <?php
            usort($userlist,"cmp");//Benutzernamen alphabetisch sortieren
            for($i=0;$i<$anzuser;$i++)
            {
	            $member = false;
              for($j=1;$j<sizeof($grouplist[$groupnumber]);$j++)
                if($userlist[$i][0] == $grouplist[$groupnumber][$j])//User ist schon in Gruppe
                  $member = true;
              if(!$member && $userlist[$i][0] != "")
                echo "<option>" . $userlist[$i][0] . "</option>";
            }//for
          ?>
          </select>
        </p>
        <input type="hidden" name="groupname" value="<?php echo $groupname;?>">
        <input type = "submit" value = "als Mitglied hinzufügen">
      </form>
    </td>
    <td width = "49%" valign = "top">
      <span class = "smallmessage_kl"><u>Hinweis:</u><br>Wählen Sie den Benutzer aus, <br>der der Gruppe hinzugefügt werden soll.</span>
    </td>
  </tr>
</table>

<?php
echo ladeOszKopf_u();

echo ladeLink("../index.php", "<b>Admin-Bereich</b>");
echo ladeLink("index.php", "Intern-Admin");

echo ladeOszFuss();
?>