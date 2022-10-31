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

use Ampache\Config\AmpConfig;
use Ampache\Repository\Model\Podcast;
use Ampache\Module\Api\Api5;
use Ampache\Module\Api\Json5_Data;
use Ampache\Module\Api\Xml5_Data;
use Ampache\Module\System\Session;
use Ampache\Repository\Model\User;

/**
 * Class Podcast5Method
 */
final class Podcast5Method
{
    public const ACTION = 'podcast';

    /**
     * podcast
     * MINIMUM_API_VERSION=420000
     *
     * Get the podcast from it's id.
     *
     * @param array $input
     * filter  = (integer) Podcast ID number
     * include = (string) 'episodes' (include episodes in the response) //optional
     * @return boolean
     */
    public static function podcast(array $input): bool
    {
        if (!AmpConfig::get('podcast')) {
            Api5::error(T_('Enable: podcast'), '4703', self::ACTION, 'system', $input['api_format']);

            return false;
        }
        if (!Api5::check_parameter($input, array('filter'), self::ACTION)) {
            return false;
        }
        $object_id = (int) $input['filter'];
        $include   = $input['include'] ?? '';
        $podcast   = new Podcast($object_id);

        if (!$podcast->id) {
            /* HINT: Requested object string/id/type ("album", "myusername", "some song title", 1298376) */
            Api5::error(sprintf(T_('Not Found: %s'), $object_id), '4704', self::ACTION, 'filter', $input['api_format']);

            return false;
        }

        $user = User::get_from_username(Session::username($input['auth']));
        ob_end_clean();
        $episodes = ($include == 'episodes' || (int)$include == 1);
        switch ($input['api_format']) {
            case 'json':
                echo Json5_Data::podcasts(array($object_id), $user->id, $episodes, false);
                break;
            default:
                echo Xml5_Data::podcasts(array($object_id), $user->id, $episodes);
        }

        return true;
    }
}
