<?php 
/**
 * Exhibit Post Type
 */

 namespace Firebelly\PostTypes\Exhibit;
 use Firebelly\Utils;

  // Custom image size for post type?
  // add_image_size( 'exhibit-thumb', 800, 800, true );

 /**
  * Register Custom Post Type
  */
function post_type() {

  $labels = array(
    'name'                => 'Exhibits',
    'singular_name'       => 'Exhibit',
    'menu_name'           => 'Exhibits',
    'parent_item_colon'   => '',
    'all_items'           => 'All Exhibits',
    'view_item'           => 'View Exhibit',
    'add_new_item'        => 'Add New Exhibit',
    'add_new'             => 'Add New',
    'edit_item'           => 'Edit Exhibit',
    'update_item'         => 'Update Exhibit',
    'search_items'        => 'Search Exhibits',
    'not_found'           => 'Not found',
    'not_found_in_trash'  => 'Not found in Trash',
  );
  $rewrite = array(
    'slug'                => 'exhibits',
    'with_front'          => false,
    'pages'               => true,
    'feeds'               => true,
  );
  $args = array(
    'label'               => 'exhibit',
    'description'         => 'Exhibit',
    'labels'              => $labels,
    'supports'            => array( 'title', 'editor', 'thumbnail', ),
    'hierarchical'        => false,
    'public'              => true,
    'show_ui'             => true,
    'show_in_menu'        => true,
    'show_in_nav_menus'   => true,
    'show_in_admin_bar'   => true,
    'menu_position'       => 20,
    'menu_icon'           => 'dashicons-admin-post',
    'can_export'          => false,
    'has_archive'         => true,
    'exclude_from_search' => true,
    'publicly_queryable'  => true,
    'rewrite'             => $rewrite,
    'capability_type'     => 'page',
  );
  register_post_type( 'exhibit', $args );
}  
add_action( 'init', __NAMESPACE__ . '\post_type', 0 );

/**
 * Register Exhibition Taxonomy
 */
$labels = array(
  'name'              => 'Exhibitions',
  'singular_name'     => 'Exhibition',
  'search_items'      => 'Search Exhibitions',
  'all_items'         => 'All Exhibitions',
  'parent_item'       => 'Parent Exhibition',
  'parent_item_colon' => 'Parent Exhibition:',
  'edit_item'         => 'Edit Exhibition',
  'update_item'       => 'Update Exhibition',
  'add_new_item'      => 'Add New Exhibition',
  'new_item_name'     => 'New Exhibition',
);
$rewrite = array(
  'slug'                => 'exhibitions',
  'with_front'          => false,
  'pages'               => true,
  'feeds'               => true,
);
$args = array( 
  'hierarchical'      => true,
  'labels'            => $labels,
  'show_admin_column' => true,
  'show_ui'           => true,
  'query_var'         => true,
  'show_in_nav_menus' => true,
  'rewrite'           => $rewrite,
);
register_taxonomy( 'exhibition', array('exhibit'), $args);
// //remove list from sidebar, instead use cmb2 meta for year tax
// function remove_exhibition_meta() {
//   remove_meta_box('exhibition_div', 'exhibit', 'side');
// }
// add_action( 'admin_menu' , __NAMESPACE__ . '\remove_exhibition_meta' );


/**
 * Custom admin cols for post type
 */
function edit_columns($columns){
  $columns = array(
    'cb' => '<input type="checkbox">',
    'title' => 'Artist',
    // '_cmb2_title' => 'Title',
    'content' => 'Description',
    'featured_image' => 'Primary Image',
    // '_cmb2_website' => 'Website',
    // '_cmb2_social' => 'Social',
    'taxonomy-exhibition' => 'Exhibition',
  );
  return $columns;
}
add_filter('manage_exhibit_posts_columns', __NAMESPACE__ . '\edit_columns');

function custom_columns($column){
  global $post;
  if ( $post->post_type == 'exhibit' ) {
    if ( $column == 'featured_image' ) 
      echo the_post_thumbnail('thumbnail');
    elseif ( $column == 'content') 
      echo Utils\get_excerpt($post);
    else {
      $custom = get_post_custom();
      if ( array_key_exists($column, $custom) ) 
        echo $custom[$column][0];
    }
  }
}
add_action('manage_posts_custom_column', __NAMESPACE__ . '\custom_columns');

/**
 * Custom CMB2 fields for Exhibits
 */
function metaboxes( array $meta_boxes ) {
  $prefix = '_cmb2_'; //start with underscore to hide from custom fields list

  $meta_boxes['exhibit_metabox'] = array(
    'id'            => 'exhibit_metabox',
    'title'         => __( 'Additional Options', 'cmb2' ),
    'object_types'  => array( 'exhibit' ),
    'context'       => 'normal',
    'priority'      => 'high',
    'show_names'    => true,
    'fields'        => array(
      // array(
      //   'name'  => 'Exhibit Title',
      //   'id'    => $prefix . 'title',
      //   'type'  => 'text_medium',
      // ),
      // array(
      //   'name'      => 'Exhibition',
      //   'id'        => $prefix . 'exhibition',
      //   'taxonomy'  => 'exhibition',
      //   'type'      => 'taxonomy_select',
      // ),
      array(
        'name'  => 'More Information',
        'desc'  => 'Secondary Information, e.g.: Website, Social Media, etc.',
        'id'    => $prefix . 'moreinfo',
        'type'  => 'wysiwyg',
      ),     
      array(
        'name'  => 'More Images',
        'desc'  => 'Any photos in addition to featured image',
        'id'    => $prefix . 'more_images',
        'type'  => 'file_list',
        'preview_size' => array( 150, 150 ),
      ),
    ),
  );

  return $meta_boxes;
}
add_filter( 'cmb2_meta_boxes', __NAMESPACE__ . '\metaboxes' );

function get_header_slider() {

  $args = array(
    'numberposts' => 3,
    'post_type'   => 'exhibit',
    'orderby'     => 'rand',
    );

  $exhibit_posts = get_posts($args);
  if (!$exhibit_posts) return false;

  $output = '<div class="slider header-slider">';

  foreach ($exhibit_posts as $exhibit_post):
    $output .= '<div class="slide-item">';
    ob_start();
    include(locate_template('templates/exhibit-listing.php'));
    $output .= ob_get_clean();
    $output .=  '</div>';
  endforeach;

  $output .=  '</div>';
 
  return $output;
}


function get_exhibits($args) {

  $output ='';

  $exhibit_posts = get_posts($args);

  if (!$exhibit_posts) {
    $output .= '<div class="alert alert-warning">' 
      . __('Sorry, no results were found.', 'sage')
      .'</div>'
      .get_search_form(false);
    return $output;
  }

  foreach ($exhibit_posts as $exhibit_post):
    ob_start();
    include(locate_template('templates/exhibit-listing.php'));
    $output .= ob_get_clean();
  endforeach;
 
  return $output;
}




function get_exhibit_thumbnails() {
  //only if he have a thumb, lets make a list of thumb urls
  if( !has_post_thumbnail() ){
    return false;
  }
  $thumb_urls = array();

  //grab the featured image url as first url
  $thumb_urls[$i=0] = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'large')[0];
  //grab all other image urls after that
  $files = get_post_meta( get_the_ID(), '_cmb2_more_images', true );
  if($files) {
    foreach($files as $file) {
      $thumb_urls[++$i] = $file;
    }
  }

  //prepare the output
  $output = '';
  $output .= '<div class="slider thumbnail-slider">';
  foreach($thumb_urls as $thumb_url){
    $duo_url = \Firebelly\Media\get_duo_url($thumb_url);  //get duotone image
    $output .= <<< HTML
    <div class="slide-item">
      <div class="color" style="background-image: url('{$thumb_url}')" ></div>
      <div class="duo" style="background-image: url('{$duo_url}')"></div>
    </div>
HTML;
  }
  $output .= '</div>';

  return $output;
}





































