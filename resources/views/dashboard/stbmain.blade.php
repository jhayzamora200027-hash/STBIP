<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<title>STB Totals</title>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800;900&display=swap" rel="stylesheet">
	<style>
		body { font-family: 'Poppins', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; background:#fff; margin:28px; color:#282828; }
		.stat-list { display:grid; grid-template-columns: 1fr; gap:18px; }
		.stat-row { display:flex; flex-direction:column; align-items:center; text-align:center; gap:6px; padding:8px 12px; }
		.stat-num { font-size:72px; font-weight:700; line-height:1; color:#282828; font-family: 'Poppins', sans-serif; }
		.stat-text { margin-top:6px; }
		.stat-heading { font-size:1.05rem; font-weight:700; margin:0; }
		.stat-desc { color:#282828; font-size:0.92rem; line-height:1.35; margin-top:6px; }

		.dashboard-grid { display:grid; grid-template-columns: 360px 640px 320px; gap:28px; align-items:start; max-width:1360px; margin:0 auto; }
		.st-map-figure-wrapper { position:relative; max-width:640px; margin:0 auto; height:760px; display:flex; align-items:center; justify-content:center; }
		.st-map-figure-wrapper object, .st-map-figure-wrapper img, #philippines-map-inline, #philippines-map-inline-mobile { width:100%; height:100%; display:block; max-height:none; }
		#philippines-map-inline svg { width:100% !important; height:100% !important; display:block; }
		.st-map-region-list {
			background: #fafdff;
			border-radius: 12px;
			box-shadow: 0 2px 8px rgba(16, 174, 181, 0.07);
			padding: 12px 12px 8px 12px;
			max-height: none;
			overflow-y: visible;
		}
		.st-map-region-list-title {
			font-weight: 600;
			color: #0f172a;
			margin-bottom: 8px;
			font-size: 0.96rem;
		}
		.st-map-region-row {
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 6px 6px;
			border-radius: 8px;
			background: #ffffff;
			box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
			margin-bottom: 6px;
			font-size: 0.9rem;
		}
		.st-map-region-row-main {
			display: flex;
			align-items: center;
			gap: 8px;
		}
		.st-map-region-color-dot {
			width: 14px;
			height: 14px;
			border-radius: 50%;
			box-shadow: 0 1px 3px rgba(15, 23, 42, 0.2);
		}
		.st-map-region-label {
			font-weight: 500;
			color: #0f172a;
		}
		.st-map-region-count {
			min-width: 34px;
			text-align: right;
			font-weight: 600;
			color: #10aeb5;
		}

		@media (max-width:1024px){ .dashboard-grid { grid-template-columns: 1fr; max-width:920px; padding:0 18px; } .st-map-figure-wrapper { max-width:520px; height:auto; } }
		@media (max-width:640px){
			.stat-num{ font-size:56px; }
			.stat-list{ grid-template-columns: repeat(2, minmax(0,1fr)); gap:12px; }
			.stat-row { padding: 12px 10px; }
			/* hide region listing on small screens to focus on totals/map */
			#map-region-list, .st-map-region-list { display: none !important; }
			/* allow map wrapper to remain centered and use available space */
			.st-map-figure-wrapper { max-width: 420px; height: 580px; }
		}
	</style>
</head>

<body>
	<div class="dashboard-grid">
		<div class="stat-list">
		<div class="stat-row">
			<div class="stat-num"><span class="counter" data-target="{{ collect($data)->filter(function($row){
				return stripos($row['region'], 'Data CY 2020-2022') === false && !empty($row['title']);
			})->count() }}">0</span></div>
			<div class="stat-text">
				<div class="stat-heading">Total Adopted and Replicated</div>
				<div class="stat-desc">Reflects the overall reach and expansion of Social Technologies through actual implementation across multiple LGUs.</div>
			</div>
		</div>

		<div class="stat-row">
			<div class="stat-num"><span class="counter" data-target="{{ collect($data)->filter(function($row){
				$val = $row['with_moa'] ?? null;
				$flag = is_bool($val) ? $val : (strtoupper(trim((string) $val)) === 'TRUE');
				return stripos($row['region'], 'Data CY 2020-2022') === false && $flag;
			})->count() }}">0</span></div>
			<div class="stat-text">
				<div class="stat-heading">Total MOA Signed</div>
				<div class="stat-desc">Represents the number of formal agreements completed, indicating secured partnerships and active collaboration.</div>
			</div>
		</div>

		<div class="stat-row">
			<div class="stat-num"><span class="counter" data-target="{{ collect($data)->filter(function($row){
				$val = $row['with_res'] ?? null;
				$flag = is_bool($val) ? $val : (strtoupper(trim((string) $val)) === 'TRUE');
				return stripos($row['region'], 'Data CY 2020-2022') === false && $flag;
			})->count() }}">0</span></div>
			<div class="stat-text">
				<div class="stat-heading">Total SB Resolution</div>
				<div class="stat-desc">Indicates the extent of local government support through officially approved resolutions.</div>
			</div>
		</div>

		<div class="stat-row">
			<div class="stat-num"><span class="counter" data-target="{{ collect($data)->filter(function($row){
				$val = $row['with_expr'] ?? null;
				$flag = is_bool($val) ? $val : (strtoupper(trim((string) $val)) === 'TRUE');
				return stripos($row['region'], 'Data CY 2020-2022') === false && $flag;
			})->count() }}">0</span></div>
			<div class="stat-text">
				<div class="stat-heading">Total Expression of Interest</div>
				<div class="stat-desc">Shows the level of interest from stakeholders, highlighting potential opportunities for future replication.</div>
			</div>
		</div>
	</div>

	<!-- Philippines map + region list -->
	<div class="st-map-figure-wrapper" style="position:relative; margin:0 auto;">
		<div id="philippines-map-inline" style="width:100%; height:100%;">{!! preg_replace('/<\?xml.*\?>/','', file_get_contents(public_path('images/philippines.svg'))) !!}</div>
		<div style="position:absolute; left:50%; bottom:12px; transform:translateX(-50%); background:transparent; width:auto; max-width:90%;">
			<div id="map-region-label" style="font-weight:600; background:rgba(255,255,255,0.95); padding:6px 10px; border-radius:6px; text-align:center; white-space:nowrap;">Hover a region on the map</div>
		</div>
		</div>

		<div id="map-region-list" class="st-map-region-list" style="margin:8px 0 0 0;">
		<!-- populated by initPhilippinesMapHover() -->
	</div>
	</div>

	<script>
	document.addEventListener('DOMContentLoaded', function(){
		const counters = document.querySelectorAll('.counter');
		const duration = 1400; // ms for each counter
		counters.forEach(counter => {
			const target = parseInt(counter.getAttribute('data-target')) || 0;
			const startTime = performance.now();
			function update(now){
				const progress = Math.min((now - startTime) / duration, 1);
				const value = Math.floor(progress * target);
				counter.textContent = value.toLocaleString();
				if(progress < 1){
					requestAnimationFrame(update);
				} else {
					counter.textContent = target.toLocaleString();
				}
			}
			requestAnimationFrame(update);
		});
	});
	</script>

	<script>
	// provide the full listing data for map counts (same filter used in main.blade.php)
	window.fullListingData = @json(collect($data)->filter(function($row){
		return stripos($row['region'], 'Data CY 2020-2022') === false && !empty($row['title']);
	})->values());
	</script>

	<script>
	// Full initPhilippinesMapHover function (supports inline SVG)
	function initPhilippinesMapHover() {
		const phMapObject = document.getElementById('philippines-map-inline') || document.getElementById('philippines-map');
		if (!phMapObject) return;
		if (phMapObject.dataset.phRegionsBound === '1') return;
		const regionLabelEl = document.getElementById('map-region-label');
		let svgDoc = null;
		if (phMapObject.tagName && phMapObject.tagName.toLowerCase() === 'object') {
			svgDoc = phMapObject.contentDocument || (phMapObject.getSVGDocument && phMapObject.getSVGDocument());
		} else {
			svgDoc = phMapObject.querySelector('svg') || phMapObject;
		}
		if (!svgDoc) return;
		// Force the inline SVG to fill the wrapper and use slice to cover area
		try {
			const svgRootEl = svgDoc.documentElement || (svgDoc.querySelector ? svgDoc.querySelector('svg') : null);
			if (svgRootEl && svgRootEl.setAttribute) {
				svgRootEl.setAttribute('preserveAspectRatio','xMidYMid slice');
				svgRootEl.style.width = '100%';
				svgRootEl.style.height = '100%';
			}
		} catch (e) {}
		const paths = svgDoc.querySelectorAll('path');
		if (!paths.length) return;

		const provinceToRegion = {
			'Ilocos Norte': 'Region I', 'Ilocos Sur': 'Region I', 'La Union': 'Region I', 'Pangasinan': 'Region I',
			'Abra': 'CAR','Apayao': 'CAR','Benguet': 'CAR','Ifugao': 'CAR','Kalinga': 'CAR','Mountain Province': 'CAR',
			'Batanes': 'Region II','Cagayan': 'Region II','Isabela': 'Region II','Nueva Vizcaya': 'Region II','Quirino': 'Region II',
			'Aurora': 'Region III','Bataan': 'Region III','Bulacan': 'Region III','Nueva Ecija': 'Region III','Pampanga': 'Region III','Tarlac': 'Region III','Zambales': 'Region III',
			'Batangas': 'Region IV-A','Cavite': 'Region IV-A','Laguna': 'Region IV-A','Quezon': 'Region IV-A','Rizal': 'Region IV-A',
			'Marinduque': 'Region IV-B','Mindoro Occidental': 'Region IV-B','Mindoro Oriental': 'Region IV-B','Palawan': 'Region IV-B','Romblon': 'Region IV-B',
			'Metropolitan Manila': 'NCR',
			'Albay': 'Region V','Camarines Norte': 'Region V','Camarines Sur': 'Region V','Catanduanes': 'Region V','Masbate': 'Region V','Sorsogon': 'Region V',
			'Aklan': 'Region VI','Antique': 'Region VI','Capiz': 'Region VI','Guimaras': 'Region VI','Iloilo': 'Region VI','Negros Occidental': 'Region VI',
			'Bohol': 'Region VII','Cebu': 'Region VII','Negros Oriental': 'Region VII','Siquijor': 'Region VII',
			'Biliran': 'Region VIII','Eastern Samar': 'Region VIII','Leyte': 'Region VIII','Northern Samar': 'Region VIII','Samar': 'Region VIII','Southern Leyte': 'Region VIII',
			'Zamboanga del Norte': 'Region IX','Zamboanga del Sur': 'Region IX','Zamboanga Sibugay': 'Region IX',
			'Bukidnon': 'Region X','Camiguin': 'Region X','Lanao del Norte': 'Region X','Misamis Occidental': 'Region X','Misamis Oriental': 'Region X',
			'Compostela Valley': 'Region XI','Davao del Norte': 'Region XI','Davao del Sur': 'Region XI','Davao Oriental': 'Region XI',
			'Cotabato': 'Region XII','Sarangani': 'Region XII','South Cotabato': 'Region XII','Sultan Kudarat': 'Region XII',
			'Agusan del Norte': 'CARAGA','Agusan del Sur': 'CARAGA','Dinagat Islands': 'CARAGA','Surigao del Norte': 'CARAGA','Surigao del Sur': 'CARAGA',
			'Basilan': 'BARMM','Lanao del Sur': 'BARMM','Maguindanao': 'BARMM','Sulu': 'BARMM','Tawi-Tawi': 'BARMM'
		};

		const regionToPaths = {};
		const regionToProvinces = {};
		const provinceRegionIndex = {};
		const pathInfos = [];
		const svgRoot = (svgDoc && svgDoc.documentElement) ? svgDoc.documentElement : svgDoc;
		const regionLabels = {
			'Region I': 'Region I – Ilocos Region','CAR': 'CAR – Cordillera Administrative Region','Region II': 'Region II – Cagayan Valley','Region III': 'Region III – Central Luzon','Region IV-A': 'Region IV-A – CALABARZON','Region IV-B': 'Region IV-B – MIMAROPA','NCR': 'NCR – National Capital Region','Region V': 'Region V – Bicol Region','Region VI': 'Region VI – Western Visayas','Region VII': 'Region VII – Central Visayas','Region VIII': 'Region VIII – Eastern Visayas','Region IX': 'Region IX – Zamboanga Peninsula','Region X': 'Region X – Northern Mindanao','Region XI': 'Region XI – Davao Region','Region XII': 'Region XII – SOCCSKSARGEN','CARAGA': 'Region XIII – CARAGA','BARMM': 'BARMM – Bangsamoro Autonomous Region'
		};
		const regionColors = { 'Region I': '#ffb74d','CAR': '#9575cd','Region II': '#4db6ac','Region III': '#81c784','Region IV-A': '#f06292','Region IV-B': '#64b5f6','NCR': '#ff8a65','Region V': '#ba68c8','Region VI': '#aed581','Region VII': '#4fc3f7','Region VIII': '#ffcc80','Region IX': '#ce93d8','Region X': '#80cbc4','Region XI': '#ffab91','Region XII': '#9fa8da','CARAGA': '#a5d6a7','BARMM': '#ffecb3' };
		const regionBlinkTimers = {};

		paths.forEach(path => {
			if (path.dataset.phHoverBound === '1') return;
			const computed = window.getComputedStyle(path);
			function extractProvinceName(p) {
				let name = '';
				if (p.getAttribute) {
					name = p.getAttribute('title') || p.getAttribute('data-name') || p.getAttribute('data-province') || p.getAttribute('aria-label') || '';
				}
				if (!name) {
					const t = p.querySelector && p.querySelector('title');
					if (t && t.textContent) name = t.textContent;
				}
				if (!name && p.id) {
					// try to derive from id like "prov_Batangas" or "Batangas-1"
					name = p.id.replace(/[_\-\d]+/g, ' ').replace(/^(prov|path|p)\s*/i, '').trim();
				}
				return name || '';
			}
			const provinceName = (extractProvinceName(path) || '').trim();
			const regionName = provinceToRegion[provinceName] || provinceRegionIndex[provinceName.toLowerCase()] || null;
			const baseFill = (regionName && regionColors[regionName]) ? regionColors[regionName] : (path.getAttribute('fill') || computed.fill || '#000000');
			const originalStroke = path.getAttribute('stroke') || computed.stroke || '#ffffff';
			const originalStrokeWidth = path.getAttribute('stroke-width') || computed.strokeWidth || '0.5';
			const originalOpacity = path.getAttribute('opacity') || computed.opacity || '1';

			const info = { path: path, originalFill: baseFill, originalStroke: originalStroke, originalStrokeWidth: originalStrokeWidth, originalOpacity: originalOpacity, regionName: regionName };
			try { path.dataset.phOriginalOpacity = originalOpacity; } catch (e) {}
			pathInfos.push(info);

			if (regionName) {
				if (!regionToPaths[regionName]) { regionToPaths[regionName] = []; }
				regionToPaths[regionName].push(info);
				if (!regionToProvinces[regionName]) { regionToProvinces[regionName] = []; }
				if (provinceName && regionToProvinces[regionName].indexOf(provinceName) === -1) {
					regionToProvinces[regionName].push(provinceName);
					const normProv = normalizeProvinceName(provinceName);
					if (normProv && !provinceRegionIndex[normProv]) { provinceRegionIndex[normProv] = regionName; }
				}
			}

			path.dataset.phHoverBound = '1';
			// force fill using style property to override any internal SVG styles
			try { path.style.setProperty('fill', info.originalFill, 'important'); } catch(e) { path.style.fill = info.originalFill; }
			path.style.transition = 'fill 0.18s ease-out, stroke 0.18s ease-out, stroke-width 0.18s ease-out, transform 0.2s ease-out, opacity 0.18s ease-out';
			path.style.cursor = 'pointer';
			try { path.style.transformBox = 'fill-box'; path.style.transformOrigin = '50% 50%'; } catch(e) {}
		});

		function getPathAnchorRect(path) {
			try {
				const bbox = path.getBBox();
				const svgEl = path.ownerSVGElement || svgRoot;
				const vb = svgEl && svgEl.viewBox && svgEl.viewBox.baseVal
					? svgEl.viewBox.baseVal
					: { x: 0, y: 0, width: svgEl.clientWidth || 1, height: svgEl.clientHeight || 1 };
				const mapRect = phMapObject.getBoundingClientRect();
				const scaleX = mapRect.width / (vb.width || 1);
				const scaleY = mapRect.height / (vb.height || 1);
				const centerX = mapRect.left + ((bbox.x + bbox.width / 2 - vb.x) * scaleX);
				const centerY = mapRect.top + ((bbox.y + bbox.height / 2 - vb.y) * scaleY);
				const size = 24;
				return { left: centerX - size / 2, right: centerX + size / 2, top: centerY - size / 2, bottom: centerY + size / 2, width: size, height: size };
			} catch (e) {
				return phMapObject.getBoundingClientRect();
			}
		}

		const dataForCounts = window.fullListingData || [];
		const regionCounts = {};
		if (Array.isArray(dataForCounts) && dataForCounts.length) {
			dataForCounts.forEach(function(row) {
				const rName = inferRegionCodeFromRow(row, provinceRegionIndex);
				if (!rName) return;
				regionCounts[rName] = (regionCounts[rName] || 0) + 1;
			});
		}

		const mapTooltip = (function() {
			let el = document.getElementById('catListTooltip');
			if (!el) {
				el = document.createElement('div');
				el.id = 'catListTooltip';
				el.style.position = 'fixed';
				el.style.zIndex = '9999';
				el.style.display = 'none';
				el.style.pointerEvents = 'none';
				el.style.background = 'rgba(34,34,34,0.97)';
				el.style.color = '#fff';
				el.style.padding = '4px 10px';
				el.style.borderRadius = '6px';
				el.style.fontSize = '12px';
				el.style.boxShadow = '0 2px 8px rgba(16,174,181,0.13)';
				el.style.whiteSpace = 'pre-line';
				el.style.maxWidth = '260px';
				el.style.lineHeight = '1.3';
				document.body.appendChild(el);
			}
			return el;
		})();

		function formatRegionTooltip(regionCode) {
			if (!regionCode) return '';
			const label = regionLabels[regionCode] || regionCode;
			const count = regionCounts[regionCode] || 0;
			const plural = count === 1 ? 'ST' : 'STs';
			return '<strong>' + label + '</strong><br><span style="color:#1de9b6;font-weight:600;">' + count + ' ' + plural + '</span>';
		}

		function showMapTooltip(regionCode, anchorRect) {
			if (!mapTooltip || !regionCode) return;
			const html = formatRegionTooltip(regionCode);
			if (!html) return;
			mapTooltip.innerHTML = html;
			mapTooltip.style.display = 'block';

			const baseRect = anchorRect || phMapObject.getBoundingClientRect();
			const tooltipWidth = mapTooltip.offsetWidth || 0;
			const tooltipHeight = mapTooltip.offsetHeight || 0;
			let left = baseRect.left + (baseRect.width - tooltipWidth) / 2;
			let top = baseRect.top - tooltipHeight - 8; // above anchor
			if (top < 8) { top = baseRect.bottom + 8; }
			left = Math.max(8, Math.min(left, window.innerWidth - tooltipWidth - 8));
			if (top + tooltipHeight + 8 > window.innerHeight) { top = Math.max(8, window.innerHeight - tooltipHeight - 8); }
			mapTooltip.style.left = left + 'px';
			mapTooltip.style.top = top + 'px';
		}

		function hideMapTooltip() { if (!mapTooltip) return; mapTooltip.style.display = 'none'; }

		const regionListEl = document.getElementById('map-region-list');
		if (regionListEl) {
			const orderedRegions = ['NCR','Region I','CAR','Region II','Region III','Region IV-A','Region IV-B','Region V','Region VI','Region VII','Region VIII','Region IX','Region X','Region XI','Region XII','CARAGA'];
			let html = '<div class="st-map-region-list-title">Regions (ST title count)</div>';
			orderedRegions.forEach(function(code) {
				if (!regionLabels[code]) return;
				const count = regionCounts[code] || 0;
				const color = regionColors[code] || '#cbd5e1';
				html += '<div class="st-map-region-row" data-region="' + code + '">';
				html += '<div class="st-map-region-row-main">';
				html += '<span class="st-map-region-color-dot" style="background:' + color + ';"></span>';
				html += '<span class="st-map-region-label">' + (regionLabels[code] || code) + '</span>';
				html += '</div>';
				html += '<span class="st-map-region-count">' + count + '</span>';
				html += '</div>';
			});
			regionListEl.innerHTML = html;
			function getRegionRepresentativeInfo(regionCode) { const arr = regionToPaths[regionCode]; return (arr && arr.length) ? arr[0] : null; }
			function getRegionAnchorRectFromCode(regionCode) { const info = getRegionRepresentativeInfo(regionCode); if (!info) return phMapObject.getBoundingClientRect(); return getPathAnchorRect(info.path); }
			function startRegionBlink(regionCode) { if (!regionCode) return; const info = getRegionRepresentativeInfo(regionCode); if (!info) return; if (regionBlinkTimers[regionCode]) { clearInterval(regionBlinkTimers[regionCode]); } let visible = true; highlightGroup(info, true, { color: '#ffeb3b', stroke: '#f9a825' }); regionBlinkTimers[regionCode] = setInterval(function() { visible = !visible; highlightGroup(info, visible, { color: '#ffeb3b', stroke: '#f9a825' }); }, 450); }
			function stopRegionBlink(regionCode) { if (!regionCode) return; const timer = regionBlinkTimers[regionCode]; if (timer) { clearInterval(timer); delete regionBlinkTimers[regionCode]; } const info = getRegionRepresentativeInfo(regionCode); if (info) { highlightGroup(info, false); } }
			const listRows = regionListEl.querySelectorAll('.st-map-region-row');
			listRows.forEach(function(row) {
				const code = row.getAttribute('data-region');
				row.addEventListener('mouseenter', function() { if (regionLabelEl) { if (code && regionLabels[code]) { regionLabelEl.textContent = regionLabels[code]; } else if (code) { regionLabelEl.textContent = code; } } showMapTooltip(code, getRegionAnchorRectFromCode(code)); startRegionBlink(code); });
				row.addEventListener('mouseleave', function() { stopRegionBlink(code); if (regionLabelEl) { regionLabelEl.textContent = 'Hover a region on the map'; } hideMapTooltip(); });
				row.addEventListener('click', function() { const info = getRegionRepresentativeInfo(code); if (info) { handleRegionClick(info); if (window.updateStatusSummaryCards) { window.updateStatusSummaryCards([code]); } } });
			});
		}

		function highlightGroup(targetInfo, isHover, options) {
			const regionName = targetInfo.regionName;
			let targets;
			if (regionName && regionToPaths[regionName] && regionToPaths[regionName].length) { targets = regionToPaths[regionName]; } else { targets = [targetInfo]; }
			const highlightStrokeDefault = options && options.stroke ? options.stroke : '#06306e';
			function brightenHex(hex, pct) {
				if (!hex) return hex;
				h = String(hex).replace('#','');
				if (h.length === 3) { h = h.split('').map(c=>c+c).join(''); }
				const num = parseInt(h,16);
				let r = (num >> 16) & 255;
				let g = (num >> 8) & 255;
				let b = num & 255;
				r = Math.min(255, Math.floor(r + (255 - r) * pct));
				g = Math.min(255, Math.floor(g + (255 - g) * pct));
				b = Math.min(255, Math.floor(b + (255 - b) * pct));
				return '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
			}
			// dim other paths and emphasize targets
			const allPaths = svgRoot.querySelectorAll('path');
			const targetSet = new Set(targets.map(t => t.path));
			if (isHover) {
				allPaths.forEach(p => { if (!targetSet.has(p)) { try { p.style.opacity = '0.18'; } catch(e){ p.setAttribute('opacity','0.18'); } } });
			}
			targets.forEach(info => {
				if (isHover) {
					const baseColor = (options && options.color) ? options.color : (info.regionName && regionColors[info.regionName]) ? regionColors[info.regionName] : '#10aeb5';
					const bright = brightenHex(baseColor, 0.42);
					try { info.path.setAttribute('fill', bright); } catch (e) { info.path.style.fill = bright; }
					try { info.path.setAttribute('stroke', highlightStrokeDefault); info.path.setAttribute('stroke-width', '3'); info.path.setAttribute('stroke-linejoin', 'round'); } catch (e) { info.path.style.stroke = highlightStrokeDefault; info.path.style.strokeWidth = '3'; }
					try { info.path.style.filter = 'drop-shadow(0 20px 36px rgba(16,174,181,0.22))'; } catch (e) { /* ignore */ }
					try { info.path.style.transform = 'scale(1.06)'; } catch (e) {}
						// do not move DOM node (avoids artifacts); rely on CSS transform/stroke for emphasis
				} else {
					try { info.path.setAttribute('fill', info.originalFill); } catch (e) { info.path.style.fill = info.originalFill; }
					try { info.path.setAttribute('stroke', info.originalStroke); info.path.setAttribute('stroke-width', info.originalStrokeWidth); } catch (e) { info.path.style.stroke = info.originalStroke; info.path.style.strokeWidth = info.originalStrokeWidth; }
					try { info.path.style.filter = ''; } catch (e) { }
					try { info.path.style.transform = ''; } catch (e) {}
					try { info.path.style.opacity = info.originalOpacity; } catch (e) { info.path.setAttribute('opacity', info.originalOpacity); }
				}
			});
			if (!isHover) {
				allPaths.forEach(p => { try { p.style.opacity = p.dataset.phOriginalOpacity || p.getAttribute('opacity') || '1'; } catch(e){ p.setAttribute('opacity','1'); } });
			}
		}

		function normalizeProvinceName(name) { if (!name) return ''; let n = String(name).toLowerCase().trim().replace(/\s+/g, ' '); if (n.includes('mindoro')) { if (n.includes('occidental')) return 'mindoro occidental'; if (n.includes('oriental')) return 'mindoro oriental'; } return n; }

		function inferRegionCodeFromRegionText(regionText) {
			if (!regionText) return null; const s = String(regionText).toLowerCase().trim(); if (!s) return null; if (s.includes('national capital') || s.includes(' ncr') || s.startsWith('ncr')) return 'NCR'; if (s.includes('ilocos')) return 'Region I'; if (s.includes('cagayan valley')) return 'Region II'; if (s.includes('central luzon')) return 'Region III'; if (s.includes('calabarzon') || s.includes('calborazon')) return 'Region IV-A'; if (s.includes('mimaropa')) return 'Region IV-B'; if (s.includes('bicol')) return 'Region V'; if (s.includes('western visayas')) return 'Region VI'; if (s.includes('central visayas')) return 'Region VII'; if (s.includes('eastern visayas')) return 'Region VIII'; if (s.includes('zamboanga peninsula') || s.includes('zamboanga pen')) return 'Region IX'; if (s.includes('northern mindanao')) return 'Region X'; if (s.includes('davao region')) return 'Region XI'; if (s.includes('soccsksargen')) return 'Region XII'; if (s.includes('caraga')) return 'CARAGA'; if (s.includes('bangsamoro') || /\bbarmm\b/.test(s)) return 'BARMM'; if (!s.includes('caraga') && (s === 'car' || s.includes('cordillera') || /\bcar\b/.test(s))) { return 'CAR'; }
			const txt = s; const romanPatterns = [ { code: 'Region XII', re: /\bxii\b/ }, { code: 'Region XI', re: /\bxi\b/ }, { code: 'Region X',  re: /\bx\b/ }, { code: 'Region IX', re: /\bix\b/ }, { code: 'Region VIII', re: /\bviii\b/ }, { code: 'Region VII',  re: /\bvii\b/ }, { code: 'Region VI',   re: /\bvi\b/ }, { code: 'Region V',    re: /\bv\b/ }, { code: 'Region IV-B', re: /\biv[\s-]?b\b/ }, { code: 'Region IV-A', re: /\biv[\s-]?a\b/ }, { code: 'Region III',  re: /\biii\b/ }, { code: 'Region II',   re: /\bii\b/ }, { code: 'Region I',    re: /\bi\b/ } ];
			for (let i = 0; i < romanPatterns.length; i++) { if (romanPatterns[i].re.test(txt)) return romanPatterns[i].code; }
			return null;
		}

		function inferRegionCodeFromRow(row, provinceRegionIndex) { if (!row) return null; let byProvince = null; if (row.province) { const normProv = normalizeProvinceName(row.province); byProvince = provinceRegionIndex[normProv] || null; } const byRegionText = inferRegionCodeFromRegionText(row.region); if (byProvince === 'BARMM' && byRegionText && byRegionText !== 'BARMM') { return byRegionText; } if (byProvince) return byProvince; return byRegionText; }

		function handleRegionClick(targetInfo) {
			const regionName = targetInfo.regionName; const data = window.fullListingData || [];
			if (!data.length) { openRegionTitlesModal(regionName || 'Region', []); return; }
			let rows = [];
			if (regionName) { rows = data.filter(function(row){ return inferRegionCodeFromRow(row, provinceRegionIndex) === regionName; }); }
			else { const singleProv = targetInfo.path.getAttribute('title') || ''; if (singleProv) { const targetProvNorm = normalizeProvinceName(singleProv); rows = data.filter(function(row){ if (!row || !row.province) return false; return normalizeProvinceName(row.province) === targetProvNorm; }); if (pageData.length < perPage) { for (let i = pageData.length; i < perPage; i++) { html += '<tr><td colspan="5" class="social-listing-empty">&nbsp;</td></tr>'; } } } }
			const displayName = (regionName && regionLabels[regionName]) || regionName || (targetInfo.path.getAttribute('title') ? ('Province: ' + targetInfo.path.getAttribute('title')) : 'Region');
			openRegionTitlesModal(displayName, rows);
			if (regionName && window.updateStatusSummaryCards) { window.updateStatusSummaryCards([regionName]); }
		}

		pathInfos.forEach(info => {
			const p = info.path;
			p.addEventListener('mouseenter', function () { highlightGroup(info, true); if (regionLabelEl) { const labelKey = info.regionName; if (labelKey && regionLabels[labelKey]) { regionLabelEl.textContent = regionLabels[labelKey]; } else if (info.regionName) { regionLabelEl.textContent = info.regionName; } else { regionLabelEl.textContent = 'Province: ' + (p.getAttribute('title') || ''); } } if (info.regionName) { showMapTooltip(info.regionName, getPathAnchorRect(p)); } else { hideMapTooltip(); } });
			p.addEventListener('mouseleave', function () { highlightGroup(info, false); if (regionLabelEl) { regionLabelEl.textContent = 'Hover a region on the map'; } hideMapTooltip(); });
			p.addEventListener('click', function () { handleRegionClick(info); hideMapTooltip(); });
		});

		phMapObject.dataset.phRegionsBound = '1';
	}

	// Attach loader to the object if present
	const phMapElement = document.getElementById('philippines-map-inline') || document.getElementById('philippines-map');
	if (phMapElement) {
		if (phMapElement.tagName && phMapElement.tagName.toLowerCase() === 'object') {
			phMapElement.addEventListener('load', function () { setTimeout(initPhilippinesMapHover, 0); });
		}
		setTimeout(initPhilippinesMapHover, 500);
	}
	</script>
</body>
</html>

