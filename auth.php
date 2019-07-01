<?php

/**
 * @author Fabio Pernegger
 *
 * Authentication Plugin: No Authentication
 *
 * No authentication at all. This method approves everything!
 *
 * 2019-02-21  File created.
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/authlib.php');

require_once("$CFG->dirroot/enrol/manual/externallib.php");
require_once("$CFG->dirroot/group/lib.php");

/**
 * Plugin for dgb
 */
class auth_plugin_dgb extends auth_plugin_base {

	/**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'dgb';
        $this->config = get_config('auth/dgb');
    }

    /**
     * Returns true if the username and password work or don't exist and false
     * if the user exists and the password is wrong.
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool Authentication success or failure.
     */
    function user_login ($username, $password) {
		global $CFG, $DB;
        if ($user = $DB->get_record('user', array('username'=>$username, 'mnethostid'=>$CFG->mnet_localhost_id))) {
            return validate_internal_user_password($user, $password);
        }
        return false;
    }

    /**
     * Updates the user's password.
     *
     * called when the user password is updated.
     *
     * @param  object  $user        User table object
     * @param  string  $newpassword Plaintext password
     * @return boolean result
     *
     */
    function user_update_password($user, $newpassword) {
        $user = get_complete_user_data('id', $user->id);
        return update_internal_user_password($user, $newpassword);
    }

    function prevent_local_passwords() {
        return false;
    }

    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    function is_internal() {
        return true;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    function can_change_password() {
        return false;
    }

    /**
     * Returns the URL for changing the user's pw, or empty if the default can
     * be used.
     *
     * @return moodle_url
     */
    function change_password_url() {
        return null;
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool
     */
    function can_reset_password() {
        return false;
    }

    /**
     * Prints a form for configuring this authentication plugin.
     *
     * This function is called from admin/auth.php, and outputs a full page with
     * a form for configuring this plugin.
     *
     * @param array $page An object containing all the data for this page.
     */
    function config_form($config, $err, $user_fields) {
        include "config.html";
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     */
    function process_config($config) {
        return true;
    }
	
    /**
     * creates a new user and adds the user to a cohort he decided
     */
	function loginpage_hook(){
        if (isset($_POST['cohortcode'])) {
            global $DB, $CFG;

            $cohortcode = optional_param('cohortcode', 0, PARAM_ALPHANUMEXT);
            $username = optional_param('username', 0, PARAM_ALPHANUMEXT);
            $password = optional_param('password', 0, PARAM_TEXT);

                if ($DB->record_exists('block_exacompcohortcode', array('cohortcode' => $cohortcode))) {
                    if ($username && !$DB->record_exists('user', array('username' => $username))) {
                        $newuser = new stdClass();
                        $newuser->username = $username;
                        $newuser->firstname = $username;
                        $newuser->lastname = $username;
                        $newuser->password = md5($password);
                        $newuser->auth = 'dgb';
                        $newuser->mnethostid = $CFG->mnet_localhost_id;
                        $newuser->confirmed = 1;
                        $newuser->timecreated = time();
                        $userid = $DB->insert_record('user', $newuser);
                        $record = $DB->get_record('block_exacompcohortcode', array('cohortcode' => $cohortcode));
                        $DB->insert_record('cohort_members', array('cohortid' => $record->cohortid, 'userid' => $userid, 'timeadded' => time()));
                        enrol_try_internal_enrol(get_config('auth_'.$this->authtype, 'courseid'), $userid, 5);
                        $DB->insert_record('block_exacompexternaltrainer', array('trainerid' => $record->trainerid, 'studentid' => $userid));

                        return true;
                    } else {
                        return print_error('error_user_exists', 'auth_dgb');
                    }
                } else {
                    return print_error('error_wrong_cohortcode', 'auth_dgb');
                }
            }
        }
   }

