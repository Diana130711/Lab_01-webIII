<?php
	// Líneas tal como llegaron del formulario (para volver a mostrarlas si hay errores)
	function postedLines() {
		$ids   = $_POST['productId'] ?? [];
		$qtys  = $_POST['quantity'] ?? [];
		$discs = $_POST['discount'] ?? [];
		$lines = [];
		foreach ($ids as $i => $id) {
			$lines[] = [
				'productId' => (int)$id,
				'quantity'  => (int)($qtys[$i] ?? 0),
				'discount'  => (int)($discs[$i] ?? 0)
			];
		}
		return $lines;
	}

	// Lee y valida las líneas del pedido contra la base de datos.
	// El precio siempre se toma de la base, nunca del formulario.
	// Devuelve [líneas válidas, errores].
	function readSaleItems($orderModel, $allowDiscount) {
		$merged = [];
		$errors = [];

		foreach (postedLines() as $line) {
			if ($line['productId'] <= 0 || $line['quantity'] <= 0) {
				continue;
			}
			$disc = $allowDiscount ? $line['discount'] / 100 : 0;
			if ($disc < 0 || $disc > 0.25) {
				$errors[] = 'El descuento debe estar entre 0% y 25%.';
				continue;
			}
			// Si el mismo producto viene dos veces, se suman las cantidades
			$id = $line['productId'];
			if (isset($merged[$id])) {
				$merged[$id]['quantity'] += $line['quantity'];
			} else {
				$merged[$id] = ['productId' => $id, 'quantity' => $line['quantity'], 'discount' => $disc];
			}
		}

		$items = [];
		foreach ($merged as $id => $line) {
			$p = $orderModel->getProductForSale($id);
			if (!$p) {
				$errors[] = 'El producto #' . $id . ' no está disponible.';
				continue;
			}
			if ($line['quantity'] > $p->UnitsInStock) {
				$errors[] = 'Solo hay ' . $p->UnitsInStock . ' unidades de "' . $p->ProductName . '".';
				continue;
			}
			$line['name']  = $p->ProductName;
			$line['price'] = $p->UnitPrice;
			$items[] = $line;
		}

		if (empty($items) && empty($errors)) {
			$errors[] = 'Agregue al menos un producto al pedido.';
		}
		return [$items, $errors];
	}

	// Arma el encabezado de la orden con los datos de envío del cliente
	function orderFromCustomer($customer, $employeeId, $shipperId, $freight, $requiredDate) {
		return [
			'customer'    => $customer->CustomerID,
			'employee'    => (int)$employeeId,
			'shipper'     => (int)$shipperId,
			'freight'     => $freight,
			'required'    => $requiredDate . ' 00:00:00',
			'shipName'    => $customer->CompanyName,
			'shipAddress' => $customer->Address,
			'shipCity'    => $customer->City,
			'shipRegion'  => $customer->Region,
			'shipPostal'  => $customer->PostalCode,
			'shipCountry' => $customer->Country
		];
	}
?>