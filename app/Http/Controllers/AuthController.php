<?php
namespace  App\Http\Controllers;

use App\Http\Requests\RegisterAuthRequest;
use App\User;
use App\UserType as Role;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use phpDocumentor\Reflection\Types\Mixed_;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public $loginAfterSignUp = true;

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
     * Guarda un nuevo usuario y genera su token
     * @param Request $request
     * @return mixed
     */
    //TODO: Crear las migraciones aparte de la bd original
	public function storeNewUser(Request $request)
	{
	    $user_name = $request->users_name;
        $password = Hash::make($request->password);
        $email = $request->email;
        $id_user_type = $request->id_user_type;
        $id_company = (int) $request->id_company;
        $date_created = null;
        if ($id_company==0 || $id_company=='') {
            return '{"error" : {"text": company cant be null}';
        }

        $sql = "INSERT INTO users 
        (id, user_name, password, id_user_type,
        id_company, date_created, date_update, surname,
        email, email_verified_at, remember_token
        ) VALUES (NULL, :user_name, :password, :id_user_type,
        :id_company, :date_created, NULL, NULL,
        :email, NULL, NULL)";
        try {
            $db = \DB::connection()->getPdo();
            $resultado = $db->prepare($sql);
            $resultado->bindParam(':user_name', $user_name);
            $resultado->bindParam(':password', $password);
            $resultado->bindParam(':email', $email);
            $resultado->bindParam(':id_user_type', $id_user_type);
            $resultado->bindParam(':id_company', $id_company);
            $resultado->bindParam(':date_created', $date_created);
            $resultado->execute();
            echo json_encode("Nuevo usuario guardado.");
        } catch (\PDOException $e) {
            echo '{"error" : {"text":' . $e->getMessage() . '}';
        }
	}

    /**
     * Permite autenticarse en el sistema
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
	public function login(Request $request)
	{
		$input = $request->only('user_name', 'password');
	    $jwt_token = null;
	    try {
	        if (!$jwt_token = JWTAuth::attempt($input)) {
		        return  response()->json([
			        'status' => 'invalid_credentials',
			        'message' => 'Usuario o contraseña no válidos.',
			        ], 404);
		        }
		    } catch (JWTException $e) {
                return response()->json(['error' => 'could_not_create_token'], 500);
        }
            $user = User::where('user_name',$request->user_name)->first();
            $role = Role::where('id',$user->id_user_type)->first();
		    return  response()
		        ->json([
          			'token' => $jwt_token,
          			'user' => [
          				'id' => $user->id,
          				'user_name' => $user->user_name,
                        'role_id' => $user->id_user_type,
                        'role' => $role->code,  
          			],
          		])
		        ->header('Authorization', 'Bearer '.$jwt_token);
	}

    /**
     * TODO: Finish
     */
  	public function users(Request $request)
  	{}

    /**
     * Retorna un usuario por su id
     * @param Request $request
     * @param int $id
     * @return void
     */
	public function getUserPerId(Request $request, int $id): void
	{
        $id_users = $id;
        $sql = "SELECT * FROM users WHERE id = $id_users";
        try {
            $db = \DB::connection()->getPdo();
            $resultado = $db->prepare($sql);
            $resultado->execute();
            if ($resultado->rowCount() > 0) {
                $users = $resultado->fetchAll(\PDO::FETCH_OBJ);
                echo json_encode($users);
            } else {
                echo json_encode("No existe usuario en la BBDD con este ID.");
            }
        } catch (\PDOException $e) {
            echo '{"error" : {"text":' . $e->getMessage() . '}';
        }
	}

    /**
     * Actualiza un usuario por su id
     * @param Request $request
     * @param int $id
     * @return void
     */
  	public function updateUserPerId(Request $request, int $id): void
  	{
        $id_users = $id;
        $user = User::where('id',$id)->first();
        if (isset($request->password)) {
            $password = Hash::make($request->password);
        } else {
            $password = $user->password;
        }
        $email = $request->email ?? $user->email;
        $user_name = $request->users_name ?? $user->user_name;
        $date_update = null;

        $sql = "UPDATE users SET
                password  = :password,
                user_name = :user_name,
                email = :email,
                date_update = :date_update
              WHERE id = $id_users";

          try {
              $db = \DB::connection()->getPdo();
              $resultado = $db->prepare($sql);
              $resultado->bindParam(':user_name', $user_name);
              $resultado->bindParam(':email', $email);
              $resultado->bindParam(':password', $password);
              $resultado->bindParam(':date_update', $date_update);
              $resultado->execute();
              echo json_encode("Password modificado.");

          } catch (\PDOException $e) {
              echo '{"error" : {"text":' . $e->getMessage() . '}';
          }
  	}

    /**
     * Borra un usuario por id
     * @param Request $request
     * @param int $id
     * @return void
     */
  	public function deleteUserPerId(Request $request, int $id): void
  	{
        $id_users = $request->id;
        $sql = "DELETE FROM users WHERE id = $id_users";

        try {
            $db = \DB::connection()->getPdo();
            $resultado = $db->prepare($sql);
            $resultado->execute();

            if ($resultado->rowCount() > 0) {
              echo json_encode("Usuario eliminado.");
            } else {
              echo json_encode("No existe usuario con este ID.");
            }

        } catch (\PDOException $e) {
            echo '{"error" : {"text":' . $e->getMessage() . '}';
        }
  	}

    /**
     * TODO: Finish
     */
  	public function loginUrlMethodGet(Request $request): void
  	{
        /**
        $user_name =  $request->getParam('user_name');
        $password =  $request->getParam('password');
        $sql = "SELECT user_name, password FROM users WHERE user_name = '$user_name' && password= '$password'";

        try {
            $db = \DB::connection()->getPdo();
            $resultado = $db->query($sql);

            if ($resultado->rowCount() > 0) {
              $users = $resultado->fetchAll(PDO::FETCH_OBJ);
              //  echo var_dump($users);
              foreach ($users as $users) {
                $users_user_name = $users->user_name;
                $users_password =  $users->password;
              }

              if ($user_name ==  $users_user_name && $password == $users_password) {
                  $_SESSION["user_name"] = $user_name;
                  echo json_encode("acceso autorizado");
              }
            } else {
              echo json_encode("Acceso negado");
            }
            $resultado = null;
            $db = null;
        } catch (PDOException $e) {
            echo '{"error" : {"text":' . $e->getMessage() . '}';
        }
        */
        echo json_encode("Acceso negado");
  	}

    /**
     * TODO: FINISH
     */
  	public function logout(Request $request)
  	{

  	}

    /**
     * retorna todos los usuarios en la bd
     * @param Request $request
     * @return void
     */
	  public function allusers(Request $request): void
	  {
		    $sql = "SELECT * FROM users";
        try {
            $db = \DB::connection()->getPdo();
            $resultado = $db->query($sql);
            if ($resultado->rowCount() > 0) {
                $users = $resultado->fetchAll(\PDO::FETCH_OBJ);
                echo json_encode($users);
            } else {
                echo json_encode("No existe usuario en la BBDD.");
            }
        } catch (\PDOException $e) {
            echo '{"error" : {"text":' . $e->getMessage() . '}';
        }
	  }
}