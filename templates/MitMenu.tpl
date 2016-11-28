  <!-- neue Spalte für Navigationsmenü -->

  <div class="rightMenu">
    <h2>Navigationsmenü</h2>
      <ul class="NavMenu">
{foreach from=$NavMenu item=eintrag}
      <li><a href="{$eintrag[0]}">{$eintrag[1]}</a></li>
{/foreach}
    </ul>
  </div> <!-- Ende Menü -->