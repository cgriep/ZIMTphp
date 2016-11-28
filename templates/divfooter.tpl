    <div id="ContentAbschluss" valign="middle">
      &nbsp;
      {if $DruckSymbol}
      <a href="javascript:window.print();"><img 
      src="http://img.oszimt.de/button/drucken_blau.gif"
        width="20" height="20" alt="Drucker" title="Seite drucken"></a> 
        <p>Seite drucken</p>
      {/if}
      <a href="#top"><img src="http://img.oszimt.de/nav/pfeitop_blau.gif"
        width="20" height="20" alt="Pfeil hoch" title="zum Seitenbeginn"></a>
    </div>
    <br />&nbsp;
  </div> <!-- content -->
{if $ContentArt}
{include file="$ContentArt.tpl"}
{/if}
</div> <!-- Ende Seite -->

<div class="footer">
  zuletzt geändert<br />
  {$LastChange}<br />
  Fehler bitte an <a href="mailto:griep@oszimt.de">Koll. Griep</a> mailen.
</div>

</body>
</html>