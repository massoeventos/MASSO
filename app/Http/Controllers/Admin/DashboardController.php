<?php
namespace Masso\Http\Controllers\Admin;
use Illuminate\Http\Request;
use Masso\Http\Controllers\Controller;
use Masso\EventEnroll;
use Masso\EventSurvey;
use Masso\EventExpired;
use Masso\Payment;
use Masso\Event;
use Masso\Log;

class DashboardController extends AdminController
{
    
    public function index( Request $request ){


    	$data = [
    		'events-active'		=> Event::where('status', 1)->count(), 
    		'events-expired'	=> EventExpired::count(),
            'events-survey'     => EventSurvey::count(),
    		'payments-active'	=> Payment::where('status', 'pagado')->sum('amount'),
            'enrolled-active'   => EventEnroll::count() ];

    	return view('admin.dashboard.index', compact('data'));
	}

}
