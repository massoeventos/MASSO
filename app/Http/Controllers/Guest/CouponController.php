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
            'tickets_ids' => 'required|array',
            'tickets_ids.*' => 'integer|exists:events_tickets,id',
        ]);

        $coupon = Coupon::where('code', $request->input('coupon_code'))->first();

        if (!$coupon) {
            return response()->json(['message' => 'Cupón inválido'], 404);
        }

        // Validar fechas
        $today = date('Y-m-d');

        if (
            ($coupon->starts_at && $coupon->starts_at > $today) ||
            ($coupon->ends_at && $coupon->ends_at < $today)
        ) {
            return response()->json(['message' => 'El cupón no está disponible actualmente.'], 400);
        }

        // Tickets válidos para el cupón
        $validTicketIds = $coupon->tickets()->pluck('event_ticket_id')->toArray();

        // Verificar tickets no válidos
        $invalidTicketIds = array_diff($request->input('tickets_ids'), $validTicketIds);

        if (!empty($invalidTicketIds)) {
            $invalidTicketNames = EventTicket::whereIn('id', $invalidTicketIds)->pluck('name')->toArray();

            return response()->json([
                'message' => 'El cupón no es válido para los siguientes tickets:',
                'invalid_tickets' => $invalidTicketNames,
            ], 400);
        }

        return response()->json([
            'message' => 'Cupón válido.',
            'discount_percentage' => $coupon->discount_percentage,
        ], 200);
    }

}