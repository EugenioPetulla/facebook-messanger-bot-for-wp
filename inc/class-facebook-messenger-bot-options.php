<?php

	if (!defined('ABSPATH')) {
	exit;
	}


/**
 * Facebook_Messenger_Bot_Options class.
 */
class Facebook_Messenger_Bot_Options{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            __( 'Settings Facebook Messenger BOT', 'text-domain'),
            __( 'Settings Facebook Messenger BOT', 'text-domain'),
            'manage_options',
            'facebook-messenger-bot',
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'facebook_messenger_bot' );
        ?>
        <div class="wrap">
            <h2><?php _e( 'Settings Facebook Messenger BOT', 'text-domain') ?></h2>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'facebook_messenger_bot_group' );
                do_settings_sections( 'facebook-messenger-bot' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'facebook_messenger_bot_group', // Option group
            'facebook_messenger_bot', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'facebook_settings', // ID
            __( 'Facebook Settings', 'text-domain' ), // Title
            array( $this, 'print_facebook_settings' ), // Callback
            'facebook-messenger-bot' // Page
        );

        add_settings_field(
            'verify_token', // ID
            __( 'Facebook Verify Token', 'text-domain' ), // Title
            array( $this, 'verify_token_callback' ), // Callback
            'facebook-messenger-bot', // Page
            'facebook_settings' // Section
        );

        add_settings_field(
            'access_token', // ID
            __( 'Facebook Access Token', 'text-domain' ), // Title
            array( $this, 'access_token_callback' ), // Callback
            'facebook-messenger-bot', // Page
            'facebook_settings' // Section
        );


        add_settings_field(
            'graph_api_url',
             __( 'Facebook Graph API URL', 'text-domain' ),
            array( $this, 'graph_api_url_callback' ),
            'facebook-messenger-bot', // Page
            'facebook_settings' // Section
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['verify_token'] ) )
            $new_input['verify_token'] = ( $input['verify_token'] );

        if( isset( $input['access_token'] ) )
            $new_input['access_token'] = ( $input['access_token'] );

        if( isset( $input['graph_api_url'] ) )
            $new_input['graph_api_url'] = esc_url_raw( $input['graph_api_url'] );

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_facebook_settings()
    {
        print _e( 'Insert Facebook Settings', 'text-domain');
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function verify_token_callback()
    {
        printf(
            '<input type="text" id="verify_token" name="facebook_messenger_bot[verify_token]" value="%s" />',
            isset( $this->options['verify_token'] ) ? esc_attr( $this->options['verify_token']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function access_token_callback()
    {
        printf(
            '<input type="text" id="access_token" name="facebook_messenger_bot[access_token]" value="%s" />',
            isset( $this->options['access_token'] ) ? esc_attr( $this->options['access_token']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function graph_api_url_callback()
    {
        printf(
            '<input type="text" id="graph_api_url" name="facebook_messenger_bot[graph_api_url]" value="%s" />',
            isset( $this->options['graph_api_url'] ) ? esc_url_raw( $this->options['graph_api_url']) : ''
        );
    }
}

if( is_admin() )
    $facebook_messenger_bot_options = new Facebook_Messenger_Bot_Options();