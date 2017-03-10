<?php

namespace Payment\JdPay\Util;

use Payment\JdPay\Util\Rsa;

/**
 * 签名
 */
class Sign {

    public static function signWithoutToHex($params,$unSignKeyList) {
        ksort($params);
        $sourceSignString = self::signString ( $params, $unSignKeyList );
        //echo  "sourceSignString=".htmlspecialchars($sourceSignString)."<br/>";
        //error_log("=========>sourceSignString:".$sourceSignString, 0);
        $sha256SourceSignString = hash ( "sha256", $sourceSignString);
        //error_log($sha256SourceSignString, 0);
        //echo "sha256SourceSignString=".htmlspecialchars($sha256SourceSignString)."<br/>";
        return Rsa::encryptByPrivateKey ($sha256SourceSignString);
    }

    public static function sign($params,$unSignKeyList) {
        ksort($params);
        $sourceSignString = self::signString ( $params, $unSignKeyList );
        error_log($sourceSignString, 0);
        $sha256SourceSignString = hash ( "sha256", $sourceSignString);
        error_log($sha256SourceSignString, 0);
        return Rsa::encryptByPrivateKey ($sha256SourceSignString);
    }

    public static function signString($data, $unSignKeyList) {
        $linkStr="";
        $isFirst=true;
        ksort($data);
        foreach($data as $key=>$value){
            if($value==null || $value==""){
                continue;
            }
            $bool=false;
            foreach ($unSignKeyList as $str) {
                if($key."" == $str.""){
                    $bool=true;
                    break;
                }
            }
            if($bool){
                continue;
            }
            if(!$isFirst){
                $linkStr.="&";
            }
            $linkStr.=$key."=".$value;
            if($isFirst){
                $isFirst=false;
            }
        }
        return $linkStr;
    }
}
