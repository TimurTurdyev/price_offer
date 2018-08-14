<?php
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PriceOfferSheet
{
    const WIDTHS = ['A' => 14, 'B' => 50, 'C' => 16, 'D' => 12, 'E' => 14, 'F' => 14, 'G' => 14, 'H' => 14];
    const DISCOUNT_COLUMNS = ['F', 'G', 'H'];
    const FIRST_ROW = 3;

    private $spreadsheet;
    private $sheet;
    private $row = self::FIRST_ROW;

    public function __construct($title)
    {
        $this->spreadsheet = new Spreadsheet();
        $this->spreadsheet->getProperties()->setTitle($title);

        $style = $this->spreadsheet->getDefaultStyle();
        $style->getFont()->setName('Arial')->setSize(10);
        $style->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        $this->sheet = $this->spreadsheet->getActiveSheet();
        $this->sheet->setTitle(mb_substr(preg_replace('/[\\\\\/?*:\[\]]/', ' ', $title), 0, 31));
        $this->sheet->getDefaultRowDimension()->setRowHeight(25);
    }

    public static function discountPrice($price, $percent)
    {
        return round($price * (100 - $percent) / 100, 2);
    }

    public function header($title, array $columns)
    {
        $sheet = $this->sheet;

        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', $title);

        foreach (array_keys(self::WIDTHS) as $index => $column) {
            $sheet->getColumnDimension($column)->setWidth(self::WIDTHS[$column]);
            $sheet->setCellValue($column . '2', $columns[$index]);
        }
    }

    public function product(array $product, array $percents)
    {
        $row = $this->row++;

        $this->sheet->setCellValue('B' . $row, $product['name']);
        $this->sheet->setCellValueExplicit('C' . $row, $product['model'], DataType::TYPE_STRING);
        $this->sheet->setCellValue('D' . $row, $product['minimum']);
        $this->image($row, $product['image']);
        $this->prices($row, $product['price'], $percents);
    }

    public function save($file)
    {
        (new Xlsx($this->spreadsheet))->save($file);
        $this->spreadsheet->disconnectWorksheets();
    }

    private function prices($row, $price, array $percents)
    {
        $this->sheet->setCellValue('E' . $row, (float)$price);

        foreach (self::DISCOUNT_COLUMNS as $index => $column) {
            if (isset($percents[$index])) {
                $this->sheet->setCellValue($column . $row, self::discountPrice($price, $percents[$index]));
            } else {
                $this->sheet->setCellValue($column . $row, '-');
            }
        }
    }

    private function image($row, $file)
    {
        if (!$file || !is_file($file)) {
            return;
        }

        $this->sheet->getRowDimension($row)->setRowHeight(70);

        $drawing = new Drawing();
        $drawing->setPath($file);
        $drawing->setCoordinates('A' . $row);
        $drawing->setWidth(80);
        $drawing->setOffsetX(10);
        $drawing->setOffsetY(8);
        $drawing->setWorksheet($this->sheet);
    }
}
