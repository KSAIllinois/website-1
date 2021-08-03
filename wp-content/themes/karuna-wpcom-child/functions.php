<?php
/**
 * components functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Karuna
 */

/*
 * Make User's name link to User's profile page when displaying it on the Post
 * page
 */
function my_kboard_user_display($user_display, $user_id, $user_name, $plugins, $boardBuilder){
  // ID 12: Marketplace Buy&Sell
	if ( $boardBuilder->board->id == '12' ){
    $obj = new UsersWP_Profile();
    $profile_url = $obj->get_profile_link( get_author_posts_url( $user_id ), $user_id );
    $user_full_name = get_user_meta( $user_id, 'first_name', true ) . ' ' . get_user_meta( $user_id, 'last_name', true );
    $user_display = "<a href=\"$profile_url\" style=\"text-decoration:underline;\">".$user_full_name.'</a>';
	}

	return $user_display;
}
add_filter('kboard_user_display', 'my_kboard_user_display', 10, 5);


/*
 * 
 * KBoard Widget: My Posts
 *
 */
// TODO: Change Class to allow User to see another User's posts
// ^ by retrieving post content from DB with uid parsed from URL
class KBoard_My_Posts_Widget extends WP_Widget {
	
  /*
   * Usage: 
   * - add to Apperance > Widgets > Shortcode Widget > Legacy Widget (pick)
   * - include as shortcode: [widget id="kboard_my_posts_widget-2"]
   */
  public function __construct() {
    // 생성자, 위젯이 실행되면 가장 먼저 처리된다.
    parent::__construct(
      'kboard_my_posts_widget', // widget ID
      'KBoard > 내가 쓴 포스트', // widget Name
      array(
        'classname' => 'kboard_my_posts_widget',
        'description' => '내가 쓴 포스트  목록을 볼 수 있습니다.',
      )
    );
  }
	
	public function widget($args, $instance){
		global $wpdb;
		
		echo $args['before_widget'];
		
		if(!empty($instance['title'])){
			echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
		}
		
		if(!empty($instance['limit'])){
			$limit = intval($instance['limit']);
		}
		
		if($limit <= 0) $limit = 5;
		
		if(is_user_logged_in()){
			$where = array();
			
			// 사용자 ID
			$user_id = get_current_user_id();
			$where[] = "`member_uid`='{$user_id}'";
			
			// 제외할 게시판 아이디
			if(!empty($instance['exclude'])){
				$exclude = esc_sql($instance['exclude']);
				$where[] = "`board_id` NOT IN ({$exclude})";
			}
			
			// 휴지통에 없는 게시글만 불러온다.
			$where[] = "(`status`='' OR `status` IS NULL OR `status`='pending_approval')";
			
			$where = implode(' AND ', $where);
			$results = $wpdb->get_results("SELECT `uid` FROM `{$wpdb->prefix}kboard_board_content` WHERE {$where} ORDER BY `date` DESC LIMIT {$limit}");
			
			if(!$results){
				echo '<p>내 게시글이 없습니다.</p>';
			}
			else{
				$url = new KBUrl();
				
				echo '<ul>';
				
				foreach($results as $row){
					echo '<li>';
					
					$content = new KBContent();
          $content->initWithUID($row->uid); // pulls all other content info (title, date, etc.)

          $date = date('Y.m.d', strtotime($content->date));
          $title = $content->title;
          $link = $url->getDocumentRedirect($content->uid);

          echo '<span>'.$date.'</span>&nbsp;&ndash;&nbsp;';
					echo '<a href="'.$link.'" title="이동">'.$title.'</a>';
					
					echo '</li>';
				}
				
				echo '</ul>';
			}
		}
		else{
			$login_url =  wp_login_url(get_permalink());
			echo '<p>먼저 <a href="'.$login_url.'" title="로그인">로그인</a> 해주세요.</p>';
		}
		
		echo $args['after_widget'];
	}
	
	public function form($instance){
		$title = !empty($instance['title'])?$instance['title']:'';
		$limit = !empty($instance['limit'])?$instance['limit']:'5';
		$exclude = !empty($instance['exclude'])?$instance['exclude']:'';
		?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title'))?>">위젯 제목</label> 
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title'))?>" name="<?php echo esc_attr($this->get_field_name('title'))?>" type="text" value="<?php echo esc_attr($title)?>">
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('limit'))?>">출력개수</label> 
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('limit'))?>" name="<?php echo esc_attr($this->get_field_name('limit'))?>" type="text" value="<?php echo intval($limit)?>">
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('exclude'))?>">제외할 게시판</label> 
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('exclude'))?>" name="<?php echo esc_attr($this->get_field_name('exclude'))?>" type="text" value="<?php echo esc_attr($exclude)?>" placeholder="예제 1,2,3">
			<span>콤마(,)로 구분해서 게시판 ID를 입력해주세요.</span>
		</p>
		<?php
	}
	
	public function update($new_instance, $old_instance){
		$instance = array();
		$instance['title'] = (!empty($new_instance['title']))?strip_tags($new_instance['title']):'';
		$instance['limit'] = (!empty($new_instance['limit']))?intval($new_instance['limit']):'';
		$instance['exclude'] = (!empty($new_instance['exclude']))?strip_tags($new_instance['exclude']):'';
		return $instance;
	}
}

add_action('widgets_init', 'kboard_my_posts_widget_init');
function kboard_my_posts_widget_init() {
  register_widget('KBoard_My_Posts_Widget');
}

/*
 *
 * KBoard Widget: My Comments
 *
 */
// TODO: Change Class to allow User to see another User's comment
// ^ by retrieving content comments from DB with uid parsed from URL
add_action('widgets_init', 'kboard_my_comments_widget_init');
function kboard_my_comments_widget_init(){
	register_widget('KBoard_My_Comments_Widget');
}

class KBoard_My_Comments_Widget extends WP_Widget {

  /*
   * Usage:
   * - add to Apperance > Widgets > Shortcode Widget > Legacy Widget (pick)
   * - include as shortcode: [widget id="kboard_my_comments_widget-2"]
   */
	public function __construct(){
    parent::__construct(
      'kboard_my_comments_widget',
      'KBoard > 내가 쓴 댓글',
      array(
				'classname' => 'kboard_my_comments_widget',
				'description' => '내가 쓴 댓글 목록을 볼 수 있습니다.',
		  )
    );
	}

	public function widget($args, $instance){
		global $wpdb;

		echo $args['before_widget'];

		if(!empty($instance['title'])){
			echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
		}

		if(!empty($instance['limit'])){
			$limit = intval($instance['limit']);
		}

		if($limit <= 0) $limit = 5;

		if(is_user_logged_in()){
			$where = array();

			// 사용자 ID
			$user_id = get_current_user_id();
			$where[] = "`user_uid`='{$user_id}'";

			$where = implode(' AND ', $where);
			$results = $wpdb->get_results("SELECT `uid` FROM `{$wpdb->prefix}kboard_comments` WHERE {$where} ORDER BY `created` DESC LIMIT {$limit}");

			if(!$results){
				echo '<p>내 댓글이 없습니다.</p>';
			}
			else{
				$url = new KBUrl();

				echo '<ul>';

				foreach($results as $row){
					echo '<li>';

					$comment = new KBComment();
          $comment->initWithUID($row->uid); // get other comment data (content, date, etc.)

          $commentText= $comment->content;
          $link = $url->getDocumentRedirect($comment->content_uid);
          $date = date('Y.m.d H:i', strtotime($comment->created));

          echo '<span>'.$date.'</span>&nbsp;&ndash;&nbsp;';
					echo '<a href="'.$link.' title="이동">'.$commentText.'</a>';

					echo '</li>';
				}

				echo '</ul>';
			}
		}
		else{
			$login_url =  wp_login_url(get_permalink());
			echo '<p>먼저 <a href="'.$login_url.'" title="로그인">로그인</a> 해주세요.</p>';
		}

		echo $args['after_widget'];
	}

	public function form($instance){
		$title = !empty($instance['title'])?$instance['title']:'';
		$limit = !empty($instance['limit'])?$instance['limit']:'5';
		?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title'))?>">제목</label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title'))?>" name="<?php echo esc_attr($this->get_field_name('title'))?>" type="text" value="<?php echo esc_attr($title)?>">
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('limit'))?>">출력개수</label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('limit'))?>" name="<?php echo esc_attr($this->get_field_name('limit'))?>" type="text" value="<?php echo intval($limit)?>">
		</p>
		<?php
	}

	public function update($new_instance, $old_instance){
		$instance = array();
		$instance['title'] = (!empty($new_instance['title']))?strip_tags($new_instance['title']):'';
		$instance['limit'] = (!empty($new_instance['limit']))?intval($new_instance['limit']):'';
		return $instance;
	}
}


/*
 *
 * KBoard: Show My Post
 *
 */
/*add_filter('kboard_widget_tab_list', 'my_kboard_widget_tab_list', 10, 1);
function my_kboard_widget_tab_list($tab_list){
	$tab_list[] = '게시글1';
	$tab_list[] = '게시글2';
	
	return $tab_list;
}

add_filter('kboard_widget_list_where', 'my_kboard_widget_list_where', 10, 5);
function my_kboard_widget_list_where($where, $value, $limit, $exclude, $with_notice){
	if($value == '게시글1'){
		$board_id = '12';
		$where .= " AND `board_id`='{$board_id}'";
	}
	
	if($value == '게시글2'){
		$board_id = '2';
		$where .= " AND `board_id`='{$board_id}'";
	}
	return $where;
}*/



/*
 *
 * Karuna stuff
 *
 */
if ( ! function_exists( 'karuna_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the aftercomponentsetup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function karuna_setup() {
	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on components, use a find and replace
	 * to change 'karuna' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'karuna', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	// Add support for responsive embeds.
	add_theme_support( 'responsive-embeds' );

	/**
	 * Gutenberg wide and full images support
	 */
	add_theme_support( 'align-wide' );

	// Add custom colors to Gutenberg
	add_theme_support(
		'editor-color-palette', array(
			array(
				'name'  => esc_html__( 'Black', 'karuna' ),
				'slug' => 'black',
				'color' => '#333333',
			),
			array(
				'name'  => esc_html__( 'Medium Gray', 'karuna' ),
				'slug' => 'medium-gray',
				'color' => '#999999',
			),
			array(
				'name'  => esc_html__( 'Light Gray', 'karuna' ),
				'slug' => 'light-gray',
				'color' => '#dddddd',
			),
			array(
				'name'  => esc_html__( 'White', 'karuna' ),
				'slug' => 'white',
				'color' => '#ffffff',
			),
			array(
				'name'  => esc_html__( 'Purple', 'karuna' ),
				'slug' => 'purple',
				'color' => '#6636cc',
			),
			array(
				'name'  => esc_html__( 'Dark Purple', 'karuna' ),
				'slug' => 'dark-purple',
				'color' => '#471e9e',
			),
			array(
				'name'  => esc_html__( 'Green', 'karuna' ),
				'slug' => 'green',
				'color' => '#85cc36',
			),
			array(
				'name'  => esc_html__( 'Dark Green', 'karuna' ),
				'slug' => 'dark-green',
				'color' => '#609d1b',
			),
		)
	);

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );

	add_image_size( 'karuna-featured-image', 685, 9999 );
	add_image_size( 'karuna-hero', 2000, 9999 );
	add_image_size( 'karuna-grid', 342, 228, true );
	add_image_size( 'karuna-thumbnail-avatar', 100, 100, true );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'menu-1' => esc_html__( 'Header', 'karuna' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	// Add theme support for custom logos
	add_theme_support( 'custom-logo',
		array(
			'width'       => 1000,
			'height'      => 200,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support( 'custom-background', apply_filters( 'karuna_custom_background_args', array(
		'default-color' => 'ffffff',
	) ) );
}
endif;
add_action( 'after_setup_theme', 'karuna_setup' );

/**
 * Return early if Custom Logos are not available.
 */
function karuna_the_custom_logo() {
	if ( ! function_exists( 'the_custom_logo' ) ) {
		return;
	} else {
		the_custom_logo();
	}
}

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function karuna_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'karuna_content_width', 685 );
}
add_action( 'after_setup_theme', 'karuna_content_width', 0 );


/*
 * Adjust $content_width for full-width and front-page.php templates
 */

if ( ! function_exists( 'karuna_adjusted_content_width' ) ) :

function karuna_adjusted_content_width() {
	global $content_width;

	if ( is_page_template( 'templates/full-width-page.php' ) || is_page_template( 'front-page.php' ) || is_active_sidebar( 'sidebar-5' ) || is_active_sidebar( 'sidebar-4' ) ) {
		$content_width = 1040; //pixels
	}
}
add_action( 'template_redirect', 'karuna_adjusted_content_width' );

endif; // if ! function_exists( 'karuna_adjusted_content_width' )

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function karuna_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'karuna' ),
		'id'            => 'sidebar-1',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Full-Width Header', 'karuna' ),
		'id'            => 'sidebar-4',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Full-Width Footer', 'karuna' ),
		'id'            => 'sidebar-5',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer 1', 'karuna' ),
		'id'            => 'sidebar-2',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer 2', 'karuna' ),
		'id'            => 'sidebar-3',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer 3', 'karuna' ),
		'id'            => 'sidebar-6',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer 4', 'karuna' ),
		'id'            => 'sidebar-7',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'karuna_widgets_init' );

/**
 * Register Google Fonts
 */
function karuna_fonts_url() {
    $fonts_url = '';

    /* Translators: If there are characters in your language that are not
	 * supported by Karla, translate this to 'off'. Do not translate
	 * into your own language.
	 */
	$karla = esc_html_x( 'on', 'Karla font: on or off', 'karuna' );

	if ( 'off' !== $karla ) {
		$font_families = array();
		$font_families[] = 'Karla:400,400italic,700,700italic';

		$query_args = array(
			'family' => urlencode( implode( '|', $font_families ) ),
			'subset' => urlencode( 'latin,latin-ext' ),
		);

		$fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
	}

	return $fonts_url;

}

/**
 * Enqueue scripts and styles.
 */
function karuna_scripts() {
	wp_enqueue_style( 'karuna-style', get_stylesheet_uri() );

	// Gutenberg styles
	wp_enqueue_style( 'karuna-blocks', get_template_directory_uri() . '/blocks.css' );

	wp_enqueue_style( 'karuna-fonts', karuna_fonts_url(), array(), null );

	wp_enqueue_style( 'genericons', get_template_directory_uri() . '/assets/fonts/genericons/genericons.css', array(), '3.4.1' );

	wp_enqueue_script( 'karuna-navigation', get_template_directory_uri() . '/assets/js/navigation.js', array(), '20151215', true );

	wp_enqueue_script( 'karuna-skip-link-focus-fix', get_template_directory_uri() . '/assets/js/skip-link-focus-fix.js', array(), '20151215', true );

	wp_enqueue_script( 'karuna-functions', get_template_directory_uri() . '/assets/js/functions.js', array( 'jquery' ), '20160531', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'karuna_scripts' );

/**
 * Gutenberg Editor Styles
 */
function karuna_editor_styles() {
	wp_enqueue_style( 'karuna-editor-block-style', get_template_directory_uri() . '/editor-blocks.css' );
	wp_enqueue_style( 'karuna-fonts', karuna_fonts_url(), array(), null );
}
add_action( 'enqueue_block_editor_assets', 'karuna_editor_styles' );

/**
 * Replaces "[...]" (appended to automatically generated excerpts) with ... and a 'Continue reading' link.
 * @return string 'Continue reading' link prepended with an ellipsis.
 */
if ( ! function_exists( 'karuna_excerpt_more' ) ) :
	function karuna_excerpt_more( $more ) {
		$link = sprintf( '<a href="%1$s" class="more-link">%2$s</a>',
			esc_url( get_permalink( get_the_ID() ) ),
			/* translators: %s: Name of current post */
			sprintf( esc_html__( 'Continue reading %s', 'karuna' ), '<span class="screen-reader-text">' . get_the_title( get_the_ID() ) . '</span>' )
			);
		return ' &hellip; ' . $link;
	}
	add_filter( 'excerpt_more', 'karuna_excerpt_more' );
endif;

/**
 * Custom header support
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';

/**
 * Load WooCommerce compatibility file.
 */
if ( class_exists( 'WooCommerce' ) ) {
	require get_template_directory() . '/inc/woocommerce.php';
}


// updater for WordPress.com themes
if ( is_admin() )
	include dirname( __FILE__ ) . '/inc/updater.php';

/**
 * 
 * Get total stats (visitors for now) from Jetpack's internal API
 * - written by Jongwon Park
 * - use shortcode with param []
 * 
 */

function jp_get_total_visitors($atts = array()) {
	$stats = ['total_views' => 0, 'total_visitors' => 0];
	$total_visitors = 0;
	
	$jpgtv_atts = shortcode_atts(array(
		'period' => 'day' // default value is 'day'
    ), $atts);
	$period = $jpgtv_atts['period'];
	
	if (!in_array($period, ['day', 'week', 'month', 'lifetime'])) {
		$period = 'day'; // value falls back to 'day' if not valid
	}
	
	// 1) today stats
	// 2) 1-week stats (rolling 7 days)
	// 3) 1-month stats (rolling 30 days)
	// 4) life-time
	
	if ($period == 'day' || $period == 'lifetime') {
		$raw_stats = stats_get_from_restapi(array('fields' => 'stats'));
		
		$total_views_arr = ['day' => $raw_stats->stats->views_today, 'lifetime' => $raw_stats->stats->views];
		$total_visitors_arr = ['day' => $raw_stats->stats->visitors_today, 'lifetime' => $raw_stats->stats->visitors];

		$total_views = $total_views_arr[$period];
		$total_visitors = $total_visitors_arr[$period];
	}
	else if ($period == 'week') $raw_stats = stats_get_from_restapi(array('fields' => 'views,visitors'), 'visits?unit=day&quantity=7');
	else if ($period == 'month') $raw_stats = stats_get_from_restapi(array('fields' => 'views,visitors'), 'visits?unit=day&quantity=30');
	
	// other units available: 'week', 'month' (NOT rolling, e.g. Week 51 or Month 11)

	// available fields: [period, views, visitors, likes, reblogs, comments, posts]
	// for some reason, stats_get_form_restapi args doesn't filter fields.
	
	if ($period == 'week' || $period == 'month') {
		$stats_data = $raw_stats->data;
		foreach ($stats_data as $value_arr) {
			$total_visitors += $value_arr[2];
		}
	}

	return $total_visitors;
}

// usage [jp_get_total_visitors period="week"]
function jp_get_total_shortcodes_init() {
	add_shortcode('jp_get_total_visitors', 'jp_get_total_visitors');
}

add_action( 'init', 'jp_get_total_shortcodes_init' );
