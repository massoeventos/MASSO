<?php

/**
 * @author     Allware Ltda. (http://www.allware.cl)
 * @copyright  2015 Transbank S.A. (http://www.tranbank.cl)
 * @date       Jan 2015
 * @license    GNU LGPL
 * @version    2.0.1
 */

require_once(__DIR__ . '/soap/soap-wsse.php');
require_once(__DIR__ . '/soap/soap-validation.php');
require_once(__DIR__ . '/soap/soapclient.php');

include('configuration.php');
include('webpay-normal.php');
include('webpay-capture.php');

class WebpayWrap {

    var $configuration, $webpayNormal, $webpayMallNormal, $webpayNullify, $webpayCapture, $webpayOneClick, $webpayCompleteTransaction;

    function __construct($params) {

        $this->configuration = $params;
    }

    public function getNormalTransaction() {
        if ($this->webpayNormal == null) {
            $this->webpayNormal = new WebPayNormal($this->configuration);
        }
        return $this->webpayNormal;
    }

    public function getMallNormalTransaction() {
        if ($this->webpayMallNormal == null) {
            $this->webpayMallNormal = new WebPayMallNormal($this->configuration);
        }
        return $this->webpayMallNormal;
    }


}

class baseBean {
    
}

class getTransactionResult {

    var $tokenInput; //string

}

class getTransactionResultResponse {

    var $return; //transactionResultOutput

}
