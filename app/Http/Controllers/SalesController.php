<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SalesController extends Controller
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
     * GET Todos los clientes
     * @param Request $request
     * @return void
     */
	public function getAllSalesInDb(Request $request): void
	{
	  	$sql = "SELECT * FROM sales_detail";
	  	try {
	    	$db = \DB::connection()->getPdo();
	    	$resultado = $db->query($sql);

	    	if ($resultado->rowCount() > 0) {
	      		$sales = $resultado->fetchAll(\PDO::FETCH_OBJ);
	      		echo json_encode($sales);
	    	} else {
	      		echo json_encode("No existen ventas en la BBDD.");
	    	}
	  	} catch (\PDOException $e) {
	    	echo '{"error" : {"text":' . $e->getMessage() . '}';
	  	}
	}

	/**
	 * GET Recueperar cliente por ID
	 * @param Request $request
	 * @param int $id
	 * @return void
	 */
	public function getSalesById(Request $request, int $id): void
	{
	  	$id_sales = $id;
	  	$sql = "SELECT * FROM sales_detail WHERE id = $id_sales";
	  	try {
	    	$db = \DB::connection()->getPdo();
	    	$resultado = $db->query($sql);

	    	if ($resultado->rowCount() > 0) {
	      		$sales = $resultado->fetchAll(\PDO::FETCH_OBJ);
	      		echo json_encode($sales);
	    	} else {
	      		echo json_encode("No existen ventas en la BBDD con este ID.");
	    	}
	  	} catch (\PDOException $e) {
	    	echo '{"error" : {"text":' . $e->getMessage() . '}';
	  	}
	}


	/**
	 * POST Crear nuevo cliente
	 * @param Request $request
	 * @return void
	 */
	public function storeNewSales(Request $request): void
	{
	  	$id_coucust = $request->id_coucust;
	  	$sales_code = $request->sales_code;
	  	$date_sale = $request->date_sale;
	  	$date_created = null; //TODO: FINISH
	  	$applied_amount  = $request->applied_amount;

	  	$sql = "INSERT INTO sales_detail (id_coucust, sales_code, date_sale, date_created, date_update) VALUES (:id_coucust,:sales_code, :date_sale, :date_created, NULL)";
	  	
	  	try {

		    $db = \DB::connection()->getPdo();
		    $resultado = $db->prepare($sql);
		    $resultado->bindParam(':id_coucust', $id_coucust);
		    $resultado->bindParam(':sales_code', $sales_code);
		    $resultado->bindParam(':date_sale', $date_sale);
		    $resultado->bindParam(':date_created', $date_created);

		    $resultado->execute();

		    //// Selecciona el monto del cupón
		    $sql2 = "SELECT coucust_detail.coupon_amount FROM coucust_detail INNER JOIN customers ON coucust_detail.id_customers = customers.id INNER JOIN couoffer_detail ON coucust_detail.id_coupon = couoffer_detail.id INNER JOIN offer_detail ON couoffer_detail.id_offer = offer_detail.id WHERE coucust_detail.id = $id_coucust GROUP BY coucust_detail.id  ";

		    $resultado2 = $db->query($sql2);
		    $offer_amount = $resultado2->fetchAll(\PDO::FETCH_OBJ);

		    foreach ($offer_amount as $offer_amount) {
		      	$offer_amount =  $offer_amount->coupon_amount;
		    }

		    $conv = (int)$offer_amount;
		    $coupon_amount = $conv - $applied_amount;

		    // UPDATE sobre el campo coupon_amount en la tabla cupones "coucust_detail"
		    $date_update = null;//date("Y-m-d h:m:s"); TODO: FINISH

		    $sql3 = "UPDATE coucust_detail SET
		          coupon_amount =  :coupon_amount,
		          date_update = :date_update
		        WHERE coucust_detail.id = $id_coucust";

		    $resultado = $db->prepare($sql3);
		    $resultado->bindParam(':coupon_amount', $coupon_amount);
		    $resultado->bindParam(':date_update', $date_update);

		    $resultado->execute();
		    echo json_encode("Nuevo ventas guardada.");

	  	} catch (\PDOException $e) {
	    	echo '{"error" : {"text":' . $e->getMessage() . '}';
	  	}
	}


	/**
	 * PUT Modificar cliente
	 * @param Request $request
	 * @param int $id
	 * @return void
	 */
	public function updateSalesById(Request $request, int $id): void
	{
	  	$id_sales = $id;
	  	$id_coupon  = $request->id_coupon;
	  	$sales_code = $request->sales_code;
	  	$date_sale = $request->date_sale;
	  	$date_update = null; //TODO: FINISH

	  	$sql = "UPDATE sales_detail SET
	        id_coupon = :id_coupon,
	        sales_code = :sales_code,
	        date_sale = :date_sale,
	        date_update = :date_update
	        WHERE id = $id_sales";

	  	try {

		    $db = \DB::connection()->getPdo();
		    $resultado = $db->prepare($sql);

		    $resultado->bindParam(':id_coupon', $id_coupon);
		    $resultado->bindParam(':sales_code', $sales_code);
		    $resultado->bindParam(':date_sale', $date_sale);
		    $resultado->bindParam(':date_update', $date_update);
		    $resultado->execute();
		    echo json_encode("Venta modificado.");

	  	} catch (\PDOException $e) {
	    	echo '{"error" : {"text":' . $e->getMessage() . '}';
	  	}
	}


	/**
	 * DELETE borar cliente
	 * @param Request $request
	 * @param int $id
	 * @return void
	 */
	public function deleteSalesById(Request $request, int $id): void
	{
	  	$id_sales = $id;
	  	$sql = "DELETE FROM sales_detail WHERE id = $id_sales";

	  	try {

		    $db = \DB::connection()->getPdo();
		    $resultado = $db->prepare($sql);
		    $resultado->execute();

		    if ($resultado->rowCount() > 0) {
		      echo json_encode("Venta eliminado.");
		    } else {
		      echo json_encode("No existe venta con este ID.");
		    }

	  	} catch (\PDOException $e) {
	    	echo '{"error" : {"text":' . $e->getMessage() . '}';
	  	}
	}
}
