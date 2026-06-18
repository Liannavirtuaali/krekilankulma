<?php
/**
 * Navigaatio — sisällytetään header.php:n kautta
 * Käytetään REQUEST_URI:ta aktiivisen linkin tunnistamiseen
 */
$uri = strtok($_SERVER['REQUEST_URI'], '?');
$uri = rtrim($uri, '/');
?>
<nav>
  <ul>
    <li><a href="<?= SITE_URL ?>/"<?= ($uri === '' || $uri === '/pages/index') ? ' class="active"' : '' ?>>Etusivu</a></li>
    <li><a href="<?= SITE_URL ?>/hevoset"<?= ($uri === '/hevoset' || $uri === '/pages/hevoset') ? ' class="active"' : '' ?>>Hevoset</a></li>
    <li><a href="<?= SITE_URL ?>/kasvatus"<?= ($uri === '/kasvatus' || $uri === '/pages/kasvatus') ? ' class="active"' : '' ?>>Kasvatus</a></li>
    <li><a href="<?= SITE_URL ?>/yhteystiedot"<?= ($uri === '/yhteystiedot' || $uri === '/pages/yhteystiedot') ? ' class="active"' : '' ?>>Yhteystiedot</a></li>
    <li><a href="<?= SITE_URL ?>/pages/ajankohtaista.php"
           <?= (strpos($uri, '/pages/ajankohtaista') === 0)
               ? ' class="active"' : '' ?>>Ajankohtaista</a></li>
  </ul>
</nav>
