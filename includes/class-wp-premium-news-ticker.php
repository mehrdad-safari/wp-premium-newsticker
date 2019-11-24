<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Premium_News_Ticker {

	/**
	 * The single instance of WP_Premium_News_Ticker.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'wp_premium_news_ticker';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );
    
    // Define Shortcodes
    add_shortcode( 'premium_newsticker', array($this,'premium_newsticker_shortcode') );
      
    	
    $news_ticker_post_type='premium_news_ticker';

		// Load API for generic admin functions
		if ( is_admin() ) {
			$this->admin = new WP_Premium_News_Ticker_Admin_API();
			// call MetaBoxes
			add_action('add_meta_boxes', array( $this,'add_metabox_premium_news_ticker'),$news_ticker_post_type);
			add_filter("{$news_ticker_post_type}_custom_fields",array( $this,'metabox_fields_premium_news_ticker_news'),$news_ticker_post_type);
		}

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );
		
		// Register a new post type for newstickers
		
		$this->register_post_type( $news_ticker_post_type, __( 'News Tickers', 'wp-premium-news-ticker' ), __( 'News Ticker', 'wp-premium-news-ticker' ),null,array( 'menu_icon'   => 'dashicons-slides') );
		
		// modify post type for news ticker Manager
		add_filter( "{$news_ticker_post_type}_register_args", array( $this,'filter_custom_fields_news_ticker'),$news_ticker_post_type );
		
		
		
		
	} // End __construct ()


/*
      function to add custom fields for newsticker 
*/
  public function add_metabox_premium_news_ticker($news_ticker_post_type){
  $this->admin->add_meta_box('metabox_premium_news_ticker_txt_content',__('Content For NewsTicker','wp-premium-news-ticker') ,array($news_ticker_post_type));
    
    $this->admin->add_meta_box('metabox_premium_news_ticker_config',__('Configure This NewsTicker','wp-premium-news-ticker') ,array($news_ticker_post_type));
    
  //add shortcode sidebar
    $this->admin->add_meta_box('metabox_premium_news_ticker_shortcode_sidebar',__('ShortCode to Use','wp-premium-news-ticker') ,array($news_ticker_post_type),'side');
  
  }

public function metabox_fields_premium_news_ticker_news(){
global $post;

$short_code=($post->ID)?'[premium_newsticker id="'.$post->ID.'"]':__('Click Save to See the ShortCode','wp-premium-news-ticker');
return array(array(
'metabox'=>"metabox_premium_news_ticker_txt_content",
'type'=>'textarea',
'id'=>'textarea_content_newsticker',
'placeholder'=>__('Separate By new Line, Each news on 1 line','wp-premium-news-ticker'),
'description'=>__('Separate By new Line, Each news on 1 line(you can use html)','wp-premium-news-ticker'),
),
array(
'metabox'=>"metabox_premium_news_ticker_shortcode_sidebar",
'type'=>'text',
'id'=>'shortcode_tocopy_for_newsticker',
'readonly'=>true,
'optional'=>'onclick="this.select();"',
'default'=>$short_code
),
// Configure newsticker
array(
'metabox'=>"metabox_premium_news_ticker_config",
'type'=>'text',
'id'=>'delay_afternews_newsticker',
'default'=>'8',
'placeholder'=>__('Delay after each news in seconds','wp-premium-news-ticker'),
'description'=>__('<br>Delay after each line in seconds.','wp-premium-news-ticker')
),
array(
'metabox'=>"metabox_premium_news_ticker_config",
'type'=>'text',
'id'=>'config_label_for_newsticker',
'default'=>'News',
'placeholder'=>__('Label for newsTicker','wp-premium-news-ticker'),
'description'=>__('<br>Prepend or append a label to the Newsticker','wp-premium-news-ticker')
),
array(
'metabox'=>"metabox_premium_news_ticker_config",
'type'=>'number',
'id'=>'config_label_space_for_newsticker',
'value'=>'2',
'placeholder'=>__('Spacing Around Label','wp-premium-news-ticker'),
'description'=>__('<br>Spacing Around Label(example: 2)','wp-premium-news-ticker')
),
array(
'metabox'=>"metabox_premium_news_ticker_config",
'type'=>'color',
'id'=>'config_label_bg_for_newsticker',
'default'=>'#ff0000',
'description'=>__('Label Background Color','wp-premium-news-ticker')
),
array(
'metabox'=>"metabox_premium_news_ticker_config",
'type'=>'color',
'id'=>'config_txt_bgcolor_for_newsticker',
'default'=>'#ffffff',
'description'=>__('Background Color','wp-premium-news-ticker')
),
array(
'metabox'=>"metabox_premium_news_ticker_config",
'type'=>'color',
'id'=>'config_label_txt_color_for_newsticker',
'default'=>'#ffffff',
'description'=>__('Label Text Color','wp-premium-news-ticker')
),


);
}
  /*
      function to edit custom fields for post type 
            'premium_news_ticker'
  */
  
  public function filter_custom_fields_news_ticker($args,$post_type=array()){
  
  $new_args=array(
  'show_in_nav_menus'   => false,
  'publicly_queryable'  => false,
  'query_var'           => false,
  'rewrite'           => false,
  'exclude_from_search' => true,
  'can_export' => false,
  'supports' => array( 'title'),
  );
  return array_merge($args,$new_args);
  }
	/**
	 * Wrapper function to register a new post type
	 * @param  string $post_type   Post type name
	 * @param  string $plural      Post type item plural name
	 * @param  string $single      Post type item single name
	 * @param  string $description Description of post type
	 * @return object              Post type class object
	 */
	public function register_post_type ( $post_type = '', $plural = '', $single = '', $description = '', $options = array() ) {

		if ( ! $post_type || ! $plural || ! $single ) return;

		$post_type = new WP_Premium_News_Ticker_Post_Type( $post_type, $plural, $single, $description, $options );

		return $post_type;
	}

	/**
	 * Wrapper function to register a new taxonomy
	 * @param  string $taxonomy   Taxonomy name
	 * @param  string $plural     Taxonomy single name
	 * @param  string $single     Taxonomy plural name
	 * @param  array  $post_types Post types to which this taxonomy applies
	 * @return object             Taxonomy class object
	 */
	public function register_taxonomy ( $taxonomy = '', $plural = '', $single = '', $post_types = array(), $taxonomy_args = array() ) {

		if ( ! $taxonomy || ! $plural || ! $single ) return;

		$taxonomy = new WP_Premium_News_Ticker_Taxonomy( $taxonomy, $plural, $single, $post_types, $taxonomy_args );

		return $taxonomy;
	}

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts ($infooter=true) {
		wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version ,$infooter);
		//library for typed.js
		wp_register_script( $this->_token . '-lib-frontend', esc_url( $this->assets_url ) . 'js/lib-typed' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version ,$infooter);
		
		
		wp_enqueue_script( $this->_token . '-lib-frontend' );
		
		wp_enqueue_script( $this->_token . '-frontend' );
	} // End enqueue_scripts ()

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()
	
		/**
	 * Define Main Shortcode for newsticker
	 * @access  public
	 * @since   1.0.0
	 * @return  string
	 */
  public function premium_newsticker_shortcode($atts, $content = null){
  $attribs = shortcode_atts( array(
        'id' => ''
    ), $atts );
    
    if($attribs['id'] && is_numeric($attribs['id'])){
    
    return $this->get_html_newsticker($attribs['id']);
    
    }else{
    return 'This shortCode ID is invalid or doesn\'t exists';
    }
    
  }
	/**
	 * create HTML for newsticker
	 * @access  public
	 * @since   1.0.0
	 * @return  String
	 */
  public function get_html_newsticker($id=null){
  
  $txt_to_type = get_post_meta( $id, 'textarea_content_newsticker', true );
  $label_txt = get_post_meta( $id, 'config_label_for_newsticker', true );
  $label_width = (int)get_post_meta( $id, 'config_label_space_for_newsticker', true );
  //$label_position = get_post_meta( $id, 'config_label_position_for_newsticker', true );
  $label_bg = get_post_meta( $id, 'config_label_bg_for_newsticker', true );
  $label_bg=($label_bg)?$label_bg:'transparent';
  
  $label_txt_color = get_post_meta( $id, 'config_label_txt_color_for_newsticker', true );
  $label_txt_color=($label_txt_color)?$label_txt_color:'transparent';
  
  $bg_txt_color = get_post_meta( $id, 'config_txt_bgcolor_for_newsticker', true );
  $bg_txt_color =($bg_txt_color )?$bg_txt_color :'transparent';
  
  $delay_after_news = (int)get_post_meta( $id, 'delay_afternews_newsticker', true );
  //$label_position=(! empty($label_position))?$label_position:'left';
  $ticker_label='';
  if(strlen(trim($label_txt))>0)
  $ticker_label='<span class="label_for_newsticker" style="float:left;background-color:'.$label_bg.';padding:0 '.$label_width.'%;"><span class="label_txt_for_newsticker" style="color:'.$label_txt_color.'">'.$label_txt.'</span></span>';
  
  if($delay_after_news<0)$delay_after_news=8;
  $delay_after_news=$delay_after_news * 1000;
  $delay_start_news=$delay_after_news - 3000;
  $delay_after_news="^{$delay_after_news}";
  $delay_start_news="^{$delay_start_news}";
// Check if news ticker has a value.
  if ( empty( $txt_to_type ) ) return '';
  
  $strings = '<p>'.$delay_start_news.str_ireplace(array("\r","\n\n","\n"),array('',"\n","{$delay_after_news}</p>\n<p>"),trim($txt_to_type,"\n\r")).' '.$delay_after_news.'</p>';
  
  $out='<div class="wp-premium-newsticker" style="background-color:'.$bg_txt_color.'">'.$ticker_label.'
  <div class="strings-to-type">
  '.$strings.'
  </div>
  <div class="premium-newsticker-type"></div>
  </div>';
  return $out;
}
	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-admin' );
		
		wp_enqueue_style( 'farbtastic' );
    wp_enqueue_script( 'farbtastic' );
	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'wp-premium-news-ticker', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'wp-premium-news-ticker';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main WP_Premium_News_Ticker Instance
	 *
	 * Ensures only one instance of WP_Premium_News_Ticker is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see WP_Premium_News_Ticker()
	 * @return Main WP_Premium_News_Ticker instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}