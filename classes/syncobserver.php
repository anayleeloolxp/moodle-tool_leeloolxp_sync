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
     * Triggered when course completed.
     *
     * @param \core\event\course_module_completion_updated $event
     */
    public static function completion_updated(\core\event\course_module_completion_updated $event) {

        global $DB;

        global $CFG;

        require_once($CFG->dirroot . '/lib/filelib.php');

        $moduleid = $event->contextinstanceid;

        $userid = $event->userid;

        $completionstate = $event->other['completionstate'];

        $user = $DB->get_record('user', array('id' => $userid));

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
            return true;
        }

        $infoteamnio = json_decode($output);

        if ($infoteamnio->status != 'false') {
            $teamniourl = $infoteamnio->data->install_url;

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
        } else {

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

        $data = $event->get_data();

        global $DB;

        global $CFG;

        require_once($CFG->dirroot . '/lib/filelib.php');

        $user = core_user::get_user($data['relateduserid'], '*', MUST_EXIST);

        $email = str_replace("'", '', $user->email);

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
            return true;
        }

        $infoteamnio = json_decode($output);

        $countries = get_string_manager()->get_list_of_countries();

        if ($infoteamnio->status != 'false') {
            $teamniourl = $infoteamnio->data->install_url;

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
        }
    }

    /**
     * Triggered when group member added.
     *
     * @param \core\event\group_member_added $events
     */
    public static function group_member_added(\core\event\group_member_added $events) {

        global $CFG;

        require_once($CFG->dirroot . '/lib/filelib.php');

        $group = $events->get_record_snapshot('groups', $events->objectid);

        $user = core_user::get_user($events->relateduserid, '*', MUST_EXIST);

        $courseid = str_replace("'", '', $courseid);

        $groupname = str_replace("'", '', $group->name);

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
            return true;
        }

        $infoteamnio = json_decode($output);

        if ($infoteamnio->status != 'false') {
            $teamniourl = $infoteamnio->data->install_url;

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
        } else {

            return true;
        }
    }

    /**
     * Triggered when new role assigned to user.
     *
     * @param \core\event\role_assigned $enrolmentdata
     */
    public static function role_assign(\core\event\role_assigned $enrolmentdata) {

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
            return true;
        }

        $infoteamnio = json_decode($output);

        if ($infoteamnio->status != 'false') {
            $teamniourl = $infoteamnio->data->install_url;
        } else {

            $teamniourl = '';

            return false;
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
    }
}
