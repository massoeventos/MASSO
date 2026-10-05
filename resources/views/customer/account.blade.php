@extends('layouts.customer-panel')

@section('content')

<style type="text/css">
    .page-titles h4.text-themecolor {
        font-size: 1.15rem;
    }
    .card-title {
        font-size: 1.05rem;
    }
    .purchase-row {
        display: flex;
        gap: 15px;
        border: 1px solid #eee;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 12px;
    }
    .purchase-row-photo {
        width: 90px;
        height: 90px;
        flex-shrink: 0;
        border-radius: 6px;
        overflow: hidden;
        background: #f5f5f5;
    }
    .purchase-row-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .purchase-row-body {
        flex: 1;
        min-width: 0;
    }
    @media (max-width: 480px) {
        .purchase-row-photo {
            width: 60px;
            height: 60px;
        }
    }
    .purchase-row .badge {
        font-size: 12px;
        display: inline-block;
        margin-bottom: 5px;
    }
    .purchase-tickets {
        font-size: 14px;
        color: #6c757d;
        margin: 5px 0 0 0;
        padding-left: 18px;
    }
    .btn-pill-action {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-top: 8px;
        border: 1px solid transparent;
        border-radius: 20px;
        padding: 3px 11px;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.4;
        background: transparent;
        cursor: pointer;
        transition: background-color .15s ease, color .15s ease;
    }
    .btn-pill-action svg {
        width: 10px;
        height: 10px;
        flex-shrink: 0;
    }
    .btn-pill-action.is-retry {
        border-color: #e7015e;
        color: #e7015e;
    }
    .btn-pill-action.is-retry:hover {
        background-color: #e7015e;
        color: #fff;
    }
    .btn-pill-action.is-resend {
        border-color: #adb5bd;
        color: #6c757d;
    }
    .btn-pill-action.is-resend:hover {
        background-color: #6c757d;
        color: #fff;
    }
</style>

<div class="row page-titles">
    <div class="col-md-5 align-self-center">
        <h4 class="text-themecolor">Mis Compras</h4>
    </div>
    <div class="col-md-7 align-self-center text-right">
        <div class="d-flex justify-content-end align-items-center">
            <ol class="breadcrumb d-none d-lg-flex">
                <li class="breadcrumb-item"><a href="{{ route('customer.account') }}">Mi Cuenta</a></li>
                <li class="breadcrumb-item active">Mis Compras</li>
            </ol>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        @include('admin.common.flash')
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Hola, {{ $customer->name ?: $customer->email }}</h4>
                <h6 class="card-subtitle mb-3">Este es el historial de tus compras hechas con esta cuenta.</h6>

                @if($payments->isEmpty())
                    <p class="text-muted mb-0">Todavía no tienes compras asociadas a esta cuenta. Tu historial empieza a partir de tu primera compra con este correo.</p>
                @else
                    @foreach($payments as $payment)
                        @php
                            $statusLabels = [
                                'pagado' => ['Pagado', 'success'],
                                'pending' => ['Pendiente', 'warning'],
                            ];
                            [$statusLabel, $statusClass] = $statusLabels[$payment->status] ?? [ucfirst($payment->status), 'secondary'];
                        @endphp
                        <div class="purchase-row">
                            <div class="purchase-row-photo">
                                <img src="{{ $payment->event->photo ?? '' }}" onerror="this.src='/images/shap/news_memphis2.png'" alt="">
                            </div>
                            <div class="purchase-row-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ $payment->description }}</strong>
                                        <div class="text-muted" style="font-size: 13px;">
                                            {{ $payment->created_at ? $payment->created_at->format('d-m-Y H:i') : '' }}
                                        </div>
                                        @if($payment->details->isNotEmpty())
                                            <ul class="purchase-tickets">
                                                @foreach($payment->details as $detail)
                                                    <li>{{ $detail->ticket ? $detail->ticket->name : 'Ticket' }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-{{ $statusClass }}">{{ $statusLabel }}</span>
                                        <div>${{ number_format($payment->amount, 0, ',', '.') }}</div>
                                        @if($payment->status === 'pending')
                                            <form method="POST" action="{{ route('customer.payment.retry', $payment->id, false) }}" class="mb-0">
                                                @csrf
                                                @if(in_array($payment->managment, ['transfer', 'transfer2']))
                                                    <button type="submit" class="btn-pill-action is-resend" title="Reenviar los datos de la transferencia a tu correo">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M3 7l9 6 9-6"></path></svg>
                                                        Reenviar
                                                    </button>
                                                @else
                                                    <button type="submit" class="btn-pill-action is-retry" title="Generar un nuevo intento de pago para esta compra">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                                                        Reintentar
                                                    </button>
                                                @endif
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
