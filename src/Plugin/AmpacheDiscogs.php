<?php

declare(strict_types=0);

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

namespace Ampache\Plugin;

use Ampache\Module\Authorization\AccessLevelEnum;
use Ampache\Repository\Model\Art;
use Ampache\Repository\Model\Preference;
use Ampache\Repository\Model\User;
use Exception;
use WpOrg\Requests\Requests;

class AmpacheDiscogs extends AmpachePlugin implements PluginGatherArtsInterface
{
    public string $name        = 'Discogs';

    public string $categories  = 'metadata';

    public string $description = 'Discogs metadata integration';

    public string $url         = 'http://www.discogs.com';

    public string $version     = '000001';

    public string $min_ampache = '370021';

    public string $max_ampache = '999999';

    // These are internal settings used by this class, run this->load to fill them out
    private string $api_key;

    private string $secret;

    /**
     * Constructor
     * This function does nothing
     */
    public function __construct()
    {
        $this->description = T_('Discogs metadata integration');
    }

    /**
     * install
     * This is a required plugin function
     */
    public function install(): bool
    {
        if (!Preference::insert('discogs_api_key', T_('Discogs consumer key'), '', AccessLevelEnum::MANAGER->value, 'string', 'plugins', $this->name)) {
            return false;
        }

        return Preference::insert('discogs_secret_api_key', T_('Discogs secret'), '', AccessLevelEnum::MANAGER->value, 'string', 'plugins', $this->name);
    }

    /**
     * uninstall
     * This is a required plugin function
     */
    public function uninstall(): bool
    {
        return (
            Preference::delete('discogs_api_key') &&
            Preference::delete('discogs_secret_api_key')
        );
    }

    /**
     * upgrade
     * This is a recommended plugin function
     */
    public function upgrade(): bool
    {
        return true;
    }

    /**
     * load
     * This is a required plugin function; here it populates the prefs we
     * need for this object.
     */
    public function load(User $user): bool
    {
        $user->set_preferences();
        $data = $user->prefs;
        // load system when nothing is given
        if (!strlen(trim((string) $data['discogs_api_key'])) || !strlen(trim((string) $data['discogs_secret_api_key']))) {
            $data                           = [];
            $data['discogs_api_key']        = Preference::get_by_user(-1, 'discogs_api_key');
            $data['discogs_secret_api_key'] = Preference::get_by_user(-1, 'discogs_secret_api_key');
        }

        if (strlen(trim((string) $data['discogs_api_key'])) !== 0) {
            $this->api_key = trim((string) $data['discogs_api_key']);
        } else {
            debug_event(self::class, 'No Discogs api key, metadata plugin skipped', 3);

            return false;
        }

        if (strlen(trim((string) $data['discogs_secret_api_key'])) !== 0) {
            $this->secret = trim((string) $data['discogs_secret_api_key']);
        } else {
            debug_event(self::class, 'No Discogs secret, metadata plugin skipped', 3);

            return false;
        }

        return true;
    }

    /**
     * @param $query
     * @return mixed
     */
    protected function query_discogs($query)
    {
        $url = 'https://api.discogs.com/' . $query;
        $url .= (str_contains((string) $query, '?')) ? '&' : '?';
        $url .= 'key=' . $this->api_key . '&secret=' . $this->secret;
        debug_event(self::class, 'Discogs request: ' . $url, 5);
        $request = Requests::get($url);

        return json_decode($request->body, true);
    }

    /**
     * @param $artist
     * @return mixed
     */
    protected function search_artist($artist)
    {
        $query = "database/search?type=artist&title=" . rawurlencode((string) $artist) . "&per_page=10";

        return $this->query_discogs($query);
    }

    /**
     * @param int $object_id
     * @return mixed
     */
    protected function get_artist($object_id)
    {
        $query = "artists/" . $object_id;

        return $this->query_discogs($query);
    }

    /**
     * @param $artist
     * @param $album
     * @return mixed
     */
    protected function search_album($artist, $album, $type = 'master')
    {
        $query = "database/search?type=' . $type . '&release_title=" . rawurlencode((string) $album) . "&artist=" . rawurlencode((string) $artist) . "&per_page=10";

        return $this->query_discogs($query);
    }

    /**
     * @param int $object_id
     * @return mixed
     */
    protected function get_album($object_id)
    {
        $query = "masters/" . $object_id;

        return $this->query_discogs($query);
    }

    /**
     * get_metadata
     * Returns song metadata for what we're passed in.
     */
    public function get_metadata(array $gather_types, array $media_info): array
    {
        debug_event(self::class, 'Getting metadata from Discogs...', 5);

        // MUSIC metadata only
        if (!in_array('music', $gather_types)) {
            debug_event(self::class, 'Not a valid media type, skipped.', 5);

            return [];
        }

        $results = [];
        try {
            if (in_array('artist', $media_info) && !in_array('album', $media_info)) {
                $artists = $this->search_artist($media_info['artist']);
                if (count($artists['results']) > 0) {
                    $artist = $this->get_artist($artists['results'][0]['id']);
                    if (count($artist['images']) > 0) {
                        $results['art'] = $artist['images'][0]['uri'];
                    }
                }
            }
            if (in_array('albumartist', $media_info) && in_array('album', $media_info)) {
                $albums = $this->search_album($media_info['albumartist'], $media_info['album']);
                if (empty($albums['results'])) {
                    $albums = $this->search_album($media_info['albumartist'], $media_info['album'], 'release');
                }

                if (!empty($albums['results'])) {
                    $album = $this->get_album($albums['results'][0]['id']);
                    if (count($album['images']) > 0) {
                        $results['art'] = $album['images'][0]['uri'];
                    }
                }
            }
        } catch (Exception $exception) {
            debug_event(self::class, 'Error getting metadata: ' . $exception->getMessage(), 1);
        }

        return $results;
    }

    /**
     * gather_arts
     * Returns art items for the requested media type
     */
    public function gather_arts(string $type, ?array $options = [], ?int $limit = 5): array
    {
        return array_slice(Art::gather_metadata_plugin($this, $type, ($options ?? [])), 0, $limit);
    }
}
