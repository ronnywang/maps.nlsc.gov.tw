<?php

include(__DIR__ . '/../libs/proj4php/proj4php.php');

class Crawler
{
    protected $_proj4 = null;
    protected $_projSrc = null;
    protected $_projDst = null;

    public function proj($x, $y, $country_id)
    {
        $url = 'http://maps.nlsc.gov.tw/O09/pro/transcoord.action';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "type=84&x={$x}&y={$y}&city={$country_id}");
        $ret = curl_exec($curl);
        $doc = new DOMDocument;
        @$doc->loadXML($ret);
        $ret = new StdClass;
        $ret->x = $doc->getElementsByTagName('coordX')->item(0)->nodeValue;
        $ret->y = $doc->getElementsByTagName('coordY')->item(0)->nodeValue;
        return $ret;
        /*
        if (!$this->_proj4) {
            $this->_proj4 = new Proj4php();
            $this->_projSrc = new Proj4phpProj('EPSG:TM2', $this->_proj4);
            $this->_projDst = new Proj4phpProj('WGS84', $this->_proj4);
        }

        $pointSrc = new proj4phpPoint($x, $y);
        $pointDst = $this->_proj4->transform($this->_projSrc, $this->_projDst, $pointSrc);
        return $pointDst;
         */
    }

    public function getCities()
    {
        return array(
            'A' => '臺北市',
            'B' => '臺中市',
            'C' => '基隆市',
            'D' => '臺南市',
            'E' => '高雄市',
            'F' => '新北市',
            'G' => '宜蘭縣',
            'H' => '桃園縣',
            'I' => '嘉義市',
            'J' => '新竹縣',
            'K' => '苗栗縣',
            'M' => '南投縣',
            'N' => '彰化縣',
            'O' => '新竹市',
            'P' => '雲林縣',
            'Q' => '嘉義縣',
            'T' => '屏東縣',
            'U' => '花蓮縣',
            'V' => '臺東縣',
            'W' => '金門縣',
            'X' => '澎湖縣',
            'Z' => '連江縣',
        );
    }

    public function getCategories()
    {
        return array(
            '交通運輸',
            '公共建設',
            '民眾服務',
            '特殊設施',
            '工商活動',
        );
    }

    public function getClassByCategory($category, $country = '')
    {
        $url = 'http://maps.nlsc.gov.tw/O09/pro/markClass.action';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "type=" . urlencode($category) . '&country=' . urlencode($country));
        $ret = curl_exec($curl);
        $doc = new DOMDocument;
        @$doc->loadXML($ret);

        $classes = array();
        foreach ($doc->getElementsByTagName('Code') as $code_dom) {
            $classes[$code_dom->getElementsByTagName('id')->item(0)->nodeValue] = $code_dom->getElementsByTagName('name')->item(0)->nodeValue;
        }
        return $classes;
    }

    public function getLandmarks($country_id, $class_id)
    {
        $url = 'http://maps.nlsc.gov.tw/O09/pro/landmarkquery.action';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "type=" . intval($class_id) . '&country=' . urlencode($country_id));
        $ret = curl_exec($curl);
        preg_match_all("#catchCoordfly\('([^']*)','([^']*)','([^']*)','([^']*)','([^']*)'#", $ret, $matches);
        $landmarks = array();
        foreach ($matches[0] as $id => $word) {
            $landmark = array(
                $matches[2][$id],
                $matches[3][$id],
                $matches[5][$id],
            );
            $landmarks[] = $landmark;
        }
        return $landmarks;
    }


    public function main()
    {
        $output = fopen('php://output', 'w');
        fputcsv($output, array(
            '縣市代號', 
            '縣市',
            '大類別',
            '小類別代號',
            '小類別名稱',
            '地標名稱',
            '緯度',
            '經度',
        ));
        foreach ($this->getCities() as $city_id => $city_name) {
            foreach ($this->getCategories() as $category) {
                foreach ($this->getClassByCategory($category) as $class_id => $class_name) {
                    foreach ($this->getLandmarks($city_id, $class_id) as $landmark) {
                        $proj = ($this->proj($landmark[0], $landmark[1], $city_id));
                        fputcsv($output, array(
                            $city_id,
                            $city_name,
                            $category,
                            $class_id,
                            $class_name,
                            $landmark[2],
                            $proj->y,
                            $proj->x,
                        ));
                    }
                }
            }
        }
    }
}

$c = new Crawler;
$c->main();
