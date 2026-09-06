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
        margin-left: 320px !important;
        max-width: calc(100% - 320px) !important;
        padding-right: 1.5rem !important;
        padding-left: 1.5rem !important;
        width: calc(100% - 320px) !important;
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

    .mainv1-row {
        display: grid !important;
        grid-template-columns: minmax(450px, 0.85fr) minmax(600px, 1.15fr);
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
        justify-content: center;
        flex-direction: column;
        gap: 0.75rem;
        min-height: 150px;
        padding: 1rem;
        box-sizing: border-box;
    }

    .mainv1-row > :last-child {
        margin-left: 0;
    }

    .mainv1-logo {
        display: block;
        width: min(420px, 100%);
        height: auto;
        flex: 0 1 auto;
    }

    .mainv1-brand-copy {
        max-width: 300px;
        text-align: center;
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

    .mainv1-map-panel object {
        display: block;
        width: min(100%, 700px);
        height: clamp(460px, 52vw, 700px);
        margin: 0 auto;
    }

    .mainv1-map-panel p {
        margin: 0.35rem 0 0;
        color: #10aeb5;
        font-size: 0.76rem;
        font-weight: 700;
    }

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
        animation: mainv1-gallery-scroll 70s linear infinite;
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
        overflow: hidden;
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
        align-items: center;
        justify-content: center;
    }

    .mainv1-region-dialog {
        position: relative;
        width: min(1440px, 100%);
        height: min(900px, calc(100vh - 1.5rem));
        max-height: calc(100vh - 1.5rem);
        overflow: hidden;
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

    .mainv1-region-modal-body {
        display: grid;
        grid-template-columns: minmax(250px, 0.9fr) minmax(320px, 1.05fr) minmax(330px, 1.35fr);
        gap: 1rem;
        padding: 1rem;
        height: calc(100% - 62px);
        box-sizing: border-box;
        overflow: hidden;
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
        height: 100%;
        min-height: 560px;
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
        flex: 1 1 auto;
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
            overflow-y: auto;
        }

        .mainv1-region-panel.title-panel {
            grid-column: 1 / -1;
            height: 420px;
            min-height: 0;
        }

        .mainv1-region-map { height: 300px; }
    }

    @media (max-width: 900px) {
        .mainv1-region-modal-body { grid-template-columns: 1fr; }
        .mainv1-region-dialog {
            height: min(900px, calc(100vh - 1rem));
            max-height: calc(100vh - 1rem);
        }
        .mainv1-region-map { height: 250px; }
        .mainv1-region-panel.title-panel {
            grid-column: auto;
            height: min(420px, 55vh);
            min-height: 220px;
        }
        .mainv1-region-title-list { min-height: 180px; max-height: 360px; }
    }

    @media (max-width: 600px) {
        .mainv1-region-modal-header { padding: 0.9rem 3rem 0.75rem 0.9rem; }
        .mainv1-region-modal-header h2 { font-size: 1rem; }
        .mainv1-region-modal-body { gap: 0.65rem; padding: 0.65rem; }
        .mainv1-region-panel { padding: 0.65rem; }
        .mainv1-region-map { height: 190px; }
        .mainv1-region-chart canvas { height: 190px; }
        .mainv1-region-title-list { max-height: 300px; }

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
        height: 480px;
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
            width: 100%;
            max-width: none;
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
            align-self: flex-end;
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
        }

        .mainv1-logo {
            width: min(320px, 90%);
            flex-basis: auto;
        }

        .mainv1-brand-copy {
            max-width: 320px;
        }

        .mainv1-brand-rule {
            margin-right: auto;
            margin-left: auto;
        }

        .mainv1-filter {
            padding: 1rem;
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
            animation-duration: 75s;
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
<div class="content-body">
    @php
        $mainv1Items = collect($regionItems ?? []);
        $mainv1FilterItems = collect($filterItems ?? $regionItems ?? []);
        $mainv1DashboardItems = $mainv1Items;
        $mainv1FilterData = $mainv1FilterItems->map(fn ($item) => [
            'region' => $item->region?->name,
            'province' => $item->province,
            'municipality' => $item->municipality,
            'year' => $item->year_of_moa,
            'title' => $item->title,
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
        <div class="pe-4">
            <form class="mainv1-filter" aria-label="Filter region items" method="GET" action="{{ auth()->check() ? route('main') : route('landing') }}">
                <div class="mainv1-filter-heading">
                    <h2>Dashboard Filters</h2>
                </div>
                <div class="mainv1-filter-grid" id="mainv1Filters">
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
    <div class="row mainv1-overview-row ps-3">
        <div class="col-md-3" style="padding-top: 22px;">
            <div class="mainv1-total-list">
                <div class="mainv1-total-card mainv1-metric-trigger" data-mainv1-metric="all" role="button" tabindex="0" aria-label="View all adopted and replicated social technologies">
                    <span>TOTAL ADOPTED AND REPLICATED</span>
                    <strong>{{ $mainv1DashboardItems->count() }}</strong>
                </div>
                <div class="mainv1-total-card mainv1-metric-trigger" data-mainv1-metric="expr" role="button" tabindex="0" aria-label="View social technologies with expression of interest">
                    <span>TOTAL EXPRESSION OF INTEREST</span>
                    <strong>{{ $mainv1DashboardItems->filter(fn ($item) => $mainv1Truthy($item->with_expr))->count() }}</strong>
                </div>
                <div class="mainv1-total-card mainv1-metric-trigger" data-mainv1-metric="res" role="button" tabindex="0" aria-label="View social technologies with SB resolution">
                    <span>TOTAL SB RESOLUTION</span>
                    <strong>{{ $mainv1DashboardItems->filter(fn ($item) => $mainv1Truthy($item->with_res))->count() }}</strong>
                </div>
                <div class="mainv1-total-card mainv1-metric-trigger" data-mainv1-metric="moa" role="button" tabindex="0" aria-label="View social technologies with memorandum of agreement">
                    <span>TOTAL MEMORANDUM OF AGREEMENT</span>
                    <strong>{{ $mainv1DashboardItems->filter(fn ($item) => $mainv1Truthy($item->with_moa))->count() }}</strong>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <section class="mainv1-dashboard" aria-label="ST inventory overview">
                <div class="mainv1-map-panel">
                    <h2>PHILIPPINES MAP &amp; REGIONS</h2>
                    <object data="{{ asset('images/philippines.svg') }}" type="image/svg+xml" aria-label="Philippines map"></object>
                    <p>Hover a region on the map</p>
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
        <div class="col-md-3" style="padding-top: 22px;">
            <div class="mainv1-total-list">
                <div class="mainv1-total-card mainv1-metric-trigger" data-mainv1-metric="active" role="button" tabindex="0" aria-label="View active region social technologies">
                    <span>TOTAL ACTIVE REGION'S SOCIAL TECHNOLOGIES</span>
                    <strong>{{ $mainv1DashboardItems->filter($mainv1Active)->count() }}</strong>
                </div>
                <div class="mainv1-total-card mainv1-metric-trigger" data-mainv1-metric="inactive" role="button" tabindex="0" aria-label="View inactive region social technologies">
                    <span>TOTAL INACTIVE REGION'S SOCIAL TECHNOLOGIES</span>
                    <strong>{{ $mainv1DashboardItems->filter($mainv1Inactive)->count() }}</strong>
                </div>
                <div class="mainv1-total-card mainv1-metric-trigger" data-mainv1-metric="replicated" role="button" tabindex="0" aria-label="View replicated social technologies">
                    <span>TOTAL REPLICATED</span>
                    <strong>{{ $mainv1DashboardItems->filter(fn ($item) => $mainv1Truthy($item->with_replicated))->count() }}</strong>
                </div>
                <div class="mainv1-total-card mainv1-metric-trigger" data-mainv1-metric="adopted" role="button" tabindex="0" aria-label="View adopted social technologies">
                    <span>TOTAL ADOPTED</span>
                    <strong>{{ $mainv1DashboardItems->filter(fn ($item) => $mainv1Truthy($item->with_adopted))->count() }}</strong>
                </div>
            </div>
        </div>
    </div>
    <div class="mainv1-metric-modal" id="mainv1MetricModal" role="dialog" aria-modal="true" aria-labelledby="mainv1MetricModalTitle" aria-hidden="true">
        <div class="mainv1-metric-dialog">
            <div class="mainv1-metric-modal-header">
                <div><span>Social Technologies</span><h2 id="mainv1MetricModalTitle">Metric records</h2><p id="mainv1MetricModalSummary"></p></div>
                <button class="mainv1-metric-modal-close" type="button" aria-label="Close metric records">&times;</button>
            </div>
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
<section class="mainv1-analytics" aria-labelledby="mainv1AnalyticsTitle">
    <div class="mainv1-analytics-heading">
        <div>
            <span class="mainv1-analytics-eyebrow">Trend overview</span>
            <h2 id="mainv1AnalyticsTitle">Social Technology Analytics</h2>
            <p>See status movement, coverage, regional concentration, and implementation records at a glance.</p>
        </div>
        <div class="mainv1-analytics-badge"><span class="mainv1-live-dot"></span><span id="mainv1AnalyticsCount">{{ $mainv1AnalyticsRows->count() }} filtered records</span></div>
    </div>

    <div class="mainv1-analytics-top">
        <article class="mainv1-analytics-panel mainv1-trend-panel">
            <div class="mainv1-panel-heading"><div><span>Trend overview</span><h3>Status movement over time</h3></div></div>
            <div class="mainv1-chart-wrap"><canvas id="mainv1StatusChart"></canvas></div>
        </article>
    </div>

    <div class="mainv1-analytics-grid mainv1-analytics-grid-secondary">
        <article class="mainv1-analytics-panel mainv1-year-panel">
            <div class="mainv1-panel-heading"><div><span>Distribution</span><h3>Year of MOAs</h3></div></div>
            <div class="mainv1-chart-wrap"><canvas id="mainv1YearChart"></canvas></div>
            <div class="mainv1-insight-strip"><div><span>Peak year</span><strong id="mainv1PeakYear">-</strong><small id="mainv1PeakMeta">No records yet</small></div><div><span>Average volume</span><strong id="mainv1AverageYear">-</strong><small>Records per year</small></div><div><span>Latest year</span><strong id="mainv1LatestYear">-</strong><small id="mainv1LatestMeta">No records yet</small></div></div>
        </article>
        <article class="mainv1-analytics-panel mainv1-share-panel"><div class="mainv1-panel-heading"><div><span>Share analysis</span><h3>Ongoing vs inactive</h3></div></div><div class="mainv1-donut-wrap"><canvas id="mainv1StatusDonut"></canvas></div><div class="mainv1-share-legend"><span><i class="legend-teal"></i>Ongoing <b id="mainv1StatusOngoingPercent">0%</b></span><span><i class="legend-rose"></i>Inactive <b id="mainv1StatusInactivePercent">0%</b></span></div><div class="mainv1-share-summary"><div class="mainv1-share-metrics"><div class="mainv1-share-stat share-stat-teal"><span>Ongoing</span><strong id="mainv1StatusOngoingValue">0</strong><small id="mainv1StatusOngoingSummary">0% of status records</small></div><div class="mainv1-share-stat share-stat-rose"><span>Inactive</span><strong id="mainv1StatusInactiveValue">0</strong><small id="mainv1StatusInactiveSummary">0% of status records</small></div></div><div class="mainv1-share-insight"><span>Current lead</span><strong id="mainv1StatusLead">Awaiting summary</strong></div></div></article>
        <article class="mainv1-analytics-panel mainv1-share-panel"><div class="mainv1-panel-heading"><div><span>Share analysis</span><h3>Replicated vs adopted</h3></div></div><div class="mainv1-donut-wrap"><canvas id="mainv1AdoptionDonut"></canvas></div><div class="mainv1-share-legend"><span><i class="legend-blue"></i>Replicated <b id="mainv1ReplicatedPercent">0%</b></span><span><i class="legend-gold"></i>Adopted <b id="mainv1AdoptedPercent">0%</b></span></div><div class="mainv1-share-summary"><div class="mainv1-share-metrics"><div class="mainv1-share-stat share-stat-blue"><span>Replicated</span><strong id="mainv1ReplicatedValue">0</strong><small id="mainv1ReplicatedSummary">0% of replicated records</small></div><div class="mainv1-share-stat share-stat-gold"><span>Adopted</span><strong id="mainv1AdoptedValue">0</strong><small id="mainv1AdoptedSummary">0% of adoption records</small></div></div><div class="mainv1-share-insight"><span>Current lead</span><strong id="mainv1AdoptionLead">Awaiting summary</strong></div></div></article>
    </div>

    <div class="mainv1-analytics-grid mainv1-analytics-grid-lower">
        <article class="mainv1-analytics-panel mainv1-heatmap-panel"><div class="mainv1-panel-heading"><div><span>Regional pattern</span><h3>Regional year activity</h3></div></div><div id="mainv1Heatmap" class="mainv1-heatmap"></div></article>
        <article class="mainv1-analytics-panel mainv1-coverage-panel"><div class="mainv1-panel-heading"><div><span>Overall totals</span><h3>Social technology coverage</h3></div></div><div id="mainv1Coverage" class="mainv1-coverage-list"></div></article>
        <article class="mainv1-analytics-panel mainv1-ranking-panel"><div class="mainv1-panel-heading"><div><span>Geographic reach</span><h3>Top regions</h3></div></div><div id="mainv1TopRegions" class="mainv1-ranking-list"></div></article>
        <article class="mainv1-analytics-panel mainv1-ranking-panel"><div class="mainv1-panel-heading"><div><span>Local concentration</span><h3>Top provinces</h3></div></div><div id="mainv1TopProvinces" class="mainv1-ranking-list"></div></article>
    </div>

    <article class="mainv1-analytics-panel mainv1-directory-panel">
        <div class="mainv1-directory-heading"><div class="mainv1-panel-heading"><div><span>Directory view</span><h3>Social Technologies</h3><p>Search and review social technology implementations.</p></div></div><div class="mainv1-directory-controls"><input id="mainv1DirectorySearch" type="search" placeholder="Search ST title" aria-label="Search ST title"><select id="mainv1DirectoryProvince" aria-label="Filter by province"><option value="">All provinces</option></select><select id="mainv1DirectoryMunicipality" aria-label="Filter by city or municipality"><option value="">All cities / municipalities</option></select><select id="mainv1DirectoryYear" aria-label="Filter by year of MOA"><option value="">All years</option></select><select id="mainv1DirectoryStatus" aria-label="Filter by status"><option value="">All statuses</option><option value="ongoing">Ongoing STs</option><option value="inactive">Inactive STs</option></select><select id="mainv1DirectoryType" aria-label="Filter by type"><option value="">All types</option><option value="replicated">With replicated</option><option value="adopted">With adopted</option></select><button type="button" id="mainv1DirectoryExport">Export CSV</button></div></div>
        <div class="mainv1-directory-table-wrap"><table class="mainv1-directory-table"><thead><tr><th>Title</th><th>Province</th><th>City / Municipality</th><th>Status</th><th>Coverage</th><th>Attachment</th></tr></thead><tbody id="mainv1DirectoryRows"></tbody></table></div>
        <div class="mainv1-directory-footer"><span id="mainv1DirectorySummary"></span><div><button type="button" id="mainv1DirectoryPrev" aria-label="Previous page">&#8592; Prev</button><strong id="mainv1DirectoryPage">Page 1</strong><button type="button" id="mainv1DirectoryNext" aria-label="Next page">Next &#8594;</button></div></div>
    </article>
</section>
<style>
.mainv1-analytics{display:grid;gap:1rem;margin:2rem 0 0;padding:clamp(1rem,2vw,2rem);border:1px solid #d8e5ef;border-radius:18px;background:linear-gradient(145deg,#f7fbfe,#fff 48%,#f4f9fc);box-shadow:0 16px 40px rgba(23,50,77,.07);color:#17324d}.mainv1-analytics-heading,.mainv1-directory-heading{display:flex;justify-content:space-between;align-items:flex-end;gap:1rem}.mainv1-analytics-eyebrow,.mainv1-panel-heading span{display:block;color:#6d8296;font-size:.68rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase}.mainv1-analytics-heading h2{margin:.2rem 0;color:#103d70;font-size:clamp(1.35rem,2vw,2rem)}.mainv1-analytics-heading p,.mainv1-directory-heading p{margin:.25rem 0 0;color:#6c7f91;font-size:.82rem}.mainv1-analytics-badge{display:flex;align-items:center;gap:.45rem;padding:.55rem .75rem;border:1px solid #cfe3ef;border-radius:999px;background:#fff;color:#45627d;font-size:.75rem;white-space:nowrap}.mainv1-live-dot{width:7px;height:7px;border-radius:50%;background:#20b6a8;box-shadow:0 0 0 4px #d9f5f0}.mainv1-analytics-top,.mainv1-analytics-grid{display:grid;gap:1rem}.mainv1-analytics-top{grid-template-columns:minmax(280px,.78fr) minmax(0,1.7fr)}.mainv1-analytics-metrics{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem}.mainv1-analytics-stat,.mainv1-analytics-panel{border:1px solid #dce8f0;border-radius:13px;background:rgba(255,255,255,.9);box-shadow:0 8px 22px rgba(23,50,77,.055)}.mainv1-analytics-stat{display:flex;min-height:150px;flex-direction:column;padding:1rem;border-top:3px solid #2e6fd8}.mainv1-analytics-stat span{color:#70859a;font-size:.63rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.mainv1-analytics-stat strong{margin:.35rem 0;color:#0e3e74;font-size:2rem}.mainv1-analytics-stat b{font-size:.78rem}.mainv1-analytics-stat small{margin-top:auto;color:#788b9c;font-size:.68rem;line-height:1.35}.mainv1-analytics-stat.stat-rose{border-color:#ff6682}.mainv1-analytics-stat.stat-rose strong{color:#ff4d6d}.mainv1-analytics-stat.stat-teal{border-color:#22b8aa}.mainv1-analytics-stat.stat-teal strong{color:#119e94}.mainv1-analytics-stat.stat-gold{border-color:#efb844}.mainv1-analytics-stat.stat-gold strong{color:#d49616}.mainv1-analytics-panel{min-width:0;padding:1rem}.mainv1-panel-heading{display:flex;justify-content:space-between;gap:.75rem}.mainv1-panel-heading h3{margin:.18rem 0 0;color:#173d68;font-size:.95rem}.mainv1-chart-wrap{position:relative;height:270px;margin-top:.65rem}.mainv1-analytics-grid-secondary{grid-template-columns:minmax(0,1.4fr) minmax(210px,.58fr) minmax(210px,.58fr)}.mainv1-analytics-grid-lower{grid-template-columns:minmax(0,1.15fr) minmax(0,1.15fr) minmax(210px,.8fr) minmax(210px,.8fr)}.mainv1-donut-wrap{position:relative;height:170px;margin:.3rem auto;width:min(100%,190px)}.mainv1-share-legend{display:grid;gap:.4rem;color:#526d85;font-size:.7rem}.mainv1-share-legend span{display:flex;align-items:center;justify-content:space-between}.mainv1-share-legend i{width:8px;height:8px;margin-right:.35rem;border-radius:50%}.mainv1-share-legend span b{margin-left:auto}.legend-teal{background:#42b9ba}.legend-rose{background:#ff6682}.legend-blue{background:#3398dc}.legend-gold{background:#ffc34f}.mainv1-insight-strip{display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;margin-top:.75rem}.mainv1-insight-strip div{padding:.55rem;border:1px solid #e4edf3;border-radius:8px}.mainv1-insight-strip span,.mainv1-insight-strip small{display:block;color:#70859a;font-size:.62rem}.mainv1-insight-strip strong{display:block;margin:.15rem 0;color:#123f70;font-size:1rem}.mainv1-heatmap{display:grid;gap:.3rem;margin-top:.8rem;max-height:240px;overflow:auto}.mainv1-heatmap-row{display:grid;grid-template-columns:72px repeat(8,1fr);gap:3px;align-items:center;font-size:.58rem}.mainv1-heatmap-label{overflow:hidden;color:#526d85;white-space:nowrap;text-overflow:ellipsis}.mainv1-heat-cell{height:17px;border-radius:3px;background:#edf4f8}.mainv1-heat-cell[data-level="1"]{background:#c5e6f6}.mainv1-heat-cell[data-level="2"]{background:#80c8ee}.mainv1-heat-cell[data-level="3"]{background:#369be0}.mainv1-coverage-list,.mainv1-ranking-list{display:grid;gap:.55rem;margin-top:.8rem}.mainv1-coverage-item{display:grid;grid-template-columns:135px 1fr 32px;gap:.45rem;align-items:center;color:#526d85;font-size:.68rem}.mainv1-coverage-bar{height:8px;overflow:hidden;border-radius:99px;background:#e7eff5}.mainv1-coverage-bar i{display:block;height:100%;border-radius:inherit;background:#38bdb1}.mainv1-ranking-item{display:grid;grid-template-columns:24px 1fr auto;gap:.5rem;align-items:center;padding:.55rem .65rem;border:1px solid #e4edf3;border-radius:9px;background:#fbfdff;font-size:.7rem}.mainv1-ranking-item em{display:grid;width:21px;height:21px;place-items:center;border-radius:50%;background:#e8f2ff;color:#347bc5;font-style:normal;font-weight:800}.mainv1-ranking-item b{color:#138f87}.mainv1-directory-panel{padding:1.1rem}.mainv1-directory-heading{align-items:center}.mainv1-directory-controls{display:grid;grid-template-columns:minmax(180px,1.5fr) repeat(2,minmax(120px,1fr));gap:.5rem;width:min(560px,100%)}.mainv1-directory-controls input,.mainv1-directory-controls select{width:100%;min-height:36px;padding:.45rem .6rem;border:1px solid #d5e3ed;border-radius:7px;background:#fff;color:#17324d;font-size:.75rem}.mainv1-directory-table-wrap{margin-top:1rem;overflow:auto;border:1px solid #e1ebf2;border-radius:10px}.mainv1-directory-table{width:100%;min-width:680px;border-collapse:collapse;font-size:.7rem}.mainv1-directory-table th{padding:.65rem;text-align:left;color:#58728a;background:#f5f9fc;font-size:.62rem;letter-spacing:.05em;text-transform:uppercase}.mainv1-directory-table td{padding:.65rem;border-top:1px solid #edf2f5;color:#34536f}.mainv1-directory-table td:first-child{max-width:260px;color:#173d68;font-weight:700}.mainv1-status-pill{display:inline-flex;padding:.2rem .45rem;border-radius:99px;font-size:.62rem;font-weight:800}.mainv1-status-pill.ongoing{background:#dff7ed;color:#168456}.mainv1-status-pill.inactive{background:#ffe5ec;color:#dc4968}.mainv1-directory-footer{display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-top:.75rem;color:#6b8296;font-size:.7rem}.mainv1-directory-footer div{display:flex;align-items:center;gap:.5rem}.mainv1-directory-footer button{padding:.4rem .65rem;border:1px solid #d5e3ed;border-radius:7px;background:#fff;color:#194878;font-size:.7rem;cursor:pointer}.mainv1-directory-footer button:disabled{opacity:.45;cursor:not-allowed}.mainv1-directory-footer strong{font-size:.7rem;color:#173d68;white-space:nowrap}@media(max-width:1100px){.mainv1-analytics-top,.mainv1-analytics-grid-secondary{grid-template-columns:1fr}.mainv1-analytics-grid-lower{grid-template-columns:repeat(2,minmax(0,1fr))}.mainv1-directory-heading{align-items:stretch;flex-direction:column}.mainv1-directory-controls{width:100%}}@media(max-width:576px){.mainv1-analytics{margin-top:1rem;padding:.75rem;border-radius:12px}.mainv1-analytics-heading{align-items:flex-start;flex-direction:column}.mainv1-analytics-metrics,.mainv1-analytics-grid-lower{grid-template-columns:1fr}.mainv1-chart-wrap{height:220px}.mainv1-insight-strip{grid-template-columns:1fr}.mainv1-directory-controls{grid-template-columns:1fr}.mainv1-directory-footer{align-items:flex-start;flex-direction:column}.mainv1-analytics-badge{white-space:normal}}
</style>
<style>
.mainv1-metric-trigger{cursor:pointer}.mainv1-metric-trigger:focus-visible{outline:3px solid rgba(46,111,216,.28);outline-offset:3px}.mainv1-metric-modal{position:fixed;inset:0;z-index:3000;display:none;align-items:center;justify-content:center;padding:1rem;background:rgba(11,35,59,.45)}.mainv1-metric-modal.is-open{display:flex}.mainv1-metric-dialog{width:min(920px,100%);max-height:min(760px,calc(100vh - 2rem));overflow:hidden;border:1px solid #d7e5ef;border-radius:16px;background:#fff;box-shadow:0 24px 70px rgba(8,43,81,.24)}.mainv1-metric-modal-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;padding:1.15rem 1.25rem;border-bottom:1px solid #e4edf3;background:linear-gradient(135deg,#f7fbff,#fff)}.mainv1-metric-modal-header span{color:#6d8296;font-size:.68rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase}.mainv1-metric-modal-header h2{margin:.25rem 0;color:#123f70;font-size:1.25rem}.mainv1-metric-modal-header p{margin:0;color:#6b8197;font-size:.78rem}.mainv1-metric-modal-close{display:grid;width:34px;height:34px;place-items:center;border:1px solid #d5e3ed;border-radius:8px;background:#fff;color:#496780;font-size:1.35rem;line-height:1;cursor:pointer}.mainv1-metric-list-wrap{max-height:calc(min(760px,100vh - 2rem) - 100px);overflow:auto;padding:.75rem 1.25rem 1.25rem}.mainv1-metric-list{display:grid;gap:.55rem}.mainv1-metric-row{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(100px,.65fr) minmax(120px,.8fr) auto;gap:.75rem;align-items:center;padding:.75rem;border:1px solid #e1ebf2;border-radius:10px;background:#fbfdff}.mainv1-metric-row strong{overflow:hidden;color:#173d68;font-size:.78rem;text-overflow:ellipsis;white-space:nowrap}.mainv1-metric-row span{overflow:hidden;color:#607990;font-size:.72rem;text-overflow:ellipsis;white-space:nowrap}.mainv1-metric-status{padding:.25rem .5rem;border-radius:99px;background:#dff7ed;color:#168456;font-size:.64rem;font-weight:800;text-align:center}.mainv1-metric-status.inactive{background:#ffe5ec;color:#dc4968}.mainv1-metric-empty{padding:2rem;text-align:center;color:#6b8197;border:1px dashed #d5e3ed;border-radius:10px}@media(max-width:576px){.mainv1-metric-dialog{max-height:calc(100vh - 1rem)}.mainv1-metric-modal-header{padding:.9rem}.mainv1-metric-list-wrap{padding:.65rem;max-height:calc(100vh - 100px)}.mainv1-metric-row{grid-template-columns:1fr;gap:.3rem}.mainv1-metric-row strong,.mainv1-metric-row span{white-space:normal}.mainv1-metric-status{width:max-content}}
.mainv1-analytics-top{grid-template-columns:1fr}
.mainv1-share-summary{margin-top:.9rem}.mainv1-share-metrics{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.7rem}.mainv1-share-stat{min-width:0;padding:.75rem;border:1px solid #dce8f0;border-top:3px solid #42b9ba;border-radius:12px;background:linear-gradient(180deg,#fff,#f8fbff)}.mainv1-share-stat span,.mainv1-share-insight span{display:block;color:#71869a;font-size:.67rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase}.mainv1-share-stat strong{display:block;margin:.2rem 0;color:#173d68;font-size:1.45rem}.mainv1-share-stat small{display:block;color:#6b8197;font-size:.68rem;line-height:1.4}.mainv1-share-stat.share-stat-rose{border-top-color:#ff6682}.mainv1-share-stat.share-stat-rose strong{color:#ff4d6d}.mainv1-share-stat.share-stat-blue{border-top-color:#3398dc}.mainv1-share-stat.share-stat-blue strong{color:#2588c9}.mainv1-share-stat.share-stat-gold{border-top-color:#ffc34f}.mainv1-share-stat.share-stat-gold strong{color:#d99c1c}.mainv1-share-insight{margin-top:.7rem;padding:.75rem;border:1px solid #d8e7f3;border-radius:12px;background:#f4f9ff}.mainv1-share-insight strong{display:block;margin-top:.25rem;color:#173d68;font-size:.8rem;line-height:1.35}
@media (min-width: 577px) and (max-width: 1100px) {
    .mainv1-analytics-grid-secondary {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .mainv1-year-panel {
        grid-column: 1 / -1;
    }
}

@media (max-width: 576px) {
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
    .mainv1-directory-controls button { min-height: 36px; padding: .45rem .7rem; border: 1px solid #165a91; border-radius: 7px; background: #15539a; color: #fff; font-size: .75rem; cursor: pointer; white-space: nowrap; }
    .mainv1-directory-controls button:hover, .mainv1-directory-controls button:focus-visible { background: #0d427b; }
    @media (max-width: 1100px) { .mainv1-directory-controls { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 576px) { .mainv1-directory-controls { grid-template-columns: 1fr; } }
</style>
<style>
    .mainv1-directory-table-wrap {
        height: 360px;
        overflow: auto;
    }

    .mainv1-directory-table {
        width: 100%;
        min-width: 900px;
        table-layout: fixed;
    }

    .mainv1-directory-table th,
    .mainv1-directory-table td {
        box-sizing: border-box;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
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
        .mainv1-directory-table-wrap { height: 420px; }
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(() => {
    const rows = @json($mainv1AnalyticsRows);
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
    const openMetricModal = (metric) => { const matchingRows = rows.filter(metricMatches[metric] || metricMatches.all); metricModalTitle.textContent = metricLabels[metric] || 'Metric records'; metricModalSummary.textContent = `${matchingRows.length} social technology records`; metricList.replaceChildren(); matchingRows.forEach(row => { const item = document.createElement('div'); item.className = 'mainv1-metric-row'; const title = document.createElement('strong'); title.textContent = row.title || 'Untitled social technology'; const location = document.createElement('span'); location.textContent = [row.province, row.municipality].filter(Boolean).join(' / ') || 'Location not specified'; const region = document.createElement('span'); region.textContent = row.region || 'Region not specified'; const status = document.createElement('span'); const isInactive = row.status === 'inactive' || row.status === 'dissolved'; status.className = `mainv1-metric-status${isInactive ? ' inactive' : ''}`; status.textContent = isInactive ? 'Inactive' : 'Ongoing'; item.append(title, location, region, status); metricList.appendChild(item); }); if (!matchingRows.length) { const empty = document.createElement('div'); empty.className = 'mainv1-metric-empty'; empty.textContent = 'No social technologies match this metric.'; metricList.appendChild(empty); } metricModal.classList.add('is-open'); metricModal.setAttribute('aria-hidden', 'false'); document.body.classList.add('mainv1-modal-open'); metricModal.querySelector('.mainv1-metric-modal-close').focus(); };
    window.openMainv1RegionRecords = region => { currentMetricKey = 'all'; currentMetricRegion = region || ''; const matchingRows = matchingMetricRows(); metricModalTitle.textContent = `${region} Social Technologies`; metricModalSummary.textContent = `${matchingRows.length} social technology records`; metricList.replaceChildren(); matchingRows.forEach(row => { const item = document.createElement('div'); item.className = 'mainv1-metric-row'; const title = document.createElement('strong'); title.textContent = row.title || 'Untitled social technology'; const location = document.createElement('span'); location.textContent = [row.province, row.municipality].filter(Boolean).join(' / ') || 'Location not specified'; const regionName = document.createElement('span'); regionName.textContent = row.region || 'Region not specified'; const status = document.createElement('span'); const isInactive = row.status === 'inactive' || row.status === 'dissolved'; status.className = `mainv1-metric-status${isInactive ? ' inactive' : ''}`; status.textContent = isInactive ? 'Inactive' : 'Ongoing'; item.dataset.title = String(row.title || '').toLowerCase(); item.dataset.province = row.province || ''; item.dataset.municipality = row.municipality || ''; item.dataset.year = String(row.year || ''); item.append(title, location, regionName, status); metricList.appendChild(item); }); if (!matchingRows.length) { const empty = document.createElement('div'); empty.className = 'mainv1-metric-empty'; empty.textContent = 'No social technologies are recorded for this region.'; metricList.appendChild(empty); } refillMetricSelect(metricProvince, metricFilterValues('province')); refillMetricSelect(metricMunicipality, metricFilterValues('municipality')); refillMetricSelect(metricYear, metricFilterValues('year')); metricTitleSearch.value = ''; metricProvince.value = ''; metricMunicipality.value = ''; metricYear.value = ''; metricModal.classList.add('is-open'); metricModal.setAttribute('aria-hidden', 'false'); document.body.classList.add('mainv1-modal-open'); metricModal.querySelector('.mainv1-metric-modal-close').focus(); };
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
    const pageSize = 8; let page = 1; const search = document.getElementById('mainv1DirectorySearch'); const status = document.getElementById('mainv1DirectoryStatus'); const type = document.getElementById('mainv1DirectoryType'); const filtered = () => rows.filter(row => (!search.value || String(row.title || '').toLowerCase().includes(search.value.toLowerCase())) && (!status.value || (status.value === 'ongoing' ? row.status === 'ongoing' : row.status === 'inactive' || row.status === 'dissolved')) && (!type.value || truthy(row[type.value]))); const renderDirectory = () => { const data = filtered(); const pages = Math.max(1, Math.ceil(data.length / pageSize)); page = Math.min(page, pages); const visible = data.slice((page - 1) * pageSize, page * pageSize); const body = document.getElementById('mainv1DirectoryRows'); body.replaceChildren(); visible.forEach(row => { const tableRow = document.createElement('tr'); const values = [row.title || 'Untitled', row.province || '-', row.municipality || '-']; values.forEach(value => { const cell = document.createElement('td'); cell.textContent = value; tableRow.appendChild(cell); }); const statusCell = document.createElement('td'); const statusPill = document.createElement('span'); statusPill.className = `mainv1-status-pill ${row.status === 'ongoing' ? 'ongoing' : 'inactive'}`; statusPill.textContent = row.status === 'ongoing' ? 'Ongoing' : 'Inactive'; statusCell.appendChild(statusPill); tableRow.appendChild(statusCell); const coverageCell = document.createElement('td'); coverageCell.textContent = [row.expr && 'EOI', row.res && 'Resolution', row.moa && 'MOA', row.replicated && 'Replicated', row.adopted && 'Adopted'].filter(Boolean).join(', ') || '-'; tableRow.appendChild(coverageCell); body.appendChild(tableRow); }); if (!visible.length) { const emptyRow = document.createElement('tr'); const emptyCell = document.createElement('td'); emptyCell.colSpan = 5; emptyCell.textContent = 'No records match the current filters.'; emptyRow.appendChild(emptyCell); body.appendChild(emptyRow); } setText('mainv1DirectorySummary', `${data.length} records`); setText('mainv1DirectoryPage', `Page ${page} of ${pages}`); document.getElementById('mainv1DirectoryPrev').disabled = page <= 1; document.getElementById('mainv1DirectoryNext').disabled = page >= pages; }; [search, status, type].forEach(input => input.addEventListener('input', () => { page = 1; renderDirectory(); })); document.getElementById('mainv1DirectoryPrev').addEventListener('click', () => { page -= 1; renderDirectory(); }); document.getElementById('mainv1DirectoryNext').addEventListener('click', () => { page += 1; renderDirectory(); }); renderDirectory();
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
    const enhanceCoverageCells = () => { directoryBody?.querySelectorAll('tr').forEach(tableRow => { const cell = tableRow.cells[4]; if (!cell || cell.dataset.coverageEnhanced === 'true') return; const labels = cell.textContent.split(',').map(value => value.trim()).filter(Boolean); cell.replaceChildren(); if (!labels.length || labels[0] === '-') { const empty = document.createElement('span'); empty.className = 'mainv1-coverage-empty'; empty.textContent = 'None'; cell.appendChild(empty); } else { const badges = document.createElement('div'); badges.className = 'mainv1-coverage-badges'; labels.forEach(label => { const badge = document.createElement('span'); const className = { EOI: 'coverage-eoi', Resolution: 'coverage-resolution', MOA: 'coverage-moa', Replicated: 'coverage-replicated', Adopted: 'coverage-adopted' }[label]; badge.className = `mainv1-coverage-badge ${className || ''}`; badge.textContent = label; badges.appendChild(badge); }); cell.appendChild(badges); } cell.dataset.coverageEnhanced = 'true'; }); };
    const addDirectoryAttachmentCells = () => { directoryBody?.querySelectorAll('tr').forEach(tableRow => { if (tableRow.cells.length !== 5) return; const title = tableRow.cells[0].textContent; const row = rows.find(candidate => candidate.title === title && candidate.province === tableRow.cells[1].textContent && candidate.municipality === tableRow.cells[2].textContent); const cell = document.createElement('td'); if (row?.attachment_url) { const button = document.createElement('button'); button.type = 'button'; button.className = 'mainv1-attachment-button'; button.textContent = 'View attachment'; button.addEventListener('click', () => openAttachmentModal(row)); cell.appendChild(button); } else { const empty = document.createElement('span'); empty.className = 'mainv1-attachment-empty'; empty.textContent = 'None'; cell.appendChild(empty); } tableRow.appendChild(cell); }); };
    const directoryObserver = directoryBody ? new MutationObserver(addDirectoryAttachmentCells) : null;
    directoryObserver?.observe(directoryBody, { childList: true });
    const coverageObserver = directoryBody ? new MutationObserver(enhanceCoverageCells) : null;
    coverageObserver?.observe(directoryBody, { childList: true, subtree: true });
    renderFilteredDirectory();
    addDirectoryAttachmentCells();
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
                        const popover = titleRow.querySelector('.mainv1-replicate-popover');
                        if (popover) {
                            popover.classList.add('is-confirmed');
                            popover.innerHTML = '<span>Replication selected for this ST.</span>';
                        }
                        window.dispatchEvent(new CustomEvent('st-replication-confirmed', { detail: { title: titleRow.dataset.title || '' } }));
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

        const setup = () => {
            const svg = map.contentDocument;
            if (!svg) return;
            const paths = [...svg.querySelectorAll('path')];
            if (!paths.length) return;
            const infos = paths.map((path) => {
                const region = provinces[path.getAttribute('title') || ''];
                const color = colors[region] || '#cbd5e1';
                path.style.fill = color;
                path.style.transition = 'fill .18s ease, opacity .18s ease, stroke-width .18s ease';
                path.style.cursor = 'pointer';
                return { path, region, color };
            });
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
                    if (label && !region) label.textContent = `Province: ${path.getAttribute('title') || ''}`;
                });
                path.addEventListener('mouseleave', () => {
                    reset();
                    document.querySelectorAll('.mainv1-region-row').forEach((row) => row.classList.remove('is-active'));
                    if (label) label.textContent = 'Hover a region on the map';
                });
                path.addEventListener('click', () => {
                    if (region && window.openMainv1RegionRecords) window.openMainv1RegionRecords(region);
                });
            });
            document.querySelectorAll('.mainv1-region-row').forEach((row) => {
                const code = rowRegionCodes[row.textContent.trim().replace(/\s+\d+$/, '')];
                row.dataset.mapRegion = code || '';
                row.addEventListener('mouseenter', () => highlightRegion(code));
                row.addEventListener('mouseleave', () => {
                    reset();
                    row.classList.remove('is-active');
                    if (label) label.textContent = 'Hover a region on the map';
                });
                row.addEventListener('focus', () => highlightRegion(code));
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