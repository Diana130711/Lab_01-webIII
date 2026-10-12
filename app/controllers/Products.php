<?php
	class Products extends Controller {

		private $productModel;

		public function __construct() {
			if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== 'Si') {
				header('Location: ' . urlRoot . '/users/login');
				exit();
			}
			$this->productModel = $this->model('Product');
		}

		// Lista general (/products) o por categoría (/products/index/3)
		public function index($categoryId = null) {
			if ($categoryId !== null) {
				$category = $this->productModel->getCategoryName($categoryId);
				if (!$category) {
					header('Location: ' . urlRoot . '/products');
					exit();
				}
				$data = [
					'title'    => 'Lista de productos por la categoría ' . $category->CategoryName,
					'products' => $this->productModel->getProductsByCategory($categoryId),
					'filtered' => true
				];
			} else {
				$data = [
					'title'    => 'Lista general de productos',
					'products' => $this->productModel->getProducts(),
					'filtered' => false
				];
			}
			$this->view('products/index', $data);
		}

		// Agregar producto
		public function create() {
			$data = $this->emptyForm();

			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				$data = $this->readForm($data);
				if (empty($data['errors'])) {
					if ($this->productModel->addProduct($data)) {
						$_SESSION['msg'] = 'Producto agregado correctamente.';
						header('Location: ' . urlRoot . '/products');
						exit();
					}
					die('Error al guardar el producto.');
				}
			}

			$data['formTitle'] = 'Agregar producto';
			$data['action']    = urlRoot . '/products/create';
			$this->view('products/form', $data);
		}

		// Modificar producto
		public function edit($id = null) {
			$product = $id ? $this->productModel->getProductById($id) : false;
			if (!$product) {
				header('Location: ' . urlRoot . '/products');
				exit();
			}

			$data = $this->emptyForm();
			$data['id'] = $product->ProductID;

			if ($_SERVER['REQUEST_METHOD'] === 'POST') {
				$data = $this->readForm($data);
				if (empty($data['errors'])) {
					if ($this->productModel->updateProduct($data)) {
						$_SESSION['msg'] = 'Producto actualizado correctamente.';
						header('Location: ' . urlRoot . '/products');
						exit();
					}
					die('Error al actualizar el producto.');
				}
			} else {
				$data['name']         = $product->ProductName;
				$data['supplier']     = $product->SupplierID;
				$data['category']     = $product->CategoryID;
				$data['qty']          = $product->QuantityPerUnit;
				$data['price']        = $product->UnitPrice;
				$data['stock']        = $product->UnitsInStock;
				$data['onorder']      = $product->UnitsOnOrder;
				$data['reorder']      = $product->ReorderLevel;
				$data['discontinued'] = ($product->Discontinued != 0) ? 1 : 0;
			}

			$data['formTitle'] = 'Modificar producto #' . $data['id'];
			$data['action']    = urlRoot . '/products/edit/' . $data['id'];
			$this->view('products/form', $data);
		}

		// Borrar producto (los que tienen ventas no se pueden borrar)
		public function delete($id = null) {
			if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
				try {
					$this->productModel->deleteProduct($id);
					$_SESSION['msg'] = 'Producto eliminado correctamente.';
				} catch (PDOException $e) {
					$_SESSION['msg'] = 'No se puede borrar este producto porque tiene ventas registradas.';
				}
			}
			header('Location: ' . urlRoot . '/products');
			exit();
		}

		// Formulario vacío con las listas de categorías y proveedores
		private function emptyForm() {
			return [
				'id' => null, 'name' => '', 'supplier' => '', 'category' => '', 'qty' => '',
				'price' => '', 'stock' => '0', 'onorder' => '0', 'reorder' => '0',
				'discontinued' => 0, 'errors' => [],
				'categories' => $this->productModel->getCategories(),
				'suppliers'  => $this->productModel->getSuppliers()
			];
		}

		// Lee y valida los datos enviados
		private function readForm($data) {
			$data['name']         = trim($_POST['txtNombre'] ?? '');
			$data['category']     = $_POST['cboCategoria'] ?? '';
			$data['supplier']     = $_POST['cboProveedor'] ?? '';
			$data['qty']          = trim($_POST['txtPresentacion'] ?? '');
			$data['price']        = trim($_POST['txtPrecio'] ?? '');
			$data['stock']        = trim($_POST['txtExistencia'] ?? '');
			$data['onorder']      = trim($_POST['txtPedidas'] ?? '');
			$data['reorder']      = trim($_POST['txtReorden'] ?? '');
			$data['discontinued'] = isset($_POST['chkDescontinuado']) ? 1 : 0;
			$data['errors']       = [];

			if ($data['name'] === '') {
				$data['errors']['name'] = 'El nombre es obligatorio.';
			} elseif (strlen($data['name']) > 40) {
				$data['errors']['name'] = 'Máximo 40 caracteres.';
			}
			if ($data['category'] === '') {
				$data['errors']['category'] = 'Seleccione una categoría.';
			}
			if ($data['supplier'] === '') {
				$data['errors']['supplier'] = 'Seleccione un proveedor.';
			}
			if ($data['qty'] === '') {
				$data['errors']['qty'] = 'La presentación es obligatoria.';
			} elseif (strlen($data['qty']) > 20) {
				$data['errors']['qty'] = 'Máximo 20 caracteres.';
			}
			if (!is_numeric($data['price']) || $data['price'] < 0) {
				$data['errors']['price'] = 'Ingrese un precio válido.';
			}
			foreach (['stock', 'onorder', 'reorder'] as $campo) {
				$ok = filter_var($data[$campo], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 32767]]);
				if ($ok === false) {
					$data['errors'][$campo] = 'Número entero entre 0 y 32767.';
				}
			}
			return $data;
		}
	}
?>