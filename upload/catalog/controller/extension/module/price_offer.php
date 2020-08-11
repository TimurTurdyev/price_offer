<?php
ini_set('memory_limit', '256M');
require_once DIR_SYSTEM . 'library/PhpSpreadsheet/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\{Alignment, Border, Fill};
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ControllerExtensionModulePriceOffer extends Controller
{
    public function index()
    {
        if ($this->config->get('price_offer_status')) {
            $this->load->language('extension/module/price_offer');

            $data['breadcrumbs'] = array();

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home')
            );

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/price_offer')
            );

            $data['settings'] = $this->config->get('price_offer_setting');
            $text = $this->config->get('price_offer_text') ?? array();

            if (!empty($text['meta_title'])) {
                $this->document->setTitle($text['meta_title']);
                unset($text['meta_title']);
            } else {
                $this->document->setTitle($this->language->get('heading_title'));
            }

            if (!empty($text['meta_description'])) {
                $this->document->setDescription($text['meta_description']);
                unset($text['meta_description']);
            }

            if (!empty($text['h1'])) {
                $data['heading_title'] = $text['h1'];
                unset($text['h1']);
            } else {
                $data['heading_title'] = $this->language->get('heading_title');
            }

            if (!empty($text['sub_title'])) {
                $data['heading_sub_title'] = $text['sub_title'];
                unset($text['sub_title']);
            } else {
                $data['heading_sub_title'] = $this->language->get('heading_sub_title');
            }

            $data['text'] = $text;

            $data['link_download'] = $this->url->link('extension/module/price_offer/download');

            $data['header'] = $this->load->controller('common/header');
            $data['footer'] = $this->load->controller('common/footer');

            $this->response->setOutput($this->load->view('extension/module/price_offer', $data));
        } else {
            $this->notFound();
        }

    }

    public function download()
    {

        $params = isset($this->request->get['setting']) ? explode('.', $this->request->get['setting']) : null;
        $setting = null;

        if ($params && count($params) > 1) {
            $setting = $this->findSetting($params);
        }

        if ($setting) {
            $this->load->model('tool/image');
            $this->load->model('extension/module/price_offer');

            $filter = array(
                'categories' => explode(',', $setting['categories'])
            );

            $products = $this->model_extension_module_price_offer->products($filter);

            $spreadsheet = new Spreadsheet();

            $spreadsheet->getDefaultStyle()->getFont()->setName('Liberation Mono');
            $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
            $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getDefaultStyle()->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $spreadsheet->getDefaultStyle()->getAlignment()->setWrapText(true);

            $spreadsheet->getProperties()->setTitle('Коммерческое предложение!');
            $spreadsheet->getProperties()->setCreated(date('d.m.Y'));

            $sheet = $spreadsheet->getActiveSheet();
            $sheet->getPageSetup()->setHorizontalCentered(true);
            $sheet->getPageSetup()->setVerticalCentered(true);
            $sheet->getDefaultRowDimension()->setRowHeight(25);

            $company_props = [
                'ООО "Либерти-Пак" | "Л-Пак"',
                'ИНН / КПП  7814690123/781401001',
                'г. Санкт-Петербург, ул. Домостроительная, д. 3, л. Д'
            ];

            $r_index = 1;

            $sheet->getRowDimension($r_index)->setRowHeight(70);

            $sheet->mergeCells('A' . $r_index . ':B' . $r_index);
            $sheet->setCellValue('A' . $r_index, implode(PHP_EOL, $company_props));
            $sheet->getStyle('A' . $r_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('A' . $r_index)->getAlignment()->setWrapText(true);

            $sheet->mergeCells('C' . $r_index . ':E' . $r_index);
            $sheet->setCellValue('C' . $r_index, implode(PHP_EOL, array(
                $this->config->get('config_telephone') . '- телефон',
                $this->config->get('config_email') . '- почта',
                HTTPS_SERVER . '- сайт'
            )));
            $sheet->getStyle('C' . $r_index)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('C' . $r_index)->getAlignment()->setWrapText(true);

            $sheet->mergeCells('F' . $r_index . ':H' . $r_index);

            $drawing = new Drawing();
            $drawing->setPath(DIR_IMAGE . 'logo.png');
            $drawing->setCoordinates('F' . $r_index);
            $drawing->setWidth(280);
            $drawing->setOffsetY($drawing->getHeight() / 4);
            $drawing->setOffsetX(10);
            $drawing->setWorksheet($sheet);

            $r_index += 1;

            $sheet->mergeCells('A' . $r_index . ':H' . $r_index);
            $sheet->setCellValue('A' . $r_index, $setting['name']);
            $r_index += 1;

            // Products Table Header
            $sheet->getColumnDimension('A')->setWidth(13);
            $sheet->setCellValue('A' . $r_index, 'Фото');
            $sheet->getColumnDimension('B')->setWidth(60);
            $sheet->setCellValue('B' . $r_index, 'Наименование');
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->setCellValue('C' . $r_index, 'Артикул');
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->setCellValue('D' . $r_index, 'Упаковка');
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet->setCellValue('E' . $r_index, 'Базовая цена');
            $sheet->getColumnDimension('F')->setAutoSize(true);
            $sheet->setCellValue('F' . $r_index, 'Мелкий опт');
            $sheet->getColumnDimension('G')->setAutoSize(true);
            $sheet->setCellValue('G' . $r_index, 'Средний опт');
            $sheet->getColumnDimension('H')->setAutoSize(true);
            $sheet->setCellValue('H' . $r_index, 'Крупный опт');
            $r_index += 1;

            $sheet->freezePane('A' . $r_index);

            // Products
            foreach ($products as $index => $product) {
                // Image
                if (file_exists(DIR_IMAGE . $product['image'])) {
                    $image = urldecode(str_replace(HTTPS_SERVER . 'image/', DIR_IMAGE . '', $this->model_tool_image->resize($product['image'], 80, 80)));
                    $sheet->getRowDimension($r_index)->setRowHeight(100);
                    $image_drawing = new Drawing();
                    $image_drawing->setPath($image);
                    $image_drawing->setCoordinates('A' . $r_index);
                    $image_drawing->setWidth(80);
                    $image_drawing->setOffsetX(10);
                    $image_drawing->setOffsetY(25);
                    $image_drawing->setWorksheet($sheet);
                }

                // Name
                $sheet->setCellValue('B' . $r_index, htmlspecialchars_decode($product['name']));
                $sheet->setCellValue('C' . $r_index, $product['model']);

                $rows = array(
                    'D' => $product['minimum'],
                    'E' => $product['price']
                );

                $coefficient = explode('|', $product['coefficient'] ?? $setting['coefficient']);
                $discounts = array(
                    'F' => !empty($coefficient[0]) ? (int)$coefficient[0] : '-',
                    'G' => !empty($coefficient[1]) ? (int)$coefficient[1] : '-',
                    'H' => !empty($coefficient[2]) ? (int)$coefficient[2] : '-'
                );

                $options = $product['options'] ? explode('|', $product['options']) : array();

                if (count($options) > 0) {
                    $rows['D'] = '-';
                    $rows['E'] = '-';
                    foreach ($discounts as $key => $discount) {
                        $rows[$key] = '-';
                    }
                } else {
                    foreach ($discounts as $key => $discount) {
                        $rows[$key] = (is_numeric($discount) ? $product['price'] - ($product['price'] / 100 * $discount) : $discount);
                    }
                }

                foreach ($rows as $key => $row) {
                    if ($row) {
                        $sheet->setCellValue($key . $r_index, $row);
                    }
                }

                foreach ($options as $option) {
                    $r_index += 1;
                    $values = explode('=', $option);
                    $sheet->setCellValue('B' . $r_index, $values[0]);
                    $sheet->setCellValue('C' . $r_index, '-');
                    $sheet->setCellValue('D' . $r_index, $product['minimum']);
                    $sheet->setCellValue('E' . $r_index, $values[1]);
                    foreach ($discounts as $key => $discount) {
                        $sheet->setCellValue($key . $r_index, (is_numeric($discount) ? $values[1] - ($values[1] / 100 * $discount) : $discount));
                    }
                }

                $r_index += 1;
            }
//            die();
            $bg_grey = array(
                'fill' => array(
                    'fillType' => Fill::FILL_SOLID,
                    'color' => array('argb' => 'f2f2f2'),
                ),
                'font' => [
                    'bold' => true,
                ],
                'borders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => '373f50'],
                ],
            );
            $styleArray = [
                'borders' => [
                    'outline' => [
                        'borderStyle' => Border::BORDER_THICK,
                        'color' => ['argb' => 'f2f2f2'],
                    ],
                    'allBorders' => ['borderStyle' => Border::BORDER_DASHDOT, 'color' => ['rgb' => 'f2f2f2']],
                ],
            ];

            $sheet->insertNewColumnBefore('A');
            $sheet->insertNewRowBefore(1, 1);

            $sheet->getStyle('A1:AA' . ($r_index + 10))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffffff');
            $sheet->getStyle('B2:I' . $r_index)->applyFromArray($styleArray);
            $sheet->getStyle('B3:I3')->applyFromArray($bg_grey);
            $sheet->getStyle('B4:I4')->applyFromArray(array(
                'font' => [
                    'bold' => true,
                ],
                'borders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => '373f50'],
                ],
            ));


            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $spreadsheet->setActiveSheetIndex(0);

            // Redirect output to a client’s web browser (Xlsx)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . 'lpack-spb_ru-kp(' . date('Y.m.d H:i:s') . ').xlsx"');
            header('Cache-Control: max-age=0');
            // If you're serving to IE 9, then the following may be needed
            header('Cache-Control: max-age=1');

            // If you're serving to IE over SSL, then the following may be needed
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header('Pragma: public'); // HTTP/1.0

            // Выбросим исключение в случае, если не удастся сохранить файл
            try {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            } catch (PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
                //echo $e->getMessage();
                die('Ошибка!');
            }
        } else {
            $this->notFound();
        }
    }

    private function findSetting($params = array())
    {
        $settings = $this->config->get('price_offer_setting');

        if (empty($settings[$params[0]])) {
            return null;
        }

        $name = array();
        $categories = '';
        $coefficient = '';
        $setting = $settings[$params[0]];

        if (!empty($setting['group_name'])) {
            array_push($name, $setting['group_name']);
        }

        if (count($params) === 2 && !empty($setting['groups'][$params[1]]) && !empty($setting['groups'][$params[1]]['categories'])) {
            array_push($name, $setting['groups'][$params[1]]['name']);
            $coefficient = $setting['groups'][$params[1]]['coefficient'];
            $categories = $setting['groups'][$params[1]]['categories'];
        } elseif (count($params) === 3 && !empty($setting['groups'][$params[1]]['items']) && !empty($setting['groups'][$params[1]]['items'][$params[2]])) {
            if ($setting['groups'][$params[1]]['name']) {
                array_push($name, $setting['groups'][$params[1]]['name']);
            }
            array_push($name, $setting['groups'][$params[1]]['items'][$params[2]]['name']);
            $coefficient = $setting['groups'][$params[1]]['items'][$params[2]]['coefficient'];
            $categories = $setting['groups'][$params[1]]['items'][$params[2]]['categories'];
        }

        if ($categories === '') {
            return null;
        }

        return array(
            'name' => join(' > ', $name),
            'coefficient' => $coefficient,
            'categories' => $categories
        );
    }

    public function notFound()
    {
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_error'),
            'href' => $this->url->link('extension/module/price_offer')
        );

        $this->document->setTitle($this->language->get('text_error'));

        $data['heading_title'] = $this->language->get('text_error');

        $data['text_error'] = $this->language->get('text_error');

        $data['button_continue'] = $this->language->get('button_continue');

        $data['continue'] = $this->url->link('common/home');

        $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('error/not_found', $data));
    }

    public function sendToEmail()
    {
        $json = [];
        $file_name = DIR_DOWNLOAD . md5('lpack_spb_kp_' . microtime()) . '.xlsx';


        if ($this->validateEmail()) {
            try {
                $writer = new Xlsx($this->index(true));
                $writer->save($file_name);
                if (file_exists($file_name)) {
                    $mail = new Mail();
                    $mail->protocol = $this->config->get('config_mail_protocol');
                    $mail->parameter = $this->config->get('config_mail_parameter');
                    $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                    $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                    $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                    $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                    $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

                    $mail->setTo($this->request->post['email_to']);
                    $mail->setFrom($this->config->get('config_email'));
                    $mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
                    $mail->setSubject(html_entity_decode('Коммерческое предложение!', ENT_QUOTES, 'UTF-8'));
                    //$mail->setHtml(  );
                    $mail->addAttachment($file_name);
                    $mail->setText('Здравствуйте! Файл с коммерческим предложением прикреплен к этому письму!');
                    $mail->send();
                }

            } catch (PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
                //echo $e->getMessage();
                $this->error['error'] = 'Ошибка!';
            }
        }

        if ($this->error) {
            $json = $this->error;
        } else {
            $json['success'] = 'Файл с коммерческим предложением успешно отправлен на указанну почту!';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function validateEmail()
    {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($this->request->post['email_to'])) {
            if ((utf8_strlen($this->request->post['email_to']) > 96) || !preg_match($this->config->get('config_mail_regexp'), $this->request->post['email_to'])) {
                $this->error['error'] = 'Проверьте емаил на ошибки!';
            }
        } else {
            $this->error['error'] = 'Ошиюка!';
        }

        return !$this->error;
    }

    protected function generateFormulaTotal($cell_array, $sign = ';')
    {
        return '=SUM(' . join($sign, $cell_array) . ')';
    }

    private function clearImage($str)
    {
        $re = '@(' . HTTPS_SERVER . 'image/|cache/|-\d{2,3}x\d{2,3})@';

        return urldecode(preg_replace($re, '', $str));
    }

    private function getPercentPrice($price = 0, $percent = 0)
    {
        if (!$price || !$percent) {
            return $price;
        }

        return ($price / 100 * $percent) + ($price);
    }
}