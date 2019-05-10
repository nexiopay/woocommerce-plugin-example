<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return apply_filters(
	'cms_nexio_settings',
	array(
		'enabled'                       => array(
			'title'       => __( 'Enable/Disable', 'cms-gateway-nexio' ),
			'label'       => __( 'Enable Nexio', 'cms-gateway-nexio' ),
			'type'        => 'checkbox',
			'description' => '',
			'default'     => 'no',
		),
		'title'                         => array(
			'title'       => __( 'Title', 'cms-gateway-nexio' ),
			'type'        => 'text',
			'description' => __( 'This controls the title which the user sees during checkout.', 'cms-gateway-nexio' ),
			'default'     => __( 'Credit Card', 'cms-gateway-nexio' ),
			'desc_tip'    => true,
		),
		'description'                   => array(
			'title'       => __( 'Description', 'cms-gateway-nexio' ),
			'type'        => 'text',
			'description' => __( 'This controls the description which the user sees during checkout.', 'cms-gateway-nexio' ),
			'default'     => __( 'Pay with your credit card.', 'cms-gateway-nexio' ),
			'desc_tip'    => true,
		),
		'api_url'                       => array(
			'title'       => __( 'API URL', 'cms-gateway-nexio' ),
			'label'       => __( 'URL of API server', 'cms-gateway-nexio' ),
			'type'        => 'text',
			'description' => __( 'The URL of Nexio API server', 'cms-gateway-nexio' ),
			'default'     => 'https://api.nexiopay.com/',
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
			'description' => __( 'Password of your Nexio account', 'cms-gateway-nexio' ),
			'default'     => '',
			'desc_tip'    => true,
		),
		'merchant_id'                       => array(
			'title'       => __( 'Merchant Id', 'cms-gateway-nexio' ),
			'label'       => __( 'Merchant Id of Nexio account', 'cms-gateway-nexio' ),
			'type'        => 'text',
			'description' => __( 'Merchant Id of your Nexio account', 'cms-gateway-nexio' ),
			'default'     => '',
			'desc_tip'    => true,
		),
		'shareSecret'                       => array(
			'title'       => __( 'Shared Secret', 'cms-gateway-nexio' ),
			'label'       => __( 'Shared Secret of merchant', 'cms-gateway-nexio' ),
			'type'        => 'password',
			'description' => __( 'Shared Secret of merchant', 'cms-gateway-nexio' ),
			'default'     => '',
			'desc_tip'    => true,
		),
		'css'                       	=> array(
			'title'       => __( 'CSS', 'cms-gateway-nexio' ),
			'label'       => __( 'CSS file location', 'cms-gateway-nexio' ),
			'type'        => 'text',
			'description' => __( 'CSS file location', 'cms-gateway-nexio' ),
			'default'     => '',
			'desc_tip'    => true,
		),
		'customtext_url'                       	=> array(
			'title'       => __( 'Custom Text File', 'cms-gateway-nexio' ),
			'label'       => __( 'Custom text file location', 'cms-gateway-nexio' ),
			'type'        => 'text',
			'description' => __( 'Custom text file location', 'cms-gateway-nexio' ),
			'default'     => '',
			'desc_tip'    => true,
		),
		'fraud'                       => array(
			'title'       => __( 'Fraud Check', 'cms-gateway-nexio' ),
			'label'       => __( 'Enable/Disable fraud check', 'cms-gateway-nexio' ),
			'type'        => 'checkbox',
			'description' => '',
			'default'     => 'no',
		),
		'requirecvc'                       => array(
			'title'       => __( 'Require CVC', 'cms-gateway-nexio' ),
			'label'       => __( 'Require CVC info', 'cms-gateway-nexio' ),
			'type'        => 'checkbox',
			'description' => '',
			'default'     => 'yes',
		),
		'hidecvc'                       => array(
			'title'       => __( 'Hide CVC', 'cms-gateway-nexio' ),
			'label'       => __( 'Hide CVC info', 'cms-gateway-nexio' ),
			'type'        => 'checkbox',
			'description' => '',
			'default'     => 'no',
		),
		'hidebilling'                       => array(
			'title'       => __( 'Hide Billing', 'cms-gateway-nexio' ),
			'label'       => __( 'Hide billing info', 'cms-gateway-nexio' ),
			'type'        => 'checkbox',
			'description' => '',
			'default'     => 'no',
		),
		'authonly'                       => array(
			'title'       => __( 'Auth Only', 'cms-gateway-nexio' ),
			'label'       => __( 'Auth Only', 'cms-gateway-nexio' ),
			'type'        => 'checkbox',
			'description' => '',
			'default'     => 'no',
		),
		'signatureverify'                       => array(
			'title'       => __( 'Verify Signature', 'cms-gateway-nexio' ),
			'label'       => __( 'Verify Signature', 'cms-gateway-nexio' ),
			'type'        => 'checkbox',
			'description' => '',
			'default'     => 'no',
		),
	)
);
