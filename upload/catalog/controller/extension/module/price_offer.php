<?php
require_once DIR_SYSTEM . 'library/PhpSpreadsheet/vendor/autoload.php';
require_once DIR_SYSTEM . 'library/price_offer/sheet.php';

class ControllerExtensionModulePriceOffer extends Controller
{
    const TAGS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'span'];
    const SEND_INTERVAL = 30;

    public function index()
    {
        if (!$this->config->get('price_offer_status')) {
            $this->notFound();
            return;
        }

        $this->load->language('extension/module/price_offer');

        $page = (array)$this->config->get('price_offer_page') + array_fill_keys(
            ['h1', 'sub_title', 'meta_title', 'meta_description', 'top', 'bottom'],
            ''
        );

        $this->document->setTitle($page['meta_title'] ?: $this->language->get('heading_title'));
        $this->document->setDescription($page['meta_description']);

        $data['breadcrumbs'] = [
            ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home')],
            ['text' => $this->language->get('heading_title'), 'href' => $this->url->link('extension/module/price_offer')],
        ];

        $data['heading_title'] = $page['h1'] ?: $this->language->get('heading_title');
        $data['sub_title'] = $page['sub_title'] ?: $this->language->get('text_sub_title');
        $data['top'] = html_entity_decode($page['top'], ENT_QUOTES, 'UTF-8');
        $data['bottom'] = html_entity_decode($page['bottom'], ENT_QUOTES, 'UTF-8');
        $data['sections'] = $this->sections();
        $data['send'] = $this->url->link('extension/module/price_offer/send');

        foreach (['text_send', 'text_choose', 'entry_offer', 'entry_email', 'button_send'] as $key) {
            $data[$key] = $this->language->get($key);
        }

        foreach (['column_left', 'column_right', 'content_top', 'content_bottom', 'footer', 'header'] as $part) {
            $data[$part] = $this->load->controller('common/' . $part);
        }

        $this->response->setOutput($this->load->view('extension/module/price_offer', $data));
    }

    public function download()
    {
        $offer = $this->offer($this->request->get['offer'] ?? 0);

        if (!$offer) {
            $this->notFound();
            return;
        }

        $this->load->language('extension/module/price_offer');

        try {
            $file = $this->export($offer);
        } catch (Exception $e) {
            $this->log->write('Price offer: ' . $e->getMessage());
            $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
            $this->response->setOutput($this->language->get('error_file'));
            return;
        }

        $this->response->addHeader('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->response->addHeader('Content-Disposition: attachment; filename="' . basename($file) . '"');
        $this->response->addHeader('Cache-Control: max-age=0');
        $this->response->setOutput(file_get_contents($file));

        $this->removeFile($file);
    }

    public function send()
    {
        $this->load->language('extension/module/price_offer');

        $offer = $this->offer($this->request->post['offer'] ?? 0);
        $email = trim(html_entity_decode($this->request->post['email'] ?? '', ENT_QUOTES, 'UTF-8'));
        $sent = $this->session->data['price_offer_sent'] ?? 0;
        $json = [];

        if ($this->request->server['REQUEST_METHOD'] != 'POST' || !$offer) {
            $json['error'] = $this->language->get('error_offer');
        } elseif (utf8_strlen($email) > 96 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $json['error'] = $this->language->get('error_email');
        } elseif (time() - $sent < self::SEND_INTERVAL) {
            $json['error'] = $this->language->get('error_wait');
        } else {
            $file = '';

            try {
                $file = $this->export($offer);
                $this->mail($email, $offer['name'], $file);

                $this->session->data['price_offer_sent'] = time();
                $json['success'] = sprintf($this->language->get('text_sent'), htmlspecialchars($email, ENT_QUOTES, 'UTF-8'));
            } catch (Exception $e) {
                $this->log->write('Price offer: ' . $e->getMessage());
                $json['error'] = $this->language->get('error_file');
            }

            if ($file) {
                $this->removeFile($file);
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
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

    // Разделы для страницы: группы с заголовками и ссылками на предложения.
    private function sections()
    {
        $sections = [];

        foreach ((array)$this->config->get('price_offer_sections') as $s => $section) {
            $groups = [];

            foreach ((array)($section['groups'] ?? []) as $g => $group) {
                $links = [];

                foreach (array_keys((array)($group['offers'] ?? [])) as $o) {
                    if ($link = $this->link($s . '.' . $g . '.' . $o)) {
                        $links[] = $link;
                    }
                }

                if ($links) {
                    $groups[] = [
                        'title' => $group['title'] ?? '',
                        'tag'   => $this->tag($group['tag'] ?? '', 'h4'),
                        'links' => $links,
                    ];
                }
            }

            if ($groups) {
                $sections[] = [
                    'title'  => $section['title'] ?? '',
                    'tag'    => $this->tag($section['tag'] ?? '', 'h2'),
                    'groups' => $groups,
                ];
            }
        }

        return $sections;
    }

    // name - подпись ссылки, title - полное имя предложения для списка отправки на почту.
    private function link($key)
    {
        $offer = $this->offer($key);

        if (!$offer) {
            return null;
        }

        return [
            'key'   => $key,
            'name'  => htmlspecialchars($offer['caption'], ENT_COMPAT, 'UTF-8'),
            'title' => htmlspecialchars($offer['name'], ENT_COMPAT, 'UTF-8'),
            'href'  => $this->url->link('extension/module/price_offer/download', 'offer=' . $key),
        ];
    }

    private function tag($tag, $default)
    {
        return in_array($tag, self::TAGS, true) ? $tag : $default;
    }

    // offer=раздел.группа.предложение - номера по порядку в настройках.
    private function offer($key)
    {
        $path = explode('.', (string)$key);
        $sections = (array)$this->config->get('price_offer_sections');

        if (count($path) != 3 || !isset($sections[$path[0]]['groups'][$path[1]]['offers'][$path[2]])) {
            return null;
        }

        $section = $sections[$path[0]];
        $group = $section['groups'][$path[1]];

        return $this->prepare($section, $group, $group['offers'][$path[2]]);
    }

    // Имя предложения собирается из заголовков раздела и группы, пустые скидки берутся у группы.
    private function prepare(array $section, array $group, array $offer)
    {
        $categories = array_filter(array_map('intval', (array)($offer['categories'] ?? [])));

        if (!$categories || ($offer['name'] ?? '') === '') {
            return null;
        }

        $names = array_filter([$section['title'] ?? '', $group['title'] ?? '', $offer['name']], 'strlen');

        return [
            'name'       => html_entity_decode(implode(' > ', $names), ENT_QUOTES, 'UTF-8'),
            'caption'    => html_entity_decode($offer['name'], ENT_QUOTES, 'UTF-8'),
            'categories' => $categories,
            'percents'   => $this->percents((array)($offer['discounts'] ?? []), (array)($group['discounts'] ?? [])),
        ];
    }

    // Скидки мелкого, среднего и крупного опта в процентах: своя у предложения или из группы.
    private function percents(array $own, array $defaults)
    {
        $percents = [];

        foreach (array_keys(PriceOfferSheet::DISCOUNT_COLUMNS) as $index) {
            $value = ($own[$index] ?? '') !== '' ? $own[$index] : ($defaults[$index] ?? '');

            if (is_numeric($value)) {
                $percents[$index] = (float)$value;
            }
        }

        return $percents;
    }

    // Пишет xlsx во временную папку, путь потом передается в removeFile().
    private function export(array $offer)
    {
        ini_set('memory_limit', '256M');

        $this->load->model('extension/module/price_offer');
        $this->load->model('tool/image');

        $sheet = new PriceOfferSheet($offer['name']);
        $sheet->header($this->company(), $this->contacts(), $this->logo(), $offer['name'], $this->columns());

        foreach ($this->model_extension_module_price_offer->getProducts($offer['categories']) as $product) {
            $product['name'] = html_entity_decode($product['name'], ENT_QUOTES, 'UTF-8');
            $product['model'] = html_entity_decode($product['model'], ENT_QUOTES, 'UTF-8');
            $product['image'] = $this->thumb($product['image']);

            foreach ($product['options'] as &$option) {
                $option['name'] = html_entity_decode($option['name'], ENT_QUOTES, 'UTF-8');
            }

            unset($option);

            $sheet->product($product, $product['discounts'] ?: $offer['percents']);
        }

        $dir = DIR_CACHE . 'price_offer_' . token(16);
        $file = $dir . '/' . $this->fileName();

        mkdir($dir, 0700);

        try {
            $sheet->save($file);
        } catch (Exception $e) {
            $this->removeFile($file);
            throw $e;
        }

        return $file;
    }

    private function removeFile($file)
    {
        if (is_file($file)) {
            unlink($file);
        }

        if (is_dir(dirname($file))) {
            rmdir(dirname($file));
        }
    }

    private function fileName()
    {
        return 'price-offer-' . date('Y-m-d') . '.xlsx';
    }

    private function company()
    {
        return [
            html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'),
            html_entity_decode($this->config->get('config_address'), ENT_QUOTES, 'UTF-8'),
        ];
    }

    private function contacts()
    {
        return [
            sprintf($this->language->get('text_telephone'), $this->config->get('config_telephone')),
            sprintf($this->language->get('text_email'), $this->config->get('config_email')),
            sprintf($this->language->get('text_site'), $this->config->get('config_url')),
        ];
    }

    private function columns()
    {
        $columns = [];

        foreach (['image', 'name', 'model', 'minimum', 'price', 'small', 'medium', 'large'] as $column) {
            $columns[] = $this->language->get('column_' . $column);
        }

        return $columns;
    }

    private function logo()
    {
        $logo = $this->config->get('config_logo');

        return $logo && is_file(DIR_IMAGE . $logo) ? DIR_IMAGE . $logo : '';
    }

    // resize() отдает ссылку на картинку, а PhpSpreadsheet нужен путь к файлу в кэше.
    private function thumb($image)
    {
        if (!$image || !is_file(DIR_IMAGE . $image)) {
            return '';
        }

        $url = $this->model_tool_image->resize($image, 80, 80);
        $position = strpos($url, '/image/cache/');

        return $position === false ? DIR_IMAGE . $image : DIR_IMAGE . rawurldecode(substr($url, $position + 7));
    }

    private function mail($email, $subject, $file)
    {
        $mail = new Mail();
        $mail->protocol = $this->config->get('config_mail_protocol');
        $mail->parameter = $this->config->get('config_mail_parameter');
        $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
        $mail->smtp_username = $this->config->get('config_mail_smtp_username');
        $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
        $mail->smtp_port = $this->config->get('config_mail_smtp_port');
        $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

        $mail->setTo($email);
        $mail->setFrom($this->config->get('config_email'));
        $mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
        $mail->setSubject(sprintf($this->language->get('mail_subject'), $subject));
        $mail->setText($this->language->get('mail_text'));
        $mail->addAttachment($file);
        $mail->send();
    }
}
