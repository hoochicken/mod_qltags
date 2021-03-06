<?php
/**
 * @package       mod_qltags
 * @copyright     Copyright (C) 2020 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;


/** @var string $strFieldName */
?>
<div class="<?php echo $strFieldName;?> ">
    <?php
    require JModuleHelper::getLayoutPath('mod_qltags', 'default_label');
    echo $item->$strFieldName;
    ?>
</div>
