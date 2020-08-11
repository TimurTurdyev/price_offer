<?php

class ControllerExtensionModulePriceOffer extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->language('extension/module/price_offer');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->document->addScript('https://cdn.jsdelivr.net/npm/vue/dist/vue.js');
        $this->document->addScript('view/javascript/price-offer/vuejs-paginate.js');

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('price_offer', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/module/price_offer', 'token=' . $this->session->data['token'], true));
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['entry_status'] = $this->language->get('entry_status');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['token'] = $this->session->data['token'];
        $data['image_catalog'] = HTTPS_CATALOG . 'image/';

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/price_offer', 'token=' . $this->session->data['token'], true)
        );

        $data['action'] = $this->url->link('extension/module/price_offer', 'token=' . $this->session->data['token'], true);

        $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true);


        // Categories
        $this->load->model('catalog/category');

        $filter_data = array(
            'sort' => 'name',
            'order' => 'ASC'
        );

        $data['categories'] = $this->model_catalog_category->getCategories($filter_data);
//print_r($data['categories']);die();
        if (isset($this->request->post['price_offer_setting'])) {
            $data['price_offer_setting'] = $this->request->post['price_offer_setting'];
        } elseif ($settings = $this->config->get('price_offer_setting')) {
            $data['price_offer_setting'] = $settings;
        } else {
            $data['price_offer_setting'] = array();
        }

        if (isset($this->request->post['price_offer_text'])) {
            $data['price_offer_text'] = $this->request->post['price_offer_text'];
        } elseif ($settings_text = $this->config->get('price_offer_text')) {
            $data['price_offer_text'] = $settings_text;
        } else {
            $data['price_offer_text'] = array(
                'h1' => '',
                'sub_title' => '',
                'meta_title' => '',
                'meta_description' => '',
                'mini_description' => '',
                'description' => ''
            );
        }

        if (isset($this->request->post['price_offer_status'])) {
            $data['price_offer_status'] = $this->request->post['price_offer_status'];
        } else {
            $data['price_offer_status'] = $this->config->get('price_offer_status');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/price_offer', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/price_offer')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function change()
    {
        $json = array();
        $product_id = $this->request->get['product_id'] ?? 0;

        $fields = [];

        if (isset($this->request->get['coefficient'])) {
            $fields[] = "isbn='" . $this->db->escape($this->request->get['coefficient']) . "'";
            $this->load->model('catalog/product');
            $product = $this->model_catalog_product->getProduct($product_id);
            $this->model_catalog_product->fieldToDiscount($product_id, $this->request->get['coefficient'], $product['price']);
        }

        if (isset($this->request->get['export'])) {
            $fields[] = "jan='" . $this->db->escape($this->request->get['export']) . "'";
        }

        if ($this->validate() && count($fields)) {
            $fields = join(',', $fields);
            $json['update'] = $this->db->query("UPDATE " . DB_PREFIX . "product SET {$fields} WHERE product_id = '" . (int)$product_id . "'");
        } elseif (count($this->error)) {
            foreach ($this->error as $name => $error_message) {
                $json[$name] = $error_message;
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function products()
    {
        $json = array();

        $categories = !empty($this->request->get['categories']) ? explode(',', $this->request->get['categories']) : array();
        $offset = isset($this->request->get['offset']) ? $this->request->get['offset'] : 0;

        $json['products'] = $this->getProducts($categories, $offset);
        $json['total'] = $this->getTotalProducts($categories);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function getProducts($categories = array(), $start = 0)
    {
        if (!count($categories)) {
            return array();
        }

        $sql = "SELECT p.*, pd.*, ";
        $sql .= " @percent := (p.price / 100) as base_percent,
                    GROUP_CONCAT(
                        d.quantity, '>', 100 - d.price / @percent
                        ORDER BY 
                            d.quantity ASC SEPARATOR '|'
                    ) AS coefficient";
        $sql .= " FROM " . DB_PREFIX . "product p";
        $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";
        $sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";
        $sql .= " LEFT JOIN " . DB_PREFIX . "product_discount d ON p.product_id = d.product_id";
        $sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
        $sql .= " AND (";

        foreach ($categories as &$category) {
            $category = "p2c.category_id = '" . (int)$category . "'";
        }
        $sql .= implode(" OR ", $categories);

        $sql .= ") GROUP BY p.product_id";
        $sql .= " ORDER BY pd.product_id ASC";

        $sql .= " LIMIT " . (int)$start . ", 100";

        $query = $this->db->query($sql);
        return $query->rows;
    }

    protected function getTotalProducts($categories = array())
    {
        if (!count($categories)) {
            return array();
        }

        $sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";

        $sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        $sql .= " AND (";

        foreach ($categories as &$category) {
            $category = "p2c.category_id = '" . (int)$category . "'";
        }
        $sql .= implode(" OR ", $categories);
        $sql .= ")";

        $query = $this->db->query($sql);

        return $query->row['total'];
    }
}