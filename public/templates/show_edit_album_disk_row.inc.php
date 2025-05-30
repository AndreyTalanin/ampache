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

use Ampache\Module\Authorization\AccessLevelEnum;
use Ampache\Module\Authorization\AccessTypeEnum;
use Ampache\Module\System\Core;
use Ampache\Repository\Model\AlbumDisk;
use Ampache\Repository\Model\Artist;
use Ampache\Repository\Model\Tag;
use Ampache\Module\Authorization\Access;
use Ampache\Module\Api\Ajax;
use Ampache\Repository\Model\User;

/** @var AlbumDisk $libitem */

$current_user = Core::get_global('user');
$has_access   = $current_user instanceof User && Access::check(AccessTypeEnum::INTERFACE, AccessLevelEnum::CONTENT_MANAGER, $current_user->getId());
$is_owner     = $current_user instanceof User && $current_user->getId() == $libitem->get_user_owner(); ?>
<div>
    <form method="post" id="edit_album_disk_<?php echo $libitem->id; ?>" class="edit_dialog_content">
        <table class="tabledata">
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Name'); ?></td>
                <td><input type="text" name="name" value="<?php echo scrub_out($libitem->get_fullname(true)); ?>" autofocus /></td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Album Artist'); ?></td>
                <td>
                    <?php
                        if ($has_access) {
                            show_artist_select('album_artist', (int)$libitem->album_artist, true, $libitem->id, true); ?>
                    <div id="album_artist_select_album_<?php echo $libitem->id; ?>">
                        <?php echo Ajax::observe('album_artist_select_' . $libitem->id, 'change', 'check_inline_song_edit("album_artist", ' . $libitem->id . ')'); ?>
                    </div>
                    <?php
                        } else {
                            echo '<input type="hidden" name="album_artist" value="' . (int)$libitem->album_artist . '"/>' . scrub_out($libitem->get_artist_fullname());
                        } ?>
                </td>
            </tr>
            <?php if (!empty($libitem->get_artists()) && count($libitem->get_artists()) > 1) { ?>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Additional Artists'); ?></td>
                <td><?php echo Artist::get_display(array_diff($libitem->get_artists(), [$libitem->album_artist])); ?></td>
            </tr>
            <?php } ?>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Year'); ?></td>
                <td><input type="text" name="year" value="<?php echo scrub_out((string)$libitem->year); ?>" /></td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('MusicBrainz ID'); ?></td>
                <td>
                    <?php if ($has_access || $is_owner) { ?>
                        <input type="text" name="mbid" value="<?php echo $libitem->mbid; ?>" />
                    <?php
                    } else {
                        echo '<input type="hidden" name="mbid" value="' . $libitem->mbid . '"/>' . $libitem->mbid;
                    } ?>
                </td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('MusicBrainz Release Group ID'); ?></td>
                <td>
                <?php if ($has_access || $is_owner) { ?>
                    <input type="text" name="mbid_group" value="<?php echo $libitem->mbid_group; ?>" />
                <?php
                } else {
                    echo '<input type="hidden" name="mbid_group" value="' . $libitem->mbid_group . '"/>' . $libitem->mbid_group;
                } ?>
                </td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Release Type'); ?></td>
                <td><input type="text" name="release_type" value="<?php echo $libitem->release_type; ?>" /></td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Release Status'); ?></td>
                <td><input type="text" name="release_status" value="<?php echo $libitem->release_status; ?>" /></td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Release Comment'); ?></td>
                <td><input type="text" name="version" value="<?php echo $libitem->version; ?>" /></td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Disk'); ?></td>
                <td><input type="text" name="disk" value="<?php echo $libitem->disk; ?>" /></td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Disk Subtitle'); ?></td>
                <td><input type="text" name="disksubtitle" value="<?php echo $libitem->disksubtitle; ?>" /></td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Catalog Number'); ?></td>
                <td><input type="text" name="catalog_number" value="<?php echo $libitem->catalog_number; ?>" /></td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Barcode'); ?></td>
                <td><input type="text" name="barcode" value="<?php echo $libitem->barcode; ?>" /></td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Original Year'); ?></td>
                <td><input type="text" name="original_year" value="<?php echo $libitem->original_year; ?>" /></td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Genres'); ?></td>
                <td><input type="text" name="edit_tags" id="edit_tags" value="<?php echo Tag::get_display(Tag::get_top_tags('album', $libitem->id, 0)); ?>" /></td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"></td>
                <td><input type="checkbox" name="overwrite_childs" value="checked" />&nbsp;<?php echo T_('Overwrite tags of sub songs'); ?></td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"></td>
                <td><input type="checkbox" name="add_to_childs" value="checked" />&nbsp;<?php echo T_('Add tags to sub songs'); ?></td>
            </tr>
        </table>
        <input type="hidden" name="id" value="<?php echo $libitem->id; ?>" />
        <input type="hidden" name="catalog" value="<?php echo $libitem->catalog; ?>" />
        <input type="hidden" name="type" value="album_disk_row" />
    </form>
</div>
