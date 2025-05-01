<?php

namespace Masso\Console\Commands;

use Illuminate\Console\Command;
use Masso\Behaviors\Facto;

class EmitTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'masso:emit-tickets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Emitir boletas de pago pendientes de emisión.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Retrieve all pending task of tickets
        $tasks = \Masso\Task::where('is_pending', true)
            ->where('task_name', 'Emitir Boleta')
            ->take(10)
            ->get();
           
        \Log::debug('IDs de object_id encontrados:', $tasks->pluck('object_id')->toArray());
          

        \Log::info('- -');
        \Log::info('Ejecutando tarea de emisión de boletas - '.count($tasks).' registros.');
        if (count($tasks) == 0) {
            \Log::info('No hay tareas pendientes de emisión de boletas.');
            return;
        }
        foreach($tasks as $task) {
            $payment = \Masso\Payment::find($task->object_id);

            try {
                \Log::info('payment_id', [$payment->id]);
                \Log::info('original_data', [$payment->data]);
                $payment_data = unserialize($payment->data);
                \Log::info('unserialize_ok', [$payment_data]);

                if ($payment) {
                    $payment_data = unserialize($payment->data);
                    $payment_data = unserialize($payment_data);
                    $payment_data = unserialize($payment->data);
                    $passport = isset($payment_data['passport']) ? $payment_data['passport'] : '';
                    $description = $payment->description;
                    $client_name = $payment->name . ' '. $payment->lastname;
                    $reference_user = 'Pago Web '.$payment->id;
                    $reference   = $client_name . $reference_user;

                    $tipo_dte = Facto::getETicketType();
                    $client = Facto::getClient();
                    $oc_fecha = date('Y-m-d', strtotime($payment->created_at));
                    $fecha_emision = date('Y-m-d');

                    $cadena_xml = "
                        <documento xsi:type='urn:emitir_dte'>
                            <encabezado xsi:type='urn:encabezado'>
                                <tipo_dte xsi:type='xsd:string'>".Facto::encoding($tipo_dte)."</tipo_dte>
                                <fecha_emision xsi:type='xsd:date'>".Facto::encoding($fecha_emision)."</fecha_emision>
                                <condiciones_pago xsi:type='xsd:string'><![CDATA[".Facto::encoding(0)."]]></condiciones_pago>
                                <orden_compra_num xsi:type='xsd:string'>".Facto::encoding($payment->id)."</orden_compra_num>
                                <orden_compra_fecha xsi:type='xsd:date'>".Facto::encoding($oc_fecha)."</orden_compra_fecha>
                                <receptor_razon xsi:type='xsd:string'>".Facto::encoding($client_name)."</receptor_razon>
                            </encabezado>

                            <detalles xsi:type='urn:detalles'>";
                            $cadena_xml .= "
                            <detalle xsi:type='urn:detalle'>
                                <cantidad xsi:type='xsd:int'>".Facto::encoding(1)."</cantidad>
                                <unidad xsi:type='xsd:string'>unid</unidad>
                                <glosa xsi:type='xsd:string'><![CDATA[".Facto::encoding($description)."]]></glosa>
                                <monto_unitario xsi:type='xsd:decimal'>".Facto::encoding(round($payment->amount,0))."</monto_unitario>
                                <exento_afecto xsi:type='xsd:boolean'>".Facto::encoding(0)."</exento_afecto>
                            </detalle>";

                            $cadena_xml .= "
                            </detalles>

                            <referencias xsi:type='urn:referencias'>
                                <referencia xsi:type='urn:referencia'>
                                    <docreferencia_tipo>802</docreferencia_tipo>
                                    <docreferencia_folio>".Facto::encoding($payment->id)."</docreferencia_folio>
                                    <docreferencia_fecha>".Facto::encoding($oc_fecha)."</docreferencia_fecha>
                                    <codigo_referencia>5</codigo_referencia>
                                    <descripcion>".Facto::encoding($reference)." - RUT: ".$passport."</descripcion>
                                </referencia>
                            </referencias>

                            <totales xsi:type='urn:totales'>
                                <total_exento xsi:type='xsd:int'>".Facto::encoding(round($payment->amount,0))."</total_exento>
                                <total_afecto xsi:type='xsd:int'>".Facto::encoding( 0 )."</total_afecto>
                                <total_iva xsi:type='xsd:int'>".Facto::encoding( 0 )."</total_iva>
                                <total_final xsi:type='xsd:int'>".Facto::encoding($payment->amount)."</total_final>
                            </totales>
                        </documento>";

                    $client->soap_defencoding = 'UTF-8';
                    $client->decode_utf8 = false;
                    $response = $client->call("emitirDocumento", $cadena_xml);


                    if( $data = Facto::checkResponse( $client, $response ) ):

                        $payment->dte = 'BE-'.$data['folio'];

                        if( !empty($data['dte']) ):
                            $contents = file_get_contents($data['dte']);
                            $payment->document = 'BE/'.$payment->dte.'.pdf';
                            \Storage::put($payment->document, $contents);
                        endif;

                        $payment->save();
                        \Log::info('Boleta electrónica emitida satisfactoriamente: '.json_encode($data));
                    else:
                        \Log::error('Error al emitir boleta electrónica: '.json_encode($response));
                    endif;

                    $task->is_pending = false;
                    $task->save();
                }
            } catch (\Exception $e) {
                \Log::error('Excepción al procesar payment ID ' . $payment->id . ': ' . $e->getMessage());
                \Log::error($e->getTraceAsString());
                continue;
            }

        }
    }
}
