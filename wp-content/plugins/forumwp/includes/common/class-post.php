<?php
namespace fmwp\common;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\common\Post' ) ) {


	/**
	 * Class Post
	 *
	 * @package fmwp\common
	 */
	class Post {


		/**
		 * Post constructor.
		 */
		function __construct() {
		}


		/**
		 * @param string $content
		 * @param string $post_type
		 *
		 * @return array
		 */
		function prepare_content( $content, $post_type ) {
			$post_content = trim( $content );
			$origin_content = $post_content;

			if ( $post_content ) {
				$safe_content = $this->sanitize_content( $post_content );

				$safe_content = FMWP()->common()->mention_links( $safe_content, [ 'post_type' => $post_type ] );

				// shared a link
				$post_content = FMWP()->parse_embed( $safe_content );
			}

			return apply_filters( 'fmwp_prepare_content', [ $origin_content, $post_content ], $post_type );
		}


		/**
		 * Check if post exists
		 *
		 * @param int $post_id
		 *
		 * @return bool
		 */
		function exists( $post_id ) {
			if ( empty( $post_id ) ) {
				return false;
			}

			$post = get_post( $post_id );

			if ( empty( $post ) || is_wp_error( $post ) ) {
				return false;
			}

			return true;
		}


		/**
		 * @param $post_id
		 *
		 * @return bool
		 */
		function is_trashed( $post_id ) {
			$post = get_post( $post_id );
			if ( empty( $post ) || is_wp_error( $post ) ) {
				return true;
			}

			if ( $post->post_status === 'trash' ) {
				return true;
			}

			return false;
		}


		/**
		 * @param int|\WP_Post $post
		 *
		 * @return bool|null
		 */
		function is_locked( $post ) {
			return false;
		}


		/**
		 * @param int|\WP_Post $post
		 *
		 * @return bool|null
		 */
		function is_spam( $post ) {
			$spam = false;
			if ( is_numeric( $post ) ) {
				$post = get_post( $post );

				if ( empty( $post ) || is_wp_error( $post ) ) {
					return $spam;
				}
			}

			if ( $post->post_type == 'fmwp_reply' ) {
				$spam = FMWP()->common()->reply()->is_spam( $post );
			} elseif ( $post->post_type == 'fmwp_topic' ) {
				$spam = FMWP()->common()->topic()->is_spam( $post );
			}

			return $spam;
		}


		/**
		 * Sanitize post content
		 *
		 * @param $content
		 *
		 * @return string
		 */
		function sanitize_content( $content ) {
			if ( FMWP()->options()->get( 'raw_html_enabled' ) ) {
				$content = stripslashes( $content );
			} else {
				$content = wp_kses( $content, 'post' );
			}

			return $content;
		}


		/**
		 * Get File Name without path and extension
		 *
		 * @param $file
		 *
		 * @return mixed|string
		 */
		function get_template_name( $file ) {
			$file = basename( $file );
			$file = preg_replace( '/\\.[^.\\s]{3,4}$/', '', $file );
			return $file;
		}


		/**
		 * Get Templates
		 *
		 * @param string|\WP_Post $post
		 * @return mixed
		 */
		function get_templates( $post ) {

			if ( is_string( $post ) && in_array( $post, [ 'fmwp_topic', 'fmwp_forum' ] ) ) {
				if ( $post == 'fmwp_forum' ) {
					$prefix = 'Forum';
				} elseif ( $post == 'fmwp_topic' ) {
					$prefix = 'Topic';
				}
			} elseif ( is_object( $post ) ) {
				if ( isset( $post->post_type ) && $post->post_type == 'fmwp_forum' ) {
					$prefix = 'Forum';
				} elseif ( isset( $post->post_type ) && $post->post_type == 'fmwp_topic' ) {
					$prefix = 'Topic';
				}
			}

			if ( ! isset( $prefix ) ) {
				return [];
			}

			$dir = FMWP()->theme_templates;

			$templates = [];
			if ( is_dir( $dir ) ) {
				$handle = opendir( $dir );
				while ( false !== ( $filename = readdir( $handle ) ) ) {
					if ( $filename === '.' || $filename === '..' ) {
						continue;
					}

					$file_path = wp_normalize_path( trailingslashit( $dir ) . $filename );
					if( ! is_file( $file_path ) || ! ( $source = file_get_contents( $file_path ) ) ){
						continue;
					}

					$clean_filename = $this->get_template_name( $filename );

					$tokens = @\token_get_all( $source );
					$comment = [
						T_COMMENT, // All comments since PHP5
						T_DOC_COMMENT, // PHPDoc comments
					];
					foreach ( $tokens as $token ) {
						if ( in_array( $token[0], $comment ) && strstr( $token[1], '/* ' . $prefix . ' Template:' ) ) {
							$txt = $token[1];
							$txt = str_replace('/* ' . $prefix . ' Template: ', '', $txt );
							$txt = str_replace(' */', '', $txt );
							$templates[ $clean_filename ] = $txt;
						}
					}
				}
				closedir( $handle );

				asort( $templates );
			}

			return $templates;
		}


		/**
		 * @param int $post_id
		 *
		 * @return bool
		 */
		function is_pending( $post_id ) {
			$post = get_post( $post_id );
			if ( empty( $post ) || is_wp_error( $post ) ) {
				return true;
			}

			if ( $post->post_status === 'pending' ) {
				return true;
			}

			return false;
		}


		/**
		 * @param int|\WP_Post $post
		 *
		 * @return int
		 */
		function get_author_id( $post ) {
			if ( is_numeric( $post ) ) {
				$post = get_post( $post );
			}

			if ( ! empty( $post ) && ! is_wp_error( $post ) ) {
				return $post->post_author;
			}

			return false;
		}
	}
}