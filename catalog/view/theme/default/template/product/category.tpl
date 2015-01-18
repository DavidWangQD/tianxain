<?php echo $header; ?>
<div class="category-header"></div>
<div class="category-time">配送时间：09：00～18：00</div>
<div class="category-info-frame">

    <?php foreach($category_products as $item) { ?>

    <div class="category-info-body">
        <div class="category-info-title"><?php echo $item['category_name']; ?></div>
        <div class="category-info-content">
            <?php foreach($item['products'] as $product_item) { ?>
            <ul class="category-info-content-item product-item-<?php echo $product_item['product_id']; ?>" data-product-id="<?php echo $product_item['product_id']; ?>">
                <li>
                    <div class="item-image">
                        <img class="lazyLoad" src="<?php echo $preLoadImg; ?>" data-original="<?php echo $product_item['image']; ?>" alt=""/>
                    </div>
                </li>
                <li>
                    <div class="item-name">
                        <?php echo $product_item['product_name']; ?>&nbsp;(&nbsp;库存：<?php echo $product_item['quantity']; ?><?php echo $product_item['weight_class']; ?>&nbsp;)&nbsp;
                    </div>
                    <div class="item-price">
                        <?php if(is_null($product_item['special'])) { ?>
                            <span class="current"><span class="symbol" style="font-size: 2em;">¥</span><?php echo sprintf("%.2f", $product_item['price']); ?>&nbsp;/&nbsp;<?php echo $product_item['weight_class']; ?></span>
                        <?php } else { ?>
                            <span class="current"><span class="symbol" style="font-size: 2em;">¥</span><?php echo sprintf("%.2f", $product_item['special']); ?>&nbsp;/&nbsp;<?php echo $product_item['weight_class']; ?></span>
                            <br /><span class="original">原价： ¥ <?php echo sprintf("%.2f", $product_item['price']); ?>&nbsp;/&nbsp;<?php echo $product_item['weight_class']; ?></span>
                        <?php } ?>
                    </div>
                </li>
                <li>
                    <div class="item-operation">
                        <div class="item-operation-button minus-button">-</div>
                        <div class="item-operation-button add-button">+</div>
                        <span style="display: none" class="buied">0</span>
                    </div>
                </li>
            </ul>
            <?php } ?>
        </div>
    </div>
    <?php } ?>
</div>
<div class="footer-contact-us" style="margin-bottom: 40px;height: 30px;font-size: 1.8em;text-align: center;margin-right: auto;margin-left: auto;">没有更多了...需要其他产品请与我们联系</div>
<div class="cart">
</div>
<div class="cart-item">
    <img class="cart-image" src="/catalog/view/theme/default/image/cart.png" width="120px" height="120px" alt="cart"/>
    <div class="cart-money"><span class="price-symbol">¥</span> <span class="cart-price-total">0</span> 元</div>
</div>
<div class="checkout">结&nbsp;算</div>
<img id="upArrow" src="catalog/view/theme/default/image/arrow.png" alt="upArrow" width="100px" />
<script type="text/javascript">

    $("img.lazyLoad").lazyload({
        effect : "fadeIn"
    });

    $(window).scroll(function(){
        var y = $(window).scrollTop();
        if( y > 15 ){
            $('#upArrow').fadeIn();
        } else {
            $('#upArrow').fadeOut();
        }
    });

    //initialize by the cart
    (function(){

        $.get('index.php?route=product/category/initializeByCart',function(data){

            if(data.hasOwnProperty('cart_total_price')){

                $(".cart-price-total").text(data.cart_total_price);

            }

            if(data.hasOwnProperty('cart_products') && data.cart_products.length > 0){

                var cart_products_length = data.cart_products.length;

                for(var i = 0;i < cart_products_length;i++) {

                    var operation_button_section = $('.product-item-'+data.cart_products[i].product_id + ' li:eq(2)').children('.item-operation');

                    operation_button_section.children('.minus-button').show();

                    operation_button_section.children('.add-button').text(data.cart_products[i].quantity);

                    operation_button_section.children('.buied').text(data.cart_products[i].quantity);

                }

            }

        },'json');

    }());

    var cart = new Object();

    cart.update = function(params, callback){

        $.post('index.php?route=checkout/cart/update',params,function(data){

            if(data.hasOwnProperty('status') && data.hasOwnProperty('cart_total') && data.status == 1) {

                $(".cart-item .cart-money .cart-price-total").text(data.cart_total);

                if(isFunction(callback)) {

                    callback();

                }

            }

        },'json');

    };

    $(function(){

        $(".add-button").click(function(){

            var product_id = $(this).parents('.category-info-content-item').data('product-id');
            var num = parseInt($(this).siblings(".buied").text());
            var record_buied_span = $(this).siblings(".buied");

            var add_button = $(this);
            var minus_button = $(this).siblings('.minus-button');

            num++;

            var params = {
                product_id:product_id,
                num:num
            };

            cart.update(params,function(){

                add_button.text(num);

                if(num === 1) {
                    minus_button.show();
                }

                record_buied_span.text(num);

            });

        });

        $(".minus-button").click(function(){

            var product_id = $(this).parents('.category-info-content-item').data('product-id');
            var num = parseInt($(this).siblings(".buied").text());

            var add_button = $(this).siblings('.add-button');
            var minus_button = $(this);
            var record_buied_span = $(this).siblings(".buied");

            num--;

            var params = {
                product_id:product_id,
                num:num
            };

            cart.update(params,function(){

                if(num === 0) {
                    add_button.text('+');
                    minus_button.hide();
                } else {
                    add_button.text(num);
                }

                record_buied_span.text(num);

            });

        });

    });



    $("#upArrow").click(function(){

        $('html, body').animate({ scrollTop: 0 }, 'slow');

    });

</script>

<?php echo $footer; ?>