<?php
	class Shop extends Controller {

		private $orderModel;
		private $customerModel;

		// Sin verificación de sesión: la usan los clientes
		public function __construct() {
			$this->orderModel    = $this->model('Order');
			$this->customerModel = $this->model('Customer');
		}

		// Paso 1: el cliente se identifica con su código
		public function index() {
			$data = ['code' => '', 'error' => ''];

			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				$data['code'] = strtoupper(trim($_POST['txtCodigo'] ?? ''));
				$customer = ($data['code'] !== '') ? $this->customerModel->getCustomerById($data['code']) : false;

				if ($customer) {
					$_SESSION['shop_customer'] = $customer->CustomerID;
					header('Location: ' . urlRoot . '/shop/order');
					exit();
				}
				$data['error'] = 'No encontramos un cliente con ese código.';
			}

			$this->view('shop/index', $data);
		}

		// Paso 2: el cliente arma y confirma su pedido
		public function order() {
			$customer = isset($_SESSION['shop_customer'])
				? $this->customerModel->getCustomerById($_SESSION['shop_customer'])
				: false;
			if (!$customer) {
				header('Location: ' . urlRoot . '/shop');
				exit();
			}

			$data = [
				'mode'     => 'tienda',
				'title'    => 'Tienda en línea',
				'action'   => urlRoot . '/shop/order',
				'client'   => $customer,
				'shippers' => $this->orderModel->getShippers(),
				'products' => $this->orderModel->getProductsForSale(),
				'shipper'  => '',
				'lines'    => [],
				'errors'   => []
			];

			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				$data['shipper'] = $_POST['cboTransporte'] ?? '';
				$data['lines']   = postedLines();

				if ($data['shipper'] === '') {
					$data['errors'][] = 'Seleccione cómo desea recibir su pedido.';
				}

				list($items, $itemErrors) = readSaleItems($this->orderModel, false);
				$data['errors'] = array_merge($data['errors'], $itemErrors);

				$webEmployee = $this->orderModel->getWebEmployeeId();
				if (!$webEmployee) {
					$data['errors'][] = 'La tienda no está configurada: falta el empleado de ventas en línea.';
				}

				if (empty($data['errors'])) {
					$required = date('Y-m-d', strtotime('+7 days'));
					$order = orderFromCustomer($customer, $webEmployee, $data['shipper'], 0, $required);
					$orderId = $this->orderModel->createOrder($order, $items);

					if ($orderId) {
						$_SESSION['shop_last_order'] = $orderId;
						header('Location: ' . urlRoot . '/shop/thanks/' . $orderId);
						exit();
					}
					$data['errors'][] = 'No se pudo registrar su pedido: ' . $this->orderModel->error;
					$data['products'] = $this->orderModel->getProductsForSale();
				}
			}

			$this->view('sales/form', $data);
		}

		// Paso 3: confirmación (solo del pedido que el cliente acaba de hacer)
		public function thanks($id = null) {
			if (!$id || ($_SESSION['shop_last_order'] ?? null) != $id) {
				header('Location: ' . urlRoot . '/shop');
				exit();
			}
			$data = [
				'heading'  => '¡Gracias por su compra! Pedido #' . (int)$id,
				'order'    => $this->orderModel->getOrderById($id),
				'details'  => $this->orderModel->getOrderDetails($id),
				'backUrl'  => urlRoot . '/shop/order',
				'backText' => 'Hacer otro pedido'
			];
			$this->view('sales/detail', $data);
		}

		// El cliente sale de la tienda
		public function logout() {
			unset($_SESSION['shop_customer'], $_SESSION['shop_last_order']);
			header('Location: ' . urlRoot . '/shop');
			exit();
		}
	}
?>