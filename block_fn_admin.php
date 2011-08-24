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
        global $course,$CFG, $USER, $DB, $OUTPUT;

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
        global $course, $USER, $CFG;

        /// Need the bigger course object.
        $this->course = $course;                
        $context = get_context_instance(CONTEXT_COURSE, $this->course->id);
        $isteacheredit = has_capability('moodle/course:update', $context);        
        $ismoving = ismoving($this->course->id);

        if ($ismoving) {
            $strmovehere = get_string("movehere");
            $strmovefull = strip_tags(get_string("movefull", "", "'$USER->activitycopyname'"));
        }

        ///Course Teacher Menu:
        if (($this->course->id != SITEID)) {
           
            $customcourse = file_exists($CFG->dirroot . '/course/format/' . $course->format . '/settings.php');
            if ($customcourse && has_capability('moodle/course:update', $context)) {              
                $this->content->items[] =  '<a href="'.$CFG->wwwroot.'/course/format/'.$course->format.'/settings.php?id='.$course->id.'&extraonly=1">'.
                                      get_string('coursesettings', 'block_fn_admin').'</a>';
                $this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/fn_admin/pix/setting.gif" height="16" width="16" alt="" STYLE="margin-right: 7px">';
            }
        }
        return $this->content;
    }
}
