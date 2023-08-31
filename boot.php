<?php

/**
 * @author Kreatif GmbH
 * @author a.platter@kreatif.it
 * Date: 23.04.21
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

\Kreatif\mail_sprog\lib\Extensions::init();

if (rex_version::compare(rex::getVersion(), '5.15.1', '>=')) {
    // Kreatif custom HACK
    // Overwrites parent method to inject custom kreatif EP REX_LIST_TABLE_ROW in rex_list, used for inline editing in yform overview
    // see https://github.com/redaxo/redaxo/pull/5770
    rex_list::setFactoryClass(rex_mailsprog_list::class);
}
