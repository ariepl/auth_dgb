<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    // Introductory explanation.
    $settings->add(new admin_setting_heading('auth_dgb/pluginname', '',
        new lang_string('auth_dgbdescription', 'auth_dgb')));
    
    $settings->add(new admin_setting_configselect('auth_dgb/courseid',
        new lang_string('select_course_key', 'auth_dgb'),
        new lang_string('select_course', 'auth_dgb'), 0, block_exacomp_get_courseids()));
    
    $settings->add(new block_exacomp_admin_setting_extraconfigtext('auth_dgb/contextid',
        new lang_string('context_id', 'auth_dgb'),
        new lang_string('context_discription', 'auth_dgb'), 1, PARAM_INT));
}
