<?php

namespace Payment\JdPay\Attribute;

use Payment\Support\Attribute;

use Payment\JdPay\Util\Config;
use Payment\JdPay\Util\Sign;
use Payment\JdPay\Util\TDES;

/**
 * Order
 */
class Order extends Attribute
{
    protected $attributes = [
        "version",
        "merchant",
        "device",
        "tradeNum",
        "tradeName",
        "tradeDesc",
        "tradeTime",
        "amount",
        "currency",
        "note",
        "callbackUrl",
        "notifyUrl",
        "ip",
        "specCardNo",
        "specId",
        "specName",
        "userType",
        "userId",
        "expireTime",
        "orderType",
        "industryCategoryCode",
        "sign",
    ];

    /**
     * Constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes, $needSign = true)
    {
        //设置默认版本号
        if (!isset($attributes['version'])) {
            $attributes['version'] = 'V2.0';
        }

        //从配置获取商户号
        if (!isset($attributes['merchant'])) {
            $attributes['merchant'] = Config::get('merchantNum');
        }

        //参数加密处理
        $attributes = $this->tdes($attributes, $needSign);

        parent::__construct($attributes);
    }

    /**
     * 参数加密处理
     * @param  [type] $param [description]
     * @return [type]        [description]
     */
    public function tdes($param, $needSign = true)
    {
        if ($needSign) {
            $unSignKeyList = array("sign");
            $sign = Sign::signWithoutToHex($param, $unSignKeyList);
            $param["sign"] = $sign;
        }

        $desKey = Config::get("desKey");
        $keys = base64_decode($desKey);

        foreach ($param as $key => $value) {
            $no_need_tdes = array('merchant', 'version', 'sign'); //不需要加密的字段
            if (!in_array($key, $no_need_tdes)) {
                if (!empty($value)) {
                    $param[$key] = TDES::encrypt2HexStr($keys, $value);
                }
            }
        }

        return $param;
    }
}
