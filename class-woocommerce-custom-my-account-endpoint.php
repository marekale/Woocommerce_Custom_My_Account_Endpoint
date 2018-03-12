<?php

require_once('trait-wp-auto-hooks.php');

// Add an endpoint to the My Account page
class Woocommerce_Custom_My_Account_Endpoint {
	use wpAutoHooks;
	
	private $name = '';
	private $title = '';
	
	public function __construct( $name, $title ) {
		$this->name = trim($name);
		$this->title = trim($title);

		// Install
		register_activation_hook( __FILE__, [ $this, 'flush_rewrite_rules'] );
		
		add_action( 'woocommerce_account_' . $this->name . '_endpoint', [ $this, 'endpoint_content'] );
		$this->connect();
	}
	
	public function flush_rewrite_rules() {

		// Register the rewrite endpoint before permalinks are flushed
		add_rewrite_endpoint( $this->name, EP_PAGES );

		// Flush Permalinks
		flush_rewrite_rules();
	}
	
	public function init_wpaction() {
		self::hook_check(__FUNCTION__);

		add_rewrite_endpoint( $this->name, EP_PAGES );
	}
	
	public function query_vars_wpfilter0( $vars ) {
		self::hook_check(__FUNCTION__);
		
		$vars[] = $this->name;
		return $vars;
	}
	
	public function the_title_wpfilter( $title ) {
		self::hook_check(__FUNCTION__);
		
		global $wp_query;
		$is_endpoint = isset( $wp_query->query_vars[ $this->name ] );

		if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
			$title = $this->title;
			remove_filter( 'the_title', array( $this, __FUNCTION__ ) );
		}

		return $title;
	}
	
	public function woocommerce_account_menu_items_wpfilter( $items ) {
		self::hook_check(__FUNCTION__);
		
		// Remove logout menu item.
		if ( array_key_exists( 'customer-logout', $items ) ) {
			$logout = $items['customer-logout'];
			unset( $items['customer-logout'] );
		}

		// Add custom menu item.
		$items[ $this->name ] = $this->title;

		// Add back the logout item.
		if ( isset( $logout ) ) {
			$items['customer-logout'] = $logout;
		}

		return $items;
	}
	
	public function endpoint_content( $_current_page ) {
		self::hook_check('woocommerce_account_' . $this->name . '_endpoint_wpaction');
		
		$current_page = empty( $_current_page ) ? 1 : absint( $_current_page );
		$name = $this->name;
		
		if ( is_callable( [ $this, $name ] ) ) { $this->$name( $current_page ); }
		else {
			$this->no_content( $current_page );
		}
	}
	
	public function woocommerce_after_my_account_wpaction() {
		self::hook_check(__FUNCTION__);
		
		if ( version_compare( WC()->version, '2.6', '<' ) ) {
			if ( is_callable( [ $this, $name ] ) ) { $this->$name(); }
			else {
				$this->no_content();
			}
		}
	}
	
	protected function no_content( $current_page=NULL ) { ?>
		
		<?php if ( !is_null( $current_page ) ) : ?>
		<p>
			<?php esc_html_e( 'Current page: ' . $current_page ); ?>
		</p>
		<?php endif; ?>
		<p>
			<?php esc_html_e( 'No method with content defined. Please define '); ?>
			<code><?php  esc_html_e( static::class . '::' . $this->name ); ?></code>
			<?php esc_html_e( ' method.' ); ?>
		</p>
	<?php }
}


