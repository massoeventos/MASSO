<?php
namespace Masso\Behaviors;

class Facto{

	public static function getClient(){
		$client = new \nusoap_client('https://conexion.facto.cl/documento.php?wsdl', true);
        $client->setCredentials('52.001.885-9/5635', '7f7720a093252070810b463a46327fb5', "basic");

		//$client->setCredentials('1.111.111-4/pruebasapi', '90809d7721fe3cdcf1668ccf33fea982', "basic");


        return $client;
	}

	public static function getETicketType(){
		return 41;
	}

	public static function encoding($origen)
	{
	    if (mb_detect_encoding($origen, "UTF-8,ISO-8859-1",true) != 'UTF-8')
	    {
	        $origen = iconv("ISO-8859-1","UTF-8//TRANSLIT",$origen);
	    }
	    
	    $origen = str_replace("&","&amp;",$origen);
	    $origen = str_replace("<","&lt;",$origen);
	    $origen = str_replace(">","&gt;",$origen);
	    
	    return $origen;
	}

	public static function logWP( $result, $title = "" ){
        $path = explode('/', __FILE__); unset($path[sizeof($path)-1]);
        $path = implode('/', $path);
        $path = $path.'/FactoLogs/transaction.log';
        $fp = fopen($path, "a+") or exit("Unable to open file!");

        $savestring = "\n";
        $savestring .= "########### ".$title." ##########\n";
        $savestring .= print_r($result, true). "\n";        

        fwrite($fp, $savestring);
        fclose($fp);

    }

	public static function checkResponse( $client, $response ){
		$err = $client->getError();

		Facto::logWP($err);
		Facto::logWP($response);

		if($err != "")
			return false;

		if( $response["resultado"]["status"] == 1 )
			return false;

		$data = [];
		if( $response["resultado"]["status"] == 0  ):
				
			if( !empty($response['encabezado']) && !empty($response['encabezado']['folio']) )
				$data['folio'] = $response['encabezado']['folio'];

			if( !empty($response["enlaces"]) && !empty($response["enlaces"]["dte_pdf"]) )
				$data['dte'] = $response["enlaces"]["dte_pdf"];

			if( !empty($data) )
				return $data;

		endif;

		return false;
        

	}


}