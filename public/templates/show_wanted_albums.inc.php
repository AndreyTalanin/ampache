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

use Ampache\Repository\Model\Browse;
use Ampache\Module\Api\Ajax;
use Ampache\Module\Util\Ui;
use Ampache\Repository\WantedRepositoryInterface;

global $dic;
$wantedRepository = $dic->get(WantedRepositoryInterface::class);

/** @var Browse $browse */
/** @var list<int> $object_ids */
?>
<table class="tabledata striped-rows <?php echo $browse->get_css_class(); ?>" data-objecttype="wanted">
    <thead>
        <tr class="th-top">
            <th class="cel_album essential"><?php echo Ajax::text('?page=browse&action=set_sort&browse_id=' . $browse->id . '&type=wanted&sort=name', T_('Album'), 'wanted_sort_album'); ?></th>
            <th class="cel_artist essential"><?php echo Ajax::text('?page=browse&action=set_sort&browse_id=' . $browse->id . '&type=wanted&sort=artist', T_('Artist'), 'wanted_sort_artist'); ?></th>
            <th class="cel_year optional"><?php echo Ajax::text('?page=browse&action=set_sort&browse_id=' . $browse->id . '&type=wanted&sort=year', T_('Year'), 'wanted_sort_year'); ?></th>
            <th class="cel_user optional"><?php echo Ajax::text('?page=browse&action=set_sort&browse_id=' . $browse->id . '&type=wanted&sort=user', T_('User'), 'wanted_sort_user'); ?></th>
            <th class="cel_action essential"><?php echo T_('Actions'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($object_ids as $wanted_id) {
            $libitem = $wantedRepository->findById($wanted_id);
            if ($libitem === null) {
                continue;
            } ?>
        <tr id="walbum_<?php echo $libitem->getMusicBrainzId(); ?>">
            <?php require Ui::find_template('show_wanted_album_row.inc.php'); ?>
        </tr>
        <?php
        } ?>
    </tbody>
</table>
<?php Ui::show_box_bottom(); ?>
