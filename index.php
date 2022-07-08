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

define('NO_OUTPUT_BUFFERING', true);

require(__DIR__ . '/../../../config.php');
global $CFG;
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/lib/filelib.php');

global $DB;

$reqcourseid = optional_param('course_id', null, PARAM_RAW);
$reqredirect = optional_param('redirect', null, PARAM_RAW);
$reqcourseid1 = optional_param('courseid', null, PARAM_RAW);
$reqaction = optional_param('action', null, PARAM_RAW);
$reqredirecthidden = optional_param('redirecthidden', null, PARAM_RAW);
$reqquizsync = optional_param_array('quiz_sync', null, PARAM_RAW);
$reqallactivities = optional_param_array('all_activities', null, PARAM_RAW);
$reqsyncactivities = optional_param('sync_activities', null, PARAM_RAW);
$reqid = optional_param('id', null, PARAM_RAW);
$requnsyncid = optional_param('unsync_id', null, PARAM_RAW);
$reqcourseidresync = optional_param('courseid_resync', null, PARAM_RAW);
$reqresync = optional_param('resync', null, PARAM_RAW);
$reqactivityname = optional_param('activity_name', null, PARAM_RAW);
$reactivityid = optional_param('activity_id', null, PARAM_RAW);
$reqresyncactivity = optional_param('resync_activity', null, PARAM_RAW);
$reqsyncategory = optional_param('syncategory', null, PARAM_RAW);

require_login();

admin_externalpage_setup('toolleeloolxp_sync');

$PAGE->set_context(context_system::instance());

$PAGE->set_url('/admin/tool/leeloolxp_sync/index.php');

$PAGE->set_title(get_string('pluginname', 'tool_leeloolxp_sync'));

$postcourses = optional_param('course', null, PARAM_RAW);

$activityset = optional_param('sync_activity_resouce', null, PARAM_RAW);

$msg = '';

$configtoolleeloolxpsync = get_config('tool_leeloolxp_sync');

$liacnsekey = $configtoolleeloolxpsync->leeloolxp_synclicensekey;

$curl = new curl;

$postdata = array('license_key' => $liacnsekey);

$url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';

$options = array(

    'CURLOPT_RETURNTRANSFER' => true,

    'CURLOPT_HEADER' => false,

    'CURLOPT_POST' => count($postdata),

);

$output = $curl->post($url, $postdata, $options);

if (!$output = $curl->post($url, $postdata, $options)) {
    $urltogo = $CFG->wwwroot . '/admin/search.php';
    redirect($urltogo, get_string('invalidsynclicensekey', 'tool_leeloolxp_sync'), 1);
    return true;
}

$infoleeloolxp = json_decode($output);

if ($infoleeloolxp->status != 'false') {
    $teamniourl = $infoleeloolxp->data->install_url;
} else {
    $urltogo = $CFG->wwwroot . '/admin/search.php';
    redirect($urltogo, get_string('invalidsynclicensekey', 'tool_leeloolxp_sync'), 1);
    return true;
}

if (isset($reqsyncategory) && $reqsyncategory == '1') {
    $courseid = $reqcourseid1;

    $categorydata = $DB->get_records_sql("SELECT * FROM {course_categories}");
    if (!empty($categorydata)) {
        $post['cat_data'] = json_encode($categorydata);
        $url = $teamniourl . '/admin/sync_moodle_course/update_insert_categories';

        $curl = new curl;

        $options = array(

            'CURLOPT_RETURNTRANSFER' => true,

            'CURLOPT_HEADER' => false,

            'CURLOPT_POST' => count($post),
            'CURLOPT_HTTPHEADER' => array(
                'Leeloolxptoken: ' . get_config('local_leeloolxpapi')->leelooapitoken . ''
            )
        );

        $curl->post($url, $post, $options);

        $msg = get_string('sychronizationed_success', 'tool_leeloolxp_sync');
    } else {
        $msg = get_string('sychronizationed_cat_not_found', 'tool_leeloolxp_sync');
    }

    $courseid = $reqcourseid1;

    if (isset($reqredirect)) {
        if ($reqredirect == 'courseview') {
            $urltogo = $CFG->wwwroot . '/course/view.php?id=' . $courseid . '&sync=1';

            redirect($urltogo);
        }
    }
}

if (isset($reqaction) && $reqaction == 'coursesyncfrmblock') {
    $courseid = $reqcourseid1;

    $coursedetails = $DB->get_record('course', array('id' => $courseid));

    $table = 'course_sections';

    $sections = $DB->get_records($table, array('course' => $courseid));

    $alldata = array();

    $quizarr = array();

    $courseaction = get_course($courseid);

    $modinfo = get_fast_modinfo($courseaction);

    if (!empty($sections)) {
        foreach ($sections as $sectionkey => $sectionsdetails) {
            if ($sectionsdetails->name == '' && $sectionsdetails->section != 0) {
                $sectionsdetails->name = get_string('topic', 'tool_leeloolxp_sync') . $sectionsdetails->section;
            }

            $sequence = $sectionsdetails->sequence;

            $modulescourse = $DB->get_records_sql("select *
                from {course_modules}
                where section = ?
                ORDER BY ID", [$sectionsdetails->id]);

            if (!empty($modulescourse)) {
                foreach ($modulescourse as $coursemoduledetails) {
                    $moduleid = $coursemoduledetails->module;

                    $instance = $coursemoduledetails->instance;

                    $completionexpected = $coursemoduledetails->completionexpected;

                    $modules = $DB->get_records("modules", array('id' => $moduleid));

                    if (!empty($modules)) {
                        foreach ($modules as $key => $value) {
                            $tbl = $value->name;

                            $moduledetail = $DB->get_records($tbl, array('id' => $instance));

                            if (!empty($moduledetail)) {
                                foreach ($moduledetail as $key => $valuefinal) {
                                    if ($tbl == 'lesson') {
                                        if ($valuefinal->available != 0) {
                                            $activitystartdates = $valuefinal->available;
                                        } else {
                                            $activitystartdates = $coursedetails->startdate;
                                        }

                                        if ($valuefinal->deadline != 0) {
                                            $activityenddatess = $valuefinal->deadline;
                                        } else {
                                            if ($completionexpected != 0) {
                                                $activityenddatess = $completionexpected;
                                            } else {
                                                $activityenddatess = $coursedetails->enddate;
                                            }
                                        }
                                    } else if ($tbl == 'quiz') {
                                        if ($valuefinal->timeopen != 0) {
                                            $activitystartdates = $valuefinal->timeopen;
                                        } else {
                                            $activitystartdates = $coursedetails->startdate;
                                        }

                                        if ($valuefinal->timeclose != 0) {
                                            $activityenddatess = $valuefinal->timeclose;
                                        } else {
                                            if ($completionexpected != 0) {
                                                $activityenddatess = $completionexpected;
                                            } else {
                                                $activityenddatess = $coursedetails->enddate;
                                            }
                                        }
                                    } else if ($tbl == 'assign') {
                                        if ($valuefinal->allowsubmissionsfromdate != 0) {
                                            $activitystartdates = $valuefinal->allowsubmissionsfromdate;
                                        } else {
                                            $activitystartdates = $coursedetails->startdate;
                                        }

                                        if ($valuefinal->duedate != 0) {
                                            $activityenddatess = $valuefinal->duedate;
                                        } else {
                                            if ($completionexpected != 0) {
                                                $activityenddatess = $completionexpected;
                                            } else {
                                                $activityenddatess = $coursedetails->enddate;
                                            }
                                        }
                                    } else if ($tbl == 'chat') {
                                        if ($valuefinal->chattime != 0) {
                                            $activitystartdates = $valuefinal->chattime;
                                        } else {
                                            $activitystartdates = $coursedetails->startdate;
                                        }

                                        if ($valuefinal->chattime != 0) {
                                            $activityenddatess = $valuefinal->chattime;
                                        } else {
                                            if ($completionexpected != 0) {
                                                $activityenddatess = $completionexpected;
                                            } else {
                                                $activityenddatess = $coursedetails->enddate;
                                            }
                                        }
                                    } else if ($tbl == 'choice') {
                                        if ($valuefinal->timeopen != 0) {
                                            $activitystartdates = $valuefinal->timeopen;
                                        } else {
                                            $activitystartdates = $coursedetails->startdate;
                                        }

                                        if ($valuefinal->timeclose != 0) {
                                            $activityenddatess = $valuefinal->timeclose;
                                        } else {
                                            if ($completionexpected != 0) {
                                                $activityenddatess = $completionexpected;
                                            } else {
                                                $activityenddatess = $coursedetails->enddate;
                                            }
                                        }
                                    } else if ($tbl == 'data') {
                                        if ($valuefinal->timeavailablefrom != 0) {
                                            $activitystartdates = $valuefinal->timeavailablefrom;
                                        } else {
                                            $activitystartdates = $coursedetails->startdate;
                                        }

                                        if ($valuefinal->timeavailableto != 0) {
                                            $activityenddatess = $valuefinal->timeavailableto;
                                        } else {
                                            if ($completionexpected != 0) {
                                                $activityenddatess = $completionexpected;
                                            } else {
                                                $activityenddatess = $coursedetails->enddate;
                                            }
                                        }
                                    } else if ($tbl == 'feedback') {
                                        if ($valuefinal->timeopen != 0) {
                                            $activitystartdates = $valuefinal->timeopen;
                                        } else {
                                            $activitystartdates = $coursedetails->startdate;
                                        }

                                        if ($valuefinal->timeclose != 0) {
                                            $activityenddatess = $valuefinal->timeclose;
                                        } else {
                                            if ($completionexpected != 0) {
                                                $activityenddatess = $completionexpected;
                                            } else {
                                                $activityenddatess = $coursedetails->enddate;
                                            }
                                        }
                                    } else if ($tbl == 'forum') {
                                        if ($valuefinal->duedate != 0) {
                                            $activitystartdates = $valuefinal->duedate;
                                        } else {
                                            $activitystartdates = $coursedetails->startdate;
                                        }

                                        if ($valuefinal->cutoffdate != 0) {
                                            $activityenddatess = $valuefinal->cutoffdate;
                                        } else {
                                            if ($completionexpected != 0) {
                                                $activityenddatess = $completionexpected;
                                            } else {
                                                $activityenddatess = $coursedetails->enddate;
                                            }
                                        }
                                    } else if ($tbl == 'leeloolxpvc') {
                                        if ($valuefinal->timeopen != 0) {
                                            $activitystartdates = $valuefinal->timeopen;
                                        } else {
                                            $activitystartdates = $coursedetails->startdate;
                                        }

                                        if ($valuefinal->timeopen != 0) {
                                            $activityenddatess = $valuefinal->timeopen;
                                        } else {
                                            if ($completionexpected != 0) {
                                                $activityenddatess = $completionexpected;
                                            } else {
                                                $activityenddatess = $coursedetails->enddate;
                                            }
                                        }
                                    } else if ($tbl == 'workshop') {
                                        if ($valuefinal->submissionstart != 0) {
                                            $activitystartdates = $valuefinal->submissionstart;
                                        } else {
                                            $activitystartdates = $coursedetails->startdate;
                                        }

                                        if ($valuefinal->submissionend != 0) {
                                            $activityenddatess = $valuefinal->submissionend;
                                        } else {
                                            if ($completionexpected != 0) {
                                                $activityenddatess = $completionexpected;
                                            } else {
                                                $activityenddatess = $coursedetails->enddate;
                                            }
                                        }
                                    } else if ($tbl == 'scorm') {
                                        if ($valuefinal->timeopen != 0) {
                                            $activitystartdates = $valuefinal->timeopen;
                                        } else {
                                            $activitystartdates = $coursedetails->startdate;
                                        }

                                        if ($valuefinal->timeclose != 0) {
                                            $activityenddatess = $valuefinal->timeclose;
                                        } else {
                                            if ($completionexpected != 0) {
                                                $activityenddatess = $completionexpected;
                                            } else {
                                                $activityenddatess = $coursedetails->enddate;
                                            }
                                        }
                                    } else {

                                        $activitystartdates = $coursedetails->startdate;

                                        if ($completionexpected != 0) {
                                            $activityenddatess = $completionexpected;
                                        } else {
                                            $activityenddatess = $coursedetails->enddate;
                                        }
                                    }

                                    $activityids = $DB->get_record(
                                        'course_modules',

                                        array('instance' => $instance, 'module' => $moduleid)
                                    );

                                    $alreadyenabled = $DB->get_record_sql("SELECT id FROM

                                        {tool_leeloolxp_sync}

                                        where activityid = ? and enabled = ? limit ?", [$activityids->id, 1, 1]);

                                    $enabled = false;
                                    $sectiondataa = $DB->get_record_sql("SELECT section FROM {course_modules}
                                            WHERE id = ? ", [$activityids->id]);

                                    if (!empty($alreadyenabled)) {
                                        $enabled = true;
                                    }

                                    $cm = $modinfo->cms[$activityids->id];

                                    if ($cm) {

                                        if ($cm->modname == 'quiz') {
                                            $quizid = $cm->get_course_module_record()->instance;
                                            $quizdata = $DB->get_record('quiz', array('id' => $quizid), '*', MUST_EXIST);

                                            if (isset($quizdata->quiztype)) {
                                                if ($quizdata->quiztype == 'discover') {
                                                    $iconurl = $CFG->wwwroot . '/local/leeloolxptrivias/pix/Discover_on.png';
                                                } else if ($quizdata->quiztype == 'exercises') {
                                                    $iconurl = $CFG->wwwroot . '/local/leeloolxptrivias/pix/Studycase_on.png';
                                                } else if ($quizdata->quiztype == 'trivias') {
                                                    $iconurl = $CFG->wwwroot . '/local/leeloolxptrivias/pix/Trivia_on.png';
                                                } else if ($quizdata->quiztype == 'assessments') {
                                                    $iconurl = $CFG->wwwroot . '/local/leeloolxptrivias/pix/Assessments_on.png';
                                                } else if ($quizdata->quiztype == 'quest') {
                                                    $iconurl = $CFG->wwwroot . '/local/leeloolxptrivias/pix/Quest_on.png';
                                                } else if ($quizdata->quiztype == 'mission') {
                                                    $iconurl = $CFG->wwwroot . '/local/leeloolxptrivias/pix/Mission_on.png';
                                                } else if ($quizdata->quiztype == 'duels') {
                                                    $iconurl = $CFG->wwwroot . '/local/leeloolxptrivias/pix/Duelos_on.png';
                                                } else {
                                                    $iconurl = $cm->get_icon_url() . '?default';
                                                }
                                            } else {
                                                $iconurl = $cm->get_icon_url() . '?default';
                                            }
                                        } else {
                                            $iconurl = $cm->get_icon_url();
                                        }
                                    } else {

                                        $iconurl = '';
                                    }

                                    if (isset($valuefinal->quiztype)) {
                                        $quiztype = $valuefinal->quiztype;
                                    } else {
                                        $quiztype = '';
                                    }

                                    $difficulty = '1';

                                    $querystring = $coursedetails->fullname . "$$" .

                                        $sectionsdetails->name . "$$" . $valuefinal->name . "$$" .

                                        $activityids->id . "$$" . $courseid . "$$" .

                                        $sectionsdetails->summary . "$$" .

                                        strip_tags(
                                            $valuefinal->intro . "$$" .

                                                $activitystartdates . "$$" .

                                                $activityenddatess . "$$" . $tbl . "$$" . $iconurl . "$$" .
                                                $quiztype . "$$" . $difficulty
                                        )
                                        . "$$" .

                                        $sectiondataa->section . "$$" . base64_encode(json_encode($moduledetail));

                                    $alldata[] = $querystring;

                                    if (isset($valuefinal->questionsperpage)) {
                                        $quizarr[] = $activityids->id;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    $i = 0;

    $moodleuserarray = array();

    $moodleuserstudentarray = array();

    $moodleuserteacherarray = array();

    $moodleuserteacherarrayy = array();

    $moodleuserstudentarrayy = array();

    $groupdata = '';

    $useridscohort = '';

    $cohortname = '';

    foreach ($alldata as $key => $value) {
        $activityidmoodlearr = array();

        $activityset = str_replace("'", '', $value);

        $activityidarr = explode('$$', $activityset);

        $courseidagain = $activityidarr[4];

        $activitystartdate = $activityidarr[7];

        $activityenddate = $activityidarr[8];

        $activitytype = $activityidarr[9];

        $activityurl = $activityidarr[10];

        $quiztypesync = $activityidarr[11];

        $quizdiffsync = $activityidarr[12];

        $arfulldata = $activityidarr[14];

        if ($CFG->dbtype == 'mysqli') {

            $sql = "SELECT cs.id cid , cs.name cname , cs.section csection , cs.sequence csequence, cfo.*,
            (Select id from mdlwf_course_sections where course = ? and section = cfo.value) as parentsectionid
            FROM mdlwf_course_sections as cs left join mdlwf_course_format_options as cfo
            on cfo.sectionid = cs.id and cfo.name = 'parent' WHERE cs.course = ?
            AND cs.section != 0 GROUP BY cs.id ORDER BY csection ASC";

            // AND ( cfo.name LIKE 'parent' OR cfo.name LIKE 'hiddensections' OR cfo.name LIKE 'coursedisplay').
            $coursehierarchy = $DB->get_records_sql($sql, [$courseidagain, $courseidagain]);
        } else {
            $sql = "SELECT DISTINCT ON (cs.id) cs.id cid , cs.name cname , cs.section csection ,
            cs.sequence csequence, cfo.value,  cfo.courseid ,cfo.format,cfo.sectionid,cfo.name ,
            cfo.value ,cfo.id id ,
            (Select id from {course_sections} where course = ? and section = CAST(cfo.value as INT)) as parentsectionid
            from   {course_sections}  cs
            left join {course_format_options} cfo ON cfo.sectionid = cs.id and cfo.name = 'parent'
            WHERE course = ? and
            cs.section != 0  ORDER BY cs.id ASC";
            $coursehierarchy = $DB->get_records_sql($sql, [$courseidagain, $courseidagain]);
            if (!empty($coursehierarchy)) {
                usort($coursehierarchy, function ($a, $b) {
                    return $a->csection - $b->csection;
                });
            }
        }

        if (empty($coursehierarchy)) {

            $sql = "SELECT id cid,name cname,section csection ,sequence csequence,id sectionid
            FROM {course_sections} cs
            WHERE course = ? AND section != 0 AND section != '0'";

            $coursehierarchy = $DB->get_records_sql($sql, [$courseidagain]);
        }

        $groupdata = $DB->get_records_sql("SELECT * FROM {groups} groups where groups.courseid = ?", [$courseidagain]);

        if ($i == '0') {
            $enrolleduser = $DB->get_records_sql("SELECT u.*, ue.id, ue.timeend, ue.timestart, e.courseid,
            ue.userid, e.status enrol_status ,
            e.sortorder  enrol_sortorder , e.enrol
            enrolmethod, (ue.timecreated)
            FROM {user_enrolments} ue
            JOIN {enrol} e ON e.id = ue.enrolid
            AND e.status = '0' JOIN {user} u ON u.id = ue.userid
            AND u.deleted = '0'
            Where e.courseid = ?", [$courseidagain]);

            foreach ($enrolleduser as $key => $moodeluservalue) {

                $useridscohort .= $moodeluservalue->userid . ',';

                $sql = "SELECT {role}.shortname shortname, {role}.id roleid

                FROM {role_assignments} LEFT JOIN {user_enrolments} ON {role_assignments}.userid = {user_enrolments}.userid

                LEFT JOIN {role} ON {role_assignments}.roleid = {role}.id LEFT JOIN {context}

                ON {context}.id = {role_assignments}.contextid LEFT JOIN {enrol} ON {enrol}.courseid = {context}.instanceid

                AND {user_enrolments}.enrolid = {enrol}.id

                WHERE  {user_enrolments}.userid = ? AND {enrol}.courseid = ?";

                $roleresult = $DB->get_records_sql($sql, [$moodeluservalue->userid, $courseidagain]);

                $userrole = '';

                foreach ($roleresult as $rolekey => $rolevalues) {
                    $userrole = $rolevalues->shortname;

                    $roleid = $rolevalues->roleid;
                }

                $usertype = '';

                $teamniorole = '';

                $teamniousertype = '';

                $userdegree = $DB->get_record_sql("SELECT DISTINCT data FROM {user_info_data}

                left join {user_info_field} on {user_info_data}.fieldid = {user_info_field}.id

                where {user_info_field}.shortname = 'degree' and {user_info_data}.userid = ?", [$moodeluservalue->userid]);

                $userdegreename = @$userdegree->data;

                $userdepartment = $moodeluservalue->department;

                $userinstitution = $moodeluservalue->institution;

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

                    $studentdbsetarr[$si] = $mstudentrole . "_" .

                        $mstudentinstitution . "_" . $mstudentdepartment . "_" . $mstudentdegree;
                }

                $userstudentinfo = $roleid . "_" . $userinstitution . "_" .

                    $userdepartment . "_" . $userdegreename;

                $matchedvalue = array_search($userstudentinfo, $studentdbsetarr);

                if ($matchedvalue) {
                    $tcolnamestudent = 'student_position_t_' . $matchedvalue;

                    $teamniorole = $ssopluginconfig->$tcolnamestudent;

                    $usertype = 'student';
                } else {

                    $teachernumcombinations = $ssopluginconfig->teacher_num_combination;

                    $teachernumcombinationsval = $teachernumcombinations;

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
                        if (empty($mteacherinstitution)) {
                            $mteacherinstitution = '';
                        }
                        if (empty($mteacherdepartment)) {
                            $mteacherdepartment = '';
                        }
                        if (empty($mteacherdegree)) {
                            $mteacherdegree = '';
                        }
                        $teacherdbsetarr[$si] = $mteacherrole . "_" .

                            $mteacherinstitution . "_" . $mteacherdepartment . "_" . $mteacherdegree;
                    }

                    $userteacherinfo = $roleid . "_" . $userinstitution . "_" .

                        $userdepartment . "_" . $userdegreename;

                    $matchedvalueteacher = array_search($userteacherinfo, $teacherdbsetarr);

                    if ($matchedvalueteacher) {
                        $tcolnameteacher = 'teacher_position_t_' . $matchedvalueteacher;

                        $teamniorole = $ssopluginconfig->$tcolnameteacher;

                        $usertype = 'teacher';
                    } else {

                        $usertype = 'student';

                        $teamniorole = $ssopluginconfig->default_student_position;
                    }
                }

                if ($usertype == 'student') {
                    $cancreateuser = $ssopluginconfig->web_new_user_student;

                    $userdesignation = $teamniorole;

                    $userapproval = $ssopluginconfig->required_aproval_student;
                } else {

                    if ($usertype == 'teacher') {
                        $cancreateuser = $ssopluginconfig->web_new_user_teacher;

                        $userdesignation = $teamniorole;

                        $userapproval = $ssopluginconfig->required_aproval_teacher;
                    }
                }

                $enrolleduserid = $moodeluservalue->userid;

                $groupsname = $DB->get_records_sql("SELECT * FROM {groups} groups left join

                {groups_members} on {groups_members}.groupid = groups.id

                where groups.courseid = ? and {groups_members}.userid= ?", [$courseidagain, $enrolleduserid]);

                $usergroupsname = array();

                if (!empty($groupsname)) {
                    foreach ($groupsname as $key => $gvalue) {
                        $usergroupsname[] = $gvalue->name;
                    }
                }

                $academicprogramfield = $DB->get_record_sql("SELECT DISTINCT data FROM {user_info_field} field
                left join {user_info_data} on {user_info_data}.fieldid = field.id
                where field.name LIKE '%Academic program%'
                and {user_info_data}.userid= ?", [$enrolleduserid]);

                if (isset($academicprogramfield->data) && $academicprogramfield->data != '') {
                    $academicprogram = $academicprogramfield->data;
                } else {
                    $academicprogram = '';
                }

                $usergroupsname = implode(',', $usergroupsname);

                $moodleurlpic = new moodle_url('/user/pix.php/' . $moodeluservalue->id . '/f.jpg');

                if ($usertype == 'student') {

                    $tempdatacon = $DB->get_record_sql(" SELECT id
                    FROM {context}
                    WHERE instanceid = ?
                    AND depth = '3'
                    ORDER BY id DESC ", [$courseidagain]);
                    if (!empty($tempdatacon)) {
                        $temptdatastr = $tempdatacon->id;
                    } else {
                        $temptdatastr = 0;
                    }
                    $userrolename = 'student';

                    if (!empty($temptdatastr)) {
                        $userrolename = $DB->get_record_sql(" SELECT r.shortname FROM {role_assignments} ra
                        left join {role} r on r.id = ra.roleid
                        WHERE contextid IN (?)
                        and userid = ? ", [$temptdatastr, $moodeluservalue->userid]);
                        $userrolename = $userrolename->shortname;
                    }

                    $lastlogin = date('Y-m-d h:i:s', $moodeluservalue->lastlogin);

                    $moodleuserstudentarrayy[] = array(

                        'username' => base64_encode($moodeluservalue->username),

                        'fullname' => ucfirst($moodeluservalue->firstname) . " " .

                            ucfirst($moodeluservalue->middlename) . " " . ucfirst($moodeluservalue->lastname),

                        'user_pic_moodle_url' => $moodleurlpic,

                        'email' => base64_encode($moodeluservalue->email),

                        'course_role' => $userrolename,

                        'city' => $moodeluservalue->city,

                        'country' => $moodeluservalue->country,

                        'timezone' => $moodeluservalue->timezone,

                        'firstnamephonetic' => $moodeluservalue->firstnamephonetic,

                        'lastnamephonetic' => $moodeluservalue->lastnamephonetic,

                        'middlename' => $moodeluservalue->middlename,

                        'alternatename' => $moodeluservalue->alternatename,

                        'icq' => $moodeluservalue->icq,

                        'skype' => $moodeluservalue->skype,

                        'aim' => $moodeluservalue->aim,

                        'yahoo' => $moodeluservalue->yahoo,

                        'msn' => $moodeluservalue->msn,

                        'idnumber' => $moodeluservalue->idnumber,

                        'institution' => $moodeluservalue->institution,

                        'department' => $moodeluservalue->department,

                        'phone' => $moodeluservalue->phone1,

                        'moodle_phone' => $moodeluservalue->phone2,

                        'adress' => $moodeluservalue->address,

                        'firstaccess' => $moodeluservalue->firstaccess,

                        'lastaccess' => $moodeluservalue->lastaccess,

                        'lastlogin' => $lastlogin,

                        'lastip' => $moodeluservalue->lastip,

                        'passwors' => $moodeluservalue->password,

                        'user_groups_name' => $usergroupsname,

                        'groups_data' => $groupsname,

                        'can_create_user' => $cancreateuser,

                        'designation' => $userdesignation,

                        'user_approval' => $userapproval,

                        'user_type' => $usertype,

                        'userrole' => $userrole,

                        'designation_id' => $userdesignation,

                        'enrol' => $moodeluservalue->enrolmethod,

                        'timeend' => $moodeluservalue->timeend,

                        'timestart' => $moodeluservalue->timestart,

                        'userid' => $moodeluservalue->userid,

                        'enrol_status' => $moodeluservalue->enrol_status,

                        'academicprogram' => $academicprogram,

                        'auth' => $moodeluservalue->auth,

                        'confirmed' => $moodeluservalue->confirmed,

                        'deleted' => $moodeluservalue->deleted,

                        'suspended' => $moodeluservalue->suspended,

                        'timecreated' => $moodeluservalue->timecreated,

                        'timemodified' => $moodeluservalue->timemodified,

                        'enrol_sortorder' => $moodeluservalue->enrol_sortorder
                    );
                } else {

                    $tempdatacon = $DB->get_record_sql(" SELECT id
                    FROM {context}
                    WHERE instanceid = ? AND depth = '3'
                    ORDER BY id DESC ", [$courseidagain]);
                    if (!empty($tempdatacon)) {
                        $temptdatastr = $tempdatacon->id;
                    } else {
                        $temptdatastr = 0;
                    }

                    $userrolename = 'student';

                    if (!empty($temptdatastr)) {
                        $userrolename = $DB->get_record_sql(" SELECT r.shortname
                        FROM {role_assignments} ra
                        left join {role} r on r.id = ra.roleid
                        WHERE contextid IN (?)
                        and userid = ? ", [$temptdatastr, $moodeluservalue->userid]);
                        $userrolename = $userrolename->shortname;
                    }

                    if ($usertype == 'teacher') {
                        $moodeluservalueteacher = $moodeluservalue;

                        $moodleuserteacherarrayy[] = array(
                            'username' => base64_encode($moodeluservalueteacher->username),

                            'fullname' => ucfirst($moodeluservalueteacher->firstname) . " " .

                                ucfirst($moodeluservalueteacher->middlename) . " " . ucfirst($moodeluservalueteacher->lastname),

                            'user_pic_moodle_url' => $moodleurlpic,

                            'email' => base64_encode($moodeluservalueteacher->email),

                            'course_role' => $userrolename,

                            'city' => $moodeluservalueteacher->city,

                            'country' => $moodeluservalueteacher->country,

                            'timezone' => $moodeluservalueteacher->timezone,

                            'firstnamephonetic' => $moodeluservalueteacher->firstnamephonetic,

                            'lastnamephonetic' => $moodeluservalueteacher->lastnamephonetic,

                            'middlename' => $moodeluservalueteacher->middlename,

                            'alternatename' => $moodeluservalueteacher->alternatename,

                            'icq' => $moodeluservalueteacher->icq,

                            'skype' => $moodeluservalueteacher->skype,

                            'aim' => $moodeluservalueteacher->aim,

                            'yahoo' => $moodeluservalueteacher->yahoo,

                            'msn' => $moodeluservalueteacher->msn,

                            'idnumber' => $moodeluservalue->idnumber,

                            'institution' => $moodeluservalueteacher->institution,

                            'department' => $moodeluservalueteacher->department,

                            'phone' => $moodeluservalueteacher->phone1,

                            'moodle_phone' => $moodeluservalueteacher->phone2,

                            'adress' => $moodeluservalueteacher->address,

                            'firstaccess' => $moodeluservalueteacher->firstaccess,

                            'lastaccess' => $moodeluservalueteacher->lastaccess,

                            'lastlogin' => $moodeluservalueteacher->lastlogin,

                            'lastip' => $moodeluservalueteacher->lastip,

                            'user_groups_name' => $usergroupsname,

                            'groups_data' => $groupsname,

                            'can_create_user' => $cancreateuser,

                            'designation' => $userdesignation,

                            'user_approval' => $userapproval,

                            'user_type' => $usertype,

                            'userrole' => $userrole,

                            'designation_id' => $userdesignation,

                            'enrol' => $moodeluservalue->enrolmethod,

                            'timeend' => $moodeluservalue->timeend,

                            'timestart' => $moodeluservalue->timestart,

                            'userid' => $moodeluservalue->userid,

                            'enrol_status' => $moodeluservalue->enrol_status,

                            'academicprogram' => $academicprogram,

                            'auth' => $moodeluservalue->auth,

                            'confirmed' => $moodeluservalue->confirmed,

                            'deleted' => $moodeluservalue->deleted,

                            'suspended' => $moodeluservalue->suspended,

                            'timecreated' => $moodeluservalue->timecreated,

                            'timemodified' => $moodeluservalue->timemodified,

                            'enrol_sortorder' => $moodeluservalue->enrol_sortorder
                        );
                    }
                }
            }
        }

        $activityid = $activityidarr[3];

        $modulegeneraldata = $DB->get_record('course_modules', array('id' => $activityid));

        $secctiondescription = $activityidarr[5];

        $activitydescription = $activityidarr[6];

        $coursedetailsagain = $DB->get_record('course', array('id' => $courseidagain));

        $useridscohort = chop($useridscohort, ",");

        if (!empty($useridscohort)) {

            $cohortdata = $DB->get_records_sql(" SELECT {cohort}.name
            FROM {cohort_members}
            left join {cohort} on {cohort}.id={cohort_members}.cohortid
            WHERE userid IN ($useridscohort) ");

            if (!empty($cohortdata)) {
                foreach ($cohortdata as $key => $value) {
                    $cohortname .= $value->name . ',';
                }
                $cohortname = chop($cohortname, ",");
            }
        }

        $groupname = '';

        $categorydata = $DB->get_records_sql("SELECT * FROM {course_categories} WHERE id = ?", [$coursedetailsagain->category]);

        $gradecategories = $DB->get_records_sql("SELECT *
        FROM {grade_categories}
        WHERE courseid = ?
        ORDER BY depth ASC ", [$courseidagain]);
        $gradeitems = $DB->get_records_sql("SELECT * FROM {grade_items} WHERE courseid = ?", [$courseidagain]);

        $tempdata = explode('$$', $activityset);

        $tagdata = [];

        if (!empty($tempdata[3])) {
            $itemidd = $tempdata[3];

            $tagdata = $DB->get_records_sql(
                "SELECT {tag}.* ,{user}.email ,{tag_instance}.contextid ,
            {tag_instance}.itemtype ,{tag_instance}.ordering ,
            {tag_instance}.tiuserid ,{tag_instance}.itemid
            FROM {tag_instance}
            join {tag} on {tag_instance}.tagid = {tag}.id
            join {user} ON {user}.id = {tag}.userid
            where {tag_instance}.itemid = ?",
                [$itemidd]
            );
        }

        $post = [

            'grade_categories' => json_encode($gradecategories),

            'grade_items' => json_encode($gradeitems),

            'tagdata' => base64_encode(json_encode($tagdata)),

            'moodle_users_students' => json_encode($moodleuserstudentarrayy),

            'moodle_users_teachers' => json_encode($moodleuserteacherarrayy),

            'course_section_activity' => $activityset,

            'coursehierarchy' => json_encode($coursehierarchy),

            'is_quiz_task' => 0,

            'group_name' => $groupname,

            'group_data' => json_encode($groupdata),

            'project_description' => $coursedetailsagain->summary,

            'subproject_description' => $secctiondescription,

            'task_description' => $activitydescription,

            'course_id' => $coursedetailsagain->id,

            'idnumber' => $coursedetailsagain->idnumber,

            'shortname' => $coursedetailsagain->shortname,

            'cohortname' => $cohortname,

            'category' => $coursedetailsagain->category,

            'visible' => $coursedetailsagain->visible,

            'cat_data' => json_encode($categorydata),

            'startdate' => $coursedetailsagain->startdate,

            'activity_start_date' => $activitystartdate,

            'activity_end_date' => $activityenddate,

            'enddate' => $coursedetailsagain->enddate,

            'activity_type' => $activitytype,

            'activity_url' => $activityurl,

            'quiztype' => $quiztypesync,

            'quizdiff' => $quizdiffsync,

            'arfulldata' => $arfulldata,

            'showdescription' => $modulegeneraldata->showdescription,

            'visible' => $modulegeneraldata->visible,

            'idnumber' => $modulegeneraldata->idnumber,

            'completion' => $modulegeneraldata->completion,

            'completionexpected' => $modulegeneraldata->completionexpected,

            'availability' => $modulegeneraldata->availability,

            'groupmode' => $modulegeneraldata->groupmode,

            'groupingid' => $modulegeneraldata->groupingid,

        ];

        $url = $teamniourl . '/admin/sync_moodle_course/index';

        $curl = new curl;

        $options = array(

            'CURLOPT_RETURNTRANSFER' => true,

            'CURLOPT_HEADER' => false,

            'CURLOPT_POST' => count($post),
            'CURLOPT_HTTPHEADER' => array(
                'Leeloolxptoken: ' . get_config('local_leeloolxpapi')->leelooapitoken . ''
            )
        );

        if (!$response = $curl->post($url, $post, $options)) {
            return true;
        }

        $response = json_decode($response, true);

        $courseid = $courseidagain;

        $sectionid = 0;

        if (!empty($response)) {
            foreach ($response as $key => $teamniotaskid) {
                if ($teamniotaskid != '0') {
                    $alreadyexistquery = $DB->get_record_sql("SELECT id FROM {tool_leeloolxp_sync}

                    where teamnio_task_id = ?", [$teamniotaskid]);

                    if (empty($alreadyexistquery)) {
                        $DB->execute("INSERT INTO {tool_leeloolxp_sync}

                        ( courseid, sectionid, activityid, enabled, teamnio_task_id, is_quiz)

                        VALUES ( ?, ?, ?, '1', ?,'0')", [$courseid, $sectionid, $activityid, $teamniotaskid]);
                    }

                    $msg = get_string('sychronizationed_success', 'tool_leeloolxp_sync');
                } else {

                    $activityidmoodlearr = $activityid;

                    $DB->execute("Update {tool_leeloolxp_sync}  set enabled = '1'

                    where activityid = ?", [$activityidmoodlearr]);

                    $msg = get_string('sychronizationed_success', 'tool_leeloolxp_sync');
                }
            }
        }

        $i++;
    }

    if (isset($quizarr)) {
        foreach ($quizarr as $value) {
            $DB->execute("Update {tool_leeloolxp_sync}  set is_quiz = '1'

            where activityid = ?", [$value]);

            $isqpost = [

                'activity_id' => $value,

            ];

            $url = $teamniourl . '/admin/sync_moodle_course/is_quiz_update';

            $curl = new curl;

            $options = array(

                'CURLOPT_RETURNTRANSFER' => true,

                'CURLOPT_HEADER' => false,

                'CURLOPT_POST' => count($isqpost),
                'CURLOPT_HTTPHEADER' => array(
                    'Leeloolxptoken: ' . get_config('local_leeloolxpapi')->leelooapitoken . ''
                )
            );

            if (!$response = $curl->post($url, $isqpost, $options)) {
                return true;
            }
        }
    }

    $urltogo = $CFG->wwwroot . '/course/view.php?id=' . $reqcourseid1 . '&sync=1';
    redirect($urltogo);
}

if (isset($reqresyncactivity)) {
    $activityid = $reactivityid;

    $tagsdata = $DB->get_records('tag_instance', array('itemid' => $activityid, 'itemtype' => 'course_modules'));

    $tagsfinaldata = [];

    if (!empty($tagsdata)) {
        foreach ($tagsdata as $keyy => $valueee) {
            $dbtagdata = $DB->get_record('tag', array('id' => $valueee->tagid));

            if (!empty($dbtagdata)) {
                $dbtagdata->contextid = $valueee->contextid;
                $tagsfinaldata[] = $dbtagdata;
            }
        }
    }

    $activityname = $reqactivityname;

    $syncactivitydescription = '';

    $courseid = $reqcourseid;

    $coursedetails = $DB->get_record('course', array('id' => $courseid)); // Fetch all exist course from.

    $coursename = $coursedetails->fullname;

    $table = 'course_sections'; // Section table name.

    $sections = $DB->get_records($table, array('course' => $courseid)); // Fetch sections of each course.

    $courseresyncactivity = get_course($courseid);

    $sectionsone = $sections;

    $modinfo = get_fast_modinfo($courseresyncactivity);

    if (!empty($sectionsone)) {
        foreach ($sectionsone as $sectionkey1 => $sectionsdetailss) {
            if ($sectionsdetailss->name != '') {
                $modulescourse = $DB->get_records(
                    "course_modules",

                    array('section' => $sectionsdetailss->id)
                ); // Fecth modules and instaced of modules.

                if (!empty($modulescourse)) {
                    foreach ($modulescourse as $coursemoduledetails) {
                        $moduleid = $coursemoduledetails->module;

                        $instance = $coursemoduledetails->instance;

                        $completionexpected = $coursemoduledetails->completionexpected;

                        $modules = $DB->get_records("modules", array('id' => $moduleid)); // Fetch modules for real table name.

                        if (!empty($modules)) {
                            foreach ($modules as $key => $value) {
                                $tbll = $value->name;

                                $moduledetail = $DB->get_records(
                                    $tbll,

                                    array('id' => $instance)
                                ); // Fetch activities and resources.

                                if (!empty($moduledetail)) {
                                    foreach ($moduledetail as $key => $valuefinal) {
                                        $activityids = $DB->get_record(
                                            'course_modules',

                                            array('instance' => $instance, 'module' => $moduleid)
                                        );

                                        $cm = $modinfo->cms[$activityids->id];

                                        if ($activityids->id == $activityid) {
                                            if (isset($valuefinal->intro)) {
                                                $syncactivitydescription = $valuefinal->intro;
                                            }

                                            if ($tbll == 'lesson') {
                                                if ($valuefinal->available != 0) {
                                                    $activitystartdates = $valuefinal->available;
                                                } else {
                                                    $activitystartdates = $coursedetails->startdate;
                                                }

                                                if ($valuefinal->deadline != 0) {
                                                    $activityenddates = $valuefinal->deadline;
                                                } else {
                                                    if ($completionexpected != 0) {
                                                        $activityenddates = $completionexpected;
                                                    } else {
                                                        $activityenddates = $coursedetails->enddate;
                                                    }
                                                }
                                            } else if ($tbll == 'quiz') {
                                                if ($valuefinal->timeopen != 0) {
                                                    $activitystartdates = $valuefinal->timeopen;
                                                } else {
                                                    $activitystartdates = $coursedetails->startdate;
                                                }

                                                if ($valuefinal->timeclose != 0) {
                                                    $activityenddates = $valuefinal->timeclose;
                                                } else {
                                                    if ($completionexpected != 0) {
                                                        $activityenddates = $completionexpected;
                                                    } else {
                                                        $activityenddates = $coursedetails->enddate;
                                                    }
                                                }
                                            } else if ($tbll == 'assign') {
                                                if ($valuefinal->allowsubmissionsfromdate != 0) {
                                                    $activitystartdates = $valuefinal->allowsubmissionsfromdate;
                                                } else {
                                                    $activitystartdates = $coursedetails->startdate;
                                                }

                                                if ($valuefinal->duedate != 0) {
                                                    $activityenddates = $valuefinal->duedate;
                                                } else {
                                                    if ($completionexpected != 0) {
                                                        $activityenddates = $completionexpected;
                                                    } else {
                                                        $activityenddates = $coursedetails->enddate;
                                                    }
                                                }
                                            } else if ($tbll == 'chat') {
                                                if ($valuefinal->chattime != 0) {
                                                    $activitystartdates = $valuefinal->chattime;
                                                } else {
                                                    $activitystartdates = $coursedetails->startdate;
                                                }

                                                if ($valuefinal->chattime != 0) {
                                                    $activityenddates = $valuefinal->chattime;
                                                } else {
                                                    if ($completionexpected != 0) {
                                                        $activityenddates = $completionexpected;
                                                    } else {
                                                        $activityenddates = $coursedetails->enddate;
                                                    }
                                                }
                                            } else if ($tbll == 'choice') {
                                                if ($valuefinal->timeopen != 0) {
                                                    $activitystartdates = $valuefinal->timeopen;
                                                } else {
                                                    $activitystartdates = $coursedetails->startdate;
                                                }

                                                if ($valuefinal->timeclose != 0) {
                                                    $activityenddates = $valuefinal->timeclose;
                                                } else {
                                                    if ($completionexpected != 0) {
                                                        $activityenddates = $completionexpected;
                                                    } else {
                                                        $activityenddates = $coursedetails->enddate;
                                                    }
                                                }
                                            } else if ($tbll == 'data') {
                                                if ($valuefinal->timeavailablefrom != 0) {
                                                    $activitystartdates = $valuefinal->timeavailablefrom;
                                                } else {
                                                    $activitystartdates = $coursedetails->startdate;
                                                }

                                                if ($valuefinal->timeavailableto != 0) {
                                                    $activityenddates = $valuefinal->timeavailableto;
                                                } else {
                                                    if ($completionexpected != 0) {
                                                        $activityenddates = $completionexpected;
                                                    } else {
                                                        $activityenddates = $coursedetails->enddate;
                                                    }
                                                }
                                            } else if ($tbll == 'feedback') {
                                                if ($valuefinal->timeopen != 0) {
                                                    $activitystartdates = $valuefinal->timeopen;
                                                } else {
                                                    $activitystartdates = $coursedetails->startdate;
                                                }

                                                if ($valuefinal->timeclose != 0) {
                                                    $activityenddates = $valuefinal->timeclose;
                                                } else {
                                                    if ($completionexpected != 0) {
                                                        $activityenddates = $completionexpected;
                                                    } else {
                                                        $activityenddates = $coursedetails->enddate;
                                                    }
                                                }
                                            } else if ($tbll == 'forum') {
                                                if ($valuefinal->duedate != 0) {
                                                    $activitystartdates = $valuefinal->duedate;
                                                } else {
                                                    $activitystartdates = $coursedetails->startdate;
                                                }

                                                if ($valuefinal->cutoffdate != 0) {
                                                    $activityenddates = $valuefinal->cutoffdate;
                                                } else {
                                                    if ($completionexpected != 0) {
                                                        $activityenddates = $completionexpected;
                                                    } else {
                                                        $activityenddates = $coursedetails->enddate;
                                                    }
                                                }
                                            } else if ($tbll == 'leeloolxpvc') {
                                                if ($valuefinal->timeopen != 0) {
                                                    $activitystartdates = $valuefinal->timeopen;
                                                } else {
                                                    $activitystartdates = $coursedetails->startdate;
                                                }

                                                if ($valuefinal->timeopen != 0) {
                                                    $activityenddates = $valuefinal->timeopen;
                                                } else {
                                                    if ($completionexpected != 0) {
                                                        $activityenddates = $completionexpected;
                                                    } else {
                                                        $activityenddates = $coursedetails->enddate;
                                                    }
                                                }
                                            } else if ($tbll == 'workshop') {
                                                if ($valuefinal->submissionstart != 0) {
                                                    $activitystartdates = $valuefinal->submissionstart;
                                                } else {
                                                    $activitystartdates = $coursedetails->startdate;
                                                }

                                                if ($valuefinal->submissionend != 0) {
                                                    $activityenddates = $valuefinal->submissionend;
                                                } else {
                                                    if ($completionexpected != 0) {
                                                        $activityenddates = $completionexpected;
                                                    } else {
                                                        $activityenddates = $coursedetails->enddate;
                                                    }
                                                }
                                            } else if ($tbl == 'scorm') {
                                                if ($valuefinal->timeopen != 0) {
                                                    $activitystartdates = $valuefinal->timeopen;
                                                } else {
                                                    $activitystartdates = $coursedetails->startdate;
                                                }

                                                if ($valuefinal->timeclose != 0) {
                                                    $activityenddates = $valuefinal->timeclose;
                                                } else {
                                                    if ($completionexpected != 0) {
                                                        $activityenddates = $completionexpected;
                                                    } else {
                                                        $activityenddates = $coursedetails->enddate;
                                                    }
                                                }
                                            } else {

                                                $activitystartdates = $coursedetails->startdate;

                                                if ($completionexpected != 0) {
                                                    $activityenddates = $completionexpected;
                                                } else {
                                                    $activityenddates = $coursedetails->enddate;
                                                }
                                            }

                                            $activitytype = $tbll;
                                        }
                                    } // loop close for activity and resources
                                } // if close for module_detail
                            } //  loop close for  modules.
                        } // single module condition close
                    } // modules_course loop
                } // modules_course  condition
            } // section name black if clsoe (codition)
        }
    }

    $modulegeneraldata = $DB->get_record('course_modules', array('id' => $activityid));

    $post = [

        'activity_id' => $activityid,

        'tags_data' => json_encode($tagsfinaldata),

        'email' => base64_encode($USER->email),

        'activity_name' => $activityname,

        'activitydescription' => $syncactivitydescription,

        'activity_start_date' => date("Y-m-d", @$activitystartdates),

        'activity_end_date' => date("Y-m-d", @$activityenddates),

        'activity_type' => @$activitytype,

        'showdescription' => $modulegeneraldata->showdescription,

        'visible' => $modulegeneraldata->visible,

        'idnumber' => $modulegeneraldata->idnumber,

        'completion' => $modulegeneraldata->completion,

        'completionexpected' => $modulegeneraldata->completionexpected,

        'availability' => $modulegeneraldata->availability,

        'groupmode' => $modulegeneraldata->groupmode,

        'groupingid' => $modulegeneraldata->groupingid,

    ];

    $curl = new curl;

    $url = $teamniourl . '/admin/sync_moodle_course/activity_sync';

    $options = array(

        'CURLOPT_RETURNTRANSFER' => true,

        'CURLOPT_HEADER' => false,

        'CURLOPT_POST' => count($post),
        'CURLOPT_HTTPHEADER' => array(
            'Leeloolxptoken: ' . get_config('local_leeloolxpapi')->leelooapitoken . ''
        )
    );

    if (!$response = $curl->post($url, $post, $options)) {
        return true;
    }

    if (isset($reqredirect)) {
        if ($reqredirect == 'courseview') {
            $urltogo = $CFG->wwwroot . '/course/view.php?id=' . $courseid . '&sync=1';

            redirect($urltogo);
        }
    }

    $msg = get_string('resychronizationed_success', 'tool_leeloolxp_sync');
}

if (isset($reqresync)) {
    $courseidresync = $reqcourseidresync;

    $coursedetails = $DB->get_record('course', array('id' => $courseidresync)); // Fetch all exist course from.

    $projectdescription = $coursedetails->summary;

    $idnumber = $coursedetails->idnumber;

    $shortname = $coursedetails->shortname;

    $coursename = $coursedetails->fullname;

    $table = 'course_sections'; // Section table name.

    $sections = $DB->get_records_sql("SELECT * FROM {course_sections} where course = ? and name != ''", [$courseidresync]);

    $course = get_course($courseidresync);

    $modinfo = get_fast_modinfo($course);

    $arrmainone = array();

    $oldsectionsname = '';

    if (!empty($sections)) {
        foreach ($sections as $sectionkey => $sectionsdetails) {
            if ($sectionsdetails->name == '' && $sectionsdetails->section != 0) {
                $sectionsdetails->name = get_string('topic', 'tool_leeloolxp_sync') . $sectionsdetails->section;
            }
            if ($sectionsdetails->name != '' && $sectionsdetails->sequence != '') {
                $modulescourse = $DB->get_records("course_modules", array('section' => $sectionsdetails->id));

                if (!empty($modulescourse)) {
                    foreach ($modulescourse as $coursemoduledetails) {
                        $moduleid = $coursemoduledetails->module;

                        $instance = $coursemoduledetails->instance;

                        $completionexpected = $coursemoduledetails->completionexpected;

                        $modules = $DB->get_records("modules", array('id' => $moduleid));

                        if (!empty($modules)) {
                            foreach ($modules as $key => $value) {
                                $tbl = $value->name;

                                $moduledetail = $DB->get_records($tbl, array('id' => $instance));

                                if (!empty($moduledetail)) {
                                    foreach ($moduledetail as $key => $valuefinal) {
                                        $syncactivitydescription = '';

                                        $activityids = $DB->get_record(
                                            'course_modules',

                                            array('instance' => $instance, 'module' => $moduleid)
                                        );

                                        $sectionsdetails->name;

                                        $cm = $modinfo->cms[$activityids->id];

                                        $oldsectionsname = $sectionsdetails->name;

                                        if (isset($valuefinal->intro)) {
                                            $syncactivitydescription = $valuefinal->intro;
                                        }

                                        if ($tbl == 'lesson') {
                                            if ($valuefinal->available != 0) {
                                                $activitystartdates = $valuefinal->available;
                                            } else {
                                                $activitystartdates = $coursedetails->startdate;
                                            }

                                            if ($valuefinal->deadline != 0) {
                                                $activityenddates = $valuefinal->deadline;
                                            } else {
                                                if ($completionexpected != 0) {
                                                    $activityenddates = $completionexpected;
                                                } else {
                                                    $activityenddates = $coursedetails->enddate;
                                                }
                                            }
                                        } else if ($tbl == 'quiz') {
                                            if ($valuefinal->timeopen != 0) {
                                                $activitystartdates = $valuefinal->timeopen;
                                            } else {
                                                $activitystartdates = $coursedetails->startdate;
                                            }

                                            if ($valuefinal->timeclose != 0) {
                                                $activityenddates = $valuefinal->timeclose;
                                            } else {
                                                if ($completionexpected != 0) {
                                                    $activityenddates = $completionexpected;
                                                } else {
                                                    $activityenddates = $coursedetails->enddate;
                                                }
                                            }
                                        } else if ($tbl == 'assign') {
                                            if ($valuefinal->allowsubmissionsfromdate != 0) {
                                                $activitystartdates = $valuefinal->allowsubmissionsfromdate;
                                            } else {
                                                $activitystartdates = $coursedetails->startdate;
                                            }

                                            if ($valuefinal->duedate != 0) {
                                                $activityenddates = $valuefinal->duedate;
                                            } else {
                                                if ($completionexpected != 0) {
                                                    $activityenddates = $completionexpected;
                                                } else {
                                                    $activityenddates = $coursedetails->enddate;
                                                }
                                            }
                                        } else if ($tbl == 'chat') {
                                            if ($valuefinal->chattime != 0) {
                                                $activitystartdates = $valuefinal->chattime;
                                            } else {
                                                $activitystartdates = $coursedetails->startdate;
                                            }

                                            if ($valuefinal->chattime != 0) {
                                                $activityenddates = $valuefinal->chattime;
                                            } else {
                                                if ($completionexpected != 0) {
                                                    $activityenddates = $completionexpected;
                                                } else {
                                                    $activityenddates = $coursedetails->enddate;
                                                }
                                            }
                                        } else if ($tbl == 'choice') {
                                            if ($valuefinal->timeopen != 0) {
                                                $activitystartdates = $valuefinal->timeopen;
                                            } else {
                                                $activitystartdates = $coursedetails->startdate;
                                            }

                                            if ($valuefinal->timeclose != 0) {
                                                $activityenddates = $valuefinal->timeclose;
                                            } else {
                                                if ($completionexpected != 0) {
                                                    $activityenddates = $completionexpected;
                                                } else {
                                                    $activityenddates = $coursedetails->enddate;
                                                }
                                            }
                                        } else if ($tbl == 'data') {
                                            if ($valuefinal->timeavailablefrom != 0) {
                                                $activitystartdates = $valuefinal->timeavailablefrom;
                                            } else {
                                                $activitystartdates = $coursedetails->startdate;
                                            }

                                            if ($valuefinal->timeavailableto != 0) {
                                                $activityenddates = $valuefinal->timeavailableto;
                                            } else {
                                                if ($completionexpected != 0) {
                                                    $activityenddates = $completionexpected;
                                                } else {
                                                    $activityenddates = $coursedetails->enddate;
                                                }
                                            }
                                        } else if ($tbl == 'feedback') {
                                            if ($valuefinal->timeopen != 0) {
                                                $activitystartdates = $valuefinal->timeopen;
                                            } else {
                                                $activitystartdates = $coursedetails->startdate;
                                            }

                                            if ($valuefinal->timeclose != 0) {
                                                $activityenddates = $valuefinal->timeclose;
                                            } else {
                                                if ($completionexpected != 0) {
                                                    $activityenddates = $completionexpected;
                                                } else {
                                                    $activityenddates = $coursedetails->enddate;
                                                }
                                            }
                                        } else if ($tbl == 'forum') {
                                            if ($valuefinal->duedate != 0) {
                                                $activitystartdates = $valuefinal->duedate;
                                            } else {
                                                $activitystartdates = $coursedetails->startdate;
                                            }

                                            if ($valuefinal->cutoffdate != 0) {
                                                $activityenddates = $valuefinal->cutoffdate;
                                            } else {
                                                if ($completionexpected != 0) {
                                                    $activityenddates = $completionexpected;
                                                } else {
                                                    $activityenddates = $coursedetails->enddate;
                                                }
                                            }
                                        } else if ($tbl == 'leeloolxpvc') {
                                            if ($valuefinal->timeopen != 0) {
                                                $activitystartdates = $valuefinal->timeopen;
                                            } else {
                                                $activitystartdates = $coursedetails->startdate;
                                            }

                                            if ($valuefinal->timeopen != 0) {
                                                $activityenddates = $valuefinal->timeopen;
                                            } else {
                                                if ($completionexpected != 0) {
                                                    $activityenddates = $completionexpected;
                                                } else {
                                                    $activityenddates = $coursedetails->enddate;
                                                }
                                            }
                                        } else if ($tbl == 'workshop') {
                                            if ($valuefinal->submissionstart != 0) {
                                                $activitystartdates = $valuefinal->submissionstart;
                                            } else {
                                                $activitystartdates = $coursedetails->startdate;
                                            }

                                            if ($valuefinal->submissionend != 0) {
                                                $activityenddates = $valuefinal->submissionend;
                                            } else {
                                                if ($completionexpected != 0) {
                                                    $activityenddates = $completionexpected;
                                                } else {
                                                    $activityenddates = $coursedetails->enddate;
                                                }
                                            }
                                        } else if ($tbl == 'scorm') {
                                            if ($valuefinal->timeopen != 0) {
                                                $activitystartdates = $valuefinal->timeopen;
                                            } else {
                                                $activitystartdates = $coursedetails->startdate;
                                            }

                                            if ($valuefinal->timeclose != 0) {
                                                $activityenddates = $valuefinal->timeclose;
                                            } else {
                                                if ($completionexpected != 0) {
                                                    $activityenddates = $completionexpected;
                                                } else {
                                                    $activityenddates = $coursedetails->enddate;
                                                }
                                            }
                                        } else {

                                            $activitystartdates = $coursedetails->startdate;

                                            if ($completionexpected != 0) {
                                                $activityenddates = $completionexpected;
                                            } else {
                                                $activityenddates = $coursedetails->enddate;
                                            }
                                        }

                                        $modulegeneraldata = $DB->get_record('course_modules', array('id' => $activityids->id));

                                        $arrmainone[$activityids->id] =

                                            array(
                                                'name' => $valuefinal->name,

                                                'activitydescription' => $syncactivitydescription,

                                                'activity_start_date' => $activitystartdates,

                                                'activity_end_date' => $activityenddates,

                                                'showdescription' => $modulegeneraldata->showdescription,

                                                'visible' => $modulegeneraldata->visible,

                                                'idnumber' => $modulegeneraldata->idnumber,

                                                'completion' => $modulegeneraldata->completion,

                                                'completionexpected' => $modulegeneraldata->completionexpected,

                                                'availability' => $modulegeneraldata->availability,

                                                'groupmode' => $modulegeneraldata->groupmode,

                                                'groupingid' => $modulegeneraldata->groupingid,
                                            );
                                    } // Loop close for activity and resources.
                                } // If close for module_detail.
                            } //  Loop close for  modules.
                        } // Single module condition close.
                    } // Modules_course loop.
                } // Modules_course  condition.
            } // section name black if clsoe (codition).
        }
    }

    $categorydata = $DB->get_records_sql("SELECT * FROM {course_categories} WHERE id = ?", [$coursedetails->category]);

    $post = [

        'course_id' => $courseidresync,

        'course_name' => $coursename,

        'activity_array' => json_encode($arrmainone),

        'start_date' => $coursedetails->startdate,

        'project_description' => $projectdescription,

        'end_date' => $coursedetails->enddate,

        'visible' => $coursedetails->visible,

        'moodle_course_id' => $idnumber,

        'shortname' => $shortname,

        'cat_data' => json_encode($categorydata),

        'sections' => json_encode($sections),

    ];

    $curl = new curl;

    $url = $teamniourl . '/admin/sync_moodle_course/resync_course';

    $options = array(

        'CURLOPT_RETURNTRANSFER' => true,

        'CURLOPT_HEADER' => false,

        'CURLOPT_POST' => count($post),
        'CURLOPT_HTTPHEADER' => array(
            'Leeloolxptoken: ' . get_config('local_leeloolxpapi')->leelooapitoken . ''
        )
    );

    $curl->post($url, $post, $options);

    $msg = get_string('resychronizationed_success', 'tool_leeloolxp_sync');

    if (isset($reqredirect)) {
        if ($reqredirect == 'courseview') {
            $urltogo = $CFG->wwwroot . '/course/view.php?id=' . $courseidresync . '&sync=1';

            redirect($urltogo);
        }
    }
}

if (isset($requnsyncid) && !empty($requnsyncid)) {
    $activityidmoodlearr = $requnsyncid;

    $post = ['activityid' => $activityidmoodlearr]; // Added by abdul.

    $curl = new curl;

    $url = $teamniourl . '/admin/sync_moodle_course/unsyncactivity';

    $options = array(

        'CURLOPT_RETURNTRANSFER' => true,

        'CURLOPT_HEADER' => false,

        'CURLOPT_POST' => count($post),

        'CURLOPT_HTTPHEADER' => array(
            'Leeloolxptoken: ' . get_config('local_leeloolxpapi')->leelooapitoken . ''
        )
    );

    if (!$response = $curl->post($url, $post, $options)) {
        return true;
    }

    $DB->execute("DELETE FROM {tool_leeloolxp_sync} where activityid = ?", [$activityidmoodlearr]);

    $msg = get_string('unsychronizationed_success', 'tool_leeloolxp_sync');
}

if (isset($reqid) && !empty($reqid)) {
    $courseid = $reqid;

    $post = ['course_id' => $courseid];

    $curl = new curl;

    $url = $teamniourl . '/admin/sync_moodle_course/unsynccourse';

    $options = array(

        'CURLOPT_RETURNTRANSFER' => true,

        'CURLOPT_HEADER' => false,

        'CURLOPT_POST' => count($post),

        'CURLOPT_HTTPHEADER' => array(
            'Leeloolxptoken: ' . get_config('local_leeloolxpapi')->leelooapitoken . ''
        )
    );

    if (!$response = $curl->post($url, $post, $options)) {
        return true;
    }

    $DB->execute("DELETE FROM {tool_leeloolxp_sync} where courseid = ?", [$courseid]);

    $msg = get_string('unsychronizationed_success', 'tool_leeloolxp_sync');
}

if (isset($reqsyncactivities) && isset($reqallactivities)) {
    $alldata = $reqallactivities;

    $i = 0;

    $moodleuserarray = array();

    $moodleuserstudentarray = array();

    $moodleuserteacherarray = array();

    $moodleuserteacherarrayy = array();

    $moodleuserstudentarrayy = array();

    $useridscohort = '';

    $cohortname = '';

    foreach ($alldata as $key => $value) {
        $activityidmoodlearr = array();

        $activityset = str_replace("'", '', $value);

        $activityidarr = explode('$$', $activityset);

        $courseidagain = $activityidarr[4];

        $activitystartdate = $activityidarr[7];

        $activityenddate = $activityidarr[8];

        $activitytype = $activityidarr[9];

        $activityurl = $activityidarr[10];

        $quiztypesync = $activityidarr[11];

        $quizdiffsync = $activityidarr[12];

        $arfulldata = $activityidarr[14];

        if ($CFG->dbtype == 'mysqli') {

            $sql = "SELECT cs.id cid , cs.name cname , cs.section csection , cs.sequence csequence, cfo.*,
            (Select id from mdlwf_course_sections where course = ? and section = cfo.value) as parentsectionid
            FROM mdlwf_course_sections as cs left join mdlwf_course_format_options as cfo
            on cfo.sectionid = cs.id and cfo.name = 'parent' WHERE cs.course = ?
            AND cs.section != 0 GROUP BY cs.id ORDER BY csection ASC";

            // AND ( cfo.name LIKE 'parent' OR cfo.name LIKE 'hiddensections' OR cfo.name LIKE 'coursedisplay').
            $coursehierarchy = $DB->get_records_sql($sql, [$courseidagain, $courseidagain]);
        } else {
            $sql = "SELECT DISTINCT ON (cs.id) cs.id cid , cs.name cname , cs.section csection ,
            cs.sequence csequence, cfo.value,  cfo.courseid ,cfo.format,cfo.sectionid,cfo.name ,
            cfo.value ,cfo.id id ,
            (Select id from {course_sections} where course = ? and section = CAST(cfo.value as INT)) as parentsectionid
            from   {course_sections}  cs
            left join {course_format_options} cfo ON cfo.sectionid = cs.id and cfo.name = 'parent'
            WHERE course = ? and
            cs.section != 0  ORDER BY cs.id ASC";
            $coursehierarchy = $DB->get_records_sql($sql, [$courseidagain, $courseidagain]);
            if (!empty($coursehierarchy)) {
                usort($coursehierarchy, function ($a, $b) {
                    return $a->csection - $b->csection;
                });
            }
        }

        if (empty($coursehierarchy)) {

            $sql = "SELECT id cid,name cname,section csection ,sequence csequence,id sectionid
            FROM {course_sections} cs
            WHERE course = ? AND section != 0 AND section != '0'   ";

            $coursehierarchy = $DB->get_records_sql($sql, [$courseidagain]);
        }

        if ($i == '0') {
            $enrolleduser = $DB->get_records_sql("SELECT u.*, ue.id, e.courseid, ue.userid, ue.timeend,
            ue.timestart, e.status enrol_status ,
            e.sortorder  enrol_sortorder , e.enrol
            enrolmethod, (ue.timecreated)
            FROM {user_enrolments} ue JOIN {enrol} e ON e.id = ue.enrolid
            AND e.status = 0 JOIN {user} u ON u.id = ue.userid
            AND u.deleted = 0
            Where e.courseid = ?", [$courseidagain]);

            foreach ($enrolleduser as $key => $moodeluservalue) {

                $useridscohort .= $moodeluservalue->userid . ',';

                $sql = "SELECT {role}.shortname shortname, {role}.id roleid

                FROM {role_assignments} LEFT JOIN {user_enrolments} ON {role_assignments}.userid = {user_enrolments}.userid

                LEFT JOIN {role} ON {role_assignments}.roleid = {role}.id LEFT JOIN {context}

                ON {context}.id = {role_assignments}.contextid LEFT JOIN {enrol} ON {enrol}.courseid = {context}.instanceid

                AND {user_enrolments}.enrolid = {enrol}.id

                WHERE  {user_enrolments}.userid = ? AND {enrol}.courseid = ?";

                $roleresult = $DB->get_records_sql($sql, [$moodeluservalue->userid, $courseidagain]);

                $userrole = '';

                foreach ($roleresult as $rolekey => $rolevalues) {
                    $userrole = $rolevalues->shortname;

                    $roleid = $rolevalues->roleid;
                }

                $usertype = '';

                $teamniorole = '';

                $teamniousertype = '';

                $userdegree = $DB->get_record_sql("SELECT DISTINCT data FROM {user_info_data}

                left join {user_info_field} on {user_info_data}.fieldid = {user_info_field}.id

                where {user_info_field}.shortname = 'degree' and {user_info_data}.userid = ?", [$moodeluservalue->userid]);

                if (isset($userdegree->data) && isset($userdegree->data) != '') {
                    $userdegreename = $userdegree->data;
                } else {
                    $userdegreename = '';
                }

                $userdepartment = $moodeluservalue->department;

                $userinstitution = $moodeluservalue->institution;

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

                    $studentdbsetarr[$si] = $mstudentrole . "_" .

                        $mstudentinstitution . "_" . $mstudentdepartment . "_" . $mstudentdegree;
                }

                $userstudentinfo = $roleid . "_" . $userinstitution . "_" .

                    $userdepartment . "_" . $userdegreename;

                $matchedvalue = array_search($userstudentinfo, $studentdbsetarr);

                if ($matchedvalue) {
                    $tcolnamestudent = 'student_position_t_' . $matchedvalue;

                    $teamniorole = $ssopluginconfig->$tcolnamestudent;

                    $usertype = 'student';
                } else {

                    $teachernumcombinations = $ssopluginconfig->teacher_num_combination;

                    $teachernumcombinationsval = $teachernumcombinations;

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
                        if (empty($mteacherinstitution)) {
                            $mteacherinstitution = '';
                        }
                        if (empty($mteacherdepartment)) {
                            $mteacherdepartment = '';
                        }
                        if (empty($mteacherdegree)) {
                            $mteacherdegree = '';
                        }
                        $teacherdbsetarr[$si] = $mteacherrole . "_" .

                            $mteacherinstitution . "_" . $mteacherdepartment . "_" . $mteacherdegree;
                    }

                    $userteacherinfo = $roleid . "_" . $userinstitution . "_" .

                        $userdepartment . "_" . $userdegreename;

                    $matchedvalueteacher = array_search($userteacherinfo, $teacherdbsetarr);

                    if ($matchedvalueteacher) {
                        $tcolnameteacher = 'teacher_position_t_' . $matchedvalueteacher;

                        $teamniorole = $ssopluginconfig->$tcolnameteacher;

                        $usertype = 'teacher';
                    } else {

                        $usertype = 'student';

                        $teamniorole = $ssopluginconfig->default_student_position;
                    }
                }

                if ($usertype == 'student') {
                    $cancreateuser = $ssopluginconfig->web_new_user_student;

                    $userdesignation = $teamniorole;

                    $userapproval = $ssopluginconfig->required_aproval_student;
                } else {

                    if ($usertype == 'teacher') {
                        $cancreateuser = $ssopluginconfig->web_new_user_teacher;

                        $userdesignation = $teamniorole;

                        $userapproval = $ssopluginconfig->required_aproval_teacher;
                    }
                }

                $enrolleduserid = $moodeluservalue->userid;

                $groupsname = $DB->get_records_sql("SELECT * FROM {groups} groups left join

                {groups_members} on {groups_members}.groupid = groups.id

                where groups.courseid = ? and {groups_members}.userid= ?", [$courseidagain, $enrolleduserid]);

                $usergroupsname = array();

                if (!empty($groupsname)) {
                    foreach ($groupsname as $key => $gvalue) {
                        $usergroupsname[] = $gvalue->name;
                    }
                }

                $academicprogramfield = $DB->get_record_sql("SELECT DISTINCT data
                FROM {user_info_field} field
                left join {user_info_data} on {user_info_data}.fieldid = field.id
                where field.name LIKE '%Academic program%'
                and {user_info_data}.userid= ?", [$enrolleduserid]);

                if (isset($academicprogramfield->data) && $academicprogramfield->data != '') {
                    $academicprogram = $academicprogramfield->data;
                } else {
                    $academicprogram = '';
                }

                $usergroupsname = implode(',', $usergroupsname);

                $moodleurlpic = new moodle_url('/user/pix.php/' . $moodeluservalue->id . '/f.jpg');

                if ($usertype == 'student') {

                    $tempdatacon = $DB->get_record_sql(" SELECT id FROM {context}
                    WHERE instanceid = ? AND depth = '3'
                    ORDER BY id DESC", [$courseidagain]);

                    if (!empty($tempdatacon)) {
                        $temptdatastr = $tempdatacon->id;
                    } else {
                        $temptdatastr = 0;
                    }

                    $userrolename = 'student';

                    if (!empty($temptdatastr)) {

                        $userrolename = $DB->get_record_sql(" SELECT r.shortname
                        FROM {role_assignments} ra
                        left join {role} r on r.id = ra.roleid
                        WHERE contextid IN (?)
                        and userid = ? ", [$temptdatastr, $moodeluservalue->userid]);


                        $userrolename = $userrolename->shortname;
                    }


                    $lastlogin = date('Y-m-d h:i:s', $moodeluservalue->lastlogin);

                    $moodleuserstudentarrayy[] = array(

                        'username' => base64_encode($moodeluservalue->username),

                        'fullname' => ucfirst($moodeluservalue->firstname) . " " .

                            ucfirst($moodeluservalue->middlename) . " " . ucfirst($moodeluservalue->lastname),

                        'user_pic_moodle_url' => $moodleurlpic,

                        'email' => base64_encode($moodeluservalue->email),

                        'course_role' => $userrolename,

                        'city' => $moodeluservalue->city,

                        'country' => $moodeluservalue->country,

                        'timezone' => $moodeluservalue->timezone,

                        'firstnamephonetic' => $moodeluservalue->firstnamephonetic,

                        'lastnamephonetic' => $moodeluservalue->lastnamephonetic,

                        'middlename' => $moodeluservalue->middlename,

                        'alternatename' => $moodeluservalue->alternatename,

                        'icq' => $moodeluservalue->icq,

                        'skype' => $moodeluservalue->skype,

                        'aim' => $moodeluservalue->aim,

                        'yahoo' => $moodeluservalue->yahoo,

                        'msn' => $moodeluservalue->msn,

                        'idnumber' => $moodeluservalue->idnumber,

                        'institution' => $moodeluservalue->institution,

                        'department' => $moodeluservalue->department,

                        'phone' => $moodeluservalue->phone1,

                        'moodle_phone' => $moodeluservalue->phone2,

                        'adress' => $moodeluservalue->address,

                        'firstaccess' => $moodeluservalue->firstaccess,

                        'lastaccess' => $moodeluservalue->lastaccess,

                        'lastlogin' => $lastlogin,

                        'lastip' => $moodeluservalue->lastip,

                        'passwors' => $moodeluservalue->password,

                        'user_groups_name' => $usergroupsname,

                        'groups_data' => $groupsname,

                        'can_create_user' => $cancreateuser,

                        'designation' => $userdesignation,

                        'user_approval' => $userapproval,

                        'user_type' => $usertype,

                        'userrole' => $userrole,

                        'designation_id' => $userdesignation,

                        'enrol' => $moodeluservalue->enrolmethod,

                        'timeend' => $moodeluservalue->timeend,

                        'timestart' => $moodeluservalue->timestart,

                        'userid' => $moodeluservalue->userid,

                        'enrol_status' => $moodeluservalue->enrol_status,

                        'academicprogram' => $academicprogram,

                        'auth' => $moodeluservalue->auth,

                        'confirmed' => $moodeluservalue->confirmed,

                        'deleted' => $moodeluservalue->deleted,

                        'suspended' => $moodeluservalue->suspended,

                        'timecreated' => $moodeluservalue->timecreated,

                        'timemodified' => $moodeluservalue->timemodified,

                        'enrol_sortorder' => $moodeluservalue->enrol_sortorder
                    );
                } else {

                    $tempdatacon = $DB->get_record_sql(" SELECT id FROM {context}
                    WHERE instanceid = ? AND depth = '3'
                    ORDER BY id DESC", [$courseidagain]);

                    if (!empty($tempdatacon)) {
                        $temptdatastr = $tempdatacon->id;
                    } else {
                        $temptdatastr = 0;
                    }

                    $userrolename = 'student';

                    if (!empty($temptdatastr)) {
                        $userrolename = $DB->get_record_sql(" SELECT r.shortname
                        FROM {role_assignments} ra
                        left join {role} r on r.id = ra.roleid
                        WHERE contextid IN (?)
                        and userid = ? ", [$temptdatastr, $moodeluservalue->userid]);
                        $userrolename = $userrolename->shortname;
                    }

                    if ($usertype == 'teacher') {
                        $moodeluservalueteacher = $moodeluservalue;

                        $moodleuserteacherarrayy[] = array(
                            'username' => base64_encode($moodeluservalueteacher->username),

                            'fullname' => ucfirst($moodeluservalueteacher->firstname) . " " .

                                ucfirst($moodeluservalueteacher->middlename) . " " . ucfirst($moodeluservalueteacher->lastname),

                            'user_pic_moodle_url' => $moodleurlpic,

                            'email' => base64_encode($moodeluservalueteacher->email),

                            'course_role' => $userrolename,

                            'city' => $moodeluservalueteacher->city,

                            'country' => $moodeluservalueteacher->country,

                            'timezone' => $moodeluservalueteacher->timezone,

                            'firstnamephonetic' => $moodeluservalueteacher->firstnamephonetic,

                            'lastnamephonetic' => $moodeluservalueteacher->lastnamephonetic,

                            'middlename' => $moodeluservalueteacher->middlename,

                            'alternatename' => $moodeluservalueteacher->alternatename,

                            'icq' => $moodeluservalueteacher->icq,

                            'skype' => $moodeluservalueteacher->skype,

                            'aim' => $moodeluservalueteacher->aim,

                            'yahoo' => $moodeluservalueteacher->yahoo,

                            'msn' => $moodeluservalueteacher->msn,

                            'idnumber' => $moodeluservalue->idnumber,

                            'institution' => $moodeluservalueteacher->institution,

                            'department' => $moodeluservalueteacher->department,

                            'phone' => $moodeluservalueteacher->phone1,

                            'moodle_phone' => $moodeluservalueteacher->phone2,

                            'adress' => $moodeluservalueteacher->address,

                            'firstaccess' => $moodeluservalueteacher->firstaccess,

                            'lastaccess' => $moodeluservalueteacher->lastaccess,

                            'lastlogin' => $moodeluservalueteacher->lastlogin,

                            'lastip' => $moodeluservalueteacher->lastip,

                            'user_groups_name' => $usergroupsname,

                            'groups_data' => $groupsname,

                            'can_create_user' => $cancreateuser,

                            'designation' => $userdesignation,

                            'user_approval' => $userapproval,

                            'user_type' => $usertype,

                            'userrole' => $userrole,

                            'designation_id' => $userdesignation,

                            'enrol' => $moodeluservalue->enrolmethod,

                            'timeend' => $moodeluservalue->timeend,

                            'timestart' => $moodeluservalue->timestart,

                            'userid' => $moodeluservalue->userid,

                            'enrol_status' => $moodeluservalue->enrol_status,

                            'academicprogram' => $academicprogram,

                            'auth' => $moodeluservalue->auth,

                            'confirmed' => $moodeluservalue->confirmed,

                            'deleted' => $moodeluservalue->deleted,

                            'suspended' => $moodeluservalue->suspended,

                            'timecreated' => $moodeluservalue->timecreated,

                            'timemodified' => $moodeluservalue->timemodified,

                            'enrol_sortorder' => $moodeluservalue->enrol_sortorder
                        );
                    }
                }
            }
        }

        $activityid = $activityidarr[3];

        $modulegeneraldata = $DB->get_record('course_modules', array('id' => $activityid));

        $secctiondescription = $activityidarr[5];

        $activitydescription = $activityidarr[6];

        $coursedetailsagain = $DB->get_record('course', array('id' => $courseidagain));

        $useridscohort = chop($useridscohort, ",");

        if (!empty($useridscohort)) {

            $cohortdata = $DB->get_records_sql(" SELECT {cohort}.name
            FROM {cohort_members}
            left join {cohort} on {cohort}.id={cohort_members}.cohortid
            WHERE userid IN ($useridscohort) ");

            if (!empty($cohortdata)) {
                foreach ($cohortdata as $key => $value) {
                    $cohortname .= $value->name . ',';
                }
                $cohortname = chop($cohortname, ",");
            }
        }

        $groupname = '';
        $categorydata = $DB->get_records_sql("SELECT * FROM {course_categories} WHERE id = ?", [$coursedetailsagain->category]);

        $gradecategories = $DB->get_records_sql("SELECT * FROM {grade_categories}
        WHERE courseid = ?
        ORDER BY depth ASC", [$courseidagain]);
        $gradeitems = $DB->get_records_sql("SELECT * FROM {grade_items} WHERE courseid = ?", [$courseidagain]);

        $tempdata = explode('$$', $activityset);

        $tagdata = [];

        if (!empty($tempdata[3])) {
            $itemidd = $tempdata[3];

            $tagdata = $DB->get_records_sql(
                "SELECT {tag}.* ,{user}.email, {tag_instance}.contextid,

            {tag_instance}.itemtype, {tag_instance}.ordering, {tag_instance}.tiuserid,
            {tag_instance}.itemid
            FROM {tag_instance}
            join {tag} on {tag_instance}.tagid = {tag}.id join {user} ON {user}.id = {tag}.userid
            where {tag_instance}.itemid = ?",
                [$itemidd]
            );
        }

        $post = [

            'grade_categories' => json_encode($gradecategories),

            'grade_items' => json_encode($gradeitems),

            'tagdata' => base64_encode(json_encode($tagdata)),

            'moodle_users_students' => json_encode($moodleuserstudentarrayy),

            'moodle_users_teachers' => json_encode($moodleuserteacherarrayy),

            'course_section_activity' => $activityset,

            'coursehierarchy' => json_encode($coursehierarchy),

            'is_quiz_task' => 0,

            'group_name' => $groupname,

            'project_description' => $coursedetailsagain->summary,

            'subproject_description' => $secctiondescription,

            'task_description' => $activitydescription,

            'course_id' => $coursedetailsagain->id,

            'idnumber' => $coursedetailsagain->idnumber,

            'shortname' => $coursedetailsagain->shortname,

            'cohortname' => $cohortname,

            'category' => $coursedetailsagain->category,

            'visible' => $coursedetailsagain->visible,

            'cat_data' => json_encode($categorydata),

            'startdate' => $coursedetailsagain->startdate,

            'activity_start_date' => $activitystartdate,

            'activity_end_date' => $activityenddate,

            'enddate' => $coursedetailsagain->enddate,

            'activity_type' => $activitytype,

            'activity_url' => $activityurl,

            'quiztype' => $quiztypesync,

            'quizdiff' => $quizdiffsync,

            'arfulldata' => $arfulldata,

            'showdescription' => $modulegeneraldata->showdescription,

            'visible' => $modulegeneraldata->visible,

            'idnumber' => $modulegeneraldata->idnumber,

            'completion' => $modulegeneraldata->completion,

            'completionexpected' => $modulegeneraldata->completionexpected,

            'availability' => $modulegeneraldata->availability,

            'groupmode' => $modulegeneraldata->groupmode,

            'groupingid' => $modulegeneraldata->groupingid,

        ];

        $url = $teamniourl . '/admin/sync_moodle_course/index';

        $curl = new curl;

        $options = array(

            'CURLOPT_RETURNTRANSFER' => true,

            'CURLOPT_HEADER' => false,

            'CURLOPT_POST' => count($post),
            'CURLOPT_HTTPHEADER' => array(
                'Leeloolxptoken: ' . get_config('local_leeloolxpapi')->leelooapitoken . ''
            )
        );

        if (!$response = $curl->post($url, $post, $options)) {
            return true;
        }

        $response = json_decode($response, true);

        $courseid = $courseidagain;

        $sectionid = 0;

        if (!empty($response)) {
            foreach ($response as $key => $teamniotaskid) {
                if ($teamniotaskid != '0') {
                    $alreadyexistquery = $DB->get_record_sql("SELECT id FROM {tool_leeloolxp_sync}

                    where teamnio_task_id = ?", [$teamniotaskid]);

                    if (empty($alreadyexistquery)) {
                        $DB->execute("INSERT INTO {tool_leeloolxp_sync}

                        ( courseid, sectionid, activityid, enabled, teamnio_task_id, is_quiz)

                        VALUES ( ?, ?, ?, '1', ?,'0')", [$courseid, $sectionid, $activityid, $teamniotaskid]);
                    }

                    $msg = get_string('sychronizationed_success', 'tool_leeloolxp_sync');
                } else {

                    $activityidmoodlearr = $activityid;

                    $DB->execute("Update {tool_leeloolxp_sync}  set enabled = '1'

                    where activityid = ?", [$activityidmoodlearr]);

                    $msg = get_string('sychronizationed_success', 'tool_leeloolxp_sync');
                }
            }
        } else {
            $msg = get_string('course_exists', 'tool_leeloolxp_sync');
        }

        $i++;
    }

    if (isset($reqredirecthidden)) {
        if ($reqredirecthidden == 'courseview') {
            $urltogo = $CFG->wwwroot . '/course/view.php?id=' . $courseidagain . '&sync=1';

            redirect($urltogo);
        }
    }
}

if (isset($reqcourseid1) && !empty($reqcourseid1)) {
    $DB->execute("Update {tool_leeloolxp_sync}  set is_quiz = '0'

    where courseid = ?", [$reqcourseid1]);

    $isqpost = [

        'courseid' => $reqcourseid1,

    ];

    $url = $teamniourl . '/admin/sync_moodle_course/is_quiz_update_by_course';

    $curl = new curl;

    $options = array(

        'CURLOPT_RETURNTRANSFER' => true,

        'CURLOPT_HEADER' => false,

        'CURLOPT_POST' => count($isqpost),
        'CURLOPT_HTTPHEADER' => array(
            'Leeloolxptoken: ' . get_config('local_leeloolxpapi')->leelooapitoken . ''
        )
    );

    if (!$response = $curl->post($url, $isqpost, $options)) {
        return true;
    }
}

if (isset($reqquizsync)) {
    foreach ($reqquizsync as $key => $value) {
        $DB->execute("Update {tool_leeloolxp_sync}  set is_quiz = '1'

        where activityid = ?", [$value]);

        $isqpost = [

            'activity_id' => $value,

        ];

        $url = $teamniourl . '/admin/sync_moodle_course/is_quiz_update';

        $curl = new curl;

        $options = array(

            'CURLOPT_RETURNTRANSFER' => true,

            'CURLOPT_HEADER' => false,

            'CURLOPT_POST' => count($isqpost),
            'CURLOPT_HTTPHEADER' => array(
                'Leeloolxptoken: ' . get_config('local_leeloolxpapi')->leelooapitoken . ''
            )
        );

        if (!$response = $curl->post($url, $isqpost, $options)) {
            return true;
        }
    }
}

echo $OUTPUT->header();

if ($msg != '') {
    echo "<p style='color:green;'>" . $msg . "</p>";
}

if (!empty($error)) {
    echo $OUTPUT->container($error, 'leeloolxp_sync_myformerror');
}

if (!isset($reqaction)) {
    echo '<div id="accordion">';

    $categories = $DB->get_records('course_categories', array());

    if (!empty($categories)) {
        foreach ($categories as $key => $catvalue) {
            echo '<div class="card">

            <div class="card-header" id="heading' . $catvalue->id . '">

                <table>

                    <tr>

                        <td>

                            <button class="btn btn-link collapsed"

                            data-toggle="collapse"

                            data-target="#collapse' . $catvalue->id . '"

                            aria-expanded="false" aria-controls="collapse

                            ' . $catvalue->id . '">' . $catvalue->name . '</button>

                        </td>

                        <td>' . get_string('is_syced', 'tool_leeloolxp_sync') . '</td>

                    </tr>

                </table>

            </div>

            <div id="collapse' . $catvalue->id . '"

            class="collapse" aria-labelledby="heading' . $catvalue->id . '"

            data-parent="#accordion">

                <div class="card-body">

                    <div class="card-table">

                        <table>';

            $courses = $DB->get_records('course', array('category' => $catvalue->id));

            if (!empty($courses)) {
                foreach ($courses as $courskey => $coursevalue) {
                    echo '<tr>

                    <td>

                        <div class="tqs-left">

                            <i class="fa fa-recycle"></i>

                            <span>' . $coursevalue->fullname . '</span>

                        </div>

                    </td>

                    <td>

                        <div class="tqs-right">';

                    $alreadysync = false;

                    $coursesyncedquery = $DB->get_records(
                        'tool_leeloolxp_sync',

                        array('courseid' => $coursevalue->id)
                    );

                    if (!empty($coursesyncedquery)) {
                        $alreadysync = true;
                    }

                    if ($alreadysync) {
                        echo '<span class="tqs-span-yes">' . get_string('yes', 'tool_leeloolxp_sync') . '</span>';
                    } else {

                        echo '<span class="tqs-span-no">' . get_string('no', 'tool_leeloolxp_sync') . '</span>';
                    }

                    echo '<ul>';

                    if ($alreadysync) {
                        echo '<li>
                        <a href="' . parse_url(
                            $_SERVER["REQUEST_URI"],
                            PHP_URL_PATH
                        ) .
                            '?action=add&courseid=' . $coursevalue->id . '">
                        ' . get_string('edit', 'tool_leeloolxp_sync') .
                            '</a>
                        </li>';

                        echo '<li>
                            <a href="javascript:void()"
                                data-coursename="' . $coursevalue->fullname . '"
                                onclick="UnsyncCourse(' . $coursevalue->id . ')">' . get_string('unsync', 'tool_leeloolxp_sync') . '
                            </a>
                        </li>';

                        echo '<li><a href="' . parse_url(
                            $_SERVER['REQUEST_URI'],

                            PHP_URL_PATH
                        ) . '?resync=resync&courseid_resync=' . $coursevalue->id . '">

                                                                ' . get_string('resync', 'tool_leeloolxp_sync') . '</a></li>';
                    } else {

                        echo '<li>
                        <a href="
                        ' . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) . '?action=add&courseid=' .
                            $coursevalue->id . '">'
                            . get_string('add', 'tool_leeloolxp_sync') .
                            '</a></li>';
                    }

                    echo '</ul>

                    </div>

                </td>

            </tr>';
                }
            }

            echo '</table>

            </div>

        </div>

    </div>

</div>';
        }
    }
    echo '</div>';
}

if (isset($reqaction)) {
    echo '<div class="back-arrow-left">

    <a href="' . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) . '">

    <i class="fa fa-arrow-left"></i> ' . get_string('backtocourse', 'tool_leeloolxp_sync') . '</a></div>

    <table id="acivity_sync_table"><tr><th>' . get_string('section', 'tool_leeloolxp_sync') . '</th>

    <th>' . get_string('name', 'tool_leeloolxp_sync') . '</th>

    <th>' . get_string('lxpartracker', 'tool_leeloolxp_sync') . ' <br>

    <input type="checkbox" name="all_activity_checkbox"

    id="all_activity_checkbox" onchange="check_all_activity();"> ' . get_string('selectall', 'tool_leeloolxp_sync') . '  </th>

    <th>' . get_string('lxpquiztracker', 'tool_leeloolxp_sync') . ' <br>

    <input type="checkbox" name="all_is_quiz_checkbox"

    id="all_is_quiz_checkbox" onchange="check_all_is_quiz();"> ' . get_string('selectall', 'tool_leeloolxp_sync') . ' </th></tr>

        <form method="post">';

    if ($reqaction == 'add') {
        $courseid = $reqcourseid1;

        $coursedetails = $DB->get_record('course', array('id' => $courseid));

        $table = 'course_sections';

        $sections = $DB->get_records($table, array('course' => $courseid), 'section ASC');

        $courseaction = get_course($courseid);

        $modinfo = get_fast_modinfo($courseaction);

        $oldsectionsname = '';

        if (!empty($sections)) {
            foreach ($sections as $sectionkey => $sectionsdetails) {
                if ($sectionsdetails->name == '' && $sectionsdetails->section != 0) {
                    $sectionsdetails->name = get_string('topic', 'tool_leeloolxp_sync') . $sectionsdetails->section;
                }

                $sequence = $sectionsdetails->sequence;

                $modulescourse = $DB->get_records_sql("select * from

                            {course_modules} where section = ? ORDER BY ID", [$sectionsdetails->id]);

                if (!empty($modulescourse)) {
                    foreach ($modulescourse as $coursemoduledetails) {
                        $moduleid = $coursemoduledetails->module;

                        $instance = $coursemoduledetails->instance;

                        $completionexpected = $coursemoduledetails->completionexpected;

                        $modules = $DB->get_records("modules", array('id' => $moduleid));

                        if (!empty($modules)) {
                            foreach ($modules as $key => $value) {
                                $tbl = $value->name;

                                $moduledetail = $DB->get_records($tbl, array('id' => $instance));

                                if (!empty($moduledetail)) {
                                    foreach ($moduledetail as $key => $valuefinal) {
                                        if ($tbl == 'lesson') {
                                            if ($valuefinal->available != 0) {
                                                $activitystartdates = $valuefinal->available;
                                            } else {
                                                $activitystartdates = $coursedetails->startdate;
                                            }

                                            if ($valuefinal->deadline != 0) {
                                                $activityenddatess = $valuefinal->deadline;
                                            } else {
                                                if ($completionexpected != 0) {
                                                    $activityenddatess = $completionexpected;
                                                } else {
                                                    $activityenddatess = $coursedetails->enddate;
                                                }
                                            }
                                        } else if ($tbl == 'quiz') {
                                            if ($valuefinal->timeopen != 0) {
                                                $activitystartdates = $valuefinal->timeopen;
                                            } else {
                                                $activitystartdates = $coursedetails->startdate;
                                            }

                                            if ($valuefinal->timeclose != 0) {
                                                $activityenddatess = $valuefinal->timeclose;
                                            } else {
                                                if ($completionexpected != 0) {
                                                    $activityenddatess = $completionexpected;
                                                } else {
                                                    $activityenddatess = $coursedetails->enddate;
                                                }
                                            }
                                        } else if ($tbl == 'assign') {
                                            if ($valuefinal->allowsubmissionsfromdate != 0) {
                                                $activitystartdates = $valuefinal->allowsubmissionsfromdate;
                                            } else {
                                                $activitystartdates = $coursedetails->startdate;
                                            }

                                            if ($valuefinal->duedate != 0) {
                                                $activityenddatess = $valuefinal->duedate;
                                            } else {
                                                if ($completionexpected != 0) {
                                                    $activityenddatess = $completionexpected;
                                                } else {
                                                    $activityenddatess = $coursedetails->enddate;
                                                }
                                            }
                                        } else if ($tbl == 'chat') {
                                            if ($valuefinal->chattime != 0) {
                                                $activitystartdates = $valuefinal->chattime;
                                            } else {
                                                $activitystartdates = $coursedetails->startdate;
                                            }

                                            if ($valuefinal->chattime != 0) {
                                                $activityenddatess = $valuefinal->chattime;
                                            } else {
                                                if ($completionexpected != 0) {
                                                    $activityenddatess = $completionexpected;
                                                } else {
                                                    $activityenddatess = $coursedetails->enddate;
                                                }
                                            }
                                        } else if ($tbl == 'choice') {
                                            if ($valuefinal->timeopen != 0) {
                                                $activitystartdates = $valuefinal->timeopen;
                                            } else {
                                                $activitystartdates = $coursedetails->startdate;
                                            }

                                            if ($valuefinal->timeclose != 0) {
                                                $activityenddatess = $valuefinal->timeclose;
                                            } else {
                                                if ($completionexpected != 0) {
                                                    $activityenddatess = $completionexpected;
                                                } else {
                                                    $activityenddatess = $coursedetails->enddate;
                                                }
                                            }
                                        } else if ($tbl == 'data') {
                                            if ($valuefinal->timeavailablefrom != 0) {
                                                $activitystartdates = $valuefinal->timeavailablefrom;
                                            } else {
                                                $activitystartdates = $coursedetails->startdate;
                                            }

                                            if ($valuefinal->timeavailableto != 0) {
                                                $activityenddatess = $valuefinal->timeavailableto;
                                            } else {
                                                if ($completionexpected != 0) {
                                                    $activityenddatess = $completionexpected;
                                                } else {
                                                    $activityenddatess = $coursedetails->enddate;
                                                }
                                            }
                                        } else if ($tbl == 'feedback') {
                                            if ($valuefinal->timeopen != 0) {
                                                $activitystartdates = $valuefinal->timeopen;
                                            } else {
                                                $activitystartdates = $coursedetails->startdate;
                                            }

                                            if ($valuefinal->timeclose != 0) {
                                                $activityenddatess = $valuefinal->timeclose;
                                            } else {
                                                if ($completionexpected != 0) {
                                                    $activityenddatess = $completionexpected;
                                                } else {
                                                    $activityenddatess = $coursedetails->enddate;
                                                }
                                            }
                                        } else if ($tbl == 'forum') {
                                            if ($valuefinal->duedate != 0) {
                                                $activitystartdates = $valuefinal->duedate;
                                            } else {
                                                $activitystartdates = $coursedetails->startdate;
                                            }

                                            if ($valuefinal->cutoffdate != 0) {
                                                $activityenddatess = $valuefinal->cutoffdate;
                                            } else {
                                                if ($completionexpected != 0) {
                                                    $activityenddatess = $completionexpected;
                                                } else {
                                                    $activityenddatess = $coursedetails->enddate;
                                                }
                                            }
                                        } else if ($tbl == 'leeloolxpvc') {
                                            if ($valuefinal->timeopen != 0) {
                                                $activitystartdates = $valuefinal->timeopen;
                                            } else {
                                                $activitystartdates = $coursedetails->startdate;
                                            }

                                            if ($valuefinal->timeopen != 0) {
                                                $activityenddatess = $valuefinal->timeopen;
                                            } else {
                                                if ($completionexpected != 0) {
                                                    $activityenddatess = $completionexpected;
                                                } else {
                                                    $activityenddatess = $coursedetails->enddate;
                                                }
                                            }
                                        } else if ($tbl == 'workshop') {
                                            if ($valuefinal->submissionstart != 0) {
                                                $activitystartdates = $valuefinal->submissionstart;
                                            } else {
                                                $activitystartdates = $coursedetails->startdate;
                                            }

                                            if ($valuefinal->submissionend != 0) {
                                                $activityenddatess = $valuefinal->submissionend;
                                            } else {
                                                if ($completionexpected != 0) {
                                                    $activityenddatess = $completionexpected;
                                                } else {
                                                    $activityenddatess = $coursedetails->enddate;
                                                }
                                            }
                                        } else if ($tbl == 'scorm') {
                                            if ($valuefinal->timeopen != 0) {
                                                $activitystartdates = $valuefinal->timeopen;
                                            } else {
                                                $activitystartdates = $coursedetails->startdate;
                                            }

                                            if ($valuefinal->timeclose != 0) {
                                                $activityenddatess = $valuefinal->timeclose;
                                            } else {
                                                if ($completionexpected != 0) {
                                                    $activityenddatess = $completionexpected;
                                                } else {
                                                    $activityenddatess = $coursedetails->enddate;
                                                }
                                            }
                                        } else {

                                            $activitystartdates = $coursedetails->startdate;

                                            if ($completionexpected != 0) {
                                                $activityenddatess = $completionexpected;
                                            } else {
                                                $activityenddatess = $coursedetails->enddate;
                                            }
                                        }

                                        $activityids = $DB->get_record(
                                            'course_modules',

                                            array('instance' => $instance, 'module' => $moduleid)
                                        );

                                        $alreadyenabled = $DB->get_record_sql("SELECT id FROM

                                                    {tool_leeloolxp_sync}
                                                    where activityid = ? and enabled = '1'
                                                    order by id desc limit 1", [$activityids->id]);
                                        $sectiondataa = $DB->get_record_sql("SELECT section FROM {course_modules}
                                            WHERE id = ? ", [$activityids->id]);

                                        $enabled = false;

                                        if (!empty($alreadyenabled)) {
                                            $enabled = true;
                                        }

                                        echo '<tr><td>';

                                        if ($oldsectionsname != $sectionsdetails->name) {
                                            echo $sectionsdetails->name . "-";

                                            echo $coursedetails->fullname;
                                        }

                                        echo '</td><td><div class="tqs-left">';

                                        $cm = $modinfo->cms[$activityids->id];

                                        if ($cm) {
                                            if ($cm->modname == 'quiz') {
                                                $quizid = $cm->get_course_module_record()->instance;
                                                $quizdata = $DB->get_record('quiz', array('id' => $quizid), '*', MUST_EXIST);

                                                if (isset($quizdata->quiztype)) {
                                                    if ($quizdata->quiztype == 'discover') {
                                                        $iconurl = $CFG->wwwroot .
                                                            '/local/leeloolxptrivias/pix/Discover_on.png';
                                                    } else if ($quizdata->quiztype == 'exercises') {
                                                        $iconurl = $CFG->wwwroot .
                                                            '/local/leeloolxptrivias/pix/Studycase_on.png';
                                                    } else if ($quizdata->quiztype == 'trivias') {
                                                        $iconurl = $CFG->wwwroot . '/local/leeloolxptrivias/pix/Trivia_on.png';
                                                    } else if ($quizdata->quiztype == 'assessments') {
                                                        $iconurl = $CFG->wwwroot .
                                                            '/local/leeloolxptrivias/pix/Assessments_on.png';
                                                    } else if ($quizdata->quiztype == 'quest') {
                                                        $iconurl = $CFG->wwwroot . '/local/leeloolxptrivias/pix/Quest_on.png';
                                                    } else if ($quizdata->quiztype == 'mission') {
                                                        $iconurl = $CFG->wwwroot . '/local/leeloolxptrivias/pix/Mission_on.png';
                                                    } else if ($quizdata->quiztype == 'duels') {
                                                        $iconurl = $CFG->wwwroot . '/local/leeloolxptrivias/pix/Duelos_on.png';
                                                    } else {
                                                        $iconurl = $cm->get_icon_url() . '?default';
                                                    }
                                                } else {
                                                    $iconurl = $cm->get_icon_url() . '?default';
                                                }
                                            } else {
                                                $iconurl = $cm->get_icon_url();
                                            }

                                            $icon = '<img src="' . $iconurl . '"

                                                        class="icon" alt="" />&nbsp;';
                                        } else {

                                            $icon = '<i class="fa fa-recycle"></i>';

                                            $iconurl = '';
                                        }

                                        if (isset($valuefinal->quiztype)) {
                                            $quiztype = $valuefinal->quiztype;
                                        } else {
                                            $quiztype = '';
                                        }

                                        $difficulty = '1';

                                        echo $icon;

                                        echo '<span>';

                                        $oldsectionsname = $sectionsdetails->name;

                                        echo $valuefinal->name;

                                        echo '</span></div></td><td>

                                                    <div class="tqs-right">

                                                    <span class="tqs-span-';

                                        if ($enabled) {
                                            echo "yes";
                                        } else {

                                            echo "no";
                                        }

                                        echo '">';

                                        if ($enabled) {
                                            echo "Yes";
                                        } else {

                                            echo "No";
                                        }

                                        echo '</span><ul>';

                                        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

                                        if ($enabled) {
                                            if (isset($reqredirect)) {
                                                $redirect = '&redirect=courseview';
                                            } else {

                                                $redirect = '';
                                            }

                                            echo '<li>
                                                <a href="' . $url . '?resync_activity=1&activity_id='
                                                . $activityids->id .
                                                '&activity_name='
                                                . $valuefinal->name .
                                                '&course_id='
                                                . $reqcourseid1 . $redirect . '">
                                                    ' . get_string('resync', 'tool_leeloolxp_sync') . '
                                                </a>
                                                </li>';
                                        }

                                        if ($enabled) {
                                            echo '<li>
                                                    <a onclick="UnsyncActivity(' . $activityids->id . ')" href="#">'
                                                . get_string('unsync', 'tool_leeloolxp_sync') .
                                                '</a>
                                                    </li>';
                                        } else {

                                            $querystring = $coursedetails->fullname . "$$" .

                                                $sectionsdetails->name . "$$" . $valuefinal->name . "$$" .

                                                $activityids->id . "$$" . $courseid . "$$" .

                                                $sectionsdetails->summary . "$$" .

                                                strip_tags($valuefinal->intro . "$$" .
                                                    $activitystartdates . "$$" .
                                                    $activityenddatess . "$$" . $tbl . "$$" . $iconurl
                                                    . "$$" . $quiztype . "$$" . $difficulty)
                                                . "$$" .

                                                $sectiondataa->section . "$$" . base64_encode(json_encode($moduledetail));

                                            echo '<li><input class="all_activity_checkbox_single"

                                                        type="checkbox" name="all_activities[]"

                                                        value="' . str_replace(
                                                '"',
                                                '',

                                                $querystring
                                            ) . '"></li>';
                                        }

                                        echo '</ul>';

                                        echo '</div></td>';

                                        if (isset($valuefinal->questionsperpage)) {
                                            $isquiz = $DB->get_record_sql(
                                                "SELECT id FROM {tool_leeloolxp_sync}
                                                    where activityid =  ? and is_quiz = '1'
                                                    order by id desc limit 1",
                                                [$activityids->id]
                                            );

                                            if (!empty($isquiz)) {
                                                $checked = true;
                                            } else {

                                                $checked = false;
                                            }

                                            echo '<td style="text-align: center"><input type="checkbox"';

                                            if ($checked) {
                                                echo "checked='checked'";
                                            }

                                            echo 'name="quiz_sync[]" class="quiz_sync_check"
                                                value="' . $activityids->id . '"></td></tr>';
                                        } else {

                                            echo '<td></td></tr>';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    if (isset($reqredirect)) {
        if ($reqredirect == 'courseview') {
            echo "<input type='hidden' name='redirecthidden' value= 'courseview'>";
        }
    }

    echo '<tr><td></td><td></td>

            <td>

            <input type="submit" name="sync_activities" value="Submit"></td>

            <td></td>

            </tr>

            </form>

            </table>';
}

?>

<script type="text/javascript">
    function check_all_is_quiz() {

        var maincheck_box = document.getElementById("all_is_quiz_checkbox").checked;

        if (maincheck_box) {

            var checkboxes = document.getElementsByClassName("quiz_sync_check");

            for (var i = 0; i < checkboxes.length; i++) {

                checkboxes[i].checked = true;

            }

        } else {

            var checkboxes = document.getElementsByClassName("quiz_sync_check");

            for (var i = 0; i < checkboxes.length; i++) {

                checkboxes[i].checked = false;

            }

        }



    }



    function check_all_activity() {

        var maincheck_box = document.getElementById("all_activity_checkbox").checked;

        if (maincheck_box) {

            var checkboxes = document.getElementsByClassName("all_activity_checkbox_single");

            for (var i = 0; i < checkboxes.length; i++) {

                checkboxes[i].checked = true;

            }

        } else {

            var checkboxes = document.getElementsByClassName("all_activity_checkbox_single");

            for (var i = 0; i < checkboxes.length; i++) {

                checkboxes[i].checked = false;

            }

        }

    }
</script>

<style>
    .card-header button {

        color: #5eb9ba;

    }

    .card-header button {

        padding: 0;

        color: #5eb9ba;

        font-size: 22px;

        font-weight: 300;

        background: none;

        border: none;

        display: block;

        width: 100%;

        text-align: left;

        cursor: pointer;

        outline: none;

    }

    .card-table table,
    .card-header table {

        width: 100%;

    }

    .card-table table td {

        color: #5eb9ba;

        font-size: 15px;

        padding: 10px;

        border: none;

    }

    .card-header table td {

        padding: 10px;

    }

    .card-table table td:first-child,
    .card-header table td:first-child {

        width: 65%;

    }

    .card-table table td:last-child,

    .card-header table td:last-child {

        width: 35%;

    }

    .card-table table td .tqs-right>* {

        display: inline-block;

    }

    .card-table table td .tqs-right ul {

        margin: 0 0 0 15px;

        list-style: none;

        padding: 0;

    }

    .card-table table td .tqs-right ul li {

        display: inline-block;

        margin: 0 5px;

    }

    .card-table table tr:nth-child(2) {

        background: #f2f2f2;

    }

    .card-table table td .tqs-right ul li a {

        color: #5eb9ba;

        font-size: 15px;

    }

    .card-table table td .tqs-right span.tqs-span-no,

    .card-table table td .tqs-right span.tqs-span-yes {

        padding: 7px;

        border-radius: 4px;

        color: #fff;

        min-width: 25px;

        text-align: center;

    }

    .card-table table td .tqs-right span.tqs-span-no {

        background: #d1d1d1;

    }

    .card-table table td .tqs-right span.tqs-span-yes {

        background: #5eb9ba;

    }

    .card-table table td .tqs-right span.tqs-span-info {

        float: right;

        font-size: 22px;

        font-weight: 700;



    }
</style>

<?php

echo $OUTPUT->footer(); ?>

<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">

<div class="dialog-modal dialog-modal-activity" style="display: none;">

    <div class="dialog-modal-inn">

        <div id="dialog">

            <h4><?php echo get_string('sure_unsync_ar', 'tool_leeloolxp_sync'); ?></h4>

            <p><?php echo get_string('unsync_warning_users', 'tool_leeloolxp_sync'); ?></p>

            <h3><?php echo get_string('cannotundo', 'tool_leeloolxp_sync'); ?></h3>

            <div class="sure-btn">

                <button data_id="" onclick="btn_yes_activityunsync();" class="btn btn_yes_activityunsync">
                    <?php echo get_string('yessure', 'tool_leeloolxp_sync'); ?>
                </button>

                <button onclick="activity_cls_popup();" class="btn activity_cls_popup">
                    <?php echo get_string('close', 'tool_leeloolxp_sync'); ?>
                </button>

            </div>

            <div class="anymore-link">

                <a href="#"><?php echo get_string('sure_not_needdata', 'tool_leeloolxp_sync'); ?></a>

            </div>

        </div>

    </div>

</div>



<div class="dialog-modal dialog-modal-course" style="display: none;">

    <div class="dialog-modal-inn">

        <div id="dialog">

            <h4><?php echo get_string('unsync_course', 'tool_leeloolxp_sync'); ?></h4>

            <p><?php echo get_string('unsync_course_warning', 'tool_leeloolxp_sync'); ?></p>

            <h3><?php echo get_string('cannotundo', 'tool_leeloolxp_sync'); ?></h3>

            <div class="sure-btn">

                <button data_id="" data_name="" onclick="btn_yes_courseunsync();" class="btn btn_yes_courseunsync">
                    <?php echo get_string('yessure', 'tool_leeloolxp_sync'); ?>
                </button>

                <button onclick="course_cls_popup();" class="btn course_cls_popup">
                    <?php echo get_string('close', 'tool_leeloolxp_sync'); ?>
                </button>

            </div>

            <div class="anymore-link">

                <a href="#"><?php echo get_string('sure_not_needdata', 'tool_leeloolxp_sync'); ?></a>

            </div>

        </div>

    </div>

</div>

<style type="text/css">
    .dialog-modal {

        position: fixed;

        top: 0;

        left: 0;

        width: 100%;

        height: 100%;

        z-index: 9999;

        background: rgba(0, 0, 0, 0.7);

        display: flex;

        align-items: center;

        justify-content: center;

    }

    .dialog-modal-inn {

        background: #fff;

        max-width: 750px;

        padding: 50px;

        text-align: center;

        width: 100%;

    }

    .dialog-modal-inn h4 {

        font-weight: 400;

        margin: 0 0 25px;

        font-size: 25px;

    }

    .dialog-modal-inn .sure-btn button {

        font-size: 20px;

        padding: .5rem 3rem;

        color: #fff;

        background-color: #74cfd0;

        border: none;

        display: inline-block;

        text-decoration: none;

        outline: none;

        box-shadow: none;

        margin: 10px 0;

    }

    .dialog-modal-inn div#dialog {

        font-size: 17px;

    }

    .dialog-modal-inn p {

        font-size: 19px;

    }

    .dialog-modal-inn h3 {

        font-weight: 500;

        font-size: 22px;

        color: #f60000;

    }

    .sure-btn {

        margin: 50px 0 0;

    }

    .anymore-link {

        margin: 15px 0 0;

    }

    .anymore-link a {

        color: #74cfd0;

        font-size: 17px;

    }

    #page-wrapper {
        z-index: -1 !important;
    }
</style>

<script type="text/javascript">
    function UnsyncActivity(id) {

        $('.dialog-modal-activity').show();

        $('.btn_yes_activityunsync').attr('data_id', id);

    }

    function activity_cls_popup() {

        $('.dialog-modal-activity').hide();

    }

    function course_cls_popup() {

        $('.dialog-modal-course').hide();

    }

    function btn_yes_activityunsync() {

        var id = $('.btn_yes_activityunsync').attr('data_id');

        var url = '<?php echo parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>';

        window.location = url + '?unsync_id=' + id;

    }

    function btn_yes_courseunsync() {

        var id = $('.btn_yes_courseunsync').attr('data_id');

        var name = $('.btn_yes_courseunsync').attr('data_name');

        var url = '<?php echo parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>';

        window.location = url + '?unsynccourse=' + name + '&id=' + id;

    }

    function UnsyncCourse(id) {

        var name = $(this).attr('data-coursename');

        $('.dialog-modal-course').show();

        $('.btn_yes_courseunsync').attr('data_id', id);

        $('.btn_yes_courseunsync').attr('data_name', name);

    }

    var slides = document.getElementsByClassName("quiz_data");

    for (var i = 0; i < slides.length; i++) {

        if (slides.item(i).innerHTML == '') {

            var data_id = slides.item(i).getAttribute("data_id");

            document.getElementsByClassName('collapse' + data_id).style = "display:none";

        }

    }
</script>

<style>
    table#acivity_sync_table {

        width: 100%;

    }



    table#acivity_sync_table th {

        font-size: 21px;

        font-weight: 400;

        padding: 8px;

    }



    table#acivity_sync_table td {

        padding: 8px;

        color: #c7c7c7;



    }



    table#acivity_sync_table td:nth-child(2) {

        color: #5eb9ba;

    }



    table#acivity_sync_table td ul {

        margin: 0;

        list-style: none;

        padding: 0;

        display: inline-block;

    }

    table#acivity_sync_table tbody tr {

        border-bottom: 1px solid #f4f4f4;

    }

    table#acivity_sync_table tbody tr:first-child {

        border-bottom: 1px solid #d9d9d9;

    }



    table#acivity_sync_table td .tqs-right span.tqs-span-no,



    table#acivity_sync_table td .tqs-right span.tqs-span-yes {

        padding: 5px;

        display: inline-block;

        font-size: 13px;

        border-radius: 4px;

        background: #f4f4f4;

        color: #333;

    }



    table#acivity_sync_table td .tqs-right span.tqs-span-yes {

        background: #0094bc;

        color: #fff;

    }

    table#acivity_sync_table td .tqs-right ul li a {

        color: #0094bc;

    }

    table#acivity_sync_table td .tqs-right span.tqs-span-info {

        float: right;

        color: #0094bc;

        padding-right: 10px;

    }

    table#acivity_sync_table td:first-child {

        color: #5eb9ba;

        font-size: 18px;

    }



    table#acivity_sync_table td ul li {

        display: inline-block;

        padding: 0 5px;

    }



    .back-arrow-left a {

        color: #666;

        font-size: 22px;

        font-weight: 300;

    }



    .back-arrow-left a i {

        padding-right: 10px;

        color: #5eb9ba;

    }

    .back-arrow-left {

        padding: 10px 0;

    }
</style>
