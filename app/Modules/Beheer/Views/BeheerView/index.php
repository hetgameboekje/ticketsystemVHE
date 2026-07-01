<?php
/** @var string|null $output */
/** @var bool $gitBeschikbaar */
?>
<div class="page-header">
  <div class="page-title">Beheer</div>
</div>

<div class="detail-layout">
  <div>
    <div class="card" style="margin-bottom:16px">
      <div class="card-header"><span class="card-title">GitHub</span></div>
      <div style="padding:16px">
        <p style="font-size:13px;color:var(--color-text-secondary);margin-top:0">
          Haalt de laatste wijzigingen op van de remote repository (<code>git pull</code>) op de server.
        </p>
        <?php if (!$gitBeschikbaar): ?>
          <p style="font-size:13px;color:#b3261e">Geen .git-map gevonden op de server — dit is waarschijnlijk geen git-checkout.</p>
        <?php else: ?>
          <form method="post" action="/beheer/git-pull" onsubmit="return confirm('Weet je zeker dat je git pull wilt uitvoeren op de live server?')">
            <button class="btn btn-primary" type="submit">Git pull uitvoeren</button>
          </form>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><span class="card-title">Database</span></div>
      <div style="padding:16px">
        <p style="font-size:13px;color:var(--color-text-secondary);margin-top:0">
          Zet de tabel-definities in <code>database/xml/*.xml</code> om naar <code>database/.parsed/schema.sql</code>.
          Dit wordt <strong>niet</strong> automatisch uitgevoerd op de database — je past het zelf handmatig toe.
          De SQL is idempotent (CREATE TABLE IF NOT EXISTS), dus bestaande tabellen en data blijven ongewijzigd.
        </p>
        <form method="post" action="/beheer/database-parsen">
          <button class="btn btn-primary" type="submit">Database parsen</button>
        </form>
      </div>
    </div>
  </div>

  <div>
    <div class="card">
      <div class="card-header"><span class="card-title">Uitvoer</span></div>
      <div style="padding:16px">
        <?php if ($output): ?>
          <pre style="white-space:pre-wrap;font-size:12px;background:var(--color-background-secondary);padding:12px;border-radius:8px;max-height:400px;overflow:auto"><?= htmlspecialchars($output) ?></pre>
        <?php else: ?>
          <p style="font-size:13px;color:var(--color-text-secondary)">Nog geen actie uitgevoerd.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
