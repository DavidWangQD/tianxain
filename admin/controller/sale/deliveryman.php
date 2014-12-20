<?php    
class ControllerSaleDeliveryman extends Controller {

	private $error = array();

    protected $filterItems = array(
        "filter_no",
        "filter_name",
        "filter_telephone"
    );

    protected function generalCreateBaseURLParams($params = array()) {

        $url = '';

        if(!empty($this->filterItems)  && is_array($this->filterItems)) {

            foreach($this->filterItems as $item) {

                if(isset($this->request->get[$item])) {

                    $url .= '&' . $item . '=' . urlencode(html_entity_decode($this->request->get[$item], ENT_QUOTES, 'UTF-8'));

                }

            }

        }

        if(!empty($this->filterItemsArray)  && is_array($this->filterItemsArray)) {

            foreach($this->filterItemsArray as $item) {

                if(isset($this->request->get[$item]) && is_array($this->request->get[$item])) {

                    foreach($this->request->get[$item] as $innerItem) {

                        $url .= '&' . $item . '[]=' . urlencode(html_entity_decode($innerItem, ENT_QUOTES, 'UTF-8'));

                    }

                }

            }

        }

        if(in_array('sort',$params) && isset($this->request->get['sort'])) {

            $url .= '&sort=' . $this->request->get['sort'];

        }

        if(in_array('order',$params) && isset($this->request->get['order'])) {

            $url .= '&order=' . $this->request->get['order'];

        }

        if(in_array('page',$params) && isset($this->request->get['page'])) {

            $url .= '&page=' . $this->request->get['page'];

        }

        return $url;

    }

    protected function generalFilterProcess($params) {

        if(!empty($this->filterItems)  && is_array($this->filterItems)) {

            foreach($this->filterItems as $item) {

                if(isset($this->request->get[$item])) {
                    $params[$item] = $this->request->get[$item];
                    $this->data[$item] = $this->request->get[$item];
                }else{
                    $this->data[$item] = '';
                }

            }

        }

        if(!empty($this->filterItemsArray)  && is_array($this->filterItemsArray)) {

            foreach($this->filterItemsArray as $item) {

                if(isset($this->request->get[$item])) {
                    $params[$item] = $this->request->get[$item];
                    $this->data[$item] = $this->request->get[$item];
                }else{
                    $this->data[$item] = array();
                }

            }

        }

        return $params;

    }

	public function index() {
		$this->language->load('sale/deliveryman');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/deliveryman');

		$this->getList();
	}

	public function insert() {
		$this->language->load('sale/deliveryman');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/deliveryman');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_sale_deliveryman->addDeliveryman($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

            $url = $this->generalCreateBaseURLParams(array('sort','order','page'));

			$this->redirect($this->url->link('sale/deliveryman', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function update() {
		$this->language->load('sale/deliveryman');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/deliveryman');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_sale_deliveryman->editDeliveryman($this->request->get['deliveryman_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

            $url = $this->generalCreateBaseURLParams(array('sort','order','page'));

			$this->redirect($this->url->link('sale/deliveryman', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->language->load('sale/deliveryman');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/deliveryman');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $deliveryman_id) {
				$this->model_sale_deliveryman->deleteDeliveryman($deliveryman_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

            $url = $this->generalCreateBaseURLParams(array('sort','order','page'));

			$this->redirect($this->url->link('sale/deliveryman', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	public function approve() {
		$this->language->load('sale/deliveryman');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/deliveryman');	

		if (!$this->user->hasPermission('modify', 'sale/deliveryman')) {
			$this->error['warning'] = $this->language->get('error_permission');
		} elseif (isset($this->request->post['selected'])) {
			$approved = 0;

			foreach ($this->request->post['selected'] as $deliveryman_id) {
				$deliveryman_info = $this->model_sale_deliveryman->getAffiliate($deliveryman_id);

				if ($deliveryman_info && !$deliveryman_info['approved']) {
					$this->model_sale_deliveryman->approve($deliveryman_id);

					$approved++;
				}
			}

			$this->session->data['success'] = sprintf($this->language->get('text_approved'), $approved);

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_email'])) {
				$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['filter_approved'])) {
				$url .= '&filter_approved=' . $this->request->get['filter_approved'];
			}	

			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}	

			$this->redirect($this->url->link('sale/deliveryman', 'token=' . $this->session->data['token'] . $url, 'SSL'));					
		}

		$this->getList();
	} 

	protected function getList() {
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = null;
		}

		if (isset($this->request->get['filter_email'])) {
			$filter_email = $this->request->get['filter_email'];
		} else {
			$filter_email = null;
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = null;
		}

		if (isset($this->request->get['filter_approved'])) {
			$filter_approved = $this->request->get['filter_approved'];
		} else {
			$filter_approved = null;
		}

		if (isset($this->request->get['filter_date_added'])) {
			$filter_date_added = $this->request->get['filter_date_added'];
		} else {
			$filter_date_added = null;
		}	

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name'; 
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

        $url = $this->generalCreateBaseURLParams(array('sort','order','page'));

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),       		
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/deliveryman', 'token=' . $this->session->data['token'] . $url, 'SSL'),
			'separator' => ' :: '
		);

		$this->data['approve'] = $this->url->link('sale/deliveryman/approve', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['insert'] = $this->url->link('sale/deliveryman/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('sale/deliveryman/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['deliverymans'] = array();

		$data = array(
			'sort'              => $sort,
			'order'             => $order,
			'start'             => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'             => $this->config->get('config_admin_limit')
		);

        $data = $this->generalFilterProcess($data);

		$affiliate_total = $this->model_sale_deliveryman->deliverymanCount($data);

		$results = $this->model_sale_deliveryman->deliverymans($data);

		foreach ($results->rows as $result) {
			$action = array();

			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('sale/deliveryman/update', 'token=' . $this->session->data['token'] . '&deliveryman_id=' . $result['deliveryman_id'] . $url, 'SSL')
			);

			$this->data['deliverymans'][] = array(
				'deliveryman_id' => $result['deliveryman_id'],
				'deliveryman_no' => $result['deliveryman_no'],
				'name'           => $result['name'],
				'telephone'      => $result['telephone'],
				'description'    => $result['description'],
				'selected'       => isset($this->request->post['deliveryman_id']) && in_array($result['deliveryman_id'], $this->request->post['selected']),
				'action'         => $action
			);
		}	

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');		
		$this->data['text_no_results'] = $this->language->get('text_no_results');

		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_email'] = $this->language->get('column_email');
		$this->data['column_balance'] = $this->language->get('column_balance');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_approved'] = $this->language->get('column_approved');
		$this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_action'] = $this->language->get('column_action');		

		$this->data['button_approve'] = $this->language->get('button_approve');
		$this->data['button_insert'] = $this->language->get('button_insert');
		$this->data['button_delete'] = $this->language->get('button_delete');
		$this->data['button_filter'] = $this->language->get('button_filter');

		$this->data['token'] = $this->session->data['token'];

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_approved'])) {
			$url .= '&filter_approved=' . $this->request->get['filter_approved'];
		}	

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$this->data['sort_name'] = $this->url->link('sale/deliveryman', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');
		$this->data['sort_email'] = $this->url->link('sale/deliveryman', 'token=' . $this->session->data['token'] . '&sort=a.email' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('sale/deliveryman', 'token=' . $this->session->data['token'] . '&sort=a.status' . $url, 'SSL');
		$this->data['sort_approved'] = $this->url->link('sale/deliveryman', 'token=' . $this->session->data['token'] . '&sort=a.approved' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('sale/deliveryman', 'token=' . $this->session->data['token'] . '&sort=a.date_added' . $url, 'SSL');

        $url = $this->generalCreateBaseURLParams(array('sort','order'));

		$pagination = new Pagination();
		$pagination->total = $affiliate_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/deliveryman', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['filter_name'] = $filter_name;
		$this->data['filter_email'] = $filter_email;
		$this->data['filter_status'] = $filter_status;
		$this->data['filter_approved'] = $filter_approved;
		$this->data['filter_date_added'] = $filter_date_added;

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->template = 'sale/deliveryman_list.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	protected function getForm() {
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_select'] = $this->language->get('text_select');
		$this->data['text_none'] = $this->language->get('text_none');
		$this->data['text_wait'] = $this->language->get('text_wait');
		$this->data['text_cheque'] = $this->language->get('text_cheque');
		$this->data['text_paypal'] = $this->language->get('text_paypal');
		$this->data['text_bank'] = $this->language->get('text_bank');

		$this->data['entry_firstname'] = $this->language->get('entry_firstname');
		$this->data['entry_lastname'] = $this->language->get('entry_lastname');
		$this->data['entry_email'] = $this->language->get('entry_email');
		$this->data['entry_telephone'] = $this->language->get('entry_telephone');
		$this->data['entry_fax'] = $this->language->get('entry_fax');
		$this->data['entry_company'] = $this->language->get('entry_company');
		$this->data['entry_address_1'] = $this->language->get('entry_address_1');
		$this->data['entry_address_2'] = $this->language->get('entry_address_2');
		$this->data['entry_city'] = $this->language->get('entry_city');
		$this->data['entry_postcode'] = $this->language->get('entry_postcode');
		$this->data['entry_country'] = $this->language->get('entry_country');
		$this->data['entry_zone'] = $this->language->get('entry_zone');
		$this->data['entry_code'] = $this->language->get('entry_code');
		$this->data['entry_commission'] = $this->language->get('entry_commission');
		$this->data['entry_tax'] = $this->language->get('entry_tax');
		$this->data['entry_payment'] = $this->language->get('entry_payment');
		$this->data['entry_cheque'] = $this->language->get('entry_cheque');
		$this->data['entry_paypal'] = $this->language->get('entry_paypal');
		$this->data['entry_bank_name'] = $this->language->get('entry_bank_name');
		$this->data['entry_bank_branch_number'] = $this->language->get('entry_bank_branch_number');
		$this->data['entry_bank_swift_code'] = $this->language->get('entry_bank_swift_code');
		$this->data['entry_bank_account_name'] = $this->language->get('entry_bank_account_name');
		$this->data['entry_bank_account_number'] = $this->language->get('entry_bank_account_number');
		$this->data['entry_password'] = $this->language->get('entry_password');
		$this->data['entry_confirm'] = $this->language->get('entry_confirm');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_amount'] = $this->language->get('entry_amount');
		$this->data['entry_description'] = $this->language->get('entry_description');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_add_transaction'] = $this->language->get('button_add_transaction');
		$this->data['button_remove'] = $this->language->get('button_remove');

		$this->data['tab_general'] = $this->language->get('tab_general');
		$this->data['tab_payment'] = $this->language->get('tab_payment');
		$this->data['tab_transaction'] = $this->language->get('tab_transaction');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$this->data['error_name'] = $this->error['name'];
		} else {
			$this->data['error_name'] = '';
		}

		if (isset($this->error['telephone'])) {
			$this->data['error_telephone'] = $this->error['telephone'];
		} else {
			$this->data['error_telephone'] = '';
		}

        $url = $this->generalCreateBaseURLParams(array('sort','order','page'));

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/deliveryman', 'token=' . $this->session->data['token'] . $url, 'SSL'),
			'separator' => ' :: '
		);

		if (!isset($this->request->get['deliveryman_id'])) {
			$this->data['action'] = $this->url->link('sale/deliveryman/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('sale/deliveryman/update', 'token=' . $this->session->data['token'] . '&deliveryman_id=' . $this->request->get['deliveryman_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('sale/deliveryman', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['deliveryman_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$deliveryman_info = $this->model_sale_deliveryman->getDeliveryman($this->request->get['deliveryman_id']);
		}

		$this->data['token'] = $this->session->data['token'];

		if (isset($this->request->get['deliveryman_id'])) {
			$this->data['deliveryman_id'] = $this->request->get['deliveryman_id'];
		} else {
			$this->data['deliveryman_id'] = 0;
		}

        if (isset($this->request->post['deliveryman_no'])) {
            $this->data['deliveryman_no'] = $this->request->post['deliveryman_no'];
        } elseif (!empty($deliveryman_info)) {
            $this->data['deliveryman_no'] = $deliveryman_info['deliveryman_no'];
        } else {
            $this->data['deliveryman_no'] = '';
        }

		if (isset($this->request->post['name'])) {
			$this->data['name'] = $this->request->post['name'];
		} elseif (!empty($deliveryman_info)) { 
			$this->data['name'] = $deliveryman_info['name'];
		} else {
			$this->data['name'] = '';
		}

        if (isset($this->request->post['telephone'])) {
            $this->data['telephone'] = $this->request->post['telephone'];
        } elseif (!empty($deliveryman_info)) {
            $this->data['telephone'] = $deliveryman_info['telephone'];
        } else {
            $this->data['telephone'] = '';
        }

        if (isset($this->request->post['description'])) {
            $this->data['description'] = $this->request->post['description'];
        } elseif (!empty($deliveryman_info)) {
            $this->data['description'] = $deliveryman_info['description'];
        } else {
            $this->data['description'] = '';
        }

        $this->template = 'sale/deliveryman_form.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'sale/deliveryman')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 1) || (utf8_strlen($this->request->post['name']) > 32)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if ((utf8_strlen($this->request->post['telephone']) < 1) || (utf8_strlen($this->request->post['telephone']) > 32)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'sale/deliveryman')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}  
	}

	public function country() {
		$json = array();

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']		
			);
		}

		$this->response->setOutput(json_encode($json));
	}

	public function transaction() {
		$this->language->load('sale/deliveryman');

		$this->load->model('sale/deliveryman');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->hasPermission('modify', 'sale/deliveryman')) { 
			$this->model_sale_deliveryman->addTransaction($this->request->get['deliveryman_id'], $this->request->post['description'], $this->request->post['amount']);

			$this->data['success'] = $this->language->get('text_success');
		} else {
			$this->data['success'] = '';
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && !$this->user->hasPermission('modify', 'sale/deliveryman')) {
			$this->data['error_warning'] = $this->language->get('error_permission');
		} else {
			$this->data['error_warning'] = '';
		}

		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_balance'] = $this->language->get('text_balance');

		$this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_description'] = $this->language->get('column_description');
		$this->data['column_amount'] = $this->language->get('column_amount');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}  

		$this->data['transactions'] = array();

		$results = $this->model_sale_deliveryman->getTransactions($this->request->get['deliveryman_id'], ($page - 1) * 10, 10);

		foreach ($results as $result) {
			$this->data['transactions'][] = array(
				'amount'      => $this->currency->format($result['amount'], $this->config->get('config_currency')),
				'description' => $result['description'],
				'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
		}

		$this->data['balance'] = $this->currency->format($this->model_sale_deliveryman->getTransactionTotal($this->request->get['deliveryman_id']), $this->config->get('config_currency'));

		$transaction_total = $this->model_sale_deliveryman->getTotalTransactions($this->request->get['deliveryman_id']);

		$pagination = new Pagination();
		$pagination->total = $transaction_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/deliveryman/transaction', 'token=' . $this->session->data['token'] . '&deliveryman_id=' . $this->request->get['deliveryman_id'] . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->template = 'sale/deliveryman_transaction.tpl';		

		$this->response->setOutput($this->render());
	}

	public function autocomplete() {
		$affiliate_data = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('sale/deliveryman');

			$data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 20
			);

			$results = $this->model_sale_deliveryman->getAffiliates($data);

			foreach ($results as $result) {
				$affiliate_data[] = array(
					'deliveryman_id' => $result['deliveryman_id'],
					'name'         => html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')
				);
			}
		}

		$this->response->setOutput(json_encode($affiliate_data));
	}		
}
?>