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
		<div class="max-w-3xl mx-auto my-6 p-8 bg-white rounded-xl shadow-lg border border-slate-100">
			<h2 class="text-xl font-bold text-slate-800 mb-6 pb-2 border-b"><?php echo htmlspecialchars($data['formTitle']); ?></h2>

			<?php
				$err = $data['errors'];
				$input = 'w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none';
				$label = 'block text-sm font-semibold text-slate-700 mb-1';
			?>

			<form action="<?php echo $data['action']; ?>" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">

				<div class="md:col-span-2">
					<label class="<?php echo $label; ?>">Nombre del producto</label>
					<input type="text" name="txtNombre" maxlength="40" value="<?php echo htmlspecialchars($data['name']); ?>" class="<?php echo $input; ?>" required>
					<span class="text-xs text-rose-500"><?php echo $err['name'] ?? ''; ?></span>
				</div>

				<div>
					<label class="<?php echo $label; ?>">Categoría</label>
					<select name="cboCategoria" class="<?php echo $input; ?>" required>
						<option value="">-- Seleccione --</option>
						<?php foreach ($data['categories'] as $c): ?>
							<option value="<?php echo $c->CategoryID; ?>" <?php echo ($data['category'] == $c->CategoryID) ? 'selected' : ''; ?>>
								<?php echo htmlspecialchars($c->CategoryName); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<span class="text-xs text-rose-500"><?php echo $err['category'] ?? ''; ?></span>
				</div>

				<div>
					<label class="<?php echo $label; ?>">Proveedor</label>
					<select name="cboProveedor" class="<?php echo $input; ?>" required>
						<option value="">-- Seleccione --</option>
						<?php foreach ($data['suppliers'] as $s): ?>
							<option value="<?php echo $s->SupplierID; ?>" <?php echo ($data['supplier'] == $s->SupplierID) ? 'selected' : ''; ?>>
								<?php echo htmlspecialchars($s->CompanyName); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<span class="text-xs text-rose-500"><?php echo $err['supplier'] ?? ''; ?></span>
				</div>

				<div>
					<label class="<?php echo $label; ?>">Presentación (cantidad por unidad)</label>
					<input type="text" name="txtPresentacion" maxlength="20" placeholder="Ej: 24 - 12 oz bottles" value="<?php echo htmlspecialchars($data['qty']); ?>" class="<?php echo $input; ?>" required>
					<span class="text-xs text-rose-500"><?php echo $err['qty'] ?? ''; ?></span>
				</div>

				<div>
					<label class="<?php echo $label; ?>">Precio unitario</label>
					<input type="number" name="txtPrecio" step="0.01" min="0" value="<?php echo htmlspecialchars($data['price']); ?>" class="<?php echo $input; ?>" required>
					<span class="text-xs text-rose-500"><?php echo $err['price'] ?? ''; ?></span>
				</div>

				<div>
					<label class="<?php echo $label; ?>">Unidades en existencia</label>
					<input type="number" name="txtExistencia" min="0" max="32767" value="<?php echo htmlspecialchars($data['stock']); ?>" class="<?php echo $input; ?>" required>
					<span class="text-xs text-rose-500"><?php echo $err['stock'] ?? ''; ?></span>
				</div>

				<div>
					<label class="<?php echo $label; ?>">Unidades pedidas</label>
					<input type="number" name="txtPedidas" min="0" max="32767" value="<?php echo htmlspecialchars($data['onorder']); ?>" class="<?php echo $input; ?>" required>
					<span class="text-xs text-rose-500"><?php echo $err['onorder'] ?? ''; ?></span>
				</div>

				<div>
					<label class="<?php echo $label; ?>">Nivel de reorden</label>
					<input type="number" name="txtReorden" min="0" max="32767" value="<?php echo htmlspecialchars($data['reorder']); ?>" class="<?php echo $input; ?>" required>
					<span class="text-xs text-rose-500"><?php echo $err['reorder'] ?? ''; ?></span>
				</div>

				<div class="flex items-center gap-2 pt-6">
					<input type="checkbox" name="chkDescontinuado" id="chkDescontinuado" class="w-4 h-4" <?php echo $data['discontinued'] ? 'checked' : ''; ?>>
					<label for="chkDescontinuado" class="text-sm font-semibold text-slate-700">Producto descontinuado</label>
				</div>

				<div class="md:col-span-2 flex justify-end gap-3 pt-4 border-t">
					<a href="<?php echo urlRoot; ?>/products" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg font-medium hover:bg-slate-300 transition">Cancelar</a>
					<button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">Aceptar</button>
				</div>
			</form>
		</div>
	</main>

	<?php require_once(appRoot . '/views/includes/pie.php'); ?>
</div>
</body>
</html>