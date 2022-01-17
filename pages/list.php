<?php

/**
 * @author Kreatif GmbH
 * @author a.platter@kreatif.it
 * Date: 14.01.22
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


use kreatif\mail_sprog\lib\model\MailSprog;


$messages   = [];
$user       = rex::getUser();
$langs      = array_values(rex_clang::getAll());
$action     = rex_request('func', 'string');
$searchTerm = trim(rex_request('search-term', 'string'));
$csrfToken  = rex_csrf_token::factory('mail-sprog-list');


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ('delete' == $action) {
    if ($csrfToken->isValid()) {
        $item = MailSprog::get(rex_get('id', 'int'));

        if ($item && $item->delete()) {
            $messages[] = rex_view::success($this->i18n('wildcard_deleted'));
        }
    } else {
        $messages[] = rex_view::error($this->i18n('csrf_token_invalid'));
    }
} elseif ('save_wildcard' == $action) {
    $wildcard = trim(rex_post('wildcard', 'string'));

    $item = MailSprog::get(rex_post('data-id', 'int'));
    $item = $item ?? MailSprog::create();
    $item->setValue('wildcard', $wildcard);
    foreach (rex_clang::getAll() as $lang) {
        $_key = "replace_{$lang->getId()}";
        $item->setValue($_key, rex_post($_key, 'string'));
        $_subject = "subject_{$lang->getId()}";
        $item->setValue($_subject, trim(strip_tags(rex_post($_subject, 'string'))));
    }
    if ($item->save()) {
        $messages[] = rex_view::success($this->i18n('wildcard_saved'));
    } else {
        $messages[] = rex_view::error(implode('<br/>', $item->getMessages()));
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$selects = [];
foreach ($langs as $lang) {
    $selects[] = "subject_{$lang->getId()}";
    $selects[] = "replace_{$lang->getId()}";
}

$sqlWhere = ['1'];
if ('' != $searchTerm) {
    $sql   = rex_sql::factory();
    $_term = $sql->escape("%{$searchTerm}%");

    $_ors = ["wildcard LIKE {$_term}"];
    foreach ($langs as $lang) {
        $_ors[] = "subject_{$lang->getId()} LIKE {$_term}";
        $_ors[] = "replace_{$lang->getId()} LIKE {$_term}";
    }
    $sqlWhere[] = implode(' OR ', $_ors);
}

$query = 'SELECT id, wildcard, ' . implode(', ', $selects) . ' FROM ' . MailSprog::getDbTable() . ' WHERE (' . implode(') AND (', $sqlWhere) . ') ORDER BY wildcard';
$list  = rex_list::factory($query, 50);
$pager = $list->getPager();
$list->addTableAttribute('class', 'table-striped');
$list->addFormAttribute('data-pjax', '[data-list-container]');

if ('' != $searchTerm) {
    $list->addParam('search-term', $searchTerm);
}

$list->addColumn('add', '', 0, ['<th class="rex-table-icon">###VALUE###</th>', '###VALUE###']);
$list->setColumnParams('add', ['func' => 'edit', 'id' => '']);
$list->setColumnFormat('add', 'custom', [MailSprog::class, 'beListStyle']);

if ($user->hasPerm('mail_sprog[add]')) {
    $addLink = $list->getColumnLink('add', '<i class="rex-icon rex-icon-add-article"></i>', ['id' => '']);
    $list->setColumnLabel('add', $addLink);
    $list->addLinkAttribute('add', 'data-pjax', '[data-list-container]');
} else {
    $list->setColumnLabel('add', '');
}

$list->setColumnLabel('id', $this->i18n('id'));
$list->setColumnLayout('id', ['<th class="rex-table-id">###VALUE###</th>', '###VALUE###']);
$list->setColumnFormat('id', 'custom', [MailSprog::class, 'beListStyle']);

$list->addColumn('info', '', 2, ['<th class="rex-table-icon">###VALUE###</th>', '###VALUE###']);
$list->setColumnLabel('info', $this->i18n('info'));
$list->setColumnLayout('info', ['<th class="rex-table-action">###VALUE###</th>', '###VALUE###']);
$list->setColumnFormat('info', 'custom', [MailSprog::class, 'beListStyle']);

$list->setColumnLabel('wildcard', $this->i18n('mail_wildcard'));
$list->setColumnLayout('wildcard', ['<th>###VALUE###</th>', '###VALUE###']);
$list->setColumnFormat('wildcard', 'custom', [MailSprog::class, 'beListStyle']);

foreach ($langs as $langIdx => $lang) {
    if (0 == $langIdx) {
        $list->setColumnLabel('subject_1', rex_i18n::msg('subject'));
        $list->setColumnLayout('subject_1', ['<th>###VALUE###</th>', '###VALUE###']);
        $list->setColumnFormat('subject_1', 'custom', [MailSprog::class, 'beListStyle']);

        $list->setColumnLabel('replace_1', rex_i18n::msg('mail_text'));
        $list->setColumnLayout('replace_1', ['<th>###VALUE###</th>', '###VALUE###']);
        $list->setColumnFormat('replace_1', 'custom', [MailSprog::class, 'beListStyle']);
    } else {
        $list->removeColumn("subject_{$lang->getId()}");
        $list->removeColumn("replace_{$lang->getId()}");
    }
}

$list->addColumn('edit', '<i class="rex-icon rex-icon-edit"></i> ' . $this->i18n('edit'));
$list->setColumnLabel('edit', $this->i18n('function'));
$list->setColumnLayout('edit', ['<th class="rex-table-action" colspan="2">###VALUE###</th>', '###VALUE###']);
$list->setColumnParams('edit', ['func' => 'edit', 'id' => '###id###', $pager->getCursorName() => $pager->getCursor()]);
$list->addLinkAttribute('edit', 'data-pjax', '[data-list-container]');
$list->setColumnFormat('edit', 'custom', [MailSprog::class, 'beListStyle']);

if ($user->hasPerm('mail_sprog[add]')) {
    $list->addColumn('delete', '<i class="rex-icon rex-icon-delete"></i> ' . $this->i18n('delete'));
    $list->setColumnLabel('delete', $this->i18n('function'));
    $list->setColumnLayout('delete', ['', '###VALUE###']);
    $list->setColumnParams('delete', ['func' => 'delete', 'id' => '###id###'] + $csrfToken->getUrlParams());
    $list->addLinkAttribute('delete', 'data-confirm', $this->i18n('delete') . '?');
    $list->setColumnFormat('delete', 'custom', [MailSprog::class, 'beListStyle']);
} else {
    $list->addColumn('delete', '');
    $list->setColumnLayout('delete', ['', '<td class="rex-table-action"></td>']);
    $list->setColumnFormat('delete', 'custom', [MailSprog::class, 'beListStyle']);
}

$content = $list->get();


$fragment = new rex_fragment();
$fragment->setVar('list', $list);
$search = $fragment->parse('mail_sprog/backend/list_search.php');


$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('list_title'));
$fragment->setVar('content', $content, false);
$fragment->setVar('options', $search, false);

?>
<div data-list-container>
    <?php foreach ($messages as $message): ?>
        <?= $message; ?>
    <?php endforeach; ?>

    <?= $fragment->parse('core/page/section.php'); ?>
</div>
