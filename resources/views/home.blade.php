<x-layout
  :title="$company['name'].' - ระบบติดตามสถานะงาน'"
  :company="$company"
  robots="index,follow"
  description="ระบบติดตามสถานะงานออนไลน์ของ {{ $company['name'] }} ตรวจสอบความคืบหน้าใบงานผ่านลิงก์หรือ QR Code ที่ได้รับจากเจ้าหน้าที่ | พัฒนาระบบโดย Anucha Yothanan (อนุชา โยธานันท์)"
  author="Anucha Yothanan (อนุชา โยธานันท์)"
>
  <section class="card empty">
    <h1>{{ $company['name'] }}</h1>
    <p class="cust">ระบบติดตามสถานะงานออนไลน์ สำหรับลูกค้าที่ได้รับลิงก์หรือ QR Code จากเจ้าหน้าที่ สามารถสแกนหรือคลิกลิงก์เพื่อตรวจสอบความคืบหน้าใบงานของท่านได้ทันที</p>
  </section>

  <x-contact :company="$company" />

  <div class="foot">
    ระบบพัฒนาโดย Anucha Yothanan (อนุชา โยธานันท์)
  </div>
</x-layout>
