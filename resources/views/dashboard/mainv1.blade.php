@extends('layouts.app')

@section('content')
<style>
    .mainv1-metric-filters { display: grid; grid-template-columns: minmax(160px, 1.35fr) repeat(3, minmax(0, 1fr)) auto; gap: .55rem; padding: .75rem 1.25rem; border-bottom: 1px solid #e4edf3; background: #fbfdff; }
    .mainv1-metric-filters input, .mainv1-metric-filters select, .mainv1-metric-filters button { box-sizing: border-box; min-height: 34px; border: 1px solid #d5e3ed; border-radius: 7px; background: #fff; color: #17324d; font-size: .72rem; padding: .4rem .55rem; }
    .mainv1-metric-filters input::placeholder { color: #71869a; }
    .mainv1-metric-filters input:focus, .mainv1-metric-filters select:focus, .mainv1-metric-filters button:focus-visible { border-color: #4b9bd4; outline: 3px solid rgba(75, 155, 212, .18); outline-offset: 1px; }
    .mainv1-metric-filters button { border-color: #165a91; background: #15539a; color: #fff; cursor: pointer; white-space: nowrap; }
    .mainv1-metric-filters button:hover, .mainv1-metric-filters button:focus-visible { background: #0d427b; }
    @media (min-width: 577px) and (max-width: 800px) { .mainv1-metric-filters { grid-template-columns: repeat(2, minmax(0, 1fr)); } .mainv1-metric-filters input { grid-column: 1 / -1; } .mainv1-metric-filters button { justify-self: start; min-width: 120px; } }
    @media (max-width: 576px) { .mainv1-metric-filters { grid-template-columns: 1fr; padding: .65rem; } }
</style>
<style>

    .stb-main-content {
        margin-left: 0 !important;
        margin-right: 0 !important;
        margin-top: 70px !important;
        max-width: 100% !important;
        overflow-x: clip;
        padding-top: 0 !important;
        width: 100% !important;
    }

    @auth
    .stb-main-content {
        box-sizing: border-box;
        margin-left: var(--stb-sidebar-offset, 320px) !important;
        max-width: calc(100% - var(--stb-sidebar-offset, 320px)) !important;
        padding-right: 1.5rem !important;
        padding-left: 1.5rem !important;
        width: calc(100% - var(--stb-sidebar-offset, 320px)) !important;
    }

    @media (max-width: 900px) {
        .stb-main-content {
            margin-left: 0 !important;
            max-width: 100% !important;
            padding-right: 0.75rem !important;
            padding-left: 0.75rem !important;
            width: 100% !important;
        }
    }
    @endauth

    .content-body{
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: flex-start;
        position: relative;
        z-index: 2;
        color: #17324d !important;
        width: 100%;
        overflow-x: clip;
        background: linear-gradient(180deg, #f8fbfd 0%, #ffffff 38%, #f7fafc 100%);
    }

    @media (min-width: 577px) and (max-width: 1100px) {
        .content-body { padding: 30px !important; }
    }

    @media (max-width: 576px) {
        .content-body { padding: 10px !important; }
    }

    @media (min-width: 577px) and (max-width: 1100px) {
        .mainv1-analytics-shell { padding: 30px !important; }
    }

    @media (max-width: 576px) {
        .mainv1-analytics-shell { padding: 10px !important; }
    }

    .mainv1-row {
        display: grid !important;
        grid-template-columns: minmax(0, 0.85fr) minmax(0, 1.15fr);
        align-items: start;
        justify-content: start;
        gap: clamp(1.5rem, 2.5vw, 3rem);
    }

    .mainv1-overview-row {
        width: 100%;
        align-items: stretch;
    }

    .mainv1-overview-row > .col-md-3 {
        display: flex;
    }

    .mainv1-row > :first-child {
        width: 100%;
        max-width: 100%;
        display: flex;
        justify-content: center;
    }

    .mainv1-brand {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        flex-direction: row;
        gap: 1.25rem;
        min-height: 100px;
        padding: 1rem;
        box-sizing: border-box;
    }

    .mainv1-row > :last-child {
        margin-left: 0;
        width: 100%;
        min-width: 0;
    }

    .mainv1-logo {
        display: block;
        width: 420px;
        max-width: 420px;
        height: auto;
        flex: 0 1 auto;
    }

    .mainv1-brand-copy {
        max-width: none;
        text-align: left;
    }

    .mainv1-brand-kicker {
        display: block;
        margin-bottom: 0.4rem;
        color: #e59b18;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        line-height: 1.2;
        text-transform: uppercase;
    }

    .mainv1-brand-title {
        margin: 0;
        color: #123b69;
        font-size: clamp(1.35rem, 2.2vw, 1.9rem);
        font-weight: 800;
        letter-spacing: 0.01em;
        line-height: 1.08;
    }

    .mainv1-brand-rule {
        display: block;
        width: 3.5rem;
        height: 3px;
        margin-top: 0.8rem;
        margin-right: auto;
        margin-left: auto;
        background: #1e90ff;
    }

    .mainv1-filter {
        width: 100%;
        min-width: 0;
        margin-top: 0;
        padding: clamp(0.5rem, 2vw, 1.25rem);
        border: 1px solid #dce7ee;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 8px 24px rgba(23, 50, 77, 0.07);
        margin-bottom: 30px;
    }

    .mainv1-filter-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .mainv1-filter-heading h2 {
        margin: 0;
        color: #17324d;
        font-size: 1rem;
    }

    .mainv1-filter-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        min-height: 36px;
        border: 1px solid #d2e0e8;
        border-radius: 7px;
        padding: 0.45rem 0.7rem;
        background: #fff;
        color: #17324d;
        font-size: 0.78rem;
        font-weight: 700;
        line-height: 1;
        white-space: nowrap;
        cursor: pointer;
    }

    .mainv1-filter-toggle:hover,
    .mainv1-filter-toggle:focus-visible {
        border-color: #4b9bd4;
        background: #eef4fa;
    }

    .mainv1-filter-toggle::after {
        content: '';
        width: 0.42rem;
        height: 0.42rem;
        margin-top: -0.2rem;
        border-right: 2px solid #49627d;
        border-bottom: 2px solid #49627d;
        transform: rotate(45deg);
        transition: transform 0.2s ease;
    }

    .mainv1-filter.is-collapsed .mainv1-filter-toggle::after {
        margin-top: 0.2rem;
        transform: rotate(-135deg);
    }

    .mainv1-filter.is-collapsed .mainv1-filter-grid {
        display: none !important;
    }

    .mainv1-filter.is-collapsed .mainv1-filter-heading {
        margin-bottom: 0;
    }

    .mainv1-filter-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 1rem;
    }

    .mainv1-multi-select[data-filter="title"] {
        grid-column: span 2;
    }

    .mainv1-filter label {
        display: block;
        margin-bottom: 0.35rem;
        color: #49627d;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .mainv1-multi-select {
        position: relative;
        min-width: 0;
    }

    .mainv1-multi-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        min-height: 42px;
        padding: 0.55rem 0.7rem;
        border: 1px solid #d2e0e8;
        border-radius: 7px;
        background: #fff;
        color: #17324d;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .mainv1-multi-trigger::after {
        content: '\25BE';
        color: #49627d;
        font-size: 0.75rem;
    }

    .mainv1-multi-menu {
        position: absolute;
        top: calc(100% + 0.35rem);
        left: 0;
        z-index: 10;
        display: none;
        width: min(100%, 620px);
        min-width: 420px;
        padding: 0.85rem;
        border: 1px solid #d2e0e8;
        border-radius: 7px;
        background: #fff;
        box-shadow: 0 10px 22px rgba(23, 50, 77, 0.14);
    }

    .mainv1-multi-select.is-open .mainv1-multi-menu {
        display: block;
    }

    .mainv1-filter .mainv1-multi-option {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        width: 100%;
        margin: 0;
        padding: 0.55rem 0.7rem;
        box-sizing: border-box;
        border-radius: 5px;
        color: #17324d;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
    }

    .mainv1-multi-panel {
        display: grid;
        grid-template-columns: 1fr 1.45fr;
        gap: 1rem;
    }

    .mainv1-multi-panel-title {
        margin: 0 0 0.55rem;
        color: #56616d;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }

    .mainv1-multi-selected {
        min-height: 115px;
        max-height: 205px;
    min-width: 0;

        overflow-y: auto;
        padding-right: 0.25rem;
    }

    .mainv1-multi-selected-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0.4rem 0;
        border-bottom: 1px solid #edf1f5;
        color: #17324d;
        font-size: 0.82rem;
    }

    .mainv1-multi-selected-item button {
        border: 0;
        background: transparent;
        color: #71849a;
        cursor: pointer;
    }

    .mainv1-multi-search {
        width: 100%;
        margin-bottom: 0.45rem;
        padding: 0.55rem 0.7rem;
        border: 1px solid #d2e0e8;
        border-radius: 7px;
        color: #17324d;
    }

    .mainv1-multi-options {
        max-height: 205px;
        overflow-y: auto;
    }

    .mainv1-filter .mainv1-multi-option:hover {
        background: #eef4fa;
    }

    .mainv1-filter .mainv1-multi-option input {
        width: 1rem;
        height: 1rem;
        flex: 0 0 auto;
        margin: 0;
        accent-color: #123b69;
    }

    .mainv1-filter .mainv1-multi-option span {
        min-width: 0;
        padding-left: 0.1rem;
        line-height: 1.35;
    }

    .mainv1-selection-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-top: 0.55rem;
    }

    .mainv1-selection-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.25rem 0.45rem;
        border-radius: 999px;
        background: #e8eef5;
        color: #123b69;
        font-size: 0.75rem;
    }

    .mainv1-selection-chip button {
        border: 0;
        padding: 0;
        background: transparent;
        color: #49627d;
        line-height: 1;
        cursor: pointer;
    }

    .mainv1-no-options {
        padding: 0.55rem;
        color: #71849a;
        font-size: 0.8rem;
    }

    .mainv1-filter select {
        width: 100%;
        min-height: 42px;
        padding: 0.55rem 0.7rem;
        border: 1px solid #d2e0e8;
        border-radius: 7px;
        background: #fff;
        color: #17324d;
    }

    .mainv1-filter-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-top: 1rem;
    }

    .mainv1-result-count {
        color: #49627d;
        font-size: 0.85rem;
    }

    .mainv1-apply {
        border: 0;
        border-radius: 7px;
        padding: 0.55rem 0.85rem;
        background: #17324d;
        color: #ffffff;
        font-weight: 700;
        white-space: nowrap;
    }

    .mainv1-results {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .mainv1-result {
        padding: 0.85rem;
        border: 1px solid #dce7ee;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.82);
    }

    .mainv1-result strong,
    .mainv1-result span {
        display: block;
    }

    .mainv1-result span {
        margin-top: 0.25rem;
        color: #49627d;
        font-size: 0.82rem;
    }

    .mainv1-dashboard {
        display: grid;
        grid-template-columns: minmax(0, 1.55fr) minmax(260px, 0.7fr);
        gap: 1.25rem;
        width: 100%;
        margin-top: 1.25rem;
        padding: 1.15rem;
        box-sizing: border-box;
        border: 1px solid #dce7ee;
        border-radius: 18px;
        background: linear-gradient(145deg, #ffffff 0%, #f6fbfd 100%);
        box-shadow: 0 12px 30px rgba(23, 50, 77, 0.08);
    }

    .mainv1-total-list {
        display: grid;
        grid-template-columns: 1fr;
        grid-template-rows: repeat(4, minmax(0, 1fr));
        gap: 0.7rem;
        align-content: stretch;
        width: 100%;
    }

    .mainv1-total-card {
        display: flex;
        min-height: 0;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
        padding: 0.7rem;
        border: 2px solid #dce7ee;
        border-radius: 10px;
        text-align: center;
        background: linear-gradient(145deg, #ffffff 0%, #f8fbfd 100%);
        box-shadow: 0 5px 16px rgba(23, 50, 77, 0.06);

    }

    .mainv1-total-card span {
        display: block;
        color: #56728a;
        font-size: 0.68rem;
        font-weight: 800;
    }


    .mainv1-total-card strong {
        display: block;
        margin-top: 0.3rem;
        color: #2789d9;
        font-size: clamp(2.8rem, 5vw, 4rem) !important;
        line-height: 1 !important;
    }

    .mainv1-metric-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.7rem; width: 100%; }
    .mainv1-metric-grid .mainv1-total-list { display: contents; }
    .mainv1-metric-card { align-items: flex-start; min-height: 158px; padding: 1rem 1.15rem; text-align: left; padding:30px;}
    .mainv1-metric-card span { min-height: 2.2em; line-height: 1.35; text-align: left; }
    .mainv1-metric-value { display: flex; align-items: baseline; justify-content: space-between; gap: 0.5rem; width: 100%; margin-top: 0.45rem; }
    .mainv1-metric-card .mainv1-metric-value strong { margin-top: 0; }
    .mainv1-metric-percent { flex: 0 0 auto; color: #56728a; font-size: 0.78rem; font-weight: 800; }
    .mainv1-metric-progress { width: 100%; height: 5px; margin-top: auto; overflow: hidden; border-radius: 999px; background: #dce7ee; }
    .mainv1-metric-progress span { display: block; width: var(--mainv1-metric-progress, 0%); min-height: 0; height: 100%; border-radius: inherit; background: #2789d9; }
    [data-mainv1-metric="all"] .mainv1-metric-progress { display: none; }
    .mainv1-metric-caption { min-height: 0 !important; margin-top: 0.45rem; color: #71849a !important; font-size: 0.72rem !important; font-weight: 600 !important; }
    .mainv1-secondary-card { display: grid; grid-template-columns: 2.8rem minmax(0, 1fr); grid-template-rows: auto 1fr; column-gap: 0.8rem; min-height: 86px; padding: 0.75rem 1rem; align-items: center; }
    .mainv1-secondary-card .mainv1-metric-icon { grid-row: 1 / span 2; display: grid; width: 2.8rem; height: 2.8rem; place-items: center; border-radius: 0.85rem; background: rgba(39, 137, 217, 0.12); color: #2789d9; font-size: 1.25rem; }
    .mainv1-secondary-card .mainv1-metric-label { grid-column: 2; min-height: 0; color: #56728a; font-size: 0.75rem; font-weight: 700; }
    .mainv1-secondary-card .mainv1-metric-value { grid-column: 2; justify-content: flex-start; gap: 0.65rem; margin-top: 0.1rem; }
    .mainv1-secondary-card .mainv1-metric-value strong { font-size: 2rem !important; }
    .mainv1-secondary-card .mainv1-metric-progress,
    .mainv1-secondary-card .mainv1-metric-caption { display: none; }
    .mainv1-overview-dashboard { width: 100%; }

    .mainv1-map-panel h2,
    .mainv1-region-panel h2 {
        margin: 0 0 0.85rem;
        color: #17324d;
        font-size: 0.9rem;
        font-weight: 800;
        letter-spacing: 0.045em;
        text-transform: uppercase;
    }

    .mainv1-map-panel {
        min-width: 0;
        padding: 0.55rem 0.75rem 0.85rem;
        border-radius: 14px;
        background: radial-gradient(circle at 50% 42%, #ffffff 0%, #f8fcfd 70%, #eef7f8 100%);
        text-align: center;
    }

    .mainv1-svg-map-wrap {
        position: relative;
        display: block;
        width: 100%;
        height: clamp(460px, 52vw, 700px);
        margin: 0 auto;
        overflow: hidden;
        border: 1px solid #dce8ee;
        border-radius: 10px;
        background: #eaf5f7;
        text-align: left;
    }
    .mainv1-svg-map-stage { display: grid; width: 100%; height: 100%; min-width: 0; min-height: 0; place-items: center; transform-origin: center; transition: transform 0.25s ease; }
    .mainv1-svg-map-stage object { display: block; width: min(100%, 700px); height: 100%; min-width: 0; min-height: 0; margin: 0 auto; }
    .mainv1-map-panel .mainv1-svg-map-stage object { width: auto !important; max-width: 100%; height: 100% !important; max-height: 100%; }
    .mainv1-map-controls { position: absolute; z-index: 2; top: 0.7rem; right: 0.7rem; display: inline-flex; gap: 0.25rem; padding: 0.25rem; border: 1px solid #d2e0e8; border-radius: 8px; background: rgba(255, 255, 255, 0.94); box-shadow: 0 2px 8px rgba(23, 50, 77, 0.12); }
    .mainv1-map-control { width: 2rem; height: 2rem; border: 0; border-radius: 5px; background: transparent; color: #17324d; font-size: 1.05rem; font-weight: 800; line-height: 1; cursor: pointer; }
    .mainv1-map-control:hover, .mainv1-map-control:focus-visible { background: #e8f6f4; outline: none; }
    .mainv1-map-control:disabled { color: #9aabba; cursor: not-allowed; opacity: 0.55; }
    .mainv1-svg-map-wrap { cursor: grab; touch-action: none; }
    .mainv1-svg-map-wrap.is-panning { cursor: grabbing; }
    .mainv1-svg-map-wrap,
    .mainv1-svg-map-wrap object { user-select: none; -webkit-user-select: none; -webkit-user-drag: none; }

    .mainv1-map-panel p {
        margin: 0.35rem 0 0;
        color: #10aeb5;
        font-size: 0.76rem;
        font-weight: 700;
    }

    .mainv1-map-panel { position: relative; }

    .mainv1-region-panel {
        min-width: 0;
        align-self: stretch;
        height: auto;
        max-height: none;
        min-height: 0;
        box-sizing: border-box;
        overflow: auto;
        padding: 1rem;
        border: 1px solid #dce8ee;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.72);
        scrollbar-color: #b8d2dc transparent;
    }

    .mainv1-region-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        min-height: 36px;
        margin-bottom: 0.45rem;
        padding: 0.45rem 0.6rem;
        border: 1px solid transparent;
        border-radius: 8px;
        background: #f7fafc;
        color: #17324d;
        font-size: 0.8rem;
        cursor: pointer;
        transition: background 0.18s ease, border-color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
    }

    .mainv1-region-row.is-active,
    .mainv1-region-row:hover,
    .mainv1-region-row:focus-visible {
        background: #e8f6f4;
        border-color: #b7e3df;
        box-shadow: 0 5px 14px rgba(16, 174, 181, 0.14);
        transform: translateX(-3px);
        outline: none;
    }

    .mainv1-region-row-main {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        min-width: 0;
    }

    .mainv1-region-color-dot {
        width: 0.75rem;
        height: 0.75rem;
        flex: 0 0 auto;
        border-radius: 50%;
    }

    .mainv1-region-row strong {
        min-width: 2.3rem;
        padding: 0.2rem 0.45rem;
        border-radius: 999px;
        background: #e8f7f6;
        color: #10aeb5;
        font-size: 0.72rem;
        text-align: center;
    }

    .mainv1-regional-overview {
        width: 100%;
        margin-top: 1.25rem;
        padding: 1.5rem 0 0.5rem;
        overflow: hidden;
    }

    .mainv1-category-track {
        width: 100%;
        overflow: clip;
        padding: 0.75rem 1rem;
    }

    .mainv1-gallery-marquee {
        display: flex;
        align-items: flex-start;
        width: max-content;
        gap: 1.15rem;
        animation: mainv1-gallery-scroll 350s linear infinite;
    }

    .mainv1-category-track:hover .mainv1-gallery-marquee,
    .mainv1-gallery-marquee:has(.mainv1-category-card:hover) {
        animation-play-state: paused;
    }

    .mainv1-gallery-marquee.is-gallery-open {
        animation-play-state: paused;
    }

    .mainv1-gallery-marquee.is-gallery-open .mainv1-category-card:not(.is-gallery-active):hover {
        flex: 0 0 180px !important;
        width: 180px !important;
        min-width: 180px !important;
        height: 155px !important;
        min-height: 155px !important;
        align-items: center !important;
        flex-direction: column !important;
        gap: 0 !important;
        transform: none !important;
        box-shadow: 0 7px 20px rgba(18, 59, 105, 0.1) !important;
    }

    .mainv1-gallery-marquee.is-gallery-open .mainv1-category-card:not(.is-gallery-active):hover .mainv1-category-content {
        width: 0 !important;
        min-width: 0 !important;
        opacity: 0 !important;
    }

    .mainv1-gallery-marquee.is-gallery-open .mainv1-category-card:not(.is-gallery-active):hover .mainv1-category-title {
        display: block !important;
    }

    .mainv1-category-card {
        display: flex;
        flex: 0 0 180px;
        height: 155px;
        min-height: 155px;
        flex-direction: column;
        align-items: center;
        justify-content: space-between;
        padding: 0.85rem;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.94);
        box-shadow: 0 7px 20px rgba(18, 59, 105, 0.1);
        color: #123b69;
        text-decoration: none;
        overflow: hidden;
        box-sizing: border-box;
        transition: flex-basis 0.35s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }

    .mainv1-category-card:hover,
    .mainv1-category-card:focus-visible,
    .mainv1-category-card.is-gallery-active {
        flex-basis: 460px !important;
        align-items: flex-start;
        flex-direction: row;
        gap: 1rem;
        transform: translateY(-4px);
        box-shadow: 0 14px 30px rgba(18, 59, 105, 0.18);
    }

    .mainv1-category-card img {
        width: 92px;
        height: 92px;
        flex: 0 0 92px;
        object-fit: contain;
    }

    .mainv1-category-card strong {
        font-size: 0.78rem;
        text-align: center;
    }

    .mainv1-category-title {
        display: block;
        color: #123b69;
    }

    .mainv1-category-content {
        display: flex;
        width: 0;
        min-width: 0;
        flex-direction: column;
        justify-content: center;
        opacity: 0;
        overflow: hidden;
        max-height: 125px;
        transition: width 0.35s ease, opacity 0.2s ease;
    }

    .mainv1-category-card:hover .mainv1-category-content,
    .mainv1-category-card:focus-visible .mainv1-category-content,
    .mainv1-category-card.is-gallery-active .mainv1-category-content {
        width: 280px !important;
        opacity: 1 !important;
    }

    .mainv1-category-card:hover .mainv1-category-title,
    .mainv1-category-card:focus-visible .mainv1-category-title,
    .mainv1-category-card.is-gallery-active .mainv1-category-title {
        display: none;
    }

    .mainv1-category-content strong {
        margin-bottom: 0.4rem;
        text-align: left;
    }

    .mainv1-category-children {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem 0.75rem;
        margin: 0;
        color: #49627d;
        font-size: 0.75rem;
        line-height: 1.35;
        max-height: 92px;
        overflow-x: hidden;
        overflow-y: auto;
        padding-right: 0.25rem;
    }

    .mainv1-gallery-popover {
        position: fixed;
        z-index: 1000;
        display: none;
        width: min(360px, calc(100vw - 2rem));
        max-height: min(460px, calc(100vh - 2rem));
        overflow-y: auto;
        padding: 1rem;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 20px 55px rgba(18, 59, 105, 0.24);
    }

    .mainv1-gallery-popover.is-open {
        display: block;
    }

    .mainv1-gallery-popover-backdrop {
        display: none;
    }

    .mainv1-gallery-popover-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .mainv1-gallery-popover-header h2 {
        margin: 0;
        color: #123b69;
        font-size: 1.2rem;
    }

    .mainv1-gallery-popover-close {
        border: 0;
        background: transparent;
        color: #49627d;
        cursor: pointer;
        font-size: 1.4rem;
        line-height: 1;
    }

    .mainv1-gallery-child {
        margin-bottom: 0.7rem;
        padding: 0.7rem 0.8rem;
        border-radius: 8px;
        background: #f7fafc;
        color: #123b69;
    }

    .mainv1-gallery-child-title {
        font-weight: 700;
    }

    .mainv1-gallery-child-title a,
    .mainv1-gallery-link {
        color: #1769aa;
        text-decoration: underline;
        text-underline-offset: 2px;
    }

    .mainv1-gallery-link {
        display: block;
        margin-top: 0.25rem;
        overflow-wrap: anywhere;
        font-size: 0.76rem;
    }

    .mainv1-gallery-child-description {
        margin: 0.25rem 0 0;
        color: #49627d;
        font-size: 0.82rem;
    }

    .mainv1-gallery-subchildren {
        display: grid;
        gap: 0.35rem;
        margin: 0.6rem 0 0 1rem;
        padding-left: 0.75rem;
    }

    .mainv1-gallery-subchild {
        color: #49627d;
        font-size: 0.8rem;
    }

    .mainv1-region-modal {
        position: fixed;
        inset: 0;
        z-index: 1200;
        display: none;
        padding: 1.25rem;
        overflow-y: auto;
        background: rgba(15, 35, 55, 0.48);
    }

    .mainv1-region-modal.is-open {
        display: flex;
        align-items: flex-start;
        justify-content: center;
    }

    .mainv1-region-dialog {
        position: relative;
        width: min(1440px, 100%);
        height: auto;
        max-height: none;
        overflow: visible;
        border: 1px solid #dbe6ed;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 24px 70px rgba(18, 59, 105, 0.28);
    }

    .mainv1-region-modal-close {
        position: absolute;
        top: 0.65rem;
        right: 0.75rem;
        z-index: 2;
        width: 2rem;
        height: 2rem;
        border: 0;
        border-radius: 50%;
        background: #edf3f7;
        color: #17324d;
        font-size: 1.35rem;
        line-height: 1;
        cursor: pointer;
    }

    .mainv1-region-modal-header {
        padding: 1.15rem 3.5rem 0.9rem 1.35rem;
        border-bottom: 1px solid #e6edf2;
        color: #17324d;
    }

    .mainv1-region-modal-header h2 {
        margin: 0;
        font-size: 1.25rem;
    }

    .mainv1-replication-settings {
        display: flex;
        align-items: end;
        gap: 0.55rem;
        margin-top: 0.7rem;
        max-width: 760px;
    }

    .mainv1-replication-settings label {
        display: grid;
        flex: 1 1 auto;
        gap: 0.25rem;
        color: #49627d;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .mainv1-replication-settings input {
        min-height: 34px;
        padding: 0.4rem 0.55rem;
        border: 1px solid #bccbd4;
        border-radius: 4px;
        color: #17324d;
    }

    .mainv1-replication-settings button {
        min-height: 34px;
        padding: 0.4rem 0.7rem;
        border: 1px solid #1769aa;
        border-radius: 4px;
        background: #1769aa;
        color: #fff;
        cursor: pointer;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .mainv1-region-modal-body {
        display: grid;
        grid-template-columns: minmax(250px, 0.9fr) minmax(320px, 1.05fr) minmax(330px, 1.35fr);
        gap: 1rem;
        padding: 1rem;
        height: auto;
        box-sizing: border-box;
        overflow: visible;
    }

    .mainv1-region-panel {
        min-width: 0;
        padding: 0.8rem;
        border: 1px solid #e5edf2;
        border-radius: 9px;
        background: #fff;
    }

    .mainv1-region-panel.title-panel {
        display: flex;
        flex-direction: column;
        height: auto;
        min-height: 0;
        box-sizing: border-box;
    }

    .mainv1-region-panel.left-panel {
        padding: 0.7rem;
    }

    .mainv1-region-map {
        display: block;
        width: 100%;
        height: 390px;
        object-fit: contain;
        border-radius: 8px;
        background: #fff;
    }

    .mainv1-region-panel-title {
        margin: 0 0 0.7rem;
        color: #17324d;
        font-size: 1rem;
    }

    .mainv1-region-filter {
        display: grid;
        gap: 0.35rem;
        margin-bottom: 0.75rem;
        color: #49627d;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .mainv1-region-filter input {
        min-height: 34px;
        padding: 0.4rem 0.55rem;
        border: 1px solid #bccbd4;
        border-radius: 4px;
        color: #17324d;
    }

    .mainv1-region-select {
        position: relative;
    }

    .mainv1-region-select-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        min-height: 34px;
        padding: 0.4rem 0.55rem;
        border: 1px solid #bccbd4;
        border-radius: 4px;
        background: #fff;
        color: #17324d;
        font-size: 0.8rem;
        text-align: left;
    }

    .mainv1-region-select-trigger::after {
        content: '\25BE';
        color: #49627d;
        font-size: 0.7rem;
    }

    .mainv1-region-select.is-open .mainv1-region-select-menu {
        display: grid;
    }

    .mainv1-region-select-menu {
        position: absolute;
        top: calc(100% + 0.35rem);
        left: 0;
        z-index: 5;
        display: none;
        grid-template-columns: 1fr 1.35fr;
        gap: 0.75rem;
        width: min(100%, 430px);
        min-width: 330px;
        padding: 0.7rem;
        border: 1px solid #d2e0e8;
        border-radius: 6px;
        background: #fff;
        box-shadow: 0 10px 22px rgba(23, 50, 77, 0.16);
    }

    .mainv1-region-select-heading {
        margin: 0 0 0.45rem;
        color: #56616d;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .mainv1-region-select-selected,
    .mainv1-region-select-options {
        max-height: 170px;
        overflow-y: auto;
    }

    .mainv1-region-select-selected-empty {
        padding: 0.45rem;
        color: #9aa8b6;
        font-size: 0.75rem;
    }

    .mainv1-region-select-selected-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.35rem;
        padding: 0.35rem 0;
        border-bottom: 1px solid #edf1f5;
        color: #17324d;
        font-size: 0.72rem;
    }

    .mainv1-region-select-selected-item button {
        border: 0;
        background: transparent;
        color: #71849a;
        cursor: pointer;
    }

    .mainv1-region-select-search {
        width: 100%;
        margin-bottom: 0.4rem;
        padding: 0.45rem 0.55rem;
        border: 1px solid #d2e0e8;
        border-radius: 5px;
        color: #17324d;
        font-size: 0.75rem;
    }

    .mainv1-region-select-option {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.35rem 0.15rem;
        color: #17324d;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .mainv1-region-select-option input {
        width: 0.9rem;
        height: 0.9rem;
        margin: 0;
        accent-color: #123b69;
    }

    .mainv1-region-status-grid,
    .mainv1-region-metric-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.65rem;
        margin-top: 0.85rem;
    }

    .mainv1-region-status,
    .mainv1-region-metric {
        min-height: 74px;
        padding: 0.65rem;
        border: 1px solid #e5edf2;
        border-radius: 8px;
        text-align: center;
        color: #688096;
        font-size: 0.72rem;
    }

    .mainv1-region-status strong,
    .mainv1-region-metric strong {
        display: block;
        margin-top: 0.3rem;
        color: #17324d;
        font-size: 1.15rem;
    }

    .mainv1-region-status.ongoing strong { color: #18a558; }
    .mainv1-region-status.inactive strong { color: #ef4444; }

    .mainv1-region-chart {
        margin-top: 0.8rem;
        padding: 0.7rem 0.7rem 0.35rem;
        border: 1px solid #e5edf2;
        border-radius: 8px;
    }

    .mainv1-region-chart-title {
        margin: 0 0 0.35rem;
        color: #688096;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .mainv1-region-chart canvas {
        display: block;
        width: 100%;
        height: 250px;
    }

    .mainv1-region-title-summary {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.65rem;
        color: #49627d;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .mainv1-region-title-list {
        display: grid;
        grid-auto-rows: max-content;
        align-content: start;
        gap: 0.55rem;
        flex: 0 0 520px;
        height: 520px;
        min-height: 0;
        overflow-y: auto;
    }

    .mainv1-region-title-row {
        position: relative;
        display: grid;
        grid-template-columns: 42px minmax(0, 1fr) 18px;
        gap: 0.65rem;
        align-items: start;
        padding: 0.75rem;
        border: 1px solid #e8eef2;
        border-radius: 7px;
        background: #fff;
        color: #17324d;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
    }

    .mainv1-replicate-popover {
        position: absolute;
        right: 0.65rem;
        bottom: calc(100% - 1px);
        z-index: 8;
        display: none;
        width: min(270px, calc(100vw - 3rem));
        padding: 0.75rem;
        border: 1px solid #cbdbe6;
        border-radius: 0;
        background: #fff;
        box-shadow: 0 10px 24px rgba(23, 50, 77, 0.2);
        color: #17324d;
        font-size: 0.76rem;
        font-weight: 600;
    }

    .mainv1-region-title-row:nth-child(-n + 2) .mainv1-replicate-popover {
        top: calc(100% - 1px);
        bottom: auto;
    }

    .mainv1-region-title-row:hover .mainv1-replicate-popover,
    .mainv1-region-title-row:focus-within .mainv1-replicate-popover,
    .mainv1-region-title-row.is-replicate-open .mainv1-replicate-popover {
        display: block;
    }

    .mainv1-replicate-popover-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.4rem;
        margin-top: 0.65rem;
    }

    .mainv1-replicate-popover-title {
        display: block;
        margin-bottom: 0.3rem;
        color: #17324d;
        font-size: 0.72rem;
    }

    .mainv1-replicate-popover button {
        padding: 0.3rem 0.55rem;
        border: 1px solid #cbdbe6;
        border-radius: 4px;
        background: #fff;
        color: #49627d;
        cursor: pointer;
        font-size: 0.7rem;
        font-weight: 700;
    }

    .mainv1-replicate-popover .mainv1-replicate-confirm {
        border-color: #1769aa;
        background: #1769aa;
        color: #fff;
    }

    .mainv1-replicate-popover.is-confirmed {
        color: #18864b;
    }

    .mainv1-region-title-row:hover,
    .mainv1-region-title-row.is-expanded {
        border-color: #b9cfe0;
        background: #f8fbfd;
    }

    .mainv1-region-title-arrow {
        color: #8ba0b1;
        text-align: center;
        transition: transform 0.15s ease;
    }

    .mainv1-region-title-row.is-expanded .mainv1-region-title-arrow {
        transform: rotate(180deg);
    }

    .mainv1-region-title-details {
        display: none;
        grid-column: 2 / -1;
        padding-top: 0.55rem;
        border-top: 1px solid #e8eef2;
        color: #49627d;
        font-size: 0.74rem;
        font-weight: 500;
    }

    .mainv1-region-title-row.is-expanded .mainv1-region-title-details {
        display: grid;
        gap: 0.3rem;
    }

    .mainv1-region-title-location {
        display: grid;
        grid-template-columns: minmax(100px, 0.8fr) minmax(0, 1.2fr) auto;
        gap: 0.5rem;
        padding: 0.3rem 0;
    }

    .mainv1-region-title-location strong {
        color: #17324d;
    }

    .mainv1-region-title-status {
        color: #18a558;
        font-weight: 700;
    }

    .mainv1-region-title-status.is-inactive {
        color: #ef4444;
    }

    .mainv1-region-title-count {
        color: #2563eb;
        text-align: center;
    }

    @media (max-width: 1100px) {
        .mainv1-region-modal-body {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .mainv1-region-panel.title-panel {
            grid-column: 1 / -1;
            min-height: 0;
        }

        .mainv1-region-map { height: 300px; }
    }

    @media (max-width: 900px) {
        .mainv1-region-modal-body { grid-template-columns: 1fr; }
        .mainv1-region-map { height: 250px; }
        .mainv1-region-panel.title-panel {
            grid-column: auto;
            min-height: 220px;
        }
        .mainv1-region-title-list {
            flex-basis: 420px;
            height: 420px;
            min-height: 180px;
        }
    }

    @media (max-width: 600px) {
        .mainv1-region-modal-header { padding: 0.9rem 3rem 0.75rem 0.9rem; }
        .mainv1-region-modal-header h2 { font-size: 1rem; }
        .mainv1-region-modal-body { gap: 0.65rem; padding: 0.65rem; }
        .mainv1-region-panel { padding: 0.65rem; }
        .mainv1-region-map { height: 190px; }
        .mainv1-region-chart canvas { height: 190px; }
        .mainv1-region-title-list {
            flex-basis: 300px;
            height: 300px;
        }

        .mainv1-region-title-row {
            grid-template-columns: 30px minmax(0, 1fr) 16px;
            gap: 0.45rem;
            padding: 0.6rem;
            font-size: 0.76rem;
        }

        .mainv1-region-title-location {
            grid-template-columns: 1fr;
            gap: 0.15rem;
        }

        .mainv1-region-select-menu {
            grid-template-columns: 1fr;
            width: 100%;
            min-width: 0;
            max-width: calc(100vw - 2rem);
        }

        .mainv1-region-status-grid,
        .mainv1-region-metric-grid { gap: 0.45rem; }

        .mainv1-region-status,
        .mainv1-region-metric {
            min-height: 62px;
            padding: 0.5rem;
            font-size: 0.66rem;
        }

        .mainv1-region-status strong,
        .mainv1-region-metric strong { font-size: 1rem; }
    }

    @keyframes mainv1-gallery-scroll {
        from { transform: translateX(0); }
        to { transform: translateX(-50%); }
    }

    .mainv1-section-title {
        margin: 0 0 1.25rem;
        color: #123b69;
        font-size: clamp(1.45rem, 2.5vw, 2rem);
        font-weight: 800;
        letter-spacing: 0.04em;
        text-align: center;
        text-transform: uppercase;
    }

    .mainv1-section-title::after {
        display: block;
        width: 135px;
        height: 4px;
        margin: 0.6rem auto 0;
        border-radius: 2px;
        background: #1e90ff;
        content: '';
    }

    .mainv1-regional-track {
        position: relative;
        width: 100%;
        height: 350px;
        overflow: hidden;
        padding-top: 2.5rem;
        box-sizing: border-box;
        perspective: 900px;
        cursor: grab;
        touch-action: pan-y;
        user-select: none;
    }

    .mainv1-regional-track.is-dragging {
        cursor: grabbing;
    }

    .mainv1-carousel-shell {
        position: relative;
        width: 100%;
    }

    .mainv1-carousel-control {
        position: absolute;
        top: 50%;
        z-index: 2;
        display: grid;
        width: 2.6rem;
        height: 2.6rem;
        place-items: center;
        padding: 0;
        transform: translateY(-50%);
        border: 1px solid rgba(18, 59, 105, 0.12);
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 8px 20px rgba(18, 59, 105, 0.16);
        color: #123b69;
        cursor: pointer;
        font-size: 1.7rem;
        line-height: 1;
        transition: background 0.18s ease, color 0.18s ease, opacity 0.18s ease, transform 0.18s ease;
    }

    .mainv1-carousel-control:hover:not(:disabled),
    .mainv1-carousel-control:focus-visible:not(:disabled) {
        background: #123b69;
        color: #fff;
        transform: translateY(-50%) scale(1.06);
    }

    .mainv1-carousel-control:disabled {
        cursor: default;
        opacity: 0.35;
    }

    .mainv1-carousel-control[data-carousel-direction="prev"] {
        left: 0.35rem;
    }

    .mainv1-carousel-control[data-carousel-direction="next"] {
        right: 0.35rem;
    }

    .mainv1-regional-card {
        display: flex;
        position: absolute;
        top: 3.5rem;
        left: 50%;
        width: 180px;
        min-height: 205px;
        flex-direction: column;
        align-items: center;
        justify-content: space-between;
        padding: 0.85rem;
        border-radius: 10px;
        background: transparent;
        box-shadow: none;
        color: #123b69;
        text-decoration: none;
        -webkit-user-drag: none;
        opacity: 0;
        pointer-events: none;
        transform: translateX(-50%) scale(0.5);
        transform-origin: center center;
        transition: transform 0.65s cubic-bezier(0.22, 0.61, 0.36, 1), opacity 0.65s ease, filter 0.65s ease;
        will-change: transform, opacity;
    }

    .mainv1-regional-card.is-carousel-visible {
        pointer-events: auto;
    }

    .mainv1-regional-card:hover,
    .mainv1-regional-card:focus-visible,
    .mainv1-regional-card.is-carousel-center {
        box-shadow: none;
    }

    .mainv1-regional-card img {
        display: block;
        width: 100%;
        height: 155px;
        object-fit: contain;
        pointer-events: none;
        user-select: none;
        -webkit-user-drag: none;
    }

    .mainv1-regional-card strong {
        color: #123b69;
        font-size: 1rem;
        text-align: center;
    }

    @media (min-width: 1101px) {
        .mainv1-overview-row > .col-md-3 {
            width: 20%;
            max-width: 20%;
        }

        .mainv1-overview-row > .col-md-6 {
            width: 60%;
            max-width: 60%;
            flex: 0 0 60%;
        }
    }

    @media (max-width: 1400px) {
        .mainv1-filter-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .mainv1-multi-select[data-filter="title"] {
            grid-column: span 2;
        }

        .mainv1-total-list {
            grid-template-columns: 1fr;
        }

        .mainv1-map-panel object {
            width: min(100%, 900px);
            height: clamp(440px, 52vw, 680px);
        }
    }

    @media (max-width: 1100px) {
        .mainv1-metric-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }

        .mainv1-overview-row {
            display: flex;
            flex-wrap: wrap;
        }

        .mainv1-overview-row > .col-md-3 {
            display: block;
            width: 50%;
            max-width: 50%;
        }

        .mainv1-total-list {
            grid-template-rows: repeat(4, minmax(0, 1fr));
        }

        .mainv1-row {
            display: flex !important;
            flex-direction: column;
            gap: 1rem;
        }

        .mainv1-row > :first-child,
        .mainv1-row > :last-child {
            box-sizing: border-box;
            width: 100%;
            max-width: none;
        }

        .mainv1-row > :last-child {
            align-self: stretch;
            padding-right: 0 !important;
            padding-left: 0 !important;
        }

        .mainv1-filter {
            box-sizing: border-box;
            width: 100%;
            padding-bottom: 1.5rem;
        }

        .mainv1-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .mainv1-multi-select[data-filter="title"],
        .mainv1-filter-submit {
            grid-column: span 2;
        }

        .mainv1-dashboard {
            grid-template-columns: minmax(0, 1fr);
        }

        .mainv1-overview-row > .col-md-6 {
            width: 100%;
            max-width: 100%;
            order: 3;
        }

        .mainv1-total-list {
            grid-template-columns: 1fr;
        }

        .mainv1-map-panel object {
            width: min(100%, 820px);
            height: clamp(420px, 72vw, 620px);
        }
    }

    @media (max-width: 576px) {
        .mainv1-metric-grid { grid-template-columns: 1fr; }

        .mainv1-overview-row > .col-md-3 {
            width: 100%;
            max-width: 100%;
        }

        .mainv1-overview-row > .col-md-3:first-child {
            order: 1;
        }

        .mainv1-overview-row > .col-md-3:last-child {
            order: 2;
        }

        .mainv1-overview-row > .col-md-6 {
            order: 3;
        }

        .mainv1-row {
            display: flex !important;
            flex-direction: column;
            align-items: flex-start !important;
            gap: 1rem;
        }

        .mainv1-row > :last-child {
            align-self: stretch;
            margin-left: 0;
        }

        .mainv1-row > :first-child {
            flex: 0 0 auto;
            width: 100%;
            max-width: 100%;
        }

        .mainv1-brand {
            min-height: 0;
            padding: 0.5rem 0;
            text-align: center;
            flex-direction: column;
            gap: 0.75rem;
            align-items: center;
        }

        .mainv1-logo {
            width: min(320px, 82%);
            max-width: 420px;
            flex-basis: auto;
        }

        .mainv1-brand-copy {
            max-width: 320px;
            text-align: center;
        }

        .mainv1-brand-rule {
            margin-right: auto;
            margin-left: auto;
        }

        .mainv1-filter {
            padding: 1rem 1rem 1.5rem;
            margin-top: 0;
        }

        .mainv1-filter-grid {
            grid-template-columns: 1fr;
        }

        .mainv1-multi-select[data-filter="title"] {
            grid-column: auto;
        }

        .mainv1-row {
            gap: 1rem;
        }

        .mainv1-filter-heading,
        .mainv1-filter-actions {
            align-items: flex-start;
            flex-direction: column;
        }

        .mainv1-multi-menu {
            min-width: 0;
            width: 100%;
        }

        .mainv1-multi-panel {
            grid-template-columns: 1fr;
        }

        .mainv1-multi-selected {
            min-height: 0;
            max-height: 110px;
        }

        .mainv1-dashboard {
            grid-template-columns: 1fr;
        }

        .mainv1-total-list {
            grid-template-columns: 1fr;
        }

        .mainv1-total-card {
            min-height: 104px;
            padding: 0.55rem;
        }

        .mainv1-total-card strong {
            font-size: 2.4rem !important;
        }

        .mainv1-map-panel object {
            height: min(100vw, 520px);
        }

        .mainv1-region-panel {
            height: auto;
            max-height: none;
        }

        .mainv1-regional-card {
            width: 155px;
            min-height: 180px;
        }

        .mainv1-regional-track {
            height: 320px;
        }

        .mainv1-regional-card img {
            height: 135px;
        }

        .mainv1-gallery-marquee {
            animation-duration: 500s;
        }

        .mainv1-category-card:hover,
        .mainv1-category-card:focus-visible,
        .mainv1-category-card.is-gallery-active {
            flex-basis: 310px !important;
            height: 180px;
            min-height: 180px;
        }

        .mainv1-category-card:hover .mainv1-category-content,
        .mainv1-category-card:focus-visible .mainv1-category-content,
        .mainv1-category-card.is-gallery-active .mainv1-category-content {
            width: 170px !important;
        }
    }
    
</style>

<div class="content-body" style="padding: 100px;">
    @php
        $mainv1Items = collect($regionItems ?? []);
        $mainv1FilterItems = collect($filterItems ?? $regionItems ?? []);
        $mainv1ReplicationTitles = $mainv1FilterItems->pluck('title')->filter()->unique()->sort()->values();
        $mainv1DashboardItems = $mainv1Items;
        $mainv1FilterData = $mainv1FilterItems->map(fn ($item) => [
            'region' => $item->region?->name,
            'province' => $item->province,
            'municipality' => $item->municipality,
            'year' => $item->year_of_moa,
            'title' => $item->title,
            'operational_status' => $item->operational_status ?: 'Operational',
        ])->values();
        $mainv1RegionCounts = $mainv1DashboardItems
            ->filter(fn ($item) => filled($item->region?->name))
            ->groupBy(fn ($item) => $item->region->name)
            ->map->count()
            ->sortKeys();
        $mainv1Truthy = fn ($value) => is_bool($value)
            ? $value
            : strtoupper(trim((string) $value)) === 'TRUE';
        $mainv1Active = fn ($item) => strtolower(trim((string) ($item->status ?? ''))) === 'ongoing';
        $mainv1Inactive = fn ($item) => in_array(
            strtolower(trim((string) ($item->status ?? ''))),
            ['inactive', 'dissolved'],
            true
        );
        $mainv1RegionColors = [
            'FO I' => '#ffb74d', 'FO CAR' => '#9575cd', 'FO II' => '#4db6ac',
            'FO III' => '#81c784', 'FO IV-A' => '#f06292', 'FO IV-B' => '#64b5f6',
            'FO NCR' => '#ff8a65', 'FO V' => '#ba68c8', 'FO VI' => '#aed581',
            'FO VII' => '#4fc3f7', 'FO VIII' => '#ffcc80', 'FO IX' => '#ce93d8',
            'FO X' => '#80cbc4', 'FO XI' => '#ffab91', 'FO XII' => '#9fa8da',
            'FO CARAGA' => '#a5d6a7',
        ];
    @endphp
    <div class="mainv1-row d-flex justify-content-between align-items-center w-100">
        <div class="mainv1-brand">
            <img class="mainv1-logo" src="{{ asset('images/dattachments/DSWD STB Bagong Pil logo.png') }}" alt="DSWD STB Bagong Pil logo">
            <div class="mainv1-brand-copy">
                <span class="mainv1-brand-kicker">Social Technology Bureau</span>
                <h1 class="mainv1-brand-title">Social Technologies Dashboard</h1>
            </div>
        </div>
        <div class="pe-4 ps-3">
            <form class="mainv1-filter is-collapsed" aria-label="Filter region items" method="GET" action="{{ auth()->check() ? route('main') : route('landing') }}">
                <div class="mainv1-filter-heading">
                    <h2>Dashboard Filters</h2>
                    <button class="mainv1-filter-toggle" type="button" aria-expanded="false" aria-controls="mainv1Filters">Show filters</button>
                </div>
                <div class="mainv1-filter-grid" id="mainv1Filters" hidden>
                    @foreach([
                        ['key' => 'region', 'label' => 'Region', 'placeholder' => 'All regions'],
                        ['key' => 'province', 'label' => 'Province', 'placeholder' => 'All provinces'],
                        ['key' => 'municipality', 'label' => 'Municipality', 'placeholder' => 'All municipalities'],
                        ['key' => 'year', 'label' => 'Year of MOA', 'placeholder' => 'All years'],
                        ['key' => 'title', 'label' => 'Social Technology Item', 'placeholder' => 'All items'],
                    ] as $filter)
                        <div class="mainv1-multi-select" data-filter="{{ $filter['key'] }}">
                            <label>{{ $filter['label'] }}</label>
                            <button class="mainv1-multi-trigger" type="button" aria-expanded="false">{{ $filter['placeholder'] }}</button>
                            <div class="mainv1-multi-menu" role="group" aria-label="{{ $filter['label'] }} options">
                                <div class="mainv1-multi-panel">
                                    <div>
                                        <p class="mainv1-multi-panel-title">Selected {{ $filter['label'] }}</p>
                                        <div class="mainv1-multi-selected"></div>
                                    </div>
                                    <div>
                                        <p class="mainv1-multi-panel-title">Search {{ $filter['label'] }}</p>
                                        <input class="mainv1-multi-search" type="search" placeholder="Type to filter {{ strtolower($filter['label']) }}" aria-label="Search {{ $filter['label'] }}">
                                        <div class="mainv1-multi-options"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mainv1-selection-list"></div>
                        </div>
                    @endforeach
                    <div class="mainv1-filter-submit">
                        <label for="mainv1Apply">&nbsp;</label>
                        <button class="mainv1-apply" type="submit" id="mainv1Apply">Apply filters</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script>
        (() => {
            const filterPanel = document.querySelector('.mainv1-filter');
            const filterToggle = filterPanel?.querySelector('.mainv1-filter-toggle');
            const filterGrid = document.getElementById('mainv1Filters');
            if (!filterPanel || !filterToggle || !filterGrid) return;
            const syncFilterState = () => {
                const isCollapsed = filterPanel.classList.contains('is-collapsed');
                filterGrid.hidden = isCollapsed;
                filterToggle.setAttribute('aria-expanded', String(!isCollapsed));
                filterToggle.firstChild.textContent = isCollapsed ? 'Show filters' : 'Hide filters';
            };
            syncFilterState();
            filterToggle.addEventListener('click', () => {
                filterPanel.classList.toggle('is-collapsed');
                syncFilterState();
            });
        })();
    </script>
    @php
        $mainv1TotalCount = $mainv1DashboardItems->count();
        $mainv1ExprCount = $mainv1DashboardItems->filter(fn ($item) => $mainv1Truthy($item->with_expr))->count();
        $mainv1ResolutionCount = $mainv1DashboardItems->filter(fn ($item) => $mainv1Truthy($item->with_res))->count();
        $mainv1MoaCount = $mainv1DashboardItems->filter(fn ($item) => $mainv1Truthy($item->with_moa))->count();
        $mainv1ActiveCount = $mainv1DashboardItems->filter($mainv1Active)->count();
        $mainv1InactiveCount = $mainv1DashboardItems->filter($mainv1Inactive)->count();
        $mainv1ReplicatedCount = $mainv1DashboardItems->filter(fn ($item) => $mainv1Truthy($item->with_replicated))->count();
        $mainv1AdoptedCount = $mainv1DashboardItems->filter(fn ($item) => $mainv1Truthy($item->with_adopted))->count();
        $mainv1AdoptedReplicatedTotal = $mainv1TotalCount;
        $mainv1MetricPercent = fn ($value) => $mainv1AdoptedReplicatedTotal > 0
            ? min(100, round(($value / $mainv1AdoptedReplicatedTotal) * 100))
            : 0;
        $mainv1RegionLocations = $mainv1DashboardItems->map(fn ($item) => [
            'region' => $item->region?->name,
            'province' => $item->province,
            'city' => $item->municipality,
        ])->values();
    @endphp
    <div class="mainv1-overview-row">
        <div class="mainv1-metric-grid" aria-label="ST inventory metrics">
            <div class="mainv1-total-list">
                <div class="mainv1-total-card mainv1-metric-card mainv1-metric-trigger" data-mainv1-metric="all" role="button" tabindex="0" aria-label="View all adopted and replicated social technologies">
                    <span>TOTAL ADOPTED AND REPLICATED</span>
                    <div class="mainv1-metric-value"><strong>{{ $mainv1TotalCount }}</strong><span class="mainv1-metric-percent">{{ $mainv1MetricPercent($mainv1TotalCount) }}%</span></div>
                    <div class="mainv1-metric-progress" aria-hidden="true" style="--mainv1-metric-progress: {{ $mainv1MetricPercent($mainv1TotalCount) }}%;"><span></span></div>
                    <span class="mainv1-metric-caption">Complete inventory of records</span>
                </div>
                <div class="mainv1-total-card mainv1-metric-card mainv1-metric-trigger" data-mainv1-metric="expr" role="button" tabindex="0" aria-label="View social technologies with expression of interest">
                    <span>TOTAL EXPRESSION OF INTEREST</span>
                    <div class="mainv1-metric-value"><strong>{{ $mainv1ExprCount }}</strong><span class="mainv1-metric-percent">{{ $mainv1MetricPercent($mainv1ExprCount) }}%</span></div>
                    <div class="mainv1-metric-progress" aria-hidden="true" style="--mainv1-metric-progress: {{ $mainv1MetricPercent($mainv1ExprCount) }}%;"><span></span></div>
                    <span class="mainv1-metric-caption">of adopted and replicated records</span>
                </div>
                <div class="mainv1-total-card mainv1-metric-card mainv1-metric-trigger" data-mainv1-metric="res" role="button" tabindex="0" aria-label="View social technologies with SB resolution">
                    <span>TOTAL SB RESOLUTION</span>
                    <div class="mainv1-metric-value"><strong>{{ $mainv1ResolutionCount }}</strong><span class="mainv1-metric-percent">{{ $mainv1MetricPercent($mainv1ResolutionCount) }}%</span></div>
                    <div class="mainv1-metric-progress" aria-hidden="true" style="--mainv1-metric-progress: {{ $mainv1MetricPercent($mainv1ResolutionCount) }}%;"><span></span></div>
                    <span class="mainv1-metric-caption">of adopted and replicated records</span>
                </div>
                <div class="mainv1-total-card mainv1-metric-card mainv1-metric-trigger" data-mainv1-metric="moa" role="button" tabindex="0" aria-label="View social technologies with memorandum of agreement">
                    <span>TOTAL MEMORANDUM OF AGREEMENT</span>
                    <div class="mainv1-metric-value"><strong>{{ $mainv1MoaCount }}</strong><span class="mainv1-metric-percent">{{ $mainv1MetricPercent($mainv1MoaCount) }}%</span></div>
                    <div class="mainv1-metric-progress" aria-hidden="true" style="--mainv1-metric-progress: {{ $mainv1MetricPercent($mainv1MoaCount) }}%;"><span></span></div>
                    <span class="mainv1-metric-caption">of adopted and replicated records</span>
                </div>
                <div class="mainv1-total-card mainv1-metric-card mainv1-secondary-card mainv1-metric-trigger" data-mainv1-metric="active" role="button" tabindex="0" aria-label="View active region social technologies">
                    <span class="mainv1-metric-icon" aria-hidden="true"><i class="bi bi-activity"></i></span>
                    <span class="mainv1-metric-label">Active</span>
                    <div class="mainv1-metric-value"><strong>{{ $mainv1ActiveCount }}</strong><span class="mainv1-metric-percent">{{ $mainv1MetricPercent($mainv1ActiveCount) }}%</span></div>
                    <div class="mainv1-metric-progress" aria-hidden="true" style="--mainv1-metric-progress: {{ $mainv1MetricPercent($mainv1ActiveCount) }}%;"><span></span></div>
                    <span class="mainv1-metric-caption">of adopted and replicated records</span>
                </div>
                <div class="mainv1-total-card mainv1-metric-card mainv1-secondary-card mainv1-metric-trigger" data-mainv1-metric="inactive" role="button" tabindex="0" aria-label="View inactive region social technologies">
                    <span class="mainv1-metric-icon" aria-hidden="true"><i class="bi bi-pause-circle-fill"></i></span>
                    <span class="mainv1-metric-label">Inactive</span>
                    <div class="mainv1-metric-value"><strong>{{ $mainv1InactiveCount }}</strong><span class="mainv1-metric-percent">{{ $mainv1MetricPercent($mainv1InactiveCount) }}%</span></div>
                    <div class="mainv1-metric-progress" aria-hidden="true" style="--mainv1-metric-progress: {{ $mainv1MetricPercent($mainv1InactiveCount) }}%;"><span></span></div>
                    <span class="mainv1-metric-caption">of adopted and replicated records</span>
                </div>
                <div class="mainv1-total-card mainv1-metric-card mainv1-secondary-card mainv1-metric-trigger" data-mainv1-metric="replicated" role="button" tabindex="0" aria-label="View replicated social technologies">
                    <span class="mainv1-metric-icon" aria-hidden="true"><i class="bi bi-arrow-repeat"></i></span>
                    <span class="mainv1-metric-label">Replicated</span>
                    <div class="mainv1-metric-value"><strong>{{ $mainv1ReplicatedCount }}</strong><span class="mainv1-metric-percent">{{ $mainv1MetricPercent($mainv1ReplicatedCount) }}%</span></div>
                    <div class="mainv1-metric-progress" aria-hidden="true" style="--mainv1-metric-progress: {{ $mainv1MetricPercent($mainv1ReplicatedCount) }}%;"><span></span></div>
                    <span class="mainv1-metric-caption">of adopted and replicated records</span>
                </div>
                <div class="mainv1-total-card mainv1-metric-card mainv1-secondary-card mainv1-metric-trigger" data-mainv1-metric="adopted" role="button" tabindex="0" aria-label="View adopted social technologies">
                    <span class="mainv1-metric-icon" aria-hidden="true"><i class="bi bi-check-circle-fill"></i></span>
                    <span class="mainv1-metric-label">Adopted</span>
                    <div class="mainv1-metric-value"><strong>{{ $mainv1AdoptedCount }}</strong><span class="mainv1-metric-percent">{{ $mainv1MetricPercent($mainv1AdoptedCount) }}%</span></div>
                    <div class="mainv1-metric-progress" aria-hidden="true" style="--mainv1-metric-progress: {{ $mainv1MetricPercent($mainv1AdoptedCount) }}%;"><span></span></div>
                    <span class="mainv1-metric-caption">of adopted and replicated records</span>
                </div>
            </div>
        </div>
        <div class="mainv1-overview-dashboard">
            <section class="mainv1-dashboard" aria-label="ST inventory overview">
                <div class="mainv1-map-panel">
                    <h2>PHILIPPINES MAP &amp; REGIONS</h2>
                    <div class="mainv1-svg-map-wrap" id="mainv1SvgMapWrap">
                        <div class="mainv1-map-controls" aria-label="Map controls">
                            <button class="mainv1-map-control" id="mainv1SvgMapZoomOut" type="button" aria-label="Zoom out" title="Zoom out">&minus;</button>
                            <button class="mainv1-map-control" id="mainv1SvgMapReset" type="button" aria-label="Reset map view" title="Reset map view">&#8634;</button>
                            <button class="mainv1-map-control" id="mainv1SvgMapZoomIn" type="button" aria-label="Zoom in" title="Zoom in">+</button>
                        </div>
                        <div class="mainv1-svg-map-stage" id="mainv1SvgMapStage">
                            <object data="{{ asset('images/philippines.svg') }}" type="image/svg+xml" aria-label="Interactive Philippines map"></object>
                        </div>
                    </div>
                    <p id="mainv1MapHint">Select a region on the map or in the list to view records</p>
                </div>
                <div class="mainv1-region-panel">
                    <h2>Regions (Social Technologies)</h2>
                    @foreach($mainv1RegionCounts as $regionName => $count)
                        <div class="mainv1-region-row" role="button" tabindex="0" aria-label="View {{ $regionName }} on the map">
                            <div class="mainv1-region-row-main">
                                <span class="mainv1-region-color-dot" style="background: {{ $mainv1RegionColors[$regionName] ?? '#cbd5e1' }}"></span>
                                <span>{{ $regionName }}</span>
                            </div>
                            <strong>{{ $count }}</strong>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
    <div class="mainv1-metric-modal" id="mainv1MetricModal" role="dialog" aria-modal="true" aria-labelledby="mainv1MetricModalTitle" aria-hidden="true">
        <div class="mainv1-metric-dialog">
            <div class="mainv1-metric-modal-header">
                <div><span>Social Technologies</span><h2 id="mainv1MetricModalTitle">Metric records</h2><p id="mainv1MetricModalSummary"></p></div>
                <div class="mainv1-metric-modal-header-actions">
                    <button class="mainv1-metric-replicate" id="mainv1MetricReplicate" type="button">Replicate Program?</button>
                    <button class="mainv1-metric-modal-close" type="button" aria-label="Close metric records">&times;</button>
                </div>
            </div>
            @if(auth()->check() && auth()->user()->usergroup === 'sysadmin')
                <form class="mainv1-replication-settings mainv1-metric-replication-settings" method="POST" action="{{ route('settings.replication-redirect.update') }}">
                    @csrf
                    <label for="mainv1MetricReplicationRedirectUrl">Replication redirect destination
                        <input id="mainv1MetricReplicationRedirectUrl" name="replication_redirect_url" type="text" inputmode="url" value="{{ old('replication_redirect_url', $replicationRedirectUrl) }}" placeholder="youtube.com or https://example.com/replicate" maxlength="2048">
                    </label>
                    <button type="submit">Save destination</button>
                </form>
            @endif
            <div class="mainv1-metric-filters" aria-label="Filter metric records">
                <input id="mainv1MetricTitleSearch" type="search" placeholder="Search ST title" aria-label="Search ST title">
                <select id="mainv1MetricProvince" aria-label="Filter by province"><option value="">All provinces</option></select>
                <select id="mainv1MetricMunicipality" aria-label="Filter by city or municipality"><option value="">All cities / municipalities</option></select>
                <select id="mainv1MetricYear" aria-label="Filter by year of MOA"><option value="">All years</option></select>
                <button type="button" id="mainv1MetricExport">Export CSV</button>
            </div>
            <div class="mainv1-metric-list-wrap"><div class="mainv1-metric-list" id="mainv1MetricList"></div></div>
        </div>
    </div>
    <div class="mainv1-replication-confirm-modal"
     id="mainv1ReplicationConfirmModal"
     role="dialog"
     aria-modal="true"
     aria-labelledby="mainv1ReplicationConfirmTitle"
     aria-describedby="mainv1ReplicationConfirmMessage"
     aria-hidden="true">

    <div class="mainv1-replication-confirm-dialog">

        <div class="mainv1-replication-confirm-icon">
            <i class="bi bi-copy"></i>
        </div>

        <h2 id="mainv1ReplicationConfirmTitle">
            Confirm Replication
        </h2>

        <p id="mainv1ReplicationConfirmMessage">
            You are about to replicate this Social Technology record.
            This action will redirect you to the replication destination.
        </p>

        <form id="mainv1ReplicationRecordForm" method="POST" action="{{ route('replication-records.store') }}">
            @csrf
            <label class="mainv1-replication-select-label" for="mainv1ReplicationTitle">Social Technology you want to replicate</label>
            <select id="mainv1ReplicationTitle" name="social_technology_title" required>
                <option value="">Select a Social Technology</option>
                @foreach($mainv1ReplicationTitles as $replicationTitle)
                    <option value="{{ $replicationTitle }}">{{ $replicationTitle }}</option>
                @endforeach
            </select>
            <p class="mainv1-replication-field-error" id="mainv1ReplicationTitleError" role="alert" hidden>Please select a Social Technology program.</p>
        </form>

        <div class="mainv1-replication-info">
            <span>
                <i class="bi bi-info-circle"></i>
                Please review the destination details before proceeding.
            </span>
        </div>

        <div class="mainv1-replication-confirm-actions">
            <button type="button"
                    class="mainv1-replication-cancel"
                    id="mainv1ReplicationCancel">
            </button>

            <button type="button"
                    class="mainv1-replication-continue"
                    id="mainv1ReplicationContinue">
                Continue Replication
                <i class="bi bi-arrow-right"></i>
            </button>
        </div>

    </div>

</div>
    <div class="mainv1-st-detail-modal" id="mainv1StDetailModal" role="dialog" aria-modal="true" aria-labelledby="mainv1StDetailTitle" aria-hidden="true">
        <div class="mainv1-st-detail-dialog">
            <header class="mainv1-st-detail-header"><h2 id="mainv1StDetailTitle">ST Details</h2><button type="button" class="mainv1-st-detail-close" aria-label="Close ST details">&times;</button></header>
            <div class="mainv1-st-detail-body" id="mainv1StDetailBody"></div>
        </div>
    </div>
    <div class="mainv1-attachment-modal" id="mainv1AttachmentModal" role="dialog" aria-modal="true" aria-labelledby="mainv1AttachmentTitle" aria-hidden="true">
        <div class="mainv1-attachment-dialog">
            <header class="mainv1-st-detail-header"><div><h2 id="mainv1AttachmentTitle">Attachment</h2><p id="mainv1AttachmentFilename"></p></div><button type="button" class="mainv1-st-detail-close" aria-label="Close attachment">&times;</button></header>
            <iframe id="mainv1AttachmentFrame" title="Social technology attachment"></iframe>
        </div>
    </div>
    <section class="mainv1-category-track pt-5" aria-label="Social technology categories">
        <h2 class="mainv1-section-title" id="mainv1SectorsTitle">Sectors</h2>
        <div class="mainv1-gallery-marquee">
            @for($copy = 0; $copy < 16; $copy++)
                @foreach($galleryCards as $galleryCard)
                    @php
                        $galleryHierarchy = $galleryCard->children->map(function ($child) {
                            return [
                                'title' => $child->title,
                                'description' => $child->description,
                                'url' => $child->url,
                                'children' => $child->children->map(function ($subchild) {
                                    return [
                                        'title' => $subchild->title,
                                        'description' => $subchild->description,
                                        'url' => $subchild->url,
                                    ];
                                })->values(),
                            ];
                        })->values();
                    @endphp
                    <a class="mainv1-category-card" href="{{ $galleryCard->url ?: '#' }}" data-gallery='@json(['title' => $galleryCard->title, 'children' => $galleryHierarchy])' aria-label="{{ $galleryCard->title }}">
                        @if($galleryCard->image)
                            <img src="{{ asset('storage/' . $galleryCard->image) }}" alt="{{ $galleryCard->title }} logo">
                        @elseif($galleryCard->icon_class)
                            <i class="{{ $galleryCard->icon_class }}" style="font-size:48px;color:#4da1f7;"></i>
                        @else
                            <span aria-hidden="true"></span>
                        @endif
                        <strong class="mainv1-category-title">{{ $galleryCard->title }}</strong>
                        <div class="mainv1-category-content">
                            <strong>{{ $galleryCard->title }}</strong>
                            <p class="mainv1-category-children">{{ $galleryCard->description }}</p>
                        </div>
                    </a>
                @endforeach
            @endfor
        </div>
    </section>
    <div class="mainv1-gallery-popover-backdrop" data-gallery-close></div>
    <section class="mainv1-gallery-popover" aria-modal="true" role="dialog" aria-labelledby="mainv1GalleryPopoverTitle">
        <div class="mainv1-gallery-popover-header">
            <h2 id="mainv1GalleryPopoverTitle"></h2>
            <button class="mainv1-gallery-popover-close" type="button" aria-label="Close" data-gallery-close>&times;</button>
        </div>
        <div id="mainv1GalleryPopoverBody"></div>
    </section>
    
    
    @php
        $mainv1RegionalCards = [
            ['image' => '1.png', 'name' => 'FO I', 'region' => 'Region I'],
            ['image' => '2.png', 'name' => 'FO II', 'region' => 'Region II'],
            ['image' => '3.png', 'name' => 'FO III', 'region' => 'Region III'],
            ['image' => '4_a.png', 'name' => 'FO IV-A', 'region' => 'Region IV-A'],
            ['image' => '4_b.png', 'name' => 'FO IV-B', 'region' => 'Region IV-B'],
            ['image' => '5.png', 'name' => 'FO V', 'region' => 'Region V'],
            ['image' => '6.png', 'name' => 'FO VI', 'region' => 'Region VI'],
            ['image' => '7.png', 'name' => 'FO VII', 'region' => 'Region VII'],
            ['image' => '8.png', 'name' => 'FO VIII', 'region' => 'Region VIII'],
            ['image' => '9.png', 'name' => 'FO IX', 'region' => 'Region IX'],
            ['image' => '10.png', 'name' => 'FO X', 'region' => 'Region X'],
            ['image' => '11.png', 'name' => 'FO XI', 'region' => 'Region XI'],
            ['image' => '12.png', 'name' => 'FO XII', 'region' => 'Region XII'],
            ['image' => '13.png', 'name' => 'FO CARAGA', 'region' => 'CARAGA'],
            ['image' => 'car.png', 'name' => 'FO CAR', 'region' => 'CAR'],
            ['image' => 'ncr.png', 'name' => 'FO NCR', 'region' => 'NCR'],
            ['image' => 'barmm.png', 'name' => 'BARMM', 'region' => 'BARMM'],
        ];
        $mainv1RegionRows = $filterItems->map(fn ($item) => [
            'region' => $item->region?->name,
            'title' => $item->title,
            'province' => $item->province,
            'municipality' => $item->municipality,
            'year' => $item->year_of_moa,
            'expr' => $item->with_expr,
            'moa' => $item->with_moa,
            'res' => $item->with_res,
            'adopted' => $item->with_adopted,
            'replicated' => $item->with_replicated,
            'status' => $item->status,
            'operational_status' => $item->operational_status ?: 'Operational',
        ])->values();
    @endphp
    <section class="mainv1-regional-overview" aria-labelledby="mainv1RegionalOverviewTitle">
        <h2 class="mainv1-section-title" id="mainv1RegionalOverviewTitle">Regional Overview</h2>
        <div class="mainv1-carousel-shell" data-regional-carousel>
            <button class="mainv1-carousel-control" type="button" data-carousel-direction="prev" aria-label="Previous regions" title="Previous regions">&#8249;</button>
            <div class="mainv1-regional-track" tabindex="0">
                @foreach($mainv1RegionalCards as $card)
                    <a class="mainv1-regional-card" href="{{ route('landing', ['region[]' => $card['region']]) }}" data-region-name="{{ $card['region'] }}" data-region-image="{{ asset('images/ST Regional Nav Slide/' . $card['image']) }}" aria-label="View {{ $card['name'] }}" onclick="if (window.openMainv1RegionModal) { event.preventDefault(); window.openMainv1RegionModal(this); return false; }">
                        <img src="{{ asset('images/ST Regional Nav Slide/' . $card['image']) }}" alt="{{ $card['name'] }} map">
                        <strong>{{ $card['name'] }}</strong>
                    </a>
                @endforeach
            </div>
            <button class="mainv1-carousel-control" type="button" data-carousel-direction="next" aria-label="Next regions" title="Next regions">&#8250;</button>
        </div>
    </section>
    <div class="mainv1-region-modal" id="mainv1RegionModal" role="dialog" aria-modal="true" aria-labelledby="mainv1RegionModalTitle">
        <div class="mainv1-region-dialog">
            <button class="mainv1-region-modal-close" type="button" data-close-region-modal aria-label="Close region overview">&times;</button>
            <header class="mainv1-region-modal-header">
                <h2 id="mainv1RegionModalTitle">Region Overview</h2>
                @if(auth()->check() && auth()->user()->usergroup === 'sysadmin')
                    <form class="mainv1-replication-settings" method="POST" action="{{ route('settings.replication-redirect.update') }}">
                        @csrf
                        <label for="mainv1ReplicationRedirectUrl">Replication redirect destination
                            <input id="mainv1ReplicationRedirectUrl" name="replication_redirect_url" type="text" inputmode="url" value="{{ old('replication_redirect_url', $replicationRedirectUrl) }}" placeholder="youtube.com or https://example.com/replicate" maxlength="2048">
                        </label>
                        <button type="submit">Save destination</button>
                    </form>
                @endif
            </header>
            <div class="mainv1-region-modal-body">
                <section class="mainv1-region-panel left-panel">
                    <img class="mainv1-region-map" id="mainv1RegionModalImage" alt="Selected region map">
                    <div class="mainv1-region-chart">
                        <h3 class="mainv1-region-chart-title">Region Metrics</h3>
                        <canvas id="mainv1RegionChart" width="480" height="250" aria-label="Region metrics chart"></canvas>
                    </div>
                </section>
                <section class="mainv1-region-panel">
                    <h3 class="mainv1-region-panel-title">Filters</h3>
                    <div class="mainv1-region-filter"><span>Province</span><div class="mainv1-region-select" data-region-filter="province"><button class="mainv1-region-select-trigger" type="button">All provinces</button><div class="mainv1-region-select-menu"><div><h4 class="mainv1-region-select-heading">Selected province</h4><div class="mainv1-region-select-selected"></div></div><div><h4 class="mainv1-region-select-heading">Search province</h4><input class="mainv1-region-select-search" type="search" placeholder="Type to filter province"><div class="mainv1-region-select-options"></div></div></div></div></div>
                    <div class="mainv1-region-filter"><span>Municipality</span><div class="mainv1-region-select" data-region-filter="municipality"><button class="mainv1-region-select-trigger" type="button">All municipalities</button><div class="mainv1-region-select-menu"><div><h4 class="mainv1-region-select-heading">Selected municipality</h4><div class="mainv1-region-select-selected"></div></div><div><h4 class="mainv1-region-select-heading">Search municipality</h4><input class="mainv1-region-select-search" type="search" placeholder="Type to filter municipality"><div class="mainv1-region-select-options"></div></div></div></div></div>
                    <div class="mainv1-region-filter"><span>Year of MOA</span><div class="mainv1-region-select" data-region-filter="year"><button class="mainv1-region-select-trigger" type="button">All years</button><div class="mainv1-region-select-menu"><div><h4 class="mainv1-region-select-heading">Selected year</h4><div class="mainv1-region-select-selected"></div></div><div><h4 class="mainv1-region-select-heading">Search year</h4><input class="mainv1-region-select-search" type="search" placeholder="Type to filter year"><div class="mainv1-region-select-options"></div></div></div></div></div>
                    <div class="mainv1-region-status-grid">
                        <div class="mainv1-region-status ongoing">Active<strong id="mainv1RegionOngoing">0</strong></div>
                        <div class="mainv1-region-status inactive">Inactive<strong id="mainv1RegionInactive">0</strong></div>
                    </div>
                    <div class="mainv1-region-metric-grid" id="mainv1RegionMetrics"></div>
                </section>
                <section class="mainv1-region-panel title-panel">
                    <h3 class="mainv1-region-panel-title" id="mainv1RegionTitlesHeading">ST Titles</h3>
                    <div class="mainv1-region-title-summary"><span id="mainv1RegionUniqueTitles">Unique titles: 0</span><span id="mainv1RegionTotalSts">Total STs: 0</span></div>
                    <div class="mainv1-region-title-list" id="mainv1RegionTitleList"></div>
                </section>
            </div>
        </div>
    </div>
</div>
@php
    $mainv1AnalyticsRows = $mainv1DashboardItems->map(fn ($item) => [
        'title' => $item->title,
        'region' => $item->region?->name,
        'province' => $item->province,
        'municipality' => $item->municipality,
        'year' => $item->year_of_moa,
        'status' => strtolower(trim((string) ($item->status ?? ''))),
        'operational_status' => $item->operational_status ?: 'Operational',
        'expr' => $mainv1Truthy($item->with_expr),
        'res' => $mainv1Truthy($item->with_res),
        'moa' => $mainv1Truthy($item->with_moa),
        'replicated' => $mainv1Truthy($item->with_replicated),
        'adopted' => $mainv1Truthy($item->with_adopted),
        'included_aip' => $mainv1Truthy($item->included_aip),
        'inactive_status' => $item->inactive_status,
        'inactive_remarks' => $item->inactive_remarks,
        'year_of_moa' => $item->year_of_moa,
        'year_of_resolution' => $item->year_of_resolution,
        'attachment_url' => $item->attachment_url,
        'attachment_filename' => $item->attachment_filename,
    ])->values();
@endphp
<div class="mainv1-analytics-shell" style="padding:100px;">
    <section class="mainv1-analytics" aria-labelledby="mainv1AnalyticsTitle">
        <div class="mainv1-analytics-heading">
            <div>
                <span class="mainv1-analytics-eyebrow">Trend overview</span>
                <h2 id="mainv1AnalyticsTitle">Social Technology Analytics</h2>
            </div>
            <div class="mainv1-analytics-heading-tools">
                <div class="mainv1-analytics-badge"><span class="mainv1-live-dot"></span><span id="mainv1AnalyticsCount">{{ $mainv1AnalyticsRows->count() }} filtered records</span></div>
                <div class="mainv1-view-tabs" role="tablist" aria-label="Analytics views">
                    <button type="button" class="mainv1-view-tab is-active" role="tab" aria-selected="true" aria-controls="mainv1-view-summary" data-mainv1-tab="summary">Summary</button>
                    <button type="button" class="mainv1-view-tab" role="tab" aria-selected="false" aria-controls="mainv1-view-trends" data-mainv1-tab="trends">Trends</button>
                    <button type="button" class="mainv1-view-tab" role="tab" aria-selected="false" aria-controls="mainv1-view-titles" data-mainv1-tab="titles">Titles</button>
                    <button type="button" class="mainv1-view-tab" role="tab" aria-selected="false" aria-controls="mainv1-view-geography" data-mainv1-tab="geography">Geography</button>
                </div>
            </div>
        </div>
    
        <div class="row g-3 mainv1-analytics-top p-2">
            <article class="col-md-6 mainv1-analytics-panel mainv1-trend-panel" data-mainv1-view="trends">
                <div class="mainv1-chart-wrap"><canvas id="mainv1StatusChart"></canvas></div>
            </article>
            <article class="col-md-6 mainv1-analytics-panel mainv1-title-count-panel" data-mainv1-view="titles">
                <div class="mainv1-title-composition">
                    <div class="mainv1-title-card mainv1-chart-wrap mainv1-title-count-chart-wrap"><div class="mainv1-title-card-heading"><span>Share by social technology</span></div><canvas id="mainv1TitleCountChart" aria-label="Social Technology title distribution chart"></canvas></div>
                    <div class="mainv1-title-card mainv1-title-reference"><div class="mainv1-title-reference-header"><span class="mainv1-title-reference-heading">Top titles by volume</span><span id="mainv1TitleReferenceCount" class="mainv1-title-reference-count"></span></div><div id="mainv1TitleCountLegend" class="mainv1-title-count-legend" aria-label="Social technology titles" aria-live="polite"></div><div class="mainv1-title-pagination"><button type="button" id="mainv1TitlePrevious" aria-label="Previous titles page">Previous</button><span id="mainv1TitlePage" aria-live="polite">Page 1 of 1</span><button type="button" id="mainv1TitleNext" aria-label="Next titles page">Next</button></div></div>
                </div>
            </article>
        </div>
    
        <div class="mainv1-analytics-grid mainv1-analytics-grid-secondary">
            <article class="mainv1-analytics-panel mainv1-year-panel" data-mainv1-view="trends">
                <div class="mainv1-panel-heading"><div><span>Distribution</span><h3>Year of MOAs</h3></div></div>
                <div class="mainv1-chart-wrap"><canvas id="mainv1YearChart"></canvas></div>
                <div class="mainv1-insight-strip"><div><span>Peak year</span><strong id="mainv1PeakYear">-</strong><small id="mainv1PeakMeta">No records yet</small></div><div><span>Average volume</span><strong id="mainv1AverageYear">-</strong><small>Records per year</small></div><div><span>Latest year</span><strong id="mainv1LatestYear">-</strong><small id="mainv1LatestMeta">No records yet</small></div></div>
            </article>
            <article class="mainv1-analytics-panel mainv1-share-panel" data-mainv1-view="summary"><div class="mainv1-panel-heading"><div><span>Share analysis</span><h3>Ongoing vs inactive</h3></div></div><div class="mainv1-donut-wrap"><canvas id="mainv1StatusDonut"></canvas></div><div class="mainv1-share-legend"><span><i class="legend-teal"></i>Ongoing <b id="mainv1StatusOngoingPercent">0%</b></span><span><i class="legend-rose"></i>Inactive <b id="mainv1StatusInactivePercent">0%</b></span></div><div class="mainv1-share-summary"><div class="mainv1-share-metrics"><div class="mainv1-share-stat share-stat-teal"><span>Ongoing</span><strong id="mainv1StatusOngoingValue">0</strong><small id="mainv1StatusOngoingSummary">0% of status records</small></div><div class="mainv1-share-stat share-stat-rose"><span>Inactive</span><strong id="mainv1StatusInactiveValue">0</strong><small id="mainv1StatusInactiveSummary">0% of status records</small></div></div><div class="mainv1-share-insight"><span>Current lead</span><strong id="mainv1StatusLead">Awaiting summary</strong></div></div></article>
            <article class="mainv1-analytics-panel mainv1-share-panel" data-mainv1-view="summary"><div class="mainv1-panel-heading"><div><span>Share analysis</span><h3>Replicated vs adopted</h3></div></div><div class="mainv1-donut-wrap"><canvas id="mainv1AdoptionDonut"></canvas></div><div class="mainv1-share-legend"><span><i class="legend-blue"></i>Replicated <b id="mainv1ReplicatedPercent">0%</b></span><span><i class="legend-gold"></i>Adopted <b id="mainv1AdoptedPercent">0%</b></span></div><div class="mainv1-share-summary"><div class="mainv1-share-metrics"><div class="mainv1-share-stat share-stat-blue"><span>Replicated</span><strong id="mainv1ReplicatedValue">0</strong><small id="mainv1ReplicatedSummary">0% of replicated records</small></div><div class="mainv1-share-stat share-stat-gold"><span>Adopted</span><strong id="mainv1AdoptedValue">0</strong><small id="mainv1AdoptedSummary">0% of adoption records</small></div></div><div class="mainv1-share-insight"><span>Current lead</span><strong id="mainv1AdoptionLead">Awaiting summary</strong></div></div></article>
            <article class="mainv1-analytics-panel mainv1-coverage-panel" data-mainv1-view="summary"><div class="mainv1-panel-heading"><div><span>Overall totals</span><h3>Social technology coverage</h3></div></div><div id="mainv1Coverage" class="mainv1-coverage-list"></div></article>
        </div>
    
        <div class="mainv1-analytics-grid mainv1-analytics-grid-lower">
            <article class="mainv1-analytics-panel mainv1-heatmap-panel" data-mainv1-view="geography"><div class="mainv1-panel-heading"><div><span>Regional pattern</span><h3>Regional year activity</h3></div></div><div id="mainv1Heatmap" class="mainv1-heatmap"></div></article>
            <article class="mainv1-analytics-panel mainv1-ranking-panel" data-mainv1-view="geography"><div class="mainv1-panel-heading"><div><span>Geographic reach</span><h3>Top regions</h3></div></div><div id="mainv1TopRegions" class="mainv1-ranking-list"></div></article>
            <article class="mainv1-analytics-panel mainv1-ranking-panel" data-mainv1-view="geography"><div class="mainv1-panel-heading"><div><span>Local concentration</span><h3>Top provinces</h3></div></div><div id="mainv1TopProvinces" class="mainv1-ranking-list"></div></article>
        </div>
    
        <article class="mainv1-analytics-panel mainv1-directory-panel">
            <div class="mainv1-directory-heading">
                <div>
                    <span class="mainv1-analytics-eyebrow">Record directory</span>
                    <p>Filter the social technology records shown below.</p>
                </div>
                <div class="mainv1-directory-controls" aria-label="Filter directory records">
                    <input id="mainv1DirectorySearch" type="search" placeholder="Search title" aria-label="Search social technology title">
                    <button type="button" class="mainv1-directory-filter-toggle" id="mainv1DirectoryFilterToggle" aria-expanded="true" aria-controls="mainv1DirectoryAdvancedFilters">More filters</button>
                    <div class="mainv1-directory-advanced-controls" id="mainv1DirectoryAdvancedFilters">
                        <select id="mainv1DirectoryProvince" aria-label="Filter by province"><option value="">All provinces</option></select>
                        <select id="mainv1DirectoryMunicipality" aria-label="Filter by city or municipality"><option value="">All cities / municipalities</option></select>
                        <select id="mainv1DirectoryYear" aria-label="Filter by year of MOA"><option value="">All years</option></select>
                        <select id="mainv1DirectoryStatus" aria-label="Filter by status"><option value="">All statuses</option><option value="ongoing">Active</option><option value="inactive">Inactive</option></select>
                        <select id="mainv1DirectoryType" aria-label="Filter by coverage"><option value="">All coverage</option><option value="expr">Expression of Interest</option><option value="res">SB Resolution</option><option value="moa">MOA</option><option value="replicated">Replicated</option><option value="adopted">Adopted</option></select>
                        <button type="button" id="mainv1DirectoryExport">Export CSV</button>
                    </div>
                </div>
            </div>
            <div class="mainv1-directory-table-wrap"><table class="mainv1-directory-table"><thead><tr><th>Title</th><th>Province</th><th>City / Municipality</th><th>Status</th><th>Coverage</th><th>Attachment</th></tr></thead><tbody id="mainv1DirectoryRows"></tbody></table></div>
            <div class="mainv1-directory-footer"><span id="mainv1DirectorySummary"></span><div><button type="button" id="mainv1DirectoryPrev" aria-label="Previous page">&#8592; Prev</button><strong id="mainv1DirectoryPage">Page 1</strong><button type="button" id="mainv1DirectoryNext" aria-label="Next page">Next &#8594;</button></div></div>
        </article>
    </section>
</div>
<script>
(() => {
    const toggle = document.getElementById('mainv1DirectoryFilterToggle');
    const advanced = document.getElementById('mainv1DirectoryAdvancedFilters');
    if (!toggle || !advanced) return;
    const mobileViewport = window.matchMedia('(max-width: 1100px)');
    const syncDirectoryFilters = () => {
        const expanded = toggle.dataset.userExpanded === 'true' || !mobileViewport.matches;
        advanced.hidden = !expanded;
        toggle.setAttribute('aria-expanded', String(expanded));
        toggle.textContent = expanded ? 'Hide filters' : 'More filters';
    };
    toggle.addEventListener('click', () => {
        const expanded = advanced.hidden;
        toggle.dataset.userExpanded = String(expanded);
        advanced.hidden = !expanded;
        toggle.setAttribute('aria-expanded', String(expanded));
        toggle.textContent = expanded ? 'Hide filters' : 'More filters';
    });
    mobileViewport.addEventListener?.('change', () => {
        delete toggle.dataset.userExpanded;
        syncDirectoryFilters();
    });
    syncDirectoryFilters();
})();
</script>
<script>
(function () {
    const dashboard = document.querySelector('.mainv1-analytics');
    if (!dashboard) return;

    const tabs = dashboard.querySelectorAll('[data-mainv1-tab]');
    const panels = dashboard.querySelectorAll('[data-mainv1-view]');
    const directory = dashboard.querySelector('.mainv1-directory-panel');
    const viewRows = {};
    const viewOrder = ['summary', 'trends', 'titles', 'geography'];
    let hasSelectedView = false;
    let activeView = 'summary';
    viewOrder.forEach(function (view) {
        const row = document.createElement('div');
        row.className = 'mainv1-view-row mainv1-view-row-' + view;
        row.dataset.mainv1Row = view;
        viewRows[view] = row;
        if (directory) dashboard.insertBefore(row, directory);
        else dashboard.appendChild(row);
    });
    panels.forEach(function (panel) {
        if (viewRows[panel.dataset.mainv1View]) viewRows[panel.dataset.mainv1View].appendChild(panel);
    });
    dashboard.querySelectorAll('.mainv1-analytics-top, .mainv1-analytics-grid').forEach(function (grid) {
        if (!grid.children.length) grid.remove();
    });
    const selectView = function (view, updateHash) {
        const selectedView = ['summary', 'trends', 'titles', 'geography'].includes(view) ? view : 'summary';
        tabs.forEach(function (tab) {
            const selected = tab.dataset.mainv1Tab === selectedView;
            tab.classList.toggle('is-active', selected);
            tab.setAttribute('aria-selected', String(selected));
        });
        panels.forEach(function (panel) {
            panel.hidden = panel.dataset.mainv1View !== selectedView;
        });
        Object.keys(viewRows).forEach(function (view) {
            viewRows[view].hidden = view !== selectedView;
        });
        const selectedRow = viewRows[selectedView];
        if (hasSelectedView && selectedRow) {
            const direction = viewOrder.indexOf(selectedView) >= viewOrder.indexOf(activeView) ? 'from-right' : 'from-left';
            selectedRow.classList.remove('is-view-entering', 'is-view-from-left', 'is-view-from-right');
            selectedRow.classList.add('is-view-entering', 'is-view-' + direction);
            void selectedRow.offsetWidth;
            selectedRow.addEventListener('animationend', function () {
                selectedRow.classList.remove('is-view-entering', 'is-view-from-left', 'is-view-from-right');
            }, { once: true });
        }
        hasSelectedView = true;
        activeView = selectedView;
        dashboard.classList.toggle('mainv1-summary-active', selectedView === 'summary');
        if (updateHash) history.replaceState(null, '', '#' + selectedView);
        requestAnimationFrame(function () {
            if (!window.Chart) return;
            dashboard.querySelectorAll('[data-mainv1-view="' + selectedView + '"] canvas').forEach(function (canvas) {
                const chart = Chart.getChart ? Chart.getChart(canvas) : null;
                if (chart) chart.resize();
            });
        });
    };
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () { selectView(tab.dataset.mainv1Tab, true); });
    });
    selectView(window.location.hash.replace('#', '').toLowerCase(), false);
})();
</script>
<style>
.mainv1-analytics-heading-tools{display:flex;align-items:flex-end;gap:.75rem;flex-direction:column}
.mainv1-view-tabs{display:flex;gap:4px;padding:4px;border:1px solid #e2e7ed;border-radius:12px;background:#f1f2f5}
.mainv1-view-tab{border:0;border-radius:9px;padding:.62rem 1.1rem;background:transparent;color:#6c7079;font:inherit;font-size:.82rem;font-weight:700;cursor:pointer;transition:background-color .18s ease,color .18s ease,box-shadow .18s ease}
.mainv1-view-tab:hover,.mainv1-view-tab:focus-visible{background:#fff;color:#173d68;outline:none}
.mainv1-view-tab.is-active{background:#fff;color:#173d68;box-shadow:0 1px 4px rgba(23,50,77,.1)}
@media (min-width: 1101px) {
    .mainv1-view-row-titles .mainv1-title-count-panel{width:100%;max-width:none;flex:0 0 100%}
    .mainv1-view-row-trends .mainv1-trend-panel,
    .mainv1-view-row-trends .mainv1-year-panel{width:100%;max-width:none;flex:0 0 auto}
    .mainv1-view-row {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    .mainv1-view-row-trends {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .mainv1-view-row-titles {
        grid-template-columns: minmax(0, 1fr);
    }

    .mainv1-summary-active .mainv1-analytics-grid-secondary {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

.mainv1-analytics [data-mainv1-view][hidden]{display:none!important}

@media (min-width: 577px) and (max-width: 1100px) {
    .mainv1-analytics,
    .mainv1-analytics.mainv1-summary-active { padding: 30px !important; }
}

@media (max-width: 576px) {
    .mainv1-analytics,
    .mainv1-analytics.mainv1-summary-active { padding: 10px !important; }
}

.mainv1-view-row[hidden] {
    display: none !important;
}

.mainv1-view-row {
    min-height: 150px;
    align-items: start;
}

.mainv1-analytics-panel {
    min-height: 150px;
}

.mainv1-coverage-panel {
    align-self: stretch;
    display: flex;
    flex-direction: column;
}

.mainv1-coverage-panel .mainv1-coverage-list {
    flex: 1;
    grid-template-rows: repeat(7, minmax(0, 1fr));
    align-content: stretch;
}

.mainv1-coverage-panel .mainv1-coverage-item {
    min-height: 34px;
    font-size: .76rem;
}

.mainv1-coverage-panel .mainv1-coverage-item b {
    color: #315b7c;
    font-size: .8rem;
    font-weight: 800;
}

.mainv1-coverage-panel .mainv1-coverage-bar {
    height: 11px;
    border-radius: 999px;
    box-shadow: inset 0 1px 2px rgba(35, 75, 101, .08);
}

.mainv1-coverage-panel .mainv1-coverage-bar i {
    border-radius: inherit;
    box-shadow: 0 1px 3px rgba(27, 160, 155, .2);
}

@keyframes mainv1-view-enter {
    from { opacity: 0; transform: translate3d(0, 0, 0); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes mainv1-view-slide-right {
    from { opacity: 0; transform: translate3d(28px, 0, 0); }
    to { opacity: 1; transform: translate3d(0, 0, 0); }
}

@keyframes mainv1-view-slide-left {
    from { opacity: 0; transform: translate3d(-28px, 0, 0); }
    to { opacity: 1; transform: translate3d(0, 0, 0); }
}

.mainv1-view-row.is-view-entering {
    animation: mainv1-view-enter .42s cubic-bezier(.22, .61, .36, 1) both;
    will-change: opacity, transform;
}

.mainv1-view-row.is-view-entering.is-view-from-right { animation-name: mainv1-view-slide-right; }
.mainv1-view-row.is-view-entering.is-view-from-left { animation-name: mainv1-view-slide-left; }

.mainv1-view-row.is-view-entering > .mainv1-analytics-panel {
    animation: mainv1-panel-enter .38s cubic-bezier(.22, .61, .36, 1) both;
}

.mainv1-view-row.is-view-entering > .mainv1-analytics-panel:nth-child(2) { animation-delay: .05s; }
.mainv1-view-row.is-view-entering > .mainv1-analytics-panel:nth-child(3) { animation-delay: .1s; }

@keyframes mainv1-panel-enter {
    from { opacity: 0; transform: translate3d(0, 6px, 0) scale(.995); }
    to { opacity: 1; transform: translate3d(0, 0, 0) scale(1); }
}

@media (prefers-reduced-motion: reduce) {
    .mainv1-view-row.is-view-entering,
    .mainv1-view-row.is-view-entering > .mainv1-analytics-panel { animation: none; }
}

@media (max-width: 1100px) {
    .mainv1-analytics-heading-tools{width:100%;align-items:stretch}
    .mainv1-view-tabs{width:100%;box-sizing:border-box}
    .mainv1-view-tab{flex:1 1 0;padding:.55rem .65rem;font-size:.76rem}
    .mainv1-view-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .mainv1-view-row-summary {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .mainv1-view-row-trends {
        grid-template-columns: 1fr;
    }

    .mainv1-view-row-trends > .mainv1-trend-panel,
    .mainv1-view-row-trends > .mainv1-year-panel {
        width: 100%;
        max-width: none;
    }

    .mainv1-view-row-titles {
        grid-template-columns: 1fr;
    }

    .mainv1-view-row-titles > .mainv1-title-count-panel {
        width: 100%;
        max-width: none;
    }

    .mainv1-view-row-geography {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 700px) {
    .mainv1-analytics-heading{align-items:stretch;flex-direction:column}
    .mainv1-view-tabs{gap:2px}
    .mainv1-view-tab{padding:.55rem .35rem;font-size:.7rem}
    .mainv1-view-row {
        grid-template-columns: 1fr;
    }
}
</style>
<style>
.mainv1-analytics{display:grid;gap:1rem;margin:2rem 0 0;padding:clamp(1rem,2vw,2rem);border:1px solid #d8e5ef;border-radius:18px;background:linear-gradient(145deg,#f7fbfe,#fff 48%,#f4f9fc);box-shadow:0 16px 40px rgba(23,50,77,.07);color:#17324d}.mainv1-analytics-heading,.mainv1-directory-heading{display:flex;justify-content:space-between;align-items:flex-end;gap:1rem}.mainv1-analytics-eyebrow,.mainv1-panel-heading span{display:block;color:#6d8296;font-size:.68rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase}.mainv1-analytics-heading h2{margin:.2rem 0;color:#103d70;font-size:clamp(1.35rem,2vw,2rem)}.mainv1-analytics-heading p,.mainv1-directory-heading p{margin:.25rem 0 0;color:#6c7f91;font-size:.82rem}.mainv1-analytics-badge{display:flex;align-items:center;gap:.45rem;padding:.55rem .75rem;border:1px solid #cfe3ef;border-radius:999px;background:#fff;color:#45627d;font-size:.75rem;white-space:nowrap}.mainv1-live-dot{width:7px;height:7px;border-radius:50%;background:#20b6a8;box-shadow:0 0 0 4px #d9f5f0}.mainv1-analytics-top,.mainv1-analytics-grid{display:grid;gap:1rem}.mainv1-analytics-top{grid-template-columns:minmax(280px,.78fr) minmax(0,1.7fr)}.mainv1-analytics-metrics{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem}.mainv1-analytics-stat,.mainv1-analytics-panel{border:1px solid #dce8f0;border-radius:13px;background:rgba(255,255,255,.9);box-shadow:0 8px 22px rgba(23,50,77,.055)}.mainv1-analytics-stat{display:flex;min-height:150px;flex-direction:column;padding:1rem;border-top:3px solid #2e6fd8}.mainv1-analytics-stat span{color:#70859a;font-size:.63rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.mainv1-analytics-stat strong{margin:.35rem 0;color:#0e3e74;font-size:2rem}.mainv1-analytics-stat b{font-size:.78rem}.mainv1-analytics-stat small{margin-top:auto;color:#788b9c;font-size:.68rem;line-height:1.35}.mainv1-analytics-stat.stat-rose{border-color:#ff6682}.mainv1-analytics-stat.stat-rose strong{color:#ff4d6d}.mainv1-analytics-stat.stat-teal{border-color:#22b8aa}.mainv1-analytics-stat.stat-teal strong{color:#119e94}.mainv1-analytics-stat.stat-gold{border-color:#efb844}.mainv1-analytics-stat.stat-gold strong{color:#d49616}.mainv1-analytics-panel{min-width:0;padding:1rem}.mainv1-panel-heading{display:flex;justify-content:space-between;gap:.75rem}.mainv1-panel-heading h3{margin:.18rem 0 0;color:#173d68;font-size:.95rem}.mainv1-chart-wrap{position:relative;height:270px;margin-top:.65rem}.mainv1-analytics-grid-secondary{grid-template-columns:minmax(0,1.4fr) minmax(210px,.58fr) minmax(210px,.58fr)}.mainv1-analytics-grid-lower{grid-template-columns:minmax(0,1.15fr) minmax(0,1.15fr) minmax(210px,.8fr) minmax(210px,.8fr)}.mainv1-donut-wrap{position:relative;height:170px;margin:.3rem auto;width:min(100%,190px)}.mainv1-share-legend{display:grid;gap:.4rem;color:#526d85;font-size:.7rem}.mainv1-share-legend span{display:flex;align-items:center;justify-content:space-between}.mainv1-share-legend i{width:8px;height:8px;margin-right:.35rem;border-radius:50%}.mainv1-share-legend span b{margin-left:auto}.legend-teal{background:#42b9ba}.legend-rose{background:#ff6682}.legend-blue{background:#3398dc}.legend-gold{background:#ffc34f}.mainv1-insight-strip{display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;margin-top:.75rem}.mainv1-insight-strip div{padding:.55rem;border:1px solid #e4edf3;border-radius:8px}.mainv1-insight-strip span,.mainv1-insight-strip small{display:block;color:#70859a;font-size:.62rem}.mainv1-insight-strip strong{display:block;margin:.15rem 0;color:#123f70;font-size:1rem}.mainv1-heatmap{display:grid;gap:.3rem;margin-top:.8rem;max-height:240px;overflow:auto}.mainv1-heatmap-row{display:grid;grid-template-columns:72px repeat(8,1fr);gap:3px;align-items:center;font-size:.58rem}.mainv1-heatmap-label{overflow:hidden;color:#526d85;white-space:nowrap;text-overflow:ellipsis}.mainv1-heat-cell{height:17px;border-radius:3px;background:#edf4f8}.mainv1-heat-cell[data-level="1"]{background:#c5e6f6}.mainv1-heat-cell[data-level="2"]{background:#80c8ee}.mainv1-heat-cell[data-level="3"]{background:#369be0}.mainv1-coverage-list,.mainv1-ranking-list{display:grid;gap:.55rem;margin-top:.8rem}.mainv1-coverage-item{display:grid;grid-template-columns:135px 1fr 32px;gap:.45rem;align-items:center;color:#526d85;font-size:.68rem}.mainv1-coverage-bar{height:8px;overflow:hidden;border-radius:99px;background:#e7eff5}.mainv1-coverage-bar i{display:block;height:100%;border-radius:inherit;background:#38bdb1}.mainv1-ranking-item{display:grid;grid-template-columns:24px 1fr auto;gap:.5rem;align-items:center;padding:.55rem .65rem;border:1px solid #e4edf3;border-radius:9px;background:#fbfdff;font-size:.7rem}.mainv1-ranking-item em{display:grid;width:21px;height:21px;place-items:center;border-radius:50%;background:#e8f2ff;color:#347bc5;font-style:normal;font-weight:800}.mainv1-ranking-item b{color:#138f87}.mainv1-directory-panel{padding:1.1rem}.mainv1-directory-heading{align-items:center}.mainv1-directory-controls{display:grid;grid-template-columns:minmax(180px,1.5fr) repeat(2,minmax(120px,1fr));gap:.5rem;width:min(560px,100%)}.mainv1-directory-controls input,.mainv1-directory-controls select{width:100%;min-height:36px;padding:.45rem .6rem;border:1px solid #d5e3ed;border-radius:7px;background:#fff;color:#17324d;font-size:.75rem}.mainv1-directory-table-wrap{margin-top:1rem;overflow:auto;border:1px solid #e1ebf2;border-radius:10px}.mainv1-directory-table{width:100%;min-width:680px;border-collapse:collapse;font-size:.7rem}.mainv1-directory-table th{padding:.65rem;text-align:left;color:#58728a;background:#f5f9fc;font-size:.62rem;letter-spacing:.05em;text-transform:uppercase}.mainv1-directory-table td{padding:.65rem;border-top:1px solid #edf2f5;color:#34536f}.mainv1-directory-table td:first-child{max-width:260px;color:#173d68;font-weight:700}.mainv1-status-pill{display:inline-flex;padding:.2rem .45rem;border-radius:99px;font-size:.62rem;font-weight:800}.mainv1-status-pill.ongoing{background:#dff7ed;color:#168456}.mainv1-status-pill.inactive{background:#ffe5ec;color:#dc4968}.mainv1-directory-footer{display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-top:.75rem;color:#6b8296;font-size:.7rem}.mainv1-directory-footer div{display:flex;align-items:center;gap:.5rem}.mainv1-directory-footer button{padding:.4rem .65rem;border:1px solid #d5e3ed;border-radius:7px;background:#fff;color:#194878;font-size:.7rem;cursor:pointer}.mainv1-directory-footer button:disabled{opacity:.45;cursor:not-allowed}.mainv1-directory-footer strong{font-size:.7rem;color:#173d68;white-space:nowrap}@media(max-width:1100px){.mainv1-analytics-top,.mainv1-analytics-grid-secondary{grid-template-columns:1fr}.mainv1-analytics-grid-lower{grid-template-columns:repeat(2,minmax(0,1fr))}.mainv1-directory-heading{align-items:stretch;flex-direction:column}.mainv1-directory-controls{width:100%}}@media(max-width:576px){.mainv1-analytics{margin-top:1rem;padding:.75rem;border-radius:12px}.mainv1-analytics-heading{align-items:flex-start;flex-direction:column}.mainv1-analytics-metrics,.mainv1-analytics-grid-lower{grid-template-columns:1fr}.mainv1-chart-wrap{height:220px}.mainv1-insight-strip{grid-template-columns:1fr}.mainv1-directory-controls{grid-template-columns:1fr}.mainv1-directory-footer{align-items:flex-start;flex-direction:column}.mainv1-analytics-badge{white-space:normal}}
</style>
<style>
.mainv1-metric-trigger{cursor:pointer}.mainv1-metric-trigger:focus-visible{outline:3px solid rgba(46,111,216,.28);outline-offset:3px}.mainv1-metric-modal{position:fixed;inset:0;z-index:3000;display:none;align-items:center;justify-content:center;padding:1rem;background:rgba(11,35,59,.45)}.mainv1-metric-modal.is-open{display:flex}.mainv1-metric-dialog{width:min(920px,100%);max-height:min(760px,calc(100vh - 2rem));overflow:hidden;border:1px solid #d7e5ef;border-radius:16px;background:#fff;box-shadow:0 24px 70px rgba(8,43,81,.24)}.mainv1-metric-modal-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;padding:1.15rem 1.25rem;border-bottom:1px solid #e4edf3;background:linear-gradient(135deg,#f7fbff,#fff)}.mainv1-metric-modal-header span{color:#6d8296;font-size:.68rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase}.mainv1-metric-modal-header h2{margin:.25rem 0;color:#123f70;font-size:1.25rem}.mainv1-metric-modal-header p{margin:0;color:#6b8197;font-size:.78rem}.mainv1-metric-modal-close{display:grid;width:34px;height:34px;place-items:center;border:1px solid #d5e3ed;border-radius:8px;background:#fff;color:#496780;font-size:1.35rem;line-height:1;cursor:pointer}.mainv1-metric-list-wrap{max-height:calc(min(760px,100vh - 2rem) - 100px);overflow:auto;padding:.75rem 1.25rem 1.25rem}.mainv1-metric-list{display:grid;gap:.55rem}.mainv1-metric-row{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(100px,.65fr) minmax(120px,.8fr) auto;gap:.75rem;align-items:center;padding:.75rem;border:1px solid #e1ebf2;border-radius:10px;background:#fbfdff}.mainv1-metric-row strong{overflow:hidden;color:#173d68;font-size:.78rem;text-overflow:ellipsis;white-space:nowrap}.mainv1-metric-row span{overflow:hidden;color:#607990;font-size:.72rem;text-overflow:ellipsis;white-space:nowrap}.mainv1-metric-status{padding:.25rem .5rem;border-radius:99px;background:#dff7ed;color:#168456;font-size:.64rem;font-weight:800;text-align:center}.mainv1-metric-status.inactive{background:#ffe5ec;color:#dc4968}.mainv1-metric-empty{padding:2rem;text-align:center;color:#6b8197;border:1px dashed #d5e3ed;border-radius:10px}@media(max-width:576px){.mainv1-metric-dialog{max-height:calc(100vh - 1rem)}.mainv1-metric-modal-header{padding:.9rem}.mainv1-metric-list-wrap{padding:.65rem;max-height:calc(100vh - 100px)}.mainv1-metric-row{grid-template-columns:1fr;gap:.3rem}.mainv1-metric-row strong,.mainv1-metric-row span{white-space:normal}.mainv1-metric-status{width:max-content}}
.mainv1-analytics-top{grid-template-columns:repeat(2,minmax(0,1fr))}
.mainv1-analytics-top > .col-md-6{width:auto;max-width:none;min-width:0}
.mainv1-trend-panel .mainv1-chart-wrap{height:330px}
.mainv1-title-composition{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:1rem;align-items:stretch;min-width:0}
.mainv1-title-count-chart-wrap{display:flex;width:100%;max-width:100%;min-width:0;height:clamp(220px,24vw,300px);align-items:center;justify-content:center;margin-top:0;padding:.5rem;box-sizing:border-box;aspect-ratio:1;overflow:hidden}
.mainv1-title-count-chart-wrap canvas{display:block;width:100%!important;height:100%!important;max-width:100%!important;max-height:100%!important;aspect-ratio:1}
.mainv1-title-count-panel{min-width:0;overflow:hidden}
.mainv1-title-count-panel{padding:0;border:0;background:transparent;box-shadow:none;overflow:visible}
.mainv1-title-card{box-sizing:border-box;border:1px solid #dce8f0;border-radius:13px;background:rgba(255,255,255,.94);box-shadow:0 8px 22px rgba(23,50,77,.055)}
.mainv1-title-card-heading{align-self:stretch;margin-bottom:.4rem;color:#173d68;font-size:.95rem;font-weight:700;text-align:left}
.mainv1-title-count-chart-wrap{height:420px!important;flex-direction:column;gap:.35rem;padding:1.15rem!important;aspect-ratio:auto}
.mainv1-title-count-chart-wrap canvas{flex:1;min-height:0;height:100%!important}
.mainv1-title-reference{height:420px;padding:1.15rem; border-left:1px solid #dce8f0}
.mainv1-title-reference-header{margin:0 0 .75rem;padding-bottom:.75rem;border-bottom:1px solid #e3edf3}
.mainv1-title-reference-heading{color:#173d68;font-size:.95rem;letter-spacing:0;text-transform:none}
.mainv1-title-count-legend{height:auto;flex:1;gap:.15rem}
.mainv1-title-reference{display:flex;min-width:0;height:420px;flex-direction:column;overflow:hidden;padding-left:1rem;border-left:1px solid #e3edf3}
.mainv1-title-reference-header{display:flex;align-items:baseline;justify-content:space-between;gap:.5rem;margin:.25rem 0 .6rem}
.mainv1-title-reference-heading{margin:0;color:#6d8296;font-size:.68rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.mainv1-title-reference-count{color:#8aa0b2;font-size:.62rem;white-space:nowrap}
.mainv1-title-count-legend{display:grid;align-content:start;gap:.42rem;height:210px;overflow-y:auto;overflow-x:hidden;padding:.1rem .4rem .1rem 0;scrollbar-color:#9bcbd1 #f1f7fa;scrollbar-width:thin}
.mainv1-title-count-legend-item{display:grid;grid-template-columns:auto minmax(0,1fr) auto;gap:.45rem;align-items:center;min-width:0;padding:.25rem .35rem;color:#536b81;font-size:.68rem;line-height:1.25;border:1px solid transparent;border-radius:7px;cursor:pointer;transition:background-color .15s ease,border-color .15s ease,color .15s ease}
.mainv1-title-count-legend-item:hover,.mainv1-title-count-legend-item:focus-visible,.mainv1-title-count-legend-item.is-highlighted{border-color:#a9dfe1;background:#eaf9f8;color:#173d68;outline:none}
.mainv1-title-count-legend-item i{width:8px;height:8px;border-radius:50%;box-shadow:0 0 0 2px #eef7fa}
.mainv1-title-count-legend-item span{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.mainv1-title-count-legend-item strong{color:#173d68;font-size:.68rem;white-space:nowrap}
.mainv1-title-pagination{display:flex;align-items:center;justify-content:space-between;gap:.35rem;margin-top:.65rem;padding-top:.6rem;border-top:1px solid #e3edf3;color:#607990;font-size:.65rem}
.mainv1-title-pagination button{padding:.35rem .5rem;border:1px solid #cfe0ec;border-radius:7px;background:#fff;color:#245b87;font-size:.66rem;font-weight:700;cursor:pointer}
.mainv1-title-pagination button:hover:not(:disabled),.mainv1-title-pagination button:focus-visible{border-color:#42b9ba;background:#effafa}
.mainv1-title-pagination button:disabled{cursor:not-allowed;opacity:.45}
@media (max-width: 1100px) and (min-width: 577px) {.mainv1-title-composition{grid-template-columns:1fr}.mainv1-title-reference{height:300px;padding:0;border-top:1px solid #e3edf3;border-left:0}.mainv1-title-count-chart-wrap{height:280px}}
@media (max-width: 576px) {.mainv1-title-composition{grid-template-columns:1fr}.mainv1-title-reference{height:240px;padding:0;border-top:1px solid #e3edf3;border-left:0}.mainv1-title-count-chart-wrap{height:280px}.mainv1-title-count-legend{height:165px}}
.mainv1-share-summary{margin-top:.9rem}.mainv1-share-metrics{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.7rem}.mainv1-share-stat{min-width:0;padding:.75rem;border:1px solid #dce8f0;border-top:3px solid #42b9ba;border-radius:12px;background:linear-gradient(180deg,#fff,#f8fbff)}.mainv1-share-stat span,.mainv1-share-insight span{display:block;color:#71869a;font-size:.67rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase}.mainv1-share-stat strong{display:block;margin:.2rem 0;color:#173d68;font-size:1.45rem}.mainv1-share-stat small{display:block;color:#6b8197;font-size:.68rem;line-height:1.4}.mainv1-share-stat.share-stat-rose{border-top-color:#ff6682}.mainv1-share-stat.share-stat-rose strong{color:#ff4d6d}.mainv1-share-stat.share-stat-blue{border-top-color:#3398dc}.mainv1-share-stat.share-stat-blue strong{color:#2588c9}.mainv1-share-stat.share-stat-gold{border-top-color:#ffc34f}.mainv1-share-stat.share-stat-gold strong{color:#d99c1c}.mainv1-share-insight{margin-top:.7rem;padding:.75rem;border:1px solid #d8e7f3;border-radius:12px;background:#f4f9ff}.mainv1-share-insight strong{display:block;margin-top:.25rem;color:#173d68;font-size:.8rem;line-height:1.35}
@media (min-width: 577px) and (max-width: 1100px) {
    .mainv1-analytics-grid-lower {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .mainv1-coverage-panel {
        grid-column: span 1;
    }

    .mainv1-analytics-grid-secondary {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .mainv1-year-panel {
        grid-column: 1 / -1;
    }
}

@media (max-width: 576px) {
    .mainv1-analytics-grid-lower {
        grid-template-columns: 1fr;
    }

    .mainv1-analytics-top {
        grid-template-columns: 1fr;
    }

    .mainv1-analytics-grid-secondary {
        grid-template-columns: 1fr;
    }

    .mainv1-year-panel {
        grid-column: auto;
    }
}
    .mainv1-metric-row { cursor: pointer; }
    .mainv1-metric-row:hover, .mainv1-metric-row:focus-visible { border-color: #8bb9e2; background: #f1f8ff; outline: none; }
    .mainv1-directory-row { cursor: pointer; }
    .mainv1-directory-row:hover, .mainv1-directory-row:focus-visible { background: #f1f8ff; outline: none; }
    .mainv1-st-detail-modal { position: fixed; inset: 0; z-index: 3100; display: none; align-items: center; justify-content: center; padding: 1rem; background: rgba(8, 28, 49, .58); backdrop-filter: blur(4px); }
    .mainv1-st-detail-modal.is-open { display: flex; }
    .mainv1-attachment-modal { position: fixed; inset: 0; z-index: 3200; display: none; align-items: center; justify-content: center; padding: 1rem; background: rgba(8, 28, 49, .66); backdrop-filter: blur(4px); }
    .mainv1-attachment-modal.is-open { display: flex; }
    .mainv1-attachment-dialog { display: flex; width: min(1000px, 100%); height: min(82vh, 760px); flex-direction: column; overflow: hidden; border: 1px solid #c7d9eb; border-radius: 16px; background: #f8fbfe; box-shadow: 0 25px 70px rgba(5, 32, 61, .35); }
    .mainv1-attachment-dialog iframe { display: block; width: 100%; min-height: 0; flex: 1; border: 0; background: #fff; }
    #mainv1AttachmentFilename { margin: .2rem 0 0; color: rgba(255, 255, 255, .78); font-size: .7rem; font-weight: 600; }
    .mainv1-attachment-button { padding: .35rem .55rem; border: 1px solid #165a91; border-radius: 6px; background: #15539a; color: #fff; font-size: .68rem; cursor: pointer; white-space: nowrap; }
    .mainv1-attachment-button:hover, .mainv1-attachment-button:focus-visible { background: #0d427b; }
    .mainv1-attachment-empty { color: #8395a5; font-size: .68rem; }
    .mainv1-st-detail-dialog { width: min(812px, 100%); max-height: calc(100vh - 2rem); overflow: hidden; border: 1px solid #c7d9eb; border-radius: 20px; background: #f8fbfe; box-shadow: 0 25px 70px rgba(5, 32, 61, .35); }
    .mainv1-st-detail-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 1.25rem; background: #15539a; color: #fff; }
    .mainv1-st-detail-header h2 { margin: 0; font-size: 1rem; }
    .mainv1-st-detail-close { border: 0; background: transparent; color: #fff; font-size: 1.5rem; line-height: 1; cursor: pointer; }
    .mainv1-st-detail-body { max-height: calc(100vh - 7rem); overflow: auto; padding: 1rem 1.15rem 1.25rem; color: #17324d; }
    .mainv1-st-detail-title { margin: 0 0 .8rem; font-size: .85rem; font-weight: 800; }
    .mainv1-st-detail-attachment { margin-bottom: 1rem; padding: 1rem; border: 1px solid #dbe8f3; border-radius: 15px; background: #f3f8fd; }
    .mainv1-st-detail-label { display: block; margin-bottom: .45rem; color: #6a8299; font-size: .68rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
    .mainv1-st-detail-muted { margin: 0; color: #71869a; font-size: .73rem; }
    .mainv1-st-detail-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .8rem; }
    .mainv1-st-detail-field { min-width: 0; }
    .mainv1-st-detail-field.full { grid-column: 1 / -1; }
    .mainv1-st-detail-field label { display: block; margin-bottom: .35rem; color: #315574; font-size: .7rem; font-weight: 800; }
    .mainv1-st-detail-field input, .mainv1-st-detail-field textarea { box-sizing: border-box; width: 100%; border: 1px solid #cbdceb; border-radius: 10px; background: #fff; color: #334b62; font: inherit; font-size: .75rem; padding: .65rem .75rem; }
    .mainv1-st-detail-field textarea { min-height: 64px; resize: vertical; }
    .mainv1-st-detail-checks { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .7rem; }
    .mainv1-st-detail-check { display: flex; align-items: flex-start; gap: .55rem; padding: .75rem; border: 1px solid #d8e5f0; border-radius: 12px; background: #f5f9fd; font-size: .72rem; font-weight: 700; }
    .mainv1-st-detail-check input { accent-color: #0e5c91; }
    .mainv1-st-detail-check small { display: block; margin-top: .25rem; color: #6e8498; font-size: .65rem; font-weight: 600; }
    @media (max-width: 600px) { .mainv1-st-detail-modal { padding: .5rem; } .mainv1-st-detail-dialog { border-radius: 15px; } .mainv1-st-detail-grid, .mainv1-st-detail-checks { grid-template-columns: 1fr; } .mainv1-st-detail-field.full { grid-column: auto; } .mainv1-st-detail-body { padding: .85rem; } }
</style>
<style>
    .mainv1-directory-heading { flex-wrap: wrap; }
    .mainv1-directory-controls { width: min(100%, 760px); min-width: 0; grid-template-columns: minmax(150px, 1.5fr) repeat(3, minmax(0, 1fr)) repeat(2, minmax(0, .8fr)) auto; }
    .mainv1-directory-controls > * { min-width: 0; }
    .mainv1-directory-advanced-controls { display: contents; }
    .mainv1-directory-advanced-controls[hidden] { display: none !important; }
    .mainv1-directory-filter-toggle { display: none; }
    .mainv1-directory-controls button { min-height: 36px; padding: .45rem .7rem; border: 1px solid #165a91; border-radius: 7px; background: #15539a; color: #fff; font-size: .75rem; cursor: pointer; white-space: nowrap; }
    .mainv1-directory-controls button:hover, .mainv1-directory-controls button:focus-visible { background: #0d427b; }
    @media (max-width: 1100px) {
        .mainv1-directory-controls { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .mainv1-directory-filter-toggle {
            display: block;
            min-height: 36px;
            padding: .45rem .7rem;
            border: 1px solid #c7dce9;
            border-radius: 7px;
            background: #f5faff;
            color: #245b87;
            font-size: .75rem;
            font-weight: 700;
        }
    }
    @media (max-width: 576px) {
        .mainv1-directory-controls { grid-template-columns: 1fr; }
        .mainv1-directory-advanced-controls {
            display: grid;
            grid-column: 1 / -1;
            gap: .5rem;
        }
    }
</style>
<style>
    .mainv1-directory-table-wrap {
        height: 360px;
        overflow: auto;
    }

    .mainv1-directory-table {
        width: 100%;
        min-width: 720px;
        table-layout: fixed;
    }

    .mainv1-directory-table th,
    .mainv1-directory-table td {
        box-sizing: border-box;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mainv1-directory-table td:nth-child(5),
    .mainv1-directory-table td:nth-child(6) {
        white-space: nowrap;
        vertical-align: middle;
    }

    .mainv1-directory-table th:nth-child(1),
    .mainv1-directory-table td:nth-child(1) { width: 36%; }
    .mainv1-directory-table th:nth-child(2),
    .mainv1-directory-table td:nth-child(2) { width: 12%; }
    .mainv1-directory-table th:nth-child(3),
    .mainv1-directory-table td:nth-child(3) { width: 14%; }
    .mainv1-directory-table th:nth-child(4),
    .mainv1-directory-table td:nth-child(4) { width: 10%; }
    .mainv1-directory-table th:nth-child(5),
    .mainv1-directory-table td:nth-child(5) { width: 18%; }
    .mainv1-directory-table th:nth-child(6),
    .mainv1-directory-table td:nth-child(6) { width: 10%; }

    .mainv1-directory-table thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #f4f9fd;
    }

    .mainv1-coverage-badges {
        display: flex;
        flex-wrap: wrap;
        gap: .25rem;
        max-height: 2.8rem;
        overflow: hidden;
    }

    .mainv1-coverage-badge {
        display: inline-flex;
        align-items: center;
        min-height: 1.25rem;
        padding: .18rem .4rem;
        border: 1px solid #cfe0ec;
        border-radius: 999px;
        background: #f1f7fb;
        color: #315574;
        font-size: .62rem;
        font-weight: 700;
        line-height: 1;
        white-space: nowrap;
    }

    .mainv1-coverage-badge.coverage-eoi { border-color: #b7dfe0; background: #e8f8f6; color: #147c78; }
    .mainv1-coverage-badge.coverage-resolution { border-color: #c9d7f3; background: #edf3ff; color: #315ea8; }
    .mainv1-coverage-badge.coverage-moa { border-color: #f1d89b; background: #fff8e6; color: #9b6b0b; }
    .mainv1-coverage-badge.coverage-replicated { border-color: #c4d9f2; background: #edf6ff; color: #276ca9; }
    .mainv1-coverage-badge.coverage-adopted { border-color: #d9c9ed; background: #f7efff; color: #7546a2; }

    .mainv1-coverage-empty { color: #8ca0b1; font-size: .68rem; }

    .mainv1-metric-modal-header-actions {
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .mainv1-metric-replicate {
        padding: .35rem .55rem;
        border: 1px solid #1769aa;
        border-radius: 6px;
        background: #1769aa;
        color: #fff;
        cursor: pointer;
        font-size: .68rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .mainv1-metric-replicate:hover,
    .mainv1-metric-replicate:focus-visible {
        background: #0d4f83;
    }

    .mainv1-replication-confirm-modal {
        position: fixed;
        inset: 0;
        z-index: 3400;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background: rgba(8, 28, 49, .58);
        backdrop-filter: blur(3px);
    }

    .mainv1-replication-confirm-modal.is-open { display: flex; }

    .mainv1-replication-confirm-dialog {
        width: min(410px, 100%);
        padding: 1.35rem;
        border: 1px solid #d5e4ef;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 22px 60px rgba(8, 43, 81, .28);
        color: #17324d;
    }

    .mainv1-replication-confirm-icon {
        display: grid;
        width: 2.4rem;
        height: 2.4rem;
        margin-bottom: .8rem;
        place-items: center;
        border-radius: 50%;
        background: #e8f4ff;
        color: #1769aa;
        font-size: 1.25rem;
        font-weight: 800;
    }

    .mainv1-replication-confirm-dialog h2 {
        margin: 0;
        color: #123f70;
        font-size: 1.15rem;
    }

    .mainv1-replication-confirm-dialog p {
        margin: .55rem 0 1.2rem;
        color: #627b92;
        font-size: .8rem;
        line-height: 1.5;
    }

    .mainv1-replication-select-label {
        display: block;
        margin-bottom: .35rem;
        color: #315574;
        font-size: .72rem;
        font-weight: 700;
        text-align: left;
    }

    .mainv1-replication-confirm-dialog select {
        width: 100%;
        min-height: 2.35rem;
        margin-bottom: 1rem;
        padding: .45rem .6rem;
        border: 1px solid #cbdce8;
        border-radius: 7px;
        background: #fff;
        color: #17324d;
        font: inherit;
        font-size: .75rem;
    }

    .mainv1-replication-confirm-dialog select:focus {
        border-color: #4b9bd4;
        outline: 3px solid rgba(75, 155, 212, .18);
        outline-offset: 1px;
    }

    .mainv1-replication-confirm-dialog select.is-invalid {
        border-color: #dc4968;
        outline: 3px solid rgba(220, 73, 104, .15);
    }

    .mainv1-replication-field-error {
        margin: -.65rem 0 1rem;
        color: #c93656;
        font-size: .72rem;
        font-weight: 700;
        text-align: left;
    }

    .mainv1-replication-confirm-actions {
        display: flex;
        justify-content: flex-end;
        gap: .55rem;
    }

    .mainv1-replication-confirm-actions button {
        min-height: 2.2rem;
        padding: .45rem .8rem;
        border-radius: 7px;
        cursor: pointer;
        font-size: .75rem;
        font-weight: 700;
    }

    .mainv1-replication-cancel {
        border: 1px solid #cbdce8;
        background: #fff;
        color: #496780;
    }

    .mainv1-replication-continue {
        border: 1px solid #1769aa;
        background: #1769aa;
        color: #fff;
    }

    .mainv1-replication-cancel:hover,
    .mainv1-replication-cancel:focus-visible { background: #f2f7fb; }

    .mainv1-replication-continue:hover,
    .mainv1-replication-continue:focus-visible { background: #0d4f83; }

    .mainv1-coverage-panel,
    .mainv1-coverage-list,
    .mainv1-coverage-item {
        min-width: 0;
    }

    .mainv1-coverage-item {
        grid-template-columns: minmax(0, 1fr) minmax(24px, .65fr) auto;
        gap: .35rem;
    }

    .mainv1-coverage-item span {
        min-width: 0;
        overflow-wrap: anywhere;
    }


    .mainv1-status-pill.ongoing {
        font-size: 0;
    }

    .mainv1-status-pill.ongoing::after {
        content: 'Active';
        font-size: .62rem;
    }

    .mainv1-metric-status:not(.inactive) {
        font-size: 0;
    }

    .mainv1-metric-status:not(.inactive)::after {
        content: 'Active';
        font-size: .72rem;
    }

    @media (max-width: 576px) {
        .mainv1-directory-panel {
            padding: .85rem;
            border-radius: 14px;
        }

        .mainv1-directory-heading {
            display: block;
        }

        .mainv1-directory-heading > div:first-child {
            margin-bottom: .8rem;
        }

        .mainv1-directory-heading p {
            margin-top: .35rem;
            font-size: .74rem;
        }

        .mainv1-directory-controls {
            display: grid;
            gap: .5rem;
            width: 100%;
        }

        .mainv1-directory-controls input,
        .mainv1-directory-controls select,
        .mainv1-directory-controls button {
            box-sizing: border-box;
            width: 100%;
            min-height: 42px;
            font-size: .78rem;
        }

        .mainv1-directory-controls input {
            border-color: #8eb9d6;
            box-shadow: 0 0 0 3px rgba(75, 155, 212, .1);
        }

        .mainv1-directory-table-wrap {
            height: 430px;
            margin-top: .75rem;
            border: 0;
            overflow: auto;
            overscroll-behavior: contain;
            scrollbar-color: #9bb9cf transparent;
            scrollbar-width: thin;
        }

        .mainv1-directory-table {
            display: block;
            min-width: 0;
        }

        .mainv1-directory-table thead {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip: rect(0 0 0 0);
            white-space: nowrap;
        }

        .mainv1-directory-table tbody {
            display: grid;
            gap: .65rem;
        }

        .mainv1-directory-table tr {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .55rem .75rem;
            padding: .8rem;
            border: 1px solid #dce8f0;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(23, 50, 77, .06);
            transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
        }

        .mainv1-directory-table tr:active {
            border-color: #75a9ce;
            box-shadow: 0 2px 7px rgba(23, 50, 77, .1);
            transform: translateY(1px);
        }

        .mainv1-directory-table td {
            display: block;
            min-width: 0;
            width: auto !important;
            padding: 0;
            overflow: visible;
            text-overflow: clip;
            white-space: normal;
            color: #315574;
            font-size: .72rem;
            line-height: 1.35;
        }

        .mainv1-directory-table td::before {
            display: block;
            margin-bottom: .18rem;
            color: #8297a9;
            content: '';
            font-size: .58rem;
            font-weight: 800;
            letter-spacing: .07em;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .mainv1-directory-table td:nth-child(1) {
            grid-column: 1 / -1;
            padding-bottom: .55rem;
            border-bottom: 1px solid #edf2f6;
            color: #173d68;
            font-size: .82rem;
            font-weight: 800;
        }

        .mainv1-directory-table td:nth-child(1)::before { content: 'Social technology'; }
        .mainv1-directory-table td:nth-child(2)::before { content: 'Province'; }
        .mainv1-directory-table td:nth-child(3)::before { content: 'City / municipality'; }
        .mainv1-directory-table td:nth-child(4)::before { content: 'Status'; }
        .mainv1-directory-table td:nth-child(5)::before { content: 'Coverage'; }
        .mainv1-directory-table td:nth-child(6)::before { content: 'Attachment'; }

        .mainv1-directory-table td:nth-child(5),
        .mainv1-directory-table td:nth-child(6) {
            white-space: normal;
            vertical-align: top;
        }

        .mainv1-directory-table td:nth-child(6) {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .mainv1-directory-table td:nth-child(6)::before { width: 100%; }
        .mainv1-directory-table .mainv1-coverage-badges { max-height: none; }
        .mainv1-directory-table .mainv1-attachment-button { min-height: 2rem; }

        .mainv1-directory-footer {
            position: sticky;
            bottom: 0;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .55rem;
            margin: .65rem -.1rem -.1rem;
            padding: .65rem .1rem .1rem;
            border-top: 1px solid #e1ebf2;
            background: rgba(255, 255, 255, .96);
        }

        .mainv1-directory-footer > span {
            color: #6d8296;
            font-size: .7rem;
            white-space: nowrap;
        }

        .mainv1-directory-footer > div {
            display: flex;
            align-items: center;
            gap: .35rem;
        }

        .mainv1-directory-footer button {
            min-width: 2.35rem;
            min-height: 2.25rem;
            padding: .4rem .55rem;
            border: 1px solid #c9dce9;
            border-radius: 7px;
            background: #fff;
            color: #245b87;
            font-size: .7rem;
            font-weight: 700;
        }

        .mainv1-directory-footer button:disabled {
            color: #9aabba;
            background: #f5f8fa;
        }

        .mainv1-directory-footer strong {
            color: #315574;
            font-size: .7rem;
            white-space: nowrap;
        }
    }

    .mainv1-replication-confirm-modal {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(6px);

    display: flex;
    align-items: center;
    justify-content: center;

    opacity: 0;
    visibility: hidden;

    transition: all .25s ease;
    z-index: 9999;
}


.mainv1-replication-confirm-modal.active {
    opacity: 1;
    visibility: visible;
}


.mainv1-replication-confirm-dialog {

    width: 420px;
    max-width: calc(100% - 32px);

    background: #ffffff;
    border-radius: 20px;

    padding: 32px;

    text-align: center;

    box-shadow:
        0 20px 40px rgba(0,0,0,.15);

    transform: translateY(20px) scale(.96);

    transition: .25s ease;
}


.mainv1-replication-confirm-modal.active
.mainv1-replication-confirm-dialog {

    transform: translateY(0) scale(1);
}


/* ICON */

.mainv1-replication-confirm-icon {

    width: 70px;
    height: 70px;

    margin: 0 auto 20px;

    border-radius: 50%;

    display:flex;
    align-items:center;
    justify-content:center;

    background:#e0f2fe;

    color:#0284c7;

    font-size:32px;
}


/* TITLE */

.mainv1-replication-confirm-dialog h2 {

    margin:0;

    font-size:24px;
    font-weight:700;

    color:#0f172a;
}


/* DESCRIPTION */

.mainv1-replication-confirm-dialog p {

    margin-top:12px;

    line-height:1.6;

    font-size:15px;

    color:#64748b;
}


/* INFO BOX */

.mainv1-replication-info {

    margin-top:20px;

    padding:12px 15px;

    border-radius:12px;

    background:#f8fafc;

    color:#475569;

    font-size:13px;

    text-align:left;
}


.mainv1-replication-info i {
    color:#0284c7;
    margin-right:6px;
}



/* BUTTONS */

.mainv1-replication-confirm-actions {

    display:flex;

    gap:12px;

    margin-top:28px;
}


.mainv1-replication-confirm-actions button {

    flex:1;

    height:45px;

    border-radius:10px;

    font-size:14px;

    font-weight:600;

    cursor:pointer;

    transition:.2s ease;

}



/* CANCEL */

.mainv1-replication-cancel {

    background:#f1f5f9;

    border:1px solid #e2e8f0;

    color:#475569;
}


.mainv1-replication-cancel:hover {

    background:#e2e8f0;

}



/* CONTINUE */

.mainv1-replication-continue {

    border:none;

    background:#2563eb;

    color:white;

}


.mainv1-replication-continue:hover {

    background:#1d4ed8;

    transform:translateY(-1px);

}


.mainv1-replication-continue i {

    margin-left:6px;

}
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(() => {
    const rows = @json($mainv1AnalyticsRows);
    const titleOperationalStatuses = @json($mainv1FilterData->pluck('operational_status', 'title'));
    const decorateOperationalIndicators = () => {
        document.querySelectorAll('.mainv1-metric-row, .mainv1-region-title-row').forEach(item => {
            if (item.querySelector('.mainv1-operational-indicator')) return;
            const title = item.dataset.title || item.querySelector('strong')?.textContent || '';
            const normalizedTitle = String(title || '').trim().toLowerCase();
            const row = rows.find(candidate => String(candidate.title || '').trim().toLowerCase() === normalizedTitle);
            const titleStatusEntry = Object.entries(titleOperationalStatuses).find(([candidateTitle]) => String(candidateTitle || '').trim().toLowerCase() === normalizedTitle);
            const operationalStatus = row?.operational_status || titleStatusEntry?.[1] || 'Operational';
            if (String(operationalStatus) === 'Operational') return;
            const indicator = document.createElement('span');
            indicator.className = 'mainv1-operational-indicator';
            indicator.textContent = 'Not Operational';
            indicator.style.cssText = 'display:inline-flex;align-items:center;width:max-content;padding:.25rem .5rem;border-radius:99px;background:#fee2e2;color:#b91c1c;font-size:.64rem;font-weight:800;line-height:1.2;';
            item.appendChild(indicator);
        });
    };
    const operationalIndicatorObserver = new MutationObserver(decorateOperationalIndicators);
    operationalIndicatorObserver.observe(document.body, { childList: true, subtree: true });
    decorateOperationalIndicators();
    const replicationRedirectUrl = @json($replicationRedirectUrl);
    const truthy = value => value === true || String(value).toLowerCase() === 'true';
    const metricModal = document.getElementById('mainv1MetricModal');
    const metricModalTitle = document.getElementById('mainv1MetricModalTitle');
    const metricModalSummary = document.getElementById('mainv1MetricModalSummary');
    const metricList = document.getElementById('mainv1MetricList');
    if (metricModal && metricModal.parentElement !== document.body) document.body.appendChild(metricModal);
    const metricProvince = document.getElementById('mainv1MetricProvince');
    const metricMunicipality = document.getElementById('mainv1MetricMunicipality');
    const metricYear = document.getElementById('mainv1MetricYear');
    const metricTitleSearch = document.getElementById('mainv1MetricTitleSearch');
    const metricExport = document.getElementById('mainv1MetricExport');
    const metricReplicate = document.getElementById('mainv1MetricReplicate');
    const replicationConfirmModal = document.getElementById('mainv1ReplicationConfirmModal');
    const replicationConfirmMessage = document.getElementById('mainv1ReplicationConfirmMessage');
    const replicationRecordForm = document.getElementById('mainv1ReplicationRecordForm');
    const replicationTitle = document.getElementById('mainv1ReplicationTitle');
    const replicationTitleError = document.getElementById('mainv1ReplicationTitleError');
    const replicationCancel = document.getElementById('mainv1ReplicationCancel');
    const replicationContinue = document.getElementById('mainv1ReplicationContinue');
    if (replicationConfirmModal && replicationConfirmModal.parentElement !== document.body) document.body.appendChild(replicationConfirmModal);
    let replicationPreviousFocus = null;
    const closeReplicationConfirm = () => {
        replicationConfirmModal?.classList.remove('active');
        replicationConfirmModal?.setAttribute('aria-hidden', 'true');
        replicationPreviousFocus?.focus();
    };
    const replicateMetric = () => {
        replicationPreviousFocus = document.activeElement;
        if (replicationTitle) replicationTitle.value = '';
        replicationTitle?.classList.remove('is-invalid');
        replicationTitle?.setAttribute('aria-invalid', 'false');
        if (replicationTitleError) replicationTitleError.hidden = true;
        if (!replicationRedirectUrl) {
            if (replicationConfirmMessage) replicationConfirmMessage.textContent = 'A sysadmin must configure the replication destination before this action can continue.';
            if (replicationContinue) replicationContinue.hidden = false;
        } else {
            if (replicationConfirmMessage) replicationConfirmMessage.textContent = 'You are about to leave this portal and continue to the configured replication destination.';
            if (replicationContinue) replicationContinue.hidden = false;
        }
        replicationConfirmModal?.classList.add('active');
        replicationConfirmModal?.setAttribute('aria-hidden', 'false');
        replicationTitle?.focus();
    };
    replicationCancel?.addEventListener('click', closeReplicationConfirm);
    replicationContinue?.addEventListener('click', () => {
        if (!replicationTitle?.value) {
            replicationTitle?.focus();
            replicationTitle?.classList.add('is-invalid');
            replicationTitle?.setAttribute('aria-invalid', 'true');
            if (replicationTitleError) replicationTitleError.hidden = false;
            return;
        }
        replicationRecordForm?.requestSubmit();
    });
    replicationTitle?.addEventListener('change', () => {
        if (!replicationTitle.value) return;
        replicationTitle.classList.remove('is-invalid');
        replicationTitle.setAttribute('aria-invalid', 'false');
        if (replicationTitleError) replicationTitleError.hidden = true;
    });
    replicationConfirmModal?.addEventListener('click', event => {
        if (event.target === replicationConfirmModal) closeReplicationConfirm();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && replicationConfirmModal?.classList.contains('active')) closeReplicationConfirm();
    });
    metricReplicate?.addEventListener('click', replicateMetric);
    window.openMainv1ReplicationConfirm = replicateMetric;
    let currentMetricKey = 'all';
    let currentMetricRegion = '';
    const matchingMetricRows = () => { const regionAliases = currentMetricRegion ? [currentMetricRegion, `FO ${currentMetricRegion}`, currentMetricRegion.replace(/^Region /, 'FO ')] : []; return rows.filter(metricMatches[currentMetricKey] || metricMatches.all).filter(row => !currentMetricRegion || regionAliases.includes(String(row.region || ''))); };
    const metricFilterValues = key => [...new Set(matchingMetricRows().map(row => row[key]).filter(value => value !== null && value !== undefined && String(value).trim() !== '').map(String))].sort((a, b) => a.localeCompare(b, undefined, { numeric: true }));
    const refillMetricSelect = (select, values) => { if (!select) return; select.length = 1; values.forEach(value => { const option = document.createElement('option'); option.value = value; option.textContent = value; select.appendChild(option); }); };
    const applyMetricFilters = () => { const title = String(metricTitleSearch?.value || '').trim().toLowerCase(); const province = metricProvince?.value || ''; const municipality = metricMunicipality?.value || ''; const year = metricYear?.value || ''; let visibleCount = 0; metricList?.querySelectorAll('.mainv1-metric-row').forEach(item => { const visible = (!title || item.dataset.title.includes(title)) && (!province || item.dataset.province === province) && (!municipality || item.dataset.municipality === municipality) && (!year || item.dataset.year === year); item.hidden = !visible; if (visible) visibleCount += 1; }); if (metricModalSummary) metricModalSummary.textContent = `${visibleCount} social technology records`; };
    const syncMetricFilters = () => { const matchingRows = matchingMetricRows(); refillMetricSelect(metricProvince, metricFilterValues('province')); refillMetricSelect(metricMunicipality, metricFilterValues('municipality')); refillMetricSelect(metricYear, metricFilterValues('year')); metricList?.querySelectorAll('.mainv1-metric-row').forEach((item, index) => { const row = matchingRows[index]; if (!row) return; item.dataset.title = String(row.title || '').toLowerCase(); item.dataset.province = row.province || ''; item.dataset.municipality = row.municipality || ''; item.dataset.year = String(row.year || ''); }); applyMetricFilters(); };
    [metricTitleSearch, metricProvince, metricMunicipality, metricYear].filter(Boolean).forEach(control => control.addEventListener(control === metricTitleSearch ? 'input' : 'change', applyMetricFilters));
    metricExport?.addEventListener('click', () => { const titleQuery = String(metricTitleSearch?.value || '').trim().toLowerCase(); const matchingRows = matchingMetricRows().filter(row => (!titleQuery || String(row.title || '').toLowerCase().includes(titleQuery)) && (!metricProvince?.value || String(row.province || '') === metricProvince.value) && (!metricMunicipality?.value || String(row.municipality || '') === metricMunicipality.value) && (!metricYear?.value || String(row.year || '') === metricYear.value)); const quote = value => `"${String(value ?? '').replace(/"/g, '""')}"`; const csv = [['Social Technology Title', 'Province', 'City / Municipality', 'Year of MOA', 'Region', 'Status'], ...matchingRows.map(row => [row.title, row.province, row.municipality, row.year, row.region, row.status === 'ongoing' ? 'Ongoing' : 'Inactive'])].map(row => row.map(quote).join(',')).join('\r\n'); const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' })); const link = document.createElement('a'); link.href = url; link.download = `metric-social-technologies-${new Date().toISOString().slice(0, 10)}.csv`; document.body.appendChild(link); link.click(); link.remove(); URL.revokeObjectURL(url); });
    const metricLabels = { all: 'Total Adopted and Replicated', expr: 'Total Expression of Interest', res: 'Total SB Resolution', moa: 'Total Memorandum of Agreement', active: "Total Active Region's Social Technologies", inactive: "Total Inactive Region's Social Technologies", replicated: 'Total Replicated', adopted: 'Total Adopted' };
    const metricMatches = { all: () => true, expr: row => truthy(row.expr), res: row => truthy(row.res), moa: row => truthy(row.moa), active: row => row.status === 'ongoing', inactive: row => row.status === 'inactive' || row.status === 'dissolved', replicated: row => truthy(row.replicated), adopted: row => truthy(row.adopted) };
    const openMetricModal = (metric) => { metricTitleSearch.value = ''; metricProvince.value = ''; metricMunicipality.value = ''; metricYear.value = ''; const matchingRows = rows.filter(metricMatches[metric] || metricMatches.all); metricModalTitle.textContent = metricLabels[metric] || 'Metric records'; metricModalSummary.textContent = `${matchingRows.length} social technology records`; metricList.replaceChildren(); matchingRows.forEach(row => { const item = document.createElement('div'); item.className = 'mainv1-metric-row'; item.dataset.title = String(row.title || '').toLowerCase(); item.dataset.province = row.province || ''; item.dataset.municipality = row.municipality || ''; item.dataset.year = String(row.year || ''); const title = document.createElement('strong'); title.textContent = row.title || 'Untitled social technology'; const location = document.createElement('span'); location.textContent = [row.province, row.municipality].filter(Boolean).join(' / ') || 'Location not specified'; const region = document.createElement('span'); region.textContent = row.region || 'Region not specified'; const status = document.createElement('span'); const isInactive = row.status === 'inactive' || row.status === 'dissolved'; status.className = `mainv1-metric-status${isInactive ? ' inactive' : ''}`; status.textContent = isInactive ? 'Inactive' : 'Ongoing'; item.append(title, location, region, status); metricList.appendChild(item); }); if (!matchingRows.length) { const empty = document.createElement('div'); empty.className = 'mainv1-metric-empty'; empty.textContent = 'No social technologies match this metric.'; metricList.appendChild(empty); } metricModal.classList.add('is-open'); metricModal.setAttribute('aria-hidden', 'false'); document.body.classList.add('mainv1-modal-open'); metricModal.querySelector('.mainv1-metric-modal-close').focus(); };
    const openTitleRecords = title => { if (!metricModal || !metricTitleSearch) return; currentMetricKey = 'all'; currentMetricRegion = ''; metricProvince.value = ''; metricMunicipality.value = ''; metricYear.value = ''; openMetricModal('all'); syncMetricFilters(); metricTitleSearch.value = title; applyMetricFilters(); metricModalTitle.textContent = title; metricModalSummary.textContent = `${rows.filter(row => String(row.title || '').trim() === title).length} records for this social technology`; };
    window.openMainv1RegionRecords = region => { currentMetricKey = 'all'; currentMetricRegion = region || ''; const matchingRows = matchingMetricRows(); metricModalTitle.textContent = `${region} Social Technologies`; metricModalSummary.textContent = `${matchingRows.length} social technology records`; metricList.replaceChildren(); matchingRows.forEach(row => { const item = document.createElement('div'); item.className = 'mainv1-metric-row'; const title = document.createElement('strong'); title.textContent = row.title || 'Untitled social technology'; const location = document.createElement('span'); location.textContent = [row.province, row.municipality].filter(Boolean).join(' / ') || 'Location not specified'; const regionName = document.createElement('span'); regionName.textContent = row.region || 'Region not specified'; const status = document.createElement('span'); const isInactive = row.status === 'inactive' || row.status === 'dissolved'; status.className = `mainv1-metric-status${isInactive ? ' inactive' : ''}`; status.textContent = isInactive ? 'Inactive' : 'Ongoing'; item.dataset.title = String(row.title || '').toLowerCase(); item.dataset.province = row.province || ''; item.dataset.municipality = row.municipality || ''; item.dataset.year = String(row.year || ''); item.append(title, location, regionName, status); metricList.appendChild(item); }); if (!matchingRows.length) { const empty = document.createElement('div'); empty.className = 'mainv1-metric-empty'; empty.textContent = 'No social technologies are recorded for this region.'; metricList.appendChild(empty); } refillMetricSelect(metricProvince, metricFilterValues('province')); refillMetricSelect(metricMunicipality, metricFilterValues('municipality')); refillMetricSelect(metricYear, metricFilterValues('year')); metricTitleSearch.value = ''; metricProvince.value = ''; metricMunicipality.value = ''; metricYear.value = ''; metricModal.classList.add('is-open'); metricModal.setAttribute('aria-hidden', 'false'); document.body.classList.add('mainv1-modal-open'); metricModal.querySelector('.mainv1-metric-modal-close').focus(); };
    window.openMainv1LocationRecords = (region, province) => {
        if (!region || !province || !window.openMainv1RegionRecords) return;
        window.openMainv1RegionRecords(region);
        if (!metricProvince) return;
        const normalizeProvince = value => String(value || '').toLowerCase().replace(/^metropolitan manila$/, 'metro manila').replace(/^province of\s+/, '').replace(/\s+/g, ' ').trim();
        const regionAliases = [region, `FO ${region}`, String(region).replace(/^Region /, 'FO ')];
        const matchingProvince = [...new Set(rows.filter(row => regionAliases.includes(String(row.region || ''))).map(row => String(row.province || '').trim()).filter(Boolean))].find(value => normalizeProvince(value) === normalizeProvince(province)) || province;
        metricProvince.value = matchingProvince;
        applyMetricFilters();
        if (metricModalTitle) metricModalTitle.textContent = `${province} Social Technologies`;
    };
    window.openMainv1MetricRecords = metric => { currentMetricKey = metric; currentMetricRegion = ''; metricTitleSearch.value = ''; metricProvince.value = ''; metricMunicipality.value = ''; metricYear.value = ''; openMetricModal(metric); syncMetricFilters(); };
    window.openMainv1YearRecords = year => { currentMetricKey = 'all'; currentMetricRegion = ''; openMetricModal('all'); syncMetricFilters(); metricModalTitle.textContent = `Social Technologies in ${year}`; metricYear.value = String(year); applyMetricFilters(); };
    document.querySelectorAll('.mainv1-metric-trigger').forEach(card => card.addEventListener('click', () => { currentMetricRegion = ''; }));
    const closeMetricModal = () => { metricModal.classList.remove('is-open'); metricModal.setAttribute('aria-hidden', 'true'); document.body.classList.remove('mainv1-modal-open'); };
    document.querySelectorAll('.mainv1-metric-trigger').forEach(card => { const open = () => openMetricModal(card.dataset.mainv1Metric); card.addEventListener('click', open); card.addEventListener('keydown', event => { if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); open(); } }); });
    document.querySelectorAll('.mainv1-metric-trigger').forEach(card => card.addEventListener('click', () => { currentMetricKey = card.dataset.mainv1Metric || 'all'; window.setTimeout(syncMetricFilters, 0); }));
    metricModal?.querySelector('.mainv1-metric-modal-close')?.addEventListener('click', closeMetricModal); metricModal?.addEventListener('click', event => { if (event.target === metricModal) closeMetricModal(); }); document.addEventListener('keydown', event => { if (event.key === 'Escape' && metricModal?.classList.contains('is-open')) closeMetricModal(); });
    const detailModal = document.getElementById('mainv1StDetailModal');
    const detailBody = document.getElementById('mainv1StDetailBody');
    const detailTitle = document.getElementById('mainv1StDetailTitle');
    const attachmentModal = document.getElementById('mainv1AttachmentModal');
    if (detailModal && detailModal.parentElement !== document.body) document.body.appendChild(detailModal);
    if (attachmentModal && attachmentModal.parentElement !== document.body) document.body.appendChild(attachmentModal);
    const attachmentFrame = document.getElementById('mainv1AttachmentFrame');
    const attachmentTitle = document.getElementById('mainv1AttachmentTitle');
    const attachmentFilename = document.getElementById('mainv1AttachmentFilename');
    const openAttachmentModal = row => { if (!attachmentModal || !row.attachment_url) return; attachmentTitle.textContent = row.title || 'Attachment'; attachmentFilename.textContent = row.attachment_filename || 'MOA attachment'; attachmentFrame.src = row.attachment_url; attachmentModal.classList.add('is-open'); attachmentModal.setAttribute('aria-hidden', 'false'); document.body.classList.add('mainv1-modal-open'); attachmentModal.querySelector('.mainv1-st-detail-close').focus(); };
    const closeAttachmentModal = () => { attachmentModal?.classList.remove('is-open'); attachmentModal?.setAttribute('aria-hidden', 'true'); if (attachmentFrame) attachmentFrame.src = 'about:blank'; document.body.classList.remove('mainv1-modal-open'); };
    const detailField = (label, value, full = false) => { const field = document.createElement('div'); field.className = `mainv1-st-detail-field${full ? ' full' : ''}`; const caption = document.createElement('label'); caption.textContent = label; const input = label === 'Inactive Remarks' ? document.createElement('textarea') : document.createElement('input'); input.readOnly = true; const displayValue = label === 'Status' && String(value || '').toLowerCase() === 'ongoing' ? 'Active' : value; input.value = displayValue || '-'; field.append(caption, input); return field; };
    const openDetailModal = row => { if (!detailModal || !detailBody) return; const status = String(row.status || '').toLowerCase(); const inactive = ['inactive', 'dissolved', 'completed'].includes(status); const adoption = truthy(row.adopted) ? 'Adopted' : (truthy(row.replicated) ? 'Replicated' : 'None'); detailTitle.textContent = row.title || 'ST Details'; detailBody.replaceChildren(); const heading = document.createElement('p'); heading.className = 'mainv1-st-detail-title'; heading.textContent = row.title || 'Social Technology'; detailBody.appendChild(heading); const attachment = document.createElement('div'); attachment.className = 'mainv1-st-detail-attachment'; const attachmentLabel = document.createElement('span'); attachmentLabel.className = 'mainv1-st-detail-label'; attachmentLabel.textContent = 'MOA Attachment'; if (row.attachment_url) { const attachmentName = document.createElement('p'); attachmentName.className = 'mainv1-st-detail-muted'; attachmentName.textContent = row.attachment_filename || 'MOA attachment.pdf'; const attachmentButton = document.createElement('button'); attachmentButton.type = 'button'; attachmentButton.className = 'mainv1-attachment-button'; attachmentButton.textContent = 'View attachment'; attachmentButton.addEventListener('click', () => openAttachmentModal(row)); attachment.append(attachmentLabel, attachmentName, attachmentButton); } else { const attachmentText = document.createElement('p'); attachmentText.className = 'mainv1-st-detail-muted'; attachmentText.textContent = 'No PDF attachment uploaded yet.'; attachment.append(attachmentLabel, attachmentText); } detailBody.appendChild(attachment); const grid = document.createElement('div'); grid.className = 'mainv1-st-detail-grid'; [['Regional Office', row.region], ['Status', inactive ? 'Inactive' : (row.status || '-')], ['Inactive Status', row.inactive_status], ['Inactive Remarks', row.inactive_remarks, true], ['Social Technology Title', row.title, true], ['Province', row.province], ['Municipality', row.municipality], ['Adopted / Replicated', adoption]].forEach(field => grid.appendChild(detailField(field[0], field[1], field[2]))); const indicatorField = document.createElement('div'); indicatorField.className = 'mainv1-st-detail-field full'; const indicatorLabel = document.createElement('label'); indicatorLabel.textContent = 'Indicators'; const checks = document.createElement('div'); checks.className = 'mainv1-st-detail-checks'; [['With Expression of Interest', row.expr], ['With MOA', row.moa], ['With Resolution', row.res], ['Included AIP', row.included_aip]].forEach(indicator => { const check = document.createElement('label'); check.className = 'mainv1-st-detail-check'; const input = document.createElement('input'); input.type = 'checkbox'; input.checked = truthy(indicator[1]); input.disabled = true; const text = document.createElement('span'); text.textContent = indicator[0]; check.append(input, text); checks.appendChild(check); }); indicatorField.append(indicatorLabel, checks); grid.appendChild(indicatorField); if (truthy(row.moa)) grid.appendChild(detailField('Year of MOA', row.year_of_moa)); if (truthy(row.res)) grid.appendChild(detailField('Year of Resolution', row.year_of_resolution)); detailBody.appendChild(grid); detailModal.classList.add('is-open'); detailModal.setAttribute('aria-hidden', 'false'); metricModal.classList.remove('is-open'); detailModal.querySelector('.mainv1-st-detail-close')?.focus(); };
    const closeDetailModal = () => { detailModal?.classList.remove('is-open'); detailModal?.setAttribute('aria-hidden', 'true'); document.body.classList.remove('mainv1-modal-open'); };
    metricList?.addEventListener('click', event => { const item = event.target.closest('.mainv1-metric-row'); if (!item) return; const title = item.querySelector('strong')?.textContent; const row = rows.find(candidate => candidate.title === title); if (row) openDetailModal(row); });
    metricList?.addEventListener('keydown', event => { if (event.key !== 'Enter' && event.key !== ' ') return; const item = event.target.closest('.mainv1-metric-row'); if (!item) return; event.preventDefault(); item.click(); });
    detailModal?.querySelector('.mainv1-st-detail-close')?.addEventListener('click', closeDetailModal); detailModal?.addEventListener('click', event => { if (event.target === detailModal) closeDetailModal(); }); document.addEventListener('keydown', event => { if (event.key === 'Escape' && detailModal?.classList.contains('is-open')) closeDetailModal(); });
    const count = predicate => rows.filter(predicate).length;
    const ongoing = count(row => row.status === 'ongoing');
    const inactive = count(row => row.status === 'inactive' || row.status === 'dissolved');
    const replicated = count(row => truthy(row.replicated));
    const adopted = count(row => truthy(row.adopted));
    const setText = (id, value) => { const node = document.getElementById(id); if (node) node.textContent = value; };
    setText('mainv1OngoingCount', ongoing); setText('mainv1InactiveCount', inactive); setText('mainv1ReplicatedCount', replicated); setText('mainv1AdoptedCount', adopted);
    const percentage = (value, total) => total ? `${Math.round((value / total) * 100)}%` : '0%';
    const statusLead = ongoing >= inactive ? `Ongoing STs lead by ${ongoing - inactive} records.` : `Inactive STs lead by ${inactive - ongoing} records.`; const adoptionLead = replicated >= adopted ? `Replicated STs lead by ${replicated - adopted} records.` : `Adopted STs lead by ${adopted - replicated} records.`; const statusOngoingPercent = percentage(ongoing, ongoing + inactive); const statusInactivePercent = percentage(inactive, ongoing + inactive); const replicatedPercent = percentage(replicated, replicated + adopted); const adoptedPercent = percentage(adopted, replicated + adopted); setText('mainv1StatusOngoingPercent', statusOngoingPercent); setText('mainv1StatusInactivePercent', statusInactivePercent); setText('mainv1ReplicatedPercent', replicatedPercent); setText('mainv1AdoptedPercent', adoptedPercent); setText('mainv1StatusOngoingValue', ongoing); setText('mainv1StatusInactiveValue', inactive); setText('mainv1ReplicatedValue', replicated); setText('mainv1AdoptedValue', adopted); setText('mainv1StatusOngoingSummary', `${statusOngoingPercent} of status records`); setText('mainv1StatusInactiveSummary', `${statusInactivePercent} of status records`); setText('mainv1ReplicatedSummary', `${replicatedPercent} of replicated records`); setText('mainv1AdoptedSummary', `${adoptedPercent} of adoption records`); setText('mainv1StatusLead', statusLead); setText('mainv1AdoptionLead', adoptionLead);
    const years = [...new Set(rows.map(row => Number(row.year)).filter(year => Number.isFinite(year) && year > 1900))].sort((a, b) => a - b);
    const yearCounts = years.map(year => count(row => Number(row.year) === year));
    const chartDefaults = { responsive: true, maintainAspectRatio: false, onClick: (_, elements, chartInstance) => { const index = elements[0]?.index; const label = chartInstance.data.labels?.[index]; const year = Number(label); if (Number.isFinite(year) && year > 1900 && window.openMainv1YearRecords) return window.openMainv1YearRecords(year); const metricByLabel = { Ongoing: 'active', Inactive: 'inactive', Replicated: 'replicated', Adopted: 'adopted' }; if (metricByLabel[label] && window.openMainv1MetricRecords) window.openMainv1MetricRecords(metricByLabel[label]); }, onHover: (event, elements, chartInstance) => { const index = elements[0]?.index; const label = chartInstance.data.labels?.[index]; const year = Number(label); const metricByLabel = { Ongoing: true, Inactive: true, Replicated: true, Adopted: true }; chartInstance.canvas.style.cursor = (Number.isFinite(year) && year > 1900) || metricByLabel[label] ? 'pointer' : 'default'; }, plugins: { legend: { display: false } } };
    const chart = (id, config) => { const canvas = document.getElementById(id); return canvas && window.Chart ? new Chart(canvas, config) : null; };
    chart('mainv1StatusChart', { type: 'line', data: { labels: years, datasets: [{ label: 'Ongoing STs', data: years.map(year => count(row => Number(row.year) === year && row.status === 'ongoing')), borderColor: '#42b9ba', backgroundColor: 'rgba(66,185,186,.12)', fill: true, tension: .35 }, { label: 'Inactive STs', data: years.map(year => count(row => Number(row.year) === year && (row.status === 'inactive' || row.status === 'dissolved'))), borderColor: '#ff6682', backgroundColor: 'rgba(255,102,130,.08)', fill: true, tension: .35 }] }, options: { ...chartDefaults, scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { grid: { display: false } } }, plugins: { legend: { display: true, position: 'top', labels: { boxWidth: 22, font: { size: 10 } } } } } });
    const titleCounts = Object.entries(rows.reduce((result, row) => { const title = String(row.title || 'Untitled social technology').trim() || 'Untitled social technology'; result[title] = (result[title] || 0) + 1; return result; }, {})).sort((a, b) => b[1] - a[1] || a[0].localeCompare(b[0]));
    const titleColors = ['#12a8b0','#20d8ad','#9bdde7','#ffb347','#ee5b91','#8d70cf','#55b7a8','#f08080','#61a7e5','#a78b7a','#c4a7d9','#7abf7b'];
    const titleLegend = document.getElementById('mainv1TitleCountLegend');
    const titlePrevious = document.getElementById('mainv1TitlePrevious');
    const titleNext = document.getElementById('mainv1TitleNext');
    const titlePage = document.getElementById('mainv1TitlePage');
    const titleReferenceCount = document.getElementById('mainv1TitleReferenceCount');
    const titlePageSize = 13;
    let titlePageNumber = 1;
    let titleChart = null;
    const highlightTitleChart = (title) => {
        if (!titleChart) return;
        const largerIndex = largerTitleCounts.findIndex(item => item[0] === title);
        const smallerIndex = smallerTitleCounts.findIndex(item => item[0] === title);
        const active = largerIndex >= 0 ? [{ datasetIndex: 0, index: largerIndex }] : (smallerIndex >= 0 ? [{ datasetIndex: 1, index: smallerIndex }] : []);
        titleChart.setActiveElements(active);
        titleChart.tooltip?.setActiveElements(active, { x: 0, y: 0 });
        titleChart.update();
    };
    const clearTitleChartHighlight = () => { if (titleChart) { titleChart.setActiveElements([]); titleChart.tooltip?.setActiveElements([], { x: 0, y: 0 }); titleChart.update(); } };
    const renderTitlePage = () => {
        if (!titleLegend) return;
        const totalTitleRecords = titleCounts.reduce((sum, item) => sum + item[1], 0);
        const totalPages = Math.max(1, Math.ceil(titleCounts.length / titlePageSize));
        titlePageNumber = Math.min(Math.max(titlePageNumber, 1), totalPages);
        titleLegend.replaceChildren();
        titleCounts.slice((titlePageNumber - 1) * titlePageSize, titlePageNumber * titlePageSize).forEach((item, index) => {
            const absoluteIndex = (titlePageNumber - 1) * titlePageSize + index;
            const entry = document.createElement('div'); entry.className = 'mainv1-title-count-legend-item';
            const swatch = document.createElement('i'); swatch.style.backgroundColor = titleColors[absoluteIndex % titleColors.length];
            const label = document.createElement('span'); label.textContent = item[0];
            const value = document.createElement('strong'); value.textContent = `${totalTitleRecords ? ((item[1] / totalTitleRecords) * 100).toFixed(1) : '0.0'}% (${item[1]})`;
            entry.dataset.title = item[0]; entry.tabIndex = 0;
            entry.addEventListener('mouseenter', () => { entry.classList.add('is-highlighted'); highlightTitleChart(item[0]); });
            entry.addEventListener('mouseleave', () => { entry.classList.remove('is-highlighted'); clearTitleChartHighlight(); });
            entry.addEventListener('focus', () => { entry.classList.add('is-highlighted'); highlightTitleChart(item[0]); });
            entry.addEventListener('blur', () => { entry.classList.remove('is-highlighted'); clearTitleChartHighlight(); });
            entry.addEventListener('click', () => openTitleRecords(item[0]));
            entry.addEventListener('keydown', event => { if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); openTitleRecords(item[0]); } });
            entry.append(swatch, label, value); titleLegend.appendChild(entry);
        });
        if (titlePage) titlePage.textContent = `Page ${titlePageNumber} of ${totalPages}`;
        if (titleReferenceCount) titleReferenceCount.textContent = `${titleCounts.length} titles`;
        if (titlePrevious) titlePrevious.disabled = titlePageNumber === 1;
        if (titleNext) titleNext.disabled = titlePageNumber === totalPages;
    };
    if (titleLegend) {
        renderTitlePage();
    }
    titlePrevious?.addEventListener('click', () => { titlePageNumber -= 1; renderTitlePage(); });
    titleNext?.addEventListener('click', () => { titlePageNumber += 1; renderTitlePage(); });
    const totalTitleRecords = titleCounts.reduce((sum, item) => sum + item[1], 0);
    const titleShare = item => totalTitleRecords ? (item[1] / totalTitleRecords) * 100 : 0;
    const largerTitleCounts = titleCounts.filter(item => titleShare(item) > 0.5);
    const smallerTitleCounts = titleCounts.filter(item => titleShare(item) <= 0.5);
    titleChart = chart('mainv1TitleCountChart', { type: 'doughnut', data: { labels: titleCounts.map(item => item[0]), datasets: [{ label: 'Titles above 0.5%', data: largerTitleCounts.map(item => item[1]), backgroundColor: largerTitleCounts.map((_, index) => titleColors[index % titleColors.length]), borderColor: '#fff', borderWidth: 2, hoverOffset: 8, hoverBorderWidth: 3 }, { label: 'Titles at or below 0.5%', data: smallerTitleCounts.map(item => item[1]), backgroundColor: smallerTitleCounts.map((_, index) => titleColors[(index + largerTitleCounts.length) % titleColors.length]), borderColor: '#fff', borderWidth: 2, hoverOffset: 8, hoverBorderWidth: 3 }] }, options: { ...chartDefaults, cutout: '48%', onClick: (_, elements) => { const element = elements[0]; if (!element) return; const source = element.datasetIndex === 1 ? smallerTitleCounts : largerTitleCounts; const title = source[element.index]?.[0]; if (title) openTitleRecords(title); }, onHover: (event, elements, chartInstance) => { chartInstance.canvas.style.cursor = elements.length ? 'pointer' : 'default'; }, plugins: { legend: { display: false }, tooltip: { callbacks: { label: context => { const source = context.datasetIndex === 1 ? smallerTitleCounts : largerTitleCounts; const title = source[context.dataIndex]?.[0] || ''; return ` ${title}: ${context.parsed} social technolog${context.parsed === 1 ? 'y' : 'ies'}`; } } } } } });
    chart('mainv1YearChart', { type: 'bar', data: { labels: years, datasets: [{ data: yearCounts, backgroundColor: ['#2db6b0','#42b9ba','#77cce0','#ffbd51','#8b82e8','#ee70aa'], borderRadius: 4 }] }, options: { ...chartDefaults, scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { grid: { display: false } } } } });
    chart('mainv1StatusDonut', { type: 'doughnut', data: { labels: ['Ongoing', 'Inactive'], datasets: [{ data: [ongoing, inactive], backgroundColor: ['#42b9ba','#ff6682'], borderWidth: 0 }] }, options: { ...chartDefaults, cutout: '68%' } });
    chart('mainv1AdoptionDonut', { type: 'doughnut', data: { labels: ['Replicated', 'Adopted'], datasets: [{ data: [replicated, adopted], backgroundColor: ['#3398dc','#ffc34f'], borderWidth: 0 }] }, options: { ...chartDefaults, cutout: '68%' } });
    const peak = yearCounts.reduce((best, value, index) => value > best.value ? { year: years[index], value } : best, { year: '-', value: 0 });
    const latest = years.length ? years[years.length - 1] : '-';
    setText('mainv1PeakYear', peak.year); setText('mainv1PeakMeta', peak.value ? `${peak.value} recorded MOAs` : 'No records yet'); setText('mainv1AverageYear', yearCounts.length ? (yearCounts.reduce((sum, value) => sum + value, 0) / yearCounts.length).toFixed(1) : '-'); setText('mainv1LatestYear', latest); setText('mainv1LatestMeta', latest === '-' ? 'No records yet' : `${yearCounts[yearCounts.length - 1]} recorded MOAs`);
    const grouped = (key) => Object.entries(rows.reduce((result, row) => { const value = row[key] || 'Unspecified'; result[value] = (result[value] || 0) + 1; return result; }, {})).sort((a, b) => b[1] - a[1]);
    const ranking = (id, values) => { const node = document.getElementById(id); if (node) node.innerHTML = values.slice(0, 5).map((entry, index) => `<div class="mainv1-ranking-item"><em>#${index + 1}</em><span>${entry[0]}</span><b>${entry[1]}</b></div>`).join('') || '<div class="mainv1-ranking-item">No records found</div>'; };
    ranking('mainv1TopRegions', grouped('region')); ranking('mainv1TopProvinces', grouped('province'));
    const coverage = [['Expression of Interest', count(row => truthy(row.expr))], ['SB Resolution', count(row => truthy(row.res))], ['Memorandum of Agreement', count(row => truthy(row.moa))], ['Ongoing STs', ongoing], ['Inactive STs', inactive], ['Replicated STs', replicated], ['Adopted STs', adopted]]; const coverageMax = Math.max(1, ...coverage.map(item => item[1])); document.getElementById('mainv1Coverage').innerHTML = coverage.map(item => `<div class="mainv1-coverage-item"><span>${item[0]}</span><div class="mainv1-coverage-bar"><i style="width:${(item[1] / coverageMax) * 100}%"></i></div><b>${item[1]}</b></div>`).join('');
    const heatmap = document.getElementById('mainv1Heatmap'); const heatYears = years.slice(-8); const regionRows = grouped('region').slice(0, 10); if (heatmap) heatmap.innerHTML = regionRows.map(entry => `<div class="mainv1-heatmap-row"><span class="mainv1-heatmap-label">${entry[0]}</span>${heatYears.map(year => { const value = count(row => row.region === entry[0] && Number(row.year) === year); return `<i class="mainv1-heat-cell" data-level="${value >= 5 ? 3 : value >= 2 ? 2 : value ? 1 : 0}" title="${entry[0]} ${year}: ${value}"></i>`; }).join('')}</div>`).join('') || '<small>No regional year records found.</small>';
    const pageSize = 8; let page = 1; const search = document.getElementById('mainv1DirectorySearch'); const status = document.getElementById('mainv1DirectoryStatus'); const type = document.getElementById('mainv1DirectoryType'); const filtered = () => rows.filter(row => (!search.value || String(row.title || '').toLowerCase().includes(search.value.toLowerCase())) && (!status.value || (status.value === 'ongoing' ? row.status === 'ongoing' : row.status === 'inactive' || row.status === 'dissolved')) && (!type.value || truthy(row[type.value]))); const renderDirectory = () => { const data = filtered(); const pages = Math.max(1, Math.ceil(data.length / pageSize)); page = Math.min(page, pages); const visible = data.slice((page - 1) * pageSize, page * pageSize); const body = document.getElementById('mainv1DirectoryRows'); body.replaceChildren(); visible.forEach(row => { const tableRow = document.createElement('tr'); const values = [row.title || 'Untitled', row.province || '-', row.municipality || '-']; values.forEach(value => { const cell = document.createElement('td'); cell.textContent = value; tableRow.appendChild(cell); }); const statusCell = document.createElement('td'); const statusPill = document.createElement('span'); statusPill.className = `mainv1-status-pill ${row.status === 'ongoing' ? 'ongoing' : 'inactive'}`; statusPill.textContent = row.status === 'ongoing' ? 'Ongoing' : 'Inactive'; statusCell.appendChild(statusPill); tableRow.appendChild(statusCell); const coverageCell = document.createElement('td'); coverageCell.textContent = [row.expr && 'EOI', row.res && 'Resolution', row.moa && 'MOA', row.replicated && 'Replicated', row.adopted && 'Adopted'].filter(Boolean).join(', ') || '-'; tableRow.appendChild(coverageCell); body.appendChild(tableRow); }); if (!visible.length) { const emptyRow = document.createElement('tr'); const emptyCell = document.createElement('td'); emptyCell.colSpan = 5; emptyCell.textContent = 'No records match the current filters.'; emptyRow.appendChild(emptyCell); body.appendChild(emptyRow); } setText('mainv1DirectorySummary', `${data.length} records`); setText('mainv1DirectoryPage', `Page ${page} of ${pages}`); document.getElementById('mainv1DirectoryPrev').disabled = page <= 1; document.getElementById('mainv1DirectoryNext').disabled = page >= pages; }; [search, status, type].filter(Boolean).forEach(input => input.addEventListener('input', () => { page = 1; renderDirectory(); })); document.getElementById('mainv1DirectoryPrev').addEventListener('click', () => { page -= 1; renderDirectory(); }); document.getElementById('mainv1DirectoryNext').addEventListener('click', () => { page += 1; renderDirectory(); });
    const provinceFilter = document.getElementById('mainv1DirectoryProvince');
    const municipalityFilter = document.getElementById('mainv1DirectoryMunicipality');
    const yearFilter = document.getElementById('mainv1DirectoryYear');
    const exportButton = document.getElementById('mainv1DirectoryExport');
    const directorySummary = document.getElementById('mainv1DirectorySummary');
    const directoryPage = document.getElementById('mainv1DirectoryPage');
    const directoryPrevious = document.getElementById('mainv1DirectoryPrev');
    const directoryNext = document.getElementById('mainv1DirectoryNext');
    const directoryValues = key => [...new Set(rows.map(row => row[key]).filter(value => value !== null && value !== undefined && String(value).trim() !== '').map(String))].sort((a, b) => a.localeCompare(b, undefined, { numeric: true }));
    const addOptions = (select, values) => values.forEach(value => { const option = document.createElement('option'); option.value = value; option.textContent = value; select.appendChild(option); });
    if (provinceFilter && municipalityFilter && yearFilter) { addOptions(provinceFilter, directoryValues('province')); addOptions(municipalityFilter, directoryValues('municipality')); addOptions(yearFilter, directoryValues('year')); }
    let directoryFilteredPage = 1;
    const directoryFilteredRows = () => { const query = String(search?.value || '').trim().toLowerCase(); return rows.filter(row => (!query || String(row.title || '').toLowerCase().includes(query)) && (!provinceFilter?.value || String(row.province || '') === provinceFilter.value) && (!municipalityFilter?.value || String(row.municipality || '') === municipalityFilter.value) && (!yearFilter?.value || String(row.year || '') === yearFilter.value) && (!status?.value || (status.value === 'ongoing' ? row.status === 'ongoing' : row.status === 'inactive' || row.status === 'dissolved')) && (!type?.value || truthy(row[type.value]))); };
    const renderFilteredDirectory = () => { const data = directoryFilteredRows(); const pages = Math.max(1, Math.ceil(data.length / pageSize)); directoryFilteredPage = Math.min(directoryFilteredPage, pages); const visible = data.slice((directoryFilteredPage - 1) * pageSize, directoryFilteredPage * pageSize); const body = document.getElementById('mainv1DirectoryRows'); body?.replaceChildren(); visible.forEach(row => { const tableRow = document.createElement('tr'); tableRow.className = 'mainv1-directory-row'; tableRow.tabIndex = 0; tableRow.setAttribute('role', 'button'); tableRow.setAttribute('aria-label', `View ${row.title || 'social technology'} details`); [row.title || 'Untitled', row.province || '-', row.municipality || '-', row.status === 'ongoing' ? 'Ongoing' : 'Inactive', [row.expr && 'EOI', row.res && 'Resolution', row.moa && 'MOA', row.replicated && 'Replicated', row.adopted && 'Adopted'].filter(Boolean).join(', ') || '-'].forEach((value, index) => { const cell = document.createElement('td'); if (index === 3) { const pill = document.createElement('span'); pill.className = `mainv1-status-pill ${row.status === 'ongoing' ? 'ongoing' : 'inactive'}`; pill.textContent = value; cell.appendChild(pill); } else { cell.textContent = value; } tableRow.appendChild(cell); }); const open = () => openDetailModal(row); tableRow.addEventListener('click', open); tableRow.addEventListener('keydown', event => { if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); open(); } }); body?.appendChild(tableRow); }); if (directorySummary) directorySummary.textContent = `${data.length} records`; if (directoryPage) directoryPage.textContent = `Page ${directoryFilteredPage} of ${pages}`; if (directoryPrevious) directoryPrevious.disabled = directoryFilteredPage <= 1; if (directoryNext) directoryNext.disabled = directoryFilteredPage >= pages; };
    [search, provinceFilter, municipalityFilter, yearFilter, status, type].filter(Boolean).forEach(control => control.addEventListener('input', () => { directoryFilteredPage = 1; renderFilteredDirectory(); }));
    [provinceFilter, municipalityFilter, yearFilter, status, type].filter(Boolean).forEach(control => control.addEventListener('change', () => { directoryFilteredPage = 1; renderFilteredDirectory(); }));
    directoryPrevious?.addEventListener('click', () => { directoryFilteredPage -= 1; renderFilteredDirectory(); }); directoryNext?.addEventListener('click', () => { directoryFilteredPage += 1; renderFilteredDirectory(); });
    exportButton?.addEventListener('click', () => { const escapeCsv = value => `"${String(value ?? '').replace(/"/g, '""')}"`; const csvRows = [['Social Technology Title', 'Province', 'City / Municipality', 'Year of MOA', 'Region', 'Status', 'Expression of Interest', 'SB Resolution', 'MOA', 'Replicated', 'Adopted'], ...directoryFilteredRows().map(row => [row.title, row.province, row.municipality, row.year, row.region, row.status === 'ongoing' ? 'Ongoing' : 'Inactive', truthy(row.expr) ? 'Yes' : 'No', truthy(row.res) ? 'Yes' : 'No', truthy(row.moa) ? 'Yes' : 'No', truthy(row.replicated) ? 'Yes' : 'No', truthy(row.adopted) ? 'Yes' : 'No'])].map(row => row.map(escapeCsv).join(',')); const blob = new Blob([csvRows.join('\r\n')], { type: 'text/csv;charset=utf-8;' }); const url = URL.createObjectURL(blob); const link = document.createElement('a'); link.href = url; link.download = `social-technologies-${new Date().toISOString().slice(0, 10)}.csv`; document.body.appendChild(link); link.click(); link.remove(); URL.revokeObjectURL(url); });
    const directoryBody = document.getElementById('mainv1DirectoryRows');
    const enhanceOperationalCells = () => { directoryBody?.querySelectorAll('tr').forEach(tableRow => { const cell = tableRow.cells[3]; if (!cell || cell.dataset.operationalEnhanced === 'true') return; const row = rows.find(candidate => String(candidate.title || '').trim().toLowerCase() === String(tableRow.cells[0]?.textContent || '').trim().toLowerCase() && String(candidate.province || '') === String(tableRow.cells[1]?.textContent || '') && String(candidate.municipality || '') === String(tableRow.cells[2]?.textContent || '')); if (!row || String(row.operational_status || 'Operational') === 'Operational') { if (cell) cell.dataset.operationalEnhanced = 'true'; return; } cell.style.display = 'flex'; cell.style.flexDirection = 'column'; cell.style.alignItems = 'flex-start'; cell.style.gap = '.3rem'; const indicator = document.createElement('span'); indicator.className = 'mainv1-operational-indicator'; indicator.textContent = 'Not Operational'; indicator.title = 'This program is no longer operational'; indicator.setAttribute('aria-label', 'This program is no longer operational'); indicator.style.cssText = 'display:inline-flex;align-items:center;width:max-content;margin:0;padding:.25rem .5rem;border-radius:99px;background:#fee2e2;color:#b91c1c;font-size:.64rem;font-weight:800;line-height:1.2;white-space:nowrap;'; cell.appendChild(indicator); cell.dataset.operationalEnhanced = 'true'; }); };
    const enhanceCoverageCells = () => { directoryBody?.querySelectorAll('tr').forEach(tableRow => { const cell = tableRow.cells[4]; if (!cell || cell.dataset.coverageEnhanced === 'true') return; const labels = cell.textContent.split(',').map(value => value.trim()).filter(Boolean); cell.replaceChildren(); if (!labels.length || labels[0] === '-') { const empty = document.createElement('span'); empty.className = 'mainv1-coverage-empty'; empty.textContent = 'None'; cell.appendChild(empty); } else { const badges = document.createElement('div'); badges.className = 'mainv1-coverage-badges'; labels.forEach(label => { const badge = document.createElement('span'); const className = { EOI: 'coverage-eoi', Resolution: 'coverage-resolution', MOA: 'coverage-moa', Replicated: 'coverage-replicated', Adopted: 'coverage-adopted' }[label]; badge.className = `mainv1-coverage-badge ${className || ''}`; badge.textContent = label; badges.appendChild(badge); }); cell.appendChild(badges); } cell.dataset.coverageEnhanced = 'true'; }); };
    const normalizeOperationalCellLayout = () => { directoryBody?.querySelectorAll('td[data-operational-enhanced="true"]').forEach(cell => { cell.style.display = 'table-cell'; cell.style.verticalAlign = 'middle'; cell.style.whiteSpace = 'normal'; cell.style.overflow = 'visible'; }); };
    const addDirectoryAttachmentCells = () => { directoryBody?.querySelectorAll('tr').forEach(tableRow => { if (tableRow.cells.length !== 5) return; const title = tableRow.cells[0].textContent; const row = rows.find(candidate => candidate.title === title && candidate.province === tableRow.cells[1].textContent && candidate.municipality === tableRow.cells[2].textContent); const cell = document.createElement('td'); if (row?.attachment_url) { const button = document.createElement('button'); button.type = 'button'; button.className = 'mainv1-attachment-button'; button.textContent = 'View attachment'; button.addEventListener('click', () => openAttachmentModal(row)); cell.appendChild(button); } else { const empty = document.createElement('span'); empty.className = 'mainv1-attachment-empty'; empty.textContent = 'None'; cell.appendChild(empty); } tableRow.appendChild(cell); }); };
    const directoryObserver = directoryBody ? new MutationObserver(addDirectoryAttachmentCells) : null;
    directoryObserver?.observe(directoryBody, { childList: true });
    const operationalObserver = directoryBody ? new MutationObserver(enhanceOperationalCells) : null;
    operationalObserver?.observe(directoryBody, { childList: true, subtree: true });
    const operationalLayoutObserver = directoryBody ? new MutationObserver(normalizeOperationalCellLayout) : null;
    operationalLayoutObserver?.observe(directoryBody, { childList: true, subtree: true });
    const coverageObserver = directoryBody ? new MutationObserver(enhanceCoverageCells) : null;
    coverageObserver?.observe(directoryBody, { childList: true, subtree: true });
    renderFilteredDirectory();
    addDirectoryAttachmentCells();
    enhanceOperationalCells();
    normalizeOperationalCellLayout();
    enhanceCoverageCells();
    attachmentModal?.querySelector('.mainv1-st-detail-close')?.addEventListener('click', closeAttachmentModal);
    attachmentModal?.addEventListener('click', event => { if (event.target === attachmentModal) closeAttachmentModal(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && attachmentModal?.classList.contains('is-open')) closeAttachmentModal(); });
})();
</script>
<script>
    (() => {
        document.querySelectorAll('[data-regional-carousel]').forEach((carousel) => {
            const track = carousel.querySelector('.mainv1-regional-track');
            const previous = carousel.querySelector('[data-carousel-direction="prev"]');
            const next = carousel.querySelector('[data-carousel-direction="next"]');
            if (!track || !previous || !next) return;
            const cards = [...track.querySelectorAll('.mainv1-regional-card')];
            if (!cards.length) return;
            const modal = document.getElementById('mainv1RegionModal');
            if (modal && modal.parentElement !== document.body) document.body.appendChild(modal);
            const modalImage = document.getElementById('mainv1RegionModalImage');
            const modalTitle = document.getElementById('mainv1RegionModalTitle');
            const replicationRedirectUrl = @json($replicationRedirectUrl);
            const regionRows = @json($mainv1RegionRows);
            const filterElements = Object.fromEntries(
                [...document.querySelectorAll('[data-region-filter]')]
                    .map((element) => [element.dataset.regionFilter, element])
            );
            const activeFilters = { province: [], municipality: [], year: [] };
            let activeFilterCleanup = null;

            const isTrue = (value) => ['1', 'true', 'yes', 'y'].includes(String(value ?? '').trim().toLowerCase());
            const openRegionModal = (card) => {
                if (!modal) return false;
                const region = card.dataset.regionName || '';
                const normalizeRegion = (value) => String(value || '').toLowerCase().replace(/^fo\s*/, '').replace(/^region\s*/, '').replace(/\s+/g, '');
                const baseRows = regionRows.filter((row) => normalizeRegion(row.region) === normalizeRegion(region));
                modalTitle.textContent = region;
                modalImage.src = card.dataset.regionImage || '';
                modalImage.alt = region + ' map';
                modal.classList.add('is-open');
                const renderRows = (rows) => {
                const titleCounts = {};
                rows.forEach((row) => { titleCounts[row.title] = (titleCounts[row.title] || 0) + 1; });
                const sortedTitles = Object.entries(titleCounts).sort((left, right) => right[1] - left[1] || left[0].localeCompare(right[0]));
                const count = (key) => rows.filter((row) => isTrue(row[key])).length;
                const inactive = rows.filter((row) => String(row.status ?? '').toLowerCase().includes('inactive') || String(row.status ?? '').toLowerCase().includes('dissolved')).length;
                const metrics = [
                    ['Total STs', rows.length], ['MOA Attachments', 0],
                    ['Total Expression of Interest', count('expr')], ['Total Replicated', count('replicated')],
                    ['SB Resolutions', count('res')], ['Total Adopted', count('adopted')], ['Total MOA', count('moa')],
                ];
                document.getElementById('mainv1RegionOngoing').textContent = rows.length - inactive;
                document.getElementById('mainv1RegionInactive').textContent = inactive;
                document.getElementById('mainv1RegionUniqueTitles').textContent = 'Unique titles: ' + sortedTitles.length;
                document.getElementById('mainv1RegionTotalSts').textContent = 'Total STs: ' + rows.length;
                document.getElementById('mainv1RegionTitlesHeading').textContent = 'ST Titles for ' + region;
                document.getElementById('mainv1RegionMetrics').innerHTML = metrics.map(([label, value]) => '<div class="mainv1-region-metric">' + label + '<strong>' + value + '</strong></div>').join('');
                document.getElementById('mainv1RegionTitleList').innerHTML = sortedTitles.length
                    ? sortedTitles.map(([title, value], titleIndex) => {
                        const titleRows = rows.filter((row) => row.title === title);
                        const locations = [...new Map(titleRows.map((row) => [String(row.province || '') + '|' + String(row.municipality || '') + '|' + String(row.status || ''), row])).values()];
                        const detailRows = locations.map((row) => {
                            const statusText = String(row.status || '').toLowerCase().includes('inactive') || String(row.status || '').toLowerCase().includes('dissolved') ? 'Inactive' : 'Active';
                            const statusClass = statusText === 'Inactive' ? ' is-inactive' : '';
                            return '<div class="mainv1-region-title-location"><strong>' + escapeHtml(row.province || 'No province') + '</strong><span>' + escapeHtml(row.municipality || 'No city/municipality') + '</span><span class="mainv1-region-title-status' + statusClass + '">' + statusText + '</span></div>';
                        }).join('');
                        return '<div class="mainv1-region-title-row" data-title-index="' + titleIndex + '" data-title="' + escapeHtml(title) + '" role="button" tabindex="0"><span class="mainv1-region-title-count">' + value + '</span><span>' + escapeHtml(title) + '</span><span class="mainv1-region-title-arrow">&#9662;</span><div class="mainv1-region-title-details">' + detailRows + '</div><div class="mainv1-replicate-popover" role="dialog" aria-label="Replicate ST confirmation"><strong class="mainv1-replicate-popover-title">' + escapeHtml(title) + '</strong><span>Do you want to replicate this ST?</span><div class="mainv1-replicate-popover-actions"><button type="button" class="mainv1-replicate-cancel">No</button><button type="button" class="mainv1-replicate-confirm">Yes, replicate</button></div></div></div>';
                    }).join('')
                    : '<div class="mainv1-region-title-row">No records found.</div>';
                document.querySelectorAll('#mainv1RegionTitleList .mainv1-region-title-row[data-title-index]').forEach((titleRow) => {
                    const toggleTitle = () => titleRow.classList.toggle('is-expanded');
                    titleRow.addEventListener('click', toggleTitle);
                    titleRow.querySelector('.mainv1-replicate-popover')?.addEventListener('click', (event) => event.stopPropagation());
                    titleRow.querySelector('.mainv1-replicate-cancel')?.addEventListener('click', (event) => {
                        event.stopPropagation();
                        titleRow.classList.remove('is-replicate-open');
                    });
                    titleRow.querySelector('.mainv1-replicate-confirm')?.addEventListener('click', (event) => {
                        event.stopPropagation();
                        titleRow.classList.remove('is-replicate-open');
                        window.dispatchEvent(new CustomEvent('st-replication-confirmed', { detail: { title: titleRow.dataset.title || '' } }));
                        window.openMainv1ReplicationConfirm?.();
                    });
                    titleRow.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            toggleTitle();
                        }
                    });
                });
                const chart = document.getElementById('mainv1RegionChart');
                if (chart) {
                    const context = chart.getContext('2d');
                    const width = chart.clientWidth;
                    const height = 250;
                    const pixelRatio = window.devicePixelRatio || 1;
                    chart.width = Math.max(1, Math.floor(width * pixelRatio));
                    chart.height = Math.floor(height * pixelRatio);
                    context.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);
                    const left = 38;
                    const right = 10;
                    const top = 14;
                    const bottom = 34;
                    const chartWidth = width - left - right;
                    const chartHeight = height - top - bottom;
                    const chartValues = [0, count('moa'), count('res'), count('expr')];
                    const maxValue = Math.max(5, ...chartValues, rows.length);
                    const points = chartValues.map((value, index) => ({
                        x: left + (chartWidth * index / (chartValues.length - 1)),
                        y: top + chartHeight - (value / maxValue * chartHeight),
                    }));
                    context.clearRect(0, 0, width, height);
                    context.strokeStyle = '#dbe5ec';
                    context.fillStyle = '#688096';
                    context.font = '11px Arial';
                    for (let tick = 0; tick <= 4; tick += 1) {
                        const y = top + chartHeight - (chartHeight * tick / 4);
                        context.beginPath();
                        context.moveTo(left, y);
                        context.lineTo(width - right, y);
                        context.stroke();
                        context.fillText(String(Math.round(maxValue * tick / 4)), 7, y + 4);
                    }
                    context.beginPath();
                    points.forEach((point, index) => index ? context.lineTo(point.x, point.y) : context.moveTo(point.x, point.y));
                    context.lineTo(points[points.length - 1].x, top + chartHeight);
                    context.lineTo(points[0].x, top + chartHeight);
                    context.closePath();
                    context.fillStyle = 'rgba(74, 123, 232, 0.2)';
                    context.fill();
                    context.beginPath();
                    points.forEach((point, index) => index ? context.lineTo(point.x, point.y) : context.moveTo(point.x, point.y));
                    context.strokeStyle = '#4a7be8';
                    context.lineWidth = 2;
                    context.stroke();
                    points.forEach((point, index) => {
                        context.fillStyle = '#4a7be8';
                        context.beginPath();
                        context.arc(point.x, point.y, 3, 0, Math.PI * 2);
                        context.fill();
                        context.fillStyle = '#516b84';
                        context.textAlign = 'center';
                        context.fillText(['Upload MOA', 'Total MOA', 'SB Res', 'Expr Interest'][index], point.x, height - 10);
                    });
                }
                };
                renderRows(baseRows);
                activeFilterCleanup?.();
                const updateFilteredRows = () => {
                    const filteredRows = baseRows.filter((row) =>
                        (!activeFilters.province.length || activeFilters.province.includes(String(row.province || '')))
                        && (!activeFilters.municipality.length || activeFilters.municipality.includes(String(row.municipality || '')))
                        && (!activeFilters.year.length || activeFilters.year.includes(String(row.year || '')))
                    );
                    renderRows(filteredRows);
                };
                const cleanupHandlers = [];
                Object.entries(filterElements).forEach(([key, element]) => {
                    activeFilters[key] = [];
                    const trigger = element.querySelector('.mainv1-region-select-trigger');
                    const menu = element.querySelector('.mainv1-region-select-menu');
                    const selected = element.querySelector('.mainv1-region-select-selected');
                    const options = element.querySelector('.mainv1-region-select-options');
                    const search = element.querySelector('.mainv1-region-select-search');
                    const values = [...new Set(baseRows.map((row) => String(row[key] ?? '')).filter(Boolean))]
                        .sort((left, right) => key === 'year' ? right.localeCompare(left, undefined, { numeric: true }) : left.localeCompare(right));
                    const renderDropdown = () => {
                        const query = search.value.trim().toLowerCase();
                        selected.replaceChildren();
                        if (!activeFilters[key].length) {
                            const empty = document.createElement('div');
                            empty.className = 'mainv1-region-select-selected-empty';
                            empty.textContent = 'Nothing selected';
                            selected.append(empty);
                        } else {
                            activeFilters[key].forEach((value) => {
                                const item = document.createElement('div');
                                item.className = 'mainv1-region-select-selected-item';
                                const text = document.createElement('span');
                                text.textContent = value;
                                const remove = document.createElement('button');
                                remove.type = 'button';
                                remove.textContent = '×';
                                remove.addEventListener('click', () => {
                                    activeFilters[key] = activeFilters[key].filter((selectedValue) => selectedValue !== value);
                                    renderDropdown();
                                    updateFilteredRows();
                                });
                                item.append(text, remove);
                                selected.append(item);
                            });
                        }
                        trigger.textContent = activeFilters[key].length ? `${activeFilters[key].length} selected` : (key === 'province' ? 'All provinces' : key === 'municipality' ? 'All municipalities' : 'All years');
                        options.replaceChildren();
                        values.filter((value) => value.toLowerCase().includes(query)).forEach((value) => {
                            const label = document.createElement('label');
                            label.className = 'mainv1-region-select-option';
                            const checkbox = document.createElement('input');
                            checkbox.type = 'checkbox';
                            checkbox.value = value;
                            checkbox.checked = activeFilters[key].includes(value);
                            const text = document.createElement('span');
                            text.textContent = value;
                            label.append(checkbox, text);
                            options.append(label);
                        });
                    };
                    const toggleMenu = (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        document.querySelectorAll('.mainv1-region-select.is-open').forEach((openElement) => {
                            if (openElement !== element) openElement.classList.remove('is-open');
                        });
                        element.classList.toggle('is-open');
                    };
                    const handleChange = (event) => {
                        if (!event.target.matches('input[type="checkbox"]')) return;
                        activeFilters[key] = [...options.querySelectorAll('input:checked')].map((input) => input.value);
                        renderDropdown();
                        updateFilteredRows();
                    };
                    const handleSearch = () => renderDropdown();
                    trigger.addEventListener('click', toggleMenu);
                    options.addEventListener('change', handleChange);
                    search.addEventListener('input', handleSearch);
                    renderDropdown();
                    cleanupHandlers.push(() => {
                        trigger.removeEventListener('click', toggleMenu);
                        options.removeEventListener('change', handleChange);
                        search.removeEventListener('input', handleSearch);
                    });
                });
                const closeMenus = (event) => {
                    if (event.target.closest('.mainv1-region-select-menu') || event.target.closest('.mainv1-region-select-trigger')) return;
                    document.querySelectorAll('.mainv1-region-select.is-open').forEach((element) => element.classList.remove('is-open'));
                };
                document.addEventListener('click', closeMenus);
                cleanupHandlers.push(() => document.removeEventListener('click', closeMenus));
                activeFilterCleanup = () => cleanupHandlers.forEach((cleanup) => cleanup());
                document.body.style.overflow = 'hidden';
                return true;
            };
            window.openMainv1RegionModal = openRegionModal;
            const closeRegionModal = () => {
                modal?.classList.remove('is-open');
                document.body.style.overflow = '';
            };
            modal?.addEventListener('click', (event) => { if (event.target === modal) closeRegionModal(); });
            modal?.querySelector('[data-close-region-modal]')?.addEventListener('click', closeRegionModal);
            document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeRegionModal(); });

            let activeIndex = 0;
            let paused = false;
            let dragStartX = null;
            let dragOriginIndex = 0;
            let suppressClick = false;

            const render = () => {
                const spacing = Math.min(190, Math.max(125, track.clientWidth * 0.16));
                const half = cards.length / 2;
                cards.forEach((card, index) => {
                    let offset = index - activeIndex;
                    if (offset > half) offset -= cards.length;
                    if (offset < -half) offset += cards.length;
                    const distance = Math.abs(offset);
                    const visible = distance <= 5;
                    const scale = distance === 0 ? 1.55 : distance === 1 ? 0.86 : distance === 2 ? 0.65 : 0.5;
                    const opacity = distance === 0 ? 1 : distance === 1 ? 0.82 : distance === 2 ? 0.5 : distance === 3 ? 0.3 : distance === 4 ? 0.18 : 0.1;
                    card.classList.toggle('is-carousel-center', distance === 0);
                    card.classList.toggle('is-carousel-visible', visible);
                    card.style.opacity = visible ? opacity : '0';
                    card.style.zIndex = String(10 - distance);
                    card.style.filter = distance > 2 ? 'blur(0.5px)' : 'none';
                    card.style.transform = `translateX(calc(-50% + ${offset * spacing}px)) translateY(${distance * 10}px) scale(${scale})`;
                });
            };

            const move = (direction) => {
                activeIndex = (activeIndex + direction + cards.length) % cards.length;
                render();
            };

            previous.addEventListener('click', () => move(-1));
            next.addEventListener('click', () => move(1));
            track.addEventListener('pointerdown', (event) => {
                if (event.button !== 0) return;
                if (event.target.closest('.mainv1-regional-card')) return;
                dragStartX = event.clientX;
                dragOriginIndex = activeIndex;
                paused = true;
                track.classList.add('is-dragging');
                track.setPointerCapture?.(event.pointerId);
            });
            track.addEventListener('dragstart', (event) => event.preventDefault());
            track.addEventListener('pointermove', (event) => {
                if (dragStartX === null) return;
                const distance = event.clientX - dragStartX;
                const steps = Math.min(
                    cards.length - 1,
                    Math.max(0, Math.round(Math.abs(distance) / 80))
                );
                activeIndex = (dragOriginIndex + (distance < 0 ? 1 : -1) * steps + cards.length) % cards.length;
                render();
            });
            const finishDrag = (clientX) => {
                if (dragStartX === null) return;
                const distance = clientX - dragStartX;
                if (Math.abs(distance) > 40) {
                    suppressClick = true;
                    window.setTimeout(() => { suppressClick = false; }, 0);
                }
                dragStartX = null;
                track.classList.remove('is-dragging');
                paused = false;
                render();
            };
            track.addEventListener('pointerup', (event) => finishDrag(event.clientX));
            window.addEventListener('pointerup', (event) => finishDrag(event.clientX));
            track.addEventListener('pointercancel', () => {
                dragStartX = null;
                track.classList.remove('is-dragging');
                paused = false;
                render();
            });
            track.addEventListener('keydown', (event) => {
                if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
                    event.preventDefault();
                    move(event.key === 'ArrowRight' ? 1 : -1);
                }
            });
            track.addEventListener('click', (event) => {
                const card = event.target.closest('.mainv1-regional-card');
                if (!card || suppressClick) return;
                event.preventDefault();
                event.stopPropagation();
                openRegionModal(card);
            }, true);
            cards.forEach((card, index) => {
                card.addEventListener('click', (event) => {
                    if (suppressClick) {
                        event.preventDefault();
                        return;
                    }
                    event.preventDefault();
                    openRegionModal(card);
                });
            });
            carousel.addEventListener('mouseenter', () => { paused = true; });
            carousel.addEventListener('mouseleave', () => { paused = false; });
            carousel.addEventListener('focusin', () => { paused = true; });
            carousel.addEventListener('focusout', () => { paused = false; });
            window.addEventListener('resize', render);
            window.setInterval(() => {
                if (!paused) move(1);
            }, 3200);
            render();
        });
    })();

    (() => {
        const data = @json($mainv1FilterData);
        const selected = {
            region: @json($selectedRegions ?? []),
            province: @json($selectedProvinces ?? []),
            municipality: @json($selectedMunicipalities ?? []),
            year: @json($selectedYears ?? []),
            title: @json($selectedTitles ?? []),
        };
        const filters = Object.fromEntries(
            [...document.querySelectorAll('.mainv1-multi-select')]
                .map((element) => [element.dataset.filter, element])
        );

        const uniqueSorted = (values, descending = false) => [...new Set(values.filter(Boolean).map(String))]
            .sort((a, b) => descending ? b.localeCompare(a, undefined, { numeric: true }) : a.localeCompare(b));

        const getValues = (key) => selected[key] || [];

        const rowsFor = (key) => data.filter((item) => {
            if (key === 'region') return true;
            if (getValues('region').length && !getValues('region').includes(item.region)) return false;
            if (key === 'province') return true;
            if (getValues('province').length && !getValues('province').includes(item.province)) return false;
            if (key === 'municipality') return true;
            if (getValues('municipality').length && !getValues('municipality').includes(item.municipality)) return false;
            if (key !== 'year' && getValues('year').length && !getValues('year').includes(String(item.year))) return false;
            return true;
        });

        const valuesFor = (key) => {
            const values = rowsFor(key).map((item) => item[key]);
            return uniqueSorted(values, key === 'year');
        };

        const syncHiddenInputs = () => {
            document.querySelectorAll('.mainv1-hidden-input').forEach((input) => input.remove());
            Object.entries(selected).forEach(([key, values]) => {
                values.forEach((value) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key === 'year' ? 'year_of_moa[]' : `${key}[]`;
                    input.value = value;
                    input.className = 'mainv1-hidden-input';
                    document.querySelector('.mainv1-filter').appendChild(input);
                });
            });
        };

        const render = (key, query = '') => {
            const container = filters[key];
            const options = container.querySelector('.mainv1-multi-options');
            const values = uniqueSorted([...valuesFor(key), ...getValues(key)], key === 'year');
            const searchQuery = String(query ?? '').toLowerCase();
            const filteredValues = values.filter((value) => value.toLowerCase().includes(searchQuery));
            options.replaceChildren();
            if (!filteredValues.length) {
                options.innerHTML = '<div class="mainv1-no-options">No options available</div>';
            } else {
                filteredValues.forEach((value) => {
                    const label = document.createElement('label');
                    label.className = 'mainv1-multi-option';
                    const input = document.createElement('input');
                    input.type = 'checkbox';
                    input.value = value;
                    input.checked = selected[key].includes(value);
                    const text = document.createElement('span');
                    text.textContent = value;
                    label.append(input, text);
                    options.append(label);
                });
            }
            const labelText = container.querySelector('label').textContent;
            container.querySelector('.mainv1-multi-trigger').textContent = selected[key].length
                ? `${selected[key].length} selected`
                : labelText === 'Year of MOA' ? 'All years' : `All ${key}s`;
            const selectedList = container.querySelector('.mainv1-multi-selected');
            selectedList.replaceChildren();
            if (!selected[key].length) {
                const empty = document.createElement('div');
                empty.className = 'mainv1-no-options';
                empty.textContent = 'Nothing selected';
                selectedList.append(empty);
            } else {
                selected[key].forEach((value) => {
                    const item = document.createElement('div');
                    item.className = 'mainv1-multi-selected-item';
                    const text = document.createElement('span');
                    text.textContent = value;
                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.dataset.remove = value;
                    remove.setAttribute('aria-label', `Remove ${value}`);
                    remove.textContent = '\u00d7';
                    item.append(text, remove);
                    selectedList.append(item);
                });
            }
        };

        const renderAll = () => {
            ['region', 'province', 'municipality', 'year', 'title'].forEach((key) => render(key));
            syncHiddenInputs();
        };

        Object.entries(filters).forEach(([key, container]) => {
            const trigger = container.querySelector('.mainv1-multi-trigger');
            trigger.addEventListener('click', () => {
                const isOpen = container.classList.toggle('is-open');
                trigger.setAttribute('aria-expanded', String(isOpen));
            });
            container.querySelector('.mainv1-multi-menu').addEventListener('click', (event) => {
                event.stopPropagation();
            });
            container.querySelector('.mainv1-multi-menu').addEventListener('change', (event) => {
                const value = event.target.value;
                selected[key] = event.target.checked
                    ? [...getValues(key), value]
                    : getValues(key).filter((item) => item !== value);
                render(key);
                if (key === 'region') {
                    ['province', 'municipality', 'year', 'title'].forEach((dependentKey) => render(dependentKey));
                } else if (key === 'province') {
                    ['municipality', 'year', 'title'].forEach((dependentKey) => render(dependentKey));
                } else if (key === 'municipality') {
                    ['year', 'title'].forEach((dependentKey) => render(dependentKey));
                }
                syncHiddenInputs();
            });
            container.querySelector('.mainv1-multi-selected').addEventListener('click', (event) => {
                const value = event.target.dataset.remove;
                if (value === undefined) return;
                event.stopPropagation();
                selected[key] = getValues(key).filter((item) => item !== value);
                renderAll();
            });
            container.querySelector('.mainv1-multi-search').addEventListener('input', (event) => {
                render(key, event.target.value);
            });
        });

        document.addEventListener('click', (event) => {
            Object.values(filters).forEach((container) => {
                if (!container.contains(event.target)) {
                    container.classList.remove('is-open');
                    container.querySelector('.mainv1-multi-trigger').setAttribute('aria-expanded', 'false');
                }
            });
        });

        renderAll();
    })();
</script>
<script>
    (() => {
        const initializeLeafletMap = () => {
        const mapElement = document.getElementById('mainv1GeoMap');
        const mapHint = document.getElementById('mainv1MapHint');
        const resetButton = document.getElementById('mainv1MapReset');
        if (!mapElement) return;

        const rowRegionCodes = {
            'FO I': 'Region I', 'FO CAR': 'CAR', 'FO II': 'Region II', 'FO III': 'Region III',
            'FO IV-A': 'Region IV-A', 'FO IV-B': 'Region IV-B', 'FO NCR': 'NCR', 'FO V': 'Region V',
            'FO VI': 'Region VI', 'FO VII': 'Region VII', 'FO VIII': 'Region VIII', 'FO IX': 'Region IX',
            'FO X': 'Region X', 'FO XI': 'Region XI', 'FO XII': 'Region XII', 'FO CARAGA': 'CARAGA'
        };
        const regionCoordinates = {
            'Region I': [17.25, 120.55], CAR: [17.55, 120.85], 'Region II': [17.35, 121.75],
            'Region III': [15.25, 120.75], 'Region IV-A': [14.05, 121.35], 'Region IV-B': [12.35, 121.35],
            NCR: [14.60, 120.98], 'Region V': [13.35, 123.35], 'Region VI': [10.75, 122.55],
            'Region VII': [10.25, 123.80], 'Region VIII': [11.25, 125.05], 'Region IX': [7.85, 123.35],
            'Region X': [8.55, 124.75], 'Region XI': [7.10, 125.60], 'Region XII': [6.35, 124.85],
            CARAGA: [8.75, 125.75], BARMM: [7.20, 124.35]
        };
        const colors = {
            'Region I': '#ffb74d', CAR: '#9575cd', 'Region II': '#4db6ac', 'Region III': '#81c784',
            'Region IV-A': '#f06292', 'Region IV-B': '#64b5f6', NCR: '#ff8a65', 'Region V': '#ba68c8',
            'Region VI': '#aed581', 'Region VII': '#4fc3f7', 'Region VIII': '#ffcc80', 'Region IX': '#ce93d8',
            'Region X': '#80cbc4', 'Region XI': '#ffab91', 'Region XII': '#9fa8da', CARAGA: '#a5d6a7', BARMM: '#ffecb3'
        };
        const counts = @json($mainv1RegionCounts);
        const philippinesBounds = L.latLngBounds([4.5, 116.8], [21.3, 126.7]);
        const geographicMap = L.map(mapElement, { zoomControl: true, minZoom: 5, maxZoom: 12, maxBounds: philippinesBounds.pad(0.12), maxBoundsViscosity: 1, worldCopyJump: false });
        geographicMap.fitBounds(philippinesBounds, { padding: [8, 8] });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(geographicMap);

        const markers = new Map();
        const setActiveRegion = (region) => {
            document.querySelectorAll('.mainv1-region-row').forEach((row) => {
                row.classList.toggle('is-active', row.dataset.mapRegion === region);
            });
            if (mapHint) mapHint.textContent = region ? `${region} selected` : 'Zoom and pan the Philippines map, then click a region marker to view records';
        };
        const focusRegion = (region, openPopup = false) => {
            const marker = markers.get(region);
            const coordinates = regionCoordinates[region];
            if (!marker || !coordinates) return;
            geographicMap.setView(coordinates, Math.max(geographicMap.getZoom(), 7), { animate: true });
            setActiveRegion(region);
            if (openPopup) marker.openPopup();
        };

        Object.entries(counts).forEach(([label, count]) => {
            const region = rowRegionCodes[label] || label;
            const coordinates = regionCoordinates[region];
            if (!coordinates) return;
            const color = colors[region] || '#2789d9';
            const marker = L.circleMarker(coordinates, {
                radius: Math.max(8, Math.min(18, 7 + Number(count || 0) / 12)),
                color,
                fillColor: color,
                fillOpacity: 0.82,
                weight: 2
            }).addTo(geographicMap);
            marker.bindTooltip(`${label} · ${count} records`, { direction: 'top', offset: [0, -8] });
            marker.bindPopup(`<strong>${label}</strong><br>${count} social technologies<br><button type="button" class="mainv1-map-popup-action">View records</button>`);
            marker.on('mouseover', () => setActiveRegion(region));
            marker.on('mouseout', () => setActiveRegion(''));
            marker.on('click', () => {
                setActiveRegion(region);
                window.openMainv1RegionRecords?.(region);
            });
            marker.on('popupopen', () => marker.getPopup().getElement()?.querySelector('.mainv1-map-popup-action')?.addEventListener('click', () => window.openMainv1RegionRecords?.(region)));
            markers.set(region, marker);
        });
        document.querySelectorAll('.mainv1-region-row').forEach((row) => {
            const region = rowRegionCodes[row.textContent.trim().replace(/\s+\d+$/, '')] || '';
            row.dataset.mapRegion = region;
            row.addEventListener('mouseenter', () => setActiveRegion(region));
            row.addEventListener('mouseleave', () => setActiveRegion(''));
            row.addEventListener('focus', () => setActiveRegion(region));
            row.addEventListener('blur', () => setActiveRegion(''));
            row.addEventListener('click', () => { focusRegion(region, true); window.openMainv1RegionRecords?.(region); });
            row.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); row.click(); }
            });
        });
        resetButton?.addEventListener('click', () => {
            geographicMap.fitBounds(philippinesBounds, { padding: [8, 8], animate: true });
            setActiveRegion('');
        });
        window.addEventListener('resize', () => geographicMap.invalidateSize());
        };
        if (typeof L === 'undefined') {
            window.addEventListener('load', initializeLeafletMap, { once: true });
        } else {
            initializeLeafletMap();
        }
    })();
</script>
<script>
    (() => {
        const map = document.querySelector('.mainv1-map-panel object');
        const label = document.querySelector('.mainv1-map-panel p');
        if (!map) return;

        const colors = {
            'Region I': '#ffb74d', CAR: '#9575cd', 'Region II': '#4db6ac',
            'Region III': '#81c784', 'Region IV-A': '#f06292', 'Region IV-B': '#64b5f6',
            NCR: '#ff8a65', 'Region V': '#ba68c8', 'Region VI': '#aed581',
            'Region VII': '#4fc3f7', 'Region VIII': '#ffcc80', 'Region IX': '#ce93d8',
            'Region X': '#80cbc4', 'Region XI': '#ffab91', 'Region XII': '#9fa8da',
            CARAGA: '#a5d6a7', BARMM: '#ffecb3'
        };
        const provinces = {
            'Ilocos Norte': 'Region I', 'Ilocos Sur': 'Region I', 'La Union': 'Region I', Pangasinan: 'Region I',
            Abra: 'CAR', Apayao: 'CAR', Benguet: 'CAR', Ifugao: 'CAR', Kalinga: 'CAR', 'Mountain Province': 'CAR',
            Batanes: 'Region II', Cagayan: 'Region II', Isabela: 'Region II', 'Nueva Vizcaya': 'Region II', Quirino: 'Region II',
            Aurora: 'Region III', Bataan: 'Region III', Bulacan: 'Region III', 'Nueva Ecija': 'Region III', Pampanga: 'Region III', Tarlac: 'Region III', Zambales: 'Region III',
            Batangas: 'Region IV-A', Cavite: 'Region IV-A', Laguna: 'Region IV-A', Quezon: 'Region IV-A', Rizal: 'Region IV-A',
            Marinduque: 'Region IV-B', 'Mindoro Occidental': 'Region IV-B', 'Mindoro Oriental': 'Region IV-B', Palawan: 'Region IV-B', Romblon: 'Region IV-B',
            'Metropolitan Manila': 'NCR', Albay: 'Region V', 'Camarines Norte': 'Region V', 'Camarines Sur': 'Region V', Catanduanes: 'Region V', Masbate: 'Region V', Sorsogon: 'Region V',
            Aklan: 'Region VI', Antique: 'Region VI', Capiz: 'Region VI', Guimaras: 'Region VI', Iloilo: 'Region VI', 'Negros Occidental': 'Region VI',
            Bohol: 'Region VII', Cebu: 'Region VII', 'Negros Oriental': 'Region VII', Siquijor: 'Region VII',
            Biliran: 'Region VIII', 'Eastern Samar': 'Region VIII', Leyte: 'Region VIII', 'Northern Samar': 'Region VIII', Samar: 'Region VIII', 'Southern Leyte': 'Region VIII',
            'Zamboanga del Norte': 'Region IX', 'Zamboanga del Sur': 'Region IX', 'Zamboanga Sibugay': 'Region IX',
            Bukidnon: 'Region X', Camiguin: 'Region X', 'Lanao del Norte': 'Region X', 'Misamis Occidental': 'Region X', 'Misamis Oriental': 'Region X',
            'Compostela Valley': 'Region XI', 'Davao del Norte': 'Region XI', 'Davao del Sur': 'Region XI', 'Davao Oriental': 'Region XI',
            Cotabato: 'Region XII', Sarangani: 'Region XII', 'South Cotabato': 'Region XII', 'Sultan Kudarat': 'Region XII',
            'Agusan del Norte': 'CARAGA', 'Agusan del Sur': 'CARAGA', 'Dinagat Islands': 'CARAGA', 'Surigao del Norte': 'CARAGA', 'Surigao del Sur': 'CARAGA',
            Basilan: 'BARMM', 'Lanao del Sur': 'BARMM', Maguindanao: 'BARMM', Sulu: 'BARMM', 'Tawi-Tawi': 'BARMM'
        };
        const locationRows = @json($mainv1RegionLocations);
        const normalizeRegion = (value) => String(value || '').toLowerCase().replace(/^fo\s*/, '').replace(/^region\s*/, '').replace(/\s+/g, '');
        const normalizeProvince = (value) => String(value || '').toLowerCase().replace(/^metropolitan manila$/, 'metro manila').replace(/^province of\s+/, '').replace(/\s+/g, ' ').trim();
        const showLocations = (region, path, locationLayer) => {
            if (!region || !path || !locationLayer) return;
            const province = String(path.getAttribute('title') || '').trim();
            const cities = [...new Set(locationRows
                .filter((row) => normalizeRegion(row.region) === normalizeRegion(region) && normalizeProvince(row.province) === normalizeProvince(province))
                .map((row) => String(row.city || '').trim()).filter(Boolean))].sort();
            const bounds = path.getBBox();
            const centerX = bounds.x + bounds.width / 2;
            const centerY = bounds.y + bounds.height / 2;
            const cityText = cities.length ? cities.join(', ') : 'No city records';
            const cityLines = cityText.match(/.{1,52}(?:\s|$)/g)?.map((line) => line.trim()).filter(Boolean) || [cityText];
            const lineTexts = [province || region, ...cityLines];
            locationLayer.replaceChildren();
            locationLayer.setAttribute('transform', 'translate(0 0)');
            const background = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
            const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            text.setAttribute('x', '8');
            text.setAttribute('y', '16');
            text.setAttribute('fill', '#17324d');
            text.setAttribute('font-family', 'Arial, sans-serif');
            text.setAttribute('font-size', '12');
            text.setAttribute('font-weight', '700');
            lineTexts.forEach((line, index) => {
                const tspan = document.createElementNS('http://www.w3.org/2000/svg', 'tspan');
                tspan.setAttribute('x', '8');
                tspan.setAttribute('dy', index ? '15' : '0');
                tspan.setAttribute('font-size', index ? '10' : '12');
                tspan.setAttribute('font-weight', index ? '500' : '700');
                tspan.textContent = line;
                text.appendChild(tspan);
            });
            locationLayer.append(background, text);
            const textBox = text.getBBox();
            background.setAttribute('x', String(textBox.x - 6));
            background.setAttribute('y', String(textBox.y - 6));
            background.setAttribute('width', String(textBox.width + 12));
            background.setAttribute('height', String(textBox.height + 12));
            background.setAttribute('rx', '6');
            background.setAttribute('fill', '#ffffff');
            background.setAttribute('fill-opacity', '0.94');
            background.setAttribute('stroke', colors[region] || '#2789d9');
            background.setAttribute('stroke-width', '1.5');
            const viewBox = (map.contentDocument.documentElement.getAttribute('viewBox') || '0 0 1 1').split(/\s+/).map(Number);
            const padding = 8;
            const labelBox = { x: textBox.x - 6, y: textBox.y - 6, width: textBox.width + 12, height: textBox.height + 12 };
            const minX = viewBox[0] + padding - labelBox.x;
            const maxX = viewBox[0] + viewBox[2] - padding - labelBox.x - labelBox.width;
            const minY = viewBox[1] + padding - labelBox.y;
            const maxY = viewBox[1] + viewBox[3] - padding - labelBox.y - labelBox.height;
            const labelX = Math.max(minX, Math.min(maxX, centerX + 12));
            const labelY = Math.max(minY, Math.min(maxY, centerY - 18));
            locationLayer.setAttribute('transform', `translate(${labelX} ${labelY})`);
        };

        const setup = () => {
            const svg = map.contentDocument;
            if (!svg) return;
            const paths = [...svg.querySelectorAll('path')];
            if (!paths.length) return;
            const locationLayer = document.createElementNS('http://www.w3.org/2000/svg', 'g');
            locationLayer.setAttribute('class', 'mainv1-svg-location-label');
            locationLayer.style.pointerEvents = 'none';
            svg.documentElement.appendChild(locationLayer);
            const infos = paths.map((path) => {
                const region = provinces[path.getAttribute('title') || ''];
                const color = colors[region] || '#cbd5e1';
                path.style.fill = color;
                path.style.transition = 'fill .18s ease, opacity .18s ease, stroke-width .18s ease';
                path.style.cursor = 'pointer';
                return { path, region, color };
            });
            const pointLayer = document.createElementNS('http://www.w3.org/2000/svg', 'g');
            pointLayer.setAttribute('class', 'mainv1-svg-location-points');
            pointLayer.style.pointerEvents = 'none';
            infos.forEach(({ path, region, color }) => {
                const province = String(path.getAttribute('title') || '').trim();
                if (!province || !region) return;
                const bounds = path.getBBox();
                const point = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                point.setAttribute('cx', String(bounds.x + bounds.width / 2));
                point.setAttribute('cy', String(bounds.y + bounds.height / 2));
                point.setAttribute('r', '3.5');
                point.setAttribute('fill', '#ffffff');
                point.setAttribute('stroke', color);
                point.setAttribute('stroke-width', '2');
                const name = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                name.setAttribute('x', String(bounds.x + bounds.width / 2 + 6));
                name.setAttribute('y', String(bounds.y + bounds.height / 2 + 3));
                name.setAttribute('fill', '#17324d');
                name.setAttribute('font-family', 'Arial, sans-serif');
                name.setAttribute('font-size', '8');
                name.setAttribute('font-weight', '700');
                name.setAttribute('paint-order', 'stroke');
                name.setAttribute('stroke', '#ffffff');
                name.setAttribute('stroke-width', '2.5');
                name.textContent = province;
                pointLayer.append(point, name);
            });
            svg.documentElement.appendChild(pointLayer);
            svg.documentElement.appendChild(locationLayer);
            const reset = () => infos.forEach(({ path, color }) => {
                path.style.fill = color;
                path.style.opacity = '1';
                path.style.strokeWidth = '0.5';
            });
            const highlightRegion = (region) => {
                infos.forEach((info) => {
                    info.path.style.opacity = info.region && info.region !== region ? '0.22' : '1';
                    info.path.style.strokeWidth = info.region === region ? '2.4' : '0.5';
                });
                document.querySelectorAll('.mainv1-region-row').forEach((row) => {
                    row.classList.toggle('is-active', row.dataset.mapRegion === region);
                });
                if (label) label.textContent = region || 'Hover a region on the map';
            };
            const rowRegionCodes = {
                'FO I': 'Region I', 'FO CAR': 'CAR', 'FO II': 'Region II', 'FO III': 'Region III',
                'FO IV-A': 'Region IV-A', 'FO IV-B': 'Region IV-B', 'FO NCR': 'NCR', 'FO V': 'Region V',
                'FO VI': 'Region VI', 'FO VII': 'Region VII', 'FO VIII': 'Region VIII', 'FO IX': 'Region IX',
                'FO X': 'Region X', 'FO XI': 'Region XI', 'FO XII': 'Region XII', 'FO CARAGA': 'CARAGA'
            };
            infos.forEach(({ path, region }) => {
                path.addEventListener('mouseenter', () => {
                    highlightRegion(region);
                    showLocations(region, path, locationLayer);
                    if (label && !region) label.textContent = `Province: ${path.getAttribute('title') || ''}`;
                });
                path.addEventListener('mouseleave', () => {
                    reset();
                    locationLayer.replaceChildren();
                    document.querySelectorAll('.mainv1-region-row').forEach((row) => row.classList.remove('is-active'));
                    if (label) label.textContent = 'Hover a region on the map';
                });
                path.addEventListener('click', () => {
                    const province = String(path.getAttribute('title') || '').trim();
                    if (region && province && window.openMainv1LocationRecords) {
                        window.openMainv1LocationRecords(region, province);
                    } else if (region && window.openMainv1RegionRecords) {
                        window.openMainv1RegionRecords(region);
                    }
                });
            });
            document.querySelectorAll('.mainv1-region-row').forEach((row) => {
                const code = rowRegionCodes[row.textContent.trim().replace(/\s+\d+$/, '')];
                row.dataset.mapRegion = code || '';
                row.addEventListener('mouseenter', () => { highlightRegion(code); showLocations(code); });
                row.addEventListener('mouseleave', () => {
                    reset();
                    row.classList.remove('is-active');
                    if (label) label.textContent = 'Hover a region on the map';
                });
                row.addEventListener('focus', () => { highlightRegion(code); showLocations(code); });
                row.addEventListener('blur', () => {
                    reset();
                    row.classList.remove('is-active');
                    if (label) label.textContent = 'Hover a region on the map';
                });
                row.addEventListener('click', () => {
                    if (code && window.openMainv1RegionRecords) window.openMainv1RegionRecords(code);
                });
                row.addEventListener('keydown', (event) => {
                    if ((event.key === 'Enter' || event.key === ' ') && code && window.openMainv1RegionRecords) {
                        event.preventDefault();
                        window.openMainv1RegionRecords(code);
                    }
                });
            });
        };
        map.addEventListener('load', setup, { once: true });
        if (map.contentDocument) setup();
    })();

        (() => {
            const mapWrap = document.getElementById('mainv1SvgMapWrap');
            const mapStage = document.getElementById('mainv1SvgMapStage');
            const zoomIn = document.getElementById('mainv1SvgMapZoomIn');
            const zoomOut = document.getElementById('mainv1SvgMapZoomOut');
            const reset = document.getElementById('mainv1SvgMapReset');
            if (mapWrap && mapStage) {
                const mapObject = mapStage.querySelector('object');
                let scale = 1;
                let panX = 0;
                let panY = 0;
                let pointerStart = null;
                let dragged = false;
                const clampPan = () => {
                    const maxX = Math.max(80, (mapWrap.clientWidth * (scale - 1)) / 2 + 80);
                    const maxY = Math.max(80, (mapWrap.clientHeight * (scale - 1)) / 2 + 80);
                    panX = Math.max(-maxX, Math.min(maxX, panX));
                    panY = Math.max(-maxY, Math.min(maxY, panY));
                };
                const applyScale = () => {
                    clampPan();
                    mapStage.style.transform = `translate(${panX}px, ${panY}px) scale(${scale})`;
                    zoomIn?.toggleAttribute('disabled', scale >= 4);
                    zoomOut?.toggleAttribute('disabled', scale <= 0.8);
                };
                const changeScale = (amount) => {
                    scale = Math.min(4, Math.max(0.8, Number((scale + amount).toFixed(1))));
                    applyScale();
                };
                zoomIn?.addEventListener('click', () => changeScale(0.2));
                zoomOut?.addEventListener('click', () => changeScale(-0.2));
                reset?.addEventListener('click', () => { scale = 1; panX = 0; panY = 0; applyScale(); });
                mapWrap.addEventListener('wheel', (event) => {
                    event.preventDefault();
                    changeScale(event.deltaY < 0 ? 0.1 : -0.1);
                }, { passive: false });
                const startDrag = (event) => {
                    if (event.target.closest?.('.mainv1-map-controls')) return;
                    event.preventDefault();
                    pointerStart = { x: event.clientX, y: event.clientY, panX, panY };
                    dragged = false;
                    try { mapWrap.setPointerCapture?.(event.pointerId); } catch (error) {}
                    mapWrap.classList.add('is-panning');
                };
                const moveDrag = (event) => {
                    if (!pointerStart) return;
                    event.preventDefault();
                    const deltaX = event.clientX - pointerStart.x;
                    const deltaY = event.clientY - pointerStart.y;
                    if (Math.abs(deltaX) > 3 || Math.abs(deltaY) > 3) dragged = true;
                    panX = pointerStart.panX + deltaX;
                    panY = pointerStart.panY + deltaY;
                    applyScale();
                };
                const endDrag = (event) => {
                    event.preventDefault();
                    pointerStart = null;
                    try { mapWrap.releasePointerCapture?.(event?.pointerId); } catch (error) {}
                    mapWrap.classList.remove('is-panning');
                };
                mapWrap.addEventListener('pointerdown', startDrag);
                mapWrap.addEventListener('pointermove', moveDrag);
                mapWrap.addEventListener('pointerup', endDrag);
                mapWrap.addEventListener('pointercancel', endDrag);
                const attachSvgDrag = () => {
                    const svgDocument = mapObject?.contentDocument;
                    if (!svgDocument || svgDocument.documentElement.dataset.panBound === 'true') return;
                    svgDocument.documentElement.dataset.panBound = 'true';
                    svgDocument.documentElement.style.userSelect = 'none';
                    svgDocument.documentElement.style.webkitUserSelect = 'none';
                    svgDocument.documentElement.style.cursor = 'grab';
                    svgDocument.addEventListener('pointerdown', startDrag, true);
                    svgDocument.addEventListener('pointermove', moveDrag, true);
                    svgDocument.addEventListener('pointerup', endDrag, true);
                    svgDocument.addEventListener('pointercancel', endDrag, true);
                    svgDocument.addEventListener('click', (event) => {
                        if (!dragged) return;
                        event.preventDefault();
                        event.stopPropagation();
                        dragged = false;
                    }, true);
                };
                mapObject?.setAttribute('draggable', 'false');
                mapObject?.addEventListener('load', attachSvgDrag, { once: true });
                attachSvgDrag();
                mapWrap.addEventListener('click', (event) => {
                    if (!dragged) return;
                    event.preventDefault();
                    event.stopPropagation();
                    dragged = false;
                }, true);
                applyScale();
            }
        })();

        (() => {
            const popover = document.querySelector('.mainv1-gallery-popover');
            const marquee = document.querySelector('.mainv1-gallery-marquee');
            const title = document.getElementById('mainv1GalleryPopoverTitle');
            const body = document.getElementById('mainv1GalleryPopoverBody');
            let activeCard = null;
            if (!popover || !marquee || !title || !body) return;

            const close = () => {
                popover.classList.remove('is-open');
                marquee.classList.remove('is-gallery-open');
                activeCard?.classList.remove('is-gallery-active');
                if (activeCard) setCardExpanded(activeCard, false);
                body.replaceChildren();
                activeCard = null;
            };

            const addText = (parent, className, value) => {
                if (!value) return;
                const element = document.createElement('div');
                element.className = className;
                element.textContent = value;
                parent.append(element);
            };

            const addLink = (parent, url, label) => {
                if (!url) return;
                const link = document.createElement('a');
                const value = String(url);
                link.href = /^[a-z][a-z\d+.-]*:/i.test(value) || value.startsWith('/')
                    ? value
                    : `https://${value}`;
                link.className = 'mainv1-gallery-link';
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.textContent = label || value;
                parent.append(link);
            };

            const positionPopover = (card) => {
                const rect = card.getBoundingClientRect();
                const gap = 12;
                const width = popover.offsetWidth;
                const left = rect.right + gap + width <= window.innerWidth
                    ? rect.right + gap
                    : rect.left - gap - width;
                const top = Math.max(gap, Math.min(rect.top, window.innerHeight - popover.offsetHeight - gap));
                popover.style.left = `${Math.max(gap, left)}px`;
                popover.style.top = `${top}px`;
            };

            const setCardExpanded = (card, expanded) => {
                const content = card.querySelector('.mainv1-category-content');
                if (!expanded) {
                    card.style.removeProperty('flex');
                    card.style.removeProperty('flex-basis');
                    card.style.removeProperty('width');
                    card.style.removeProperty('min-width');
                    card.style.removeProperty('height');
                    card.style.removeProperty('min-height');
                    card.style.removeProperty('flex-direction');
                    card.style.removeProperty('align-items');
                    content?.style.removeProperty('width');
                    content?.style.removeProperty('min-width');
                    content?.style.removeProperty('opacity');
                    return;
                }
                const compact = window.matchMedia('(max-width: 576px)').matches;
                card.style.flex = compact ? '0 0 310px' : '0 0 460px';
                card.style.width = compact ? '310px' : '460px';
                card.style.minWidth = compact ? '310px' : '460px';
                card.style.height = compact ? '180px' : '155px';
                card.style.minHeight = compact ? '180px' : '155px';
                card.style.flexDirection = 'row';
                card.style.alignItems = 'flex-start';
                if (content) {
                    content.style.width = compact ? '170px' : '280px';
                    content.style.minWidth = compact ? '170px' : '280px';
                    content.style.opacity = '1';
                }
            };

            const renderChildren = (children) => {
                children.forEach((child) => {
                    const item = document.createElement('div');
                    item.className = 'mainv1-gallery-child';
                    const childTitle = document.createElement('div');
                    childTitle.className = 'mainv1-gallery-child-title';
                    if (child.url) {
                        addLink(childTitle, child.url, child.title);
                    } else {
                        childTitle.textContent = child.title || '';
                    }
                    item.append(childTitle);
                    addText(item, 'mainv1-gallery-child-description', child.description);
                    if (child.children?.length) {
                        const nested = document.createElement('div');
                        nested.className = 'mainv1-gallery-subchildren';
                        child.children.forEach((subchild) => {
                            const subitem = document.createElement('div');
                            subitem.className = 'mainv1-gallery-subchild';
                            const subchildTitle = document.createElement('div');
                            subchildTitle.className = 'mainv1-gallery-child-title';
                            if (subchild.url) {
                                addLink(subchildTitle, subchild.url, subchild.title);
                            } else {
                                subchildTitle.textContent = subchild.title || '';
                            }
                            subitem.append(subchildTitle);
                            addText(subitem, 'mainv1-gallery-child-description', subchild.description);
                            nested.append(subitem);
                        });
                        item.append(nested);
                    }
                    body.append(item);
                });
            };

            marquee.addEventListener('click', (event) => {
                const card = event.target.closest('.mainv1-category-card');
                if (!card || !card.dataset.gallery) return;
                if (event.target.closest('a.mainv1-gallery-link')) return;
                let gallery;
                try {
                    gallery = JSON.parse(card.dataset.gallery);
                } catch (error) {
                    return;
                }
                if (!Array.isArray(gallery.children) || !gallery.children.length) return;
                event.preventDefault();
                if (activeCard && activeCard !== card) {
                    activeCard.classList.remove('is-gallery-active');
                    setCardExpanded(activeCard, false);
                }
                title.textContent = gallery.title;
                body.replaceChildren();
                renderChildren(gallery.children);
                activeCard = card;
                card.classList.add('is-gallery-active');
                setCardExpanded(card, true);
                marquee.classList.add('is-gallery-open');
                popover.classList.add('is-open');
                positionPopover(card);
            });

            document.querySelector('.mainv1-gallery-popover-close')?.addEventListener('click', close);
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') close();
            });
            document.addEventListener('click', (event) => {
                if (activeCard && !popover.contains(event.target) && !activeCard.contains(event.target)) close();
            });
            window.addEventListener('resize', () => {
                if (activeCard) {
                    setCardExpanded(activeCard, true);
                    positionPopover(activeCard);
                }
            });
            window.addEventListener('scroll', close, { passive: true });
        })();
    </script>
@endsection