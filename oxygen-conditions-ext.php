<?php

/*
Plugin Name: Oxygen Conditions Ext
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: A brief description of the Plugin.
Version: 1.1.3
Author: admin
Author URI: http://URI_Of_The_Plugin_Author
License: A "Slug" license name e.g. GPL2
*/

add_action( 'init', 'oxext_register_conditions' );

function oxext_register_conditions() {
	if ( function_exists( 'oxygen_vsb_register_condition' ) ) {

		oxext_register_binary_condition( 'Is home page', 'oxext_is_home_page', 'Post' );
		oxext_register_binary_condition( 'Is front page', 'oxext_is_front_page', 'Post' );
		oxext_register_binary_condition( 'Is admin', 'oxext_is_admin', 'Other' );
		oxext_register_binary_condition( 'Is single', 'oxext_is_single', 'Post' );
		oxext_register_binary_condition( 'Is page', 'oxext_is_page', 'Post' );
		oxext_register_binary_condition( 'Is sticky', 'oxext_is_sticky', 'Post' );
		oxext_register_binary_condition( 'Is 404 page', 'oxext_is_404', 'Post' );
		oxext_register_binary_condition( 'Is attachment page', 'oxext_is_attachment', 'Post' );
		oxext_register_binary_condition( 'Is author page', 'oxext_is_author_page', 'Post' );
		oxext_register_binary_condition( 'Is search page', 'oxext_is_search', 'Post' );
		oxext_register_binary_condition( 'Search has results', 'oxext_search_has_results', 'Post' );

		$types   = get_post_types();
		$options = array();
		foreach ( $types as $type ) {
			$options[ $type ] = $type;
		}
		oxygen_vsb_register_condition( 'Is singular', array( 'options' => $options, 'custom' => true ),
			array( '==', '!=' ), 'oxext_is_singular', 'Post' );

		oxygen_vsb_register_condition( 'Is post type archive', array( 'options' => $options, 'custom' => true ),
			array( '==', '!=' ), 'oxext_post_type_archive', 'Archive' );
		oxygen_vsb_register_condition( 'Is archive', array( 'options' => array( 'True', 'False' ), 'custom' => true ),
			array( '==' ), 'oxext_check_if_archive', 'Archive' );
		oxygen_vsb_register_condition( 'Post has no parent', array(
			'options' => array( 'True', 'False' ),
			'custom'  => true
		),
			array( '==' ), 'oxext_post_has_no_parent', 'Post' );
		oxygen_vsb_register_condition( 'Post has no children', array(
			'options' => array( 'True', 'False' ),
			'custom'  => true
		),
			array( '==' ), 'oxext_post_has_no_children', 'Post' );

		$taxonomies = get_taxonomies();
		oxygen_vsb_register_condition( 'Post taxonomy type', array(
			'options' => array_values( $taxonomies ),
			'custom'  => true
		),
			array( '==', '!=' ), 'oxext_post_taxonomy', 'Post' );

		oxygen_vsb_register_condition( 'Taxonomy type archive', array(
			'options' => array_values( $taxonomies ),
			'custom'  => true
		),
			array( '==', '!=' ), 'oxext_taxonomy_type_archive', 'Archive' );


	}
}

function oxext_register_binary_condition( string $tag, callable $callback, string $category ): void {
	oxygen_vsb_register_condition( $tag, array( 'options' => array( 'True', 'False' ), 'custom' => true ),
		array( '==' ), $callback, $category );
}

function oxext_is_home_page( $value, $operator ): bool {
	return oxext_check_binary( is_home(), $value );
}

function oxext_check_binary( bool $condition_result, string $value ): bool {
	return $value === 'True' ? $condition_result : ! $condition_result;
}

function oxext_is_front_page( $value, $operator ): bool {
	return oxext_check_binary( is_front_page(), $value );
}

function oxext_is_admin( $value, $operator ): bool {
	return oxext_check_binary( is_admin(), $value );
}

function oxext_is_single( $value, $operator ): bool {
	return oxext_check_binary( is_single(), $value );
}

function oxext_is_page( $value, $operator ): bool {
	return oxext_check_binary( is_page(), $value );
}

function oxext_is_singular( $value, $operator ): bool {
	$singular = is_singular( array( $value ) );
	return $operator === '==' ? $singular : ! $singular;
}

function oxext_is_sticky( $value, $operator ): bool {
	return oxext_check_binary( is_sticky(), $value );
}

function oxext_is_404( $value, $operator ): bool {
	return oxext_check_binary( is_404(), $value );
}

function oxext_is_attachment( $value, $operator ): bool {
	return oxext_check_binary( is_attachment(), $value );
}

function oxext_is_author_page( $value, $operator ): bool {
	return oxext_check_binary( is_author(), $value );
}

function oxext_is_search( $value, $operator ): bool {
	return oxext_check_binary( is_search(), $value );
}

function oxext_post_type_archive( $value, $operator ): bool {
	$is_archive = is_post_type_archive( $value );
	return $operator === '==' ? $is_archive : ! $is_archive;
}

function oxext_check_if_archive( $value, $operator ): bool {
	return oxext_check_binary( is_archive(), $value );
}

function oxext_post_has_no_parent( $value, $operator ): bool {
	if ( oxext_check_is_single() ) {
		$post = get_post();
		return $value === 'True' ? $post->post_parent == 0 : $post->post_parent > 0;
	}

	return false;
}

function oxext_post_has_no_children( $value, $operator ): bool {
	if ( oxext_check_is_single() ) {

		$post_id = get_the_ID();
		$post_type = get_post_type();
		global $wpdb;
		$posts_table    = $wpdb->posts;
		$query          = "SELECT COUNT(ID) FROM $posts_table WHERE post_parent=$post_id AND post_type='$post_type'";
		$children_count = intval( $wpdb->get_var( $query ) );
		return $value === 'True' ? $children_count === 0 : $children_count > 0;
	}

	return false;
}

function oxext_post_taxonomy( $value, $operator ): bool {
	if( oxext_check_is_single() ){
		$post_terms     = wp_get_post_terms( get_the_ID(), $value );
		if( $operator === '==' ){
			return ! empty( $post_terms );
		} else {
			return empty( $post_terms );
		}

	}

	return false;
}

function oxext_check_is_single(){
	return is_single() || is_singular() || is_page();
}

function oxext_taxonomy_type_archive( $value, $operator ): bool {
	if( is_archive() ){
		$post_terms     = wp_get_post_terms( get_the_ID(), $value );
		if( $operator === '==' ){
			return ! empty( $post_terms );
		} else {
			return empty( $post_terms );
		}
	}
	return false;
}

function oxext_search_has_results( $value, $operator ): bool {
	if( get_search_query() ) {
		global $wp_query;
		if( $wp_query->posts && $value === 'True' ||
			! $wp_query->posts && $value === 'False' ) {
			return true;
		}
	}
	return false;
}
