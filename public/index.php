<?php

declare(strict_types=1);

use Svadpicka\RsvpRepository;

require dirname(__DIR__) . '/src/bootstrap.php';

$token = trim((string) ($_GET['token'] ?? ''));
$guest = null;
if ($token !== '') {
    try {
        $guest = (new RsvpRepository())->findActiveGuest($token);
    } catch (Throwable) {
        $guest = null;
    }
}
$fullName = $guest ? trim($guest['meno'] . ' ' . $guest['priezvisko']) : '';
?>
<!doctype html>
<html lang="sk">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Svad(p)ička — Janko a Paťka</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>
<main class="container py-5" style="max-width: 760px">
  <h1>Svad(p)ička</h1>
  <p>Janko a Paťka · 18. 9. 2026 · Château Révay</p>

  <?php if (!$guest): ?>
    <div class="alert alert-warning">Otvor svoj osobný odkaz z pozvánky.</div>
  <?php else: ?>
    <form id="rsvp-form" action="/api/rsvp" method="post" novalidate>
      <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES) ?>">
      <input class="d-none" type="text" name="website" tabindex="-1" autocomplete="off">

      <div class="mb-4">
        <label class="form-label" for="name">Meno a priezvisko</label>
        <input class="form-control" id="name" name="meno_a_priezvisko" value="<?= htmlspecialchars($fullName, ENT_QUOTES) ?>" required>
      </div>

      <fieldset class="mb-4"><legend class="h5">Prídeš?</legend>
        <label class="me-3"><input type="radio" name="ucast" value="pridem" required> Prídem</label>
        <label><input type="radio" name="ucast" value="nepridem"> Neprídem</label>
      </fieldset>

      <div data-attending>
        <fieldset class="mb-4"><legend class="h5">Si?</legend>
          <label class="me-3"><input type="radio" name="strava" value="masozravec"> Mäsožravec</label>
          <label class="me-3"><input type="radio" name="strava" value="bylinozravec"> Bylinožravec</label>
          <label><input type="radio" name="strava" value="vsezravec"> Všežravec</label>
        </fieldset>

        <div class="mb-4"><label class="form-label">Alergie a intolerancie</label><br>
          <label class="me-3"><input type="checkbox" name="alergia_lepok"> Lepok</label>
          <label><input type="checkbox" name="alergia_laktoza"> Laktóza</label>
          <textarea class="form-control mt-2" name="alergie_ine" placeholder="Iné"></textarea>
        </div>

        <fieldset class="mb-4"><legend class="h5">Čo s tebou poteší bar?</legend>
          <?php foreach (['pivo'=>'Pivo','biele_vino'=>'Biele víno','cervene_vino'=>'Červené víno','prosecco'=>'Prosecco','rum'=>'Rum','whiskey'=>'Whiskey','gin'=>'Gin','tequila'=>'Tequila','vodka'=>'Vodka','nepijem'=>'Nepijem alkohol'] as $value => $label): ?>
            <label class="me-3 mb-2"><input type="checkbox" name="alkohol[]" value="<?= $value ?>"> <?= $label ?></label>
          <?php endforeach; ?>
          <textarea class="form-control mt-2" name="nealko" placeholder="My vieme sa baviť aj bez neAlka? Napíš čo piješ."></textarea>
        </fieldset>

        <fieldset class="mb-4"><legend class="h5">Máš záujem o ubytovanie?</legend>
          <label class="me-3"><input type="radio" name="ubytovanie" value="ano"> Áno</label>
          <label><input type="radio" name="ubytovanie" value="nie"> Nie</label>
        </fieldset>

        <label class="d-block mb-4"><input type="checkbox" name="koleso_suhlas" value="true"> Súhlasím s Kolesom neŠťastia.</label>
        <textarea class="form-control mb-3" name="dzubox" placeholder="Džubox — pesnička na želanie (nepovinné)"></textarea>
        <textarea class="form-control mb-4" name="poznamka" placeholder="Odkaz alebo poznámka"></textarea>
      </div>

      <button class="btn btn-dark" type="submit">Odoslať odpoveď</button>
      <p id="form-status" class="mt-3" role="status"></p>
    </form>
  <?php endif; ?>
</main>
<script src="/assets/js/rsvp.js" defer></script>
</body>
</html>

