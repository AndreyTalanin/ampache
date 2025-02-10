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

use Ampache\Config\AmpConfig;
use Ampache\Module\Util\Ui;
use Ampache\Module\System\Dba;
use Ampache\Repository\Model\database_object;

if (AmpConfig::get('show_footer_statistics')) {
    $load_time_end = microtime(true);
    $load_time     = number_format(($load_time_end - AmpConfig::get('load_time_begin')), 4);
    echo '<br />' .
        '<span class="query-count">' .
        T_('Queries: ') . Dba::$stats['query'] . ' | ' .
        T_('Cache Hits: ') . database_object::$cache_hit . ' | ' .
        T_('Load Time: ') . $load_time . ' | ' .
        Ui::format_bytes(memory_get_peak_usage(true)) .
        '</span>';
} ?>
