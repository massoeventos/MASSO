<?php

namespace Masso\Http\Controllers\Guest;
use Masso\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Masso\Coupon;
use Masso\EventTicket;

class CouponController extends Controller
{
    public function validateCoupon(Request $request)
    {
        $this->validate($request, [
            'coupon_code' => 'required|string',
            'event_id' => 'required|numeric',
            'tickets_ids' => 'required|array',
            'tickets_ids.*' => 'integer|exists:events_tickets,id',
        ]);

        $coupon = Coupon::where([
            'code' => $request->input('coupon_code'),
            'event_id' => $request->input('event_id'),
        ])->first();

        if (!$coupon) {
            return response()->json(['message' => 'Cupón inválido'], 404);
        }

        $result = $coupon->validateForTickets($request->input('tickets_ids'));

        if (!$result['valid']) {
            return response()->json([
                'message' => $result['message'],
                'invalid_tickets' => $result['invalid_ticket_names']
            ], 400);
        }

        return response()->json([
            'message' => $result['message'],
            'discount_percentage' => $result['discount_percentage']
        ], 200);
    }

}