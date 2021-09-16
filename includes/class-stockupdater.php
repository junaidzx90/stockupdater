<?php
class StockUpdater{

    function __construct(){
        add_filter( 'cron_schedules', [$this,'stockupdater_add_cron_interval'] );
		if ( ! wp_next_scheduled( 'stockupdater_update_products_cron_event' ) ) {
			wp_schedule_event( time(), 'fourtimes_daily', 'stockupdater_update_products_cron_event');
		}
    }
    
    function stockupdater_add_cron_interval( $schedules ) { 
        $schedules['fourtimes_daily'] = array(
            'interval' => 60,
            'display'  => esc_html__( 'Fourtimes daily' ), );
        return $schedules;
    }

    // Manage feeds for update product
    function stockupdater_update_products_cron_event(){
        //$this->get_feed_one_data();
    }

    // Update woocommerce product stock based sku
    function update_woo_product_stock($sku, $stock){

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

    function run(){
        //var_dump($this->feed_two_get_data());
    }
}