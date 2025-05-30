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
use Ampache\Module\Api\Ajax;
use Ampache\Module\Authorization\Access;
use Ampache\Module\Authorization\AccessLevelEnum;
use Ampache\Module\Authorization\AccessTypeEnum;
use Ampache\Module\Metadata\MetadataManagerInterface;
use Ampache\Module\System\Core;
use Ampache\Repository\Model\Artist;
use Ampache\Repository\Model\Metadata;
use Ampache\Repository\Model\Song;
use Ampache\Repository\Model\Tag;
use Ampache\Repository\Model\User;

global $dic;
$metadataManager = $dic->get(MetadataManagerInterface::class);

/** @var Song $libitem */

$current_user = Core::get_global('user');
$has_access   = $current_user instanceof User && Access::check(AccessTypeEnum::INTERFACE, AccessLevelEnum::MANAGER, $current_user->getId());
$is_owner     = $current_user instanceof User && $current_user->getId() == $libitem->get_user_owner(); ?>
<div>
    <form method="post" id="edit_song_<?php echo $libitem->id; ?>" class="edit_dialog_content">
        <table class="tabledata">
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Title'); ?></td>
                <td><input type="text" name="title" value="<?php echo scrub_out($libitem->title); ?>" autofocus /></td>
            </tr>
            <?php
                if ($has_access || $is_owner) { ?>
                <tr>
                    <td class="edit_dialog_content_header"><?php echo T_('Artist'); ?></td>
                    <td>
                        <?php show_artist_select('artist', (int) $libitem->artist, true, $libitem->id); ?>
                        <div id="artist_select_song_<?php echo $libitem->id; ?>">
                            <?php echo Ajax::observe('artist_select_' . $libitem->id, 'change', 'check_inline_song_edit("artist", ' . $libitem->id . ')'); ?>
                        </div>
                    </td>
                </tr>
                    <?php if (count($libitem->get_artists()) > 1) { ?>
                        <tr>
                            <td class="edit_dialog_content_header"><?php echo T_('Additional Artists'); ?></td>
                            <td><?php echo Artist::get_display(array_diff($libitem->get_artists(), [$libitem->artist])); ?></td>
                        </tr>
                    <?php } ?>
                <tr>
                    <td class="edit_dialog_content_header"><?php echo T_('Album'); ?></td>
                    <td>
                        <?php show_album_select('album', $libitem->album, true, $libitem->id); ?>
                        <div id="album_select_song_<?php echo $libitem->id; ?>">
                            <?php echo Ajax::observe('album_select_' . $libitem->id, 'change', 'check_inline_song_edit("album", ' . $libitem->id . ')'); ?>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Disk'); ?></td>
                <td><input type="text" name="disk" value="<?php echo scrub_out((string)$libitem->disk); ?>" /></td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Track'); ?></td>
                <td><input type="text" name="track" value="<?php echo scrub_out((string)$libitem->track); ?>" /></td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('MusicBrainz ID'); ?></td>
                <td>
                    <?php
                        if (Access::check(AccessTypeEnum::INTERFACE, AccessLevelEnum::CONTENT_MANAGER)) { ?>
                            <input type="text" name="mbid" value="<?php echo $libitem->mbid; ?>" />
                        <?php
                        } else {
                            echo '<input type="hidden" name="mbid" value="' . $libitem->mbid . '"/>' . $libitem->mbid;
                        } ?>
                </td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Composer'); ?></td>
                <td><input type="text" name="composer" value="<?php echo scrub_out($libitem->composer); ?>" /></td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Comment'); ?></td>
                <td><input type="text" name="comment" value="<?php echo scrub_out($libitem->comment); ?>" /></td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Label'); ?></td>
                <td><input type="text" name="label" value="<?php echo scrub_out($libitem->label); ?>" /></td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Year'); ?></td>
                <td><input type="text" name="year" value="<?php echo scrub_out((string)$libitem->year); ?>" /></td>
            </tr>
            <tr>
                <td class="edit_dialog_content_header"><?php echo T_('Genres'); ?></td>
                <td><input type="text" name="edit_tags" id="edit_tags" value="<?php echo Tag::get_display(Tag::get_top_tags('song', $libitem->id, 0)); ?>" /></td>
            </tr>
            <?php
                if (AmpConfig::get('licensing')) { ?>
                    <tr>
                        <td class="edit_dialog_content_header"><?php echo T_('Music License'); ?></td>
                        <td>
                            <?php show_license_select('license', (int)$libitem->license, $libitem->id); ?>
                            <div id="album_select_license_<?php echo $libitem->license; ?>">
                                <?php echo Ajax::observe('license_select_' . $libitem->license, 'change', 'check_inline_song_edit("license", ' . $libitem->id . ')'); ?>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
        </table>
        <?php if ($metadataManager->isCustomMetadataEnabled()) { ?>
            <button class="metadataAccordionButton"><?php echo T_('More Metadata'); ?></button>
                    <div class="metadataAccordion">
                    <table class="tabledata">
                        <?php
                        $dismetas = $metadataManager->getDisabledMetadataFields();
            foreach ($metadataManager->getMetadata($libitem) as $metadata) {
                /** @var Metadata $metadata */
                $field = $metadata->getField();
                if (
                    $field !== null &&
                    $field->isPublic() &&
                    !in_array($field->getName(), $dismetas)
                ) {
                    echo '<tr>' .
                    '<td class="edit_dialog_content_header">' . ucwords(str_replace("_", " ", $field->getName())) . '</td>' .
                    '<td><input type="text" name="metadata[' . $metadata->getId() . ']" value="' . $metadata->getData() . '"/></td>' .
                    '</tr>';
                }
            } ?>
                    </table>
                </div>
            <?php } ?>
        <input type="hidden" name="id" value="<?php echo $libitem->id; ?>" />
        <input type="hidden" name="type" value="song_row" />
    </form>

    <script>
        $('.metadataAccordionButton').button().click(function() {
            $('.metadataAccordion').toggle();
            $(this).text($(this).text() == 'More Metadata' ? 'Less Metadata' : 'More Metadata');

            return false;
        });
    </script>
</div>
