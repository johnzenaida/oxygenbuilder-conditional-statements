<?php

/*
Plugin Name: Oxygen Conditions Ext
Plugin URI: https://agaveplugins.com
Description: A brief description of the Plugin.
Version: 1.1.2
Author: Andrey and John
Author URI: https://agaveplugins.com
License: GLPv2 https://www.gnu.org/licenses/gpl-2.0.html
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

		$pages = get_posts( array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => - 1
		) );

		$page_options = array();
		foreach ( $pages as $page ) {
			$page_options[ $page->ID ] = $page->post_title . '(' . $page->ID . ')';
		}

		oxygen_vsb_register_condition(
			'Page parent',
			array(
				'options' => $page_options,
				'custom'  => true,
				'keys'    => array_keys( $page_options )
			),
			array( '==', '!=' ),
			'oxext_is_post_child',
			'Page'
		);
		oxygen_vsb_register_condition(
			'Page ancestor',
			array(
				'options' => $page_options,
				'custom'  => true,
				'keys'    => array_keys( $page_options )
			),
			array( '==', '!=' ),
			'oxext_is_post_grandchild',
			'Page'
		);

		$args      = array(
			'hierarchical' => true,
			'_builtin'     => false
		);
		$posttypes = get_post_types( $args );

		$choices = array();
		if ( $posttypes ) {
			foreach ( $posttypes as $posttype ) {
				if ( $posttype !== 'acf' && $posttype !== 'post' && $posttype !== 'page' ) {
					$args        = array(
						'post_type'      => $posttype,
						'posts_per_page' => - 1,
						'post_status'    => 'publish'
					);
					$customposts = get_posts( $args );
					if ( $customposts ) {
						foreach ( $customposts as $custompost ) {
							$choices[ $custompost->ID ] = $custompost->post_title . ' (' . $custompost->ID . ')' ;
						}
					}
				}
			}
		}

		oxygen_vsb_register_condition(
			'CPT parent',
			array(
				'options' => $choices,
				'keys'    => array_keys( $choices ),
				'custom'  => true
			),
			array(
				'==',
				'!='
			),
			'oxext_is_post_child',
			'Custom Post Type'
		);

		oxygen_vsb_register_condition(
			'CPT ancestor',
			array(
				'options' => $choices,
				'keys'    => array_keys( $choices ),
				'custom'  => true
			),
			array( '==', '!=' ),
			'oxext_is_post_grandchild',
			'Custom Post Type'
		);

		$options = array();
		foreach ( $posttypes as $type ) {
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
			'keys'    => array_keys( $taxonomies ),
			'custom'  => true
		),
			array( '==', '!=' ), 'oxext_post_taxonomy', 'Post' );

		oxygen_vsb_register_condition( 'Taxonomy type archive', array(
			'options' => array_values( $taxonomies ),
			'keys'    => array_keys( $taxonomies ),
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

		$post_id   = get_the_ID();
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
	if ( oxext_check_is_single() ) {
		$post_terms = wp_get_post_terms( get_the_ID(), $value );
		if ( $operator === '==' ) {
			return ! empty( $post_terms );
		} else {
			return empty( $post_terms );
		}

	}

	return false;
}

function oxext_check_is_single() {
	return is_single() || is_singular() || is_page();
}

function oxext_taxonomy_type_archive( $value, $operator ): bool {
	if ( is_archive() ) {
		$post_terms = wp_get_post_terms( get_the_ID(), $value );
		if ( $operator === '==' ) {
			return ! empty( $post_terms );
		} else {
			return empty( $post_terms );
		}
	}

	return false;
}

function oxext_is_post_child( $value, $operator ): bool {
	$post          = get_post();
	$selected_post = (int) $value;
	$match         = false;

	// post parent
	$post_parent = $post->post_parent;

	if ( $operator === "==" ) {
		$match = ( $post_parent == $selected_post );
	} elseif ( $operator === "!=" ) {
		$match = ( $post_parent != $selected_post );
	}

	return $match;
}

function oxext_is_post_grandchild( $value, $operator ): bool {
	$post          = get_post();
	$selected_post = (int) $value;
	$match         = false;

	do {
		// post parent
		$post_parent = $post->post_parent;

		if ( $operator === "==" ) {
			$match = ( $post_parent == $selected_post );
		} elseif ( $operator === "!=" ) {
			$match = ( $post_parent != $selected_post );
		}
		if ( ! $match && $post_parent ) {
			$post = get_post( $post_parent );
		}
	} while ( $post_parent && ! $match );

	return $match;
}
