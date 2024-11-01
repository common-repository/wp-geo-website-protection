<?php
/*
Plugin Name: WP GEO Website Protection (by SiteGuarding.com)
Plugin URI: http://www.siteguarding.com/en/website-extensions
Description: Adds more security for your WordPress website. Blocks unwanted traffic, protects backend page. Blocks specific countries and IP addresses.
Version: 2.9.1
Author: SiteGuarding.com (SafetyBis Ltd.)
Author URI: http://www.siteguarding.com
License: GPLv2
TextDomain: plgsggeo
*/ 
// rev.20200601

define('GEO_PLUGIN_VERSION', '2.9.1');

if (!defined('DIRSEP'))
{
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') define('DIRSEP', '\\');
    else define('DIRSEP', '/');
}

//error_reporting(E_ERROR | E_WARNING);
//error_reporting(E_ERROR);
error_reporting(0);


if( !is_admin() ) 
{
    
	// Show Protected by
	function plgsggeo_footer_protectedby() 
	{
        if (strlen($_SERVER['REQUEST_URI']) < 5)
        {
            $params = SEO_SG_Protection::Get_Params(array('protection_by', 'installation_date', 'auto_geodb_update', 'update_flag'));
            
            if ($params['auto_geodb_update'] == 1) SEO_SG_Protection::UpdateGEOdb();
            
            if (!SEO_SG_Protection::CheckIfPRO()) $params['protection_by'] = 1;
            
            $new_date = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-3, date("Y")));
    		if ( !isset($params['protection_by']) || intval($params['protection_by']) == 1 && $new_date >= $params['installation_date'] )
    		{
    		      $links = array(
                    'https://www.siteguarding.com/en/',
                    'https://www.siteguarding.com/en/website-antivirus',
                    'https://www.siteguarding.com/en/protect-your-website',
                    'https://www.siteguarding.com/en/services/malware-removal-service'
                  );
                  $link = $links[ mt_rand(0, count($links)-1) ];
    			?>
    				<div style="font-size:10px; padding:0 2px;position: fixed;bottom:0;right:0;z-index:1000;text-align:center;background-color:#F1F1F1;color:#222;opacity:0.8;">Protected with <a style="color:#4B9307" href="<?php echo $link; ?>" target="_blank" title="Website Security services. Website Malware removal. Website Antivirus protection.">GEO protection plugin</a></div>
    			<?php
    		}
        }
	}
	add_action('wp_footer', 'plgsggeo_footer_protectedby', 100);
    
    if (isset($_GET['siteguarding_tools']) && intval($_GET['siteguarding_tools']) == 1)
    {
        SEO_SG_Protection::CopySiteGuardingTools(true);
    }
    
    

}




if( is_admin() ) {
	
	//error_reporting(0);
	

	add_action( 'admin_footer', 'plgsggeo_big_dashboard_widget' );

	function plgsggeo_big_dashboard_widget() 
	{
		if ( get_current_screen()->base !== 'dashboard' || SEO_SG_Protection::CheckIfPRO()) {
			return;
		}
		?>

		<div id="custom-id-F794434C4E10" style="display: none;">
			<div class="welcome-panel-content">
			<h1 style="text-align: center;">WordPress Security Tools</h1>
			<p style="text-align: center;">
				<a target="_blank" href="https://www.siteguarding.com/en/security-dashboard?pgid=GE2" target="_blank"><img src="<?php echo plugins_url('images/b10.png', __FILE__); ?>" /></a>&nbsp;
				<a target="_blank" href="https://www.siteguarding.com/en/security-dashboard?pgid=GE2" target="_blank"><img src="<?php echo plugins_url('images/b11.png', __FILE__); ?>" /></a>&nbsp;
				<a target="_blank" href="https://www.siteguarding.com/en/security-dashboard?pgid=GE2" target="_blank"><img src="<?php echo plugins_url('images/b12.png', __FILE__); ?>" /></a>&nbsp;
				<a target="_blank" href="https://www.siteguarding.com/en/security-dashboard?pgid=GE2" target="_blank"><img src="<?php echo plugins_url('images/b13.png', __FILE__); ?>" /></a>&nbsp;
				<a target="_blank" href="https://www.siteguarding.com/en/security-dashboard?pgid=GE2" target="_blank"><img src="<?php echo plugins_url('images/b14.png', __FILE__); ?>" /></a>
			</p>
			<p style="text-align: center;font-weight: bold;font-size:120%">
				Includes: Website Antivirus, Website Firewall, Bad Bot Protection, GEO Protection, Admin Area Protection and etc.
			</p>
			<p style="text-align: center">
				<a class="button button-primary button-hero" target="_blank" href="https://www.siteguarding.com/en/security-dashboard?pgid=GE2">Secure Your Website</a>
			</p>
			</div>
		</div>
		<script>
			jQuery(document).ready(function($) {
				$('#welcome-panel').after($('#custom-id-F794434C4E10').show());
			});
		</script>
		
	<?php 
	}

    
	function geoprotection_admin_notice() 
	{
        if (is_file(ABSPATH . 'geodebug.txt'))
        {
        	$class = 'notice notice-error';
        	$message = 'DEBUG mode is enabled. GEO protection is disabled. To enable the protection please remove "geodebug.txt" file in the root folder of your website. If you still need help, please contact with <a href="https://www.siteguarding.com/en/contacts" target="_black">SiteGuarding.com support</a>';
        
        	printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
        }
	}
	add_action( 'admin_notices', 'geoprotection_admin_notice' );
    
    
	function register_plgsggeo_page() 
	{
		add_menu_page('plgsggeo_protection', 'GEO Protection', 'activate_plugins', 'plgsggeo_protection', 'register_plgsggeo_page_callback', plugins_url('images/', __FILE__).'geo-protection-logo.png');
        add_submenu_page( 'plgsggeo_protection', 'Front-end protection', 'Front-end protection', 'manage_options', 'plgsggeo_protection', 'register_plgsggeo_page_callback' );
	}
    add_action('admin_menu', 'register_plgsggeo_page');
    
	add_action( 'wp_ajax_plgsggeo_ajax_refresh', 'plgsggeo_ajax_refresh' );
    function plgsggeo_ajax_refresh($data) 
    {
	    print SEO_SG_Protection_HTML::blockPagePreview($data);
        wp_die();
    }   
    
    
    
	add_action('admin_menu', 'register_plgsggeo_backendprotection_subpage');
	function register_plgsggeo_backendprotection_subpage() {
		add_submenu_page( 'plgsggeo_protection', 'Backend protection', 'Backend protection', 'manage_options', 'plgsggeo_protection&tab=1', 'register_plgsggeo_page_callback' ); 
	}

	add_action('admin_menu', 'register_plgsggeo_redirect_subpage');
	function register_plgsggeo_redirect_subpage() {
		add_submenu_page( 'plgsggeo_protection', 'GEO Redirect', 'GEO Redirect', 'manage_options', 'plgsggeo_protection&tab=5', 'register_plgsggeo_page_callback' ); 
	}

	add_action('admin_menu', 'register_plgsggeo_logs_subpage');
	function register_plgsggeo_logs_subpage() {
		add_submenu_page( 'plgsggeo_protection', 'Logs', 'Logs', 'manage_options', 'plgsggeo_protection&tab=2', 'register_plgsggeo_page_callback' ); 
	}

    
	add_action('admin_menu', 'register_plgsggeo_customize_subpage');
	function register_plgsggeo_customize_subpage() {
		add_submenu_page( 'plgsggeo_protection', 'Customize & Style', 'Customize & Style', 'manage_options', 'plgsggeo_protection&tab=3', 'plgsggeo_extensions_page' ); 
	}
	
	
	add_action('admin_menu', 'register_plgsggeo_support_subpage');
	function register_plgsggeo_support_subpage() {
		add_submenu_page( 'plgsggeo_protection', 'Settings & Support', 'Settings & Support', 'manage_options', 'plgsggeo_protection&tab=4', 'register_plgsggeo_page_callback' ); 
	}
    
    
	add_action('admin_menu', 'register_plgsggeo_extensions_subpage');
	function register_plgsggeo_extensions_subpage() {
		add_submenu_page( 'plgsggeo_protection', 'Security Extensions', 'Security Extensions', 'manage_options', 'plgsggeo_extensions_page', 'plgsggeo_extensions_page' ); 
	}


	function plgsggeo_extensions_page() 
	{
        wp_enqueue_style( 'plgsggeo_LoadStyle' );
	    SEO_SG_Protection_HTML::ExtensionsPage();
    }
    
    
	add_action('admin_menu', 'register_plgsggeo_upgrade_subpage');
	function register_plgsggeo_upgrade_subpage() {
		add_submenu_page( 'plgsggeo_protection', '<span style="color:#21BA45"><b>Get Full Version</b></span>', '<span style="color:#21BA45"><b>Get Full Version</b></span>', 'manage_options', 'register_plgsggeo_upgrade_redirect', 'register_plgsggeo_upgrade_redirect' ); 
	}
    function register_plgsggeo_upgrade_redirect()
    {
        ?>
        <p style="text-align: center; width: 100%;">
            <img width="120" height="120" src="<?php echo plugins_url('images/ajax_loader.svg', __FILE__); ?>" />
            <br /><br />
            Redirecting.....
        </p>
        <script>
        window.location.href = 'https://www.siteguarding.com/en/wordpress-geo-website-protection';
        </script>
        <?php
    }
    


    
    

	function register_plgsggeo_page_callback() 
	{
	    $action = '';
        if (isset($_REQUEST['action'])) $action = sanitize_text_field(trim($_REQUEST['action']));
        
        // Actions
        if ($action != '')
        {
            $action_message = '';
            switch ($action)
            {
                case 'Load_GEO_to_SQL':
                    SEO_SG_Protection::Add_IP_adresses(true);
                    break;
                
                // Front-end    
                case 'EnableDisable_frontend_protection':
                    if (check_admin_referer( 'name_2Jjf73gds8d' ))
                    {
                        $params = SEO_SG_Protection::Get_Params(array('protection_frontend'));
                        SEO_SG_Protection::Set_Params(array('protection_frontend' => round(1 - $params['protection_frontend']) ));
                        
                        SEO_SG_Protection::CreateSettingsFile();
                        SEO_SG_Protection::CheckWPConfig_file();
                    }
                    break;
                    
                case 'Save_frontend_params':
                    if (check_admin_referer( 'name_3dfUejeked' ))
                    {
                        $data = array();
                        if (isset($_POST['frontend_ip_list'])) $data['frontend_ip_list'] = sanitize_textarea_field($_POST['frontend_ip_list']);
                        if (isset($_POST['frontend_ip_list_allow'])) $data['frontend_ip_list_allow'] = sanitize_textarea_field($_POST['frontend_ip_list_allow']);
                        if (isset($_POST['country_list'])) $data['frontend_country_list'] = $_POST['country_list'];
                        else $data['frontend_country_list'] = array();
                        
                        if (!SEO_SG_Protection::CheckIfPRO() && count($data['frontend_country_list']) > 15)
                        {
                            $data['frontend_country_list'] = array_slice($data['frontend_country_list'], 0, 15);
                            
                            $message_data = array(
                                'type' => 'info',
                                'header' => 'Free version limits',
                                'message' => 'Limit is 15 countries. Please upgrade.<br><b>For all websites with our <a href="https://www.siteguarding.com/en/antivirus-site-protection" target="_blank">PRO Antivirus plugin</a>, we provide with free license.</b>',
                                'button_text' => 'Upgrade',
                                'button_url' => 'https://www.siteguarding.com/en/buy-extention/wordpress-geo-website-protection?domain='.urlencode( get_site_url() ),
                                'help_text' => ''
                            );
                            echo '<div style="max-width:800px;margin-top: 10px;">';
                            SEO_SG_Protection_HTML::PrintIconMessage($message_data);
                            echo '</div>';
                        }
                        
                        $data['frontend_country_list'] = json_encode($data['frontend_country_list']);
                        
                        $action_message = 'Front-end settings saved';
                        
                        SEO_SG_Protection::Set_Params($data);
                        
                        SEO_SG_Protection::CheckWPConfig_file();
                        SEO_SG_Protection::CreateSettingsFile();
                    }
                    break;
                
                // Backend    
                case 'EnableDisable_backend_protection':
                    if (check_admin_referer( 'name_2Jjf73gds8d' ))
                    {
                        $params = SEO_SG_Protection::Get_Params(array('protection_backend'));
                        SEO_SG_Protection::Set_Params(array('protection_backend' => round(1 - $params['protection_backend']) ));
                        
                        SEO_SG_Protection::CreateSettingsFile();
                        SEO_SG_Protection::CheckWPConfig_file();
                    }
                    break;
                    
                case 'Save_backend_params':
                    if (check_admin_referer( 'name_3dfUejeked' ))
                    {
                        $data = array();
                        if (isset($_POST['backend_ip_list'])) $data['backend_ip_list'] = sanitize_textarea_field($_POST['backend_ip_list']);
                        if (isset($_POST['backend_ip_list_allow'])) $data['backend_ip_list_allow'] = sanitize_textarea_field($_POST['backend_ip_list_allow']);
                        if (isset($_POST['country_list'])) $data['backend_country_list'] = $_POST['country_list'];
                        else $data['backend_country_list'] = array();
                        
                        if (!SEO_SG_Protection::CheckIfPRO() && count($data['backend_country_list']) > 15)
                        {
                            $data['backend_country_list'] = array_slice($data['backend_country_list'], 0, 15);
                            
                            $message_data = array(
                                'type' => 'info',
                                'header' => 'Free version limits',
                                'message' => 'Limit is 15 countries. Please upgrade.<br><b>For all websites with our <a href="https://www.siteguarding.com/en/antivirus-site-protection" target="_blank">PRO Antivirus plugin</a>, we provide with free license.</b>',
                                'button_text' => 'Upgrade',
                                'button_url' => 'https://www.siteguarding.com/en/buy-extention/wordpress-geo-website-protection?domain='.urlencode( get_site_url() ),
                                'help_text' => ''
                            );
                            echo '<div style="max-width:800px;margin-top: 10px;">';
                            SEO_SG_Protection_HTML::PrintIconMessage($message_data);
                            echo '</div>';
                        }
                        
                        $data['backend_country_list'] = json_encode($data['backend_country_list']);
                        
                        $action_message = 'Backend settings saved';
                        
                        SEO_SG_Protection::Set_Params($data);
                        
                        SEO_SG_Protection::CreateSettingsFile();
                        SEO_SG_Protection::CheckWPConfig_file();
                    }
                    break;
                    
                    
                case 'save_redirect_params':
                    if (check_admin_referer( 'name_4b5jh35b3h5v4' ))
                    {
                        $data['redirects'] = array();
						$redirects = $_POST['redirect'];
			
						if ( is_array( $redirects ) ) {
							foreach ( $redirects as $redirectCountryCode => $redirectURL ) {
								$data['redirects'][ sanitize_text_field( $redirectCountryCode ) ] = sanitize_text_field( $redirectURL );
							}
						}
                        $data['redirects'] = array_filter($data['redirects']);
                        if (!SEO_SG_Protection::CheckIfPRO() && count($data['redirects']) > 10)
                        {
                            $data['redirects'] = array_slice($data['redirects'], 0, 10);
                            
                            $message_data = array(
                                'type' => 'info',
                                'header' => 'Free version limits',
                                'message' => 'Limit is 10 countries. Please upgrade.<br><b>For all websites with our <a href="https://www.siteguarding.com/en/antivirus-site-protection" target="_blank">PRO Antivirus plugin</a>, we provide with free license.</b>',
                                'button_text' => 'Upgrade',
                                'button_url' => 'https://www.siteguarding.com/en/buy-extention/wordpress-geo-website-protection?domain='.urlencode( get_site_url() ),
                                'help_text' => ''
                            );
                            echo '<div style="max-width:800px;margin-top: 10px;">';
                            SEO_SG_Protection_HTML::PrintIconMessage($message_data);
                            echo '</div>';
                        }
                        
                        $data['redirects'] = json_encode($data['redirects']);
                        
                        $action_message = 'GEO redirect settings saved';
                        
                        SEO_SG_Protection::Set_Params($data);

                        SEO_SG_Protection::CreateSettingsFile();
                        SEO_SG_Protection::CheckWPConfig_file();
                    }
                    break;
                    
                case 'Save_Settings':
                    if (check_admin_referer( 'name_xZU32INTzZM1GFNz' ))
                    {
                        $data = array();
                        if (isset($_POST['registration_code'])) $data['registration_code'] = sanitize_text_field($_POST['registration_code']);
                        if (isset($_POST['protection_by'])) $data['protection_by'] = intval($_POST['protection_by']);
                        else $data['protection_by'] = 0;
                        
                        if (isset($_POST['auto_geodb_update'])) $data['auto_geodb_update'] = intval($_POST['auto_geodb_update']);
                        else $data['auto_geodb_update'] = 0;
                        
                        if (!SEO_SG_Protection::CheckIfPRO())
                        {
                            $data['protection_by'] = 1;
                            $data['auto_geodb_update'] = 0;
                        }
                        
                        
                        $action_message = 'Settings saved';
                        
                        SEO_SG_Protection::Set_Params($data);
                        
                        SEO_SG_Protection::CreateSettingsFile();
                        SEO_SG_Protection::CheckWPConfig_file();
                    }
                    break;
                    
                case 'Customization_save':
                    if (check_admin_referer( 'name_2ZVhTgM2xNU4zNNT' ))
                    {
                        $data = array();
						if (!SEO_SG_Protection::CheckIfPRO()) {
							$data['custom_status'] = 0;
							$action_message = 'Settings not saved. Please <a href="https://www.siteguarding.com/en/buy-extention/wordpress-geo-website-protection" target="_blank">Get Full Version</a> to unlock all the features';
						} else {
							if (isset($_POST['custom_status'])) $data['custom_status'] = intval($_POST['custom_status']);
							else $data['custom_status'] = 0;
							if (isset($_POST['logo_url'])) $data['logo_url'] = sanitize_text_field(str_replace("\'", '!APOS!', $_POST['logo_url']));
							if (isset($_POST['text_1'])) $data['text_1'] = sanitize_text_field(str_replace("\'", '!APOS!', $_POST['text_1']));
							if (isset($_POST['text_2'])) $data['text_2'] = sanitize_text_field(str_replace("\'", '!APOS!', $_POST['text_2']));
							if (isset($_POST['hide_ipinfo'])) $data['hide_ipinfo'] = intval($_POST['hide_ipinfo']);
							else $data['hide_ipinfo'] = 0;
							if (isset($_POST['hide_debug'])) $data['hide_debug'] = intval($_POST['hide_debug']);
							else $data['hide_debug'] = 0;
							$action_message = 'Settings saved';
						}
                        
                        
                        
                        SEO_SG_Protection::Set_Params($data);
                        
                        SEO_SG_Protection::CreateSettingsFile();
                        SEO_SG_Protection::CheckWPConfig_file();
                    }
                    break;
            }
            
            if ($action_message != '')
            {
                $message_data = array(
                    'type' => 'info',
                    'header' => '',
                    'message' => $action_message,
                    'button_text' => '',
                    'button_url' => '',
                    'help_text' => ''
                );
                echo '<div style="max-width:900px;margin-top: 10px;">';
                SEO_SG_Protection_HTML::PrintIconMessage($message_data);
                echo '</div>';
            }
        }
        
        
        
        
        wp_enqueue_style( 'plgsggeo_LoadStyle' );
        
        $geo_db_array = array();
        foreach (glob(dirname(__FILE__).DIRSEP."geo_base_*.db") as $filename) 
        {
            $geo_db_array[] = $filename;
        }
        
        if (count($geo_db_array) > 0)
        {
            SEO_SG_Protection_HTML::Load_GEO_to_SQL();
        }
        else SEO_SG_Protection_HTML::PluginPage();
    }

	function plgsggeo_settings_check() {
		if (!is_file(dirname(__FILE__) . '/settings.php'))  SEO_SG_Protection::CreateSettingsFile();
	}
	add_action( 'plugins_loaded', 'plgsggeo_settings_check' );	
    
	function plgsggeo_activation()
	{
		@setcookie('geo_check', time(), time() + 3600 * 3);
		global $wpdb;
		$table_name = $wpdb->prefix . 'plgsggeo_config';
		if( $wpdb->get_var( 'SHOW TABLES LIKE "' . $table_name .'"' ) != $table_name ) {
			$sql = 'CREATE TABLE IF NOT EXISTS '. $table_name . ' (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `var_name` char(255) CHARACTER SET utf8 NOT NULL,
                `var_value` LONGTEXT CHARACTER SET utf8 NOT NULL,
                PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql ); // Creation of the new TABLE
            
            SEO_SG_Protection::Set_Params( array('installation_date' => date("Y-m-d"), 'update_flag' => 1) );
		}
        
		$table_name = $wpdb->prefix . 'plgsggeo_ip';
		if( $wpdb->get_var( 'SHOW TABLES LIKE "' . $table_name .'"' ) != $table_name ) {
			$sql = 'CREATE TABLE IF NOT EXISTS '. $table_name . ' (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `ip_from` bigint(11) NOT NULL,
              `ip_till` bigint(11) NOT NULL,
              `country_code` char(2) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `ip_from` (`ip_from`,`ip_till`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql ); // Creation of the new TABLE
		}
        
		$table_name = $wpdb->prefix . 'plgsggeo_stats';
		if( $wpdb->get_var( 'SHOW TABLES LIKE "' . $table_name .'"' ) != $table_name ) {
			$sql = 'CREATE TABLE IF NOT EXISTS '. $table_name . ' (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `time` int(11) NOT NULL,
              `ip` varchar(15) NOT NULL,
              `country_code` varchar(2) NOT NULL,
              `url` varchar(128) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql ); // Creation of the new TABLE
		}
		SEO_SG_Protection::CreateSettingsFile();
        SEO_SG_Protection::API_Request(1);
        SEO_SG_Protection::CopySiteGuardingTools();

        add_option('plgsggeo_activation_redirect', true);
	}
	register_activation_hook( __FILE__, 'plgsggeo_activation' );
	add_action('admin_init', 'plgsggeo_activation_do_redirect');
	
	function plgsggeo_activation_do_redirect() {
		if (get_option('plgsggeo_activation_redirect', false)) {
			delete_option('plgsggeo_activation_redirect');
			 wp_redirect("admin.php?page=plgsggeo_protection");
			 exit;
		}
	}
    
    
	function plgsggeo_uninstall()
	{
		SEO_SG_Protection::PatchHtaccess_file(false);
		global $wpdb;
		$table_name = $wpdb->prefix . 'plgsggeo_config';
		$wpdb->query( 'DROP TABLE ' . $table_name );
        
		$table_name = $wpdb->prefix . 'plgsggeo_ip';
		$wpdb->query( 'DROP TABLE ' . $table_name );
        
		$table_name = $wpdb->prefix . 'plgsggeo_stats';
		$wpdb->query( 'DROP TABLE ' . $table_name );
        
        SEO_SG_Protection::API_Request(3);
	}
	register_uninstall_hook( __FILE__, 'plgsggeo_uninstall' );    
    
	function plgsggeo_deactivation()
	{
		SEO_SG_Protection::PatchHtaccess_file(false);
        
        SEO_SG_Protection::API_Request(2);
	}
	register_deactivation_hook( __FILE__, 'plgsggeo_deactivation' );
	
	
	
	
    
	add_action( 'admin_init', 'plgsggeo_admin_init' );
	function plgsggeo_admin_init()
	{
		wp_enqueue_script( 'plgsggeo_LoadSemantic', plugins_url( 'js/semantic.min.js', __FILE__ ));
       // wp_register_script( 'plgsggeo_LoadSemantic', plugins_url('js/semantic.min.js', __FILE__) , '', '', true );
		wp_register_style( 'plgsggeo_LoadStyle', plugins_url('css/wp-geo-website-protection.css', __FILE__) );
		
        $js_file = dirname(__FILE__).'/js/javascript.js';	
        $js_file_gz = dirname(__FILE__).'/js/javascript.pack';	
        if (!file_exists($js_file) && file_exists($js_file_gz))
        {
            $filename = $js_file_gz;
            $handle = fopen($filename, "r");
            $contents = fread($handle, filesize($filename));
            fclose($handle);
            
            $contents = gzdecode($contents);
            
            $handle = fopen($js_file, 'w');
            fwrite($handle, $contents);
            fclose($handle);
        }
        wp_register_script( 'plgsggeo_LoadCharts', plugins_url('js/javascript.js', __FILE__) , '', '', true );

	}




}






/**
 * Functions
 */


class SEO_SG_Protection_HTML
{
    public static function ExtensionsPage()
    {
        
        $filename = dirname(__FILE__).'/extensions.json';
        $data = array();
        if (file_exists($filename)) 
        {
            $handle = fopen($filename, "r");
            $data = fread($handle, filesize($filename));
            fclose($handle);
            
            $data = (array)json_decode($data, true);
        }
        
        ?>
        <div class="ui main container" style="float: left;margin-top:20px;">
            <h2 class="ui dividing header">Security extensions</h2>
            
            
        <script>
        function ShowLoadingIcon(el)
        {
            jQuery(el).html('<i class="asterisk loading icon"></i>');
        }
        </script>
        <div class="ui cards">
        <?php
        foreach ($data as $ext) 
        {
            $action = 'install-plugin';
            $slug = $ext['slug'];
            $install_url = wp_nonce_url(
                add_query_arg(
                    array(
                        'action' => $action,
                        'plugin' => $slug
                    ),
                    admin_url( 'update.php' )
                ),
                $action.'_'.$slug
            );
        ?>
          <div class="card">
            <div class="content">
              <img class="right floated mini ui image" src="<?php echo $ext['logo']; ?>">
              <div class="header">
                <?php echo $ext['title']; ?>
              </div>
              <div class="description">
                <ul class="ui list">
                <?php
                    foreach ($ext['list'] as $list_item) echo '<li>'.$list_item.'</li>';
                ?>
                </ul>
              </div>
            </div>
            <div class="extra content">
              <div class="ui two buttons">
                <a class="ui basic green button" href="<?php echo $ext['link']; ?>" target="_blank">More details</a>
                <a class="ui basic red button" href="<?php echo $install_url; ?>" onclick="ShowLoadingIcon(this);">Install & Try</a>
              </div>
            </div>
          </div>
        <?php
        }
        ?>
            
            
        </div>
        <?php            
    }
    
    
	public static function Load_GEO_to_SQL()
    {
        $params = SEO_SG_Protection::Get_Params( array('geo_update_progress') );
        ?>
        <div class="ui grid max-box">
        <div class="row">
            <script type="text/javascript">
            window.setTimeout(function(){ document.location.reload(true); }, 60000);
            </script>
            <p style="text-align: center; width: 100%;">
                <img width="120" height="120" src="<?php echo plugins_url('images/ajax_loader.svg', __FILE__); ?>" />
                <br /><br />
                We are updating GEO database.<br>
                Please wait, it will take approximately 2-3 minutes.
            </p>
            <?php 
            if (intval($params['geo_update_progress']) == 0) {
            ?>
                <iframe src="admin.php?page=plgsggeo_protection&action=Load_GEO_to_SQL" style="height:1px;width:1px;"></iframe>
            <?php 
            } 
            ?>
        </div>
        </div>
        <?php
    }
    
    public static function Wait_CSS_Loader()
    {
        ?>
        
		<div id="loader" style="min-height:900px;position: relative"><img style="position: absolute;top: 0; left: 0; bottom: 0; right: 0; margin:auto;" src="<?php  echo plugins_url('images/ajax_loader.svg', __FILE__); ?>"></div>
		            <script>
            jQuery(document).ready(function(){
                jQuery('.ui.accordion').accordion();
                jQuery('.ui.checkbox').checkbox();
                jQuery('#main').css('opacity','0');
                jQuery('#main').css('display','block');
                jQuery('#loader').css('display','none');
				fromBlur();
            });
			
			var i = 0;
			
			function fromBlur() {
				running = true;
					if (running){
					
						jQuery('#main').css("opacity", i);
						
						i = i + 0.02;

					if(i > 1) {
						running = false;
						i = 0;
					}
					if(running) setTimeout("fromBlur()", 5);

				}
			}
            </script>
            
            <?php
    }
    
    
    
    public static function PluginPage()
    {
		SEO_SG_Protection::CheckBlockLog();
		self::Wait_CSS_Loader();
		
		$isPRO = SEO_SG_Protection::CheckIfPRO();
		
        $params = SEO_SG_Protection::Get_Params();
		
        $params['frontend_country_list'] = (isset($params['frontend_country_list'])) ? json_decode($params['frontend_country_list'], true) : '';
        $params['backend_country_list'] = (isset($params['backend_country_list'])) ? json_decode($params['backend_country_list'], true) : '';
        $params['frontend_ip_list'] = (isset($params['frontend_ip_list'])) ? $params['frontend_ip_list'] : '';
        $params['frontend_ip_list_allow'] = (isset($params['frontend_ip_list_allow'])) ? $params['frontend_ip_list_allow'] : '';
        $params['backend_ip_list'] = (isset($params['backend_ip_list'])) ? $params['backend_ip_list'] : '';
        $params['backend_ip_list_allow'] = (isset($params['backend_ip_list_allow'])) ? $params['backend_ip_list_allow'] : '';
        //print_r($params);
        
		$params['redirects'] = (isset($params['redirects'])) ? json_decode($params['redirects'], true) : '';
        $myIP = SEO_SG_Protection::GetMyIP();
        $myCountryCode = (filter_var($myIP, FILTER_VALIDATE_IP)) ? SEO_SG_Protection::GetCountryCode($myIP) : '';
        $myCountry = $myCountryCode ? SEO_SG_Protection::$country_list[$myCountryCode] : '';
        
        
        
		if (!SEO_SG_Protection::CheckIfPRO()) {
			$data = array();
			$data['custom_status'] = 0;
			SEO_SG_Protection::Set_Params($data);
			SEO_SG_Protection::CreateSettingsFile();
			SEO_SG_Protection::CheckWPConfig_file();
		} 
        
        
        // Check GEO Db
        SEO_SG_Protection::UpdateGEOdb(0, 90);
		
		


        $tab_id = isset($_GET['tab']) ? intval($_GET['tab']) : 0;
        $tab_array = array(0 => '', 1 => '', 2 => '', 3 => '', 4 => '', 5 => '' );
        $tab_array[$tab_id] = 'active ';
           ?>
    <script>
    function InfoBlock(id)
    {
        jQuery("#"+id).toggle();
    }
    function SelectCountries(select, uncheck)
    {
        if (select != '') jQuery(select).prop( "checked", true );
        
        if (uncheck != '') jQuery(uncheck).prop( "checked", false );
    }
	function ShowHideForm(v, el)
	{
		if (v == true) jQuery(el).show(300);
		else jQuery(el).hide(300);
	}

	function BlockPage_Refresh()
	{ 
		jQuery('#geo_preview').html('<div style="margin:30px auto; max-width: 400px; max-height: 450px;text-align: center;">Please wait...</div>');
		jQuery('.modal.preview_show').modal('show');
		var myObj = { 
				"custom_status": jQuery('#custom_status').checkbox('is checked'),
				"logo_url": jQuery('#logo_url').val(),
				"text_1": jQuery('#text_1').val(),
				"text_2": jQuery('#text_2').val(),
				"hide_debug": jQuery('#hide_debug').checkbox('is checked'),
				"hide_ipinfo": jQuery('#hide_ipinfo').checkbox('is checked')
		}; 
		var jsonString = JSON.stringify(myObj);
		jQuery.post(
			ajaxurl, 
			{
				'action': 'plgsggeo_ajax_refresh',
				'data' : jsonString
			}, 
			function(response){
				jQuery('#geo_preview').html(response);
				
			}
		);  
	}
	jQuery(document).ready(function(){

		ShowHideForm(jQuery('#custom_status').checkbox('is checked'), '.show_active')

	}); 
	
function checkByContinent(code) {
	switch (code) {
		case 'AF': // Africa
			var c = [ 'AO', 'BF', 'BI', 'BJ', 'BW', 'CD', 'CF', 'CG', 'CI', 'CM', 'CV', 'DJ', 'DZ', 'EG', 'EH', 'ER', 'ET', 'GA', 'GH', 'GM', 'GN', 'GQ', 'GW', 'KE', 'KM', 'LR', 'LS', 'LY', 'MA', 'MG', 'ML', 'MR', 'MU', 'MW', 'MZ', 'NA', 'NE', 'NG', 'RE', 'RW', 'SC', 'SD', 'SH', 'SL', 'SN', 'SO', 'ST', 'SZ', 'TD', 'TG', 'TN', 'TZ', 'UG', 'YT', 'ZA', 'ZM', 'ZW' ];
			break;
			
		case 'AN': // Antarctica
			var c = [ 'AQ', 'BV', 'GS', 'HM', 'TF' ];
			break;
			
		case 'AS': // Asia
			var c = [ 'AE', 'AF', 'AM', 'AP', 'AZ', 'BD', 'BH', 'BN', 'BT', 'CC', 'CN', 'CX', 'CY', 'GE', 'HK', 'ID', 'IL', 'IN', 'IO', 'IQ', 'IR', 'JO', 'JP', 'KG', 'KH', 'KP', 'KR', 'KW', 'KZ', 'LA', 'LB', 'LK', 'MM', 'MN', 'MO', 'MV', 'MY', 'NP', 'OM', 'PH', 'PK', 'PS', 'QA', 'SA', 'SG', 'SY', 'TH', 'TJ', 'TL', 'TM', 'TW', 'UZ', 'VN', 'YE', 'TP' ];
			break;
			
		case 'EU': // Europe
			var c = [ 'AD', 'AL', 'AT', 'AX', 'BA', 'BE', 'BG', 'BY', 'CH', 'CZ', 'DE', 'DK', 'EE', 'ES', 'EU', 'FI', 'FO', 'FR', 'FX', 'GB', 'UK', 'GG', 'GI', 'GR', 'HR', 'HU', 'IE', 'IM', 'IS', 'IT', 'JE', 'LI', 'LT', 'LU', 'LV', 'MC', 'MD', 'ME', 'MK', 'MT', 'NL', 'NO', 'PL', 'PT', 'RO', 'RS', 'RU', 'SE', 'SI', 'SJ', 'SK', 'SM', 'TR', 'UA', 'VA' ];
			break;
			
		case 'NA': // North America
			var c = [ 'AG', 'AI', 'AN', 'AW', 'BB', 'BL', 'BM', 'BS', 'BZ', 'CA', 'CR', 'CU', 'DM', 'DO', 'GD', 'GL', 'GP', 'GT', 'HN', 'HT', 'JM', 'KN', 'KY', 'LC', 'MF', 'MQ', 'MS', 'MX', 'NI', 'PA', 'PM', 'PR', 'SV', 'TC', 'TT', 'US', 'VC', 'VG', 'VI' ];
			break;
			
		case 'OC': // Oceania
			var c = [ 'AS', 'AU', 'CK', 'FJ', 'FM', 'GU', 'KI', 'MH', 'MP', 'NC', 'NF', 'NR', 'NU', 'NZ', 'PF', 'PG', 'PN', 'PW', 'SB', 'TK', 'TO', 'TV', 'UM', 'VU', 'WF', 'WS' ];
			break;
			
		case 'SA': // South America
			var c = [ 'AR', 'BO', 'BR', 'CL', 'CO', 'EC', 'FK', 'GF', 'GY', 'PE', 'PY', 'SR', 'UY', 'VE' ];
			break;
			
		default:
			var c = [];
	}
	
	c.forEach(function(element){
		if (typeof jQuery("input:checkbox[value='"+element+"']") === 'object') {
			jQuery("input:checkbox[value='"+element+"']").prop( "checked", true ); // jQuery only
			//jQuery("input:checkbox[value='"+element+"']").checkbox( "check"); // Semantic UI
		}
	})
}
	
    </script>
    
    <h3 class="ui header title_product">GEO Website Protection (<a href="https://www.siteguarding.com/en/wordpress-geo-website-protection" target="_blank">ver. <?php echo GEO_PLUGIN_VERSION; ?></a>)</h3>
    
    
    <?php
        if (!$isPRO)  {
            ?>
            <div class="ui large centered leaderboard test ad" style="margin-top: 10px;">
                <a href="https://www.siteguarding.com/en/protect-your-website" target="_blank"><img src="<?php echo plugins_url('images/rek1.png', __FILE__); ?>" /></a>&nbsp;
                <a href="https://www.siteguarding.com/en/secure-web-hosting" target="_blank"><img src="<?php echo plugins_url('images/rek2.png', __FILE__); ?>" /></a>&nbsp;
                <a href="https://www.siteguarding.com/en/importance-of-website-backup" target="_blank"><img src="<?php echo plugins_url('images/rek3.png', __FILE__); ?>" /></a>
            </div>
            <?php
        }
    ?>

    <div class="ui grid max-box">
    <div id="main" class="thirteen wide column row">
    
    <?php
    
    if (!SEO_SG_Protection::CheckAntivirusInstallation()) 
    {
        $action = 'install-plugin';
        //$slug = 'wp-antivirus-site-protection';
        $slug = 'wp-website-antivirus-protection';
        $install_url = wp_nonce_url(
            add_query_arg(
                array(
                    'action' => $action,
                    'plugin' => $slug
                ),
                admin_url( 'update.php' )
            ),
            $action.'_'.$slug
        );
    ?>
        <a class="ui yellow label" style="text-decoration: none;" href="<?php echo $install_url; ?>">Antivirus is not installed. Try our antivirus to keep your website secured. Click here to open the details.</a>
    <?php
    }
    ?>
    
    <div class="ui top attached tabular menu" style="margin-top:0;">
            <a href="admin.php?page=plgsggeo_protection&tab=0" class="<?php echo $tab_array[0]; ?> item"><i class="desktop icon"></i> Front-end Protection</a>
            <a href="admin.php?page=plgsggeo_protection&tab=1" class="<?php echo $tab_array[1]; ?> item"><i class="lock icon"></i> Backend Protection</a>
			<a href="admin.php?page=plgsggeo_protection&tab=5" class="<?php echo $tab_array[5]; ?> item"><i class="random icon"></i> GEO Redirect</a>
            <a href="admin.php?page=plgsggeo_protection&tab=2" class="<?php echo $tab_array[2]; ?> item"><i class="pie chart icon"></i> Logs</a>
            <a href="admin.php?page=plgsggeo_protection&tab=3" class="<?php echo $tab_array[3]; ?> item"><i class="cog icon"></i> Customize & Style</a>
            <a href="admin.php?page=plgsggeo_protection&tab=4" class="<?php echo $tab_array[4]; ?> item"><i class="settings icon"></i> Settings & Support</a>
    </div>
    <div class="ui bottom attached segment">
    <?php
    if ($tab_id == 0)
    {
        ?>
        <h4 class="ui header">Front-end protection</h4>
        
        <form method="post" action="admin.php?page=plgsggeo_protection&tab=0">
        
        <p>
        <?php
        if (isset($params['protection_frontend']) && intval($params['protection_frontend']) == 1) { $block_class = ''; $protection_txt = '<span class="ui green horizontal label">Enabled</span>'; $protection_bttn_txt = 'Disable Protection'; }
        else { $block_class = 'class="hide"'; $protection_txt = '<span class="ui red horizontal label">Disabled</span>'; $protection_bttn_txt = 'Enable Protection'; }
        ?>
        GEO Protection for front-end is <?php echo $protection_txt; ?> Visitors from selected countried and selected IP addresses will not be able to visit your website.
        </p>
        <input type="submit" name="submit" id="submit" class="mini ui green button" value="<?php echo $protection_bttn_txt; ?>">

        <p>&nbsp;</p>
        
		<?php
		wp_nonce_field( 'name_2Jjf73gds8d' );
		?>
		<input type="hidden" name="page" value="plgsggeo_protection"/>
		<input type="hidden" name="action" value="EnableDisable_frontend_protection"/>
		</form>
        
        <form method="post" action="admin.php?page=plgsggeo_protection&tab=0">
        <div <?php echo $block_class; ?>>
        
            <?php
                if (!$isPRO)  {
                    ?><div class="ui red mini message"><center>Your version has limits: maximum 15 countries and maximum 15 IP addresses to block.<br /><b>If you like our plugin, just leave your <a href="https://wordpress.org/support/plugin/wp-geo-website-protection/reviews/" target="_blank">feedback here</a> and contact with our support to get your unlock code</b><br>or <a href="https://www.siteguarding.com/en/buy-extention/wordpress-geo-website-protection" target="_blank">Get Full Version</a> and unlock all the features</center></div><?php
                }
            ?>
			<div class="ui blue mini message"><center>Always remember about search engine bots. Add Google bot IP addresses to allow list. Use quick button below. </center></div>
            <h4 class="ui header">Block (blacklist) by IP or range (your IP is <?php echo $myIP; ?>)</h4>
            
            <div class="ui ignored message">
                  <i class="help circle icon"></i>e.g. 200.150.160.1 or 200.150.160.* or 200.150.*.*
            </div>
            
            <div class="ui input" style="width: 100%;margin-bottom:10px">
                <textarea name="frontend_ip_list" style="width: 100%;height:200px" placeholder="Insert IP addresses or range you want to block, one by line"><?php echo $params['frontend_ip_list']; ?></textarea>
            </div>
            <input type="submit" name="submit" id="submit" class="ui green button" value="Save & Apply">
            
            <h4 class="ui header">Block by country (your country is <?php echo $myCountry; ?>)</h4>
<div class="ui icon message">
  <div class="content">
    <div style="text-align:center" class="header">
      Quick buttons:
    </div>
    <p style="text-align:center"><a class="mini ui button bttn_bottom" href="javascript:SelectCountries('', '.all');">Uncheck All</a> <a class="mini ui button bttn_bottom" href="javascript:SelectCountries('.all', '.country_<?php echo $myCountryCode; ?>');">Select All (exclude <?php echo $myCountryCode; ?>)</a> <a class="mini ui button bttn_bottom" href="javascript:SelectCountries('.all', '.country_US,.country_CA');">Select All (exclude USA, Canada)</a> <a class="mini ui button bttn_bottom" href="javascript:SelectCountries('.all', '.europe');">Select All (exclude EU countries)</a> <a class="mini ui button bttn_bottom" href="javascript:SelectCountries('.3rdcountry', '');">Select All 3rd party countries</a><br>

                  <a class="mini ui button bttn_bottom" href="javascript:checkByContinent('AF');">Select Africa</a> <a class="mini ui button bttn_bottom" href="javascript:checkByContinent('OC');">Select Oceania</a> <a class="mini ui button bttn_bottom" href="javascript:checkByContinent('EU');">Select Europe</a> <a class="mini ui button bttn_bottom" href="javascript:checkByContinent('SA');">Select South America</a> <a class="mini ui button bttn_bottom" href="javascript:checkByContinent('NA');">Select North America</a> <a class="mini ui button bttn_bottom" href="javascript:checkByContinent('AS');">Select Asia</a> <a class="mini ui button bttn_bottom" href="javascript:checkByContinent('AN');">Select Antarctica</a></p>
  </div>
</div>

            
            <?php echo self::CountryList_checkboxes($params['frontend_country_list']); ?>
            
            <p>&nbsp;</p>
            <input type="submit" name="submit" id="submit" class="ui green button" value="Save & Apply">
            
            
            <h4 class="ui header">Allow (whitelist) by IP or range (your IP is <?php echo $myIP; ?>)</h4>
            
            <div class="ui ignored message">
                  <i class="help circle icon"></i>e.g. 200.150.160.1 or 200.150.160.* or 200.150.*.*
            </div>
            <p>
				<span class="mini ui green button allow">Allow bots</span> Add IP addresses of Google, Bing, Yahoo to allow list 
			</p>
			<?php if (!$isPRO) : ?>
				<script>
				jQuery(document).ready(function(){
					jQuery('.allow').click(function(){
						jQuery('.modal.paid').modal('show');
					});

					

				}); 
				
				</script>
				<div class="tiny ui modal paid">
				  <div class="header c_red">Alert</div>
				  <div class="content">
					<p><b>Available in full version only</b></p>
				  </div>
				  <div class="actions">
					<button class="medium ui cancel button">Close</button>
				  </div>
				</div>
				

			<?php else : ?>
				<script>
				jQuery(document).ready(function(){
					jQuery('.allow').click(function(){
						content = jQuery('.frontend_ip_list_allow').val();
						if (content) content = content + "\n";
						jQuery('.frontend_ip_list_allow').val(content + "#GoogleBot\n64.233.*.*\n66.102.*.*\n66.249.*.*\n72.14.*.*\n74.125.*.*\n209.85.*.*\n216.239.*.*\n\n#BingBot\n104.146.*.*\n\n#MSNBot\n64.4.*.*\n65.52.*.*\n65.53.*.*\n65.54.*.*\n131.253.*.*\n157.54.*.*\n207.46.*.*\n207.68.*.*\n\n\n#Yahoo\n8.12.144.*\n66.196.*.*\n66.228.*.*\n67.195.*.*\n68.142.*.*\n72.30.*.*\n74.6.*.*\n98.136.*.*\n98.137.*.*\n98.138.*.*\n98.139.*.*\n202.160.*.*\n209.191.*.*\n#YandexBot\n100.43.*.*\n");
					});
				});
				
				</script>
			
			
			
			<?php endif; ?>
			<div class="ui input" style="width: 100%;margin-bottom:10px">
				<textarea class="frontend_ip_list_allow" name="frontend_ip_list_allow" style="width: 100%;height:200px" placeholder="Insert IP addresses or range you want to allow, one by line"><?php echo $params['frontend_ip_list_allow']; ?></textarea>
			</div>
			<input type="submit" name="submit" id="submit" class="ui green button" value="Save & Apply">
        </div>
        
		<?php
		wp_nonce_field( 'name_3dfUejeked' );
		?>
		<input type="hidden" name="page" value="plgsggeo_protection"/>
		<input type="hidden" name="action" value="Save_frontend_params"/>
		</form>
        <?php
    }
    
    
    
    
    if ($tab_id == 1)
    {
        ?>
        <h4 class="ui header">Backend protection</h4>
        
        <form method="post" action="admin.php?page=plgsggeo_protection&tab=1">
        
        <p>
        <?php
        if (isset($params['protection_backend']) && intval($params['protection_backend']) == 1) { $block_class = ''; $protection_txt = '<span class="ui green horizontal label">Enabled</span>'; $protection_bttn_txt = 'Disable Protection'; }
        else { $block_class = 'class="hide"'; $protection_txt = '<span class="ui red horizontal label">Disabled</span>'; $protection_bttn_txt = 'Enable Protection'; }
        ?>
        GEO Protection for backend is <?php echo $protection_txt; ?> Visitors from selected countried and selected IP addresses will not be able to login to backend of your website.
        </p>
        <input type="submit" name="submit" id="submit" class="mini ui green button" value="<?php echo $protection_bttn_txt; ?>">
        <p>&nbsp;</p>
		<?php
		wp_nonce_field( 'name_2Jjf73gds8d' );
		?>
		<input type="hidden" name="page" value="plgsggeo_protection"/>
		<input type="hidden" name="action" value="EnableDisable_backend_protection"/>
		</form>
        
        <form method="post" action="admin.php?page=plgsggeo_protection&tab=1">
        <div <?php echo $block_class; ?>>
        
            <?php
                if (!$isPRO)  {
                    ?><div class="ui red mini message"><center>Your version has limits: maximum 15 countries and maximum 15 IP addresses to allow.<br /><b>If you like our plugin, just leave your <a href="https://wordpress.org/support/plugin/wp-geo-website-protection/reviews/" target="_blank">feedback here</a> and contact with our support to get your unlock code</b><br>or <a href="https://www.siteguarding.com/en/buy-extention/wordpress-geo-website-protection" target="_blank">Get Full Version</a> and unlock all the features</center></div><?php
                }
            ?>

            <h4 class="ui header">Block (blacklist) by IP or range (your IP is <?php echo $myIP; ?>)</h4>
            
            <div class="ui ignored message">
                  <i class="help circle icon"></i>e.g. 200.150.160.1 or 200.150.160.* or 200.150.*.*
            </div>
            
            <div class="ui input" style="width: 100%;margin-bottom:10px">
                <textarea name="backend_ip_list" style="width: 100%;height:200px" placeholder="Insert IP addresses or range you want to block, one by line"><?php echo $params['backend_ip_list']; ?></textarea>
            </div>
            <input type="submit" name="submit" id="submit" class="ui green button" value="Save & Apply">
            
            <h4 class="ui header">Block by country (your country is <?php echo $myCountry; ?>)</h4>
            
<div class="ui icon message">
  <div class="content">
    <div style="text-align:center" class="header">
      Quick buttons:
    </div>
    <p style="text-align:center"><a class="mini ui button bttn_bottom" href="javascript:SelectCountries('', '.all');">Uncheck All</a> <a class="mini ui button bttn_bottom" href="javascript:SelectCountries('.all', '.country_<?php echo $myCountryCode; ?>');">Select All (exclude <?php echo $myCountryCode; ?>)</a> <a class="mini ui button bttn_bottom" href="javascript:SelectCountries('.all', '.country_US,.country_CA');">Select All (exclude USA, Canada)</a> <a class="mini ui button bttn_bottom" href="javascript:SelectCountries('.all', '.europe');">Select All (exclude EU countries)</a> <a class="mini ui button bttn_bottom" href="javascript:SelectCountries('.3rdcountry', '');">Select All 3rd party countries</a><br>

                  <a class="mini ui button bttn_bottom" href="javascript:checkByContinent('AF');">Select Africa</a> <a class="mini ui button bttn_bottom" href="javascript:checkByContinent('OC');">Select Oceania</a> <a class="mini ui button bttn_bottom" href="javascript:checkByContinent('EU');">Select Europe</a> <a class="mini ui button bttn_bottom" href="javascript:checkByContinent('SA');">Select South America</a> <a class="mini ui button bttn_bottom" href="javascript:checkByContinent('NA');">Select North America</a> <a class="mini ui button bttn_bottom" href="javascript:checkByContinent('AS');">Select Asia</a> <a class="mini ui button bttn_bottom" href="javascript:checkByContinent('AN');">Select Antarctica</a></p>
  </div>
</div>
            
            <?php echo self::CountryList_checkboxes($params['backend_country_list']); ?>
            
            
            <p>&nbsp;</p>
            <input type="submit" name="submit" id="submit" class="ui green button" value="Save & Apply">
            
            
            <h4 class="ui header">Allow (whitelist) by IP or range (your IP is <?php echo $myIP; ?>)</h4>
            
            <div class="ui ignored message">
                  <i class="help circle icon"></i>e.g. 200.150.160.1 or 200.150.160.* or 200.150.*.*
            </div>
            
            <div class="ui input" style="width: 100%;margin-bottom:10px">
                <textarea name="backend_ip_list_allow" style="width: 100%;height:200px" placeholder="Insert IP addresses or range you want to allow, one by line"><?php echo $params['backend_ip_list_allow']; ?></textarea>
            </div>
            <input type="submit" name="submit" id="submit" class="ui green button" value="Save & Apply">
            
            
        </div>
        
		<?php
		wp_nonce_field( 'name_3dfUejeked' );
		?>
		<input type="hidden" name="page" value="plgsggeo_protection"/>
		<input type="hidden" name="action" value="Save_backend_params"/>
		</form>
        <?php
    }
    
    

	

    if ($tab_id == 5)
    {
		?>
		<h4 class="ui header">GEO Redirect</h4>
		
		<p>You can redirect the visitors from selected countries to specific page of your website or another domain.</p>
<div class="ui ignored message">
    <h4 class="ui header"><i class="help circle icon"></i>Samples and Syntax</h4>
    <br />
    <b>Redirect to local page</b>. If you need to redirect all visitors e.g. from Italy to special contact us page on your website.<br>
    <br> 
    Sample of syntax: /it/contact-us<br>
    <br>
    <b>Redirect to another website</b>. If you need to redirect all visitors e.g. from Italy to another website.<br>
    <br> 
    Sample of syntax: https://www.site.it<br>
    <br>
    <b>Redirect to another website and keep URL</b>. If you need to redirect all visitors e.g. from Italy to another website and keep the same links structure.<br>
    <br> 
    Sample of syntax: https://www.site.it%PATH%<br>
    <br>
    E.g. your website is https://www.mysite.com. When visitor from Italy opens https://www.mysite.com , he will be redirected to https://www.site.it (home page) , but if visitor opens https://www.mysite.com/contact-us , he will be redirected to https://www.site.it/contact-us (URL will keep /contact-us, the same links structure as on your original website)
</div>
		<form method="post" action="admin.php?page=plgsggeo_protection&tab=5">
		<table class="ui single line selectable table">
		  <thead>
			<tr>
			  <th>Country</th>
			  <th>Redirect URL</th>
			</tr>
		  </thead>
		  <tbody>
			<?php
			foreach (SEO_SG_Protection::$country_list as $country_code => $country_name)
			{
				?>
				<tr>
				  <td class="two wide"><?php echo $country_name; ?></td>
				  <td>
						<div class="ui form">
							  <input class="ui input sixteen wide field" placeholder="e.g. /contact-us   or   http://www.google.com/search" type="text" name="redirect[<?php echo $country_code; ?>]" value="<?php if (isset($params['redirects'][$country_code])) echo $params['redirects'][$country_code]; ?>">
						</div>
				  </td>
				</tr>
				<?php
			}
			?>
		  </tbody>
		</table>
		<?php
		wp_nonce_field( 'name_4b5jh35b3h5v4' );
		?>
		<input type="hidden" name="action" value="save_redirect_params"/>
		<input type="submit" name="submit" id="submit" class="ui green button" value="Save &amp; Apply">
		</form>
		<?php
	}

	
	
	
    if ($tab_id == 2)
    {
        wp_enqueue_script( 'plgsggeo_LoadCharts' );
        
        ?>
        <h4 class="ui header">Charts</h4>
        
       
        <?php
        $pie_array = SEO_SG_Protection::GeneratePieData(1);
        $pie_data = SEO_SG_Protection::PreparePieData($pie_array);
        ?>
		<script type="text/javascript">
        jQuery(function () {
            jQuery('#pie_container_1').<?php echo 'high'.'charts'; ?>({
                credits: false,
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: 'Blocked activity for the last 24 hours'
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                            style: {
                                color: (<?php echo 'High'.'cha'.'rts'; ?>.theme && <?php echo 'High'.'cha'.'rts'; ?>.theme.contrastTextColor) || 'black'
                            }
                        }
                    }
                },
                series: [{
                    name: 'Total',
                    colorByPoint: true,
                    data: [<?php echo implode(", ", $pie_data); ?>]
                }]
            });
        });
        		</script>

        
        <div id="pie_container_1" style="min-width: 310px; height: 400px; max-width: 600px; margin: 0 auto"></div>
        
        <hr />

        <?php
        $pie_array = SEO_SG_Protection::GeneratePieData(7);
        $pie_data = SEO_SG_Protection::PreparePieData($pie_array);
        ?>
		<script type="text/javascript">
        jQuery(function () {
            jQuery('#pie_container_2').<?php echo 'high'.'char'.'ts'; ?>({
                credits: false,
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: 'Blocked activity for the last 7 days'
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                            style: {
                                color: (<?php echo 'High'.'cha'.'rts'; ?>.theme && <?php echo 'High'.'cha'.'rts'; ?>.theme.contrastTextColor) || 'black'
                            }
                        }
                    }
                },
                series: [{
                    name: 'Total',
                    colorByPoint: true,
                    data: [<?php echo implode(", ", $pie_data); ?>]
                }]
            });
        });
        		</script>

        
        <div id="pie_container_2" style="min-width: 310px; height: 400px; max-width: 600px; margin: 0 auto"></div>
        
        <hr />
        
        <?php
        $pie_array = SEO_SG_Protection::GeneratePieData(30);
        $pie_data = SEO_SG_Protection::PreparePieData($pie_array);
        ?>
		<script type="text/javascript">
        jQuery(function () {
            jQuery('#pie_container_3').<?php echo 'high'.'chart'.'s'; ?>({
                credits: false,
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                title: {
                    text: 'Blocked activity for the last 30 days'
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                            style: {
                                color: (<?php echo 'High'.'cha'.'rts'; ?>.theme && <?php echo 'High'.'cha'.'rts'; ?>.theme.contrastTextColor) || 'black'
                            }
                        }
                    }
                },
                series: [{
                    name: 'Total',
                    colorByPoint: true,
                    data: [<?php echo implode(", ", $pie_data); ?>]
                }]
            });
        });
        		</script>
        	</head>
        	<body>
        
        <div id="pie_container_3" style="min-width: 310px; height: 400px; max-width: 600px; margin: 0 auto"></div>
        
        <hr />
        
        <?php
        $amount_records = 50;
        $latest_records_array = SEO_SG_Protection::GetLatestRecords($amount_records);
        ?>
        <h4 class="ui header">Latest Logs (latest <?php echo $amount_records; ?> records)</h4>
        <?php
        if (count($latest_records_array) == 0) echo '<p>No records</p>';
        else {
            ?>
            <table class="ui celled table small">
              <thead>
                <tr><th>Date</th>
                <th>Country</th>
                <th>IP address</th>
                <th>URL</th>
              </tr></thead>
              <tbody>
                <?php
                foreach ($latest_records_array as $v) {
                ?>
                <tr>
                  <td><?php echo date("Y-m-d H:i:s", $v->time); ?></td>
                  <td><?php echo SEO_SG_Protection::$country_list[ $v->country_code ].' ['.$v->country_code.']'; ?></td>
                  <td><?php echo $v->ip; ?></td>
                  <td class="tbl_urlrow"><a target="_blank" href="<?php echo $v->url; ?>"><?php echo $v->url; ?></span></a></td>
                </tr>
                <?php
                }
                ?>
              </tbody>
            </table>
            
            <?php
        }
    }
    
    
    if ($tab_id == 3)
    {  
        $isPRO = SEO_SG_Protection::CheckIfPRO();
        if (!$isPRO) $params['protection_by'] = 1;
        
        if ($isPRO)
        {
            $box_text = 'You have <b>PRO version</b>';
        }
        else {
            $box_text = '<span style="color:#9f3a38">You have <b>Free version</b>. Please note free version has some limits. Please <a href="https://www.siteguarding.com/en/wordpress-geo-website-protection" target="_blank">Upgrade</a></span><br><i class="thumbs up icon"></i>Try our <a href="https://wordpress.org/plugins/wp-antivirus-site-protection/" target="_blank">WordPress Antivirus scanner</a> PRO version and get your registration code for GEO protection plugin for free.';
        }
        ?>

		<h4 class="ui header">Customize Block page</h4>
        <div class="ui ignored info message"><center><?php echo $box_text; ?></center></div>
        <form class="ui form" method="post" action="admin.php?page=plgsggeo_protection&tab=3">
        
          <div class="inline field">
            <div id="custom_status" class="ui slider checkbox <?php if(!$isPRO) echo 'disabled'; ?>">
              <input <?php if($isPRO && isset($params['custom_status']) && intval($params['custom_status']) == 1) echo 'checked'; ?> value="1" type="checkbox" tabindex="0" name="custom_status" class="hidden" onchange="ShowHideForm(jQuery(this).is(':checked'), '.show_active')">
              <label>Enable custom design</label>
            </div>
          </div>
		<?php
		wp_nonce_field( 'name_2ZVhTgM2xNU4zNNT' );
		?>      
          <div class="field show_active hidden">
            <label>URL to your logo image</label>
            <input  type="text" name="logo_url" id="logo_url" placeholder="URL to your logo image (e.g. <?php echo get_site_url(); ?>/images/logo.jpg)" value="<?php if(isset($params['logo_url'])) echo str_replace("!APOS!", "'", $params['logo_url']); ?>">
          </div>
          
          <div class="field show_active hidden">
            <label>Text line 1</label>
            <input  type="text" id="text_1"  name="text_1" placeholder="Default: Access is not allowed from your IP or your country." value="<?php if(isset($params['text_1'])) echo str_replace("!APOS!", "'", $params['text_1']); ?>">
          </div>
          
          <div class="field show_active hidden">
            <label>Text line 2</label>
            <input  type="text" id="text_2" name="text_2" placeholder="Default: If you think it's a mistake, please contact with the websmater of the website." value="<?php if(isset($params['text_2'])) echo str_replace("!APOS!", "'", $params['text_2']); ?>">
          </div>
          
          <div class="inline field show_active hidden">
            <div class="ui slider checkbox" id="hide_debug">
              <input <?php if(isset($params['hide_debug']) && intval($params['hide_debug']) == 1) echo 'checked'; ?> value="1"  type="checkbox" tabindex="0" name="hide_debug" class="hidden">
              <label>Hide text: If you the owner of the website. Please enable DEBUG mode in your WordPress (use FTP) to disable GEO Protection.</label>
            </div>
          </div>
          
          <div class="inline field show_active hidden">
            <div class="ui slider checkbox" id="hide_ipinfo">
              <input <?php if(isset($params['hide_ipinfo']) && intval($params['hide_ipinfo']) == 1) echo 'checked'; ?> value="1" type="checkbox" tabindex="0" id="hide_ipinfo" name="hide_ipinfo" class="hidden">
              <label>Hide session information: Your IP: xxx.xxx.xxx.xxx, Country</label>
            </div>
          </div>
          

          
            <?php if ($isPRO) : ?>
            <div class="ui mini positive message">
              <div class="header">
                Help & Tips
              </div>
              <p>If you need help to customize GEO block page for your website, please contact with our support. It's free of charge.</p>
            </div>
			<?php else : ?>
			<div class="ui mini negative message">
              <div class="header">
                Help & Tips
              </div>
              <p>This feature is available in the full version only.</p>
            </div>
        <?php endif; ?>

              <input class="ui right floated green button  <?php if(!$isPRO) echo 'disabled'; ?>" type="submit" value="Save" />
<span class="ui right floated button" data-inverted="" data-tooltip="Preview only. It will not save the settings." onclick="BlockPage_Refresh()"><i class="tv icon"></i>Block Page Preview</span>
              

            <input type="hidden" name="action" id="action" value="Customization_save" />
<br><br>
        </form>



		<?php
        
        
    }
    


    
    
    if ($tab_id == 4)
    {   
        $isPRO = SEO_SG_Protection::CheckIfPRO();
        
        $do_update = false;
        
        if ($isPRO) $do_update = true;
        else {
            $last_update_date = SEO_SG_Protection::Get_Last_Update_date_GEOdb();
            if (time() - strtotime(trim($last_update_date)) > 30 * 24 * 60 * 60) $do_update = true;
        }


        if (isset($_GET['geo_update']) && intval($_GET['geo_update']) == 1)
        {
            if ($do_update)
            {
                SEO_SG_Protection::UpdateGEOdb(1);
                
                $message_data = array(
                    'type' => 'info',
                    'header' => '',
                    'message' => 'GEO database updated successfully',
                    'button_text' => '',
                    'button_url' => '',
                    'help_text' => ''
                );
            }
            else {
                $message_data = array(
                    'type' => 'alert',
                    'header' => '',
                    'message' => 'GEO database is not updated. Free version limit: Update once per month. Please get full version.',
                    'button_text' => '',
                    'button_url' => '',
                    'help_text' => ''
                );
            }

            SEO_SG_Protection_HTML::PrintIconMessage($message_data);
        }

                
        
        
        if (!$isPRO) $params['protection_by'] = 1;
        
        if ($isPRO)
        {
            $box_text = 'You have <b>PRO version</b>';
        }
        else {
            $box_text = '<span style="color:#9f3a38">You have <b>Free version</b>. Please note free version has some limits. Please <a href="https://www.siteguarding.com/en/wordpress-geo-website-protection" target="_blank">Upgrade</a></span><br><i class="thumbs up icon"></i>Try our <a href="https://wordpress.org/plugins/wp-antivirus-site-protection/" target="_blank">WordPress Antivirus scanner</a> PRO version and get your registration code for GEO protection plugin for free.';
        }
        ?>
        <h4 class="ui header">Settings</h4>
        
        <div class="ui ignored info message"><center><?php echo $box_text; ?></center></div>
        
        <form method="post" class="ui form" action="admin.php?page=plgsggeo_protection&tab=4">
        
        <div class="ui fluid form">
        
            Registration Code<br>
            <div class="ui input ui-form-row">
              <input class="ui input" size="40" placeholder="Enter your registration code" type="text" name="registration_code" value="<?php if (isset($params['registration_code'])) echo $params['registration_code']; ?>">
            </div><br>
            
            
			<?php if (!$isPRO && !$do_update) { ?>
				<script>
				jQuery(document).ready(function(){
					jQuery('.allow').click(function(){
						jQuery('.modal.paid').modal('show');
					});
				}); 
				</script>
                
				<div class="tiny ui modal paid">
				  <div class="header c_red">Alert</div>
				  <div class="content">
					<p><b>Free version limit: Update once per month. Please get full version.</b></p>
				  </div>
				  <div class="actions">
					<button class="medium ui cancel button">Close</button>
				  </div>
				</div>
			<?php } ?>
            
          <div class="ui checkbox ui-form-row">
            <input type="checkbox" name="auto_geodb_update" value="1" <?php if (!$isPRO) echo 'disabled="disabled"'; ?> <?php if ($params['auto_geodb_update'] == 1) echo 'checked="checked"'; ?>>
            <label>Automatically Update GEO database</label>
          </div>
          <?php
          $last_update_date = SEO_SG_Protection::Get_Last_Update_date_GEOdb();
          if ($last_update_date !== false) echo ' (Last update was: '.$last_update_date.') ';
          ?>
          <a class="mini ui button allow" href="<?php if ($do_update) echo 'admin.php?page=plgsggeo_protection&tab=4&geo_update=1'; else echo 'javascript:;'; ?>">Update Manually</a>

          <br>
          
          <div class="ui checkbox ui-form-row">
            <input type="checkbox" name="protection_by" value="1" <?php if (!$isPRO) echo 'disabled="disabled"'; ?> <?php if ($params['protection_by'] == 1) echo 'checked="checked"'; ?>>
            <label>Enable 'Protected by' sign</label>
          </div>
        </div>
                
        <input type="submit" name="submit" id="submit" class="ui green button" value="Save Settings">
        <p>&nbsp;</p>
		<?php
		wp_nonce_field( 'name_xZU32INTzZM1GFNz' );
		?>
		<input type="hidden" name="page" value="plgsggeo_protection"/>
		<input type="hidden" name="action" value="Save_Settings"/>
		</form>
		
        <hr />

        <h4 class="ui header">Debug mode</h4>
		<?php if(!is_file(ABSPATH . 'geodebug.txt')) : ?>
		<p>DEBUG mode is disabled. To enable DEBUG mode please create an empty file with 'geodebug.txt' name in the root folder of your website:<br><b><?php echo ABSPATH . 'geodebug.txt'; ?></b></p>
		<?php else : ?>
		<p>DEBUG mode is enabled. GEO protection is disabled. To enable the protection please remove 'geodebug.txt' file in the root folder of your website.</p>
		<?php endif; ?>
        
        
        <hr />

        <h4 class="ui header">Support</h4>
        
		<p>
		For more information and details about GEO Website Protection please <a target="_blank" href="https://www.siteguarding.com/en/wordpress-geo-website-protection">click here</a>.<br /><br />
		<a href="http://www.siteguarding.com/livechat/index.html" target="_blank">
			<img src="<?php echo plugins_url('images/livechat.png', __FILE__); ?>"/>
		</a><br />
		For any questions and support please use LiveChat or this <a href="https://www.siteguarding.com/en/contacts" rel="nofollow" target="_blank" title="SiteGuarding.com - Website Security. Professional security services against hacker activity. Daily website file scanning and file changes monitoring. Malware detecting and removal.">contact form</a>.<br>
		<br>
		<a href="https://www.siteguarding.com/" target="_blank">SiteGuarding.com</a> - Website Security. Professional security services against hacker activity.<br />
		</p>
		<?php
        
        
    }
    


    ?>
    
    </div>
           		        
				
			
				<div class="tiny ui modal preview_show">
				  <div class="content">
						 <center><h4 class="ui header" data-inverted="" data-tooltip="Preview only. It will not save the settings."><i class="tv icon"></i>GEO Block Page Preview</h4></center><hr>
						<div id="geo_preview">	
						</div>
				  </div>
				  <div class="actions">
					<button class="medium ui cancel button">Close Preview</button>
				  </div>
				</div>
        
    </div>
	
	
	
    </div>	

    		<?php

    }
    

    
	
	
    public static function CountryList_checkboxes($selected_array = array())
    {
        $selected = array();
        if (count($selected_array))
        {
            foreach ($selected_array as $v)
            {
                $selected[$v] = $v;
            }
            
        }
        $a = '<div class="ui five column grid country_list">'."\n";

        foreach (SEO_SG_Protection::$country_list as $country_code => $country_name)
        {
            if (isset($selected[$country_code])) $checked = 'checked="checked"';
            else $checked = '';
            $a .= '<div class="column"><label><input class="country_'.$country_code.' '.SEO_SG_Protection::$country_type_list[$country_code].'" '.$checked.' type="checkbox" name="country_list[]" value="'.$country_code.'">'.$country_name.'</label></div>'."\n";
        }

        $a .= '</div>';
        
        return $a;
    }
    
	public static function blockPagePreview() {
		$ajaxData = isset($_POST['data']) ? trim($_POST['data']) : false;

		$blockpage_json = array();
        $blockpage_json['logo_url'] = '/wp-content/plugins/wp-geo-website-protection/images/logo_siteguarding.svg';
        $blockpage_json['text_1'] = 'Access is not allowed from your IP or your country.';
        $blockpage_json['text_2'] = 'If you think it\'s a mistake, please contactwith the webmaster of the website';
        $blockpage_json['hide_debug'] = 0;
        $blockpage_json['hide_ipinfo'] = 0;

        if ($ajaxData && SEO_SG_Protection::CheckIfPRO())  {
					// Replace default settings with customized
			$ajaxData = (array)json_decode(stripslashes($ajaxData), true);
			
			if (isset($ajaxData['custom_status']) && intval($ajaxData['custom_status']) == 1) {
				if ($ajaxData['logo_url'] != '') $blockpage_json['logo_url'] = $ajaxData['logo_url'];
				if ($ajaxData['text_1'] != '') $blockpage_json['text_1'] = $ajaxData['text_1'];
				if ($ajaxData['text_2'] != '') $blockpage_json['text_2'] = $ajaxData['text_2'];
				
				$blockpage_json['hide_debug'] = intval($ajaxData['hide_debug']);
				$blockpage_json['hide_ipinfo'] = intval($ajaxData['hide_ipinfo']);
			}

		} 

		$myIP = SEO_SG_Protection::GetMyIP();
		$myCountryCode = SEO_SG_Protection::GetCountryCode($myIP);
        
		$logo_url = '';
        if ($blockpage_json['logo_url'] != '') $logo_url = '<p><img style="max-width:300px;max-height:200px" src="'.$blockpage_json['logo_url'].'" id="logo"></p>';

        $debug_info = '';
        if ($blockpage_json['hide_debug'] == 0) $debug_info = '<p>If you the owner of the website. Please enable DEBUG mode in your WordPress (use FTP) to disable GEO Protection.<br>
            Read more about it on <a target="_blank" href="https://codex.wordpress.org/Debugging_in_WordPress">Debugging in WordPress</a> or contact with <a target="_blank" href="https://www.siteguarding.com/en/contacts">SiteGuarding.com support</a></p>';        

        $ipinfo = '';
        if ($blockpage_json['hide_ipinfo'] == 0) {
			$ipinfo = '<h4>Session details:</h4><p>IP: '.$myIP.'</p>';
			if ($myCountryCode != '') $ipinfo .= '<p>Country: '.SEO_SG_Protection::$country_list[$myCountryCode].'</p>';
		}
		
		?>
		        <div style="margin:30px auto; max-width: 400px; max-height: 450px;text-align: center;">
			<?php echo $logo_url; ?>
            
            <h3 style="color: #de0027; text-align: center;"><?php echo $blockpage_json['text_1']; ?></h3>
            <p><?php echo $blockpage_json['text_2']; ?></p>
            
            
            <?php echo $debug_info; ?>

            <?php echo $ipinfo; ?>
            <p>&nbsp;</p>
            

            <p style="font-size: 70%;">Powered by <a target="_blank" href="https://www.siteguarding.com/">SiteGuarding.com</a></p>


        </div>
		<?php
	}
    
    
    public static function PrintIconMessage($data)
    {
        $rand_id = "id_".rand(1,10000).'_'.rand(1,10000);
        if ($data['type'] == '' || $data['type'] == 'alert') {$type_message = 'negative'; $icon = 'warning sign';}
        if ($data['type'] == 'ok') {$type_message = 'green'; $icon = 'checkmark box';}
        if ($data['type'] == 'info') {$type_message = 'yellow'; $icon = 'info';}
        ?>
        <div class="ui icon <?php echo $type_message; ?> message">
            <i class="<?php echo $icon; ?> icon"></i>
            <div class="msg_block_row">
                <?php
                if ($data['button_text'] != '' || $data['help_text'] != '') {
                ?>
                <div class="msg_block_txt">
                    <?php
                    if ($data['header'] != '') {
                    ?>
                    <div class="header"><?php echo $data['header']; ?></div>
                    <?php
                    }
                    ?>
                    <?php
                    if ($data['message'] != '') {
                    ?>
                    <p><?php echo $data['message']; ?></p>
                    <?php
                    }
                    ?>
                </div>
                <div class="msg_block_btn">
                    <?php
                    if ($data['help_text'] != '') {
                    ?>
                    <a class="link_info" href="javascript:;" onclick="InfoBlock('<?php echo $rand_id; ?>');"><i class="help circle icon"></i></a>
                    <?php
                    }
                    ?>
                    <?php
                    if ($data['button_text'] != '') {
                        if (!isset($data['button_url_target']) || $data['button_url_target'] == true) $new_window = 'target="_blank"';
                        else $new_window = '';
                    ?>
                    <a class="mini ui green button" <?php echo $new_window; ?> href="<?php echo $data['button_url']; ?>"><?php echo $data['button_text']; ?></a>
                    <?php
                    }
                    ?>
                </div>
                    <?php
                    if ($data['help_text'] != '') {
                    ?>
                        <div style="clear: both;"></div>
                        <div id="<?php echo $rand_id; ?>" style="display: none;">
                            <div class="ui divider"></div>
                            <p><?php echo $data['help_text']; ?></p>
                        </div>
                    <?php
                    }
                    ?>
                <?php
                } else {
                ?>
                    <?php
                    if ($data['header'] != '') {
                    ?>
                    <div class="header"><?php echo $data['header']; ?></div>
                    <?php
                    }
                    ?>
                    <?php
                    if ($data['message'] != '') {
                    ?>
                    <p><?php echo $data['message']; ?></p>
                    <?php
                    }
                    ?>
                <?php
                }
                ?>
            </div> 
        </div>
        <?php
    }
    
    
    public static function BlockPage($myIP, $myCountryCode = '')
    {
        ?><html><head>
        <link rel="stylesheet" type="text/css" href="<?php echo plugins_url('images/logo_siteguarding.svg', __FILE__); ?>">
        </head>
        <body>
        <div style="margin:100px auto; max-width: 500px;text-align: center;">
            <p><img src="<?php echo plugins_url('images/logo_siteguarding.svg', __FILE__); ?>"/></p>
            <p>&nbsp;</p>
            <h3 style="color: #de0027; text-align: center;">Access is not allowed from your IP or your country.</h3>
            <p>If you think it's a mistake, please contact with the websmater of the website.</p>
            <p>If you the owner of the website. Please enable DEBUG mode in your WordPress (use FTP) to disable GEO Protection.<br>
            Read more about it on <a target="_blank" href="https://codex.wordpress.org/Debugging_in_WordPress">Debugging in WordPress</a> or contact with <a target="_blank" href="https://www.siteguarding.com/en/contacts">SiteGuarding.com support</a></p>
            <h4>Session details:</h4>
            <p>IP: <?php echo $myIP; ?></p>
            <?php
            if ($myCountryCode != '') echo '<p>Country: '.SEO_SG_Protection::$country_list[$myCountryCode].'</p>';
            ?>
            <p>&nbsp;</p>
            <p>&nbsp;</p>

            <p style="font-size: 70%;">Powered by <a target="_blank" href="https://www.siteguarding.com/">SiteGuarding.com</a></p>


        </div>
        </body></html>
        <?php

        die();
    }
    
}


class SEO_SG_Protection
{
    public static $country_list = array(
        "AF" => "Afghanistan",   // Afghanistan
        "AL" => "Albania",   // Albania
        "DZ" => "Algeria",   // Algeria
        "AS" => "American Samoa",   // American Samoa
        "AD" => "Andorra",   // Andorra 
        "AO" => "Angola",   // Angola
        "AI" => "Anguilla",   // Anguilla
        "AQ" => "Antarctica",   // Antarctica
        "AG" => "Antigua and Barbuda",   // Antigua and Barbuda
        "AR" => "Argentina",   // Argentina
        "AM" => "Armenia",   // Armenia
        "AW" => "Aruba",   // Aruba 
        "AU" => "Australia",   // Australia 
        "AT" => "Austria",   // Austria
        "AZ" => "Azerbaijan",   // Azerbaijan
        "BS" => "Bahamas",   // Bahamas
        "BH" => "Bahrain",   // Bahrain 
        "BD" => "Bangladesh",   // Bangladesh
        "BB" => "Barbados",   // Barbados 
        "BY" => "Belarus",   // Belarus 
        "BE" => "Belgium",   // Belgium
        "BZ" => "Belize",   // Belize
        "BJ" => "Benin",   // Benin
        "BM" => "Bermuda",   // Bermuda
        "BT" => "Bhutan",   // Bhutan
        "BO" => "Bolivia",   // Bolivia
        "BA" => "Bosnia and Herzegovina",   // Bosnia and Herzegovina
        "BW" => "Botswana",   // Botswana
        "BV" => "Bouvet Island",   // Bouvet Island
        "BR" => "Brazil",   // Brazil
        "IO" => "British Indian Ocean Territory",   // British Indian Ocean Territory
        "VG" => "British Virgin Islands",   // British Virgin Islands,
        "BN" => "Brunei Darussalam",   // Brunei Darussalam
        "BG" => "Bulgaria",   // Bulgaria
        "BF" => "Burkina Faso",   // Burkina Faso
        "BI" => "Burundi",   // Burundi
        "KH" => "Cambodia",   // Cambodia 
        "CM" => "Cameroon",   // Cameroon
        "CA" => "Canada",   // Canada 
        "CV" => "Cape Verde",   // Cape Verde
        "KY" => "Cayman Islands",   // Cayman Islands
        "CF" => "Central African Republic",   // Central African Republic
        "TD" => "Chad",   // Chad
        "CL" => "Chile",   // Chile
        "CN" => "China",   // China
        "CX" => "Christmas Island",   // Christmas Island
        "CC" => "Cocos (Keeling Islands)",   // Cocos (Keeling Islands)
        "CO" => "Colombia",   // Colombia
        "KM" => "Comoros",   // Comoros
        "CG" => "Congo",   // Congo 
        "CK" => "Cook Islands",   // Cook Islands
        "CR" => "Costa Rica",   // Costa Rica 
        "HR" => "Croatia (Hrvatska)",   // Croatia (Hrvatska
        "CY" => "Cyprus",   // Cyprus
        "CZ" => "Czech Republic",   // Czech Republic
        "CG" => "Democratic Republic of Congo",   // Democratic Republic of Congo,
        "DK" => "Denmark",   // Denmark
        "DJ" => "Djibouti",   // Djibouti
        "DM" => "Dominica",   // Dominica
        "DO" => "Dominican Republic",   // Dominican Republic
        "TP" => "East Timor",   // East Timor
        "EC" => "Ecuador",   // Ecuador
        "EG" => "Egypt",   // Egypt 
        "SV" => "El Salvador",   // El Salvador 
        "GQ" => "Equatorial Guinea",   // Equatorial Guinea
        "ER" => "Eritrea",   // Eritrea 
        "EE" => "Estonia",   // Estonia 
        "ET" => "Ethiopia",   // Ethiopia
        "FK" => "Falkland Islands (Malvinas)",   // Falkland Islands (Malvinas)
        "FO" => "Faroe Islands",   // Faroe Islands 
        "FM" => "Federated States of Micronesia",   // Federated States of Micronesia,
        "FJ" => "Fiji",   // Fiji
        "FI" => "Finland",   // Finland
        "FR" => "France",   // France
        "GF" => "French Guiana",   // French Guiana
        "PF" => "French Polynesia",   // French Polynesia
        "TF" => "French Southern Territories",   // French Southern Territories
        "GA" => "Gabon",   // Gabon
        "GM" => "Gambia",   // Gambia
        "GE" => "Georgia",   // Georgia
        "DE" => "Germany",   // Germany
        "GH" => "Ghana",   // Ghana
        "GI" => "Gibraltar",   // Gibraltar
        "GR" => "Greece",   // Greece
        "GL" => "Greenland",   // Greenland
        "GD" => "Grenada",   // Grenada 
        "GP" => "Guadeloupe",   // Guadeloupe
        "GU" => "Guam",   // Guam 
        "GT" => "Guatemala",   // Guatemala
        "GN" => "Guinea",   // Guinea
        "GW" => "Guinea-Bissau",   // Guinea-Bissau
        "GY" => "Guyana",   // Guyana
        "HT" => "Haiti",   // Haiti
        "HM" => "Heard and McDonald Islands",   // Heard and McDonald Islands
        "HN" => "Honduras",   // Honduras
        "HK" => "Hong Kong",   // Hong Kong
        "HU" => "Hungary",   // Hungary
        "IS" => "Iceland",   // Iceland
        "IN" => "India",   // India
        "ID" => "Indonesia",   // Indonesia
        "IR" => "Iran",   // Iran
        "IQ" => "Iraq",   // Iraq
        "IE" => "Ireland",   // Ireland
        "IL" => "Israel",   // Israel
        "IT" => "Italy",   // Italy
        "CI" => "Ivory Coast",   // Ivory Coast,
        "JM" => "Jamaica",   // Jamaica
        "JP" => "Japan",   // Japan 
        "JO" => "Jordan",   // Jordan 
        "KZ" => "Kazakhstan",   // Kazakhstan
        "KE" => "Kenya",   // Kenya 
        "KI" => "Kiribati",   // Kiribati 
        "KW" => "Kuwait",   // Kuwait
        "KG" => "Kyrgyzstan",   // Kyrgyzstan
        "LA" => "Laos",   // Laos
        "LV" => "Latvia",   // Latvia
        "LB" => "Lebanon",   // Lebanon
        "LS" => "Lesotho",   // Lesotho
        "LR" => "Liberia",   // Liberia 
        "LY" => "Libya",   // Libya
        "LI" => "Liechtenstein",   // Liechtenstein
        "LT" => "Lithuania",   // Lithuania
        "LU" => "Luxembourg",   // Luxembourg 
        "MO" => "Macau",   // Macau
        "MK" => "Macedonia",   // Macedonia
        "MG" => "Madagascar",   // Madagascar
        "MW" => "Malawi",   // Malawi
        "MY" => "Malaysia",   // Malaysia
        "MV" => "Maldives",   // Maldives
        "ML" => "Mali",   // Mali
        "MT" => "Malta",   // Malta
        "MH" => "Marshall Islands",   // Marshall Islands
        "MQ" => "Martinique",   // Martinique
        "MR" => "Mauritania",   // Mauritania
        "MU" => "Mauritius",   // Mauritius
        "YT" => "Mayotte",   // Mayotte
        "MX" => "Mexico",   // Mexico
        "MD" => "Moldova",   // Moldova
        "MC" => "Monaco",   // Monaco
        "MN" => "Mongolia",   // Mongolia
        "MS" => "Montserrat",   // Montserrat
		'ME' => "Montenegro", // Montenegro
        "MA" => "Morocco",   // Morocco
        "MZ" => "Mozambique",   // Mozambique
        "MM" => "Myanmar",   // Myanmar
        "NA" => "Namibia",   // Namibia
        "NR" => "Nauru",   // Nauru
        "NP" => "Nepal",   // Nepal
        "NL" => "Netherlands",   // Netherlands
        "AN" => "Netherlands Antilles",   // Netherlands Antilles
        "NC" => "New Caledonia",   // New Caledonia
        "NZ" => "New Zealand",   // New Zealand
        "NI" => "Nicaragua",   // Nicaragua
        "NE" => "Nicaragua",   // Niger
        "NG" => "Nigeria",   // Nigeria
        "NU" => "Niue",   // Niue
        "NF" => "Norfolk Island",   // Norfolk Island
        "KP" => "Korea (North)",   // Korea (North)
        "MP" => "Northern Mariana Islands",   // Northern Mariana Islands
        "NO" => "Norway",   // Norway
        "OM" => "Oman",   // Oman
        "PK" => "Pakistan",   // Pakistan
        "PW" => "Palau",   // Palau
        "PA" => "Panama",   // Panama
        "PG" => "Papua New Guinea",   // Papua New Guinea
        "PY" => "Paraguay",   // Paraguay
        "PE" => "Peru",   // Peru
        "PH" => "Philippines",   // Philippines
        "PN" => "Pitcairn",   // Pitcairn
        "PL" => "Poland",   // Poland
        "PT" => "Portugal",   // Portugal
        "PR" => "Puerto Rico",   // Puerto Rico
        "QA" => "Qatar",   // Qatar
        "RE" => "Reunion",   // Reunion
        "RO" => "Romania",   // Romania
        "RU" => "Russian Federation",   // Russian Federation
        "RW" => "Rwanda",   // Rwanda
        "SH" => "Saint Helena and Dependencies",   // Saint Helena and Dependencies,
        "KN" => "Saint Kitts and Nevis",   // Saint Kitts and Nevis
        "LC" => "Saint Lucia",   // Saint Lucia
        "VC" => "Saint Vincent and The Grenadines",   // Saint Vincent and The Grenadines
        "VC" => "Saint Vincent and the Grenadines",   // Saint Vincent and the Grenadines,
        "WS" => "Samoa",   // Samoa
        "SM" => "San Marino",   // San Marino
        "ST" => "Sao Tome and Principe",   // Sao Tome and Principe 
        "SA" => "Saudi Arabia",   // Saudi Arabia
        "SN" => "Senegal",   // Senegal
		"RS" => "Serbia",   // Serbia
        "SC" => "Seychelles",   // Seychelles
        "SL" => "Sierra Leone",   // Sierra Leone
        "SG" => "Singapore",   // Singapore
        "SK" => "Slovak Republic",   // Slovak Republic
        "SI" => "Slovenia",   // Slovenia
        "SB" => "Solomon Islands",   // Solomon Islands
        "SO" => "Somalia",   // Somalia
        "ZA" => "South Africa",   // South Africa
        "GS" => "S. Georgia and S. Sandwich Isls.",   // S. Georgia and S. Sandwich Isls.
        "KR" => "South Korea",   // South Korea,
        "ES" => "Spain",   // Spain
        "LK" => "Sri Lanka",   // Sri Lanka
        "SR" => "Suriname",   // Suriname
        "SJ" => "Svalbard and Jan Mayen Islands",   // Svalbard and Jan Mayen Islands
        "SZ" => "Swaziland",   // Swaziland
        "SE" => "Sweden",   // Sweden
        "CH" => "Switzerland",   // Switzerland
        "SY" => "Syria",   // Syria
        "TW" => "Taiwan",   // Taiwan
        "TJ" => "Tajikistan",   // Tajikistan
        "TZ" => "Tanzania",   // Tanzania
        "TH" => "Thailand",   // Thailand
        "TG" => "Togo",   // Togo
        "TK" => "Tokelau",   // Tokelau
        "TO" => "Tonga",   // Tonga
        "TT" => "Trinidad and Tobago",   // Trinidad and Tobago
        "TN" => "Tunisia",   // Tunisia
        "TR" => "Turkey",   // Turkey
        "TM" => "Turkmenistan",   // Turkmenistan
        "TC" => "Turks and Caicos Islands",   // Turks and Caicos Islands
        "TV" => "Tuvalu",   // Tuvalu
        "UG" => "Uganda",   // Uganda
        "UA" => "Ukraine",   // Ukraine
        "AE" => "United Arab Emirates",   // United Arab Emirates
        "UK" => "United Kingdom",   // United Kingdom
        "US" => "United States",   // United States
        "UM" => "US Minor Outlying Islands",   // US Minor Outlying Islands
        "UY" => "Uruguay",   // Uruguay
        "VI" => "US Virgin Islands",   // US Virgin Islands,
        "UZ" => "Uzbekistan",   // Uzbekistan
        "VU" => "Vanuatu",   // Vanuatu
        "VA" => "Vatican City State (Holy See)",   // Vatican City State (Holy See)
        "VE" => "Venezuela",   // Venezuela
        "VN" => "Viet Nam",   // Viet Nam
        "WF" => "Wallis and Futuna Islands",   // Wallis and Futuna Islands
        "EH" => "Western Sahara",   // Western Sahara
        "YE" => "Yemen",   // Yemen
        "ZM" => "Zambia",   // Zambia
        "ZW" => "Zimbabwe",   // Zimbabwe
        "CU" => "Cuba",   // Cuba,
        "IR" => "Iran",   // Iran,
    );
    
    public static $country_type_list = array(
        "AF" => "all 3rdcountry",   // Afghanistan
        "AL" => "all",   // Albania
        "DZ" => "all",   // Algeria
        "AS" => "all",   // American Samoa
        "AD" => "all",   // Andorra 
        "AO" => "all",   // Angola
        "AI" => "all",   // Anguilla
        "AQ" => "all",   // Antarctica
        "AG" => "all",   // Antigua and Barbuda
        "AR" => "all",   // Argentina
        "AM" => "all",   // Armenia
        "AW" => "all",   // Aruba 
        "AU" => "all",   // Australia 
        "AT" => "all europe",   // Austria
        "AZ" => "all",   // Azerbaijan
        "BS" => "all",   // Bahamas
        "BH" => "all",   // Bahrain 
        "BD" => "all",   // Bangladesh
        "BB" => "all",   // Barbados 
        "BY" => "all",   // Belarus 
		'ME' => "all europe", // Montenegro
        "BE" => "all europe",   // Belgium
        "BZ" => "all",   // Belize
        "BJ" => "all",   // Benin
        "BM" => "all",   // Bermuda
        "BT" => "all",   // Bhutan
        "BO" => "all",   // Bolivia
        "BA" => "all",   // Bosnia and Herzegovina
        "BW" => "all",   // Botswana
        "BV" => "all",   // Bouvet Island
        "BR" => "all",   // Brazil
        "IO" => "all",   // British Indian Ocean Territory
        "VG" => "all",   // British Virgin Islands,
        "BN" => "all",   // Brunei Darussalam
        "BG" => "all europe",   // Bulgaria
        "BF" => "all",   // Burkina Faso
        "BI" => "all 3rdcountry",   // Burundi
        "KH" => "all",   // Cambodia 
        "CM" => "all",   // Cameroon
        "CA" => "all",   // Canada 
        "CV" => "all",   // Cape Verde
        "KY" => "all",   // Cayman Islands
        "CF" => "all",   // Central African Republic
        "TD" => "all",   // Chad
        "CL" => "all",   // Chile
        "CN" => "all",   // China
        "CX" => "all",   // Christmas Island
        "CC" => "all",   // Cocos (Keeling Islands)
        "CO" => "all",   // Colombia
        "KM" => "all",   // Comoros
        "CG" => "all 3rdcountry",   // Congo 
        "CK" => "all",   // Cook Islands
        "CR" => "all",   // Costa Rica 
        "HR" => "all europe",   // Croatia (Hrvatska
        "CY" => "all europe",   // Cyprus
        "CZ" => "all europe",   // Czech Republic
        "CG" => "all",   // Democratic Republic of Congo,
        "DK" => "all europe",   // Denmark
        "DJ" => "all",   // Djibouti
        "DM" => "all",   // Dominica
        "DO" => "all",   // Dominican Republic
        "TP" => "all",   // East Timor
        "EC" => "all",   // Ecuador
        "EG" => "all",   // Egypt 
        "SV" => "all",   // El Salvador 
        "GQ" => "all",   // Equatorial Guinea
        "ER" => "all 3rdcountry",   // Eritrea 
        "EE" => "all europe",   // Estonia 
        "ET" => "all 3rdcountry",   // Ethiopia
        "FK" => "all",   // Falkland Islands (Malvinas)
        "FO" => "all",   // Faroe Islands 
        "FM" => "all",   // Federated States of Micronesia,
        "FJ" => "all",   // Fiji
        "FI" => "all europe",   // Finland
        "FR" => "all europe",   // France
        "GF" => "all",   // French Guiana
        "PF" => "all",   // French Polynesia
        "TF" => "all",   // French Southern Territories
        "GA" => "all",   // Gabon
        "GM" => "all",   // Gambia
        "GE" => "all",   // Georgia
        "DE" => "all europe",   // Germany
        "GH" => "all",   // Ghana
        "GI" => "all",   // Gibraltar
        "GR" => "all europe",   // Greece
        "GL" => "all",   // Greenland
        "GD" => "all",   // Grenada 
        "GP" => "all",   // Guadeloupe
        "GU" => "all",   // Guam 
        "GT" => "all",   // Guatemala
        "GN" => "all",   // Guinea
        "GW" => "all 3rdcountry",   // Guinea-Bissau
        "GY" => "all",   // Guyana
        "HT" => "all",   // Haiti
        "HM" => "all",   // Heard and McDonald Islands
        "HN" => "all",   // Honduras
        "HK" => "all",   // Hong Kong
        "HU" => "all europe",   // Hungary
        "IS" => "all",   // Iceland
        "IN" => "all",   // India
        "ID" => "all",   // Indonesia
        "IR" => "all",   // Iran
        "IQ" => "all",   // Iraq
        "IE" => "all europe",   // Ireland
        "IL" => "all",   // Israel
        "IT" => "all europe",   // Italy
        "CI" => "all",   // Ivory Coast,
        "JM" => "all",   // Jamaica
        "JP" => "all",   // Japan 
        "JO" => "all",   // Jordan 
        "KZ" => "all",   // Kazakhstan
        "KE" => "all",   // Kenya 
        "KI" => "all",   // Kiribati 
        "KW" => "all",   // Kuwait
        "KG" => "all",   // Kyrgyzstan
        "LA" => "all",   // Laos
        "LV" => "all europe",   // Latvia
        "LB" => "all",   // Lebanon
        "LS" => "all",   // Lesotho
        "LR" => "all 3rdcountry",   // Liberia 
        "LY" => "all",   // Libya
        "LI" => "all",   // Liechtenstein
        "LT" => "all europe",   // Lithuania
        "LU" => "all europe",   // Luxembourg 
        "MO" => "all",   // Macau
        "MK" => "all",   // Macedonia
        "MG" => "all 3rdcountry",   // Madagascar
        "MW" => "all 3rdcountry",   // Malawi
        "MY" => "all",   // Malaysia
        "MV" => "all",   // Maldives
        "ML" => "all",   // Mali
        "MT" => "all europe",   // Malta
        "MH" => "all",   // Marshall Islands
        "MQ" => "all",   // Martinique
        "MR" => "all",   // Mauritania
        "MU" => "all",   // Mauritius
        "YT" => "all",   // Mayotte
        "MX" => "all",   // Mexico
        "MD" => "all",   // Moldova
        "MC" => "all",   // Monaco
        "MN" => "all",   // Mongolia
        "MS" => "all",   // Montserrat
        "MA" => "all",   // Morocco
        "MZ" => "all",   // Mozambique
        "MM" => "all",   // Myanmar
        "NA" => "all",   // Namibia
        "NR" => "all",   // Nauru
        "NP" => "all",   // Nepal
        "NL" => "all europe",   // Netherlands
        "AN" => "all",   // Netherlands Antilles
        "NC" => "all",   // New Caledonia
        "NZ" => "all",   // New Zealand
        "NI" => "all",   // Nicaragua
        "NE" => "all 3rdcountry",   // Niger
        "NG" => "all",   // Nigeria
        "NU" => "all",   // Niue
        "NF" => "all",   // Norfolk Island
        "KP" => "all",   // Korea (North)
        "MP" => "all",   // Northern Mariana Islands
        "NO" => "all",   // Norway
        "OM" => "all",   // Oman
        "PK" => "all",   // Pakistan
        "PW" => "all",   // Palau
        "PA" => "all",   // Panama
        "PG" => "all",   // Papua New Guinea
        "PY" => "all",   // Paraguay
        "PE" => "all",   // Peru
        "PH" => "all",   // Philippines
        "PN" => "all",   // Pitcairn
        "PL" => "all europe",   // Poland
        "PT" => "all europe",   // Portugal
        "PR" => "all",   // Puerto Rico
        "QA" => "all",   // Qatar
        "RE" => "all",   // Reunion
        "RO" => "all europe",   // Romania
        "RU" => "all",   // Russian Federation
        "RW" => "all",   // Rwanda
        "SH" => "all",   // Saint Helena and Dependencies,
        "KN" => "all",   // Saint Kitts and Nevis
        "LC" => "all",   // Saint Lucia
        "VC" => "all",   // Saint Vincent and The Grenadines
        "VC" => "all",   // Saint Vincent and the Grenadines,
        "WS" => "all",   // Samoa
        "SM" => "all",   // San Marino
        "ST" => "all",   // Sao Tome and Principe 
        "SA" => "all",   // Saudi Arabia
        "SN" => "all",   // Senegal
		"RS" => "all",   // Serbia
        "SC" => "all",   // Seychelles
        "SL" => "all 3rdcountry",   // Sierra Leone
        "SG" => "all",   // Singapore
        "SK" => "all europe",   // Slovak Republic
        "SI" => "all europe",   // Slovenia
        "SB" => "all",   // Solomon Islands
        "SO" => "all",   // Somalia
        "ZA" => "all",   // South Africa
        "GS" => "all",   // S. Georgia and S. Sandwich Isls.
        "KR" => "all",   // South Korea,
        "ES" => "all europe",   // Spain
        "LK" => "all",   // Sri Lanka
        "SR" => "all",   // Suriname
        "SJ" => "all",   // Svalbard and Jan Mayen Islands
        "SZ" => "all",   // Swaziland
        "SE" => "all europe",   // Sweden
        "CH" => "all",   // Switzerland
        "SY" => "all",   // Syria
        "TW" => "all",   // Taiwan
        "TJ" => "all",   // Tajikistan
        "TZ" => "all 3rdcountry",   // Tanzania
        "TH" => "all",   // Thailand
        "TG" => "all",   // Togo
        "TK" => "all",   // Tokelau
        "TO" => "all",   // Tonga
        "TT" => "all",   // Trinidad and Tobago
        "TN" => "all",   // Tunisia
        "TR" => "all",   // Turkey
        "TM" => "all",   // Turkmenistan
        "TC" => "all",   // Turks and Caicos Islands
        "TV" => "all",   // Tuvalu
        "UG" => "all",   // Uganda
        "UA" => "all",   // Ukraine
        "AE" => "all",   // United Arab Emirates
        "UK" => "all europe",   // United Kingdom
        "US" => "all",   // United States
        "UM" => "all",   // US Minor Outlying Islands
        "UY" => "all",   // Uruguay
        "VI" => "all",   // US Virgin Islands,
        "UZ" => "all",   // Uzbekistan
        "VU" => "all",   // Vanuatu
        "VA" => "all",   // Vatican City State (Holy See)
        "VE" => "all",   // Venezuela
        "VN" => "all",   // Viet Nam
        "WF" => "all",   // Wallis and Futuna Islands
        "EH" => "all",   // Western Sahara
        "YE" => "all 3rdcountry",   // Yemen
        "ZM" => "all 3rdcountry",   // Zambia
        "ZW" => "all",   // Zimbabwe
        "CU" => "all",   // Cuba,
        "IR" => "all",   // Iran,
    );
    
    
    public static function API_Request($type = '')
    {
        $plugin_code = 13;
        $website_url = get_site_url();
        
        $url = "https://www.siteguarding.com/ext/plugin_api/index.php";
        $response = wp_remote_post( $url, array(
            'method'      => 'POST',
            'timeout'     => 600,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array(),
            'body'        => array(
                'action' => 'inform',
                'website_url' => $website_url,
                'action_code' => $type,
                'plugin_code' => $plugin_code,
            ),
            'cookies'     => array()
            )
        );
    }

    public static function CopySiteGuardingTools($output = false)
    {
        $file_from = dirname(__FILE__).'/siteguarding_tools.php';
    	if (!file_exists($file_from)) 
        {
            if ($output) die('File absent');
            return;
        }
        $file_to = ABSPATH.'/siteguarding_tools.php';
        $status = copy($file_from, $file_to);
        if ($status === false) 
        {
            if ($output) die('Copy Error');
            return;
        }
        else {
            if ($output) die('Copy OK, size: '.filesize($file_to).' bytes');
        }
    }
    
    public static function UpdateGEOdb($manual = 0, $days = 30)
    {
        $last_update_date = self::Get_Last_Update_date_GEOdb();
        
        $date_days_ago = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d")-$days, date("Y")));
        
        $domain = self::PrepareDomain(get_site_url());
        
        if ($last_update_date === false || $last_update_date <= $date_days_ago || $manual == 1)
        {
            $plg_name = 'wp-geo-website-protection';
            $SITEGUARDING_SERVER = 'https://www.siteguarding.com/ext/updater/updater.php';
            $request_url = $SITEGUARDING_SERVER.'?product=geo_db&domain='.$domain.'&plg_name='.$plg_name;
            $response = wp_remote_get($request_url);
            $json = (array)json_decode($response['body'], true);

            if ($json === false || count($json) == 0) return;   // Error in answer
            
            if (trim($json['status']) != 'ok') return;   // Error

            $new_md5 = trim($json['md5']); 
            
            $geo_file = dirname(__FILE__).'/geo.mmdb';
            if ($new_md5 != md5_file($geo_file))
            {
                // Update
                $request_url = $SITEGUARDING_SERVER.'?product=geo_db&domain='.$domain.'&plg_name='.$plg_name.'&md5='.$new_md5.'&action=download';
                $file_save_tmp = dirname(__FILE__).'/geo.mmdb.tmp';
                $status = self::CreateRemote_file_contents($request_url, $file_save_tmp);
                if ($status !== false && md5_file($file_save_tmp) == $new_md5)
                {
                    unlink($geo_file);
                    copy($file_save_tmp, $geo_file);
                    unlink($file_save_tmp);
                }
            }
            
            // Save update date
            $file = dirname(__FILE__).'/geo_update.log';
            $fp = fopen($file, 'w');
            fwrite($fp, date("Y-m-d"));
            fclose($fp);
        }
    }
    
    public static function Get_Last_Update_date_GEOdb()
    {
        $file = dirname(__FILE__).'/geo_update.log';
        if (!file_exists($file)) return false;
        
        $handle = fopen($file, "r");
        $contents = fread($handle, filesize($file));
        fclose($handle);
        
        return $contents;
    }
    
    public static function CreateRemote_file_contents($url, $dst)
    {
        if (extension_loaded('curl')) 
        {
            $dst = fopen($dst, 'w');
            
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, $url );
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36");
            curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 3600000);
            curl_setopt($ch, CURLOPT_FILE, $dst);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // 10 sec
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 10000); // 10 sec
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $a = curl_exec($ch);
            if ($a === false)  return false;
            
            $info = curl_getinfo($ch);
            
            curl_close($ch);
            fflush($dst);
            fclose($dst);
            
            return $info['size_download'];
        }
        else return false;
    }


    public static function CheckBlockLog()
    {
        $file_tmp_block_log = dirname(__FILE__).'/block.log';
        if (file_exists($file_tmp_block_log))
        {
            $handle = fopen($file_tmp_block_log, "r");
            $contents = fread($handle, filesize($file_tmp_block_log));
            fclose($handle);
            
            unlink($file_tmp_block_log);
            
            $contents = explode("\n", $contents);
            if (count($contents))
            {
                foreach ($contents as $row)
                {
                    $row = (array)json_decode($row, true);
                    self::Save_Block_alert($row);
                }
            }
        }
    }



    public static function CreateSettingsFile()
    {
        $params = self::Get_Params();
		
		$params['params_id'] = uniqid();
        
        $line = '<?php $seo_sg_settings = "'.addslashes(json_encode($params)).'"; ?>';
        
        $fp = fopen(dirname(__FILE__).'/settings.php', 'w');
        fwrite($fp, $line);
        fclose($fp);
    }


	public static function CheckWPConfig_file()
	{
	    if (!file_exists(dirname(__FILE__).'/settings.php')) self::CreateSettingsFile();
        
	    if (!defined('DIRSEP'))
        {
    	    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') define('DIRSEP', '\\');
    		else define('DIRSEP', '/');
        }
        
		if (!defined('ABSPATH') || strlen(ABSPATH) < 8) 
		{
			$scan_path = dirname(__FILE__);
			$scan_path = str_replace(DIRSEP.'wp-content'.DIRSEP.'plugins'.DIRSEP.'wp-geo-website-protection', DIRSEP, $scan_path);
    		//echo TEST;
		}
        else $scan_path = ABSPATH;
        
        $filename = $scan_path.DIRSEP.'wp-config.php';
        if (!is_file($filename)) $filename = dirname($scan_path).DIRSEP.'wp-config.php';
        $handle = fopen($filename, "r");
        if ($handle === false) return false;
        $contents = fread($handle, filesize($filename));
        if ($contents === false) return false;
        fclose($handle);
        
        if (stripos($contents, '45FDLO87BB9-START') === false)     // Not found
        {
            self::PatchWPConfig_file();
            self::PatchHtaccess_file();
        }
    }
    
	public static function PatchWPConfig_file($action = true)   // true - insert, false - remove
	{
	    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') define('DIRSEP', '\\');
		else define('DIRSEP', '/');
        
		$file = dirname(__FILE__).DIRSEP."geo.check.php";

        $integration_code = '<?php /* Siteguarding Block 45FDLO87BB9-START */ if (file_exists("'.$file.'"))include_once("'.$file.'");/* Siteguarding Block 45FDLO87BB9-END */?>';
        
        // Insert code
		if (!defined('ABSPATH') || strlen(ABSPATH) < 8) 
		{
			$scan_path = dirname(__FILE__);
			$scan_path = str_replace(DIRSEP.'wp-content'.DIRSEP.'plugins'.DIRSEP.'wp-geo-website-protection', DIRSEP, $scan_path);
    		//echo TEST;
		}
        else $scan_path = ABSPATH;
        
        $filename = $scan_path.DIRSEP.'wp-config.php';
        if (!is_file($filename)) $filename = dirname($scan_path).DIRSEP.'wp-config.php';
        $handle = fopen($filename, "r");
        if ($handle === false) return false;
        $contents = fread($handle, filesize($filename));
        if ($contents === false) return false;
        fclose($handle);
        
        $pos_code = stripos($contents, '45FDLO87BB9');
        
        if ($action === false)
        {
            // Remove block
            $contents = str_replace($integration_code, "", $contents);
        }
        else {
            // Insert block
            if ( $pos_code !== false/* && $pos_code == 0*/)
            {
                // Skip double code injection
                return true;
            }
            else {
                // Insert
                $contents = $integration_code.$contents;
            }
        }
        
        $handle = fopen($filename, 'w');
        if ($handle === false) 
        {
            // 2nd try , change file permssion to 666
            $status = chmod($filename, 0666);
            if ($status === false) return false;
            
            $handle = fopen($filename, 'w');
            if ($handle === false) return false;
        }
        
        $status = fwrite($handle, $contents);
        if ($status === false) return false;
        fclose($handle);

        
        return true;
	}
    
        
	public static function PatchHtaccess_file($action = true)   // true - insert, false - remove
	{
		$params = self::Get_Params(array('frontend_ip_list_allow', 'backend_ip_list_allow'));
		$frontend_ip_list_allow = isset($params['frontend_ip_list_allow']) ? trim($params['frontend_ip_list_allow']) : '';
		$backend_ip_list_allow = isset($params['backend_ip_list_allow']) ? trim($params['backend_ip_list_allow']) : '';
		
		$whitelist = '';
		$ip_list = $frontend_ip_list_allow . PHP_EOL . $backend_ip_list_allow;

		$ip_list = str_replace(array("*"), "[0-9]{1,3}", trim($ip_list));
        $ip_list = explode("\n", $ip_list);
        $ip_list = array_map('trim', $ip_list);
		$ip_list = array_unique($ip_list);
        if (count($ip_list))
        {
            foreach ($ip_list as $ip)
            {
				$ip = trim($ip);
				if (!$ip) continue;
				$ipArr = explode(".", $ip);
				$whitelist .= "RewriteCond %{REMOTE_ADDR} !^".$ipArr['0'];
				if (isset($ipArr['1']))	$whitelist .= "\.".$ipArr['1'];
				if (isset($ipArr['2']))	$whitelist .= "\.".$ipArr['2'];
				if (isset($ipArr['3']))	$whitelist .= "\.".$ipArr['3'];
				$whitelist .= "$ [NC]" . PHP_EOL;
            }
        }
		if ($whitelist) $whitelist = PHP_EOL . trim($whitelist);
	    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') define('DIRSEP', '\\');
		else define('DIRSEP', '/');
        
		$file = dirname(__FILE__).DIRSEP."geo.check.php";

        $integration_code = "# BEGIN WpGeoProtection

<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{REMOTE_ADDR} !^185\.72\.157\.169$ [NC]
RewriteCond %{REMOTE_ADDR} !^185\.72\.157\.170$ [NC]
RewriteCond %{REMOTE_ADDR} !^185\.72\.157\.171$ [NC]
RewriteCond %{REMOTE_ADDR} !^185\.72\.157\.172$ [NC]
RewriteCond %{REMOTE_ADDR} !^185\.72\.157\.173$ [NC]
RewriteCond %{REMOTE_ADDR} !^78\.87\.3\.20$ [NC]".$whitelist."
RewriteCond %{REQUEST_URI} !\.(webm|ogg|mp4|ico|pdf|flv|jpg|jpeg|png|gif|webp|js|css|swf|x-html|css|xml|js|woff|woff2|ttf|svg|eot|less|cur)$ [NC]
RewriteCond %{HTTP_USER_AGENT} !(googlebot|msnbot|Surp|bingbot|yahoo|yandex) [NC]
RewriteCond %{HTTP:Cookie} !geo_check [NC]
RewriteRule . index.php [L]
</IfModule>

# END WpGeoProtection
";
       // var_dump($integration_code); die;
        // Insert code
		if (!defined('ABSPATH') || strlen(ABSPATH) < 8) 
		{
			$scan_path = dirname(__FILE__);
			$scan_path = str_replace(DIRSEP.'wp-content'.DIRSEP.'plugins'.DIRSEP.'wp-geo-website-protection', DIRSEP, $scan_path);
    		//echo TEST;
		}
        else $scan_path = ABSPATH;
        
        $filename = $scan_path.DIRSEP.'.htaccess';
        if (!is_file($filename)) $filename = dirname($scan_path).DIRSEP.'.htaccess';
        $handle = fopen($filename, "r");
        if ($handle === false) return false;
        $contents = fread($handle, filesize($filename));
        if ($contents === false) return false;
        fclose($handle);
        
        $pos_code = stripos($contents, 'WpGeoProtection');
        
        if ($action === false)
        {
            // Remove block
            //$contents = str_replace($integration_code, "", $contents);
            $contents = preg_replace('/# BEGIN WpGeoProtection[\s\S]+?# END WpGeoProtection[\n\r]{1,2}/', '', $contents);
        }
        else {
            // Insert block
            if ( $pos_code !== false/* && $pos_code == 0*/)
            {
                // Skip double code injection
                return true;
            }
            else {
                // Insert
                $contents = $integration_code.$contents;
            }
        }
        
        $handle = fopen($filename, 'w');
        if ($handle === false) 
        {
            // 2nd try , change file permssion to 666
            $status = chmod($filename, 0666);
            if ($status === false) return false;
            
            $handle = fopen($filename, 'w');
            if ($handle === false) return false;
        }
        
        $status = fwrite($handle, $contents);
        if ($status === false) return false;
        fclose($handle);

        
        return true;
	}
    
    
	
    public static function Add_IP_adresses_shutdown_function()
    {
	    $reason = error_get_last();
		$fp = fopen(dirname(__FILE__).DIRSEP.'debug_geo.txt', 'a');
		$a = date("Y-m-d H:i:s")." Reason: ".$reason['message'].' File: '.$reason['file'].' Line: '.$reason['line'];	
		fwrite($fp, $a);
		fclose($fp);
    }
    
    public static function Add_IP_adresses($remove_file = true)
    {
        error_reporting(0);
        ignore_user_abort(true);
        set_time_limit(0);
        ini_set('memory_limit', '256M');
        
        register_shutdown_function('self::Add_IP_adresses_shutdown_function');
        
        // Find GEO DB files
        $geo_db_array = array();
        foreach (glob(dirname(__FILE__).DIRSEP."geo_base_*.db") as $filename) 
        {
            $geo_db_array[] = $filename;
        }

		global $wpdb;
        
		$table_name = $wpdb->prefix . 'plgsggeo_ip';
        
        self::Set_Params(array('geo_update_progress' => 1));
        
        // Save data to sql
        
        
        // Trunc database with IP
        if (count($geo_db_array) > 0 && file_exists(dirname(__FILE__).DIRSEP."geo_base_0.db"))
        {
            $query = "TRUNCATE ".$table_name.";";
    		$wpdb->query( $query );
        }
        
        
        foreach ($geo_db_array as $file)
        {
            $lines = file($file);
            $i = 0;
            foreach ($lines as $line)
            {
                $i++;
                if (trim($line) == '') continue;
                
                $a = explode(",", $line);
                
                $ip_from = trim(str_replace('"', '', $a[0]));
                $ip_till = trim(str_replace('"', '', $a[1]));
                $country_code = trim(strtoupper(str_replace('"', '', $a[2])));
                
                if (strlen($country_code) != 2) continue;
                if (strpos($ip_from, ":") !== false || strpos($ip_till, ":") !== false) continue;
                
                if (strpos($ip_from, ".") !== false)
                {
                    // Convert to number
                    $tmp_ip = explode(".", $ip_from);
                    $ip_from = $tmp_ip[0]*256*256*256 + $tmp_ip[1]*256*256 + $tmp_ip[2]*256 + $tmp_ip[3];
                }
                if (strpos($ip_till, ".") !== false)
                {
                    // Convert to number
                    $tmp_ip = explode(".", $ip_till);
                    $ip_till = $tmp_ip[0]*256*256*256 + $tmp_ip[1]*256*256 + $tmp_ip[2]*256 + $tmp_ip[3];
                }
                
        		$sql_array = array(
        			'ip_from' => $ip_from,
        			'ip_till' => $ip_till,
                    'country_code' => $country_code
        		);
                
                $wpdb->insert( $table_name, $sql_array ); 
            }
            
            if ($remove_file) unlink($file);
        }
        
        self::Set_Params(array('geo_update_progress' => 0));
    }
    
    
    
    public static function Get_Params($vars = array())
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'plgsggeo_config';
        
        $ppbv_table = $wpdb->get_results("SHOW TABLES LIKE '".$table_name."'" , ARRAY_N);
        if(!isset($ppbv_table[0])) return false;
        
        if (count($vars) == 0)
        {
            $rows = $wpdb->get_results( 
            	"
            	SELECT *
            	FROM ".$table_name."
            	"
            );
        }
        else {
            foreach ($vars as $k => $v) $vars[$k] = "'".$v."'";
            
            $rows = $wpdb->get_results( 
            	"
            	SELECT * 
            	FROM ".$table_name."
                WHERE var_name IN (".implode(',',$vars).")
            	"
            );
        }
        
        $a = array();
        if (count($rows))
        {
            foreach ( $rows as $row ) 
            {
            	$a[trim($row->var_name)] = trim($row->var_value);
            }
        }
    
        return $a;
    }
    
    
    public static function Set_Params($data = array())
    {
		global $wpdb;
		$table_name = $wpdb->prefix . 'plgsggeo_config';
    
        if (count($data) == 0) return;   
        
        foreach ($data as $k => $v)
        {
            $tmp = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $table_name . ' WHERE var_name = %s LIMIT 1;', $k ) );
            
            if ($tmp == 0)
            {
                // Insert    
                $wpdb->insert( $table_name, array( 'var_name' => $k, 'var_value' => $v ) ); 
            }
            else {
                // Update
                $data = array('var_value'=>$v);
                $where = array('var_name' => $k);
                $wpdb->update( $table_name, $data, $where );
            }
        } 
		self::PatchHtaccess_file(false);
		self::PatchHtaccess_file();
    }
    
    public static function GetMyIP()
    {
        $ip_address = $_SERVER["REMOTE_ADDR"];
        if (isset($_SERVER["HTTP_X_REAL_IP"]) && filter_var($_SERVER["HTTP_X_REAL_IP"], FILTER_VALIDATE_IP)) $ip_address = $_SERVER["HTTP_X_REAL_IP"];
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]) && filter_var($_SERVER["HTTP_X_FORWARDED_FOR"], FILTER_VALIDATE_IP)) $ip_address = $_SERVER["HTTP_X_FORWARDED_FOR"];
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"]) && filter_var($_SERVER["HTTP_CF_CONNECTING_IP"], FILTER_VALIDATE_IP)) $ip_address = $_SERVER["HTTP_CF_CONNECTING_IP"];
        
        return $ip_address ;
    }
    
    public static function GetCountryCode($ip)
    {
        if (isset($_COOKIE["GEO_country_code"]) && isset($_COOKIE["GEO_country_code_hash"]))
        {
            $cookie_GEO_country_code = trim($_COOKIE["GEO_country_code"]);
            $cookie_GEO_country_code_hash = trim($_COOKIE["GEO_country_code_hash"]);
            
            $hash = md5($ip.'-'.$cookie_GEO_country_code);
            if ($cookie_GEO_country_code_hash == $hash) return $cookie_GEO_country_code;
        }
        
        if (!class_exists('sg_Geo_IP2Country'))
        {
            include_once(dirname(__FILE__).DIRSEP.'geo.php');
        }
        
        $geo = new sg_Geo_IP2Country;
        $country_code = $geo->getCountryByIP($ip); 
        
        if ($country_code != '')
        {
            // Set cookie
            $hash = md5($ip.'-'.$country_code);
            setcookie("GEO_country_code", $country_code, time()+3600*24);
            setcookie("GEO_country_code_hash", $hash, time()+3600*24);
        }
        
        return $country_code;
        
        /*global $wpdb;
        
        $table_name = $wpdb->prefix . 'plgsggeo_ip';
        
    	$real_ip = $ip;
        $tmp = explode(".", $ip);
        $ip = $tmp[0]*256*256*256 + $tmp[1]*256*256 + $tmp[2]*256 + $tmp[3];
        
        $query = "SELECT country_code
            FROM ".$table_name."
            WHERE ".$ip." BETWEEN ip_from AND ip_till
            LIMIT 1;";

        $rows = $wpdb->get_results($query);

        
        $a = array();
        if (count($rows))
        {
            foreach ( $rows as $row ) 
            {
                // Set cookie
                $hash = md5($ip.'-'.$row->country_code);
                setcookie("GEO_country_code", $row->country_code, time()+3600*24);
                setcookie("GEO_country_code_hash", $hash, time()+3600*24);
            	return trim($row->country_code);
            }
        }
        
        return '';*/
    }
    
    
    public static function Check_if_User_allowed($myCountryCode, $blocked_country_list = array())
    {
        if (count($blocked_country_list) && in_array($myCountryCode, $blocked_country_list)) return false;
        return true;
    }
    
    
    public static function Check_if_User_IP_allowed($ip, $ip_list = '')
    {
        if ($ip_list == '') return true;
        
        $ip_list = str_replace(array(".*.*.*", ".*.*", ".*"), ".", trim($ip_list));
        $ip_list = explode("\n", $ip_list);
        if (count($ip_list))
        {
            foreach ($ip_list as $rule_ip)
            {
                if (strpos($ip, $rule_ip) === 0) 
                {
                    // match
                    return false;
                }
            }
        }
        
        return true;
    }
    
    public static function Check_IP_in_list($ip, $ip_list = '')
    {
        if ($ip_list == '') return false;   // IP is not in the list
        
        $ip_list = str_replace(array(".*.*.*", ".*.*", ".*"), ".", trim($ip_list));
        $ip_list = explode("\n", $ip_list);
        if (count($ip_list))
        {
            foreach ($ip_list as $rule_ip)
            {
                if (strpos($ip, $rule_ip) === 0) 
                {
                    // match
                    return true;    // IP is in the list
                }
            }
        }
        
        return  false;   // IP is not in the list
    }
    
    

    public static function Save_Block_alert($alert_data)
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'plgsggeo_stats';
        
        $sql_array = array(
            'time' => intval($alert_data['time']),
            'ip' => $alert_data['ip'],
            'country_code' => $alert_data['country_code'],
            'url' => addslashes($alert_data['url']),
        );
        
        $wpdb->insert( $table_name, $sql_array ); 
    }
    
    
    public static function Delete_old_logs($days)
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'plgsggeo_stats';
        
        $old_time = time() - $days*24*60*60;
        
        $sql = 'DELETE FROM '.$table_name.' WHERE time < '.$old_time;
        $wpdb->query($sql); 
    }


	public static function PrepareDomain($domain)
	{
	    $host_info = parse_url($domain);
	    if ($host_info == NULL) return false;
	    $domain = $host_info['host'];
	    if ($domain[0] == "w" && $domain[1] == "w" && $domain[2] == "w" && $domain[3] == ".") $domain = str_replace("www.", "", $domain);
	    //$domain = str_replace("www.", "", $domain);
	    
	    return $domain;
	}
    
    public static function CheckIfPRO()
    {
        $domain = self::PrepareDomain(get_site_url());
        
        $params = self::Get_Params(array('registration_code'));
        if (!empty($params)) $registration_code = strtoupper( $params['registration_code'] );
		else return false;
        
        $check_code = strtoupper( md5( md5( md5($domain)."Version 1MI3WNNjkME4TUZj" )."5OJjDFMjjYZk2MZT" ) );
        
        if ($check_code == $registration_code) return true;
        else return false;
    }
    
    public static function CheckAntivirusInstallation()
    {
        // Check for wp-antivirus-site-protection
        $avp_path = dirname(__FILE__);
		$avp_path = str_replace('wp-geo-website-protection', 'wp-antivirus-site-protection', $avp_path);
        if ( file_exists($avp_path) ) return true;
        
        // Check for wp-antivirus-website-protection-and-website-firewall
        $avp_path = dirname(__FILE__);
		$avp_path = str_replace('wp-geo-website-protection', 'wp-antivirus-website-protection-and-website-firewall', $avp_path);
        if ( file_exists($avp_path) ) return true;
        
        // Check for wp-website-antivirus-protection
        $avp_path = dirname(__FILE__);
		$avp_path = str_replace('wp-geo-website-protection', 'wp-website-antivirus-protection', $avp_path);
        if ( file_exists($avp_path) ) return true;

        return false;
    }
    
    public static function GeneratePieData($days = 1)
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'plgsggeo_stats';
        
        $new_time = time() - $days * 24 * 60 * 60;
        
        $query = "SELECT country_code, count(*) AS country_num
            FROM ".$table_name."
            WHERE time > '".$new_time."' 
            GROUP BY country_code
            ORDER BY count(*) desc";

        $rows = $wpdb->get_results($query);
        
        //print_r($rows);

        
        $data = array();
        if (count($rows))
        {
            $total = 0;
            $i_limit = 10;
            foreach ( $rows as $row ) 
            {
                $total = $total + $row->country_num;
                if ($i_limit > 0) $data[ $row->country_code ] = $row->country_num;
                else $data[ 'Other' ] += $row->country_num;
                
                $i_limit--;
            }
            
            //print_r($data);
            
            foreach ($data as $k => $v)
            {
                $data[$k] = round( 100 * $v / $total, 2);
            }
            
            //print_r($data);
        }
        
        return $data;
    }


    public static function PreparePieData($pie_array, $slice_flag = true)
    {
        $a = array();
        if (count($pie_array))
        {
            foreach ($pie_array as $country_code => $country_proc)
            {
                if ($country_code == "Other") $country_name_txt = "Other";
                else $country_name_txt = self::$country_list[ $country_code ];
                if ($country_name_txt == "") $country_name_txt = $country_code;
                
                if ($slice_flag) $txt = "{name: '".addslashes($country_name_txt)."', y: ".$country_proc.", sliced: true, selected: true}";
                else $txt = "{name: '".addslashes($country_name_txt)."', y: ".$country_proc."}";
                $a[] = $txt;
                
                $slice_flag = false;
            }
        }
        
        return $a;
    }
    
    public static function GetLatestRecords($amount)
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'plgsggeo_stats';
        
        $query = "SELECT *
            FROM ".$table_name."
            ORDER BY id DESC
            LIMIT ".$amount;

        $rows = $wpdb->get_results($query);
        
        return $rows;
    }

}

/* Dont remove this code: SiteGuarding_Block_AE74F51A6762 */
