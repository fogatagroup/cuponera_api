<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transaction;
use App\Customer;
use App\Company;
use Carbon;
use App\Gateway;

class TransactionController extends Controller
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
	 * 
	 */
	public function storeTransaction(Request $request) {

	}

	/**
	 * 
	 */
	public function changeStatus(Transaction $transaction, string $status): bool 
	{
		if (!in_array($status,Transaction::STATUSES)) {
			return false;
		}
		if ($status == Transaction::STATUSES['completed']) {
			$transaction->complete_at = Carbon::now();
		}
		$transaction->status = Transaction::STATUSES[$status];
		$transaction->save();
		return true;
	}

	/**
	 * Store a new transaction from a pay 
	 * @param Request &$request
	 * @param Customer $customer
	 * @param Company $company
	 * @param Gateway $gateway
	 * @return array
	 */
	public function storeNewTransaction(Request &$request, Customer $customer, Company $company, Gateway $gateway): array
	{
		//El uuid se auto genera atraves de un observer
		$transaction = new Transaction;
		try {
			//$transaction->id = $request->id;
			//$transaction->uuid = $request->uuid;
			//$transaction->status = $request->status;
			$transaction->currency = $gateway->currency;
			$transaction->sender_name = $customer->firstname.''.$customer->lastname;
			$transaction->receive_name = $request->receive_name;
			$transaction->amount = $request->amount;
			//chech to process in future any feed
			$transaction->final_amount = $request->amount;
			$transaction->internal_notes = json_encode(['gateway' => $gateway->name]);
			//$transaction->extra_info = $request->extra_info;
			$transaction->complete_at = null;
			//$transaction->merchant_reference = $request->merchant_reference;
			//$transaction->channel_reference = $request->channel_reference;
			$transaction->id_customer = $customer->id;
			$transaction->id_company = $company->id;
			$transaction->id_gateway = $gateway->id;
			$transaction->save();
		} catch (\Exception $e) {
			\Log::error('Error:'. $e->getMessage());
			return [];
		}
	}

	/**
	 * GET all Transaction
	 * @param Request $request
	 * @return void
	 */
	public function getAllTransactionInDb(Request $request): void
	{
	  	$sql = "SELECT * FROM transactions";
	  	try {
		    $db = \DB::connection()->getPdo();
		    $resultado = $db->query($sql);

		    if ($resultado->rowCount() > 0) {
		      $user_type = $resultado->fetchAll(\PDO::FETCH_OBJ);
		      echo json_encode($user_type);
		    } else {
		      echo json_encode("No existen transacciones en la BBDD.");
		    }

	  	} catch (\PDOException $e) {
	    	echo '{"error" : {"text":' . $e->getMessage() . '}';
	  	}
	}
}
