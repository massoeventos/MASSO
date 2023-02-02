<?php

namespace Masso\Behaviors;
use Masso\Member;
use Masso\Publication;
use Masso\Payment;

class StatisticsBehavior{

    public static function getBackofficeStatistics(){

        $values = new \StdClass;

        $date_init_today = date('Y-m-01 00:00:00');
        $date_finish_today = date('Y-m-t 23:59:59');
        $date_init_previously = date('Y-m-01 00:00:00', strtotime($date_init_today.' -1 month'));
        $date_finish_previously = date('Y-m-t 23:59:59', strtotime($date_init_previously));

        $values->member = new \stdClass;        
        $values->member->today = intval(Member::whereBetween('created_at', [$date_init_today, $date_finish_today])->count());
        $values->member->previous = intval(Member::whereBetween('created_at', [$date_init_previously, $date_finish_previously])->count());
        $values->member->variation = ($values->member->today > 0) ? (($values->member->previous * 100)/$values->member->today) : 100;
        $values->member->variation = 100 - $values->member->variation;

        $values->publication = new \stdClass;        
        $values->publication->today = intval(Publication::whereBetween('created_at', [$date_init_today, $date_finish_today])->count());
        $values->publication->previous = intval(Publication::whereBetween('created_at', [$date_init_previously, $date_finish_previously])->count());
        $values->publication->variation = ($values->publication->today > 0) ? (($values->publication->previous * 100)/$values->publication->today) : 100;
        $values->publication->variation = 100 - $values->publication->variation;

        $values->payment = new \stdClass;        
        $values->payment->today = intval(Payment::whereBetween('created_at', [$date_init_today, $date_finish_today])->count());
        $values->payment->previous = intval(Payment::whereBetween('created_at', [$date_init_previously, $date_finish_previously])->count());
        $values->payment->variation = ($values->payment->today > 0) ? (($values->payment->previous * 100)/$values->payment->today) : 100;
        $values->payment->variation = 100 - $values->payment->variation;

        $values->paymentamount = new \stdClass;        
        $values->paymentamount->today = intval(Payment::whereBetween('created_at', [$date_init_today, $date_finish_today])->sum('amount'));
        $values->paymentamount->previous = intval(Payment::whereBetween('created_at', [$date_init_previously, $date_finish_previously])->sum('amount'));
        $values->paymentamount->variation = ($values->paymentamount->today > 0) ? (($values->paymentamount->previous * 100)/$values->paymentamount->today) : 100;
        $values->paymentamount->variation = 100 - $values->paymentamount->variation;

        return $values;
    }

    
}