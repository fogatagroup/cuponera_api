<?php
namespace App\Factories\GatewaysFactory;

use App\Gateways\AbstractGateway;
use ReflectionClass;

class GatewaysFactory {

    /**
     * 
     */
    public function __construct()
    {
        
    }
    
    /**
     * 
     */
    public function __destruct()
    {

    }

    /**
     * @param AbstractGateway $gateway
     * @return mixed $instance
     */
    public static function setup(AbstractGateway $gateway)
    {
        try {
            //Extraigo todas las rutas de clases y sus aliases de esta configuracion, en forma de array
            $class = config('gateways_aliases.aliases');
            //Creo una nueva instancia de la clase que necesito, la clase abstracta tiene un dato que permite identificar que tipo de gateway es
            $instance = new ReflectionClass($class[$gateway->type]);
            return $instance->newInstanceArgs(array($gateway));
        } catch (\Exception $e) {
            \Log::error('Error in setup Instance - '.$e);
        }
    }
}