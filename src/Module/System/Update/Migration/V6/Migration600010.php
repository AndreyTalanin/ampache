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

namespace Ampache\Module\System\Update\Migration\V6;

use Ampache\Module\System\Update\Migration\AbstractMigration;

/**
 * Rename `user_data` album keys
 */
final class Migration600010 extends AbstractMigration
{
    protected array $changelog = ['Rename `user_data` album keys'];

    public function migrate(): void
    {
        // album was the ungrouped disks so rename those first
        $this->updateDatabase("UPDATE IGNORE `user_data` SET `key` = 'album_disk' WHERE `key` = 'album';");

        // album_group is now the default state
        $this->updateDatabase("UPDATE IGNORE `user_data` SET `key` = 'album' WHERE `key` = 'album_group';");

        $this->updateDatabase("DELETE FROM `user_data` WHERE `key` = 'album_group';");
    }
}
