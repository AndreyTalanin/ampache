<?php

declare(strict_types=1);

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
 */

namespace Ampache\Module\System\Update\Migration\V7;

use Ampache\Module\System\Dba;
use Ampache\Module\System\Update\Migration\AbstractMigration;

/**
 * Add a last_count to playlists to speed up access requests
 */
final class Migration700005 extends AbstractMigration
{
    protected array $changelog = ['Add a last_count to playlist table to speed up access requests'];

    public function migrate(): void
    {
        if (!Dba::read('SELECT `last_count` FROM `playlist` LIMIT 1;', [], true)) {
            $this->updateDatabase("ALTER TABLE `playlist` ADD COLUMN `last_count` INT(11) NULL;");
        }

        $sql        = "SELECT `playlist`.`id`, COUNT(`playlist_data`.`id`) AS `count` FROM `playlist` LEFT JOIN `playlist_data` ON `playlist_data`.`playlist` = `playlist`.`id` GROUP BY `playlist`.`id`;";
        $db_results = Dba::read($sql, [], true);
        $playlists  = [];
        while ($results = Dba::fetch_assoc($db_results)) {
            $playlists[(int)$results['id']] = (int)$results['count'];
        }

        foreach ($playlists as $playlist_id => $last_count) {
            Dba::write("UPDATE `playlist` SET `last_count` = ? WHERE `id` = ?", [$playlist_id, $last_count], true);
        }
    }
}
