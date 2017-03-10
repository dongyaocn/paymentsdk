<?php

namespace Payment\JdPay;

use Payment\Support\Collection;
use Payment\JdPay\Util\Xml;
use Payment\JdPay\Util\Config;
use Payment\JdPay\Util\TDES;
use Payment\JdPay\Util\Sign;
use Payment\JdPay\Util\Rsa;
use Symfony\Component\HttpFoundation\Request;

/**
 * 京东支付回调处理
 */
class Notify
{
    /**
     * Request instance.
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * Payment notify (extract from XML).
     *
     * @var Collection
     */
    protected $notify;

    /**
     * 是否验签通过
     *
     * @var boolean
     */
    protected $isValid = false;

    /**
     * Constructor.
     *
     * @param Merchant $merchant
     * @param Request  $request
     */
    public function __construct()
    {
        $this->request = Request::createFromGlobals();
    }

    /**
     * Validate the request params.
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     * Return the notify body from request.
     *
     * @return \EasyWeChat\Support\Collection
     *
     * @throws \EasyWeChat\Core\Exceptions\FaultException
     */
    public function getNotifyData()
    {
        if (!empty($this->notify)) {
            return $this->notify;
        }

        $resdata = array();
        $xml = strval($this->request->getContent());

        $this->isValid = Xml::decryptResXml($xml, $resdata);

        //记录日志
        if (true === Config::get('debug')) {
            $this->log('notify xml: ' . var_export($xml, true), 'Info');
            $this->log('notify data: ' . var_export($resdata, true), 'Info');
            $this->log('isValid: ' . $this->isValid, 'Info');
        }

        return $this->notify = new Collection($resdata);
    }

    /**
     * Return the notify body from request.
     *
     * @return \EasyWeChat\Support\Collection
     *
     * @throws \EasyWeChat\Core\Exceptions\FaultException
     */
    public function getReturnData()
    {
        if (!empty($this->notify)) {
            return $this->notify;
        }

        $desKey = Config::get("desKey");
        $keys = base64_decode($desKey);

        $tradeNum  = $this->request->get('tradeNum');
        $amount    = $this->request->get('amount');
        $currency  = $this->request->get('currency');
        $tradeTime = $this->request->get('tradeTime');
        $note      = $this->request->get('note');
        $status    = $this->request->get('status');
        $sign      = $this->request->get('sign');

        $resdata = array();
        if('' !== $tradeNum){
            $resdata['tradeNum'] = TDES::decrypt4HexStr($keys, $tradeNum);
        }

        if('' !== $amount){
            $resdata['amount'] = TDES::decrypt4HexStr($keys, $amount);
        }

        if('' !== $currency){
            $resdata['currency'] = TDES::decrypt4HexStr($keys, $currency);
        }

        if('' !== $tradeTime){
            $resdata['tradeTime'] = TDES::decrypt4HexStr($keys, $tradeTime);
        }

        if('' !== $note){
            $resdata['note'] = TDES::decrypt4HexStr($keys, $note);
        }

        if('' !== $status){
            $resdata['status'] = TDES::decrypt4HexStr($keys, $status);
        }

        if('' !== $tradeNum){
            $resdata['tradeNum'] = TDES::decrypt4HexStr($keys, $tradeNum);
        }

        $strSourceData = Sign::signString($resdata, array());
        $decryptStr = Rsa::decryptByPublicKey($sign);
        $sha256SourceSignString = hash("sha256", $strSourceData);

        if($decryptStr == $sha256SourceSignString){
            $this->isValid = true;
        }

        if (true === Config::get('debug')) {
            $this->log('return data: ' . var_export($resdata, true), 'Info');
            $this->log('isValid: ' . $this->isValid . ";{$decryptStr} == {$sha256SourceSignString}", 'Info');
        }

        return $this->notify = new Collection($resdata);
    }

    /**
     * 写入日志
     * @param  string $message     [description]
     * @param  string $level       [description]
     * @param  string $file_prefix [description]
     * @return [type]              [description]
     */
    protected function log($message = '', $level = 'ERROR', $file_prefix = 'jdpay_notify') {
        $now = date('[ c ]');

        $path = dirname(__DIR__) . '/logs/';
        if (defined('LOG_PATH')) {
            $path = LOG_PATH;
        }

        if (!file_exists($path . date('Y'))) {
            mkdir($path . date('Y'));
        }

        $destination = $path . date('Y') . '/' . $file_prefix . '_' . date('Y_m_d').'.log';
        //检测日志文件大小，超过2M则备份日志文件重新生成
        if(is_file($destination) && filesize($destination) >= 10 * 1024 * 1024) {
            rename($destination, substr($destination, 0, -4) . '_' . time() . '.log');
        }

        error_log("{$now} {$level}: {$message}\r\n", 3, $destination);
    }
}
