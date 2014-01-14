<?php
/**
 * Custom functions that act independently of the theme templates
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package Responsive
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function responsive_body_classes( $classes ) {
	// Adds a class of group-blog to blogs with more than 1 published author.
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}
	// Get Responsive theme option.
	global $responsive_options;
	if( $responsive_options['front_page'] == 1 && is_front_page() ) {
		$classes[] = 'front-page';
	}
	if( $responsive_options['compatibility'] == 0 ) {
		$classes[] = 'bootstrap';
	}

	return $classes;
}
add_filter( 'body_class', 'responsive_body_classes' );

/**
 * Filters wp_title to print a neat <title> tag based on what is being viewed.
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 * @return string The filtered title.
 */
function responsive_wp_title( $title, $sep ) {
	global $page, $paged;

	if ( is_feed() ) {
		return $title;
	}

	// Add the blog name
	$title .= get_bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) ) {
		$title .= " $sep $site_description";
	}

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 ) {
		$title .= " $sep " . sprintf( __( 'Page %s', 'responsive' ), max( $paged, $page ) );
	}

	return $title;
}
add_filter( 'wp_title', 'responsive_wp_title', 10, 2 );

/**
 * wp_title() Filter for better SEO.
 *
 * Adopted from Twenty Twelve
 * @see http://codex.wordpress.org/Plugin_API/Filter_Reference/wp_title
 *
 */
if( !function_exists( 'responsive_wp_title' ) && !defined( 'AIOSEOP_VERSION' ) ) {

	function responsive_wp_title( $title, $sep ) {
		global $page, $paged;

		if( is_feed() ) {
			return $title;
		}

		// Add the site name.
		$title .= get_bloginfo( 'name' );

		// Add the site description for the home/front page.
		$site_description = get_bloginfo( 'description', 'display' );
		if( $site_description && ( is_home() || is_front_page() ) ) {
			$title .= " $sep $site_description";
		}

		// Add a page number if necessary.
		if( $paged >= 2 || $page >= 2 ) {
			$title .= " $sep " . sprintf( __( 'Page %s', 'responsive' ), max( $paged, $page ) );
		}

		return $title;
	}

}
add_filter( 'wp_title', 'responsive_wp_title', 10, 2 );

/**
 * Sets the authordata global when viewing an author archive.
 *
 * This provides backwards compatibility with
 * http://core.trac.wordpress.org/changeset/25574
 *
 * It removes the need to call the_post() and rewind_posts() in an author
 * template to print information about the author.
 *
 * @global WP_Query $wp_query WordPress Query object.
 * @return void
 */
function responsive_setup_author() {
		global $wp_query;

		if ( $wp_query->is_author() && isset( $wp_query->post ) ) {
				$GLOBALS['authordata'] = get_userdata( $wp_query->post->post_author );
		}
}
add_action( 'wp', 'responsive_setup_author' );

/**
 * Filter 'get_comments_number'
 *
 * Filter 'get_comments_number' to display correct
 * number of comments (count only comments, not
 * trackbacks/pingbacks)
 *
 * Chip Bennett Contribution
 */
function responsive_comment_count( $count ) {
	if( !is_admin() ) {
		global $id;
		$comments         = get_comments( 'status=approve&post_id=' . $id );
		$comments_by_type = separate_comments( $comments );

		return count( $comments_by_type['comment'] );
	}
	else {
		return $count;
	}
}

add_filter( 'get_comments_number', 'responsive_comment_count', 0 );

/**
 * A comment reply.
 */
function responsive_enqueue_comment_reply() {
	if( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'responsive_enqueue_comment_reply' );

/**
 * wp_list_comments() Pings Callback
 *
 * wp_list_comments() Callback function for
 * Pings (Trackbacks/Pingbacks)
 */
function responsive_comment_list_pings( $comment ) {
	$GLOBALS['comment'] = $comment;
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>"><?php echo comment_author_link(); ?></li>
<?php
}

/**
 * Sets the post excerpt length to 40 words.
 * Adopted from Coraline
 */
function responsive_excerpt_length( $length ) {
	return 40;
}
add_filter( 'excerpt_length', 'responsive_excerpt_length' );

/**
 * Returns a "Read more" link for excerpts
 */
function responsive_read_more() {
	return '<div class="read-more"><a href="' . get_permalink() . '">' . __( 'Read more &#8250;', 'responsive' ) . '</a></div><!-- end of .read-more -->';
}

/**
 * Replaces "[...]" (appended to automatically generated excerpts) with an ellipsis and responsive_read_more_link().
 */
function responsive_auto_excerpt_more( $more ) {
	return '<span class="ellipsis">&hellip;</span>' . responsive_read_more();
}
add_filter( 'excerpt_more', 'responsive_auto_excerpt_more' );

/**
 * Adds a pretty "Read more" link to custom post excerpts.
 */
function responsive_custom_excerpt_more( $output ) {
	if( has_excerpt() && !is_attachment() ) {
		$output .= responsive_read_more();
	}

	return $output;
}
add_filter( 'get_the_excerpt', 'responsive_custom_excerpt_more' );

/**
 * This function removes inline styles set by WordPress gallery.
 */
function responsive_remove_gallery_css( $css ) {
	return preg_replace( "#<style type='text/css'>(.*?)</style>#s", '', $css );
}
add_filter( 'gallery_style', 'responsive_remove_gallery_css' );

/**
 * This function removes default styles set by WordPress recent comments widget.
 */
function responsive_remove_recent_comments_style() {
	global $wp_widget_factory;
	if( isset( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'] ) ) {
		remove_action( 'wp_head', array( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style' ) );
	}
}
add_action( 'widgets_init', 'responsive_remove_recent_comments_style' );

/**
 * This function removes WordPress generated category and tag atributes.
 * For W3C validation purposes only.
 *
 */
function responsive_category_rel_removal( $output ) {
	$output = str_replace( ' rel="category tag"', '', $output );

	return $output;
}
add_filter( 'wp_list_categories', 'responsive_category_rel_removal' );
add_filter( 'the_category', 'responsive_category_rel_removal' );

/**
 * Front Page function starts here. The Front page overides WP's show_on_front option. So when show_on_front option changes it sets the themes front_page to 0 therefore displaying the new option
 */
function responsive_front_page_override( $new, $orig ) {
	global $responsive_options;

	if( $orig !== $new ) {
		$responsive_options['front_page'] = 0;

		update_option( 'responsive_theme_options', $responsive_options );
	}

	return $new;
}

add_filter( 'pre_update_option_show_on_front', 'responsive_front_page_override', 10, 2 );


/**
 * Helps file locations in child themes. If the file is not being overwritten by the child theme then
 * return the parent theme location of the file. Great for images.
 *
 * @param $dir string directory
 *
 * @return string complete uri
 */
function responsive_child_uri( $dir ) {
	if( is_child_theme() ) {
		$directory = get_stylesheet_directory() . $dir;
		$test      = is_file( $directory );
		if( is_file( $directory ) ) {
			$file = get_stylesheet_directory_uri() . $dir;
		} else {
			$file = get_template_directory_uri() . $dir;
		}
	}
	else {
		$file = get_template_directory_uri() . $dir;
	}

	return $file;
}
