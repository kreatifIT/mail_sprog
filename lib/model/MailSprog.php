<?php

/**
 * @author Kreatif GmbH
 * @author a.platter@kreatif.it
 * Date: 14.01.22
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kreatif\mail_sprog\lib\model;


use yform\usability\Model;


class MailSprog extends Model
{
    const TABLE = '{PREFIX}mail_sprog';

    public static function getByWildcard(string $wildcard): ?self
    {
        $query = parent::query();
        $query->where('wildcard', $wildcard);
        return $query->findOne();
    }

    public static function ensureMailWildcards(\rex_package $package)
    {
        foreach (\rex_clang::getAll() as $lang) {
            $langId   = $lang->getId();
            $filepath = $package->getPath("install/lang/mail_sprog_{$lang->getCode()}.csv");

            if (file_exists($filepath)) {
                if (($handle = fopen($filepath, "r")) !== false) {
                    while (($row = fgetcsv($handle, 0, ";")) !== false) {
                        $item = self::getByWildcard($row[0]) ?? self::create();
                        $item->setValue('wildcard', $row[0]);

                        if ('' == $item->getValue("subject_{$langId}")) {
                            $item->setValue("subject_{$langId}", $row[1]);
                        }
                        if ('' == $item->getValue("replace_{$langId}")) {
                            $item->setValue("replace_{$langId}", $row[2]);
                        }
                        $item->save();
                    }
                    fclose($handle);
                }
            }
        }
    }

    public static function beListStyle($params, $value = null, $class = '')
    {
        /** @var \rex_list $list */
        $list = $params['list'];

        if ('delete' == $params['field']) {
            $value = $list->getColumnLink('delete', '<i class="rex-icon rex-icon-delete"></i> ' . \rex_i18n::msg('delete'));
            $class = ' class="rex-table-action"';
        } elseif ('info' == $params['field']) {
            $messages = [];

            foreach (\rex_clang::getAll() as $lang) {
                $subject = trim($list->getValue("subject_{$lang->getId()}"));
                $text    = trim($list->getValue("replace_{$lang->getId()}"));

                if ('' == $subject) {
                    $messages[] = 'Betreff ' . strtoupper($lang->getCode()) . ' ist leer';
                }
                if ('' == $text) {
                    $messages[] = 'Mailtext ' . strtoupper($lang->getCode()) . ' ist leer';
                }
            }
            if (count($messages)) {
                $value = '<span class="label label-warning">' . implode('</span><br/><span class="label label-warning">', $messages) . '</span>';
            } else {
                $value = '<span class="label label-success">OK</span>';
            }
        } elseif ('wildcard' == $params['field']) {
            $value = "<code>{$params['value']}</code>";
        } elseif ('add' == $params['field']) {
            $value = $list->getColumnLink('edit', '<i class="rex-icon rex-icon-refresh"></i>');
        } elseif ('edit' == $params['field']) {
            $value = $list->getColumnLink('edit', '<i class="rex-icon rex-icon-edit"></i> ' . \rex_i18n::msg('edit'));
            $class = ' class="rex-table-action"';
        }

        $value = $value ?? $params['value'];
        return '<td' . $class . '>' . $value . '</td>';
    }
}