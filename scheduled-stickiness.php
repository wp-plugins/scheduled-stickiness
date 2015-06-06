<?php
/*
Plugin Name: Scheduled Stickiness
Plugin URI:  http://magnus-karlsson.nu/program/scheduled-stickiness/
Description: A plugin that sets and unsets stickiness for individual posts based on meta fields
Version:     0.1
Author:      Magnus Karlsson
Author URI:  http://magnus-karlsson.nu
*/

// security
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/** ********************************************************
*	Register the plugin to run with wp pseudo cron
********************************************************** */

register_activation_hook(__FILE__, 'mknu_scheduled_stickiness_activation');
add_action('mknu_stickiness_event', 'mknu_stickiness_cron');


function mknu_scheduled_stickiness_activation() {
	wp_schedule_event(time(), 'hourly', 'mknu_stickiness_event');	
	log_me("Plugin activated");	
}


register_deactivation_hook(__FILE__, 'mknu_scheduled_stickiness_deactivation');
function mknu_scheduled_stickiness_deactivation() {
	wp_clear_scheduled_hook('mknu_stickiness_event');
	log_me("Plugin deactivated");	
}


function mknu_stickiness_cron()
{
	log_me("cron");
	mknu_check_for_scheduled_stickiness();
}


/* ************ META BOX UI AND ACTION ********************************** 
* Based on http://themefoundation.com/wordpress-meta-boxes-guide/
* Thanks!
********************************************************************* */

//Adds a meta box to the post editing screen
function mknu_scheduled_stickiness_meta() {
    add_meta_box( 'mknu_scheduled_stickiness', 'Schedule stickiness', 'mknu_scheduled_sticky_callback', 'post', 'side' );
}
add_action( 'add_meta_boxes', 'mknu_scheduled_stickiness_meta' );


// Outputs the content of the meta box
function mknu_scheduled_sticky_callback( $post ) {
    
    wp_nonce_field( basename( __FILE__ ), 'mknu_nonce' );
    $stored_meta = get_post_meta( $post->ID );    
    ?>
    <p>
        <label for="mknu_sticky_date"><strong>Set sticky</strong>. What is the first date the post should be sticky (yyyy-mm-dd)</label>
        <input type="text" name="mknu_sticky_date" id="mknu_sticky_date" value="<?php if ( isset ( $stored_meta['mknu_sticky_date'] ) ) echo $stored_meta['mknu_sticky_date'][0]; ?>" />
    </p>
    <p>
        <label for="mknu_unsticky_date"><strong>Set unsticky</strong>. What is the last date the post should be sticky (yyyy-mm-dd)</label>
        <input type="text" name="mknu_unsticky_date" id="mknu_unsticky_date" value="<?php if ( isset ( $stored_meta['mknu_unsticky_date'] ) ) echo $stored_meta['mknu_unsticky_date'][0]; ?>" />
    </p> 
    <?php
}


// Saves the custom meta input
function mknu_scheduled_stickiness_save( $post_id ) {
 
    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'mknu_nonce' ] ) && wp_verify_nonce( $_POST[ 'mknu_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
 
    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {    
    	//log_me("Exiting script. autosave=".$is_autosave." revision=".$is_revision." valid nonce:".$is_valid_nonce);    
        return;
    }
  	
    // Checks for input and sanitizes and saves if needed
    if( isset( $_POST[ 'mknu_sticky_date' ] ) ) {
        $update_sticky = update_post_meta( $post_id, 'mknu_sticky_date', sanitize_text_field( $_POST[ 'mknu_sticky_date' ] ) );
    }
    if( isset( $_POST[ 'mknu_unsticky_date' ] ) ) {
        $update_unsticky = update_post_meta( $post_id, 'mknu_unsticky_date', sanitize_text_field( $_POST[ 'mknu_unsticky_date' ] ) );
    }
 
}

add_action( 'save_post', 'mknu_scheduled_stickiness_save' );

/* // UI  */


/** ***********************************************************************
* 	DO PSEUDO CRON JOB set/unset STICKINESS 
************************************************************************** */
function mknu_check_for_scheduled_stickiness() {
	
	// get posts
	// loop through posts and check their sticky meta fields
	// set sticky if in the interval
	// unset sticky if not in interval

	// specify query
	$args = array(
		'post-type' => 'post',
		'meta_query' => array(
				'relation' => 'OR',
			array(
				'key' => 'mknu_sticky_date'
			),
			array(
				'key' => 'mknu_unsticky_date'
			)
		)
	);

	// The Query
	$the_query = new WP_Query( $args );

	$now = date("Y-m-d");

	// The Loop
	if ( $the_query->have_posts() ) {

		while ( $the_query->have_posts() ) {
		
			$the_query->the_post();
		
			$stored_meta = get_post_meta(get_the_ID());	
			$start = $stored_meta['mknu_sticky_date'][0];
			$stop  = $stored_meta['mknu_unsticky_date'][0];
		
		
			if (empty($start) && empty($stop))
			{
				continue; // no sticky date fields set, go to next post
			}
			else
			{		
				if ($start>$now)
				{
					// should not be sticky yet
				}
				else if ($start<=$now && $stop>=$now)
				{
					// should become sticky
					if (!is_sticky(get_the_ID()))
					{
						stick_post(get_the_ID());					
						log_me(get_the_ID()." stickiness set to $start");
					}
					else
					log_me(get_the_ID()." already was sticky");
				}
				else if ($stop < $now)
				{
					// should not be sticky any longer
					if (is_sticky(get_the_ID()))
					{
						unstick_post(get_the_ID());
						log_me(get_the_ID()." unstickiness set to $stop");
					}
					else
						log_me(get_the_ID()." already was unsticky");
				}
				else
				{
					// this is weird
				}			
			}
				
		} // while

	} else {
		// no posts found
	}

	/* Restore original Post Data */
	// is this needed in a plugin???
	wp_reset_postdata();
	
} // end "cron" job


// ***************** debug output *******************
function log_me($message) {
    if (WP_DEBUG === true) {
        if (is_array($message) || is_object($message)) {
            error_log(print_r($message, true));
        } else {
            error_log("MKNU: ".$message);
        }
    }
}

?>
