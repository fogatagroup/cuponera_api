<?php
namespace App\Gateways\CommonGateway;

interface SingleActionInterface
{
    
    /**
     * Determina si la transaccion es un pago
     * @return bool
     */
    public function isPayment(): bool;

    /**
     * Determina si la transaccion es un deposito
     * @return bool
     */
    public function isDeposit(): bool;

    /**
     * Determina si la transaccion es reembolsable
     * @return bool
     */
    public function isRefundable(): bool;

    /**
     * Determina si la transaccion fue rechazada
     * @return bool
     */
    public function isRejected(): bool;

    /**
     * Determina si la transaccion fue cancelada
     * @return bool
     */
    public function isCancelled(): bool;

    /**
     * Determina si fue autorizada la transaccion
     * @return bool
     */
    public function isAuthorized(): bool;

    /**
     * Determina si la transaccion esta en espera
     * @return bool
     */
    public function isOnHold(): bool;

    /**
     * Determina si la transaccion esta siendo procesada
     * @return bool
     */
    public function isProcessing(): bool;

    /**
     * Determina si la transaccion fue culminada con exito
     * @param bool
     */
    public function isSuccessfull(): bool;

    /**
     * Inicializa los objetos del gateway
     * @param array $param
     * @return bool
     */
    public function initializeTransaction(array $param): bool;

    /**
     * Crea una consulta utilizando un cliente
     * @param array @param
     * @return bool
     */
    public function createRequestWithClient(array $param): bool;

    /**
     * Crea un request y autoriza una transaccion 
     * @param array $param
     * @return bool
     */
    public function authorizedTransaction(array $param): bool;

    /**
     * Captura el monto anteriormente autorizado
     * @param array $param
     * @return bool
     */
    public function completeAuthorizedTransaction(array $param): bool;

    /**
     * Autoriza y captura el monto a depositar
     * @param array $param
     * @return bool
     */
    public function processPurchase(array $param): bool;

    /**
     * Cancela una autorizacion
     * @param array $param
     */
    public function void(array $param);

}