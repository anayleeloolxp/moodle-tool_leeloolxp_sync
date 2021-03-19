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
$observers = array(

    array(
        'eventname' => '*',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),
    array(
        'eventname' => 'core\event\badge_criteria_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_criteria_createdd',
    ),
    array(
        'eventname' => '\core\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::viewed_activity',
    ),

    array(
        'eventname' => '\core\event\group_member_added',
        'callback' => '\tool_leeloolxp_sync\syncobserver::group_member_added',
    ),

    array(
        'eventname' => '\core\event\user_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::edit_user',
    ),

    array(
        'eventname' => '\core\event\role_assigned',
        'callback' => '\tool_leeloolxp_sync\syncobserver::role_assign',
    ),

    array(
        'eventname' => '\core\event\course_module_completion_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::completion_updated',
    ),

    array(
        'eventname' => 'core\event\course_category_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::course_category_delete',
    ),
);