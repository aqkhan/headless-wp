<?php

/*
Plugin Name: Headless WP
Description: API Modifications for Headless WP
Author: A Q Khan
Version: Beta
Author URI: http://aqkhan.ninja
*/

add_action( 'rest_api_init', function() {
    remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
    add_filter( 'rest_pre_serve_request', function( $value ) {
        header( 'Access-Control-Allow-Origin: *' );
        header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE' );
        header( 'Access-Control-Allow-Credentials: true' );
        return $value;
    });
}, 15 );

add_action( 'rest_api_init', 'register_routes' );

function register_routes() {
    register_rest_route( 'api/', 'categories', array(
        'methods' => 'GET',
        'callback' => 'get_blog_categories'
    ) );
    register_rest_route( 'api/', 'category/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'get_category_posts',
        'args' => array(
            'id' => array(
                'validate_callback' => function($param, $request, $key) {
                    return is_numeric( $param );
                }
            ),
        ),
    ) );
    register_rest_route( 'api/', 'post/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'get_single_post',
        'args' => array(
            'id' => array(
                'validate_callback' => function($param, $request, $key) {
                    return is_numeric( $param );
                }
            ),
        ),
    ) );
}

function get_blog_categories() {
    $args = array(
        'taxonomy' => 'category',
        'hide_empty' => true,
    );
    $categories = get_terms($args);
    foreach ($categories as $cat) {
        $cat->thumbnail = get_field('thumbnail', 'category' . '_' . $cat->term_id);
    }
    return $categories;
}

function get_category_posts(WP_REST_Request $request) {
    $cat_id =  $request->get_param( 'id' );
    $args = array(
        'post_type' => 'post',
        'tax_query' => array(
            array(
                'taxonomy' => 'category',
                'field'    => 'term_id',
                'terms' => $cat_id
            )
        )
    );
    $posts = get_posts($args);
    foreach ($posts as $post) {
        $post->thumbnail = get_the_post_thumbnail_url($post->ID, 'full');
    }
    return $posts;
}

function get_single_post(WP_REST_Request $request) {
    $post_id =  $request->get_param( 'id' );
    $post = get_post($post_id);
    $post->thumbnail = get_the_post_thumbnail_url($post->ID, 'full');
    return $post;
}