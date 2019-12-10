<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CompanyController extends Controller
{
	/**
     * Constructor de la clase
     *
     */
    public function __construct()
    {
    //$this->middleware('jwt.auth', ['except' => ['login']]);
    }
    
	/**
	 * GET Todos los usuarios
	 * @param Request $request
	 * @return void
	 */
	public function getAllCompanyInDb(Request $request): void
	{
	  	$sql = "SELECT * FROM companies";
	  	try {
		    $db = \DB::connection()->getPdo();
		    $resultado = $db->query($sql);

		    if ($resultado->rowCount() > 0) {
		      $user_type = $resultado->fetchAll(\PDO::FETCH_OBJ);
		      echo json_encode($user_type);
		    } else {
		      echo json_encode("No existe la compañia en la BBDD.");
		    }

	  	} catch (\PDOException $e) {
	    	echo '{"error" : {"text":' . $e->getMessage() . '}';
	  	}
	}
}
