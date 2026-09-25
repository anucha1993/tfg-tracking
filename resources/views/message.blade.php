<x-layout :title="$title" :company="$company">
  <section class="card empty">
    <h1>{{ $title }}</h1>
    <p class="cust">{{ $message }}</p>
  </section>
  <x-contact :company="$company" />
</x-layout>
