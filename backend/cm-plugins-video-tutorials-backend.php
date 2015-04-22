<?php
if( !defined('ABSPATH') )
{
    exit;
}

class CMPluginsVideoTutorialsBackend
{
    public static $calledClassName;
    protected static $instance = NULL;
    protected static $cssPath = NULL;
    protected static $jsPath = NULL;
    protected static $viewsPath = NULL;

    const PAGE_YEARLY_OFFER = 'https://www.cminds.com/store/cm-wordpress-plugins-yearly-membership/';

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

        self::$cssPath = CMPVT_PLUGIN_URL . 'backend/assets/css/';
        self::$jsPath = CMPVT_PLUGIN_URL . 'backend/assets/js/';
        self::$viewsPath = CMPVT_PLUGIN_DIR . 'backend/views/';

        add_action('admin_enqueue_scripts', array(self::$calledClassName, 'cmpvt_enqeue_scripts'));
        add_action('admin_menu', array(self::$calledClassName, 'cmpvt_admin_menu'));
    }

    public static function cmpvt_enqeue_scripts()
    {
        $currentScreen = get_current_screen();

        if( $currentScreen->id == 'toplevel_page_cm-plugins-video-tutorials' )
        {
            $path = self::$jsPath . 'cmpvt_admin_scripts.js';
            wp_enqueue_script('cmpvt-admin-functions', $path, array('jquery'));
        }

        wp_enqueue_style('cmpvt-admin-styles', self::$cssPath . 'cmpvt_admin_styles.css');
    }

    public static function cmpvt_admin_menu()
    {
        global $submenu;
        
        add_menu_page('CM Plugins Video Tutorials', __('CM Plugins Video Tutorials', CMPVT_SLUG_NAME), 'read', CMPVT_SLUG_NAME, array(self::$calledClassName, 'cmpvt_render_page'));
        add_submenu_page(CMPVT_SLUG_NAME, 'About', __('About', CMPVT_SLUG_NAME), 'manage_options', CMPVT_SLUG_NAME . '-about', array(self::$calledClassName, 'cmpvt_render_page'));
//        add_submenu_page(CMPVT_SLUG_NAME, 'User Guide', __('User Guide', CMPVT_SLUG_NAME), 'manage_options', CMPVT_SLUG_NAME . '-userguide', array(self::$calledClassName, 'cmpvt_render_page'));

        if( current_user_can('manage_options') )
        {
            $submenu[CMPVT_SLUG_NAME][999] = array('Yearly membership offer', 'manage_options', self::PAGE_YEARLY_OFFER);
            add_action('admin_head', array(__CLASS__, 'admin_head'));
        }
    }

    public static function admin_head()
    {
        echo '<style type="text/css">
        		#toplevel_page_'.CMPVT_SLUG_NAME.' a[href*="cm-wordpress-plugins-yearly-membership"] {color: white;}
    			a[href*="cm-wordpress-plugins-yearly-membership"]:before {font-size: 16px; vertical-align: middle; padding-right: 5px; color: #d54e21;
    				content: "\f487";
				    display: inline-block;
					-webkit-font-smoothing: antialiased;
					font: normal 16px/1 \'dashicons\';
    			}
    			#toplevel_page_'.CMPVT_SLUG_NAME.' a[href*="cm-wordpress-plugins-yearly-membership"]:before {vertical-align: bottom;}

        	</style>';
    }

    public static function cmpvt_render_page()
    {
        global $wpdb;
        $pageId = filter_input(INPUT_GET, 'page');
        $currentPage = filter_input(INPUT_GET, 'pg');

        switch($pageId)
        {
            case CMPVT_SLUG_NAME:
                {
                    /*
                     * Choose the channel based on the license
                     */
                    $perPage = 9;
                    $currentPage = empty($currentPage) ? 1 : $currentPage;

                    $channel = self::getCurrentChannel();

                    /*
                     * This is how you make a call to the Vimeo API
                     */
                    // Authenticate Vimeo
                    $vimeo = new Vimeo('c844e902607c19505ceb6ac1477549091d838ac7', '198e50f38982b18621da7aa83df3e188c50c31c2', 'aeadb9c2446ce7e06bab71513262ca8d');

                    // Try to access the API
                    try
                    {
                        $args = array(
                            'per_page' => $perPage, // 50 is the max per page, use "page" parameter for more pages
                            'page'     => $currentPage, // 50 is the max per page, use "page" parameter for more pages
                        );
                        $results = $vimeo->request('/channels/' . $channel . '/videos/', $args, 'GET');
                    }
                    catch(VimeoAPIException $e)
                    {
                        $error = "Encountered an API error -- code {$e->getCode()} - {$e->getMessage()}";
                    }

                    ob_start();
                    include_once self::$viewsPath . 'videos.phtml';
                    $content = ob_get_contents();
                    ob_end_clean();
                    break;
                }
            case CMPVT_SLUG_NAME . '-about':
                {
                    ob_start();
                    include_once self::$viewsPath . 'about.phtml';
                    $content = ob_get_contents();
                    ob_end_clean();
                    break;
                }
        }

        self::displayAdminPage($content);
    }

    public static function displayAdminPage($content)
    {
        $nav = self::getAdminNav();
        include_once self::$viewsPath . 'template.phtml';
    }

    public static function getAdminNav()
    {
        global $self, $parent_file, $submenu_file, $plugin_page, $typenow, $submenu;
        ob_start();
        $submenus = array();

        $menuItem = CMPVT_SLUG_NAME;

        if( isset($submenu[$menuItem]) )
        {
            $thisMenu = $submenu[$menuItem];

            foreach($thisMenu as $sub_item)
            {
                $slug = $sub_item[2];

                // Handle current for post_type=post|page|foo pages, which won't match $self.
                $self_type = !empty($typenow) ? $self . '?post_type=' . $typenow : 'nothing';

                $isCurrent = FALSE;
                $subpageUrl = get_admin_url('', 'admin.php?page=' . $slug);

                if(
                        (!isset($plugin_page) && $self == $slug ) ||
                        ( isset($plugin_page) && $plugin_page == $slug && ( $menuItem == $self_type || $menuItem == $self || file_exists($menuItem) === false ) )
                )
                {
                    $isCurrent = TRUE;
                }

                $url = (strpos($slug, '.php') !== false || strpos($slug, 'http://') !== false) ? $slug : $subpageUrl;
                $submenus[] = array(
                    'link'    => $url,
                    'title'   => $sub_item[0],
                    'current' => $isCurrent
                );
            }
            include self::$viewsPath . 'nav.phtml';
        }
        $nav = ob_get_contents();
        ob_end_clean();
        return $nav;
    }

    /* OK, its safe for us to save the data now. */

    public static function getTheOptionNames($k)
    {
        return strpos($k, 'cmpvt_') === 0;
    }

    protected static function _isPost()
    {
        return strtolower($_SERVER['REQUEST_METHOD']) == 'post';
    }

    public static function outputPagination($atts)
    {
        $output = '';

        $pagination_args = array(
            'base'       => esc_url(add_query_arg('pg', '%#%')),
            'format'     => '',
            'total'      => $atts['max_pg'],
            'current'    => max(1, $atts['pg']),
            'link_class' => 'button',
        );

        $pagination = tle_paginate_links($pagination_args);

        if( $pagination )
        {
            $output .= '<div class="cmpvt-pagination">' . CMPluginsVideoTutorials::__('Page: ') . $pagination . '</div>';
        }

        return $output;
    }

    public static function outputCategories($atts = array())
    {
        $output = '';

        $channels = array(
            '822494' => 'All CM Tutorials',
            '822317' => 'CM Answers',
            '822489' => 'CM Download Manager',
            '822492' => 'CM Ad Changer',
            '822493' => 'CM Tooltip Glossary',
            '822491' => 'CM MicroPayments',
            //     '824469' => 'CM Product Catalog',
            '824470' => 'CM OnBoarding',
            '824471' => 'CM EDD Related Add-Ons',
        );

        $currentChannel = self::getCurrentChannel();

        $currentPage = filter_input(INPUT_GET, 'page');

        $output .= '<div class="cmpvt-categories"><form action="" method="GET">';
        $output .= '<input type="hidden" name="page" value="' . $currentPage . '" />';
        $output .= CMPluginsVideoTutorials::__('Channel: ') . '<select name="channel">';
        foreach($channels as $channelSlug => $channelName)
        {
            $output .= '<option value="' . $channelSlug . '" ' . selected($channelSlug, $currentChannel, false) . '>' . $channelName . '</option>';
        }
        $output .= '</select></form></div>';

        return $output;
    }

    public static function outputButton($atts = array())
    {
        $output = '';

        $currentChannel = self::getCurrentChannel();

        switch($currentChannel)
        {
            case '822494':
                $href = 'https://plugins.cminds.com';
                $freeHref = '';
                break;
            case '822317':
                $href = 'https://answers.cminds.com/pricing';
                $freeHref = 'https://downloads.wordpress.org/plugin/cm-answers.zip';
                break;
            case '822489':
                $href = 'https://downloadmanager.cminds.com/pricing';
                $freeHref = 'https://downloads.wordpress.org/plugin/cm-download-manager.zip';
                break;
            case '822492':
                $href = 'https://adchanger.cminds.com';
                $freeHref = 'https://downloads.wordpress.org/plugin/cm-ad-changer.zip';
                break;
            case '822493':
                $href = 'https://tooltip.cminds.com';
                $freeHref = 'https://downloads.wordpress.org/plugin/enhanced-tooltipglossary.zip';
                break;
            case '822491':
                $href = 'https://plugins.cminds.com/cm-micropayment-platform/';
                $freeHref = '';
                break;
            case '824469':
                $href = 'https://plugins.cminds.com/cm-product-catalog/';
                $freeHref = '';
                break;
            case '824470':
                $href = 'https://plugins.cminds.com';
                $freeHref = '';
                break;
            case '824471':
                $href = 'https://plugins.cminds.com';
                $freeHref = '';
                break;

            default:
                $href = 'https://plugins.cminds.com';
                break;
        }

        $output .= '<div class="cmpvt-purchase-button">';
        $output .= '<a href="' . $href . '" target="_blank" class="cmpvt-button button">' . CMPluginsVideoTutorials::__('Purchase Pro Plugin') . '</a>';
        if( !empty($freeHref) )
        {
            $output .= '&nbsp; <a href="' . $freeHref . '" target="_blank" class="cmpvt-button button">' . CMPluginsVideoTutorials::__('Download Free Version') . '</a>';
        }
        $output .= '</div>';

        return $output;
    }

    public static function getCurrentChannel()
    {
        $defult = '822494';
        $currentChannel = filter_input(INPUT_GET, 'channel');
        if( empty($currentChannel) )
        {
            $currentChannel = $defult;
        }

        return $currentChannel;
    }

}