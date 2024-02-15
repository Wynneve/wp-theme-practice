<?php

// Style

function events_styles() {
    wp_enqueue_style('events-style', get_stylesheet_uri());
}

add_action('wp_enqueue_scripts', 'events_styles');

// Event post type

function create_event_post_type() {
    register_post_type('event', [
        'labels' => [
            'name' => __('События'),
            'singular_name' => __('Событие')
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'event'],
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'comments'],
        'taxonomies' => ['category', 'post_tag'],
    ]);
}

add_action('init', 'create_event_post_type');

// Location post type

function create_location_post_type() {
    register_post_type('location', [
        'labels' => [
            'name' => __('Площадки'),
            'singular_name' => __('Площадка')
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'location'],
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'comments'],
        'taxonomies' => ['category', 'post_tag']
    ]);
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
    ?>
        <input type="date" id="date_field" name="date" value="<?=$date?>">
    <?php
}

function show_location_field($post) {
    $locations = get_locations();
    $location_id = get_post_meta($post->ID, 'location', true);

    ?>
        <select id="location_field" name="location">    
    <?php

    foreach ($locations as $location) {
        ?>
            <option value="<?=$location[0]?>" <?=($location[0] == $location_id) ? 'selected' : ''?>><?=$location[1]?></option>
        <?php
    }

    ?>
        </select>
    <?php
}

function save_event_fields($post_id) {
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    
    if(!current_user_can('edit_post', $post_id)) return;

    if(get_post_type($post_id) != 'event') return;

    $date = isset($_POST['date']) ? $_POST['date'] : '';
    $location = isset($_POST['location']) ? $_POST['location'] : '';

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
    ?>
        <input type="text" id="address_field" name="address" value="<?=$address?>">
    <?php
}

function show_telephone_field($post) {
    $telephone = get_post_meta($post->ID, 'telephone', true);
    ?>
        <input type="text" id="telephone_field" name="telephone" value="<?=$telephone?>">
    <?php
}

function save_location_fields($post_id) {
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    
    if(!current_user_can('edit_post', $post_id)) return;
    
    if(get_post_type($post_id) != 'location') return;

    $address = isset($_POST['address']) ? $_POST['address'] : '';
    $telephone = isset($_POST['telephone']) ? $_POST['telephone'] : '';

    update_post_meta($post_id, 'address', $address);
    update_post_meta($post_id, 'telephone', $telephone);
}

// Helper functions

function get_locations() {
    $locations = get_posts([
		'post_type' => 'location',
	]);
    $filtered = [];
    foreach($locations as $location) {
        array_push($filtered, [$location->ID, $location->post_title]);
    }

    return empty($filtered) ? false : $filtered;
}

// REST API

add_action('rest_api_init', 'register_event_route');

function register_event_route() {
    $namespace = 'custom';

	$route = '/events';

	$route_params = [
		'methods'  => 'GET',
		'callback' => 'get_events',
		'args'     => [
			'location' => [
				'type'     => 'int',
				'required' => false,
            ],
			'date_start' => [
                'type'     => 'string',
                'required' => false,
            ],
            'date_finish' => [
                'type'     => 'string',
                'required' => false,
            ],
		],
	];

	register_rest_route($namespace, $route, $route_params);
}

function get_events(WP_REST_Request $request) {
    $query = $request->get_query_params();
    if(!empty($query['location'])) $location = $query['location'];
    if(!empty($query['date_start'])) $date_start = $query['date_start'];
    if(!empty($query['date_finish'])) $date_finish = $query['date_finish'];

	$posts = get_posts([
		'post_type' => 'event',
        'meta_query' => [
            'relation' => 'AND',
            isset($location) ? [
                'key' => 'location',
                'value' => $location,
                'compare_key' => '=',
            ] : [],
            ],
	]);

    $result = [];
    
    foreach($posts as $post) {
        $post->meta = get_post_meta($post->ID);

        if(isset($date_start) && strtotime($post->meta['date'][0]) < strtotime($date_start)) continue;
        if(isset($date_finish) && strtotime($post->meta['date'][0]) > strtotime($date_finish)) continue;

        array_push($result, $post);
    }

	if (empty($result)) {
		return new WP_Error( 'no_posts', 'Событий не найдено', [ 'status' => 404 ] );
	}

	return $posts;
}

?>