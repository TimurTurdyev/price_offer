<?php
class ControllerExtensionModulePriceOffer extends Controller
{
    public function index()
    {
        if (!$this->config->get('price_offer_status')) {
            $this->notFound();
            return;
        }

        $this->load->language('extension/module/price_offer');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [
            ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home')],
            ['text' => $this->language->get('heading_title'), 'href' => $this->url->link('extension/module/price_offer')],
        ];

        $data['heading_title'] = $this->language->get('heading_title');
        $data['sub_title'] = $this->language->get('text_sub_title');

        foreach (['column_left', 'column_right', 'content_top', 'content_bottom', 'footer', 'header'] as $part) {
            $data[$part] = $this->load->controller('common/' . $part);
        }

        $this->response->setOutput($this->load->view('extension/module/price_offer', $data));
    }

    private function notFound()
    {
        $this->load->language('error/not_found');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [
            ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home')],
            ['text' => $this->language->get('heading_title'), 'href' => $this->url->link('extension/module/price_offer')],
        ];

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_error'] = $this->language->get('text_error');
        $data['button_continue'] = $this->language->get('button_continue');
        $data['continue'] = $this->url->link('common/home');

        foreach (['column_left', 'column_right', 'content_top', 'content_bottom', 'footer', 'header'] as $part) {
            $data[$part] = $this->load->controller('common/' . $part);
        }

        $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
        $this->response->setOutput($this->load->view('error/not_found', $data));
    }
}
