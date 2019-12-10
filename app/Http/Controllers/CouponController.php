<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;

class CouponController extends Controller
{
	/**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('jwt.auth', ['except' => ['login']]);
    }

    /**
	 * Retorna el Id de la compañia guardada en el usuario
	 * @return int
	 */
	public function getIdCompanyFromUserAuth(): int
	{
		$response = 0;
		try {
			$response = JWTAuth::user();
			return (int) $response->id_company;
		} catch (\Exception $e) {
			\Log::error(['getIdCompanyFromUserAuth Error' => $e->getMessage()]);
			return $response;
		}
	}

	/**
	 * Muestra Todos Los Cupones Registrados en la bd
	 * @param Request $request
	 * @return void
	 */
	public function getAllCoupon(Request $request): void
	{
		$sql = "SELECT * FROM couoffer_detail";
	 	try {
	    	$db = \DB::connection()->getPdo();
	    	$resultado = $db->query($sql);

	    	if ($resultado->rowCount() > 0) {
	      		$coupon = $resultado->fetchAll(\PDO::FETCH_OBJ);
	      		echo json_encode($coupon);
	    		} else {
	      		echo json_encode("No existen cupones en la BBDD.");
	    	}
	  	} catch (\PDOException $e) {
	   		echo '{"error" : {"text":' . $e->getMessage() . '}';
	   	}
	}

	/**
	 * Muestra un cupon por el id del cupon
	 * @param Request $request
	 * @param int $id
	 * @return void
	 */
	public function getCouponPerId(Request $request, int $id): void
	{
	  	$id_coupon = $id;
	  	$sql = "SELECT * FROM couoffer_detail WHERE id = $id_coupon";
	  	try {
	    	$db = \DB::connection()->getPdo();
	    	$resultado = $db->query($sql);
	    	if ($resultado->rowCount() > 0) {
	      		$coupon = $resultado->fetchAll(\PDO::FETCH_OBJ);
	      		echo json_encode($coupon);
	    	} else {
	      		echo json_encode("No existe ese cupón en la BBDD con este ID.");
	    	}
	  	} catch (\PDOException $e) {
	    echo '{"error" : {"text":' . $e->getMessage() . '}';
	  	}
	}

	/**
	 * GET Recuperar cupones por ID customers
	 * @param Request $request
	 * @param int $id_customers
	 * @return void
	 */
	public function getCouponPerIdCustomers(Request $request, int $id_customers): void
	{
	  	$id_coupon = $id_customers;
	  	$sql = "SELECT * FROM couoffer_detail WHERE id = $id_customers";
	  	try {
		    $db = \DB::connection()->getPdo();
		    $resultado = $db->query($sql);

		    if ($resultado->rowCount() > 0) {
		      $coupon = $resultado->fetchAll(\PDO::FETCH_OBJ);
		      echo json_encode($coupon);
		    } else {
		      echo json_encode("No existe ese cupón en la BBDD con este ID.");
		    }
	  	} catch (\PDOException $e) {
	    	echo '{"error" : {"text":' . $e->getMessage() . '}';
	  	}
	}

	/**
	 * GET Recuperar cupón disponible para asignar
	 * @param Request $request
	 * @param int $id_offer
	 * @return void
	 */
	public function getOfferPerIdOffer(Request $request, int $id_offer): void
	{
	  $id_offer = $id_offer;
	  $sql = "SELECT offer_detail.offer_code, couoffer_detail.coupon_code FROM `couoffer_detail`, offer_detail WHERE couoffer_detail.`id_offer` = $id_offer and offer_detail.id = $id_offer and couoffer_detail.`status`= 1 ORDER BY couoffer_detail.`id` ASC LIMIT 0,1 ";

	  try {
	    $db = \DB::connection()->getPdo();
	    $resultado = $db->query($sql);

	    if ($resultado->rowCount() > 0) {
		    $coupon = $resultado->fetchAll(\PDO::FETCH_OBJ);
		    foreach ($coupon as $coupon) {
		        $coupon_offer_code =  $coupon->offer_code;
		        $coupon_coupon_code = $coupon->coupon_code;
		        $coupon_offer_con = $coupon_offer_code . '-' . $coupon_coupon_code;
		        echo json_encode($coupon_offer_con);
		    }
	    } else {
	      	echo json_encode("No existe ese cupón en la BBDD con este ID.");
	    }
	  } catch (\PDOException $e) {
	    echo '{"error" : {"text":' . $e->getMessage() . '}';
	  }
	}

	/**
	 * POST Canjear cupón del customers
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function exchangeCoupon(Request $request)
	{
		$response = null;
		//Aqui Atrapo el Token de la web modificar
	  	$id_company = $this->getIdCompanyFromUserAuth();
	  	if (($id_company == 0)||($id_company == NULL)) {
	    	return '{"error" : {"text": company no found}'; 
	  	}
	  ////////////////////////////////////////////


	    $number_coupon = $request->number_coupon;
	  	$date_created = date("Y-m-d h:m:s");

	  	try {

		    //Select para obtener el id de la tabala coucust desde el number cupón
		    $offer_code = substr($number_coupon, 0, 5);
		    $coupon_code = substr($number_coupon, -3);

		    $sql4 = "SELECT coucust_detail.id FROM coucust_detail INNER JOIN couoffer_detail ON coucust_detail.id_coupon = couoffer_detail.id INNER JOIN offer_detail ON couoffer_detail.id_offer = offer_detail.id WHERE offer_detail.offer_code= '$offer_code' and couoffer_detail.coupon_code = '$coupon_code' LIMIT 1  ";
		    $db = \DB::connection()->getPdo();
		    $resultado4 = $db->query($sql4);
		    if ($resultado4->rowCount() > 0) {
		      $id_coucust = $resultado4->fetchAll(\PDO::FETCH_OBJ);
		      foreach ($id_coucust as $id_coucust) {
		        $id_coucust_id = $id_coucust->id;
		      }
		    }
		    $conv =  intval($id_coucust_id);

		    /// Selecta para  traer informacion del cliente para el canje
		    $sql = "SELECT concat_ws( '-',offer_detail.offer_code, couoffer_detail.coupon_code) as cupon, customers.identification_id,customers.firstname, customers.lastname,customers.telephone, customers.email FROM coucust_detail INNER JOIN customers ON coucust_detail.id_customers = customers.id INNER JOIN couoffer_detail ON coucust_detail.id_coupon = couoffer_detail.id INNER JOIN offer_detail ON couoffer_detail.id_offer = offer_detail.id WHERE coucust_detail.id = '$id_coucust_id' GROUP BY coucust_detail.id ";

		    $resultado = $db->query($sql);
		    $aux = 0;
		    $coupon = $resultado->fetchObject();

		    if ($coupon == null) {

		      return $response->withStatus(400)->withJson([
		        'status' => false,
		        'message' => "El customers aun no ha capturado el cupón."
		      ]);
		    }

		    //Verifica que el cupon sea de la empresa que lo esta canjeando
		    $sqlempresa = "SELECT coucust_detail.id FROM coucust_detail INNER JOIN couoffer_detail ON coucust_detail.id_coupon = couoffer_detail.id INNER JOIN offer_detail ON couoffer_detail.id_offer = offer_detail.id WHERE offer_detail.offer_code= '$offer_code' and couoffer_detail.coupon_code = '$coupon_code' and  offer_detail.id_company = '$id_company' ";

		    $resultadoempresa = $db->query($sqlempresa);
		    if ($resultadoempresa->rowCount() == 0) {
		      return $response->withStatus(400)->withJson([
		        'status' => false,
		        'message' => "El Cupon Pertenece a Otra Empresa."
		      ]);
		    }



		    /// Verifica si el cupón fue canjeado
		    $sql = "SELECT * FROM `exchange_detail` WHERE id_coucust= $id_coucust_id";

		    $resultado = $db->query($sql);

		    if ($resultado->fetchObject() != null) {
		      return $response->withStatus(400)->withJson([
		        'status' => false,
		        'message' => "Este cupón ya ha sido canjeado."
		      ]);
		    }


		    $status = 1;
		    $sql2 = "INSERT INTO exchange_detail (id_coucust, status,  date_created) VALUES
		              (:id_coucust,:status, :date_created)";

		    $resultado = $db->prepare($sql2);
		    $resultado->bindParam(':id_coucust', $id_coucust_id);
		    $resultado->bindParam(':status', $status);
		    $resultado->bindParam(':date_created', $date_created);
		    $resultado->execute();

		    $resultado = null;
		    $db = null;

		    return $response->withStatus(200)->withJson([
		      'status' => true,
		      'message' => "Canje aplicado con exito.",
		      'data' => $coupon
		    ]);
	  	} catch (\PDOException $e) {
	    	return $response->withStatus(400)->withJson([
	      		'status' => false,
	      		'message' => $e->getMessage()
	    		]);
	  	}
	}


	/** 
	 * SELECT  Recuperar cupones canjeados  por ID customers
	 * @param Request $request
	 * @param int id_customers
	 * @return mixed
	 */
	public function getAllExchangeCouponFilterByCustomers(Request $request, int $id_customers)
	{
		$id_company = $this->getIdCompanyFromUserAuth();
		if (($id_company == 0)||($id_company == NULL)) {
		    return '{"error" : {"text": company no found}';
		}

		$id_customers = $id_customers;
		$sql = "SELECT customers.id, concat_ws( ' ',customers.firstname, customers.lastname) as customer, concat_ws( '-',offer_detail.offer_code, couoffer_detail.coupon_code) as cupon, exchange_detail.date_created as date FROM `exchange_detail`, couoffer_detail, coucust_detail, customers, offer_detail WHERE exchange_detail.id_coucust = coucust_detail.id AND coucust_detail.id_coupon = couoffer_detail.id AND coucust_detail.id_customers = customers.id AND couoffer_detail.id_offer = offer_detail.id AND customers.id = $id_customers AND offer_detail.id_company = $id_company";// GROUP BY customers.id";
		try {
		    $db = \DB::connection()->getPdo();
		    $resultado = $db->query($sql);

		    if ($resultado->rowCount() > 0) {
		      	$coupon = $resultado->fetchAll(\PDO::FETCH_OBJ);
		      	echo json_encode($coupon);
		    } else {
		      	echo json_encode("No existe ese cupón en la BBDD con este ID.");
		    }
		} catch (\PDOException $e) {
		    echo '{"error" : {"text":' . $e->getMessage() . '}';
		}
	}
}
