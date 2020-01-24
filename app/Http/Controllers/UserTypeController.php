<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserTypeController extends Controller
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
     * Retorna Todos Los UserType de la bd
     * @param Request $request
     * @return void
     */
  	public function getAllUserType(Request $request): void
    {
        $sql = "SELECT * FROM user_type";

        try {
            $db = \DB::connection()->getPdo();
            $resultado = $db->query($sql);

            if ($resultado->rowCount() > 0) {
                $user_type = $resultado->fetchAll(\PDO::FETCH_OBJ);
                echo json_encode($user_type);
            } else {
                echo json_encode("No existe el tipo de usuario en la BBDD.");
            }
        } catch (\PDOException $e) {
            echo '{"error" : {"text":' . $e->getMessage() . '}';
        }
    }

    /**
     * GET Recuperar usuarios por ID
     * @param Request $request
     * @param int $id
     * @return void
     */
    public function getUserTypeById(Request $request, int $id): void
    {
        $id_user_type = $id;
        $sql = "SELECT * FROM user_type WHERE id = $id_user_type";
        try {
            $db = \DB::connection()->getPdo();
            $resultado = $db->query($sql);

            if ($resultado->rowCount() > 0) {
                $user_type = $resultado->fetchAll(\PDO::FETCH_OBJ);
                echo json_encode($user_type);
            } else {
                echo json_encode("No existe el tipo de usuario en la BBDD con este ID.");
            }
        } catch (\PDOException $e) {
            echo '{"error" : {"text":' . $e->getMessage() . '}';
        }
    }


    /**
     * POST Crear nuevo tipo de usuario
     * @param Request $request
     * @return void
     */
    public function storeNewUserType(Request $request): void
    {
        $code = $request->code;
        $name = $request->name;
        $description = $request->description;
        $date_created = null; //TODO: FINISH

        $sql = "INSERT INTO user_type (code, name, description, date_created,date_update) VALUES
                (:code,:name, :description, :date_created,NULL)";
        try {
            $db = \DB::connection()->getPdo();
            $resultado = $db->prepare($sql);
            $resultado->bindParam(':code', $code);
            $resultado->bindParam(':name', $name);
            $resultado->bindParam(':description', $description);
            $resultado->bindParam(':date_created', $date_created);

            $resultado->execute();
            echo json_encode("Nuevo tipo de usuario guardado.");
        } catch (\PDOException $e) {
            echo '{"error" : {"text":' . $e->getMessage() . '}';
        }
    }


    /** 
     * PUT Modificar tipo de usuario
     * @param Request $request
     * @param int $id
     * @return void
     */
    public function updateUserTypeById(Request $request, int $id): void
    {
        $id_user_type = $id;
        $code = $request->code;
        $name = $request->name;
        $description = $request->description;
        $date_update = null; //TODO: FINISH

        $sql = "UPDATE user_type SET
                code = :code,
                name = :name,
                description = :description,
                date_update = :date_update
              WHERE id = $id_user_type";

        try {
            $db = \DB::connection()->getPdo();
            $resultado = $db->prepare($sql);

            $resultado->bindParam(':code', $code);
            $resultado->bindParam(':name', $name);
            $resultado->bindParam(':description', $description);
            $resultado->bindParam(':date_update', $date_update);
            $resultado->execute();
            echo json_encode("Tipo de usuario modificado.");

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
    public function deleteUserTypeById(Request $request, int $id) {
        $id_user_type = $id;
        $sql = "DELETE FROM user_type WHERE id = $id_user_type";

        try {
          $db = \DB::connection()->getPdo();
          $resultado = $db->prepare($sql);
          $resultado->execute();

          if ($resultado->rowCount() > 0) {
              echo json_encode("Tipo de usuario eliminado.");
          } else {
              echo json_encode("No existe el tipo de usuario con este ID.");
          }
        } catch (\PDOException $e) {
            echo '{"error" : {"text":' . $e->getMessage() . '}';
        }
    }
}
