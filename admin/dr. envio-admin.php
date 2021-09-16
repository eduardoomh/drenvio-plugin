<?php
include_once(ABSPATH . 'wp-admin/includes/plugin.php');

function DrfWoo_shipping_init() {
    global $DrEnvioFWoo_language;

    if ( ! class_exists( 'DRFWOO_SHIPPING_METHOD') ) {
        class DRFWOO_SHIPPING_METHOD extends WC_Shipping_Method {
           protected $DrEnvioFWoo_accessFormFields;
           public static $instance;
           
           public function __construct() {
               global $DrEnvioFWoo_language;
               $this->id                 = 'drenviofwoo'; 
               $this->method_title       = __( 'DrEnvioforWooCommerce' );
               $this->enabled            = "yes";
               $this->title              = "seleccione la paqueteria";
               $this->init();
               self::$instance = $this;
               return $this;
           }

           public static function get() {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
            }
           
           public function init() {
               $this->init_settings(); 
               $this->DrEnvioFWoo_accessFormFields = $this->DrEnvioFWoo_form($this->settings);
               $this->form_fields = array_merge(
                   $this->DrEnvioFWoo_accessFormFields
               );
               add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
            }

            public function DrEnvioFWoo_calculate_weight($package = array()) {
                $_parcels = array(
                    'weight' => 0,
                    'height' => 0,
                    'width' => 0,
                    'length' => 0
                );

                $_length = 0;
                $_height = 0;
                $_width = 0;
                $_weight = 0;
                $_qty = 0;
                
                foreach ( $package['contents'] as $item_id => $values ) {
                    if(!empty($values['data'])) {
                        $_Q = $values['quantity'];
                        $product = $values['data'];
                        $tmpLength = 0;
                        $tmpHeight = 0;
                        $tmpWidth = 0;

                        $thisParcel = null;
                        
                        $_weight = (float)$product->get_weight();
                        $_qty = (float)$_Q;
                        $tmpLength = (float)$product->get_length();
                        $tmpHeight = (float)$product->get_height();
                        $tmpWidth = (float)$product->get_width();


                        $weightUnit = get_option('woocommerce_weight_unit');
                        $dimensionUnit = get_option('woocommerce_dimension_unit');

                        $originalWeight = $addPackagingWeight === 'yes' ? ( $_weight + $standard_packaging_weight ) : $_weight;

                        if ($dimensionUnit === 'cm' && $tmpHeight < 1) {
                            $tmpHeight = 1;
                        }

                        if ($weightUnit === 'g') {
                            $originalWeight /= 1000;
                            $weightUnit = 'kg';
                        }

                        if ($weightUnit === 'oz') {
                            $originalWeight /= 35.274;
                            $weightUnit = 'kg';
                        }

                        $endWeight = round($originalWeight, 1);

                        if ($endWeight <= 0) {
                            $endWeight = 0.1;
                            $weightUnit = 'kg';
                        }

                        $thisParcel = array(
                            'quantity' => $_qty,
                            'weight' => $endWeight,
                            'weight_unit' => $weightUnit,
                            'length' => $tmpLength,
                            'height' => $tmpHeight,
                            'width' => $tmpWidth,
                            'dimension_unit' => $dimensionUnit,
                        );

                        if($_qty > 1){
                            $_parcels['weight'] =  $_parcels['weight'] + ($endWeight * $_qty);
                            $_parcels['length'] = $_parcels['length'] + ($tmpLength * $_qty);  
                        }else{
                            $_parcels['weight'] =  $_parcels['weight'] + $endWeight;
                            $_parcels['length'] = $_parcels['length'] + $tmpLength;
                        }

                        $_parcels['width'] = $_parcels['width'] + $tmpWidth;
                        $_parcels['height'] = $_parcels['height'] + $tmpHeight;
                    }
                }
                return $_parcels;
            }

           
           public function calculate_shipping( $package = array() ) {
               $settings = get_option('woocommerce_drenviofwoo_settings');
               $rate_list = array();
               $origin_country = "MX";
               $origin_postcode = $settings['cp_origin'];
               $dest_country = $package["destination"]["country"];
               $dest_postcode = $package["destination"]["postcode"];
               $extra_mount = $settings['extra_mount'];
               $extra_weight = $settings['extra_weight'];

               if (isset($package['rates'])) {
                foreach ($package['rates'] as $key => $value) {
                    unset($package['rates'][$key]);
                }
            }
      
               
               $parsels = $this->DrEnvioFWoo_calculate_weight($package);
                $drenvioObject = new DrEnvioFWooClass();

                $carriers_array_request = array(
                    'fedex' => array(
                        'name' => 'fedex',
                        'value' => $settings['carrier_fedex']
                    ),
                    'sendex' => array(
                        'name' => 'sendex',
                        'value' => $settings['carrier_sendex']
                    ),
                    'dhl' => array(
                        'name' => 'dhl',
                        'value' => $settings['carrier_dhl']
                    ),
                    'ups' => array(
                        'name' => 'ups',
                        'value' => $settings['carrier_ups']
                    ),
                    'redpack' => array(
                        'name' => 'redpack',
                        'value' => $settings['carrier_redpack']
                    ),
                    'carssa' => array(
                        'name' => 'carssa',
                        'value' => $settings['carrier_carssa']
                    ),
                    'vencedor' => array(
                        'name' => 'vencedor',
                        'value' => $settings['carrier_vencedor']
                    ),
                    'ivoy' => array(
                        'name' => 'ivoy',
                        'value' => $settings['carrier_ivoy']
                    ),
                    'scm' => array(
                        'name' => 'scm',
                        'value' => $settings['carrier_scm']
                    ),
                    'quiken' => array(
                        'name' => 'quiken',
                        'value' => $settings['carrier_quiken']
                    ),
                    'ampm' => array(
                        'name' => 'ampm',
                        'value' => $settings['carrier_ampm']
                    ),
                    'estafeta' => array(
                        'name' => 'estafeta',
                        'value' => $settings['carrier_estafeta']
                    ),
                    'tracusa' => array(
                        'name' => 'tracusa',
                        'value' => $settings['carrier_tracusa']
                    ),
                    'paquetexpress' => array(
                        'name' => 'paquetexpress',
                        'value' => $settings['carrier_paquetexpress']
                    ),
                    'noventa9Minutos' => array(
                        'name' => 'noventa9Minutos',
                        'value' => $settings['carrier_noventa9Minutos']
                    )                    
         
                );

                $carriers_for_request = $drenvioObject->DrEnvioFWoo_filter_carriers($carriers_array_request);


               $request = json_encode(array(
                    "origin"=> array(
                        "country"=> $origin_country,
                        "postalCode"=> $origin_postcode
                    ),
                    "destination"=> array(
                        "country"=> $dest_country,
                        "postalCode"=> $dest_postcode
                    ),
                    "package" => array(
                        "weight" => $parsels['weight'] + ($extra_weight / 1000),
                        "height" => $parsels['height'],
                        "width" => $parsels['width'],
                        "length" => $parsels['length'],
                        "declaredValue" => 10,
                        "type" => "box"
                    ),
                    "insurance" => 30,
                    "carriers" => $carriers_for_request   
                ));

                $setting = get_option('woocommerce_drenvio_settings');

                $rate_list = array();
                $response = $drenvioObject->DrEnvioFWoo_get_api_data('/rate', $request);

                if ($response['status']['code'] == 200) {
                    $new_order_array = $drenvioObject->DrEnvioFWoo_reorganized_array($response['body']->rates);
                            
                    foreach ($new_order_array as $rates_item) { 
                        $service = $drenvioObject->DrEnvioFWoo_rename_services($rates_item->service);
                        $label_html = "<label>$service</label>";
                        $paragraph_html = "<p>$rates_item->days</p>";

                        $rate = array(
                            'id' => ''.$rates_item->ObjectId.''.$rates_item->ShippingId,
                            'label' => $rates_item->carrier.' - '.$service.' ('.$rates_item->days.')',
                            'cost' => (int)$rates_item->price + $extra_mount,
                            'calc_tax' => 'per_item',
                            'meta_data' => array(
                                'label_description_Advance' => base64_encode(''.$label_html.$paragraph_html.''),
                                'quoteId' => 1,
                                'rateQuoteId' => 1,
                                'shippingMethod' => $rates_item->carrier,
                                'shippingMethodStore' => $rates_item->carrier,
                            )
                        );
                        $rate_list[] = $rate;
                    }   
                }

               foreach ($rate_list as $rate) {
                   $this->add_rate($rate);
                   
               }
              
           }


           public function admin_options() {
               global $woocommerce;
               $php_version    = PHP_VERSION;
               $wp_version     = get_bloginfo('version');
               $wc_version     = $woocommerce->version;
               $server_name    = $_SERVER['SERVER_NAME'];
               $curl_exists    = class_exists('WP_Http_Curl') ? 'Yes': 'No';
               $stringSupport  = base64_encode(json_encode([
                   'php_version'           => $php_version,
                   'wordpress_version'     => $wp_version,
                   'woocommerce_version'   => $wc_version,
                   'server_name'           => $server_name,
                   'curl_exists'           => $curl_exists
               ]));
            ?>
                <div class="tablecontent drenvio-settings" id="drenvio-access-conf">
            <?php 
                $this->form_fields = $this->DrEnvioFWoo_form($this->settings);
                parent::admin_options();
            ?>
            </div>
        <?php
           }

           static function DrEnvioFWoo_form($settings = []) {
            global $DrEnvioFWoo_language;

                $setup_form['key_production'] = array(
                        'title'       => __($DrEnvioFWoo_language->key_production, DRENVIOFWOO_PLUGIN),
                        'type'        => 'text',
                        'default'     => '',
                        'desc_tip'    => __($DrEnvioFWoo_language->key_production_description, DRENVIOFWOO_PLUGIN),
                );

                $setup_form['cp_origin'] = array(
                        'title'       => __($DrEnvioFWoo_language->cp_origin, DRENVIOFWOO_PLUGIN),
                        'type'        => 'number',
                        'default'     => 0,
                        'desc_tip'    => __($DrEnvioFWoo_language->cp_origin_description, DRENVIOFWOO_PLUGIN),
                );

                $setup_form['extra_mount'] = array(
                        'title'       => __($DrEnvioFWoo_language->extra_mount, DRENVIOFWOO_PLUGIN),
                        'type'        => 'number',
                        'default'     => 0,
                        'desc_tip'    => __($DrEnvioFWoo_language->extra_mount_description, DRENVIOFWOO_PLUGIN),
                );

                $setup_form['extra_weight'] = array(
                        'title'       => __($DrEnvioFWoo_language->extra_weight, DRENVIOFWOO_PLUGIN),
                        'type'        => 'number',
                        'default'     => 0,
                        'desc_tip'    => __($DrEnvioFWoo_language->extra_weight_description, DRENVIOFWOO_PLUGIN),
                );

                $setup_form['carrier_fedex'] = array(
                        'title'       => __($DrEnvioFWoo_language->fedex_shipping, DRENVIOFWOO_PLUGIN),
                        'type'        => 'checkbox',
                        'default'     => 'no',
                        'desc_tip'    => __($DrEnvioFWoo_language->fedex_shipping_description, DRENVIOFWOO_PLUGIN),
                );

                $setup_form['carrier_estafeta'] = array(
                        'title'       => __($DrEnvioFWoo_language->estafeta_shipping, DRENVIOFWOO_PLUGIN),
                        'type'        => 'checkbox',
                        'default'     => 'no',
                        'desc_tip'    => __($DrEnvioFWoo_language->estafeta_shipping_description, DRENVIOFWOO_PLUGIN),
                );

                $setup_form['carrier_dhl'] = array(
                        'title'       => __($DrEnvioFWoo_language->dhl_shipping, DRENVIOFWOO_PLUGIN),
                        'type'        => 'checkbox',
                        'default'     => 'no',
                        'desc_tip'    => __($DrEnvioFWoo_language->dhl_shipping_description, DRENVIOFWOO_PLUGIN),
                );

                $setup_form['carrier_ups'] = array(
                        'title'       => __($DrEnvioFWoo_language->ups_shipping, DRENVIOFWOO_PLUGIN),
                        'type'        => 'checkbox',
                        'default'     => 'no',
                        'desc_tip'    => __($DrEnvioFWoo_language->ups_shipping_description, DRENVIOFWOO_PLUGIN),
                );

                $setup_form['carrier_paquetexpress'] = array(
                        'title'       => __($DrEnvioFWoo_language->paquetexpress_shipping, DRENVIOFWOO_PLUGIN),
                        'type'        => 'checkbox',
                        'default'     => 'no',
                        'desc_tip'    =>  __($DrEnvioFWoo_language->paquetexpress_shipping_description, DRENVIOFWOO_PLUGIN),
                );

                $setup_form['carrier_sendex'] = array(
                        'title'       => __($DrEnvioFWoo_language->sendex_shipping, DRENVIOFWOO_PLUGIN),
                        'type'        => 'checkbox',
                        'default'     => 'no',
                        'desc_tip'    => __($DrEnvioFWoo_language->sendex_shipping_description, DRENVIOFWOO_PLUGIN),
                );

                $setup_form['carrier_redpack'] = array(
                        'title'       => __($DrEnvioFWoo_language->redpack_shipping, DRENVIOFWOO_PLUGIN),
                        'type'        => 'checkbox',
                        'default'     => 'no',
                        'desc_tip'    => __($DrEnvioFWoo_language->redpack_shipping_description, DRENVIOFWOO_PLUGIN),
                );

                $setup_form['carrier_quiken'] = array(
                        'title'       =>  __($DrEnvioFWoo_language->quiken_shipping, DRENVIOFWOO_PLUGIN),
                        'type'        => 'checkbox',
                        'default'     => 'no',
                        'desc_tip'    =>  __($DrEnvioFWoo_language->quiken_shipping_description, DRENVIOFWOO_PLUGIN),
                );

                $setup_form['carrier_ampm'] = array(
                        'title'       => __($DrEnvioFWoo_language->ampm_shipping, DRENVIOFWOO_PLUGIN),
                        'type'        => 'checkbox',
                        'default'     => 'no',
                        'desc_tip'    => __($DrEnvioFWoo_language->ampm_shipping_description, DRENVIOFWOO_PLUGIN),
                );

                $setup_form['carrier_tracusa'] = array(
                        'title'       => __($DrEnvioFWoo_language->tracusa_shipping, DRENVIOFWOO_PLUGIN),
                        'type'        => 'checkbox',
                        'default'     => 'no',
                        'desc_tip'    => __($DrEnvioFWoo_language->tracusa_shipping_description, DRENVIOFWOO_PLUGIN),
                );
                
                $setup_form['carrier_vencedor'] = array(
                        'title'       => __($DrEnvioFWoo_language->vencedor_shipping, DRENVIOFWOO_PLUGIN),
                        'type'        => 'checkbox',
                        'default'     => 'no',
                        'desc_tip'    => __($DrEnvioFWoo_language->vencedor_shipping_description, DRENVIOFWOO_PLUGIN),
                );
                            
                $setup_form['carrier_ivoy'] = array(
                        'title'       => __($DrEnvioFWoo_language->ivoy_shipping, DRENVIOFWOO_PLUGIN),
                        'type'        => 'checkbox',
                        'default'     => 'no',
                        'desc_tip'    => __($DrEnvioFWoo_language->ivoy_shipping_description, DRENVIOFWOO_PLUGIN),
                );

                $setup_form['carrier_scm'] = array(
                        'title'       =>  __($DrEnvioFWoo_language->scm_shipping, DRENVIOFWOO_PLUGIN),
                        'type'        => 'checkbox',
                        'default'     => 'no',
                        'desc_tip'    =>  __($DrEnvioFWoo_language->scm_shipping_description, DRENVIOFWOO_PLUGIN),
                );

                $setup_form['carrier_carssa'] = array(
                        'title'       => __($DrEnvioFWoo_language->carssa_shipping, DRENVIOFWOO_PLUGIN),
                        'type'        => 'checkbox',
                        'default'     => 'no',
                        'desc_tip'    => __($DrEnvioFWoo_language->carssa_shipping_description, DRENVIOFWOO_PLUGIN),
                );

                $setup_form['carrier_noventa9Minutos'] = array(
                        'title'       => __($DrEnvioFWoo_language->noventa9minutos_shipping, DRENVIOFWOO_PLUGIN),
                        'type'        => 'checkbox',
                        'default'     => 'no',
                        'desc_tip'    =>  __($DrEnvioFWoo_language->noventa9minutos_shipping_description, DRENVIOFWOO_PLUGIN),
                );

               return $setup_form;

           } 
           
        }
    }

}

add_action( 'woocommerce_shipping_init', 'DrfWoo_shipping_init' );