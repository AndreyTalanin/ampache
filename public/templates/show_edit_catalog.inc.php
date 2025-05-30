<?php

declare(strict_types=0);

/**
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 * LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
 * Copyright Ampache.org, 2001-2024
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

use Ampache\Config\AmpConfig;
use Ampache\Module\Util\Ui;

/** @var Ampache\Repository\Model\Catalog $catalog */

/* HINT: Catalog Name */
Ui::show_box_top(sprintf(T_('Settings for Catalog: %s'), scrub_out($catalog->name) . ' (' . $catalog->f_info . ')'), 'box box_edit_catalog'); ?>
<form method="post" action="<?php echo AmpConfig::get_web_path('/admin'); ?>/catalog.php" enctype="multipart/form-data">
    <table class="tabledata">
        <tr>
            <td><?php echo T_('Name'); ?></td>
            <td><input type="text" name="name" value="<?php echo scrub_out($catalog->name); ?>" autofocus></input></td>
            <td style="vertical-align:top; font-family: monospace;" rowspan="5">
                <strong><?php echo T_('Auto-inserted Fields'); ?></strong><br />
                <span class="format-specifier">%A</span> = <?php echo T_('Album'); ?><br />
                <span class="format-specifier">%B</span> = <?php echo T_('Album Artist'); ?><br />
                <span class="format-specifier">%a</span> = <?php echo T_('Song Artist'); ?><br />
                <span class="format-specifier">%m</span> = <?php echo T_('Artist'); ?><br />
                <span class="format-specifier">%t</span> = <?php echo T_('Song Title'); ?><br />
                <span class="format-specifier">%T</span> = <?php echo T_('Track (0 padded)'); ?><br />
                <span class="format-specifier">%d</span> = <?php echo T_('Disk'); ?><br />
                <span class="format-specifier">%g</span> = <?php echo T_('Genre'); ?><br />
                <span class="format-specifier">%y</span> = <?php echo T_('Year'); ?><br />
                <span class="format-specifier">%Y</span> = <?php echo T_('Original Year'); ?><br />
                <span class="format-specifier">%c</span> = <?php echo T_('Comment'); ?><br />
                <span class="format-specifier">%l</span> = <?php echo T_('Label'); ?><br />
                <span class="format-specifier">%r</span> = <?php echo T_('Release Type'); ?><br />
                <span class="format-specifier">%R</span> = <?php echo T_('Release Status'); ?><br />
                <span class="format-specifier">%s</span> = <?php echo T_('Release Comment'); ?><br />
                <span class="format-specifier">%C</span> = <?php echo T_('Catalog Number'); ?><br />
                <span class="format-specifier">%b</span> = <?php echo T_('Barcode'); ?><br />
                <span class="format-specifier">%o</span> = <?php echo T_('Ignore'); ?><br />
            </td>
        </tr>
        <tr>
            <td><?php echo T_('Catalog Type'); ?></td>
            <td><?php echo scrub_out(ucfirst((string)$catalog->catalog_type)); ?></td>
        </tr>
        <tr>
            <td><?php echo T_('Catalog Media Type'); ?></td>
            <td><?php echo scrub_out(ucfirst((string) $catalog->gather_types)); ?></td>
        </tr>
        <tr>
            <td><?php echo T_('Filename Pattern'); ?></td>
            <td><input type="text" name="rename_pattern" value="<?php echo scrub_out($catalog->rename_pattern); ?>" /></td>
        </tr>
        <tr>
            <td><?php echo T_('Folder Pattern'); ?>:<br /><?php echo T_("(no leading or ending '/')"); ?></td>
            <td><input type="text" name="sort_pattern" value="<?php echo scrub_out($catalog->sort_pattern); ?>" /></td>
        </tr>
    </table>
    <div class="formValidation">
        <input type="hidden" name="catalog_id" value="<?php echo scrub_out((string)$catalog->id); ?>" />
        <input type="hidden" name="action" value="update_catalog_settings" />
        <input class="button" type="submit" value="<?php echo T_('Save Catalog Settings'); ?>" />
    </div>
</form>
<?php Ui::show_box_bottom(); ?>
