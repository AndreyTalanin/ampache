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

namespace Ampache\Module\Application\Stats;

use Ampache\Config\ConfigContainerInterface;
use Ampache\Config\ConfigurationKeyEnum;
use Ampache\Module\System\Core;
use Ampache\Repository\Model\ModelFactoryInterface;
use Ampache\Repository\Model\Rating;
use Ampache\Repository\Model\User;
use Ampache\Module\Application\ApplicationActionInterface;
use Ampache\Module\Authorization\GuiGatekeeperInterface;
use Ampache\Module\Util\UiInterface;
use Ampache\Repository\VideoRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HighestVideoAction implements ApplicationActionInterface
{
    public const REQUEST_KEY = 'highest_video';

    private UiInterface $ui;

    private ModelFactoryInterface $modelFactory;

    private ConfigContainerInterface $configContainer;

    private VideoRepositoryInterface $videoRepository;

    public function __construct(
        UiInterface $ui,
        ModelFactoryInterface $modelFactory,
        ConfigContainerInterface $configContainer,
        VideoRepositoryInterface $videoRepository
    ) {
        $this->ui              = $ui;
        $this->modelFactory    = $modelFactory;
        $this->configContainer = $configContainer;
        $this->videoRepository = $videoRepository;
    }

    public function run(ServerRequestInterface $request, GuiGatekeeperInterface $gatekeeper): ?ResponseInterface
    {
        $thresh_value = $this->configContainer->get(ConfigurationKeyEnum::STATS_THRESHOLD);
        $limit        = $this->configContainer->get(ConfigurationKeyEnum::OFFSET_LIMIT);

        $this->ui->showHeader();
        $this->ui->show('show_form_highest.inc.php');
        $this->ui->showHeader();

        define('TABLE_RENDERED', 1);

        // Temporary workaround to avoid sorting on custom base requests
        define('NO_BROWSE_SORTING', true);

        $user_id = ($this->configContainer->get(ConfigurationKeyEnum::CATALOG_FILTER) && Core::get_global('user') instanceof User)
            ? Core::get_global('user')->id
            : null;

        if (
            $this->configContainer->isFeatureEnabled(ConfigurationKeyEnum::ALLOW_VIDEO) &&
            $this->videoRepository->getItemCount()
        ) {
            $objects = Rating::get_highest('video', $limit, 0, $user_id);
            $browse  = $this->modelFactory->createBrowse();
            $browse->set_type('video');
            $browse->show_objects($objects);
            $browse->store();
        }

        $this->ui->showBoxBottom();

        show_table_render(false, true);

        $this->ui->showQueryStats();
        $this->ui->showFooter();

        return null;
    }
}
