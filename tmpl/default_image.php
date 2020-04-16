<?php
/**
 * @package       mod_qltags
 * @copyright     Copyright (C) 2020 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/** @var stdClass $params */
/** @var stdClass $objJson */
/** @var string $strFieldName */
$strCaption = !empty($objJson->image_intro_caption) ? $objJson->{$strFieldName . '_caption'} : $item->title;
$strSource = $objJson->$strFieldName;
$strAlt = $objJson->{$strFieldName . '_alt'};
?>
<?php if (1 == $params->get('linkImages')) : ?>
    <a href="<?php echo $item->link;?>">
<?php endif; ?>
    <img src="<?php echo $strSource; ?>" alt="<?php echo $strAlt;?>" title="<?php echo $strCaption; ?>" />
<?php if (1 == $params->get('linkImages')) : ?>
</a>
<?php endif; ?>