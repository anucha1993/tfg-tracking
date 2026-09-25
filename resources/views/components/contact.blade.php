@if($company['phone'] || $company['line_url'])
<section class="card">
  <h2>{{ __('มีคำถามเกี่ยวกับงานนี้?') }}</h2>
  <div class="label">{{ __('แจ้งเลขที่งานกับเจ้าหน้าที่เพื่อความรวดเร็ว') }}</div>
  <div class="contact">
    @if($company['phone'])
      <a class="btn" href="tel:{{ preg_replace('/[^\d+]/', '', $company['phone']) }}">{{ __('โทร') }} {{ $company['phone'] }}</a>
    @endif
    @if($company['line_url'])
      <a class="btn line" href="{{ $company['line_url'] }}" target="_blank" rel="noopener">{{ __('ติดต่อทาง LINE') }}</a>
    @endif
  </div>
</section>
@endif
