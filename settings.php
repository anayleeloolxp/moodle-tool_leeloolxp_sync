<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Admin settings and defaults
 *
 * @package tool_leeloolxp_sync
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author Leeloo LXP <info@leeloolxp.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
if ($hassiteconfig) {
    $settings = new admin_settingpage('tool_leeloolxp_sync', get_string('leeloolxp_sync_settings', 'tool_leeloolxp_sync'));

    $name = new lang_string('leeloolxp_synclicensekey', 'tool_leeloolxp_sync');

    $description = new lang_string('leeloolxp_synclicensekey_help', 'tool_leeloolxp_sync');

    $default = '';

    $settings->add(new admin_setting_configtext('tool_leeloolxp_sync/leeloolxp_synclicensekey', $name, $description, $default));

    // Link to Course Archiver tool.

    $ADMIN->add('courses', new admin_externalpage(
        'toolleeloolxp_sync',

        get_string('leeloolxp_sync', 'tool_leeloolxp_sync'),
        "$CFG->wwwroot/$CFG->admin/tool/leeloolxp_sync/index.php"
    ));

    // Add the category to the admin tree.

    $ADMIN->add('tools', $settings);
}
