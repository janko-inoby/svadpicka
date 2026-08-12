<?php

declare(strict_types=1);

use Svadpicka\RsvpRepository;

require dirname(__DIR__) . '/src/bootstrap.php';

$token = trim((string) ($_GET['token'] ?? ''));
$isRsvpPage = $token !== '';
$guest = null;

if ($isRsvpPage) {
    try {
        $guest = (new RsvpRepository())->findActiveGuest($token);
    } catch (Throwable) {
        $guest = null;
    }
}

$esc = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
$checked = static fn (bool $condition): string => $condition ? ' checked' : '';
$answer = static fn (string $key, mixed $default = ''): mixed => $guest[$key] ?? $default;

function renderHero(bool $rsvpPage): void
{
    ?>
    <section class="hero section-pad text-center">
      <div class="container">
        <div class="row">
          <div class="col-12 col-lg-8 offset-lg-2">
            <p class="working-title">SVAD(P)IČKA</p>
            <h1>Paťka a Janko</h1>
            <img class="hero-chateau" src="/assets/images/chateau.svg" alt="Ilustrácia Château Révay">
            <?php if ($rsvpPage): ?>
              <p class="hero-copy">Drahý vyvolený!<br>
                Ak práve čítaš tieto riadky, znamená to, že si srdečne alebo zo slušnosti pozvaný na našu svadbu.<br>
                Prosíme Ťa, aby si si prečítal/a nasledujúce inštrukcie<br class="d-none d-lg-inline">
                a vyplnil/a dotazník, aby si neostal/a o hlade či s riedkou stolicou na záchode.
              </p>
            <?php else: ?>
              <p class="hero-copy">Tutok nájdeš všetko, čo potrebuješ.</p>
            <?php endif; ?>
            <div class="date-badge">18 · 09 · 2026</div>
            <p class="venue">CHÂTEAU RÉVAY · TURČIANSKA ŠTIAVNIČKA</p>
            <?php if ($rsvpPage): ?>
              <a class="text-link" href="/">← VŠETKY INFORMÁCIE O SVADBE</a>
            <?php else: ?>
              <a class="text-link" href="https://maps.app.goo.gl/ybxoPjY4YJ4xaATD8" target="_blank" rel="noopener">OTVORIŤ V GOOGLE MAPS ↗</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>
    <?php
}
?>
<!doctype html>
<html lang="sk">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Svad(p)ička Janka a Paťky — 18. septembra 2026 v Château Révay.">
  <title><?= $isRsvpPage ? 'Potvrdenie účasti' : 'Svad(p)ička' ?> — Paťka a Janko</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/assets/css/app.css?v=4" rel="stylesheet">
</head>
<body>
<main>
  <?php renderHero($isRsvpPage); ?>

  <?php if ($isRsvpPage): ?>
    <section class="rsvp-intro section-pad text-center">
      <div class="container">
        <div class="row">
          <div class="col-12 col-lg-8 offset-lg-2">
            <p class="section-kicker">NAJDÔLEŽITEJŠIA OTÁZKA</p>
            <h2>Prídeš?</h2>
            <p class="narrow-copy">Potvrď nám účasť do <strong>16. 8. 2026</strong>. Každý hosť vypĺňa formulár <strong>samostatne</strong> — áno, aj ten, čo vždy odpovedá za všetkých.</p>
            <a class="pill-button" href="#rsvp">VYPLNIŤ&nbsp; ↓</a>
          </div>
        </div>
      </div>
    </section>

    <section id="rsvp" class="rsvp-form-section section-pad">
      <div class="container position-relative">
        <div class="row">
          <div class="col-12 col-lg-8 offset-lg-2">
            <?php if (!$guest): ?>
              <div class="form-card access-note">
                <strong>Tento osobný odkaz nie je platný.</strong>
                <p class="mb-0">Skontroluj, či sa pri kopírovaní nestratila jeho časť, alebo nám napíš.</p>
              </div>
            <?php else: ?>
              <p class="section-kicker mb-2">TAKŽE,</p>
              <h2><?= $esc((string) $guest['prezyvka']) ?></h2>
              <form id="rsvp-form" action="/api/rsvp" method="post" novalidate>
                <input type="hidden" name="token" value="<?= $esc($token) ?>">
                <input type="hidden" name="poznamka" value="<?= $esc((string) $answer('poznamka')) ?>">
                <input class="visually-hidden" type="text" name="website" tabindex="-1" autocomplete="off">

                <p class="form-intro">Označ správnu možnosť/i</p>

                <fieldset class="form-card">
                  <legend class="field-title">Účasť *</legend>
                  <label class="choice"><input type="radio" name="ucast" value="pridem" required<?= $checked($answer('ucast') === 'pridem') ?>> <span>Prídem, jasné, že prídem</span></label>
                  <label class="choice"><input type="radio" name="ucast" value="nepridem"<?= $checked($answer('ucast') === 'nepridem') ?>> <span>Neprídem, budem to do smrti ľutovať</span></label>
                </fieldset>

                <div data-attending<?= $answer('ucast') === 'nepridem' ? ' hidden' : '' ?>>
                  <fieldset class="form-card">
                    <legend class="field-title">Si? *</legend>
                    <div class="choice-row">
                      <label class="choice"><input type="radio" name="strava" value="masozravec"<?= $checked($answer('strava') === 'masozravec') ?>> <span>Mäsožravec</span></label>
                      <label class="choice"><input type="radio" name="strava" value="bylinozravec"<?= $checked($answer('strava') === 'bylinozravec') ?>> <span>Bylinožravec</span></label>
                    </div>
                  </fieldset>

                  <fieldset class="form-card">
                    <legend class="field-title">Z čoho by si presedel/a celú svadbu na záchode?</legend>
                    <div class="choice-row">
                      <label class="choice"><input type="checkbox" name="alergia_lepok"<?= $checked((bool) $answer('alergia_lepok', false)) ?>> <span>Lepok</span></label>
                      <label class="choice"><input type="checkbox" name="alergia_laktoza"<?= $checked((bool) $answer('alergia_laktoza', false)) ?>> <span>Laktóza</span></label>
                    </div>
                    <textarea class="form-control" name="alergie_ine" placeholder="+ Iné — napíš nám, z čoho Ti bude nevoľno"><?= $esc((string) $answer('alergie_ine')) ?></textarea>
                  </fieldset>

                  <fieldset class="form-card">
                    <legend class="field-title">My vieme sa baviť aj bez neAlka?</legend>
                    <div class="choice-row choice-row-wrap">
                      <?php foreach (['pivo'=>'Pivo','biele_vino'=>'Biele víno','cervene_vino'=>'Červené víno','prosecco'=>'Prosecco','rum'=>'Rum','whiskey'=>'Whiskey','gin'=>'Gin','tequila'=>'Tequila','vodka'=>'Vodka','nepijem'=>'Nepijem alkohol'] as $value => $label): ?>
                        <label class="choice"><input type="checkbox" name="alkohol[]" value="<?= $value ?>"<?= $checked(in_array($value, (array) $answer('alkohol', []), true)) ?>> <span><?= $label ?></span></label>
                      <?php endforeach; ?>
                    </div>
                    <textarea class="form-control" name="alkohol_ine" placeholder="iné"><?= $esc((string) $answer('alkohol_ine')) ?></textarea>
                  </fieldset>

                  <fieldset class="form-card">
                    <legend class="field-title">Potrebuješ nocľah? *</legend>
                    <div class="choice-row">
                      <label class="choice"><input type="radio" name="ubytovanie" value="ano"<?= $checked($answer('ubytovanie') === 'ano') ?>> <span>Áno</span></label>
                      <label class="choice"><input type="radio" name="ubytovanie" value="nie"<?= $checked($answer('ubytovanie') === 'nie') ?>> <span>Nie</span></label>
                    </div>
                    <p class="field-note">60 € / osoba / noc · priamo v areáli</p>
                  </fieldset>

                  <fieldset class="form-card">
                    <legend class="field-title">Koleso nieŠťastia *</legend>
                    <label class="choice"><input type="checkbox" name="koleso_suhlas" value="true"<?= $checked((bool) $answer('koleso_suhlas', false)) ?>> <span>Súhlasím, že môžem byť dobrovoľne/nedobrovoľne zapojený/á.</span></label>
                  </fieldset>

                  <div class="form-card">
                    <label class="field-title" for="dzubox">DŽUBOX</label>
                    <p class="field-note">Sem napíš pieseňku, ktorú pridáme do Spotifája a zahráme Ti ju (možno).</p>
                    <textarea class="form-control" id="dzubox" name="dzubox" placeholder="Interpret — názov pesničky"><?= $esc((string) $answer('dzubox')) ?></textarea>
                  </div>
                </div>

                <button class="pill-button border-0" type="submit">ODOSLAŤ A SPEČATIŤ OSUD</button>
                <p id="form-status" class="form-status" role="status"></p>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>
  <?php else: ?>
    <section class="program-section section-pad">
      <div class="container">
        <div class="row">
          <div class="col-12 col-lg-8 offset-lg-2">
            <p class="section-kicker">NÁVOD NA POUŽITIE SVÄDBØ FROM IKEA</p>
            <h2>Program</h2>
            <p>in progress ...</p>
            <div class="row g-4 info-grid">
              <div class="col-12 col-lg-6"><article class="info-card"><h3>3:15 parkuješ</h3><p>Odporúčaný príchod. Nech stihneš Welcome drink a pár smalltalkov</p></article></div>
              <div class="col-12 col-lg-6"><article class="info-card"><h3>4:20</h3><p>Hovoríme ÁNO (pravdepodobne)</p></article></div>
              <div class="col-12 col-lg-6"><article class="info-card"><h3>4:40</h3><p>Spoločná foto a kondolencie</p></article></div>
              <div class="col-12 col-lg-6"><article class="info-card"><h3>5:00 –</h3><p>Doplníme časom</p></article></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="practical-section section-pad">
      <div class="container">
        <div class="row">
          <div class="col-12 col-lg-8 offset-lg-2">
            <p class="section-kicker">NECH VIEŠ ČO A AKO</p>
            <h2>ne/Dôležité info</h2>
            <div class="row g-4 info-grid">
              <div class="col-12 col-lg-6"><article class="info-card"><h3>Oblečenie</h3><p>Obleč sa pohodlne a možno aj pekne :)<br>Tenisky a šušťáková súprava ale nie.</p></article></div>
              <div class="col-12 col-lg-6"><article class="info-card"><h3>Parkovanie</h3><p>Bezplatne priamo v areáli. <a href="https://maps.app.goo.gl/HD7EsFpcubHGF2XN9" target="_blank" rel="noopener">Pozrieť mapu ↗</a></p></article></div>
              <div class="col-12 col-lg-6"><article class="info-card"><h3>Odvoz</h3><p>Bude šofér, číslo pribudne.</p></article></div>
              <div class="col-12 col-lg-6"><article class="info-card"><h3>Ubytovanie</h3><p>Priamo v Château Révay · 60 € / osoba / noc. Potrebujeme o záujme vedieť.</p></article></div>
              <div class="col-12 col-lg-6"><article class="info-card"><h3>Kontakty</h3><p>Pred svadbou: My – kontakt hádam máš.<br>V deň svadby: Veronika · <a href="tel:+421908458424">0908 458 424</a></p></article></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="gifts-section section-pad">
      <div class="container position-relative">
        <div class="row">
          <div class="col-12 col-lg-8 offset-lg-2">
            <div class="layered-icon gifts-icon" aria-hidden="true"><img src="/assets/images/gifts-1.svg" alt=""><img src="/assets/images/gifts-2.svg" alt=""></div>
            <p class="section-kicker">DARY A KVETY</p>
            <h2>Máme všetko.</h2>
            <h3 class="emphasis">Okrem peňazí.</h3>
            <p>Taniere, príbory, ovce, ovocné misy, drevené pokladničky, spotrebiče a paplóny už máme, tak ak nám chceš pomôcť s pergolou, spálňou alebo iným romantickým stavebným projektom, či svadobnou cestou, obálka to zvládne najlepšie.</p>
            <h3 class="flowers-title">Rezané kvety, prosíme, nie.</h3>
            <p>Ak máš neukojiteľné nutkanie kúpiť kvety, môžeš ho ukojiť trvalkou do záhrady: nepeta, biela levanduľa, perovskia alebo klasický zelený vavrínovec. Ideálne však nič — aj rastliny si radi vyberáme sami. :-D</p>
          </div>
        </div>
      </div>
    </section>

    <section class="rules-section section-pad">
      <div class="container">
        <div class="row">
          <div class="col-12 col-lg-8 offset-lg-2">
            <img class="section-illustration rules-illustration" src="/assets/images/rules.svg" alt="">
            <p class="section-kicker">SVADOBNÉ DESATORO</p>
            <h2>Pravidlá, ktoré sa nevyjednávajú.</h2>
            <ol class="rules-list">
              <li value="1">Nevesta sa neunáša.</li>
              <li value="2">Za málo slanú polievku je pokuta 40 €.</li>
              <li value="4">Slzy dojatia sú povolené. Vreckovky si zabezpeč vo vlastnej réžii.</li>
              <li value="7">Pozor na pesničkového démona!</li>
              <li value="10">Každý sa pokúsi odísť po vlastných.</li>
            </ol>
            <p class="rules-more">Ďalšie pravidlá doplníme, keď nás napadnú ďalšie zákazy.</p>
          </div>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <footer class="footer section-pad text-center">
    <div class="container">
      <div class="row">
        <div class="col-12 col-lg-8 offset-lg-2">
          <p class="footer-names">Paťka a Janko</p>
          <p class="footer-date">18. 9. 2026 · Château Révay</p>
          <p class="footer-joke">Tešíme sa. Alebo sa aspoň tvárime.</p>
        </div>
      </div>
    </div>
  </footer>
</main>
<?php if ($isRsvpPage): ?>
  <script src="/assets/js/rsvp.js?v=4" defer></script>
<?php endif; ?>
</body>
</html>
