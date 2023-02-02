<?php
namespace Masso\Http\Controllers\Guest;
use Masso\Http\Controllers\Controller;
use Masso\Mail\OrderPayment;
use Illuminate\Http\Request;
use Masso\WebPay\WebPayTransaction;
use Masso\Client;
use Masso\Payment;
use Masso\Transaction;
use Masso\Log;

class CartController extends Controller
{
    public function check( Request $request )
    {
        $data = $request->all();

        if(!empty($data['TBK_TOKEN'])&&!empty($data['TBK_ID_SESION'])&&!empty($data['TBK_ORDEN_COMPRA']) ):
            $transaction = Transaction::where('token', $data['TBK_TOKEN'])->first();

            if( empty($transaction) )
                return redirect()->route('cart.webpayerror');

            $transaction->response_code = 8;
            $transaction->save();
            session()->flash('error_alert', 'Su transacción ha sido anulada, puede volver a intentarlo si así lo desea.');
            return redirect()->route('public.payment');
        endif;

        if( empty($data['token_ws']) )
            return redirect()->route('cart.webpayerror');

        $transaction = Transaction::where('token', $data['token_ws'])->first();

        if( empty($transaction) )
            return redirect()->route('cart.webpayerror');

        if( $transaction->response_code != 9 )
           return redirect()->route('cart.webpayerror');

        $webpay = new WebPayTransaction;

        try{
            $result = $webpay->getTransactionResult($transaction->token);
        }catch( \Exception $e ){
            $result = false;
        }

        if( is_bool($result) ):
            $transaction->response_code = 8;
            $transaction->save();
            return redirect()->route('cart.webpayerror');
        endif;

        $transaction->response_code = $result->responseCode;
        $transaction->card_number   = $result->cardNumber;
        $transaction->auth_code     = $result->authorizationCode;
        $transaction->payment_type  = $result->paymentTypeCode;
        $transaction->quotes        = $result->installmentsNumber;
        $error = false;


        try{
            $flag = $webpay->acknowledgeTransaction($transaction->token);
        }catch( \Exception $e ){
            $error = true;
        }

        if( $error == true )
            return redirect()->route('cart.webpayerror');

        $transaction->save();
        $events = array();

        $payment = Payment::where('id', $transaction->payment_id)->first();


        if ($transaction->response_code == 0) {
            $payment->status = 'pagado';
            $payment->save();

            if ($payment->type === 'inscription') {
                $payment->updateTicketStock();
                $events = $payment->getEvent();
            }
        }

        if( $payment->status != 'pagado' )
            return redirect()->route('cart.webpayerror');

        session(['payment'=>$payment, 'events' => $events]);

        return redirect()->route('cart.webpayexito');

    }

    public function verify( Request $request  )
    {
        $data = $request->all();

        if(!empty($data['TBK_TOKEN'])&&!empty($data['TBK_ID_SESION'])&&!empty($data['TBK_ORDEN_COMPRA']) ):
            $transaction = Transaction::where('token', $data['TBK_TOKEN'])->first();

            if( empty($transaction) )
                return redirect()->route('cart.webpayerror');

            $transaction->response_code = 8;
            $transaction->save();
            session()->flash('error_alert', 'Su transacción ha sido anulada, puede volver a intentarlo si así lo desea.');
            return redirect()->route('public.payment');
        endif;

        if( empty($data['token_ws']) )
            return redirect()->route('public.payment');

        $transaction = Transaction::where('token', $data['token_ws'])->first();

        if( empty($transaction) )
            return redirect()->route('public.payment');

        $payment = Payment::where('id', $transaction->payment_id)->first();

        if( empty($payment) )
            return redirect()->route('public.payment');

        if( $payment->status == 'pagado' )
            return redirect()->route('cart.webpayexito');

        return redirect()->route('cart.webpayerror');
    }

    public function webpayError(){
        $payment = session('payment');
        return view('guest.webpay-fail', compact('payment'));
    }

    public function webpayExito(){

        $payment = session('payment');
        $events = session('events');

        if( !empty($payment) )
            return view('guest.webpay-exito', compact('payment', 'events'));

        return redirect()->route('cart.webpayerror');
    }

}
