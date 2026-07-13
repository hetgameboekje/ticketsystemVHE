<div class="page-header">
  <div style="display:flex;align-items:center;gap:12px">
    <a class="btn" href="/kennisbank" style="padding:6px 10px">&larr;</a>
    <div class="page-title">Nieuw artikel</div>
  </div>
</div>

<form method="post" action="/kennisbank">
  <div class="card" style="margin-bottom:16px">
    <div style="padding:16px">
      <div class="form-group"><label class="form-label">Titel</label><input type="text" name="titel" required></div>
      <div class="form-group"><label class="form-label">Categorie</label><input type="text" name="categorie" placeholder="bijv. Handleiding, FAQ, Beleid"></div>
      <div class="form-group"><label class="form-label">Inhoud</label><textarea name="inhoud" style="min-height:160px" required></textarea></div>
    </div>
  </div>

  <div style="display:flex;gap:8px;margin-top:8px">
    <button class="btn btn-primary" type="submit">Opslaan</button>
    <a class="btn" href="/kennisbank">Annuleren</a>
  </div>
</form>
