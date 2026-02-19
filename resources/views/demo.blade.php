@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css"/>

<style>
/* RESET */
*, *::before, *::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

html, body {
    height: 100%;
    overflow: hidden;
}

/* LAYOUT */
.slider-wrapper {
    position: fixed;
    top: 50px;
    left: 260px;
    width: calc(100% - 260px);
    height: calc(100% - 50px);
    display: flex;
    background: #000;
}

/* SWIPER */
.swiper {
    height: 100%;
}

/* MAIN / NAV */
.main-slider {
    width: calc(100% - 240px);
    height: 100%;
}

/* NAV SLIDER */
.nav-slider {
    width: 240px;
    background: #111;
    padding: 10px 0;
    height: 100%;
}

/* Make swiper fill height */
.nav-slider .swiper-wrapper {
    height: 100%;
}

/* SLIDES */
.swiper-slide {
    position: relative;
    overflow: hidden;
    color: #fff;
    display: flex;
    justify-content: center;
}

/* FIXED BACKGROUND */
.fixed-bgimg {
    position: fixed;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 100vw;
    height: 100vh;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    z-index: -1;
    transition: background-image .6s ease-in-out;
}

/* CONTENT */
.content {
    position: absolute;
    top: 40%;
    left: 5%;
    width: 60%;
}

.title {
    font-size: 2.6em;
    font-weight: bold;
    margin-bottom: 20px;
}

.caption {
    font-size: 14px;
    opacity: 0;
    transform: translateX(30px);
    transition: all .6s ease;
}

.caption.show {
    opacity: 1;
    transform: translateX(0);
}

/* Nav slides auto-expand */
.nav-slider .swiper-slide {
    flex: 1 0 auto;       
    height: auto;         
    min-height: 120px;    
    opacity: .4;
    cursor: pointer;
    transition: opacity .3s ease, transform .3s ease;
    display: flex;
}

/* Active slide */
.nav-slider .swiper-slide-active {
    opacity: 1;
    transform: scale(1.05);
}

.nav-bg {
    width: 100%;
    height: 100%;
    background-size: cover;      /* 👈 THIS is the key */
    background-repeat: no-repeat;
    background-position: center;
    border-radius: 6px;
    border: 2px solid rgba(255,255,255,.15);
}
</style>

@php
$images = [
    null,
    '1.png','2.png','3.png','4_a.png','4_b.png','5.png','6.png','7.png',
    '8.png','9.png','10.png','11.png','12.png','13.png','ncr.png','car.png','barmm.png',
    'nir.png'
];

$titles = [
    'Total STs by Region',
    'Region I','Region II','Region III','Region IV-A','Region IV-B',
    'Region V','Region VI','Region VII','Region VIII','Region IX','Region X',
    'Region XI','Region XII','Region XIII','NCR','CAR','BARMM','NIR'
];

$count = count($images);
@endphp

<div class="slider-wrapper">

    <!-- FIXED BG -->
    <div class="fixed-bgimg" id="bg-img"
        style="background-image:url('{{ asset('images/dattachments/Background Design4.png') }}')">
    </div>

    <!-- MAIN -->
    <div class="swiper main-slider">
        <div class="swiper-wrapper">
            @foreach ($images as $i => $img)
                <div class="swiper-slide"
                     data-bg-image="{{ asset('images/dattachments/'.$img) }}">
                    <div class="content">
                        <div class="title">{{ $titles[$i] }}</div>
                        <div class="caption">Scanning slide effect</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- NAV -->
    <div class="swiper nav-slider">
        <div class="swiper-wrapper">
            @foreach ($images as $img)
                <div class="swiper-slide">
                    <div class="nav-bg"
                         style="background-image:url('{{ asset('images/ST Regional Nav Slide/'.$img) }}')">
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>

<script>
/* MAIN SLIDER */
const mainSlider = new Swiper('.main-slider', {
    loop: true,
    loopedSlides: {{ $count }},
    speed: 900,
    effect: 'slide',
    on: {
        init() {
            updateBG(this);
            showCaption(this);
        },
        slideChange() {
            updateBG(this);
        },
        slideChangeTransitionEnd() {
            showCaption(this);
        }
    }
});

/* NAV SLIDER */
const navSlider = new Swiper('.nav-slider', {
    direction: 'vertical',
    slidesPerView: 5,
    centeredSlides: true,
    slideToClickedSlide: true,
    loop: true,
    loopedSlides: {{ $count }},
    speed: 900,
});

/* SYNC */
mainSlider.controller.control = navSlider;
navSlider.controller.control = mainSlider;

/* HELPERS */
function updateBG(swiper) {
    const slide = swiper.slides[swiper.activeIndex];
    document.getElementById('bg-img').style.backgroundImage =
        `url('${slide.dataset.bgImage}')`;
}

function showCaption(swiper) {
    document.querySelectorAll('.caption')
        .forEach(c => c.classList.remove('show'));

    swiper.slides[swiper.activeIndex]
        .querySelector('.caption')
        .classList.add('show');
}
</script>

@endsection
