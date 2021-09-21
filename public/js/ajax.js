jQuery( document ).on( 'updated_cart_totals', function(){
    if (jQuery('#calc_shipping_postcode').val() == '') {
        jQuery('#shipping_method li').hide();
        var DrEnvioFWooSubtotal = jQuery('.cart-subtotal td span.woocommerce-Price-amount.amount').text();
	    jQuery('.order-total td span.woocommerce-Price-amount.amount').text(DrEnvioFWooSubtotal);
	}



    jQuery(document).on('click','#shipping_method li :not(input)', function(){   
        jQuery(this).parent().find('input').click();
    });

    const shipment = {
        action: 'calculate_shipping',
        destination: {
            country: jQuery('#calc_shipping_country').val(),
            postcode: jQuery('#calc_shipping_postcode').val(),
        }
    };

    jQuery.post(DrEnvioFWooAjax.ajaxurl, shipment, (status, message, { responseText }) => {

    });
});

jQuery('body').on('updated_checkout', function () {

    let DrEnvioFWooElementos = document.querySelectorAll(".input-second-class");
    let DrEnvioFWooArrayElementos = Array.from(DrEnvioFWooElementos);

    let DrEnvioFWooCheckeds = DrEnvioFWooArrayElementos.filter(el => el.checked === true);

    if(DrEnvioFWooCheckeds && DrEnvioFWooCheckeds.length === 0){
        DrEnvioFWooArrayElementos[0].checked = true;

    }
});

jQuery( document ).ready( function(){
    console.log("listo")
    if (jQuery('#calc_shipping_postcode').val() == '') {
        jQuery('#shipping_method li').hide();
        var subtotal = jQuery('.cart-subtotal td span.woocommerce-Price-amount.amount').text();
	    jQuery('.order-total td span.woocommerce-Price-amount.amount').text(subtotal);
    }

    
    jQuery(document).on('click','#shipping_method li :not(input)', function(){    
        jQuery(this).parent().find('input').click();
    });
});
