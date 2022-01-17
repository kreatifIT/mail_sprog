<?php

/**
 * @author Kreatif GmbH
 * @author a.platter@kreatif.it
 * Date: 14.01.22
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/** @var rex_list $list */
$list = $this->getVar('list');
$page = rex_be_controller::getCurrentPageObject();
$term = rex_request('search-term', 'string');


?>
<form action="<?= $list->getUrl() ?>" method="get" class="form-inline" data-pjax="[data-list-container]">
    <div class="input-group input-group-xs">
        <input type="hidden" name="page" value="<?= $page->getFullKey() ?>"/>
        <input type="text" class="form-control" name="search-term" value="<?= htmlspecialchars($term) ?>"/>
        <div class="input-group-btn">
            <button type="submit" class="btn btn-primary btn-xs">
                <?= rex_i18n::msg('do_search') ?>
            </button>
        </div>
    </div>
</form>
