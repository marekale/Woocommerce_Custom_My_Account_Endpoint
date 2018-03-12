<?php

require_once('class-woocommerce-custom-my-account-endpoint.php');

for ( $i=0; $i<10; $i++ ) {
	new Woocommerce_Custom_My_Account_Endpoint('test' . ($i+1), 'Test - ' . ($i+1));
}

// Don't forget to flush the rewrite rules or deactivate/activate your plugin.