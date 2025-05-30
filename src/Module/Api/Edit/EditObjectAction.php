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

namespace Ampache\Module\Api\Edit;

use Ampache\Config\AmpConfig;
use Ampache\Config\ConfigContainerInterface;
use Ampache\Module\Authorization\AccessLevelEnum;
use Ampache\Module\Authorization\AccessTypeEnum;
use Ampache\Module\System\LegacyLogger;
use Ampache\Repository\Model\Browse;
use Ampache\Repository\Model\library_item;
use Ampache\Repository\Model\Podcast;
use Ampache\Repository\Model\Share;
use Ampache\Repository\Model\Tag;
use Ampache\Module\Authorization\GuiGatekeeperInterface;
use Ampache\Module\Util\ObjectTypeToClassNameMapper;
use Ampache\Repository\LabelRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class EditObjectAction extends AbstractEditAction
{
    public const REQUEST_KEY = 'edit_object';

    private LabelRepositoryInterface $labelRepository;

    private LoggerInterface $logger;

    public function __construct(
        ConfigContainerInterface $configContainer,
        LabelRepositoryInterface $labelRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($configContainer, $logger);
        $this->labelRepository = $labelRepository;
        $this->logger          = $logger;
    }

    protected function handle(
        ServerRequestInterface $request,
        GuiGatekeeperInterface $gatekeeper,
        string $object_type,
        library_item|Share $libitem,
        int $object_id,
        ?Browse $browse = null
    ): ?ResponseInterface {
        // Scrub the data, walk recursive through array
        $entities = function (&$data) use (&$entities) {
            foreach ($data as $key => $value) {
                $data[$key] = (is_array($value))
                    ? $entities($value)
                    : unhtmlentities((string)scrub_in((string) $value));
            }

            return $data;
        };

        if (!isset($_POST['id'])) {
            return null;
        }

        $user   = $gatekeeper->getUser();
        $userId = ($user)
            ? $user->getId()
            : null;

        $entities($_POST);
        if (empty($object_type)) {
            $object_type = filter_input(INPUT_GET, 'object_type', FILTER_SANITIZE_SPECIAL_CHARS);
        } else {
            $object_type = implode('_', explode('_', $object_type, -1));
        }
        $this->logger->debug(
            'edit_object: {' . $object_type . '} {' . $object_id . '}',
            [LegacyLogger::CONTEXT_TYPE => self::class]
        );
        $className = ObjectTypeToClassNameMapper::map((string)$object_type);
        /** @var library_item|Share $libitem */
        $libitem = new $className($_POST['id']);
        if (
            $libitem->get_user_owner() === $userId &&
            AmpConfig::get('upload_allow_edit') &&
            !$gatekeeper->mayAccess(AccessTypeEnum::INTERFACE, AccessLevelEnum::CONTENT_MANAGER)
        ) {
            // TODO: improve this uniqueness check
            if (isset($_POST['user'])) {
                unset($_POST['user']);
            }
            if (isset($_POST['artist'])) {
                unset($_POST['artist']);
            }
            if (isset($_POST['artist_name'])) {
                unset($_POST['artist_name']);
            }
            if (isset($_POST['album'])) {
                unset($_POST['album']);
            }
            if (isset($_POST['album_name'])) {
                unset($_POST['album_name']);
            }
            if (isset($_POST['artist_name'])) {
                unset($_POST['artist_name']);
            }
            if (isset($_POST['edit_tags'])) {
                $_POST['edit_tags'] = Tag::clean_to_existing($_POST['edit_tags']);
            }
            if (isset($_POST['edit_labels'])) {
                $_POST['edit_labels'] = $this->clean_to_existing($_POST['edit_labels']);
            }
            // Check mbid and *_mbid match as it is used as identifier
            if (isset($_POST['mbid']) && isset($libitem->mbid)) {
                $_POST['mbid'] = $libitem->mbid;
            }
            if (isset($_POST['mbid_group']) && isset($libitem->mbid_group)) {
                $_POST['mbid_group'] = $libitem->mbid_group;
            }
        }

        /**
         * @todo updating must be separated by item type - this is ugly as hell
         */
        if ($libitem instanceof Share && $user !== null) {
            $libitem->update($_POST, $user);
        } elseif ($libitem instanceof Podcast) {
            $feedUrl = $_POST['feed'] ?? '';

            if (filter_var($feedUrl, FILTER_VALIDATE_URL)) {
                $libitem->setTitle($_POST['title'] ?? $libitem->getTitle())
                    ->setFeedUrl($feedUrl)
                    ->setWebsite(filter_var(urldecode($_POST['website']), FILTER_VALIDATE_URL) ?: $libitem->getWebsite())
                    ->setDescription($_POST['description'] ?? $libitem->getDescription())
                    ->setLanguage($_POST['language'] ?? $libitem->getLanguage())
                    ->setGenerator($_POST['generator'] ?? $libitem->getGenerator())
                    ->setCopyright($_POST['copyright'] ?? $libitem->getCopyright())
                    ->save();
            }
        } else {
            // @todo: is it really necessary to call format before updating the object?
            if (method_exists($libitem, 'format')) {
                $libitem->format();
            }
            $libitem->update($_POST);
        }

        xoutput_headers();

        echo (string) xoutput_from_array(['id' => $object_id]);

        return null;
    }

    /**
     * clean_to_existing
     * Clean label list to existing label list only
     * @param string|string[] $labels
     * @return string[]|string
     */
    private function clean_to_existing(array|string $labels): array|string
    {
        $array = (is_array($labels)) ? $labels : preg_split('/(\s*,*\s*)*,+(\s*,*\s*)*/', $labels);
        $ret   = [];
        if ($array !== false) {
            foreach ($array as $label) {
                $label = trim((string)$label);
                if (!empty($label)) {
                    if ($this->labelRepository->lookup($label) > 0) {
                        $ret[] = $label;
                    }
                }
            }
        }

        return (is_array($labels)
            ? $ret
            : implode(",", $ret));
    }
}
