<?php

namespace Masso\Http\Controllers\Admin;
use Masso\Http\Requests\EventUpdateRequest;
use Masso\Http\Requests\EventStoreRequest;
use Illuminate\Http\Request;
use Masso\Behaviors\FileBehavior;
use Masso\Event;
use Masso\EventTicket;
use Masso\TicketRule;
use Masso\EventImage;
use Masso\Log;
use Masso\Http\Requests\CouponUpdateRequest;

class EventController extends AdminController
{


    public function index(Request $request)
    {
        $filter = $request->get('search', false);
        $events = Event::orderBy('id', 'DESC');

        if( !empty($filter) )
            $events = $events->where('name', 'LIKE', '%'.$filter.'%');

        $events = $events->paginate(20);
        $title = 'Listado de Eventos';
        return view('admin.general.events.index', compact('events', 'title') );
    }


    /**
     * Listado de eventos expirados (panel admin)
     */
    public function expired(Request $request)
    {
        $filter = $request->get('search', false);
        $expired = Event::expired()->orderBy('date_finish', 'DESC');

        if( !empty($filter) )
            $expired = $expired->where('name', 'LIKE', '%'.$filter.'%');

        $expired = $expired->paginate(20);
        $title = 'Listado de Eventos Expirados';
        return view('admin.general.expired.index', compact('expired', 'title') );  
    }


	public function create()
    {
        $title = 'Crear Nuevo Evento';
        $ticketsEditor = $this->ticketsEditorData();
        return view('admin.general.events.create', compact('title', 'ticketsEditor'));
    }

    /**
     * Datos iniciales del editor de categorías y entradas (Vue,
     * resources/js/admin/events/TicketsEditor.vue): lo enviado si el form
     * vuelve con errores de validación, o lo guardado del evento. Las claves
     * son los ids reales (o la clave temporal que el editor generó para lo
     * nuevo), y category_keys apunta a claves de categoría -- el mismo
     * contrato que resuelve syncTicketsAndCategories().
     */
    private function ticketsEditorData(?Event $event = null): array
    {
        $oldCategories = old('ticket_categories');
        $oldTickets = old('tickets');
        $validDate = fn ($value) => !empty($value) && strtotime($value) ? substr($value, 0, 10) : date('Y-m-d');
        $flag = fn ($value, $default) => $value === null ? $default : in_array($value, [true, 1, '1', 'true'], true);

        // vendidos de las entradas guardadas (para mostrar vendidos/disponibles)
        $sold = $event
            ? $event->tickets()->withCount(EventTicket::soldCountQuery())->get()->mapWithKeys(fn ($t) => [(string) $t->id => $t->soldCount()])
            : collect();

        if (is_array($oldCategories) || is_array($oldTickets)) {
            $categories = collect($oldCategories ?? [])->map(fn ($c, $key) => [
                'key' => (string) $key,
                'name' => $c['name'] ?? '',
                'name_eng' => $c['name_eng'] ?? '',
                'is_active' => $flag($c['is_active'] ?? null, true),
            ])->values();

            $tickets = collect($oldTickets ?? [])->map(fn ($t, $key) => [
                'key' => (string) $key,
                'name' => $t['name'] ?? '',
                'name_eng' => $t['name_eng'] ?? '',
                'description' => $t['description'] ?? '',
                'description_eng' => $t['description_eng'] ?? '',
                'category_keys' => array_map('strval', array_values((array) ($t['category_ids'] ?? []))),
                'price' => (string) ($t['price'] ?? ''),
                'capacity' => (string) ($t['capacity'] ?? ''),
                'max_per_purchase' => (string) ($t['max_per_purchase'] ?? '1'),
                'sold' => $sold[(string) $key] ?? 0,
                'from' => $validDate($t['from'] ?? null),
                'to' => $validDate($t['to'] ?? null),
                'is_mandatory' => $flag($t['is_mandatory'] ?? null, false),
                'requires_document' => $flag($t['requires_document'] ?? null, false),
                'is_active' => $flag($t['is_active'] ?? null, true),
                'kind' => $t['kind'] ?? EventTicket::KIND_PRINCIPAL,
                'allowed_keys' => array_map('strval', array_values((array) ($t['allowed_with'] ?? []))),
            ])->values();
        } elseif ($event) {
            $categories = $event->ticketCategories->map(fn ($c) => [
                'key' => (string) $c->id,
                'name' => $c->name,
                'name_eng' => $c->name_eng ?? '',
                'is_active' => (bool) $c->is_active,
            ])->values();

            $tickets = $event->tickets()->with('categories', 'allowedWith')->get()->map(fn ($t) => [
                'key' => (string) $t->id,
                'name' => $t->name,
                'name_eng' => $t->name_eng ?? '',
                'description' => $t->description ?? '',
                'description_eng' => $t->description_eng ?? '',
                'category_keys' => $t->categories->pluck('id')->map(fn ($id) => (string) $id)->values()->all(),
                'price' => (string) $t->price,
                'capacity' => (string) $t->capacity,
                'max_per_purchase' => (string) $t->maxPerPurchase(),
                'sold' => $sold[(string) $t->id] ?? 0,
                'from' => $validDate($t->from),
                'to' => $validDate($t->to),
                'is_mandatory' => (bool) $t->is_mandatory,
                'requires_document' => (bool) $t->requires_document,
                'is_active' => (bool) $t->is_active,
                'kind' => $t->kind ?: EventTicket::KIND_PRINCIPAL,
                'allowed_keys' => $t->allowedWith->pluck('id')->map(fn ($id) => (string) $id)->values()->all(),
            ])->values();
        } else {
            $categories = collect();
            $tickets = collect();
        }

        // reglas que nombran cada categoría o alguna de sus entradas (para el filtro)
        $ruleCounts = [];
        if ($event) {
            $rules = $event->ticketRules()->get();
            foreach ($categories as $category) {
                $ticketKeys = $tickets->filter(fn ($t) => in_array($category['key'], $t['category_keys'], true))->pluck('key');
                $ruleCounts[$category['key']] = $rules->filter(fn ($rule) => $rule->mentions(TicketRule::TYPE_CATEGORY, $category['key'])
                    || $ticketKeys->contains(fn ($key) => $rule->mentions(TicketRule::TYPE_TICKET, $key)))->count();
            }
        }

        return [
            'categories' => $categories,
            'tickets' => $tickets,
            'ruleCounts' => (object) $ruleCounts,
            'rulesUrl' => $event ? route('events.rules', $event->id) : null,
        ];
    }

    /**
     * Guarda categorías y entradas enviadas por el editor (crear y editar).
     * Las claves nuevas (temporales del editor) se crean; las que faltan se
     * eliminan (soft delete, como antes). Cada entrada guarda sus categorías
     * en el pivote; category_id queda como la primera (columna legada) y
     * stock como los disponibles (columna legada; lo que manda es capacity).
     * Los complementarios guardan con qué entradas principales se venden
     * (allowed_with, por clave del editor; vacío = con todas).
     */
    private function syncTicketsAndCategories(Event $event, array $data): void
    {
        \DB::transaction(function () use ($event, $data) {
            $categoryIdMap = [];
            foreach ($data['ticket_categories'] ?? [] as $key => $categoryData) {
                $category = $event->ticketCategories()->find($key);
                if ($category) {
                    $category->update($categoryData);
                } else {
                    $category = $event->ticketCategories()->create($categoryData);
                }
                $categoryIdMap[$key] = $category->id;
            }
            $event->ticketCategories()->whereNotIn('id', array_values($categoryIdMap))->delete();

            $ticketIds = [];
            $allowedKeys = [];
            foreach ($data['tickets'] ?? [] as $key => $ticketData) {
                // categorías inexistentes o borradas en esta misma solicitud se descartan
                $categoryIds = collect($ticketData['category_ids'] ?? [])
                    ->map(fn ($categoryKey) => $categoryIdMap[$categoryKey] ?? null)
                    ->filter()->unique()->values()->all();
                $allowedKeys[$key] = $ticketData['kind'] === EventTicket::KIND_COMPLEMENTARY ? ($ticketData['allowed_with'] ?? []) : [];
                unset($ticketData['category_ids'], $ticketData['allowed_with']);

                $ticket = $event->tickets()->withCount(EventTicket::soldCountQuery())->find($key);
                $ticketData['category_id'] = $categoryIds[0] ?? null;
                $ticketData['stock'] = max(0, (int) $ticketData['capacity'] - ($ticket ? $ticket->soldCount() : 0));

                if ($ticket) {
                    $ticket->update($ticketData);
                } else {
                    $ticket = $event->tickets()->create($ticketData);
                }
                $ticket->categories()->sync($categoryIds);
                $ticketIds[$key] = $ticket->id;
            }
            $event->tickets()->whereNotIn('id', array_values($ticketIds))->delete();

            // entradas inexistentes o que ya no son principales se descartan
            $principalKeys = collect($data['tickets'] ?? [])
                ->filter(fn ($t) => $t['kind'] !== EventTicket::KIND_COMPLEMENTARY)->keys()
                ->map(fn ($key) => (string) $key)->all();
            foreach ($allowedKeys as $key => $keys) {
                $ids = collect($keys)->map(fn ($k) => (string) $k)->intersect($principalKeys)
                    ->map(fn ($k) => $ticketIds[$k])->unique()->values()->all();
                EventTicket::find($ticketIds[$key])->allowedWith()->sync($ids);
            }
        });
    }


	public function store( EventStoreRequest $request )
    {

    	$data = $request->all();

        if( $request->hasFile('photo') )
            $data['photo'] = FileBehavior::upload( 'photo', 'images/events/', $request );

    	if( $event = Event::create( $data ) ):

            if( $request->hasFile('banner_image') ){
                $bannerPath = FileBehavior::upload('banner_image', 'images/events/', $request);
                $event->images()->create(['path'=>$bannerPath, 'type'=>'banner', 'position'=>0]);
            }

            if( $request->hasFile('footer_images') ){
                foreach( $request->file('footer_images') as $idx => $file ){
                    if(!$file) continue;
                    $path = FileBehavior::upload($file, 'images/events/');
                    $event->images()->create(['path'=>$path, 'type'=>'footer', 'position'=>$idx]);
                }
            }

            $this->syncTicketsAndCategories($event, $data);

            if( !empty($data['inputs']) )
                foreach( $data['inputs'] as $key => $input )
                    $event->inputs()->updateOrCreate(['id'=>$key], $input);

            Log::create(['area'=>'Eventos', 'module'=>'Eventos', 'action'=>'Creó Evento '.$data['name'], 'user_id'=>\Auth::user()->id]);
    		\Session::flash('success_alert', 'El evento ha sido creado exitosamente.');
            return \Redirect::route('events.index');
    	endif;

    	\Session::flash('error_alert', 'Ocurrió un error al procesar la operación. Favor intente nuevamente');
        return \Redirect::back()->withInput();

    }

	public function edit($id)
    {

        $event = Event::where('id', $id)->first();

        if( empty($event) )
            abort(404);

        $title = 'Editar Evento '.$event->name;
        $ticketsEditor = $this->ticketsEditorData($event);

        return view('admin.general.events.edit', compact('event', 'title', 'ticketsEditor'));
    }


    public function update(EventUpdateRequest $request, $id)
    {
        $event = Event::findOrFail($id);

        $data = $request->only(
            'name',
            'name_eng',
            'location',
            'date_init',
            'date_finish',
            'status',
            'show_location_fields',
            'description',
            'description_eng',
            'tickets',
            'inputs',
            'organize',
            'isUC',
            'is_multiple_selection_ticket',
            'max_selection_ticket',
            'terms_and_conditions',
            'terms_and_conditions_eng',
            'allow_bank_transfer',
            'rules_consider_previous_purchases',
            'ticket_categories'
        );

        if ($request->hasFile('photo')) {
            $data['photo'] = FileBehavior::upload('photo', 'images/events/', $request);
        }

        // Eliminar banner existente si se solicita
        if ($request->has('remove_banner')) {
            $event->images()->where('type', 'banner')->delete();
        }

        // Nueva imagen principal
        if ($request->hasFile('banner_image')) {
            // eliminar banner previo
            $event->images()->where('type','banner')->delete();
            $bannerPath = FileBehavior::upload('banner_image', 'images/events/', $request);
            $event->images()->create(['path'=>$bannerPath, 'type'=>'banner', 'position'=>0]);
        }

        // Eliminar footers seleccionados
        $removeFooterIds = (array) $request->input('remove_footer_ids', []);
        if (!empty($removeFooterIds)) {
            $event->images()->where('type','footer')->whereIn('id', $removeFooterIds)->delete();
        }

        // Nuevas imágenes de pie de página (se agregan al final sin borrar las existentes)
        if ($request->hasFile('footer_images')) {
            $current = $event->images()->where('type','footer')->count();
            foreach ($request->file('footer_images') as $idx => $file) {
                if(!$file) continue;
                $path = FileBehavior::upload($file, 'images/events/');
                $event->images()->create(['path'=>$path, 'type'=>'footer', 'position'=>$current + $idx]);
            }
        }

        $event->fill($data);

        if ($event->save()) {

            $this->syncTicketsAndCategories($event, $data);

            // === INPUTS ===
            $inputIds = [];
            if (!empty($data['inputs'])) {
                foreach ($data['inputs'] as $id => $inputData) {
                    $input = $event->inputs()->updateOrCreate(['id' => $id], $inputData);
                    $inputIds[] = $input->id;
                }

                // Eliminar los inputs que no están en la nueva lista
                $event->inputs()->whereNotIn('id', $inputIds)->delete();
            } else {
                // Si no se envían inputs, eliminarlos todos
                $event->inputs()->delete();
            }

            // Log y redirección
            Log::create([
                'area'   => 'Eventos',
                'module' => 'Eventos',
                'action' => 'Editó evento ' . $event->name,
                'user_id' => \Auth::user()->id
            ]);

            \Session::flash('success_alert', 'El evento ha sido actualizado exitosamente.');
            return \Redirect::route('events.index');
        }

        \Session::flash('error_alert', 'Ocurrió un error al procesar la operación. Favor intente nuevamente.');
        return \Redirect::back()->withInput();

    }

    public function editCoupons($id)
    {

        $event = Event::where('id', $id)->first();

        if( empty($event) )
            abort(404);

        $title = 'Editar cupones para: '.$event->name;

        return view('admin.general.coupons.form', compact('event','title'));
    }
    
    
    public function updateCoupons(CouponUpdateRequest $request, $id)
    {
        $event = Event::findOrFail($id);

        $data = $request->all();

        $couponIds = [];

        if (!empty($data['coupons'])) {
            foreach ($data['coupons'] as $couponId => $couponData) {
                // Extraer los tickets
                $ticketIds = $couponData['coupon_tickets'] ?? [];

                // Quitar los coupon_tickets del array principal antes de guardar el cupón
                unset($couponData['coupon_tickets']);

                // Crear o actualizar el cupón
                $coupon = $event->coupons()->updateOrCreate(
                    ['id' => $couponId],
                    $couponData
                );

                // Sincronizar los tickets relacionados
                $coupon->tickets()->sync($ticketIds);

                $couponIds[] = $coupon->id;
            }

            // Eliminar los cupones que no se incluyeron en la solicitud
            $event->coupons()->whereNotIn('id', $couponIds)->delete();

        } else {
            // Si no se envían cupones, eliminar todos los existentes
            $event->coupons()->delete();
        }

        // Log de acción
        Log::create([
            'area'     => 'Eventos',
            'module'   => 'Eventos',
            'action'   => 'Editó cupones del evento ' . $event->name,
            'user_id'  => \Auth::id(),
        ]);

        \Session::flash('success_alert', 'Cupones actualizados exitosamente.');
        return \Redirect::route('events.index');
    }


    public function destroy($id)
    {

        $events = Event::find($id);

        if( $events->status != 2 ):

            $events->status = 2;
            Log::create(['area'=>'Eventos', 'module'=>'Eventos', 'action'=>'Archivó evento '.$events->name, 'user_id'=>\Auth::user()->id]);
            $events->save();
            \Session::flash('success_alert', 'El evento ha sido archivado exitosamente.');
            return \Redirect::back()->withInput();
        endif;

        if( !empty($events) ):
            Log::create(['area'=>'Eventos', 'module'=>'Eventos', 'action'=>'Eliminó evento '.$events->name, 'user_id'=>\Auth::user()->id]);
            $events->delete();
        endif;

		\Session::flash('success_alert', 'El evento ha sido eliminado exitosamente.');
        return \Redirect::back()->withInput();
    }
}
