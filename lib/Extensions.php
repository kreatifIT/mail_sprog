<?php

/**
 * @author Kreatif GmbH
 * @author a.platter@kreatif.it
 * Date: 14.01.22
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kreatif\mail_sprog\lib;


use Kreatif\Api;
use Kreatif\kganalytics\lib\cron\QueueCron;
use Kreatif\kganalytics\lib\Model\Queue;
use Kreatif\mail_sprog\lib\model\MailSprog;
use ReportingTest;
use rex;
use rex_addon;
use rex_extension;
use rex_extension_point;
use rex_file;
use rex_request;
use rex_scss_compiler;
use rex_view;


class Extensions
{

    public static function init()
    {
        // register model class
        \rex_yform_manager_dataset::setModelClass(MailSprog::getDbTable(), MailSprog::class);

        if (rex::isBackend() && rex::getUser()) {
            $addon = rex_addon::get('mail_sprog');

            if ($addon->getProperty('compile') == 1 || !file_exists($addon->getAssetsPath('css/backend.css'))) {
                $cssFilePath = $addon->getPath('assets/css/backend.css');
                $compiler    = new rex_scss_compiler();
                $compiler->setScssFile($addon->getPath('assets/css/backend.scss'));
                $compiler->setCssFile($cssFilePath);
                $compiler->compile();
                rex_file::copy($cssFilePath, $addon->getAssetsPath('css/backend.css'));
            }
            rex_view::addCssFile($addon->getAssetsUrl('css/backend.css'));

            rex_extension::register('REX_LIST_TABLE_ROW', [self::class, 'handleListRows']);
        }
    }

    public static function handleListRows(rex_extension_point $ep): void
    {
        $action = rex_get('func', 'string');

        if ('edit' == $action && 'mail_sprog/list' == \rex_be_controller::getCurrentPageObject()->getFullKey()) {
            $html = '';
            /** @var \rex_list $list */
            $list = $ep->getParam('list');
            /** @var \rex_sql $sql */
            $sql = $ep->getParam('sql');
            $id  = rex_request::request('id', 'int');

            if ($id == 0 && $sql->key() == 0) {
                $sql  = null;
                $html = $ep->getSubject();

                if (strpos($html, '<tbody>')) {
                    $html = str_replace('<tbody>', '<tbody>{{INPUT_HTML}}', $html);
                } else {
                    // for the case where no entry exists and therefor no tbody tag is generated
                    $html = substr($html, 0, strrpos($html, '<tr')) . '{{INPUT_HTML}}';
                }
            } elseif ($id > 0 && $sql->getValue('id') == $id) {
                $html = $ep->getSubject();
                $html = substr($html, 0, strrpos($html, '<tr')) . '{{INPUT_HTML}}';
            }

            if ($html) {
                $fragment = new \rex_fragment();
                $fragment->setVar('sql', $sql);
                $fragment->setVar('list', $list);
                $inputHtml = $fragment->parse('mail_sprog/backend/input_tabs.php');
                $ep->setSubject(str_replace('{{INPUT_HTML}}', $inputHtml, $html));
            }
        }
    }
}