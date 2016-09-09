<?php
/*
Plugin Name: Compass HB
Description: Required for api.compasshb.com
Author: Brad Smith
Version: 1.2
GitHub Plugin URI: compasshb/plugin
*/


/**
 * Modify REST API content for pages to force
 * shortcodes to render since Visual Composer does not
 * do this
 */
add_action( 'rest_api_init', function ()
{
   register_rest_field(
          'page',
          'content',
          array(
                 'get_callback'    => 'compasshb_do_shortcodes',
                 'update_callback' => null,
                 'schema'          => null,
          )
       );
});

function compasshb_do_shortcodes( $object, $field_name, $request )
{
   WPBMap::addAllMappedShortcodes(); // This does all the work

   global $post;
   $post = get_post ($object['id']);
   $output['rendered'] = apply_filters( 'the_content', $post->post_content );

   return $output;
}


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

function rest_prepare_post_tag( $response, $object ) {
    if ( $object instanceof WP_Term ) {
        $response->data['acf'] = get_fields( $object->taxonomy . '_' . $object->term_id );
    }

    return $response;
}

add_filter( 'rest_prepare_post_tag', 'rest_prepare_post_tag', 10, 2 );

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
 * Get the value of the custom field
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

/**
 * Add users without requiring an email address
 * For sermon/teachers, etc.
 */
add_action( 'user_profile_update_errors', 'remove_empty_email_error' );

function remove_empty_email_error( $arg ) {
    if ( !empty( $arg->errors['empty_email'] ) ) unset( $arg->errors['empty_email'] );
}


/**
 * ACF config
 */
if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array (
	'key' => 'group_578497be9e603',
	'title' => 'Series Feature Image (Tag)',
	'fields' => array (
		array (
			'key' => 'field_578497dd46a45',
			'label' => 'Sermon Series Featured Image',
			'name' => 'series_image',
			'type' => 'image',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'return_format' => 'array',
			'preview_size' => 'thumbnail',
			'library' => 'all',
			'min_width' => '',
			'min_height' => '',
			'min_size' => '',
			'max_width' => '',
			'max_height' => '',
			'max_size' => '',
			'mime_types' => '',
		),
	),
	'location' => array (
		array (
			array (
				'param' => 'taxonomy',
				'operator' => '==',
				'value' => 'post_tag',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => 1,
	'description' => '',
));

acf_add_local_field_group(array (
	'key' => 'group_57789739a333d',
	'title' => 'Sermon Text & Series (Category)',
	'fields' => array (
		array (
			'key' => 'field_572d29ea9cc18',
			'label' => 'Scripture Text',
			'name' => 'text',
			'type' => 'text',
			'instructions' => 'Scripture reference. Book, chapter and verse. Input "Various" otherwise.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'formatting' => 'html',
			'maxlength' => '',
			'readonly' => 0,
			'disabled' => 0,
		),
		array (
			'key' => 'field_5768c51138762',
			'label' => 'Series',
			'name' => 'series',
			'type' => 'taxonomy',
			'instructions' => 'To add a new Sermon Series, create a new Tag under the Posts menu. Leave blank for no series.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'taxonomy' => 'post_tag',
			'field_type' => 'select',
			'allow_null' => 1,
			'add_term' => 1,
			'save_terms' => 1,
			'load_terms' => 0,
			'return_format' => 'object',
			'multiple' => 0,
		),
	),
	'location' => array (
		array (
			array (
				'param' => 'post_category',
				'operator' => '==',
				'value' => 'category:sermon',
			),
		),
		array (
			array (
				'param' => 'post_category',
				'operator' => '==',
				'value' => 'category:message',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'acf_after_title',
	'style' => 'seamless',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => array (
		0 => 'permalink',
		1 => 'custom_fields',
		2 => 'discussion',
		3 => 'comments',
		4 => 'slug',
		5 => 'format',
		6 => 'send-trackbacks',
	),
	'active' => 1,
	'description' => '',
));


acf_add_local_field_group(array (
	'key' => 'group_57886d35e281b',
	'title' => 'Sermon Worksheet',
	'fields' => array (
		array (
			'key' => 'field_57886d3d4dee6',
			'label' => 'Worksheet',
			'name' => 'worksheet',
			'type' => 'file',
			'instructions' => 'Supported: .pdf',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'return_format' => 'array',
			'library' => 'uploadedTo',
			'min_size' => '',
			'max_size' => '',
			'mime_types' => 'pdf',
		),
	),
	'location' => array (
		array (
			array (
				'param' => 'post_category',
				'operator' => '==',
				'value' => 'category:sermon',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => 1,
	'description' => '',
));

endif;