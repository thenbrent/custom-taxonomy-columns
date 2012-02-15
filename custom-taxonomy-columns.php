<?php
/*
Plugin Name: Custom Taxonomy Columns
Description: Give custom taxonomies a column on the manage posts admin page. 
Author: _FindingSimple
Author URI: http://findingsimple.com/
Version: 1.1
*/


/**
 * Add a column to the manage posts page for each registered custom taxonomy. 
 **/
function rt_add_columns( $columns, $post_type ) {

	$taxonomy_names = get_object_taxonomies( 'post' );

	foreach ( $taxonomy_names as $taxonomy_name ) {

		$taxonomy = get_taxonomy( $taxonomy_name );

		if ( $taxonomy->_builtin || !in_array( $post_type, $taxonomy->object_type ) )
			continue;

		$columns[ $taxonomy_name ] = $taxonomy->label;
	}

	return $columns;
}
add_filter( 'manage_posts_columns', 'rt_add_columns', 10, 2 ); //Filter out Post Columns with 2 custom columns


/**
 * Add the terms assigned to a post for each registered custom taxonomy to the 
 * custom column on the manage posts page.
 **/
function rt_column_contents( $column_name, $post_id ) {
	global $wpdb, $post_type;
	
	$type = ''; //set blank post type
	
	if ($post_type != 'post') {
		$type = 'post_type=' . $post_type . '&';
	}

	$taxonomy_names = get_object_taxonomies( 'post' );

	foreach ( $taxonomy_names as $taxonomy_name ) {
		$taxonomy = get_taxonomy( $taxonomy_name );

		if ( $taxonomy->_builtin || $column_name != $taxonomy_name )
			continue;

		$terms = get_the_terms( $post_id, $taxonomy_name ); //lang is the first custom taxonomy slug
		if ( !empty( $terms ) ) {
			$out = array();
			foreach ( $terms as $term )
				$termlist[] = "<a href='edit.php?" . $type . $taxonomy->rewrite['slug']."=$term->slug'> " . esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, $taxonomy_name, 'display' ) ) . "</a>";
			echo join( ', ', $termlist );
		} else {
			printf( __( 'No %s.'), $taxonomy->label );
		}
	}
}
add_action( 'manage_posts_custom_column', 'rt_column_contents', 10, 2 );


/**
 * Generate mock taxonomies for testing.
 **/
function rt_mock_tax(){
	$args = array( 'label' => 'Mocks' );
	register_taxonomy( 'mock_tax', 'post', $args );

	$args = array( 'label' => 'Faux', 'hierarchical' => true );
	register_taxonomy( 'faux_tax', 'post', $args );
}
add_action( 'init', 'rt_mock_tax' );
