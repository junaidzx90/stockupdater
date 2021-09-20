<?php
class StockUpdater{

    function __construct(){
        add_filter( 'cron_schedules', [$this,'stockupdater_add_cron_interval'] );
        add_action( 'stockupdater_update_products', [$this, 'stockupdater_update_products_cront_event']);
        add_action('admin_footer', [$this, 'stockupdater_admin_script']);
		if ( ! wp_next_scheduled( 'stockupdater_update_products' ) ) {
			wp_schedule_event( time(), 'fourtimes_daily', 'stockupdater_update_products');
		}
        add_action("admin_menu", [$this, 'stockupdater_menupage']);
    }

    /**
     * Menupage Register 
     */
    function stockupdater_menupage(){
        add_options_page( 'Stock Updater', 'Stock Updater', 'manage_options', 'stockupdater', [$this, 'menupage_html'], 45 );
    }
    
    function menupage_html(){
        echo '<h3>Stock Updater</h3>';
        echo '<form action="" method="post" id="stockupdater_settings_section">';
        echo '<table style="width: 30%" class="widefat">';
        echo '<tr>';
        echo '<td>';
        echo '<input autocomplete="off" type="text" name="feed_one_prefix" class="prefixinp widefat" value="'.(get_option('feed_one_prefix')).'">';
        echo htmlentities('<sku>');
        echo '<span class="prefix">'.(get_option('feed_one_prefix')).'</span>DKK0120001R';
        echo htmlentities('</sku>');
        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<td>';
        echo '<input autocomplete="off" type="text" name="feed_two_prefix" class="prefixinp widefat" value="'.(get_option('feed_two_prefix')).'">';
        echo htmlentities('<cod_produs>');
        echo '<span class="prefix">'.(get_option('feed_two_prefix')).'</span>1045-03549';
        echo htmlentities('</cod_produs>');
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        echo '<input style="margin-top: 5px" type="submit" class="button button-primary" name="save_form" value="Save Changes">';
        echo '</form>';
    }
    
    // Wp Cron Timer
    function stockupdater_add_cron_interval( $schedules ) { 
        $schedules['fourtimes_daily'] = array(
            'interval' => 4 * HOUR_IN_SECONDS,
            // 'interval' => 60, //For test
            'display'  => esc_html__( 'Fourtimes daily' ), );
        return $schedules;
    }

    // Manage feeds for update product
    function stockupdater_update_products_cront_event(){
        // Update feed one
        $feed_one = $this->get_feed_one_data();
        $prefix1 = get_option('feed_one_prefix');
        $this->update_woo_product_stock($feed_one,$prefix1);
        
        // Update feed two
        $feed_two = $this->get_feed_two_data();
        $prefix2 = get_option('feed_two_prefix');
        $this->update_woo_product_stock($feed_two,$prefix2);
    }

    // Update woocommerce product stock based sku
    function update_woo_product_stock($products, $prefix){

        if(is_array($products) && count($products)>0){
            foreach($products as $product){
                $product_sku = $product['sku'];
                $product_sku = $prefix.$product_sku;
                $product_qty = $product['stock'];

                // Get product_id from SKU — returns null if not found
                $product_id = wc_get_product_id_by_sku( $product_sku );

                // Process if product found
                if ( $product_id != null ) {
                    
                    // Check for negative stock (backorders etc.) and set to 0
                    if ( $product_qty <= 0 ) {
                        $product_qty = 0;
                    }
                    
                    // Set up WooCommerce product object
                    $product = new WC_Product( $product_id );
                    
                    // Make changes to stock quantity and save
                    $product->set_manage_stock( true );
                    $product->set_stock_quantity( $product_qty );
                    $product->save();

                }
            }
        }
    }

    // Get feed one data from xml
    function get_feed_one_data(){
        $hubners = new XMLSimpler('https://www.hubners.ro/index.php/datafeed/index/index/id/5cf90534af201');

        $first_feed = [];
        while($xml = $hubners->read()){
            //Getting attributes
            $products = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOBLANKS && LIBXML_NOWARNING);
            foreach($products as $product){
                $product_info = [];
                $product_info['sku'] = strip_tags($product->sku);
                $product_info['stock'] = strip_tags($product->stock);
                
                array_push($first_feed, $product_info);
            }
        }
        return $first_feed;
    }

    // Get feed tow data from xml
    function get_feed_two_data(){
        $partenerviva = new XMLSimpler('https://www.partenerviva.ro/feed?format=xml&token=6e799f2caf0fa383bcd31dd05b694a');

        $second_feed = [];
        while($xml = $partenerviva->read()){
            //Getting attributes
            $products = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOBLANKS && LIBXML_NOWARNING);

            foreach($products as $product){

                $product_info = [];
                $product_info['sku'] = strip_tags($product->cod_produs);
                $product_info['stock'] = strip_tags($product->stoc_produs);
                
                array_push($second_feed, $product_info);
            }
        }
        return $second_feed;
    }

    function stockupdater_admin_script(){
        wp_enqueue_script('jquery');
        ?>
        <script>
            jQuery('.prefixinp').each(function(){
                jQuery(this).on('keyup', function(){
                    jQuery(this).next('.prefix').text(jQuery(this).val())
                });
            })
        </script>
        <?php
    }
}