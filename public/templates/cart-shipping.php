<?php

 include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<tr class="checkout-shipping-container" class="shipping">
    <th class="shipping-table-title"><?php echo wp_kses_post( $package_name ); ?></th>
    <td class="shippint-table-content" data-title="<?php echo esc_attr( $package_name ); ?>">
        <div>
        <ul class="package-list" id="package_list" id="shipping_method">
            <?php  
              $list = array();
              function DrEnvioFWoo_image($value){
                return plugins_url('public/img/'.$value.'.svg', DRENVIOFWOO_FILE);
            }

            if(count($available_methods) == 0){
                echo '
                <p> 
                    El Código postal introducido es incorrecto o no está disponible.
                </p>';
            }else{
                foreach ( $available_methods as $method ) {
     
                    $list = array();
                    array_push($list, $method);
                    $arrier_list;
  
                    
                      foreach ( $list as $i => $method ) {
                          $header = $method->get_meta_data();
                          if (isset($header['shippingMethod'])) {
                              $carrier = $header['shippingMethod'];
                              $label = $method->label;
                              $price = $method->cost;
                              $advanced_label = base64_decode($header['label_description_Advance']);
                              echo '
                              <li class="package-list__item"> 
                                  <input class="input-package-list_'.$index.' input-second-class" type="radio" name="shipping_method['.$index.']" data-index="'.$index.'" id="shipping_method_'.$index.'_'.sanitize_title( $method->id ).'" value="'.esc_attr( $method->id ).'" class="shipping_method" '.checked( $method->id, $chosen_method, false ).' />
                                  <img class="image-paqueteria" width="64" height="32" src="'.DrEnvioFWoo_image($carrier).'" /> 
                                  <div class="text_paqueteria">'.$advanced_label.'</div>
                                  <div class="item_price"><p>$'.$price.'</p></div>
                              </li>';
                          }
                      }        
  
                  do_action( 'woocommerce_after_shipping_rate', $method, $index );
                    
                }
            }

        ?>
        </ul>
        <!-- <button>Calcular pedido</button> -->
    </div>
</td>
</tr>