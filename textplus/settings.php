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
 * Plugin settings
 *
 * @package    local_textplus
 * @copyright  2025 G Wiz IT Solutions {@link https://gwizit.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     G Wiz IT Solutions
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_textplus', get_string('settingstitle', 'local_textplus'));

    // Default search term.
    $settings->add(new admin_setting_configtext(
        'local_textplus/defaultsearchterm',
        get_string('defaultsearchterm', 'local_textplus'),
        get_string('defaultsearchterm_desc', 'local_textplus'),
        '',
        PARAM_TEXT
    ));

    // Default execution mode.
    $settings->add(new admin_setting_configselect(
        'local_textplus/defaultmode',
        get_string('defaultmode', 'local_textplus'),
        get_string('defaultmode_desc', 'local_textplus'),
        'preview',
        [
            'preview' => get_string('mode_preview', 'local_textplus'),
            'execute' => get_string('mode_execute', 'local_textplus'),
        ]
    ));

    $ADMIN->add('localplugins', $settings);

    // Add link to the tool in the site administration menu.
    // Try 'server' first (Moodle 5.x), fall back to 'tools' if it doesn't exist
    if ($ADMIN->locate('server')) {
        $ADMIN->add('server',
            new admin_externalpage(
                'local_textplus_tool',
                get_string('pluginname', 'local_textplus'),
                new moodle_url('/local/textplus/index.php'),
                'local/textplus:view'
            )
        );
    } else {
        $ADMIN->add('tools',
            new admin_externalpage(
                'local_textplus_tool',
                get_string('pluginname', 'local_textplus'),
                new moodle_url('/local/textplus/index.php'),
                'local/textplus:view'
            )
        );
    }
}
