<?php
require_once DIR_SYSTEM . 'library/PhpSpreadsheet/vendor/autoload.php';
require_once DIR_SYSTEM . 'library/price_offer/sheet.php';

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
        $data['sections'] = $this->sections();

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
                        'links' => $links,
                    ];
                }
            }

            if ($groups) {
                $sections[] = [
                    'title'  => $section['title'] ?? '',
                    'groups' => $groups,
                ];
            }
        }

        return $sections;
    }

    private function link($key)
    {
        $offer = $this->offer($key);

        if (!$offer) {
            return null;
        }

        return [
            'key'  => $key,
            'name' => htmlspecialchars($offer['caption'], ENT_COMPAT, 'UTF-8'),
            'href' => $this->url->link('extension/module/price_offer/download', 'offer=' . $key),
        ];
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

    // Имя предложения собирается из заголовков раздела и группы.
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
            'percents'   => $this->percents((array)($offer['discounts'] ?? [])),
        ];
    }

    // Скидки мелкого, среднего и крупного опта в процентах, пустая скидка - прочерк в колонке.
    private function percents(array $values)
    {
        $percents = [];

        foreach (array_keys(PriceOfferSheet::DISCOUNT_COLUMNS) as $index) {
            if (is_numeric($values[$index] ?? '')) {
                $percents[$index] = (float)$values[$index];
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
        $sheet->header($offer['name'], $this->columns());

        foreach ($this->model_extension_module_price_offer->getProducts($offer['categories']) as $product) {
            $product['name'] = html_entity_decode($product['name'], ENT_QUOTES, 'UTF-8');
            $product['model'] = html_entity_decode($product['model'], ENT_QUOTES, 'UTF-8');
            $product['image'] = $this->thumb($product['image']);

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

    private function columns()
    {
        $columns = [];

        foreach (['image', 'name', 'model', 'minimum', 'price', 'small', 'medium', 'large'] as $column) {
            $columns[] = $this->language->get('column_' . $column);
        }

        return $columns;
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
}
