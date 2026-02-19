@extends('layouts.app')

@section('content')

<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>

<style>
body {
    overflow-x: hidden !important;
}

/* ================= SWIPER ================= */

.slider-wrapper {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    width: 100%;
}

.swiper {
    width: 100vw;
    height: 75vh;
    min-height: 350px;
}

.swiper-slide {
    width: 85vw;
    max-width: 420px;
    height: 65vh;
    min-height: 300px;
    border-radius: 20px;
    overflow: hidden;
    transition: 0.4s ease;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
}

.swiper-slide img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 18px;
}

.swiper-slide-active {
    transform: scale(1.25);
}

.swiper-button-next,
.swiper-button-prev {
    color: #0d47a1;
}

/* ================= MODAL ================= */

.slider-modal {
    position: fixed !important;
    inset: 0;
    width: 100vw !important;
    height: 100vh !important;
    z-index: 999999 !important; /* above sidebar */
    pointer-events: none;
    display: none;
    -webkit-tap-highlight-color: transparent;
    touch-action: pan-y pinch-zoom;
}

.slider-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.85);
    opacity: 0;
    transition: opacity 0.3s ease;
    touch-action: none;
}

.slider-modal-content {
    position: fixed !important;
    top: 0;
    left: 0;
    margin: 0 !important;
    padding: 0 !important;
    display: flex;
    align-items: flex-start;
    justify-content: flex-start;
    height: 100vh;
    width: auto;
    min-width: 280px;
    max-width: 60vw;
    background: transparent;
    box-shadow: none;
    touch-action: pan-y pinch-zoom;
    -webkit-overflow-scrolling: touch;
    transform-origin: center center;
    will-change: width, left, transform;
}

/* ensure image remains responsive and pinch-zoomable */
.slider-modal-content img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    max-width: 100%;
    max-height: 100%;
    transition: transform 0.28s cubic-bezier(.22,1,.36,1), box-shadow 0.2s ease;
    transform-origin: center center;
    transform: scale(1);
    touch-action: none; /* gestures handled on container */
    -webkit-user-drag: none;
}

/* programmatic zoom state (used by JS) */
.slider-modal-content.zoomed img {
    transition: transform 120ms ease;
    /* actual scale is set inline by JS for pinch/double-tap */
}

/* small visual scale when modal opens */
.slider-modal.open .slider-modal-content img { transform: scale(1.02); }

/* Modal responsive + expand behavior */
.slider-modal-content {
    transition: width 320ms cubic-bezier(.2,.9,.2,1), left 320ms cubic-bezier(.2,.9,.2,1), transform 320ms ease;
}
.slider-modal-content.expanded {
    left: 0 !important;
    top: 0 !important;
    width: calc(100vw - 16px) !important;
    max-width: 100vw !important;
    height: 100vh !important;
    padding: 12px !important;
    transform: none !important;
}

@media (max-width: 900px) {
    /* keep modal content responsive and allow horizontal scrolling for side-by-side cards */
    #sliderModalContent { flex-direction: row !important; align-items: flex-start !important; overflow-x: auto; -webkit-overflow-scrolling: touch; padding: 12px !important; max-width: 96vw !important; width: calc(100vw - 24px) !important; }

    /* make cards flow horizontally (override absolute positioning used on large screens) */
    #stTitlesPanel, #sliderAllTitleListCard, #sliderMoaCard, #sliderUploadedCard, #sliderExprCard, #sliderResCard, #sliderTotalStCard, #sliderStatsCard {
        position: relative !important;
        left: auto !important;
        top: auto !important;
        transform: none !important;
        display: inline-block !important;
        vertical-align: top !important;
        width: min(86vw, 360px) !important;
        margin: 6px !important;
    }

    /* make the chart wrapper flexible so it scales with available width */
    #modalStatsChartWrap { min-width: 280px; width: min(86vw, 360px); flex: 0 0 auto; }
    #modalStatsChart { width: 100% !important; height: auto !important; }

    /* smooth transitions for card visibility and layout changes */
    #stTitlesPanel, #sliderAllTitleListCard, #sliderMoaCard, #sliderUploadedCard, #sliderExprCard, #sliderResCard, #sliderTotalStCard, #sliderStatsCard { transition: all 260ms cubic-bezier(.2,.9,.2,1); }
}

/* Close button (modal) — floating zoom controls removed */
.slider-modal-close {
    background: rgba(0,0,0,0.45);
    color: #fff;
    border-radius: 8px;
    width:44px;
    height:44px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    font-size:1.6rem;
    border: none;
    cursor: pointer;
    z-index: 10001;
    opacity: 0;
    transition: opacity 0.3s ease 0.3s, transform 180ms ease;
    -webkit-tap-highlight-color: transparent;
    touch-action: manipulation;
}

/* floating zoom control UI removed — gestures still work but controls are hidden */
.modal-zoom-controls .modal-zoom-reset { background:transparent; color:#fff; border:1px solid rgba(255,255,255,0.12); padding:6px 8px; }

/* ensure controls are visible above the dark overlay */
.slider-modal .modal-zoom-controls .modal-zoom-btn, .slider-modal .slider-modal-close { box-shadow: 0 6px 18px rgba(0,0,0,0.35); }

/* end modal controls */

/* ====== Animations for province / city / ST transitions ====== */
#st-selection-header { transition: all 220ms cubic-bezier(.2,.9,.2,1); opacity: 0; transform: translateY(-6px); display: none !important; }

/* All Titles adjuster toolbar */
.slider-all-title-toolbar { position: absolute; top: 8px; right: 8px; display:flex; gap:6px; align-items:center; z-index:12; }
.slider-all-title-toolbar button { background: rgba(2,6,23,0.06); border: none; padding:4px 6px; border-radius:6px; font-size:12px; cursor:pointer; color:#0b2540; }
.slider-all-title-toolbar .nudge { width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; box-shadow:none; }
#allTitleAdjustStatus { font-size:11px; padding:3px 6px; border-radius:999px; background:#eef2ff; color:#3730a3; font-weight:700; }
#st-selection-header.visible { opacity: 1; transform: none; /* kept for compatibility but header is hidden */ }

.st-title-listing-table .prov-row,
.st-title-listing-table .prov-header-row { transition: background-color 200ms ease, transform 260ms cubic-bezier(.2,.9,.2,1), box-shadow 260ms ease; will-change: transform, box-shadow; }

/* wrapper inside table cell we animate (table rows can't be reliably transformed) */
.st-title-listing-table .prov-inner { display:block; width:100%; transition: transform 260ms cubic-bezier(.2,.9,.2,1), box-shadow 260ms ease; }

/* subtle lift + slide on hover / focus for provinces (applied to inner wrapper) */
.st-title-listing-table .prov-row:hover .prov-inner,
.st-title-listing-table .prov-row:focus-visible .prov-inner {
    transform: translateX(6px) translateY(-2px) scale(1.01);
    background: #f4fbff;
    box-shadow: 0 10px 26px rgba(2,6,23,0.06);
}
.st-title-listing-table .prov-row:active .prov-inner { transform: translateX(2px) translateY(-1px) scale(0.998); }

/* fallback: animate the TD itself when inner wrapper isn't present */
.st-title-listing-table .prov-row td,
.st-title-listing-table .prov-header-row td { transition: transform 260ms cubic-bezier(.2,.9,.2,1), box-shadow 260ms ease; }
.st-title-listing-table .prov-row:hover td,
.st-title-listing-table .prov-header-row:hover td { transform: translateX(6px) translateY(-2px) scale(1.01); background:#f4fbff; box-shadow: 0 10px 26px rgba(2,6,23,0.06); }
.st-title-listing-table .prov-row:active td { transform: translateX(2px) translateY(-1px) scale(0.998); }
.table-active { background-color: #e9f7ff !important; transition: background-color 260ms ease; }

/* Selected province should look like a header */
.st-title-listing-table .prov-row.prov-selected { background: #f7f9fa; font-weight:800; font-size:1rem; color: #0d6efd; border-left:4px solid rgba(13,110,253,0.18); padding-left:8px; box-shadow: inset 0 0 0 rgba(13,110,253,0.02); }

/* badge shown under prov header when a city is selected */
.prov-selected-badge { display:inline-block; margin-left:10px; background: rgba(13,110,253,0.06); color:#0d6efd; border-radius:999px; padding:3px 8px; font-size:0.78rem; vertical-align:middle; }
.prov-row.prov-selected td { position:relative; }
.prov-row.prov-selected td::after { content: ''; /* reserved for possible future icon */ }


/* make province header rows feel interactive (cursor + hover fallback for paths without .prov-inner) */
.st-title-listing-table .prov-header-row { cursor: pointer; }
.st-title-listing-table .prov-header-row td { transition: transform 260ms cubic-bezier(.2,.9,.2,1), box-shadow 260ms ease; }
.st-title-listing-table .prov-header-row:hover td { transform: translateX(6px) translateY(-2px) scale(1.01); background:#f4fbff; box-shadow: 0 10px 26px rgba(2,6,23,0.06); }
.st-title-listing-table .city-row { transition: background 180ms ease, transform 200ms ease, opacity 220ms ease; cursor: pointer; }
.st-title-listing-table .city-row:hover { background:#fbfdfe; transform: translateX(6px); }

/* section divider between Provinces / Cities / STs */
.st-title-listing-table .section-divider td { padding:8px 12px; color:#6c757d; font-size:0.78rem; text-transform:uppercase; letter-spacing:0.06em; border-top:1px solid #eef3f6; border-bottom:1px solid #f5f7f9; background:transparent; }
.st-title-listing-table .divider-label { display:block; width:100%; }
.st-title-listing-table .section-divider.row-anim { opacity:0; transform:translateY(6px); transition: opacity 220ms ease, transform 260ms cubic-bezier(.2,.9,.2,1); }
.st-title-listing-table .section-divider.row-anim.show { opacity:1; transform:none; }
.st-title-listing-table .city-row.city-selected { background:#e8f4ff; font-weight:700; color: #0d6efd; border-left:4px solid rgba(13,110,253,0.08); padding-left:8px; position:relative; }
.st-title-listing-table .city-row.city-selected td { position:relative; } /* checkmark removed */

/* also show check indicator for selected province */
.st-title-listing-table .prov-row.prov-selected td { position:relative; } /* header/city checkmark removed */


.st-title-listing-table .st-row { opacity: 0; transform: translateY(6px); transition: opacity 260ms ease, transform 260ms ease; }
.st-title-listing-table .st-row.show { opacity: 1; transform: none; }

/* ST Titles panel fade + compact rows */
#stTitlesPanel { opacity: 0; transform: translateY(6px); transition: opacity 260ms ease, transform 260ms ease; }
#stTitlesPanel.visible { opacity: 1; transform: none; }
#stTitlesPanel.hidden { opacity: 0; transform: translateY(6px); pointer-events: none; }

/* MOA card fade behaviour — mirror ST Titles panel */
#sliderMoaCard { opacity: 0; transform: translateY(6px); transition: opacity 260ms ease, transform 260ms ease; }
#sliderMoaCard.visible { opacity: 1; transform: none; }
#sliderMoaCard.hidden { opacity: 0; transform: translateY(6px); pointer-events: none; }

/* Uploaded MOAs card — same fade behaviour */
#sliderUploadedCard { opacity: 0; transform: translateY(6px); transition: opacity 260ms ease, transform 260ms ease; }
#sliderUploadedCard.visible { opacity: 1; transform: none; }
#sliderUploadedCard.hidden { opacity: 0; transform: translateY(6px); pointer-events: none; }

/* Expression of Interest + SB Resolution + Stats cards */
#sliderExprCard, #sliderResCard, #sliderTitleListCard, #sliderAllTitleListCard, #sliderTotalStCard, #sliderStatsCard { opacity: 0; transform: translateY(6px); transition: opacity 260ms ease, transform 260ms ease; }
#sliderExprCard.visible, #sliderResCard.visible, #sliderTitleListCard.visible, #sliderAllTitleListCard.visible, #sliderTotalStCard.visible, #sliderStatsCard.visible { opacity: 1; transform: none; }
#sliderExprCard.hidden, #sliderResCard.hidden, #sliderTitleListCard.hidden, #sliderAllTitleListCard.hidden, #sliderTotalStCard.hidden, #sliderStatsCard.hidden { opacity: 0; transform: translateY(6px); pointer-events: none; }

/* Full-list item styling */
.slider-all-title-item { transition: background 160ms ease; }
.slider-all-title-item:hover { background: #fbfdfe; }
.slider-all-title-item.active { background: rgba(13,110,253,0.04); border-left:4px solid rgba(13,110,253,0.08); }

/* replicate popover (comment-style) shown when user selects an ST in the full list) */
.st-replicate-popover { position:absolute; z-index:1620; min-width:220px; max-width:320px; padding:10px 12px; border-radius:10px; background:#fff; box-shadow:0 12px 36px rgba(2,6,23,0.12); font-size:0.88rem; color:#0f172a; opacity:0; transform:translateY(-6px); transition:opacity 180ms ease, transform 220ms cubic-bezier(.2,.9,.2,1); pointer-events:none; border:1px solid rgba(2,6,23,0.04); }
.st-replicate-popover.visible { opacity:1; transform:none; pointer-events:auto; }
.st-replicate-popover .replicate-msg { font-weight:600; color:#0b2540; }
.st-replicate-popover .replicate-title { margin-top:6px; font-size:0.9rem; color:#111827; max-height:48px; overflow:hidden; text-overflow:ellipsis; }
.st-replicate-popover .replicate-actions { display:flex; gap:8px; justify-content:flex-end; margin-top:10px; }
.st-replicate-popover .replicate-actions button { padding:6px 8px; border-radius:6px; border: none; font-size:0.85rem; cursor:pointer; }
.st-replicate-popover .replicate-yes { background:#0eaeb5; color:#fff; }
.st-replicate-popover .replicate-no { background: rgba(2,6,23,0.06); color:#0b2540; }
.st-replicate-popover::after { content:''; position:absolute; top:100%; left:18px; border-width:6px; border-style:solid; border-color: #fff transparent transparent transparent; filter: drop-shadow(0 -2px 2px rgba(0,0,0,0.04)); }
.st-replicate-popover.confirmed { background:#f0fdf4; border-color:#bbf7d0; }

.st-title-listing-table td { padding: 8px 10px; font-size:0.95rem; }
.st-title-listing-table .prov-header-row td { padding:10px 12px; }
.st-title-listing-table .prov-header-row.visible-header td { background:#eef6ff; border-bottom:1px solid rgba(2,6,23,0.04); }
#stTitlesPanel::-webkit-scrollbar { width:8px; }
#stTitlesPanel::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.08); border-radius:6px; }


/* generic row entrance animation */
.row-anim { opacity: 0; transform: translateY(6px); }
.row-anim.show { opacity: 1; transform: none; transition: all 260ms cubic-bezier(.2,.9,.2,1); }

/* faded state used to dim non-selected rows during transitions */
.st-title-listing-table tr.faded { opacity: 0.18; transform: translateX(-6px) scale(.995); pointer-events: none; transition: opacity 220ms ease, transform 220ms ease; }

/* floating clone for move-up animation */
.floating-clone { position:fixed; z-index:12000; background: #fff; box-shadow: 0 6px 20px rgba(0,0,0,0.12); border-radius:4px; pointer-events:none; transform-origin:left top; transition: transform 320ms cubic-bezier(.2,.9,.2,1), opacity 260ms ease; opacity:1; }

/* move-up animation applied to actual rows (simpler than cloning) */
.prov-row.move-up { transform: translateY(-36px); transition: transform 360ms cubic-bezier(.2,.9,.2,1), opacity 220ms ease; z-index:5; box-shadow: 0 6px 18px rgba(0,0,0,0.08); }
.city-row.move-up { transform: translateY(-22px); transition: transform 320ms cubic-bezier(.2,.9,.2,1), opacity 200ms ease; z-index:4; box-shadow: 0 6px 14px rgba(0,0,0,0.06); }

/* small caret for clickable city rows */
.city-row > td:first-child { font-weight:600; color:#0d6efd; }

</style>

<!-- Card gallery (uses images in public/images/dattachments) -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<section class="card-gallery" style="--card-bg:#fff; position:relative; z-index:60; pointer-events:auto;">
    <div class="container-cards" style="pointer-events:auto;">
        <a class="card card-link" href="#" data-href="/category/older-person" data-title="Older Person" aria-label="Older Person">
            <div class="imgContainer">
                <div class="logo-badge"><img src="{{ asset('images/dattachments/Older Person logo.png') }}" alt="Older Person logo"></div>
            </div>
            <div class="content">
                <h2>Older Person</h2>
                <p>are valuable members of society who deserve to age with dignity, security, and respect. Promoting their health, social protection, economic security, and opportunities for active participation supports their well-being, independence, and continued contribution to their families and communities.</p>
            </div>
        </a>

        <a class="card card-link" href="#" data-href="/category/internally-displaced-person" data-title="Internally Displaced Person" aria-label="Internally Displaced Person">
            <div class="imgContainer">
                <div class="logo-badge"><img src="{{ asset('images/dattachments/Internally Displaced Person logo.png') }}" alt="Internally Displaced Person logo"></div>
            </div>
            <div class="content">
                <h2>Internally Displaced Person</h2>
                <p>are individuals or families forced to leave their homes due to armed conflict, disasters, violence, or other emergencies. Ensuring timely humanitarian assistance, protection, access to basic services, and support for recovery and reintegration helps restore their safety, dignity, and resilience.</p>
            </div>
        </a>

        <a class="card card-link" href="#" data-href="/category/indigenous-peoples" data-title="Indigenous Peoples" aria-label="Indigenous Peoples">
            <div class="imgContainer">
                <div class="logo-badge"><img src="{{ asset('images/dattachments/Indenuos peoples logo.png') }}" alt="Indigenous Peoples logo"></div>
            </div>
            <div class="content">
                <h2>Indigenous Peoples</h2>
                <p>are culturally distinct communities with their own social structures, traditions, and ancestral domains. Promoting their rights, cultural integrity, self-determination, and equitable access to basic services and development opportunities ensures inclusive growth while respecting their identity, knowledge systems, and way of life.</p>
            </div>
        </a>

        <a class="card card-link" href="#" data-href="/category/women" data-title="Women" aria-label="Women">
            <div class="imgContainer">
                <div class="logo-badge"><img src="{{ asset('images/dattachments/Women logo.png') }}" alt="Women logo"></div>
            </div>
            <div class="content">
                <h2>Women</h2>
                <p>are key contributors to social and economic development. Ensuring gender equality and empowering women through access to opportunities, protection from discrimination and violence, and participation in decision-making promotes inclusive growth and strengthens families, communities, and institutions.</p>
            </div>
        </a>

        <a class="card card-link" href="#" data-href="/category/person-with-disability" data-title="Person with Disability" aria-label="Person with Disability">
            <div class="imgContainer">
                <div class="logo-badge"><img src="{{ asset('images/dattachments/Person with disability logo.png') }}" alt="Person with disability logo"></div>
            </div>
            <div class="content">
                <h2>Person with Disability</h2>
                <p>are individuals with long-term physical, sensory, intellectual, or psychosocial impairments who may face barriers to full participation in society. Promoting accessibility, inclusion, equal opportunities, and rights-based support enables PWDs to live independently, participate productively, and contribute meaningfully to community development.</p>
            </div>
        </a>

        <a class="card card-link" href="#" data-href="/category/children-and-youth" data-title="Children & Youth" aria-label="Children and Youth">
            <div class="imgContainer">
                <div class="logo-badge"><img src="{{ asset('images/dattachments/Children and Youth logo.png') }}" alt="Children and Youth logo"></div>
            </div>
            <div class="content">
                <h2>Children & Youth</h2>
                <p>are vital assets of the nation and the foundation of future development. Ensuring their survival, protection, development, and meaningful participation through access to quality education, health, protection services, life skills, and opportunities for engagement enables them to reach their full potential and become responsible, productive, and empowered members of society.</p>
            </div>
        </a>

        <a class="card card-link" href="#" data-href="/category/family" data-title="Family" aria-label="Family">
            <div class="imgContainer">
                <div class="logo-badge"><img src="{{ asset('images/dattachments/Family logo.png') }}" alt="Family logo"></div>
            </div>
            <div class="content">
                <h2>Family</h2>
                <p>is the basic unit of society and the primary source of care, protection, and social development for its members/ Strengthening families promotes stability, resilience, and improved well-being, enabling individuals especially children and older persons to grow in a safe, nurturing, and supportive environment.</p>
            </div>
        </a>
    </div>
</section>

<style>
/* Scoped card styles — do NOT override global page styles */
.card-gallery { display:flex; justify-content:center; align-items:center; padding:28px 12px; position:relative; z-index:60; pointer-events:auto; }
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
.container-cards { display:flex; gap:18px; flex-wrap:nowrap; justify-content:flex-start; align-items:flex-start; overflow-x:auto; -webkit-overflow-scrolling:touch; scroll-behavior:smooth; padding:8px 6px; }
.container-cards::-webkit-scrollbar{ height:8px; }
.container-cards::-webkit-scrollbar-thumb{ background: rgba(0,0,0,0.08); border-radius:6px; }
/* collapsed card (single-row) — left badge initially centered above */
.container-cards { display:flex; gap:18px; flex-wrap:nowrap; justify-content:flex-start; align-items:center; overflow-x:auto; overflow-y:visible; -ms-overflow-style: none; scrollbar-width: none; scroll-behavior:smooth; padding:8px 6px; }
.container-cards::-webkit-scrollbar{ display:none; }
/* allow drag-to-scroll affordance */
.container-cards { cursor: grab; -webkit-user-select: none; user-select: none; }
.container-cards.dragging { cursor: grabbing; }
.container-cards img { -webkit-user-drag: none; user-drag: none; }
.container-cards .card { background:var(--card-bg); flex:0 0 240px; height:215px; margin:8px 6px; padding:16px; border-radius:12px; box-shadow: 0 6px 24px rgba(2,6,23,0.08); transition: flex-basis 360ms cubic-bezier(.2,.9,.2,1), box-shadow 220ms ease; overflow:visible; position:relative; display:flex; align-items:center; gap:18px; text-decoration:none; color:inherit; cursor:pointer; }
/* expand width to the right only */
/* only allow hover expansion when the scroller is NOT actively scrolling or being drag-scrolled */
.container-cards:not(.dragging):not(.is-scrolling) .card:hover { flex:0 0 760px; transform: translateY(-6px); box-shadow: 0 18px 60px rgba(2,6,23,0.12); }

/* image starts centered then animates to left on expand */
.container-cards .card .imgContainer { position:absolute; top:16px; left:50%; transform:translate(-50%, 0); width:120px; height:120px; z-index:3; box-shadow: 0 8px 34px rgba(2,6,23,0.08); border-radius:8px; overflow:visible; background: transparent; display:flex; align-items:center; justify-content:center; transition: left 420ms cubic-bezier(.2,.9,.2,1), transform 420ms cubic-bezier(.2,.9,.2,1), top 420ms ease; }
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
    font-size: 0.95rem;
    color: #374151; /* gray-700 */
    font-weight: 600;
    text-align: center;
    white-space: nowrap;
    pointer-events: none;
    opacity: 1;
    z-index: 2;
    transition: opacity 220ms ease, transform 220ms ease;
}

/* hide collapsed label while card expands / types / focused (image-hover or keyboard focus)
   NOTE: do NOT hide when the card merely has `.title-typed` (keeps collapsed label visible after typing completes) */
.container-cards .card.image-hover::after,
.container-cards .card.typing-active::after,
.container-cards .card:focus-visible::after {
    opacity: 0;
    transform: translateX(-50%) translateY(-6px);
} 

/* content panel (hidden when collapsed) — revealed to the right and shifted to avoid overlap */
.container-cards .card .content { width:0; opacity:0; visibility:hidden; overflow:hidden; transition: width 360ms cubic-bezier(.2,.9,.2,1), opacity 220ms ease, margin-left 360ms cubic-bezier(.2,.9,.2,1); display:flex; flex-direction:column; justify-content:center; padding:0; margin-left:0; z-index:1; }
.container-cards .card .content h2 { font-size:1.05rem; margin:0 0 6px 0; text-align:left; }
.container-cards .card .content p { color:#505050; font-size:0.92rem; line-height:1.35; margin:0; text-align:left; max-width:520px; opacity:0; transition: opacity 200ms ease; }
/* paragraph visible only after the title has finished typing (class `title-typed`) */
.container-cards .card.title-typed .content p { opacity:1; }
/* when expanded, move the content to the right so the badge doesn't cover text */
.container-cards:not(.dragging):not(.is-scrolling) .card:hover .content,
.container-cards .card:focus-visible .content,
.container-cards .card.typing-active .content {
    margin-left:160px; /* shift past the badge */
    width: calc(100% - 200px);
    opacity:1;
    visibility:visible;
    padding-left:20px;
}

/* animate image to left when expanded or during typing */
.container-cards:not(.dragging):not(.is-scrolling) .card:hover .imgContainer,
.container-cards .card:focus-visible .imgContainer,
.container-cards .card.typing-active .imgContainer {
  left:18px;
  top:50%;
  transform:translate(0, -50%);
}

.card-link:focus-visible { outline: 3px solid rgba(16,174,181,0.18); outline-offset:4px; border-radius:12px; }

/* typing cursor */
.content p.typing::after, .content h2.typing::after { content: '\007C'; margin-left:6px; display:inline-block; opacity:1; animation: blink 1s steps(1) infinite; }
@keyframes blink { 50% { opacity: 0; } }
.card-link:focus-visible { outline: 3px solid rgba(16,174,181,0.25); outline-offset:4px; border-radius:10px; }
.container-cards .card .content h2 { font-size:1.05rem; margin-bottom:6px; color: #111827; }
.container-cards .card .content p { color:#505050; font-size:0.86rem; line-height:1.35; }
@media (max-width: 1200px) { .container-cards { gap:12px; } .logo-badge { width:92px; height:92px; } .container-cards .card { flex:0 0 220px; } }
@media (max-width: 820px) { .container-cards .card { flex:0 0 200px; } .container-cards .card .imgContainer { width:180px; height:180px; left:12px; } .logo-badge { width:76px; height:76px; } }
@media (max-width: 420px) { .container-cards { gap:10px; } .container-cards .card { width:90%; max-width:320px; margin:10px 0; } .container-cards .card .imgContainer { left:calc(50% - 90px); } }


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

                // hover-snap state: when user hovers a partially-visible card, speed up until that card is fully visible, then pause
                let hoverSnap = { active:false, card:null, target:0 };
                const HOVER_SNAP_DURATION_MS = 1000; // aim to finish snap within ~1 second
                const MIN_HOVER_SPEED = 60; // px/sec minimum for snapping (lower for smooth small adjustments)
                const MAX_HOVER_SPEED = 2000; // px/sec cap to avoid extreme jumps
                const AUTO_RESUME_MS = 2500; // resume autoscroll automatically after this many ms
                let _autoResumeTimer = null;

                function _clearAutoResume(){ if (_autoResumeTimer) { clearTimeout(_autoResumeTimer); _autoResumeTimer = null; } }
                function _scheduleAutoResume(){ _clearAutoResume(); _autoResumeTimer = setTimeout(()=>{ if (!hoverSnap.active) { running = true; scroller.classList.remove('autoscroll-paused'); } _autoResumeTimer = null; }, AUTO_RESUME_MS); }

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

                    running = true; // ensure loop runs
                    scroller.classList.remove('autoscroll-paused');
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
                    running = true;
                    scroller.classList.remove('autoscroll-paused');
                }

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
                    const prev = card ? _liftTimers.get(card) : null;
                    if (prev) { clearTimeout(prev); if (card) _liftTimers.delete(card); }
                    if (card) card.classList.remove('card-lifted');
                    scroller.classList.remove('lift-in-progress');
                    // keep gallery-lifted while hoverSnap is active (keeps floated look while paused)
                    if (!hoverSnap.active) scroller.classList.remove('gallery-lifted');
                }

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
                                    try { snappedCard.classList.remove('card-lifted'); } catch(e){}
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
                scroller.addEventListener('mouseleave', ()=> { cancelLift(); if (!hoverSnap.active) { running = true; scroller.classList.remove('autoscroll-paused'); } lastTs = null; });
                scroller.addEventListener('focusin', ()=> { if (!hoverSnap.active) running = false; });
                scroller.addEventListener('focusout', ()=> { cancelHoverSnap(); running = true; lastTs = null; });

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
                    if (!hoverSnap.active) { running = true; lastTs = null; scroller.classList.remove('autoscroll-paused'); }
                });
                // touch fallback — pause on touch
                scroller.addEventListener('touchstart', ()=> { running = false; scroller.classList.add('autoscroll-paused'); });
                scroller.addEventListener('touchend', ()=> { if (!hoverSnap.active) { running = true; lastTs = null; scroller.classList.remove('autoscroll-paused'); } });

                // Drag-to-scroll (pointer) — enables slide/drag scrolling with mouse & touchpad
                let _isPointerDragging = false;
                let _pointerDragStartX = 0;
                let _pointerDragStartScroll = 0;
                let _scrollTimer = null; // debounce timer used to mark active scrolling (prevents hover-expand)
                const _DRAG_THRESHOLD = 6; // px

                scroller.addEventListener('pointerdown', (ev) => {
                    // only primary button
                    if (ev.button && ev.button !== 0) return;
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

                    if (useTransformFallback || _isPointerDragging) return;
                    try {
                        if (scroller.scrollLeft >= originalWidth) scroller.scrollLeft -= originalWidth;
                        else if (scroller.scrollLeft < 0) scroller.scrollLeft += originalWidth;
                    } catch(e) { /* ignore */ }
                });

                // convert vertical wheel into horizontal scroll when pointer is over the gallery
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
                    if (!hoverSnap.active) { running = true; scroller.classList.remove('autoscroll-paused'); }
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
                try { startTypingSequence(card); } catch(e){}
            });

            scrollerEl.addEventListener('pointerout', (ev) => {
                const card = ev.target && ev.target.closest ? ev.target.closest('.card') : null;
                if (!card || !scrollerEl.contains(card)) return;
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
            @endphp

            @foreach ($sliderImages as $img)
                <div class="swiper-slide">
                    <img src="/images/ST Regional Nav Slide/{{ $img }}"
                         class="slider-img"
                         data-img="/images/ST Regional Nav Slide/{{ $img }}">
                </div>
            @endforeach

        </div>

        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
</div>




<!-- Modal -->
<style>
    /* Ensure the modal is always above the region image */
    .modal-backdrop.show { z-index: 1200 !important; }
    .modal.fade.show { z-index: 1300 !important; }
    .modal-dialog { z-index: 1301 !important; }

    /* ST Titles panel — smoother rounded container */
    #stTitlesPanel {
        border-radius: 14px; /* smooth curve on all corners */
        box-shadow: 0 12px 36px rgba(2,6,23,0.12);
        overflow: auto; /* keep scroll while clipping children to border radius */
        -webkit-overflow-scrolling: touch;
    }
    /* ensure the table inside the rounded panel respects the radius */
    #stTitlesPanel .st-title-listing-table {
        border-collapse: separate;
        border-radius: 0 0 12px 12px;
        overflow: hidden;
        background-clip: padding-box;
    }
</style>
<div class="slider-modal" id="sliderModal">
    <div class="slider-modal-overlay" id="sliderModalOverlay"></div>
    <div class="slider-modal-content" id="sliderModalContent" style="position:relative; flex-direction:column; align-items:center; justify-content:flex-start;">
        <div style="position:relative; width:100%; height:500vh;">
            <div id="modalRegionHeader" style="position:absolute; top:24px; left:0; width:100%; text-align:center; z-index:2; opacity:0; transition:opacity 0.5s; pointer-events:none;">
                <h2 id="modalRegionTitle" style="margin:0 0 5px 0; font-size:2rem; font-weight:bold; color:#fff; text-shadow:0 2px 8px #000; line-height:1.1;"></h2>
                <span id="modalRegionName" style="font-size:0.95rem; color:#eee; text-shadow:0 1px 4px #000; line-height:1.1;"></span>
            </div>
            <img id="sliderModalImg" src="" style="position:absolute; top:0; left:0; width:100%; height:100%; object-fit:contain; border-radius:18px; margin-left:0; display:block; z-index:1;">
        </div>

        <!-- Total STs (region-filtered) -->
        <div id="sliderTotalStCard" style="display:none; position:absolute; top:18px; width:220px; background:#fff; border-radius:12px; box-shadow:0 10px 30px rgba(2,6,23,0.12); padding:12px 14px; z-index:1600;">
            <div style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Total STs</div>
            <div id="sliderTotalStCardCount" style="font-size:1.6rem; font-weight:800; color:#10aeb5;">0</div>
        </div>

        <!-- MOA Totals card (region-filtered) -->
        <div id="sliderMoaCard" style="display:none; position:absolute; top:18px; width:220px; background:#fff; border-radius:12px; box-shadow:0 10px 30px rgba(2,6,23,0.12); padding:12px 14px; z-index:1600;">
            <div style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Total Memorandum of Agreement</div>
            <div id="sliderMoaCardCount" style="font-size:1.6rem; font-weight:800; color:#10aeb5;">0</div>
        </div>

        <!-- Uploaded MOA card (counts MOAs that have an uploaded attachment for this region) -->
        <div id="sliderUploadedCard" style="display:none; position:absolute; top:18px; width:220px; background:#fff; border-radius:12px; box-shadow:0 10px 30px rgba(2,6,23,0.12); padding:12px 14px; z-index:1600;">
            <div style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Uploaded MOA</div>
            <div id="sliderUploadedCardCount" style="font-size:1.6rem; font-weight:800; color:#10aeb5;">0</div>
        </div>

        <!-- Expression of Interest total (region-filtered) -->
        <div id="sliderExprCard" style="display:none; position:absolute; top:18px; width:220px; background:#fff; border-radius:12px; box-shadow:0 10px 30px rgba(2,6,23,0.12); padding:12px 14px; z-index:1600;">
            <div style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Total Expression of Interest</div>
            <div id="sliderExprCardCount" style="font-size:1.6rem; font-weight:800; color:#10aeb5;">0</div>
        </div>

        <!-- SB Resolution total (region-filtered) -->
        <div id="sliderResCard" style="display:none; position:absolute; top:18px; width:220px; background:#fff; border-radius:12px; box-shadow:0 10px 30px rgba(2,6,23,0.12); padding:12px 14px; z-index:1600;">
            <div style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Total SB Resolution</div>
            <div id="sliderResCardCount" style="font-size:1.6rem; font-weight:800; color:#10aeb5;">0</div>
        </div>

        <!-- Small statistics chart (region) -->
        <div id="sliderStatsCard" data-fixed="true" data-left="648" data-top="548" style="display:none; position:absolute; top:548px; left:648px; width:590px; background:#fff; border-radius:12px; box-shadow:0 10px 30px rgba(2,6,23,0.12); padding:12px 14px; z-index:1600;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                <div style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase;">Region Metrics</div>
                <div style="display:flex; gap:8px; align-items:center;">
                    <div id="modalStatsTotal" style="font-size:0.95rem; font-weight:800; color:#0b2540; background:#eef2ff; padding:4px 8px; border-radius:999px;">Total STs: 0</div>
                </div>
            </div>
            <div id="modalStatsChartWrap" style="position:relative; display:block; width:100%; height:215px;">
                <div id="modalStatsChartControls" style="position:absolute; top:8px; right:8px; z-index:30; display:flex; gap:6px;">
                    <button id="modalStatsZoomIn" title="Zoom in" style="background:#fff;border:1px solid rgba(11,37,64,0.06);padding:6px 8px;border-radius:8px;box-shadow:0 4px 10px rgba(2,6,23,0.06);">＋</button>
                    <button id="modalStatsZoomOut" title="Zoom out" style="background:#fff;border:1px solid rgba(11,37,64,0.06);padding:6px 8px;border-radius:8px;box-shadow:0 4px 10px rgba(2,6,23,0.06);">－</button>
                    <button id="modalStatsResetZoom" title="Reset zoom" style="background:#2563eb;color:#fff;border:none;padding:6px 8px;border-radius:8px;box-shadow:0 6px 18px rgba(37,99,235,0.12);">Reset</button>
                    <button id="modalStatsExpand" title="Expand" style="background:transparent;border:1px solid rgba(11,37,64,0.06);padding:6px 8px;border-radius:8px;color:#0b2540;">⤢</button>
                </div>
                <canvas id="modalStatsChart" width="960" height="480" style="display:block; width:100%; height:215px;"></canvas>
                <div id="modalStatsChartZones" style="position:absolute; inset:0; pointer-events:none; z-index:12; visibility:hidden;"></div>
            </div>
        </div>

        <!-- Short Top Titles card removed — using single full "All ST Titles (Region)" card only -->

        <!-- Full Title Listing (ALL ST Titles for the selected region) -->
        <div id="sliderAllTitleListCard" style="display:none; position:absolute; top:26px; right:24px; left:auto; width:600px; max-width:600px; background:#fff; border-radius:12px; box-shadow:0 10px 30px rgba(2,6,23,0.12); padding:12px 14px; z-index:1600;">
            <div style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase; margin-bottom:8px;">All ST Titles (Region)</div>

            <div id="sliderAllTitleListBody" style="height:750px; overflow:auto; margin-top:8px;">
                <!-- populated dynamically -->
                <ul id="sliderAllTitleListUl" style="list-style:none;margin:0;padding:0;">
                    <li style="color:#64748b;font-size:0.95rem;">No titles</li>
                </ul>
            </div>

            <!-- stReplicatePopover moved to modal root -->

        </div> 

        <!-- Replicate popover (modal-root; used by full-list and ST panel) -->
<div id="stReplicatePopover" class="st-replicate-popover" style="display:none;">
    <div class="replicate-msg">Replicate this ST?</div>
    <div class="replicate-title"></div>
    <div class="replicate-actions">
        <button class="replicate-no">Cancel</button>
        <button class="replicate-yes">Replicate</button>
    </div>
</div>
<div id="stTitlesPanel" style="position:absolute; top:0; left:calc(100% + 16px); width:360px; height:515px; max-height:515px; background:#fff; z-index:1500; overflow:auto; display:none; box-shadow:0 12px 36px rgba(2,6,23,0.12); border-radius:14px; border:1px solid rgba(2,6,23,0.04);">
            <div style="position:sticky; top:0; z-index:6; background:#4da1f7; color:#fff; padding:12px 16px; border-radius:14px 14px 0 0; box-shadow: inset 0 -1px 0 rgba(255,255,255,0.06);">
                <h5 style="margin:0;">ST Titles Listing (Region Matched)</h5>
            </div>

            <div style="padding:12px; overflow:auto; max-height: calc(100% - 64px); box-sizing: border-box;">
                <!-- Selection header (shows selected Province / City when chosen) -->
                <div id="st-selection-header" style="margin-bottom:8px;">
                    <!-- Filled dynamically: province and city lines -->
                </div>

                <!-- Results table: initially shows provinces as rows; clicking a province makes it the header and shows cities; clicking a city sets the city header and reveals STs -->
                <div id="st-results-wrapper">
                    <table class="table table-bordered table-striped align-middle mb-0 st-title-listing-table">
                        <tbody id="st-results-body">
                            <tr><td class="text-center">No data found.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <button class="slider-modal-close" id="sliderModalClose" aria-label="Close">&times;</button>
</div>



<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

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

    const modal = document.getElementById("sliderModal");
    const overlay = document.getElementById("sliderModalOverlay");
    const modalContent = document.getElementById("sliderModalContent");
    const modalImg = document.getElementById("sliderModalImg");
    const closeBtn = document.getElementById("sliderModalClose");

    /* --- Mobile / gesture-friendly pinch/double-tap/wheel zoom + pan --- */
    (function enableModalPinchAndZoom(){
        let scale = 1, minScale = 1, maxScale = 4; // allow higher zoom for clarity
        let tx = 0, ty = 0;                                   // translation when panned
        let pointers = new Map();
        let startDistance = 0, startScale = 1;
        let lastPanPoint = null, isPanning = false;
        let lastTap = 0;

        function clamp(v, a, b){ return Math.max(a, Math.min(b, v)); }
        function getDistance(a,b){ const dx = b.clientX - a.clientX; const dy = b.clientY - a.clientY; return Math.hypot(dx,dy); }
        function getTouchDistance(touches){ const a = touches[0], b = touches[1]; return Math.hypot(b.clientX - a.clientX, b.clientY - a.clientY); }

        function applyTransform(){
            modalImg.style.transform = `translate(${tx}px, ${ty}px) scale(${scale})`;
        }

        function clampTranslation(){
            // reasonable guard so user can't drag image off-screen; allow small overscroll
            const cont = modalContent.getBoundingClientRect();
            const img = modalImg.getBoundingClientRect();
            const maxX = Math.max(0, (img.width * scale - cont.width) / 2);
            const maxY = Math.max(0, (img.height * scale - cont.height) / 2);
            tx = clamp(tx, -maxX - 80, maxX + 80);
            ty = clamp(ty, -maxY - 80, maxY + 80);
        }

        function setScale(newScale, origin){
            newScale = clamp(newScale, minScale, maxScale);
            if (!origin) origin = { clientX: modalContent.getBoundingClientRect().left + modalContent.clientWidth/2, clientY: modalContent.getBoundingClientRect().top + modalContent.clientHeight/2 };

            // zoom toward the origin point (so pinch/double-tap feels natural)
            const imgRect = modalImg.getBoundingClientRect();
            const offsetX = (origin.clientX - imgRect.left);
            const offsetY = (origin.clientY - imgRect.top);
            // percent offsets relative to image
            const px = (offsetX / imgRect.width) - 0.5;
            const py = (offsetY / imgRect.height) - 0.5;

            // adjust translation so focal point remains under the finger/cursor
            tx -= px * imgRect.width * (newScale - scale);
            ty -= py * imgRect.height * (newScale - scale);

            scale = newScale;
            modalContent.classList.toggle('zoomed', scale > 1.01);
            clampTranslation();
            applyTransform();
        }

        // pointer event handlers (preferred)
        modalImg.addEventListener('pointerdown', ev => {
            modalImg.setPointerCapture(ev.pointerId);
            pointers.set(ev.pointerId, ev);

            if (pointers.size === 2) {
                const [p1,p2] = Array.from(pointers.values());
                startDistance = getDistance(p1,p2);
                startScale = scale;
            } else {
                lastPanPoint = { x: ev.clientX, y: ev.clientY };
                isPanning = scale > 1.01;
            }

            const now = Date.now();
            if (now - lastTap < 350) { // double-tap to toggle zoom
                setScale(scale > 1.05 ? 1 : Math.min(2.2, maxScale), ev);
            }
            lastTap = now;
        }, { passive: true });

        modalImg.addEventListener('pointermove', ev => {
            if (!pointers.has(ev.pointerId)) return;
            pointers.set(ev.pointerId, ev);

            if (pointers.size === 2) {
                const [p1,p2] = Array.from(pointers.values());
                const dist = getDistance(p1,p2);
                if (startDistance > 0) {
                    setScale(startScale * (dist / startDistance), { clientX: (p1.clientX + p2.clientX)/2, clientY: (p1.clientY + p2.clientY)/2 });
                }
            } else if (isPanning && lastPanPoint) {
                const dx = ev.clientX - lastPanPoint.x;
                const dy = ev.clientY - lastPanPoint.y;
                lastPanPoint = { x: ev.clientX, y: ev.clientY };
                tx += dx; ty += dy; clampTranslation(); applyTransform();
            }
        }, { passive: true });

        ['pointerup','pointercancel','pointerout','pointerleave'].forEach(name => modalImg.addEventListener(name, ev => {
            pointers.delete(ev.pointerId);
            if (pointers.size < 2) startDistance = 0;
            if (scale < 1.03) { scale = 1; tx = 0; ty = 0; applyTransform(); modalContent.classList.remove('zoomed'); }
            if (pointers.size === 0) { isPanning = false; lastPanPoint = null; }
        }, { passive: true }));

        // touch fallback (older Safari / some mobile browsers)
        modalImg.addEventListener('touchstart', ev => {
            if (ev.touches && ev.touches.length === 2) { startDistance = getTouchDistance(ev.touches); startScale = scale; }
            else if (ev.touches && ev.touches.length === 1) { lastPanPoint = { x: ev.touches[0].clientX, y: ev.touches[0].clientY }; isPanning = scale > 1.01; }
        }, { passive: false });

        modalImg.addEventListener('touchmove', ev => {
            if (ev.touches && ev.touches.length === 2) {
                const dist = getTouchDistance(ev.touches);
                const midX = (ev.touches[0].clientX + ev.touches[1].clientX)/2;
                const midY = (ev.touches[0].clientY + ev.touches[1].clientY)/2;
                setScale(startScale * (dist / startDistance), { clientX: midX, clientY: midY });
                ev.preventDefault();
            } else if (ev.touches && ev.touches.length === 1 && isPanning) {
                const dx = ev.touches[0].clientX - lastPanPoint.x;
                const dy = ev.touches[0].clientY - lastPanPoint.y;
                lastPanPoint = { x: ev.touches[0].clientX, y: ev.touches[0].clientY };
                tx += dx; ty += dy; clampTranslation(); applyTransform();
                ev.preventDefault();
            }
        }, { passive: false });

        modalImg.addEventListener('touchend', ev => {
            if (!ev.touches || ev.touches.length < 2) startDistance = 0;
            if (scale < 1.03) { scale = 1; tx = 0; ty = 0; applyTransform(); modalContent.classList.remove('zoomed'); }
        }, { passive: true });

        // wheel zoom while hovering the image (desktop)
        modalImg.addEventListener('wheel', ev => {
            ev.preventDefault();
            const delta = -ev.deltaY * 0.0016; // tuned sensitivity
            setScale(clamp(scale * (1 + delta), minScale, maxScale), ev);
        }, { passive: false });

        // Zoom control buttons (visible controls) — image handler will NOT bind them; visible controls now operate on the whole modal (global zoom).
        const btnIn = document.getElementById('modalZoomIn');
        const btnOut = document.getElementById('modalZoomOut');
        const btnReset = document.getElementById('modalZoomReset');
        // (listeners for these buttons are attached to the global-modal zoom handler below)

        // small UX: tapping modalContent on narrow view toggles expanded/full layout (keeps existing behavior)
        modalContent.addEventListener('click', ev => { if (window.innerWidth <= 900 && !(scale > 1.01)) modalContent.classList.toggle('expanded'); });

        function resetModalZoom(){ scale = 1; tx = 0; ty = 0; startDistance = 0; startScale = 1; pointers.clear(); modalImg.style.transform = ''; modalImg.style.transformOrigin = ''; modalContent.classList.remove('zoomed','expanded'); }

        // expose reset for modal close
        modal.resetZoom = resetModalZoom;
    })();


    /* --- Global modal (cards + image) pinch/zoom + pan — scales the whole `#sliderModalContent` --- */
    (function enableGlobalModalZoom(){
        let gScale = 1, gMin = 1, gMax = 1.6; // whole-modal scale bounds
        let gTx = 0, gTy = 0; // pan offsets
        let gPointers = new Map();
        let gStartDistance = 0, gStartScale = 1;
        let isPanning = false, lastPanPoint = null;

        function gGetDistance(a,b){ const dx = b.clientX - a.clientX; const dy = b.clientY - a.clientY; return Math.hypot(dx,dy); }
        function applyGlobalTransform(){
            // apply translate + scale to the modalContent so cards + image zoom together
            modalContent.style.transform = `translate(${gTx}px, ${gTy}px) scale(${gScale})`;
        }
        function clampGlobalTranslation(){
            const rect = modalContent.getBoundingClientRect();
            // allow panning only within reasonable overscroll limits
            const maxX = Math.max(0, (rect.width * gScale - rect.width) / 2);
            const maxY = Math.max(0, (rect.height * gScale - rect.height) / 2);
            gTx = Math.max(-maxX - 80, Math.min(maxX + 80, gTx));
            gTy = Math.max(-maxY - 80, Math.min(maxY + 80, gTy));
        }

        function setGlobalScale(newScale, originEvent){
            newScale = Math.max(gMin, Math.min(gMax, newScale));
            // keep focal point stable when zooming
            if (originEvent && originEvent.clientX !== undefined) {
                const r = modalContent.getBoundingClientRect();
                const ox = originEvent.clientX - r.left;
                const oy = originEvent.clientY - r.top;
                const px = (ox / r.width) - 0.5;
                const py = (oy / r.height) - 0.5;
                gTx -= px * r.width * (newScale - gScale);
                gTy -= py * r.height * (newScale - gScale);
            }
            gScale = newScale;
            modalContent.classList.toggle('global-zoomed', gScale > 1.01);
            clampGlobalTranslation();
            applyGlobalTransform();
        }

        // pointer handlers on the modal container (ignore interactions that start on controls)
        modalContent.addEventListener('pointerdown', ev => {
            if (ev.target.closest('.modal-zoom-controls, .slider-modal-close, button, a, input, textarea, select')) return;
            modalContent.setPointerCapture(ev.pointerId);
            gPointers.set(ev.pointerId, ev);
            if (gPointers.size === 2) {
                const [p1,p2] = Array.from(gPointers.values());
                gStartDistance = gGetDistance(p1,p2);
                gStartScale = gScale;
            } else {
                lastPanPoint = { x: ev.clientX, y: ev.clientY };
                isPanning = gScale > 1.01;
            }
        }, { passive: true });

        modalContent.addEventListener('pointermove', ev => {
            if (!gPointers.has(ev.pointerId)) return;
            gPointers.set(ev.pointerId, ev);
            if (gPointers.size === 2) {
                const [p1,p2] = Array.from(gPointers.values());
                const dist = gGetDistance(p1,p2);
                if (gStartDistance > 0) setGlobalScale(gStartScale * (dist / gStartDistance), { clientX: (p1.clientX + p2.clientX)/2, clientY: (p1.clientY + p2.clientY)/2 });
            } else if (isPanning && lastPanPoint) {
                const dx = ev.clientX - lastPanPoint.x;
                const dy = ev.clientY - lastPanPoint.y;
                lastPanPoint = { x: ev.clientX, y: ev.clientY };
                gTx += dx; gTy += dy; clampGlobalTranslation(); applyGlobalTransform();
            }
        }, { passive: true });

        ['pointerup','pointercancel','pointerout','pointerleave'].forEach(name => modalContent.addEventListener(name, ev => {
            gPointers.delete(ev.pointerId);
            if (gPointers.size < 2) gStartDistance = 0;
            if (gScale < 1.03) { gScale = 1; gTx = 0; gTy = 0; applyGlobalTransform(); modalContent.classList.remove('global-zoomed'); }
            if (gPointers.size === 0) { isPanning = false; lastPanPoint = null; }
        }, { passive: true }));

        // touch fallback for mobile pinch/pan
        modalContent.addEventListener('touchstart', ev => {
            if (ev.touches && ev.touches.length === 2) { gStartDistance = gGetDistance(ev.touches[0], ev.touches[1]); gStartScale = gScale; }
            else if (ev.touches && ev.touches.length === 1) { lastPanPoint = { x: ev.touches[0].clientX, y: ev.touches[0].clientY }; isPanning = gScale > 1.01; }
        }, { passive: false });

        modalContent.addEventListener('touchmove', ev => {
            if (ev.touches && ev.touches.length === 2) {
                const dist = gGetDistance(ev.touches[0], ev.touches[1]);
                const midX = (ev.touches[0].clientX + ev.touches[1].clientX)/2;
                const midY = (ev.touches[0].clientY + ev.touches[1].clientY)/2;
                setGlobalScale(gStartScale * (dist / gStartDistance), { clientX: midX, clientY: midY });
                ev.preventDefault();
            } else if (ev.touches && ev.touches.length === 1 && isPanning) {
                const dx = ev.touches[0].clientX - lastPanPoint.x;
                const dy = ev.touches[0].clientY - lastPanPoint.y;
                lastPanPoint = { x: ev.touches[0].clientX, y: ev.touches[0].clientY };
                gTx += dx; gTy += dy; clampGlobalTranslation(); applyGlobalTransform();
                ev.preventDefault();
            }
        }, { passive: false });

        modalContent.addEventListener('touchend', ev => {
            if (!ev.touches || ev.touches.length < 2) gStartDistance = 0;
            if (gScale < 1.03) { gScale = 1; gTx = 0; gTy = 0; applyGlobalTransform(); modalContent.classList.remove('global-zoomed'); }
        }, { passive: true });

        // wheel: only zoom when ctrl/meta pressed (pinch-to-zoom on trackpads typically sends ctrl+wheel)
        modalContent.addEventListener('wheel', ev => {
            if (!(ev.ctrlKey || ev.metaKey)) return; // otherwise allow normal scroll
            ev.preventDefault();
            const delta = -ev.deltaY * 0.0016;
            setGlobalScale(Math.max(gMin, Math.min(gMax, gScale * (1 + delta))), ev);
        }, { passive: false });

        // wire visible controls to global modal zoom (these override previous image-only bindings)
        const btnInG = document.getElementById('modalZoomIn');
        const btnOutG = document.getElementById('modalZoomOut');
        const btnResetG = document.getElementById('modalZoomReset');
        if (btnInG) btnInG.addEventListener('click', () => setGlobalScale(Math.min(gMax, gScale * 1.25)));
        if (btnOutG) btnOutG.addEventListener('click', () => setGlobalScale(Math.max(gMin, gScale / 1.25)));
        if (btnResetG) btnResetG.addEventListener('click', () => { gScale = 1; gTx = 0; gTy = 0; applyGlobalTransform(); modalContent.classList.remove('global-zoomed'); });

        function resetGlobalZoom(){ gScale = 1; gTx = 0; gTy = 0; gStartDistance = 0; gStartScale = 1; gPointers.clear(); modalContent.style.transform = ''; modalContent.classList.remove('global-zoomed'); }
        modal.resetGlobalZoom = resetGlobalZoom;
    })();


    // Chart instance for modal stats (lazy-initialized)
    const DEBUG_MODAL_CHART_HITZONES = false; // debug hit-zones off
    let modalStatsChart = null;
    function initOrUpdateModalStatsChart(values = [0,0,0,0], perYearTotals = null) {
        const el = document.getElementById('modalStatsChart');
        if (!el || typeof Chart === 'undefined') return;
        const labels = ['Total Memo','Uploaded MOA','Expression of Interest','SB Resolution'];
        const ctx = el.getContext('2d');
        // create gradient for area fill
        const gradient = ctx.createLinearGradient(0, 0, 0, el.height || 120);
        gradient.addColorStop(0, 'rgba(37,99,235,0.16)');
        gradient.addColorStop(1, 'rgba(37,99,235,0.02)');

        // helper: build datasets (first = aggregated blue; others = per-year)
        function buildDatasets(baseValues, perYear) {
            const datasets = [];
            datasets.push({
                label: 'All',
                data: baseValues,
                borderColor: '#2563eb',
                backgroundColor: gradient,
                fill: true,
                cubicInterpolationMode: 'monotone',
                tension: 0.42,
                pointStyle: 'circle',
                pointBackgroundColor: '#fff',
                pointBorderColor: '#2563eb',
                pointRadius: 10,
                pointHitRadius: 40,
                pointHoverRadius: 14,
                pointHoverBorderWidth: 3,
                borderWidth: 4,
                showValues: true
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
                    tension: 0.42,
                    pointStyle: 'circle',
                    pointBackgroundColor: '#fff',
                    pointBorderColor: color,
                    pointRadius: 6,
                    pointHitRadius: 24,
                    pointHoverRadius: 10,
                    borderWidth: 3,
                    borderDash: [6,4],
                    // show numeric labels for per-year lines (colored to match the line)
                    showValues: true
                });
            });
            return datasets;
        }

        if (!modalStatsChart) {
            // dynamic axis sizing for better readability
            const _maxVal = Math.max(1, ...(values.map(v => Number(v) || 0)));
            const _suggestedMax = Math.max(5, Math.ceil(_maxVal + Math.max(2, _maxVal * 0.15)));
            const _step = Math.ceil(_suggestedMax / 5);

            modalStatsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: buildDatasets(values, perYearTotals)
                },
                options: {
                    responsive: false,
                    maintainAspectRatio: false,
                    interaction: { mode: 'nearest', intersect: false, axis: 'xy' },
                    // hover interactions disabled
                    onHover: null,
                    events: ['click'],
                    layout: { padding: { top: 6, right: 8, bottom: 18, left: 8 } },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            align: 'center',
                            labels: { usePointStyle: true, boxWidth: 24, padding: 8, font: { size: 16, weight: '700' } },
                            onClick: function(evt, legendItem, legend) {
                                // toggle dataset visibility with coordinated fade for numbers + line/points
                                const chart = legend.chart;
                                const dsIndex = legendItem.datasetIndex;
                                const currentlyVisible = chart.isDatasetVisible(dsIndex);
                                const dsLabel = chart.data.datasets[dsIndex] && chart.data.datasets[dsIndex].label;

                                if (currentlyVisible) {
                                    // animate both label alpha and dataset alpha to 0, then hide dataset
                                    const currentLabelAlpha = (chart._labelAlphas && chart._labelAlphas[dsLabel]) || 1;
                                    const currentDatasetAlpha = (chart._datasetAlphas && chart._datasetAlphas[dsLabel]) || 1;
                                    if (chart._animateLabelAlpha || chart._animateDatasetAlpha) {
                                        let done = 0;
                                        const finish = () => { done++; if (done === 2) { chart.setDatasetVisibility(dsIndex, false); chart.update(); } };
                                        if (chart._animateLabelAlpha) chart._animateLabelAlpha(dsLabel, currentLabelAlpha, 0, 220, finish); else finish();
                                        if (chart._animateDatasetAlpha) chart._animateDatasetAlpha(dsLabel, currentDatasetAlpha, 0, 220, finish); else finish();
                                    } else {
                                        chart.setDatasetVisibility(dsIndex, false);
                                        chart.update();
                                    }
                                } else {
                                    // show dataset immediately but start it invisible, then fade both label and dataset in
                                    chart.setDatasetVisibility(dsIndex, true);
                                    if (!chart._labelAlphas) chart._labelAlphas = {};
                                    if (!chart._datasetAlphas) chart._datasetAlphas = {};
                                    chart._labelAlphas[dsLabel] = 0;
                                    chart._datasetAlphas[dsLabel] = 0;
                                    chart.update();
                                    if (chart._animateLabelAlpha) chart._animateLabelAlpha(dsLabel, 0, 1, 220);
                                    if (chart._animateDatasetAlpha) chart._animateDatasetAlpha(dsLabel, 0, 1, 220);
                                }
                            }
                        },
                        tooltip: { enabled: false }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#374151', maxRotation: 0, autoSkip: false, font: { size: 18, weight: '700' }, padding: 4 },
                            title: { display: false, text: 'Metric (Total Memorandum, Uploaded MOA, Expression of Interest, SB Resolution)', color: '#6b7280', font: { size: 13, weight: '700' }, padding: { top: 8 } }
                        },
                        y: {
                            beginAtZero: true,
                            suggestedMax: _suggestedMax,
                            ticks: { precision: 0, stepSize: _step, color: '#374151', font: { size: 14, weight: '600' } },
                            grid: { color: 'rgba(15,23,42,0.08)' },
                            title: { display: true, text: 'Number of STs', color: '#6b7280', font: { size: 13, weight: '700' }, padding: { bottom: 6 } }
                        }
                    }
                },
                plugins: [{
                    id: 'valueLabels',

                    // fade datasets by applying per-dataset alpha before/after each dataset draw
                    beforeDatasetDraw: function(chart, args, options) {
                        const dsIndex = args.index;
                        const ds = chart.data.datasets[dsIndex];
                        const alpha = (chart._datasetAlphas && typeof chart._datasetAlphas[ds.label] !== 'undefined') ? chart._datasetAlphas[ds.label] : (chart.isDatasetVisible(dsIndex) ? 1 : 0);
                        chart.ctx.save();
                        chart.ctx.globalAlpha = alpha;
                    },

                    afterDatasetDraw: function(chart, args, options) {
                        chart.ctx.restore();
                    },

                    afterDatasetsDraw: function(chart) {
                        const ctx = chart.ctx;
                        const active = chart.getActiveElements();
                        const activeIndex = (active && active.length) ? active[0].index : null;

                        chart.data.datasets.forEach((dataset, dsIndex) => {
                            // only draw labels for datasets that allow it
                            if (dataset.showValues === false) return;

                            // skip entirely when label alpha is effectively zero (supports fade-out)
                            const _alpha = (chart._labelAlphas && typeof chart._labelAlphas[dataset.label] !== 'undefined') ? chart._labelAlphas[dataset.label] : (chart.isDatasetVisible(dsIndex) ? 1 : 0);
                            if (!_alpha || _alpha <= 0.01) return;

                            const meta = chart.getDatasetMeta(dsIndex);
                            if (!meta || !meta.data) return;
                            meta.data.forEach((el, idx) => {
                                const val = dataset.data[idx];
                                if (val === null || val === undefined) return;
                                ctx.save();
                                // apply per-dataset label alpha (fade)
                                ctx.globalAlpha = _alpha;

                                const isSB = (idx === 3); // SB Resolution point
                                const isActive = (activeIndex === idx);

                                if (isSB && isActive) {
                                    // emphasize hovered SB point with ring + colored label
                                    ctx.beginPath();
                                    ctx.arc(el.x, el.y, 18, 0, Math.PI * 2);
                                    ctx.fillStyle = 'rgba(37,99,235,0.06)';
                                    ctx.fill();
                                    ctx.lineWidth = 3;
                                    ctx.strokeStyle = 'rgba(37,99,235,0.45)';
                                    ctx.stroke();

                                    ctx.font = '900 28px Poppins, Inter, system-ui, sans-serif';
                                    ctx.lineWidth = 8;
                                    ctx.strokeStyle = 'rgba(255,255,255,0.95)';
                                    ctx.strokeText(String(val), el.x, el.y - 34);
                                    // use dataset color when available (fallback to previous blue)
                                    ctx.fillStyle = (dataset && dataset.borderColor) ? dataset.borderColor : '#2563eb';
                                    ctx.fillText(String(val), el.x, el.y - 34);
                                } else if (isSB) {
                                    // SB default: slightly emphasized so it's identifiable
                                    ctx.font = '800 18px Poppins, Inter, system-ui, sans-serif';
                                    ctx.lineWidth = 6;
                                    ctx.strokeStyle = 'rgba(255,255,255,0.95)';
                                    ctx.strokeText(String(val), el.x, el.y - 24);
                                    // color text with dataset color for per-year lines, keep dark for the main 'All' label
                                    ctx.fillStyle = (dataset && dataset.label && dataset.label !== 'All' && dataset.borderColor) ? dataset.borderColor : '#0b2540';
                                    ctx.fillText(String(val), el.x, el.y - 24);
                                } else {
                                    // normal labels for other points
                                    ctx.font = '700 16px Poppins, Inter, system-ui, sans-serif';
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'bottom';
                                    // use dataset color for per-year datasets; keep dark color for the primary 'All' series
                                    const _labelColor = (dataset && dataset.label && dataset.label !== 'All' && dataset.borderColor) ? dataset.borderColor : '#0b2540';
                                    ctx.fillStyle = _labelColor;
                                    ctx.fillText(String(val), el.x, el.y - 12);
                                }

                                ctx.restore();
                            });
                        });
                    }
                }]
            });

            // attach label-alpha state + animator so numeric labels can fade in/out when datasets toggle
            modalStatsChart._labelAlphas = {};
            modalStatsChart._labelFadeHandles = {};
            modalStatsChart._setLabelAlpha = function(label, v) { this._labelAlphas[label] = v; };
            modalStatsChart._getLabelAlpha = function(label) { return (this._labelAlphas && typeof this._labelAlphas[label] !== 'undefined') ? this._labelAlphas[label] : 1; };
            modalStatsChart._animateLabelAlpha = function(label, from, to, duration = 220, cb) {
                const self = this;
                if (!label) { if (cb) cb(); return; }
                if (!self._labelAlphas) self._labelAlphas = {};
                if (self._labelFadeHandles && self._labelFadeHandles[label] && self._labelFadeHandles[label].raf) cancelAnimationFrame(self._labelFadeHandles[label].raf);
                const start = performance.now();
                const easeOutCubic = t => 1 - Math.pow(1 - t, 3);
                const step = (now) => {
                    const t = Math.min(1, (now - start) / Math.max(1, duration));
                    const v = from + (to - from) * easeOutCubic(t);
                    self._labelAlphas[label] = v;
                    // redraw to show intermediate opacity
                    try { self.draw(); } catch (e) { /* ignore */ }
                    if (t < 1) {
                        self._labelFadeHandles[label] = { raf: requestAnimationFrame(step) };
                    } else {
                        if (self._labelFadeHandles) delete self._labelFadeHandles[label];
                        if (cb) cb();
                    }
                };
                self._labelFadeHandles = self._labelFadeHandles || {};
                self._labelFadeHandles[label] = { raf: requestAnimationFrame(step) };
            };

            // initialize alphas according to visibility
            modalStatsChart.data.datasets.forEach((ds, idx) => { modalStatsChart._labelAlphas[ds.label] = modalStatsChart.isDatasetVisible(idx) ? 1 : 0; });

            // attach dataset-alpha state + animator so lines/points can fade in/out
            modalStatsChart._datasetAlphas = {};
            modalStatsChart._datasetFadeHandles = {};
            modalStatsChart._setDatasetAlpha = function(label, v) { this._datasetAlphas[label] = v; };
            modalStatsChart._getDatasetAlpha = function(label) { return (this._datasetAlphas && typeof this._datasetAlphas[label] !== 'undefined') ? this._datasetAlphas[label] : 1; };
            modalStatsChart._animateDatasetAlpha = function(label, from, to, duration = 220, cb) {
                const self = this;
                if (!label) { if (cb) cb(); return; }
                if (!self._datasetAlphas) self._datasetAlphas = {};
                if (self._datasetFadeHandles && self._datasetFadeHandles[label] && self._datasetFadeHandles[label].raf) cancelAnimationFrame(self._datasetFadeHandles[label].raf);
                const start = performance.now();
                const easeOutCubic = t => 1 - Math.pow(1 - t, 3);
                const step = (now) => {
                    const t = Math.min(1, (now - start) / Math.max(1, duration));
                    const v = from + (to - from) * easeOutCubic(t);
                    self._datasetAlphas[label] = v;
                    // redraw to show intermediate opacity
                    try { self.draw(); } catch (e) { /* ignore */ }
                    if (t < 1) {
                        self._datasetFadeHandles[label] = { raf: requestAnimationFrame(step) };
                    } else {
                        if (self._datasetFadeHandles) delete self._datasetFadeHandles[label];
                        if (cb) cb();
                    }
                };
                self._datasetFadeHandles = self._datasetFadeHandles || {};
                self._datasetFadeHandles[label] = { raf: requestAnimationFrame(step) };
            };

            // initialize dataset alphas according to visibility
            modalStatsChart.data.datasets.forEach((ds, idx) => { modalStatsChart._datasetAlphas[ds.label] = modalStatsChart.isDatasetVisible(idx) ? 1 : 0; });

            /* SB custom hover removed — using Chart.js built-in interaction (nearest, intersect:false, axis:'xy'). */
            // hit-zones disabled

            // Legend click fallback: detect clicks on legend hit-boxes and toggle datasets reliably
            (function(){
                const canvas = el; /* modalStatsChart canvas element */
                if (!canvas) return;

                const hitTestScale = () => {
                    return {
                        scaleX: modalStatsChart.width / (canvas.clientWidth || canvas.width || 1),
                        scaleY: modalStatsChart.height / (canvas.clientHeight || canvas.height || 1)
                    };
                };

                const isPointInBox = (x, y, box) => (x >= box.left && x <= (box.left + box.width) && y >= box.top && y <= (box.top + box.height));

                const onCanvasClickForLegend = function(evt) {
                    if (!modalStatsChart || !modalStatsChart.legend) return;
                    const boxes = (modalStatsChart.legend && modalStatsChart.legend.legendHitBoxes) || [];
                    if (!boxes.length) return;
                    const rect = canvas.getBoundingClientRect();
                    const { scaleX, scaleY } = hitTestScale();
                    const cx = (evt.clientX - rect.left) * scaleX;
                    const cy = (evt.clientY - rect.top) * scaleY;
                    for (let i = 0; i < boxes.length; i++) {
                        const box = boxes[i];
                        if (isPointInBox(cx, cy, box)) {
                            const item = (modalStatsChart.legend && modalStatsChart.legend.legendItems && modalStatsChart.legend.legendItems[i]) || {};
                            const dsIndex = (typeof item.datasetIndex !== 'undefined') ? item.datasetIndex : i;
                            const currentlyVisible = modalStatsChart.isDatasetVisible(dsIndex);
                            const dsLabel = modalStatsChart.data.datasets[dsIndex] && modalStatsChart.data.datasets[dsIndex].label;

                            if (currentlyVisible) {
                                // fade both label and dataset alpha, then hide dataset
                                const currentLabelAlpha = (modalStatsChart._labelAlphas && modalStatsChart._labelAlphas[dsLabel]) || 1;
                                const currentDatasetAlpha = (modalStatsChart._datasetAlphas && modalStatsChart._datasetAlphas[dsLabel]) || 1;
                                if (modalStatsChart._animateLabelAlpha || modalStatsChart._animateDatasetAlpha) {
                                    let done = 0;
                                    const finish = () => { done++; if (done === 2) { modalStatsChart.setDatasetVisibility(dsIndex, false); modalStatsChart.update(); } };
                                    if (modalStatsChart._animateLabelAlpha) modalStatsChart._animateLabelAlpha(dsLabel, currentLabelAlpha, 0, 220, finish); else finish();
                                    if (modalStatsChart._animateDatasetAlpha) modalStatsChart._animateDatasetAlpha(dsLabel, currentDatasetAlpha, 0, 220, finish); else finish();
                                } else {
                                    modalStatsChart.setDatasetVisibility(dsIndex, false);
                                    modalStatsChart.update();
                                }
                            } else {
                                // show dataset then fade label + dataset in
                                modalStatsChart.setDatasetVisibility(dsIndex, true);
                                if (!modalStatsChart._labelAlphas) modalStatsChart._labelAlphas = {};
                                if (!modalStatsChart._datasetAlphas) modalStatsChart._datasetAlphas = {};
                                modalStatsChart._labelAlphas[dsLabel] = 0;
                                modalStatsChart._datasetAlphas[dsLabel] = 0;
                                modalStatsChart.update();
                                if (modalStatsChart._animateLabelAlpha) modalStatsChart._animateLabelAlpha(dsLabel, 0, 1, 220);
                                if (modalStatsChart._animateDatasetAlpha) modalStatsChart._animateDatasetAlpha(dsLabel, 0, 1, 220);
                            }

                            evt.preventDefault();
                            return;
                        }
                    }
                };

                const onCanvasMoveForLegend = function(evt) {
                    if (!modalStatsChart || !modalStatsChart.legend) return;
                    const boxes = (modalStatsChart.legend && modalStatsChart.legend.legendHitBoxes) || [];
                    if (!boxes.length) { canvas.style.cursor = ''; return; }
                    const rect = canvas.getBoundingClientRect();
                    const { scaleX, scaleY } = hitTestScale();
                    const cx = (evt.clientX - rect.left) * scaleX;
                    const cy = (evt.clientY - rect.top) * scaleY;
                    let over = false;
                    for (let i = 0; i < boxes.length; i++) { if (isPointInBox(cx, cy, boxes[i])) { over = true; break; } }
                    canvas.style.cursor = over ? 'pointer' : '';
                };

                canvas.addEventListener('click', onCanvasClickForLegend);
                canvas.addEventListener('mousemove', onCanvasMoveForLegend);
            })();

        } else {
            const _maxVal = Math.max(1, ...(values.map(v => Number(v) || 0)));
            const _suggestedMax = Math.max(5, Math.ceil(_maxVal + Math.max(2, _maxVal * 0.15)));
            const _step = Math.ceil(_suggestedMax / 5);
            // preserve current dataset visibility by label so user toggles survive dataset rebuilds
            const _visMap = {};
            modalStatsChart.data.datasets.forEach((ds, idx) => { _visMap[ds.label] = modalStatsChart.isDatasetVisible(idx); });

            modalStatsChart.data.labels = labels;
            modalStatsChart.data.datasets = buildDatasets(values, perYearTotals);

            // reapply visibility for matching labels (default = visible)
            modalStatsChart.data.datasets.forEach((ds, idx) => {
                const vis = (typeof _visMap[ds.label] !== 'undefined') ? _visMap[ds.label] : true;
                modalStatsChart.setDatasetVisibility(idx, vis);
                // preserve or initialize per-dataset label alpha so fades persist across updates
                if (!modalStatsChart._labelAlphas) modalStatsChart._labelAlphas = {};
                modalStatsChart._labelAlphas[ds.label] = (typeof modalStatsChart._labelAlphas[ds.label] !== 'undefined') ? modalStatsChart._labelAlphas[ds.label] : (vis ? 1 : 0);
                // preserve or initialize per-dataset drawing alpha so line/points fade persist
                if (!modalStatsChart._datasetAlphas) modalStatsChart._datasetAlphas = {};
                modalStatsChart._datasetAlphas[ds.label] = (typeof modalStatsChart._datasetAlphas[ds.label] !== 'undefined') ? modalStatsChart._datasetAlphas[ds.label] : (vis ? 1 : 0);
            });

            modalStatsChart.options.scales.y.suggestedMax = _suggestedMax;
            modalStatsChart.options.scales.y.ticks.stepSize = _step;
            modalStatsChart.update();
            // hit-zones disabled
        }
    }




    // Create DOM hit-zones (overlay) for crisp per-point hover targets
    function createChartHitZones(chart) {
        try {
            const canvas = chart && chart.canvas;
            const zonesContainer = document.getElementById('modalStatsChartZones');
            if (!zonesContainer || !canvas) return;

            // Allow zones to receive pointer events (was previously none)
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

                // Prefer small, centered hit zones. Shrink radius when point is near an edge
                // so the center stays exactly at the plotted point (no lateral shift).
                const preferredR = Math.max(12, Math.min(36, Math.round(horizGapCss * 0.32)));
                const minR = 8;

                // max radius that keeps the full circle inside the overlay while keeping center
                const maxAllowedByEdges = Math.floor(Math.min(cssLeft, rect.width - cssLeft, cssTop, rect.height - cssTop));
                let r = Math.min(preferredR, Math.max(minR, maxAllowedByEdges));

                // fallback if even minR doesn't fit: clamp center and pick a very small r
                let finalLeft = cssLeft;
                let finalTop = cssTop;
                if (maxAllowedByEdges < minR) {
                    finalLeft = Math.max(minR, Math.min(rect.width - minR, cssLeft));
                    finalTop = Math.max(minR, Math.min(rect.height - minR, cssTop));
                    r = Math.max(6, Math.min(preferredR, Math.floor(Math.min(rect.width, rect.height) / 6)));
                    if (typeof DEBUG_MODAL_CHART_HITZONES !== 'undefined' && DEBUG_MODAL_CHART_HITZONES) {
                    }
                } else {
                    if (typeof DEBUG_MODAL_CHART_HITZONES !== 'undefined' && DEBUG_MODAL_CHART_HITZONES && r !== preferredR) {
                    }
                }

                const zone = document.createElement('div');
                zone.className = 'chart-hit-zone';
                zone.dataset.idx = i;
                zone.style.position = 'absolute';
                zone.style.left = (finalLeft) + 'px';
                zone.style.top = (finalTop) + 'px';
                zone.style.width = (r * 2) + 'px';
                zone.style.height = (r * 2) + 'px';
                zone.style.transform = 'translate(-50%, -50%)';
                zone.style.borderRadius = '50%';
                zone.style.pointerEvents = 'auto';
                zone.style.zIndex = 9999;

                // Debug visuals + logs
                if (typeof DEBUG_MODAL_CHART_HITZONES !== 'undefined' && DEBUG_MODAL_CHART_HITZONES) {
                    zone.style.background = 'rgba(34,197,94,0.04)';
                    zone.style.outline = '1px solid rgba(16,185,129,0.45)';
                } else {
                    zone.style.background = 'transparent';
                }

                // pointer events + capture: prevent the underlying canvas from stealing the active element
                zone.addEventListener('pointerenter', function(e) {
                    e.stopPropagation();
                    try { if (e.pointerId && zone.setPointerCapture) zone.setPointerCapture(e.pointerId); } catch (err) {}
                    if (typeof DEBUG_MODAL_CHART_HITZONES !== 'undefined' && DEBUG_MODAL_CHART_HITZONES) console.log('[hitzone] enter', i, zone.getBoundingClientRect());
                    chart.setActiveElements([{ datasetIndex: 0, index: i }]);
                    chart.tooltip.setActiveElements([{ datasetIndex: 0, index: i }], { x: p.x, y: p.y });
                    canvas.style.cursor = 'pointer';
                    chart.update();
                });

                zone.addEventListener('pointermove', function(e) {
                    e.stopPropagation();
                    chart.setActiveElements([{ datasetIndex: 0, index: i }]);
                    chart.tooltip.setActiveElements([{ datasetIndex: 0, index: i }], { x: p.x, y: p.y });
                    chart.draw();
                });

                zone.addEventListener('pointerleave', function(e) {
                    e.stopPropagation();
                    try { if (e.pointerId && zone.releasePointerCapture) zone.releasePointerCapture(e.pointerId); } catch (err) {}
                    if (typeof DEBUG_MODAL_CHART_HITZONES !== 'undefined' && DEBUG_MODAL_CHART_HITZONES) console.log('[hitzone] leave', i);
                    chart.setActiveElements([]);
                    chart.tooltip.setActiveElements([], { x: 0, y: 0 });
                    canvas.style.cursor = 'default';
                    chart.update();
                });

                zonesContainer.appendChild(zone);
            });

            // Canvas-level mouse coordinate logging (attach once)
            if (typeof DEBUG_MODAL_CHART_HITZONES !== 'undefined' && DEBUG_MODAL_CHART_HITZONES && !canvas._hitzoneMouseLogger) {
                canvas._hitzoneMouseLogger = true;
                canvas.addEventListener('mousemove', function(ev) {
                    const r = canvas.getBoundingClientRect();
                    const cx = ev.clientX - r.left;
                    const cy = ev.clientY - r.top;
                    const scaledX = Math.round(cx * (canvas.width / r.width));
                    const scaledY = Math.round(cy * (canvas.height / r.height));
                    try { const near = chart.getElementsAtEventForMode(ev, 'nearest', { intersect: false }); console.log('[chart-nearest]', near.map(n => ({ index: n.index, datasetIndex: n.datasetIndex }))); } catch(e){}
                });
            }

        } catch (err) {
        }
    }

    // Map image file to region and name
    const regionMap = {
        '1.png': { region: 'Region I', name: 'Ilocos Region' },
        '2.png': { region: 'Region II', name: 'Cagayan Valley' },
        '3.png': { region: 'Region III', name: 'Central Luzon' },
        '4_a.png': { region: 'Region IV-A', name: 'CALABARZON' },
        '4_b.png': { region: 'Region IV-B', name: 'MIMAROPA' },
        '5.png': { region: 'Region V', name: 'Bicol Region' },
        '6.png': { region: 'Region VI', name: 'Western Visayas' },
        '7.png': { region: 'Region VII', name: 'Central Visayas' },
        '8.png': { region: 'Region VIII', name: 'Eastern Visayas' },
        '9.png': { region: 'Region IX', name: 'Zamboanga Peninsula' },
        '10.png': { region: 'Region X', name: 'Northern Mindanao' },
        '11.png': { region: 'Region XI', name: 'Davao Region' },
        '12.png': { region: 'Region XII', name: 'SOCCSKSARGEN' },
        '13.png': { region: 'Region XIII', name: 'Caraga' },
        'barmm.png': { region: 'BARMM', name: 'Bangsamoro Autonomous Region' },
        'car.png': { region: 'CAR', name: 'Cordillera Administrative Region' },
        'ncr.png': { region: 'NCR', name: 'National Capital Region' },
        'nir.png': { region: 'NIR', name: 'Negros Island Region' },
    };

    // configuration: toggle automatic expansion when modal opens
    const AUTO_EXPAND = false; // set true = auto-open first province + city
    // modal opening flag — used to reveal the ST Titles panel only after the image finishes placing
    let modalOpening = false;
    // Save the slider-image rect when opening so the close animation returns to the exact spot
    let _lastActiveImgRect = null;

    /* pick-and-place helper (global) — clones a row, animates it to top, then calls onComplete.
       Used so the selected Province/City looks "picked up" and placed into the header/slot
       without a visible full re-render. */
    function pickAndPlace(rowEl, targetTopOffsetPx = 8, onComplete = null) {
        if (!rowEl) { if (onComplete) onComplete(); return; }
        const srcRect = rowEl.getBoundingClientRect();
        const resultsBody = document.getElementById('st-results-body');
        const tbodyRect = resultsBody ? resultsBody.getBoundingClientRect() : { top: srcRect.top };

        const clone = rowEl.cloneNode(true);
        clone.classList.add('floating-clone');
        clone.style.width = Math.max(200, srcRect.width) + 'px';
        clone.style.left = srcRect.left + 'px';
        clone.style.top = srcRect.top + 'px';
        clone.style.margin = '0';
        document.body.appendChild(clone);

        // hide original while clone animates (preserve layout)
        rowEl.style.visibility = 'hidden';

        const destY = Math.round((tbodyRect.top || srcRect.top) + targetTopOffsetPx);
        const deltaY = destY - srcRect.top;

        requestAnimationFrame(() => {
            clone.style.transform = `translateY(${deltaY}px) scale(1.02)`;
            clone.style.boxShadow = '0 14px 34px rgba(0,0,0,0.16)';
            clone.style.transition = 'transform 420ms cubic-bezier(.2,.9,.2,1), opacity 260ms ease';
            clone.style.opacity = '0.98';
            clone.style.zIndex = 14000;
        });

        const cleanup = () => {
            if (clone && clone.parentNode) clone.parentNode.removeChild(clone);
            rowEl.style.visibility = '';
            if (typeof onComplete === 'function') onComplete();
        };

        const timeoutId = setTimeout(cleanup, 520);
        clone.addEventListener('transitionend', () => { clearTimeout(timeoutId); cleanup(); }, { once: true });
    }

    // store last payload for delegated handlers
    let _lastGrouped = {};
    let _lastProvinces = [];

    // shared row-entrance animator (used by updateModalTable and delegated handlers)
    function animateRows(html) {
        const resultsBody = document.getElementById('st-results-body');
        resultsBody.innerHTML = html;
        const rows = Array.from(resultsBody.querySelectorAll('tr'));
        rows.forEach(r => r.classList.add('row-anim'));
        requestAnimationFrame(() => {
            rows.forEach((r,i) => setTimeout(() => r.classList.add('show'), i * 18));

            // if ST rows were rendered but the province header is missing, add/mark it
            const firstSt = resultsBody.querySelector('.st-row');
            const provFromSt = firstSt?.dataset?.prov;
            if (provFromSt) {
                let headerEl = resultsBody.querySelector('.prov-header-row');
                const selectedCity = (resultsBody.querySelector('.city-row.city-selected') || resultsBody.querySelector('.st-row'))?.dataset?.city;
                if (!headerEl) {
                    const ph = document.createElement('tr');
                    ph.className = 'prov-header-row row-anim prov-selected visible-header';
                    ph.dataset.prov = provFromSt;
                    const count = _lastGrouped && _lastGrouped[provFromSt] ? Object.keys(_lastGrouped[provFromSt]).length : 0;
                    ph.innerHTML = `<td><div class="prov-inner" style="font-weight:700;background:#f7f9fa;width:100%;">${provFromSt} <small style="color:#666;">(${count} cities)</small>${selectedCity ? `<span class=\"prov-selected-badge\">${selectedCity}</span>` : ''}</div></td>`;
                    // insert a 'Province' divider before the prov-header so the label is always visible in ST view
                    const divProvTop = document.createElement('tr');
                    divProvTop.className = 'section-divider row-anim';
                    divProvTop.innerHTML = '<td class="divider-label">Province</td>';
                    resultsBody.insertBefore(divProvTop, resultsBody.firstChild);
                    resultsBody.insertBefore(ph, resultsBody.firstChild);
                    try { ph.scrollIntoView({ block: 'start', behavior: 'auto' }); } catch(e) { resultsBody.scrollTop = 0; }
                } else {
                    headerEl.classList.add('prov-selected');
                    if (selectedCity) {
                        const badge = headerEl.querySelector('.prov-selected-badge');
                        if (badge) badge.textContent = selectedCity; else headerEl.querySelector('.prov-inner')?.insertAdjacentHTML('beforeend', `<span class=\"prov-selected-badge\">${selectedCity}</span>`);
                    }
                }
                // mark selected province in the province list as well
                document.querySelectorAll('.prov-row').forEach(r => r.dataset.prov === provFromSt ? r.classList.add('prov-selected') : r.classList.remove('prov-selected'));
            }
        });
    }

    function updateModalTable(region) {
        // Fetch hierarchical JSON: provinces -> cities -> ST rows
        fetch(`/demo1/ajax-region-hierarchy?region_image=${encodeURIComponent(region)}`)
            .then(response => response.json())
            .then(payload => {
                const resultsBody = document.getElementById('st-results-body');
                const selectionHeader = document.getElementById('st-selection-header');

                resultsBody.innerHTML = '<tr><td class="text-center">No data found.</td></tr>';
                selectionHeader.innerHTML = '';

                const grouped = payload.grouped || {};
                const provinces = payload.provinces || [];
                // expose to delegated click handlers
                _lastGrouped = grouped;
                _lastProvinces = provinces || [];

                // Update MOA total in the slider modal (region-filtered)
                (function updateModalMoa() {
                    const moaEl = document.getElementById('sliderMoaCardCount');
                    if (!moaEl) return;
                    const rows = Array.isArray(payload.allRows) ? payload.allRows : [];
                    const truthy = v => (typeof v === 'boolean') ? v : String(v || '').trim().toUpperCase() === 'TRUE';
                    const moaCount = rows.reduce((acc, r) => acc + (truthy(r.with_moa) ? 1 : 0), 0);
                    moaEl.textContent = moaCount;
                })();

                // Update Uploaded MOAs count (server-side computed)
                (function updateUploadedCount() {
                    const el = document.getElementById('sliderUploadedCardCount');
                    if (!el) return;
                    el.textContent = Number(payload.uploadedCount || 0);
                })();

                // Update Expression of Interest total (client-side from parsed rows)
                (function updateExprCount() {
                    const el = document.getElementById('sliderExprCardCount');
                    if (!el) return;
                    const rows = Array.isArray(payload.allRows) ? payload.allRows : [];
                    const truthy = v => (typeof v === 'boolean') ? v : String(v || '').trim().toUpperCase() === 'TRUE';
                    const count = rows.reduce((acc, r) => acc + (truthy(r.with_expr) ? 1 : 0), 0);
                    el.textContent = count;
                })();

                // Update SB Resolution total (client-side from parsed rows)
                (function updateResCount() {
                    const el = document.getElementById('sliderResCardCount');
                    if (!el) return;
                    const rows = Array.isArray(payload.allRows) ? payload.allRows : [];
                    const truthy = v => (typeof v === 'boolean') ? v : String(v || '').trim().toUpperCase() === 'TRUE';
                    const count = rows.reduce((acc, r) => acc + (truthy(r.with_res) ? 1 : 0), 0);
                    el.textContent = count;
                })();

                // Update Total STs (region-filtered)
                (function updateTotalStCount() {
                    const el = document.getElementById('sliderTotalStCardCount');
                    if (!el) return;
                    const rows = Array.isArray(payload.allRows) ? payload.allRows : [];
                    el.textContent = rows.length;
                })();

                // Update modal stats chart (Total STs shown as summary; metrics plotted as a smooth area)
                (function updateModalChart() {
                    const total = Number(document.getElementById('sliderTotalStCardCount')?.textContent || 0);
                    const moa = Number(document.getElementById('sliderMoaCardCount')?.textContent || 0);
                    const uploaded = Number(document.getElementById('sliderUploadedCardCount')?.textContent || 0);
                    const expr = Number(document.getElementById('sliderExprCardCount')?.textContent || 0);
                    const res = Number(document.getElementById('sliderResCardCount')?.textContent || 0);
                    // update total badge above the chart
                    const totalEl = document.getElementById('modalStatsTotal'); if (totalEl) totalEl.textContent = `Total STs: ${total}`;
                    // chart expects [moa, uploaded, expr, res]
                    initOrUpdateModalStatsChart([moa, uploaded, expr, res], payload.perYearTotals || null);
                })();

                // Short/top titles removed — only the full "All ST Titles (Region)" card is used now.

                // Render full "All ST Titles" listing into the larger card
                (function renderFullTitleList() {
                    const allUl = document.getElementById('sliderAllTitleListUl');
                    const allCard = document.getElementById('sliderAllTitleListCard');
                    if (!allUl || !allCard) return;
                    const rows = Array.isArray(payload.allRows) ? payload.allRows : [];
                    const titleMap = {};
                    rows.forEach(function(r) {
                        const t = (r.title || '').toString().trim();
                        if (!t) return;
                        titleMap[t] = (titleMap[t] || 0) + 1;
                    });
                    const entries = Object.entries(titleMap).sort((a,b) => b[1] - a[1]);
                    console.log('renderFullTitleList: entries=', entries.length);
                    if (!entries.length) {
                        allUl.innerHTML = '<li style="color:#64748b;font-size:0.95rem;">No titles</li>';
                        allCard.style.display = 'none';
                        return;
                    }
                    let allHtml = '';
                    entries.forEach(([title, count]) => {
                        const esc = (s) => (s || '').toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                        allHtml += `<li class=\"slider-all-title-item\" data-title=\"${esc(title)}\" style=\"padding:10px 8px;border-radius:6px;display:flex;align-items:center;gap:12px;cursor:pointer;border-bottom:1px solid #f1f5f9;\">\n                                        <div style=\"min-width:44px;font-weight:700;color:#10aeb5;\">${count}</div>\n                                        <div style=\"flex:1; font-size:0.95rem; color:#0f172a;\">${esc(title)}</div>\n                                    </li>`;
                    });
                    allUl.innerHTML = allHtml;
                    allCard.style.display = 'block';
                    // Prefer aligning the full-list card with the MOA / totals column (if present), otherwise fall back to panel-based placement.
                    // Also schedule/post-check and observe modal size so the card repositions if the modal layout changes (fixes stale placement after data reload).
                    (function _placeAllTitleCard(){
                        const modalRect = modalContent.getBoundingClientRect();
                        const moaCol = document.getElementById('sliderMoaCard');
                        const panelEl = document.getElementById('stTitlesPanel');

                        function _computeAndApply(leftPx, topPx){
                            // clamp horizontally inside modalContent and always prefer right-edge placement
                            const maxLeft = Math.max(8, Math.round((modalRect.width || window.innerWidth) - (allCard.offsetWidth || 420) - 20));
                            leftPx = Math.min(Math.max(8, Math.round(leftPx || 8)), maxLeft);

                            // apply computed coords (do NOT fallback to left-side placement)
                            allCard.style.left = leftPx + 'px';
                            allCard.style.top = Math.max(8, Math.round(topPx || 18)) + 'px';
                        }

                        if (moaCol) {
                            const moaRect = moaCol.getBoundingClientRect();
                            const gap = 12;
                            const moaLeftRel = Math.round(moaRect.left - modalRect.left);
                            const moaWidth = moaCol.offsetWidth || 220;
                            let leftPx = moaLeftRel + moaWidth + gap;
                            const topPx = Math.max(8, Math.round(moaRect.top - modalRect.top));
                            _computeAndApply(leftPx, topPx);
                        } else if (panelEl) {
                            const panelRect = panelEl.getBoundingClientRect();
                            const leftPx = Math.max(12, Math.round(modalRect.width - (allCard.offsetWidth || 420) - 40));
                            const topPx = Math.max(8, Math.round(panelRect.top - modalRect.top));
                            _computeAndApply(leftPx, topPx);
                        } else {
                            const leftPx = Math.max(12, Math.round(modalRect.width - (allCard.offsetWidth || 420) - 40));
                            _computeAndApply(leftPx, 18);
                        }

                        // schedule a short re-check after layout stabilizes (handles async DOM updates / transforms)
                        requestAnimationFrame(() => requestAnimationFrame(() => {
                            try {
                                const modalRect2 = modalContent.getBoundingClientRect();
                                const curLeft = parseInt(allCard.style.left || '0', 10) || 0;
                                const maxLeft2 = Math.max(8, Math.round((modalRect2.width || window.innerWidth) - (allCard.offsetWidth || 420) - 20));
                                if (curLeft > maxLeft2) allCard.style.left = maxLeft2 + 'px';
                            } catch(e) {}
                        }));

                        // Attach a ResizeObserver once so future modal resizes/reflows re-position the card correctly
                        try {
                            if (!modalContent.__allTitleResizeObserver) {
                                modalContent.__allTitleResizeObserver = new ResizeObserver(() => { try { _placeAllTitleCard(); } catch(e){} });
                                modalContent.__allTitleResizeObserver.observe(modalContent);
                            }
                        } catch(e) { /* ResizeObserver may not be available in some old browsers */ }
                    })();
                    // delegate click handlers — use event delegation so dynamically-updated items always work
                    if (!allUl.__delegateBound) {
                        allUl.__delegateBound = true;
                        console.log('renderFullTitleList: allUl delegation bound');

                        // capture-phase pointerdown: remember the pressed LI and schedule a short fallback show (cancels on move)
                        allUl.addEventListener('pointerdown', function(ev) {
                            const liDebug = ev.target.closest('.slider-all-title-item');
                            if (liDebug) {
                                allUl._lastPointerTarget = liDebug;
                                allUl._lastPointerTs = Date.now();
                                console.log('allUl.pointerdown ->', liDebug.dataset.title || liDebug.textContent.trim().slice(0,60));

                                // record start position for drag-detection and schedule a fast fallback show
                                allUl._pointerDownPos = { x: ev.clientX, y: ev.clientY };
                                allUl._moved = false;
                                if (allUl._showTimer) { clearTimeout(allUl._showTimer); allUl._showTimer = null; }
                                allUl._showTimer = setTimeout(() => {
                                    // if pointer didn't move and pop not already visible, force-show as a fast fallback
                                    if (allUl._moved) return;
                                    const li = allUl._lastPointerTarget;
                                    if (!li) return;
                                    const popNow = document.getElementById('stReplicatePopover');
                                    if (popNow && popNow.classList.contains('visible')) return;
                                    _forceShowReplicatePopover(li.dataset.title || li.textContent.trim().slice(0,120), li.getBoundingClientRect(), allUl._pointerDownPos?.x, allUl._pointerDownPos?.y);
                                }, 16);
                            } else {
                                allUl._lastPointerTarget = null;
                                allUl._lastPointerTs = 0;
                            }
                        }, true);

                        // cancel fallback-show if pointer moves (drag/scroll)
                        allUl.addEventListener('pointermove', function(ev) {
                            if (!allUl._pointerDownPos) return;
                            const dx = ev.clientX - allUl._pointerDownPos.x;
                            const dy = ev.clientY - allUl._pointerDownPos.y;
                            if (Math.hypot(dx, dy) > 6) {
                                allUl._moved = true;
                                if (allUl._showTimer) { clearTimeout(allUl._showTimer); allUl._showTimer = null; }
                            }
                        }, true);
                        allUl.addEventListener('pointercancel', function() {
                            if (allUl._showTimer) { clearTimeout(allUl._showTimer); allUl._showTimer = null; }
                            allUl._pointerDownPos = null;
                            allUl._moved = false;
                        }, true);

                        // pointerup (capture) — show popover on release (single-click behavior)
                        allUl.addEventListener('pointerup', function(ev) {
                            const liUp = ev.target.closest('.slider-all-title-item');
                            if (!liUp || !allUl._lastPointerTarget || liUp !== allUl._lastPointerTarget) return;
                            if ((Date.now() - (allUl._lastPointerTs || 0)) > 800) { allUl._lastPointerTarget = null; allUl._lastPointerTs = 0; return; }

                            const pop = document.getElementById('stReplicatePopover');
                            if (!pop) return;
                            const title = liUp.dataset.title || liUp.textContent.trim().slice(0,120);
                            pop.style.display = 'block';
                            pop.style.zIndex = '99999';
                            pop.classList.remove('confirmed');
                            pop.querySelector('.replicate-title').textContent = title;
                            pop.dataset.targetTitle = title;
                            pop._shownByPointerTs = Date.now();
                            console.log('stReplicatePopover: show (full-list, pointerup) ->', title);

                            // position relative to modalContent (prefer pointer coordinates so pop aligns with click)
                            const modalRect = modalContent.getBoundingClientRect();
                            const liRect = liUp.getBoundingClientRect();
                            requestAnimationFrame(() => {
                                const popW = pop.offsetWidth || 220;
                                const popH = pop.offsetHeight || 80;

                                const targetX = (ev && ev.clientX) ? (ev.clientX - modalRect.left) : (liRect.left - modalRect.left + 8);
                                const targetY = (ev && ev.clientY) ? (ev.clientY - modalRect.top) : (liRect.top - modalRect.top);

                                // align pop arrow (CSS ::after left:18px) to pointer X
                                let left = Math.round(targetX - 18);
                                left = Math.max(8, Math.min(left, (modalContent.clientWidth || modalRect.width) - popW - 12));

                                // position above pointer if possible, otherwise below
                                let top = Math.round(targetY - popH - 10);
                                if (top < 8) top = Math.round(targetY + 10);

                                pop.style.left = left + 'px';
                                pop.style.top = top + 'px';
                                requestAnimationFrame(() => pop.classList.add('visible'));
                            });

                            if (allUl._showTimer) { clearTimeout(allUl._showTimer); allUl._showTimer = null; }
                            allUl._pointerDownPos = null;
                            allUl._moved = false;
                            allUl._lastPointerTarget = null;
                            allUl._lastPointerTs = 0;
                        }, true);

                        // capture-phase click: ensure single-click shows popover even if other handlers stop propagation
                        allUl.addEventListener('click', function(ev) {
                            const li = ev.target.closest('.slider-all-title-item');
                            if (!li || !allUl.contains(li)) return;
                            const popNow = document.getElementById('stReplicatePopover');
                            if (popNow && popNow._shownByPointerTs && (Date.now() - popNow._shownByPointerTs) < 400) { popNow._shownByPointerTs = null; return; }

                            console.log('allUl.capture-click ->', li.dataset.title || li.textContent.trim().slice(0,60));
                            const pop = document.getElementById('stReplicatePopover');
                            if (!pop) return;
                            const title = li.dataset.title || li.textContent.trim().slice(0,120);
                            pop.style.display = 'block';
                            pop.style.zIndex = '99999';
                            pop.classList.remove('confirmed');
                            pop.querySelector('.replicate-title').textContent = title;
                            pop.dataset.targetTitle = title;
                            pop._shownByPointerTs = Date.now();
                            console.log('stReplicatePopover: show (full-list, capture-click) ->', title);

                            const modalRect = modalContent.getBoundingClientRect();
                            const liRect = li.getBoundingClientRect();
                            requestAnimationFrame(() => {
                                const popW = pop.offsetWidth || 220;
                                const popH = pop.offsetHeight || 80;
                                const targetX = (ev && ev.clientX) ? (ev.clientX - modalRect.left) : (liRect.left - modalRect.left + (liRect.width||0)/2);
                                const targetY = (ev && ev.clientY) ? (ev.clientY - modalRect.top) : (liRect.top - modalRect.top);

                                let left = Math.round(targetX - 18);
                                left = Math.max(8, Math.min(left, (modalContent.clientWidth || modalRect.width) - popW - 12));

                                let top = Math.round(targetY - popH - 10);
                                if (top < 8) top = Math.round(targetY + 10);

                                pop.style.left = left + 'px';
                                pop.style.top = top + 'px';
                                requestAnimationFrame(() => pop.classList.add('visible'));
                            });

                            // visual selection inside the full-list card only
                            Array.from(allUl.querySelectorAll('.slider-all-title-item')).forEach(i => i.classList.remove('active'));
                            li.classList.add('active');

                            // stop propagation so bubble-phase handlers don't duplicate work
                            ev.stopPropagation();
                        }, true);

                        // fallback/bubble-phase handler (existing) — kept for compatibility
                        allUl.addEventListener('click', function(ev) {
                            const li = ev.target.closest('.slider-all-title-item');
                            const popNow = document.getElementById('stReplicatePopover');
                            if (popNow && popNow._shownByPointerTs && (Date.now() - popNow._shownByPointerTs) < 400) { popNow._shownByPointerTs = null; return; }
                            if (!li || !allUl.contains(li)) return;

                            // visual selection inside the full-list card only
                            Array.from(allUl.querySelectorAll('.slider-all-title-item')).forEach(i => i.classList.remove('active'));
                            li.classList.add('active');

                            // show replicate confirmation popover anchored near the clicked item
                            const pop = document.getElementById('stReplicatePopover');
                            if (!pop) return;
                            pop.style.display = 'block';
                            pop.style.zIndex = '99999';
                            pop.classList.remove('confirmed');
                            pop.querySelector('.replicate-title').textContent = li.dataset.title || li.textContent.trim().slice(0,120);
                            pop.dataset.targetTitle = pop.querySelector('.replicate-title').textContent;

                            // position the popover relative to the card container and clicked li
                            const allCard = document.getElementById('sliderAllTitleListCard');
                            const cardRect = allCard.getBoundingClientRect();
                            const liRect = li.getBoundingClientRect();

                            // ensure pop is measured (rendered) before computing offsets (position relative to modalContent)
                            const modalRect = modalContent.getBoundingClientRect();
                            requestAnimationFrame(() => {
                                const popW = pop.offsetWidth || 220;
                                const popH = pop.offsetHeight || 80;
                                // left: align to the clicked LI but clamp inside modalContent
                                let left = Math.round(liRect.left - modalRect.left) + 8;
                                left = Math.max(8, Math.min(left, (modalContent.clientWidth || modalRect.width) - popW - 12));
                                // top: prefer above the li; if not enough room, place below
                                let top = Math.round(liRect.top - modalRect.top) - popH - 10;
                                if (top < 8) top = Math.round(liRect.bottom - modalRect.top) + 10;
                                pop.style.left = left + 'px';
                                pop.style.top = top + 'px';
                                requestAnimationFrame(() => pop.classList.add('visible'));
                            });

                            ev.stopPropagation();
                        });
                    }

                    // bind popover actions once
                    if (!window.__stReplicatePopoverBound) {
                        window.__stReplicatePopoverBound = true;
                        const popEl = document.getElementById('stReplicatePopover');
                        if (popEl) {
                            popEl.querySelector('.replicate-no').addEventListener('click', (e) => {
                                e.stopPropagation(); popEl.classList.remove('visible'); setTimeout(()=> popEl.style.display = 'none', 200);
                            });
                            popEl.querySelector('.replicate-yes').addEventListener('click', (e) => {
                                e.stopPropagation();
                                const title = popEl.dataset.targetTitle || '';
                                // dispatch a custom event so backend or other code can handle replication
                                window.dispatchEvent(new CustomEvent('st:replicate', { detail: { title } }));
                                popEl.classList.add('confirmed');
                                popEl.querySelector('.replicate-msg').textContent = 'Replication requested';
                                setTimeout(()=> { popEl.classList.remove('visible'); popEl.style.display = 'none'; }, 900);
                            });

                            // hide on outside click or ESC
                            document.addEventListener('click', (ev) => {
                                // if pop was just shown, ignore the immediate outside-click (prevents capture-phase hide when anchor was detached)
                                const now = Date.now();
                                if (popEl._shownByPointerTs && (now - popEl._shownByPointerTs) < 350) {
                                    console.log('stReplicatePopover: ignoring document click (recently shown)', now - popEl._shownByPointerTs);
                                    return;
                                }

                                if (!popEl.contains(ev.target) && !ev.target.closest('.slider-all-title-item') && !ev.target.closest('tr.st-row')) {
                                    popEl.classList.remove('visible'); setTimeout(()=> popEl.style.display = 'none', 200);
                                }
                            }, true);
                            document.addEventListener('keydown', (ev) => { if (ev.key === 'Escape') { popEl.classList.remove('visible'); setTimeout(()=> popEl.style.display = 'none', 120); } });
                        }
                    }
                })();

                // robust helper to position & force-show the replicate popover (used as a fast fallback)
                function _forceShowReplicatePopover(title, anchorRect) {
                    try {
                        const pop = document.getElementById('stReplicatePopover');
                        if (!pop) return;
                        pop.style.display = 'block';
                        pop.style.zIndex = '99999';
                        pop.classList.remove('confirmed');
                        const tEl = pop.querySelector('.replicate-title');
                        if (tEl) tEl.textContent = title;
                        pop.dataset.targetTitle = title;
                        pop._shownByPointerTs = Date.now();

                        // compute position relative to modalContent
                        const modalRect = modalContent.getBoundingClientRect();
                        const popW = pop.offsetWidth || 220;
                        const popH = pop.offsetHeight || 80;
                        let left = Math.round(anchorRect.left - modalRect.left) + 8;
                        left = Math.max(8, Math.min(left, (modalContent.clientWidth || modalRect.width) - popW - 12));
                        let top = Math.round(anchorRect.top - modalRect.top) - popH - 10;
                        if (top < 8) top = Math.round(anchorRect.bottom - modalRect.top) + 10;
                        pop.style.left = left + 'px';
                        pop.style.top = top + 'px';

                        // force visible immediately (bypass any transition timing issues)
                        const prevTransition = pop.style.transition || '';
                        pop.style.transition = 'none';
                        pop.classList.add('visible');
                        pop.style.opacity = '1';
                        pop.style.transform = 'none';
                        pop.style.pointerEvents = 'auto';

                        // log computed style for diagnosis
                        console.log('forceShowReplicatePopover:', { title, left, top, width: pop.offsetWidth, height: pop.offsetHeight, compOpacity: window.getComputedStyle(pop).opacity, zIndex: pop.style.zIndex });

                        // restore transition shortly after so CSS animations still apply for subsequent shows
                        requestAnimationFrame(() => {
                            pop.style.transition = prevTransition;
                            setTimeout(()=> { pop.style.opacity = ''; pop.style.transform = ''; }, 260);
                        });
                    } catch(e) { console.error('forceShowReplicatePopover error', e); }
                }

                // fallback: if pointerdown was recorded but target's pointerup/click was swallowed, ensure we show the popover on global pointerup
                document.addEventListener('pointerup', function(ev) {
                    try {
                        const pop = document.getElementById('stReplicatePopover');
                        if (pop && pop.classList.contains('visible')) return;
                        const allUl = document.getElementById('sliderAllTitleListUl');
                        if (allUl && allUl._lastPointerTarget) {
                            const li = allUl._lastPointerTarget;
                            if (li && document.body.contains(li)) { _forceShowReplicatePopover(li.dataset.title || li.textContent.trim().slice(0,120), li.getBoundingClientRect(), allUl._pointerDownPos?.x, allUl._pointerDownPos?.y); allUl._lastPointerTarget = null; allUl._lastPointerTs = 0; return; }
                        }
                        const tbody = document.getElementById('st-results-body');
                        if (tbody && tbody._lastPointerTarget) {
                            const tr = tbody._lastPointerTarget;
                            if (tr && document.body.contains(tr)) { _forceShowReplicatePopover((tr.querySelector('td')?.textContent||'').trim(), tr.getBoundingClientRect(), tbody._pointerDownPos?.x, tbody._pointerDownPos?.y); tbody._lastPointerTarget = null; tbody._lastPointerTs = 0; return; }
                        }
                    } catch(e) { /* silently continue */ }
                }, true);

                const esc = s => (s || '').toString().replace(/</g,'&lt;').replace(/>/g,'&gt;');

                // helper to animate row insertion
                function animateRows(html) {
                    resultsBody.innerHTML = html;
                    const rows = Array.from(resultsBody.querySelectorAll('tr'));
                    rows.forEach(r => r.classList.add('row-anim'));
                    requestAnimationFrame(() => {
                        rows.forEach((r,i) => setTimeout(() => r.classList.add('show'), i * 18));

                        // if ST rows are present ensure province header exists + is marked
                        const firstSt = resultsBody.querySelector('.st-row');
                        const provFromSt = firstSt?.dataset?.prov;
                        if (provFromSt) {
                            let headerEl = resultsBody.querySelector('.prov-header-row');
                            if (!headerEl) {
                                const ph = document.createElement('tr');
                                ph.className = 'prov-header-row row-anim prov-selected';
                                ph.dataset.prov = provFromSt;
                                const count = _lastGrouped && _lastGrouped[provFromSt] ? Object.keys(_lastGrouped[provFromSt]).length : 0;
                                ph.innerHTML = `<td><div class=\"prov-inner\" style=\"font-weight:700;background:#f7f9fa;width:100%;\">${provFromSt} <small style=\"color:#666;\">(${count} cities)</small></div></td>`;
                                resultsBody.insertBefore(ph, resultsBody.firstChild);
                            } else {
                                headerEl.classList.add('prov-selected');
                            }
                            document.querySelectorAll('.prov-row').forEach(r => r.dataset.prov === provFromSt ? r.classList.add('prov-selected') : r.classList.remove('prov-selected'));
                        }
                    });
                }



                // Render provinces as top-level rows
                if (!provinces.length) {
                    animateRows('<tr><td colspan="3" class="text-center">No provinces found for this region.</td></tr>');
                    return;
                }

                let html = `<tr class="section-divider"><td class="divider-label">Provinces</td></tr>`;
                provinces.forEach(prov => {
                    const cityCount = Object.keys(grouped[prov] || {}).length;
                    html += `<tr class="prov-row" data-prov="${esc(prov)}" style="cursor:pointer;background:#fbfdfe;"><td><div class=\"prov-inner\" style=\"font-weight:700;width:100%;\">${esc(prov)} <small style=\"color:#666;\">(${cityCount} cities)</small></div></td></tr>`;
                });

                // animate province rows on initial render (staggered entrance)
                animateRows(html);

                // Auto-expand first province + first city (show STs)
                // Controlled by `AUTO_EXPAND` flag (set below). When false the modal will not auto-select.
                if (typeof AUTO_EXPAND !== 'undefined' && AUTO_EXPAND) {
                    (function autoExpandFirst() {
                        const firstProv = resultsBody.querySelector('.prov-row');
                        if (!firstProv) return;
                        // simulate user click on the first province row
                        firstProv.click();
                        // wait briefly for province handler to render cities, then open first city
                        setTimeout(() => {
                            const firstCity = resultsBody.querySelector('.city-row');
                            if (firstCity) {
                                firstCity.click();
                                const sel = document.getElementById('st-selection-header');
                                if (sel) sel.classList.add('visible');
                            }
                        }, 260);
                    })();
                }

                // When province row clicked: make province the header and list its cities below
                document.querySelectorAll('.prov-row').forEach(pRow => {
                    pRow.addEventListener('click', function () { return; /* disabled: delegated handler used instead */
                        const prov = this.dataset.prov;

                        // mark handled to prevent delegated duplicate handling
                        this.__handled = true; setTimeout(()=> this.__handled = false, 900);

                        // visually fade out other provinces while keeping this one prominent
                        document.querySelectorAll('.prov-row').forEach(r => {
                            if (r.dataset.prov !== prov) r.classList.add('faded');
                            else r.classList.remove('faded');
                        });

                        // animate the clicked province row moving up (simpler and more reliable than cloning)
                        this.classList.add('move-up');
                        // remove the move-up class once the animation completes (cleanup happens after rows render)

                        // header: Province (clear city)
                        selectionHeader.innerHTML = `
                            <div style=\"font-weight:800; font-size:1rem; margin-bottom:4px;\">${esc(prov)}</div>
                            <div id=\"st-selection-city\" style=\"color:#666; font-size:0.95rem; margin-bottom:6px;\">(select a city)</div>
                        `;
                        selectionHeader.classList.add('visible');

                        // Render province header + city rows in table body
                        const cities = Object.keys(grouped[prov] || {});
                        if (!cities.length) {
                            animateRows('<tr><td class="text-center">No cities found for this province.</td></tr>');
                            return;
                        }

                        let bodyHtml = `<tr class=\"prov-header-row\" data-prov=\"${esc(prov)}\"><td><div class=\"prov-inner\" style=\"font-weight:700;background:#f7f9fa;width:100%;\">${esc(prov)} <small style=\"color:#666;\">(${cities.length} cities)</small></div></td></tr>`;
                        cities.forEach(city => {
                            const stCount = (grouped[prov][city] || []).length;
                            bodyHtml += `<tr class="city-row" data-prov="${esc(prov)}" data-city="${esc(city)}" style="cursor:pointer;"><td style="padding-left:18px;"> ${esc(city)} (${stCount})</td></tr>`;
                        });

                        // animate the clicked province as a "pick-and-place", then update the DOM
                        const theRow = this;
                        pickAndPlace(theRow, 6, () => {
                            // remove all other rows and reuse the clicked row as the prov-header-row
                            Array.from(resultsBody.querySelectorAll('tr')).forEach(r => { if (r !== theRow) r.remove(); });

                            // convert the clicked row into a prov-header-row (keeps DOM continuity)
                            theRow.className = 'prov-header-row row-anim prov-selected visible-header'; // already marked selected (kept for clarity) 
                            theRow.dataset.prov = prov;
                            theRow.innerHTML = `<td><div class=\"prov-inner\" style=\"font-weight:700;background:#f7f9fa;width:100%;\">${esc(prov)} <small style=\"color:#666;\">(${cities.length} cities)</small></div></td>`;

                            // insert city rows after the header
                            cities.forEach((city) => {
                                const tr = document.createElement('tr');
                                tr.className = 'city-row row-anim';
                                tr.dataset.prov = prov;
                                tr.dataset.city = city;
                                const stCount = (grouped[prov][city] || []).length;
                                tr.style.cursor = 'pointer';
                                tr.innerHTML = `<td style="padding-left:18px;"> ${esc(city)} (${stCount})</td>`;
                                resultsBody.appendChild(tr);
                            });

                            // staggered entrance for the newly-inserted rows
                            requestAnimationFrame(() => Array.from(resultsBody.querySelectorAll('.row-anim')).forEach((r,i) => setTimeout(() => r.classList.add('show'), i * 18)));
                        });

                        // highlight selected province row visually in list of provinces (if present)
                        document.querySelectorAll('.prov-row').forEach(r => { r.classList.remove('table-active'); r.classList.remove('prov-selected'); });
                        this.classList.add('prov-selected');

                        // ensure move-up class is removed after animation completes (cleanup for legacy code paths)
                        setTimeout(() => document.querySelectorAll('.prov-row.move-up').forEach(r => r.classList.remove('move-up')), 520);

                        // City click: update header to show city and reveal ST rows
                        document.querySelectorAll('.city-row').forEach(cRow => {
                            cRow.addEventListener('click', function () { return; /* disabled: delegated handler used instead */
                                const city = this.dataset.city;
                                const prov = this.dataset.prov;

                                // mark handled to avoid delegated duplicate
                                this.__handled = true; setTimeout(()=> this.__handled = false, 900);

                                // animate the clicked city row moving up (no DOM clone required)
                                this.classList.add('move-up');

                                // fade out sibling city rows to focus the selected one
                                document.querySelectorAll('.city-row').forEach(r => {
                                    if (r.dataset.city !== city) r.classList.add('faded');
                                    else r.classList.remove('faded');
                                });

                                // update selection header: Province then City
                                selectionHeader.innerHTML = `
                                    <div style=\"font-weight:800; font-size:1rem; margin-bottom:2px;\">${esc(prov)}</div>
                                    <div style=\"font-weight:700; font-size:0.95rem; color:#0d6efd; margin-bottom:6px;\">${esc(city)}</div>
                                `;
                                selectionHeader.classList.add('visible');

                                // IMMEDIATE: ensure prov-header-row exists in the table *synchronously* so header is visible while STs animate in
                                (function ensureImmediateHeader() {
                                    if (!resultsBody.querySelector('.prov-header-row')) {
                                        const provHeaderHtml = `<tr class=\"prov-header-row row-anim prov-selected visible-header\" data-prov=\"${esc(prov)}\"><td><div class=\"prov-inner\" style=\"font-weight:700;background:#f7f9fa;width:100%;\">${esc(prov)} <small style=\"color:#666;\">(${Object.keys(grouped[prov] || {}).length} cities)</small><span class=\"prov-selected-badge\">${esc(city)}</span></div></td></tr>`;
                                        resultsBody.insertAdjacentHTML('afterbegin', provHeaderHtml);
                                    } else {
                                        const hdr = resultsBody.querySelector('.prov-header-row');
                                        hdr.classList.add('prov-selected','visible-header');
                                        const badge = hdr.querySelector('.prov-selected-badge');
                                        if (badge) badge.textContent = esc(city); else hdr.querySelector('.prov-inner')?.insertAdjacentHTML('beforeend', `<span class=\"prov-selected-badge\">${esc(city)}</span>`);
                                    }
                                    // also mark province in the provinces list immediately
                                    document.querySelectorAll('.prov-row').forEach(r => r.dataset.prov === prov ? r.classList.add('prov-selected') : r.classList.remove('prov-selected'));
                                })();

                                // replace rows after the province header with ST rows for this city
                                const stRows = grouped[prov][city] || [];
                                const stHtml = stRows.length ? stRows.map(r => `\n                                    <tr class=\\"st-row row-anim\\" data-prov=\\"${esc(prov)}\\" data-city=\\"${esc(city)}\\">\n                                        <td>${esc(r.title)}</td>\n                                    </tr>\n                                `).join('') : '<tr><td class="text-center">No STs for this city.</td></tr>';

                                // Keep the province header row at top, keep the city rows, and insert ST rows after the selected city
                                const provHeaderHtml = `<tr class=\\"prov-header-row row-anim prov-selected\\" data-prov=\\"${esc(prov)}\\"><td><div class=\\"prov-inner\\" style=\\"font-weight:700;background:#f7f9fa;width:100%;\\">${esc(prov)} <small style=\\"color:#666;\\">(${Object.keys(grouped[prov] || {}).length} cities)</small></div></td></tr>`;

                                // build province + city rows, and insert ST rows under the clicked city
                                const citiesAll = Object.keys(grouped[prov] || {});
                                let combinedHtml = provHeaderHtml;
                                citiesAll.forEach(cn => {
                                    const count = (grouped[prov][cn] || []).length;
                                    if (cn === city) {
                                        combinedHtml += `<tr class=\\"city-row city-selected row-anim\\" data-prov=\\"${esc(prov)}\\" data-city=\\"${esc(cn)}\\"><td style=\\"padding-left:18px;\\"> ${esc(cn)} (${count})</td></tr>`;
                                        combinedHtml += stHtml; // insert ST rows immediately after the selected city
                                    } else {
                                        combinedHtml += `<tr class=\\"city-row row-anim\\" data-prov=\\"${esc(prov)}\\" data-city=\\"${esc(cn)}\\"><td style=\\"padding-left:18px;\\"> ${esc(cn)} (${count})</td></tr>`;
                                    }
                                });

                                // allow the fade on siblings to complete, then animate the new rows into view
                                setTimeout(() => {
                                    // animate placement of the clicked city, then rebuild rows in-place (no full refresh visible)
                                    pickAndPlace(this, 36, () => {
                                        Array.from(resultsBody.querySelectorAll('tr')).forEach(r => r.remove());
                                        const provHeaderRow = document.createElement('tr');
                                        provHeaderRow.className = 'prov-header-row row-anim prov-selected visible-header';
                                        provHeaderRow.dataset.prov = prov;
                                        provHeaderRow.title = `Selected: ${esc(city)}`;
                                        provHeaderRow.innerHTML = `<td><div class=\"prov-inner\" style=\"font-weight:700;background:#f7f9fa;width:100%;\">${esc(prov)} <small style=\"color:#666;\">(${Object.keys(grouped[prov] || {}).length} cities)</small><span class=\"prov-selected-badge\">${esc(city)}</span></div></td>`;
                                        // insert a 'Province' divider before the prov-header so the label is always visible in ST view
                                        const divProvBefore = document.createElement('tr');
                                        divProvBefore.className = 'section-divider row-anim';
                                        divProvBefore.innerHTML = '<td class="divider-label">Province</td>';
                                        resultsBody.appendChild(divProvBefore);
                                        resultsBody.appendChild(provHeaderRow);
                                        try { provHeaderRow.scrollIntoView({ block: 'start', behavior: 'auto' }); } catch(e) { resultsBody.scrollTop = 0; }

                                        // re-create all city rows, mark the clicked one selected and insert its STs
                                        const citiesAll = Object.keys(grouped[prov] || {});
                                        citiesAll.forEach(cn => {
                                            const tr = document.createElement('tr');
                                            tr.className = (cn === city) ? 'city-row city-selected row-anim' : 'city-row row-anim';
                                            tr.dataset.prov = prov;
                                            tr.dataset.city = cn;
                                            tr.innerHTML = `<td style="padding-left:18px;"> ${esc(cn)} (${(grouped[prov][cn] || []).length})</td>`;
                                            resultsBody.appendChild(tr);
                                            if (cn === city) {
                                                const stRows = grouped[prov][cn] || [];
                                                if (stRows.length) {
                                                    stRows.forEach(r => {
                                                        const str = document.createElement('tr');
                                                        str.className = 'st-row row-anim';
                                                        str.dataset.prov = prov;
                                                        str.dataset.city = cn;
                                                        str.innerHTML = `<td>${esc(r.title)}</td>`;
                                                        resultsBody.appendChild(str);
                                                    });
                                                } else {
                                                    const empty = document.createElement('tr');
                                                    empty.innerHTML = '<td class="text-center">No STs for this city.</td>';
                                                    resultsBody.appendChild(empty);
                                                }
                                            }
                                        });

                                        requestAnimationFrame(() => Array.from(resultsBody.querySelectorAll('.row-anim')).forEach((r,i) => setTimeout(() => r.classList.add('show'), i * 18)));
                                    });
                                    // cleanup move-up class from city rows after animation
                                    setTimeout(() => document.querySelectorAll('.city-row.move-up').forEach(r => r.classList.remove('move-up')), 520);

                                    // re-attach city click handlers so user can switch cities without reloading the province
                                    document.querySelectorAll('.city-row').forEach(cRowInner => {
                                        cRowInner.addEventListener('click', function () { return; /* disabled: delegated handler used instead */
                                            // delegate to the same behavior used earlier: update header and show STs for clicked city
                                            const cityInner = this.dataset.city;
                                            const provInner = this.dataset.prov;

                                            // fade siblings
                                            document.querySelectorAll('.city-row').forEach(r => r.dataset.city !== cityInner ? r.classList.add('faded') : r.classList.remove('faded'));

                                            setTimeout(() => {
                                                selectionHeader.innerHTML = `\n                                                    <div style=\\"font-weight:800; font-size:1rem; margin-bottom:2px;\\">${esc(provInner)}</div>\n                                                    <div style=\\"font-weight:700; font-size:0.95rem; color:#0d6efd; margin-bottom:6px;\\">${esc(cityInner)}</div>\n                                                `;

                                                const stRowsInner = grouped[provInner][cityInner] || [];
                                                const stHtmlInner = stRowsInner.length ? stRowsInner.map(r => `\\n                                                    <tr class=\\\"st-row row-anim\\\" data-prov=\\\"${esc(provInner)}\\\" data-city=\\\"${esc(cityInner)}\\\">\\n                                                        <td>${esc(r.title)}</td>\\n                                                    </tr>\\n                                                `).join('') : '<tr><td class="text-center">No STs for this city.</td></tr>';

                                                // rebuild tbody in-place for the selected city (no full refresh)
                                                Array.from(resultsBody.querySelectorAll('tr')).forEach(r => r.remove());
                                                const ph = document.createElement('tr');
                                                ph.className = 'prov-header-row row-anim prov-selected';
                                                ph.dataset.prov = provInner;
                                                ph.innerHTML = `<td><div class="prov-inner" style="font-weight:700;background:#f7f9fa;width:100%;">${esc(provInner)} <small style="color:#666;">(${Object.keys(grouped[provInner] || {}).length} cities)</small></div></td>`;
                                                resultsBody.appendChild(ph);

                                                const stRowsInnerArr = grouped[provInner][cityInner] || [];
                                                // insert STs divider before appending ST rows
                                                const divRow = document.createElement('tr');
                                                divRow.className = 'section-divider row-anim';
                                                divRow.innerHTML = '<td class="divider-label">STs</td>';
                                                resultsBody.appendChild(divRow);

                                                if (stRowsInnerArr.length) {
                                                    stRowsInnerArr.forEach(rr => {
                                                        const tr = document.createElement('tr');
                                                        tr.className = 'st-row row-anim';
                                                        tr.dataset.prov = provInner;
                                                        tr.dataset.city = cityInner;
                                                        tr.innerHTML = `<td>${esc(rr.title)}</td>`;
                                                        resultsBody.appendChild(tr);
                                                    });
                                                } else {
                                                    const empty = document.createElement('tr');
                                                    empty.innerHTML = '<td class="text-center">No STs for this city.</td>';
                                                    resultsBody.appendChild(empty);
                                                }

                                                requestAnimationFrame(() => Array.from(resultsBody.querySelectorAll('.row-anim')).forEach((r,i) => setTimeout(() => r.classList.add('show'), i * 18)));
                                            }, 220);
                                        });
                                    });

                                    // subtle header flash
                                    const headerEl = document.querySelector('.prov-header-row');
                                    if (headerEl) {
                                        headerEl.style.boxShadow = 'inset 0 -3px 0 rgba(13,110,253,0.08)';
                                        setTimeout(()=> headerEl.style.boxShadow = '', 800);
                                    }
                                }, 220);
                            });
                        });
                    });
                });

            })
.catch(err => {
            });
    }

    // Delegated click handler — ensures clicks work on dynamically-rendered rows
    (function attachDelegatedHandlers() {
        const tbody = document.getElementById('st-results-body');
        if (!tbody) return;
        console.log('attachDelegatedHandlers: bound to st-results-body');

        // capture-phase pointerdown to detect interactions prior to any stopPropagation (schedules short fallback if needed)
        tbody.addEventListener('pointerdown', function(ev) {
            const tr = ev.target.closest('tr');
            if (tr) console.log('st-results-body.pointerdown ->', tr.className, tr.dataset);

            const stRow = ev.target.closest('tr.st-row');
            if (stRow) {
                tbody._lastPointerTarget = stRow;
                tbody._lastPointerTs = Date.now();

                tbody._pointerDownPos = { x: ev.clientX, y: ev.clientY };
                tbody._moved = false;
                if (tbody._showTimer) { clearTimeout(tbody._showTimer); tbody._showTimer = null; }
                tbody._showTimer = setTimeout(() => {
                    if (tbody._moved) return;
                    const r = tbody._lastPointerTarget;
                    if (!r) return;
                    const popNow = document.getElementById('stReplicatePopover');
                    if (popNow && popNow.classList.contains('visible')) return;
                    _forceShowReplicatePopover((r.querySelector('td')?.textContent||'').trim(), r.getBoundingClientRect());
                }, 16);
            } else {
                tbody._lastPointerTarget = null;
                tbody._lastPointerTs = 0;
            }
        }, true);
        tbody.addEventListener('pointermove', function(ev) {
            if (!tbody._pointerDownPos) return;
            const dx = ev.clientX - tbody._pointerDownPos.x;
            const dy = ev.clientY - tbody._pointerDownPos.y;
            if (Math.hypot(dx, dy) > 6) {
                tbody._moved = true;
                if (tbody._showTimer) { clearTimeout(tbody._showTimer); tbody._showTimer = null; }
            }
        }, true);
        tbody.addEventListener('pointercancel', function() {
            if (tbody._showTimer) { clearTimeout(tbody._showTimer); tbody._showTimer = null; }
            tbody._pointerDownPos = null;
            tbody._moved = false;
        }, true);

        // capture-phase click fallback: ensure single-click shows popover for ST rows even if other handlers stop propagation
        tbody.addEventListener('click', function(ev) {
            const row = ev.target.closest('tr.st-row');
            if (!row) return;
            const popNow = document.getElementById('stReplicatePopover');
            if (popNow && popNow._shownByPointerTs && (Date.now() - popNow._shownByPointerTs) < 400) { popNow._shownByPointerTs = null; return; }

            console.log('st-results-body.capture-click ->', row.dataset);
            const pop = document.getElementById('stReplicatePopover');
            if (!pop) return;
            const title = (row.querySelector('td')?.textContent || '').trim();
            pop.style.display = 'block';
            pop.style.zIndex = '99999';
            pop.classList.remove('confirmed');
            pop.querySelector('.replicate-title').textContent = title;
            pop.dataset.targetTitle = title;
            pop._shownByPointerTs = Date.now();
            console.log('stReplicatePopover: show (st-panel, capture-click) ->', title);

            const modalRect = modalContent.getBoundingClientRect();
            const rowRect = row.getBoundingClientRect();
            requestAnimationFrame(() => {
                const popW = pop.offsetWidth || 220;
                const popH = pop.offsetHeight || 80;
                const targetX = (ev && ev.clientX) ? (ev.clientX - modalRect.left) : (rowRect.left - modalRect.left + (rowRect.width||0)/2);
                const targetY = (ev && ev.clientY) ? (ev.clientY - modalRect.top) : (rowRect.top - modalRect.top);

                let left = Math.round(targetX - 18);
                left = Math.max(8, Math.min(left, (modalContent.clientWidth || modalRect.width) - popW - 12));

                let top = Math.round(targetX ? targetY - popH - 10 : rowRect.top - modalRect.top - popH - 10);
                if (top < 8) top = Math.round(targetY + 10);

                pop.style.left = left + 'px';
                pop.style.top = top + 'px';
                requestAnimationFrame(() => pop.classList.add('visible'));
            });

            ev.stopPropagation();
        }, true);

        // show popover on pointerup (capture) so single-click works even if click is swallowed by other handlers
        tbody.addEventListener('pointerup', function(ev) {
            const trUp = ev.target.closest('tr.st-row');
            if (!trUp || !tbody._lastPointerTarget || trUp !== tbody._lastPointerTarget) return;
            if ((Date.now() - (tbody._lastPointerTs || 0)) > 800) { tbody._lastPointerTarget = null; tbody._lastPointerTs = 0; return; }

            const pop = document.getElementById('stReplicatePopover');
            if (!pop) return;
            const title = (trUp.querySelector('td')?.textContent || '').trim();
            pop.style.display = 'block';
            pop.style.zIndex = '99999';
            pop.classList.remove('confirmed');
            pop.querySelector('.replicate-title').textContent = title;
            pop.dataset.targetTitle = title;
            pop._shownByPointerTs = Date.now();
            console.log('stReplicatePopover: show (st-panel, pointerup) ->', title);

            const modalRect = modalContent.getBoundingClientRect();
            const rowRect = trUp.getBoundingClientRect();
            requestAnimationFrame(() => {
                const popW = pop.offsetWidth || 220;
                const popH = pop.offsetHeight || 80;
                let left = Math.round(rowRect.left - modalRect.left) + 8;
                left = Math.max(8, Math.min(left, (modalContent.clientWidth || modalRect.width) - popW - 12));
                let top = Math.round(rowRect.top - modalRect.top) - popH - 10;
                if (top < 8) top = Math.round(rowRect.bottom - modalRect.top) + 10;
                pop.style.left = left + 'px';
                pop.style.top = top + 'px';
                requestAnimationFrame(() => pop.classList.add('visible'));
            });

            if (tbody._showTimer) { clearTimeout(tbody._showTimer); tbody._showTimer = null; }
            tbody._pointerDownPos = null;
            tbody._moved = false;
            tbody._lastPointerTarget = null;
            tbody._lastPointerTs = 0;
        }, true);

        tbody.addEventListener('click', function (ev) {
            console.log('st-results-body click ->', ev.target && ev.target.tagName, ev.target && ev.target.className);
            const resultsBody = tbody; // ensure delegated handler has access to the table body reference

            // If user clicks the province header row, go back to the provinces list (animated)
            const provHeaderClick = ev.target.closest('tr.prov-header-row');
            if (provHeaderClick) {
                const provinces = _lastProvinces || [];
                const grouped = _lastGrouped || {};
                if (!provinces.length) return;
                const esc = s => (s || '').toString().replace(/</g,'&lt;').replace(/>/g,'&gt;');
                pickAndPlace(provHeaderClick, 6, () => {
                    let html = '';
                    provinces.forEach(pr => {
                        const cityCount = Object.keys(grouped[pr] || {}).length;
                        html += `<tr class="prov-row" data-prov="${esc(pr)}" style="cursor:pointer;background:#fbfdfe;"><td><div class=\"prov-inner\" style=\"font-weight:700;width:100%;\">${esc(pr)} <small style=\"color:#666;\">(${cityCount} cities)</small></div></td></tr>`;
                    });
                    animateRows(html);
                });
                return;
            }

            const provRow = ev.target.closest('tr.prov-row');
            if (provRow) {
                if (provRow.__handled) return;
                const prov = provRow.dataset.prov;
                // if this province is already shown as header, treat click as no-op (prevents toggle-back)
                const currentProvShown = (document.querySelector('.prov-header-row') || document.querySelector('.prov-row.prov-selected'))?.dataset?.prov;
                if (currentProvShown && currentProvShown === prov) return;
                // simulate the province-click behaviour (fade siblings → show cities)
                document.querySelectorAll('.prov-row').forEach(r => { r.classList.remove('prov-selected'); if (r.dataset.prov !== prov) r.classList.add('faded'); else r.classList.remove('faded'); });
                // mark this row as selected in the provinces list
                provRow.classList.add('prov-selected');
                // render province header + cities from cached payload
                const grouped = _lastGrouped || {};
                const esc = s => (s || '').toString().replace(/</g,'&lt;').replace(/>/g,'&gt;');
                const cities = Object.keys(grouped[prov] || {});
                if (!cities.length) {
                    animateRows('<tr><td class="text-center">No cities found for this province.</td></tr>');
                    return;
                }
                let bodyHtml = `<tr class=\"section-divider\"><td class=\"divider-label\">Province</td></tr><tr class=\"prov-header-row row-anim prov-selected\" data-prov=\"${esc(prov)}\"><td><div class=\"prov-inner\" style=\"font-weight:700;background:#f7f9fa;width:100%;\">${esc(prov)} <small style=\"color:#666;\">(${cities.length} cities)</small></div></td></tr><tr class=\"section-divider\"><td class=\"divider-label\">Cities</td></tr>`;
                cities.forEach(city => {
                    const stCount = (grouped[prov][city] || []).length;
                    bodyHtml += `<tr class=\"city-row row-anim\" data-prov=\"${esc(prov)}\" data-city=\"${esc(city)}\" style=\"cursor:pointer;\"><td style=\"padding-left:18px;\"> ${esc(city)} (${stCount})</td></tr>`;
                });
                // ensure selection header displays the province when handled by delegated listener
                const selectionHeader = document.getElementById('st-selection-header');
                if (selectionHeader) {
                    selectionHeader.innerHTML = `<div style="font-weight:800; font-size:1rem; margin-bottom:4px;">${esc(prov)}</div><div id="st-selection-city" style="color:#666; font-size:0.95rem; margin-bottom:6px;">(select a city)</div>`;
                    selectionHeader.classList.add('visible');
                }
                // animate pick-and-place for delegated province click, then render cities without a full refresh
                pickAndPlace(provRow, 6, () => {
                    // clear tbody and reuse the clicked provRow DOM if present; otherwise create prov-header
                    Array.from(resultsBody.querySelectorAll('tr')).forEach(r => r.remove());

                    const divProvince = document.createElement('tr');
                    divProvince.className = 'section-divider row-anim';
                    divProvince.innerHTML = '<td class="divider-label">Province</td>';
                    resultsBody.appendChild(divProvince);

                    const provHeaderRow = document.createElement('tr');
                    provHeaderRow.className = 'prov-header-row row-anim prov-selected';
                    provHeaderRow.dataset.prov = prov;
                    provHeaderRow.innerHTML = `<td><div class=\"prov-inner\" style=\"font-weight:700;background:#f7f9fa;width:100%;\">${esc(prov)} <small style=\"color:#666;\">(${cities.length} cities)</small></div></td>`;
                    resultsBody.appendChild(provHeaderRow);
                    try { provHeaderRow.scrollIntoView({ block: 'start', behavior: 'auto' }); } catch(e) { resultsBody.scrollTop = 0; }

                    // insert Cities divider so it appears immediately after selecting a province
                    const divCitiesInit = document.createElement('tr');
                    divCitiesInit.className = 'section-divider row-anim';
                    divCitiesInit.innerHTML = '<td class="divider-label">Cities</td>';
                    resultsBody.appendChild(divCitiesInit);

                    cities.forEach(city => {
                        const tr = document.createElement('tr');
                        tr.className = 'city-row row-anim';
                        tr.dataset.prov = prov;
                        tr.dataset.city = city;
                        const stCount = (grouped[prov][city] || []).length;
                        tr.style.cursor = 'pointer';
                        tr.innerHTML = `<td style="padding-left:18px;"> ${esc(city)} (${stCount})</td>`;
                        resultsBody.appendChild(tr);
                    });

                    requestAnimationFrame(() => Array.from(resultsBody.querySelectorAll('.row-anim')).forEach((r,i) => setTimeout(() => r.classList.add('show'), i * 18)));
                });
                return;
            }

            const cityRow = ev.target.closest('tr.city-row');
            if (cityRow) {
                const city = cityRow.dataset.city;
                const prov = cityRow.dataset.prov;
                const grouped = _lastGrouped || {};
                const esc = s => (s || '').toString().replace(/</g,'&lt;').replace(/>/g,'&gt;');

                

                // if the clicked city is already selected, collapse STs and show city list instead
                const curSelected = document.querySelector('.city-row.city-selected');
                if (curSelected && curSelected.dataset.city === city) {
                    // collapse to city list (animated)
                    pickAndPlace(cityRow, 36, () => {
                        const groupedLocal = _lastGrouped || {};
                        const cities = Object.keys(groupedLocal[prov] || {});
                        Array.from(resultsBody.querySelectorAll('tr')).forEach(r => r.remove());
                        const provHeaderRow = document.createElement('tr');
                        provHeaderRow.className = 'prov-header-row row-anim prov-selected';
                        provHeaderRow.dataset.prov = prov;
                        provHeaderRow.innerHTML = `<td><div class=\"prov-inner\" style=\"font-weight:700;background:#f7f9fa;width:100%;\">${esc(prov)} <small style=\"color:#666;\">(${cities.length} cities)</small></div></td>`;
                        resultsBody.appendChild(provHeaderRow);
                        // insert Cities divider when rebuilding the city list
                        const divCities = document.createElement('tr');
                        divCities.className = 'section-divider row-anim';
                        divCities.innerHTML = '<td class="divider-label">Cities</td>';
                        resultsBody.appendChild(divCities);

                        cities.forEach(cn => {
                            const tr = document.createElement('tr');
                            tr.className = 'city-row row-anim';
                            tr.dataset.prov = prov;
                            tr.dataset.city = cn;
                            const cnt = (groupedLocal[prov][cn] || []).length;
                            tr.innerHTML = `<td style="padding-left:18px;"> ${esc(cn)} (${cnt})</td>`;
                            resultsBody.appendChild(tr);
                        });
                        requestAnimationFrame(() => Array.from(resultsBody.querySelectorAll('.row-anim')).forEach((r,i) => setTimeout(() => r.classList.add('show'), i * 18)));
                    });
                    return;
                }

                // allow delegated handler to always handle city-clicks (protect with brief __handled flag)
                cityRow.__handled = true; setTimeout(() => cityRow.__handled = false, 800);

                // fade siblings immediately for feedback
                document.querySelectorAll('.city-row').forEach(r => r.dataset.city !== city ? r.classList.add('faded') : r.classList.remove('faded'));

                setTimeout(() => {
                    // build ST rows for the selected city and keep the province header
                    const stRows = grouped[prov][city] || [];
                    const stHtml = stRows.length ? stRows.map(r => `\n                        <tr class=\\"st-row row-anim\\" data-prov=\\"${esc(prov)}\\" data-city=\\"${esc(city)}\\">\n                            <td>${esc(r.title)}</td>\n                        </tr>\n                    `).join('') : '<tr><td class="text-center">No STs for this city.</td></tr>';
                    const provHeaderHtml = `<tr class=\"prov-header-row row-anim prov-selected\" data-prov=\"${esc(prov)}\"><td><div class=\"prov-inner\" style=\"font-weight:700;background:#f7f9fa;width:100%;\">${esc(prov)} <small style=\"color:#666;\">(${Object.keys(grouped[prov] || {}).length} cities)</small></div></td></tr>`;
                    // ensure selection header shows Province + City when handled by delegated handler
                    const selHead = document.getElementById('st-selection-header');
                    if (selHead) { selHead.innerHTML = `<div style=\"font-weight:800; font-size:1rem; margin-bottom:2px;\">${esc(prov)}</div><div style=\"font-weight:700; font-size:0.95rem; color:#0d6efd; margin-bottom:6px;\">${esc(city)}</div>`; selHead.classList.add('visible'); }

                // also mark the corresponding province in the provinces list as selected (shows checkmark there)
                document.querySelectorAll('.prov-row').forEach(r => r.dataset.prov === prov ? r.classList.add('prov-selected') : r.classList.remove('prov-selected'));

                // If there's an in-body prov-row for the same province, convert it to a prov-header-row so header is visible in ST view
                (function ensureInBodyProvHeader() {
                    const inBodyProv = resultsBody.querySelector(`tr.prov-row[data-prov="${prov}"]`);
                    if (inBodyProv) {
                        // replace it with a prov-header-row element (preserve innerHTML)
                        const hdr = document.createElement('tr');
                        hdr.className = 'prov-header-row row-anim prov-selected';
                        hdr.dataset.prov = prov;
                        hdr.innerHTML = inBodyProv.innerHTML;
                        inBodyProv.replaceWith(hdr);
                    }
                })();

                    // keep province header, show selected city row (row 2), then ST rows — animate with pick-and-place
                    pickAndPlace(cityRow, 36, () => {
                        // clear tbody and re-create: prov-header, selected city, then ST rows
                        Array.from(resultsBody.querySelectorAll('tr')).forEach(r => r.remove());

                        const provHeaderRow = document.createElement('tr');
                                            provHeaderRow.className = 'prov-header-row row-anim prov-selected';
                        provHeaderRow.innerHTML = `<td><div class=\"prov-inner\" style=\"font-weight:700;background:#f7f9fa;width:100%;\">${esc(prov)} <small style=\"color:#666;\">(${Object.keys(grouped[prov] || {}).length} cities)</small></div></td>`;
// insert a 'Province' divider before the prov-header so the label is always visible in ST view
                    const divProvInit = document.createElement('tr');
                    divProvInit.className = 'section-divider row-anim';
                    divProvInit.innerHTML = '<td class="divider-label">Province</td>';
                    resultsBody.appendChild(divProvInit);
                    resultsBody.appendChild(provHeaderRow);
                        // ensure the province header is visible when STs are shown
                        try { provHeaderRow.scrollIntoView({ block: 'start', behavior: 'auto' }); } catch(e) { resultsBody.scrollTop = 0; }

                        // insert Cities divider above the selected city
                        const divCities = document.createElement('tr');
                        divCities.className = 'section-divider row-anim';
                        divCities.innerHTML = '<td class="divider-label">Cities</td>';
                        resultsBody.appendChild(divCities);

                        const selCity = document.createElement('tr');
                        selCity.className = 'city-row city-selected row-anim';
                        selCity.dataset.prov = prov;
                        selCity.dataset.city = city;
                        selCity.innerHTML = `<td style="padding-left:18px;"> ${esc(city)} (${(grouped[prov][city]||[]).length})</td>`;
                        resultsBody.appendChild(selCity);

                        if (stHtml) {
                            // insert STs divider before the ST rows
                            const divSt = document.createElement('tr');
                            divSt.className = 'section-divider row-anim';
                            divSt.innerHTML = '<td class="divider-label">STs</td>';
                            resultsBody.appendChild(divSt);
                            // append parsed ST rows from stHtml (build DOM from grouped data to avoid innerHTML parsing)
                            const stRows = grouped[prov][city] || [];
                            
                            if (stRows.length) {
                                stRows.forEach(r => {
                                    const tr = document.createElement('tr');
                                    tr.className = 'st-row row-anim';
                                    tr.dataset.prov = prov;
                                    tr.dataset.city = city;
                                    tr.innerHTML = `<td>${esc(r.title)}</td>`;
                                    resultsBody.appendChild(tr);
                                });
                            } else {
                                const empty = document.createElement('tr');
                                empty.innerHTML = '<td class="text-center">No STs for this city.</td>';
                                resultsBody.appendChild(empty);
                            }
                        }

                        // staggered entrance
                        requestAnimationFrame(() => Array.from(resultsBody.querySelectorAll('.row-anim')).forEach((r,i) => setTimeout(() => r.classList.add('show'), i * 18)));
                    });
                }, 220);
            }

            // Clicking an ST row in the Titles panel should show the same replicate popover
            const stRow = ev.target.closest('tr.st-row');
            if (stRow) {
                const popNow = document.getElementById('stReplicatePopover');
                if (popNow && popNow._shownByPointerTs && (Date.now() - popNow._shownByPointerTs) < 400) { popNow._shownByPointerTs = null; ev.stopPropagation(); return; }
                const pop = document.getElementById('stReplicatePopover');
                if (!pop) return;
                pop.style.display = 'block';
                pop.style.zIndex = '99999';
                pop.classList.remove('confirmed');
                const title = (stRow.querySelector('td')?.textContent || '').trim();
                pop.querySelector('.replicate-title').textContent = title;
                pop.dataset.targetTitle = title; console.log('stReplicatePopover: show (st-panel) ->', title);

                const modalRect = modalContent.getBoundingClientRect();
                const rowRect = stRow.getBoundingClientRect();
                requestAnimationFrame(() => {
                    const popW = pop.offsetWidth || 220;
                    const popH = pop.offsetHeight || 80;
                    let left = Math.round(rowRect.left - modalRect.left) + 8;
                    left = Math.max(8, Math.min(left, (modalContent.clientWidth || modalRect.width) - popW - 12));
                    let top = Math.round(rowRect.top - modalRect.top) - popH - 10;
                    if (top < 8) top = Math.round(rowRect.bottom - modalRect.top) + 10;
                    pop.style.left = left + 'px';
                    pop.style.top = top + 'px';
                    requestAnimationFrame(() => pop.classList.add('visible'));
                });

                ev.stopPropagation();
                return;
            }
        });
    })();

    // Position the titles panel so it sits directly beside the visible image inside the modal (does NOT affect image size).
    function positionTitlesPanel() {
        const panel = document.getElementById('stTitlesPanel');
        if (!panel || panel.style.display === 'none') return;

        // Use image bounds and modalContent bounds so panel is positioned ABSOLUTELY inside modalContent
        const imgRect = modalImg.getBoundingClientRect();
        const modalRect = modalContent.getBoundingClientRect();
        const gap = 16; // visual gap between image and panel
        const extraOffset = 48; // push the panel further to the right
        const panelWidth = panel.offsetWidth || 360;

        // pin the panel to the right side of the viewport (fixed) so it never overlaps the image
        // keep same visual anchor but compute exact pixel positions for adjacent elements
        panel.style.left = '72%';

        // vertically align panel to the modal (not the image)
        const topAnchor = Math.round(modalRect.top + 16);
        panel.style.top = Math.max(8, topAnchor) + 'px';

        // match panel height to the available modal viewport so the Titles panel can be full-height
        // compute available vertical space (respect modal top offset) and use that as the panel height
        const availableHeight = Math.max(50, Math.round(window.innerHeight - topAnchor - 16));
        panel.style.height = availableHeight + 'px';

        // All ST Titles card is positioned together with the MOA / totals column (handled below in the MOA block).

        // Position the separate MOA card so it sits immediately to the RIGHT of the ST Titles panel
        // Prefer placing on the panel's right side; fall back to left only when there is insufficient room.
        const moaCard = document.getElementById('sliderMoaCard');
        if (moaCard) {
            const panelRect = panel.getBoundingClientRect();
            const moaW = moaCard.offsetWidth || 220;
            const gapCard = 12;
            const maxLeft = Math.round(modalRect.width - moaW - 20);

            // Always place MOA card to the RIGHT of the ST Titles panel.
            // Prefer percentage anchor (same as the panel) so the card follows the panel's placement.
            const panelLeftCss = (panel.style && panel.style.left) ? panel.style.left : '';
            const offsetPx = panel.offsetWidth + gapCard;
            let leftCss = null;

            if (panelLeftCss && panelLeftCss.indexOf('%') !== -1) {
                // anchor to the panel's percentage-based left (keeps behavior consistent)
                leftCss = `calc(${panelLeftCss} + ${offsetPx}px)`;
                moaCard.style.left = leftCss;
            } else {
                // pixel-based fallback: compute right edge of panel relative to modalContent
                let leftRel = Math.round(panelRect.right - modalRect.left + gapCard);
                // If it won't fit, expand modalContent width (so we do not move the card to the left)
                if (leftRel + moaW + 20 > modalRect.width) {
                    const needed = leftRel + moaW + 40;
                    modalContent.style.width = Math.min(window.innerWidth - 40, needed) + 'px';
                    // recompute rects after width change
                    const updatedModalRect = modalContent.getBoundingClientRect();
                    const updatedPanelRect = panel.getBoundingClientRect();
                    leftRel = Math.round(updatedPanelRect.right - updatedModalRect.left + gapCard);
                }
                leftRel = Math.max(8, Math.min(leftRel, maxLeft));
                moaCard.style.left = leftRel + 'px';
            }

            // vertically align with the panel top (no image involvement)
            const topRel = Math.max(8, Math.round(panelRect.top - modalRect.top));

            // Position Total STs card above the MOA card (keeps totals stacked)
            const totalStCard = document.getElementById('sliderTotalStCard');
            if (totalStCard) {
                // align left to MOA column anchor (computed above)
                totalStCard.style.left = moaCard.style.left || (Math.round(panelRect.right - modalRect.left + 12) + 'px');
                totalStCard.style.top = topRel + 'px';
                totalStCard.style.display = 'block';
            }

            // position MOA card below Total STs (or at topRel if Total STs absent)
            const moaTop = totalStCard ? (topRel + (totalStCard.offsetHeight || 68) + 12) : topRel;
            moaCard.style.top = moaTop + 'px';

            // show the card
            moaCard.style.display = 'block';

            // Position the All ST Titles card — always anchor it to the RIGHT edge of the modalContent unless explicitly moved by the user.
            const allTitleCard = document.getElementById('sliderAllTitleListCard');
            if (allTitleCard) {
                allTitleCard.style.display = 'block';

                // compute canonical right-edge left coordinate based on current modal width
                try {
                    const modalRectInner = modalContent.getBoundingClientRect();
                    const cardW = allTitleCard.offsetWidth || 420;
                    const rightInset = 24; // px from right edge
                    const computedRightLeft = Math.max(12, Math.round(modalRectInner.width - cardW - rightInset));

                    if (allTitleCard.dataset && allTitleCard.dataset.fixed === 'true') {
                        // keep card 'fixed' but update its stored coordinate so it remains on the right after layout changes
                        const fixedTop = parseInt(allTitleCard.getAttribute('data-top') || (allTitleCard.style.top ? parseInt(allTitleCard.style.top, 10) : '26'), 10);
                        allTitleCard.style.left = computedRightLeft + 'px';
                        allTitleCard.style.top = (Number.isFinite(fixedTop) ? fixedTop : Math.max(8, Math.round(panel.getBoundingClientRect().top - modalRect.top))) + 'px';
                        allTitleCard.setAttribute('data-left', String(computedRightLeft));
                        const status = document.getElementById('allTitleAdjustStatus'); if (status) status.textContent = `Locked: ${computedRightLeft}px, ${allTitleCard.style.top}`;
                    } else {
                        // default auto-placement: always prefer right-edge placement
                        const leftPx = computedRightLeft;
                        allTitleCard.style.left = leftPx + 'px';

                        // vertically align with the ST Titles panel top (keeps visual alignment)
                        const panelRect = panel.getBoundingClientRect();
                        const topPx = Math.max(8, Math.round(panelRect.top - modalRect.top));
                        allTitleCard.style.top = topPx + 'px';
                        allTitleCard.style.display = 'block';
                    }
                } catch(e) {
                    // fallback to current inline style if anything goes wrong
                    allTitleCard.style.left = allTitleCard.style.left || '12px';
                }
            }

            // position Uploaded MOA card directly beneath MOA card (same left anchor)
            const uploadedCard = document.getElementById('sliderUploadedCard');
            if (uploadedCard) {
                uploadedCard.style.left = moaCard.style.left || (moaCard.getBoundingClientRect().left - modalRect.left) + 'px';
                const moaTopPx2 = parseInt(moaCard.style.top || (moaCard.getBoundingClientRect().top - modalRect.top), 10) || 18;
                const uploadedTop = moaTopPx2 + (moaCard.offsetHeight || 68) + 12; // 12px spacing
                uploadedCard.style.top = uploadedTop + 'px';
                uploadedCard.style.display = 'block';

                // Expression of Interest card (stacked beneath Uploaded)
                const exprCard = document.getElementById('sliderExprCard');
                if (exprCard) {
                    exprCard.style.left = uploadedCard.style.left;
                    const exprTop = uploadedTop + (uploadedCard.offsetHeight || 68) + 12;
                    exprCard.style.top = exprTop + 'px';
                    exprCard.style.display = 'block';
                }

                // SB Resolution card (stacked beneath Expression card)
                const resCard = document.getElementById('sliderResCard');
                if (resCard) {
                    resCard.style.left = uploadedCard.style.left;
                    const resTop = (exprCard ? (parseInt(exprCard.style.top || 0, 10) + (exprCard.offsetHeight || 68) + 12) : (uploadedTop + (uploadedCard.offsetHeight || 68) + 12));
                    resCard.style.top = resTop + 'px';
                    resCard.style.display = 'block';
                }

                // Stats chart card (stacked beneath SB Resolution)
                const statsCard = document.getElementById('sliderStatsCard');
                if (statsCard) {
                    // If fixed or user-moved, do NOT auto-reposition — respect inline/data coords
                    if (statsCard.dataset && statsCard.dataset.fixed === 'true') {
                        const fixedLeft = parseInt(statsCard.getAttribute('data-left') || (statsCard.style.left ? parseInt(statsCard.style.left,10) : '0'), 10);
                        const fixedTop = parseInt(statsCard.getAttribute('data-top') || (statsCard.style.top ? parseInt(statsCard.style.top,10) : '0'), 10);
                        statsCard.style.left = fixedLeft + 'px';
                        statsCard.style.top = fixedTop + 'px';
                        statsCard.style.display = 'block';
                    } else if (statsCard.dataset && statsCard.dataset.userMoved === 'true') {
                        // user moved but didn't save — keep current inline position
                        statsCard.style.display = 'block';
                        const curLeft = parseInt(statsCard.style.left || 0, 10);
                        const curTop = parseInt(statsCard.style.top || 0, 10);
                    } else {
                        // default auto-placement (align beneath SB Resolution)
                        statsCard.style.left = uploadedCard.style.left;
                        const statsTop = (resCard ? (parseInt(resCard.style.top || 0, 10) + (resCard.offsetHeight || 68) + 12) : (uploadedTop + (uploadedCard.offsetHeight || 68) + 12));
                        statsCard.style.top = statsTop + 'px';
                        statsCard.style.display = 'block';
                        const curLeft = parseInt(statsCard.style.left || 0, 10);
                        const curTop = parseInt(statsCard.style.top || 0, 10);
                    }
                }
            }
        }
    }

    // Reposition on resize while panel is visible
    window.addEventListener('resize', () => {
        positionTitlesPanel();
    });

    document.querySelectorAll(".slider-img").forEach(img => {
        img.addEventListener("click", function () {
            if (!img.parentElement.classList.contains('swiper-slide-active')) return;
            let fileName = img.src.split('/').pop();
            if (regionMap[fileName]) {
                // Prepare the ST Titles panel but DO NOT reveal it yet — wait until the image finishes its placement animation
                const panel = document.getElementById('stTitlesPanel');
                if (panel) {
                    panel.style.display = 'block';
                    panel.classList.remove('visible');
                    panel.classList.add('hidden');
                }
                // Prepare MOA card (hidden state) so it can fade in together with the panel
                const moaCard = document.getElementById('sliderMoaCard');
                if (moaCard) {
                    moaCard.style.display = 'block';
                    moaCard.classList.remove('visible');
                    moaCard.classList.add('hidden');
                }
                // Prepare Total STs card (hidden state)
                const totalCard = document.getElementById('sliderTotalStCard');
                if (totalCard) {
                    totalCard.style.display = 'block';
                    totalCard.classList.remove('visible');
                    totalCard.classList.add('hidden');
                }
                // Prepare Uploaded MOA card (hidden state)
                const uploadedCard = document.getElementById('sliderUploadedCard');
                if (uploadedCard) {
                    uploadedCard.style.display = 'block';
                    uploadedCard.classList.remove('visible');
                    uploadedCard.classList.add('hidden');
                }
                // Prepare Expression of Interest card (hidden state)
                const exprCard = document.getElementById('sliderExprCard');
                if (exprCard) {
                    exprCard.style.display = 'block';
                    exprCard.classList.remove('visible');
                    exprCard.classList.add('hidden');
                }
                // Prepare SB Resolution card (hidden state)
                const resCard = document.getElementById('sliderResCard');
                if (resCard) {
                    resCard.style.display = 'block';
                    resCard.classList.remove('visible');
                    resCard.classList.add('hidden');
                }

                // Prepare Stats card (hidden state)
                const statsCard = document.getElementById('sliderStatsCard');
                if (statsCard) {
                    statsCard.style.display = 'block';
                    statsCard.classList.remove('visible');
                    statsCard.classList.add('hidden');
                }

                // Prepare Full Title List card (hidden state)
                const allTitleCard = document.getElementById('sliderAllTitleListCard');
                if (allTitleCard) {
                    allTitleCard.style.display = 'block';
                    allTitleCard.classList.remove('visible');
                    allTitleCard.classList.add('hidden');
                }
                // load data immediately so content is ready when panel fades in
                updateModalTable(regionMap[fileName].region);
                // mark opening state so transitionend can reveal the panel
                modalOpening = true;
            }

            const rect = img.getBoundingClientRect();
            // remember this rect so we can return exactly to it on close (fixes misalignment)
            _lastActiveImgRect = rect;
            modalImg.src = img.dataset.img;

            // Get region info from src
            let region = 'Region', name = 'Region Name';
            if (regionMap[fileName]) {
                region = regionMap[fileName].region;
                name = regionMap[fileName].name;
            }

            const header = document.getElementById('modalRegionHeader');
            const titleEl = document.getElementById('modalRegionTitle');
            const nameEl = document.getElementById('modalRegionName');
            header.style.opacity = 0;
            // Typing effect helper
            function typeText(el, text, delay, cb) {
                el.textContent = '';
                let i = 0;
                function type() {
                    if (i <= text.length) {
                        el.textContent = text.slice(0, i);
                        i++;
                        setTimeout(type, delay);
                    } else if (cb) {
                        cb();
                    }
                }
                type();
            }

            // Start with empty, then type title, then name
            titleEl.textContent = '';
            nameEl.textContent = '';
            setTimeout(() => {
                header.style.opacity = 1;
                typeText(titleEl, region, 40, () => {
                    setTimeout(() => typeText(nameEl, name, 30), 120);
                });
            }, 50);


            modal.style.display = "block";
            modal.style.pointerEvents = "auto";
            modal.classList.add('open');

            // Start EXACTLY on top of slider image
            modalContent.style.transition = "none";
            modalContent.style.width = (rect.width - 40) + "px";
            modalContent.style.height = (rect.height - 40) + "px";
            modalContent.style.left = (rect.left + 20) + "px";
            modalContent.style.top = (rect.top + 20) + "px";

            requestAnimationFrame(() => {
                    overlay.style.opacity = "1";
                    modalContent.style.transition = "all 0.6s cubic-bezier(0.22, 1, 0.36, 1)";

                    // reveal the ST Titles panel only after the modalContent transition completes
                    const onModalPlaced = (ev) => {
                        // only run once for the opening motion
                        if (!modalOpening) return;
                        modalOpening = false;
                        const panel = document.getElementById('stTitlesPanel');
                        if (panel) {
                            // make sure position and size are correct before fading in
                            positionTitlesPanel();
                            panel.classList.remove('hidden');
                            // small delay so layout settles, then fade in
                            requestAnimationFrame(() => panel.classList.add('visible'));

                            // reveal MOA card with the same fade timing
                            const moaCard = document.getElementById('sliderMoaCard');
                            if (moaCard) {
                                // positionTitlesPanel() already set moaCard display/left/top — just toggle classes
                                moaCard.classList.remove('hidden');
                                requestAnimationFrame(() => moaCard.classList.add('visible'));
                            }

                            // reveal Total STs card (same timing)
                            const totalCard = document.getElementById('sliderTotalStCard');
                            if (totalCard) {
                                totalCard.classList.remove('hidden');
                                requestAnimationFrame(() => totalCard.classList.add('visible'));
                            }

                            // reveal Uploaded MOA card as well (same timing)
                            const uploadedCard = document.getElementById('sliderUploadedCard');
                            if (uploadedCard) {
                                uploadedCard.classList.remove('hidden');
                                requestAnimationFrame(() => uploadedCard.classList.add('visible'));
                            }

                            // reveal Expression of Interest card
                            const exprCard = document.getElementById('sliderExprCard');
                            if (exprCard) {
                                exprCard.classList.remove('hidden');
                                requestAnimationFrame(() => exprCard.classList.add('visible'));
                            }

                            // reveal SB Resolution card
                            const resCard = document.getElementById('sliderResCard');
                            if (resCard) {
                                resCard.classList.remove('hidden');
                                requestAnimationFrame(() => resCard.classList.add('visible'));
                            }

                            // reveal Stats card (same fade timing as other totals)
                            const statsCard = document.getElementById('sliderStatsCard');
                            if (statsCard) {
                                statsCard.classList.remove('hidden');
                                requestAnimationFrame(() => statsCard.classList.add('visible'));
                            }

                            // reveal Full Title list card only
                            const allTitleCard = document.getElementById('sliderAllTitleListCard');
                            if (allTitleCard) {
                                allTitleCard.classList.remove('hidden');
                                requestAnimationFrame(() => allTitleCard.classList.add('visible'));
                            }
                        }
                    };
                    modalContent.addEventListener('transitionend', onModalPlaced, { once: true });

                    /* HARD LOCK TO REAL LEFT EDGE */
                    modalContent.style.top = "0";
                    modalContent.style.left = "-185px";   // pull past sidebar
                    modalContent.style.maxWidth = 'none';
                    modalContent.style.width = "calc(75vw + 250px)";
                    // ensure inline width can fit image + panel (prevents panel overlapping the image)
                    (function(){
                        const imgContainer = document.getElementById('modalImageContainer');
                        const panel = document.getElementById('stTitlesPanel');
                        const panelW = panel ? (panel.offsetWidth || 360) : 360;
                        const imgW = imgContainer ? (imgContainer.offsetWidth || 420) : 420;
                        const gap = 16;
                        const extraOffset = 48;
                        const needed = imgW + panelW + gap + extraOffset + 56; // padding/margins
                        const current = parseInt(getComputedStyle(modalContent).width, 10) || 0;
                        if (current < needed) modalContent.style.width = Math.min(window.innerWidth - 40, needed) + 'px';
                    })();
                    modalContent.style.height = "100vh";
                    closeBtn.style.opacity = "1";

                    // Position the titles panel next to the expanded modal content
                    setTimeout(positionTitlesPanel, 40); // small delay to let layout settle
                });
        });
    });



    function closeModal() {
        const header = document.getElementById('modalRegionHeader');
        header.style.opacity = 0; // Start fading out immediately

        const activeImg = document.querySelector('.swiper-slide-active img');
        // prefer the originally-saved rect (from opening); fallback to current DOM rect
        const rect = _lastActiveImgRect || (activeImg ? activeImg.getBoundingClientRect() : { left: 0, top: 0, width: 0, height: 0 });

        // First: fade out the ST Titles panel (if visible), then return the modal image
        const panel = document.getElementById('stTitlesPanel');
        const doReturnAnimation = () => {
            // start overlay/modal shrink animation
            overlay.style.opacity = "0";
            closeBtn.style.opacity = "0";

            modalContent.style.width = rect.width + "px";
            modalContent.style.height = rect.height + "px";
            // nudge return position slightly right to match visual alignment preference
            const returnOffsetX = 6; // px (small rightward offset)
            modalContent.style.left = (rect.left + returnOffsetX) + "px";
            modalContent.style.top = rect.top + "px";

            // reposition titles panel so it animates/aligns with the shrinking image
            positionTitlesPanel();

            // Wait for the modal transition to finish before hiding the modal
            setTimeout(() => {
                modal.classList.remove('open');
                modal.style.display = "none";
                modal.style.pointerEvents = "none";
                // clear any zoom/pan state so next open is fresh
                if (modal && typeof modal.resetZoom === 'function') modal.resetZoom();
                if (modal && typeof modal.resetGlobalZoom === 'function') modal.resetGlobalZoom();
                modalImg.src = "";
                if (panel) panel.style.display = 'none';
                const moaCard = document.getElementById('sliderMoaCard');
                if (moaCard) moaCard.style.display = 'none';
                const uploadedCard = document.getElementById('sliderUploadedCard');
                if (uploadedCard) uploadedCard.style.display = 'none';
                const exprCard = document.getElementById('sliderExprCard');
                if (exprCard) exprCard.style.display = 'none';
                const resCard = document.getElementById('sliderResCard');
                if (resCard) resCard.style.display = 'none';
                const statsCardDisp = document.getElementById('sliderStatsCard');
                if (statsCardDisp) statsCardDisp.style.display = 'none';
                const totalCardDisp = document.getElementById('sliderTotalStCard');
                if (totalCardDisp) totalCardDisp.style.display = 'none';
                const allTitleCard = document.getElementById('sliderAllTitleListCard');
                if (allTitleCard) allTitleCard.style.display = 'none';
            }, 520); // match modalContent transition (slightly longer than panel fade)
        };

        if (panel && panel.style.display !== 'none') {
            // fade panel first
            panel.classList.remove('visible');
            panel.classList.add('hidden');
            // fade MOA card + Uploaded card + All Titles card (keeps transitions consistent)
            const moaCard = document.getElementById('sliderMoaCard');
            if (moaCard) {
                moaCard.classList.remove('visible');
                moaCard.classList.add('hidden');
            }
            const uploadedCard = document.getElementById('sliderUploadedCard');
            if (uploadedCard) {
                uploadedCard.classList.remove('visible');
                uploadedCard.classList.add('hidden');
            }
            const exprCard = document.getElementById('sliderExprCard');
            if (exprCard) {
                exprCard.classList.remove('visible');
                exprCard.classList.add('hidden');
            }
            const resCard = document.getElementById('sliderResCard');
            if (resCard) {
                resCard.classList.remove('visible');
                resCard.classList.add('hidden');
            }
            const statsCardHide = document.getElementById('sliderStatsCard');
            if (statsCardHide) { statsCardHide.classList.remove('visible'); statsCardHide.classList.add('hidden'); }
            const totalCardHide = document.getElementById('sliderTotalStCard');
            if (totalCardHide) { totalCardHide.classList.remove('visible'); totalCardHide.classList.add('hidden'); }
            const allTitleCard = document.getElementById('sliderAllTitleListCard');
            if (allTitleCard) {
                allTitleCard.classList.remove('visible');
                allTitleCard.classList.add('hidden');
            }
            // wait for panel fade to complete, then run return animation
            setTimeout(doReturnAnimation, 260);
        } else {
            // ensure MOA + Uploaded + Expr + Res + All Titles cards are hidden even if panel wasn't visible
            const moaCard = document.getElementById('sliderMoaCard');
            if (moaCard) { moaCard.classList.remove('visible'); moaCard.classList.add('hidden'); }
            const uploadedCard = document.getElementById('sliderUploadedCard');
            if (uploadedCard) { uploadedCard.classList.remove('visible'); uploadedCard.classList.add('hidden'); }
            const exprCard = document.getElementById('sliderExprCard');
            if (exprCard) { exprCard.classList.remove('visible'); exprCard.classList.add('hidden'); }
            const resCard = document.getElementById('sliderResCard');
            if (resCard) { resCard.classList.remove('visible'); resCard.classList.add('hidden'); }
            const statsCardHide2 = document.getElementById('sliderStatsCard');
            if (statsCardHide2) { statsCardHide2.classList.remove('visible'); statsCardHide2.classList.add('hidden'); }
            const totalCardHide2 = document.getElementById('sliderTotalStCard');
            if (totalCardHide2) { totalCardHide2.classList.remove('visible'); totalCardHide2.classList.add('hidden'); }
            const allTitleCard = document.getElementById('sliderAllTitleListCard');
            if (allTitleCard) { allTitleCard.classList.remove('visible'); allTitleCard.classList.add('hidden'); }
            doReturnAnimation();
        }
    }



    /* Manual adjuster removed — All ST Titles position is fixed by inline style (data-fixed="true"). */
    console.info('AllTitles: manual adjuster removed; position fixed via inline style/data-fixed');

    // wire up modal close handlers
    closeBtn.addEventListener("click", closeModal);
    overlay.addEventListener("click", closeModal);

});
</script>

@php
    // Get all region data from the controller (simulate, or pass $data and $regions from controller)
    // For demo, we use the same logic as MainReportController
    $regionImage = request()->input('region_image'); // e.g. 'Region 1', 'Region 2', etc.
    $regionToMatch = $regionImage ?? null;
    $allData = [];
    $allRegions = [];
    if (function_exists('app') && app()->bound('view')) {
        $allData = $data ?? [];
        $allRegions = $regions ?? [];
    }
    // If region image is set, filter data by region
    $filteredData = $allData;
    if ($regionToMatch) {
        $filteredData = collect($allData)->where('region', $regionToMatch);
    }
@endphp



@endsection
