<?php

/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 *  LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
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

namespace Ampache\Module\Api\Method\Api5;

use Ampache\Repository\Model\User;
use Ampache\Module\Api\Api5;
use Ampache\Module\Api\Json5_Data;
use Ampache\Module\Api\Xml5_Data;
use Ampache\Module\Playback\Stream_Url;
use Ampache\Module\System\Session;

/**
 * Class UrlToSong5Method
 */
final class UrlToSong5Method
{
    public const ACTION = 'url_to_song';

    /**
     * url_to_song
     * MINIMUM_API_VERSION=380001
     *
     * This takes a url and returns the song object in question
     *
     * @param array $input
     * url = (string) $url
     * @return boolean
     */
    public static function url_to_song(array $input): bool
    {
        if (!Api5::check_parameter($input, array('url'), self::ACTION)) {
            return false;
        }
        // Don't scrub, the function needs her raw and juicy
        $url_data = Stream_Url::parse($input['url']);
        if (array_key_exists('id', $url_data)) {
            Api5::error(T_('Bad Request'), '4710', self::ACTION, 'url', $input['api_format']);

            return false;
        }
        $user = User::get_from_username(Session::username($input['auth']));
        ob_end_clean();
        switch ($input['api_format']) {
            case 'json':
                echo Json5_Data::songs(array($url_data['id']), $user, true, false);
                break;
            default:
                echo Xml5_Data::songs(array($url_data['id']), $user);
        }

        return true;
    }
}
