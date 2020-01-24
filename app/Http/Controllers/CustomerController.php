<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use App\Customer;
use App\Notifications\CapturedNewCouponByCustomer;
use Carbon\Carbon;

class CustomerController extends Controller
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
     * GET Todos los customers
     * @param Request $request
     * @return void
     */
    public function getAllCustomers(Request $request): void
    {
        $id_company = $this->getIdCompanyFromUserAuth();
        if (($id_company == 0)||($id_company == NULL)) {
            echo '{"error" : {"text": company no found}'; 
        }
        
        $sql = "SELECT customers.*, coucust_detail.coupon_amount 
        FROM customers 
        INNER JOIN coucust_detail 
        on customers.id = coucust_detail.id_customers";
        $count_email = [];
        $count_repeated_email = [];
        $response = null;
        try {
            $db = \DB::connection()->getPdo();
            $resultado = $db->query($sql);

            if ($resultado->rowCount() > 0) {
                $customers = $resultado->fetchAll(\PDO::FETCH_OBJ);
                foreach ($customers as $customer) {
                    $count_email[$customer->email] = $customer->email;
                }
                foreach ($customers as $customer) {
                    if (array_key_exists($customer->email,$count_email)) {
                        unset($count_email[$customer->email]);
                        $response[] = $customer;
                    }
                }
                echo json_encode($response);
            } else {
                echo json_encode([]);
            }
        } catch (\PDOException $e) {
            echo '{"error" : {"text":' . $e->getMessage() . '}';
            \Log::error(['getAllCustomers Error' => $e->getMessage()]);
        }
    }


    /**
     * GET Recueperar customers por ID relaciondo a coucust  solo los que tengan cupones capturados
     * @param Request $request
     * @param int $id
     * @return void
     */
    public function getCustomersCaptureById(Request $request, int $id): void
    {
        $id_customers = $id;
        $sql = "SELECT customers.*, coucust_detail.coupon_amount FROM customers INNER JOIN coucust_detail ON customers.id = coucust_detail. id_customers WHERE customers.id = $id_customers";
        try {
            $db = \DB::connection()->getPdo();
            $resultado = $db->query($sql);

            if ($resultado->rowCount() > 0) {
                $customers = $resultado->fetchAll(\PDO::FETCH_OBJ);
                echo json_encode($customers[0]);
            } else {
                echo json_encode("No existen clientes en la BBDD con este ID.");
            }
        } catch (\PDOException $e) {
            echo '{"error" : {"text":' . $e->getMessage() . '}';
            \Log::error(['getCustomersCaptureById Error' => $e->getMessage()]);
        }
    }


/**
 * POST Crear nuevo customers
 * @param Request $request
 * @param string $offer_code
 * @param string $channel
 * @return 
 */
public function storeNewCustomers(Request $request)
{
  $queryParam = $request->query();
  
  $response = null;
  $offer_code = $queryParam['offer_code'];
  
  //echo var_dump($channel);
  //Seleccion las fechas inicio y fin para validad vigencia de la oferta
  $sql4 = "SELECT offer_detail.offer_value, offer_detail.date_star, offer_detail.date_end, offer_detail.amount_coupons FROM offer_detail WHERE offer_detail.offer_code = '$offer_code' ";

  $db = \DB::connection()->getPdo();
  $resultado4 = $db->prepare($sql4);
  $resultado4 = $db->query($sql4);

  if ($resultado4->rowCount() > 0) {
    $date = $resultado4->fetchAll(\PDO::FETCH_OBJ);
    foreach ($date as $date) {
      $date_offer_value = $date->offer_value;
      $date_date_star =  strtotime($date->date_star);
      $date_date_end = strtotime($date->date_end);
      $amount_coupons = (int) $date->amount_coupons;
    }
  }

  //Get Amount Coupon From View | name in view is amount_coupon_client
  $amount_coupon_client = (int) $request->amount_coupon_client;
  //Capture a error in case $amount_coupon_client > that $amount_coupons
  if ($amount_coupon_client > $amount_coupons) {
    return $response->withStatus(400)->withJson([
      'status' => false,
      'message' => "La cantidad de cupones solicitados supera la cantidad de cupones permitidos para esta oferta."
    ]);
  }

  $date_actual =  strtotime(date("Y-m-d h:m:s"));

  if (($date_actual  >= $date_date_star)  &&  ($date_actual <= $date_date_end)) {

    $identification_id = $request->identification_id;
    $firstname = $request->firstname;
    $lastname = $request->lastname;
    $telephone = $request->telephone;
    $email = $request->email;
    $birthdate = $request->birthdate;
    $address = $request->address;
    $city = $request->city;
    $country = $request->country;
    $instagram = $request->instagram;
    $facebook = $request->facebook;
    $date_created = Carbon::now()->toDateTimeString();
    $id_coupon = $request->id_coupon;
    $channel = $queryParam['channel'] ?? '';
    
    

    try {
      //Select para obtener id customers
      $sql5 = "SELECT id FROM customers WHERE email = '$email' ";

      $resultado5 = $db->prepare($sql5);
      $resultado5 = $db->query($sql5);

      if ($resultado5->rowCount() > 0) {
        $idcustomers = $resultado5->fetchAll(\PDO::FETCH_OBJ);
        foreach ($idcustomers as $idcustomers) {
          $idcustomers_id = $idcustomers->id;
        }
        
        $lastId =  intval($idcustomers_id);
      } else {
        //Aqui reviso el registro de un cliente
        //Insert datos de customers
        $sql = "INSERT INTO customers (identification_id, firstname, lastname, telephone, email,birthdate,address, city, country, instagram, facebook, date_created,date_update) VALUES
          (:identification_id,:firstname, :lastname, :telephone, :email, :birthdate, :address, :city, :country, :instagram,:facebook, :date_created, null)";
        $resultado = $db->prepare($sql);
        $resultado->bindParam(':identification_id', $identification_id);
        $resultado->bindParam(':firstname', $firstname);
        $resultado->bindParam(':lastname', $lastname);
        $resultado->bindParam(':telephone', $telephone);
        $resultado->bindParam(':email', $email);
        $resultado->bindParam(':birthdate', $birthdate);
        $resultado->bindParam(':address', $address);
        $resultado->bindParam(':city', $city);
        $resultado->bindParam(':country', $country);
        $resultado->bindParam(':instagram', $instagram);
        $resultado->bindParam(':facebook', $facebook);
        $resultado->bindParam(':date_created', $date_created);
        $resultado->execute();
        $lastId = $db->lastInsertId();
      }
      // Select Recuperar cupón disponible para asignar
      $sql = "SELECT offer_detail.offer_code, couoffer_detail.coupon_code FROM couoffer_detail INNER JOIN offer_detail ON couoffer_detail.id_offer = offer_detail.id WHERE offer_detail.offer_code = '$offer_code' and couoffer_detail.status = 1 ORDER BY couoffer_detail.id ASC LIMIT 0, $amount_coupon_client";
      $resultado = $db->query($sql);
      if ($resultado->rowCount() > 0) {
        $coupon = $resultado->fetchAll(\PDO::FETCH_OBJ);
        $all = [];
        $coupon_id = '';
        foreach ($coupon as $coupon) {
          $coupon_offer_code =  $coupon->offer_code;
          $coupon_coupon_code = $coupon->coupon_code;
          $coupon_offer_con = $coupon_offer_code . '-' . $coupon_coupon_code;
          //  echo json_encode($coupon_offer_con);
          array_push($all, $coupon_offer_con);
        }
        if (sizeof($all) > 0) {
          // Select para obtener el id del cupón desde el codigo cupón
          $sql4 = "SELECT couoffer_detail.id FROM couoffer_detail INNER JOIN offer_detail ON couoffer_detail.id_offer = offer_detail.id WHERE offer_detail.offer_code= '$offer_code' and couoffer_detail.status = 1 LIMIT 0, $amount_coupon_client";
          $resultado4 = $db->query($sql4);
          if ($resultado4->rowCount() > 0) {
            $id_coupon = $resultado4->fetchAll(\PDO::FETCH_OBJ);
              foreach ($id_coupon as $id_coupon) {
                $id_coupon_id = $id_coupon->id;
                $conv =  intval($id_coupon_id);
                // Insert datos de captura de cupón
                $sql2 = "INSERT INTO coucust_detail(id_coupon, id_customers, channel, coupon_amount, date_created) VALUES (:id_coupon,:id_customers, :channel, :coupon_amount, :date_created)";
                $resultado = $db->prepare($sql2);
                $resultado->bindParam(':id_coupon', $conv);
                $resultado->bindParam(':id_customers', $lastId);
                $resultado->bindParam(':channel', $channel);
                $resultado->bindParam(':coupon_amount', $date_offer_value);
                $resultado->bindParam(':date_created', $date_created);
                $resultado->execute();
                // UPDATE sobre el campo status en la tabla cupones "couoffer_detail"
                $status = 0;
                $date_update = Carbon::now()->toDateTimeString();
                $sql3 = "UPDATE couoffer_detail SET status = :status, date_update = :date_update WHERE couoffer_detail.id = $conv";
                $resultado = $db->prepare($sql3);
                $resultado->bindParam(':status', $status);
                $resultado->bindParam(':date_update', $date_update);
                $resultado->execute();
                }
              }
              
              \Notification::route('mail', (string) $email)->notify(new CapturedNewCouponByCustomer($all, $offer_code));

          return json_encode($all);  
        } else {
          return $response->withStatus(400)->withJson([
            'status' => false,
            'message' => "No hay cupones disponibles"
          ]);
        }
      }
    } catch (\PDOException $e) {
      echo '{"error" : {"text":' . $e->getMessage() . '}';
      \Log::error(['StoreNewCustomer Error' => $e->getMessage()]);
    }
  } else {
    echo json_encode("Oferta fuera de tiempo.");
  }
}

    /**
     * PUT Modificar customers
     * @param Request $request
     * @param int $int
     * @return void
     */
    public function updateCustomersById(Request $request, int $id): void
    {
        $id_cliente = $id;
        $lastname = $request->lastname;
        $telephone = $request->telephone;
        $birthdate = $request->birthdate;
        $address = $request->address;
        $city = $request->city;
        $country = $request->country;
        $instagram = $request->instagram;
        $facebook = $request->facebook;
        $date_update = Carbon::now()->toDateTimeString();

        $sql = "UPDATE customers SET
                lastname = :lastname,
                telephone = :telephone,
                birthdate = :birthdate,
                address = :address,
                city = :city,
                country = :country,
                instagram = :instagram,
                facebook = :facebook,
                date_update = :date_update
              WHERE id = $id_cliente";

        try {
            $db = \DB::connection()->getPdo();

            $resultado = $db->prepare($sql);

            $resultado->bindParam(':lastname', $lastname);
            $resultado->bindParam(':telephone', $telephone);
            $resultado->bindParam(':birthdate', $birthdate);
            $resultado->bindParam(':address', $address);
            $resultado->bindParam(':city', $city);
            $resultado->bindParam(':country', $country);
            $resultado->bindParam(':instagram', $instagram);
            $resultado->bindParam(':facebook', $facebook);
            $resultado->bindParam(':date_update', $date_update);
            $resultado->execute();
            echo json_encode("Cliente modificado.");
        } catch (\PDOException $e) {
            echo '{"error" : {"text":' . $e->getMessage() . '}';
            \Log::error(['updateCustomersById Error' => $e->getMessage()]);
        }
    }


    /**
     * DELETE borrar customers
     * @param Request $request
     * @param int $id
     * @return void
     */
    public function deleteCustomersById(Request $request, int $id): void
    {
        $id_cliente = $id;
        $sql = "DELETE FROM customers WHERE id = $id_cliente";

        try {
            $db = \DB::connection()->getPdo();
            $resultado = $db->prepare($sql);
            $resultado->execute();

            if ($resultado->rowCount() > 0) {
                echo json_encode("Cliente eliminado.");
            } else {
                echo json_encode("No existe cliente con este ID.");
            }
        } catch (\PDOException $e) {
            echo '{"error" : {"text":' . $e->getMessage() . '}';
            \Log::error(['deleteCustomersById Error' => $e->getMessage()]);
        }
    }
}
