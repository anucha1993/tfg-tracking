<x-layout :title="__('สถานะงาน').' '.$v['jobCode']" :company="$company">
  <section class="card head">
    <img src="{{ asset('logo.png') }}" alt="{{ $company['name'] }}" class="logo">
    <div class="label">{{ __('เลขที่งาน') }}</div>
    <h1>{{ $v['jobCode'] }}</h1>
    @if($v['refNo'])
      <div class="label">{{ __('เลขอ้างอิงใบงาน') }} {{ $v['refNo'] }}</div>
    @endif
    <p class="cust">{{ $v['customer'] }}</p>
    @if(count($v['services']))
      <div class="chips">
        @foreach($v['services'] as $svc)
          <span class="chip">{{ __($svc) }}</span>
        @endforeach
      </div>
    @endif
    <div class="grid">
      <div><span class="label">{{ __('เริ่มดำเนินการ') }}</span><b>{{ \App\Support\PublicView::thDate($v['startDate']) }}</b></div>
      <div><span class="label">{{ __('คาดว่าจะเสร็จ') }}</span><b>{{ \App\Support\PublicView::thDate($v['estimatedFinish']) }}</b></div>
      <div><span class="label">{{ __('ความคืบหน้า') }}</span><b>{{ $v['done'] }}/{{ $v['total'] }} {{ __('ขั้นตอน') }}</b></div>
    </div>
    <div class="bar" role="progressbar" aria-valuenow="{{ $v['percent'] }}" aria-valuemin="0" aria-valuemax="100"><span style="width:{{ $v['percent'] }}%"></span></div>
  </section>

  <section class="card">
    @if($v['allDone'])
      <div class="now">
        <span class="dot" style="background:var(--done);border-color:var(--done);color:#fff">✓</span>
        <div><div class="label">{{ __('สถานะล่าสุด') }}</div><b>{{ __('ดำเนินการครบทุกขั้นตอนแล้ว') }}</b></div>
      </div>
    @elseif($v['current'])
      @php $planText = \App\Support\PublicView::planText($v['current']); @endphp
      <div class="now">
        <span class="dot" style="{{ $v['current']['status'] === 'delayed' ? 'border-color:var(--delay);color:var(--delay);background:var(--delay-bg)' : 'border-color:var(--active);color:var(--active);background:var(--active-bg)' }}">•</span>
        <div>
          <div class="label">{{ __('ขั้นตอนปัจจุบัน') }}</div>
          <b>{{ __($v['current']['title']) }}</b>
          @if($planText)
            <div class="meta">{{ $planText }}</div>
          @endif
        </div>
      </div>
    @else
      <div class="label">{{ __('สถานะ') }}: {{ $v['stage'] ? __($v['stage']) : '-' }}</div>
    @endif
  </section>

  <section class="card">
    <h2>{{ __('ขั้นตอนการดำเนินงาน') }}</h2>
    @if($v['total'])
      <ol>
        @foreach($v['steps'] as $i => $s)
          @php
            $dateLine = $s['status'] === 'done'
              ? __('เสร็จเมื่อ').' '.\App\Support\PublicView::thDate($s['actualDate'] ?: $s['expectedDate'])
              : \App\Support\PublicView::planText($s);
          @endphp
          <li class="{{ $s['status'] }}{{ $s['isCurrent'] ? ' cur' : '' }}">
            <span class="dot">{{ $s['status'] === 'done' ? '✓' : ($s['status'] === 'delayed' ? '!' : $i + 1) }}</span>
            <div class="body">
              <div class="t">{{ __($s['title']) }}
                @if($s['status'] !== 'pending')
                  <span class="badge b-{{ $s['status'] }}">{{ __(\App\Support\PublicView::STATUS_LABEL[$s['status']]) }}</span>
                @endif
              </div>
              @if($dateLine)
                <div class="meta">{{ $dateLine }}</div>
              @endif
              @if($s['delayReason'])
                <div class="reason">{{ __('หมายเหตุ') }}: {{ __($s['delayReason']) }}</div>
              @endif
            </div>
          </li>
        @endforeach
      </ol>
    @else
      <div class="label">{{ __('เจ้าหน้าที่กำลังจัดเตรียมแผนการดำเนินงาน') }}</div>
    @endif
  </section>

  <x-contact :company="$company" />

  <div class="foot">
    {{ __('อัปเดตล่าสุด') }} {{ \App\Support\PublicView::thDateTime($v['updatedAt']) }}<br>
    {{ __('ข้อมูลอาจมีการเปลี่ยนแปลงตามขั้นตอนของหน่วยงานราชการ') }}
  </div>
</x-layout>
