<?php

class MeowPro_WPMC_CLI extends WP_CLI_Command {

	public function __construct() {
	}

	public function issues() {
		global $wpdb;
		$table_name = $wpdb->prefix . "mclean_scan";
		$items = $wpdb->get_results( "SELECT id, type, postId, path, size, ignored, deleted, issue
			FROM $table_name
			WHERE ignored = 0 AND deleted = 0
			ORDER BY path, time
			DESC", OBJECT );
		$issues_count = count( $items );
		if ( !$issues_count ) {
			WP_CLI::line( "No issues found." );
		}
		else {
			WP_CLI::line( "Found ${issues_count} issues:" );
			foreach ( $items as $item ) {
				WP_CLI::line( "- Media {$item->postId} ({$item->path}): {$item->issue}" );
			}
		}
	}

	public function delete() {
		global $wpdb;
		$table_name = $wpdb->prefix . "mclean_scan";
		$items = $wpdb->get_results( "SELECT id, type, postId, path, size, ignored, deleted, issue
			FROM $table_name
			WHERE ignored = 0 AND deleted = 0
			ORDER BY path, time
			DESC", OBJECT );
		$issues_count = count( $items );
		if ( !$issues_count ) {
			WP_CLI::line( "No issues found." );
		}
		else {
			global $wpmc;
			foreach ( $items as $item ) {
				if ( $wpmc->delete( $item->id ) )
					WP_CLI::line( "- Deleted Media {$item->postId} ({$item->path}): {$item->issue}" );
				else
				WP_CLI::line( "- Could not delete Media {$item->postId} ({$item->path}): {$item->issue}" );
			}
		}
	}

	public function trash() {
		global $wpdb;
		$table_name = $wpdb->prefix . "mclean_scan";
		$items = $wpdb->get_results( "SELECT id, type, postId, path, size, ignored, deleted, issue
			FROM $table_name
			WHERE ignored = 0 AND deleted = 1
			ORDER BY path, time
			DESC", OBJECT );
		$issues_count = count( $items );
		if ( !$issues_count ) {
			WP_CLI::line( "No issues found." );
		}
		else {
			global $wpmc;
			foreach ( $items as $item ) {
				if ( $wpmc->delete( $item->id ) )
					WP_CLI::line( "- Trashed Media {$item->postId} ({$item->path}): {$item->issue}" );
				else
				WP_CLI::line( "- Could not trash Media {$item->postId} ({$item->path}): {$item->issue}" );
			}
		}
	}

	public function scan( $args ) {
		$method = get_option( 'wpmc_method', 'media' ) === 'media' ? 'Media Library' : 'Filesystem';
		$check_library = get_option(' wpmc_media_library', true ) ? 'yes' : 'no';

		$check_content = false;
		if ( $method === 'media' ) {
			$check_content = get_option( 'wpmc_content', true ) ? 'yes' : 'no';
		}
		else if ( $method === 'files' ) {
			$check_content = get_option( 'wpmc_filesystem_content', false ) ? 'yes' : 'no';
		}

		$check_live_content = get_option( 'wpmc_live_content', false ) ? 'yes' : 'no';

		if ( $args && in_array( 'filesystem', $args ) ) {
			$method = 'Filesystem';
		}
		if ( $args && in_array( 'media', $args ) ) {
			$method = 'Media Library';
		}
		if ( $args && in_array( 'check-media', $args ) ) {
			$check_library = 'yes';
		}
		if ( $args && in_array( 'uncheck-media', $args ) ) {
			$check_library = 'no';
		}
		if ( $args && in_array( 'check-content', $args ) ) {
			$check_content = 'yes';
		}
		if ( $args && in_array( 'uncheck-content', $args ) ) {
			$check_content = 'no';
		}

		WP_CLI::line( "* Method        : ${method}" );
		WP_CLI::line( "* Check Library : {$check_library}" );
		WP_CLI::line( "* Check Content : {$check_content}" );
		WP_CLI::line( "* Check Live    : {$check_live_content}" );
		WP_CLI::line();

		global $wpmc;
		$wpmc->catch_timeout = false;
		$wpmc->reset_issues();

		// Check Content
		if ( $check_content === 'yes' ) {
			$progress = \WP_CLI\Utils\make_progress_bar( 'Read content:   ', 100 );
			$finished = false;
			$limit = 0;
			$limitSize = 1000;
			while ( !$finished ) {
				$finished = $wpmc->engine->extractRefsFromContent( $limit, $limitSize );
				for ( $c = 0; $c < $limitSize; $c++ )
					$progress->tick();
				$limit += $limitSize;
			}
			$progress->finish();
		}

		// Check Library
		if ( $check_library === 'yes' ) {
			$progress = \WP_CLI\Utils\make_progress_bar( 'Read library: ', 100 );
			$finished = false;
			$limit = 0;
			$limitSize = 1000;
			while ( !$finished ) {
				$finished = $wpmc->engine->extractRefsFromLibrary( $limit, $limitSize );
				for ( $c = 0; $c < $limitSize; $c++ )
					$progress->tick();
				$limit += $limitSize;
			}
			$progress->finish();
		}

		// Method: Filesystem
		$files = array();
		if ( $method === 'Filesystem' ) {
			$progress = \WP_CLI\Utils\make_progress_bar( 'Read files:   ', 100 );
			$dirs = array( '.' );
			while ( count( $dirs ) > 0 ) {
				$dir = array_pop( $dirs );
				$new_files = $wpmc->engine->get_files( $dir );
				foreach ( $new_files as $file ) {
					if ( $file['type'] === 'dir' )
						array_push( $dirs, $file['path'] );
					else {
						array_push( $files, $file['path'] );
					}
				}
			}
			$progress->finish();

			// Final Check
			$filesCount = count( $files );
			$progress = \WP_CLI\Utils\make_progress_bar( 'Check usage:  ', $filesCount );
			do_action( 'wpmc_check_file_init' );
			foreach ( $files as $file ) {
				$wpmc->engine->check_file( $file );
				$progress->tick();
			}
			$progress->finish();
		}

		else {
			// Method: Media Library
			$progress = \WP_CLI\Utils\make_progress_bar( 'Read Media Library:', 100 );
			$finished = false;
			$limit = 0;
			$limitSize = 1000;
			$mediaIds = array();
			while ( !$finished ) {
				$newMediaIds = $wpmc->engine->get_media_entries( $limit, $limitSize );
				$newMediaIdsCount = count( $newMediaIds );
				for ( $c = 0; $c < $newMediaIdsCount; $c++ ) {
					array_push( $mediaIds, $newMediaIds[$c] );
					$progress->tick();
				}
				$limit += $limitSize;
				$finished = $newMediaIdsCount < $limitSize;
			}
			$progress->finish();

			// Final Check
			$mediaCount = count( $mediaIds );
			$progress = \WP_CLI\Utils\make_progress_bar( 'Check usage:       ', $mediaCount );
			foreach ( $mediaIds as $mediaId ) {
				$wpmc->check_media( $mediaId );
				$progress->tick();
			}
			$progress->finish();
		}

		WP_CLI::line();
		$this->issues();

	}

}

?>