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

		<?php if (!empty($_SESSION['msg'])): ?>
			<div class="mb-4 px-4 py-3 rounded-lg bg-indigo-50 text-indigo-800 text-sm border border-indigo-100">
				<?php echo htmlspecialchars($_SESSION['msg']); unset($_SESSION['msg']); ?>
			</div>
		<?php endif; ?>

		<div class="flex flex-wrap justify-between items-center gap-3 mb-6 pb-4 border-b border-slate-200">
			<div>
				<h1 class="text-2xl font-bold text-slate-800"><?php echo htmlspecialchars($data['title']); ?></h1>
				<p class="text-sm text-slate-500"><?php echo count($data['products']); ?> productos encontrados</p>
			</div>
			<div class="flex gap-2">
				<?php if ($data['filtered']): ?>
					<a href="<?php echo urlRoot; ?>/products" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium rounded-lg transition">Ver todos</a>
				<?php endif; ?>
				<a href="<?php echo urlRoot; ?>/products/create" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition shadow-md">+ Agregar producto</a>
			</div>
		</div>

		<div class="overflow-x-auto rounded-lg border border-slate-200">
			<table class="min-w-full divide-y divide-slate-200 text-sm">
				<thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider">
					<tr>
						<th class="px-4 py-3 text-left">Código</th>
						<th class="px-4 py-3 text-left">Producto</th>
						<th class="px-4 py-3 text-left">Categoría</th>
						<th class="px-4 py-3 text-left">Proveedor</th>
						<th class="px-4 py-3 text-right">Existencia</th>
						<th class="px-4 py-3 text-right">Precio</th>
						<th class="px-4 py-3 text-center">Acciones</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-slate-200 bg-white">
					<?php if (empty($data['products'])): ?>
						<tr><td colspan="7" class="px-4 py-6 text-center text-slate-500">No hay productos para mostrar.</td></tr>
					<?php endif; ?>
					<?php foreach ($data['products'] as $p): ?>
						<tr class="hover:bg-slate-50 transition-colors">
							<td class="px-4 py-3 font-mono text-indigo-600 font-semibold">#<?php echo $p->ProductID; ?></td>
							<td class="px-4 py-3 font-medium text-slate-800"><?php echo htmlspecialchars($p->ProductName); ?></td>
							<td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($p->CategoryName); ?></td>
							<td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($p->CompanyName); ?></td>
							<td class="px-4 py-3 text-right"><?php echo $p->UnitsInStock; ?></td>
							<td class="px-4 py-3 text-right">$<?php echo number_format($p->UnitPrice, 2); ?></td>
							<td class="px-4 py-3 text-center whitespace-nowrap space-x-2">
								<a href="<?php echo urlRoot; ?>/products/edit/<?php echo $p->ProductID; ?>" class="inline-block px-3 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded text-xs font-medium transition">Editar</a>
								<form action="<?php echo urlRoot; ?>/products/delete/<?php echo $p->ProductID; ?>" method="POST" class="inline-block" onsubmit="return confirm('¿Está seguro de eliminar este producto?');">
									<button type="submit" class="px-3 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded text-xs font-medium transition">Borrar</button>
								</form>
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