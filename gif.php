<?php
require_once('ljs-includes.php');

if (!isset($_GET['gif']))
    die();

/**
 * Using the RewriteEngine, we should come here from
 *  WEBSITE_URL/quand-...
 * We are actually redirected to
 *  WEBSITE_URL/gif.php?gif=quand-...
 */

$gif_permalink = $_GET['gif'];
$gif = getGifFromPermalink($gif_permalink);
if ($gif == null)
    die();

global $pageName;   $pageName = $gif->catchPhrase;
include(ROOT_DIR.'/ljs-template/header.part.php');
?>
<div class="content">
    <?= $gif->getHTML(); ?>
</div>
<? include(ROOT_DIR.'/ljs-template/facebook-sdk.part.php'); ?>
<? include(ROOT_DIR.'/ljs-template/footer.part.php'); ?>
