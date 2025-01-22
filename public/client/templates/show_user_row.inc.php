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
use Ampache\Module\Authorization\AccessLevelEnum;
use Ampache\Module\Authorization\AccessTypeEnum;
use Ampache\Repository\Model\User;
use Ampache\Module\Authorization\Access;
use Ampache\Module\System\Core;
use Ampache\Module\User\Following\UserFollowStateRendererInterface;
use Ampache\Module\Util\Ui;

/** @var string $web_path */
/** @var string $admin_path */
/** @var UserFollowStateRendererInterface $userFollowStateRenderer */
/** @var User $libitem */
/** @var string $last_seen */
/** @var string $create_date */
/** @var string $t_send_pm */
/** @var string $t_edit */
/** @var string $t_preferences */
/** @var string $t_enable */
/** @var string $t_disable */
/** @var string $t_delete */
?>
    <td class="cel_username">
        <a href="<?php echo $web_path; ?>/stats.php?action=show_user&user_id=<?php echo $libitem->id; ?>">
            <?php
                if ($libitem->f_avatar_mini) {
                    echo $libitem->f_avatar_mini;
                }
echo scrub_out($libitem->username);
if ($libitem->fullname_public || Access::check(AccessTypeEnum::INTERFACE, AccessLevelEnum::ADMIN)) {
    echo " (" . scrub_out($libitem->fullname) . ")";
} ?>
        </a>
    </td>
    <td class="cel_lastseen"><?php echo $last_seen; ?></td>
    <td class="cel_registrationdate"><?php echo $create_date; ?></td>
    <?php
        if (Access::check(AccessTypeEnum::INTERFACE, AccessLevelEnum::CONTENT_MANAGER)) { ?>
            <td class="cel_activity"><?php echo $libitem->f_usage; ?></td>
        <?php
            if (AmpConfig::get('track_user_ip')) { ?>
                <td class="cel_lastip">
                    <a href="<?php echo $admin_path; ?>/users.php?action=show_ip_history&user_id=<?php echo $libitem->id; ?>">
                        <?php echo $libitem->ip_history; ?>
                    </a>
                </td>
                <?php
            }
        }
if (Core::get_global('user') instanceof User && Access::check(AccessTypeEnum::INTERFACE, AccessLevelEnum::USER) && AmpConfig::get('sociable')) { ?>
            <td class="cel_follow"><?php echo $userFollowStateRenderer->render($libitem, Core::get_global('user')); ?></td>
            <?php } ?>
    <td class="cel_action">
    <?php
if (Access::check(AccessTypeEnum::INTERFACE, AccessLevelEnum::USER) && AmpConfig::get('sociable')) { ?>
            <a id="<?php echo 'reply_pvmsg_' . $libitem->id; ?>" href="<?php echo AmpConfig::get_web_path('/client'); ?>/pvmsg.php?action=show_add_message&to_user=<?php echo urlencode((string)$libitem->username); ?>">
                <?php echo Ui::get_material_symbol('mail', $t_send_pm); ?>
            </a>
        <?php } ?>
    <?php
if (Access::check(AccessTypeEnum::INTERFACE, AccessLevelEnum::ADMIN)) { ?>
            <a href="<?php echo $admin_path; ?>/users.php?action=show_edit&user_id=<?php echo $libitem->id; ?>">
                <?php echo Ui::get_material_symbol('edit', $t_edit); ?>
            </a>
            <a href="<?php echo $admin_path; ?>/users.php?action=show_preferences&user_id=<?php echo $libitem->id; ?>">
                <?php echo Ui::get_material_symbol('page_info', $t_preferences); ?>
            </a>
        <?php
    // FIXME: Fix this for the extra permission levels
    if ($libitem->disabled == '1') {
        echo "<a href=\"" . $admin_path . "/users.php?action=enable&user_id=$libitem->id\">" . Ui::get_material_symbol('person', $t_enable) . "</a>";
    } else {
        echo "<a href=\"" . $admin_path . "/users.php?action=disable&user_id=$libitem->id\">" . Ui::get_material_symbol('person_off', $t_disable) . "</a>";
    } ?>
        <a href="<?php echo $admin_path; ?>/users.php?action=delete&user_id=<?php echo $libitem->id; ?>">
            <?php echo Ui::get_material_symbol('close', $t_delete); ?>
        </a>
        <?php } ?>
    </td>
    <?php
        if (($libitem->is_logged_in()) && ($libitem->is_online())) {
            echo "<td class=\"cel_online user_online\"> &nbsp; </td>";
        } elseif ($libitem->disabled == 1) {
            echo "<td class=\"cel_online user_disabled\"> &nbsp; </td>";
        } else {
            echo "<td class=\"cel_online user_offline\"> &nbsp; </td>";
        } ?>
