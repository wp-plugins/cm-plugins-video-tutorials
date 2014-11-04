<?php

class CMPluginsVideoTutorialsFrontend
{
    public static $calledClassName;
    protected static $instance = NULL;
    protected static $cssPath = NULL;
    protected static $jsPath = NULL;
    protected static $viewsPath = NULL;

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

        self::$cssPath = CMPVT_PLUGIN_URL . 'frontend/assets/css/';
        self::$jsPath = CMPVT_PLUGIN_URL . 'frontend/assets/js/';
        self::$viewsPath = CMPVT_PLUGIN_DIR . 'frontend/views/';

        add_filter('wp_enqueue_scripts', array(self::$calledClassName, 'cmpvt_enqueue_styles'));
    }

    public static function cmpvt_enqueue_styles()
    {
        //Registering Scripts & Styles for the FrontEnd
//        wp_enqueue_style('cmpvt-style', self::$cssPath . 'cmpvt-style.css');
//        wp_enqueue_script('cmpvt-functions', self::$jsPath . 'cmpvt-functions.js', array('jquery'));
    }

}