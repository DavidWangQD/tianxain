<?php 
class ControllerProductCategory extends Controller {  
	public function index() {

        $this->document->setTitle("天线果盟 - 鲜果配送");

        $this->load->model('product/category');
        $this->load->model('tool/image');

        $this->data['preLoadImg'] = $this->model_tool_image->resizeNoBlank(DEFAULT_RESTAURANT_IMAGE, 180, 180);

        $products = $this->model_product_category->getProducts();

        $category_products = array();

        foreach($products as $item) {

            if(array_key_exists($item['category_id'], $category_products)) {

                if(file_exists(DIR_IMAGE . $item['image']) && is_file(DIR_IMAGE . $item['image'])) {

                    $item['image'] = $this->model_tool_image->resizeNoBlank($item['image'], 180, 180);

                }

                $category_products[$item['category_id']]['products'][] = $item;

            } else {

                $category_products[$item['category_id']] = array(
                    'category_name' => $item['category_name'],
                    'products'      => array()
                );

                if(file_exists(DIR_IMAGE . $item['image']) && is_file(DIR_IMAGE . $item['image'])) {

                    $item['image'] = $this->model_tool_image->resizeNoBlank($item['image'], 180, 180);

                }

                $category_products[$item['category_id']]['products'][] = $item;

            }

        }

        $this->data['category_products'] = $category_products;

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/product/category.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/product/category.tpl';
        } else {
            $this->template = 'default/template/product/category.tpl';
        }

        $this->children = array(
            'common/footer',
            'common/header'
        );

        $this->response->setOutput($this->render());

	}

    public function initializeByCart() {

        $response = array(
            'cart_total_price'  =>  $this->cart->getSubTotal(),
            'cart_products'     =>  array()
        );

        foreach($this->cart->getProducts() as $item) {

            $response['cart_products'][] = array(
                'product_id'    =>  $item['product_id'],
                'quantity'      =>  $item['quantity']
            );

        }

        die(json_encode($response));

    }


}
?>