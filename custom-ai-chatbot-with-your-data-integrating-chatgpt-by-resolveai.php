<?php
if (!defined('ABSPATH'))
    exit;
/*
  Plugin Name: Custom AI Chatbot with your data integrating ChatGPT by ResolveAI
  Description: Easily connect your website pages, documents and other data resources to train a 24/7 custom AI for your business. No coding required.
  Version: 1.0
  License: GPL v2 or later
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

class ResolveAIIntegrations {
	
	var $websiteUrl = "https://resolveai.co";
	var $appUrl = "https://app.resolveai.co";
	var $current_user = false;
	var $hostname = '';
	var $theme = '';

    function __construct() {
	    $this->plugin_url = plugin_dir_url(__FILE__);
        $this->pages_types = array('Front Page', 'Blog Index', 'Pages', 'Posts');
        $this->pages = array();
        $pages = get_posts(array(
            'post_type' => 'page',
            'posts_per_page' => -1
        ));
        
		$hostname = parse_url(get_site_url(), PHP_URL_HOST);
        $this->hostname = $hostname;
		$this->theme = wp_get_theme();
        
        $posts_page = sanitize_text_field(get_option('page_for_posts'));
        $front_page = sanitize_text_field(get_option('page_on_front'));

        foreach ($pages as $page) {
            if ($page->ID != $posts_page && $page->ID != $front_page) {
                $this->pages[] = $page;
            }
        }
    }
    
    function get_user_info(){
	    
	    if(!function_exists('wp_get_current_user')) return false;
	    
		$this->current_user = wp_get_current_user(); 
		
		if ( !($this->current_user instanceof WP_User) ) 
			return; 
	}

    function execute_sidewide_widget() {
        $show = false;
        $chatbot_id = get_option('reai_chatbot_id');     
        
        if (trim($chatbot_id) == '') {
            return true;
        }

        $reai_page_type_front_page = get_option('reai_page_type_front-page');
        $reai_page_type_blog_index = get_option('reai_page_type_blog-index');
        $reai_page_type_pages = get_option('reai_page_type_pages');
        $reai_page_type_posts = get_option('reai_page_type_posts');

		$frontpage_id = get_option( 'page_on_front' ); 
		$blog_index_id = get_option( 'page_for_posts' );
		$current_page_id = get_the_ID();  
		
        if ($reai_page_type_front_page === '1' && is_front_page()) {
			$show = true; 
        }
        if ($reai_page_type_blog_index === '1' && is_home() && $current_page_id<>$frontpage_id) {
            $show = true;
        }
        if ($reai_page_type_pages === '1' && is_page() && $current_page_id<>$frontpage_id) {
            $show = true; 
        }
        if ($reai_page_type_posts === '1' && is_single() && $current_page_id<>$frontpage_id) {
            $show = true; 
        }

        if (is_page()) {
            $page_id = get_the_ID();
            //reai_page_show_6
            //reai_page_hide_2
			
            if (get_option('reai_page_show_' . $page_id) == '1') {
                $show = true;
            }
            if (get_option('reai_page_hide_' . $page_id) == '1') {
                $show = false;
            }
        }

        /* by url */

        $url_itself = get_option('reai_url_itself');
        $url_type = get_option('reai_url_type');
        
        if (is_array($url_itself) && !empty($url_itself)) {
            foreach ($url_itself as $key => $value) {
                
				$uri = sanitize_text_field($_SERVER['REQUEST_URI']);
                $ru = str_replace('/','',$uri);
                $va = str_replace('/','',$value);
               
                if(fnmatch($va, $ru) && $url_type[$key]=='show')
                {
                    $show = true;
                }
                
                if(fnmatch($va, $ru) && $url_type[$key]=='hide')
                {
                    $show = false;
                }
                
            }
        }
		
        if ($show) { 
			echo $this->get_widget_code($chatbot_id, false);
        }
    }

    function init() {
        //wp_enqueue_style('reai_css', plugin_dir_url(__FILE__) . 'css/rai-front.css');
    }

    function admin_enqueue_scripts() {
        wp_enqueue_style('reai_css_admin', $this->plugin_url . 'css/rai-admin.css');
        wp_enqueue_script('reai_js_admin', $this->plugin_url . 'js/rai-admin.js');
		wp_localize_script('reai_js_admin', 'ajax_var', array(
			'url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('ajax-nonce')
		));
    }

    function admin_menu() {
        add_menu_page('ResolveAI', 'AI Chat', 'manage_options', 'reai_home', array($this, 'reai_home'), $this->plugin_url . '/images/icon.png');
		add_submenu_page('reai_home', 'Setup', 'Setup', 'manage_options', 'reai_home', array($this, 'reai_home'));
        add_submenu_page('reai_home', 'Floating Widget', 'Floating Widget', 'manage_options', 'reai_widget', array($this, 'reai_pagewide_widget'));
        add_submenu_page('reai_home', 'Shortcodes', 'Shortcodes', 'manage_options', 'reai_shortcode', array($this, 'reai_shortcode'));
    }
    
    function reai_overview() {
        require_once dirname(__FILE__) . '/tmpl/overview.php';
    }

    function verify_nonce($nonce, $name) {
        return isset($nonce) && wp_verify_nonce(sanitize_text_field(wp_unslash($nonce)), $name);
    }

    function reai_home() {

		$saved = false;
        if (!empty($_POST) && isset($_POST['reai_save']) && current_user_can('manage_options')) {

			if(!$this->verify_nonce($_GET['_wpnonce'], 'home-nonce')) {
				$this->handle_nonce_error();
			}
            
            unset($_POST['reai_save']);
            foreach ($_POST as $key => $value) {
	            if(substr( $key, 0, 5 ) === "reai_") {
		            $value = sanitize_text_field($value);
		            update_option($key, $value);
	            }
            }
            $saved = true;
		}
        require_once dirname(__FILE__) . '/tmpl/home.php';
    }

    function reai_pagewide_widget() {
        $saved = false;
        if (!empty($_POST) && isset($_POST['reai_save']) && current_user_can('manage_options')) {

			if(!$this->verify_nonce($_GET['_wpnonce'], 'floating-nonce')) {
				$this->handle_nonce_error();
			}
			
	        $url_itself = [];
	        $url_type = [];
            if (!empty($_POST['url_itself'])) {
                foreach ($_POST['url_itself'] as $key => $value) {
	                
	                if(!empty(trim($value))) {
		                $url_itself[$key] = sanitize_text_field($_POST['url_itself'][$key]);
		                $url_type[$key] = sanitize_text_field($_POST['url_type'][$key]);
	                }
                }
            }
            
            update_option('reai_url_itself', $url_itself);
            update_option('reai_url_type', $url_type);
            
            foreach ($_POST as $key => $value) {
	            if(substr( $key, 0, 5 ) === "reai_") {
		            $value = sanitize_text_field($value);
		            update_option($key, $value);
	            }
            }
            $saved = true;
        }
        require_once dirname(__FILE__) . '/tmpl/widget.php';
    }

	function reai_shortcode() {

		if (!empty($_POST) && isset($_POST['reai_save']) && current_user_can('manage_options')) {

			if(!$this->verify_nonce($_GET['_wpnonce'], 'shortcode-nonce')) {
				$this->handle_nonce_error();
			}
            
		}

		$chatbot_id = !empty($_POST['reai_chatbot_id']) ? sanitize_text_field($_POST['reai_chatbot_id']) : get_option('reai_chatbot_id');
		$reai_width = !empty($_POST['reai_width']) && is_finite($_POST['reai_width']) ? sanitize_text_field($_POST['reai_width']) : "360";
		$reai_width_type = !empty($_POST['reai_width_type']) ? sanitize_text_field($_POST['reai_width_type']) : "px"; 
		$reai_height = !empty($_POST['reai_height']) && is_finite($_POST['reai_height']) ? sanitize_text_field($_POST['reai_height']) : "630";
		$reai_height_type = !empty($_POST['reai_height_type']) ? sanitize_text_field($_POST['reai_height_type']) : "px";
		$reai_align = !empty($_POST['reai_align']) ? sanitize_text_field($_POST['reai_align']) : "left";
		
		if(!empty($chatbot_id)) {
			$reai_shortcode = '[reai_chatbot id="'.$chatbot_id.'" width="'.$reai_width.$reai_width_type.'" height="'.$reai_height.$reai_height_type.'" align="'.$reai_align.'"]';
			//$chat_preview = $this->get_widget_iframe_code($chatbot_id, $reai_width.$reai_width_type, $reai_height.$reai_height_type, $reai_align);
		}		
		
	    require_once dirname(__FILE__) . '/tmpl/shortcode.php';
    }

	function handle_nonce_error() {
		header( "Content-Type: application/json" );
	    echo wp_json_encode(['success'=>false, 'error'=>'nonce']);
		exit();
	}
	
    function reai_chatbot_shortcode($args) {
		ob_start();
		if (isset($args['id'])) {
            $chatbot_id = $args['id'];
			$width = !empty($args['width']) ? $args['width'] : '360px';
			$height = !empty($args['height']) ? $args['height'] : '630px';
			$align = !empty($args['align']) ? $args['align'] : 'left';
			echo $this->get_widget_iframe_code($chatbot_id, $width, $height, $align);
		}	    
		return ob_get_clean();
    }

    function get_widget_code($chatbot_id, $args = false) {
		ob_start();
        if ($chatbot_id) {
            ?>
            <!-- Begin widget code -->
			<script src="https://cdn.resolveai.co/ri.js"  id="resolve-ai-chat-widget" chatbot-id="<?php echo esc_attr($chatbot_id)?>" defer></script>
			<!-- End widget code -->
            <?php
        }
        return ob_get_clean();
    }

	function get_widget_iframe_code($chatbot_id, $width='360px', $height='630px', $align='left') {
		if ($chatbot_id) { 
			return '<!-- Begin widget code --><div style="text-align: '.esc_attr($align).'; "><iframe src="https://app.resolveai.co/embed/chatbot?chatbotId='.esc_attr($chatbot_id).'" style="width: '.esc_attr($width).'; height: '.esc_attr($height).'; border: none; overflow: hidden; box-shadow: none;"></iframe></div><!-- End widget code -->';
        }
    }
    
    function my_plugin_action_links( $links ) {

		$links = array_merge( array(
			'<a href="' . esc_attr( admin_url( '/admin.php?page=reai_home' ) ) . '">' . __( 'Settings', 'textdomain' ) . '</a>'
		), $links );
	
		return $links;	
	}
}

$da = new ResolveAIIntegrations();
add_action('init', array($da, 'init'));
add_action('wp_footer', array($da, 'execute_sidewide_widget'));
add_action('admin_enqueue_scripts', array($da, 'admin_enqueue_scripts'));
add_action('admin_menu', array($da, 'admin_menu'));
add_shortcode('reai_chatbot', array($da, 'reai_chatbot_shortcode'));
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array($da, 'my_plugin_action_links') );
add_action( 'plugins_loaded', array( $da, 'get_user_info' ) );
