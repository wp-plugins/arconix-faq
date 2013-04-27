<?php

/**
 * Change the Post Updated messages
 *
 * @global type $post
 * @global type $post_ID
 * @param type $messages
 * @return type $messages
 * @since 0.9
 */
function faq_updated_messages( $messages ) {
    global $post, $post_ID;

    $messages['faq'] = array(
        0 => '', // Unused. Messages start at index 1.
        1 => sprintf( __( 'FAQ updated. <a href="%s">View staff</a>' ), esc_url( get_permalink( $post_ID ) ) ),
        2 => __( 'Custom field updated.' ),
        3 => __( 'Custom field deleted.' ),
        4 => __( 'FAQ updated.' ),
        /* translators: %s: date and time of the revision */
        5 => isset( $_GET['revision'] ) ? sprintf( __( 'FAQ restored to revision from %s' ), wp_post_revision_title( ( int ) $_GET['revision'], false ) ) : false,
        6 => sprintf( __( 'FAQ published. <a href="%s">View FAQ</a>' ), esc_url( get_permalink( $post_ID ) ) ),
        7 => __( 'FAQ saved.' ),
        8 => sprintf( __( 'FAQ submitted. <a target="_blank" href="%s">Preview FAQ</a>' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
        9 => sprintf( __( 'FAQ scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview FAQ</a>' ),
                // translators: Publish box date format, see http://php.net/date
                date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
        10 => sprintf( __( 'FAQ draft updated. <a target="_blank" href="%s">Preview FAQ</a>' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
    );

    return $messages;
}

/**
 * Add the post-type icon to the admin screen
 *
 * @since 0.9
 */
function faq_post_type_admin_image() {
    printf( '<style type="text/css" media="screen">.icon32-posts-faq { background: transparent url(%s) no-repeat !important; }</style>', ACF_URL . 'images/faq-32x32.png' );
}

/**
 * Choose the specific columns we want to display
 *
 * @param array $columns
 * @return string $columns
 * @since 0.9
 * @version 1.2
 */
function faq_columns_filter( $columns ) {
    $columns = array(
        "cb" => "<input type=\"checkbox\" />",
        "title" => __( 'FAQ Title', 'acf' ),
        "faq_content" => __( 'Answer', 'acf' ),
        'faq_groups' => __( 'Group', 'acf' ),
        "date" => __( 'Date', 'acf' )
    );

    return $columns;
}

/**
 * Filter the data that shows up in the columns we defined above
 *
 * @global type $post
 * @param type $column
 * @since 0.9
 * @version 1.1
 */
function faq_column_data( $column ) {
    global $post;

    switch( $column ) {
        case "faq_content":
            the_excerpt();
            break;
        case "faq_groups":
            echo get_the_term_list( $post->ID, 'group', '', ', ', '' );
            break;
        default:
            break;
    }
}

/**
 * Add the Post type to the "Right Now" Dashboard Widget
 *
 * @link http://bajada.net/2010/06/08/how-to-add-custom-post-types-and-taxonomies-to-the-wordpress-right-now-dashboard-widget
 * @version 1.0
 */
function faq_right_now() {
    // Define the post type text here, allowing us to quickly re-use this code in other projects
    $ac_pt = 'faq'; // must be the registered post type
    $ac_pt_p = 'FAQs';
    $ac_pt_s = 'FAQ';

    // No need to modify these 2
    $ac_pt_pp = $ac_pt_p . ' Pending';
    $ac_pt_sp = $ac_pt_s . ' Pending';


    $args = array(
        'public' => true,
        '_builtin' => false
    );
    $output = 'object';
    $operator = 'and';

    $num_posts = wp_count_posts( $ac_pt );
    $num = number_format_i18n( $num_posts->publish );
    $text = _n( $ac_pt_s, $ac_pt_p, intval( $num_posts->publish ) );

    if( current_user_can( 'edit_posts' ) ) {

        $num = "<a href='edit.php?post_type=$ac_pt'>$num</a>";
        $text = "<a href='edit.php?post_type=$ac_pt'>$text</a>";
    }

    echo '<td class="first b b-' . $ac_pt . '">' . $num . '</td>';
    echo '<td class="t ' . $ac_pt . '">' . $text . '</td>';
    echo '</tr>';

    if( $num_posts->pending > 0 ) {
        $num = number_format_i18n( $num_posts->pending );
        $text = _n( $ac_pt_sp, $ac_pt_pp, intval( $num_posts->pending ) );

        if( current_user_can( 'edit_posts' ) ) {

            $num = "<a href='edit.php?post_status=pending&post_type='$ac_pt'>$num</a>";
            $text = "<a href='edit.php?post_status=pending&post_type=$ac_pt'>$text</a>";
        }

        echo '<td class="first b b-' . $ac_pt . '">' . $num . '</td>';
        echo '<td class="t ' . $ac_pt . '">' . $text . '</td>';
        echo '</tr>';
    }

    $faq_args = array( 'name' => 'group' );

    $taxonomies = get_taxonomies( $faq_args, $output, $operator );

    foreach( $taxonomies as $taxonomy ) {
        $num_terms = wp_count_terms( $taxonomy->name );
        $num = number_format_i18n( $num_terms );
        $text = _n( $taxonomy->labels->singular_name, $taxonomy->labels->name, intval( $num_terms ) );
        if( current_user_can( 'manage_categories' ) ) {

            $num = "<a href='edit-tags.php?taxonomy=$taxonomy->name'>$num</a>";
            $text = "<a href='edit-tags.php?taxonomy=$taxonomy->name'>$text</a>";
        }
        echo '<tr><td class="first b b-' . $taxonomy->name . '">' . $num . '</td>';
        echo '<td class="t ' . $taxonomy->name . '">' . $text . '</td></tr>';
    }
}

/**
 * Adds a widget to the dashboard.
 *
 * @since 1.0.3
 * @version 1.1
 */
function faq_register_dashboard_widget() {
    wp_add_dashboard_widget( 'ac-faq', 'Arconix FAQ', 'dashboard_widget_output' );
}

/**
 * Add a widget to the dashboard
 *
 * @since 1.0
 * @version 1.1
 */
function dashboard_widget_output() {

    echo '<div class="rss-widget">';

    wp_widget_rss_output( array(
        'url' => 'http://arconixpc.com/tag/arconix-faq/feed', // feed url
        'title' => 'Arconix FAQ', // feed title
        'items' => 3, // how many posts to show
        'show_summary' => 1, // display excerpt
        'show_author' => 0, // display author
        'show_date' => 1 // display post date
    ) );

    echo '<div class="acf-widget-bottom"><ul>';
    ?>
        <li><a href="http://arcnx.co/afwiki"><img src="<?php echo ACF_IMAGES_URL . 'page-16x16.png' ?>">Wiki Page</a></li>
        <li><a href="http://arcnx.co/afhelp"><img src="<?php echo ACF_IMAGES_URL . 'help-16x16.png' ?>">Support Forum</a></li>
        <li><a href="http://arcnx.co/aftrello"><img src="<?php echo ACF_IMAGES_URL . 'trello-16x16.png' ?>">Dev Board</a></li>
        <li><a href="http://arcnx.co/afsource"><img src="<?php echo ACF_IMAGES_URL . 'github-16x16.png'; ?>">Source Code</a></li>
    <?php
    echo '</ul></div></div>';

    // handle the styling
    echo '<style type="text/css">
            #ac-faq .rsssummary { display: block; }
            #ac-faq .acf-widget-bottom { border-top: 1px solid #ddd; padding-top: 10px; text-align: center; }
            #ac-faq .acf-widget-bottom ul { list-style: none; }
            #ac-faq .acf-widget-bottom ul li { display: inline; padding-right: 9%; }
            #ac-faq .acf-widget-bottom img { padding-right: 3px; vertical-align: top; }
        </style>';
}
