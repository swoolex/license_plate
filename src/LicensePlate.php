<?php
/**
 * +----------------------------------------------------------------------
 * 国内车牌号归属地解析
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace Swx\LicensePlate;


class LicensePlate {
    /**
     * 当前版本号
    */
    private $version = '1.0.1';
    /**
     * 失败原因
    */
    private $error = '';
    /**
     * 结果集
    */
    private $data = [];

    /**
     * 调用入口
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2021-12-03
     * @deprecated 暂不启用
     * @global 无
     * @param string $car_code 车牌号码
     * @return false.array
    */
    public function handle($car_code) {
        if (empty($car_code)) {
            $this->error = '车牌号码为空';
            return false;
        }

        $one = mb_substr($car_code, 0, 1);
        if (!preg_match("/[\x7f-\xff]/", $one)) {
            $one = strtoupper(mb_substr($car_code, 0, 2));
            $two = $one;
        } else {
            $str = ltrim($car_code, $one);
            if ($one == '使') {
                $two = mb_substr($str, 0, 3);
            } else {
                $res = $this->carVif($car_code);
                if (!$res) return false;

                $two = strtoupper(mb_substr($str, 0, 1));
            }
        }
        
        $array = require __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'region_map.php';
        
        if (!isset($array[$one][$two])) {
            $this->error = '车牌号码识别失败，建议通知SW-X开发组成员，更新归属地址库';
            return false;
        }

        $data = $array[$one][$two];
        $this->data = [
            'car_prefix' => $one,
            'car_code' => $two,
            'province' => $data['a'],
            'city' => $data['b']
        ];

        return $this->data;
    }

    /**
     * 获取失败原因描述
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2021-12-03
     * @deprecated 暂不启用
     * @global 无
     * @return string
    */
    public function error() {
        return $this->error;
    }

    /**
     * 成员属性的方式读取结果集
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2021-12-03
     * @deprecated 暂不启用
     * @global 无
     * @param string $name
     * @return mixed
    */
    public function __get($name) {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        return false;
    }

    /**
     * 车牌号码合法性验证
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2021.12.03
     * @deprecated 暂不弃用
     * @global 无
     * @param string $car_code 车牌号
     * @return bool
    */
    private function carVif($car_code){ 
        $regular = "/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新使]{1}[A-Z]{1}[0-9a-zA-Z]{5}$/u";
        preg_match($regular, $car_code, $match);
        if (isset($match[0])) {
            return true;
        }
        #小型新能源车
        $regular = "/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]{1}[A-Z]{1}[DF]{1}[0-9a-zA-Z]{5}$/u";
        preg_match($regular, $car_code, $match);
        if (isset($match[0])) {
            return true;
        }
        #大型新能源车
        $regular = "/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]{1}[A-Z]{1}[0-9a-zA-Z]{5}[DF]{1}$/u";
        preg_match($regular, $car_code, $match);
        if (isset($match[0])) {
            return true;
        }

        $this->error = '车牌号码格式错误';
        return false;
    } 
}
