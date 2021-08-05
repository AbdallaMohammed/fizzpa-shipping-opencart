<?php

class Controllerextensionshippingfizzpafizzpatracking extends Controller {
    private $error = [];

    public function index() {
        $this->load->model('sale/order');
        $this->load->model('extension/shipping/fizzpa');

        if (isset($this->request->get['order_id'])) {
            $order_id = $this->request->get['order_id'];
        } else {
            $order_id = 0;
        }

        $order_info = $this->model_sale_order->getOrder($order_id);
        $order_ref = $this->model_extension_shipping_fizzpa->getOrderRef($order_id);

        if ($order_info && $order_ref) {
            $this->language->load('extension/shipping/fizzpa');
            $this->document->setTitle($this->language->get('heading_tracking'));

            $data['text_back_to_order'] = $this->language->get('text_back_to_order');
            $data['heading_title'] = $this->language->get('heading_tracking');

            $data['breadcrumbs'] = [];

            $data['breadcrumbs'][] = [
                'text'      => $this->language->get('text_home'),
                'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
                'separator' => false
            ];

            $data['breadcrumbs'][] = [
                'text'      => $this->language->get('heading_tracking'),
                'href'      => $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'], true),
                'separator' => ' :: '
            ];

            $data['order_id'] = $this->request->get['order_id'];
            $data['back_to_order'] = $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $order_id, true);

            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');

            $data['shipment'] = $this->model_extension_shipping_fizzpa->getShipmentData($order_ref);
            $data['tracking_data'] = $this->model_extension_shipping_fizzpa->getTrackingData($order_ref);

            $this->response->setOutput($this->load->view('extension/shipping/fizzpa_tracking', $data));
        } else {
            $this->language->load('error/not_found');

            $this->document->setTitle($this->language->get('heading_title'));

            $data['heading_title'] = $this->language->get('heading_title');

            $data['text_not_found'] = $this->language->get('text_not_found');

            $data['breadcrumbs'] = [];

            $data['breadcrumbs'][] = [
                'text'      => $this->language->get('text_home'),
                'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
                'separator' => false
            ];

            $data['breadcrumbs'][] = [
                'text'      => $this->language->get('heading_title'),
                'href'      => $this->url->link('error/not_found', 'user_token=' . $this->session->data['user_token'], true),
                'separator' => ' :: '
            ];

            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');

            $this->response->setOutput($this->load->view('error/not_found', $data));
        }
    }
}