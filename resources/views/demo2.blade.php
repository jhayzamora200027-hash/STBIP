@extends('layouts.app')

@section('content')

<!-- Alpine + Collapse Plugin -->
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<div class="container" style="max-width:900px; margin:40px auto;">

    <h2 style="margin-bottom:20px;">Orders Preview</h2>

    @php
        $orders = [
            ['id'=>1,'customer'=>'Juan Dela Cruz','total'=>1500,'status'=>'Pending','date'=>'Feb 10, 2026','address'=>'Manila','payment'=>'Cash'],
            ['id'=>2,'customer'=>'Maria Santos','total'=>3200,'status'=>'Completed','date'=>'Feb 09, 2026','address'=>'Cebu','payment'=>'GCash'],
            ['id'=>3,'customer'=>'Pedro Reyes','total'=>2750,'status'=>'Cancelled','date'=>'Feb 08, 2026','address'=>'Davao','payment'=>'Card'],
        ];
    @endphp

    <div x-data="{ selected: null }" class="relative space-y-3">

        <template x-for="order in {{ json_encode($orders) }}" :key="order.id">
            <div 
                class="relative transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]"
                :class="selected === order.id ? 'z-20' : 'z-0'"
            >

                <!-- Card -->
                <div 
                    @click="selected = selected === order.id ? null : order.id"
                    :class="selected === order.id 
                        ? 'shadow-2xl scale-[1.03] -translate-y-3' 
                        : 'shadow-md scale-100 translate-y-0'"
                    style="
                        background:#fff;
                        padding:18px;
                        border-radius:12px;
                        border:1px solid #e5e5e5;
                        cursor:pointer;
                        transition: all 0.6s cubic-bezier(0.22,1,0.36,1);
                    "
                >

                    <!-- Summary Row -->
                    <div style="display:grid; grid-template-columns:80px 1fr 120px 120px; gap:10px;">
                        <div>#<span x-text="order.id"></span></div>
                        <div x-text="order.customer"></div>
                        <div>₱<span x-text="order.total.toLocaleString()"></span></div>
                        <div x-text="order.status"></div>
                    </div>

                    <!-- Expanded Content -->
                    <div 
                        x-show="selected === order.id"
                        x-collapse
                        x-transition.opacity.duration.300ms
                        style="margin-top:18px; padding-top:18px; border-top:1px solid #eee;"
                    >
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                            
                            <div>
                                <p><strong>Customer:</strong> <span x-text="order.customer"></span></p>
                                <p><strong>Status:</strong> <span x-text="order.status"></span></p>
                                <p><strong>Total:</strong> ₱<span x-text="order.total.toLocaleString()"></span></p>
                            </div>

                            <div>
                                <p><strong>Order Date:</strong> <span x-text="order.date"></span></p>
                                <p><strong>Shipping Address:</strong> <span x-text="order.address"></span></p>
                                <p><strong>Payment Method:</strong> <span x-text="order.payment"></span></p>
                            </div>

                        </div>

                        <div style="margin-top:20px;">
                            <button 
                                @click.stop="selected = null"
                                style="
                                    padding:8px 16px;
                                    background:#007bff;
                                    color:white;
                                    border:none;
                                    border-radius:8px;
                                    cursor:pointer;
                                    transition: background 0.3s;
                                "
                                onmouseover="this.style.background='#0056b3'"
                                onmouseout="this.style.background='#007bff'"
                            >
                                Back
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        </template>

    </div>

</div>

@endsection