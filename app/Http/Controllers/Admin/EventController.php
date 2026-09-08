<?php

namespace Masso\Http\Controllers\Admin;
use Masso\Http\Requests\EventUpdateRequest;
use Masso\Http\Requests\EventStoreRequest;
use Illuminate\Http\Request;
use Masso\Behaviors\FileBehavior;
use Masso\Event;
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
        return view('admin.general.events.create', compact('title'));
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

            \DB::transaction(function () use ($event, $data) {
                $categoryIdMap = [];
                if( !empty($data['ticket_categories']) )
                    foreach( $data['ticket_categories'] as $key => $categoryData )
                        $categoryIdMap[$key] = $event->ticketCategories()->create($categoryData)->id;

                if( !empty($data['tickets']) )
                    foreach( $data['tickets'] as $ticket ) {
                        if( !empty($ticket['category_id']) )
                            // El evento recién se está creando: cualquier category_id válido debe
                            // resolver contra una categoría enviada en esta misma solicitud.
                            $ticket['category_id'] = $categoryIdMap[$ticket['category_id']] ?? null;
                        $event->tickets()->create($ticket);
                    }
            });

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

        return view('admin.general.events.edit', compact('event','title'));
    }


    public function update(EventUpdateRequest $request, $id)
    {
        $event = Event::findOrFail($id);

        $data = $request->only(
            'name',
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

            // === TICKET CATEGORIES + TICKETS ===
            // Nota: el id enviado desde el formulario para una categoría nueva es un
            // pseudo-id generado en cliente (Date.now()), que excede el rango de la
            // columna `id` (int unsigned). No se usa como PK: se resuelve a un id real
            // vía $categoryIdMap para poder enlazar los tickets a la categoría recién creada.
            // Se envuelve en una transacción para que un error a mitad de camino (p.ej. un
            // ticket referenciando una categoría inválida) no deje categorías huérfanas creadas.
            \DB::transaction(function () use ($event, $data) {
                $categoryIds = [];
                $categoryIdMap = [];
                if (!empty($data['ticket_categories'])) {
                    foreach ($data['ticket_categories'] as $key => $categoryData) {
                        $category = $event->ticketCategories()->find($key);
                        if ($category) {
                            $category->update($categoryData);
                        } else {
                            $category = $event->ticketCategories()->create($categoryData);
                        }
                        $categoryIdMap[$key] = $category->id;
                        $categoryIds[] = $category->id;
                    }

                    // Las categorías removidas del formulario se eliminan, pero sus tickets
                    // quedan sin categoría (no se eliminan).
                    $event->tickets()->whereNotNull('category_id')->whereNotIn('category_id', $categoryIds)->update(['category_id' => null]);
                    $event->ticketCategories()->whereNotIn('id', $categoryIds)->delete();
                } else {
                    $event->tickets()->whereNotNull('category_id')->update(['category_id' => null]);
                    $event->ticketCategories()->delete();
                }

                // === TICKETS ===
                $ticketIds = [];
                if (!empty($data['tickets'])) {
                    foreach ($data['tickets'] as $id => $ticketData) {
                        if (!empty($ticketData['category_id'])) {
                            if (isset($categoryIdMap[$ticketData['category_id']]))
                                $ticketData['category_id'] = $categoryIdMap[$ticketData['category_id']];
                            elseif (!in_array($ticketData['category_id'], $categoryIds))
                                // Categoría inexistente o recién eliminada en esta misma solicitud: no se confía en el valor recibido.
                                $ticketData['category_id'] = null;
                        }
                        $ticket = $event->tickets()->updateOrCreate(['id' => $id], $ticketData);
                        $ticketIds[] = $ticket->id;
                    }

                    // Eliminar los tickets que no están en la nueva lista
                    $event->tickets()->whereNotIn('id', $ticketIds)->delete();
                } else {
                    // Si no se envían tickets, eliminarlos todos
                    $event->tickets()->delete();
                }
            });

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
