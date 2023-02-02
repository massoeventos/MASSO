<?php
namespace Masso\Http\Controllers\Admin;
use Masso\Http\Requests\SurveyStoreRequest;
use Masso\Http\Requests\SurveyUpdateRequest;
use Illuminate\Http\Request;
use Masso\EventSurvey;
use Masso\Event;
use Masso\Log;

class SurveyController extends AdminController
{


    public function index(Request $request)
    {
        $filter     = $request->get('search', false);
        $surveys    = EventSurvey::orderBy('id', 'DESC');
        $events     = Event::pluck('name', 'id');

        if( !empty($filter) )
            $surveys = $surveys->where(function($query) use ($filter) {
                return $query->where('event_id', '=', $filter);
            });

        $surveys = $surveys->paginate(20);
        $title = 'Respuestas de Encuesta';
        return view('admin.general.surveys.index', compact('surveys', 'events', 'title') );  
    }

	public function create( $client )
    {
        $client = Client::where('id', $client)->firstOrFail();
        $title = 'Crear Nuevo Pago';

        return view('admin.general.surveys.create', compact('client','title'));
    }


	public function store( PaymentStoreRequest $request, $client )
    {

    	$data = $request->all();
        $data['status'] = 'pendiente';
        $data['client_id'] = $client;

    	if( $payment = Payment::create( $data ) ):
            Log::create(['area'=>'General', 'module'=>'Pagos', 'action'=>'Creó pago folio '.$payment->id, 'user_id'=>\Auth::user()->id]);
            \Mail::to($payment->client->email)->send(new OrderPayment($payment));
    		\Session::flash('success_alert', 'El pago ha sido generado exitosamente. Se envió notificación a cliente.');
            return \Redirect::route('payments.index');
    	endif;

    	\Session::flash('error_alert', 'Ocurrió un error al procesar la operación. Favor intente nuevamente');
        return \Redirect::back()->withInput();

    }



	public function show($id)
    {
        $payment = Payment::where('id', $id)->first();

        if( empty($payment) )
            abort(404);

        if( isset($_GET['send-mail']) ):

            \Mail::to($payment->client->email)->send(new OrderPayment($payment));
            \Session::flash('success_alert', 'El correo ha sido reenviado al cliente '.$payment->client->mail.'.');
            return \Redirect::back()->withInput();
        endif;

        $title = 'Ficha Pago '.$payment->description;
        return view('admin.general.surveys.show', compact('payment','title'));
    }



    public function destroy($id)
    {

        $payment = Payment::where('status', 'pendiente')->where('id', $id)->first();

        if( !empty($payment) ):
            Log::create(['area'=>'General', 'module'=>'Pagos', 'action'=>'Eliminó pago '.$payment->id, 'user_id'=>\Auth::user()->id]);

            $payment->save();
            $payment->delete();
            \Session::flash('success_alert', 'Se ha eliminado el pago folio #'.$payment->id.'.');
        endif;

        return \Redirect::back()->withInput();
    }
}