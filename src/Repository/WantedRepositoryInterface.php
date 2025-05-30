<?php

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

namespace Ampache\Repository;

use Ampache\Repository\Model\User;
use Ampache\Repository\Model\Wanted;

/**
 * @phpstan-type DatabaseRow array{
 *     id: int,
 *     user: int,
 *     artist: ?int,
 *     artist_mbid: ?string,
 *     mbid: ?string,
 *     name: ?string,
 *     year: ?int,
 *     date: int,
 *     accepted: int
 * }
 */
interface WantedRepositoryInterface
{
    /**
     * Get wanted list.
     *
     * @return list<int>
     */
    public function findAll(?User $user = null): array;

    /**
     * Check if a release mbid is already marked as wanted
     */
    public function find(string $musicbrainzId, User $user): ?int;

    /**
     * Delete wanted release.
     */
    public function deleteByMusicbrainzId(
        string $musicbrainzId,
        ?User $user = null
    ): void;

    /**
     * Get accepted wanted release count.
     */
    public function getAcceptedCount(): int;

    /**
     * retrieves the info from the database and puts it in the cache
     *
     * @return null|DatabaseRow
     */
    public function getById(int $wantedId): ?array;

    /**
     * Find a single item by its id
     */
    public function findById(int $itemId): ?Wanted;

    /**
     * Find wanted release by name.
     */
    public function findByName(string $name): ?Wanted;

    /**
     * Find wanted release by mbid.
     */
    public function findByMusicBrainzId(string $mbid): ?Wanted;

    public function prototype(): Wanted;

    /**
     * This cleans out unused wanted items
     */
    public function collectGarbage(): void;

    /**
     * Migrate an object associate stats to a new object
     */
    public function migrateArtist(int $oldObjectId, int $newObjectId): void;
}
