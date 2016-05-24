<?php 
/**
 * Exhibit Post Type
 */

 namespace Firebelly\PostTypes\Exhibit;
 use Firebelly\Utils;

  // Custom image size for post type?
  add_image_size( 'slide', 2000, 0, true );
  add_image_size( 'listing', 800, 0, true );

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
    'exclude_from_search' => false,
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


/**
 * Custom admin cols for post type
 */
function edit_columns($columns){
  $columns = array(
    'cb' => '<input type="checkbox">',
    'title' => 'Artist',
    '_cmb2_title' => 'Title',
    'content' => 'Statement',
    'featured_image' => 'Featured Image',
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
      array(
        'name'  => 'Title',
        'desc'  => 'Title of exhibit',
        'id'    => $prefix . 'title',
        'type'  => 'text_medium',
      ),     
      array(
        'name'  => 'Materials & Dimensions',
        'desc'  => '(optional)',
        'id'    => $prefix . 'materials',
        'type'  => 'wysiwyg',
      ),     
      array(
        'name'  => 'Bio',
        'desc'  => 'Artist\'s Biography (optional)',
        'id'    => $prefix . 'bio',
        'type'  => 'wysiwyg',
      ),           
      array(
        'name'  => 'Social',
        'desc'  => 'Website, Social Media, etc. (optional)',
        'id'    => $prefix . 'social',
        'type'  => 'wysiwyg',
      ),   
      array(
        'name'  => 'More Images',
        'desc'  => 'Any images for exhibit page in addition to featured image (optional)',
        'id'    => $prefix . 'more_images',
        'type'  => 'file_list',
        'preview_size' => array( 150, 150 ),
      ),
    ),
  );

  return $meta_boxes;
}
add_filter( 'cmb2_meta_boxes', __NAMESPACE__ . '\metaboxes' );

function get_intro_slider() {

  $args = array(
    'numberposts' => 5,
    'post_type'   => 'exhibit',
    'orderby'     => 'rand',
    );

  $exhibit_posts = get_posts($args);
  if (!$exhibit_posts) return false;

  $output = '<div class="slider intro-slider">';

  foreach ($exhibit_posts as $exhibit_post):
    $output .= '<div class="slide-item">';
    ob_start();
    $thumb_size = 'slide';
    include(locate_template('templates/exhibit-listing.php'));
    $output .= ob_get_clean();
    $output .=  '</div>';
  endforeach;

  $output .=  '</div>';
 
  return $output;
}


function get_exhibits($args) {

  $output ='';

  $exhibit_posts = new \WP_Query( $args );

  if ( $exhibit_posts->have_posts() ) {

    $output .= '<ul class="exhibit-list load-more-container">';
    while ( $exhibit_posts->have_posts() ) { 
      $exhibit_posts->the_post();
      global $post;
      $exhibit_post = $post;
      $output .= '<li class="exhibit">';
      ob_start();
      include(locate_template('templates/exhibit-listing.php'));
      $output .= ob_get_clean();
      $output .= '</li>';
    }
    $output .= '</ul>';
    wp_reset_postdata();

    ob_start();
    include(locate_template('templates/load-more-exhibits-button.php'));
    $output .= ob_get_clean();
    
  }else{
    $output .= '<div class="alert alert-warning">' 
      . __('Sorry, no results were found.', 'sage')
      .'</div>'
      .get_search_form(false);
  }

  return $output;
}


function get_exhibit_thumbnails() {
  // Do not proceed if no thumbnail
  if( !has_post_thumbnail() ){
    return false;
  }
  // Lets get all the thumbnail ids
  $thumb_ids = array();
  // Grab the featured image ids as first id
  $thumb_ids[$i=0] = get_post_thumbnail_id( get_the_ID() );
  // Grab all the image from our cmb2 file_list
  $files = get_post_meta( get_the_ID(), '_cmb2_more_images', true );
  if($files) {
    foreach($files as  $file_id => $file_url) {
      $thumb_ids[++$i] = $file_id;
    }
  }
  // Loop through each image gathered and make some html!
  $output = '';
  $output .= '<div class="slider thumbnail-slider">';
  foreach($thumb_ids as $thumb_id){
    $thumb_url = wp_get_attachment_image_src( $thumb_id, 'slide' )[0]; //get normal color image url
    $duo_url = \Firebelly\Media\get_duo_url($thumb_id, ['size' => 'slide'] );  //get duotone image url
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


function get_exhibition_object($exhibit_id) {
  return wp_get_post_terms($exhibit_id,'exhibition')[0];
}
































