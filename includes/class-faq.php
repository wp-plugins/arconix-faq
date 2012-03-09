<?php
/**
 * This file contains the post-type class.
 *
 * This class handles the creation of the "faq" post type, and creates a
 * UI to display the staff-specific data on the admin screens.
 */
class Arconix_FAQ {

    /**
     * This var is used in the shortcode to flag the loading of javascript
     * @var type boolean
     */
    static $load_js;


    /**
     * Construct Method.
     */
    function __construct() {

        // Post Type Creation
        add_action( 'init', array( $this, 'create_post_type' ) );
        add_filter( 'post_updated_messages', array( $this, 'updated_messages' ) );

        // Modify the Post Type Admin screen
        add_action( 'admin_head', array( $this, 'post_type_admin_image' ) );
        add_filter( 'manage_edit-faq_columns', array( $this, 'columns_filter' ) );
        add_action( 'manage_posts_custom_column', array( $this, 'column_data' ) );

        // Register and add the javascript and CSS
	add_action( 'init', array( $this , 'register_script' ) );
	add_action( 'wp_footer', array( $this , 'print_script' ) );
	add_action( 'wp_enqueue_scripts', array( $this , 'enqueue_css' ) );

       // Create the shortcode
        add_shortcode( 'faq', array( $this, 'faq_shortcode' ) );

        // Modify Dashboard widgets
        add_action( 'right_now_content_table_end', array( $this, 'right_now' ) );

    }

    /**
     * Create FAQ Post Type
     */
    function create_post_type() {

        $args = apply_filters( 'arconix_faq_post_type_args',
            array(
                'labels' => array(
                    'name' => __( 'FAQ', 'acf' ),
                    'singular_name' => __( 'FAQ', 'acf' ),
                    'add_new' => __( 'Add New', 'acf' ),
                    'add_new_item' => __( 'Add New Question', 'acf' ),
                    'edit' => __( 'Edit', 'acf' ),
                    'edit_item' => __( 'Edit Question', 'acf' ),
                    'new_item' => __( 'New Question', 'acf' ),
                    'view' => __( 'View FAQ', 'acf' ),
                    'view_item' => __( 'View Question', 'acf' ),
                    'search_items' => __( 'Search FAQ', 'acf' ),
                    'not_found' => __( 'No FAQs found', 'acf' ),
                    'not_found_in_trash' => __( 'No FAQs found in Trash', 'acf' )
                ),
                    'public' => true,
                    'query_var' => true,
                    'menu_position' => 20,
                    'menu_icon' => ACF_URL . 'images/faq-16x16.png',
                    'has_archive' => false,
                    'supports' => array( 'title', 'editor', 'revisions' ),
                    'rewrite' => array( 'slug' => 'faqs', 'with_front' => false )
            )
        );

        register_post_type( 'faq', $args );

    }

    /** Modify updated messages on post type save */
    function updated_messages( $messages ) {
        global $post, $post_ID;

        $messages['faq'] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => sprintf( __('FAQ updated. <a href="%s">View staff</a>'), esc_url( get_permalink($post_ID) ) ),
            2 => __('Custom field updated.'),
            3 => __('Custom field deleted.'),
            4 => __('FAQ updated.'),
            /* translators: %s: date and time of the revision */
            5 => isset($_GET['revision']) ? sprintf( __('FAQ restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6 => sprintf( __('FAQ published. <a href="%s">View FAQ</a>'), esc_url( get_permalink($post_ID) ) ),
            7 => __('FAQ saved.'),
            8 => sprintf( __('FAQ submitted. <a target="_blank" href="%s">Preview FAQ</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
            9 => sprintf( __('FAQ scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview FAQ</a>'),
              // translators: Publish box date format, see http://php.net/date
              date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
            10 => sprintf( __('FAQ draft updated. <a target="_blank" href="%s">Preview FAQ</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
      );

        return $messages;
    }


    /**
     * Add the post-type icon to the admin screen
     */
    function post_type_admin_image() {
        printf( '<style type="text/css" media="screen">.icon32-posts-faq { background: transparent url(%s) no-repeat !important; }</style>', ACF_URL . 'images/faq-32x32.png' );
    }
    
    
    /**
     * Choose the specific columns we want to display
     *
     * @param array $columns
     * @return string
     * @since 0.9
     */
    function columns_filter ( $columns ) {

        $columns = array (
            "cb" => "<input type=\"checkbox\" />",
            "title" => "FAQ Title",
            "faq_content" => "Details",
            "date" => "Date"
        );

        return $columns;
    }


    /**
     * Filter the data that shows up in the columns we defined above
     *
     * @global type $post
     * @param type $column
     * @since 0.9
     */
    function column_data( $column ) {

        global $post;

        switch( $column ) {
            case "faq_content":
                the_excerpt();
                break;
            default:
                break;

        }
    }
    
    
    /**
     *  Register the javascript
     */
    function register_script() {
        
        if( file_exists( get_stylesheet_directory() . "/arconix-faq.js" ) ) {
	    wp_register_script( 'arconix-faq-js', get_stylesheet_directory_uri() . '/arconix-faq.js', array( 'jquery' ), ACF_VERSION, true );
	}
	elseif( file_exists( get_template_directory() . "/arconix-faq.js" ) ) {
	    wp_register_script( 'arconix-faq-js', get_template_directory_uri() . '/arconix-faq.js', array( 'jquery' ), ACF_VERSION, true );
	}
	else {
            wp_register_script( 'arconix-faq-js', ACF_URL . 'includes/faq.js', array( 'jquery' ), ACF_VERSION, true );
	}
    }
    
    
    /**
     * Load the javascript on a page where the FAQ shortcode is used
     * @return type 
     */
    function print_script() {
        
        if( ! self::$load_js ) 
            return;

	wp_print_scripts( 'arconix-faq-js' );
       
    }
    
    
    /**
     * Load the plugin's stylesheet 
     */
    function enqueue_css() {
        
        if( file_exists( get_stylesheet_directory() . "/arconix-faq.css" ) ) {
	    wp_enqueue_style( 'arconix-faq', get_stylesheet_directory_uri() . '/arconix-faq.css', array(), ACF_VERSION );
	}
	elseif( file_exists( get_template_directory() . "/arconix-faq.css" ) ) {
	    wp_enqueue_style( 'arconix-faq', get_template_directory_uri() . '/arconix-faq.css', array(), ACF_VERSION );
	}
	else {
            wp_enqueue_style( 'arconix-faq', ACF_URL . 'includes/faq.css', array(), ACF_VERSION );
	}
        
    }


    /**
     * Display FAQs
     *
     * @param type $atts
     * @param type $content
     */
    function faq_shortcode( $atts, $content = null ) {
        
        // Set the js flag
        self::$load_js = true;

	$defaults = apply_filters( 'arconix_faq_shortcode_query_args',
	    array(
		'post_type' => 'faq',
		'order' => 'ASC',
		'orderby' => 'title'
	    )
	);

	extract( shortcode_atts( $defaults, $atts ) );

        /** create a new query bsaed on our own arguments */
	$faq_query = new WP_Query( array( 'post_type' => $post_type, 'order' => $order, 'orderby' => $orderby ) );

        if( $faq_query->have_posts() ) : while ( $faq_query->have_posts() ) : $faq_query->the_post();

            echo '<div id="post-' . get_the_ID() .'" class="arconix-faq-wrap">';
            echo '<div class="arconix-faq-title">' . get_the_title() . '</div>';
            echo '<div class="arconix-faq-content">';
            the_content();
            echo '</div></div>';

        endwhile; endif;
    }


    /**
     * Add the Post type to the "Right Now" Dashboard Widget
     *
     * @link http://bajada.net/2010/06/08/how-to-add-custom-post-types-and-taxonomies-to-the-wordpress-right-now-dashboard-widget
     */
    function right_now() {
        include_once( dirname( __FILE__ ) . '/views/right-now.php' );
    }


}
?>
