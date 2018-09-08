<?php
class ControllerExtensionModulePriceOffer extends Controller
{
    private $error = [];

    public function index()
    {
        $this->load->language('extension/module/price_offer');
        $this->load->model('setting/setting');
        $this->load->model('extension/module/price_offer');

        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->addScript('https://cdn.jsdelivr.net/npm/vue@2.5.17/dist/vue.min.js');

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
            $this->model_setting_setting->editSetting('price_offer', $this->postedSettings());

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->link('extension/module/price_offer'));
        }

        foreach ([
            'heading_title', 'text_edit', 'text_enabled', 'text_disabled', 'text_categories', 'text_add', 'text_section',
            'text_group', 'text_default', 'text_no_sections', 'text_help', 'entry_status', 'entry_target', 'entry_name',
            'entry_discounts', 'entry_categories', 'entry_small', 'entry_medium', 'entry_large', 'entry_section_title',
            'entry_group_title', 'button_save', 'button_cancel', 'button_add', 'button_remove',
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

        $data['config'] = json_encode([
            'sections'   => $this->decode($this->cleanSections($settings['price_offer_sections'] ?? [])),
            'categories' => $this->categories(),
            'text'       => [
                'new_section' => $this->language->get('text_new_section'),
                'new_group'   => $this->language->get('text_new_group'),
                'new_offer'   => $this->language->get('text_new_offer'),
                'categories'  => $this->language->get('error_categories'),
                'name'        => $this->language->get('error_name'),
            ],
        ]);

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

        foreach ($this->cleanSections($this->request->post['price_offer_sections'] ?? []) as $section) {
            foreach ($section['groups'] as $group) {
                foreach (array_merge([$group], $group['offers']) as $row) {
                    foreach ($row['discounts'] as $value) {
                        if ($value !== '' && (!is_numeric($value) || $value < 0 || $value >= 100)) {
                            $this->error[] = sprintf($this->language->get('error_discount'), $value, $row['name'] ?? $row['title']);
                        }
                    }
                }
            }
        }

        return !$this->error;
    }

    private function postedSettings()
    {
        $post = $this->request->post;

        return [
            'price_offer_status'   => empty($post['price_offer_status']) ? 0 : 1,
            'price_offer_sections' => $this->cleanSections($post['price_offer_sections'] ?? []),
        ];
    }

    // Только известные поля; пустые группы и разделы отбрасываются.
    private function cleanSections($sections)
    {
        $clean = [];

        foreach ((array)$sections as $section) {
            $groups = [];

            foreach ((array)($section['groups'] ?? []) as $group) {
                $offers = [];

                foreach ((array)($group['offers'] ?? []) as $offer) {
                    $offers[] = [
                        'name'       => trim($offer['name'] ?? ''),
                        'categories' => $this->ids($offer['categories'] ?? ''),
                        'discounts'  => $this->cleanDiscounts($offer['discounts'] ?? []),
                    ];
                }

                if ($offers) {
                    $groups[] = [
                        'title'     => trim($group['title'] ?? ''),
                        'discounts' => $this->cleanDiscounts($group['discounts'] ?? []),
                        'offers'    => $offers,
                    ];
                }
            }

            if ($groups) {
                $clean[] = [
                    'title'  => trim($section['title'] ?? ''),
                    'groups' => $groups,
                ];
            }
        }

        return $clean;
    }

    // Скидки мелкого, среднего и крупного опта в процентах, пустая строка - не задана.
    private function cleanDiscounts($values)
    {
        $values = array_values((array)$values);
        $clean = [];

        for ($i = 0; $i < 3; $i++) {
            $clean[] = str_replace(',', '.', trim($values[$i] ?? ''));
        }

        return $clean;
    }

    private function ids($value)
    {
        $ids = is_array($value) ? $value : explode(',', $value);

        return array_values(array_unique(array_filter(array_map('intval', $ids))));
    }

    private function categories()
    {
        $categories = [];

        foreach ($this->model_extension_module_price_offer->getCategories() as $category) {
            $categories[] = [
                'id'       => (int)$category['category_id'],
                'name'     => html_entity_decode($category['name'], ENT_QUOTES, 'UTF-8'),
                'products' => (int)$category['products'],
            ];
        }

        return $categories;
    }

    // OpenCart хранит ввод экранированным, а в полях Vue нужен исходный текст: при сохранении он экранируется снова.
    private function decode($value)
    {
        if (is_array($value)) {
            return array_map([$this, 'decode'], $value);
        }

        return is_string($value) ? html_entity_decode($value, ENT_QUOTES, 'UTF-8') : $value;
    }

    private function link($route, $args = '')
    {
        $url = $this->url->link($route, 'token=' . $this->session->data['token'] . ($args ? '&' . $args : ''), true);

        return str_replace('&amp;', '&', $url);
    }
}
