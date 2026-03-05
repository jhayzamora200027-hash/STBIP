<script>
// silence console output for report scripts
if (window.console) { ['log','warn','error','debug','info','trace'].forEach(m=>{console[m]=function(){};}); }

// global DOM utility helpers (must be available before any other script blocks)
function fetchEl(id) {
    // look in parent frame first for same-origin embedding
    if (window.parent && window.parent !== window && window.parent.document) {
        const pe = window.parent.document.getElementById(id);
        if (pe) return pe;
    }
    return document.getElementById(id);
}
function rsmEl(id) { return fetchEl(id); }

// helper that determines what region the UI is currently showing. this
// duplicates the logic used in several places below but keeps it together
// so we can compare cached payloads against the live region and fetch a
// fresh payload if they ever diverge (this prevents filters from running
// against stale data after a slider transition).
function getCurrentRegion(){
    let region = null;
    if (window.__lastActiveRegion) {
        region = window.__lastActiveRegion;
    }
    if (!region) {
        const provSel = document.getElementById('rsm-filter-prov');
        if (provSel && provSel._lastRegionEvent && provSel._lastRegionEvent.regionName) {
            region = provSel._lastRegionEvent.regionName;
        }
    }
    if (!region) {
        const activeImg = document.querySelector('.swiper-slide.swiper-slide-active .slider-img');
        if (activeImg) {
            region = activeImg.getAttribute('data-region-name') || activeImg.getAttribute('data-region-number');
        }
    }
    if (!region) {
        const modalImg = document.getElementById('rsm-modal-image');
        if (modalImg) region = modalImg.getAttribute('data-region-name');
    }
    // normalize digits ("2" -> "Region II") to keep in step with server
    if (/^\d+$/.test(region)) {
        const romans = ['','I','II','III','IV-A','V','VI','VII','VIII','IX','X','XI','XII','','CARAGA'];
        const num = parseInt(region,10);
        region = 'Region ' + (romans[num] || region);
    }
    return region;
}

// --- client-side filter apply/reset handlers ---------------------------------
// utility used by several filtering routines.  use `rsmEl` so that
// we support scenarios where the filter controls live in a parent frame
// (eg. when the report is embedded); the original implementation blindly
// used `document.getElementById` which meant selections were always empty
// when the iframe context changed.  also keep the same logging as before.
function _getSelectedValues(id) {
    const el = rsmEl ? rsmEl(id) : document.getElementById(id);
    if (!el) return [];
    try {
        // prefer Select2 / jQuery value when available
        try { if (window.jQuery && jQuery(el).data('select2')) {
                    const v = jQuery(el).val();
                    const result = Array.isArray(v) ? v.filter(Boolean) : (v ? [v] : []);
                    return result;
                } } catch(e) {}
        if (el.selectedOptions && el.selectedOptions.length) {
            const res = Array.from(el.selectedOptions).map(o => o.value);
            return res;
        }
        // fallback
        const res = Array.from(el.querySelectorAll('option:checked')).map(o => o.value);
        return res;
    } catch(e) { console.error('[RSM] _getSelectedValues error', e); return []; }
}

// helpers used by filter routines. defining them here at the top of the
// first <script> block guarantees they exist before any filtering code
// may execute (previously they lived in a later <script> block which
// sometimes caused `ReferenceError: getRowCity is not defined`).
function getRowCity(r){
    // server-side logic builds hierarchy using municipality first, so
    // mirror that order here. many rows only set `municipality` and leave
    // `city` blank, which was previously causing the filter to always
    // return an empty string and thus never match.
    return ((r && (r.municipality || r.city)) || '').toString().trim();
}
function getRowYear(r){
    return ((r && (r.year_of_moa || r.moa_year || r.moa_year_of || r.moa_year_of_moa || r.moa_year_of)) || '').toString().trim();
}

// shared cached fetch helper for region hierarchy JSON so multiple
// widgets using the same region don't keep refetching from the server.
(function(){
    const cache = {};
    function fetchRegionHierarchy(region){
        if (!region) return Promise.reject(new Error('Missing region'));
        const key = String(region);
        if (cache[key] && cache[key].data) {
            return Promise.resolve(cache[key].data);
        }
        if (cache[key] && cache[key].promise) {
            return cache[key].promise;
        }
        const url = '/sts-report/ajax-region-hierarchy?region_image=' + encodeURIComponent(region);
        const p = fetch(url)
            .then(r => { if (!r.ok) throw new Error('Network'); return r.json(); })
            .then(data => {
                cache[key] = { data: data };
                return data;
            })
            .catch(err => { delete cache[key]; throw err; });
        cache[key] = { promise: p };
        return p;
    }
    try { window.fetchRegionHierarchy = fetchRegionHierarchy; } catch(e) {}
})();

// make a harmless placeholder available immediately so that code which
// fires before the real implementation loads doesn't throw an error.
// the real version will overwrite this when it is defined later in the
// same script block, but at least callers can safely invoke it early.
if (!window.updateProvinceFilters) {
    window.updateProvinceFilters = function(){
        console.warn('[RSM] updateProvinceFilters placeholder invoked before definition');
    };
}

function renderStTitlesFromRows(rows, regionParam) {
    try {
        const stListEl = rsmEl('rsm-st-list');
        const headerEl = rsmEl('rsm-st-listing-header');
        if (headerEl) headerEl.textContent = `ST Titles for ${regionParam || 'this region'}`;
        if (!stListEl) return;
        if (!rows || !rows.length) { stListEl.innerHTML = '<div class="rsm-empty">No ST titles for selected filters</div>'; return; }
        const titleMap = {};
        rows.forEach(r => {
            const t = (r && r.title) ? String(r.title).trim() : '';
            if (!t) return; titleMap[t] = (titleMap[t] || 0) + 1;
        });
        const entries = Object.entries(titleMap).sort((a,b) => b[1] - a[1]);
        if (!entries.length) { stListEl.innerHTML = '<div class="rsm-empty">No ST titles for selected filters</div>'; return; }
        const totalSts = rows.length; const uniqueTitles = entries.length;
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
        // wire handlers
        stListEl.onclick = function(ev){ const row = ev.target.closest('.rsm-st-summary-row'); if (!row) return; ev.stopPropagation(); const title = row.getAttribute('data-title')||''; showReplicateConfirmPopover(row, { title: title, row: { title: title } }); };
    } catch(e) { console.error('renderStTitlesFromRows', e); }
}

function applyRsmFilters(){
    // local truthiness helper in case outer scope failed to define it
    const truthy = v => (typeof v === 'boolean') ? v : (String(v || '').toLowerCase().trim() === 'true');

    // if our cached payload belongs to a different region than the UI is
    // currently showing then drop it so that the subsequent logic will
    // trigger a fresh fetch. this guards against timing issues where a
    // slider change may still be in-flight when the user clicks "Filter".
    let payload = window._lastRsmPayload || null;
    const currentRegion = getCurrentRegion();
    if (payload && payload.region && currentRegion && payload.region !== currentRegion) {
        window._lastRsmPayload = null;
        payload = null;
    }

    // make sure city/year options exist before we read selections; the
    // list is built by updateProvinceFilters which is normally invoked
    // when the province selector changes. only refresh if the selects are
    // empty, otherwise we risk clearing the user's choice when they try to
    // filter by city or year alone.
    try {
        const cityElInit = document.getElementById('rsm-filter-city');
        const yearElInit = document.getElementById('rsm-filter-year');
        const needPopulate = (cityElInit && cityElInit.options.length === 0) ||
                             (yearElInit && yearElInit.options.length === 0);
        if (needPopulate && typeof updateProvinceFilters === 'function') {
            updateProvinceFilters();
        }
    } catch(e) { console.warn('[RSM] initial province filter update failed', e); }
    try {
        // close any open select2 dropdowns so the underlying <select> has
        // the correct value before we read it. this helps when user clicks
        // the filter button while the dropdown is still open.
        try {
            if (window.jQuery) {
                ['#rsm-filter-prov','#rsm-filter-city','#rsm-filter-year'].forEach(sel=>{
                    const $el = jQuery(sel);
                    if ($el && $el.data('select2')) {
                        $el.select2('close');
                    }
                });
            }
        } catch(_e){}
        // log the selection values for debugging
        const provsDbg = _getSelectedValues('rsm-filter-prov');
        const citiesDbg = _getSelectedValues('rsm-filter-city');
        const yearsDbg = _getSelectedValues('rsm-filter-year');
        const rawCityVal = (window.jQuery && jQuery('#rsm-filter-city').length) ? jQuery('#rsm-filter-city').val() : null;
        if (!payload || !Array.isArray(payload.allRows)) {
            console.warn('[RSM] _lastRsmPayload missing — attempting fetch');
            // try to derive region param from last active region or modal image
            // try several places to figure out which region the user is
            // currently looking at. historically we relied solely on
            // __lastActiveRegion or the modal image, but in some embed
            // scenarios those values can still be null. fall back to the
            // province selector (which stores the last region event) and
            // finally the active slider image so filters work even if the
            // user hasn't clicked anything yet.
            let regionParam = null;
            if (window.__lastActiveRegion) {
                regionParam = window.__lastActiveRegion;
            }
            if (!regionParam) {
                const provSel = document.getElementById('rsm-filter-prov');
                if (provSel && provSel._lastRegionEvent && provSel._lastRegionEvent.regionName) {
                    regionParam = provSel._lastRegionEvent.regionName;
                }
            }
            if (!regionParam) {
                const activeImg = document.querySelector('.swiper-slide.swiper-slide-active .slider-img');
                if (activeImg) {
                    regionParam = activeImg.getAttribute('data-region-name') || activeImg.getAttribute('data-region-number');
                }
            }
            if (!regionParam) {
                const modalImg = document.getElementById('rsm-modal-image');
                if (modalImg) regionParam = modalImg.getAttribute('data-region-name');
            }
            // if we still don't know the region, try to infer it from the
            // selected province (use parent.regionMap which was populated
            // when the iframe was loaded). this covers cases where the
            // user just picked a province without ever clicking the slider.
            if (!regionParam) {
                try {
                    const provSel = document.getElementById('rsm-filter-prov');
                    if (provSel && provSel.value && window.parent && window.parent.regionMap) {
                        const provVal = provSel.value.toString().trim().toLowerCase();
                        Object.keys(window.parent.regionMap || {}).some(r => {
                            const map = window.parent.regionMap[r] || {};
                            const list = Object.keys(map.provinces || {});
                            if (list.some(p=>p.toString().trim().toLowerCase() === provVal)) {
                                regionParam = r;
                                return true;
                            }
                            return false;
                        });
                    }
                } catch(_){ }
            }
            // if we got a bare number (sheet names are numeric in some
            // spreadsheets) convert it to the canonical form the backend
            // expects. normalizeRegionText does not handle digits, so do it
            // manually here.
            if (/^\d+$/.test(regionParam)) {
                const romans = ['','I','II','III','IV-A','V','VI','VII','VIII','IX','X','XI','XII','','CARAGA'];
                const num = parseInt(regionParam,10);
                regionParam = 'Region ' + (romans[num] || regionParam);
            }
            if (!regionParam) {
                console.warn('[RSM] cannot derive region to fetch payload');
                return;
            }
            try {
                // perform same AJAX used elsewhere, but via cached helper
                (window.fetchRegionHierarchy ? window.fetchRegionHierarchy(regionParam) : Promise.reject(new Error('fetchRegionHierarchy missing')))
                    .then(p => { p.region = regionParam; window._lastRsmPayload = p; payload = p; applyRsmFilters(); })
                    .catch(err => console.error('[RSM] fetch fallback failed', err));
            } catch(e) { console.error('[RSM] fetch fallback exception', e); }
            return;
        }
        const allRows = payload.allRows.slice();
        // debug payload content
        console.debug('[RSM] payload headers', payload.headers);
        console.debug('[RSM] sample rows', allRows.slice(0,5));
        const provs = _getSelectedValues('rsm-filter-prov');
        const cities = _getSelectedValues('rsm-filter-city');
        const years = _getSelectedValues('rsm-filter-year');
        // capture selections so we can restore them if we rebuild the
        // city/year dropdowns further down. this prevents the UI from
        // clearing the user's choice when applyRsmFilters runs again.
        const savedCities = cities.slice();
        const savedYears = years.slice();
        const filtered = allRows.filter(r => {
            let ok = true;
            // normalize row values for case-insensitive comparison
            const rowProv = (r.province||'').toString().trim().toLowerCase();
            // guard against missing helper (shouldn't happen now, but safe)
            const rowCity = (typeof getRowCity === 'function'
                ? getRowCity(r)
                : ((r && (r.municipality||r.city))||'').toString().trim()
            ).toLowerCase();
            if (provs && provs.length) {
                ok = provs.some(p => rowProv === (p||'').toString().trim().toLowerCase());
            }
            if (ok && cities && cities.length) {
                // log each comparison to see why the row is kept/dropped
                let matchedCity = false;
                cities.forEach(c => {
                    const normC = (c||'').toString().trim().toLowerCase();
                    // allow substring matches so minor formatting differences
                    // (extra words, punctuation, accents) don't defeat the filter.
                    const isMatch = rowCity.includes(normC) || normC.includes(rowCity);
                    console.debug('[RSM] compare row city', rowCity, 'with', normC, '=>', isMatch);
                    if (isMatch) matchedCity = true;
                });
                if (!matchedCity) {
                    console.debug('[RSM] city filter rejected row', r, 'city', rowCity, 'selected', cities);
                }
                ok = matchedCity;
            }
            if (ok && years && years.length) {
                const yval = (typeof getRowYear === 'function' ? getRowYear(r) : ((r && (r.year_of_moa||r.moa_year||r.moa_year_of||r.moa_year_of_moa||r.moa_year_of))||'').toString().trim());
                ok = years.some(y => (yval === (y||'').toString().trim()));
            }
            return ok;
        });

        // client-side status inference using headers if needed
        try {
            const headersArr = (payload && payload.headers) ? payload.headers : [];
            const idxO = headersArr.findIndex(h => h && h.toString().toLowerCase().includes('ongoing'));
            const idxD = headersArr.findIndex(h => h && (h.toString().toLowerCase().includes('dissolved') || h.toString().toLowerCase().includes('inactive')));
            if (idxO !== -1 || idxD !== -1) {
                filtered.forEach(r => {
                    let st = (r.status || '').toString().toLowerCase();
                    if (!st && idxO !== -1 && r.row && r.row[idxO] !== undefined && String(r.row[idxO]).trim() !== '') {
                        st = 'ongoing';
                    }
                    if (!st && idxD !== -1 && r.row && r.row[idxD] !== undefined && String(r.row[idxD]).trim() !== '') {
                        st = 'dissolved';
                    }
                    r.status = st;
                });
            }
        } catch(_){ }


        // update totals
        const totalSts = filtered.length || 0;
        const totalMoa = filtered.reduce((acc,r) => acc + (truthy(r.with_moa) ? 1 : 0), 0);
        const totalRes = filtered.reduce((acc,r) => acc + (truthy(r.with_res) ? 1 : 0), 0);
        const totalExpr = filtered.reduce((acc,r) => acc + (truthy(r.with_expr) ? 1 : 0), 0);
        const moaAttachments = 0;

        // compute ongoing / dissolved counts using the same rules as the
        // main dashboard: use numeric values in the dedicated Ongoing /
        // Dissolved columns when present, otherwise treat any non-empty
        // cell or status text as a single instance.
        let ongoingCount = 0;
        let dissolvedCount = 0;

        try {
            const headersArr = (payload && payload.headers) ? payload.headers : [];
            const idxO = headersArr.findIndex(h => h && h.toString().toLowerCase().includes('ongoing'));
            const idxD = headersArr.findIndex(h => h && (h.toString().toLowerCase().includes('dissolved') || h.toString().toLowerCase().includes('inactive')));
            const cellHasStatusMark = v => {
                if (typeof v === 'boolean') return v;
                if (v === null || v === undefined) return false;
                const s = String(v).trim();
                if (s === '' || s === '0') return false;
                if (!isNaN(s)) return Number(s) !== 0;
                return true;
            };

            filtered.forEach(r => {
                let oCnt = 0; let dCnt = 0;

                if (idxO !== -1 && r.row && r.row[idxO] !== undefined) {
                    const v = r.row[idxO];
                    const s = String(v).trim();
                    if (!isNaN(s) && s !== '') {
                        oCnt = Math.max(0, parseInt(s, 10) || 0);
                    } else if (cellHasStatusMark(v)) {
                        oCnt = 1;
                    }
                }
                if (idxD !== -1 && r.row && r.row[idxD] !== undefined) {
                    const v = r.row[idxD];
                    const s = String(v).trim();
                    if (!isNaN(s) && s !== '') {
                        dCnt = Math.max(0, parseInt(s, 10) || 0);
                    } else if (cellHasStatusMark(v)) {
                        dCnt = 1;
                    }
                }

                let st = (r.status || '').toString().toLowerCase();
                if (!st && idxO !== -1 && r.row && cellHasStatusMark(r.row[idxO])) st = 'ongoing';
                if (!st && idxD !== -1 && r.row && cellHasStatusMark(r.row[idxD])) st = 'dissolved';

                if ((st.includes('ongoing') || st === 'on going') && oCnt === 0 && !cellHasStatusMark(r.row && r.row[idxO])) {
                    oCnt = 1;
                } else if ((st.includes('dissolved') || st.includes('inactive') || st.includes('completed')) && dCnt === 0 && !cellHasStatusMark(r.row && r.row[idxD])) {
                    dCnt = 1;
                }

                ongoingCount += oCnt;
                dissolvedCount += dCnt;
            });
        } catch (e) {
            console.error('[RSM] status aggregation failed; falling back to simple counts', e);
            ongoingCount = filtered.reduce((acc,r) => {
                const st = (r.status || '').toString().toLowerCase();
                return acc + ((st.includes('ongoing') || st === 'on going') ? 1 : 0);
            }, 0);
            dissolvedCount = filtered.reduce((acc,r) => {
                const st = (r.status || '').toString().toLowerCase();
                return acc + ((st.includes('dissolved') || st.includes('inactive') || st.includes('completed')) ? 1 : 0);
            }, 0);
        }
        console.debug('[RSM] computed counts', { total: filtered.length, ongoingCount, dissolvedCount });
        // log sample statuses for debugging
        if (filtered.length) {
            const statuses = Array.from(new Set(filtered.map(r=> (r.status||'').toString().toLowerCase())));
            console.debug('[RSM] unique statuses', statuses.slice(0,20));
        }

        // refresh city/year selects based on filtered rows as well. keep
        // any existing selection so the user doesn't lose their choice.
        try {
            const cityEl2 = document.getElementById('rsm-filter-city');
            const yearEl2 = document.getElementById('rsm-filter-year');
            if (cityEl2) cityEl2.innerHTML = '';
            if (yearEl2) yearEl2.innerHTML = '';
            const citySet = new Set();
            const yearSet = new Set();
            filtered.forEach(r => {
                const cityName = getRowCity(r);
                if (cityName) citySet.add(cityName);
                const yval = (typeof getRowYear === 'function' ? getRowYear(r) : ((r.year_of_moa || r.moa_year || r.moa_year_of || r.moa_year_of_moa || r.moa_year_of) || '').toString().trim());
                if (yval) yearSet.add(yval);
            });
            Array.from(citySet).sort().forEach(c => cityEl2 && cityEl2.appendChild(new Option(c, c)));
            Array.from(yearSet).sort().forEach(y => yearEl2 && yearEl2.appendChild(new Option(y, y)));
            // restore previously selected values if still present
            if (cityEl2) {
                if (window.jQuery && jQuery(cityEl2).data('select2')) {
                    const available = Array.from(cityEl2.options).map(o=>o.value);
                    const toSet = savedCities.filter(v=>available.includes(v));
                    jQuery(cityEl2).val(toSet).trigger('change.select2');
                } else {
                    Array.from(cityEl2.options).forEach(o => { o.selected = savedCities.includes(o.value); });
                }
            }
            if (yearEl2) {
                if (window.jQuery && jQuery(yearEl2).data('select2')) {
                    const availableY = Array.from(yearEl2.options).map(o=>o.value);
                    const toSetY = savedYears.filter(v=>availableY.includes(v));
                    jQuery(yearEl2).val(toSetY).trigger('change.select2');
                } else {
                    Array.from(yearEl2.options).forEach(o => { o.selected = savedYears.includes(o.value); });
                }
            }
        } catch(e) { console.error('refresh city/year from filtered failed', e); }

        fetchEl('rsm-total-sts').textContent = String(totalSts);
        fetchEl('rsm-total-moa').textContent = String(totalMoa);
        fetchEl('rsm-total-res').textContent = String(totalRes);
        fetchEl('rsm-total-expr').textContent = String(totalExpr);
        fetchEl('rsm-total-moa-attachments').textContent = String(moaAttachments);
        // update replicated/adopted totals
        const totalRep = filtered.reduce((acc, r) => acc + (typeof r.with_replicated === 'number' ? r.with_replicated : ((typeof r.with_replicated === 'boolean') ? (r.with_replicated ? 1 : 0) : (String(r.with_replicated||'').toLowerCase().trim() === 'true' ? 1 : 0))), 0);
        const totalAdopt = filtered.reduce((acc, r) => acc + (typeof r.with_adopted === 'number' ? r.with_adopted : ((typeof r.with_adopted === 'boolean') ? (r.with_adopted ? 1 : 0) : (String(r.with_adopted||'').toLowerCase().trim() === 'true' ? 1 : 0))), 0);
        fetchEl('rsm-total-rep').textContent = String(totalRep);
        fetchEl('rsm-total-adopt').textContent = String(totalAdopt);
        // update ongoing/dissolved card counts
        const ongoingEl = fetchEl('rsm-count-ongoing'); if (ongoingEl) ongoingEl.textContent = String(ongoingCount);
        const dissolvedEl = fetchEl('rsm-count-dissolved'); if (dissolvedEl) dissolvedEl.textContent = String(dissolvedCount);

        // update chart (base values only)
        const baseValuesOrdered = [ Number(moaAttachments || 0), Number(totalMoa || 0), Number(totalRes || 0), Number(totalExpr || 0) ];
        if (typeof initOrUpdateModalStatsChart === 'function') initOrUpdateModalStatsChart(baseValuesOrdered, null);

        // update ST titles listing
        renderStTitlesFromRows(filtered, (payload && payload.region) || window.__lastActiveRegion || 'this region');

        // update provinces list to only show provinces present in filtered rows
        try {
            const provEl = rsmEl('rsm-provinces');
            if (provEl) {
                const byProv = {};
                filtered.forEach(r => { const p = (r.province||'').toString().trim() || 'UNKNOWN'; byProv[p] = byProv[p] || []; byProv[p].push(r); });
                const esc = s => (s||'').toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                const provincesArr = Object.keys(byProv).sort();
                provEl.innerHTML = provincesArr.length ? provincesArr.map(p => `<div class="rsm-prov-item province-item" role="button" tabindex="0" data-prov="${esc(p)}"><div class="prov-name">${esc(p)}</div><div class="province-badge">${byProv[p].length}</div></div>`).join('') : '<div class="rsm-empty">No provinces match filters</div>';
            }
        } catch(e) {}

    } catch(e) { console.error('applyRsmFilters', e); }
}

function resetRsmFilters(){
    try {
        ['rsm-filter-prov','rsm-filter-city','rsm-filter-year'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            Array.from(el.options || []).forEach(o => o.selected = false);
            // if select2 is present, trigger update
            try { if (window.jQuery && jQuery(el).data('select2')) jQuery(el).val([]).trigger('change'); } catch(e) {}
        });
    } catch(e){}
    // re-run rendering with full payload
    try { const payload = window._lastRsmPayload || null; if (payload && Array.isArray(payload.allRows)) {
            renderStTitlesFromRows(payload.allRows, (payload && payload.region) || window.__lastActiveRegion || 'this region');
            // compute ongoing / dissolved counts for full dataset
            try {
                const allRows = payload.allRows;
                const ongoingCount = allRows.reduce((acc,r) => {
                    const st = (r.status || '').toString().toLowerCase();
                    return acc + (st.includes('ongoing') ? 1 : 0);
                }, 0);
                const dissolvedCount = allRows.reduce((acc,r) => {
                    const st = (r.status || '').toString().toLowerCase();
                    return acc + (st.includes('dissolved') ? 1 : 0);
                }, 0);
                const ongoingEl = fetchEl('rsm-count-ongoing'); if (ongoingEl) ongoingEl.textContent = String(ongoingCount);
                const dissolvedEl = fetchEl('rsm-count-dissolved'); if (dissolvedEl) dissolvedEl.textContent = String(dissolvedCount);
                // replicated/adopted totals for full dataset
                const totalRep = allRows.reduce((acc, r) => acc + (typeof r.with_replicated === 'number' ? r.with_replicated : ((typeof r.with_replicated === 'boolean') ? (r.with_replicated ? 1 : 0) : (String(r.with_replicated||'').toLowerCase().trim() === 'true' ? 1 : 0))), 0);
                const totalAdopt = allRows.reduce((acc, r) => acc + (typeof r.with_adopted === 'number' ? r.with_adopted : ((typeof r.with_adopted === 'boolean') ? (r.with_adopted ? 1 : 0) : (String(r.with_adopted||'').toLowerCase().trim() === 'true' ? 1 : 0))), 0);
                fetchEl('rsm-total-rep').textContent = String(totalRep);
                fetchEl('rsm-total-adopt').textContent = String(totalAdopt);
            } catch(_){ }
            if (typeof initOrUpdateModalStatsChart === 'function') {
                const allRows = payload.allRows;
                const totalMoa = allRows.reduce((acc,r) => acc + (truthy(r.with_moa) ? 1 : 0), 0);
                const totalRes = allRows.reduce((acc,r) => acc + (truthy(r.with_res) ? 1 : 0), 0);
                const totalExpr = allRows.reduce((acc,r) => acc + (truthy(r.with_expr) ? 1 : 0), 0);
                initOrUpdateModalStatsChart([0, totalMoa, totalRes, totalExpr], payload.perYearTotals || null);
            }
        }
    } catch(e){ console.error('resetRsmFilters', e); }
}

// expose handlers (filter buttons removed but functions may still be used elsewhere)
try { window.applyRsmFilters = applyRsmFilters; window.resetRsmFilters = resetRsmFilters; } catch(e) { console.warn('[RSM] failed to expose filter functions', e); }
/*
// binding of filter buttons has been deprecated; UI no longer contains them
try {
    const btnA = document.getElementById('rsm-filter-apply');
    const btnR = document.getElementById('rsm-filter-reset');
    if (btnA) { btnA.addEventListener('click', function(ev){ ev.preventDefault(); console.log('[RSM] apply button clicked'); applyRsmFilters(); }); console.log('[RSM] bound apply button'); }
    else console.warn('[RSM] apply button not found');
    if (btnR) { btnR.addEventListener('click', function(ev){ ev.preventDefault(); resetRsmFilters(); }); console.log('[RSM] bound reset button'); }
    else console.warn('[RSM] reset button not found');
} catch(e) { console.error('[RSM] binding filter buttons failed', e); }
*/

// click delegation no longer checks for apply/reset, but left in place for other potential delegated actions
// document.addEventListener('click', function(ev){
//     try {
//         const a = ev.target && ev.target.closest ? ev.target.closest('#rsm-filter-apply') : null;
//         if (a) { ev.preventDefault(); try { applyRsmFilters(); } catch(e) { console.error('applyRsmFilters failed', e); } return; }
//         const r = ev.target && ev.target.closest ? ev.target.closest('#rsm-filter-reset') : null;
//         if (r) { ev.preventDefault(); try { resetRsmFilters(); } catch(e) { console.error('resetRsmFilters failed', e); } return; }
//     } catch(e) { /* ignore delegation errors */ }
// });


// track whether the user is interacting with any of the filter controls
window._rsmFilterActive = false;
function _setFilterActive(v) { window._rsmFilterActive = !!v; }

// helper to wire focus/blur on native selects and select2 events
function _wireFilterInteraction(id) {
    const el = document.getElementById(id);
    if (el) {
        el.addEventListener('focus', () => _setFilterActive(true));
        el.addEventListener('blur', () => setTimeout(() => _setFilterActive(false), 0));
    }
    if (window.jQuery && jQuery.fn.select2) {
        const $el = jQuery('#' + id);
        if ($el.data('select2')) {
            $el.on('select2:opening', () => _setFilterActive(true));
            $el.on('select2:closing', () => _setFilterActive(false));
        }
    }
}
// wire all three filters early
['rsm-filter-prov','rsm-filter-city','rsm-filter-year'].forEach(_wireFilterInteraction);

// when province picker changes, refresh city list
const provSel = document.getElementById('rsm-filter-prov');
if (provSel) {
    function deriveCurrentRegion(){
        // replicate regionParam derivation from applyRsmFilters
        let region = window.__lastActiveRegion || null;
        if (!region) {
            const provSel = document.getElementById('rsm-filter-prov');
            if (provSel && provSel._lastRegionEvent && provSel._lastRegionEvent.regionName) {
                region = provSel._lastRegionEvent.regionName;
            }
        }
        if (!region) {
            const activeImg = document.querySelector('.swiper-slide.swiper-slide-active .slider-img');
            if (activeImg) {
                region = activeImg.getAttribute('data-region-name') || activeImg.getAttribute('data-region-number');
            }
        }
        if (!region) {
            const modalImg = document.getElementById('rsm-modal-image');
            if (modalImg) region = modalImg.getAttribute('data-region-name');
        }
        return region;
    }

    function updateProvinceFilters(){
        const cityEl2 = document.getElementById('rsm-filter-city');
        const yearEl2 = document.getElementById('rsm-filter-year');
        if (cityEl2) cityEl2.innerHTML = '';
        if (yearEl2) yearEl2.innerHTML = '';

        const provs = _getSelectedValues('rsm-filter-prov');
        let payload = window._lastRsmPayload || {};
        let allRows = Array.isArray(payload.allRows) ? payload.allRows : [];
        console.log('[RSM] province selection, building cities from', provs, 'rows', allRows.length);
        if (!allRows.length) {
            // attempt to fetch payload same as applyRsmFilters does
            const regionParam = deriveCurrentRegion();
            if (regionParam && window.fetchRegionHierarchy) {
                window.fetchRegionHierarchy(regionParam)
                  .then(p => {
                      window._lastRsmPayload = p;
                      payload = p;
                      allRows = Array.isArray(payload.allRows) ? payload.allRows : [];
                      console.log('[RSM] fetched payload for province filter, rows', allRows.length);
                      // rerun the filter logic now that we have rows
                      updateProvinceFilters();
                  }).catch(err=>{ console.error('[RSM] province payload fetch failed', err); });
                return; // bail now; will re-enter when fetch completes
            }
        }

        const citySet = new Set();
        const yearSet = new Set();
        // log unique provinces present in allRows for debugging
        try {
            const uniqueProvs = Array.from(new Set(allRows.map(r=>(r.province||'').toString().trim()))).filter(Boolean);
            console.log('[RSM] provinces in payload', uniqueProvs);
        } catch(e){}
        allRows.forEach(r => {
            const prov = (r.province||'').toString().trim();
            if (!provs.length || provs.some(p=>p.toString().trim() === prov)) {
                const cityName = getRowCity(r);
                if (cityName) citySet.add(cityName);
                const yval = getRowYear(r);
                if (yval) yearSet.add(yval);
            }
        });
        console.log('[RSM] computed cities', Array.from(citySet).sort());


        // helper that rewrites underlying <select> and reinitializes
        // select2 so the dropdown list is up‑to‑date even if already open
        function fillSelect(el, items) {
            if (!el) return;
            // clear existing options
            if (window.jQuery && jQuery(el).data('select2')) {
                const $e = jQuery(el);
                $e.find('option').remove();
                items.forEach(i => $e.append(new Option(i,i)));
                $e.trigger('change'); // tell select2 to refresh
            } else {
                el.innerHTML = items.map(i => `<option value="${i}">${i}</option>`).join('');
            }
        }

        fillSelect(cityEl2, Array.from(citySet).sort());
        fillSelect(yearEl2, Array.from(yearSet).sort());
    }

    // province changes should refresh dependent selects and re-filter
    provSel.addEventListener('change', function(ev){
        setTimeout(()=>{ updateProvinceFilters(); applyRsmFilters(); },0);
    });
    // also update when city or year are selected (no need to rebuild lists)
    const citySel = rsmEl ? rsmEl('rsm-filter-city') : document.getElementById('rsm-filter-city');
    if (citySel) {
        console.log('[RSM] attaching change listener to city select');
        citySel.addEventListener('change', function(ev){
            console.log('[RSM] city select change event');
            setTimeout(applyRsmFilters,0);
        });
    } else {
        console.warn('[RSM] city select not found when binding listener');
    }
    const yearSel = rsmEl ? rsmEl('rsm-filter-year') : document.getElementById('rsm-filter-year');
    if (yearSel) {
        console.log('[RSM] attaching change listener to year select');
        yearSel.addEventListener('change', function(ev){
            console.log('[RSM] year select change event');
            setTimeout(applyRsmFilters,0);
        });
    } else {
        console.warn('[RSM] year select not found when binding listener');
    }

    // also bind for select2 events: handle all three selects so users
    // picking values via the UI will auto‑apply filters without extra clicks.
    if (window.jQuery) {
        const $prov = jQuery(provSel);
        if ($prov.data('select2')) {
            $prov.on('change.select2 select2:select select2:unselect', function(ev){
                console.log('[RSM] select2 province change event');
                setTimeout(()=>{ updateProvinceFilters(); applyRsmFilters(); },0);
            });
        }
        if (citySel && jQuery(citySel).data('select2')) {
            jQuery(citySel).on('change.select2 select2:select select2:unselect', function(){
                console.log('[RSM] select2 city change event');
                setTimeout(applyRsmFilters,0);
            });
        }
        if (yearSel && jQuery(yearSel).data('select2')) {
            jQuery(yearSel).on('change.select2 select2:select select2:unselect', function(){
                console.log('[RSM] select2 year change event');
                setTimeout(applyRsmFilters,0);
            });
        }

        // delegated listeners in case select2 or other code replaces the elements
        jQuery(document).on('change', '#rsm-filter-city', function(){
            console.log('[RSM] delegated change on city');
            setTimeout(applyRsmFilters,0);
        });
        jQuery(document).on('change', '#rsm-filter-year', function(){
            console.log('[RSM] delegated change on year');
            setTimeout(applyRsmFilters,0);
        });
        jQuery(document).on('select2:select select2:unselect', '#rsm-filter-city', function(){
            console.log('[RSM] delegated select2 event on city');
            setTimeout(applyRsmFilters,0);
        });
        jQuery(document).on('select2:select select2:unselect', '#rsm-filter-year', function(){
            console.log('[RSM] delegated select2 event on year');
            setTimeout(applyRsmFilters,0);
        });
    }

    // fallback listener on document in case select2 replaces the element
    document.addEventListener('change', function(ev){
        if (ev.target && ev.target.id === 'rsm-filter-prov') {
            setTimeout(()=>{ updateProvinceFilters(); applyRsmFilters(); },0);
        }
        if (ev.target && (ev.target.id === 'rsm-filter-city' || ev.target.id === 'rsm-filter-year')) {
            setTimeout(applyRsmFilters,0);
        }
    });

    // expose the province helper globally so other early code (select2 init,
    // inline buttons, messages from parent etc.) can call it without needing
    // to know about the local scope above.
    try { window.updateProvinceFilters = updateProvinceFilters; } catch(e) {}
}

// store last region event on province element for use in change handler
// also reapply filters whenever the active region changes so the
// ongoing/dissolved cards reflect the new region immediately.
document.addEventListener('sliderActiveRegionChanged', function(e){
    const provSel2 = document.getElementById('rsm-filter-prov');
    if (provSel2 && e && e.detail) provSel2._lastRegionEvent = e.detail;
    // refresh the data for the new region
    try {
        if (typeof updateProvinceFilters === 'function') updateProvinceFilters();
    } catch(_){ }
    try { if (typeof applyRsmFilters === 'function') applyRsmFilters(); } catch(_){ }
});

// run filters once on initial load so the cards aren't left at zero
// when the view first appears (payload fetch will occur automatically)
document.addEventListener('DOMContentLoaded', function(){
    try { if (typeof applyRsmFilters === 'function') applyRsmFilters(); } catch(_){ }
});

// Lightweight polling watcher: keep filters and cards in sync even when
// Select2 or parent-frame code changes values without firing our local
// handlers. Because region payloads are now cached by
// window.fetchRegionHierarchy, this will NOT spam the AJAX endpoint; it
// simply calls applyRsmFilters using the existing payload.
(function(){
    let lastProvs = '';
    let lastCities = '';
    let lastYears = '';
    setInterval(function(){
        const provs = _getSelectedValues('rsm-filter-prov');
        const cities = _getSelectedValues('rsm-filter-city');
        const years = _getSelectedValues('rsm-filter-year');
        const provsStr = provs.join('|');
        const citiesStr = cities.join('|');
        const yearsStr = years.join('|');
        let changed = false;
        if (provsStr !== lastProvs) {
            lastProvs = provsStr;
            try { if (typeof updateProvinceFilters === 'function') updateProvinceFilters(); } catch(e){}
            changed = true;
        }
        if (citiesStr !== lastCities) {
            lastCities = citiesStr;
            changed = true;
        }
        if (yearsStr !== lastYears) {
            lastYears = yearsStr;
            changed = true;
        }
        if (changed) {
            try { if (typeof applyRsmFilters === 'function') applyRsmFilters(); } catch(e){}
        }
    }, 500);
})();

// global replicate popover helper (defined early so it exists even if DOMContentLoaded has passed)
function showReplicateConfirmPopover(targetEl, stInfo = {}) {
    try {
        // remove any existing popover
        const existing = document.body.querySelector('.replicate-popover');
        if (existing) existing.remove();
        const pop = document.createElement('div');
        // inline styles for independency
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

        // style buttons
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

        // position relative to target element
        const r = targetEl.getBoundingClientRect();
        const measure = () => {
            const pw = pop.offsetWidth;
            const ph = pop.offsetHeight;
            let left = r.left + (r.width/2) - (pw/2);
            let top = r.top - ph - 8;
            if (top < 8) top = r.bottom + 8;
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

// ensure global reference exists immediately
try { window.showReplicateConfirmPopover = showReplicateConfirmPopover; } catch(e) {}

// helper used in multiple places to derive a canonical region text from
// slider elements or event details. placed here at the top so it's
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
		if (s.includes('calabarzon')) return 'Region IV-A';
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

	// centre the swiper on a particular region label; uses slideToLoop when
	// available so the correct duplicate/clone index is chosen. silence errors
	// if swiper isn't ready yet.
	function centerSliderOnRegion(region){
		if (!region || !swiper) return null;
		var matchedImg = null;
		// suppress any height/collapse messaging triggered by the programmatic move
		window._suppressHeightChange = true;
		try {
			var slides = swiper.slides || [];
			for (var i = 0; i < slides.length; i++) {
				var img = slides[i].querySelector && slides[i].querySelector('.slider-img');
				if (!img) continue;
				var nm = normalizeRegionText(img.getAttribute('data-region-name')||'');
				if (nm === region) {
					matchedImg = img;
					if (swiper.slideToLoop) {
						swiper.slideToLoop(i, 0);
					} else if (swiper.slideTo) {
						swiper.slideTo(i, 0);
					}
					break;
				}
			}
		} finally {
			window._suppressHeightChange = false;
		}
		if (!matchedImg) {
			console.warn('[slider] centerSliderOnRegion could not find region', region,
				'slides count', (swiper.slides && swiper.slides.length));
		}
		return matchedImg;
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
			const region = filenameToRegion[fileName] || img.getAttribute('data-region-name') || ('Region ' + (img.getAttribute('data-region-number')||''));
			if (regions.includes(region)) {
				slide.style.display = '';
			} else {
				slide.style.display = 'none';
			}
		});
		// After toggling visibility run swiper update so it recalculates sizes.
		// also reset position to first visible slide without an animation to avoid
		// the “ghost extension” that was seen when filters were applied.  If the
		// carousel is running in loop mode we also destroy/recreate the loop so the
		// clones reflect the new subset of visible slides; failing to do this used
		// to let a hidden duplicate peek out at the edge.
		if (swiper && typeof swiper.update === 'function') {
			// avoid height changes while updating and sliding
			window._suppressHeightChange = true;
			try {
				swiper.update();
				if (swiper.params && swiper.params.loop) {
					swiper.loopDestroy && swiper.loopDestroy();
					swiper.loopCreate && swiper.loopCreate();
				}
				// center on first remaining slide for a sane default
				const all = swiper.slides || [];
				for (let i = 0; i < all.length; i++) {
					if (all[i].style.display !== 'none') {
						swiper.slideTo(i, 0);
						break;
					}
				}
			} finally {
				window._suppressHeightChange = false;
			}
		}
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
			// after filtering make sure the slider centres on the last item in the
			// selection (typically the one the user just picked)
			// centerSliderOnRegion(selected[selected.length-1]); // DISABLED: Only update slider on reload, not dynamically
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
			// filterSliderByRegions(regs); // DISABLED: do not touch slider dynamically
			filterGalleryCards(regs, yrs);
			// centerSliderOnRegion(regs[regs.length-1]); // DISABLED: Only update slider on reload, not dynamically
			// update graph for the most recently selected region. avoid triggering a
			// click event on the image since that was causing the swipe component
			// to “twitch” and show part of the neighbouring slide; instead move the
			// swiper programmatically then render the stats.
			if (false) {
				var target = regs[regs.length-1];
				// clear any cached payload so render runs fresh
				window._lastRsmPayload = null;
				var imgs = document.querySelectorAll('.slider-img');
				for (var i = 0; i < imgs.length; i++) {
					var nm = normalizeRegionText(imgs[i].getAttribute('data-region-name') || '');
					if (nm === target) {
						if (swiper && typeof swiper.slideTo === 'function') {
							var slideEl = imgs[i].closest('.swiper-slide');
							var idx = Array.prototype.indexOf.call(swiper.slides, slideEl);
							if (idx >= 0) swiper.slideTo(idx, 0);
						}
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
		// (filters are reset by the parent when it collapses; avoid clearing
		// here during normal slide movement because it interferes with users
		// opening the dropdowns)
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
                        (window.fetchRegionHierarchy ? window.fetchRegionHierarchy(regionParam) : Promise.reject(new Error('fetchRegionHierarchy missing')))
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
		// bail out if the user is currently interacting with a filter
		if (window._rsmFilterActive) return;
		// previous focus-based guard for safety (rare cases)
		try {
			const active = document.activeElement;
			if (active) {
				const id = active.id || '';
				if (id === 'rsm-filter-prov' || id === 'rsm-filter-city' || id === 'rsm-filter-year') {
					return;
				}
				if (active.closest && active.closest('.select2-container')) {
					return;
				}
			}
		} catch(e) { /* ignore focus detection errors */ }
		try {
			if (window.parent && window.parent !== window && window.parent.postMessage) {
				window.parent.postMessage({ type:'streportToggleHeight', height:'600px' }, '*');
			}
		} catch(_e) { }
		// filters will be cleared by the parent once collapse completes
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
		'4a': 'CALABARZON',
		'4b': 'MIMAROPA',
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
        // Use cached AJAX helper which returns {provinces, grouped, allRows, uploadedCount...}
        (window.fetchRegionHierarchy ? window.fetchRegionHierarchy(regionKey) : Promise.reject(new Error('fetchRegionHierarchy missing')))
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
            (window.fetchRegionHierarchy ? window.fetchRegionHierarchy(regionParam) : Promise.reject(new Error('fetchRegionHierarchy missing')))
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
</script>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

@if(isset($galleryCards) && $galleryCards->count())
<section class="card-gallery" style="--card-bg:#fff; position:relative; z-index:60; pointer-events:auto;">
    <div class="container-cards" style="pointer-events:auto;">
        @foreach($galleryCards as $card)
            @php
                // include all top-level children (show inactive children as muted in the popover)
                $__cardChildren = $card->children
                    ->filter(function($c){ return is_null($c->parent_child_id); })
                    ->map(function($c){
                        return [
                            'title' => $c->title,
                            'url' => $c->url,
                            'active' => (int) $c->is_active,
                            'status' => $c->status ?? 'On going',
                            'children' => ($c->children && $c->children->count())
                                ? $c->children->map(function($s){ return ['title' => $s->title, 'url' => $s->url, 'active' => (int) $s->is_active, 'status' => $s->status ?? 'On going']; })->values()
                                : []
                        ];
                    })->values();
            @endphp
            <a class="card card-link" href="{{ $card->url ?? '#' }}" data-href="{{ $card->url ?? '#' }}" data-title="{{ $card->title }}" aria-label="{{ $card->title }}" data-children='@json($__cardChildren)'>
                <div class="imgContainer" style= "background-color: transparent !important; border: none !important;">
                    <div class="logo-badge">
                        @if($card->image)
                            <img src="{{ asset('storage/' . $card->image) }}" alt="{{ $card->title }} logo">
                        @elseif($card->icon_class)
                            <i class="{{ $card->icon_class }}" style="font-size:48px;color:#4da1f7;"></i>
                        @else
                            <div style="width:72px;height:72px;border-radius:999px;background:#f1f5f9;"></div>
                        @endif
                    </div>
                </div>
                <div class="content">
                    <h2>{{ $card->title }}</h2>
                    <p>{{ $card->description }}</p>
                </div>
            </a>
        @endforeach
    </div>
</section>



<!-- Gallery children modal (opens on gallery card click) -->
<div id="galleryChildrenModal" class="gallery-children-modal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="galleryChildrenModalTitle" style="display:none;">
  <div class="gcm-backdrop" data-action="close" style="position:fixed; inset:0; background:rgba(0,0,0,0.45);"></div>
  <div class="gcm-panel" style="position:fixed; left:50%; top:50%; transform:translate(-50%,-50%); width:min(820px,calc(100% - 48px)); max-height:80vh; overflow:auto; background:#fff; border-radius:12px; padding:18px; box-shadow:0 18px 60px rgba(2,6,23,0.12); z-index:100000;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;">
      <h3 style="margin:0;font-size:1.05rem;"><a id="galleryChildrenModalTitle" href="#" target="_blank" rel="noopener noreferrer" style="color:#0369a1;text-decoration:none;display:inline-block;cursor:pointer;"></a></h3>
      <div style="display:flex;gap:8px;align-items:center;">
        <button id="gcm-close" aria-label="Close" style="background:transparent;border:none;font-size:20px;cursor:pointer;">✕</button>
      </div>
    </div>
    <div id="gcm-body" class="gcm-body" style="font-size:0.95rem;color:#0f1724;"></div>
  </div>
</div>

<!-- Popover: shown anchored to the clicked card (used instead of the centered modal) -->
<div id="galleryPopover" class="gallery-popover" role="dialog" aria-hidden="true"
     style="position:absolute; display:none; z-index:100250; min-width:260px; max-width:420px; width:fit-content; background:#fff; border-radius:12px; padding:12px; box-shadow:0 18px 60px rgba(2,6,23,0.12);">
  <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px;">
    <a id="galleryPopoverTitle" href="#" target="_blank" rel="noopener noreferrer" style="font-weight:700;color:#0369a1;text-decoration:none;display:block;cursor:pointer;"> </a>
    <div style="display:flex;gap:6px;align-items:center;">
      <button id="galleryPopoverClose" aria-label="Close popover" style="background:transparent;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
  </div>
  <div id="galleryPopoverBody" style="font-size:0.95rem;color:#0f1724; max-height:60vh; overflow:auto;"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const modal = document.getElementById('galleryChildrenModal');
  const modalTitle = document.getElementById('galleryChildrenModalTitle');
  const modalBody = document.getElementById('gcm-body');
  const modalClose = document.getElementById('gcm-close');


  function escapeHtml(s){ return (s||'').toString().replace(/[&<>"]/g, function(c){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'})[c]; }); }
  function escapeAttr(s){ return escapeHtml(s).replace(/"/g,'&quot;'); }

  // helper: returns true when gallery popover is currently visible
  function isPopoverOpen(){
    try {
      if (window.__galleryPopoverActive) return true; // defensive global
      const p = document.getElementById('galleryPopover');
      return p && p.getAttribute('aria-hidden') === 'false';
    } catch(e){ return !!window.__galleryPopoverActive; }
  }

  function renderChildrenTree(items){
    if (!items || !items.length) return '<div class="gcm-empty" style="color:#6b7280;">No items</div>';
    let html = '<div class="gcm-list" style="display:flex;flex-direction:column;gap:10px;">';
    items.forEach((m,mi) => {
      // default: render all mother rows expanded so child + sub-child lists are visible
      // render the mother title as a clickable link (if a URL exists) and keep a separate toggle button
      html += `<div class="gcm-mother" style="display:flex;flex-direction:column;gap:6px;">`;
      if (m.url) {
        html += `<div style="display:flex;align-items:center;gap:8px;"><a class="gcm-mother-link" href="${escapeAttr(m.url||'#')}" target="_blank" rel="noopener noreferrer" style="display:block;width:100%;text-align:left;font-weight:700;font-size:1rem;color:#0369a1;text-decoration:none;padding:4px 0;cursor:pointer;">${escapeHtml(m.title)}</a><button class="gcm-mother-toggle" aria-expanded="true" data-index="${mi}" style="background:transparent;border:none;cursor:pointer;color:#0369a1;font-size:0.95rem;padding:4px 6px;"></button></div>`;
      } else {
        // no URL: render the mother title as a toggle but keep it visually blue
      html += `<button class="gcm-mother-toggle" aria-expanded="true" data-index="${mi}" style="text-align:left;background:transparent;border:none;font-weight:700;font-size:1rem;cursor:pointer;color:#0369a1;">${escapeHtml(m.title)}</button>`;
      }
      if (m.children && m.children.length) {
        // show child list by default
        html += '<ul class="gcm-child-list" style="display:block;margin:4px 0 0 12px;padding-left:0;list-style:none;">';
        m.children.forEach(c => {
          // render active children as links, inactive as muted/plain text
          if (c.active === 1 || c.active === '1' || c.active === true) {
            html += `<li style="margin-bottom:6px;"><a href="${escapeAttr(c.url||'#')}" target="_blank" rel="noopener noreferrer" style="color:#0369a1;text-decoration:none;">${escapeHtml(c.title)}</a>`;
          } else {
            html += `<li style="margin-bottom:6px;"><span style="color:#9ca3af;">${escapeHtml(c.title)}</span>`;
          }

          if (c.children && c.children.length) {
            // render sub-children; inactive sub-children are muted
            html += '<ul class="gcm-subchild-list" style="margin-top:6px;margin-left:12px;list-style:none;padding-left:0;">';
            c.children.forEach(sc => {
              if (sc.active === 1 || sc.active === '1' || sc.active === true) {
                html += `<li style="margin-bottom:4px;"><a href="${escapeAttr(sc.url||'#')}" target="_blank" rel="noopener noreferrer" style="color:#475569;text-decoration:none;font-size:0.95rem;">${escapeHtml(sc.title)}</a></li>`;
              } else {
                html += `<li style="margin-bottom:4px;"><span style="color:#9ca3af;font-size:0.95rem;">${escapeHtml(sc.title)}</span></li>`;
              }
            });
            html += '</ul>';
          }
          html += '</li>';
        });
        html += '</ul>';
      }
      html += '</div>';
    });
    html += '</div>';
    return html;
  }

  // open a small popover anchored to the clicked card (preferred over the centered modal)
  function openModalForCard(card){
    // when popover is opening we want to lock the scrolling/wrapping logic so the track
    // doesn't jump (especially for cards near the duplicated boundary). resume after close.
    wrapSuspended = true;

    // defensive: ensure any previously-open popover is fully cleaned up before opening a new one
    try { closePopover(); } catch(e){}

    const title = card.dataset.title || card.getAttribute('aria-label') || '';
    const href = card.dataset.href || card.getAttribute('href') || '#';
    let items = [];
    try { items = card.dataset.children ? JSON.parse(card.dataset.children) : []; } catch(e){ console.error('[STsReport] failed to parse card.dataset.children', card && card.dataset && card.dataset.children, e); items = []; }

    // fallback for duplicated/cloned cards that may not carry data-children:
    if ((!items || !items.length) && card && card.dataset && (card.dataset.title || card.dataset.href)) {
        try {
            const keyTitle = card.dataset.title || '';
            const keyHref = card.dataset.href || '';
            const candidates = Array.from(document.querySelectorAll('.card-link'));
            const source = candidates.find(el => (el.dataset && el.dataset.children) && (el.dataset.title === keyTitle || el.dataset.href === keyHref));
            if (source && source.dataset && source.dataset.children) {
                try { items = JSON.parse(source.dataset.children || '[]'); } catch(e) { items = items || []; }
            }
        } catch(e) { /* ignore fallback errors */ }
    }

    // populate popover
    const pop = document.getElementById('galleryPopover');
    const popBody = document.getElementById('galleryPopoverBody');
    const popTitle = document.getElementById('galleryPopoverTitle');

    popTitle.textContent = title;
    try { popTitle.href = href || '#'; popTitle.setAttribute('aria-label', title); } catch(e) {}
    // Render popover with two status panes ("On going" / "Completed"). Mothers remain top-level headers in both views.
    function renderChildrenByStatus(list, status){
      if (!Array.isArray(list)) return '<div class="gcm-empty" style="color:#6b7280;">No items</div>';
      let sections = '';

      // Include mother when ANY descendant (child OR sub-child) matches the status.
      list.forEach((m) => {
        const children = Array.isArray(m.children) ? m.children : [];

        // For each top-level child determine if it should be shown:
        // - show if child.status matches OR any of its sub-children match
        const childrenToShow = children.map(c => {
          const subs = Array.isArray(c.children) ? c.children : [];
          const matchingSubs = subs.filter(sc => (sc.status||'On going') === status);
          const childMatches = (c.status||'On going') === status;
          if (childMatches || matchingSubs.length) return { child: c, matchingSubs };
          return null;
        }).filter(Boolean);

        // if this `m` is a grouped mother (has nested children) we only show it when
        // some descendant matches; otherwise, if m itself is a standalone item (no nested children)
        // show it when its own status matches the selected status.
        if (children.length > 0) {
          if (!childrenToShow.length) return;
        } else {
          // standalone entry: treat `m` itself as an item (show only when m.status matches)
          if ((m.status || 'On going') !== status) return;
        }

        let motherHtml = '<div class="gcm-mother" style="display:flex;flex-direction:column;gap:6px;">';
        // only render a header when this entry is a grouped mother (has nested children)
        if (children.length > 0) {
          if (m.url) motherHtml += `<div style="display:flex;align-items:center;gap:8px;"><a href="${escapeAttr(m.url||'#')}" target="_blank" rel="noopener noreferrer" style="font-weight:700;color:#0369a1;text-decoration:none;">${escapeHtml(m.title)}</a></div>`;
          else motherHtml += `<div style="font-weight:700;color:#0369a1;">${escapeHtml(m.title)}</div>`;
        }

        motherHtml += '<ul style="margin:4px 0 0 12px;padding-left:0;list-style:none;">';

        // if this `m` has no nested children but `m` itself matches the status,
        // render `m` as a standalone list item so top-level entries (like SWPDOP) appear
        if (children.length === 0 && (m.status || 'On going') === status) {
          if (m.url && m.url.trim()) {
            motherHtml += `<li style="margin-bottom:6px;"><a href="${escapeAttr(m.url)}" target="_blank" rel="noopener noreferrer" style="color:#0369a1;text-decoration:none;">${escapeHtml(m.title)}</a></li>`;
          } else {
            motherHtml += `<li style="margin-bottom:6px;"><span style="color:#475569;">${escapeHtml(m.title)}</span></li>`;
          }
        }

        childrenToShow.forEach(entry => {
          const c = entry.child;
          const matchingSubs = entry.matchingSubs;

          // Render child — clickable if it has a URL, otherwise plain text.
          if (c.url && c.url.trim()) {
            motherHtml += `<li style="margin-bottom:6px;"><a href="${escapeAttr(c.url)}" target="_blank" rel="noopener noreferrer" style="color:#0369a1;text-decoration:none;">${escapeHtml(c.title)}</a>`;
          } else {
            motherHtml += `<li style="margin-bottom:6px;"><span style="color:#475569;">${escapeHtml(c.title)}</span>`;
          }

          // If the child itself matches the requested status, show its matching sub-children as well (if any)
          if (matchingSubs && matchingSubs.length) {
            motherHtml += '<ul style="margin-top:6px;margin-left:12px;list-style:none;padding-left:0;">';
            matchingSubs.forEach(sc => {
              if (sc.url && sc.url.trim()) {
                motherHtml += `<li style="margin-bottom:4px;"><a href="${escapeAttr(sc.url)}" target="_blank" rel="noopener noreferrer" style="color:#475569;text-decoration:none;font-size:0.95rem;">${escapeHtml(sc.title)}</a></li>`;
              } else {
                motherHtml += `<li style="margin-bottom:4px;"><span style="color:#475569;font-size:0.95rem;">${escapeHtml(sc.title)}</span></li>`;
              }
            });
            motherHtml += '</ul>';
          }

          motherHtml += '</li>';
        });

        motherHtml += '</ul>';
        motherHtml += '</div>';

        sections += motherHtml;
      });

      if (!sections) return '<div class="gcm-empty" style="color:#6b7280;">No items in this status</div>';
      return `<div class="gcm-list" style="display:flex;flex-direction:column;gap:10px;">${sections}</div>`;
    }

    // build the tabbed status popover (hover over tabs switches view)
    const tabHtml = `
      <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px;">
        <button class="gcm-status-btn active" data-status="Completed" style="padding:6px 10px;border-radius:8px;border:1px solid rgba(2,6,23,0.06);background:linear-gradient(180deg,#ffffff,#f8fafc);cursor:pointer;font-weight:600;color:#0b2540;">Completed</button>
        <button class="gcm-status-btn" data-status="On going" style="padding:6px 10px;border-radius:8px;border:1px solid rgba(2,6,23,0.06);background:transparent;cursor:pointer;font-weight:600;color:#6b7280;">On going</button>
      </div>
      <div class="gcm-status-views">
        <div class="gcm-status-view" data-status="Completed">${renderChildrenByStatus(items, 'Completed')}</div>
        <div class="gcm-status-view" data-status="On going" style="display:none">${renderChildrenByStatus(items, 'On going')}</div>
      </div>
    `;

    popBody.innerHTML = tabHtml;

    // wire hover / click behavior for the status buttons
    try {
      const tabs = popBody.querySelectorAll('.gcm-status-btn');
      const views = popBody.querySelectorAll('.gcm-status-view');
      tabs.forEach(t => {
        const showFor = (st) => {
          tabs.forEach(x => { x.classList.toggle('active', x === t); x.style.background = (x === t) ? 'linear-gradient(180deg,#ffffff,#f8fafc)' : 'transparent'; x.style.color = (x === t) ? '#0b2540' : '#6b7280'; } );
          views.forEach(v => v.style.display = (v.getAttribute('data-status') === st) ? 'block' : 'none');
        };
        t.addEventListener('mouseenter', () => showFor(t.getAttribute('data-status')) );
        t.addEventListener('focus', () => showFor(t.getAttribute('data-status')) );
        t.addEventListener('click', (ev) => { ev.preventDefault(); showFor(t.getAttribute('data-status')); });
      });
    } catch(e){ console.error('status tabs wiring failed', e); }

    // also ensure the centered modal title (fallback) has the href available
    try { if (modalTitle) { modalTitle.textContent = title; modalTitle.href = href || '#'; modalTitle.setAttribute('aria-label', title); } } catch(e) {}

    // show & position popover
    pop.setAttribute('aria-hidden','false');
    pop.style.display = 'block';

    // mark popover anchor and pause gallery autoscroll while popover is open
    try { window.__galleryPopoverActive = true; } catch(e){}
    try { pop._anchor = card; } catch(e){}
    try { pop._anchorKey = (card && (card.dataset && (card.dataset.title || card.dataset.href))) || ''; } catch(e){}

    // add lifted class to all matching (duplicated) cards so the extension remains visible while popover is open
    try {
        const key = pop._anchorKey || '';
        if (key) {
            document.querySelectorAll('.card-link').forEach(function(el){
                try { if ((el.dataset && (el.dataset.title === key || el.dataset.href === key)) ) el.classList.add('card-lifted'); } catch(e){}
            });
        }
    } catch(e){}

    try { _clearAutoResume(); } catch(e){}
    try { running = false; scroller.classList.add('autoscroll-paused'); } catch(e){}
    // pause CSS animation (if present) and freeze transform-track offset when in transform-mode
    try { const track = document.querySelector('.marquee-track'); if (track) { track.style.animationPlayState = 'paused'; track.dataset.frozen = '1'; } } catch(e){}
    // cancel any hover-snap so the popover holds the gallery steady
    try { cancelHoverSnap(card); } catch(e){}

    // Prefer to anchor the popover to the card's right edge ("extension edge").
    // If insufficient horizontal space, flip to the left; otherwise center as a fallback.
    const rect = card.getBoundingClientRect();
    // ensure pop is rendered to compute size
    const popRect = pop.getBoundingClientRect();
    const margin = 8;

    // primary placement: to the RIGHT of the card (aligned vertically center)
    let left = Math.round(rect.right + margin);
    let top = Math.round(rect.top + (rect.height - popRect.height) / 2);

    // if right-side placement would overflow, try left-side placement
    if (left + popRect.width > window.innerWidth - margin) {
      const leftAlt = Math.round(rect.left - popRect.width - margin);
      if (leftAlt >= margin) {
        left = leftAlt;
      } else {
        // final fallback: horizontally center near the card (clamped)
        left = Math.round(rect.left + (rect.width / 2) - (popRect.width / 2));
        left = Math.max(margin, Math.min(left, window.innerWidth - popRect.width - margin));
      }
    }

    // clamp vertical position so popover remains visible
    top = Math.max(margin, Math.min(top, window.innerHeight - popRect.height - margin));

    // account for document scroll
    pop.style.left = (left + window.pageXOffset) + 'px';
    pop.style.top = (top + window.pageYOffset) + 'px';

    // focus first interactive element inside popover and ensure the
    // extension itself is scrolled into view (use card for anchor if pop
    // is absolutely positioned offscreen). doing this after a timeout
    // lets layout settle.
    setTimeout(() => {
      try {
        // simple attempt first; keeps horizontal centering
        pop.scrollIntoView({ behavior:'smooth', block:'center', inline:'nearest' });
      } catch(e) {}
      try {
        // ensure the underlying card is visible as well
        card && card.scrollIntoView({ behavior:'smooth', block:'center', inline:'nearest' });
      } catch(e) {}
      // additionally make sure the *entire* popover rectangle is within
      // the viewport. if the element is taller than the viewport we can't fit
      // it fully, so instead aim to center the popover's midpoint in the
      // window (this brings the middle body area into view).
      try {
        const r = pop.getBoundingClientRect();
        const margin = 12;
        if (r.height <= window.innerHeight) {
          if (r.top < margin) {
            window.scrollBy({ top: r.top - margin, behavior:'smooth' });
          } else if (r.bottom > window.innerHeight - margin) {
            window.scrollBy({ top: r.bottom - window.innerHeight + margin, behavior:'smooth' });
          }
        } else {
          // popover taller than viewport: center its midpoint
          const popCenter = r.top + r.height/2;
          const goal = window.innerHeight/2;
          window.scrollBy({ top: popCenter - goal, behavior:'smooth' });
        }
        // finally, if there is an rsm-header within the popover, ensure its
        // own midpoint is centered (this targets the header specifically).
        // because you want to see more of the header's body, we add an extra
        // downward offset equal to half the header height; this pushes the
        // midpoint further down the screen so the middle of the header is
        // clearly visible rather than being right at the top edge.
        const hdr = pop.querySelector('.rsm-header');
        if (hdr) {
          const hr = hdr.getBoundingClientRect();
          const hdrCenter = hr.top + hr.height/2;
          const winCenter = window.innerHeight/2;
          const extra = hr.height/2; // additional scroll amount
          window.scrollBy({ top: hdrCenter - winCenter + extra, behavior:'smooth' });
        }
        // finally, nudge the whole popover midpoint down a bit more so the
        // true centre of the extension is visible rather than the very top
        try {
          const pr = pop.getBoundingClientRect();
          const popMid = pr.top + pr.height/2;
          const winMid = window.innerHeight/2;
          const extra2 = pr.height/4; // push the midpoint further down
          window.scrollBy({ top: popMid - winMid + extra2, behavior:'smooth' });
        } catch(_e) {}
      } catch(_e) {}
      // ensure the popover container itself can receive focus for overall
      // keyboard interactions; this makes the whole extension 'active'.
      try {
        pop.tabIndex = -1;
        pop.focus();
      } catch(e) {}
      const first = pop.querySelector('a,button,[tabindex]');
      if (first) first.focus();
    }, 0);

    // attach one-time handlers to close popover on outside click / Escape / resize / scroll
    function _docClick(ev){ if (!pop.contains(ev.target) && !card.contains(ev.target)) closePopover(); }
    function _onKey(ev){ if (ev.key === 'Escape') closePopover(); }
    function _onScroll(){ closePopover(); }

    document.addEventListener('click', _docClick);
    document.addEventListener('keydown', _onKey);
    window.addEventListener('resize', _onScroll);
    (document.querySelector('.container-cards') || window).addEventListener('scroll', _onScroll, { passive:true });

    // wire close button
    const popCloseBtn = document.getElementById('galleryPopoverClose');
    if (popCloseBtn) popCloseBtn.onclick = closePopover;

    // store cleanup references so closePopover can remove them
    pop._cleanup = { _docClick, _onKey, _onScroll };
  }

  function closeModal(){
    // keep the centered modal available as fallback — hide both
    try { closePopover(); } catch(e){}
    modal.setAttribute('aria-hidden','true');
    modal.style.display = 'none';
  }

  function closePopover(){
    const pop = document.getElementById('galleryPopover');
    if (!pop || pop.getAttribute('aria-hidden') === 'true') return;
    pop.setAttribute('aria-hidden','true');
    pop.style.display = 'none';
    // remove attached handlers
    try {
      const c = pop._cleanup || {};
      if (c._docClick) document.removeEventListener('click', c._docClick);
      if (c._onKey) document.removeEventListener('keydown', c._onKey);
      if (c._onScroll) window.removeEventListener('resize', c._onScroll);
      const sc = document.querySelector('.container-cards') || window;
      sc.removeEventListener('scroll', c._onScroll);
    } catch(e){}
    pop._cleanup = null;

    // defensive cleanup: remove any leftover lifted state that could block interaction
    try { document.querySelectorAll('.card-link.card-lifted').forEach(el => el.classList.remove('card-lifted')); } catch(e){}
    try {
        // remove 'card-lifted' from any duplicated cards that matched the popover anchor key
        const anchorKey = pop && pop._anchorKey ? pop._anchorKey : null;
        if (anchorKey) {
            document.querySelectorAll('.card-link').forEach(function(el){
                try { if (el.dataset && (el.dataset.title === anchorKey || el.dataset.href === anchorKey)) el.classList.remove('card-lifted'); } catch(e){}
            });
        }
    } catch(e){}

    try { pop._anchor = null; pop._anchorKey = null; } catch(e){}
    try { window.__galleryPopoverActive = false; } catch(e){}
    // allow wrapping again once the popover fully closes
    wrapSuspended = false;
    // restore CSS animation / thaw transform-track
    try { const track = document.querySelector('.marquee-track'); if (track) { track.dataset.frozen = '0'; track.style.animationPlayState = ''; } } catch(e){}
    // allow auto-resume (but only if no extension/popover remains open) — schedule rather than immediate
    try { _scheduleAutoResume(); } catch(e){}
  }

  // automatically close any open gallery extension when the slider's active region changes
  document.addEventListener('sliderActiveRegionChanged', function(e){
      try { closePopover(); } catch(e){}
      let region = e && e.detail && e.detail.regionName ? e.detail.regionName : null;
      if (!region && e && e.detail) {
          // try to derive a name from the detail object
          region = deriveRegionFromSlider(e.detail) || null;
      }
      // remember last active region for other components (e.g. card click logging)
      window.__lastActiveRegion = region;

      // log available years, provinces, and cities for the new region
      if (region && window.parent && window.parent.regionMap) {
          const norm = normalizeRegionText(region);
          let map = window.parent.regionMap[norm];
          if (!map) {
              // fallback to any matching key
              Object.keys(window.parent.regionMap || {}).some(k => {
                  if (normalizeRegionText(k) === norm) {
                      map = window.parent.regionMap[k];
                      return true;
                  }
                  return false;
              });
          }
          const yrs = (map && map.years ? (map.years.slice().sort()) : []);
          const provs = Object.keys(map.provinces || {}).sort();
          const allCities = new Set();
          provs.forEach(p=>{
              (map.provinces[p]||[]).forEach(c=>allCities.add(c));
          });
          const cities = Array.from(allCities).sort();
          console.log('[RSM] available region data', region, { years: yrs, provinces: provs, cities: cities });
      }
      // update filter dropdowns when region changes
      populateRegionFilters(region);
  });


  // helper to derive a canonical region name from slider data or image
  // (duplicate from top, kept for safety but likely unused since global)
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

  // helper to populate province/city/year selects based on regionMap
  function populateRegionFilters(region) {
      const provEl = document.getElementById('rsm-filter-prov');
      const cityEl = document.getElementById('rsm-filter-city');
      const yearEl = document.getElementById('rsm-filter-year');
      // reset
      if (provEl) provEl.innerHTML = '';
      if (cityEl) cityEl.innerHTML = '';
      if (yearEl) yearEl.innerHTML = '';
      // Prefer client-provided regionMap (populated by parent). If missing
      // or empty, fetch the aggregated JSON from the server endpoint so
      // selects can be populated even when embedded or when regionMap
      // hasn't been preloaded.
      const norm = region ? normalizeRegionText(region) : '';
      const keys = Object.keys((window.parent && window.parent.regionMap) ? window.parent.regionMap : {});
      let map = (window.parent && window.parent.regionMap) ? window.parent.regionMap[norm] : null;

      function fillFromMap(m) {
          m = m || {};
          const provs = Object.keys(m.provinces || {}).sort();
          provs.forEach(p => { if (provEl) provEl.appendChild(new Option(p, p)); });
          // gather all cities across provinces for full-region list
          const allCities = new Set();
          provs.forEach(p => { (m.provinces[p] || []).forEach(c => allCities.add(c)); });
          Array.from(allCities).sort().forEach(c => { if (cityEl) cityEl.appendChild(new Option(c, c)); });
          const yrs = (m.years || []).slice().sort();
          yrs.forEach(y => { if (yearEl) yearEl.appendChild(new Option(y, y)); });
          // refresh select2 widgets if present
          try { if (provEl && window.jQuery && jQuery(provEl).data('select2')) jQuery(provEl).trigger('change.select2'); } catch(e){}
          try { if (cityEl && window.jQuery && jQuery(cityEl).data('select2')) jQuery(cityEl).trigger('change.select2'); } catch(e){}
          try { if (yearEl && window.jQuery && jQuery(yearEl).data('select2')) jQuery(yearEl).trigger('change.select2'); } catch(e){}
      }

      if (map && Object.keys(map.provinces || {}).length) {
          fillFromMap(map);
          return;
      }

      // attempt to find a matching key in parent.regionMap (normalized match)
      if (!map && keys.length) {
          for (const k of keys) {
              if (normalizeRegionText(k) === norm) { map = window.parent.regionMap[k]; break; }
          }
      }
      if (map && Object.keys(map.provinces || {}).length) { fillFromMap(map); return; }

      // fallback: fetch aggregated data from server endpoint
      if (region) {
                    (window.fetchRegionHierarchy ? window.fetchRegionHierarchy(region) : Promise.reject(new Error('fetchRegionHierarchy missing')))
                        .then(payload => {
                // server returns { provinces: [...], grouped: {prov: {city:[rows]}}, availableYears }
                const m = { provinces: {}, years: [] };
                try {
                    if (Array.isArray(payload.provinces)) {
                        payload.provinces.forEach(p => { m.provinces[p] = Object.keys(payload.grouped && payload.grouped[p] ? payload.grouped[p] : {}); });
                    }
                    if (Array.isArray(payload.availableYears)) m.years = payload.availableYears.slice();
                } catch(e) { console.error('populateRegionFilters: payload parse', e); }
                fillFromMap(m);
                // also cache last payload for other code paths
                try { window._lastRsmPayload = payload; } catch(e){}
            }).catch(err => {
                console.error('populateRegionFilters fetch failed', err);
            });
      }
  }

  // --- client-side filter apply/reset handlers ---------------------------------
  // helper used throughout the filtering logic – introduced here again for
  // the later script block.  the implementation is identical to the one
  // declared earlier but updated to use `rsmEl` so it works inside iframe
  // embeddings.
  function _getSelectedValues(id) {
      const el = rsmEl ? rsmEl(id) : document.getElementById(id);
      if (!el) return [];
      try {
          // prefer Select2 / jQuery value when available
          try { if (window.jQuery && jQuery(el).data('select2')) {
                      const v = jQuery(el).val();
                      const result = Array.isArray(v) ? v.filter(Boolean) : (v ? [v] : []);
                      return result;
                  } } catch(e) {}
          if (el.selectedOptions && el.selectedOptions.length) {
              const res = Array.from(el.selectedOptions).map(o => o.value);
              console.log('[RSM] _getSelectedValues (native) for', id, res);
              return res;
          }
          // fallback
          const res = Array.from(el.querySelectorAll('option:checked')).map(o => o.value);
          console.log('[RSM] _getSelectedValues (fallback) for', id, res);
          return res;
      } catch(e) { console.error('[RSM] _getSelectedValues error', e); return []; }
  }

  // the city/year helpers were originally defined earlier in the
// first <script> block so that filtering logic can run as soon as the
// page loads.  keep these comments here in case this block is moved
// independently, but do not redeclare the functions to avoid
// confusion; they already exist on the global scope.
//
// function getRowCity(r){
//     return ((r.city||r.municipality)||'').toString().trim();
// }
// function getRowYear(r){
//     return (r.year_of_moa || r.moa_year || r.moa_year_of || r.moa_year_of_moa || r.moa_year_of || '').toString().trim();
// }

  function renderStTitlesFromRows(rows, regionParam) {
      try {
          const stListEl = rsmEl('rsm-st-list');
          const headerEl = rsmEl('rsm-st-listing-header');
          if (headerEl) headerEl.textContent = `ST Titles for ${regionParam || 'this region'}`;
          if (!stListEl) return;
          if (!rows || !rows.length) { stListEl.innerHTML = '<div class="rsm-empty">No ST titles for selected filters</div>'; return; }
          const titleMap = {};
          rows.forEach(r => {
              const t = (r && r.title) ? String(r.title).trim() : '';
              if (!t) return; titleMap[t] = (titleMap[t] || 0) + 1;
          });
          const entries = Object.entries(titleMap).sort((a,b) => b[1] - a[1]);
          if (!entries.length) { stListEl.innerHTML = '<div class="rsm-empty">No ST titles for selected filters</div>'; return; }
          const totalSts = rows.length; const uniqueTitles = entries.length;
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
          // wire handlers
          stListEl.onclick = function(ev){ const row = ev.target.closest('.rsm-st-summary-row'); if (!row) return; ev.stopPropagation(); const title = row.getAttribute('data-title')||''; showReplicateConfirmPopover(row, { title: title, row: { title: title } }); };
      } catch(e) { console.error('renderStTitlesFromRows', e); }
  }

  // if early stub handlers ran before the real functions were ready we
  // mark a flag; once the above real implementation exists, replay any
  // pending filter request so that user actions aren’t lost.
  try {
      if (window._rsmFilterRequested) {
          console.log('[RSM] replaying pending filters after initialization');
          updateProvinceFilters();
          applyRsmFilters();
          window._rsmFilterRequested = false;
      }
  } catch(e) { console.error('[RSM] pending filter replay failed', e); }


  function resetRsmFilters(){
      try {
          ['rsm-filter-prov','rsm-filter-city','rsm-filter-year'].forEach(id => {
              const el = document.getElementById(id);
              if (!el) return;
              Array.from(el.options || []).forEach(o => o.selected = false);
              // if select2 is present, trigger update
              try { if (window.jQuery && jQuery(el).data('select2')) jQuery(el).val([]).trigger('change'); } catch(e) {}
          });
      } catch(e){}
      // re-run rendering with full payload
      try { const payload = window._lastRsmPayload || null; if (payload && Array.isArray(payload.allRows)) { renderStTitlesFromRows(payload.allRows, (payload && payload.region) || window.__lastActiveRegion || 'this region'); if (typeof initOrUpdateModalStatsChart === 'function') { const allRows = payload.allRows; const totalMoa = allRows.reduce((acc,r) => acc + (truthy(r.with_moa) ? 1 : 0), 0); const totalRes = allRows.reduce((acc,r) => acc + (truthy(r.with_res) ? 1 : 0), 0); const totalExpr = allRows.reduce((acc,r) => acc + (truthy(r.with_expr) ? 1 : 0), 0); initOrUpdateModalStatsChart([0, totalMoa, totalRes, totalExpr], payload.perYearTotals || null); } }
      } catch(e){ console.error('resetRsmFilters', e); }
  }

  // expose handlers and bind buttons; include delegated fallback
  try { window.applyRsmFilters = applyRsmFilters; window.resetRsmFilters = resetRsmFilters; } catch(e) { console.warn('[RSM] failed to expose filter functions', e); }
  try {
      const btnA = document.getElementById('rsm-filter-apply');
      const btnR = document.getElementById('rsm-filter-reset');
      if (btnA) { btnA.addEventListener('click', function(ev){ ev.preventDefault(); applyRsmFilters(); }); console.log('[RSM] bound apply button'); }
      else console.warn('[RSM] apply button not found');
      if (btnR) { btnR.addEventListener('click', function(ev){ ev.preventDefault(); resetRsmFilters(); }); console.log('[RSM] bound reset button'); }
      else console.warn('[RSM] reset button not found');
  } catch(e) { console.error('[RSM] binding filter buttons failed', e); }

  // Delegated click handler as a fallback in case buttons are added later or replaced
  document.addEventListener('click', function(ev){
      try {
          const a = ev.target && ev.target.closest ? ev.target.closest('#rsm-filter-apply') : null;
          if (a) { ev.preventDefault(); try { applyRsmFilters(); } catch(e) { console.error('applyRsmFilters failed', e); } return; }
          const r = ev.target && ev.target.closest ? ev.target.closest('#rsm-filter-reset') : null;
          if (r) { ev.preventDefault(); try { resetRsmFilters(); } catch(e) { console.error('resetRsmFilters failed', e); } return; }
      } catch(e) { /* ignore delegation errors */ }
  });


  // when province picker changes, refresh city list
  const provSel = document.getElementById('rsm-filter-prov');
  if (provSel) {
      function deriveCurrentRegion(){
          // replicate regionParam derivation from applyRsmFilters
          let region = window.__lastActiveRegion || null;
          if (!region) {
              const provSel = document.getElementById('rsm-filter-prov');
              if (provSel && provSel._lastRegionEvent && provSel._lastRegionEvent.regionName) {
                  region = provSel._lastRegionEvent.regionName;
              }
          }
          if (!region) {
              const activeImg = document.querySelector('.swiper-slide.swiper-slide-active .slider-img');
              if (activeImg) {
                  region = activeImg.getAttribute('data-region-name') || activeImg.getAttribute('data-region-number');
              }
          }
          if (!region) {
              const modalImg = document.getElementById('rsm-modal-image');
              if (modalImg) region = modalImg.getAttribute('data-region-name');
          }
          return region;
      }

      provSel.addEventListener('change', function(ev){
          // delay until value has been updated by select2
          setTimeout(()=>{ updateProvinceFilters(); applyRsmFilters(); },0);
      });
      // also bind for select2 events: both the namespaced change and the
      // more granular select/unselect events which fire when choices are
      // added/removed via the UI.
      if (window.jQuery && jQuery(provSel).data('select2')) {
          const $prov = jQuery(provSel);
          $prov.on('change.select2 select2:select select2:unselect', function(ev){
              setTimeout(()=>{ updateProvinceFilters(); applyRsmFilters(); },0);
          });
      }
      // fallback listener on document in case select2 replaces the element
      document.addEventListener('change', function(ev){
          if (ev.target && ev.target.id === 'rsm-filter-prov') {
              setTimeout(()=>{ updateProvinceFilters(); applyRsmFilters(); },0);
          }
      });

  }

  // store last region event on province element for use in change handler
  document.addEventListener('sliderActiveRegionChanged', function(e){
      const provSel2 = document.getElementById('rsm-filter-prov');
      if (provSel2 && e && e.detail) provSel2._lastRegionEvent = e.detail;
  });

  // open modal on gallery card click (delegated) — ignore clicks produced by drag/scroll
  const scroller = document.querySelector('.container-cards');
  if (scroller) {
    let isPointerDown = false;
    let isDragging = false;
    let pointerStartX = 0;
    let pointerStartY = 0;
    let pointerStartScroll = 0;
    const DRAG_THRESHOLD = 10; // px - movement larger than this is considered a drag

    const onPointerDown = (ev) => {
      isPointerDown = true;
      isDragging = false;
      pointerStartX = ev.clientX ?? (ev.touches && ev.touches[0] && ev.touches[0].clientX) ?? 0;
      pointerStartY = ev.clientY ?? (ev.touches && ev.touches[0] && ev.touches[0].clientY) ?? 0;
      pointerStartScroll = scroller.scrollLeft || 0;
    };

    const onPointerMove = (ev) => {
      if (!isPointerDown) return;
      const x = ev.clientX ?? (ev.touches && ev.touches[0] && ev.touches[0].clientX) ?? 0;
      const y = ev.clientY ?? (ev.touches && ev.touches[0] && ev.touches[0].clientY) ?? 0;
      if (Math.abs(x - pointerStartX) > DRAG_THRESHOLD || Math.abs(y - pointerStartY) > DRAG_THRESHOLD) {
        isDragging = true;
      }
    };

    const onPointerUp = () => {
      isPointerDown = false;
      // keep `isDragging` truthy for the next click event; clear on the next tick
      setTimeout(() => { isDragging = false; }, 0);
    };

    // pointer events + fallbacks for older environments
    scroller.addEventListener('pointerdown', onPointerDown, { passive: true });
    scroller.addEventListener('pointermove', onPointerMove, { passive: true });
    scroller.addEventListener('pointerup', onPointerUp);
    scroller.addEventListener('pointercancel', onPointerUp);

    scroller.addEventListener('touchstart', onPointerDown, { passive: true });
    scroller.addEventListener('touchmove', onPointerMove, { passive: true });
    scroller.addEventListener('touchend', onPointerUp);

    scroller.addEventListener('mousedown', onPointerDown);
    scroller.addEventListener('mousemove', onPointerMove);
    scroller.addEventListener('mouseup', onPointerUp);

    scroller.addEventListener('click', function(ev){

      const card = ev.target && ev.target.closest ? ev.target.closest('.card-link') : null;
      if (!card) return;
      // debug: log what prevented the click (if anything)
      const scrollerDragged = (scroller.dataset && scroller.dataset.pointerDragging === '1');
      const scrolledSinceDown = Math.abs((scroller.scrollLeft||0) - (pointerStartScroll||0)) > 4;

      // also print available years for the currently active region (if known)
      try {
          const region = window.__lastActiveRegion || null;
          if (region && window.parent && window.parent.regionMap) {
              const norm = normalizeRegionText(region);
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
              const yrs = (map && map.years ? map.years.slice().sort() : []);
              console.log('[STsReport] active region on card click', region, 'years', yrs);
          }
      } catch(e){ console.warn('error logging region years', e); }

      // ignore clicks that are the result of dragging/scrolling
      if (isDragging || scrollerDragged || scrolledSinceDown) {
        ev.stopPropagation();
        ev.preventDefault();
        return;
      }
      ev.preventDefault();
      openModalForCard(card);
    });

    scroller.addEventListener('keydown', function(e){
      const trigger = e.target && e.target.closest ? e.target.closest('.card-link') : null;
      if (!trigger) return;
      if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') { e.preventDefault(); openModalForCard(trigger); }
    });
  }

  // document-level fallback: handle clicks on cloned/duplicated cards that may exist outside the main scroller
  document.addEventListener('click', function(ev){
    const card = ev.target && ev.target.closest ? ev.target.closest('.card-link') : null;
    if (!card) return;
    // if this card is inside the main scroller, let the scroller's click handler handle it (avoid double-open)
    if (card.closest && card.closest('.container-cards')) return;
    // avoid reopening when modal already visible
    if (modal && modal.getAttribute && modal.getAttribute('aria-hidden') === 'false') return;
    const scrollerMain = document.querySelector('.container-cards');
    const scrollerDragged = scrollerMain && scrollerMain.dataset && scrollerMain.dataset.pointerDragging === '1';
    if (scrollerDragged) { return; }
    ev.preventDefault();
    openModalForCard(card);
  });

  // expose a helper so you can manually open the modal from the browser console for debugging
  try { window.openSTsModal = function(el){ if (!el) el = document.querySelector('.card-link'); return el ? openModalForCard(el) : null; }; } catch(e){}

  modalClose.addEventListener('click', closeModal);
  modal.querySelector('.gcm-backdrop').addEventListener('click', closeModal);
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape'){
      // close popover first if visible, else the centered modal
      const pop = document.getElementById('galleryPopover');
      if (pop && pop.getAttribute('aria-hidden') === 'false') return closePopover();
      if (modal && modal.getAttribute && modal.getAttribute('aria-hidden') === 'false') return closeModal();
    }
  });

  // delegated handler for modal body:
  // - open child/sub-child anchors in a new tab
  // - toggle mother rows
  modalBody.addEventListener('click', function(e){
    const a = e.target && e.target.closest ? e.target.closest('a') : null;
    if (a) {
      const href = a.getAttribute('href') || '#';
      if (href && href !== '#') {
        try { window.open(href, '_blank', 'noopener,noreferrer'); } catch(err) { a.target = '_blank'; }
      }
      e.preventDefault();
      return;
    }

    const btn = e.target && e.target.closest ? e.target.closest('.gcm-mother-toggle') : null;
    if (!btn) return;
    const expanded = btn.getAttribute('aria-expanded') === 'true';
    btn.setAttribute('aria-expanded', expanded ? 'false' : 'true');
    const mother = btn.closest('.gcm-mother');
    const list = mother && mother.querySelector('.gcm-child-list');
    if (list) list.style.display = expanded ? 'none' : 'block';
  });

  // delegated toggle for mother rows inside popover (same behavior)
  const popBodyEl = document.getElementById('galleryPopoverBody');
  if (popBodyEl) popBodyEl.addEventListener('click', function(e){
    // If an anchor inside the popover body was clicked, open it in a new tab (fallback)
    const a = e.target && e.target.closest ? e.target.closest('a') : null;
    if (a) {
      const href = a.getAttribute('href') || '#';
      // ignore placeholder hashes
      if (href && href !== '#') {
        // prefer native target if present; fallback to window.open to ensure new tab
        try { window.open(href, '_blank', 'noopener,noreferrer'); } catch(err) { a.target = '_blank'; }
      }
      e.preventDefault();
      return;
    }

    // delegated toggle for mother rows inside popover (same behavior as modal)
    const btn = e.target && e.target.closest ? e.target.closest('.gcm-mother-toggle') : null;
    if (!btn) return;
    const expanded = btn.getAttribute('aria-expanded') === 'true';
    btn.setAttribute('aria-expanded', expanded ? 'false' : 'true');
    const mother = btn.closest('.gcm-mother');
    const list = mother && mother.querySelector('.gcm-child-list');
    if (list) list.style.display = expanded ? 'none' : 'block';
  });
});
</script>


@else
<!-- fallback: original static gallery -->
<section class="card-gallery" style="--card-bg:#fff; position:relative; z-index:60; pointer-events:auto;">
	<div class="container-cards" style="pointer-events:auto;">
		<a class="card card-link" href="#" data-href="/category/older-person" data-title="Older Person" aria-label="Older Person">
			<div class="imgContainer">
				<div class="logo-badge"><img src="{{ asset('images/dattachments/Older Person logo.png') }}" alt="Older Person logo"></div>
			</div>
			<div class="content">
				<h2>Older Person</h2>
				<p>Support and services for older persons.</p>
			</div>
		</a>

		<a class="card card-link" href="#" data-href="/category/internally-displaced-person" data-title="Internally Displaced Person" aria-label="Internally Displaced Person">
			<div class="imgContainer">
				<div class="logo-badge"><img src="{{ asset('images/dattachments/Internally Displaced Person logo.png') }}" alt="Internally Displaced Person logo"></div>
			</div>
			<div class="content">
				<h2>Internally Displaced Person</h2>
				<p>Assistance for displaced communities.</p>
			</div>
		</a>

		<a class="card card-link" href="#" data-href="/category/indigenous-peoples" data-title="Indigenous Peoples" aria-label="Indigenous Peoples">
			<div class="imgContainer">
				<div class="logo-badge"><img src="{{ asset('images/dattachments/Indenuos peoples logo.png') }}" alt="Indigenous Peoples logo"></div>
			</div>
			<div class="content">
				<h2>Indigenous Peoples</h2>
				<p>Programs for indigenous communities.</p>
			</div>
		</a>

		<a class="card card-link" href="#" data-href="/category/women" data-title="Women" aria-label="Women">
			<div class="imgContainer">
				<div class="logo-badge"><img src="{{ asset('images/dattachments/Women logo.png') }}" alt="Women logo"></div>
			</div>
			<div class="content">
				<h2>Women</h2>
				<p>Programs and services for women empowerment.</p>
			</div>
		</a>

		<a class="card card-link" href="#" data-href="/category/person-with-disability" data-title="Person with Disability" aria-label="Person with Disability">
			<div class="imgContainer">
				<div class="logo-badge"><img src="{{ asset('images/dattachments/Person with disability logo.png') }}" alt="Person with disability logo"></div>
			</div>
			<div class="content">
				<h2>Person with Disability</h2>
				<p>Accessibility and inclusive support.</p>
			</div>
		</a>

		<a class="card card-link" href="#" data-href="/category/children-and-youth" data-title="Children & Youth" aria-label="Children and Youth">
			<div class="imgContainer">
				<div class="logo-badge"><img src="{{ asset('images/dattachments/Children and Youth logo.png') }}" alt="Children and Youth logo"></div>
			</div>
			<div class="content">
				<h2>Children & Youth</h2>
				<p>are vital assets of the nation and the foundation of future development. Ensuring their survival, protection, development, and meaningful participation through access to quality education, health, protection services, life skills, and opportunities for engagement enables them to reach their full potential and become responsible, productive, and empowered members of society</p>
			</div>
		</a>

		<a class="card card-link" href="#" data-href="/category/family" data-title="Family" aria-label="Family">
			<div class="imgContainer">
				<div class="logo-badge"><img src="{{ asset('images/dattachments/Family logo.png') }}" alt="Family logo"></div>
			</div>
			<div class="content">
				<h2>Family</h2>
				<p>Family-focused support and community programs.</p>
			</div>
		</a>
	</div>
</section>
@endif

<!-- Slider (Swiper) -->
<div class="slider-wrapper">
    <div class="swiper mySwiper">

        <div class="swiper-wrapper">
            @php
                $sliderImages = [
                    '1.png','2.png','3.png','4_a.png','4_b.png','5.png',
                    '6.png','7.png','8.png','9.png','10.png',
                    '11.png','12.png','13.png',
                    'barmm.png','car.png','ncr.png','nir.png'
                ];
                // mapping used both here and by JS filter functions later
                $sliderRegionMap = [
                    '1' => 'Region I',    '2' => 'Region II',   '3' => 'Region III',
                    '4_a' => 'Region IV-A','4_b' => 'Region IV-B','5' => 'Region V',
                    '6' => 'Region VI',   '7' => 'Region VII',  '8' => 'Region VIII',
                    '9' => 'Region IX',   '10' => 'Region X',   '11' => 'Region XI',
                    '12' => 'Region XII','13' => 'CARAGA',      'barmm' => 'BARMM',
                    'car' => 'CAR',      'ncr' => 'NCR',       'nir' => 'NIR',
                ];
            @endphp

            @foreach ($sliderImages as $img)
                @php
                    $base = pathinfo($img, PATHINFO_FILENAME);
                    // always compute a human-friendly region name so we can
                    // rely on it when the numeric position gets skewed by
                    // non‑numeric entries (e.g. 4_a/4_b) which previously
                    // caused the "Region V" slide to report number 6.
                    $regionNameAttr = $sliderRegionMap[$base] ?? ucwords(str_replace(['_','-'], ' ', $base));
                    // only supply a numeric region-number when the base is a
                    // plain integer; this keeps older code that expects a
                    // number working without depending on the loop index.
                    $regionNumberAttr = '';
                    if (preg_match('/^(\d+)$/', $base, $m)) {
                        $regionNumberAttr = $m[1];
                    }
                @endphp
                <div class="swiper-slide">
                    <img src="/images/ST Regional Nav Slide/{{ $img }}"
                         class="slider-img"
                         data-img="/images/ST Regional Nav Slide/{{ $img }}"
                         data-region-number="{{ $regionNumberAttr }}"
                         data-region-name="{{ $regionNameAttr }}">
                </div>
            @endforeach

        </div>

    </div>

<!-- slider modal markup has been moved to main layout for global display -->
            </div>
        </div>
    </div>
    <!-- external Total-STs card (commented out) -->
        <!--
        <div id="sliderProvinceTotalCard" class="slider-province-total-card" aria-hidden="true" role="status" aria-label="Region total STs">
            <div class="region-st-title">Total STs</div>
            <div id="sliderProvinceTotalCardCount" class="region-st-count">0</div>
        </div>
        -->
</div>

  <div id="regionStatsPanel" class="rsm-panel" role="document">
    <div class="rsm-header">
      <div class="rsm-header-left" style="display:flex;align-items:flex-start;gap:12px;">
        <div style="display:flex;align-items:center;gap:12px;flex-direction:column;">
          <img id="rsm-modal-image" src="" alt="Region image" class="rsm-modal-image" style="visibility:hidden;width:480px;height:480px;border-radius:12px;object-fit:contain; background:transparent; border:none; box-shadow:none;" />
          <!-- moved metrics under image -->
          <div id="modalStatsChartWrap" class="rsm-metrics rsm-card" style="position:relative; display:block; width:500px !important; min-width:500px !important; max-width:500px !important; height:312px !important; overflow:hidden; box-sizing:border-box; margin-top:12px;">
              <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                  <div style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase;">Region Metrics</div>
                  <div style="display:flex; gap:8px; align-items:center;">
                      <div id="modalStatsTotal" style="font-size:0.5rem; font-weight:800; color:#8492a6; background:#eef2ff; padding:4px 8px; border-radius:999px;">Total STs: 0</div>
                  </div>
              </div>
              <div id="modalStatsChartControls" style="position:absolute; top:8px; right:8px; z-index:30; display:flex; gap:6px;"></div>
              <canvas id="modalStatsChart" width="960" height="480" style="display:block; width:100%; height:312px;"></canvas>
              <div id="modalStatsChartZones" style="position:absolute; inset:0; pointer-events:none; z-index:12; visibility:hidden;"></div>
          </div>
        </div>
        <!-- provinces/totals plus ST titles grouped together -->
        <div class="rsm-prov-total-st" style="display:flex;gap:24px;">
          <div class="rsm-provinces-and-totals" style="display:flex;flex-direction:column;gap:12px;">
            <div class="rsm-card rsm-prov-card">
              <!-- filter fields for provinces and cities (UI only) -->
              <div class="rsm-filter-fields" style="margin-top:12px; display:flex; flex-direction:column; gap:8px;">
                <div class="rsm-filter-group" style="width:100%;">
                    <div class="rsm-filter-group" style="width:100%;">
                  <label for="rsm-filter-prov" style="font-size:0.85rem; display:block; margin-bottom:4px;">Province</label>
                  <select id="rsm-filter-prov" multiple class="rsm-select2" style="width:100%; padding:4px 6px; font-size:0.9rem;">
                    @if(!empty($provinces) && is_array($provinces))
                        @foreach($provinces as $province)
                            <option value="{{ $province }}" {{ collect(request('province'))->contains($province) ? 'selected' : '' }}>{{ $province }}</option>
                        @endforeach
                    @endif
                  </select>
                  <div class="rsm-filter-group" style="width:100%;">
                  <label for="rsm-filter-city" style="font-size:0.85rem; display:block; margin-bottom:4px;">City</label>
                  <select id="rsm-filter-city" multiple class="rsm-select2" style="width:100%; padding:4px 6px; font-size:0.9rem;">
                    @if(!empty($cities) && is_array($cities))
                        @foreach($cities as $city)
                            <option value="{{ $city }}" {{ collect(request('municipality'))->contains($city) ? 'selected' : '' }}>{{ $city }}</option>
                        @endforeach
                    @endif
                  </select>
                </div>
                </div>
                  <label for="rsm-filter-year" style="font-size:0.85rem; display:block; margin-bottom:4px;">Year</label>
                  <select id="rsm-filter-year" multiple class="rsm-select2" style="width:100%; padding:4px 6px; font-size:0.9rem;">
                    @if(!empty($years) && is_array($years))
                        @foreach($years as $year)
                            <option value="{{ $year }}" {{ collect(request('year_of_moa'))->contains($year) ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    @endif
                  </select>
                                </div>
                                <!-- ongoing/dissolved counts card replaces filter buttons -->
                                <div id="rsm-filter-status-card" style="display:flex;gap:16px;margin-top:8px;align-items:center;">
                                    <div class="rsm-stat" style="flex:1; height:145px; background:#f5fef5; border-radius:8px; display:flex; flex-direction:column; justify-content:center; align-items:center;">
                                        <div class="rsm-stat-label" style="font-size:1rem; color:#065f46;">Ongoing</div>
                                        <div id="rsm-count-ongoing" class="rsm-stat-value" style="font-size:1.1rem;font-weight:700; color:#10b981;">0</div>
                                    </div>
                                    <div class="rsm-stat" style="flex:1; height:145px; background:#fdf6f6; border-radius:8px; display:flex; flex-direction:column; justify-content:center; align-items:center;">
                                        <div class="rsm-stat-label" style="font-size:0.85rem; color:#7f1d1d;">Dissolved</div>
                                        <div id="rsm-count-dissolved" class="rsm-stat-value" style="font-size:1.1rem;font-weight:700; color:#ef4444;">0</div>
                                    </div>
                                </div>
                
                
              </div>
            </div>
            <div class="rsm-card">
                <div class="rsm-stats-grid">
                  <div class="rsm-stat">
                    <div class="rsm-stat-label">Total STs</div>
                    <div id="rsm-total-sts" class="rsm-stat-value">0</div>
                  </div>
                  <div class="rsm-stat">
                    <div class="rsm-stat-label">Total Expression of Interest</div>
                    <div id="rsm-total-expr" class="rsm-stat-value">0</div>
                  </div>
                  <div class="rsm-stat">
                    <div class="rsm-stat-label">SB Resolutions</div>
                    <div id="rsm-total-res" class="rsm-stat-value">0</div>
                  </div>
                  <div class="rsm-stat">
                    <div class="rsm-stat-label">Total MOA</div>
                    <div id="rsm-total-moa" class="rsm-stat-value">0</div>
                  </div>
                  <div class="rsm-stat">
                    <div class="rsm-stat-label">MOA Attachments</div>
                    <div id="rsm-total-moa-attachments" class="rsm-stat-value">0</div>
                  </div>
                  <!-- replicate/adopt placeholders (always 0) -->
                  <div class="rsm-stat">
                    <div class="rsm-stat-label">Total Replicated</div>
                    <div id="rsm-total-rep" class="rsm-stat-value">0</div>
                  </div>
                  <div class="rsm-stat">
                    <div class="rsm-stat-label">Total Adopted</div>
                    <div id="rsm-total-adopt" class="rsm-stat-value">0</div>
                  </div>
                </div>
            </div>
          </div>

          <!-- ST Titles card now right of provinces/totals -->
          <div class="rsm-listing-wrap" style="margin-top:0;">
            <div class="rsm-card">
              <h4 id="rsm-st-listing-header" class="rsm-listing-title">ST Titles — select a city to view</h4>
              <div id="rsm-st-list" class="rsm-st-list"><div class="rsm-empty">Select a city to view ST titles</div></div>
            </div>
          </div>
        </div>
      </div>

      <div id="rsm-container" class="rsm-container">
				<div class = 'container-form'>
					<div id="rsm-loading" class="rsm-loading" style="display:none;">Loading…</div> 
				</div>
        <div id="rsm-cards" class="rsm-cards" style="display:none;">
          <!-- metrics moved into header; this section intentionally left empty -->
        </div>
      </div>

    </div>
    <div class="rsm-body">
	
    </div>
  </div>


<style>
    .imgContainer {
        background: transparent !important;
        /* If there's a background-color, override it */
        background-color: transparent !important;
        
        
    }
/* Scoped card styles — do NOT override global page styles */
.card-gallery { display:flex; justify-content:center; align-items:center; padding:28px 12px; position:relative; z-index:60; pointer-events:auto; width: 120vw; margin-left: 0; overflow-x: auto; -ms-overflow-style: none; /* IE/Edge */ scrollbar-width: none; /* Firefox */ }
.card-gallery::-webkit-scrollbar { display: none; /* WebKit */ }

.card-gallery * { box-sizing: border-box; font-family: 'Poppins', sans-serif; }
.card-gallery .container-cards, .card-gallery .container-cards .card, .card-gallery .container-cards .imgContainer { pointer-events: auto; }
/* visual pause indicator while debugging hover */
.card-gallery .container-cards.autoscroll-paused { opacity:0.94; }
.card-gallery .container-cards.autoscroll-paused::after { content: 'Paused'; position:absolute; right:20px; top:10px; background:rgba(0,0,0,0.65); color:#fff; font-size:12px; padding:6px 8px; border-radius:999px; z-index:9999; pointer-events:none; }

/* marquee fallback when content is not wider than viewport */
.marquee-force { overflow:hidden; }
/* default CSS marquee (used only for the pure CSS fallback) */
.marquee-track { display:flex; gap:18px; align-items:center; animation: marquee 30s linear infinite; will-change: transform; }
/* when we use JS transform-mode, disable the CSS animation so JS controls the motion */
.marquee-track.js-transform { animation: none !important; }
/* also pause any remaining CSS animation when user hovers */
.container-cards.autoscroll-paused .marquee-track { animation-play-state: paused !important; }
@keyframes marquee { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
/* single-row scroller */
.container-cards { display:flex; gap:30px; flex-wrap:nowrap; justify-content:flex-start; align-items:flex-start; overflow-x:auto; -webkit-overflow-scrolling:touch; scroll-behavior:smooth; padding:12px 8px; }
.container-cards::-webkit-scrollbar{ height:10px; }
.container-cards::-webkit-scrollbar-thumb{ background: rgba(0,0,0,0.08); border-radius:6px; }
/* collapsed card (single-row) — left badge initially centered above */
.container-cards { display:flex; gap:20px; flex-wrap:nowrap; justify-content:flex-start; align-items:center; overflow-x:auto; overflow-y:visible; -ms-overflow-style: none; scrollbar-width: none; scroll-behavior:smooth; padding:12px 8px; }
.container-cards::-webkit-scrollbar{ display:none; }
/* allow drag-to-scroll affordance */
.container-cards { cursor: grab; -webkit-user-select: none; user-select: none; }
.container-cards.dragging { cursor: grabbing; }

/* when autoscroll is paused we disable drag affordance */
.container-cards.autoscroll-paused { cursor: default; }
.container-cards img { -webkit-user-drag: none; user-drag: none; }
.container-cards .card { background:var(--card-bg); flex:0 0 180px; height:170px; margin:6px 6px; padding:10px; border-radius:12px; box-shadow: 0 6px 24px rgba(2,6,23,0.08); transition: flex-basis 360ms cubic-bezier(.2,.9,.2,1), box-shadow 220ms ease; overflow:visible; position:relative; display:flex; align-items:center; gap:12px; text-decoration:none; color:inherit; cursor:pointer; }
/* expand width to the right only */
/* only allow hover expansion when the scroller is NOT actively scrolling or being drag-scrolled */
.container-cards:not(.dragging):not(.is-scrolling) .card:hover { flex:0 0 844px; transform: translateY(-6px); box-shadow: 0 18px 60px rgba(2,6,23,0.12); }

/* Programmatic "lifted" state should visually match :hover so the extension remains visible
   when JS sets/keeps the card lifted (eg. popover is open). */
.container-cards:not(.dragging):not(.is-scrolling) .card.card-lifted { flex:0 0 844px; transform: translateY(-6px); box-shadow: 0 18px 60px rgba(2,6,23,0.12); }

/* image starts centered then animates to left on expand */
.container-cards .card .imgContainer { position:absolute; top:8px; left:50%; transform:translate(-50%, 0); width:140px; height:140px; z-index:3; overflow:visible; background: transparent; display:flex; align-items:center; justify-content:center; transition: left 420ms cubic-bezier(.2,.9,.2,1), transform 420ms cubic-bezier(.2,.9,.2,1), top 420ms ease; }
.container-cards .card .imgContainer img { width:100%; height:100%; object-fit:contain; display:block; }
.logo-badge { width:96px; height:96px; border-radius:999px; background: transparent; display:flex; align-items:center; justify-content:center; box-shadow: 0 8px 28px rgba(2,6,23,0.08); overflow:hidden; }
.logo-badge img { width:72%; height:72%; object-fit:contain; display:block; }

/* collapsed-title: show card title when not expanded */
.container-cards .card[data-title]::after {
    content: attr(data-title);
    position: absolute;
    left: 50%;
    bottom: 18px;
    transform: translateX(-50%);
    font-size: 0.70rem;
    color: #374151; /* gray-700 */
    font-weight: 600;
    text-align: center;
    white-space: nowrap;
    pointer-events: none;
    opacity: 1;
    z-index: 5; /* ensure title is above the image badge */
    transition: opacity 220ms ease, transform 220ms ease;
}

/* hide collapsed label while card expands / types / focused (image-hover or keyboard focus)
   also hide when the card is hovered/ lifted to show the content panel
   NOTE: do NOT hide when the card merely has `.title-typed` (keeps collapsed label visible after typing completes) */
.container-cards .card.image-hover::after,
.container-cards .card.typing-active::after,
.container-cards .card:focus-visible::after,
.container-cards:not(.dragging):not(.is-scrolling) .card:hover::after,
.container-cards .card.card-lifted::after {
    opacity: 0;
    transform: translateX(-50%) translateY(-6px);
} 

/* content panel (hidden when collapsed) — revealed to the right and shifted to avoid overlap */
.container-cards .card .content { width:0; opacity:0; visibility:hidden; overflow:hidden; transition: width 360ms cubic-bezier(.2,.9,.2,1), opacity 220ms ease, margin-left 360ms cubic-bezier(.2,.9,.2,1); display:flex; flex-direction:column; justify-content:center; padding:0; margin-left:0; z-index:1; }
.container-cards .card .content h2 { font-size:0.95rem; margin:0 0 6px 0; text-align:left; }
.container-cards .card .content p { color:#505050; font-size:0.92rem; line-height:1.35; margin:0; text-align:left; max-width:520px; opacity:0; transition: opacity 200ms ease; }
/* paragraph visible only after the title has finished typing (class `title-typed`) */
.container-cards .card.title-typed .content p { opacity:1; }
/* when expanded, move the content to the right so the badge doesn't cover text */
.container-cards:not(.dragging):not(.is-scrolling) .card:hover .content,
.container-cards .card:focus-visible .content,
.container-cards .card.typing-active .content,
.container-cards .card.card-lifted .content {
    margin-left:180px; /* shift past the badge */
    width: calc(100% - 220px);
    opacity:1;
    visibility:visible;
    padding-left:20px;
}

/* animate image to left when expanded or during typing */
.container-cards:not(.dragging):not(.is-scrolling) .card:hover .imgContainer,
.container-cards .card:focus-visible .imgContainer,
.container-cards .card.typing-active .imgContainer,
.container-cards .card.card-lifted .imgContainer {
  left:18px;
  top:50%;
  transform:translate(0, -50%);
}

.card-link:focus-visible { outline: 3px solid rgba(16,174,181,0.18); outline-offset:4px; border-radius:12px; }

/* typing cursor */
.content p.typing::after, .content h2.typing::after { content: '\007C'; margin-left:6px; display:inline-block; opacity:1; animation: blink 1s steps(1) infinite; }
@keyframes blink { 50% { opacity: 0; } }
.card-link:focus-visible { outline: 3px solid rgba(16,174,181,0.25); outline-offset:4px; border-radius:10px; }
.container-cards .card .content h2 { font-size:0.95rem; margin-bottom:6px; color: #111827; }
.container-cards .card .content p { color:#505050; font-size:0.86rem; line-height:1.35; }
@media (max-width: 1200px) { .container-cards { gap:12px; } .logo-badge { width:92px; height:92px; } .container-cards .card { flex:0 0 240px; } }
@media (max-width: 820px) { .container-cards .card { flex:0 0 220px; } .container-cards .card .imgContainer { width:180px; height:180px; left:12px; } .logo-badge { width:76px; height:76px; } }
@media (max-width: 900px) {
  /* Stack cards vertically on narrow screens for better tap targets */
  .container-cards { flex-direction: column; align-items: center; gap:24px; padding:12px; }
  .container-cards .card { flex: 0 0 92%; max-width:920px; width: 92%; margin:12px 0; height: auto; min-height: auto; padding:18px;}
  .container-cards .card .imgContainer { position: relative; left: 50%; transform: translateX(-50%); top: 0; width:180px; height:180px; margin-bottom:14px; }
  .logo-badge { width:96px; height:96px; }
  .container-cards .card .content { width:100%; margin-left:0; padding-left:0; opacity:1; visibility:visible; }
  .container-cards .card .content h2 { font-size:1rem; }
  .container-cards .card .content p { font-size:0.95rem; line-height:1.5; }
  .container-cards .card::after { display:none; } /* hide collapsed label on small screens */
}
@media (max-width: 420px) { .container-cards { gap:14px; } .container-cards .card { width:94%; max-width:360px; margin:10px 0; padding:14px; } .container-cards .card .imgContainer { width:140px; height:140px; left:calc(50% - 70px); } .logo-badge { width:72px; height:72px; } }

/* Improved dropdown (right-edge, accessible, animated) */
.card-dropdown { position:absolute; right:12px; top:50%; transform:translateY(-50%) scale(0.98); background: #fff; border-radius:10px; padding:10px; box-shadow: 0 14px 40px rgba(2,6,23,0.12); border:1px solid rgba(2,6,23,0.06); width: 300px; max-height:360px; overflow:auto; font-size:0.92rem; color:#111827; opacity:0; pointer-events:none; transition: opacity .18s ease, transform .18s cubic-bezier(.2,.9,.2,1); z-index:30; }
/* hide inline per-card dropdowns by default (but show on hover) */
.container-cards .card .card-dropdown { display: none !important; pointer-events: none !important; }
/* show inline dropdown when hovering/focusing the card (override the above) */
.container-cards .card:hover .card-dropdown,
.container-cards .card:focus-within .card-dropdown {
  display: block !important; /* show on hover */
  opacity: 1 !important;
  pointer-events: auto !important;
  transform: translateY(-50%) scale(1) !important;
}
/* keep demo-visible rule removed */

/* Floating dropdown (used on hover/focus of a card) */
.card-dropdown.card-dropdown-floating { position: absolute; right: auto; top: auto; transform: none !important; opacity:1 !important; pointer-events:auto !important; display: none; width: 320px; max-height: 420px; z-index: 999999; box-shadow: 0 18px 60px rgba(2,6,23,0.12); border-radius:10px; }


.card-dropdown.card-dropdown-floating[aria-hidden="false"] { display:block; }
@media (max-width:920px) { .card-dropdown { display:none !important; } }
.card-dropdown-header { display:flex; justify-content:space-between; align-items:center; padding-bottom:6px; border-bottom:1px solid rgba(2,6,23,0.05); margin-bottom:8px; font-weight:700; font-size:0.95rem; }
.card-dropdown-all { font-size:0.8rem; color:#2563eb; text-decoration:none; padding:4px 6px; border-radius:6px; background:transparent; border:1px solid rgba(37,99,235,0.08); }
.card-dropdown-list { margin:0; padding:0; list-style:none; }
.card-dropdown-item { padding:6px 6px; border-radius:6px; }
.card-dropdown-link { color:#0f1724; text-decoration:none; display:block; padding:4px 6px; border-radius:4px; }
.card-dropdown-link:focus, .card-dropdown-link:hover { background:#f8fafc; outline:none; color:#0369a1; }
.card-dropdown-sublist { margin-top:6px; padding-left:12px; list-style:none; }
.card-dropdown-sublink { color:#475569; font-size:0.85rem; }
.card-dropdown-empty { color:#9ca3af; font-size:0.9rem; }
@media (max-width:920px) { .card-dropdown { display:none !important; } }

/* Disable desktop hover expansion for touch devices */
@media (hover: none) {
  .container-cards:not(.dragging):not(.is-scrolling) .card:hover { transform: none; box-shadow: 0 6px 24px rgba(2,6,23,0.08); flex-basis: 240px; }
}

/* ===== Swiper slider (copied/simplified from demo1) ===== */
.slider-wrapper {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    /* break out of any parent container so the carousel spans the
       full viewport width */
    width: 100vw;
    left: 50%;
    right: 50%;
    margin-left: -50vw;
    margin-right: -50vw;
    overflow: hidden;
    /* keep top margin; remove auto horizontal centering since we're
       full‑width now */
    margin-top: 2rem;
}


/* ensure gallery above slider when necessary */
.gallery, .container-cards { position: relative; z-index: 10; }
/* slider sizing and centering: flexible container that caps at 1700px */
.swiper {
    /* allow the carousel to shrink when few slides remain */
    width: auto !important;
    max-width: 1700px !important;
    height: 300px; /* reasonable fixed height */
    max-height: 300px;
    margin: 0 auto; /* center within wrapper */
    transform: none;
    transition: transform 320ms cubic-bezier(.2,.9,.2,1);
    overflow: hidden; /* hide any overflowing slides/clones when filtering */
}
.swiper-slide { width: 500px; /* fixed size to fit container */ height: 240px; border-radius: 20px; overflow: hidden; transition: 0.4s ease; display: flex; justify-content: center; align-items: center; cursor: pointer; }
.swiper-slide img { width: 100%; height: 100%; object-fit: contain; border-radius: 18px; }
/* even smaller zoom so layout stays tight */
.swiper-slide-active { transform: scale(1.08); }
.swiper-button-next, .swiper-button-prev { color: #0d47a1; }
/* removed left offset; full-width slider should fill available space */
@media (max-width:1200px) { .swiper { transform: none; /* was translateX(-100px) */ } }
/* ensure slides scale down slightly on small viewports */
@media (max-width:800px) { .swiper-slide { width: 180px; height: 200px; } }
@media (max-width:900px) { .swiper { transform: none; width: 100%; } }

/* hide source image while modal clone is animating so it looks "taken" */
/* non-centered slides should not be clickable */
.swiper-slide:not(.swiper-slide-active) .slider-img {
    pointer-events: none;
}
.slider-img.modal-hidden { opacity: 0; transition: opacity 180ms ease; pointer-events: none; }

/* slider styles moved to main */

/* preview placed below the slider and left-aligned so it reserves its own space */
.slider-bottom-preview { position: relative; align-self: flex-start; margin: 18px 0 36px 18px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:10px; z-index:20; pointer-events: none; background: rgba(255,255,255,0.96); padding:12px; border-radius:12px; box-shadow: 0 20px 60px rgba(2,6,23,0.12); border: 1px solid rgba(2,6,23,0.04); width: 380px; max-width: calc(100% - 36px); }

/* Region Stats Modal (slider center click) */

.rsm-header { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; width:100%; flex-wrap:wrap; background:#fff; border:1px solid rgba(0,0,0,0.1); box-shadow:0 2px 4px rgba(0,0,0,0.08); padding:12px 16px; border-radius:8px; }
.rsm-header-left { display:flex; align-items:flex-start; gap:24px; }
.rsm-header .rsm-modal-image { width: clamp(220px, 34vw, 640px); height: auto; max-height: calc(96vh - 220px); object-fit:contain; flex-shrink:0; }
.rsm-prov-total-st { display:flex; align-items:flex-start; gap:24px; }
.rsm-close { background:transparent;border:none;font-size:20px; cursor:pointer; }
.rsm-body { display:flex; flex-direction:column; gap:12px; }
.rsm-cards {
    display: grid;
    /* simple single-column layout now metrics only */
    grid-template-columns: auto;
    grid-template-rows: auto;
    gap: 12px; /* spacing around the metrics card */
}
/* ensure ST titles column doesn't collapse and push into totals */
/* this selector targets stale elements (grid-column:3) and has no effect on our fourth-column panel */
/* keep it in case other templates use it, but it won't shrink our region modal listing */
.rsm-listing-wrap[style*="grid-column: 3"] { min-width: 100px; max-width: 100px; width: 100px; margin-left: 40px;}
#modalStatsChartWrap { margin-top: 0; } /* align with image */
#modalStatsChartWrap {
    /* margin-top handled earlier to create space */
    padding: 30px; /* add card padding instead of graph padding */
    /* enforce fixed width so layout doesn’t jump around */
    width: 500px !important;
    min-width: 500px !important;
    max-width: 500px;
    height: 312px !important; /* total outer height including padding */
    /* limit flex-basis to match the desired height rather than the previous 500px which forced a tall box */
    flex: 0 0 312px !important;
    box-sizing: border-box; /* include padding in width */
}
/* ensure the internal chart does not exceed the wrapper width */
#modalStatsChart,
#modalStatsChartWrap > canvas {
    width: 100% !important;
    max-width: 500px !important;
    min-width: 500px !important;
    height: 270px !important; /* match wrapper content height */
    box-sizing: border-box;
    padding-right: 30px; /* account for wrapper padding */
}
.slider-province-card {
    padding: 30px; /* provide consistent padding inside provinces card */
}
.rsm-metrics { /* keep fallback */
    flex:1 0 100%;
}
.rsm-container { display:flex; gap:28px; align-items:flex-start; width:900px; max-width:100%; }
.rsm-right { flex: 0 0 140px; /* expand totals panel width */ }
/* ensure totals card itself matches column width */
.rsm-right > .rsm-card { /* flexible totals card */ width:100%; max-width:none; height:auto !important; max-height:none !important; box-sizing:border-box; overflow:visible; }
/* same constraints for modal totals card which isn't under .rsm-right */
.rsm-provinces-and-totals > .rsm-card { /* flexible province card container */ width:100%; max-width:none; height:auto !important; max-height:none !important; box-sizing:border-box; overflow:visible; }
/* totals now lives inside the grid so ordering and negative offsets aren’t needed */
.rsm-right { order: 1; }
.rsm-right > .rsm-card { margin-left: 20px; }
.rsm-listing-wrap { order: 2; }
/* ensure ST Titles panel has reasonable width but let grid dictate spacing */
.rsm-listing-wrap,
.rsm-listing-wrap .rsm-card {
    /* match the fourth column size defined in .rsm-cards grid-template-columns */
    width: 500px !important;
    min-width: 500px !important;
    max-width: 500px !important;
    margin-left: 0 !important;
    margin-top: 0 !important;
}
/* force listing card height to 810px to prevent overlap/overflow */
.rsm-listing-wrap .rsm-card {
    height: 818px !important;
}

.rsm-container .rsm-cards {
    flex: 0 0 clamp(460px, 36vw, 840px);
    max-width: clamp(360px, 36vw, 840px);
}
/* when showing the modal with region metrics, allow the cards wrapper to expand/scroll */
#sliderModalContent .rsm-container .rsm-cards {
    min-width: 500px !important;
    max-width: none !important;
    overflow-x: auto !important;
}
/* override the previous min-width clamp for listing-wrap which would otherwise force 260px */
.rsm-container .rsm-listing-wrap { flex: 1 1 auto; min-width: 100px !important; }
@media (max-width:900px) { .rsm-container { flex-direction:column; } .rsm-container .rsm-cards, .rsm-container .rsm-listing-wrap { width:100%; max-width:none; } }
.rsm-left { flex: 0 0 460px; max-width: 460px; }
.rsm-right { flex:0 0 100px; }
.rsm-card { background:#fff; border-radius:10px; padding:12px; border:1px solid rgba(2,6,23,0.04); box-shadow: 0 8px 20px rgba(2,6,23,0.04); }
/* make sure provinces panel and the totals card both use only 12px
   padding (the global rule already covers it, but this reinforces the intent
   and resets any inner list spacing) */
.rsm-prov-card,
.rsm-right > .rsm-card {
    padding: 12px;
}
.rsm-prov-card .rsm-provinces-list { padding-top:12px; }

/* ensure province card in RSM has fixed height */
.rsm-prov-card { /* allow variable height, but keep a reasonable minimum */ min-height:405px; height:auto; }
.rsm-prov-card .rsm-provinces-list { height:100%; }
/* allow full height for provinces list, remove restrictive max-height */
.rsm-prov-card .rsm-provinces-list { max-height: none; height: 100%; width: 420px; min-width: 360px; overflow:auto; display:flex; flex-direction:column; gap:6px; padding-top:6px; -webkit-overflow-scrolling: touch; }
/* filter fields styling to stretch across available width */
.rsm-prov-card .rsm-filter-fields { width:100%; }
.rsm-prov-card .rsm-filter-fields .rsm-filter-group { width:100%; }
.rsm-prov-card .rsm-filter-fields select { width:100%; box-sizing:border-box; padding:4px 6px; border:1px solid #ccc; border-radius:4px; font-size:0.95rem; }
.rsm-prov-card .rsm-filter-fields label { font-weight:600; color:#333; }
.rsm-prov-item { padding:8px 10px; border-radius:8px; background:linear-gradient(180deg,#fff,#fafafa); box-shadow: inset 0 -1px 0 rgba(2,6,23,0.02); font-size:0.95rem; }
/* ensure province name and badge are side-by-side in RSM card */
.rsm-prov-item.province-item { display:flex; justify-content:space-between; align-items:center; width:330px !important; max-width:330px !important; overflow:hidden !important; }
/* blunt override for city and ST entries too */
.rsm-provinces-list .province-sublist .city-item,
.slider-province-card .province-sublist .city-item,
.slider-province-card .city-item,
.rsm-provinces-list .province-sublist .st-item,
.slider-province-card .st-item {
    width: 320px !important;
    max-width: 320px !important;
    overflow: hidden !important;
}
.rsm-prov-item .province-badge { flex-shrink:0; }
.rsm-stats-grid {
    display: grid;
    /* flow in columns, four rows per column; extras wrap into another column */
    grid-auto-flow: column;
    grid-template-rows: repeat(4, auto);
    column-gap: 10px;
    row-gap: 10px;
    max-width: none; /* allow horizontal expansion for additional columns */
}

.rsm-stat { background:linear-gradient(180deg,#fff,#fbfdff); padding:10px; border-radius:8px; text-align:center; border:1px solid rgba(2,6,23,0.04); }
/* make sure any other card-like container also has a defined border */
.rsm-prov-card, .rsm-right > .rsm-card, .rsm-card { border:1px solid rgba(2,6,23,0.04); }
.rsm-stat-label { font-size:0.82rem; color:#64748b; }
.rsm-stat-value { font-weight:700; font-size:1.25rem; margin-top:6px; color:#0f1724; }
.rsm-listing-wrap { margin-top:6px; }
.rsm-listing-title { margin:0 0 8px 0; font-size:1rem; }
.rsm-modal-image { width:72px; height:72px; border-radius:12px; object-fit:contain; display:block; transition: none; }
.rsm-st-list, .rsm-sts-region-list { max-height: 745px; min-height: 745px; /* limit height to 900px as requested */
    overflow:auto; border-radius:8px; border:1px solid rgba(2,6,23,0.04); padding:8px; background:#fff; -webkit-overflow-scrolling: touch; }
.rsm-empty { color:#94a3b8; padding:8px; }
@media (max-width:900px) { .rsm-header { flex-direction:column; align-items:center; gap:12px; } .rsm-header .rsm-modal-image { width: min(80vw, 360px); height: auto; max-height: 40vh; } .rsm-cards { display:flex; flex-direction:column; } .rsm-right { width:100%; } .rsm-left { width:100%; } .rsm-panel { width: calc(100% - 24px); } .rsm-prov-card .rsm-provinces-list, .rsm-st-list { max-height: 40vh; } .rsm-container { flex-direction: column; gap: 12px; } }

/* preview must not intercept slider pointer events */
.slider-bottom-preview img, .slider-bottom-preview .slider-bottom-label { pointer-events: none; }

.slider-bottom-preview img { width:360px; height:360px; object-fit:contain; border-radius:12px; box-shadow: 0 12px 34px rgba(2,6,23,0.12); background: linear-gradient(180deg,#fff,#f8fafc); border: 1px solid rgba(2,6,23,0.04); transition: transform 220ms ease; }
.slider-bottom-preview img:hover { transform: scale(1.02); }
.slider-bottom-label { font-weight:800; color:#0f1724; font-size:1rem; text-align:center; margin-top:8px; background: rgba(255,255,255,0.95); padding:6px 10px; border-radius:6px; box-shadow: 0 8px 30px rgba(2,6,23,0.06); }

@media (max-width:1200px) { .slider-bottom-preview img { width:300px; height:300px; } .slider-bottom-preview { width:320px; margin-left:12px; } }
@media (max-width:900px) { .slider-bottom-preview { align-self:center; margin:12px auto 24px; width:160px; } .slider-bottom-preview img { width:160px; height:160px; } }

/* bottom preview province-list specific styles */
.slider-bottom-province-card { width:100%; margin-top:14px; background:transparent; padding:6px; box-shadow:none; border-radius:8px; height: calc(40px * 8 + 16px + 40px) !important; min-height: calc(40px * 8 + 16px + 40px) !important; max-height: calc(40px * 8 + 16px + 40px) !important; }
.slider-bottom-province-card .province-list { height: calc(40px * 8 + 16px) !important; min-height: calc(40px * 8 + 16px) !important; max-height: calc(40px * 8 + 16px) !important; overflow:auto; display:flex; flex-direction:column; gap:8px; padding:6px 4px; }
/* rows inside bottom province card should also be fixed height */
.slider-bottom-province-card .province-list .province-item {
    height: 40px !important;
    line-height: 40px !important;
    padding: 0 6px !important;
    margin-bottom: 2px !important;
}
.slider-bottom-province-card .province-item { padding:8px 10px; border-radius:8px; background: rgba(247,249,250,0.95); display:flex; justify-content:space-between; gap:8px; align-items:center; border:1px solid rgba(2,6,23,0.04); color:#0f1724; font-weight:600; font-size:0.95rem; }
.slider-bottom-province-card .province-item .prov-name { font-weight:700; }
.slider-bottom-province-card .province-empty { color:#6b7280; padding:8px 6px; }

/* Popover tree — unified connectors, spacing, and arrows */
.gcm-list { --gcm-line-left: 12px; --gcm-indent: 22px; --gcm-gap-y: 8px; font-size:0.95rem; color:#0f1724; }
.gcm-mother { position:relative; padding-left: calc(var(--gcm-line-left) + 6px); margin-bottom: var(--gcm-gap-y); display:flex; flex-direction:column; gap:6px; }
.gcm-mother .gcm-mother-link { display:block; color:#0369a1; font-weight:700; text-decoration:none; padding:4px 0; cursor:pointer; }
.gcm-mother::before { /* short horizontal connector from vertical guide to mother */ content:''; position:absolute; left:calc(var(--gcm-line-left)); top:12px; width:12px; height:2px; background:#e6edf3; border-radius:2px; }

.gcm-child-list, .gcm-subchild-list { position:relative; margin:6px 0 0 0; padding-left: calc(var(--gcm-indent)); }
.gcm-child-list::before, .gcm-subchild-list::before { content:''; position:absolute; left: calc(var(--gcm-line-left)); top:0; bottom:0; width:1px; background:#e6edf3; border-radius:1px; }

.gcm-child-list li, .gcm-subchild-list li { position:relative; margin-bottom: var(--gcm-gap-y); padding-left: 12px; display:flex; align-items:center; gap:8px; }
.gcm-child-list li::before, .gcm-subchild-list li::before { content:''; position:absolute; left: calc(var(--gcm-line-left)); top: 50%; transform: translateY(-50%); width: 14px; height:1px; background:#e6edf3; border-radius:1px; }

/* unified arrow + text styling */
.gcm-child-list li > a, .gcm-subchild-list li > a { color:#0369a1; text-decoration:none; font-size:0.95rem; }
.gcm-child-list li > span, .gcm-subchild-list li > span { color:#9ca3af; font-size:0.95rem; }
.gcm-child-list li > a::before, .gcm-subchild-list li > a::before,
.gcm-child-list li > span::before, .gcm-subchild-list li > span::before { content:'➜'; display:inline-block; margin-right:8px; color: currentColor; font-size:0.95rem; transform: translateY(-1px); opacity:0.95; }

@media (max-width:520px) {
  .gcm-child-list, .gcm-subchild-list { padding-left: 16px; }
  .gcm-child-list li::before, .gcm-subchild-list li::before { width: 10px; left: calc(var(--gcm-line-left)); }
  .gcm-mother::before { left: calc(var(--gcm-line-left)); }
}

</style>

<script>
// Continuous slow auto-scroll (seamless loop) — waits for images and ensures content is wide enough
(function(){
    const scroller = document.querySelector('.container-cards');
    if (!scroller) return;
    scroller.tabIndex = 0; // focusable for keyboard pause/navigation

    function waitForImages(container){
        const imgs = Array.from(container.querySelectorAll('img'));
        if (!imgs.length) return Promise.resolve();
        return Promise.all(imgs.map(img => img.complete ? Promise.resolve() : new Promise(r => img.addEventListener('load', r))));
    }

    function initLoop(){
        try {

            // if scroller isn't visible yet, retry a few times
            if (!scroller.clientWidth) {
                let tries = 0;
                const retry = () => {
                    tries++;
                    if (scroller.clientWidth || tries > 8) return initLoop();
                    setTimeout(retry, 200);
                };
                return retry();
            }

            // ensure duplicated content so we can loop seamlessly
            if (!scroller.dataset.looped) {
                scroller.dataset.looped = '1';
                const html = scroller.innerHTML;
                scroller.innerHTML = html + html;
            }

            waitForImages(scroller).then(()=>{
                // ensure scroller content is wider than viewport; duplicate more aggressively before falling back
                let originalWidth = scroller.scrollWidth / 2;
                const baseHtml = scroller.innerHTML.slice(0, Math.floor(scroller.innerHTML.length/2));
                let copies = 0;
                const MAX_COPIES = 20; // try many copies so JS loop can run reliably

                while (originalWidth <= scroller.clientWidth && copies < MAX_COPIES) {
                    scroller.innerHTML += baseHtml; // append another copy
                    originalWidth = scroller.scrollWidth / 2;
                    copies++;
                }


                // if not wider after duplication, add spacers as a last attempt
                let spacerAttempts = 0;
                while (originalWidth <= scroller.clientWidth && spacerAttempts < 6) {
                    const spacer = document.createElement('div');
                    spacer.style.width = Math.max(200, scroller.clientWidth) + 'px';
                    spacer.style.flex = '0 0 auto';
                    scroller.appendChild(spacer);
                    originalWidth = scroller.scrollWidth / 2;
                    spacerAttempts++;
                }

                if (originalWidth <= scroller.clientWidth) {

                    // create a JS-transform track (keeps autoscroll JS-driven even when element isn't scrollable)
                    const childrenHtml = scroller.innerHTML;
                    scroller.innerHTML = '';
                    const track = document.createElement('div');
                    track.className = 'marquee-track js-transform';
                    track.innerHTML = childrenHtml + childrenHtml; // duplicate
                    scroller.appendChild(track);
                    scroller.classList.add('js-transform-mode');

                    // transform-based loop (uses same SPEED_PX_PER_SEC)
                    track.dataset.offset = '0';
                    let _last = null;
                    function transformStep(ts){
                        if (!_last) _last = ts;
                        const dt = ts - _last;
                        _last = ts;

                        // if hover-snap active, move track toward target
                        if (hoverSnap.active) {
                            let cur = parseFloat(track.dataset.offset) || 0;
                            const delta = hoverSnap.target - cur;
                            const move = Math.sign(delta) * Math.min(Math.abs(delta), (currentSpeed * dt) / 1000);
                            cur = cur + move;
                            if (cur >= originalWidth) cur -= originalWidth;
                            track.dataset.offset = cur;
                            track.style.transform = `translateX(${-cur}px)`;

                            if (Math.abs(hoverSnap.target - cur) <= 2) {
                                // verify visual centering before completing (handles copy/wrap issues)
                                const snappedCard = hoverSnap.card;
                                const scrollerRect = scroller.getBoundingClientRect();
                                const cardRectNow = snappedCard.getBoundingClientRect();
                                const visualLeftNow = Math.round(cardRectNow.left - scrollerRect.left);
                                const expandedGuess = Math.max(760, Math.round(snappedCard.offsetWidth * 1.6));
                                const expectedWidth = Math.max(snappedCard.offsetWidth, expandedGuess);
                                const desiredLeftForExpanded = Math.round((scroller.clientWidth - expectedWidth) / 2);
                                if (Math.abs(visualLeftNow - desiredLeftForExpanded) > 10) {
                                    // not visually centered for expanded card yet — adjust target to compensate and continue
                                    const adjust = desiredLeftForExpanded - visualLeftNow;
                                    hoverSnap.target = hoverSnap.target + adjust;
                                    // keep active; let next frame move it
                                    // reduce speed for fine adjustment
                                    currentSpeed = Math.max(MIN_HOVER_SPEED, Math.round(currentSpeed / 2));
                                } else {
                                    // snap to the exact target (handle rounding/wrap)
                                    const finalPos = Math.round(hoverSnap.target);
                                    track.dataset.offset = finalPos;
                                    track.style.transform = `translateX(${-finalPos}px)`;
                                    hoverSnap.active = false;
                                    running = false;
                                    scroller.classList.add('autoscroll-paused');
                                    currentSpeed = baseSpeed;
                                    try { startTypingSequence(snappedCard); } catch(e){}
                                    try { _scheduleAutoResume(); } catch(e){}
                                }
                            }
                        } else if (typeof running !== 'undefined' && running) {
                            let cur = parseFloat(track.dataset.offset) || 0;
                            cur += (currentSpeed * dt) / 1000; // px/sec
                            if (cur >= originalWidth) cur -= originalWidth;
                            track.dataset.offset = cur;
                            track.style.transform = `translateX(${-cur}px)`;
                        }
                        requestAnimationFrame(transformStep);
                    }

                    // pause/resume on hover/focus (scroller listeners already toggle `running`)
                    requestAnimationFrame(transformStep);

                    /* debug badge removed */

                    // mark we are using transform fallback so the scrollLeft loop will no-op
                    useTransformFallback = true;
                    return;
                }

                // JS-driven scroll (preferred)
                const baseSpeed = 18; // normal px/sec
                let currentSpeed = baseSpeed; // can be raised temporarily when snapping to hovered card
                let running = true;
                let lastTs = null;
                let useTransformFallback = false; // set to true when we switch to transform-mode (so scrollLeft loop becomes a no-op)
                let wrapSuspended = false; // when true, we stop wrapping scrollLeft; used while popover open

                // hover-snap state: when user hovers a partially-visible card, speed up until that card is fully visible, then pause
                let hoverSnap = { active:false, card:null, target:0 };
                const HOVER_SNAP_DURATION_MS = 1000; // aim to finish snap within ~1 second
                const MIN_HOVER_SPEED = 60; // px/sec minimum for snapping (lower for smooth small adjustments)
                const MAX_HOVER_SPEED = 2000; // px/sec cap to avoid extreme jumps
                const AUTO_RESUME_MS = 2500; // resume autoscroll automatically after this many ms
                let _autoResumeTimer = null;

                function _clearAutoResume(){ if (_autoResumeTimer) { clearTimeout(_autoResumeTimer); _autoResumeTimer = null; } }
                function _scheduleAutoResume(){ _clearAutoResume(); _autoResumeTimer = setTimeout(()=>{ try { if (!hoverSnap.active && !isPopoverOpen() && !_isGalleryExpanded()) { running = true; scroller.classList.remove('autoscroll-paused'); } else { running = false; scroller.classList.add('autoscroll-paused'); } } catch(e){ if (!hoverSnap.active) { running = true; scroller.classList.remove('autoscroll-paused'); } } _autoResumeTimer = null; }, AUTO_RESUME_MS); }

                function startHoverSnap(card){
                    _clearAutoResume();
                    if (!card) return;
                    // if already snapping to this card, do nothing
                    if (hoverSnap.active && hoverSnap.card === card) return;
                    // compute target offset using the card element's content position so we center it reliably
                    const cardWidth = card.offsetWidth;
                    const visibleW = scroller.clientWidth || scroller.getBoundingClientRect().width;
                    const cur = useTransformFallback ? (parseFloat((document.querySelector('.marquee-track')||{}).dataset.offset) || 0) : scroller.scrollLeft;

                    // card.offsetLeft is the position *within the scroller content* for this specific DOM element (handles duplicates)
                    const cardContentLeft = card.offsetLeft;
                    let desiredScrollLeft = Math.max(0, Math.min(originalWidth - visibleW, Math.round(cardContentLeft - (visibleW - cardWidth)/2)));

                    // ensure expanded card will be fully visible after centering
                    try {
                        const EXPAND_MARGIN = 24;
                        const expandedGuess = Math.max(760, Math.round(cardWidth * 1.6));
                        const visualLeftAfterCenter = Math.round(cardContentLeft - desiredScrollLeft);
                        const expandedRight = visualLeftAfterCenter + expandedGuess;
                        const expandedLeft = visualLeftAfterCenter;
                        if (expandedRight > (visibleW - EXPAND_MARGIN)) {
                            desiredScrollLeft = Math.max(0, desiredScrollLeft + (expandedRight - (visibleW - EXPAND_MARGIN)));
                        }
                        if (expandedLeft < EXPAND_MARGIN) {
                            desiredScrollLeft = Math.max(0, desiredScrollLeft - (EXPAND_MARGIN - expandedLeft));
                        }
                        desiredScrollLeft = Math.max(0, Math.min(originalWidth - visibleW, Math.round(desiredScrollLeft)));
                    } catch(e) { /* ignore */ }

                    // if already visually centered enough (use bounding rect to avoid duplicate/wrap mismatches), pause and type
                    const scrollerRectEarly = scroller.getBoundingClientRect();
                    const cardRectEarly = card.getBoundingClientRect();
                    const cardCenterVis = Math.round((cardRectEarly.left - scrollerRectEarly.left) + (cardWidth / 2));
                    const CENTER_THRESHOLD = 8; // px tolerance for being 'centered'
                    if (Math.abs(cardCenterVis - (visibleW / 2)) <= CENTER_THRESHOLD) {
                        // ensure scrollLeft is normalized to the nearest duplicated copy so visual and logical positions match
                        try {
                            const visualOffset = Math.round(cardContentLeft - (visibleW - cardWidth) / 2);
                            const delta2 = visualOffset - cur;
                            const n2 = originalWidth;
                            const raw2 = (delta2 + n2/2) - Math.floor((delta2 + n2/2) / n2) * n2 - n2/2;
                            const normalized = Math.round(cur + raw2);
                            if (!useTransformFallback) scroller.scrollLeft = normalized; else (document.querySelector('.marquee-track')||{}).dataset.offset = normalized;
                        } catch(e) { /* ignore normalization errors */ }

                        running = false;
                        scroller.classList.add('autoscroll-paused');
                        try { startTypingSequence(card); } catch(e){}
                        _scheduleAutoResume();
                        return;
                    }

                    // normalize desiredScrollLeft to the nearest duplicated copy (handles wrap)
                    const delta = desiredScrollLeft - cur;
                    const n = originalWidth;
                    const raw = (delta + n/2) - Math.floor((delta + n/2) / n) * n - n/2;
                    const adjustedTarget = Math.round(cur + raw);

                    // use adjustedTarget from here on to animate/center
                    hoverSnap = { active:true, card:card, target:adjustedTarget };
                    _lastHoverCard = card; // remember current hovered card
                    const distance = Math.abs(adjustedTarget - cur);
                    const durationSec = Math.max(0.2, HOVER_SNAP_DURATION_MS / 1000); // avoid zero/too-small durations
                    const desiredSpeed = distance / durationSec;
                    currentSpeed = Math.max(MIN_HOVER_SPEED, Math.min(MAX_HOVER_SPEED, Math.round(desiredSpeed)));

                    // ensure loop runs only when no popover/extension is open
                    try {
                        if (!isPopoverOpen() && !_isGalleryExpanded()) { running = true; scroller.classList.remove('autoscroll-paused'); }
                        else { running = false; scroller.classList.add('autoscroll-paused'); }
                    } catch(e) { running = true; scroller.classList.remove('autoscroll-paused'); }
                }

                function cancelHoverSnap(card){
                    _clearAutoResume();
                    // cancel any pending lift
                    cancelLift(card);
                    if (hoverSnap && hoverSnap.active) {
                        // only cancel if it's the same card or if card is null
                        if (!card || hoverSnap.card === card) hoverSnap.active = false;
                    }
                    // fully reset hoverSnap so subsequent hovers always retrigger
                    hoverSnap = { active:false, card:null, target:0 };
                    currentSpeed = baseSpeed;

                    // only resume autoscroll when no popover is open and nothing is expanded
                    try {
                        if (!isPopoverOpen() && !_isGalleryExpanded()) {
                            running = true;
                            scroller.classList.remove('autoscroll-paused');
                        } else {
                            running = false;
                            scroller.classList.add('autoscroll-paused');
                        }
                    } catch(e){ running = true; scroller.classList.remove('autoscroll-paused'); }
                }

                // expose for other scripts that may call it externally (defensive)
                try { window.cancelHoverSnap = cancelHoverSnap; } catch(e){}

                // show badge when JS-run (and update it with live metrics)
                /* debug badge removed */

                // LIFT helpers — float-up visual before centering
                const LIFT_DURATION_MS = 420; // ms for float-up
                const _liftTimers = new WeakMap();

                function liftThenCenter(card){
                    _clearAutoResume();
                    if (!card) return;

                    // apply gallery + card lift and suppress immediate width expansion
                    scroller.classList.add('lift-in-progress','gallery-lifted');
                    card.classList.add('card-lifted');

                    const prev = _liftTimers.get(card);
                    if (prev) clearTimeout(prev);

                    const t = setTimeout(()=>{
                        _liftTimers.delete(card);
                        // trigger the regular hover-snap centering after lift completes
                        startHoverSnap(card);
                    }, LIFT_DURATION_MS);

                    _liftTimers.set(card, t);
                }

                function cancelLift(card){
                    // If the popover is open and anchored to this card (or matches by key), keep that card lifted (don't collapse the extension).
                    try {
                        const pop = document.getElementById('galleryPopover');
                        const popOpen = pop && pop.getAttribute && pop.getAttribute('aria-hidden') === 'false';
                        const popAnchor = pop && pop._anchor ? pop._anchor : null;
                        const anchorKey = pop && pop._anchorKey ? pop._anchorKey : null;
                        const cardKey = card && card.dataset ? (card.dataset.title || card.dataset.href) : null;
                        if (popOpen && card && (popAnchor === card || (anchorKey && cardKey && anchorKey === cardKey))) return; // keep this card lifted while popover open
                    } catch(e){}

                    const prev = card ? _liftTimers.get(card) : null;
                    if (prev) { clearTimeout(prev); if (card) _liftTimers.delete(card); }
                    if (card) card.classList.remove('card-lifted');
                    scroller.classList.remove('lift-in-progress');
                    // keep gallery-lifted while hoverSnap is active (keeps floated look while paused)
                    if (!hoverSnap.active) {
                        try {
                            const pop = document.getElementById('galleryPopover');
                            const popOpen = pop && pop.getAttribute && pop.getAttribute('aria-hidden') === 'false';
                            if (!popOpen) scroller.classList.remove('gallery-lifted');
                        } catch(e){ scroller.classList.remove('gallery-lifted'); }
                    }
                }
                // expose to global so inline/other handlers won't throw when invoked from elsewhere
                try { window.cancelLift = cancelLift; } catch(e){}

                // detect if scrollLeft actually moves — if not, fallback to CSS marquee
                let lastSeen = scroller.scrollLeft;
                let stableCounter = 0;
                const stabilityLimit = 6; // checks (approx 800ms)
                const badgeUpdater = setInterval(()=>{
                    const L = Math.round(scroller.scrollLeft);
                    const W = Math.round(scroller.scrollWidth/2);
                    /* metrics (no badge) */
                    if (L === lastSeen) stableCounter++; else stableCounter = 0;
                    lastSeen = L;
                    // if we've tried but scrollLeft isn't changing, switch to transform-driven track instead of CSS fallback
                    if (stableCounter >= stabilityLimit) {
                        clearInterval(badgeUpdater);
                        const childrenHtml = scroller.innerHTML;
                        scroller.innerHTML = '';
                        const track = document.createElement('div');
                        track.className = 'marquee-track js-transform';
                        track.innerHTML = childrenHtml + childrenHtml;
                        scroller.appendChild(track);
                        scroller.classList.add('js-transform-mode');

                        // transform-based loop
                        track.dataset.offset = '0';
                        let trLast = null;
                        function trackStep(ts){
                            // suspend transform-mode movement while popover is open OR track explicitly frozen
                            try { const track = document.querySelector('.marquee-track'); if (track && track.dataset && track.dataset.frozen === '1') { trLast = ts; requestAnimationFrame(trackStep); return; } } catch(e){}
                            try { if (isPopoverOpen()) { trLast = ts; requestAnimationFrame(trackStep); return; } } catch(e){}
                            if (!trLast) trLast = ts;
                            const dt = ts - trLast;
                            trLast = ts;

                            // if hover-snap active and we're using transform-mode, move the track toward the hover target
                            if (hoverSnap.active) {
                                let cur = parseFloat(track.dataset.offset) || 0;
                                const delta = hoverSnap.target - cur;
                                const move = Math.sign(delta) * Math.min(Math.abs(delta), (currentSpeed * dt) / 1000);
                                cur = cur + move;
                                if (cur >= originalWidth) cur -= originalWidth;
                                track.dataset.offset = cur;
                                track.style.transform = `translateX(${-cur}px)`;

                                if (Math.abs(hoverSnap.target - cur) <= 2) {
                                    const snappedCard = hoverSnap.card;
                                    hoverSnap.active = false;
                                    running = false;
                                    scroller.classList.add('autoscroll-paused');
                                    // allow card to expand now that centering finished
                                    scroller.classList.remove('lift-in-progress');
                                    try {
                                        // do NOT collapse the snapped card if the popover is anchored to it (duplicate-safe check)
                                        const pop = document.getElementById('galleryPopover');
                                        const anchorKey = pop && pop._anchorKey ? pop._anchorKey : null;
                                        const snappedKey = (snappedCard && snappedCard.dataset) ? (snappedCard.dataset.title || snappedCard.dataset.href) : null;
                                        if (!(anchorKey && snappedKey && anchorKey === snappedKey)) {
                                            try { snappedCard.classList.remove('card-lifted'); } catch(e){}
                                        }
                                    } catch(e){}
                                    currentSpeed = baseSpeed;
                                    try { startTypingSequence(snappedCard); } catch(e){}
                                    // schedule auto-resume so repeated hovers still work
                                    try { _scheduleAutoResume(); } catch(e){}
                                }
                            } else if (running) {
                                let cur = parseFloat(track.dataset.offset) || 0;
                                cur += (currentSpeed * dt) / 1000;
                                if (cur >= originalWidth) cur -= originalWidth;
                                track.dataset.offset = cur;
                                track.style.transform = `translateX(${-cur}px)`;
                            }

                            requestAnimationFrame(trackStep);
                        }
                        requestAnimationFrame(trackStep);
                        useTransformFallback = true;
                        /* transform fallback active (no badge) */
                    }
                }, 140);

                function step(ts){
                    // if we're using transform fallback, keep the RAF alive but don't modify scrollLeft (transformStep handles movement)
                    if (useTransformFallback) { requestAnimationFrame(step); return; }

                    // if popover is open, suspend any movement (including hover-snap and auto-scroll)
                    try { if (isPopoverOpen()) { lastTs = ts; requestAnimationFrame(step); return; } } catch(e){}

                    if (!lastTs) lastTs = ts;
                    const dt = ts - lastTs;
                    lastTs = ts;

                    // if hover-snap active, move toward the hovered card target (scrollLeft mode)
                    if (hoverSnap.active) {
                        const cur = scroller.scrollLeft;
                        const delta = hoverSnap.target - cur;
                        const move = Math.sign(delta) * Math.min(Math.abs(delta), (currentSpeed * dt) / 1000);
                        scroller.scrollLeft = cur + move;
                        if (Math.abs(hoverSnap.target - scroller.scrollLeft) <= 2) {
                            const snappedCard = hoverSnap.card;
                            // verify visual centering before completing
                            const scrollerRect = scroller.getBoundingClientRect();
                            const cardRectNow = snappedCard.getBoundingClientRect();
                            const visualLeftNow = Math.round(cardRectNow.left - scrollerRect.left);
                            const expandedGuess = Math.max(760, Math.round(snappedCard.offsetWidth * 1.6));
                            const expectedWidth = Math.max(snappedCard.offsetWidth, expandedGuess);
                            const desiredLeftForExpanded = Math.round((scroller.clientWidth - expectedWidth) / 2);
                            if (Math.abs(visualLeftNow - desiredLeftForExpanded) > 10) {
                                const adjust = desiredLeftForExpanded - visualLeftNow;
                                hoverSnap.target = hoverSnap.target + adjust;
                                currentSpeed = Math.max(MIN_HOVER_SPEED, Math.round(currentSpeed / 2));
                            } else {
                                // snap to the exact target (handle rounding/wrap)
                                const finalPos = Math.round(hoverSnap.target);
                                scroller.scrollLeft = finalPos;
                                hoverSnap.active = false;
                                running = false;
                                scroller.classList.add('autoscroll-paused');
                                currentSpeed = baseSpeed;
                                try { startTypingSequence(snappedCard); } catch(e){}
                                // schedule auto-resume so repeated hovers still work
                                try { _scheduleAutoResume(); } catch(e){}
                            }
                        }
                    } else if (running) {
                        scroller.scrollLeft += (currentSpeed * dt) / 1000;
                        if (scroller.scrollLeft >= originalWidth) scroller.scrollLeft -= originalWidth; // loop
                    }

                    requestAnimationFrame(step);
                }

                scroller.addEventListener('mouseenter', ()=> { running = false; scroller.classList.add('autoscroll-paused'); });
                scroller.addEventListener('mouseleave', ()=> { cancelLift(); try { if (!hoverSnap.active && !isPopoverOpen() && !_isGalleryExpanded()) { running = true; scroller.classList.remove('autoscroll-paused'); } else { running = false; scroller.classList.add('autoscroll-paused'); } } catch(e){ if (!hoverSnap.active) { running = true; scroller.classList.remove('autoscroll-paused'); } } lastTs = null; });
                scroller.addEventListener('focusin', ()=> { if (!hoverSnap.active) running = false; });
                scroller.addEventListener('focusout', ()=> { cancelHoverSnap(); try { if (!isPopoverOpen() && !_isGalleryExpanded()) { running = true; scroller.classList.remove('autoscroll-paused'); } else { running = false; scroller.classList.add('autoscroll-paused'); } } catch(e){ running = true; scroller.classList.remove('autoscroll-paused'); } lastTs = null; });

                // Auto-open popover when a gallery card finishes its expand transition ✅
                (function bindAutoOpenOnExpand(){
                    if (window.__galleryAutoOpenBound) return; window.__galleryAutoOpenBound = true;
                    const _autoOpenTimestamps = new WeakMap();

                    // listen for the "flex-basis" transition which indicates the card finished expanding
                    scroller.addEventListener('transitionend', (ev) => {
                        try {
                            const el = ev.target;
                            if (!el || !el.classList) return;
                            if (!el.classList.contains('card')) return; // only care about card elements
                            if (ev.propertyName !== 'flex-basis') return; // expansion finished

                            // only open if this card is expanded and no popover is already visible
                            if (!el.classList.contains('card-lifted')) return;
                            if (isPopoverOpen()) return;

                            // debounce per-element to avoid duplicate openings from clones/transitions
                            const now = Date.now();
                            const last = _autoOpenTimestamps.get(el) || 0;
                            if (now - last < 600) return;
                            _autoOpenTimestamps.set(el, now);

                            // find the clickable anchor (.card-link) inside the card (or use the card itself)
                            const anchor = el.matches('.card-link') ? el : (el.querySelector ? el.querySelector('.card-link') : null) || el;

                            // final guard: only open for visible/interactive anchors
                            if (!anchor || !(anchor.offsetParent !== null)) return;

                            // open the popover on the next frame (ensures layout is stable)
                            requestAnimationFrame(() => { try { if (!isPopoverOpen()) openModalForCard(anchor); } catch(e){} });
                        } catch(e) { /* defensive - ignore */ }
                    }, true);
                })();

                // delegated pointer/touch handlers — works even if cards are replaced (transform fallback)
                // pause/resume via pointerover/out + capture-phase pointerenter/pointerleave (robust across DOM replacements)
                scroller.addEventListener('pointerover', (ev) => {
                    // pause autoscroll on hover (do NOT auto-center)
                    const card = ev.target && ev.target.closest ? ev.target.closest('.card') : null;
                    if (card && scroller.contains(card)) {
                        running = false;
                        scroller.classList.add('autoscroll-paused');
                    }
                });
                scroller.addEventListener('pointerout', (ev) => {
                    const leftCard = ev.target && ev.target.closest ? ev.target.closest('.card') : null;
                    const to = ev.relatedTarget;
                    if (!leftCard) return;
                    // if moving into another card, keep paused state; otherwise cancel
                    if (to && scroller.contains(to) && to.closest('.card')) return;
                    cancelLift(leftCard);
                    cancelHoverSnap(leftCard);
                    try {
                        if (!hoverSnap.active && !isPopoverOpen() && !_isGalleryExpanded()) { running = true; lastTs = null; scroller.classList.remove('autoscroll-paused'); }
                        else { running = false; scroller.classList.add('autoscroll-paused'); }
                    } catch(e){ if (!hoverSnap.active) { running = true; lastTs = null; scroller.classList.remove('autoscroll-paused'); } }
                });
                // touch fallback — pause on touch
                scroller.addEventListener('touchstart', ()=> { running = false; scroller.classList.add('autoscroll-paused'); });
                scroller.addEventListener('touchend', ()=> { try { if (!hoverSnap.active && !isPopoverOpen() && !_isGalleryExpanded()) { running = true; lastTs = null; scroller.classList.remove('autoscroll-paused'); } else { running = false; scroller.classList.add('autoscroll-paused'); } } catch(e){ if (!hoverSnap.active) { running = true; lastTs = null; scroller.classList.remove('autoscroll-paused'); } } });

                // Drag-to-scroll (pointer) — enables slide/drag scrolling with mouse & touchpad
                let _isPointerDragging = false;
                let _pointerDragStartX = 0;
                let _pointerDragStartScroll = 0;
                let _scrollTimer = null; // debounce timer used to mark active scrolling (prevents hover-expand)
                const _DRAG_THRESHOLD = 6; // px

                // helper: returns true when any card is currently expanded (hover or keyboard focus)
                function _isGalleryExpanded() {
                    try {
                        // consider hovered/focused cards *and* programmatically lifted cards
                        if (scroller.querySelector('.card:hover, .card:focus-within')) return true;
                        if (scroller.querySelector('.card-lifted')) return true;
                        if (scroller.classList.contains('gallery-lifted')) return true;
                        return false;
                    } catch(e){ return false; }
                }

                scroller.addEventListener('pointerdown', (ev) => {
                    // only primary button
                    if (ev.button && ev.button !== 0) return;

                    // Do not start drag-to-scroll when the gallery is in an "expanded" state
                    // (user is interacting with the expanded card) or when autoscroll is paused.
                    if (scroller.classList.contains('autoscroll-paused') || _isGalleryExpanded()) {
                        // allow normal click/interaction inside the expanded card — do not set pointer dragging
                        return;
                    }

                    _isPointerDragging = true;
                    _pointerDragStartX = ev.clientX;
                    // start from the appropriate offset depending on transform fallback
                    _pointerDragStartScroll = useTransformFallback ? (parseFloat((document.querySelector('.marquee-track')||{}).dataset.offset) || 0) : scroller.scrollLeft;
                    scroller.classList.add('dragging');
                    scroller.dataset.pointerDragging = '0';
                    try { scroller.setPointerCapture(ev.pointerId); } catch(e){}

                    // pause autoscroll while dragging
                    running = false;
                    scroller.classList.add('autoscroll-paused');
                });

                scroller.addEventListener('pointermove', (ev) => {
                    if (!_isPointerDragging) return;
                    const dx = ev.clientX - _pointerDragStartX;
                    if (Math.abs(dx) > _DRAG_THRESHOLD) scroller.dataset.pointerDragging = '1';

                    // compute desired logical offset then normalize into [0, originalWidth)
                    const desired = Math.round(_pointerDragStartScroll - dx);
                    const wrap = (v) => {
                        if (!originalWidth || !isFinite(originalWidth)) return Math.max(0, v);
                        return ((v % originalWidth) + originalWidth) % originalWidth;
                    };

                    if (useTransformFallback) {
                        const track = document.querySelector('.marquee-track');
                        if (track) {
                            const pos = wrap(desired);
                            track.dataset.offset = pos;
                            track.style.transform = `translateX(${-pos}px)`;
                        }
                    } else {
                        scroller.scrollLeft = wrap(desired);
                    }
                });

                function _endPointerDrag(ev){
                    if (!_isPointerDragging) return;
                    _isPointerDragging = false;
                    scroller.classList.remove('dragging');
                    try { scroller.releasePointerCapture && scroller.releasePointerCapture(ev && ev.pointerId); } catch(e){}

                    // normalize current scroll position for seamless loop (in case user stopped in duplicated region)
                    try {
                        if (!useTransformFallback) {
                            while (scroller.scrollLeft >= originalWidth) scroller.scrollLeft -= originalWidth;
                            while (scroller.scrollLeft < 0) scroller.scrollLeft += originalWidth;
                        } else {
                            const track = document.querySelector('.marquee-track');
                            if (track) {
                                let off = parseFloat(track.dataset.offset) || 0;
                                off = ((off % originalWidth) + originalWidth) % originalWidth;
                                track.dataset.offset = off;
                                track.style.transform = `translateX(${-off}px)`;
                            }
                        }
                    } catch(e) { /* ignore */ }

                    // resume autoscroll after short delay so user can see the final position
                    _scheduleAutoResume();
                    // clear temporary flag shortly after so click handlers can rely on it
                    setTimeout(()=> { try { scroller.dataset.pointerDragging = '0'; } catch(e){} }, 40);
                }

                scroller.addEventListener('pointerup', _endPointerDrag);
                scroller.addEventListener('pointercancel', _endPointerDrag);

                // normalize scroll when user scrolls by wheel or other means so loop is seamless
                scroller.addEventListener('scroll', () => {
                    // mark scrolling to temporarily disable hover expansion
                    scroller.classList.add('is-scrolling');
                    clearTimeout(_scrollTimer);
                    _scrollTimer = setTimeout(()=> { scroller.classList.remove('is-scrolling'); }, 180);
                });
        if (useTransformFallback || _isPointerDragging || wrapSuspended) return;
                scroller.addEventListener('wheel', (ev) => {
                    // only when the pointer is inside the scroller
                    if (!scroller.contains(ev.target)) return;
                    // allow native horizontal wheel gestures to pass through (if user is already scrolling horizontally)
                    if (Math.abs(ev.deltaX) > Math.abs(ev.deltaY)) return;

                    // prevent page from scrolling vertically while interacting with the gallery
                    ev.preventDefault();

                    // normalize deltaMode (0=pixel, 1=line, 2=page)
                    let dy = ev.deltaY;
                    if (ev.deltaMode === 1) dy *= 24;
                    else if (ev.deltaMode === 2) dy *= window.innerHeight || 800;

                    // small sensitivity factor — adjust if too slow/fast
                    const WHEEL_SPEED = 1.0;
                    const delta = dy * WHEEL_SPEED;

                    // pause automatic looping while user scrolls manually
                    running = false;
                    scroller.classList.add('autoscroll-paused');
                    _clearAutoResume();

                    // compute desired logical offset and wrap so loop stays seamless
                    const wrap = (v) => {
                        if (!originalWidth || !isFinite(originalWidth)) return Math.max(0, v);
                        return ((Math.round(v) % originalWidth) + originalWidth) % originalWidth;
                    };

                    if (useTransformFallback) {
                        const track = document.querySelector('.marquee-track');
                        if (!track) return;
                        const cur = parseFloat(track.dataset.offset) || 0;
                        const desired = wrap(cur + delta);
                        track.dataset.offset = desired;
                        track.style.transform = `translateX(${-desired}px)`;
                    } else {
                        const cur = scroller.scrollLeft || 0;
                        const desired = wrap(cur + delta);
                        scroller.scrollLeft = desired;
                    }

                    // schedule auto-resume after interaction ends
                    _scheduleAutoResume();
                }, { passive: false });

                // prevent accidental native image drag from fighting our pointer-drag behaviour
                scroller.addEventListener('dragstart', (ev) => ev.preventDefault());

                // capture-phase document handlers catch pointerenter/pointerleave even when propagation is stopped by children
                document.addEventListener('pointerenter', (ev) => {
                    // pointerenter no longer triggers lift/center — user action (click/focus) required
                }, true);
                document.addEventListener('pointerleave', (ev) => {
                    const card = ev.target && ev.target.closest ? ev.target.closest('.card') : null;
                    if (!card) return;
                    cancelLift(card);
                    cancelHoverSnap(card);
                }, true);

                // fallback: also watch mouseover/mouseout at document level (very robust)
                function docOverHandler(ev){
                    const card = ev.target && ev.target.closest ? ev.target.closest('.card') : null;
                    if (card && scroller.contains(card)) {
                        running = false;
                        scroller.classList.add('autoscroll-paused');
                    }
                }
                function docOutHandler(ev){
                    const card = ev.target && ev.target.closest ? ev.target.closest('.card') : null;
                    if (card && scroller.contains(card)) {
                        // if moving into another card, keep snap/paused state
                        const to = ev.relatedTarget;
                        if (to && scroller.contains(to) && to.closest('.card')) return;
                    }
                    cancelLift(card);
                    cancelHoverSnap(card);
                    try {
                        if (!hoverSnap.active && !isPopoverOpen() && !_isGalleryExpanded()) { running = true; scroller.classList.remove('autoscroll-paused'); }
                        else { running = false; scroller.classList.add('autoscroll-paused'); }
                    } catch(e){ if (!hoverSnap.active) { running = true; scroller.classList.remove('autoscroll-paused'); } }
                }
                document.addEventListener('mouseover', docOverHandler, true);
                document.addEventListener('mouseout', docOutHandler, true);

                // fallback: document-level mousemove using bounding-box check (works even when events are intercepted)
                let _lastHoverCard = null;
                let _lastMousePos = { x: 0, y: 0 };
                let _mouseRaf = null;

                // ensure expanded content remains visible without centering the card
                function smoothScrollLeftTo(target, duration = 260){
                    if (useTransformFallback) return; // skip for transform-mode
                    target = Math.max(0, Math.min(originalWidth - scroller.clientWidth, Math.round(target)));
                    const start = scroller.scrollLeft;
                    const delta = target - start;
                    if (Math.abs(delta) < 2) return;
                    const startTs = performance.now();
                    function step(ts){
                        const t = Math.min(1, (ts - startTs) / duration);
                        // easeInOutQuad
                        const eased = t < 0.5 ? 2*t*t : -1 + (4 - 2*t) * t;
                        scroller.scrollLeft = Math.round(start + delta * eased);
                        if (t < 1) requestAnimationFrame(step);
                    }
                    requestAnimationFrame(step);
                }

                function ensureExpandedVisible(card){
                    if (!card || useTransformFallback) return;
                    try {
                        const scRect = scroller.getBoundingClientRect();
                        const cRect = card.getBoundingClientRect();
                        const visibleW = scroller.clientWidth || scRect.width;
                        const EXPAND_MARGIN = 24;
                        const cardWidth = card.offsetWidth || (cRect.width || 240);
                        const expandedGuess = Math.max(760, Math.round(cardWidth * 1.6));
                        const visualLeftAfterCenter = Math.round(cRect.left - scRect.left);
                        const expandedRight = visualLeftAfterCenter + expandedGuess;
                        const expandedLeft = visualLeftAfterCenter;

                        let shift = 0;
                        if (expandedRight > (visibleW - EXPAND_MARGIN)) {
                            shift = expandedRight - (visibleW - EXPAND_MARGIN);
                        } else if (expandedLeft < EXPAND_MARGIN) {
                            shift = expandedLeft - EXPAND_MARGIN; // negative
                        }

                        if (shift !== 0) {
                            const newScroll = scroller.scrollLeft + shift;
                            smoothScrollLeftTo(newScroll, 260);
                        }
                    } catch(e) { /* ignore */ }
                }

                document.addEventListener('mousemove', (ev) => {
                    if (_mouseRaf) return; // throttle
                    _mouseRaf = requestAnimationFrame(() => {
                        _mouseRaf = null;
                        try {
                            // suppress hover-derived behavior while the scroller is actively moving (auto-scroll or user scroll/drag)
                            if (scroller.classList.contains('is-scrolling') || scroller.dataset.pointerDragging === '1') {
                                if (_lastHoverCard) { try { stopTypingSequence(_lastHoverCard); } catch(e){} _lastHoverCard = null; }
                                return;
                            }

                            const x = ev.clientX, y = ev.clientY;
                            _lastMousePos.x = x; _lastMousePos.y = y;
                            const cardEls = Array.from(scroller.querySelectorAll('.card'));
                            let cardUnder = null;
                            for (const c of cardEls) {
                                const r = c.getBoundingClientRect();
                                if (x >= r.left && x <= r.right && y >= r.top && y <= r.bottom) { cardUnder = c; break; }
                            }

                            if (cardUnder !== _lastHoverCard) {
                                const prev = _lastHoverCard;
                                _lastHoverCard = cardUnder;

                                // start/stop typing on hover change
                                if (prev) { try { stopTypingSequence(prev); } catch(e){} }
                                if (_lastHoverCard) { try { startTypingSequence(_lastHoverCard); } catch(e){} ensureExpandedVisible(_lastHoverCard); }

                                const overCardNow = !!cardUnder;
                                if (!hoverSnap.active) {
                                    running = !overCardNow ? true : false;
                                    if (!running) lastTs = null;
                                }
                            }
                        } catch (e) { /* ignore */ }
                    });
                });
                // keyboard left/right to jump a card; pauses while interacting
                scroller.addEventListener('keydown', (e)=>{
                    if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                        e.preventDefault();
                        running = false;
                        const cards = Array.from(scroller.querySelectorAll('.card'));
                        if (!cards.length) return;
                        const center = scroller.scrollLeft + scroller.clientWidth / 2;
                        let idx = cards.findIndex(c => c.offsetLeft + c.offsetWidth/2 > center);
                        if (idx < 0) idx = 0;
                        idx += (e.key === 'ArrowRight') ? 1 : -1;
                        idx = ((idx % (cards.length/2)) + (cards.length/2)) % (cards.length/2);
                        const targetCard = cards[idx];
                        // lift then center (keyboard) — preserves the float-then-center UX
                        liftThenCenter(targetCard);
                    }
                });

                requestAnimationFrame(step);
                // kick-start painting: small nudges so browsers render scrollLeft changes without requiring user interaction
                setTimeout(()=>{ try { scroller.scrollLeft = Math.min(1, Math.max(0, scroller.scrollLeft + 1)); } catch(e){} }, 60);
                setTimeout(()=>{ try { scroller.scrollBy(1,0); scroller.scrollBy(-1,0); } catch(e){} }, 120);
            }).catch(err => console.error('card-gallery: waitForImages failed', err));
        } catch (err) {
        }
    }

    if (document.readyState === 'complete') initLoop(); else window.addEventListener('load', initLoop);
})();

/* Typing effect for card title + description: types when card is hovered or focused */
(function(){
    const TYPING_SPEED = 12; // ms per char (faster)

    function typeChars(el, text) {
        return new Promise(resolve => {
            if (!el) return resolve();
            // debug: indicate typing start
            // clear any existing interval
            clearInterval(el._typingInterval);
            el.classList.add('typing');
            el.textContent = '';
            let i = 0;
            el._typingInterval = setInterval(()=>{
                if (i < text.length) {
                    el.textContent += text.charAt(i++);
                } else {
                    clearInterval(el._typingInterval);
                    el._typingInterval = null;
                    el.classList.remove('typing');
                    resolve();
                }
            }, TYPING_SPEED);
        });
    }

    function stopTypingEl(el){
        if (!el) return;
        clearInterval(el._typingInterval);
        el._typingInterval = null;
        el.classList.remove('typing');
        if (el.dataset.fulltext) el.textContent = el.dataset.fulltext;
    }

    async function startTypingSequence(card){
        if (!card) return;
        // prevent re-entrant / repeated typing when the card is already typing or title already completed
        if (card.classList.contains('typing-active') || card.classList.contains('title-typed')) return;
        const h = card.querySelector('.content h2');
        const p = card.querySelector('.content p');
        if (h && !h.dataset.fulltext) h.dataset.fulltext = h.textContent.trim();
        if (p && !p.dataset.fulltext) p.dataset.fulltext = p.textContent.trim();

        // ensure content area is visible even if :hover/focus selectors fail
        card.classList.add('typing-active');

        // mark title as not yet completed and ensure paragraph hidden
        card.classList.remove('title-typed');
        if (p) { p.textContent = ''; }

        // stop any currently running typing on these elements
        stopTypingEl(h); stopTypingEl(p);

        // debug & fallback: ensure we always reveal something if typing never starts

        // title fallback timer (if title typing doesn't render quickly, reveal full title)
        if (card._typingFallbackTimer) { clearTimeout(card._typingFallbackTimer); card._typingFallbackTimer = null; }
        card._typingFallbackTimer = setTimeout(()=>{
            try {
                if (h && (!h.classList.contains('typing') && (!h.textContent || h.textContent.trim()===''))) h.textContent = h.dataset.fulltext || '';
            } catch(e){}
        }, 160);

        // paragraph fallback will be set after the title typing completes (moved to paragraph typing block)

        // type title first, then reveal + type description
        if (h && h.dataset.fulltext) await typeChars(h, h.dataset.fulltext);
        // mark title done so CSS reveals paragraph and JS will type it
        card.classList.add('title-typed');
        if (card._typingFallbackTimer) { clearTimeout(card._typingFallbackTimer); card._typingFallbackTimer = null; }

        // start paragraph typing after title finishes (character-by-character)
        if (p && p.dataset.fulltext) {
            // paragraph fallback timer (in case typing stalls) — started here so it won't preempt title typing
            if (card._pTypingFallbackTimer) { clearTimeout(card._pTypingFallbackTimer); card._pTypingFallbackTimer = null; }
            card._pTypingFallbackTimer = setTimeout(()=>{
                try {
                    if (p && (!p.classList.contains('typing') && (!p.textContent || p.textContent.trim()===''))) p.textContent = p.dataset.fulltext || '';
                } catch(e){}
            }, 1500);

            // type paragraph with the same helper (shows typing cursor via .typing)
            await typeChars(p, p.dataset.fulltext);

            // typing finished — clear fallback and log
            if (card._pTypingFallbackTimer) { clearTimeout(card._pTypingFallbackTimer); card._pTypingFallbackTimer = null; }
        }

        // typing finished — leave typing-active so user still sees content until mouseout
    }

    function stopTypingSequence(card){
        if (!card) return;
        // clear fallback timers if set
        try { if (card._typingFallbackTimer) { clearTimeout(card._typingFallbackTimer); card._typingFallbackTimer = null; } } catch(e){}
        try { if (card._pTypingFallbackTimer) { clearTimeout(card._pTypingFallbackTimer); card._pTypingFallbackTimer = null; } } catch(e){}
        const h = card.querySelector('.content h2');
        const p = card.querySelector('.content p');
        // if title had completed, show full paragraph; otherwise keep paragraph hidden
        const titleDone = card.classList.contains('title-typed');
        stopTypingEl(h);
        if (titleDone) stopTypingEl(p); else {
            // cancel paragraph typing and keep it hidden
            clearInterval(p?._typingInterval);
            if (p) { p._typingInterval = null; p.textContent = ''; p.classList.remove('typing'); }
            card.classList.remove('title-typed');
        }
        // remove typing-active so hover rules resume control
        card.classList.remove('typing-active');
    }

    (function initCardTypingBindings(){
        // initialize bindings immediately if DOM already available, otherwise attach on DOMContentLoaded
        function setup(){
            // track last input modality so pointer-clicks don't trigger keyboard-focus behavior
            const __cardLastInteraction = { type: 'pointer', ts: Date.now() };
            document.addEventListener('pointerdown', ()=> { __cardLastInteraction.type = 'pointer'; __cardLastInteraction.ts = Date.now(); }, true);
            document.addEventListener('keydown', ()=> { __cardLastInteraction.type = 'keyboard'; __cardLastInteraction.ts = Date.now(); }, true);

            // Use delegated handlers on the stable scroller element so listeners survive DOM duplication
            const scrollerEl = document.querySelector('.container-cards');

            // lazy-initialize fulltext for cards that may be created/replaced later
            function ensureCardFulltext(card){
                if (!card) return;
                const h = card.querySelector('.content h2');
                const p = card.querySelector('.content p');
                if (h && !h.dataset.fulltext) h.dataset.fulltext = h.textContent.trim();
                if (p && !p.dataset.fulltext) p.dataset.fulltext = p.textContent.trim();
            }

            scrollerEl.addEventListener('pointerover', (ev) => {
                const card = ev.target && ev.target.closest ? ev.target.closest('.card') : null;
                if (!card || !scrollerEl.contains(card)) return;
                ensureCardFulltext(card);
                try { running = false; scrollerEl.classList.add('autoscroll-paused'); } catch(e){}

                // do not retrigger typing if it's already running or if the title has completed
                try {
                    if (card.classList.contains('typing-active') || card.classList.contains('title-typed')) return;
                    // if popover is open and anchored to this card, avoid restarting typing
                    const pop = document.getElementById('galleryPopover');
                    if (pop && pop.getAttribute && pop.getAttribute('aria-hidden') === 'false') {
                        const anchorKey = pop._anchorKey || null;
                        const cardKey = (card.dataset && (card.dataset.title || card.dataset.href)) || null;
                        if (anchorKey && cardKey && anchorKey === cardKey) return;
                    }
                } catch(e){}

                try { startTypingSequence(card); } catch(e){}
            });

            scrollerEl.addEventListener('pointerout', (ev) => {
                const card = ev.target && ev.target.closest ? ev.target.closest('.card') : null;
                if (!card || !scrollerEl.contains(card)) return;

                // if the popover is open and anchored to this card, do not stop typing / collapse the extension
                try {
                    const pop = document.getElementById('galleryPopover');
                    if (pop && pop.getAttribute && pop.getAttribute('aria-hidden') === 'false') {
                        const anchorKey = pop._anchorKey || null;
                        const cardKey = (card.dataset && (card.dataset.title || card.dataset.href)) || null;
                        if (anchorKey && cardKey && anchorKey === cardKey) return;
                    }
                } catch(e){}

                stopTypingSequence(card);
                cancelLift(card);
                cancelHoverSnap(card);
            });

            scrollerEl.addEventListener('click', (ev) => {
                // ignore clicks that immediately follow a drag
                if (scrollerEl.dataset && scrollerEl.dataset.pointerDragging === '1') { ev.preventDefault(); ev.stopPropagation(); return; }
                const card = ev.target && ev.target.closest ? ev.target.closest('.card') : null;
                if (!card || !scrollerEl.contains(card)) return;
                if (card.matches('.card-link')) ev.preventDefault();
                if (__cardLastInteraction.type === 'pointer') {
                    cancelHoverSnap(card);
                    cancelLift(card);
                    try { card.blur(); } catch(e){}
                }
            });

            scrollerEl.addEventListener('focusin', (ev) => {
                const card = ev.target && ev.target.closest ? ev.target.closest('.card') : null;
                if (!card || !scrollerEl.contains(card)) return;
                ensureCardFulltext(card);
                if (__cardLastInteraction.type === 'keyboard') liftThenCenter(card);
                try { startTypingSequence(card); } catch(e){}
            });

            scrollerEl.addEventListener('focusout', (ev) => {
                const card = ev.target && ev.target.closest ? ev.target.closest('.card') : null;
                if (!card) return;
                stopTypingSequence(card);
            });
        }

        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', setup); else setup();
    })();
})();
</script>