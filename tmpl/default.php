<?php
/**
 * @package       mod_qltags
 * @copyright     Copyright (C) 2020 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
//echo '<pre>';print_r($list);

$arrImages = ['image_intro', 'image_fulltext'];
?>
<div id="module<?php $module->id; ?>" class="qltags<?php echo $moduleclass_sfx; ?>">
<?php if (!count($list)) : ?>
	<div class="alert alert-no-items"><?php echo JText::_('MOD_QLTAGS_NO_ITEMS_FOUND'); ?></div>
<?php else : ?>
    <div class="items">
        <?php foreach ($list as $item) : ?>
        <div class="<?php echo $params->get('itemClass', ''); ?>">
            <?php for($i=1; $i <= 11; $i++) {
                $strParamName = 'tag_position' . $i;
                $strFieldName = $params->get($strParamName, 0);
                $strOutput = '';

                // if there is no content given, ignore loop
                if (('0' === $strFieldName || !isset($item->$strFieldName)) && !in_array($strFieldName, $arrImages)) {
                    continue;
                }

                // images
                if (in_array($strFieldName, $arrImages)) {
                    $strJsonImages = $item->images;
                    $objJson = json_decode($strJsonImages);

                    // if no image is given => ignore
                    if (!isset($objJson->$strFieldName)) {
                        continue;
                    }
                    require JModuleHelper::getLayoutPath('mod_qltags', 'default_image');
                }

                // title
                elseif ('title' === $strFieldName) {
                    require JModuleHelper::getLayoutPath('mod_qltags', 'default_title');
                }
                // hits
                elseif ('count' === $strFieldName && 1 != $params->get('countInTitle', 0)) {
                    require JModuleHelper::getLayoutPath('mod_qltags', 'default_count');
                }
                // default layout
                else {
                    require JModuleHelper::getLayoutPath('mod_qltags', 'default_default');
                }

            };?>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
</div>
