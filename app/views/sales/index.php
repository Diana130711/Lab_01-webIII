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

	<main class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 min-h-[300px]">

		<div class="flex flex-wrap justify-between items-center gap-3 mb-6 pb-4 border-b border-slate-200">
			<div>
				<h1 class="text-2xl font-bold text-slate-800"><?php echo htmlspecialchars($data['title']); ?></h1>
				<p class="text-sm text-slate-500">Últimas <?php echo count($data['orders']); ?> ventas registradas</p>
			</div>
			<div class="flex gap-2">
				<a href="<?php echo urlRoot; ?>/shop" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium rounded-lg transition">Abrir tienda</a>
				<a href="<?php echo urlRoot; ?>/sales/create" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition shadow-md">+ Nueva venta asistida</a>
			</div>
		</div>

		<div class="overflow-x-auto rounded-lg border border-slate-200">
			<table class="min-w-full divide-y divide-slate-200 text-sm">
				<thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider">
					<tr>
						<th class="px-4 py-3 text-left">Orden</th>
						<th class="px-4 py-3 text-left">Fecha</th>
						<th class="px-4 py-3 text-left">Cliente</th>
						<th class="px-4 py-3 text-left">Atendido por</th>
						<th class="px-4 py-3 text-right">Total</th>
						<th class="px-4 py-3 text-center">Acciones</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-slate-200 bg-white">
					<?php foreach ($data['orders'] as $o): ?>
						<tr class="hover:bg-slate-50 transition-colors">
							<td class="px-4 py-3 font-mono text-indigo-600 font-semibold">#<?php echo $o->OrderID; ?></td>
							<td class="px-4 py-3 text-slate-600"><?php echo date('d/m/Y', strtotime($o->OrderDate)); ?></td>
							<td class="px-4 py-3 font-medium text-slate-800"><?php echo htmlspecialchars($o->CompanyName); ?></td>
							<td class="px-4 py-3 text-slate-600">
								<?php if ($o->Title === 'Venta en linea'): ?>
									<span class="px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-medium">Tienda en línea</span>
								<?php else: ?>
									<?php echo htmlspecialchars($o->EmployeeName); ?>
								<?php endif; ?>
							</td>
							<td class="px-4 py-3 text-right">$<?php echo number_format((float)$o->Total, 2); ?></td>
							<td class="px-4 py-3 text-center">
								<a href="<?php echo urlRoot; ?>/sales/detail/<?php echo $o->OrderID; ?>" class="inline-block px-3 py-1 bg-indigo-500 hover:bg-indigo-600 text-white rounded text-xs font-medium transition">Ver detalle</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</main>

	<?php require_once(appRoot . '/views/includes/pie.php'); ?>
</div>
</body>
</html>