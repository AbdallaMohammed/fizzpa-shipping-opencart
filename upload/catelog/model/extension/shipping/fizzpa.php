<?php

class ModelExtensionShippingFizzpa extends Model {
    public function getQuote($address) {
        $this->load->model('extension/shipping/fizzpa');

        $this->load->language('extension/shipping/fedex');

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get('shipping_fizzpa_geo_zone_id') . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");
    
        if (! $this->config->get('shipping_fizzpa_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $quote_data = [];

        if ($status) {
            $cost = ($this->config->get('shipping_fizzpa_default_rate')) ? $this->config->get('shipping_fizzpa_default_rate') : 10;
            $currency = $this->config->get('config_currency');

            $quote_data['fizzpa'] = [
                'code' => 'fizzpa.fizzpa',
                'title' => 'Fizzpa Shipping Charges',
                'cost' => $this->currency->convert($cost, $currency, $this->config->get('config_currency')),
                'tax_class_id' => $this->config->get('shipping_fizzpa_tax_class_id'),
                'text' => $this->currency->format($this->tax->calculate($this->currency->convert($cost, $currency, $this->config->get('config_currency')), $this->config->get('shipping_fizzpa_tax_class_id'), $this->config->get('config_tax')), $this->config->get('config_currency'), 1.0000000)
            ];
        }

        $error = '';
        $method_data = [];

        if ($quote_data || $error) {
            $title = 'Fizzpa';

            if ($this->config->get('fedex_display_weight')) {
                $title .= ' (' . $this->language->get('text_weight') . ' ' . $this->weight->format($weight, $this->config->get('fedex_weight_class_id')) . ')';
            }

            $method_data = [
                'code' => 'fedex',
                'title' => $title,
                'quote' => $quote_data,
                'sort_order' => $this->config->get('fedex_sort_order'),
                'error' => $error
            ];
        }

        return $method_data;
    }
}