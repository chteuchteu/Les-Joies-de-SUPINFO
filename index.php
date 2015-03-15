<?php
require_once('ljs-includes.php');

// Pagination
$page = isset($_GET['p']) ? intval($_GET['p']) : 1;
$pagesCount = getPagesCount();
if ($page < 1 || $page > $pagesCount)
    $page = 1;

// Set pageName and homePage for header.part.php template (empty for index)
global $pageName;   $pageName = '';
global $homePage;   $homePage = $page == 1;

include(ROOT_DIR.'/ljs-template/header.part.php');
?>
<div class="content">
    <?php foreach (getGifs($page) as $gif) {
        echo $gif->getHTML();
    } ?>

    <div class="pagination">
        <?php if ($page > 1) { ?>
        <a href="?p=<?php echo $page-1 ?>">&lt; Plus récents</a>
        <?php } ?>
        Page <?php echo $page ?> / <?php echo $pagesCount ?>
        <?php if ($page != $pagesCount) { ?>
        <a href="?p=<?php echo $page+1 ?>">Plus anciens &gt;</a>
        <?php } ?>
    </div>
</div>

<?php include(ROOT_DIR.'/ljs-template/facebook-sdk.part.php'); ?>
<?php include(ROOT_DIR.'/ljs-template/footer.part.php'); ?>
