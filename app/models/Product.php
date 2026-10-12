<?php
	class Product {
		private $db;

		public function __construct() {
			$this->db = new Database();
		}

		// Lista general en orden alfabético, con nombre de categoría y proveedor
		public function getProducts() {
			$this->db->query('SELECT p.ProductID, p.ProductName, p.UnitsInStock, p.UnitPrice,
			                         c.CategoryName, s.CompanyName
			                  FROM products p
			                  INNER JOIN categories c ON c.CategoryID = p.CategoryID
			                  INNER JOIN suppliers s ON s.SupplierID = p.SupplierID
			                  ORDER BY p.ProductName ASC');
			return $this->db->resultSet();
		}

		// Lista filtrada por categoría
		public function getProductsByCategory($categoryId) {
			$this->db->query('SELECT p.ProductID, p.ProductName, p.UnitsInStock, p.UnitPrice,
			                         c.CategoryName, s.CompanyName
			                  FROM products p
			                  INNER JOIN categories c ON c.CategoryID = p.CategoryID
			                  INNER JOIN suppliers s ON s.SupplierID = p.SupplierID
			                  WHERE p.CategoryID = :cat
			                  ORDER BY p.ProductName ASC');
			$this->db->bind(':cat', (int)$categoryId);
			return $this->db->resultSet();
		}

		// Un producto completo
		public function getProductById($id) {
			$this->db->query('SELECT * FROM products WHERE ProductID = :id');
			$this->db->bind(':id', (int)$id);
			return $this->db->singleRow();
		}

		// Nombre de una categoría (para el título de la lista filtrada)
		public function getCategoryName($categoryId) {
			$this->db->query('SELECT CategoryName FROM categories WHERE CategoryID = :id');
			$this->db->bind(':id', (int)$categoryId);
			return $this->db->singleRow();
		}

		// Listas para los combos del formulario
		public function getCategories() {
			$this->db->query('SELECT CategoryID, CategoryName FROM categories ORDER BY CategoryName');
			return $this->db->resultSet();
		}

		public function getSuppliers() {
			$this->db->query('SELECT SupplierID, CompanyName FROM suppliers ORDER BY CompanyName');
			return $this->db->resultSet();
		}

		// Insertar
		public function addProduct($data) {
			$this->db->query('INSERT INTO products (ProductName, SupplierID, CategoryID, QuantityPerUnit,
			                                        UnitPrice, UnitsInStock, UnitsOnOrder, ReorderLevel, Discontinued)
			                  VALUES (:name, :sup, :cat, :qty, :price, :stock, :onorder, :reorder, :disc)');
			$this->bindFields($data);
			return $this->db->execute();
		}

		// Modificar
		public function updateProduct($data) {
			$this->db->query('UPDATE products SET ProductName = :name, SupplierID = :sup, CategoryID = :cat,
			                         QuantityPerUnit = :qty, UnitPrice = :price, UnitsInStock = :stock,
			                         UnitsOnOrder = :onorder, ReorderLevel = :reorder, Discontinued = :disc
			                  WHERE ProductID = :id');
			$this->bindFields($data);
			$this->db->bind(':id', (int)$data['id']);
			return $this->db->execute();
		}

		// Borrar
		public function deleteProduct($id) {
			$this->db->query('DELETE FROM products WHERE ProductID = :id');
			$this->db->bind(':id', (int)$id);
			return $this->db->execute();
		}

		// Enlaza los campos comunes de insertar y modificar
		private function bindFields($data) {
			$this->db->bind(':name', $data['name']);
			$this->db->bind(':sup', (int)$data['supplier']);
			$this->db->bind(':cat', (int)$data['category']);
			$this->db->bind(':qty', $data['qty']);
			$this->db->bind(':price', $data['price']);
			$this->db->bind(':stock', (int)$data['stock']);
			$this->db->bind(':onorder', (int)$data['onorder']);
			$this->db->bind(':reorder', (int)$data['reorder']);
			$this->db->bind(':disc', (int)$data['discontinued']);
		}
	}
?>