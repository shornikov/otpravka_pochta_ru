<?php
/**
 * Created by PhpStorm.
 * User: shornikov
 * Date: 27.11.2017
 * Time: 10:52
 */

//declare(strict_types=1);
//
class otpravka
{
    private $ch;
    private $headers;
    private $url = "https://otpravka-api.pochta.ru/";
    private $raw;
    private $lastError = [];
    private $lastErrorType = "string";


    function __construct($login, $password)
    {
        $this->ch = curl_init();
        $this->headers = [
            "Authorization:	AccessToken " . $login,
            "X-User-Authorization:	Basic " . $password,
            "Content-Type:	application/json;charset=UTF-8",
        ];
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
    }


    function __destruct()
    {
        curl_close($this->ch);
    }


    function close()
    {
        $this->__destruct();
    }

    function getLastRaw(): string
    {
        return $this->raw;
    }

    function getSetting()
    {
        $command = "/1.0/settings";

        $this->lastErrorClear();

        curl_setopt($this->ch, CURLOPT_URL, $this->url . $command);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "GET");

        $this->raw = curl_exec($this->ch);

        if ($this->connectionError()) {
            $this->lastError = $this->connectionErrorDescription();
            return false;
        }

        $json = json_decode($this->raw, JSON_UNESCAPED_UNICODE);

        if (json_last_error() != JSON_ERROR_NONE) {
            $this->lastError = json_last_error_msg();
            return false;
        }
        if (isset($json['status']) && $json['status'] == "ERROR" && isset($json['message'])) {
            $this->lastError = $json['message'];
            return false;
        }

        return json_decode($this->raw, JSON_UNESCAPED_UNICODE);
    }


    function getShippingPoints()
    {
        $command = "/1.0/user-shipping-points";

        $this->lastErrorClear();

        curl_setopt($this->ch, CURLOPT_URL, $this->url . $command);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "GET");

        $this->raw = curl_exec($this->ch);

        if ($this->connectionError()) {
            $this->lastError = $this->connectionErrorDescription();
            return false;
        }

        return json_decode($this->raw, JSON_UNESCAPED_UNICODE);
    }


    function connectionError()
    {
        $err = curl_errno($this->ch);
        if ($err != 0)
            return $err;
        else return false;
    }


    function connectionErrorDescription()
    {
        if ($this->connectionError())
            return curl_error($this->ch);
        else return false;
    }


    function lastError()
    {
        return $this->lastError;
    }

    function lastErrorType()
    {
        return $this->lastErrorType;
    }

    private function lastErrorClear()
    {
        $this->lastError = "";
        $this->lastErrorType = "string";
    }

    /**
     * Создание блока
     * @param array $ids
     * @return array|false
     */
    function createBlock(array $ids)
    {
        $command = "/1.0/user/shipment";

        $this->lastErrorClear();
        curl_setopt($this->ch, CURLOPT_URL, $this->url . $command);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "POST");

        $idsJSON = "[" . join(",", $ids) . "]";
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $idsJSON);

        $this->raw = curl_exec($this->ch);

        if ($this->connectionError()) {
            $this->lastError = $this->connectionErrorDescription();
            return false;
        }

        $json = json_decode($this->raw, JSON_UNESCAPED_UNICODE);

        if (json_last_error() != JSON_ERROR_NONE) {
            $this->lastError = json_last_error_msg();
            return false;
        }

        if (isset($json['error']) && isset($json['status']) && isset($json['exception']) && isset($json['message'])) {
            $this->lastError = $json['message'] . ": " . $json['exception'];
            return false;
        }

        if (isset($json['errors']) && is_array($json['errors'])) {
            $this->lastErrorType = "json";
            $this->lastError = $json['errors'];
            return false;
        }

        return json_decode($this->raw, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Получение общей информации о блоке
     * @param int $blockId
     * @return array|false
     */
    function getBlockInfo(int $blockId)
    {
        $command = "/1.0/batch/" . $blockId;

        $this->lastErrorClear();

        curl_setopt($this->ch, CURLOPT_URL, $this->url . $command);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "GET");

        $this->raw = curl_exec($this->ch);

        if ($this->connectionError()) {
            $this->lastError = $this->connectionErrorDescription();
            return false;
        }

        return json_decode($this->raw, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Получение полной информации о блоке
     * @param $blockId
     * @return array|false
     */
    function getBlockInfoExtended($blockId)
    {
        $command = "/1.0/batch/" . $blockId . "/shipment";

        $this->lastErrorClear();

        curl_setopt($this->ch, CURLOPT_URL, $this->url . $command);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "GET");

        $this->raw = curl_exec($this->ch);

        if ($this->connectionError()) {
            $this->lastError = $this->connectionErrorDescription();
            return false;
        }

        $json = json_decode($this->raw, JSON_UNESCAPED_UNICODE);

        if (json_last_error() != JSON_ERROR_NONE) {
            $this->lastError = json_last_error_msg();
            return false;
        }

        if (isset($json['code']) && isset($json['desc']) && isset($json['sub-code'])) {
            $this->lastError = $json['desc'];
            return false;
        }

        return json_decode($this->raw, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Возвращение заказа из блока в новые
     * @param $ids
     * @return array|false
     */
    function revokeOrders($ids)
    {
        $command = "/1.0/user/backlog";

        $this->lastErrorClear();

        curl_setopt($this->ch, CURLOPT_URL, $this->url . $command);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "POST");

        if (is_array($ids)) {
            $idsJSON = "[" . join(",", $ids) . "]";
        } else {
            $idsJSON = "[" . $ids . "]";
        }

        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $idsJSON);


        if ($this->connectionError()) {
            $this->lastError = $this->connectionErrorDescription();
            return false;
        }

        $this->raw = curl_exec($this->ch);
        return json_decode($this->raw, JSON_UNESCAPED_UNICODE);

    }

    /**
     * Разбор блока в новые
     * @param int $blockId
     * @return array|false
     */
    function revokeBlock(int $blockId)
    {
        $result = $this->getBlockInfoExtended($blockId);

        if (!$result)
            return false;
        $ids = array_column($result, "id");

        $res = $this->revokeOrders($ids);
        if (!$res)
            return false;

        return json_decode($this->raw, JSON_UNESCAPED_UNICODE);
    }


    /**
     * @param array $orderData
     * @return array|boolean
     * returned array with id's
     */
    function createOrder(array $orderData)
    {
        $command = "/1.0/user/backlog";

        $this->lastErrorClear();

        curl_setopt($this->ch, CURLOPT_URL, $this->url . $command);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "PUT");

        $orderDataJson = json_encode($orderData, JSON_UNESCAPED_UNICODE);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $orderDataJson);

        $this->raw = curl_exec($this->ch);

        if ($this->connectionError()) {
            $this->lastError = $this->connectionErrorDescription();
            return false;
        }

        $json = json_decode($this->raw, JSON_UNESCAPED_UNICODE);

        if (json_last_error() != JSON_ERROR_NONE) {
            $this->lastError = json_last_error_msg();
            return false;
        }

        if (isset($json['code']) && isset($json['desc']) && isset($json['sub-code'])) {
            $this->lastError = $json['sub-code'] . ": " . $json['desc'];
            return false;
        }

        if (isset($json['error']) && isset($json['status']) && isset($json['exception']) && isset($json['message'])) {
            $this->lastError = $json['message'] . ": " . $json['exception'];
            return false;
        }

        if (isset($json['errors'])) {
            $this->lastError = $json['errors'];
            $this->lastErrorType = "array";
            return false;
        }
        if (isset($json['result-ids'])) {
            return $json['result-ids'];
        }

        return false;

    }


    function updateOrder(int $id, array $orderData)
    {
        $command = "/1.0/backlog/" . $id;

        $this->lastErrorClear();

        curl_setopt($this->ch, CURLOPT_URL, $this->url . $command);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "PUT");

        $dataJson = json_encode($orderData, JSON_UNESCAPED_UNICODE);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $dataJson);

        $this->raw = curl_exec($this->ch);

        if ($this->connectionError()) {
            $this->lastError = $this->connectionErrorDescription();
            return false;
        }

        $json = json_decode($this->raw, JSON_UNESCAPED_UNICODE);

        if (json_last_error() != JSON_ERROR_NONE) {
            $this->lastError = json_last_error_msg();
            return false;
        }

        // ошибки с неправильными данными
        if (isset($json['error']) && isset($json['status']) && isset($json['exception']) && isset($json['message'])) {
            $this->lastError = $json['message'] . ": " . $json['exception'];
            return false;
        }

        // неверный номер
        if (isset($json['code']) && isset($json['desc']) && isset($json['sub-code'])) {
            $this->lastError = $json['sub-code'] . ": " . $json['desc'];
            return false;
        }

        //все найдено но ошибки
        if (isset($json['error-codes'])) {
            if (sizeof($json['error-codes']) == 1) {
                if (isset($json['error-codes'][0]['details']))
                    $this->lastError = $json['error-codes'][0]['details'];
                else $this->lastError = $json['error-codes'][0]['description'];
            } else {
                $this->lastErrorType = "json";
                $this->lastError = array_column($json['error-codes'], "description");
            }
            return false;
        }
        //при успехе - пустой массив
        return true;
    }

    /**
     * Удаление заказа
     * @param $ids
     * @return array|false
     */
    function deleteOrder($ids)
    {
        $command = "/1.0/backlog";

        $this->lastErrorClear();

        curl_setopt($this->ch, CURLOPT_URL, $this->url . $command);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "DELETE");

        if (is_array($ids)) {
            $idsJSON = "[" . join(",", $ids) . "]";
        } else {
            $idsJSON = "[" . $ids . "]";
        }

        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $idsJSON);

        $this->raw = curl_exec($this->ch);

        if ($this->connectionError()) {
            $this->lastError = $this->connectionErrorDescription();
            return false;
        }

        $json = json_decode($this->raw, JSON_UNESCAPED_UNICODE);

        if (json_last_error() != JSON_ERROR_NONE) {
            $this->lastError = json_last_error_msg();
            return false;
        }

        if (isset($json['errors'])) {
            $this->lastError = $json['errors'];
            $this->lastErrorType = "array";
            return false;
        }

//        {
//            "result-ids" : [ xxx ]
//}
        return true;
    }

    /**
     * Получение информации о заказе
     * @param int $id
     * @return array|false
     */
    function getOrder(int $id)
    {
        $command = "/1.0/backlog/" . $id;

        $this->lastErrorClear();

        curl_setopt($this->ch, CURLOPT_URL, $this->url . $command);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "GET");

        $this->raw = curl_exec($this->ch);

        if ($this->connectionError()) {
            $this->lastError = $this->connectionErrorDescription();
            return false;
        }

        return json_decode($this->raw, JSON_UNESCAPED_UNICODE);

    }

    /**
     * Получение документтов партии (Zip-файл)
     * @param int $blockId
     * @return string|false
     */
    function getBlockFiles(int $blockId): string
    {
        $command = "/1.0/forms/" . $blockId . "/zip-all";

        $this->lastErrorClear();

        curl_setopt($this->ch, CURLOPT_URL, $this->url . $command);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "GET");

        $this->raw = curl_exec($this->ch);

        if ($this->connectionError()) {
            $this->lastError = $this->connectionErrorDescription();
            return false;
        }

        if ($this->raw[0] != "{" && json_decode($this->raw) == null)
            return $this->raw;
        else {
            $json = json_decode($this->raw, true);
            if (isset($json['status']) && $json['status'] == "ERROR" && isset($json['message']))
                $this->lastError = $json['message'];
            elseif (isset($json['code']) && isset($json["desc"]) && $json['code'] == 1013) {
                $this->lastError = $json['desc'];
            }
            return false;
        }
    }

    /**
     * Подпись партии
     * @param int $blockId
     * @param bool $sendEmail
     * @return array|false
     */
    function checkInBlock(int $blockId, bool $sendEmail = false)
    {
        $command = "/1.0/batch/" . $blockId . "/checkin?sendEmail=" . ($sendEmail ? 'true' : 'false');

        $this->lastErrorClear();

        curl_setopt($this->ch, CURLOPT_URL, $this->url . $command);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "POST");

        $this->raw = curl_exec($this->ch);

        if ($this->connectionError()) {
            $this->lastError = $this->connectionErrorDescription();
            return false;
        }
        $json = json_decode($this->raw, JSON_UNESCAPED_UNICODE);

        if (json_last_error() != JSON_ERROR_NONE) {
            $this->lastError = json_last_error_msg();
            return false;
        }
        if (isset($json["error-code"])) {
            if ($json["error-code"] == "BATCH_NOT_CHANGED") {
                return true;
            } else {
                $this->lastError = $json["error-code"];
                return false;
            }
        } else if (isset($json["f103-sent"]))
            return true;
        else {
            $this->lastError = "Неизвестная ошибка. ожидали получить переменную f103-sent, а ее нет.";
            return false;
        }
    }


    /**
     * Установка даты сдачи партии
     * @param $blockId
     * @param $date (YYYY-MM-DD) format
     * @return bool|array
     */
    function setBlockDate(int $blockId, string $date)
    {
        list($year, $month, $day) = preg_split("/-/", $date);

        if (empty($year) || empty($month) || empty($day)) {
            return false;
        }
        $command = "/1.0/batch/" . $blockId . "/sending/" . $year . "/" . $month . "/" . $day;

        $this->lastErrorClear();

        curl_setopt($this->ch, CURLOPT_URL, $this->url . $command);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "POST");

        $this->raw = curl_exec($this->ch);

        if ($this->connectionError()) {
            $this->lastError = $this->connectionErrorDescription();
            return false;
        }

        $json = json_decode($this->raw, JSON_UNESCAPED_UNICODE);

        if (json_last_error() != JSON_ERROR_NONE) {
            $this->lastError = json_last_error_msg();
            return false;
        }

        // ошибки с неправильными данными
        if (isset($json['error']) && isset($json['status']) && isset($json['exception']) && isset($json['message'])) {
            $this->lastError = $json['message'] . ": " . $json['exception'];
            return false;
        }


        if (isset($json['error-code'])) {
            $this->lastError = $json['error-code'];
            return false;
        }

        if (isset($json['code']) && isset($json['desc'])) {
            $this->lastError = $json['desc'];
            return false;
        }

        if (is_array($json) && sizeof($json) == 0)
            return true;

        $this->lastError = "Неизвестная ошибка";
        return false;
    }
}
