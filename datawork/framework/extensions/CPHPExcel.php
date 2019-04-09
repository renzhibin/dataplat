<?php
require_once (Yii::getPathOfAlias('framework.extensions.PHPExcel') . DIRECTORY_SEPARATOR . 'PHPExcel.php');

class CPHPExcel extends PHPExcel {

    public function setDefaultStyle($cell_width = 15, $header_row_num = 1) {
        $cell_width = intval($cell_width);
        if($cell_width <= 10) $cell_width = 10;
        foreach($this->getWorksheetIterator() as $worksheet) {
            // 设置宽度
            for($i = 0; $i < PHPExcel_Cell::columnIndexFromString($worksheet->getHighestColumn()); $i++) {
                $worksheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setWidth($cell_width);
            }
            // 设置边框
            $style = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN
                    )
                )
            );
            $range = "A1:".$worksheet->getHighestColumn().$worksheet->getHighestRow();
            $worksheet->getStyle($range)->applyFromArray($style);
            // 设置header背景颜色
            $style = array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array(
                        "rgb" => '7ba4d8',
                    ),
                ),
            );
            $range = "A1:".$worksheet->getHighestColumn().$header_row_num;
            $worksheet->getStyle($range)->applyFromArray($style);
        }
        return $this;
    }

    public function dumpToClient($filename) {
        $filename = trim($filename);
        if(substr($filename, -5) !== ".xlsx") {
            $filename = "{$filename}.xlsx";
        }
        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($this, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
}
