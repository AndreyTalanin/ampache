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
 *
 */

namespace Ampache\Module\Util\Rss\Type;

use Ampache\Module\Statistics\Stats;
use Ampache\Repository\Model\Album;
use Ampache\Repository\Model\Art;
use Ampache\Repository\Model\User;
use Generator;

final readonly class LatestAlbumFeed extends AbstractGenericRssFeed
{
    public function __construct(
        private ?User $user,
    ) {
    }

    protected function getTitle(): string
    {
        return T_('Newest Albums');
    }

    protected function getItems(): Generator
    {
        $ids = Stats::get_newest('album', 10, 0, 0, $this->user);

        foreach ($ids as $albumid) {
            $album = new Album($albumid);

            yield [
                'title' => $album->get_fullname(),
                'link' => $album->get_link(),
                'description' => $album->get_artist_fullname() . ' - ' . $album->get_fullname(true),
                'comments' => '',
                'pubDate' => date("c", $album->addition_time),
                'guid' => (isset($album->mbid)) ? 'https://musicbrainz.org/release/' . $album->mbid : (isset($album->mbid_group) ? 'https://musicbrainz.org/release-group/' . $album->mbid_group : 'album-' . $album->id),
                'isPermaLink' => (isset($album->mbid) || isset($album->mbid_group))
                    ? 'true'
                    : 'false',
                'image' => (string)Art::url($album->id, 'album', null, 2),
            ];
        }
    }
}
