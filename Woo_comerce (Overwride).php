


/*=========================================================================
                            WooCommerce Overwride
==========================================================================*/ 
function woo_custom_meta_box(){
    woocommerce_wp_text_input(array(
        'id' => '_custom_text',
        'label' => 'Custom Text',
        'description' => 'Enter extra text for this product',
        'desc_tip' => true,
    ));
}
add_action('woocommerce_product_options_general_product_data', 'woo_custom_meta_box');



function woo_custom_meta_boxes(){
    add_meta_box(
        'after_short_desc_meta',
        'After Short Description',
        'after_short_desc_meta_callback',
        'product',
        'normal',
        'low'
    );
}
add_action('add_meta_boxes', 'woo_custom_meta_boxes');

function after_short_desc_meta_callback($post) {

    $value = get_post_meta($post->ID, '_after_short_desc_text', true);
    ?>

    <p>
        <label for="after_short_desc_text"><strong>Custom Text</strong></label>
    </p>

    <input type="text"
           id="after_short_desc_text"
           name="after_short_desc_text"
           value="<?php echo esc_attr($value); ?>"
           style="width:100%;"
           placeholder="Enter text after short description">


    <?php
}

add_action('save_post_product', function ($post_id) {

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['after_short_desc_text'])) {
        update_post_meta(
            $post_id,
            '_after_short_desc_text',
            sanitize_text_field($_POST['after_short_desc_text'])
        );
    }

});




/*---------------------------------    */ 
add_action('add_meta_boxes', function () {

    add_meta_box(
        'after_short_desc_image',
        'After Short Description Image',
        'after_short_desc_image_callback',
        'product',
        'normal',
        'low'
    );

});

function after_short_desc_image_callback($post) {

    // 🔐 Nonce (MOST IMPORTANT)
    wp_nonce_field('after_short_desc_image_nonce', 'after_short_desc_image_nonce_field');

    $image_id  = get_post_meta($post->ID, '_after_short_desc_image_id', true);
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : '';
    ?>

    <div>
        <img id="after-short-desc-preview"
             src="<?php echo esc_url($image_url); ?>"
             style="max-width:200px;<?php echo $image_url ? '' : 'display:none;'; ?>">

        <input type="hidden"
               id="after_short_desc_image_id"
               name="after_short_desc_image_id"
               value="<?php echo esc_attr($image_id); ?>">

        <p>
            <button type="button" class="button upload-after-short-desc-image">
                Upload Image
            </button>
            <button type="button" class="button remove-after-short-desc-image"
                <?php echo $image_url ? '' : 'style="display:none;"'; ?>>
                Remove
            </button>
        </p>
    </div>

    <?php
}



add_action('save_post_product', function ($post_id) {

    // Autosave check
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    // Permission check
    if (!current_user_can('edit_post', $post_id)) return;

    // 🔐 Nonce verify (IMAGE WILL NOT SAVE WITHOUT THIS)
    if (
        !isset($_POST['after_short_desc_image_nonce_field']) ||
        !wp_verify_nonce(
            $_POST['after_short_desc_image_nonce_field'],
            'after_short_desc_image_nonce'
        )
    ) {
        return;
    }

    // Save image ID
    if (isset($_POST['after_short_desc_image_id'])) {
        update_post_meta(
            $post_id,
            '_after_short_desc_image_id',
            absint($_POST['after_short_desc_image_id'])
        );
    }

});



add_action('admin_footer', function () {

    if (!function_exists('get_current_screen')) return;
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'product') return;
    ?>
    <script>
        jQuery(function ($) {

            let frame;

            $('.upload-after-short-desc-image').on('click', function (e) {
                e.preventDefault();

                if (frame) {
                    frame.open();
                    return;
                }

                frame = wp.media({
                    title: 'Select Image',
                    multiple: false
                });

                frame.on('select', function () {
                    let attachment = frame.state().get('selection').first().toJSON();

                    $('#after_short_desc_image_id').val(attachment.id);
                    $('#after-short-desc-preview')
                        .attr('src', attachment.url)
                        .show();

                    $('.remove-after-short-desc-image').show();
                });

                frame.open();
            });

            $('.remove-after-short-desc-image').on('click', function () {
                $('#after_short_desc_image_id').val('');
                $('#after-short-desc-preview').hide();
                $(this).hide();
            });

        });
    </script>
    <?php
});



// 
add_action('woocommerce_single_product_summary', function () {

    global $product;

    // Get saved image ID
    $image_id = get_post_meta(
        $product->get_id(),
        '_after_short_desc_image_id',
        true
    );

    if ($image_id) {
        echo '<div class="after-short-desc-image">';
        echo wp_get_attachment_image($image_id, 'large');
        echo '</div>';
    }

}, 25); // 👈 Short description-এর পরে






/*============================================================================================================================*/ 







// Add Custom Field in WooCommerce Variation
// 1. Meta Box UI
function variation_custom_meta_field($loop, $variation_data, $variation){

    $image_id  = get_post_meta($variation->ID, '_variation_custom_image_id', true);
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';

    ?>
        <div class="form-row form-row-full variation-custom-image-wrap">
            <label><strong>Variation Custom Image</strong></label>

            <div style="display:flex;align-items:center;gap:10px;">
                <img class="variation-custom-image-preview"
                        src="<?php echo esc_url($image_url); ?>"
                        style="width:60px;<?php echo $image_url ? '' : 'display:none;'; ?>">

                <input type="hidden"
                        class="variation-custom-image-id"
                        name="variation_custom_image[<?php echo esc_attr($loop); ?>]"
                        value="<?php echo esc_attr($image_id); ?>">

                <button type="button" class="button upload-variation-custom-image">
                    Upload
                </button>

                <button type="button"
                        class="button remove-variation-custom-image"
                        <?php echo $image_url ? '' : 'style="display:none;"'; ?>>
                    Remove
                </button>
            </div>
        </div>
    <?php

}
add_action('woocommerce_variation_options_pricing' , 'variation_custom_meta_field', 10,3);


// 2. Save Meta Data
function save_variation_custom_meta($variation, $i){
    if (isset($_POST['variation_custom_image'][$i])) {
        $variation->update_meta_data(
            '_variation_custom_image_id',
            absint($_POST['variation_custom_image'][$i])
        );
    }
}
add_action('woocommerce_admin_process_variation_object', 'save_variation_custom_meta', 10 , 2);


// 3. Add jQuerry
function variation_custom_meta_js(){

     if (!function_exists('get_current_screen')) return;
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'product') return;
    ?>
    <script>
        jQuery(function ($) {

            let frame;

            function triggerVariationSave(wrap) {
                wrap.find('.variation-custom-image-id').trigger('change');

                $('#variable_product_options')
                    .trigger('woocommerce_variations_input_changed')
                    .trigger('woocommerce_variations_changed');
            }

            // Upload image
            $(document).on('click', '.upload-variation-custom-image', function (e) {
                e.preventDefault();

                let wrap = $(this).closest('.variation-custom-image-wrap');

                frame = wp.media({
                    title: 'Select Variation Image',
                    multiple: false
                });

                frame.on('select', function () {
                    let attachment = frame.state().get('selection').first().toJSON();

                    wrap.find('.variation-custom-image-id').val(attachment.id);
                    wrap.find('.variation-custom-image-preview')
                        .attr('src', attachment.sizes.thumbnail.url)
                        .show();

                    wrap.find('.remove-variation-custom-image').show();

                    triggerVariationSave(wrap);
                });

                frame.open();
            });

            // Remove image
            $(document).on('click', '.remove-variation-custom-image', function () {
                let wrap = $(this).closest('.variation-custom-image-wrap');

                wrap.find('.variation-custom-image-id').val('');
                wrap.find('.variation-custom-image-preview').hide();
                $(this).hide();

                triggerVariationSave(wrap);
            });

        });
    </script>
    <?php
}
add_action('admin_footer','variation_custom_meta_js');



/*
// Add custom variation image to frontend data
add_filter(
    'woocommerce_available_variation',
    'add_custom_image_to_variation_data',
    10,
    3
);

function add_custom_image_to_variation_data($variation_data, $product, $variation){

    $image_id = get_post_meta($variation->get_id(), '_variation_custom_image_id', true);

    if ($image_id) {
        $variation_data['custom_image'] = wp_get_attachment_image_url($image_id, 'large');
    } else {
        $variation_data['custom_image'] = '';
    }

    return $variation_data;
}




add_action('wp_footer', function () {

    if (!is_product()) return;

    ?>
    <script>
    jQuery(function ($) {

        let $form = $('form.variations_form');
        let $gallery = $('.woocommerce-product-gallery');

        // Create container once
        if (!$('.variation-extra-image').length) {
            $gallery.after(
                '<div class="variation-extra-image" style="margin-top:20px; display:none;"></div>'
            );
        }

        // When variation selected
        $form.on('found_variation', function (event, variation) {

            if (variation.custom_image) {
                $('.variation-extra-image')
                    .html('<img src="' + variation.custom_image + '" style="max-width:100%;">')
                    .slideDown();
            } else {
                $('.variation-extra-image').slideUp().empty();
            }

        });

        // When reset
        $form.on('reset_data', function () {
            $('.variation-extra-image').slideUp().empty();
        });

    });

    </script>
    <?php
});
*/