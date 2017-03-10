# paymentsdk
支付SDK



## 京东支付

> 使用京东H5支付（在线支付）

        $attribute = [
            'tradeNum' => $payment_sn,
            'tradeName' => '微仓商品',
            'tradeDesc' => substr($detail, 0, 1024),
            'tradeTime' => date('YmdHis', $now),
            'amount' => strval($unpaid_money),
            'currency' => 'CNY',
            'callbackUrl' => router_url('_payment_jdpay_return'),
            'notifyUrl' => router_url('_payment_jdpay_notify'),
            'ip' => $this->request->getClientIp(),
            'orderType' => '1',
            'userId' => $user_id,
            'expireTime' => '259200', // 86400 * 3
        ];
        $jdOrder = new JdOrder($attribute);
    
        $pay = new JdPay();
        echo $pay->buildRequestFormH5($jdOrder);
> 异步回调处理

     public function jdPayNotify() {
       $pay = new JdPay();
        $response = $pay->handleNotify(function($notify, $successful){
            if ($successful) {
            	#业务逻辑处理...........
            	
                return true;
            } else {
                return 'fail';
            }
        });
    
        $response->send();
    }

> 同步回调处理

    public function jdPayReturn() {
       $pay = new JdPay();
       $response = $pay->handleReturn(function($notify, $successful){
           if ($successful) {
        	   #业务逻辑处理...........
        	
               return true;
           } else {
               return 'fail';
           }
        });
    
    	//页面跳转
    }