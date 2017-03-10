<?php

namespace Payment\JdPay;

use Payment\JdPay\Attribute\Order;

use Payment\JdPay\Util\Xml;
use Payment\JdPay\Util\Config;
use Payment\JdPay\Util\Http;

use Symfony\Component\HttpFoundation\Response;

class Pay {

    const API_PAY = 'https://wepay.jd.com/jdpay/saveOrder';
    const API_PAY_H5 = 'https://h5pay.jd.com/jdpay/saveOrder';
    const API_PAY_UNIORDER = 'https://paygate.jd.com/service/uniorder'; //统一下单接口
    const API_ORDER_QUERY = 'https://paygate.jd.com/service/query';
    const API_ORDER_REFUND = 'https://paygate.jd.com/service/refund';

    private $orderId = ''; //预支付ID

    /**
     * 使用PC版支付方式
     *
     * @param  Order  $order [description]
     * @return [type]        [description]
     */
    public function buildRequestForm(Order $order)
    {
        $action = self::API_PAY;
        $form_html = "<form action='{$action}' method='post' id='batchForm' >";

        $attrs = $order->all();
        foreach ($attrs as $key => $value) {
            $form_html .= "<input type='hidden' name='{$key}' value='{$value}'/><br/>";
        }
        $form_html .= '</form>';

        $html = '';
        $html .= '<!DOCTYPE html>';
        $html .= '<html>';
        $html .= '<head><meta charset="UTF-8"><title>京东支付</title></head><body onload="autosubmit()">';
        $html .= $form_html;
        $html .= '<script>function autosubmit(){document.getElementById("batchForm").submit();}</script>';
        $html .= '</body></html>';

        return $html;
    }

    /**
     * 使用h5版支付方式
     * @param  Order  $order [description]
     * @return [type]        [description]
     */
    public function buildRequestFormH5(Order $order)
    {
        $action = self::API_PAY_H5;
        $form_html = "<form action='{$action}' method='post' id='batchForm' >";

        $attrs = $order->all();
        foreach ($attrs as $key => $value) {
            $form_html .= "<input type='hidden' name='{$key}' value='{$value}'/><br/>";
        }
        $form_html .= '</form>';

        $html = '';
        $html .= '<!DOCTYPE html>';
        $html .= '<html>';
        $html .= '<head><meta charset="UTF-8"><title>京东支付</title></head><body onload="autosubmit()">';
        $html .= $form_html;
        $html .= '<script>function autosubmit(){document.getElementById("batchForm").submit();}</script>';
        $html .= '</body></html>';

        return $html;
    }

    /**
     * 获取预支付订单号
     * @param  Order  $order [description]
     * @return [type]        [description]
     */
    public function getOrderId(Order $order) {
        $reqXmlStr = Xml::encryptReqXml($order->all());

        $http = new Http();
        list ($return_code, $return_content) = $http->http_post_data(self::API_PAY_UNIORDER, $reqXmlStr);

        $resData;
        $flag = Xml::decryptResXml($return_content, $resData);
        if ($flag) {
            $this->orderId = $resData['orderId'];
        }

        return $this->orderId;
    }

    /**
     * 获取orderId md5签名值
     * @return [type] [description]
     */
    public function getSignData()
    {
        $merchant = Config::get('merchantNum');
        $merchant = Config::get('md5Key');

        return md5('merchant=' . $merchant . '&orderId=' . $this->orderId . '&key=' . $md5Key);
    }

    /**
     * 支付异步回调处理
     * @param  callable $callback [description]
     * @return [type]             [description]
     */
    public function handleNotify(callable $callback)
    {
        $notify = $this->getNotify();

        $notify_data = $notify->getNotifyData();
        $response = 'fail';
        if (!$notify->isValid()) {
            $response = '验证签名失败';
        } else {
            $successful = $notify_data->get('status') === '2';

            $handleResult = call_user_func_array($callback, [$notify_data, $successful]);
            if (is_bool($handleResult) && $handleResult) {
                $response = 'ok';
            } else {
                $response = $handleResult;
            }
        }

        return new Response(strval($response));
    }

    /**
     * 支付同步回调处理
     * @param  callable $callback [description]
     * @return [type]             [description]
     */
    public function handleReturn(callable $callback)
    {
        $notify = $this->getNotify();

        $return_data = $notify->getReturnData();
        $response = 'fail';
        if (!$notify->isValid()) {
            $response = '验证签名失败';
        } else {
            $successful = $return_data->get('status') === '0';

            $handleResult = call_user_func_array($callback, [$return_data, $successful]);
            if (is_bool($handleResult) && $handleResult) {
                $response = 'ok';
            } else {
                $response = $handleResult;
            }
        }

        return new Response(strval($response));
    }

    /**
     * 获取支付回调参数对象
     * @return [type] [description]
     */
    public function getNotify()
    {
        return new Notify();
    }
}