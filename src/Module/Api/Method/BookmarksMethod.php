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
 *
 */

declare(strict_types=0);

namespace Ampache\Module\Api\Method;

use Ampache\Repository\Model\User;
use Ampache\Module\Api\Api;
use Ampache\Module\Api\Json_Data;
use Ampache\Module\Api\Xml_Data;
use Ampache\Repository\BookmarkRepositoryInterface;

/**
 * Class BookmarksMethod
 * @package Lib\ApiMethods
 */
final class BookmarksMethod
{
    public const ACTION = 'bookmarks';

    /**
     * bookmarks
     * MINIMUM_API_VERSION=5.0.0
     *
     * Get information about bookmarked media this user is allowed to manage.
     *
     * @param array $input
     * @param User|null $user
     * @return boolean
     */
    public static function bookmarks(array $input, ?User $user): bool
    {
        $results = static::getBookmarkRepository()->getBookmarks($user->getId());
        if (empty($results)) {
            Api::empty('bookmark', $input['api_format']);

            return false;
        }

        ob_end_clean();
        switch ($input['api_format']) {
            case 'json':
                echo Json_Data::bookmarks($results);
                break;
            default:
                echo Xml_Data::bookmarks($results);
        }

        return true;
    }

    private static function getBookmarkRepository(): BookmarkRepositoryInterface
    {
        global $dic;

        return $dic->get(BookmarkRepositoryInterface::class);
    }
}
