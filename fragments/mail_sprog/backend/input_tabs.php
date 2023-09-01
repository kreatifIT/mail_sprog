<?php

/**
 * @author Kreatif GmbH
 * @author a.platter@kreatif.it
 * Date: 14.01.22
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/** @var rex_sql $sql */

use Cke5\Utils\Cke5Lang;

$sql = $this->getVar('sql', rex_sql::factory());
/** @var rex_list $list */
$list = $this->getVar('list');
$langs = array_values(rex_clang::getAll());
$pager = $list->getPager();
$itemId = $sql->hasValue('id') ? $sql->getValue('id') : null;


$wildcard = $sql->getValue('wildcard');
$readonly = rex::getUser()->hasPerm('mail_sprog[add]') ? '' : ' readonly="readonly"';

$oldTinyInstalled = (rex_addon::get('tinymce4')->isAvailable());
// fallback tiny
$rteClass = $oldTinyInstalled ? 'tiny5-editor' : 'cke5-editor';
$rteProfile = $oldTinyInstalled ? 'mail-sprog' : 'default';

?>
<tr class="editing-tr-heading">
    <td></td>
    <td><?= $itemId ?? '-' ?></td>
    <td colspan="2"></td>
    <td colspan="2"><input class="form-control" type="text" name="wildcard"
                           value="<?= htmlspecialchars($wildcard) ?>" <?= $readonly ?> /></td>
    <td>
        <button class="btn btn-save btn-block" type="submit" name="func"
                value="save_wildcard"><?= \rex_i18n::msg('update') ?></button>
    </td>
    <td>
        <a href="<?= $list->getUrl([$pager->getCursorName() => $pager->getCursor()]) ?>"
           class="btn btn-primary btn-block" data-pjax="[data-list-container]">
            <?= \rex_i18n::msg('abort') ?>
        </a>
    </td>
</tr>
<tr class="editing-tr">
    <td></td>
    <td colspan="3" class="edit-input-td">
        <ul class="nav nav-pills nav-stacked">
            <?php foreach ($langs as $langIdx => $lang): ?>
                <li role="presentation" <?= $langIdx == 0 ? 'class="active"' : '' ?>>
                    <a href="#lang-<?= $lang->getId() ?>" role="tab" data-toggle="pill">
                        <?= $lang->getName() ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </td>
    <td colspan="2" class="edit-input-td">
        <div class="tab-content">
            <?php foreach ($langs as $langIdx => $lang): ?>
                <?php
                $_value = htmlspecialchars($sql->getValue("replace_{$lang->getId()}"));
                $_subject = $sql->getValue("subject_{$lang->getId()}");
                ?>
                <div role="tabpanel" class="tab-pane  <?= $langIdx == 0 ? 'active' : '' ?>"
                     id="lang-<?= $lang->getId() ?>">

                    <div class="form-group">
                        <label class="control-label"><?= rex_i18n::msg('subject') ?></label>
                        <input type="text" class="form-control" name="subject_<?= $lang->getId() ?>"
                               value="<?= $_subject ?>">
                    </div>

                    <div class="form-group">
                        <label class="control-label"><?= rex_i18n::msg('mail_text') ?></label>
                        <textarea class="form-control <?= $rteClass ?>" name="replace_<?= $lang->getId() ?>"
                                  data-profile="<?= $rteProfile ?>>"><?= $_value ?></textarea>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
        <input type="hidden" name="data-id" value="<?= $itemId ?>">
        <input type="hidden" name="<?= $pager->getCursorName() ?>" value="<?= $pager->getCursor() ?>">
    </td>
    <td colspan="2"></td>
</tr>

