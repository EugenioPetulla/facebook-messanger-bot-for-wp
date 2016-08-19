<?php
/*
Plugin Name: Facebook Messenger Bot for WP
Description: A complete and configurable Messenger bot for WordPress and/or Woocommerce
Plugin URI:
Author: iGenius
Author URI:
Version: 1.0
License: GPLv3
 */
if (!defined('ABSPATH')) {
	exit;
}

class Mess_Bot {
	function __construct() {
		// Plugin Namespace
		$this->namespace = 'mess-bot';
		// Verify Token
		$this->verify_token = '';
		// Facebook URL for post
		$this->graph_api_url = '';
		// Facebook Page access token
		$this->access_token = '';

		// REST API ROUTE
		add_action('rest_api_init', array($this, 'register_routes'));
	}
	function register_routes() {
		register_rest_route($this->namespace, '/bot', array(
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array($this, 'get'),
				'permission_callback' => array($this, 'verify_request'),
			),
			array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => array($this, 'post'),
			),
		));
	}
	function verify_request($request) {
		$params = $request->get_query_params();
		if ($params && isset($params['hub_challenge']) && $params['hub_verify_token'] == $this->verify_token) {
			return true;
		}
		return false;
	}
	function get($request) {
		$params = $request->get_query_params();
		echo $params['hub_challenge'];
		die();
	}
	function post($request) {
		$params = $request->get_params();
		if ($params && $params['entry']) {
			foreach ((array) $params['entry'] as $entry) {
				if ($entry && $entry['messaging']) {
					foreach ((array) $entry['messaging'] as $message) {
						$this->send_message($message);
					}
				}
			}
		}
		die();
	}
	function send_message($message) {
		// Check for text
		if (!isset($message['message']['text'])) {
			return;
		}
		$sender_id = $message['sender']['id'];
		$text = strtolower($message['message']['text']);
		// Graph URL With Token
		$graph = $this->graph_api_url . $this->access_token;

		switch ($text) {
			case 'news':
			case 'post':
				$template = array(
				'attachment' => array(
					'type' => 'template',
					'payload' => array(
						'template_type' => 'generic',
						'elements' => $this->get_posts_elements(),
					),
				),);
				break;
			
			case '/info':
			case '/help':
			case 'info':
			case 'help':
				// Text Template
				$template = array('text' => 'For latest 10 post type news or post');
				break;

			default:
				
				break;
		}
		$data = array(
			'body' => array(
				'recipient' => array('id' => $sender_id),
				'message' => $template,
			),
		);
		$response = wp_remote_post($graph, $data);

	}

	function get_posts_elements() {
		$args = array(
			'posts_per_page' => 10,
			'post_type' => 'post',
		);
		$posts = get_posts($args);
		$elements = [];
		if ($posts) {
			foreach ($posts as $post) {
				if(has_post_thumbnail($post->ID)){
					$img = get_the_post_thumbnail_url($post->ID);
				}
				else {
					$img = 'https://unsplash.it/900/?random';
				}
				$data = array(
					'title' => $this->truncate($post->post_title, 45),
					'image_url' => $img,
					'subtitle' => $this->truncate($post->post_content, 80),
					'buttons' => array(
						array(
							'type' => 'web_url',
							'url' => get_permalink($post),
							'title' => 'Read This',
						),
					),
				);
				$elements[] = $data;
			}
		}
		return $elements;
	}
	function truncate($text, $length) {
		$length = abs((int) $length);
		$text = trim(preg_replace("/&#?[a-z0-9]{2,8};/i", "", $text));
		if (strlen($text) > $length) {
			$text = preg_replace("/^(.{1,$length})(\s.*|$)/s", '\\1...', $text);
		}
		return ($text);
	}
}
new Mess_Bot;