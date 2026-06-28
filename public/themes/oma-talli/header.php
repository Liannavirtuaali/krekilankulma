<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php if (defined('THEME_URL')): ?>
<base href="<?= htmlspecialchars(THEME_URL, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>
<link rel="shortcut icon" href="img/icon.png" />
<title><?= isset($page_title) ? htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') . ' — ' : '' ?>Krekilänkulma</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-4" />
<link href="style.css" rel="stylesheet" type="text/css" />
<?php if (!empty($_vt_head_extra)): echo $_vt_head_extra; endif; ?>
</head>
<body>

	<div id="header"><h1 class="inset-text">Krekilänkulma</h1></div>

	<div id="wrapper"><div id="navbar"><div id="nav">

<?php
$_navSiteUrl = defined('SITE_URL') ? SITE_URL : '';
$_navEntries = function_exists('getThemeManifest') ? (getThemeManifest()['nav'] ?? []) : [];
foreach ($_navEntries as $_navIdx => $_navEntry):
    if (isset($_navEntry['slug'])) {
        $_navHref = $_navSiteUrl . '/pages/' . htmlspecialchars($_navEntry['slug'], ENT_QUOTES, 'UTF-8');
    } elseif ($_navEntry['url'] !== '') {
        $_navHref = htmlspecialchars($_navEntry['url'], ENT_QUOTES, 'UTF-8');
    } else {
        $_navHref = $_navSiteUrl . '/pages/';
    }
?>
<a href="<?= $_navHref ?>"<?= $_navIdx === 0 ? ' style="margin-left: 0;"' : '' ?>><?= htmlspecialchars($_navEntry['label'], ENT_QUOTES, 'UTF-8') ?></a>
<?php endforeach; ?>

	</div></div><div id="content">