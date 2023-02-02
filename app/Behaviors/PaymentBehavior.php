<?php

namespace Masso\Behaviors;
use Masso\Member;
use Masso\Publication;
use Masso\Payment;

class PaymentBehavior{

    public static function countPendingPaymentsByFee( $fee_id, $time ){

    	return Member::whereHas("payments", function($q) use ($fee_id) {
            		$q->where('fee_id', $fee_id);
            	}, '<', 1)
    			->whereHas('profile', function ($query) use ($time) {
					$query->where('admission', '<=', $time);
				})->count();

    }

    public static function getPendingPaymentsByFee( $fee_id, $time ){

    	return Member::whereHas("payments", function($q) use ($fee_id) {
            		$q->where('fee_id', $fee_id);
            	}, '<', 1)
    			->whereHas('profile', function ($query) use ($time) {
					$query->where('admission', '<=', $time);
				})->orderBy('code', 'DESC')->get();

    }


    public static function countSuccessPaymentsByFee( $fee_id, $time ){

    	return Member::whereHas("payments", function($q) use ($fee_id) {
              $q->where('fee_id', $fee_id);
            }, '>', 0)->count();

    }

    public static function getSuccessPaymentsByFee( $fee_id, $time ){

        return Payment::where('fee_id', $fee_id)->orderBy('date', 'DESC')->orderBy('created_at', 'DESC')->get();

    }

    

    public static function sumSuccessPaymentsByFee( $fee_id, $time ){

        return Payment::where('fee_id', $fee_id)->sum('amount');

    }

    
}