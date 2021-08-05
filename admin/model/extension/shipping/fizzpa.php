<?php

class ModelExtensionShippingFizzpa extends Model {
    public function create($args, $order, $order_id) {
        $curl = curl_init();

        $pieces_count = 0;
        $total_weight = 0;

        if (isset($order_info['payment_code']) && $order_info['payment_code'] == 'cod') {
            $cod_amount = number_format($order_info['total'], 2);
        } else {
            $cod_amount = 0;
        }

        $config_weight_class_id = $this->config->get('config_weight_class_id');

        $order_products = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int) $order_id . "'");

        foreach ($order_products->rows as $order_product) {
            $product_weight_query = $this->db->query("SELECT weight, weight_class_id FROM " . DB_PREFIX . "product WHERE product_id = '" . $order_product['product_id'] . "'");
            $weight_class_query = $this->db->query("SELECT wcd.unit FROM " . DB_PREFIX . "weight_class wc LEFT JOIN " . DB_PREFIX . "weight_class_description wcd ON (wc.weight_class_id = wcd.weight_class_id) WHERE wcd.language_id = '" . (int) $this->config->get('config_language_id') . "' AND wc.weight_class_id = '" . $product_weight_query->row['weight_class_id'] . "'");

            $weight = number_format($this->weight->convert($product_weight_query->row['weight'], $product_weight_query->row['weight_class_id'], $config_weight_class_id), 2);

            $total_weight += ($weight * $order_product['quantity']);
            $pieces_count += $order_product['quantity'];
        }

        curl_setopt($curl, CURLOPT_URL, 'https://fizzapi.anyitservice.com/api/orders');
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
            'SenderPhone' => $args['fizzpa_sender_phone'],
            'SenderName' => $args['fizzpa_sender_name'],
            'SenderEmail' => $args['fizzpa_sender_email'],
            'RecipientCityId' => $this->getRecipientCityId($args['fizzpa_recipient_city']),
            'RecipientName' => $args['fizzpa_recipient_name'],
            'RecipientPhone1' => $args['fizzpa_recipient_phone'],
            'RecipientAddress' => $args['fizzpa_recipient_address'],
            'RecipientEmail' => $args['fizzpa_recipient_email'],
            'PickupAddressId' => $args['fizzpa_pickup_address_id'],
            'OrderRef' => $order_id,
            'CodAmount' => $cod_amount,
            'OrderPiecesCount' => $pieces_count,
            'OrderTotalWeight' => $total_weight,
        ]));
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: ' . $this->config->get('shipping_fizzpa_token'),
            'Referer: ' . $this->config->get('shipping_fizzpa_referer'),
        ]);

        $response = json_decode(curl_exec($curl));
        curl_close($curl);

        return $response;
    }

    public function getPickupAddresses() {
        $data = [];

        if (empty($this->config->get('shipping_fizzpa_token')) || empty($this->config->get('shipping_fizzpa_referer'))) {
            return [
                '' => $this->language->get('text_select'),
            ];
        }

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, 'https://fizzapi.anyitservice.com/api/locations/AgentAddress');
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: ' . $this->config->get('shipping_fizzpa_token'),
            'Referer: ' . $this->config->get('shipping_fizzpa_referer'),
        ]);

        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);

        if (is_array($response)) {
            foreach ($response as $address) {
                $data[$address['AddressNumber']] = $address['Address'];
            }
        }

        return $data;
    }

    public function getRecipientCityId($city) {
        $lang = ! preg_match('/[^A-Za-z0-9]/', $city) ? 'en' : 'ar';

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, 'https://fizzapi.anyitservice.com/api/locations/cities/' . $city . '/' . $lang);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: ' . $this->config->get('shipping_fizzpa_token'),
            'Referer: ' . $this->config->get('shipping_fizzpa_referer'),
        ]);

        $response = json_decode(curl_exec($curl));
        curl_close($curl);

        if ($response->CityId) {
            return $response->CityId;
        }

        return 1;
    }

    public function getOrderRef($order_ref) {
        return 0;
    }

    public function getShipmentData($order_id) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, 'https://fizzapi.anyitservice.com/api/orders/' . $order_id);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: ' . $this->config->get('shipping_fizzpa_token'),
            'Referer: ' . $this->config->get('shipping_fizzpa_referer'),
        ]);

        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);

        return $response[0];
    }

    public function getTrackingData($order_id) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, 'https://fizzapi.anyitservice.com/api/Tracking/' . $order_id);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: ' . $this->config->get('shipping_fizzpa_token'),
            'Referer: ' . $this->config->get('shipping_fizzpa_referer'),
        ]);

        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);

        return $response;
    }
}