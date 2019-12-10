<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;

class OfferController extends Controller
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
	 * Retorna Todas las ofertas encontradas en la db y de la compañia del usuario logeado
	 * @param Request $request
	 * @return mixed
	 */
    public function getAllOfferInDb(Request $request)
    {
    	$id_company = $this->getIdCompanyFromUserAuth();
    	if (($id_company == 0)||($id_company == NULL)) {
    		return '{"error" : {"text": company no found}'; 
  		}
    	$sql2 = "SELECT id,offer_code,id_company From offer_detail where id_company = '$id_company';";
    	$redeemed = []; //variable para canjeados
    	try {
    		$db = \DB::connection()->getPdo();
    		$resultado2 = $db->query($sql2);
    		$all = [];
    		if ($resultado2->rowCount() > 0) {
    			$coupon2 = $resultado2->fetchAll(\PDO::FETCH_OBJ);
    			foreach ($coupon2 as $coupon2) {
    				$coupon_offer_code = $coupon2->offer_code;
    				$coupon_offer_id = $coupon2->id;
    				$redeemed = [];

        			$sql = "SELECT *,IFNULL((SELECT COUNT(status) FROM couoffer_detail INNER JOIN offer_detail ON couoffer_detail.id_offer = offer_detail.id WHERE status = 0 and offer_code = '$coupon_offer_code'  group by id_offer),0) AS capture, IFNULL((SELECT COUNT(status) FROM couoffer_detail INNER JOIN offer_detail ON couoffer_detail.id_offer = offer_detail.id WHERE status = 1 and offer_code = '$coupon_offer_code' group by id_offer),0) AS active  FROM offer_detail WHERE offer_code = '$coupon_offer_code';";
          			//obtengo los id del couoffer_detail que esten relaconados a un coupon_offer
          			$sql3 = "SELECT id FROM couoffer_detail WHERE id_offer = '$coupon_offer_id';";
          			$resultado3 = $db->query($sql3);
          			if ($resultado3->rowCount() > 0) {
          				$coupon_couoffer = $resultado3->fetchAll(\PDO::FETCH_OBJ);
          				//$redeemed[] = $resultado3->fetchAll(PDO::FETCH_OBJ);
            			foreach ($coupon_couoffer as $coupon_couoffer) {
              				//obtengo por cada couponcouoffer su coucustdetail
              				$sql4 = "SELECT * FROM coucust_detail WHERE id_coupon = '$coupon_couoffer->id';";
              				$resultado4 = $db->query($sql4);
              				if ($resultado4->rowCount() > 0) {
                			$coupon_coucust_detail = $resultado4->fetchAll(\PDO::FETCH_OBJ);
                				foreach ($coupon_coucust_detail as $coupon_coucust_detail) { 
                  					$sql5 = "SELECT * FROM exchange_detail where id_coucust = '$coupon_coucust_detail->id';";
                  					$resultado5 = $db->query($sql5);
                  					if ($resultado5->rowCount() > 0) {
                    					$redeemed[] = $resultado5->fetchAll(\PDO::FETCH_OBJ);
                  					}
                				}
              				}
            			}
          			}
        			$resultado = $db->query($sql);
        			if ($resultado->rowCount() > 0) {
          				$coupon = $resultado->fetchAll(\PDO::FETCH_OBJ);
          				//Aqui modifico el stdobject resultante del query
          				foreach ($coupon as $key => $value) {
            				//agrego una nueva propiedad al stdobject
            				$coupon[$key]->redeemed = count($redeemed);
          				}
          				$all = array_merge($all, $coupon);
          				// return json_encode($all);
          				// echo str_replace("}][{", "},{", json_encode($coupon));
        			}
			        //   else {
			        //         echo json_encode("No existen oferta en la BBDD.");

			        // }
      			}
      			if (sizeof($all) > 0) {
        		//echo json_encode($all);
        			return json_encode($all);
      			} else {
        			echo json_encode("No existe oferta en la BBDD.");
      			}
    		} else {
      			echo json_encode("No existe oferta en la BBDD.");
    		}
  		} catch (\PDOException $e) {
    		echo '{"error" : {"text":' . $e->getMessage() . '}';
  		}
    }

    /**
     * Guarda en la DB una nueva oferta
     * @param Request $request
     * @return void
     */
    public function newOfferStore(Request $request): void
    {
		//echo $request->pay;
  		$id_company = $this->getIdCompanyFromUserAuth();
  		if (($id_company == 0)||($id_company == NULL)) {
    		echo '{"error" : {"text": company no found}';
		}
		
		if (!is_numeric((int) $request->pay)) {
			echo '{"error" : {"text": no is a option}';
		}

		if (($request->pay == 1) && ($request->offer_value <= 0)) {
			echo '{"error" : {"text": Offer Value cant be 0}';
		}

		$numero = 0;
		$offer_code = $this->getRandomCode();
		$offer_name = $request->offer_name;
		$offer_description = $request->offer_description;
		$offer_value  = $request->offer_value;
		$img = $request->img;
		$amount_coupons = $request->amount_coupons;
		$date_star  = $request->date_star;
		$date_end = $request->date_end;
		$date_created = null;
		$coupon_count = $request->coupon_count;
		$is_pay =(int) $request->pay;
		// Insert de la oferta
		$sql = "INSERT INTO offer_detail (offer_code, offer_name, offer_description, offer_value, img, amount_coupons, date_star, date_end, id_company ,date_created,date_update,is_payable) VALUES
		          (:offer_code, :offer_name, :offer_description, :offer_value, :img, :amount_coupons, :date_star, :date_end , :id_company ,:date_created, NULL, :is_pay)";
		try {
		    $db = \DB::connection()->getPdo();
		    $resultado = $db->prepare($sql);

		    $resultado->bindParam(':offer_code', $offer_code);
		    $resultado->bindParam(':offer_name', $offer_name);
		    $resultado->bindParam(':offer_description', $offer_description);
		    $resultado->bindParam(':offer_value', $offer_value);
		    $resultado->bindParam(':img', $img);
		    $resultado->bindParam(':amount_coupons', $amount_coupons);
		    $resultado->bindParam(':date_star', $date_star);
		    $resultado->bindParam(':date_end', $date_end);
		    $resultado->bindParam(':id_company', $id_company);
			$resultado->bindParam(':date_created', $date_created);
			$resultado->bindParam(':is_pay', $is_pay);

		    $resultado->execute();
		    $lastId = $db->lastInsertId();

		    ///// Generacion de Cupones
		    for ($i = 0; $i < $coupon_count; $i++) {
		        ++$numero;
		        $id_offer = $request->id_offer;
		        $coupon_code = sprintf("%03d", $numero);
		        $date_created =  null;
		        $sql = "INSERT INTO couoffer_detail (id_offer, coupon_code, date_created) VALUES
		            (:id_offer, :coupon_code, :date_created)";

		        $resultado = $db->prepare($sql);
		        $resultado->bindParam(':id_offer', $lastId);
		        $resultado->bindParam(':coupon_code', $coupon_code);
		        $resultado->bindParam(':date_created', $date_created);
		        $resultado->execute();
		    }

		    // Genera el codigo de la oferta
		    $sql = "SELECT offer_code FROM offer_detail WHERE id = $lastId";

		    $resultado = $db->query($sql);

		    if ($resultado->rowCount() > 0) {
		      $coupon = $resultado->fetchAll(\PDO::FETCH_OBJ);
		      echo json_encode($coupon[0]);
		    }
		} catch (\PDOException $e) {
		    echo '{"error" : {"text":' . $e->getMessage() . '}';
		}
	}

	/**
	 * Retorna una oferta por su codigo de oferta
	 * @param Request $request
	 * @param string $offer_code
	 * @return void
	 */
	public function getOfferPerCode(Request $request): void
	{
		$offer_code = $request->query();
		$offer_code = $offer_code['offer_code'];
		$sql = "SELECT *,(SELECT COUNT(status) FROM couoffer_detail INNER JOIN offer_detail ON couoffer_detail.id_offer = offer_detail.id WHERE status = 0 and offer_code = '$offer_code'  group by id_offer) AS capture, (SELECT COUNT(status) FROM couoffer_detail INNER JOIN offer_detail ON couoffer_detail.id_offer = offer_detail.id WHERE status = 1 and offer_code = '$offer_code' group by id_offer) AS active  FROM offer_detail WHERE offer_code = '$offer_code'; ";
		try {
		    $db = \DB::connection()->getPdo();
		    $resultado = $db->query($sql);

		    if ($resultado->rowCount() > 0) {
		      $coupon = $resultado->fetchAll(\PDO::FETCH_OBJ);
		      echo json_encode($coupon[0]);
		    } else {
		      echo json_encode("No existe esa oferta en la BBDD con este ID.");
		    }
		} catch (\PDOException $e) {
		    echo '{"error" : {"text":' . $e->getMessage() . '}';
		}
	}

	/**
	 * Retorna una oferta por su id
	 * @param Request $request
	 * @param int $id
	 * @return void
	 */
	public function getOfferPerId(Request $request, int $id): void
	{
		$id_offer = $id;
		$sql = "SELECT offer_code FROM offer_detail WHERE id = $id_offer";
		try {
		    $db = \DB::connection()->getPdo();
		    $resultado = $db->query($sql);

		    if ($resultado->rowCount() > 0) {
		        $coupon = $resultado->fetchAll(\PDO::FETCH_OBJ);
		        echo json_encode($coupon);
		    } else {
		        echo json_encode("No existe esa oferta en la BBDD con este ID.");
		    }
		} catch (\PDOException $e) {
		    echo '{"error" : {"text":' . $e->getMessage() . '}';
		}
	}

	/**
	 * Actualiza el registro de una oferta por su id
	 * @param Request $request
	 * @param int $id
	 * @return void
	 */
	public function updateOfferPerId(Request $request, int $id): void
	{
		$id_offer = $id;
		$offer_name = $request->offer_name;
		$offer_description = $request->offer_description;
		$offer_value = $request->offer_value;
		$img = $request->img;
		$date_update =  null; //TODO: change this

		$sql = "UPDATE offer_detail SET
		        offer_name = :offer_name,
		        offer_description = :offer_description,
		        offer_value = :offer_value,
		        img = :img,
		        date_update = :date_update
		        WHERE id = $id_offer";

		try {
		    $db = \DB::connection()->getPdo();
		    $resultado = $db->prepare($sql);

		    $resultado->bindParam(':offer_name', $offer_name);
		    $resultado->bindParam(':offer_description', $offer_description);
		    $resultado->bindParam(':offer_value', $offer_value);
		    $resultado->bindParam(':img', $img);
		    $resultado->bindParam(':date_update', $date_update);
		    $resultado->execute();
		    echo json_encode("Oferta modificada.");

		} catch (\PDOException $e) {
		    echo '{"error" : {"text":' . $e->getMessage() . '}';
		}
	}

	/**
	 * Borra una oferta por su id
	 * @param Request $request
	 * @param int $id
	 * @return void
	 */
	public function deleteOfferPerId(Request $request, int $id): void
	{
		$id_offer = $id;
		$sql = "DELETE FROM offer_detail WHERE id = $id_offer";
		try {
		    $db = \DB::connection()->getPdo();
		    $resultado = $db->prepare($sql);
		    $resultado->execute();

		    if ($resultado->rowCount() > 0) {
		      echo json_encode("Oferta eliminada.");
		    } else {
		      echo json_encode("No existe esa oferta con este ID.");
		    }

		} catch (\PDOException $e) {
		   echo '{"error" : {"text":' . $e->getMessage() . '}';
		}
	}

	/**
	 * Genera un Codigo Random
	 * return string
	 */
	protected function getRandomCode(): string
	{
		$an = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$nu = "0123456789";
		$su = strlen($an) - 1;
		$sa = strlen($nu) - 1;
		$code = substr($an, rand(0, $su), 1) .
		substr($an, rand(0, $su), 1) .
		substr($an, rand(0, $su), 1) .
		substr($nu, rand(0, $sa), 1) .
		substr($nu, rand(0, $sa), 1);
		return $code;
	}
}
