<?php
/**
 * @package       mod_qltags
 * @copyright     Copyright (C) 2020 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/** @var stdClass $objJson */
/** @var string $strFieldName */

$strTitle = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8');
?>
<div class="title">
    <a href="<?php echo $item->link;?>">
        <?php
        require JModuleHelper::getLayoutPath('mod_qltags', 'default_label');
        echo $strTitle;
        ?>
    </a>
    <?php if (1 == $params->get('countInTitle', 0) && isset($item->count)) :?>
        <span class="tag-count badge badge-info"><?php echo $item->count; ?></span>
    <?php endif; ?>
</div>

