// available regardless of which <script> block runs first.
function deriveRegionFromSlider(source){
    let name = '';
    if (source && typeof source === 'object'){
        if (source.regionName) name = source.regionName;
        if (!name && source.src) {
            const file = source.src.split('/').pop();
            const filenameMap = {
                '1.png':'Region I','2.png':'Region II','3.png':'Region III',
                '4_a.png':'Region IV-A','4_b.png':'Region IV-B','5.png':'Region V',
                '6.png':'Region VI','7.png':'Region VII','8.png':'Region VIII',
                '9.png':'Region IX','10.png':'Region X','11.png':'Region XI',
                '12.png':'Region XII','13.png':'CARAGA',
                'barmm.png':'BARMM','car.png':'CAR','ncr.png':'NCR','nir.png':'NIR'
            };
            name = filenameMap[file] || '';
        }
        if (!name && source.regionNumber) {
            const romans = ['','I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
            const num = parseInt(source.regionNumber,10);
            if (!isNaN(num) && romans[num]) name = 'Region ' + romans[num];
        }
    }
    if (!name && source && source.getAttribute) {
        name = source.getAttribute('data-region-name') || '';
        if (!name) {
            const file = (source.dataset.img||source.src||'').split('/').pop();
            const filenameMap2 = {
                '1.png':'Region I','2.png':'Region II','3.png':'Region III',
                '4_a.png':'Region IV-A','4_b.png':'Region IV-B','5.png':'Region V',
                '6.png':'Region VI','7.png':'Region VII','8.png':'Region VIII',
                '9.png':'Region IX','10.png':'Region X','11.png':'Region XI',
                '12.png':'Region XII','13.png':'CARAGA',
                'barmm.png':'BARMM','car.png':'CAR','ncr.png':'NCR','nir.png':'NIR'
            };
            name = filenameMap2[file] || '';
        }
    }
    return name;
}


document.addEventListener("DOMContentLoaded", function () {

	// load jquery+select2 to make filters multi-select/searchable
	(function(){
		function loadCss(u){var l=document.createElement('link');l.rel='stylesheet';l.href=u;document.head.appendChild(l);}        
		function loadScript(u,cb){var s=document.createElement('script');s.onload=cb;s.src=u;document.head.appendChild(s);}        
		function init(){
			try{ jQuery('.rsm-select2').select2({ width:'100%', placeholder:function(){return jQuery(this).data('placeholder')||'';}, allowClear:true });
                // after select2 has replaced the <select>, attach listeners to it
                const $prov = jQuery('#rsm-filter-prov');
                if ($prov.length) {
                    $prov.on('change.select2 select2:select select2:unselect', function(){
                        console.log('[RSM] select2 province event fired');
                        setTimeout(()=>{
                            // these functions are defined later in the file; guard against
                            // the case where the user interacts before the second script
                            // block has executed (see errors in console). if they're not
                            // ready yet we'll simply log and wait for the normal
                            // binding later on to take effect.
                            if (typeof updateProvinceFilters === 'function') {
                                updateProvinceFilters();
                            } else {
                                console.warn('[RSM] updateProvinceFilters not available yet');
                            }
                            if (typeof applyRsmFilters === 'function') {
                                applyRsmFilters();
                            } else {
                                console.warn('[RSM] applyRsmFilters not available yet');
                            }
                        },0);
                    });
                    // if there is already a selection, update once now
                    setTimeout(()=>{
                        if (typeof updateProvinceFilters === 'function') {
                            updateProvinceFilters();
                        }
                    },0);
                }
            }catch(e){ console.error('[RSM] select2 init error', e); }
		}
		if (!window.jQuery){
			loadScript('https://code.jquery.com/jquery-3.6.0.min.js',function(){
				loadCss('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
				loadScript('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',init);
			});
		}else if (!jQuery.fn.select2){
			loadCss('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
			loadScript('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',init);
		}else{init();}
	})();

	// strip ASCII control characters from a string (mirrors PHP cleanup)
	function clean(s) {
		return (s||'').toString().replace(/[\x00-\x1F\x7F]/g, '');
	}

	// helper to trim province/city keys received from server
	function normalizeGrouping(src) {
		const out = {};
		Object.keys(src || {}).forEach(p => {
			const pp = (p||'').toString().trim();
			out[pp] = {};
			const cities = src[p] || {};
			Object.keys(cities).forEach(c => {
				const cc = (c||'').toString().trim();
				out[pp][cc] = cities[c];
			});
		});
		return out;
	}

	const swiper = new Swiper(".mySwiper", {
		effect: "coverflow",
		grabCursor: true,
		centeredSlides: true,
		slidesPerView: "auto",
		loop: true,
		speed: 1200,
		coverflowEffect: {
			rotate: 0,
			stretch: 0,
			depth: 400,
			modifier: 2.5,
			slideShadows: false,
		},
		navigation: {
			nextEl: ".swiper-button-next",
			prevEl: ".swiper-button-prev",
		},
	});

	// normalize various region strings to canonical slider labels
	// helper used throughout to convert disparate region identifiers to the canonical keys
	// stored in window.parent.regionMap.
	function normalizeRegionText(r){
		if (!r) return '';
		var s = String(r).toLowerCase().trim();
		if (!s) return '';
		if (s.includes('national capital') || s.includes(' ncr') || s.startsWith('ncr')) return 'NCR';
		if (s.includes('ilocos')) return 'Region I';
		if (s.includes('cagayan valley')) return 'Region II';
		if (s.includes('central luzon')) return 'Region III';
		// treat typo as IV-A as well
		if (s.includes('calabarzon') || s.includes('calborazon')) return 'Region IV-A';
		if (s.includes('mimaropa')) return 'Region IV-B';
		if (s.includes('bicol')) return 'Region V';
		if (s.includes('western visayas')) return 'Region VI';
		if (s.includes('central visayas')) return 'Region VII';
		if (s.includes('eastern visayas')) return 'Region VIII';
		if (s.includes('zamboanga peninsula') || s.includes('zamboanga pen')) return 'Region IX';
		if (s.includes('northern mindanao')) return 'Region X';
		if (s.includes('davao region')) return 'Region XI';
		if (s.includes('soccsksargen')) return 'Region XII';
		if (s.includes('caraga')) return 'CARAGA';
		if (s.includes('bangsamoro') || /\bbarmm\b/.test(s)) return 'BARMM';
		if (!s.includes('caraga') && (s === 'car' || s.includes('cordillera') || /\bcar\b/.test(s))) {
			return 'CAR';
		}
		// generic roman detection
		var romanPatterns = [
			{ code: 'Region XII', re: /\bxii\b/ },
			{ code: 'Region XI', re: /\bxi\b/ },
			{ code: 'Region X',  re: /\bx\b/ },
			{ code: 'Region IX', re: /\bix\b/ },
			{ code: 'Region VIII', re: /\bviii\b/ },
			{ code: 'Region VII',  re: /\bvii\b/ },
			{ code: 'Region VI',   re: /\bvi\b/ },
			{ code: 'Region V',    re: /\bv\b/ },
			{ code: 'Region IV-B', re: /\biv[\s-]?b\b/ },
			{ code: 'Region IV-A', re: /\biv[\s-]?a\b/ },
			{ code: 'Region III',  re: /\biii\b/ },
			{ code: 'Region II',   re: /\bii\b/ },
			{ code: 'Region I',    re: /\bi\b/ }
		];
		for (var i=0;i<romanPatterns.length;i++){
			if (romanPatterns[i].re.test(s)) return romanPatterns[i].code;
		}
		return r;
	}

	// helper to hide/show slides based on a list of region names
	function filterSliderByRegions(regions){
		if (!regions || !regions.length) return;
		const filenameToRegion = {
			'1.png': 'Region I','2.png': 'Region II','3.png': 'Region III',
			'4_a.png': 'Region IV-A','4_b.png': 'Region IV-B','5.png': 'Region V',
			'6.png': 'Region VI','7.png': 'Region VII','8.png': 'Region VIII',
			'9.png': 'Region IX','10.png': 'Region X','11.png': 'Region XI',
			'12.png': 'Region XII','13.png': 'CARAGA','barmm.png': 'BARMM',
			'car.png': 'CAR','ncr.png': 'NCR','nir.png': 'NIR'
		};
		const slides = document.querySelectorAll('.swiper-slide');
		slides.forEach(slide => {
			const img = slide.querySelector('img');
			if (!img) return;
			const fileName = (img.dataset.img||img.src||'').split('/').pop();
			// same fallback as main script: use mapped name or explicit name,
			// otherwise use the raw number so digit-normalizer can convert it.
			const region = filenameToRegion[fileName] || img.getAttribute('data-region-name') || (img.getAttribute('data-region-number')||'');
			if (regions.includes(region)) {
				slide.style.display = '';
			} else {
				slide.style.display = 'none';
			}
		});
		if (swiper && typeof swiper.update === 'function') swiper.update();
	}

	// hide/show gallery cards based on region/year text matching
	function filterGalleryCards(regions, years) {
		const cards = document.querySelectorAll('.card-gallery .card');
		cards.forEach(card => {
			const txt = ((card.textContent||'') + ' ' + (card.getAttribute('data-title')||'')).toLowerCase();
			let visible = true;
			if (regions && regions.length) {
				visible = regions.some(r=> txt.indexOf(r.toLowerCase()) !== -1);
			}
			if (visible && years && years.length) {
				visible = years.some(y=> txt.indexOf(y.toString()) !== -1);
			}
			card.style.display = visible ? '' : 'none';
		});
	}

	// when the page loads read any region[] query params and apply
	document.addEventListener('DOMContentLoaded', function(){
		const urlParams = new URLSearchParams(window.location.search);
		let selected = urlParams.getAll('region[]');
		if (!selected.length) selected = urlParams.getAll('region');
		let yrs = urlParams.getAll('year_of_moa[]');
		if (!yrs.length) yrs = urlParams.getAll('year_of_moa');
		// normalize any region inputs (e.g. "FO I")
		selected = selected.map(normalizeRegionText);
		// if only year filters present, derive regions from parent.regionMap if available
		if (!selected.length && yrs.length) {
			try {
				var map = window.parent.regionMap || {};
				Object.keys(map).forEach(function(r){
					var ys = map[r].years || [];
					if (ys.some(y=> yrs.includes(y.toString()))) selected.push(r);
				});
			} catch(_){ }
		}
		if (selected.length) {
			filterSliderByRegions(selected);
			filterGalleryCards(selected, yrs);
		}
	});

	// listen for filter updates from parent frame (executed even before DOM ready)
	window.addEventListener('message', function(e){
		if (e.data && e.data.type === 'streportFilters') {
			var regs = (e.data.regions || []).map(normalizeRegionText);
			var yrs = e.data.years || [];
			// if only year filters provided, try to derive regions from parent.regionMap
			if (!regs.length && yrs.length) {
				try {
					var map = window.parent.regionMap || {};
					Object.keys(map).forEach(function(r){
						var ys = map[r].years || [];
						if (ys.some(y=> yrs.includes(y.toString()))) regs.push(r);
					});
				} catch(_){ }
			}
			filterSliderByRegions(regs);
			filterGalleryCards(regs, yrs);
			// update graph for the most recently selected region
			if (regs.length) {
				var target = regs[regs.length-1];
				// clear any cached payload so render runs fresh
				window._lastRsmPayload = null;
				var imgs = document.querySelectorAll('.slider-img');
				for (var i = 0; i < imgs.length; i++) {
					var nm = normalizeRegionText(imgs[i].getAttribute('data-region-name') || '');
					if (nm === target) {
						// if the slider library provides a click or activate method, use it;
						// otherwise just render stats directly
						try { imgs[i].click(); } catch(e) {}
						renderRegionStatsForImg(imgs[i]);
						break;
					}
				}
			}
		}
	});

	// legacy: click actions were previously disabled here. behaviour is now handled
	// further down by a dedicated listener that shows the region stats panel and
	// also notifies parent to adjust iframe height.

	// bottom preview: mirror the currently-centered slide into the bottom preview area
	function renderBottomProvinceList(payload) {
		try {
			const list = document.getElementById('sliderBottomProvinceList');
			const card = document.getElementById('sliderBottomProvinceListCard');
			if (!list || !card) return;
			const provinces = Array.isArray(payload && payload.provinces) ? payload.provinces : [];
			const rawGrouped = payload && payload.grouped ? payload.grouped : {};
		const grouped = normalizeGrouping(rawGrouped);
			if (!provinces.length) {
				list.innerHTML = '<div class="province-empty">No provinces found for this region.</div>';
				card.setAttribute('aria-hidden','true');
				return;
			}
			card.setAttribute('aria-hidden','false');
			const esc = s => (s||'').toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
			let html = '';
			provinces.forEach(prov => {
				let cityCount = 0;
				const cities = Object.keys(grouped[prov] || {});
				for (const c of cities) cityCount += (grouped[prov][c] || []).length;
				const label = (prov === 'UNKNOWN') ? '(no province specified)' : prov;
				html += `<div class="province-item"><div class="prov-name">${esc(label)}</div><div class="province-badge">${cityCount}</div></div>`;
			});
			list.innerHTML = html;
			// nothing else needed here

		} catch (e) { console.error('renderBottomProvinceList error', e); }
	}

	function updateSliderBottomPreview() {
		// when the slider center image moves (or user drags) we want the
		// parent STsReport iframe to collapse if it was expanded. the parent
		// listens for a `streportToggleHeight` message with an explicit
		// height of 600px, so post that here. repeated messages harmless.
		try {
			if (window.parent && window.parent !== window && window.parent.postMessage) {
				window.parent.postMessage({ type:'streportToggleHeight', height:'600px' }, '*');
			}
		} catch(_e) { /* ignore */ }
		// close any open gallery popover when the active region changes
		try { closePopover(); } catch(e) {}
		try {
			const activeImg = document.querySelector('.swiper-slide.swiper-slide-active .slider-img');
			const preview = document.getElementById('sliderBottomCopy');
			const label = document.getElementById('sliderBottomLabel');
			if (!preview) return;
			if (activeImg) {
				const src = activeImg.dataset.img || activeImg.src;
				let regionName = activeImg.getAttribute('data-region-name') || '';
				const regionNumber = activeImg.getAttribute('data-region-number') || '';
				preview.src = src;
				preview.alt = regionName || ('Region ' + regionNumber);
				preview.setAttribute('data-region-name', regionName);
				preview.setAttribute('data-region-number', regionNumber);
				if (label) label.textContent = regionName || ('Region ' + regionNumber);
				// emit custom event so graphing code can react
				document.dispatchEvent(new CustomEvent('sliderActiveRegionChanged', { detail: { src, regionName, regionNumber } }));

                // also log years right away for debugging (bypass listener timing issues)
                try {
                    if (!regionName) {
                        regionName = deriveRegionFromSlider({src, regionName, regionNumber});
                    }
                    if (regionName && window.parent && window.parent.regionMap) {
                        const norm = normalizeRegionText(regionName);
                        let map = window.parent.regionMap[norm];
                        if (!map) {
                            Object.keys(window.parent.regionMap || {}).some(k => {
                                if (normalizeRegionText(k) === norm) {
                                    map = window.parent.regionMap[k];
                                    return true;
                                }
                                return false;
                            });
                        }
                        const yrs = (map && map.years ? (map.years.slice().sort()) : []);
                        console.log('[RSM] immediate years for slider region', regionName, yrs);
                    }
                } catch(e) { console.warn('[RSM] year log error', e); }

				// fetch provinces for the active region and render into the bottom preview
				try {
					const fileName = (src || '').split('/').pop();
					const bottomList = document.getElementById('sliderBottomProvinceList');
					if (fileName && bottomList) {
						// map slider image filename -> region code expected by ajaxRegionHierarchy
						const filenameToRegion = {
							'1.png': 'Region I','2.png': 'Region II','3.png': 'Region III',
							'4_a.png': 'Region IV-A','4_b.png': 'Region IV-B','5.png': 'Region V',
							'6.png': 'Region VI','7.png': 'Region VII','8.png': 'Region VIII',
							'9.png': 'Region IX','10.png': 'Region X','11.png': 'Region XI',
							'12.png': 'Region XII',
					// map image for Region 13 to CARAGA (backend expects this key)
					'13.png': 'CARAGA',
					'barmm.png': 'BARMM',
							'car.png': 'CAR','ncr.png': 'NCR','nir.png': 'NIR'
						};
						const mappedRegion = filenameToRegion[fileName] || null;
						const regionParam = mappedRegion || (activeImg && activeImg.getAttribute('data-region-name')) || (activeImg && activeImg.getAttribute('data-region-number')) || fileName;
						bottomList.innerHTML = '<div class="province-empty">Loading…</div>';
						fetch('/sts-report/ajax-region-hierarchy?region_image=' + encodeURIComponent(regionParam))
							.then(r => { if (!r.ok) throw new Error('Network'); return r.json(); })
							.then(payload => renderBottomProvinceList(payload))
							.catch(err => { console.error('bottom province fetch', err); bottomList.innerHTML = '<div class="province-empty">Failed to load provinces</div>'; });
					}
				} catch(e) { console.error(e); }
			} else {
				preview.src = '';
				if (label) label.textContent = '';
				document.dispatchEvent(new CustomEvent('sliderActiveRegionChanged', { detail: null }));
			}
		} catch (e) { console.error(e); }
	}

	// helper to tell parent iframe to collapse
	function collapseStreportIframe(){
		try {
			if (window.parent && window.parent !== window && window.parent.postMessage) {
				window.parent.postMessage({ type:'streportToggleHeight', height:'600px' }, '*');
			}
		} catch(_e) { }
	}

	// initial sync + attach to Swiper events
	updateSliderBottomPreview();
	if (swiper && typeof swiper.on === 'function') {
		swiper.on('slideChangeTransitionEnd', updateSliderBottomPreview);
		swiper.on('init', updateSliderBottomPreview);
		// collapse immediately when a new slide starts moving (drag or arrow)
		swiper.on('slideChangeTransitionStart', collapseStreportIframe);
		// sometimes fast/forceful drags bypass transition events; the
		// `slideChange` callback fires whenever the active index changes.
		swiper.on('slideChange', collapseStreportIframe);
	}

	// helper that looks for an element first in the current document and
	// then in the parent frame (if same‑origin). this allows the modal
	// markup to live outside the iframe while the script continues to run
	// inside it.
	// NOTE: global fetchEl is already defined at the top of the file,
	// so we just refer to that rather than redeclare.
	// variant that accepts a CSS selector and queries both parent and local documents
	function fetchQS(selector) {
		if (window.parent && window.parent !== window && window.parent.document) {
			const pe = window.parent.document.querySelector(selector);
			if (pe) return pe;
		}
		return document.querySelector(selector);
	}

	const modal = fetchEl("sliderModal");
	const overlay = fetchEl("sliderModalOverlay");
	const modalContent = fetchEl("sliderModalContent");
	const modalViewport = fetchEl('sliderModalViewport');
	const modalImg = fetchEl("sliderModalImg");
	const closeBtn = null; // close button removed — keep variable falsey so existing checks remain safe
	const modalTitle = fetchEl("sliderModalTitle");
	
	// helper for region stats elements (also looked up in parent)
	// public API used in numerous handlers outside the DOMContentLoaded
	// callback, so define it at top level rather than inside the ready
	// listener. (global rsmEl already exists)
	


	// shared truthiness helper for parsed Excel fields (boolean or textual 'true')
	const truthy = v => (typeof v === 'boolean') ? v : (String(v || '').toLowerCase().trim() === 'true');
	let _titleTypingTimer = null;

	// baseline capture so we can neutralize browser zoom changes
	const _modalBaseDPR = window.devicePixelRatio || 1;
	const computeNominalDesktopWidth = w => Math.min(Math.round(w * 0.55 + 250), Math.max(w - 64, 520));
	const _modalBaseWidth = computeNominalDesktopWidth(window.innerWidth);

	function applyModalZoomFix() {
		if (!modal || !modalContent) return;
		const viewport = modalViewport || modalContent.querySelector('.slider-modal-viewport');
		const curDPR = window.devicePixelRatio || 1;
		const scaleFix = (_modalBaseDPR || 1) / curDPR; // inverse scale to neutralize browser zoom

		// set modalContent width to the baseline (clamped to viewport) so visual size is stable
		let width = _modalBaseWidth || computeNominalDesktopWidth(window.innerWidth);
		const maxAllowed = Math.max(window.innerWidth - 64, 520);
		width = Math.min(width, maxAllowed);
		modalContent.style.width = width + 'px';

		if (viewport) {
			viewport.style.transform = `scale(${scaleFix})`;
			viewport.style.transformOrigin = 'center center';
		}
	}

	// position the external Total-STs card so it visually sits to the right of the Provinces card
	function positionProvinceTotalCard() {
		try {
			const provCard = document.getElementById('sliderProvinceListCard');
			const totalCard = document.getElementById('sliderProvinceTotalCard');
			const modalRect = modalContent && modalContent.getBoundingClientRect ? modalContent.getBoundingClientRect() : { left:0, top:0, width: window.innerWidth };
			if (!provCard || !totalCard) return;

			// compute coordinates relative to modalContent — remove gap so the Total card sits nearly flush
			const pRect = provCard.getBoundingClientRect();
			const headerEl = provCard.querySelector('.province-card-title');
			const headerRect = headerEl && headerEl.getBoundingClientRect ? headerEl.getBoundingClientRect() : pRect;
			const gap = 0; // px — minimal spacing
			const unclampedLeft = (pRect.right - modalRect.left) + gap;
			// allow a very small clamp so the card never goes off-screen but otherwise sit flush
			const left = Math.min(Math.max(2, unclampedLeft), Math.max(2, modalRect.width - totalCard.offsetWidth - 2));

			// align Total card tightly with the province header
			const top = Math.max(2, headerRect.top - modalRect.top + 2);
			totalCard.style.left = left + 'px';
			totalCard.style.top = top + 'px';
		} catch (e) { /* ignore positioning errors */ }
	}

	// keep our position helper responsive
	window.addEventListener('resize', () => {
		try { positionProvinceTotalCard(); } catch(e){}
	});

	// human-friendly region lookup (used when image filename is numeric)
	const REGION_LOOKUP = {
		'1': 'Ilocos Region',
		'2': 'Cagayan Valley',
		'3': 'Central Luzon',
		'4a': 'Region IV-A – CALABARZON',
		'4b': 'Region IV-B – MIMAROPA',
		'5': 'Bicol Region',
		'6': 'Western Visayas',
		'7': 'Central Visayas',
		'8': 'Eastern Visayas',
		'9': 'Zamboanga Peninsula',
		'10': 'Northern Mindanao',
		'11': 'Davao Region',
		'12': 'SOCCSKSARGEN',
		'13': 'Caraga',
		'barmm': 'BARMM',
		'car': 'Cordillera Administrative Region',
		'ncr': 'National Capital Region',
		'nir': 'Negros Island Region'
	};

	function toRoman(num) {
		if (!num || num <= 0) return '';
		const vals = [[1000,'M'],[900,'CM'],[500,'D'],[400,'CD'],[100,'C'],[90,'XC'],[50,'L'],[40,'XL'],[10,'X'],[9,'IX'],[5,'V'],[4,'IV'],[1,'I']];
		let res = '';
		for (const [v,r] of vals) {
			while (num >= v) { res += r; num -= v; }
		}
		return res;
	}

	// modal title typing removed (function intentionally deleted)


	// track last opened image and its bounding rect so we can animate back on close
	let _lastActiveImgEl = null;
	let _lastActiveImgRect = null;

	// image‑preview modal helper using sliderModal elements
	function openImageModal(target) {
		if (!modal) return;
		const url = (target && (target.dataset.img || target.src)) || '';
		if (!url) return;
		modalImg.src = url;
		modal.style.display = 'block';
		modal.classList.add('expanded');
		// explicitly expand content in case CSS gets overridden
		if (modalContent) {
			modalContent.style.width = '90vw';
			modalContent.style.height = '90vh';
		}
		overlay.style.display = 'block';
		document.body.style.overflow = 'hidden';
		// tell parent to bump iframe height for the slider preview
		if (window.parent && window.parent !== window && window.parent.postMessage) {
			window.parent.postMessage({ type:'streportToggleHeight', height:'2000px' }, '*');
		}
	}

	function closeImageModal() {
		if (!modal) return;
		// notify parent that slider preview went away and iframe can shrink
		if (window.parent && window.parent !== window && window.parent.postMessage) {
			window.parent.postMessage({ type:'streportToggleHeight', height:'600px' }, '*');
		}
		modal.style.display = 'none';
		modal.classList.remove('expanded');
		if (modalContent) {
			modalContent.style.width = '';
			modalContent.style.height = '';
		}
		overlay.style.display = 'none';
		modalImg.src = '';
		document.body.style.overflow = '';
	}

	function closeImageModal() {
		if (!modal) return;
		// notify parent that slider preview went away and iframe can shrink
		if (window.parent && window.parent !== window && window.parent.postMessage) {
			window.parent.postMessage({ type:'streportToggleHeight', height:'600px' }, '*');
		}
		modal.style.display = 'none';
		modal.classList.remove('expanded');
		overlay.style.display = 'none';
		modalImg.src = '';
		document.body.style.overflow = '';
	}

	// wire overlay and escape key
	overlay && overlay.addEventListener('click', closeImageModal);
	document.addEventListener('keydown', function(e){ if (e.key === 'Escape') { closeImageModal(); } });


	(function enableModalPinchAndZoom(){

    /* --- Province-list helper (AJAX) ---------------------------------- */
    function renderProvinceCard(payload) {
        const card = document.getElementById('sliderProvinceListCard');
        const list = document.getElementById('sliderProvinceList');
        if (!card || !list) return;
        const provinces = Array.isArray(payload.provinces) ? payload.provinces : [];
        const rawGrouped = payload.grouped || {};
        const grouped = normalizeGrouping(rawGrouped);
        if (!provinces.length) {
            list.innerHTML = '<div class="province-empty">No provinces found for this region.</div>';
            card.setAttribute('aria-hidden','false');
            return;
        }
        const esc = s => (s||'').toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        let html = '';
        provinces.forEach(prov => {
            const cities = Object.keys(grouped[prov] || {});
            const cityCount = cities.length || 0;
            const displayProv = (prov === 'UNKNOWN') ? '(no province specified)' : prov;
            // render province as a button with caret — supports inline accordion expansion
            html += `<div class="province-item" role="button" tabindex="0" data-prov="${esc(prov)}"><div class="prov-name">${esc(displayProv)}</div><div class="province-badge">${cityCount}</div></div>`;
        });
        
        list.innerHTML = html;
        // keep main province list tall regardless of item count
        list.style.height = 'calc(40px * 8 + 16px)';
        // also lock card height in case CSS is overridden
        if(card) card.style.height = 'calc(40px * 8 + 16px + 40px)';
        card.setAttribute('aria-hidden','false');
        try { positionProvinceTotalCard(); } catch(e) { /* ignore */ }

        // update external Total-STs card + Expression-of-Interest
        try {
            const card = document.getElementById('sliderProvinceTotalCard');
            const stCountEl = document.getElementById('sliderProvinceTotalCardCount');
            const exprCard = document.getElementById('sliderProvinceExprCard');
            const exprCountEl = document.getElementById('sliderProvinceExprCardCount');
            const repCard = document.getElementById('sliderProvinceReplicatedCard');
            const repCountEl = document.getElementById('sliderProvinceReplicatedCardCount');
            const adCard = document.getElementById('sliderProvinceAdoptedCard');
            const adCountEl = document.getElementById('sliderProvinceAdoptedCardCount');

            let total = 0;
            if (payload && Array.isArray(payload.allRows)) total = payload.allRows.length;
            else if (payload && typeof payload.uploadedCount === 'number') total = payload.uploadedCount;
            else {
                // fallback: sum grouped counts
                total = provinces.reduce((acc, p) => {
                    const citiesForProv = grouped[p] || {};
                    for (const k in citiesForProv) acc += (citiesForProv[k] || []).length;
                    return acc;
                }, 0);
            }

            // Expression-of-Interest count (only available when server returns allRows)
            let exprTotal = 0;
            if (payload && Array.isArray(payload.allRows)) {
                exprTotal = payload.allRows.reduce((acc, r) => {
                    const v = r && r.with_expr;
                    const flag = (typeof v === 'boolean') ? v : (String(v||'').toLowerCase().trim() === 'true');
                    return acc + (flag ? 1 : 0);
                }, 0);
            }

            // new totals are dummy, always show zero
            const replicatedTotal = 0;
            const adoptedTotal = 0;

            if (stCountEl) stCountEl.textContent = String(total || 0);
            if (card) card.setAttribute('aria-hidden', total ? 'false' : 'true');

            if (exprCountEl) exprCountEl.textContent = String(exprTotal || 0);
            if (exprCard) exprCard.setAttribute('aria-hidden', exprTotal ? 'false' : 'true');

            if (repCountEl) repCountEl.textContent = String(replicatedTotal);
            if (repCard) repCard.setAttribute('aria-hidden', 'false');

            if (adCountEl) adCountEl.textContent = String(adoptedTotal);
            if (adCard) adCard.setAttribute('aria-hidden', 'false');

            try { positionProvinceTotalCard(); } catch(e) {}
        } catch(e) { /* ignore */ }

        // attach click + keyboard handler to expand/collapse province (inline accordion)
        Array.from(list.querySelectorAll('.province-item')).forEach(el => {
            function _toggleProv(ev){
                ev.stopPropagation();
                const prov = el.getAttribute('data-prov');
                showCitiesInProvince(el, prov, grouped[prov] || {});
            }
            el.addEventListener('click', _toggleProv);
            el.addEventListener('keydown', (k) => { if (k.key === 'Enter' || k.key === ' ') { k.preventDefault(); _toggleProv(k); }});
        });
    }

    function showCitiesInProvince(provEl, prov, citiesObj) {
        // New behavior: selecting a province hides *all other provinces* and shows
        // its city list inline (single-focused dropdown). Cities behave the same.
        const card = document.getElementById('sliderProvinceListCard');
        const list = document.getElementById('sliderProvinceList');
        if (!card || !list || !provEl) return;
        const esc = s => (s||'').toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

        // helper: animate collapse + remove a sublist node
        function collapseAndRemove(node) {
            return new Promise(resolve => {
                try {
                    if (!node || !node.parentNode) return resolve();
                    node.style.maxHeight = node.scrollHeight + 'px';
                    node.getBoundingClientRect();
                    node.style.transition = 'max-height 320ms cubic-bezier(.2,.8,.2,1), opacity 220ms ease, transform 200ms ease';
                    node.style.maxHeight = '0px';
                    node.style.opacity = '0';
                    node.classList.remove('open');
                    const onEnd = (ev) => { if (ev.propertyName === 'max-height') { node.removeEventListener('transitionend', onEnd); try { if (node.parentNode) node.parentNode.removeChild(node); } catch(e){} resolve(); } };
                    node.addEventListener('transitionend', onEnd);
                    setTimeout(()=>{ try { if (node.parentNode) node.parentNode.removeChild(node); } catch(e){} resolve(); }, 480);
                } catch(e) { resolve(); }
            });
        }

        // toggle: if already expanded, collapse and restore all provinces
        if (provEl.classList.contains('expanded')) {
            provEl.classList.remove('expanded');
            provEl.setAttribute('aria-expanded','false');
            const next = provEl.nextElementSibling;
            if (next && next.classList.contains('province-sublist')) {
                // animate collapse then restore siblings
                collapseAndRemove(next).then(() => {
                    Array.from(list.querySelectorAll('.province-item.hidden')).forEach(x => { x.classList.remove('hidden'); x.style.display = ''; });
                    provEl.focus();
                });
                return;
            }
            Array.from(list.querySelectorAll('.province-item.hidden')).forEach(x => { x.classList.remove('hidden'); x.style.display = ''; });
            provEl.focus();
            return;
        }

        // hide all other provinces (single-focus mode) — add inline style as a defensive fallback
        Array.from(list.querySelectorAll('.province-item')).forEach(pi => {
            if (pi !== provEl) { pi.classList.add('hidden'); pi.style.display = 'none'; }
            else { pi.classList.remove('hidden'); pi.style.display = ''; }
        });

        // remove any existing sublists (animate collapse)
        Array.from(list.querySelectorAll('.province-sublist')).forEach(s => { try { collapseAndRemove(s); } catch(e) { try { s.remove(); } catch(e){} } });

        // build focused sublist (header shows province — back-to-all removed per UI change)
        const cities = Object.keys(citiesObj || {});
        const sub = document.createElement('div');
        sub.className = 'province-sublist';
        // header center uses .sublist-title — show a static prompt instead of the province/city name
        let html = `<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;"><div></div><div class="sublist-title" style="font-weight:700;color:#0f1724;">Choose City</div><div></div></div>`;

        if (!cities.length) {
            html += '<div class="province-empty">No cities found for this province.</div>';
            sub.innerHTML = html;
            provEl.parentNode.insertBefore(sub, provEl.nextSibling);
            provEl.classList.add('expanded'); provEl.setAttribute('aria-expanded','true');
            return;
        }

        html += '<div class="city-list">';
        cities.forEach(c => {
            const key = (c||'').toString().trim();
            const rows = (citiesObj[key] || []);
            const displayCity = (c === 'UNKNOWN') ? '(no city specified)' : c;
            html += `<div class="city-item" role="button" tabindex="0" data-prov="${esc(prov)}" data-city="${esc(key)}"><div class="prov-name">${esc(displayCity)}</div><div class="province-badge">${rows.length || 0}</div></div>`;
        });
        html += '</div>';
        html += '<div class="st-list" style="margin-top:8px;"></div>';
        sub.innerHTML = html;

        // insert and mark expanded
        provEl.parentNode.insertBefore(sub, provEl.nextSibling);
        provEl.classList.add('expanded'); provEl.setAttribute('aria-expanded','true');

        // animate expand (slide + fade)
        try {
            sub.style.overflow = 'hidden';
            sub.style.maxHeight = '0px';
            sub.style.opacity = '0';
            sub.style.transform = 'translateY(-6px)';
            requestAnimationFrame(() => {
                const full = sub.scrollHeight;
                sub.style.transition = 'max-height 320ms cubic-bezier(.2,.8,.2,1), opacity 220ms ease, transform 200ms ease';
                sub.style.maxHeight = Math.max(64, full) + 'px';
                sub.style.opacity = '1';
                sub.style.transform = 'translateY(0)';
                // stagger reveal of city items
                Array.from(sub.querySelectorAll('.city-item')).forEach((it, i) => { it.style.transitionDelay = (i * 34) + 'ms'; });
                // clear max-height after transition so content can grow naturally
                const tidy = (ev) => { if (ev.propertyName === 'max-height') { sub.style.maxHeight = ''; sub.removeEventListener('transitionend', tidy); } };
                sub.addEventListener('transitionend', tidy);
                sub.classList.add('open');
            });
        } catch(e){}

        /* "All provinces" back-control removed per request; collapse the province element to return to province list */

        // city click handlers — selecting a city will hide other cities (dropdown behavior)
        const cityItems = Array.from(sub.querySelectorAll('.city-item'));
        cityItems.forEach(ci => {
            const renderSTs = () => {
                // IMPORTANT: do NOT populate or modify ST Titles from province/city interactions.
                // ST Titles are managed in their dedicated panel and must remain unchanged.
                const stContainer = sub.querySelector('.st-list');
                if (!stContainer) return;
                stContainer.innerHTML = '<div class="province-empty">ST Titles are managed in the ST Titles panel and are not affected by province selection.</div>';
            };

            ci.addEventListener('click', ev => {
                ev.stopPropagation();
                // toggle selection: if already selected, behave as city-back
                if (ci.classList.contains('selected')) {
                    cityItems.forEach(x => { x.classList.remove('hidden','selected'); x.style.display = ''; });
                    const sc = sub.querySelector('.st-list'); if (sc) sc.innerHTML = '';
                    // remove the inline ST header
                    try { const hdr = sub.querySelector('.st-list-header'); if (hdr) hdr.remove(); } catch(e){}
                    // restore header back to 'Choose City' and make it visible again when deselecting a city
                    try { const stitle = sub.querySelector('.sublist-title'); if (stitle) { stitle.textContent = 'Choose City'; stitle.style.display = ''; } } catch(e){}
                    return;
                }

                // hide sibling cities (dropdown) and mark selected
                cityItems.forEach(x => {
                    if (x !== ci) { x.classList.add('hidden'); x.style.display = 'none'; x.classList.remove('selected'); }
                    else { x.classList.remove('hidden'); x.style.display = ''; x.classList.add('selected'); }
                });
                renderSTs();
            });

            ci.addEventListener('keydown', k => { if (k.key === 'Enter' || k.key === ' ') { k.preventDefault(); ci.click(); }});
        });

        // focus first city for keyboard users
        const firstCity = sub.querySelector('.city-item'); if (firstCity) firstCity.focus();
    }

    function fetchModalProvinces(regionKey) {
        const list = document.getElementById('sliderProvinceList');
        const card = document.getElementById('sliderProvinceListCard');
        if (!list || !card) return;
        list.innerHTML = '<div class="province-empty">Loading…</div>';
        card.setAttribute('aria-hidden','false');
        try { const card = document.getElementById('sliderProvinceTotalCard'); const stCountEl = document.getElementById('sliderProvinceTotalCardCount'); const exprCard = document.getElementById('sliderProvinceExprCard'); const exprCountEl = document.getElementById('sliderProvinceExprCardCount'); const repCard = document.getElementById('sliderProvinceReplicatedCard'); const repCountEl = document.getElementById('sliderProvinceReplicatedCardCount'); const adCard = document.getElementById('sliderProvinceAdoptedCard'); const adCountEl = document.getElementById('sliderProvinceAdoptedCardCount'); if (card) card.setAttribute('aria-hidden','false'); if (stCountEl) stCountEl.textContent = '…'; if (exprCard) exprCard.setAttribute('aria-hidden','false'); if (exprCountEl) exprCountEl.textContent = '…'; if (repCard) repCard.setAttribute('aria-hidden','false'); if (repCountEl) repCountEl.textContent = '…'; if (adCard) adCard.setAttribute('aria-hidden','false'); if (adCountEl) adCountEl.textContent = '…'; positionProvinceTotalCard(); } catch(e) {}
        // Use demo1 AJAX endpoint which returns {provinces, grouped, allRows, uploadedCount...}
        fetch('/sts-report/ajax-region-hierarchy?region_image=' + encodeURIComponent(regionKey))
            .then(r => { if (!r.ok) throw new Error('Network'); return r.json(); })
            .then(payload => { try { window._lastProvincePayload = payload; } catch(e){}; return renderProvinceCard(payload); })
.catch(err => { console.error('fetchModalProvinces error', err); list.innerHTML = '<div class="province-empty">Failed to load provinces</div>'; try { const card = document.getElementById('sliderProvinceTotalCard'); if (card) card.setAttribute('aria-hidden','true'); const stCountEl = document.getElementById('sliderProvinceTotalCardCount'); if (stCountEl) stCountEl.textContent = ''; const exprCard = document.getElementById('sliderProvinceExprCard'); if (exprCard) exprCard.setAttribute('aria-hidden','true'); const exprCountEl = document.getElementById('sliderProvinceExprCardCount'); if (exprCountEl) exprCountEl.textContent = ''; const repCard = document.getElementById('sliderProvinceReplicatedCard'); if (repCard) repCard.setAttribute('aria-hidden','true'); const repCountEl = document.getElementById('sliderProvinceReplicatedCardCount'); if (repCountEl) repCountEl.textContent = ''; const adCard = document.getElementById('sliderProvinceAdoptedCard'); if (adCard) adCard.setAttribute('aria-hidden','true'); const adCountEl = document.getElementById('sliderProvinceAdoptedCardCount'); if (adCountEl) adCountEl.textContent = ''; } catch(e) {} });
    }

    // expose helper globally so openImageModal (outer scope) can call it
    try { window.fetchModalProvinces = fetchModalProvinces; window.renderProvinceCard = renderProvinceCard; } catch(e) {}

    // transient popover used for ST click feedback (non-navigating)
    function showTransientPopover(targetEl, text, opts = {}) {
        try {
            const existing = document.querySelector('.slider-province-card .transient-popover');
            if (existing) existing.remove();
            const pop = document.createElement('div');
            pop.className = 'transient-popover';
            pop.textContent = text || '';
            const card = document.getElementById('sliderProvinceListCard') || document.body;
            card.appendChild(pop);
            const r = targetEl.getBoundingClientRect();
            const cr = card.getBoundingClientRect();
            pop.style.position = 'absolute';
            pop.style.left = Math.max(8, (r.left - cr.left) + (r.width/2) - 60) + 'px';
            pop.style.top  = Math.max(8, (r.top - cr.top) - 36) + 'px';
            requestAnimationFrame(()=> pop.classList.add('show'));
            setTimeout(()=> { pop.classList.remove('show'); setTimeout(()=> pop.remove(), 240); }, opts.duration || 1500);
        } catch(e){}
    }

    // confirm / cancel popover for replicating an ST
    function showReplicateConfirmPopover(targetEl, stInfo = {}) {
        try {
            // remove any existing popover first
            const existing = document.body.querySelector('.replicate-popover');
            if (existing) existing.remove();

            const pop = document.createElement('div');
            // inline styling so no external CSS dependency
            pop.style.position = 'fixed';
            pop.style.zIndex = '2147483647';
            pop.style.width = '220px';
            pop.style.background = 'linear-gradient(180deg,#0b1220, #0f1724)';
            pop.style.color = '#e6eef1';
            pop.style.padding = '10px';
            pop.style.borderRadius = '8px';
            pop.style.fontSize = '0.92rem';
            pop.style.boxShadow = '0 12px 40px rgba(2,6,23,0.6)';
            pop.style.pointerEvents = 'auto';
            pop.style.opacity = '0';
            pop.style.transform = 'translateY(-6px) scale(0.98)';
            pop.style.transition = 'opacity 160ms ease, transform 160ms ease';

            const title = (stInfo && stInfo.title) ? stInfo.title : '';
            pop.innerHTML = `<div class="rp-msg">Replicate "${(title||'').replace(/</g,'&lt;').replace(/>/g,'&gt;')}"?</div><div class="rp-actions"><button class="rp-confirm">Confirm</button><button class="rp-cancel">Cancel</button></div>`;
            document.body.appendChild(pop);

            // style action buttons
            try {
                const cb = pop.querySelector('.rp-confirm');
                const xb = pop.querySelector('.rp-cancel');
                if (cb) {
                    cb.style.background = '#10b981';
                    cb.style.color = '#042f2e';
                    cb.style.border = 'none';
                    cb.style.padding = '6px 10px';
                    cb.style.borderRadius = '6px';
                    cb.style.cursor = 'pointer';
                    cb.style.fontWeight = '600';
                }
                if (xb) {
                    xb.style.background = 'transparent';
                    xb.style.color = '#9ca3af';
                    xb.style.border = '1px solid rgba(255,255,255,0.03)';
                    xb.style.padding = '6px 10px';
                    xb.style.borderRadius = '6px';
                    xb.style.cursor = 'pointer';
                }
            } catch(e) {}

            // positioning
            const r = targetEl.getBoundingClientRect();
            const measure = () => {
                const pw = pop.offsetWidth;
                const ph = pop.offsetHeight;
                let left = r.left + (r.width/2) - (pw/2);
                let top = r.top - ph - 8; // prefer above
                if (top < 8) top = r.bottom + 8; // fallback below
                left = Math.max(8, Math.min(left, window.innerWidth - pw - 8));
                pop.style.left = left + 'px';
                pop.style.top = top + 'px';
            };
            requestAnimationFrame(() => { measure();
                pop.style.opacity = '1';
                pop.style.transform = 'translateY(0) scale(1)';
            });

            const confirmBtn = pop.querySelector('.rp-confirm');
            const cancelBtn = pop.querySelector('.rp-cancel');
            const cleanup = () => { try {
                    pop.style.pointerEvents = 'none';
                    pop.style.opacity = '0';
                    pop.style.transform = 'translateY(-6px) scale(0.98)';
                    setTimeout(()=> pop.remove(), 180);
                    document.removeEventListener('keydown', onKey);
                } catch(e){} };
            const onKey = (ev) => { if (ev.key === 'Escape') { ev.preventDefault(); cleanup(); } else if (ev.key === 'Enter') { ev.preventDefault(); confirmBtn.click(); } };

            function doConfirm() {
                try {
                    const handler = window.replicateST;
                    const payload = stInfo.row || stInfo;
                    if (typeof handler === 'function') {
                        const result = handler(payload, targetEl);
                        if (result && typeof result.then === 'function') {
                            confirmBtn.disabled = true; cancelBtn.disabled = true;
                            result.then(res => { cleanup(); showTransientPopover(targetEl, 'Replication started'); }).catch(err => { cleanup(); showTransientPopover(targetEl, 'Replication failed'); console.error(err); });
                            return;
                        }
                    }
                    cleanup();
                    showTransientPopover(targetEl, 'Replication started');
                } catch(err) { cleanup(); console.error(err); showTransientPopover(targetEl, 'Replication failed'); }
            }

            confirmBtn.addEventListener('click', doConfirm);
            cancelBtn.addEventListener('click', (e) => { e.stopPropagation(); cleanup(); });
            requestAnimationFrame(()=> confirmBtn.focus());
            document.addEventListener('keydown', onKey);
        } catch(e) {}
    }

    // default stub — consumers may override window.replicateST with a real implementation
    try { window.replicateST = window.replicateST || function(payload, sourceEl){ return new Promise(res => setTimeout(()=> res({ ok:true }), 600)); }; } catch(e) {}



// modal image gesture/zoom handlers removed — province helpers preserved
	// keep a harmless stub so code that checks for modal.resetZoom won't fail
	try { modal.resetZoom = function(){}; } catch(e) {}
	})();



	/* global modal zoom removed — no-op stub retained */
	try { modal.resetGlobalZoom = function(){}; } catch(e) {}


	// click handlers: open only for the active slide; clicking non-active slide will navigate to it
	function renderRegionStatsForImg(img){
		try {
			if (!img) return;
			const src = img.dataset.img || img.src || '';
			const fileName = (src || '').split('/').pop();
			const filenameToRegion = {
				'1.png': 'Region I','2.png': 'Region II','3.png': 'Region III',
				'4_a.png': 'Region IV-A','4_b.png': 'Region IV-B','5.png': 'Region V',
				'6.png': 'Region VI','7.png': 'Region VII','8.png': 'Region VIII',
				'9.png': 'Region IX','10.png': 'Region X','11.png': 'Region XI',
				'12.png': 'Region XII',
						// region XIII stored as CARAGA in dataset
						'13.png': 'CARAGA','barmm.png': 'BARMM',
				'car.png': 'CAR','ncr.png': 'NCR','nir.png': 'NIR'
			};
			const mappedRegion = filenameToRegion[fileName] || null;
			const regionParam = mappedRegion || img.getAttribute('data-region-name') || img.getAttribute('data-region-number') || fileName;

			// no modal wrappers – simply update image and data
			const titleEl = rsmEl('rsm-modal-image');
			if (titleEl) {
				const srcFull = src || img.src || '';
				titleEl.src = srcFull;
				titleEl.style.visibility = 'visible';
			}

// show modal image immediately (animation removed)
            try {
                const modalImgEl = rsmEl('rsm-modal-image');
                const srcFull = src || img.src || '';
                if (modalImgEl) {
                    modalImgEl.src = srcFull;
                    modalImgEl.style.visibility = 'visible';
                }
            } catch(e) { console.error('rsm animation removed', e); try { const ri = rsmEl('rsm-modal-image'); if (ri) { ri.src = src || ''; ri.style.visibility = 'visible'; } } catch(e){} }


			// show loader, hide content while fetching
			rsmEl('rsm-loading').style.display = ''; 
			document.getElementById('rsm-cards').style.display = 'none';
			rsmEl('rsm-st-list').innerHTML = '<div class="rsm-empty">Select a city to view ST titles</div>'; 

			// fetch aggregated JSON for the region (provinces, grouped, rows, uploads, per-year totals)
			fetch('/sts-report/ajax-region-hierarchy?region_image=' + encodeURIComponent(regionParam))
				.then(r => { if (!r.ok) throw new Error('Network'); return r.json(); })
				.then(payload => {
					// populate provinces list
					const provEl = rsmEl('rsm-provinces');
					if (provEl) {
					const provincesArr = Array.isArray(payload.provinces) ? payload.provinces : [];
					const rawGrouped = payload.grouped || {};
		const grouped = normalizeGrouping(rawGrouped);
					const esc = s => (s||'').toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
					if (!provincesArr.length) {
						provEl.innerHTML = '<div class="rsm-empty">No provinces</div>';
						provEl.setAttribute('aria-hidden','true');
					} else {
						provEl.innerHTML = provincesArr.map(p => {
							const cities = Object.keys(grouped[p] || {});
							return `<div class="rsm-prov-item province-item" role="button" tabindex="0" data-prov="${esc(p)}"><div class="prov-name">${esc(p)}</div><div class="province-badge">${cities.length}</div></div>`;
						}).join('');
						provEl.setAttribute('aria-hidden','false');

						// store last payload for quick lookup when interacting with provinces/cities
						window._lastRsmPayload = payload;

						// capture-phase handler: ensure city clicks always render inline STs (runs before bubble handlers that may stopPropagation)
						try {
							if (!provEl.dataset.cityCaptureBound) {
								provEl.addEventListener('click', function(ev){
									const ci = ev.target.closest('.city-item');
									if (!ci || !provEl.contains(ci)) return;
									const sub = ci.closest('.province-sublist');
									if (!sub) return;
									const provName = ci.getAttribute('data-prov');
									const city = (ci.getAttribute('data-city')||'').trim();
									let rows = (grouped[provName] && grouped[provName][city]) ? grouped[provName][city] : [];
									if (!rows.length && window._lastProvincePayload && Array.isArray(window._lastProvincePayload.allRows)) {
										const provKey = provName.toString().trim().toLowerCase();
										const cityKey = city.toString().trim().toLowerCase();
										rows = window._lastProvincePayload.allRows.filter(r => {
											const rp = (r.province||'').toString().trim().toLowerCase();
											const rc = (r.city||'').toString().trim().toLowerCase();
											return rp === provKey && rc === cityKey;
										});
									}

									const cityItems = Array.from(sub.querySelectorAll('.city-item'));
									// toggle off (restore)
									if (ci.classList.contains('selected')) {
										cityItems.forEach(x => { x.classList.remove('hidden','selected'); x.style.display = ''; });
										const sc = sub.querySelector('.st-list'); if (sc) sc.innerHTML = '';
										try { const hdr = sub.querySelector('.st-list-header'); if (hdr) hdr.remove(); } catch(e){}
										return;
									}
									// single-focus: hide siblings and mark selected
									cityItems.forEach(x => { if (x !== ci) { x.classList.add('hidden'); x.style.display = 'none'; x.classList.remove('selected'); } else { x.classList.remove('hidden'); x.style.display = ''; x.classList.add('selected'); } });
									const stContainer = sub.querySelector('.st-list');
									if (!stContainer) return;
									if (!rows.length) { stContainer.innerHTML = '<div class="province-empty">No STs for this city</div>'; try { const hdr = sub.querySelector('.st-list-header'); if (hdr) hdr.remove(); } catch(e){} return; }
									let sHtml = '<div style="max-height:220px;overflow:auto;display:flex;flex-direction:column;gap:6px;">';
									rows.forEach(r => { sHtml += `<div class="st-item" role="button" tabindex="0" title="${esc(r.title)}" style="padding:8px;border-radius:6px;background:transparent;cursor:pointer;">${esc(r.title || '(no title)')}</div>`; });
									sHtml += '</div>';
									stContainer.innerHTML = sHtml;
									try { let hdr = sub.querySelector('.st-list-header'); if (!hdr) { hdr = document.createElement('div'); hdr.className = 'st-list-header'; const cityListNode = sub.querySelector('.city-list'); if (cityListNode) cityListNode.insertAdjacentElement('afterend', hdr); else sub.insertBefore(hdr, stContainer); } hdr.textContent = 'List of STs'; } catch(e) {}
									try { const stItems = Array.from(stContainer.querySelectorAll('.st-item')); stItems.forEach(el => el.classList.remove('show')); stItems.forEach((el, i) => setTimeout(()=> el.classList.add('show'), 60 + i * 34)); } catch(e) {}
									try { Array.from(stContainer.querySelectorAll('.st-item')).forEach((si, idx) => { si.addEventListener('click', ev3 => { ev3.stopPropagation(); const row = rows[idx] || { title: si.textContent }; showReplicateConfirmPopover(si, { province: provName, city: city, title: row.title || si.textContent, row }); }); si.addEventListener('keydown', k => { if (k.key === 'Enter' || k.key === ' ') { k.preventDefault(); si.click(); } }); }); } catch(e) {}
								}, true);
								provEl.dataset.cityCaptureBound = '1';
							}
						} catch(e) {}

						// delegated city click handler for the Provinces card — expand city row and render STs inline (provinces card only)
						if (!provEl.dataset.cityDelegateBound) {
							provEl.addEventListener('click', function(ev) {
								const ci = ev.target.closest('.city-item');
								if (!ci || !provEl.contains(ci)) return;
								ev.stopPropagation();
								const sub = ci.closest('.province-sublist');
								if (!sub) return;
								const provName = ci.getAttribute('data-prov');
								const city = (ci.getAttribute('data-city')||'').trim();
								let rows = (grouped[provName] && grouped[provName][city]) ? grouped[provName][city] : [];
								if (!rows.length && window._lastProvincePayload && Array.isArray(window._lastProvincePayload.allRows)) {
									rows = window._lastProvincePayload.allRows.filter(r => {
										const rp = (r.province||'').toString().trim();
										const rc = (r.city||'').toString().trim();
										return rp === provName && rc === city;
										});
								}

								const cityItems = Array.from(sub.querySelectorAll('.city-item'));
								// toggle off (restore)
								if (ci.classList.contains('selected')) {
									cityItems.forEach(x => { x.classList.remove('hidden','selected'); x.style.display = ''; });
									const sc = sub.querySelector('.st-list'); if (sc) sc.innerHTML = '';
									try { const hdr = sub.querySelector('.st-list-header'); if (hdr) hdr.remove(); } catch(e){}
									return;
								}
								// single-focus: hide siblings and mark selected
						cityItems.forEach(x => {
							if (x !== ci) { x.classList.add('hidden'); x.style.display = 'none'; x.classList.remove('selected'); }
							else { x.classList.remove('hidden'); x.style.display = ''; x.classList.add('selected'); }
						});
								const stContainer = sub.querySelector('.st-list');
								if (!stContainer) return;
								if (!rows.length) { stContainer.innerHTML = '<div class="province-empty">No STs for this city</div>'; try { const hdr = sub.querySelector('.st-list-header'); if (hdr) hdr.remove(); } catch(e){} return; }

								let sHtml = '<div style="max-height:220px;overflow:auto;display:flex;flex-direction:column;gap:6px;">';
								rows.forEach(r => { sHtml += `<div class="st-item" role="button" tabindex="0" title="${esc(r.title)}" style="padding:8px;border-radius:6px;background:transparent;cursor:pointer;">${esc(r.title || '(no title)')}</div>`; });
								sHtml += '</div>';
								stContainer.innerHTML = sHtml;

								try { let hdr = sub.querySelector('.st-list-header'); if (!hdr) { hdr = document.createElement('div'); hdr.className = 'st-list-header'; const cityListNode = sub.querySelector('.city-list'); if (cityListNode) cityListNode.insertAdjacentElement('afterend', hdr); else sub.insertBefore(hdr, stContainer); } hdr.textContent = 'List of STs'; } catch(e) {}
								try { const stItems = Array.from(stContainer.querySelectorAll('.st-item')); stItems.forEach(el => el.classList.remove('show')); stItems.forEach((el, i) => setTimeout(()=> el.classList.add('show'), 60 + i * 34)); } catch(e) {}
								try { Array.from(stContainer.querySelectorAll('.st-item')).forEach((si, idx) => { si.addEventListener('click', ev3 => { ev3.stopPropagation(); const row = rows[idx] || { title: si.textContent }; showReplicateConfirmPopover(si, { province: provName, city: city, title: row.title || si.textContent, row }); }); si.addEventListener('keydown', k => { if (k.key === 'Enter' || k.key === ' ') { k.preventDefault(); si.click(); } }); }); } catch(e) {}
							});
							provEl.dataset.cityDelegateBound = '1';
						}

						// attach expand/click handlers: provinces -> cities -> STs
						Array.from(provEl.querySelectorAll('.province-item')).forEach(pi => {
							pi.addEventListener('click', function(ev){
								ev.stopPropagation();
								const prov = this.getAttribute('data-prov');
								// collapse if already expanded
								const next = this.nextElementSibling;
								if (next && next.classList && next.classList.contains('province-sublist')) {
									// remove sublist and restore all provinces + clear ST listing
									next.remove();
									this.classList.remove('expanded');
									this.setAttribute('aria-expanded','false');
									Array.from(provEl.querySelectorAll('.province-item')).forEach(x => { x.classList.remove('hidden','selected'); x.style.display = ''; });
									
									return;
								}
								// remove other sublists and mark this expanded
								Array.from(provEl.querySelectorAll('.province-sublist')).forEach(s => s.remove());
								Array.from(provEl.querySelectorAll('.province-item.expanded')).forEach(x => { x.classList.remove('expanded'); x.setAttribute('aria-expanded','false'); });
								this.classList.add('expanded');
								this.setAttribute('aria-expanded','true');

								// hide all other provinces (single-focus mode)
								Array.from(provEl.querySelectorAll('.province-item')).forEach(pi2 => {
									if (pi2 !== this) { pi2.classList.add('hidden'); pi2.style.display = 'none'; }
									else { pi2.classList.remove('hidden'); pi2.style.display = ''; pi2.classList.add('selected'); }
								});

								const citiesObj = grouped[prov] || {};
								const cityNames = Object.keys(citiesObj);
					// include an inline ST container so city clicks expand in-place (provinces card only)
					const subHtml = cityNames.length ? `<div class="province-sublist">${cityNames.map(cn => { const key=(cn||'').toString().trim(); const displayCity = (cn === 'UNKNOWN') ? '(no city specified)' : cn; return `<div class="city-item" role="button" tabindex="0" data-prov="${esc(prov)}" data-city="${esc(key)}"><div class="city-name">${esc(displayCity)}</div><div class="province-badge">${(citiesObj[key]||[]).length}</div></div>` }).join('')}<div class="st-list" style="margin-top:8px;"></div></div>` : `<div class="province-sublist"><div class="province-empty">No cities</div><div class="st-list" style="margin-top:8px;"></div></div>`;
					// insert the sublist so city items are visible when a province is expanded
					this.insertAdjacentHTML('afterend', subHtml);
								// attach city handlers (render STs into right-side listing) — hide other cities when one selected
								const sub = this.nextElementSibling;
								if (sub) {
									const cityItems = Array.from(sub.querySelectorAll('.city-item'));
									cityItems.forEach(ci => {
										const renderSTs = () => {
								// NO-OP: do not update the global ST Titles panel from province/city interactions.
								// ST Titles must remain unchanged.
								return;
							};
											ci.addEventListener('click', ev2 => {

											if (ci.classList.contains('selected')) {
												cityItems.forEach(x => { x.classList.remove('hidden','selected'); x.style.display = ''; });
												
												return;
											}
											// hide sibling cities and mark selected
											cityItems.forEach(x => {
									if (x !== ci) { x.classList.add('hidden'); x.style.display = 'none'; x.classList.remove('selected'); }
									else { x.classList.remove('hidden'); x.style.display = ''; x.classList.add('selected'); }
								});
											renderSTs();
										});
										ci.addEventListener('keydown', k => { if (k.key === 'Enter' || k.key === ' ') { k.preventDefault(); ci.click(); } });
									});
									// focus first city for keyboard users
									const firstCity = sub.querySelector('.city-item'); if (firstCity) firstCity.focus();
								}
							});
							pi.addEventListener('keydown', k => { if (k.key === 'Enter' || k.key === ' ') { k.preventDefault(); pi.click(); } });
						});
					}}

					// totals
					const allRows = Array.isArray(payload.allRows) ? payload.allRows : [];
					rsmEl('rsm-total-sts').textContent = String(allRows.length || 0);
				try { const modalTotal = document.getElementById('modalStatsTotal'); if (modalTotal) modalTotal.textContent = `Total STs: ${allRows.length || 0}`; } catch(e) {}
					rsmEl('rsm-total-moa').textContent = String(allRows.reduce((acc,r) => acc + (truthy(r.with_moa) ? 1 : 0), 0));
					rsmEl('rsm-total-res').textContent = String(allRows.reduce((acc,r) => acc + (truthy(r.with_res) ? 1 : 0), 0));
				rsmEl('rsm-total-expr').textContent = String(allRows.reduce((acc,r) => acc + (truthy(r.with_expr) ? 1 : 0), 0));
					// Total MOA Attachment — sum uploaded counts across perYearTotals (attachments for MOA years)
					let moaAttachments = 0;
					if (payload.perYearTotals) {
						Object.values(payload.perYearTotals).forEach(arr => { moaAttachments += Number((arr && arr[1]) || 0); });
					} else moaAttachments = Number(payload.uploadedCount || 0);
					rsmEl('rsm-total-moa-attachments').textContent = String(moaAttachments || 0);

				// --- build chart values (X order requested: Uploaded MOA, Total MOA, SB Resolution, Expression of Interest)
					// prepare a container outside the try so it's always in scope
					let _chartData = { base: [], perYear: null };
					try {
						const totalMoa = allRows.reduce((acc,r) => acc + (truthy(r.with_moa) ? 1 : 0), 0);
						const totalRes = allRows.reduce((acc,r) => acc + (truthy(r.with_res) ? 1 : 0), 0);
					// keep the standalone SB-resolution stat card up to date too
					rsmEl('rsm-total-res').textContent = String(totalRes);
						const totalExpr = allRows.reduce((acc,r) => acc + (truthy(r.with_expr) ? 1 : 0), 0);
						const baseValuesOrdered = [ Number(moaAttachments || 0), Number(totalMoa || 0), Number(totalRes || 0), Number(totalExpr || 0) ];
						let perYearTransformed = null;
						if (payload.perYearTotals) {
							perYearTransformed = {};
							Object.keys(payload.perYearTotals).forEach(y => {
								const arr = payload.perYearTotals[y] || [0,0,0,0]; // [moaY, uploadedY, exprY, resY]
								perYearTransformed[y] = [ Number(arr[1]||0), Number(arr[0]||0), Number(arr[3]||0), Number(arr[2]||0) ];
							});
						}
						// postpone chart rendering until after metrics cards are visible
						_chartData = {base: baseValuesOrdered, perYear: perYearTransformed};
					} catch(e) { console.error('modal chart error', e); }

						document.getElementById('rsm-loading').style.display = 'none';
						const _cardsEl = rsmEl('rsm-cards');
						_cardsEl.style.display = '';
						if (typeof initOrUpdateModalStatsChart === 'function') {
							initOrUpdateModalStatsChart(_chartData.base, _chartData.perYear);
						}
						// ensure chart.js recalculates size if it already existed
						try { if (modalStatsChart && typeof modalStatsChart.resize === 'function') modalStatsChart.resize(); } catch(e) {}

					// load the ST listing (HTML partial) for the region
                (function renderRegionAggregatedTitles(){
                    try {
                        const stListEl = rsmEl('rsm-st-list');
                        const headerEl = rsmEl('rsm-st-listing-header');
                        if (headerEl) {
                            headerEl.textContent = `ST Titles for ${regionParam || 'this region'}`;
                        }
                        if (!stListEl) return;
                        const rows = Array.isArray(payload.allRows) ? payload.allRows : [];
                        // if server didn't return rows, keep the instruction shown to users
                        if (!rows.length) {
                            const emptyMsg = '<div class="rsm-empty">No ST titles for this region</div>';
                            stListEl.innerHTML = emptyMsg;
                            return;
                        }

                        // build frequency map of titles (trim and ignore empty titles)
                        const titleMap = {};
                        rows.forEach(r => {
                            const t = (r && r.title) ? String(r.title).trim() : '';
                            if (!t) return;
                            titleMap[t] = (titleMap[t] || 0) + 1;
                        });

                        const entries = Object.entries(titleMap).sort((a,b) => b[1] - a[1]);
                        if (!entries.length) {
                            const emptyMsg = '<div class="rsm-empty">No ST titles for this region</div>';
                            stListEl.innerHTML = emptyMsg;
                            return;
                        }

                        // header summary (unique / total)
                        const totalSts = rows.length;
                        const uniqueTitles = entries.length;
                        const esc = s => (s||'').toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

                        let html = `<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;color:#475569;font-weight:700;font-size:0.88rem;">` +
                                   `<div>Unique titles: ${uniqueTitles}</div><div>Total STs: ${totalSts}</div></div>`;

                        html += '<div style="display:flex;flex-direction:column;gap:6px;">';
                        entries.forEach(([title, count]) => {
                            html += `<div class="rsm-st-summary-row" data-title="${esc(title)}" tabindex="0" style="display:flex;align-items:center;gap:12px;padding:8px;border-radius:6px;border:1px solid rgba(2,6,23,0.04);background:#fff;cursor:pointer;">` +
                                    `<div style="min-width:44px;text-align:center;font-weight:800;color:#2563eb;">${count}</div>` +
                                    `<div style="flex:1;color:#0b2540;font-weight:600;">${esc(title)}</div>` +
                                    `</div>`;
                        });
                        html += '</div>';

                        stListEl.innerHTML = html;
                        // delegated handler so events persist through re-renders
                        const applyHandlers = el => {
                            if (!el) return;
                            el.onclick = function(ev) {
                                const row = ev.target.closest('.rsm-st-summary-row');
                                if (!row) return;
                                ev.stopPropagation();
                                const title = row.getAttribute('data-title') || '';
                                showReplicateConfirmPopover(row, { title: title, row: { title: title } });
                            };
                            el.onkeydown = function(e) {
                                if (e.key === 'Enter' || e.key === ' ') {
                                    const row = e.target.closest('.rsm-st-summary-row');
                                    if (row) { e.preventDefault(); row.click(); }
                                }
                            };
                        };
                        applyHandlers(stListEl);
                    } catch(e) { console.error('renderRegionAggregatedTitles', e); }
                })();
						
						
				})
				.catch(err => {
					document.getElementById('rsm-loading').style.display = 'none';
					document.getElementById('rsm-cards').style.display = 'none';
					rsmEl('rsm-provinces').innerHTML = '<div class="rsm-empty">Failed to load provinces</div>'; 
					rsmEl('rsm-st-list').innerHTML = '<div class="rsm-empty">Failed to load STs listing</div>'; 
					console.error(err);
				});
		} catch(e){ console.error('renderRegionStatsForImg', e); }
	}

	// slider click interactions: render region stats and ask parent to extend iframe.
	// we still want the height animation but the image/totals/ST listing should appear
	// immediately rather than waiting for the toggling to finish.
	(function(){
		document.addEventListener('click', function(ev){
			const img = ev.target && ev.target.closest ? ev.target.closest('.slider-img') : null;
			if (!img) return;
			// only react when the image is inside the active (center) slide
			const slide = img.closest('.swiper-slide');
			if (!slide || !slide.classList.contains('swiper-slide-active')) return;
			// immediately log years for the clicked image's region
			try {
				// regionName attribute is empty for numeric slides; derive from img if needed
				let regionName = img.getAttribute('data-region-name') || '';
				if (!regionName) {
					regionName = deriveRegionFromSlider(img) || '';
				}
				if (regionName && window.parent && window.parent.regionMap) {
					const norm = normalizeRegionText(regionName);
					let map = window.parent.regionMap[norm];
					if (!map) {
						Object.keys(window.parent.regionMap || {}).some(k => {
							if (normalizeRegionText(k) === norm) {
								map = window.parent.regionMap[k];
								return true;
							}
							return false;
						});
					}
					let yrs = [];
					let provs = [];
					let cities = [];
					if (map) {
						yrs = (map.years||[]).slice().sort();
						provs = Object.keys(map.provinces||{}).sort();
						const citiesSet = new Set();
						provs.forEach(p => { (map.provinces[p]||[]).forEach(c=>citiesSet.add(c)); });
						cities = Array.from(citiesSet).sort();
					}
					try {
						const yearEl = document.getElementById('rsm-filter-year');
						const provEl = document.getElementById('rsm-filter-prov');
						const cityEl = document.getElementById('rsm-filter-city');
						if (yearEl) {
							if (yrs.length) {
								yearEl.innerHTML = yrs.map(y=> '<option>'+y+'</option>').join('');
							} else {
								yearEl.innerHTML = '<option value="">No data found</option>';
							}
						}
						if (provEl) {
							if (provs.length) {
								provEl.innerHTML = provs.map(p=> '<option>'+p+'</option>').join('');
							} else {
								provEl.innerHTML = '<option value="">No data found</option>';
							}
						}
						if (cityEl) {
							if (cities.length) {
								cityEl.innerHTML = cities.map(c=> '<option>'+c+'</option>').join('');
							} else {
								cityEl.innerHTML = '<option value="">No data found</option>';
							}
						}
					} catch(e){ console.warn('[iframe] update selects failed', e); }
					// inform parent so filtering fields can be updated (optional)
					try {
						window.parent.postMessage({ type:'sliderRegionData', region: regionName, years: yrs, provinces: provs, cities: cities }, '*');
					} catch(e) { console.warn('[iframe] post sliderRegionData failed', e); }
				}
			} catch(e) { console.warn('[iframe] click year log error', e); }
			try { renderRegionStatsForImg(img); } catch(err) { console.error('renderRegionStatsForImg failed', err); }
			try {
				window.parent.postMessage({ type:'streportToggleHeight' }, '*');
			} catch(err) { console.error('postMessage failed', err); }
		});
	})();


	// modal close interactions removed (close button / overlay / Escape) for the slider image modal (kept as no-op)
	// previously: closeBtn.click, overlay.click, Escape key would close the modal — disabled.



// Chart instance for Region Stats modal (lazy-initialized)
let modalStatsChart = null;
function initOrUpdateModalStatsChart(values = [0,0,0,0], perYearTotals = null) {
    // helper for checkbox controls (hoisted so update branch can call it)
    function refreshControls(){
        const ctrl = fetchEl('modalStatsChartControls');
        if (!ctrl || !modalStatsChart) return;
        // intentionally left blank; no user controls
        ctrl.innerHTML = '';
    }
    // prefer local canvas; fetchEl may return parent element which can be null in embed context
    let el = document.getElementById('modalStatsChart');
    if (!el) el = fetchEl('modalStatsChart');
    if (!el) { console.warn('[STsReport] modalStatsChart element not found'); return; }
    // allow Chart.js from parent if embed context doesn’t have it
    let ChartCtor = (typeof Chart !== 'undefined') ? Chart : (window.parent && window.parent.Chart);
    if (!ChartCtor) {
        console.warn('[STsReport] Chart.js not available anywhere; attempting to load dynamically');
        // dynamically inject Chart.js and retry once loaded
        const existing = document.querySelector('script[src*="chart.js"]');
        if (!existing) {
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            s.onload = () => {
                initOrUpdateModalStatsChart(values, perYearTotals);
            };
            s.onerror = () => console.error('[STsReport] failed to load Chart.js dynamically');
            document.head.appendChild(s);
        } else {
            // if script tag exists but library still not ready, wait briefly and retry
            setTimeout(() => initOrUpdateModalStatsChart(values, perYearTotals), 200);
        }
        return;
    }
    // X order requested by user: Uploaded MOA, Total MOA, SB Resolution, Expression of Interest
    const labels = ['Uploaded MOA','Total MOA','SB Resolution','Expression of Interest'];
    const ctx = el.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, el.height || 120);
    gradient.addColorStop(0, 'rgba(37,99,235,0.16)');
    gradient.addColorStop(1, 'rgba(37,99,235,0.02)');

    function buildDatasets(baseValues, perYear) {
        const datasets = [];
        // primary "All" series: thin blue line with light gradient fill
        datasets.push({
            label: 'All',
            data: baseValues,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37,99,235,0.16)',
            fill: true,
            cubicInterpolationMode: 'monotone',
            tension: 0.3,
            pointRadius: 0,                // hide individual points for minimalist look
            pointHoverRadius: 6,
            borderWidth: 2
        });
        if (!perYear) return datasets;
        const palette = ['#059669','#d97706','#7c3aed','#0ea5e9','#ef4444','#0891b2'];
        let pi = 0;
        Object.keys(perYear).forEach(year => {
            const arr = perYear[year] || [0,0,0,0];
            const color = palette[pi % palette.length];
            pi++;
            datasets.push({
                label: String(year),
                data: arr,
                borderColor: color,
                backgroundColor: 'transparent',
                fill: false,
                cubicInterpolationMode: 'monotone',
                tension: 0.3,
                pointRadius: 0,
                pointHoverRadius: 6,
                borderWidth: 2,
                borderDash: [4,4]
            });
        });
        return datasets;
    }

    if (!modalStatsChart) {
        const _maxVal = Math.max(1, ...(values.map(v => Number(v) || 0)));
        const _suggestedMax = Math.max(5, Math.ceil(_maxVal + Math.max(2, _maxVal * 0.15)));
        const _step = Math.ceil(_suggestedMax / 5);

        modalStatsChart = new ChartCtor(ctx, {
            type: 'line',
            data: { labels: labels, datasets: buildDatasets(values, perYearTotals) },
            options: {
                responsive: false,
                maintainAspectRatio: false,
                interaction: { mode: 'nearest', intersect: false, axis: 'xy' },
                onHover: null,
                events: ['click'],
                layout: { padding: { top: 4, right: 12, bottom: 20, left: 6 } },
                plugins: {
                    legend: { display: true, position: 'top', align: 'center', labels: { usePointStyle: true, boxWidth: 16, padding: 6, font: { size: 14, weight: '600' } },
                        onClick: function(evt, legendItem, legend) {
                            const chart = legend.chart; const dsIndex = legendItem.datasetIndex; const currentlyVisible = chart.isDatasetVisible(dsIndex);
                            const dsLabel = chart.data.datasets[dsIndex] && chart.data.datasets[dsIndex].label;
                            if (currentlyVisible) {
                                const currentLabelAlpha = (chart._labelAlphas && chart._labelAlphas[dsLabel]) || 1;
                                const currentDatasetAlpha = (chart._datasetAlphas && chart._datasetAlphas[dsLabel]) || 1;
                                if (chart._animateLabelAlpha || chart._animateDatasetAlpha) {
                                    let done = 0; const finish = () => { done++; if (done === 2) { chart.setDatasetVisibility(dsIndex, false); chart.update(); } };
                                    if (chart._animateLabelAlpha) chart._animateLabelAlpha(dsLabel, currentLabelAlpha, 0, 220, finish); else finish();
                                    if (chart._animateDatasetAlpha) chart._animateDatasetAlpha(dsLabel, currentDatasetAlpha, 0, 220, finish); else finish();
                                } else { chart.setDatasetVisibility(dsIndex, false); chart.update(); }
                            } else {
                                chart.setDatasetVisibility(dsIndex, true);
                                if (!chart._labelAlphas) chart._labelAlphas = {};
                                if (!chart._datasetAlphas) chart._datasetAlphas = {};
                                chart._labelAlphas[dsLabel] = 0; chart._datasetAlphas[dsLabel] = 0; chart.update();
                                if (chart._animateLabelAlpha) chart._animateLabelAlpha(dsLabel, 0, 1, 220);
                                if (chart._animateDatasetAlpha) chart._animateDatasetAlpha(dsLabel, 0, 1, 220);
                            }
                        }
                    },
                    tooltip: { enabled: false }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#374151', maxRotation: 0, autoSkip: true, font: { size: 11, weight: '600' }, padding: 8,
                        callback: function(val) {
                            const lbl = this.getLabelForValue(val) || '';
                            // explicit mapping for each metric
                            switch(lbl) {
                                case 'Uploaded MOA': return 'Upload MOA';
                                case 'Total MOA': return 'Total MOA';
                                case 'SB Resolution': return 'SB Res';
                                case 'Expression of Interest': return ['Expr','Interest'];
                                default: return lbl;
                            }
                        }
                    }, title: { display: false } },
                    y: { beginAtZero: true, suggestedMax: _suggestedMax, ticks: { precision: 0, stepSize: _step, color: '#374151', font: { size: 14, weight: '600' } }, grid: { color: 'rgba(15,23,42,0.08)' }, title: { display: true, text: 'Number of STs', color: '#6b7280', font: { size: 13, weight: '700' }, padding: { bottom: 6 } } }
                }
            },
            // minimalist chart – plugins for value indicators
            plugins: [
                {
                    id: 'simpleValues',
                    afterDatasetsDraw: function(chart) {
                        const ctx = chart.ctx;
                        chart.data.datasets.forEach((ds, dsIndex) => {
                            if (!chart.isDatasetVisible(dsIndex)) return;
                            const meta = chart.getDatasetMeta(dsIndex);
                            ds.data.forEach((val, idx) => {
                                const el = meta.data[idx];
                                if (el && val != null) {
                                    ctx.save();
                                    ctx.font = '600 12px Poppins, Inter, system-ui, sans-serif';
                                    ctx.fillStyle = ds.borderColor || '#000';
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'bottom';
                                    ctx.fillText(String(val), el.x, el.y - 6);
                                    ctx.restore();
                                }
                            });
                        });
                    }
                },
                {
                    id: 'endLineLabels',
                    afterDatasetsDraw: function(chart) {
                        const ctx = chart.ctx;
                        chart.data.datasets.forEach((ds, dsIndex) => {
                            if (!chart.isDatasetVisible(dsIndex)) return;
                            const meta = chart.getDatasetMeta(dsIndex);
                            const len = meta.data.length;
                            if (len === 0) return;
                            const el = meta.data[len-1];
                            const val = ds.data[len-1];
                            if (el && val != null) {
                                ctx.save();
                                ctx.font = '700 12px Poppins, Inter, system-ui, sans-serif';
                                ctx.fillStyle = ds.borderColor || '#000';
                                ctx.textAlign = 'left';
                                ctx.textBaseline = 'middle';
                                ctx.fillText(String(val), el.x + 8, el.y);
                                ctx.restore();
                            }
                        });
                    }
                }
            ]
        });
        try { createChartHitZones(modalStatsChart); } catch(e) {}

        // build and render show/hide controls.  We already defined a
        // checkbox-friendly version earlier in the outer scope, so just
        // call that helper here instead of redefining it.  This ensures the
        // multi‑select dropdown used throughout the modal always has checkboxes
        // (and avoids accidentally overriding the function).
        refreshControls();

        // attach alpha animators + init alpha state
        modalStatsChart._labelAlphas = {}; modalStatsChart._labelFadeHandles = {};
        modalStatsChart._setLabelAlpha = function(label, v) { this._labelAlphas[label] = v; };
        modalStatsChart._getLabelAlpha = function(label) { return (this._labelAlphas && typeof this._labelAlphas[label] !== 'undefined') ? this._labelAlphas[label] : 1; };
        modalStatsChart._animateLabelAlpha = function(label, from, to, duration = 220, cb) {
            const self = this; if (!label) { if (cb) cb(); return; } if (!self._labelAlphas) self._labelAlphas = {}; if (self._labelFadeHandles && self._labelFadeHandles[label] && self._labelFadeHandles[label].raf) cancelAnimationFrame(self._labelFadeHandles[label].raf);
            const start = performance.now(); const easeOutCubic = t => 1 - Math.pow(1 - t, 3);
            const step = (now) => { const t = Math.min(1, (now - start) / Math.max(1, duration)); const v = from + (to - from) * easeOutCubic(t); self._labelAlphas[label] = v; try { self.draw(); } catch (e) {} if (t < 1) { self._labelFadeHandles[label] = { raf: requestAnimationFrame(step) }; } else { if (self._labelFadeHandles) delete self._labelFadeHandles[label]; if (cb) cb(); } };
            self._labelFadeHandles = self._labelFadeHandles || {}; self._labelFadeHandles[label] = { raf: requestAnimationFrame(step) };
        };

        modalStatsChart.data.datasets.forEach((ds, idx) => { modalStatsChart._labelAlphas[ds.label] = modalStatsChart.isDatasetVisible(idx) ? 1 : 0; });
        modalStatsChart._datasetAlphas = {}; modalStatsChart._datasetFadeHandles = {};
        modalStatsChart._setDatasetAlpha = function(label, v) { this._datasetAlphas[label] = v; };
        modalStatsChart._getDatasetAlpha = function(label) { return (this._datasetAlphas && typeof this._datasetAlphas[label] !== 'undefined') ? this._datasetAlphas[label] : 1; };
        modalStatsChart._animateDatasetAlpha = function(label, from, to, duration = 220, cb) {
            const self = this; if (!label) { if (cb) cb(); return; } if (!self._datasetAlphas) self._datasetAlphas = {}; if (self._datasetFadeHandles && self._datasetFadeHandles[label] && self._datasetFadeHandles[label].raf) cancelAnimationFrame(self._datasetFadeHandles[label].raf);
            const start = performance.now(); const easeOutCubic = t => 1 - Math.pow(1 - t, 3);
            const step = (now) => { const t = Math.min(1, (now - start) / Math.max(1, duration)); const v = from + (to - from) * easeOutCubic(t); self._datasetAlphas[label] = v; try { self.draw(); } catch (e) {} if (t < 1) { self._datasetFadeHandles[label] = { raf: requestAnimationFrame(step) }; } else { if (self._datasetFadeHandles) delete self._datasetFadeHandles[label]; if (cb) cb(); } };
            self._datasetFadeHandles = self._datasetFadeHandles || {}; self._datasetFadeHandles[label] = { raf: requestAnimationFrame(step) };
        };

        modalStatsChart.data.datasets.forEach((ds, idx) => { modalStatsChart._datasetAlphas[ds.label] = modalStatsChart.isDatasetVisible(idx) ? 1 : 0; });

        // legend hit-zone helpers (canvas click handling) — kept from demo1
        (function(){ const canvas = el; if (!canvas) return; const hitTestScale = () => { return { scaleX: modalStatsChart.width / (canvas.clientWidth || canvas.width || 1), scaleY: modalStatsChart.height / (canvas.clientHeight || canvas.height || 1) }; };
            const isPointInBox = (x, y, box) => (x >= box.left && x <= (box.left + box.width) && y >= box.top && y <= (box.top + box.height));
            const onCanvasClickForLegend = function(evt) { if (!modalStatsChart || !modalStatsChart.legend) return; const boxes = (modalStatsChart.legend && modalStatsChart.legend.legendHitBoxes) || []; if (!boxes.length) return; const rect = canvas.getBoundingClientRect(); const { scaleX, scaleY } = hitTestScale(); const cx = (evt.clientX - rect.left) * scaleX; const cy = (evt.clientY - rect.top) * scaleY; for (let i = 0; i < boxes.length; i++) { const box = boxes[i]; if (isPointInBox(cx, cy, box)) { const item = (modalStatsChart.legend && modalStatsChart.legend.legendItems && modalStatsChart.legend.legendItems[i]) || {}; const dsIndex = (typeof item.datasetIndex !== 'undefined') ? item.datasetIndex : i; const currentlyVisible = modalStatsChart.isDatasetVisible(dsIndex); const dsLabel = modalStatsChart.data.datasets[dsIndex] && modalStatsChart.data.datasets[dsIndex].label;
                        if (currentlyVisible) { const currentLabelAlpha = (modalStatsChart._labelAlphas && modalStatsChart._labelAlphas[dsLabel]) || 1; const currentDatasetAlpha = (modalStatsChart._datasetAlphas && modalStatsChart._datasetAlphas[dsLabel]) || 1; if (modalStatsChart._animateLabelAlpha || modalStatsChart._animateDatasetAlpha) { let done = 0; const finish = () => { done++; if (done === 2) { modalStatsChart.setDatasetVisibility(dsIndex, false); modalStatsChart.update(); } }; if (modalStatsChart._animateLabelAlpha) modalStatsChart._animateLabelAlpha(dsLabel, currentLabelAlpha, 0, 220, finish); else finish(); if (modalStatsChart._animateDatasetAlpha) modalStatsChart._animateDatasetAlpha(dsLabel, currentDatasetAlpha, 0, 220, finish); else finish(); } else { modalStatsChart.setDatasetVisibility(dsIndex, false); modalStatsChart.update(); } } else { modalStatsChart.setDatasetVisibility(dsIndex, true); if (!modalStatsChart._labelAlphas) modalStatsChart._labelAlphas = {}; if (!modalStatsChart._datasetAlphas) modalStatsChart._datasetAlphas = {}; modalStatsChart._labelAlphas[dsLabel] = 0; modalStatsChart._datasetAlphas[dsLabel] = 0; modalStatsChart.update(); if (modalStatsChart._animateLabelAlpha) modalStatsChart._animateLabelAlpha(dsLabel, 0, 1, 220); if (modalStatsChart._animateDatasetAlpha) modalStatsChart._animateDatasetAlpha(dsLabel, 0, 1, 220); }
                        evt.preventDefault(); return; } } };
            const onCanvasMoveForLegend = function(evt) { if (!modalStatsChart || !modalStatsChart.legend) return; const boxes = (modalStatsChart.legend && modalStatsChart.legend.legendHitBoxes) || []; if (!boxes.length) { canvas.style.cursor = ''; return; } const rect = canvas.getBoundingClientRect(); const { scaleX, scaleY } = hitTestScale(); const cx = (evt.clientX - rect.left) * scaleX; const cy = (evt.clientY - rect.top) * scaleY; let over = false; for (let i = 0; i < boxes.length; i++) { if (isPointInBox(cx, cy, boxes[i])) { over = true; break; } } canvas.style.cursor = over ? 'pointer' : ''; };
            canvas.addEventListener('click', onCanvasClickForLegend); canvas.addEventListener('mousemove', onCanvasMoveForLegend);
        })();

    } else {
        const _maxVal = Math.max(1, ...(values.map(v => Number(v) || 0)));
        const _suggestedMax = Math.max(5, Math.ceil(_maxVal + Math.max(2, _maxVal * 0.15)));
        const _step = Math.ceil(_suggestedMax / 5);
        const _visMap = {};
        modalStatsChart.data.datasets.forEach((ds, idx) => { _visMap[ds.label] = modalStatsChart.isDatasetVisible(idx); });
        modalStatsChart.data.labels = labels;
        modalStatsChart.data.datasets = buildDatasets(values, perYearTotals);
        refreshControls();
        modalStatsChart.data.datasets.forEach((ds, idx) => {
            const vis = (typeof _visMap[ds.label] !== 'undefined') ? _visMap[ds.label] : true;
            modalStatsChart.setDatasetVisibility(idx, vis);
            if (!modalStatsChart._labelAlphas) modalStatsChart._labelAlphas = {};
            modalStatsChart._labelAlphas[ds.label] = (typeof modalStatsChart._labelAlphas[ds.label] !== 'undefined') ? modalStatsChart._labelAlphas[ds.label] : (vis ? 1 : 0);
            if (!modalStatsChart._datasetAlphas) modalStatsChart._datasetAlphas = {};
            modalStatsChart._datasetAlphas[ds.label] = (typeof modalStatsChart._datasetAlphas[ds.label] !== 'undefined') ? modalStatsChart._datasetAlphas[ds.label] : (vis ? 1 : 0);
        });
        modalStatsChart.options.scales.y.suggestedMax = _suggestedMax;
        modalStatsChart.options.scales.y.ticks.stepSize = _step;
        modalStatsChart.update();
        try { createChartHitZones(modalStatsChart); } catch(e) {}
    }
}

function createChartHitZones(chart) {
    try {
        const canvas = chart && chart.canvas;
        const zonesContainer = document.getElementById('modalStatsChartZones');
        if (!zonesContainer || !canvas) return;
        zonesContainer.innerHTML = '';
        zonesContainer.style.pointerEvents = 'auto';
        const meta = chart.getDatasetMeta(0);
        const pts = (meta && meta.data) ? meta.data : [];
        if (!pts.length) return;
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width || 1;
        const scaleY = canvas.height / rect.height || 1;
        const xCoords = pts.map(p => p.x);
        const horizDists = pts.map((p, i) => {
            const left = i > 0 ? Math.abs(p.x - xCoords[i-1]) : Infinity;
            const right = i < xCoords.length - 1 ? Math.abs(xCoords[i+1] - p.x) : Infinity;
            return Math.min(left, right);
        });
        pts.forEach((p, i) => {
            const cssLeft = (p.x / scaleX);
            const cssTop = (p.y / scaleY);
            const horizGapCss = (horizDists[i] === Infinity) ? 120 : (horizDists[i] / scaleX);
            const preferredR = Math.max(12, Math.min(36, Math.round(horizGapCss * 0.32)));
            const minR = 8;
            const maxAllowedByEdges = Math.floor(Math.min(cssLeft, rect.width - cssLeft, cssTop, rect.height - cssTop));
            let r = Math.min(preferredR, Math.max(minR, maxAllowedByEdges));
            let finalLeft = cssLeft; let finalTop = cssTop;
            if (maxAllowedByEdges < minR) {
                finalLeft = Math.max(minR, Math.min(rect.width - minR, cssLeft));
                finalTop = Math.max(minR, Math.min(rect.height - minR, cssTop));
                r = Math.max(6, Math.min(preferredR, Math.floor(Math.min(rect.width, rect.height) / 6)));
            }
            const zone = document.createElement('div');
            zone.className = 'chart-hit-zone'; zone.dataset.idx = i; zone.style.position = 'absolute'; zone.style.left = (finalLeft) + 'px'; zone.style.top = (finalTop) + 'px'; zone.style.width = (r * 2) + 'px'; zone.style.height = (r * 2) + 'px'; zone.style.transform = 'translate(-50%, -50%)'; zone.style.borderRadius = '50%'; zone.style.pointerEvents = 'auto'; zone.style.zIndex = 9999; zone.style.background = 'transparent';
            zone.addEventListener('pointerenter', function(e) { e.stopPropagation(); try { if (e.pointerId && zone.setPointerCapture) zone.setPointerCapture(e.pointerId); } catch (err) {} chart.setActiveElements([{ datasetIndex: 0, index: i }]); chart.tooltip.setActiveElements([{ datasetIndex: 0, index: i }], { x: p.x, y: p.y }); canvas.style.cursor = 'pointer'; chart.update(); });
            zone.addEventListener('pointerleave', function(e) { e.stopPropagation(); try { if (e.pointerId && zone.releasePointerCapture) zone.releasePointerCapture(e.pointerId); } catch (err) {} try { chart.setActiveElements([]); chart.tooltip.setActiveElements([], { x: 0, y: 0 }); } catch (err) {} canvas.style.cursor = ''; chart.update(); });
            zone.addEventListener('click', function(ev) { ev.stopPropagation(); try { const idx = Number(zone.dataset.idx || 0); chart.setActiveElements([{ datasetIndex: 0, index: idx }]); chart.tooltip.setActiveElements([{ datasetIndex: 0, index: idx }], { x: p.x, y: p.y }); chart.update(); } catch (err) {} });
            zonesContainer.appendChild(zone);
        });
    } catch (e) { /* ignore */ }
}

	// Unified replicate popover — single component for All ST Titles and ST Titles panel
	(function initUnifiedReplicatePopover() { return; /* modal popover removed */
		if (window.__unifiedReplicatePopoverBound) return;
		window.__unifiedReplicatePopoverBound = true;
		const pop = document.getElementById('stReplicatePopover');
	})();
});
