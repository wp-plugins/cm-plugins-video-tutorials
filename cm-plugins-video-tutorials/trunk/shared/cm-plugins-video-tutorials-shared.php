<?php
if( !defined('ABSPATH') )
{
    exit;
}

class CMPluginsVideoTutorialsShared
{
    protected static $instance = NULL;
    public static $calledClassName;

    public static function instance()
    {
        $class = __CLASS__;
        if( !isset(self::$instance) && !( self::$instance instanceof $class ) )
        {
            self::$instance = new $class;
        }
        return self::$instance;
    }

    public function __construct()
    {
        if( empty(self::$calledClassName) )
        {
            self::$calledClassName = __CLASS__;
        }

        self::setupConstants();
        self::setupOptions();
        self::loadClasses();
        self::registerActions();
    }

    /**
     * Register the plugin's shared actions (both backend and frontend)
     */
    private static function registerActions()
    {
//        add_action('init', array(self::$calledClassName, 'registerPostTypeAndTaxonomies'));
    }

    /**
     * Setup plugin constants
     *
     * @access private
     * @since 1.1
     * @return void
     */
    private static function setupConstants()
    {

    }

    /**
     * Setup plugin constants
     *
     * @access private
     * @since 1.1
     * @return void
     */
    private static function setupOptions()
    {
        /*
         * Adding additional options
         */
        do_action('cmpvt_setup_options');
    }

    /**
     * Create taxonomies
     */
    public static function cmpvt_create_taxonomies()
    {
        return;
    }

    /**
     * Load plugin's required classes
     *
     * @access private
     * @since 1.1
     * @return void
     */
    private static function loadClasses()
    {
        /*
         * Load the file with shared global functions
         */
        include_once CMPVT_PLUGIN_DIR . "shared/functions.php";
    }

    public function registerShortcodes()
    {
        return;
    }

    public function registerFilters()
    {
        return;
    }

    public static function initSession()
    {
        if( !session_id() )
        {
            session_start();
        }
    }

}