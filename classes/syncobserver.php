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

namespace tool_leeloolxp_sync;

use core_user;
use curl;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * Plugin to sync users on new enroll, groups, trackign of activity view to LeelooLXP account of the Moodle Admin
 */
class syncobserver {

    /**
     * Function to get Leeloo Install
     *
     * @return string leeloo url
     */
    public static function tool_leeloolxp_sync_get_leelooinstall() {

        global $SESSION;

        if (isset($SESSION->sync_leelooinstall)) {
            return $SESSION->sync_leelooinstall;
        }

        $configenroll = get_config('tool_leeloolxp_sync');
        $liacnsekey = $configenroll->leeloolxp_synclicensekey;
        $postdata = array('license_key' => $liacnsekey);
        $url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';
        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );

        if (!$output = $curl->post($url, $postdata, $options)) {
            $leelooinstallurl = 'no';
            $SESSION->sync_leelooinstall = $leelooinstallurl;
        }

        $infoteamnio = json_decode($output);
        if ($infoteamnio->status != 'false') {
            $leelooinstallurl = $infoteamnio->data->install_url;
            $SESSION->sync_leelooinstall = $leelooinstallurl;
        } else {
            $leelooinstallurl = 'no';
            $SESSION->sync_leelooinstall = $leelooinstallurl;
        }

        return $leelooinstallurl;
    }

    /**
     * Triggered when course completed.
     *
     * @param \core\event\course_module_completion_updated $event
     */
    public static function completion_updated(\core\event\course_module_completion_updated $event) {

        $teamniourl = self::tool_leeloolxp_sync_get_leelooinstall();

        if ($teamniourl == 'no') {
            return true;
        }

        global $DB;

        global $CFG;

        require_once($CFG->dirroot . '/lib/filelib.php');

        $moduleid = $event->contextinstanceid;

        $userid = $event->userid;

        $completionstate = $event->other['completionstate'];

        $user = $DB->get_record('user', array('id' => $userid));

        $postdata = array('email' => base64_encode($user->email), 'completionstate' => $completionstate, 'activity_id' => $moduleid);

        $url = $teamniourl . '/admin/sync_moodle_course/mark_completed_by_moodle_user';

        $curl = new curl;

        $options = array(

            'CURLOPT_RETURNTRANSFER' => true,

            'CURLOPT_HEADER' => false,

            'CURLOPT_POST' => count($postdata),

        );

        if (!$output = $curl->post($url, $postdata, $options)) {
            return true;
        }

        return true;
    }

    /**
     * Triggered when user profile updated.
     *
     * @param \core\event\user_updated $event
     */
    public static function edit_user(\core\event\user_updated $event) {

        $teamniourl = self::tool_leeloolxp_sync_get_leelooinstall();

        if ($teamniourl == 'no') {
            return true;
        }

        $data = $event->get_data();

        global $DB;

        global $CFG;

        require_once($CFG->dirroot . '/lib/filelib.php');

        $user = core_user::get_user($data['relateduserid'], '*', MUST_EXIST);

        $email = str_replace("'", '', $user->email);

        $countries = get_string_manager()->get_list_of_countries();

        $userinterests = $DB->get_records_sql("SELECT * FROM {tag}

            JOIN {tag_instance} ON {tag_instance}.tagid = {tag}.id JOIN {user}

            ON {user}.id = {tag_instance}.itemid AND {tag_instance}.itemtype = 'user'

            AND {user}.username = ?", [$user->username]);

        $userinterestsarr = array();

        if (!empty($userinterests)) {
            foreach ($userinterests as $value) {
                $userinterestsarr[] = $value->name;
            }
        }

        $userinterestsstring = implode(',', $userinterestsarr);

        $lastlogin = date('Y-m-d h:i:s', $user->lastlogin);

        $fullname = fullname($user);

        $city = $user->city;

        $country = $countries[$user->country];

        $timezone = $user->timezone;

        $skype = $user->skype;

        $idnumber = $user->idnumber;

        $institution = $user->institution;

        $department = $user->department;

        $phone = $user->phone1;

        $moodlephone = $user->phone2;

        $address = $user->address;

        $firstaccess = $user->firstaccess;

        $lastaccess = $user->lastaccess;

        $lastlogin = $lastlogin;

        $lastip = $user->lastip;

        $interests = $userinterestsstring;

        $description = $user->description;

        $descriptionofpic = $user->imagealt;

        $alternatename = $user->alternatename;

        $webpage = $user->url;

        $sql = "SELECT ud.data  FROM {user_info_data} ud JOIN

            {user_info_field} uf ON uf.id = ud.fieldid WHERE ud.userid = :userid

            AND uf.shortname = :fieldname";

        $params = array('userid' => $user->id, 'fieldname' => 'degree');

        $degree = $DB->get_field_sql($sql, $params);

        $sql = "SELECT ud.data FROM {user_info_data} ud JOIN {user_info_field}

            uf ON uf.id = ud.fieldid WHERE ud.userid = :userid AND

            uf.shortname = :fieldname";

        $params = array('userid' => $user->id, 'fieldname' => 'Pathway');

        $pathway = $DB->get_field_sql($sql, $params);

        $imgurl = new moodle_url('/user/pix.php/' . $user->id . '/f1.jpg');

        $postdata = array(
            'email' => base64_encode($email),
            'name' => $fullname,
            'city' => $city,
            'country' => $country,
            'timezone' => $timezone,
            'skype' => $skype,
            'idnumber' => $idnumber,
            'institution' => $institution,
            'department' => $department,
            'phone' => $phone,
            'moodle_phone' => $moodlephone,
            'address' => $address,
            'firstaccess' => $firstaccess,
            'lastaccess' => $lastaccess,
            'lastlogin' => $lastlogin,
            'lastip' => $lastip,
            'description' => $description,
            'description_of_pic' => $descriptionofpic,
            'alternatename' => $alternatename,
            'web_page' => $webpage,
            'img_url' => $imgurl,
            'interests' => $interests,
            'degree' => $degree,
            'pathway' => $pathway,
        );

        $url = $teamniourl . '/admin/sync_moodle_course/update_username';

        $curl = new curl;

        $options = array(

            'CURLOPT_RETURNTRANSFER' => true,

            'CURLOPT_HEADER' => false,

            'CURLOPT_POST' => count($postdata),

        );

        if (!$output = $curl->post($url, $postdata, $options)) {
            return true;
        }
        return true;
    }

    /**
     * Triggered when group member added.
     *
     * @param \core\event\group_member_added $events
     */
    public static function group_member_added(\core\event\group_member_added $events) {

        $teamniourl = self::tool_leeloolxp_sync_get_leelooinstall();

        if ($teamniourl == 'no') {
            return true;
        }

        global $CFG;

        require_once($CFG->dirroot . '/lib/filelib.php');

        $group = $events->get_record_snapshot('groups', $events->objectid);

        $user = core_user::get_user($events->relateduserid, '*', MUST_EXIST);

        $courseid = str_replace("'", '', $courseid);

        $groupname = str_replace("'", '', $group->name);

        $postdata = array('email' => base64_encode($user->email), 'courseid' => $courseid, 'group_name' => $groupname);

        $url = $teamniourl . '/admin/sync_moodle_course/update_group';

        $curl = new curl;

        $options = array(

            'CURLOPT_RETURNTRANSFER' => true,

            'CURLOPT_HEADER' => false,

            'CURLOPT_POST' => count($postdata),

        );

        if (!$output = $curl->post($url, $postdata, $options)) {
            return true;
        }
        return true;
    }

    /**
     * Triggered when new role assigned to user.
     *
     * @param \core\event\role_assigned $enrolmentdata
     */
    public static function role_assign(\core\event\role_assigned $enrolmentdata) {

        $teamniourl = self::tool_leeloolxp_sync_get_leelooinstall();

        if ($teamniourl == 'no') {
            return true;
        }

        global $DB;

        global $CFG;

        require_once($CFG->dirroot . '/lib/filelib.php');

        $snapshotid = $enrolmentdata->get_data()['other']['id'];

        $snapshot = $enrolmentdata->get_record_snapshot('role_assignments', $snapshotid);

        $roleid = $snapshot->roleid;

        $usertype = '';

        $teamniorole = '';

        $user = $DB->get_record('user', array('id' => $enrolmentdata->relateduserid));

        $userdegree = $DB->get_record_sql("SELECT DISTINCT data  FROM {user_info_data}

        left join {user_info_field} on {user_info_data}.fieldid = {user_info_field}.id where {user_info_field}.shortname =

        'degree' and {user_info_data}.userid = ?", [$user->id]);

        $userdegreename = @$userdegree->data;

        $userdepartment = $user->department;

        $userinstitution = $user->institution;

        $ssopluginconfig = get_config('auth_leeloolxp_tracking_sso');

        $studentnumcombinationsval = $ssopluginconfig->student_num_combination;

        $studentdbsetarr = array();

        for ($si = 1; $studentnumcombinationsval >= $si; $si++) {
            $studentpositionmoodle = 'student_position_moodle_' . $si;

            $mstudentrole = $ssopluginconfig->$studentpositionmoodle;

            $studentinstitution = 'student_institution_' . $si;

            $mstudentinstitution = $ssopluginconfig->$studentinstitution;

            $studentdepartment = 'student_department_' . $si;

            $mstudentdepartment = $ssopluginconfig->$studentdepartment;

            $studentdegree = 'student_degree_' . $si;

            $mstudentdegree = $ssopluginconfig->$studentdegree;

            $studentdbsetarr[$si] = $mstudentrole . "_" . $mstudentinstitution . "_" .

                $mstudentdepartment . "_" . $mstudentdegree;
        }

        $userstudentinfo = $roleid . "_" . $userinstitution . "_" . $userdepartment . "_" .

            $userdegreename;

        $matchedvalue = array_search($userstudentinfo, $studentdbsetarr);

        if ($matchedvalue) {
            $tcolnamestudent = 'student_position_t_' . $matchedvalue;
            $teamniostudentrole = $ssopluginconfig->$tcolnamestudent;
            if (!empty($teamniostudentrole)) {
                $teamniorole = $teamniostudentrole;
            }
            $usertype = 'student';
        } else {

            @$teachernumcombinationsval = $ssopluginconfig->teachernumcombination;

            $teacherdbsetarr = array();

            for ($si = 1; $teachernumcombinationsval >= $si; $si++) {
                $teacherpositionmoodle = 'teacher_position_moodle_' . $si;

                $mteacherrole = $ssopluginconfig->$teacherpositionmoodle;

                $teacherinstitution = 'teacher_institution_' . $si;

                $mteacherinstitution = $ssopluginconfig->$teacherinstitution;

                $teacherdepartment = 'teacher_department_' . $si;

                $mteacherdepartment = $ssopluginconfig->$teacherdepartment;

                $teacherdegree = 'teacher_degree_' . $si;

                $mteacherdegree = $ssopluginconfig->$teacherdegree;

                $teacherdbsetarr[$si] = $mteacherrole . "_" . $mteacherinstitution . "_" .

                    $mteacherdepartment . "_" . $mteacherdegree;
            }

            $userteacherinfo = $roleid . "_" . $userinstitution . "_" . $userdepartment . "_" .

                $userdegreename;

            $matchedvalueteacher = array_search($userteacherinfo, $teacherdbsetarr);

            if ($matchedvalueteacher) {
                $tcolnameteacher = 'teacher_position_t_' . $matchedvalueteacher;

                $teamnioteacherrole = $ssopluginconfig->$tcolnameteacher;

                if (!empty($teamnioteacherrole)) {
                    $teamniorole = $teamnioteacherrole;
                }

                $usertype = 'teacher';
            } else {

                $usertype = 'student';

                $teamniorole = $ssopluginconfig->default_student_position;
            }
        }

        if ($usertype == 'student') {
            $cancreateuser = $ssopluginconfig->web_new_user_student;

            $userapproval = $ssopluginconfig->required_aproval_student;

            $userdesignation = $teamniorole;
        } else {

            if ($usertype == 'teacher') {
                $cancreateuser = $ssopluginconfig->web_new_user_teacher;

                $userdesignation = $teamniorole;

                $userapproval = $ssopluginconfig->required_aproval_teacher;
            }
        }

        $lastlogin = date('Y-m-d h:i:s', $user->lastlogin);

        $fullname = fullname($user);

        $city = $user->city;

        $country = $user->country;

        $timezone = $user->timezone;

        $skype = $user->skype;

        $idnumber = $user->idnumber;

        $institution = $user->institution;

        $department = $user->department;

        $phone = $user->phone1;

        $moodlephone = $user->phone2;

        $adress = $user->address;

        $firstaccess = $user->firstaccess;

        $lastaccess = $user->lastaccess;

        $lastlogin = $lastlogin;

        $lastip = $user->lastip;

        $description = $user->description;

        $descriptionofpic = $user->imagealt;

        $alternatename = $user->alternatename;

        $webpage = $user->url;

        $moodleurlpic = new moodle_url('/user/pix.php/' . $user->id . '/f1.jpg');

        $moodlepicdata = file_get_contents($moodleurlpic);

        $postdata = array(
            'email' => base64_encode($user->email),
            'username' => base64_encode($user->username),
            'fullname' => $fullname,
            'courseid' => $enrolmentdata->courseid,
            'designation' => $userdesignation,
            'user_role' => $teamniorole,
            'user_approval' => $userapproval,
            'can_user_create' => $cancreateuser,
            'user_type' => $usertype,
            'city' => $city,
            'country' => $country,
            'timezone' => $timezone,
            'skype' => $skype,
            'idnumber' => $idnumber,
            'institution' => $institution,
            'department' => $department,
            'phone' => $phone,
            'moodle_phone' => $moodlephone,
            'adress' => $adress,
            'firstaccess' => $firstaccess,
            'lastaccess' => $lastaccess,
            'lastlogin' => $lastlogin,
            'lastip' => $lastip,
            'user_profile_pic' => urlencode($moodlepicdata),
            'user_description' => $description,
            'picture_description' => $descriptionofpic,
            'alternate_name' => $alternatename,
            'web_page' => $webpage,
        );

        $url = $teamniourl . '/admin/sync_moodle_course/enrolment_newuser';

        $curl = new curl;

        $options = array(

            'CURLOPT_RETURNTRANSFER' => true,

            'CURLOPT_HEADER' => false,

            'CURLOPT_POST' => count($postdata),

        );

        if (!$output = $curl->post($url, $postdata, $options)) {
            return true;
        }
        return true;
    }

    /**
     * Triggered when user_logged_in.
     *
     * @param \core\event\user_loggedin $events
     */
    public static function user_logged_in(\core\event\user_loggedin $events) {

        $teamniourl = self::tool_leeloolxp_sync_get_leelooinstall();

        if ($teamniourl == 'no') {
            return true;
        }

        $postdata = '&moodle_cat_id=0';
        $url = $teamniourl . '/admin/sync_moodle_course/check_grade_and_history';
        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => 1,
        );
        $output = $curl->post($url, $postdata, $options);
        $output = json_decode($output);
        session_start();
        $_SESSION['gradehistoryid'] = $output->grade_history_id;
        $_SESSION['gradegradesid'] = $output->grade_grades_id;
        $_SESSION['coursecompleteid'] = $output->course_complete;

        return true;
    }

    /**
     * Triggered when category delete.
     *
     * @param \core\event\course_category_deleted $events
     */
    public static function course_category_delete(\core\event\course_category_deleted $events) {

        $teamniourl = self::tool_leeloolxp_sync_get_leelooinstall();

        if ($teamniourl == 'no') {
            return true;
        }

        $postdata = '&moodle_cat_id=' . $events->get_data()['objectid'];
        $url = $teamniourl . '/admin/sync_moodle_course/delete_category_from_moodle/';
        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => 1,
        );

        if (!$output = $curl->post($url, $postdata, $options)) {
            return true;
        }
        return true;
    }

    /**
     * Triggered when base.
     *
     * @param \core\event\base $events
     */
    public static function badge_createdd(\core\event\base $events) {

        $teamniourl = self::tool_leeloolxp_sync_get_leelooinstall();

        if ($teamniourl == 'no') {
            return true;
        }

        // return true; // to disable it for now bcz * event is not allowed by moodle.

        global $USER;
        global $CFG;
        global $DB;

        require_once($CFG->dirroot . '/lib/filelib.php');

        $eventdata = $events->get_data();
        $alldetail = json_encode($eventdata);

        $userid = $eventdata['userid'];
        $courseid = $eventdata['courseid'];
        $contextinstanceid = $eventdata['contextinstanceid'];
        $component = $eventdata['target'];
        $action = $eventdata['action'];
        $eventname = $eventdata['eventname'];
        // echo "$eventname";die;

        /* if ($eventname != '\mod_assign\event\course_module_viewed')  { 
            echo "$eventname";die;
            // file_put_contents(dirname(__FILE__) . "/privacy/test_point.txt", print_r($output,true) );
        } */ 

      /*  if ($eventname == '\core\event\user_deleted')  { //user deleted
            $eventdata = $events->get_data();
            $objecttable = $eventdata['objecttable'];
            $objectid = $eventdata['objectid'];
            $coursedatamain = $events->get_record_snapshot($objecttable, $objectid);
            echo "<pre>";print_r($coursedatamain);die;

        } */

        if ($eventname == '\core\event\course_updated')  {
            // move course/category sync
            $eventdata = $events->get_data();
            $courseid = $eventdata['objectid'];
            $categoryid = $eventdata['other']['updatedfields']['category'];



            $postdata = '&useremail=' . base64_encode($USER->email) . '&courseid=' . $courseid . '&categoryid=' . $categoryid;

            $url = $teamniourl . '/admin/sync_moodle_course/move_course_category';
            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => 1,
            );
            $output = $curl->post($url, $postdata, $options);
            // print_r($output);die;
        }
        
        if ($eventname == '\core\event\user_updated' || $eventname == '\core\event\user_deleted')  {
        // user suspended && deleted
            $eventdata = $events->get_data();
            $userid = $eventdata['objectid'];
            $userdata = $DB->get_record_sql("select email,deleted,suspended,timemodified,username from {user} where id = '$userid'"); 
            if ($eventname == '\core\event\user_deleted') {
                $useremail = str_replace('.'.$userdata->timemodified, '', $userdata->username);
                $userdata->email = $useremail;
            }

            $postdataassign = '&userdata=' . json_encode($userdata);

            $url = $teamniourl . '/admin/sync_moodle_course/update_user_data';
            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => 1,
            );
            $output = $curl->post($url, $postdataassign, $options); 
            // print_r($output);die;
        }

        if ($eventname == '\mod_assign\event\submission_status_updated')  {
            $eventdata = $events->get_data(); 
            $postdataassign = '&useremail=' . base64_encode($USER->email) . '&activity_id=' . $eventdata['contextinstanceid'];

            $url = $teamniourl . '/admin/sync_moodle_course/remove_assingment_submission';
            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => 1,
            );
            $output = $curl->post($url, $postdataassign, $options); 
        }

        if ($eventname == '\mod_forum\event\post_created' || $eventname == '\mod_forum\event\post_deleted')  { // forum reply
            $eventdata = $events->get_data();
            $userid = $eventdata['userid']; 
            $useralldiscussion = $DB->get_records_sql("SELECT fp.id  as post_id, fp.discussion , fp.created , fp.modified , fd.course , cm.id as activityid FROM {forum_posts} fp left join {forum_discussions} fd on fd.id = fp.discussion left join {course_modules} cm on cm.instance = fd.forum where fp.userid = '$userid' AND `module` = '9' ");  
            $postdataforum = '&useremail=' . base64_encode($USER->email) . '&data=' . json_encode($useralldiscussion);             

            $url = $teamniourl . '/admin/sync_moodle_course/insert_update_forum_posts';
            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => 1,
            );
            $output = $curl->post($url, $postdataforum, $options); 
             

        }

        if ($eventname == '\mod_forum\event\discussion_created' || $eventname == '\mod_forum\event\discussion_deleted') { // discussion created/deleted
            $eventdata = $events->get_data();
            $userid = $eventdata['userid']; 
            $useralldiscussion = $DB->get_records_sql("SELECT fd.id  as moodleid, fd.course , fd.name , fd.name , fd.timemodified , cm.id as activityid FROM {forum_discussions} fd left join {course_modules} cm on cm.instance = fd.forum where fd.userid = '$userid' AND `module` = '9' ");  
            $postdataforum = '&useremail=' . base64_encode($USER->email) . '&data=' . json_encode($useralldiscussion); 

            $url = $teamniourl . '/admin/sync_moodle_course/insert_update_forum_discussion';
            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => 1,
            );
            $output = $curl->post($url, $postdataforum, $options);

        }

        if ($eventname == '\assignsubmission_file\event\assessable_uploaded' || $eventname == '\mod_choice\event\answer_created'|| $eventname == '\mod_feedback\event\response_submitted' || $eventname == '\mod_forum\event\assessable_uploaded' || $eventname == '\mod_glossary\event\entry_created' || $eventname == '\mod_lesson\event\question_answered' || $eventname == '\mod_quiz\event\attempt_submitted' || $eventname == '\mod_survey\event\response_submitted' || $eventname == '\mod_workshop\event\submission_created') {

            $eventdata = $events->get_data(); 
            $postdataassign = '&useremail=' . base64_encode($USER->email) . '&activity_id=' . $eventdata['contextinstanceid'];

            $url = $teamniourl . '/admin/sync_moodle_course/insert_activity_submission_date';
            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => 1,
            );
            $output = $curl->post($url, $postdataassign, $options); 
        }            

        if ($eventname == '\core\event\course_category_created' || $eventname == '\core\event\course_category_updated') {
            $eventdata = $events->get_data();
            $iddd = $eventdata['objectid'];
            $catdatamin = $DB->get_records_sql("select * from {course_categories} where id = '$iddd'");

            $postdataworkshopgrade = '&useremail=' . base64_encode($USER->email) . '&cat_data=' . json_encode($catdatamin);

            $url = $teamniourl . '/admin/sync_moodle_course/update_insert_categories';
            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => 1,
            );
            $output = $curl->post($url, $postdataworkshopgrade, $options);
        }

        if ($eventname == '\mod_workshop\event\assessable_uploaded' || $eventname == '\assignsubmission_onlinetext\event\assessable_uploaded' || $eventname == '\mod_assign\event\assessable_submitted') {
            $eventdata = $events->get_data();

            $postdataworkshopgrade = '&useremail=' . base64_encode($USER->email) . '&activity_id=' . $eventdata['contextinstanceid'];

            $url = $teamniourl . '/admin/sync_moodle_course/insert_update_user_submission_completion';
            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => 1,
            );
            $output = $curl->post($url, $postdataworkshopgrade, $options);
        }

        if ($eventname == '\core\event\course_module_completion_updated') {
            $eventdata = $events->get_data();
            $objecttable = $eventdata['objecttable'];
            $objectid = $eventdata['objectid'];
            $coursedatamain = $events->get_record_snapshot($objecttable, $objectid);

            $sql = "SELECT email FROM {user} where id = ?";
            $userdetail = $DB->get_record_sql($sql, [$eventdata['relateduserid']]);

            $postdataworkshopgrade = '&coursedatamain=' . json_encode($coursedatamain) . '&useremail=' . base64_encode($userdetail->email) . '&gradedby=' . base64_encode($USER->email) . '&activity_id=' . $eventdata['contextinstanceid'];

            $url = $teamniourl . '/admin/sync_moodle_course/insert_update_activity_completion';
            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => 1,
            );
            $output = $curl->post($url, $postdataworkshopgrade, $options);
        }

        if ($eventname == '\report_completion\event\report_viewed') {
            $eventdata = $events->get_data();
            $courseid = $eventdata['courseid'];
            $sql = "SELECT email FROM {user} where id = ?";
            $userscompletedcourse = $DB->get_records_sql("SELECT DISTINCT user.email , cc.id , cc.timestarted , cc.reaggregate FROM {course_completions} cc left join {user} user on cc.userid = user.id where cc.course = '$courseid' group by cc.userid ");

            $postdatamain = '&userscompletedcourse=' . json_encode($userscompletedcourse) . '&email=' . base64_encode($USER->email) . '&is_bulk_insert=' . $courseid;

            $url = $teamniourl . '/admin/sync_moodle_course/insert_update_course_completion';
            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => 1,
            );
            $output = $curl->post($url, $postdatamain, $options);
        }

        if ($eventname == '\core\event\role_unassigned' || $eventname == '\core\event\role_assigned') {
            $eventdata = $events->get_data();
            $relateduserid = $eventdata['relateduserid'];
            $rolenames = '';

            $userroles = $DB->get_records_sql("SELECT  DISTINCT rol.shortname  FROM {role_assignments} ra left join {role} rol on ra.roleid = rol.id where ra.userid = '$relateduserid' group by ra.roleid ");

            if (!empty($userroles)) {
                foreach ($userroles as $key => $value) {
                    if ($value->shortname == "teacher") {
                        $rolenames .= 'non editing teacher,';
                    } else if ($value->shortname == "editingteacher") {
                        $rolenames .= 'teacher,';
                    } else {
                        $rolenames .= $value->shortname . ',';
                    }
                }
                $rolenames = trim($rolenames, ',');
            }

            $sql = "SELECT email FROM {user} where id = ?";
            $userdetail = $DB->get_record_sql($sql, [$relateduserid]);

            $postdatamain = '&rolenames=' . json_encode($rolenames) . '&user_email=' . base64_encode($userdetail->email) . '&email_changedby=' . base64_encode($USER->email);

            $url = $teamniourl . '/admin/sync_moodle_course/update_user_role_changed';
            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => 1,
            );
            $output = $curl->post($url, $postdatamain, $options);
        }

        if ($eventname == '\mod_workshop\event\submission_reassessed' || $eventname == '\mod_workshop\event\submission_assessed') {
            // Workshop sync

            $eventdata = $events->get_data();
            $objecttable = $eventdata['objecttable'];
            $objectid = $eventdata['objectid'];
            $fulldata = $events->get_record_snapshot($objecttable, $objectid);

            $sql = "SELECT * FROM {workshop_grades} where assessmentid = ?";
            $workshopdatamain = $DB->get_record_sql($sql, [$eventdata['objectid']]);

            $workshopdatamain->timecreated = $fulldata->timecreated;
            $workshopdatamain->timemodified = $fulldata->timemodified;
            $workshopdatamain->activity_id = $eventdata['contextinstanceid'];

            $sql = "SELECT email FROM {user} where id = ?";
            $userdetail = $DB->get_record_sql($sql, [$eventdata['relateduserid']]);

            $postdataworkshopgrade = '&workshopdatamain=' . json_encode($workshopdatamain) . '&email=' . base64_encode($userdetail->email) . '&gradedby=' . base64_encode($USER->email);

            $url = $teamniourl . '/admin/sync_moodle_course/update_grade_workshop_grade';
            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => 1,
            );
            $output = $curl->post($url, $postdataworkshopgrade, $options);
        }

        if ($eventname == '\mod_quiz\event\attempt_deleted') {
            // Quiz deleted

            $eventdata = $events->get_data();

            $objecttable = $eventdata['objecttable'];
            $objectid = $eventdata['objectid'];
            $fulldata = $events->get_record_snapshot($objecttable, $objectid);

            $userid = $fulldata->userid;
            $quizid = $fulldata->quiz;

            $sql = "SELECT email FROM {user} where id = ?";
            $userdetail = $DB->get_record_sql($sql, [$userid]);

            $sql = "SELECT * FROM {quiz_grades} where quiz = ? and userid = ? ";
            $quizdata = $DB->get_record_sql($sql, [$quizid, $userid]);

            $postdataquizgrade = '&quizdata=' . json_encode($quizdata) . '&email=' . base64_encode($userdetail->email);

            $url = $teamniourl . '/admin/sync_moodle_course/delete_grade_quiz_grade';
            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => 1,
            );
            $output = $curl->post($url, $postdataquizgrade, $options);
        }

        // sync tag to leeloo
        if ($eventname == '\core\event\tag_created') {
            $tagdata = json_encode($events->get_data());

            $posttagdata = '&tagdata=' . $tagdata;

            $url = $teamniourl . '/admin/sync_moodle_course/sync_tags';
            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => 1,
            );
            $output = $curl->post($url, $posttagdata, $options);
        }

        if ($eventname == '\core\event\user_graded') {
            // Forum sync and delete lesson also and quiz submit

            $eventdata = $events->get_data();

            $objecttable = $eventdata['objecttable'];
            $objectid = $eventdata['objectid'];
            $fulldata = $events->get_record_snapshot($objecttable, $objectid);

            $sql = "SELECT iteminstance,itemnumber FROM {grade_items} where id = ? ";
            $itemdata = $DB->get_record_sql($sql, [$fulldata->itemid]);

            $sql = "SELECT * FROM {forum_grades} where forum = ? AND itemnumber = ? AND userid = ? ";
            $forumgradedata = $DB->get_record_sql($sql, [$itemdata->iteminstance, $itemdata->itemnumber, $fulldata->userid]);

            $sql = "SELECT email FROM {user} where id = ?";
            $userdetail = $DB->get_record_sql($sql, [$fulldata->userid]);

            if (!empty($eventdata['other']['overridden'])) {
                $sql = "SELECT * FROM {grade_grades_history} where oldid = ? AND itemid = ? AND userid = ? ORDER BY `id` DESC ";
                $forumgradedata = $DB->get_record_sql($sql, [$eventdata['objectid'], $eventdata['other']['itemid'], $eventdata['relateduserid']]);

                $url = $teamniourl . '/admin/sync_moodle_course/insert_grade_history';
            } else if (!empty($forumgradedata)) {

                $sql = "SELECT * FROM {course_modules} where course = ? AND module = ? AND instance = ? ";
                $forummodule = $DB->get_record_sql($sql, [$eventdata['courseid'], '9', $itemdata->iteminstance]);
                $forumgradedata->activity_id = $forummodule->id;

                $url = $teamniourl . '/admin/sync_moodle_course/update_grade_forum_grade';
            } else {
                $relateduserid = $eventdata['relateduserid'];
                $iteminstance = $itemdata->iteminstance;
                $forumgradedata = array('iteminstance' => $iteminstance, 'relateduserid' => $relateduserid);
                $url = $teamniourl . '/admin/sync_moodle_course/delete_lesson_grades';
            }

            $postdataforum = '&forumgradedata=' . json_encode($forumgradedata) . '&email=' . base64_encode($userdetail->email) . '&gradedby=' . base64_encode($USER->email);

            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => 1,
            );
            $output = $curl->post($url, $postdataforum, $options);
        }

        if ($eventname == '\mod_quiz\event\attempt_reviewed') {
            // Quiz sync

            $eventdata = $events->get_data();

            $userid = $eventdata['userid'];
            $quizid = $eventdata['other']['quizid'];
            $activityid = $eventdata['contextinstanceid'];

            $sql = "SELECT email FROM {user} where id = ?";
            $userdetail = $DB->get_record_sql($sql, [$userid]);

            $sql = "SELECT * FROM {quiz_grades} where quiz = ? and userid = ? ";
            $quizdata = $DB->get_record_sql($sql, [$quizid, $userid]);

            $sql = "SELECT gradepass FROM {grade_items} where iteminstance = ? and courseid = ? ";
            $argrade = $DB->get_record_sql($sql, [$quizdata->lessonid, $eventdata['courseid']]);

            $passfail = 'passed';

            if (!empty($argrade) && !empty($argrade->gradepass)) {
                $passfail = 'failed';
                if ($lessionrec->grade >= $argrade->gradepass) {
                    $passfail = 'passed';
                }
            }

            $postdataquizgrade = '&quizdata=' . json_encode($quizdata) . '&activity_id=' . json_encode($activityid) . '&email=' . base64_encode($userdetail->email) . '&gradedby=' . base64_encode($USER->email);

            $url = $teamniourl . '/admin/sync_moodle_course/update_grade_quiz_grade';
            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => 1,
            );
            $output = $curl->post($url, $postdataquizgrade, $options);
        }

        if ($eventname == '\mod_lesson\event\essay_assessed') {
            // lesson sync

            $lessiongradedata = json_encode($events->get_data());

            $objecttable = $eventdata['objecttable'];
            $objectid = $eventdata['objectid'];
            $lessionrec = $events->get_record_snapshot($objecttable, $objectid);

            $sql = "SELECT email FROM {user} where id = ?";
            $userdetail = $DB->get_record_sql($sql, [$lessionrec->userid]);

            $sql = "SELECT gradepass FROM {grade_items} where iteminstance = ? and courseid = ? ";
            $argrade = $DB->get_record_sql($sql, [$lessionrec->lessonid, $eventdata['courseid']]);

            $passfail = 'passed';

            if (!empty($argrade) && !empty($argrade->gradepass)) {
                $passfail = 'failed';
                if ($lessionrec->grade >= $argrade->gradepass) {
                    $passfail = 'passed';
                }
            }

            $postdatalessiongrade = '&lession_grade_data=' . $lessiongradedata . '&grade=' . json_encode($lessionrec->grade) . '&late=' . json_encode($lessionrec->late) . '&completed=' . json_encode($lessionrec->completed) . '&email=' . base64_encode($userdetail->email) . '&gradedby=' . base64_encode($USER->email) . '&pass_fail=' . json_encode($passfail);

            $url = $teamniourl . '/admin/sync_moodle_course/update_grade_lession_grade';
            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => 1,
            );
            $output = $curl->post($url, $postdatalessiongrade, $options);
        }

        if ($eventname == '\mod_assign\event\submission_graded') {
            // assign sync

            $objecttable = $eventdata['objecttable'];
            $objectid = $eventdata['objectid'];
            $rec = $events->get_record_snapshot($objecttable, $objectid);
            $assigndatamain = json_encode($rec);
            $assigndatacommon = json_encode($events->get_data());

            $sql = "SELECT email FROM {user} where id = ?";
            $userdetail = $DB->get_record_sql($sql, [$rec->userid]);

            $postdataassigngrade = '&assign_data_main=' . $assigndatamain . '&assign_data_common=' . $assigndatacommon . '&email=' . base64_encode($userdetail->email) . '&gradedby=' . base64_encode($USER->email);

            $url = $teamniourl . '/admin/sync_moodle_course/update_grade_assign_submit';
            $curl = new curl;
            $options = array(
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => 1,
            );
            $output = $curl->post($url, $postdataassigngrade, $options);
        }

        if ($eventname == '\core\event\grade_item_updated' || $eventname == '\core\event\grade_item_created') {
            $itemdata = $events->get_grade_item();

            $itemdataonly = array(
                'courseid' => $itemdata->courseid,
                'categoryid' => $itemdata->categoryid,
                'item_category' => $itemdata->item_category,
                'parent_category' => $itemdata->parent_category,
                'itemname' => $itemdata->itemname,
                'itemtype' => $itemdata->itemtype,
                'itemmodule' => $itemdata->itemmodule,
                'iteminstance' => $itemdata->iteminstance,
                'itemnumber' => $itemdata->itemnumber,
                'iteminfo' => $itemdata->iteminfo,
                'idnumber' => $itemdata->idnumber,
                'calculation_normalized' => $itemdata->calculation_normalized,
                'formula' => $itemdata->formula,
                'gradetype' => $itemdata->gradetype,
                'grademax' => $itemdata->grademax,
                'grademin' => $itemdata->grademin,
                'scaleid' => $itemdata->scaleid,
                'scale' => $itemdata->scale,
                'outcomeid' => $itemdata->outcomeid,
                'gradepass' => $itemdata->gradepass,
                'multfactor' => $itemdata->multfactor,
                'plusfactor' => $itemdata->plusfactor,
                'aggregationcoef' => $itemdata->aggregationcoef,
                'aggregationcoef2' => $itemdata->aggregationcoef2,
                'sortorder' => $itemdata->sortorder,
                'display' => $itemdata->display,
                'decimals' => $itemdata->decimals,
                'locked' => $itemdata->locked,
                'locktime' => $itemdata->locktime,
                'needsupdate' => $itemdata->needsupdate,
                'weightoverride' => $itemdata->weightoverride,
                'dependson_cache' => $itemdata->dependson_cache,
                'optional_fields' => json_encode($itemdata->optional_fields),
                'id' => $itemdata->id,
                'timecreated' => $itemdata->timecreated,
                'timemodified' => $itemdata->timemodified,
                'hidden' => $itemdata->hidden,
            );
            $grededata = '&item_data=' . json_encode($itemdataonly);

            $gredegradedatastring = '';

            if ($itemdata->itemtype == 'category' && !empty($itemdata->iteminstance)) {
                $gradecategorydata = $DB->get_records_sql("select * from {grade_categories} where id = '$itemdata->iteminstance' ");
                $gradecategory = '&grade_category=' . json_encode($gradecategorydata);
            }
        } else {
            $grededata = '';
            $gredegradedatastring = '';
            $gradecategory = '';
        }

        session_start();
        $gradehistoryid = $_SESSION['gradehistoryid'];
        $gradegradesid = $_SESSION['gradegradesid'];
        $coursecompleteid = $_SESSION['coursecompleteid'];

        $sql = "SELECT ggh.*,u.email  FROM {grade_grades} ggh left join {user} u on ggh.userid = u.id where ggh.id > ? ";
        $gradegradesdata = $DB->get_records_sql($sql, [$gradegradesid]); 

        $sql = "SELECT ggh.*,u.email  FROM {grade_grades} ggh left join {user} u on ggh.userid = u.id where ggh.id > ? ";
        $gradegradesdata = $DB->get_records_sql($sql, [$gradegradesid]);

        $sql = "SELECT cc.*,u.email  FROM {course_completions} cc left join {user} u on cc.userid = u.id where cc.id > ? ";
        $coursecompletedata = $DB->get_records_sql($sql, [$coursecompleteid]);

        $postdata = '&coursecompletedata=' . json_encode($coursecompletedata) . '&gradegradesdata=' . json_encode($gradegradesdata) . '&gradehistorydata=' . json_encode($gradehistorydata) . '&moodle_user_id=' . $userid . '&course_id=' . $courseid . '&activity_id=' .

        $contextinstanceid . "&mod_name=" . $component . "&user_email=" . base64_encode($USER->email) .

            "&action=" . $action . '&detail=' . $alldetail . '&event_name=' . $eventname . $grededata . $gredegradedatastring . $gradecategory;

        $url = $teamniourl . '/admin/sync_moodle_course/update_viewed_log';
        $curl = new curl;
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => 1,
        );
        $output = $curl->post($url, $postdata, $options); 

        if (!empty($output)) {
            $outputarr = explode('&', $output);
            $_SESSION['gradegradesid'] = $outputarr[0];
            $_SESSION['gradehistoryid'] = $outputarr[1];
            $_SESSION['coursecompleteid'] = $outputarr[2];
        }
    }
}
