<?php

require_once('class-woocommerce-custom-my-account-endpoint.php');

for ( $i=0; $i<10; $i++ ) {
	new Woocommerce_Custom_My_Account_Endpoint('test' . ($i+1), 'Test - ' . ($i+1));
}

class Custom_Endpoint extends Woocommerce_Custom_My_Account_Endpoint {
	protected function no_content( $current_page = NULL ) {
		parent::no_content( $current_page );
		echo __METHOD__;
	}
}

for ( $i=0; $i<10; $i++ ) {
	new Custom_Endpoint('endpoint' . ($i+1), 'Endpoint - ' . ($i+1));
}

// Don't forget to flush the rewrite rules or deactivate/activate your plugin.