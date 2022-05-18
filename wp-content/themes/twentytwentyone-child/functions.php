<?php

add_action( 'wp_enqueue_scripts', 'twentytwentyonechild_enqueue_styles' );
function twentytwentyonechild_enqueue_styles() {
    $parenthandle = 'parent-style';
    $theme = wp_get_theme();
    wp_enqueue_style( $parenthandle, get_template_directory_uri() . '/style.css', 
        array(),
        $theme->parent()->get('Version')
    );
    wp_enqueue_style( 'child-style', get_stylesheet_uri(),
        array( $parenthandle ),
        $theme->get('Version')
    );
}

include_once (__DIR__.'/inc/books/books.inc.php');