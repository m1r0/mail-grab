<?php

namespace m1r0\MailGrab;

use WP_Query;

defined( 'ABSPATH' ) || exit;

/**
 * The admin search logic.
 *
 * @package m1r0\MailGrab
 */
class Search {

	/**
	 * Register actions, filters, etc...
	 *
	 * @return void
	 */
	public function initialize() {
		add_filter( 'posts_where',    array( $this, 'posts_where' ),    10, 2 );
		add_filter( 'posts_join',     array( $this, 'posts_join' ),     10, 2 );
		add_filter( 'posts_distinct', array( $this, 'posts_distinct' ), 10, 2 );
	}

	/**
	 * Modify the search query "where".
	 * Add search by meta.
	 *
	 * @param string   $where The WHERE clause of the query.
	 * @param WP_Query $query The WP_Query instance.
	 *
	 * @return string
	 */
	public function posts_where( $where, WP_Query $query ) {
		global $wpdb;

		if ( $this->is_search_query( $query ) ) {
			$where = preg_replace(
				"/\(\s*{$wpdb->posts}.post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
				"({$wpdb->posts}.post_title LIKE $1) OR ({$wpdb->postmeta}.meta_value LIKE $1)",
				$where
			);
		}

		return $where;
	}

	/**
	 * Modify the search query "join".
	 *
	 * @param string   $join  The JOIN clause of the query.
	 * @param WP_Query $query The WP_Query instance.
	 *
	 * @return string
	 */
	public function posts_join( $join, WP_Query $query ) {
		global $wpdb;

		if ( $this->is_search_query( $query ) ) {
			$join .= " LEFT JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id ";
		}

		return $join;
	}

	/**
	 * Modify the search query "distinct".
	 *
	 * @param string   $distinct The DISTINCT clause of the query.
	 * @param WP_Query $query    The WP_Query instance.
	 *
	 * @return string
	 */
	public function posts_distinct( $distinct, WP_Query $query ) {
		if ( $this->is_search_query( $query ) ) {
			return 'DISTINCT';
		}

		return $distinct;
	}

	/**
	 * Check if this is the correct search query.
	 *
	 * @param WP_Query $query The WP_Query instance.
	 *
	 * @return bool
	 */
	protected function is_search_query( WP_Query $query ) {
		$is_search_query = is_admin()
			&& $query->is_main_query()
			&& $query->is_search()
			&& $query->get( 'post_type' ) === MailGrab::POST_TYPE;

		return apply_filters( 'mlgb_is_search_query', $is_search_query, $query );
	}

}
