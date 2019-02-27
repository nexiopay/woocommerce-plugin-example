<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return apply_filters(
	'cms_nexio_settings',
	array(
		'enabled'                       => array(
			'title'       => __( 'Enable/Disable', 'cms-gateway-nexio' ),
			'label'       => __( 'Enable nexio', 'cms-gateway-nexio' ),
			'type'        => 'checkbox',
			'description' => '',
			'default'     => 'no',
		),
		'title'                         => array(
			'title'       => __( 'Title', 'cms-gateway-nexio' ),
			'type'        => 'text',
			'description' => __( 'This controls the title which the user sees during checkout.', 'cms-gateway-nexio' ),
			'default'     => __( 'Credit Card(Nexio)', 'cms-gateway-nexio' ),
			'desc_tip'    => true,
		),
		'description'                   => array(
			'title'       => __( 'Description', 'cms-gateway-nexio' ),
			'type'        => 'text',
			'description' => __( 'This controls the description which the user sees during checkout.', 'cms-gateway-nexio' ),
			'default'     => __( 'Pay with your credit card via nexio.', 'cms-gateway-nexio' ),
			'desc_tip'    => true,
		),
		'api_url'                       => array(
			'title'       => __( 'API URL', 'cms-gateway-nexio' ),
			'label'       => __( 'URL of API server', 'cms-gateway-nexio' ),
			'type'        => 'text',
			'description' => __( 'The URL of Nexio API server', 'cms-gateway-nexio' ),
			'default'     => 'https://api.nexiopaysandbox.com/',
			'desc_tip'    => true,
		),
		'user_name'                       => array(
			'title'       => __( 'User Name', 'cms-gateway-nexio' ),
			'label'       => __( 'User name of Nexio account', 'cms-gateway-nexio' ),
			'type'        => 'text',
			'description' => __( 'User name of your Nexio account', 'cms-gateway-nexio' ),
			'default'     => '',
			'desc_tip'    => true,
		),
		'password'                       => array(
			'title'       => __( 'Password', 'cms-gateway-nexio' ),
			'label'       => __( 'Password of Nexio account', 'cms-gateway-nexio' ),
			'type'        => 'password',
			'description' => __( 'Passwrod of your Nexio account', 'cms-gateway-nexio' ),
			'default'     => '',
			'desc_tip'    => true,
		),
	)
);
