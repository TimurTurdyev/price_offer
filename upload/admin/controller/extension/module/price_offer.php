<?php
class ControllerExtensionModulePriceOffer extends Controller
{
    private $error = [];

    public function index()
    {
        $this->load->language('extension/module/price_offer');
        $this->load->model('setting/setting');

        $this->document->setTitle($this->language->get('heading_title'));

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
            $this->model_setting_setting->editSetting('price_offer', $this->postedSettings());

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->link('extension/module/price_offer'));
        }

        foreach ([
            'heading_title', 'text_edit', 'text_enabled', 'text_disabled', 'entry_status', 'button_save', 'button_cancel',
        ] as $key) {
            $data[$key] = $this->language->get($key);
        }

        $data['error_warning'] = implode('<br>', $this->error);
        $data['success'] = '';

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        $data['breadcrumbs'] = [
            ['text' => $this->language->get('text_home'), 'href' => $this->link('common/dashboard')],
            ['text' => $this->language->get('text_extension'), 'href' => $this->link('extension/extension', 'type=module')],
            ['text' => $this->language->get('heading_title'), 'href' => $this->link('extension/module/price_offer')],
        ];

        $data['action'] = $this->link('extension/module/price_offer');
        $data['cancel'] = $this->link('extension/extension', 'type=module');

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $settings = $this->postedSettings();
        } else {
            $settings = $this->model_setting_setting->getSetting('price_offer');
        }

        $data['status'] = !empty($settings['price_offer_status']);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/price_offer', $data));
    }

    private function validateForm()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/price_offer')) {
            $this->error[] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    private function postedSettings()
    {
        $post = $this->request->post;

        return [
            'price_offer_status' => empty($post['price_offer_status']) ? 0 : 1,
        ];
    }

    private function link($route, $args = '')
    {
        $url = $this->url->link($route, 'token=' . $this->session->data['token'] . ($args ? '&' . $args : ''), true);

        return str_replace('&amp;', '&', $url);
    }
}
