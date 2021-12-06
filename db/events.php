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
        'eventname' => '\core\event\course_module_completion_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::completion_updated',
    ),

    array(
        'eventname' => '\core\event\user_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::edit_user',
    ),

    array(
        'eventname' => '\core\event\group_member_added',
        'callback' => '\tool_leeloolxp_sync\syncobserver::group_member_added',
    ),

    array(
        'eventname' => '\core\event\role_assigned',
        'callback' => '\tool_leeloolxp_sync\syncobserver::role_assign',
    ),

    array(
        'eventname' => '\core\event\user_loggedin',
        'callback' => '\tool_leeloolxp_sync\syncobserver::user_logged_in',
    ),

    array(
        'eventname' => '\core\event\course_category_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::course_category_delete',
    ),

    /*array(
        'eventname' => '*',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),*/

    array(
        'eventname' => '\core\event\cohort_member_added',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\cohort_member_removed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\cohort_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\cohort_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\cohort_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\course_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\user_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\user_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_assign\event\submission_status_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_forum\event\post_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_forum\event\post_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_forum\event\discussion_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_forum\event\discussion_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\assignsubmission_file\event\assessable_uploaded',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_choice\event\answer_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_feedback\event\response_submitted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_forum\event\assessable_uploaded',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_glossary\event\entry_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_lesson\event\question_answered',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_quiz\event\attempt_submitted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_survey\event\response_submitted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_workshop\event\submission_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\course_category_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\course_category_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_workshop\event\assessable_uploaded',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\assignsubmission_onlinetext\event\assessable_uploaded',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_assign\event\assessable_submitted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\course_module_completion_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\report_completion\event\report_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\role_unassigned',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\role_assigned',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_workshop\event\submission_reassessed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_workshop\event\submission_assessed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_quiz\event\attempt_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\tag_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\user_graded',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_quiz\event\attempt_reviewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_lesson\event\essay_assessed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_assign\event\submission_graded',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\grade_item_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\grade_item_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\badge_archived',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\badge_awarded',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\badge_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\badge_criteria_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\badge_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\badge_disabled',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\badge_enabled',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\badge_revoked',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\badge_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\calendar_event_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\calendar_event_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\calendar_event_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\calendar_subscription_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\calendar_subscription_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\calendar_subscription_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_assign\event\extension_granted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_assign\event\submission_locked',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_assign\event\submission_unlocked',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\course_module_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_assign\event\submission_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_book\event\chapter_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_assign\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_book\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_chat\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_choice\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_customcert\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_data\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_feedback\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_folder\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_forum\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_glossary\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_h5pactivity\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_imscp\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_leeloolxpvc\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_leeloolxpvimeo\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_lesson\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_lti\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_page\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_quiz\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_resource\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_scorm\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_survey\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_url\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_wiki\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_workshop\event\course_module_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\course_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\message_sent',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_chat\event\message_sent',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_choice\event\answer_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_choice\event\answer_submitted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_choice\event\answer_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_data\event\comment_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_glossary\event\comment_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_wiki\event\comment_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\block_comments\event\comment_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\assignsubmission_comments\event\comment_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_data\event\comment_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_glossary\event\comment_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_wiki\event\comment_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\block_comments\event\comment_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\assignsubmission_comments\event\comment_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_data\event\record_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_data\event\record_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_data\event\record_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_glossary\event\entry_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_glossary\event\entry_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_feedback\event\response_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_folder\event\all_files_downloaded',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_forum\event\course_searched',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_forum\event\discussion_subscription_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_forum\event\discussion_subscription_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_forum\event\discussion_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_forum\event\post_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_forum\event\subscription_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\tool_monitor\event\subscription_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_forum\event\subscription_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_forum\event\user_report_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_scorm\event\user_report_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\report_completion\event\user_report_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\report_log\event\user_report_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\report_stats\event\user_report_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_glossary\event\category_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_glossary\event\category_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_glossary\event\category_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_glossary\event\entry_approved',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_glossary\event\entry_disapproved',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_glossary\event\entry_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\note_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\note_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_wiki\event\page_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_lesson\event\content_page_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_lesson\event\highscore_added',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_lesson\event\highscores_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_lesson\event\lesson_ended',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_lesson\event\lesson_restarted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_lesson\event\lesson_resumed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_lesson\event\lesson_started',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_lesson\event\question_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\core\event\question_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_quiz\event\attempt_abandoned',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_quiz\event\attempt_becameoverdue',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_quiz\event\attempt_started',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_quiz\event\attempt_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_scorm\event\sco_launched',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_scorm\event\scoreraw_submitted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_scorm\event\status_submitted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_wiki\event\comments_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_lesson\event\page_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_wiki\event\page_created',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_lesson\event\page_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_wiki\event\page_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_wiki\event\page_diff_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_wiki\event\page_history_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_wiki\event\page_map_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_lesson\event\page_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_wiki\event\page_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_wiki\event\page_version_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_wiki\event\page_version_restored',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_wiki\event\page_version_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_workshop\event\submission_deleted',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_workshop\event\submission_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\assignsubmission_file\event\submission_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\assignsubmission_onlinetext\event\submission_updated',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),

    array(
        'eventname' => '\mod_workshop\event\submission_viewed',
        'callback' => '\tool_leeloolxp_sync\syncobserver::badge_createdd',
    ),
);
