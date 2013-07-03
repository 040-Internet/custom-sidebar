<?php
/*
Plugin Name: Custom Sidebar
Plugin URI: http://040.se
Description: This plugin adds a meta box on every page, that allows you to create a custom sidebar for that page.
Version: 0.1
Author: Martin Nilsson
Author URI: http://040.se
*/


 /**
  *
  * Load the function file
  *
  **/
  require_once('options/functions.php');


 /**
  *
  * Add the meta boxes
  *
  **/
  add_action('add_meta_boxes', 'custom_sidebar_meta_box');
  function custom_sidebar_meta_box() {
    $types = get_option( 'custom_sidebar_settings' );

    foreach ($types as $key => $type) {
      add_meta_box(
      'custom-sidebar',       // ID attribute of metabox
      'Custom Sidebar',       // Title of metabox visible to user
      'meta_box_callback',    // Function that prints box in wp-admin

      $type,                  // Show box for posts, pages, custom, etc.
                              // (In this case, thats decided from the plugin option panel)

      'side',                 // Where on the page to show the box
      'high' );               // Priority of box in display order
      }
      
  }


 /**
  *
  * This function handles the markup. All the html
  * for the option page goes here
  *
  **/
  function meta_box_callback() {
    global $post;
    $custom = get_post_custom($post->ID);
    $field_id = $custom['has_custom_sidebar'][0];
   
    $field_id_value = get_post_meta($post->ID, 'has_custom_sidebar', true);
    if($field_id_value == "yes"){
      $field_id_checked = 'checked="checked"';
    } ?>

    <p style="font-size: 12px; color: #aaa; font-style: italic;"><?php _e('If you want to use a custom sidebar on this page, check the box below. To customize the content of the sidebar, go to Appearance > Widgets.'); ?></p>
    <input type="checkbox" name="has_custom_sidebar" value="yes" <?php echo $field_id_checked; ?> /> <?php _e('Use custom sidebar.'); ?>
  
  <?php
  }


 /**
  *
  * This function handles the saving process, it also deletes the
  * options thats not filled out from the DB
  *
  **/
  add_action('save_post', 'save_details');
  function save_details() {
    global $post;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post->ID;

    if ( isset($_POST['has_custom_sidebar']) ) {
        update_post_meta($post->ID, 'has_custom_sidebar', $_POST['has_custom_sidebar']);
    }else{
        delete_post_meta($post->ID, 'has_custom_sidebar');
    }
  }


 /**
  *
  * This function creates the widgets for each post, page etc.
  * Where the option for a custom_sidebar shows up, is decided
  * from the option panel.
  *
  **/
  add_action('widgets_init', 'custom_sidebar_widget_init');
  function custom_sidebar_widget_init() {
    $types = get_option( 'custom_sidebar_settings' );
    $landingIDs = array();
    $landingpages = '';

    if(!empty($types)){
      foreach ($types as $key => $type) {
        $landingpages[] = get_posts(  array(
          'meta_key'      =>  'has_custom_sidebar',
          'meta_value'    =>  'yes',
          'post_type'      =>  $type
        ));
      }

      foreach ($landingpages as $key) {
        foreach($key as $landingpage) {
          if(!in_array($landingpage->ID, $landingIDs)) {
            $landingIDs[] = $landingpage->ID;
            register_sidebar(array(
              'name' => 'Custom Sidebar: ' . $landingpage->post_title,
              'description' => '',
              'id' => 'custom-sidebar-' . $landingpage->ID,
              'before_widget' => '<div id="%1$s" class="widget %2$s"><div class="center">',
              'after_widget'  => '</div></div>',
              'before_title'  => '<h2 class="title">',
              'after_title'   => '</h2>'
            ));
          }
        }
      }
    }
  }


 /**
  *
  * This function will dynamically handle each custom sidebar
  * to make it easier for you. It takes one parameter:
  *
  * 1. Fallback sidebar (default: null)
  *
  **/
  function custom_sidebar($fallback = null) {

    global $wp_query;
    $pID = $wp_query->post->ID;


    if (is_active_sidebar('custom-sidebar-'.$pID)) {
      
      if (is_page() || is_single()) {
        dynamic_sidebar('custom-sidebar-'.$pID);
      } else {
        if($fallback) {
          dynamic_sidebar($fallback);
        }
      }
    }

  }