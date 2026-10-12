<?php
	class Customer {
		private $db;

		public function __construct() {
			$this->db = new Database();
		}

		// Lista de clientes para el combo de la venta asistida
		public function getCustomers() {
			$this->db->query('SELECT CustomerID, CompanyName, ContactName, City, Country
			                  FROM customers ORDER BY CompanyName');
			return $this->db->resultSet();
		}

		// Un cliente por su código (ALFKI, ANATR, ...)
		public function getCustomerById($id) {
			$this->db->query('SELECT * FROM customers WHERE CustomerID = :id');
			$this->db->bind(':id', strtoupper(trim($id)));
			return $this->db->singleRow();
		}
	}
?>