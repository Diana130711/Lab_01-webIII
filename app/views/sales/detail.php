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
		$o = $data['order'];
		$totalProductos = 0;
		foreach ($data['details'] as $d) { $totalProductos += $d->Subtotal; }
		$enviado = (substr($o->ShippedDate, 0, 4) !== '0000');
	?>

	<main class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 min-h-[300px]">

		<?php if (!empty($_SESSION['msg'])): ?>
			<div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-800 text-sm border border-emerald-100">
				<?php echo htmlspecialchars($_SESSION['msg']); unset($_SESSION['msg']); ?>
			</div>
		<?php endif; ?>

		<div class="flex flex-wrap justify-between items-center gap-3 mb-6 pb-4 border-b border-slate-200">
			<h1 class="text-2xl font-bold text-slate-800"><?php echo htmlspecialchars($data['heading']); ?></h1>
			<a href="<?php echo $data['backUrl']; ?>" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium rounded-lg transition"><?php echo htmlspecialchars($data['backText']); ?></a>
		</div>

		<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 text-sm">
			<div class="p-4 rounded-lg bg-slate-50 border border-slate-200">
				<p class="text-xs uppercase text-slate-500 font-semibold mb-1">Cliente</p>
				<p class="font-semibold text-slate-800"><?php echo htmlspecialchars($o->CustomerName); ?> (<?php echo $o->CustomerID; ?>)</p>
				<p class="text-slate-600"><?php echo htmlspecialchars($o->ContactName); ?></p>
			</div>
			<div class="p-4 rounded-lg bg-slate-50 border border-slate-200">
				<p class="text-xs uppercase text-slate-500 font-semibold mb-1">Atendido por</p>
				<p class="font-semibold text-slate-800">
					<?php echo ($o->EmployeeTitle === 'Venta en linea') ? 'Tienda en línea (autogestión)' : htmlspecialchars($o->EmployeeName); ?>
				</p>
				<p class="text-slate-600"><?php echo htmlspecialchars((string)$o->EmployeeTitle); ?></p>
			</div>
			<div class="p-4 rounded-lg bg-slate-50 border border-slate-200">
				<p class="text-xs uppercase text-slate-500 font-semibold mb-1">Fechas</p>
				<p class="text-slate-700">Pedido: <?php echo date('d/m/Y H:i', strtotime($o->OrderDate)); ?></p>
				<p class="text-slate-700">Requerido: <?php echo date('d/m/Y', strtotime($o->RequiredDate)); ?></p>
				<p class="text-slate-700">Envío: <?php echo $enviado ? date('d/m/Y', strtotime($o->ShippedDate)) : 'Pendiente'; ?></p>
			</div>
			<div class="md:col-span-3 p-4 rounded-lg bg-slate-50 border border-slate-200">
				<p class="text-xs uppercase text-slate-500 font-semibold mb-1">Envío</p>
				<p class="text-slate-700">
					<?php echo htmlspecialchars($o->ShipName . ' - ' . $o->ShipAddress . ', ' . $o->ShipCity . ', ' . $o->ShipCountry); ?>
				</p>
				<p class="text-slate-700">Transportista: <?php echo htmlspecialchars($o->ShipperName); ?></p>
			</div>
		</div>

		<div class="overflow-x-auto rounded-lg border border-slate-200">
			<table class="min-w-full divide-y divide-slate-200 text-sm">
				<thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider text-xs">
					<tr>
						<th class="px-4 py-3 text-left">Producto</th>
						<th class="px-4 py-3 text-left">Categoría</th>
						<th class="px-4 py-3 text-right">Precio</th>
						<th class="px-4 py-3 text-right">Cantidad</th>
						<th class="px-4 py-3 text-right">Descuento</th>
						<th class="px-4 py-3 text-right">Subtotal</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-slate-200 bg-white">
					<?php foreach ($data['details'] as $d): ?>
						<tr>
							<td class="px-4 py-3 font-medium text-slate-800"><?php echo htmlspecialchars($d->ProductName); ?></td>
							<td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($d->CategoryName); ?></td>
							<td class="px-4 py-3 text-right">$<?php echo number_format($d->UnitPrice, 2); ?></td>
							<td class="px-4 py-3 text-right"><?php echo $d->Quantity; ?></td>
							<td class="px-4 py-3 text-right"><?php echo round($d->Discount * 100); ?>%</td>
							<td class="px-4 py-3 text-right">$<?php echo number_format($d->Subtotal, 2); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
				<tfoot class="bg-slate-50 text-sm">
					<tr>
						<td colspan="5" class="px-4 py-2 text-right">Productos</td>
						<td class="px-4 py-2 text-right">$<?php echo number_format($totalProductos, 2); ?></td>
					</tr>
					<tr>
						<td colspan="5" class="px-4 py-2 text-right">Flete</td>
						<td class="px-4 py-2 text-right">$<?php echo number_format($o->Freight, 2); ?></td>
					</tr>
					<tr>
						<td colspan="5" class="px-4 py-3 text-right font-bold">Total</td>
						<td class="px-4 py-3 text-right font-bold text-slate-800">$<?php echo number_format($totalProductos + $o->Freight, 2); ?></td>
					</tr>
				</tfoot>
			</table>
		</div>
	</main>

	<?php require_once(appRoot . '/views/includes/pie.php'); ?>
</div>
</body>
</html>