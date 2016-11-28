<?php
include_once("include/helper.inc.php");
include_once("include/oszframe.inc.php");

echo ladeOszKopf_o("Intern - Admin", "Administration - Interner Bereich");
?>
<table cellpadding = "20" border = "0" width = "100%">
  <tr>
    <td height = "1%" width = "49%">&nbsp;</td>
    <td align = "left" width = "1%" bgcolor = "d1d1d1">
      <b>Benutzerverwaltung</b>
     </td>
    <td nowrap align = "left" bgcolor = "eeeeee">
      <a class = "linktable" href="show_userform.php">Benutzer anzeigen</a>
    </td>
    <td height = 1% width = "49%">&nbsp;</td>    
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>  
  <tr>
    <td height = 1%>&nbsp;</td>
    <td align = "left" bgcolor = "d1d1d1">
      <b>Gruppenverwaltung</b>
    </td>
    <td nowrap align = "left" bgcolor = "eeeeee">
      <a class = "linktable" href="show_groupform1.php">Gruppe anzeigen</a>
      <br><br>
      <a class = "linktable" href="add_groupform.php">Gruppe anlegen</a>
      <br><br>
      <a class = "linktable" href="delete_groupform.php">Gruppe l&ouml;schen</a>
      <br><br>
      <a class = "linktable" href="add_utgroupform1.php">Mitglied zu Gruppe hinzuf&uuml;gen</a>
      <br><br>
      <a class = "linktable" href="delete_ufgroupform1.php">Mitglied aus Gruppe l&ouml;schen</a>
    </td>
    <td height = 1%>&nbsp;</td>    
  </tr>
</table>
<?php
echo ladeOszKopf_u();

echo ladeLink("../index.php", "<b>Admin-Bereich</b>");

echo ladeOszFuss();
?>