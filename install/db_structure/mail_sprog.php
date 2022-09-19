<?php

/**
 * This file is part of the Kreatif\Project package.
 *
 * @author Kreatif GmbH
 * @author p.parth@kreatif.it
 * Date: 15.09.21
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Kreatif\mail_sprog\lib\model\MailSprog;
use yform\usability\Usability;


$prio  = 0;
$table = MailSprog::getDbTable();
$langs = array_values(\rex_clang::getAll());


Usability::ensureValueField(
    $table,
    'wildcard',
    'text',
    [
        'prio' => $prio++,
    ],
    [
        'list_hidden' => 0,
        'search'      => 1,
        'label'       => 'Wildcard',
        'db_type'     => 'varchar(191)',
    ]
);

Usability::ensureValidateField(
    $table,
    'unique',
    'empty',
    [
        'prio' => $prio++,
    ],
    [
        'name'         => 'wildcard',
        'table'        => $table,
        'empty_option' => true,
        'message'      => 'Der Platzhalter muss eindeutig sein',
    ]
);

foreach ($langs as $lang) {
    Usability::ensureValueField(
        $table,
        "subject_{$lang->getId()}",
        'text',
        [
            'prio' => $prio++,
        ],
        [
            'list_hidden' => 0,
            'search'      => 1,
            'label'       => 'Subject ' . $lang->getName(),
            'db_type'     => 'varchar(191)',
        ]
    );
    Usability::ensureValueField(
        $table,
        "replace_{$lang->getId()}",
        'textarea',
        [
            'prio' => $prio++,
        ],
        [
            'list_hidden' => 0,
            'search'      => 1,
            'label'       => 'Text ' . $lang->getName(),
            'db_type'     => 'mediumtext',
            'attributes'  => json_encode(['class' => 'tinyMCEEditor']),
        ]
    );
}

Usability::ensureValidateField(
    $table,
    'subject_' . $langs[0]->getId(),
    'empty',
    [
        'prio' => $prio++,
    ],
    [
        'name'    => 'subject_' . $langs[0]->getId(),
        'message' => 'Betreff darf nicht leer sein',
    ]
);

Usability::ensureDateFields($table, $prio);

$yTable = \rex_yform_manager_table::get($table);
\rex_yform_manager_table_api::generateTableAndFields($yTable);
