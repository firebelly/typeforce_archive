<?php

namespace Firebelly\Utils;

/**
 * Bump up # search results
 */
// function search_queries( $query ) {
//   if ( !is_admin() && is_search() ) {
//     $query->set( 'posts_per_page', 40 );
//   }
//   return $query;
// }
// add_filter( 'pre_get_posts', __NAMESPACE__ . '\\search_queries' );

/**
 * Remove pages/posts from search
 */
function remove_pages_and_posts_from_search() {
    global $wp_post_types;
    $wp_post_types['page']->exclude_from_search = true;
    $wp_post_types['post']->exclude_from_search = true;
}
add_action('init', __NAMESPACE__ . '\\remove_pages_and_posts_from_search');

/**
 * Custom li'l excerpt function
 */
function get_excerpt( $post, $length=15, $force_content=false ) {
  $excerpt = trim($post->post_excerpt);
  if (!$excerpt || $force_content) {
    $excerpt = $post->post_content;
    $excerpt = strip_shortcodes( $excerpt );
    $excerpt = apply_filters( 'the_content', $excerpt );
    $excerpt = str_replace( ']]>', ']]&gt;', $excerpt );
    $excerpt_length = apply_filters( 'excerpt_length', $length );
    $excerpt = wp_trim_words( $excerpt, $excerpt_length );
  }
  return $excerpt;
}

/**
 * Get top ancestor for post
 */
function get_top_ancestor($post){
  if (!$post) return;
  $ancestors = $post->ancestors;
  if ($ancestors) {
    return end($ancestors);
  } else {
    return $post->ID;
  }
}

/**
 * Get first term for post
 */
function get_first_term($post, $taxonomy='category') {
  $return = false;
  if ($terms = get_the_terms($post->ID, $taxonomy))
    $return = array_pop($terms);
  return $return;
}

/**
 * Get page content from slug
 */
function get_page_content($slug) {
  $return = false;
  if ($page = get_page_by_path($slug))
    $return = apply_filters('the_content', $page->post_content);
  return $return;
}

/**
 * Get category for post
 */
function get_category($post) {
  if ($category = get_the_category($post)) {
    return $category[0];
  } else return false;
}

/**
 * Get num_pages for category given slug + per_page
 */
function get_total_pages($post_type,$per_page) {
  $count_posts = wp_count_posts($post_type);
  $total_published = $count_posts->publish;
  $num_pages = ceil($total_published/ $per_page);
  return $num_pages;
}

/**
 * Get Page Blocks
 */
function get_page_blocks($post) {
  $output = '';
  $page_blocks = get_post_meta($post->ID, '_cmb2_page_blocks', true);
  if ($page_blocks) {
    foreach ($page_blocks as $page_block) {
      if (empty($page_block['hide_block'])) {
        $block_title = $block_body = '';
        if (!empty($page_block['title']))
          $block_title = $page_block['title'];
        if (!empty($page_block['body'])) {
          $block_body = apply_filters('the_content', $page_block['body']);
          $output .= '<div class="page-block">';
          if ($block_title) {
            $output .= '<h2 class="flag">' . $block_title . '</h2>';
          }
          $output .= '<div class="user-content">' . $block_body . '</div>';
          $output .= '</div>';
        }
      }
    }
  }
  return $output;
}

/**
 * Extend WordPress search to include custom fields
 * http://adambalee.com
 */

/**
 * Join posts and postmeta tables
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_join
 */
function search_join( $join ) {
    global $wpdb;

    if ( is_search() ) {    
        $join .=' LEFT JOIN '.$wpdb->postmeta. ' ON '. $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
    }
    
    return $join;
}
add_filter('posts_join', __NAMESPACE__ . '\\search_join' );

/**
 * Modify the search query with posts_where
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_where
 */
function search_where( $where ) {
    global $pagenow, $wpdb;
   
    if ( is_search() ) {
        $where = preg_replace(
            "/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
            "(".$wpdb->posts.".post_title LIKE $1) OR (".$wpdb->postmeta.".meta_value LIKE $1)", $where );
    }

    return $where;
}
add_filter( 'posts_where', __NAMESPACE__ . '\\search_where' );

/**
 * Prevent duplicates
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_distinct
 */
function search_distinct( $where ) {
    global $wpdb;

    if ( is_search() ) {
        return "DISTINCT";
    }

    return $where;
}
add_filter( 'posts_distinct', __NAMESPACE__ . '\\search_distinct' );