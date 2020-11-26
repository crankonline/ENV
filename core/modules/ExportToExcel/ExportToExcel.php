<?php


namespace Environment\modules\ExportToExcel;


use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportToExcel
{
    const
     ONE_COL      = 1,
     TWO_COL      = 2,
     THREE_COL    = 3,
     FOUR_COL     = 4,
     FIVE_COL     = 5,
     SIX_COL      = 6;

    function getExcel (array $Arr , $def) {
        $rb = [
            'font' => [
                'name' => 'Arial',
                'bold' => true,
                'italic' => false,
                'underline' => Font::UNDERLINE_DOUBLE,
                'strikethrough' => false,
                'color' => [
                    'rgb' => '228B22'
                ]
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => [
                        'rgb' => '228B22'
                    ]
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ]
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getStyle('A1')->applyFromArray($rb);
        $sheet->getStyle('B1')->applyFromArray($rb);
        $sheet->getStyle('C1')->applyFromArray($rb);
        $sheet->getStyle('D1')->applyFromArray($rb);
        $sheet->getStyle('E1')->applyFromArray($rb);
        $sheet->getStyle('F1')->applyFromArray($rb);

        if ($def  == 'dealer') {
            $sheet->setTitle('Платежи дилерки');
            $sheet->setCellValueByColumnAndRow(self::ONE_COL, self::ONE_COL, 'Аккаунт');
            $sheet->setCellValueByColumnAndRow(self::TWO_COL, self::ONE_COL, 'ИНН');
            $sheet->setCellValueByColumnAndRow(self::THREE_COL, self::ONE_COL, 'Дата');
            $sheet->setCellValueByColumnAndRow(self::FOUR_COL, self::ONE_COL, 'Система');
            $sheet->setCellValueByColumnAndRow(self::FIVE_COL, self::ONE_COL, 'ID транзакции');
            $sheet->setCellValueByColumnAndRow(self::SIX_COL, self::ONE_COL, 'Сумма');

            $row = 2;
            foreach ($Arr as $index => $rs) {
                $date = date_create($rs['DateTime']);
                $sheet->setCellValueExplicitByColumnAndRow(self::ONE_COL, $row, $rs['Account'], DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(self::TWO_COL, $row, $rs['inn'], DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(self::THREE_COL, $row, date_format($date, 'Y-m-d H:i:s'), DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(self::FOUR_COL, $row, $rs['Name'], DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(self::FIVE_COL, $row, $rs['TXNID'], DataType::TYPE_STRING);
                $sheet->setCellValueByColumnAndRow(self::SIX_COL, $row, $rs['Sum']);
                $row++;
            }
            $lets = 'F';
            $stCol = $lets.'2';
        } else {
            $sheet->setTitle('Платежи Сочи');
            $sheet->setCellValueByColumnAndRow(self::ONE_COL, self::ONE_COL, 'Аккаунт');
            $sheet->setCellValueByColumnAndRow(self::TWO_COL, self::ONE_COL, 'Дата');
            $sheet->setCellValueByColumnAndRow(self::THREE_COL, self::ONE_COL, 'Система');
            $sheet->setCellValueByColumnAndRow(self::FOUR_COL, self::ONE_COL, 'ID Биллинг');
            $sheet->setCellValueByColumnAndRow(self::FIVE_COL, self::ONE_COL, 'Сумма');

            $row = 2;
            foreach ($Arr as $index => $rs) {
                $date = date_create($rs['PayDateTime']);
                $sheet->setCellValueExplicitByColumnAndRow(self::ONE_COL, $row, $rs['Account'], DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(self::TWO_COL, $row, date_format($date, 'Y-m-d H:i:s'), DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(self::THREE_COL, $row, $rs['Name'], DataType::TYPE_STRING);
                $sheet->setCellValueExplicitByColumnAndRow(self::FOUR_COL, $row, $rs['BillingID'], DataType::TYPE_STRING);
                $sheet->setCellValueByColumnAndRow(self::FIVE_COL, $row, $rs['Sum']);
                $row++;
            }
            $lets = 'E';
            $stCol = $lets.'2';

        }
        $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);
        $res = $lets.$row;
        $sheet->setCellValue($res, "=SUM($stCol:$res)" );
        $sheet->getStyle($res)->applyFromArray($rb);
        foreach ($cellIterator as $cell) {
            $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
        }
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();
        echo json_encode('data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,'.base64_encode($xlsData));
        exit();

    }

}