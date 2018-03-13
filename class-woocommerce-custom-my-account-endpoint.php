<?php

require_once('trait-wp-auto-hooks.php');

// Add an endpoint to the My Account page
class Woocommerce_Custom_My_Account_Endpoint {
    use wpAutoHooks;

    protected $name  = '';
    protected $title = '';
    private $show_pagination = TRUE;

    public function __construct( $name, $title ) {
		$this->name  = trim($name);
		$this->title = trim($title);

		// Install
		register_activation_hook( $GLOBALS['plugin'], [ $this, 'flush_rewrite_rules'] );

		add_action( 'woocommerce_account_' . $this->name . '_endpoint', [ $this, 'endpoint_content'] );
		
		$this->connect();
	}
	
	public function flush_rewrite_rules() {

		// Register the rewrite endpoint before permalinks are flushed
		add_rewrite_endpoint( $this->name, EP_PAGES );

		// Flush Permalinks
		if ( !has_action( 'shutdown', 'flush_rewrite_rules' ) ) {
                    add_action( 'shutdown', 'flush_rewrite_rules' );
                }
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

		$this->output( $current_page );
		$this->pagination( $current_page );
	}
	
	public function woocommerce_after_my_account_wpaction() {
		self::hook_check(__FUNCTION__);
		
		if ( version_compare( WC()->version, '2.6', '<' ) ) {
			$this->output();
		}
	}
	
	protected function output( $current_page=NULL ) { ?>
		
		<?php if ( !is_null( $current_page ) ) : ?>
		<p>
			<?php esc_html_e( 'Current page: ' . $current_page ); ?>
		</p>
		<?php endif; ?>
		<p>
			<?php esc_html_e( 'No method with content defined. Please define: '); ?>
		<p><code><?php  esc_html_e( static::class . '::output( $current_page )' ); ?></code></p>
			<?php 
			esc_html_e( ' method' ); 
			esc_html_e( (self::class === static::class ) ? ' in a child class.' : '.' ) ; 
			?>
		</p>
	<?php }
	
	protected function pagination( $current_page ) { ?>
                <?php if ( !$this->show_pagination() ) : return; endif;?>
		<nav class="woocommerce-myaccount-endpoint-pagination">
			<a class="prev<?php echo $current_page == 1 ? ' disabled' : '' ?>"
			   <?php if ( $current_page > 1 ) : ?>
			   href="<?php echo esc_url($this->url()) . ( $current_page-1 ); ?>"
			   <?php endif; ?>>
				Previous
			</a>
			<a class="next" 
			   href="<?php echo esc_url($this->url()) . ( $current_page+1 ); ?>">
				Next
			</a>
		</nav>
	<?php }
        
        protected function show_pagination( $show=NULL ) {
            if (is_null($show)) { return $this->show_pagination; }
            $this->show_pagination = (bool)$show;
            return $this->show_pagination;
        }
	
	public function url() {
		return wc_get_endpoint_url( $this->name, '', wc_get_page_permalink( 'myaccount' ) );
	}
}


