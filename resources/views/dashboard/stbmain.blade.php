<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<title>STB Totals</title>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800;900&display=swap" rel="stylesheet">
	<style>
		body { font-family: 'Poppins', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; background:#fff; margin:28px; color:#111; }
		.stat-list { max-width:720px; display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:28px 32px; }
		.stat-row { display:flex; flex-direction:column; align-items:center; text-align:center; gap:6px; padding:8px 12px; }
		.stat-num { font-size:72px; font-weight:900; line-height:1; color:#111; font-family: 'Poppins', sans-serif; }
		.stat-text { margin-top:6px; }
		.stat-heading { font-size:1.05rem; font-weight:700; margin:0; }
		.stat-desc { color:#777; font-size:0.92rem; line-height:1.35; margin-top:6px; }
		@media (max-width:640px){ .stat-list{ grid-template-columns: 1fr; } .stat-num{ font-size:56px; } }
	</style>
</head>
<body>
	<div class="stat-list">
		<div class="stat-row">
			<div class="stat-num">{{ collect($data)->filter(function($row){
				return stripos($row['region'], 'Data CY 2020-2022') === false && !empty($row['title']);
			})->count() }}</div>
			<div class="stat-text">
				<div class="stat-heading">Total Adopted and Replicated</div>
				<div class="stat-desc">Reflects the overall reach and expansion of Social Technologies through actual implementation across multiple LGUs.</div>
			</div>
		</div>

		<div class="stat-row">
			<div class="stat-num">{{ collect($data)->filter(function($row){
				$val = $row['with_moa'] ?? null;
				$flag = is_bool($val) ? $val : (strtoupper(trim((string) $val)) === 'TRUE');
				return stripos($row['region'], 'Data CY 2020-2022') === false && $flag;
			})->count() }}</div>
			<div class="stat-text">
				<div class="stat-heading">Total MOA Signed</div>
				<div class="stat-desc">Represents the number of formal agreements completed, indicating secured partnerships and active collaboration.</div>
			</div>
		</div>

		<div class="stat-row">
			<div class="stat-num">{{ collect($data)->filter(function($row){
				$val = $row['with_res'] ?? null;
				$flag = is_bool($val) ? $val : (strtoupper(trim((string) $val)) === 'TRUE');
				return stripos($row['region'], 'Data CY 2020-2022') === false && $flag;
			})->count() }}</div>
			<div class="stat-text">
				<div class="stat-heading">Total SB Resolution</div>
				<div class="stat-desc">Indicates the extent of local government support through officially approved resolutions.</div>
			</div>
		</div>

		<div class="stat-row">
			<div class="stat-num">{{ collect($data)->filter(function($row){
				$val = $row['with_expr'] ?? null;
				$flag = is_bool($val) ? $val : (strtoupper(trim((string) $val)) === 'TRUE');
				return stripos($row['region'], 'Data CY 2020-2022') === false && $flag;
			})->count() }}</div>
			<div class="stat-text">
				<div class="stat-heading">Total Expression of Interest</div>
				<div class="stat-desc">Shows the level of interest from stakeholders, highlighting potential opportunities for future replication.</div>
			</div>
		</div>
	</div>
</body>
</html>

