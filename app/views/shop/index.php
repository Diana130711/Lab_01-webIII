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

	<main class="flex justify-center my-8">
		<div class="w-full max-w-md">
			<div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
				<div class="mb-6">
					<h1 class="text-xl font-bold text-slate-800">Tienda en línea NorthWind</h1>
					<p class="text-sm text-slate-500">Ingrese su código de cliente para hacer su pedido.</p>
				</div>

				<form action="<?php echo urlRoot; ?>/shop" method="POST" class="space-y-4">
					<div>
						<label class="block text-sm font-medium text-slate-700 mb-1">Código de cliente</label>
						<input type="text" name="txtCodigo" maxlength="5" placeholder="Ej: ALFKI" value="<?php echo htmlspecialchars($data['code']); ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm outline-none uppercase" required>
						<?php if (!empty($data['error'])): ?>
							<span class="text-xs text-rose-500 mt-1 block font-medium"><?php echo htmlspecialchars($data['error']); ?></span>
						<?php endif; ?>
					</div>
					<button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-4 rounded-lg shadow-sm text-sm transition-colors">Continuar</button>
				</form>
			</div>
		</div>
	</main>

	<?php require_once(appRoot . '/views/includes/pie.php'); ?>
</div>
</body>
</html>