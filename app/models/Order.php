<?php
	class Order {
		private $db;
		public $error = '';

		// Puesto del empleado especial para ventas autogestionadas
		const WEB_TITLE = 'Venta en linea';

		public function __construct() {
			$this->db = new Database();
		}

		// ---------- Consultas de apoyo ----------

		public function getEmployees() {
			$this->db->query('SELECT EmployeeID, FirstName, LastName, Title
			                  FROM employees WHERE Title IS NULL OR Title <> :web
			                  ORDER BY FirstName, LastName');
			$this->db->bind(':web', self::WEB_TITLE);
			return $this->db->resultSet();
		}

		public function getWebEmployeeId() {
			$this->db->query('SELECT EmployeeID FROM employees WHERE Title = :web LIMIT 1');
			$this->db->bind(':web', self::WEB_TITLE);
			$row = $this->db->singleRow();
			return $row ? (int)$row->EmployeeID : null;
		}

		public function getShippers() {
			$this->db->query('SELECT ShipperID, CompanyName FROM shippers ORDER BY CompanyName');
			return $this->db->resultSet();
		}

		// Productos que se pueden vender: no descontinuados y con existencia
		public function getProductsForSale() {
			$this->db->query('SELECT p.ProductID, p.ProductName, p.UnitPrice, p.UnitsInStock, c.CategoryName
			                  FROM products p
			                  INNER JOIN categories c ON c.CategoryID = p.CategoryID
			                  WHERE p.Discontinued = 0 AND p.UnitsInStock > 0
			                  ORDER BY p.ProductName');
			return $this->db->resultSet();
		}

		public function getProductForSale($id) {
			$this->db->query('SELECT ProductID, ProductName, UnitPrice, UnitsInStock
			                  FROM products
			                  WHERE ProductID = :id AND Discontinued = 0');
			$this->db->bind(':id', (int)$id);
			return $this->db->singleRow();
		}

		// ---------- Historial ----------

		// Últimas ventas con cliente, empleado y total
		public function getOrders($limit = 50) {
			$this->db->query('SELECT o.OrderID, o.OrderDate, c.CompanyName,
			                         CONCAT(e.FirstName, " ", e.LastName) AS EmployeeName, e.Title,
			                         SUM(d.UnitPrice * d.Quantity * (1 - d.Discount)) AS Total
			                  FROM orders o
			                  INNER JOIN customers c ON c.CustomerID = o.CustomerID
			                  INNER JOIN employees e ON e.EmployeeID = o.EmployeeID
			                  LEFT JOIN order_details d ON d.OrderID = o.OrderID
			                  GROUP BY o.OrderID, o.OrderDate, c.CompanyName, e.FirstName, e.LastName, e.Title
			                  ORDER BY o.OrderID DESC
			                  LIMIT ' . (int)$limit);
			return $this->db->resultSet();
		}

		// Encabezado de una venta con los nombres de cliente, empleado y transportista
		public function getOrderById($id) {
			$this->db->query('SELECT o.*, c.CompanyName AS CustomerName, c.ContactName,
			                         CONCAT(e.FirstName, " ", e.LastName) AS EmployeeName, e.Title AS EmployeeTitle,
			                         s.CompanyName AS ShipperName
			                  FROM orders o
			                  INNER JOIN customers c ON c.CustomerID = o.CustomerID
			                  INNER JOIN employees e ON e.EmployeeID = o.EmployeeID
			                  INNER JOIN shippers s ON s.ShipperID = o.ShipVia
			                  WHERE o.OrderID = :id');
			$this->db->bind(':id', (int)$id);
			return $this->db->singleRow();
		}

		// Líneas de una venta con producto y categoría
		public function getOrderDetails($id) {
			$this->db->query('SELECT d.ProductID, p.ProductName, c.CategoryName,
			                         d.UnitPrice, d.Quantity, d.Discount,
			                         (d.UnitPrice * d.Quantity * (1 - d.Discount)) AS Subtotal
			                  FROM order_details d
			                  INNER JOIN products p ON p.ProductID = d.ProductID
			                  INNER JOIN categories c ON c.CategoryID = p.CategoryID
			                  WHERE d.OrderID = :id
			                  ORDER BY p.ProductName');
			$this->db->bind(':id', (int)$id);
			return $this->db->resultSet();
		}

		// ---------- Registrar la venta ----------

		// Guarda encabezado, líneas y descuenta existencias. Todo o nada.
		// Devuelve el número de orden, o false (con el motivo en $this->error).
		public function createOrder($order, $items) {
			try {
				$this->db->beginTransaction();

				$this->db->query('INSERT INTO orders (CustomerID, EmployeeID, OrderDate, RequiredDate, ShippedDate,
				                                      ShipVia, Freight, ShipName, ShipAddress, ShipCity,
				                                      ShipRegion, ShipPostalCode, ShipCountry)
				                  VALUES (:cust, :emp, NOW(), :req, "0000-00-00 00:00:00",
				                          :via, :freight, :name, :addr, :city,
				                          :region, :postal, :country)');
				$this->db->bind(':cust', $order['customer']);
				$this->db->bind(':emp', (int)$order['employee']);
				$this->db->bind(':req', $order['required']);
				$this->db->bind(':via', (int)$order['shipper']);
				$this->db->bind(':freight', (string)$order['freight']);
				$this->db->bind(':name', $order['shipName']);
				$this->db->bind(':addr', $order['shipAddress']);
				$this->db->bind(':city', $order['shipCity']);
				$this->db->bind(':region', $order['shipRegion']);
				$this->db->bind(':postal', $order['shipPostal']);
				$this->db->bind(':country', $order['shipCountry']);
				$this->db->execute();

				$orderId = $this->db->lastInsertId();

				foreach ($items as $it) {
					// Descontar existencia solo si alcanza
					$this->db->query('UPDATE products
					                  SET UnitsInStock = UnitsInStock - :qty
					                  WHERE ProductID = :id AND UnitsInStock >= :qty2 AND Discontinued = 0');
					$this->db->bind(':qty', (int)$it['quantity']);
					$this->db->bind(':qty2', (int)$it['quantity']);
					$this->db->bind(':id', (int)$it['productId']);
					$this->db->execute();

					if ($this->db->affectedRows() !== 1) {
						throw new Exception('No hay existencia suficiente de "' . $it['name'] . '".');
					}

					$this->db->query('INSERT INTO order_details (OrderID, ProductID, UnitPrice, Quantity, Discount)
					                  VALUES (:o, :p, :price, :q, :d)');
					$this->db->bind(':o', $orderId);
					$this->db->bind(':p', (int)$it['productId']);
					$this->db->bind(':price', (string)$it['price']);
					$this->db->bind(':q', (int)$it['quantity']);
					$this->db->bind(':d', (string)$it['discount']);
					$this->db->execute();
				}

				$this->db->commit();
				return $orderId;

			} catch (Exception $e) {
				if ($this->db->inTransaction()) {
					$this->db->rollBack();
				}
				$this->error = $e->getMessage();
				return false;
			}
		}
	}
?>