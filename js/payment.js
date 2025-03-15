jQuery(document).ready(function($) {
    let orderID = null;

    $('form.woocommerce-checkout').on('submit', function(event) {
        if (!orderID) {
            event.preventDefault();
            $.ajax({
                url: zerocryptopay_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'zerocryptopay_create_tracking',
                },
                success: function(response) {
                    if (response.success) {
                        orderID = response.data.order_id;
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'zerocryptopay_order_id',
                            value: orderID
                        }).appendTo('form.woocommerce-checkout');
                        $('form.woocommerce-checkout').unbind('submit').submit();
                    } else {
                        alert('Payment error: Could not generate order ID.');
                    }
                }
            });
        }
    });
});
