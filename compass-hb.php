<?php
/*
Plugin Name: Compass HB
Description: Required for api.compasshb.com
Author: Brad Smith
Version: 1.0
*/


/**
 * Expose tribe_events to WP-API
 * 
 * The Event feature uses a plugin called The Event Calendar
 * which registers a custom post type named 'tribe_events' 
 * This function will let WP-API know about and return this
 * already registered post type.
 * Reference: http://v2.wp-api.org/extending/custom-content-types/
 */
add_action( 'init', 'tribe_events_rest_support', 25 );

function tribe_events_rest_support() 
{
	global $wp_post_types;

	$post_type_name = 'tribe_events';

	if( isset( $wp_post_types[ $post_type_name ] ) ) {
		$wp_post_types[$post_type_name]->show_in_rest = true;
		$wp_post_types[$post_type_name]->rest_base = $post_type_name;
		$wp_post_types[$post_type_name]->rest_controller_class = 'WP_REST_Posts_Controller';
	}

}

// We need to return some additional fields in the event API
// such as the start and end date
add_action( 'rest_api_init', 'slug_register_eventtimes' );
function slug_register_eventtimes() {
    register_rest_field( 'tribe_events',
        '_EventStartDate',
        array(
            'get_callback'    => 'slug_get_custom_meta',
            'update_callback' => null,
            'schema'          => null,
        )
    );
    register_rest_field( 'tribe_events',
        '_EventEndDate',
        array(
            'get_callback'    => 'slug_get_custom_meta',
            'update_callback' => null,
            'schema'          => null,
        )
    );
}

/**
 * Get the value of the "starship" field
 *
 * @param array $object Details of current post.
 * @param string $field_name Name of field.
 * @param WP_REST_Request $request Current request
 *
 * @return mixed
 */
function slug_get_custom_meta( $object, $field_name, $request ) {
    return get_post_meta( $object[ 'id' ], $field_name, true );
}