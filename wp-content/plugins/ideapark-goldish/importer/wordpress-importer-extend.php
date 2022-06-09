<?php

class WP_Importer_Base {
	var $message = '';
	var $step_total = 0;
	var $step_done = 0;
}

class WP_Importer_Extend extends WP_Import {
	var $message = '';
	var $step_total = 1;
	var $step_done = 0;
	var $_posts = [];
	var $start_timer = 0;

	private function getmicrotime() {
		list( $usec, $sec ) = explode( " ", microtime() );

		return ( (float) $usec + (float) $sec );
	}

	public function importing() {
		$this->start_timer = $this->getmicrotime();
		add_filter( 'import_post_meta_key', [ $this, 'is_valid_meta_key' ] );
		add_filter( 'http_request_timeout', [ &$this, 'bump_request_timeout' ] );
		wp_defer_term_counting( true );
		wp_defer_comment_counting( true );

		$processed = 0;
		wp_suspend_cache_invalidation( true );
		foreach ( $this->_posts as $post ) {
			$processed ++;
			$this->posts = [ $post ];
			$this->process_posts();
			if ( $post['post_type'] == 'attachment' ) {
				$this->step_done ++;
				if ( ! $this->message ) {
					$this->message = __( 'Importing media files', 'ideapark-goldish' );
				}
				if ( $this->getmicrotime() - $this->start_timer >= 20 ) {
					break;
				}
			} elseif ( $post['post_type'] == 'product' || $post['post_type'] == 'post' || $post['post_type'] == 'page' ) {
				$this->step_done ++;
				if ( ! $this->message ) {
					$this->message = __( 'Importing ', 'ideapark-goldish' ) . $post['post_type'] . ': ' . $post['post_title'];
				}
			}
			if ( memory_get_usage() / ( 1024 * 1024 ) > str_replace( 'M', '', ini_get( 'memory_limit' ) ) * 0.8 ) {
				break;
			}
		}
		$this->_posts = array_slice( $this->_posts, $processed );
		if ( count( $this->_posts ) ) {
			return true;
		} else {
			return false;
		}

	}

	public function import_start( $file ) {

		$f  = implode( '_', [ 'ideapark', 'api', 'theme', 'get', 'file' ] );
		$fn = $f( $file );

		if ( is_wp_error( $fn ) ) {
			return $fn;
		} else {
			$result = unzip_file( $fn, IDEAPARK_UPLOAD_DIR );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
			if ( is_file( $fn2 = IDEAPARK_UPLOAD_DIR . 'content.xml' ) ) {
				ideapark_delete_file( $fn );
				$fn = $fn2;
			} else {
				return new WP_Error( 'ideapark_file_not_found', __( 'File content.xml not found', 'ideapark-goldish' ) );
			}
		}

		parent::import_start( $fn );

		if ( $this->fetch_attachments || true ) {
			$this->_posts = $this->posts;

			$this->posts = [];

			foreach ( $this->_posts as $post ) {
				if ( $post['post_type'] == 'attachment' || $post['post_type'] == 'product' || $post['post_type'] == 'post' || $post['post_type'] == 'page' ) {
					$this->step_total ++;
				}
			}
		}

		$this->get_author_mapping();

	}

	public function import_terms() {
		add_filter( 'import_post_meta_key', [ $this, 'is_valid_meta_key' ] );
		add_filter( 'http_request_timeout', [ &$this, 'bump_request_timeout' ] );
		wp_defer_term_counting( true );
		wp_defer_comment_counting( true );

		wp_suspend_cache_invalidation( true );
		$this->process_categories();
		$this->process_tags();
		$this->process_terms();
		wp_suspend_cache_invalidation( false );
	}

	public function import_end() {

		$this->backfill_parents();
		$this->backfill_attachment_urls();
		$this->remap_featured_images();

		parent::import_end();
	}
}