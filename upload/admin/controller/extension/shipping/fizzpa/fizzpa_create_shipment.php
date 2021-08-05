<?php

class ControllerExtensionShippingFizzpaFizzpaCreateshipment extends Controller {
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

        if ($order_info) {
            $this->language->load('extension/shipping/fizzpa');

            $this->document->setTitle($this->language->get('heading_title1'));

            $data['text_back_to_order'] = $this->language->get('text_back_to_order');
            $data['text_create_sipment'] = $this->language->get('text_create_sipment');
            $data['text_track'] = $this->language->get('text_track');
            $data['heading_title'] = $this->language->get('heading_title1');

            $data['breadcrumbs'] = [];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], 'SSL'),
                'separator' => false
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('heading_title1'),
                'href' => $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'], 'SSL'),
                'separator' => ' :: '
            ];

            $data['order_id'] = $this->request->get['order_id'];

            $data['store_name'] = $order_info['store_name'];
            $data['store_url'] = $order_info['store_url'];
            $data['firstname'] = $order_info['firstname'];
            $data['lastname'] = $order_info['lastname'];

            $data['back_to_order'] = $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $order_id, true);

            if ($order_info['customer_id']) {
                $data['customer'] = $this->url->link('extension/shipping/customer/update', 'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $order_info['customer_id'], 'SSL');
            } else {
                $data['customer'] = '';
            }

            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');
            $data['pickup_addresses'] = $this->model_extension_shipping_fizzpa->getPickupAddresses();

            $sender_name = '';
            if (
                (isset($order_info['shipping_firstname']) && ! empty($order_info['shipping_firstname']))
                && isset($order_info['shipping_lastname']) && ! empty($order_info['shipping_lastname'])
            ) {
                $sender_name = $order_info['shipping_firstname'] . ' ' . $order_info['shipping_lastname'];
            }
            
            $sender_phone = ($order_info['telephone']) ? $order_info['telephone'] : '';
            $sender_email = ($order_info['email']) ? $order_info['email'] : '';
            $recipient_city = ($order_info['shipping_city']) ? $order_info['shipping_city'] : '';
            $recipient_address = $order_info['shipping_address_1'];

            if (isset($this->request->post['fizzpa_sender_name']) && !empty($this->request->post['fizzpa_sender_name'])) {
                $data['fizzpa_sender_name'] = $this->request->post['fizzpa_sender_name'];
            } else {
                $data['fizzpa_sender_name'] = $sender_name;
            }

            if (isset($this->request->post['fizzpa_sender_phone']) && !empty($this->request->post['fizzpa_sender_phone'])) {
                $data['fizzpa_sender_phone'] = $this->request->post['fizzpa_sender_phone'];
            } else {
                $data['fizzpa_sender_phone'] = $sender_phone;
            }

            if (isset($this->request->post['fizzpa_sender_email']) && !empty($this->request->post['fizzpa_sender_email'])) {
                $data['fizzpa_sender_email'] = $this->request->post['fizzpa_sender_email'];
            } else {
                $data['fizzpa_sender_email'] = $sender_email;
            }

            if (isset($this->request->post['fizzpa_recipient_city']) && !empty($this->request->post['fizzpa_recipient_city'])) {
                $data['fizzpa_recipient_city'] = $this->request->post['fizzpa_recipient_city'];
            } else {
                $data['fizzpa_recipient_city'] = $recipient_city;
            }

            if (isset($this->request->post['fizzpa_pickup_address_id']) && !empty($this->request->post['fizzpa_pickup_address_id'])) {
                $data['fizzpa_pickup_address_id'] = $this->request->post['fizzpa_pickup_address_id'];
            } else {
                $data['fizzpa_pickup_address_id'] = $this->config->get('shipping_fizzpa_pickup_address_id');
            }

            if (isset($this->request->post['fizzpa_recipient_address']) && !empty($this->request->post['fizzpa_recipient_address'])) {
                $data['fizzpa_recipient_address'] = $this->request->post['fizzpa_recipient_address'];
            } else {
                $data['fizzpa_recipient_address'] = $recipient_address;
            }

            if (isset($this->request->post['fizzpa_recipient_name']) && !empty($this->request->post['fizzpa_recipient_name'])) {
                $data['fizzpa_recipient_name'] = $this->request->post['fizzpa_recipient_name'];
            } else {
                $data['fizzpa_recipient_name'] = $sender_name;
            }

            if (isset($this->request->post['fizzpa_recipient_phone']) && !empty($this->request->post['fizzpa_recipient_phone'])) {
                $data['fizzpa_recipient_phone'] = $this->request->post['fizzpa_recipient_phone'];
            } else {
                $data['fizzpa_recipient_phone'] = $sender_phone;
            }

            if (isset($this->request->post['fizzpa_recipient_email']) && !empty($this->request->post['fizzpa_recipient_email'])) {
                $data['fizzpa_recipient_email'] = $this->request->post['fizzpa_recipient_email'];
            } else {
                $data['fizzpa_recipient_email'] = $sender_email;
            }

            if ($this->request->post) {
                $response = $this->model_extension_shipping_fizzpa->create($this->request->post, $order_info, $this->request->get['order_id']);

                if (! $response->success) {
                    $data['error_warning'] = $response->message;
                } else {
                    $data['success_message'] = $response->message;
                }
            }

            $this->response->setOutput($this->load->view('extension/shipping/fizzpa_create_shipment', $data));
        } else {
            $this->language->load('error/not_found');

            $this->document->setTitle($this->language->get('heading_title1'));

            $data['heading_title'] = $this->language->get('heading_title1');

            $data['text_not_found'] = $this->language->get('text_not_found');

            $data['breadcrumbs'] = [];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], 'SSL'),
                'separator' => false
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('heading_title1'),
                'href' => $this->url->link('error/not_found', 'user_token=' . $this->session->data['user_token'], 'SSL'),
                'separator' => ' :: '
            ];

            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');

            $this->response->setOutput($this->load->view('error/not_found', $data));
        }
    }
}