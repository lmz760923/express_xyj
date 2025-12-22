<?php
/*
Plugin Name:my paypal payment
*/

add_action('init',function(){
    register_post_type('mppp_order',array(
    'labels'=>array(
        'name'=>__('name','my-paypal-payment'),
        'singular_name'=>__('singular_name','my-paypal-payment'),

        'menu_name'=>__('menu_name','my-paypal-payment'),

        'add_new'=>__('add_new','my-paypal-payment'),

        'add_new_item'=>__('add_new_item','my-paypal-payment'),

        'edit_item'=>__('edit_item','my-paypal-payment'),

        'new_item'=>__('new_item','my-paypal-payment'),

        'view_item'=>__('view_item','my-paypal-payment'),

        'search_items'=>__('search_items','my-paypal-payment'),


    ),
    'public'=>true,
    'public_queryable'=>false,
    'show_ui'=>true,
    'show_in_menu'=>true,
    'query_var'=>true,
    'capability_type'=>'post',
    'has_archive'=>true,
    'hierarchical'=>false,
    //'menu_position'=>56,
    'menu_icon'=>'dashicons-cart',
    'supports'=>array('title'),
    'show_in_rest'=>false,
));
});


