<?php
/** @var array $roots */

if (!function_exists('renderMedewerkerBoom')) {
    function renderMedewerkerBoom(array $nodes): void
    {
        echo '<ul>';
        foreach ($nodes as $node) {
            $naam = trim($node['voornaam'] . ' ' . $node['achternaam']);
            echo '<li><div class="org-node">';
            echo '<a href="/medewerkers/' . (int) $node['id'] . '">' . htmlspecialchars($naam) . '</a>';
            if (!empty($node['functie'])) {
                echo '<span class="org-functie">' . htmlspecialchars($node['functie']) . '</span>';
            }
            if (!empty($node['afdeling_naam'])) {
                echo '<span class="org-functie">&middot; ' . htmlspecialchars($node['afdeling_naam']) . '</span>';
            }
            if (!empty($node['is_keyuser'])) {
                echo '<span class="badge badge-keyuser">Keyuser</span>';
            }
            echo '</div>';
            if (!empty($node['children'])) {
                renderMedewerkerBoom($node['children']);
            }
            echo '</li>';
        }
        echo '</ul>';
    }
}
?>
<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/medewerkers" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Hiërarchie</div>
  </div>
</div>

<div class="card" style="padding:16px">
  <?php if (empty($roots)): ?>
    <div class="empty-state">Nog geen medewerkers om een hiërarchie op te bouwen.</div>
  <?php else: ?>
    <div class="org-tree">
      <?php renderMedewerkerBoom($roots); ?>
    </div>
  <?php endif; ?>
</div>
