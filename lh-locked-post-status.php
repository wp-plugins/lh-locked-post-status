<?php
/*
Plugin Name: LH Locked Post Status
Version: 0.01
Plugin URI: http://lhero.org/plugins/lh-locked-post-status/
Description: Creates two additional post statuses of Publicly Locked and Privately Locked
Author: Peter Shaw
Author URI: http://shawfactor.com
*/





class LH_locked_post_status_plugin {

var $newstatusname;
var $newstatuslabel;
var $newstatuslabel_count;
var $capability;

function current_user_can_view() {
	/**
	 * Default capability to grant ability to view Archived content (if the status is set to non public)
	 *
	 * @since 0.3.0
	 *
	 * @return string
	 */

if ($this->capability == "read"){

return true;


} else {

	return current_user_can($this->capability);

}
}


function append_post_status_list(){
     global $post;
     $complete = '';
     $label = '';

if ( current_user_can('manage_options') ) {


          if($post->post_status == $this->newstatusname){
               $complete = ' selected=\"selected\"';
               $label = '<span id=\"post-status-display\">'.ucwords($this->newstatuslabel).'</span>';

          echo '
          <script>
          jQuery(document).ready(function($){
$("select#post_status").append("<option value=\"'.$this->newstatusname.'\" '.$complete.'>'.ucwords($this->newstatuslabel).'</option>");
               $(".misc-pub-section label").append("'.$label.'");
          });
          </script>
          ';




          } elseif ($post->post_status == "publish"){


          echo '
          <script>
          jQuery(document).ready(function($){
               $("select#post_status").append("<option value=\"'.$this->newstatusname.'\">'.ucwords($this->newstatuslabel).'</option>");
          });
          </script>
          ';




}

}
     
} 


function display_locked_state( $states ) {
     global $post;
     $arg = get_query_var( 'post_status' );
     if($arg != $this->newstatusname){
          if($post->post_status == $this->newstatusname){
               return array($this->newstatuslabel);
          }
     }
    return $states;
}



function create_locked_custom_post_status(){

$args = array(
	  'public' => $this->current_user_can_view(),
          'label'                     => _x( $this->newstatuslabel, 'post' ),
          'show_in_admin_all_list'    => false,
          'show_in_admin_status_list' => true,
          'label_count'               => _n_noop( $this->newstatuslabel_count, $this->newstatuslabel_count) );


if ($this->public){
$args['public'] = $this->public;
}


if ($this->private){
$args['private'] = $this->private; 

}



register_post_status( $this->newstatusname, $args);
}




function protect_locked_posts( $caps, $cap, $user_id, $args ) {



/* If the user doesn't have manage_options, remove their ability to edit or delete the post type object. */

if ( 'edit_post' == $cap || 'delete_post' == $cap ) {

if (!user_can( $user_id, "manage_options")){

$post = get_post( $args[0] );
		
if ($post->post_status == $this->newstatusname){

$caps[] = 'do_not_allow';

}

}

} else {

$post = get_post( $args[0] );
		
if ($post->post_status == $this->newstatusname){

$caps[] = 'read_private_posts';

}

}
	
/* Return the capabilities required by the user. */
return $caps;
}




function __construct($name,$label,$count,$capability = 'read') {

$this->newstatusname = $name;
$this->newstatuslabel = $label;
$this->newstatuslabel_count = $count;
$this->capability = $capability;


add_action( 'init', array($this,"create_locked_custom_post_status"));

add_filter( 'display_post_states', array($this,"display_locked_state"));

add_action('admin_footer-post.php', array($this,"append_post_status_list"));

add_filter( 'map_meta_cap', array($this,"protect_locked_posts"),9,4);




	}





}



$lh_locked_public_post_status = new LH_locked_post_status_plugin('public_lock','publicly locked','Publicly Locked <span class="count">(%s)</span>','read');

$lh_locked_private_post_status = new LH_locked_post_status_plugin('private_lock','privately locked','Privately locked <span class="count">(%s)</span>','read_private_posts');







?>