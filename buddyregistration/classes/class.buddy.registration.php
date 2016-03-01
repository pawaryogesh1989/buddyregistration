<?php

require('class.buddy.profile.php');

//Main Plugin Class

class Buddy_Registration extends WP_Widget
{
    
    static $instance;
    
    //Constructor of the Class
    public function Buddy_Registration()
    {
        
        self::$instance = $this;
        parent::WP_Widget(false, $name = __('BuddyPress Registration Form', 'wp_widget_plugin'));
        
        add_action('wp_enqueue_scripts', array(
            $this,
            'Buddy_Registration_Scripts'
        ));
        
        add_action('bp_core_screen_signup', array(
            $this,
            'buddydev_redirect_on_signup'
        ));
        
        add_action('bp_init', array(
            $this,
            'buddydev_process_signup_errors'
        ));
        
        register_activation_hook('buddyregistration/classes/class.buddy.registration.php', array(
            $this,
            'hook_activate'
        ));
        register_deactivation_hook('buddyregistration/classes/class.buddy.registration.php', array(
            $this,
            'hook_deactivate'
        ));
        
        add_filter('bp_is_profile_cover_image_active', '__return_false');
        add_filter('bp_is_groups_cover_image_active', '__return_false');
        
    }
    
    /* Function to include scripts necessary for the plugin.
     * Scripts are saved in the JS folder of the plugin.
     */
    
    public function Buddy_Registration_Scripts()
    {
        wp_enqueue_script('buddy-registration-script', AUTH_PLUGINS_PATH . '/buddyregistration/js/buddyregistration.js', array(), '1.0.0', true);
    }
    
    function hook_activate()
    {
        
        if (!current_user_can('activate_plugins'))
            return;
        $plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
        check_admin_referer("activate-plugin_{$plugin}");
    }
    
    function hook_deactivate()
    {
        
        if (!current_user_can('activate_plugins'))
            return;
        $plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
        check_admin_referer("deactivate-plugin_{$plugin}");
    }
    
    // widget display
    function widget($args, $instance)
    {
        if (!is_user_logged_in()) {
            extract($args);
            // these are the widget options
            $title    = apply_filters('widget_title', $instance['title']);
            $text     = $instance['text'];
            $textarea = $instance['textarea'];
            echo $before_widget;
            // Display the widget
            echo '<div class="widget-text wp_widget_plugin_box">';
            require_once(BUDDY_FILE_DIRECTORY . "/templates/form-template.php");
            echo '</div>';
            echo $after_widget;
        }
    }
    
    /**
     * If the signup form is being processed, Redirect to the page where the signup form is
     *
     */
    function buddydev_redirect_on_signup()
    {
        
        if ('POST' !== strtoupper($_SERVER['REQUEST_METHOD']))
            return;
        
        $bp = buddypress();
        
        //only if bp signup object is set
        if (!empty($bp->signup)) {
            //save the signup object and submitted post data
            $_SESSION['buddydev_signup']        = $bp->signup;
            $_SESSION['buddydev_signup_fields'] = $_POST;
        }
        
        bp_core_redirect(wp_get_referer());
    }
    
    
    function buddydev_process_signup_errors()
    {
        
        //we don't need to process if the user is logged in
        if (is_user_logged_in())
            return;
        
        //if session was not started by another code, let us begin the session
        if (!session_id())
            session_start();
        
        //check if the current request
        if (!empty($_SESSION['buddydev_signup'])) {
            
            $bp         = buddypress();
            //restore the old signup object
            $bp->signup = $_SESSION['buddydev_signup'];
            
            //we are sure that it is our redirect from the buddydev_redirect_on_signup function, so we can safely replace the $_POST array
            if (isset($bp->signup->errors) && !empty($bp->signup->errors))
                $_POST = $_SESSION['buddydev_signup_fields']; //we need to restore so that the signup form can show the old data
            
            $errors = array();
            
            if (isset($bp->signup->errors))
                $errors = $bp->signup->errors;
            
            foreach ((array) $errors as $fieldname => $error_message) {
                
                add_action('bp_' . $fieldname . '_errors', create_function('', 'echo apply_filters(\'bp_members_signup_error_message\', "<div class=\"error\">" . stripslashes( \'' . addslashes($error_message) . '\' ) . "</div>" );'));
            }
            //remove from session
            $_SESSION['buddydev_signup']        = null;
            $_SESSION['buddydev_signup_fields'] = null;
        }
    }
    
}
?>