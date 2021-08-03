<?php
namespace fmwp\common;


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'fmwp\common\Mail' ) ) {


	/**
	 * Class Mail
	 *
	 * @package fmwp\common
	 */
	class Mail {


		/**
		 * @var array
		 */
		var $paths = [];


		/**
		 * Mail constructor.
		 */
		function __construct() {
		}


		/**
		 * Check blog ID on multisite, return '' if single site
		 *
		 * @return string
		 */
		function get_blog_id() {
			$blog_id = '';
			if ( is_multisite() ) {
				$blog_id = DIRECTORY_SEPARATOR . get_current_blog_id();
			}

			return $blog_id;
		}


		/**
		 * Locate a template and return the path for inclusion.
		 *
		 * @param string $template_name
		 * @return string
		 */
		function locate_template( $template_name ) {
			// check if there is template at theme folder
			$blog_id = $this->get_blog_id();

			// get template file from the current theme
			$template = locate_template( [
				trailingslashit( 'forumwp' . DIRECTORY_SEPARATOR . 'emails' . $blog_id ) . $template_name . '.php',
				trailingslashit( 'forumwp' . DIRECTORY_SEPARATOR . 'emails' ) . $template_name . '.php'
			] );

			// if there isn't template at theme folder get template file from plugin dir
			if ( ! $template ) {
				$path = ! empty( $this->paths[ $template_name ] ) ? $this->paths[ $template_name ] : fmwp_path . 'templates' . DIRECTORY_SEPARATOR . 'emails';
				$template = trailingslashit( $path ) . $template_name . '.php';
			}

			// Return what we found.
			return apply_filters( 'fmwp_locate_email_template', $template, $template_name );
		}


		/**
		 * @param $slug
		 * @param $args
		 *
		 * @return bool|string
		 */
		function get_template( $slug, $args = [] ) {
			$located = wp_normalize_path( $this->locate_template( $slug ) );

			$located = apply_filters( 'fmwp_email_template_path', $located, $slug, $args );

			if ( ! file_exists( $located ) ) {
				_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '2.1' );
				return false;
			}

			ob_start();

			do_action( 'fmwp_before_email_template_part', $slug, $located, $args );

			include( $located );

			do_action( 'fmwp_after_email_template_part', $slug, $located, $args );

			return ob_get_clean();
		}


		/**
		 * Method returns expected path for template
		 *
		 * @access public
		 *
		 * @param string $location
		 * @param string $template_name
		 *
		 * @return string
		 */
		function get_template_file( $location, $template_name ) {
			$template_name_file = $this->get_template_filename( $template_name );

			$template_path = '';
			switch( $location ) {
				case 'theme':
					//save email template in blog ID folder if we use multisite
					$blog_id = $this->get_blog_id();

					$template_path = trailingslashit( get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'forumwp' . DIRECTORY_SEPARATOR . 'emails' . $blog_id ). $template_name_file . '.php';
					break;
				case 'plugin':
					$path = ! empty( $this->paths[ $template_name ] ) ? $this->paths[ $template_name ] : fmwp_path . 'templates' . DIRECTORY_SEPARATOR . 'emails';
					$template_path = trailingslashit( $path ) . $template_name . '.php';
					break;
			}

			return wp_normalize_path( $template_path );
		}


		/**
		 * @param string $template_name
		 *
		 * @return string
		 */
		function get_template_filename( $template_name ) {
			return apply_filters( 'fmwp_change_email_template_file', $template_name );
		}


		/**
		 * Ajax copy template to the theme
		 *
		 * @param string $template
		 * @return bool
		 */
		function copy_template( $template ) {
			$in_theme = $this->template_in_theme( $template );
			if ( $in_theme ) {
				return false;
			}

			$plugin_template_path = $this->get_template_file( 'plugin', $template );
			$theme_template_path = $this->get_template_file( 'theme', $template );

			$theme_dir_path = dirname( $theme_template_path );
			if ( ! is_dir( $theme_dir_path ) ) {
				mkdir( $theme_dir_path, 0774, true ); // third argument enables recursive mode
			}

			return file_exists( $plugin_template_path ) && copy( $plugin_template_path, $theme_template_path );
		}


		/**
		 * Locate a template and return the path for inclusion.
		 *
		 * @access public
		 * @param string $template_name
		 * @return string
		 */
		function template_in_theme( $template_name ) {
			$template_name_file = $this->get_template_filename( $template_name );

			$blog_id = $this->get_blog_id();

			// check if there is template at theme blog ID folder
			$template = locate_template( [
				trailingslashit( 'forumwp' . DIRECTORY_SEPARATOR . 'emails' . $blog_id ) . $template_name_file . '.php'
			] );

			// Return what we found.
			return ! $template ? false : true;
		}


		/**
		 * @param $slug
		 * @param $args
		 * @return bool|string
		 */
		function get_email_template( $slug, $args = [] ) {
			$located = $this->locate_template( $slug );

			if ( ! file_exists( $located ) ) {
				_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '2.1' );
				return false;
			}

			ob_start();

			include( $located );

			return ob_get_clean();
		}


		/**
		 * Prepare email template to send
		 *
		 * @param $slug
		 * @param $args
		 * @return mixed|string
		 */
		function prepare_template( $slug, $args = [] ) {
			$args['slug'] = $slug;

			ob_start();

			FMWP()->get_template_part( 'emails/base_wrapper', $args );

			$message = ob_get_clean();

			$message = apply_filters( 'fmwp_email_template_content', $message, $slug, $args );

			// Convert tags in email template
			$message = $this->replace_placeholders( $message, $args );
			return $message;
		}


		/**
		 * Send Email function
		 *
		 * @param string $email
		 * @param null $template
		 * @param array $args
		 */
		function send( $email, $template, $args = [] ) {
			if ( ! is_email( $email ) ) {
				return;
			}

			if ( FMWP()->options()->get( $template . '_on' ) != 1 ) {
				return;
			}

			$disable = apply_filters( 'fmwp_disable_email_notification_by_hook', false, $email, $template, $args );
			if ( $disable ) {
				return;
			}

			$attachments = null;
			$content_type = apply_filters( 'fmwp_email_template_content_type', 'text/html', $template, $args, $email );

			$headers = 'From: '. FMWP()->options()->get( 'mail_from' ) .' <'. FMWP()->options()->get( 'mail_from_addr' ) .'>' . "\r\n";
			$headers .= "Content-Type: {$content_type}\r\n";

			$subject = apply_filters( 'fmwp_email_send_subject', FMWP()->options()->get( $template . '_sub' ), $template, $email );
			$subject = $this->replace_placeholders( $subject, $args );
			$subject = stripslashes( $subject );

			$message = $this->prepare_template( $template, $args );

			// Send mail
			wp_mail( $email, $subject, $message, $headers, $attachments );

			do_action( 'fmwp_after_email_sending', $email, $template, $args );
		}


		/**
		 * Replace placeholders
		 *
		 * @param $content
		 * @param $args
		 *
		 * @return mixed
		 */
		function replace_placeholders( $content, $args ) {
			$tags = array_map( function( $item ) {
				return '{' . $item . '}';
			}, array_keys( $args ) );
			$tags_replace = array_values( $args );

			$content = str_replace( $tags, $tags_replace, $content );
			return $content;
		}
	}
}