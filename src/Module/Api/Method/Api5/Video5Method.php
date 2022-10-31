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
use Ampache\Repository\Model\User;
use Ampache\Repository\Model\Video;
use Ampache\Module\Api\Api5;
use Ampache\Module\Api\Json5_Data;
use Ampache\Module\Api\Xml5_Data;
use Ampache\Module\System\Session;

/**
 * Class Video5Method
 */
final class Video5Method
{
    public const ACTION = 'video';

    /**
     * video
     * This returns a single video
     *
     * @param array $input
     * filter = (string) UID of video
     * @return boolean
     */
    public static function video(array $input): bool
    {
        if (!AmpConfig::get('allow_video')) {
            Api5::error(T_('Enable: video'), '4703', self::ACTION, 'system', $input['api_format']);

            return false;
        }
        if (!Api5::check_parameter($input, array('filter'), self::ACTION)) {
            return false;
        }
        $object_id = (int) $input['filter'];
        $video     = new Video($object_id);
        if (!$video->id) {
            /* HINT: Requested object string/id/type ("album", "myusername", "some song title", 1298376) */
            Api5::error(sprintf(T_('Not Found: %s'), $object_id), '4704', self::ACTION, 'song', $input['api_format']);

            return false;
        }

        $user = User::get_from_username(Session::username($input['auth']));
        switch ($input['api_format']) {
            case 'json':
                echo Json5_Data::videos(array($object_id), $user->id, false);
                break;
            default:
                echo Xml5_Data::videos(array($object_id), $user->id);
        }

        return true;
    }
}
