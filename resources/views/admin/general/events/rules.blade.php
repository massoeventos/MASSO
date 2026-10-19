@extends('layouts.panel')

@php
    $kindLabel = fn ($kind) => $kind === \Masso\EventTicket::KIND_COMPLEMENTARY ? 'Complementario' : 'Principal';
    $scopeLabel = fn ($scope) => $scope === \Masso\TicketRule::SCOPE_ENTRY ? 'Por entrada' : 'Por compra';
    $oldScope = old('scope', $usesEntries ? \Masso\TicketRule::SCOPE_ENTRY : \Masso\TicketRule::SCOPE_PURCHASE);
@endphp

@section('content')
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h4 class="text-themecolor">{{ $title }}</h4>
        </div>
        <div class="col-md-7 align-self-center text-right">
            <div class="d-flex justify-content-end align-items-center">
                <ol class="breadcrumb d-none d-lg-flex">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('events.index') }}">Eventos</a></li>
                    <li class="breadcrumb-item active">Reglas de compra</li>
                </ol>

                <a href="{{ route('events.edit', $event->id) }}" class="btn btn-warning m-l-15">
                    <i class="fa fa-edit"></i> Editar evento
                </a>
                <a href="{{ route('events.index') }}" class="btn btn-danger m-l-15">
                    <i class="fa fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 flash-container">
            @include('admin.common.flash')
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Reglas</h4>
                    <h6 class="card-subtitle">
                        Restricciones que el formulario de compra valida antes de pagar.
                        @if ($usesEntries)
                            Este evento vende por entradas: las reglas <b>por entrada</b> se revisan dentro de cada entrada (su ticket principal con sus complementarios); las reglas <b>por compra</b>, sobre toda la compra.
                        @endif
                    </h6>

                    @if ($rules->isEmpty())
                        <p class="text-muted m-t-20">Este evento no tiene reglas.</p>
                    @else
                        <table class="table table-sm m-t-20 rules-table">
                            <thead>
                                <tr>
                                    @if ($usesEntries)<th style="width: 120px;">Ámbito</th>@endif
                                    <th>Regla</th>
                                    <th style="width: 60px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rules as $rule)
                                    <tr>
                                        @if ($usesEntries)<td><span class="badge badge-light">{{ $scopeLabel($rule->scope) }}</span></td>@endif
                                        <td>{{ $rule->sentence($name) }}</td>
                                        <td class="text-right">
                                            <form action="{{ route('events.rules.destroy', [$event->id, $rule->id]) }}" method="POST" style="display:inline">
                                                @csrf
                                                @method('DELETE')
                                                <button title="Eliminar regla" class="btn btn-danger btn-xs" onclick="return confirm('¿Eliminar esta regla?')"><i class="fa fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    <form method="POST" action="{{ route('events.rules.store', $event->id) }}" class="row rules-form m-t-20" id="rule-form">
                        @csrf
                        <div class="col-12"><label>Agregar regla</label></div>

                        @if ($usesEntries)
                            <div class="col-md-2">
                                <small>Ámbito</small>
                                <select name="scope" class="form-control">
                                    <option value="{{ \Masso\TicketRule::SCOPE_ENTRY }}" @selected($oldScope === \Masso\TicketRule::SCOPE_ENTRY)>Por entrada</option>
                                    <option value="{{ \Masso\TicketRule::SCOPE_PURCHASE }}" @selected($oldScope === \Masso\TicketRule::SCOPE_PURCHASE)>Por compra</option>
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="scope" value="{{ \Masso\TicketRule::SCOPE_PURCHASE }}">
                        @endif

                        @foreach (['subject' => 'Si se elige / Sujeto', 'target' => 'Objetivo'] as $field => $label)
                            @if ($field === 'target')
                                <div class="col-md-2">
                                    <small>Condición</small>
                                    <select name="rule" class="form-control" id="rule-type">
                                        <option value="requires" @selected(old('rule') === 'requires')>requiere al menos</option>
                                        <option value="excludes" @selected(old('rule') === 'excludes')>no se combina con</option>
                                        <option value="max" @selected(old('rule') === 'max')>máximo</option>
                                        <option value="min" @selected(old('rule') === 'min')>mínimo</option>
                                    </select>
                                </div>
                                <div class="col-md-1 rule-quantity">
                                    <small>Cantidad</small>
                                    <input type="number" name="quantity" class="form-control" min="0" max="100" value="{{ old('quantity', 1) }}">
                                </div>
                            @endif
                            <div class="{{ $usesEntries ? 'col-md-3' : 'col-md-4' }} rule-{{ $field }}">
                                <small>{{ $label }}</small>
                                <select name="{{ $field }}" class="form-control">
                                    <option value="">Seleccione…</option>
                                    <optgroup label="Entradas">
                                        @foreach ($tickets as $ticket)
                                            <option value="ticket:{{ $ticket->id }}" @selected(old($field) === 'ticket:' . $ticket->id)>{{ $ticket->name }} ({{ strtolower($kindLabel($ticket->kind)) }})</option>
                                        @endforeach
                                    </optgroup>
                                    @if ($categories->isNotEmpty())
                                        <optgroup label="Categorías">
                                            @foreach ($categories as $category)
                                                <option value="category:{{ $category->id }}" @selected(old($field) === 'category:' . $category->id)>Categoría {{ $category->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                    <optgroup label="Tipo">
                                        <option value="kind:principal" @selected(old($field) === 'kind:principal')>Entradas principales</option>
                                        <option value="kind:complementario" @selected(old($field) === 'kind:complementario')>Complementarios</option>
                                    </optgroup>
                                </select>
                            </div>
                        @endforeach

                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-dark btn-block">Agregar</button>
                        </div>
                        <small class="col-12 text-muted m-t-10">
                            <b>requiere</b>: si hay sujeto, debe haber al menos esa cantidad del objetivo ·
                            <b>no se combina</b>: sujeto y objetivo no pueden ir juntos ·
                            <b>máximo / mínimo</b>: unidades permitidas del sujeto.
                            Una entrada que está en varias categorías cuenta en todas.
                        </small>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Vista por categoría</h4>
                    <h6 class="card-subtitle">Solo lectura. Las entradas se editan en <a href="{{ route('events.edit', $event->id) }}">Editar evento</a>.</h6>

                    @if ($kindRules->isNotEmpty())
                        <div class="rules-group m-t-20">
                            <h5>En todas las categorías</h5>
                            <ul class="rules-group-rules">
                                @foreach ($kindRules as $rule)
                                    <li>@if ($usesEntries)<span class="badge badge-light">{{ $scopeLabel($rule->scope) }}</span> @endif{{ $rule->sentence($name) }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row m-t-20">
                        @foreach ($groups as $group)
                            @php $groupRules = $rulesFor($group); @endphp
                            <div class="col-md-6">
                                <div class="rules-group">
                                    <h5>
                                        {{ $group['category'] ? $group['category']->name : 'Sin categoría' }}
                                        @if ($group['category'] && !$group['category']->is_active)<span class="badge badge-secondary">Inactiva</span>@endif
                                    </h5>
                                    <ul class="rules-group-tickets">
                                        @foreach ($group['tickets'] as $ticket)
                                            <li class="{{ $ticket->is_active ? '' : 'text-muted' }}">
                                                {{ $ticket->name }}
                                                <span class="badge {{ $ticket->isComplementary() ? 'badge-info' : 'badge-primary' }}">{{ $kindLabel($ticket->kind) }}</span>
                                                @if (!$ticket->is_active)<span class="badge badge-secondary">Inactiva</span>@endif
                                                @if ($ticket->isComplementary() && $ticket->allowedWith->isNotEmpty())
                                                    <small class="text-muted">· con {{ $ticket->allowedWith->pluck('name')->implode(', ') }}</small>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                    @if ($groupRules->isNotEmpty())
                                        <small class="text-uppercase text-muted">Reglas</small>
                                        <ul class="rules-group-rules">
                                            @foreach ($groupRules as $rule)
                                                <li>@if ($usesEntries)<span class="badge badge-light">{{ $scopeLabel($rule->scope) }}</span> @endif{{ $rule->sentence($name) }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    <style>
        .rules-form small { display: block; text-transform: uppercase; font-size: 9px; padding: 0 4px 4px; }
        .rules-form small.text-muted { text-transform: none; font-size: 12px; }
        .rules-group { border: 1px solid #e9ecef; border-radius: 4px; padding: 12px 14px; margin-bottom: 15px; }
        .rules-group h5 { font-size: 15px; margin-bottom: 8px; }
        .rules-group ul { padding-left: 18px; margin-bottom: 8px; font-size: 13px; }
    </style>
    <script>
        // Máximo / mínimo no tienen objetivo; "no se combina" no usa cantidad.
        (function () {
            var form = document.getElementById('rule-form');
            var type = document.getElementById('rule-type');
            function sync() {
                var rule = type.value;
                var hasTarget = rule === 'requires' || rule === 'excludes';
                form.querySelector('.rule-target').style.display = hasTarget ? '' : 'none';
                form.querySelector('.rule-target select').required = hasTarget;
                form.querySelector('.rule-quantity').style.visibility = rule === 'excludes' ? 'hidden' : '';
                form.querySelector('.rule-quantity input').min = rule === 'max' ? 0 : 1;
            }
            type.addEventListener('change', sync);
            sync();
        })();
    </script>
@endsection
