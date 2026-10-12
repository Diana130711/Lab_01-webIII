<?php
	class Sales extends Controller {

		private $orderModel;
		private $customerModel;

		public function __construct() {
			// Solo empleados con sesión iniciada
			if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== 'Si') {
				header('Location: ' . urlRoot . '/users/login');
				exit();
			}
			$this->orderModel    = $this->model('Order');
			$this->customerModel = $this->model('Customer');
		}

		// Historial de ventas
		public function index() {
			$data = [
				'title'  => 'Historial de ventas',
				'orders' => $this->orderModel->getOrders(50)
			];
			$this->view('sales/index', $data);
		}

		// Venta asistida por un empleado
		public function create() {
			$data = [
				'mode'      => 'asistida',
				'title'     => 'Venta asistida por empleado',
				'action'    => urlRoot . '/sales/create',
				'customers' => $this->customerModel->getCustomers(),
				'employees' => $this->orderModel->getEmployees(),
				'shippers'  => $this->orderModel->getShippers(),
				'products'  => $this->orderModel->getProductsForSale(),
				'customer'  => '',
				'employee'  => '',
				'shipper'   => '',
				'freight'   => '0',
				'required'  => date('Y-m-d', strtotime('+7 days')),
				'lines'     => [],
				'errors'    => []
			];

			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				$data['customer'] = trim($_POST['cboCliente'] ?? '');
				$data['employee'] = $_POST['cboEmpleado'] ?? '';
				$data['shipper']  = $_POST['cboTransporte'] ?? '';
				$data['freight']  = trim($_POST['txtFlete'] ?? '0');
				$data['required'] = $_POST['txtFechaReq'] ?? '';
				$data['lines']    = postedLines();

				$customer = ($data['customer'] !== '') ? $this->customerModel->getCustomerById($data['customer']) : false;
				if (!$customer) {
					$data['errors'][] = 'Seleccione un cliente.';
				}
				if ($data['employee'] === '') {
					$data['errors'][] = 'Seleccione el empleado que atiende la venta.';
				}
				if ($data['shipper'] === '') {
					$data['errors'][] = 'Seleccione la transportista.';
				}
				if (!is_numeric($data['freight']) || $data['freight'] < 0) {
					$data['errors'][] = 'El flete debe ser un número mayor o igual a 0.';
				}
				$fecha = DateTime::createFromFormat('Y-m-d', $data['required']);
				if (!$fecha || $data['required'] < date('Y-m-d')) {
					$data['errors'][] = 'La fecha requerida no puede ser anterior a hoy.';
				}

				list($items, $itemErrors) = readSaleItems($this->orderModel, true);
				$data['errors'] = array_merge($data['errors'], $itemErrors);

				if (empty($data['errors'])) {
					$order = orderFromCustomer($customer, $data['employee'], $data['shipper'], $data['freight'], $data['required']);
					$orderId = $this->orderModel->createOrder($order, $items);

					if ($orderId) {
						$_SESSION['msg'] = 'Venta #' . $orderId . ' registrada correctamente.';
						header('Location: ' . urlRoot . '/sales/detail/' . $orderId);
						exit();
					}
					$data['errors'][] = 'No se pudo registrar la venta: ' . $this->orderModel->error;
					// Recargar existencias por si cambiaron
					$data['products'] = $this->orderModel->getProductsForSale();
				}
			}

			$this->view('sales/form', $data);
		}

		// Detalle de una venta
		public function detail($id = null) {
			$order = $id ? $this->orderModel->getOrderById($id) : false;
			if (!$order) {
				header('Location: ' . urlRoot . '/sales');
				exit();
			}
			$data = [
				'heading'  => 'Detalle de la venta #' . $order->OrderID,
				'order'    => $order,
				'details'  => $this->orderModel->getOrderDetails($id),
				'backUrl'  => urlRoot . '/sales',
				'backText' => 'Volver al historial'
			];
			$this->view('sales/detail', $data);
		}
	}
?>