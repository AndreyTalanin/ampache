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

use Ampache\Module\System\Update\Migration\AbstractMigration;
use Ampache\Repository\Model\Preference;

/**
 * Add 'show_playlist_media_album', 'show_playlist_media_artist' preferences, remove 'show_playlist_media_parent' as deprecated.
 * Warning: This is an exclusive migration of the 'AndreyTalanin/ampache' repository. It is not present in the upstream repository.
 */
final class Migration760002 extends AbstractMigration
{
    protected array $changelog = ['Add `show_playlist_media_album`, `show_playlist_media_artist` preferences, remove `show_playlist_media_parent` as deprecated. (Warning: Fork-exclusive migration.)'];

    public function migrate(): void
    {
        $this->updatePreferences('show_playlist_media_album', 'Show Album column on playlist media rows', '0', 25, 'boolean', 'playlist');

        $this->updatePreferences('show_playlist_media_artist', 'Show Artist column on playlist media rows', '0', 25, 'boolean', 'playlist');

        Preference::delete('show_playlist_media_parent');
    }
}
