<?php

//$Id: block_fn_marking.php,v 1.4 2010/01/12 14:22:06 mchurch Exp $

/**
 * Simple  class for block
 *
 * @copyright 2011 Moodlefn
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_fn_admin extends block_list {

    /**
     * Sets the block title
     *
     * @return none
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_fn_admin');
    }

    /**
     * Constrols the block title based on instance configuration
     *
     * @return bool
     */
    public function specialization() {
        global $course;

        /// Need the bigger course object.
        $this->course = $course;

        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_fn_admin');
        } else {
            $this->title = $this->config->title;
        }

        if (!isset($this->config->showadminmenuitems)) {
            $this->config->showadminmenuitems = 1;
        }

        if (!isset($this->config->showenrolunenollink)) {
            $this->config->showenrolunenollink = 1;
        }

        if (!isset($this->config->showprofilelink)) {
            $this->config->showprofilelink = 1;
        }
        if (!isset($this->config->showgradelink)) {
            $this->config->showgradelink = 1;
        }

        if (!isset($this->config->showteachertools)) {
            $this->config->showteachertools = 1;
        }
    }

    /**
     * Constrols the block title based on instance configuration
     *
     * @return bool
     */
    public function instance_allow_config() {
        return true;
    }

    /**
     * Creates the blocks main content
     *
     * @return string
     */
    public function get_content() {
        global $course;
        global $CFG, $USER, $DB, $OUTPUT;

        /// Need the bigger course object.
        $this->course = $course;

        if ($this->content !== null) {
            return $this->content;
        }
        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';
        $context = get_context_instance(CONTEXT_COURSE, $this->course->id);
        $isteacher = has_capability('moodle/grade:viewall', $context);

        if (!$isteacher) {
            return $this->content;
        }

        $this->get_fnblock_content();
        return $this->content;
    }

    /**
     * Defines where the block can be added
     *
     * @return array
     */
    public function applicable_formats() {
        // Default case: the block can be used in all course types
        return array('all' => false,
            'course-*' => true);
    }

    /**
     * Function to return the standard content, used in all versions.
     *
     */
    private function get_fnblock_content() {
        global $USER, $CFG, $THEME, $SESSION, $PAGE;
        global $course;
        global $DB, $OUTPUT;

        /// Need the bigger course object.
        $this->course = $course;

        require_once($CFG->dirroot . '/blocks/fn_marking/lib.php');
        require_once($CFG->dirroot . '/mod/forum/lib.php');
        $groupbuttons = $this->course->groupmode;
        $groupbuttonslink = (!$this->course->groupmodeforce);
        $isadmin = is_primary_admin($this->course->id);
        $context = get_context_instance(CONTEXT_COURSE, $this->course->id);
        $isteacheredit = has_capability('moodle/course:update', $context);
        $isediting = $PAGE->user_is_editing();
        $ismoving = ismoving($this->course->id);

        if ($ismoving) {
            $strmovehere = get_string("movehere");
            $strmovefull = strip_tags(get_string("movefull", "", "'$USER->activitycopyname'"));
        }

        ///Course Teacher Menu:
        if (($this->course->id != SITEID)) {
            /// moodle admin items
            if ($this->config->showadminmenuitems && $this->config->showadminmenuitems == 1) {

                /// show editing on button
                if ($course->id !== SITEID and has_capability('moodle/course:update', $context)) {
                    $this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/fn_admin/pix/edit.gif" height="16"
                                                        width="16" alt="">';
                    if ($PAGE->user_is_editing($this->course->id)) {
                        $this->content->items[] = '<a href="view.php?id=' . $this->course->id . '&amp;edit=off&amp;
                                                            sesskey=' . sesskey() . '">' . get_string('turneditingoff') . '</a>';
                    } else {
                        $this->content->items[] = '<a href="view.php?id=' . $this->course->id . '&amp;edit=on&amp;
                                                            sesskey=' . sesskey() . '">' . get_string('turneditingon') . '</a>';
                    }
                    $this->content->items[] = '<a href="' . $CFG->wwwroot . '/course/edit.php?id=' . $this->course->id . '">
                                                        ' . get_string('settings') . '</a>';
                    $this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/fn_admin/pix/settings.gif"
                                                        height="16" width="16" alt="">';
                }

                ///show role assign link
                if ($course->id != SITEID) {
                    if (has_capability('moodle/role:assign', $context)) {
                        $this->content->items[] = '<a href="' . $CFG->wwwroot . '/' . $CFG->admin . '/roles/assign.php?contextid=' . $context->id . '">' . get_string('assignroles', 'role') . '</a>';
                        $this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/fn_admin/pix/roles.gif"
                                                        height="16" width="16" alt="">';
                    } else if (get_overridable_roles($context, 'name', ROLENAME_ORIGINAL)) {
                        $this->content->items[] = '<a href="' . $CFG->wwwroot . '/' . $CFG->admin . '/roles/override.php?contextid=' . $context->id . '">' . get_string('overridepermissions', 'role') . '</a>';
                        $this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/fn_admin/pix/roles.gif"
                                                        height="16" width="16" alt="">';
                    }
                }

                /// Backup this course
                if ($course->id !== SITEID and has_capability('moodle/backup:backupcourse', $context)) {
                    $this->content->items[] = '<a href="' . $CFG->wwwroot . '/backup/backup.php?id=' . $this->course->id . '">' . get_string('backup') . '</a>';
                    $this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/fn_admin/pix/backup.gif"
                                                        height="16" width="16" alt="">';
                }

                /// Restore to this course
                if ($course->id !== SITEID and has_capability('moodle/course:managefiles', $context)) {
                    $this->content->items[] = '<a href="' . $CFG->wwwroot . '/backup/restorefile.php?contextid=' . $context->id . '">' . get_string('restore') . '</a>';
                    $this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/fn_admin/pix/restore.gif"
                                                        height="16" width="16" alt="">';
                }

                /// Import data from other courses
                if (!$course->id !== SITEID and has_capability('moodle/backup:backuptargetimport', $context)) {
                    $this->content->items[] = '<a href="' . $CFG->wwwroot . '/backup/import.php?id=' . $this->course->id . '">' . get_string('import') . '</a>';
                    $this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/fn_admin/pix/import.gif"
                                                        height="16" width="16" alt="">';
                }

                /// Reset this course
                if ($course->id !== SITEID and has_capability('moodle/course:reset', $context)) {
                    $this->content->items[] = '<a href="' . $CFG->wwwroot . '/course/reset.php?id=' . $this->course->id . '">' . get_string('reset') . '</a>';
                    $this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/fn_admin/pix/reset.gif"
                                                        height="16" width="16" alt="" class="icon">';
                }

                /// Unenrol link
                if (empty($course->metacourse) && ($course->id !== SITEID)) {
                    if (has_capability('moodle/course:update', $context, null, false)) {   // Are a guest now
                        $this->content->items[]='<a href="'.$CFG->wwwroot.'/course/enrol.php?id='.$this->course->id.'">
                                                       '.get_string('enrolme', 'block_fn_admin', format_string($course->shortname)).'</a>';
                        $this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/fn_admin/pix/user.gif"
                                                       height="16" width="16" alt="" class="icon">';
                    } else if (has_capability('moodle/course:update', $context, null, false) &&
                                                      get_user_roles($context, $USER->id, false)) {  // Have some role
                        $this->content->items[]='<a href="'.$CFG->wwwroot.'/course/unenrol.php?id='.$this->instance->pageid.'">
                                                        '.get_string('unenrolme', '', format_string($course->shortname)).'</a>';
                        $this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/fn_admin/pix/user.gif"
                                                          height="16" width="16" alt="" class="icon">';
                    }
                }
                /// Link to the user own profile (except guests)
                if (!isguestuser() and isloggedin() && $this->config->showprofilelink && $this->config->showprofilelink ==1) {
                    $this->content->items[]='<a href="'.$CFG->wwwroot.'/user/view.php?id='.$USER->id.'&amp;course='.$course->id.'">'.get_string('profile').'</a>';
                    $this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/fn_admin/pix/user.gif"
                                                        height="16" width="16" alt="" class="icon">';
                }
                /// show report link
                if ($course->id !== SITEID) {
                    $reportavailable = false;
                    if (has_capability('moodle/grade:viewall', $context)) {
                        $reportavailable = true;
                    } else if (!empty($course->showgrades)) {
                        if ($reports = get_list_of_plugins('grade/report')) {     // Get all installed reports
                            arsort($reports); // user is last, we want to test it first
                            foreach ($reports as $plugin) {
                                if (has_capability('gradereport/' . $plugin . ':view', $context)) {
                                    //stop when the first visible plugin is found
                                    $reportavailable = true;
                                    break;
                                }
                            }
                        }
                    }

                    if ($reportavailable) {
                        $this->content->items[] = '<a href="' . $CFG->wwwroot . '/grade/report/index.php?id=' . $this->course->id . '">' . get_string('grades') . '</a>';
                        $this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/fn_admin/pix/roles.gif"
                                                            height="16" width="16" alt="">';
                    }
                }

                /// Course outcomes (to help give it more prominence because it's important)
                if (!empty($CFG->enableoutcomes)) {
                    if ($course->id !== SITEID and has_capability('moodle/course:update', $context)) {
                        $this->content->items[] = '<a href="' . $CFG->wwwroot . '/grade/edit/outcome/course.php?id=' . $this->course->id . '">' . get_string('outcomes', 'grades') . '</a>';
                        $this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/fn_admin/pix/outcomes.gif"
                                                            height="16" width="16" alt="">';
                    }
                }
            }

            /// teacher tool section
            if ($this->config->showteachertools && $this->config->showteachertools == 1) {
                if ($this->config->showadminmenuitems && $this->config->showadminmenuitems == 1) {                   
                    $this->content->items[] = "<div style='width:162px;'><hr /></div>";
                    $this->content->icons[] = "";
                }
                $this->content->items[] = '<div class="sectionheader"><strong>'. get_string('teachertools'
                                                        , 'block_fn_admin') . '</strong></div>';
                $this->content->icons[] = "";
                if (file_exists($CFG->dirroot . '/blocks/fn_marking/lib.php') && has_capability('moodle/grade:viewall', $context)) {
                    require_once($CFG->dirroot . '/blocks/fn_marking/lib.php');
                    $numung = count_unmarked_activities($this->course);
                    $this->content->items[] = '<a href="' . $CFG->wwwroot . '/blocks/fn_marking/fn_gradebook.php?id=' . $this->course->id . '&show=unmarked' .
                            '&navlevel=top">' . $numung . ' Unmarked Activities</a>';
                    $this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/fn_marking/pix/unmarked.gif"
                                                        height="16" width="16" alt="">';
                }

                /// show grade link
                if ($this->config->showgradelink && $this->config->showgradelink == 1) {
                    global $OUTPUT;
                    $this->content->items[] = '<a href="' . $CFG->wwwroot . '/grade/report/index.php?id=' . $this->course->id .
                            '&navlevel=top">' . get_string('gradeslink', 'block_fn_marking') . '</a>';
                    $this->content->icons[] = "<img src=\"" . $OUTPUT->pix_url('i/grades') . "\" class=\"icon\" alt=\"\" />";
                }

                /// show report link
                global $OUTPUT;
                $this->content->items[] = '<a href="' . $CFG->wwwroot . '/course/report.php?id=' . $this->course->id .
                        '&navlevel=top">' . get_string('reportslink', 'block_fn_marking') . '</a>';
                $this->content->icons[] = "<img src=\"" . $OUTPUT->pix_url('i/log') . "\" class=\"icon\" alt=\"\" />";
                /// Manage questions
                if ($course->id !== SITEID) {
                    $questionlink = '';
                    $questioncaps = array(
                        'moodle/question:add',
                        'moodle/question:editmine',
                        'moodle/question:editall',
                        'moodle/question:viewmine',
                        'moodle/question:viewall',
                        'moodle/question:movemine',
                        'moodle/question:moveall');
                    foreach ($questioncaps as $questioncap) {
                        if (has_capability($questioncap, $context)) {
                            $questionlink = 'edit.php';
                            break;
                        }
                    }
                    if (!$questionlink && has_capability('moodle/question:managecategory', $context)) {
                        $questionlink = 'category.php';
                    }
                    if ($questionlink) {
                        $this->content->items[] = '<a href="' . $CFG->wwwroot . '/question/' . $questionlink .
                                '?courseid=' . $this->course->id . '">' . get_string('questions', 'quiz') . '</a>';
                        $this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/fn_admin/pix/questions.gif"
                                                            height="16" width="16" alt="">';
                    }
                }

                /// Manage groups in this course
                if (($course->id !== SITEID) && ($course->groupmode || !$course->groupmodeforce)
                                            && has_capability('moodle/course:managegroups', $context)) {
                    $strgroups = get_string('groups');
                    $this->content->items[] = '<a title="' . $strgroups . '" href="' . $CFG->wwwroot . '/group/index.php?id=' . $this->course->id . '">' . $strgroups . '</a>';
                    $this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/fn_admin/pix/group.gif"
                                            height="16" width="16" alt="">';
                }

                /// Manage own file link
                global $PAGE;
                if ($course->id !== SITEID and has_capability('moodle/user:manageownfiles', $context)) {
                    $this->content->items[] = '<a href="' . $CFG->wwwroot . '/user/filesedit.php?returnurl=' . $PAGE->url->out() . '">
                                                ' . get_string('files') . '</a>';
                    $this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/fn_admin/pix/files.gif"
                                                        height="16" width="16" alt="">';
                }
            }
            $customcourse = file_exists($CFG->dirroot . '/course/format/' . $course->format . '/settings.php');
            if ($customcourse && has_capability('moodle/course:update', $context)) {
                if ($this->config->showteachertools && $this->config->showteachertools == 1) {                   
                    $this->content->items[] = "<div style='width:162px;'><hr /></div>";
                    $this->content->icons[] = "";
                }
                $this->content->items[] = '<div class="sectionheader"><strong>' . get_string('controlcentre',
                                                'block_fn_admin') . '</strong></div>';
                $this->content->icons[] = "";
                $this->content->items[] =  '<a href="'.$CFG->wwwroot.'/course/format/'.$course->format.'/settings.php?id='.$course->id.'&extraonly=1">'.
                                      get_string('coursesettings', 'block_fn_admin').'</a>';
                $this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/fn_admin/pix/setting.gif" height="16" width="16" alt="">';
            }
        }
        return $this->content;
    }
}
