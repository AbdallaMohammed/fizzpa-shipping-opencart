<?php

class ControllerExtensionShippingFizzpa extends Controller {
    private $error = [];

    public function index() {
        $this->language->load('extension/shipping/fizzpa');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/shipping/fizzpa');
        $this->load->model('setting/setting');
        $this->load->model('localisation/geo_zone');
        $this->load->model('localisation/tax_class');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('shipping_fizzpa', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/shipping/fizzpa', 'user_token=' . $this->session->data['user_token'], true));
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_select_all'] = $this->language->get('text_select_all');
        $data['text_unselect_all'] = $this->language->get('text_unselect_all');
        $data['text_all_zones'] = $this->language->get('text_all_zones');
        $data['text_none'] = $this->language->get('text_none');
        
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_token'] = $this->language->get('entry_token');
        $data['entry_referer'] = $this->language->get('entry_referer');
        $data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
        $data['entry_tax_class'] = $this->language->get('entry_tax_class');
        $data['entry_pickup_address_id'] = $this->language->get('entry_pickup_address_id');
        $data['entry_shipping_rate'] = $this->language->get('entry_shipping_rate');

        $data['pickup_addresses'] = $this->model_extension_shipping_fizzpa->getPickupAddresses();
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
        $data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['error_token'])) {
            $data['error_token'] = $this->error['error_token'];
        } else {
            $data['error_token'] = '';
        }

        if (isset($this->error['error_referer'])) {
            $data['error_referer'] = $this->error['error_referer'];
        } else {
            $data['error_referer'] = '';
        }

        if (isset($this->error['error_pickup_address'])) {
            $data['error_pickup_address'] = $this->error['error_pickup_address'];
        } else {
            $data['error_pickup_address'] = '';
        }

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
            'separator' => false
        ];

        $data['breadcrumbs'][] = [
            'text'      => $this->language->get('text_shipping'),
            'href'      => $this->url->link('extension/shipping/fizzpa', 'user_token=' . $this->session->data['user_token'], true),
            'separator' => ' :: '
        ];

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/shipping/fizzpa', 'user_token=' . $this->session->data['user_token'], true),
            'separator' => ' :: '
        );

        $data['action'] = $this->url->link('extension/shipping/fizzpa', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/extension&type=shipping', 'user_token=' . $this->session->data['user_token'], true);

        if (isset($this->request->post['shipping_fizzpa_token'])) {
            $data['shipping_fizzpa_token'] = $this->request->post['shipping_fizzpa_token'];
        } else {
            $data['shipping_fizzpa_token'] = $this->config->get('shipping_fizzpa_token');
        }

        if (isset($this->request->post['shipping_fizzpa_referer'])) {
            $data['shipping_fizzpa_referer'] = $this->request->post['shipping_fizzpa_referer'];
        } else {
            $data['shipping_fizzpa_referer'] = $this->config->get('shipping_fizzpa_referer');
        }

        if (isset($this->request->post['shipping_fizzpa_geo_zone_id'])) {
            $data['shipping_fizzpa_geo_zone_id'] = $this->request->post['shipping_fizzpa_geo_zone_id'];
        } else {
            $data['shipping_fizzpa_geo_zone_id'] = $this->config->get('shipping_fizzpa_geo_zone_id');
        }

        if (isset($this->request->post['shipping_fizzpa_pickup_address_id'])) {
            $data['shipping_fizzpa_pickup_address_id'] = $this->request->post['shipping_fizzpa_pickup_address_id'];
        } else {
            $data['shipping_fizzpa_pickup_address_id'] = $this->config->get('shipping_fizzpa_pickup_address_id');
        }

        if (isset($this->request->post['shipping_fizzpa_tax_class_id'])) {
            $data['shipping_fizzpa_tax_class_id'] = $this->request->post['shipping_fizzpa_tax_class_id'];
        } else {
            $data['shipping_fizzpa_tax_class_id'] = $this->config->get('shipping_fizzpa_tax_class_id');
        }

        if (isset($this->request->post['shipping_fizzpa_status'])) {
            $data['shipping_fizzpa_status'] = $this->request->post['shipping_fizzpa_status'];
        } else {
            $data['shipping_fizzpa_status'] = $this->config->get('shipping_fizzpa_status');
        }

        if (isset($this->request->post['shipping_fizzpa_default_rate'])) {
            $data['shipping_fizzpa_default_rate'] = $this->request->post['shipping_fizzpa_default_rate'];
        } else {
            $data['shipping_fizzpa_default_rate'] = $this->config->get('shipping_fizzpa_default_rate');
        }
    
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/shipping/fizzpa', $data));
    }

    protected function validate() {
        if (! $this->user->hasPermission('modify', 'extension/shipping/fizzpa')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (! $this->request->post['shipping_fizzpa_token']) {
            $this->error['error_token'] = $this->language->get('error_token');
        }

        if (! $this->request->post['shipping_fizzpa_referer']) {
            $this->error['error_referer'] = $this->language->get('error_referer');
        }

        if (
            ! $this->request->post['shipping_fizzpa_pickup_address_id'] 
            && (
                ! empty($this->config->get('shipping_fizzpa_token'))
                && ! empty($this->config->get('shipping_fizzpa_referer'))
            )) {
            $this->error['error_pickup_address'] = $this->language->get('error_pickup_address');
        }

        return ! $this->error;
    }
}