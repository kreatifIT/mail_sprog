<?php

/**
 * @author Kreatif GmbH
 * @author a.platter@kreatif.it
 * Date: 23.04.21
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


$addon = \rex_addon::get('mail_sprog');

\yform\usability\Usability::installTableSets($addon->getPath('install/tablesets/*.json'));
\yform\usability\Usability::installTableStructure($addon->getPath('install/db_structure/*.php'));
