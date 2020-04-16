<?php
/**
 * @package       mod_qltags
 * @copyright     Copyright (C) 2020 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the qltags functions only once
JLoader::register('ModQltagsHelper', __DIR__ . '/helper.php');
JLoader::register('TagsHelperRoute', JPATH_BASE . '/components/com_tags/helpers/route.php');

$cacheparams = new stdClass;
$cacheparams->cachemode = 'safeuri';
$cacheparams->class = 'ModQltagsHelper';
$cacheparams->method = 'getList';
$cacheparams->methodparams = $params;
$cacheparams->modeparams = array('id' => 'array', 'Itemid' => 'int');

$list = JModuleHelper::moduleCache($module, $params, $cacheparams);

if (!count($list) && !$params->get('no_results_text')) {
	return;
}

JHtml::stylesheet('mod_qltags/qltags.css', false, true, false);

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');
$display_count   = $params->get('display_count', 0);

require JModuleHelper::getLayoutPath('mod_qltags', $params->get('layout', 'default'));
