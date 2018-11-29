<?php
require_once "{$_SERVER['DOCUMENT_ROOT']}/PHPExcel/PHPExcel.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/PHPExcel/PHPExcel/Writer/Excel2007.php";

class PHPExcelExt
{
    public $obj_writer;
    public $obj;

    public function __construct()
    {
        $cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
        $cacheSettings = array('memoryCacheSize' => '30MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
        $this->obj = new PHPExcel();
        $this->obj->setActiveSheetIndex(0);
        $this->obj_writer = new PHPExcel_Writer_Excel2007($this->obj);

    }

    private $cur_line = 0;


    private $head_key = [];

    public function head($data)
    {
        $arr = [];
        foreach ($data as $index => $datum) {
            $arr[] = $datum;
            $this->head_key[] = $index;
        }
        $this->line($arr);
    }

    public function addAll($data)
    {
        //dump($data);
        for ($i = 0; $i < count($data); $i++) {
            $this->add($data[$i]);
        }
    }

    public function add($data)
    {
        $head_key = $this->head_key;
        $arr = [];
        foreach ($head_key as $index => $datum) {
            $arr[] = $data[$datum];
        }
        $this->line($arr);
    }

    public function line($data)
    {
        for ($i = 0; $i < count($data); $i++) {
            $item = $data[$i];
            $this->obj->getActiveSheet()->setCellValueExplicitByColumnAndRow($i, $this->cur_line + 1, $item);
        }
        $this->cur_line++;
    }

    public function out($handle)
    {
        $this->obj_writer->save($handle);
    }

    public function httpOut()
    {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="data.xlsx"');
        header('Cache-Control: max-age=0');
        $this->out('php://output');
    }


    public function import($file, $imageFilePath = '/', $encoding = 'UTF-8')
    {
        $objReader = null;
        try {
            $objReader = PHPExcel_IOFactory::createReader('Excel5');//use excel2007 for 2007 format
            $objPHPExcel = $objReader->load($file);
        } catch (Exception $e) {
            $objReader = PHPExcel_IOFactory::createReader('Excel2007');//use excel2007 for 2007 format
            $objPHPExcel = $objReader->load($file);
        }
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();           //取得总行数
        $highestColumn = $sheet->getHighestColumn(); //取得总列数
        $rt_arr = Array();
        for ($j = 1; $j <= $highestRow; $j++)                        //从第一行开始读取数据
        {
            for ($k = 'A'; $k <= $highestColumn; $k++)            //从A列读取数据
            {
                $_value = $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
                if (empty($_value)) {
                    continue;
                }
                if ($encoding != 'UTF-8') {
                    $rt_arr[$j - 1][$k] = mb_convert_encoding($_value . '', $encoding, 'UTF-8');//读取单元格
                } else {
                    $rt_arr[$j - 1][$k] = $_value . '';//读取单元格
                }
            }
        }

        $data = $sheet->toArray();

        $imgData = array();
        foreach ($sheet->getDrawingCollection() as $img) {
            list ($startColumn, $startRow) = PHPExcel_Cell::coordinateFromString($img->getCoordinates());//获取列与行号
            $imageFileName = $img->getCoordinates() . mt_rand(100, 999);
            /*表格解析后图片会以资源形式保存在对象中，可以通过getImageResource函数直接获取图片资源然后写入本地文件中*/
            switch ($img->getMimeType()) {//处理图片格式
                case 'image/jpg':
                case 'image/jpeg':
                    $imageFileName .= '.jpg';
                    imagejpeg($img->getImageResource(), $imageFilePath . $imageFileName);
                    break;
                case 'image/gif':
                    $imageFileName .= '.gif';
                    imagegif($img->getImageResource(), $imageFilePath . $imageFileName);
                    break;
                case 'image/png':
                    $imageFileName .= '.png';
                    imagepng($img->getImageResource(), $imageFilePath . $imageFileName);
                    break;
            }
            $rt_arr[$startRow - 1][$startColumn]['type'] = 'img';//追加到数组中去
            $rt_arr[$startRow - 1][$startColumn]['res'] = $imageFilePath . $imageFileName;//追加到数组中去

            $fileres = file_get_contents($imageFilePath . $imageFileName);
            $rt_arr[$startRow - 1][$startColumn]['base64'] = base64_encode($fileres);//追加到数组中去
        }
        return $rt_arr;
    }
    public function getAllExcel($url){
        //先尝试加载excel
        try{
            $excel_old=PHPExcel_IOFactory::createReader('Excel5');
            //创建文件对象
            $file=$excel_old->load($url);
        } catch (Exception $e){
            $excel_old=PHPExcel_IOFactory::createReader('Excel2007');//use excel2007 for 2007 format
            $file=$excel_old->load($url);
        }
        //选择excel表
        $sheet=$file->getSheet(0);
        $highestRow = $sheet->getHighestRow();           //取得总行数
        $highestColumn = $sheet->getHighestColumn(); //取得总列数

    }

}

