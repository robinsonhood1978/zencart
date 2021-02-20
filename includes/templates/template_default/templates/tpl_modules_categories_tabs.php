<?php
/**
 * Module Template - categories_tabs
 *
 * Template stub used to display categories-tabs output
 *
 * @package templateSystem
 * @copyright Copyright 2003-2005 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_modules_categories_tabs.php 3395 2006-04-08 21:13:00Z ajeh $
 */

  include(DIR_WS_MODULES . zen_get_module_directory(FILENAME_CATEGORIES_TABS));
?>
<?php if (CATEGORIES_TABS_STATUS == '1' && sizeof($links_list) >= 1) { ?>
<div class="searchbar">
<div class="searchproduct"> Search Products
    <form name="quick_find" action="/index.php?main_page=advanced_search_result" method="get"><input type="hidden" name="main_page" value="advanced_search_result"><input type="hidden" name="search_in_description" value="1"><input type="text" name="keyword" size="38" maxlength="100" style="width: 320px" placeholder="search here" aria-label="search here"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <button type="submit" class="button button-pill button-primary button-small">Search</button>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="/index.php?main_page=advanced_search">Advanced Search</a></form>
</div>
    <?php //for ($i=0, $n=sizeof($links_list); $i<$n; $i++) { ?>
  <?php //echo $links_list[$i];?>
<?php //} ?>

</div>
<?php } ?>