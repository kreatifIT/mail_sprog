<?php

/**
 * This file is part of the Kreatif\Project package.
 *
 * @author Kreatif GmbH
 * @author a.platter@kreatif.it
 * Date: 26.04.20
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$addon = \rex_addon::get('mail_sprog');

echo rex_view::title($addon->getProperty('page')['title']);
rex_be_controller::includeCurrentPageSubPath();
