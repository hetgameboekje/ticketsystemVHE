<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/printers" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Nieuwe printer</div>
  </div>
</div>

<div class="card">
  <form class="new-form" method="post" action="/printers">
    <div class="form-grid">
      <div class="form-group" style="grid-column:1/-1">
        <label class="form-label">Naam</label>
        <input type="text" name="naam" required placeholder="bv. HP OfficeJet 7740 BG-Testen Proto">
      </div>
      <div class="form-group">
        <label class="form-label">Server (ComputerName)</label>
        <input type="text" name="computer_naam" placeholder="bv. fs01-leenvanpunt">
      </div>
      <div class="form-group">
        <label class="form-label">Type</label>
        <input type="text" name="type" value="Local" placeholder="Local / Netwerk">
      </div>
      <div class="form-group">
        <label class="form-label">Driver</label>
        <input type="text" name="driver_naam" placeholder="bv. HP Universal Printing">
      </div>
      <div class="form-group">
        <label class="form-label">IP-adres / poort</label>
        <input type="text" name="ip_adres" placeholder="bv. 10.32.5.128">
      </div>
      <div class="form-group" style="grid-column:1/-1">
        <label class="form-label">Opmerking</label>
        <textarea name="opmerking"></textarea>
      </div>
    </div>
    <p style="font-size:12px;color:var(--color-text-secondary);margin-bottom:12px">
      Als de server (ComputerName) is ingevuld, wordt het installatiecommando gebouwd met <code>\\server\naam</code>.
      Staat er geen server bij, dan wordt het IP-adres gebruikt.
    </p>
    <div style="display:flex;gap:8px;margin-top:8px">
      <button class="btn btn-primary" type="submit">Toevoegen</button>
      <a class="btn" href="/printers">Annuleren</a>
    </div>
  </form>
</div>
