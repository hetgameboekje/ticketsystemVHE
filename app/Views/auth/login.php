<?php /** @var string|null $error */ ?>
<div class="login-wrap">
  <div class="login-card">
    <div class="login-brand">Intranet</div>
    <?php if (!empty($error)): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="/login">
      <div class="form-group">
        <label class="form-label">E-mailadres</label>
        <input type="email" name="email" placeholder="naam@intranet.local" required>
      </div>
      <div class="form-group">
        <label class="form-label">Wachtwoord</label>
        <input type="password" name="wachtwoord" required>
      </div>
      <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center">Inloggen</button>
    </form>
  </div>
</div>
