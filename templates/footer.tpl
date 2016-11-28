</td></tr>
{if $PrintLink }
<tr>
  <td align="right">
    <table>
      <tr>
        <td>
          <a href="{$PrintLink}" target="printerversion">
            <img src="http://img.oszimt.de/button/drucken_blau.gif" width="20"
             height="20" border="0" alt="Druckansicht">
          </a>
        </td>
        <td valign="middle">
          <span class="funktion">Druckansicht</span>
        </td>
        <td width="25">
        </td>
        <td>
          <a href="#top">
            <img src="http://img.oszimt.de/nav/pfeitop_blau.gif"
               width="20" height="20" border="0" alt="zum Seitenbeginn">
          </a>
        </td>
      </tr>
    </table>
  </td>
</tr>
{/if}

</table>
</td>
{if $menue}
  <td valign="top">
  <span class="home-rubrik">Navigationsmenü</span></br>
  <table>
  {section name=Nr loop=$menue}
  <tr>
    <td valign="top">
      <a class="navlink" href="{$menue[Nr][1]}">
      <img width="10pt" height="12pt" border="0" src="http://img.oszimt.de/nav/link-klein.gif" />
      </a>
    </td>
    <td>
      <a class="navlink" href="{$menue[Nr][1]}">{$menue[Nr][0]}</a>
    </td>
  </tr>
  {/section}
  </table></td>
{/if}
</tr>
<tr><td align="right" class="small" {if $menue} colspan="2" {/if}>
zuletzt geändert<br />{$LastChange}
<br />
Fehler bitte an <a href="mailto:griep@oszimt.de">Koll. Griep</a> mailen.
</td></tr>
</table>

</body>
</html>