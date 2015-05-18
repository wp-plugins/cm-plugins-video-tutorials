<?php
/*
  Plugin Name: CM Plugins Video Tutorials
  Plugin URI:https://plugins.cminds.com/cm-video-tutorials-for-wordpress/
  Description: Display a gallery of CM Plugins video tutorials in the WordpPress admin dashboard
  Version: 1.0.3
  Author: CreativeMindsSolutions
  Author URI: https://www.cminds.com
 */
/*

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
// Exit if accessed directly
if( !defined('ABSPATH') )
{
    exit;
}

/**
 * Main plugin class file.
 * What it does:
 * - checks which part of the plugin should be affected by the query frontend or backend and passes the control to the right controller
 * - manages installation
 * - manages uninstallation
 * - defines the things that should be global in the plugin scope (settings etc.)
 * @author ThinkLearnEarnSolutions
 */
class CMPluginsVideoTutorials
{
    public static $calledClassName;
    protected static $instance = NULL;

    const SHORTCODE_PAGE_OPTION = 'cmpvt_shortcode_page_id';

    /**
     * Main Instance
     *
     * Insures that only one instance of class exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 1.0
     * @static
     * @staticvar array $instance
     * @return The one true AKRSubscribeNotifications
     */
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

        /*
         * Shared
         */

        include_once CMPVT_PLUGIN_DIR . '/shared/classes/Labels.php';
        if( !class_exists('CMVimeo') )
        {
            include_once CMPVT_PLUGIN_DIR . '/shared/libs/vimeo/vimeo.php';
        }

        include_once CMPVT_PLUGIN_DIR . '/shared/cm-plugins-video-tutorials-shared.php';
        $CMPluginsVideoTutorialsSharedInstance = CMPluginsVideoTutorialsShared::instance();

        if( is_admin() )
        {
            /*
             * Backend
             */
            include_once CMPVT_PLUGIN_DIR . '/backend/cm-plugins-video-tutorials-backend.php';
            $CMPluginsVideoTutorialsBackendInstance = CMPluginsVideoTutorialsBackend::instance();
        }
        else
        {
            /*
             * Frontend
             */
            include_once CMPVT_PLUGIN_DIR . '/frontend/cm-plugins-video-tutorials-frontend.php';
            $CMPluginsVideoTutorialsFrontendInstance = CMPluginsVideoTutorialsFrontend::instance();
        }
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
        /**
         * Define Plugin Version
         *
         * @since 1.0
         */
        if( !defined('CMPVT_VERSION') )
        {
            define('CMPVT_VERSION', '1.0.2');
        }

        /**
         * Define Plugin Directory
         *
         * @since 1.0
         */
        if( !defined('CMPVT_PLUGIN_DIR') )
        {
            define('CMPVT_PLUGIN_DIR', plugin_dir_path(__FILE__));
        }

        /**
         * Define Plugin URL
         *
         * @since 1.0
         */
        if( !defined('CMPVT_PLUGIN_URL') )
        {
            define('CMPVT_PLUGIN_URL', plugin_dir_url(__FILE__));
        }

        /**
         * Define Plugin File Name
         *
         * @since 1.0
         */
        if( !defined('CMPVT_PLUGIN_FILE') )
        {
            define('CMPVT_PLUGIN_FILE', __FILE__);
        }

        /**
         * Define Plugin Slug name
         *
         * @since 1.0
         */
        if( !defined('CMPVT_SLUG_NAME') )
        {
            define('CMPVT_SLUG_NAME', 'cm-plugins-video-tutorials');
        }

        /**
         * Define Plugin name
         *
         * @since 1.0
         */
        if( !defined('CMPVT_NAME') )
        {
            define('CMPVT_NAME', 'CM Plugins Video Tutorials');
        }

        /**
         * Define Plugin basename
         *
         * @since 1.0
         */
        if( !defined('CMPVT_PLUGIN') )
        {
            define('CMPVT_PLUGIN', plugin_basename(__FILE__));
        }

        /**
         * Define Plugin code
         *
         * @since 1.0
         */
        if( !defined('CMPVT_CODE') )
        {
            define('CMPVT_CODE', 'cmpvt');
        }
    }

    public static function _install()
    {
        global $user_ID;
        return;
    }

    public static function _uninstall()
    {
        return;
    }

    public function registerAjaxFunctions()
    {
        return;
    }

    /**
     * Get localized string.
     *
     * @param string $msg
     * @return string
     */
    public static function __($msg)
    {
        return __($msg, CMPVT_SLUG_NAME);
    }

    /**
     * Get item meta
     *
     * @param string $msg
     * @return string
     */
    public static function meta($id, $key, $default = null)
    {
        $result = get_post_meta($id, $key, true);
        if( $default !== null )
        {
            $result = !empty($result) ? $result : $default;
        }
        return $result;
    }

}

/**
 * The main function responsible for returning the one true plugin class
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $marcinPluginPrototype = MarcinPluginPrototypePlugin(); ?>
 *
 * @since 1.0
 * @return object The one true CMPluginsVideoTutorialsInit instance
 */
function CMPluginsVideoTutorialsInit()
{
    return CMPluginsVideoTutorials::instance();
}

$CMPluginsVideoTutorials = CMPluginsVideoTutorialsInit();

register_activation_hook(__FILE__, array('CMPluginsVideoTutorials', '_install'));
register_deactivation_hook(__FILE__, array('CMPluginsVideoTutorials', '_uninstall'));
