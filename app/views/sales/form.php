<!DOCTYPE html>
<html lang="es">
<head>
	<?php require_once(appRoot . '/views/includes/enca.php'); ?>
	<title><?php echo siteName; ?></title>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 flex flex-col justify-between">
<div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6">
	<header>
		<?php require_once(appRoot . '/views/includes/menu.php'); ?>
	</header>

	<?php
		$asistida = ($data['mode'] === 'asistida');
		$input = 'w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm';
		$label = 'block text-sm font-semibold text-slate-700 mb-1';
	?>

	<main class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 min-h-[300px]">

		<div class="mb-6 pb-4 border-b border-slate-200">
			<h1 class="text-2xl font-bold text-slate-800"><?php echo htmlspecialchars($data['title']); ?></h1>
			<p class="text-sm text-slate-500">
				<?php echo $asistida
					? 'Registro de una venta atendida por uno de nuestros empleados.'
					: 'Arme su pedido y confírmelo. El envío no tiene costo y la entrega estimada es de 7 días.'; ?>
			</p>
		</div>

		<?php if (!empty($data['errors'])): ?>
			<div class="mb-6 px-4 py-3 rounded-lg bg-rose-50 text-rose-700 text-sm border border-rose-100">
				<ul class="list-disc pl-5 space-y-1">
					<?php foreach ($data['errors'] as $e): ?>
						<li><?php echo htmlspecialchars($e); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<form action="<?php echo $data['action']; ?>" method="POST" class="space-y-6">

			<!-- Encabezado de la venta -->
			<?php if ($asistida): ?>
				<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
					<div class="md:col-span-2">
						<label class="<?php echo $label; ?>">Cliente</label>
						<select name="cboCliente" class="<?php echo $input; ?>" required>
							<option value="">-- Seleccione --</option>
							<?php foreach ($data['customers'] as $c): ?>
								<option value="<?php echo $c->CustomerID; ?>" <?php echo ($data['customer'] === $c->CustomerID) ? 'selected' : ''; ?>>
									<?php echo htmlspecialchars($c->CompanyName . ' (' . $c->CustomerID . ') - ' . $c->City . ', ' . $c->Country); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div>
						<label class="<?php echo $label; ?>">Atendido por</label>
						<select name="cboEmpleado" class="<?php echo $input; ?>" required>
							<option value="">-- Seleccione --</option>
							<?php foreach ($data['employees'] as $e): ?>
								<option value="<?php echo $e->EmployeeID; ?>" <?php echo ($data['employee'] == $e->EmployeeID) ? 'selected' : ''; ?>>
									<?php echo htmlspecialchars($e->FirstName . ' ' . $e->LastName . ' - ' . $e->Title); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div>
						<label class="<?php echo $label; ?>">Transportista</label>
						<select name="cboTransporte" class="<?php echo $input; ?>" required>
							<option value="">-- Seleccione --</option>
							<?php foreach ($data['shippers'] as $s): ?>
								<option value="<?php echo $s->ShipperID; ?>" <?php echo ($data['shipper'] == $s->ShipperID) ? 'selected' : ''; ?>>
									<?php echo htmlspecialchars($s->CompanyName); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div>
						<label class="<?php echo $label; ?>">Fecha requerida</label>
						<input type="date" name="txtFechaReq" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($data['required']); ?>" class="<?php echo $input; ?>" required>
					</div>
					<div>
						<label class="<?php echo $label; ?>">Flete ($)</label>
						<input type="number" name="txtFlete" step="0.01" min="0" value="<?php echo htmlspecialchars($data['freight']); ?>" class="<?php echo $input; ?>" required>
					</div>
				</div>
			<?php else: ?>
				<?php $cl = $data['client']; ?>
				<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
					<div class="md:col-span-2 p-4 rounded-lg bg-slate-50 border border-slate-200 text-sm">
						<p class="font-semibold text-slate-800"><?php echo htmlspecialchars($cl->CompanyName); ?> (<?php echo $cl->CustomerID; ?>)</p>
						<p class="text-slate-600">Contacto: <?php echo htmlspecialchars($cl->ContactName); ?></p>
						<p class="text-slate-600">Envío a: <?php echo htmlspecialchars($cl->Address . ', ' . $cl->City . ', ' . $cl->Country); ?></p>
						<a href="<?php echo urlRoot; ?>/shop/logout" class="inline-block mt-2 text-indigo-600 hover:underline text-xs">No soy este cliente</a>
					</div>
					<div>
						<label class="<?php echo $label; ?>">¿Cómo desea recibirlo?</label>
						<select name="cboTransporte" class="<?php echo $input; ?>" required>
							<option value="">-- Seleccione --</option>
							<?php foreach ($data['shippers'] as $s): ?>
								<option value="<?php echo $s->ShipperID; ?>" <?php echo ($data['shipper'] == $s->ShipperID) ? 'selected' : ''; ?>>
									<?php echo htmlspecialchars($s->CompanyName); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			<?php endif; ?>

			<!-- Líneas del pedido -->
			<div>
				<div class="flex justify-between items-center mb-2">
					<h2 class="text-lg font-bold text-slate-800">Productos</h2>
					<button type="button" id="btnAgregar" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-sm font-medium transition">+ Agregar producto</button>
				</div>
				<div class="overflow-x-auto rounded-lg border border-slate-200">
					<table class="min-w-full divide-y divide-slate-200 text-sm">
						<thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider text-xs">
							<tr>
								<th class="px-3 py-2 text-left">Producto</th>
								<th class="px-3 py-2 text-left">Cantidad</th>
								<?php if ($asistida): ?><th class="px-3 py-2 text-left">Desc. %</th><?php endif; ?>
								<th class="px-3 py-2 text-right">Subtotal</th>
								<th class="px-3 py-2"></th>
							</tr>
						</thead>
						<tbody id="lineas" class="divide-y divide-slate-200 bg-white"></tbody>
						<tfoot class="bg-slate-50">
							<tr>
								<td colspan="<?php echo $asistida ? 3 : 2; ?>" class="px-3 py-3 text-right font-semibold">Total productos</td>
								<td id="total" class="px-3 py-3 text-right font-bold text-slate-800">$0.00</td>
								<td></td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>

			<div class="flex justify-end gap-3 pt-4 border-t">
				<a href="<?php echo $asistida ? urlRoot . '/sales' : urlRoot . '/shop/logout'; ?>" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg font-medium hover:bg-slate-300 transition">Cancelar</a>
				<button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">
					<?php echo $asistida ? 'Registrar venta' : 'Confirmar pedido'; ?>
				</button>
			</div>
		</form>
	</main>

	<!-- Plantilla de una línea del pedido -->
	<template id="tplLinea">
		<tr class="linea">
			<td class="px-3 py-2 min-w-[260px]">
				<select name="productId[]" class="sel-prod <?php echo $input; ?>" required>
					<option value="">-- Seleccione --</option>
					<?php foreach ($data['products'] as $p): ?>
						<option value="<?php echo $p->ProductID; ?>" data-price="<?php echo (float)$p->UnitPrice; ?>" data-stock="<?php echo (int)$p->UnitsInStock; ?>">
							<?php echo htmlspecialchars($p->ProductName . ' (' . $p->CategoryName . ') - $' . number_format($p->UnitPrice, 2) . ' - disp. ' . $p->UnitsInStock); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</td>
			<td class="px-3 py-2 w-28">
				<input type="number" name="quantity[]" min="1" value="1" class="inp-qty <?php echo $input; ?>" required>
			</td>
			<?php if ($asistida): ?>
				<td class="px-3 py-2 w-28">
					<input type="number" name="discount[]" min="0" max="25" step="1" value="0" class="inp-disc <?php echo $input; ?>">
				</td>
			<?php endif; ?>
			<td class="px-3 py-2 text-right sub whitespace-nowrap">$0.00</td>
			<td class="px-3 py-2 text-center">
				<button type="button" class="btn-del px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded text-xs font-medium">Quitar</button>
			</td>
		</tr>
	</template>

	<script>
		const tbody = document.getElementById('lineas');
		const tpl = document.getElementById('tplLinea');

		function money(n) { return '$' + n.toFixed(2); }

		// Recalcula subtotales y total
		function recalc() {
			let total = 0;
			tbody.querySelectorAll('tr.linea').forEach(function (tr) {
				const opt = tr.querySelector('.sel-prod').selectedOptions[0];
				const price = (opt && opt.dataset.price) ? parseFloat(opt.dataset.price) : 0;
				const stock = (opt && opt.dataset.stock) ? parseInt(opt.dataset.stock) : 0;
				const qtyInput = tr.querySelector('.inp-qty');
				if (stock) { qtyInput.max = stock; }
				const qty = parseInt(qtyInput.value) || 0;
				const discInput = tr.querySelector('.inp-disc');
				const disc = discInput ? (parseInt(discInput.value) || 0) / 100 : 0;
				const sub = price * qty * (1 - disc);
				tr.querySelector('.sub').textContent = money(sub);
				total += sub;
			});
			document.getElementById('total').textContent = money(total);
		}

		// Agrega una línea (vacía o con datos previos)
		function addLine(line) {
			const row = tpl.content.firstElementChild.cloneNode(true);
			if (line) {
				row.querySelector('.sel-prod').value = line.productId || '';
				row.querySelector('.inp-qty').value = line.quantity || 1;
				const d = row.querySelector('.inp-disc');
				if (d) { d.value = line.discount || 0; }
			}
			tbody.appendChild(row);
			recalc();
		}

		tbody.addEventListener('input', recalc);
		tbody.addEventListener('change', recalc);
		tbody.addEventListener('click', function (e) {
			if (e.target.classList.contains('btn-del')) {
				e.target.closest('tr').remove();
				recalc();
			}
		});
		document.getElementById('btnAgregar').addEventListener('click', function () { addLine(); });

		// Si hubo errores, se vuelven a mostrar las líneas que la persona había puesto
		const iniciales = <?php echo json_encode($data['lines'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
		if (iniciales.length) { iniciales.forEach(addLine); } else { addLine(); }
	</script>

	<?php require_once(appRoot . '/views/includes/pie.php'); ?>
</div>
</body>
</html>