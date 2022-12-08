<?php
/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 * LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
 * Copyright 2001 - 2022 Ampache.org
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

declare(strict_types=1);

namespace Ampache\Repository;

use Ampache\Config\AmpConfig;
use Ampache\Module\System\Dba;
use Ampache\Repository\Model\Catalog;

final class LiveStreamRepository implements LiveStreamRepositoryInterface
{
    /**
     * @return int[]
     */
    public function getAll(): array
    {
        $sql        = "SELECT `live_stream`.`id` FROM `live_stream` INNER JOIN `catalog_map` ON `catalog_map`.`catalog_id` = `live_stream`.`catalog` AND `catalog_map`.`catalog_id` IN (" . implode(',', Catalog::get_catalogs()) . ");";
        $db_results = Dba::read($sql);
        $radios     = [];

        while ($results = Dba::fetch_assoc($db_results)) {
            $radios[] = (int) $results['id'];
        }

        return $radios;
    }

    /**
     * This deletes the object with the given id from the database
     */
    public function delete(int $liveStreamId): void
    {
        Dba::write(
            'DELETE FROM `live_stream` WHERE `id` = ?',
            [$liveStreamId]
        );
        Catalog::count_table('live_stream');
    }
}
