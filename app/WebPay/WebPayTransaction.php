<?php
namespace Masso\WebPay;
use Transbank\Webpay\WebpayPlus\Transaction;
use Transbank\Webpay\WebpayPlus;

class WebPayTransaction{

    protected $configArray;
    protected $certificate;

    public function __construct() {
    }

    public function initTransaction( $amount, $order, $urlReturn, $urlFinal ) {

        if( env('APP_ENV') == 'production' ):
            $conf = \Config::get('webpay.webpay');
            WebpayPlus::configureForProduction($conf['commerce_code'], $conf['private_key']);
        else:
            WebpayPlus::configureForTesting();
        endif;

        if(!env('APP_DEBUG'))
            error_reporting(0);

        try {

            #$result = $webpay->initTransaction($amount, $order, $order, $urlReturn, $urlFinal);
            $result  = WebpayPlus::transaction()->create($order, $order, $amount, $urlReturn);

            session()->put('payment_id', $order);

            if( !empty($result->token) && isset($result->token) )
                return $result;

        } catch (\Exception $e) {
            $error = $e;
        }

        abort('WebPay no disponible');

    }

    public function getTransactionResult( $token ){

        if( env('APP_ENV') == 'production' ):
            $conf = \Config::get('webpay.webpay');
            WebpayPlus::configureForProduction($conf['commerce_code'], $conf['private_key']);
        else:
            WebpayPlus::configureForTesting();
        endif;

        $result = WebpayPlus::transaction()->commit($token);

        return $result;
    }

    public function acknowledgeTransaction( $token ){
        return true;
    }


}
