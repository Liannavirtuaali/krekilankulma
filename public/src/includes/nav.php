<?php
/**
 * Navigaatio — sisällytetään header.php:n kautta
 * Käytetään PHP_SELF:iä aktiivisen linkin tunnistamiseen
 */
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav>
  <ul>
    <li><a href="<?= SITE_URL ?>/pages/index.php"<?= $current_page === 'index.php' ? ' class="active"' : '' ?>>Etusivu</a></li>
    <li><a href="<?= SITE_URL ?>/pages/hevoset.php"<?= $current_page === 'hevoset.php' ? ' class="active"' : '' ?>>Hevoset</a></li>
    <li><a href="<?= SITE_URL ?>/pages/kasvatus.php"<?= $current_page === 'kasvatus.php' ? ' class="active"' : '' ?>>Kasvatus</a></li>
    <li><a href="<?= SITE_URL ?>/pages/yhteystiedot.php"<?= $current_page === 'yhteystiedot.php' ? ' class="active"' : '' ?>>Yhteystiedot</a></li>
  </ul>
</nav>
