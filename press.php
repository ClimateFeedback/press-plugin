<?php
/*
Plugin Name: Press
Description: Plugin to create a custom post type displaying press coverage.
Version: 1.1
Author: Jake Hartnell
License: BSD
*/
add_action( 'init', 'create_press' );


function create_press() {
  register_post_type( 'press',
    array(
      'labels' => array(
        'name' => 'Press',
        'singular_name' => 'Press',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Press',
        'edit' => 'Edit',
        'edit_item' => 'Edit Press',
        'new_item' => 'New Press',
        'view' => 'View',
        'view_item' => 'View Press',
        'search_items' => 'Search Press',
        'not_found' => 'No Press found',
        'not_found_in_trash' => 'No Press found in Trash',
        'parent' => 'Parent Press'
      ),
      'public' => true,
      'menu_position' => 5,
      'supports' => array( 'title', 'thumbnail', 'excerpt' ),
      'taxonomies' => array( 'category' ),
      'has_archive' => false
    )
  );
}

add_action( 'admin_init', 'press_admin' );




function press_admin() {
  add_meta_box( 'press_meta_box',
    'Press Details',
    'display_press_meta_box',
    'press', 'normal', 'high'
  );
}

function display_press_meta_box( $press ) {

  $link = esc_html( get_post_meta( $press->ID, 'link', true ) );

  $date = esc_html( get_post_meta( $press->ID, 'date', true ) );

  $outlet = esc_html( get_post_meta( $press->ID, 'outlet', true ) );

  ?>
  <table>
    <tr>
      <td style="width: 100%">Press Link</td>
      <td><input type="text" size="80" name="press_link" value="<?php echo $link; ?>" /></td>
    </tr>

    <tr>
      <td style="width: 100%">Press Date</td>
      <td><input type="text" size="80" name="press_date" value="<?php echo $date; ?>" /></td>
    </tr>

    <tr>
      <td style="width: 100%">Outlet</td>
      <td><input type="text" size="80" name="press_outlet" value="<?php echo $outlet; ?>" /></td>
    </tr>

  </table>
  <?php }

add_action( 'save_post', 'add_press_fields', 10, 2 );


function add_press_fields( $press_id, $press ) {
  if ( $press->post_type == 'press' ) {

    if ( isset( $_POST['press_link'] ) && $_POST['press_link'] != '' ) {
      update_post_meta( $press_id, 'link', $_POST['press_link'] );
    }

    if ( isset( $_POST['press_date'] ) && $_POST['press_date'] != '' ) {
      update_post_meta( $press_id, 'date', $_POST['press_date'] );
    }

    if ( isset( $_POST['press_outlet'] ) && $_POST['press_outlet'] != '' ) {
      update_post_meta( $press_id, 'outlet', $_POST['press_outlet'] );
    }
  }
}

function pressLoop( $atts ) {
      extract( shortcode_atts( array(
          'type' => 'press',
      ), $atts ) );
      $output = '';
      $paged = ( get_query_var('page') ) ? get_query_var('page') : 1;
      $args = array(
          'post_type' => $type,
          'sort_column'   => 'menu_order',
          'posts_per_page' => 10,
          'paged' => $paged
      );
      $yo_quiery = new WP_Query( $args );

      while ( $yo_quiery->have_posts() ) : $yo_quiery->the_post();
          $output .= '<div class="row press">
              <div class="col-lg-12">
              <div class="media-left hidden-xs">
                <a class="postpic" href="'.get_post_meta( get_the_ID(), 'link', true ).'">
                  '.get_the_post_thumbnail().'
                </a>
              </div>
              <div class="media-body">
                <h3 class="noborder">'.get_post_meta( get_the_ID(), 'outlet', true ).'</h3>
                <a class="strong" target="_blank" href="'.get_post_meta( get_the_ID(), 'link', true ).'">'.get_the_title().'</a>
                <p class="small">
                  <span>'.get_post_meta( get_the_ID(), 'date', true ).'</span>
                </p>
                <p class="small"><em>'.get_the_excerpt().'</em></p>
              </div>
              </div>
            </div>';
      endwhile;

    wp_reset_query();
    return $output;

  }
  add_shortcode('press-loop', 'pressLoop');


  function press_pagination( $atts ) {
    extract( shortcode_atts( array(
        'type' => 'press',
    ), $atts ) );
    $paged = ( get_query_var('page') ) ? get_query_var('page') : 1;
    $args = array(
        // 'post_parent' => $parent,
        'post_type' => $type,
        'sort_column'   => 'menu_order',
        'posts_per_page' => 10,
        'paged' => $paged
    );
    $yo_quiery = new WP_Query( $args );

    $yo_quiery->query_vars['paged'] > 1 ? $current = $yo_quiery->query_vars['paged'] : $current = 1;

    $pagination = array(
        'base' => @add_query_arg('page','%#%'),
        'format' => '',
        'total' => $yo_quiery->max_num_pages,
        'current' => $current,
        'show_all' => true,
        'type' => 'list',
        'next_text' => '&raquo;',
        'prev_text' => '&laquo;'
    );

    if( !empty($yo_quiery->query_vars['s']) )
        $pagination['add_args'] = array( 's' => get_query_var( 's' ) );


    wp_reset_query();
    return paginate_links( $pagination );
  }
  add_shortcode('paginate-press', 'press_pagination');

?>
