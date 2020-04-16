<?php
/**
 * @package       mod_qltags
 * @copyright     Copyright (C) 2020 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/** @var string $strFieldName */

if (0 == $params->get('displayLabels', 0)) {
    return;
}
echo $strFieldName . ': ';
