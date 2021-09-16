<?php

class DrEnvioFWooClass {

    public static function DrEnvioFWoo_plugin_settings_link($links) {
        $href = 'admin.php?page=wc-settings&tab=shipping&section=drenviofwoo';
        $settings_link = "<a href='{$href}'>". __('Ajustes', DRENVIOFWOO_PLUGIN ) ."</a>";
        array_unshift($links, $settings_link);

        return $links;
    }
    
    public static function DrEnvioFWoo_plugin_menu_link($links) {
        $url = 'admin.php?page=wc-settings&tab=shipping&section=drenviofwoo';
        array_unshift($links, $url);

        return $links;
    }

    static public function DrEnvioFWoo_shipping_methods($methods) {
        $methods[] = 'DRFWOO_SHIPPING_METHOD';
        return $methods;

    }

    public function DrEnvioFWoo_filter_carriers($carriers){
        $carrier_list = [];
        foreach ($carriers as $carrier){
            if($carrier['value'] == 'yes'){
                array_push($carrier_list, $carrier['name']);
            }
        }
        return $carrier_list;
 
    }

    public function DrEnvioFWoo_reorganized_array($array){
        $array_one = array();
        $array_two = array();

        $principal_carriers = array(
            "fedex",
            "estafeta",
            "dhl",
            "ups",
            "paquetexpress",
        );


        foreach($array as $array_item){
            if(in_array($array_item->carrier, $principal_carriers)){
                $array_one[] = $array_item;
            }else{
                $array_two[] = $array_item;
            }
        }
        //usort($array_two, function($a, $b) { return $a->price > $b->price; });
        $final_array = array_merge($array_one, $array_two);

        return $final_array;

    }

    public function DrEnvioFWoo_rename_services($service){
        switch($service){
            case 'ground':
                return 'Terrestre';
            case 'express':
                return 'Express';
            case 'express_2':
                return 'Express';
            default:
                return $service;
        }
    }

    public function DrEnvioFWoo_rename_messages($message){
        switch($message){
            case 'Destination Postal Code is not valid':
                return 'El código postal del destino no es válido o está bloqueado, seleccione otro código postal';
            case 'Origin Postal Code is not valid':
                return 'El código postal del origen no es válido o está bloqueado,seleccione otro código postal';
            default: 
            return 'El código postal no es válido o está bloqueado, seleccione otro código postal';
        }
    }

    public function DrEnvioFWoo_get_api_data($api_url ,  $json = []) {
        
        $settings = get_option('woocommerce_drenvio_settings');
        $api_key = $settings['key_production'];

        //$api_base_url = 'https://drenvio-api-test.vercel.app/api'. $api_url;
        $api_base_url = 'https://api-drenvio.vercel.app/api'. $api_url;

        $method = 'POST';
    
       
        $DrEnvioFWoo_request_headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key
        ); 

        //$wp_request_headers['key'] = 'Bearer '.$api_key;     
        $timeout = 100000;
        $request_data = [
            'method'    => $method,
            'timeout' => $timeout,
            'headers'   => $DrEnvioFWoo_request_headers,
            'body'      => $json
        ];
        $response = wp_remote_request($api_base_url, $request_data);
        //$body = get_object_vars($response['body']);
        

        if(!$response->errors){
            $result = array(
                'status' => array(
                    'code' => $response['response']['code'],
                    'message' => $response['response']['message']
                ),
                'body' => json_decode($response['body'])
            );
            return $result;
        }

        $result = array(
            'status' => array(
                'code' => '500',
                'message' => 'Ha habido un problema en el servidor'
            ),
        );

        return $result;
        
  
    }
}