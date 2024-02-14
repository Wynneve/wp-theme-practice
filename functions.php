<?php

// Style

function events_styles() {
    wp_enqueue_style('events-style', get_stylesheet_uri());
}

add_action('wp_enqueue_scripts', 'events_styles');

// Event post type

function create_event_post_type() {
    register_post_type('event', array(
        'labels' => array(
            'name' => __('События'),
            'singular_name' => __('Событие')
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'event'),
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'comments'),
        'taxonomies' => array('category', 'post_tag')
    ));
}

add_action('init', 'create_event_post_type');

// Location post type

function create_location_post_type() {
    register_post_type('location', array(
        'labels' => array(
            'name' => __('Площадки'),
            'singular_name' => __('Площадка')
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'location'),
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'comments'),
        'taxonomies' => array('category', 'post_tag')
    ));
}

add_action('init', 'create_location_post_type');

// Event metaboxes

add_action('add_meta_boxes', 'add_event_metaboxes');
add_action('save_post', 'save_event_fields');

function add_event_metaboxes() {
    add_meta_box(
        'date',
        'Дата',
        'show_date_field',
        'event',
        'normal',
        'default'
    );

    add_meta_box(
        'location',
        'Площадка',
        'show_location_field',
        'event',
        'normal',
        'default'
    );
}

function show_date_field($post) {
    $date = get_post_meta($post->ID, 'date', true);
    echo
    '
        <input type="date" id="date_field" name="date" value="' . $date . '">
    ';
}

function show_location_field($post) {
    $locations = get_locations();

    $location_id = get_post_meta($post->ID, 'location', true);

    echo
    '
        <select id="location_field" name="location">    
    ';

    foreach ($locations as $location) {
        echo
        '
            <option value="' . $location[0] . '" ' . (($location[0] == $location_id) ? 'selected' : '') . '>' . $location[1] . '</option>
        ';
    }

    echo
    '
        </select>
    ';
}

function save_event_fields($post_id) {
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    
    if(!current_user_can('edit_post', $post_id)) return;

    $date = array_key_exists('date', $_POST) ? $_POST['date'] : '';
    $location = array_key_exists('location', $_POST) ? $_POST['location'] : '';

    update_post_meta($post_id, 'date', $date);
    update_post_meta($post_id, 'location', $location);
}

// Location metaboxes

add_action('add_meta_boxes', 'add_location_metaboxes');
add_action('save_post', 'save_location_fields');

function add_location_metaboxes() {
    add_meta_box(
        'address',
        'Адрес',
        'show_address_field',
        'location',
        'normal',
        'default'
    );

    add_meta_box(
        'telephone',
        'Номер телефона',
        'show_telephone_field',
        'location',
        'normal',
        'default'
    );
}

function show_address_field($post) {
    $address = get_post_meta($post->ID, 'address', true);
    echo
    '
        <input type="text" id="address_field" name="address" value="' . $address . '">
    ';
}

function show_telephone_field($post) {
    $telephone = get_post_meta($post->ID, 'telephone', true);
    echo
    '
        <input type="text" id="telephone_field" name="telephone" value="' . $telephone . '">
    ';
}

function save_location_fields($post_id) {
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    
    if(!current_user_can('edit_post', $post_id)) return;

    $address = array_key_exists('address', $_POST) ? $_POST['address'] : '';
    $telephone = array_key_exists('telephone', $_POST) ? $_POST['telephone'] : '';

    update_post_meta($post_id, 'address', $address);
    update_post_meta($post_id, 'telephone', $telephone);
}

function get_locations() {
    $args = array('post_type' => 'location');
    $query = new WP_Query($args);
    $locations = array();
    $has_locations = $query->have_posts();
    while($query->have_posts()) {
        $query->the_post();
        array_push($locations, array(get_the_ID(), get_the_title()));
    }
    wp_reset_postdata();

    return $has_locations ? $locations : false;
}


// REST API


// создание маршрута
add_action( 'rest_api_init', function(){

	// маршрут
	$route = '/events/(?P<id>\d+)';

	// параметры конечной точки (маршрута)
	$route_params = [
		'methods'  => 'GET',
		'callback' => 'get_events',
		'args'     => [
			'location' => [
				'type'     => 'int', // значение параметра должно быть строкой
				'required' => true,     // параметр обязательный
            ]
			// ],
			// 'arg_int' => [
			// 	'type'    => 'integer', // значение параметра должно быть числом
			// 	'default' => 10,        // значение по умолчанию = 10
			// ],
		],
		'permission_callback' => function( $request ){
			// только авторизованный юзер имеет доступ к эндпоинту
			return is_user_logged_in();
		},
	];

	register_rest_route( $namespace, $route, $route_params );

} );

// функция обработчик конечной точки (маршрута)
function get_events( WP_REST_Request $request ){

	$posts = get_posts( [
		'type' => 'event',
	] );

	if ( empty( $posts ) ) {
		return new WP_Error( 'no_posts', 'Событий не найдено', [ 'status' => 404 ] );
	}

	return $posts;
}

?>