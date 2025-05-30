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

namespace Ampache\Module\Database;

use Ampache\Module\Database\Exception\DatabaseException;
use Ampache\Module\Database\Exception\InsertIdInvalidException;
use PDOStatement;

interface DatabaseConnectionInterface
{
    /**
     * Executes the provided sql query
     *
     * If the query fails, a DatabaseException will be thrown
     *
     * @param list<mixed> $params
     *
     * @throws DatabaseException
     */
    public function query(
        string $sql,
        array $params = [],
        bool $silent = false,
    ): PDOStatement;

    /**
     * Fetches a single column from the query result
     *
     * Useful e.g. for counting-queries
     *
     * @param list<mixed> $params
     *
     * @return mixed Will return `false` on empty row
     */
    public function fetchOne(
        string $sql,
        array $params = []
    );

    /**
     * Fetches a single whole row and returns it as an associative array
     *
     * @param list<mixed> $params
     *
     * @return false|array<string, mixed> Will return `false` if row is empty
     * @throws InsertIdInvalidException
     */
    public function fetchRow(
        string $sql,
        array $params = []
    );

    /**
     * Returns the most recent inserted id
     *
     * @return non-negative-int
     */
    public function getLastInsertedId(): int;
}
