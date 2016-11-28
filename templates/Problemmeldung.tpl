{include file="header.tpl" title=Problemmeldung}

{if $Versandt}
    <strong>&gt;&gt;&gt; Ihre Meldung wurde an die Techniker versandt.</strong><br />
    Das Problem wird schnellstmöglich bearbeitet werden.<br />
    <hr />
  </td>
</tr>
<tr>
  <td>
{/if}
{if $Meldung}
    <div style="background-color: red; color:white; font-weight:bold;">
	  {$Meldung} 
    </div>
  </td>
</tr>
<tr>
  <td>
{/if}

<form action="{$smarty.server.PHP_SELF}" method="post" name="Inv">
{if $inventar[Inventar_id]}
  <input type="hidden" name="ip[Inventar_id]" value="{$inventar[Inventar_id]}" />
{/if}
    Hier können Sie Probleme mit der Computerhardware am OSZ IMT direkt an die 
    Techniker melden. Durch Ihre Mithilfe kann so gewährleistet werden, dass 
    Probleme möglichst schnell behoben werden können.
  </td>
</tr>
<tr>
  <td>
    <table>
      <tr>
        <td>Standort</td>
        <td>
        {if $Raeumeid}
          <select name="F_Raum_id">
            {html_options values=$Raeumeid output=$RaeumeRaum}
          </select>
        {else}
        {$Standort}
        <input type="hidden" name="F_Raum_id" value="{$F_Raum_id}" />
        {/if}
        </td>
      </tr>
      {if $F_Raum_id}
      <tr>
        <td>
        Art des Gerätes
        </td>        
        <td>
        {if $Artid}
          <select name="F_Art_id">
            {html_options values=$Artid output=$Art}
          </select>
        {else}
        {$Art}
        <input type="hidden" name="F_Art_id" value="{$F_Art_id}" />
        {/if}  
        </td>
      </tr>
      	
      {/if}
      <tr>
        <td>
          {if $F_Art_id && $F_Inventar_id && $F_Raum_id}      
          <input type="Submit" name="Save" value="Meldung absenden" />
          {else}
          <input type="Submit" value="Weiter" />
            {if $F_Raum_id}
         &nbsp;&nbsp;&nbsp;[ <a href="{$smarty.server.PHP_SELF}">Neue Meldung beginnen</a> ]
            {/if}
          {/if}
        </td>
      </tr>
    </table>
    </form> 
  </td>
</tr>
<tr>
  <td align="center">
    <a href="/">Zurück zum internen Bereich</a>

{include file="footer.tpl"}