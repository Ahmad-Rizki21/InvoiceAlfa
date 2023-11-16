@extends('print')

@section('body')
<div class="form-store-printable q-pa-sm q-gutter-md">
  <div class="print-paper-wrapper A4">
    <div class="print-paper">
      <div class="paper-content">
        <div class="store-list-header">
          <div class="company-logo-wrapper">
            <img src="/img/artacom.png" alt="artacom logo" class="company-logo">
          </div>
        </div>
        <div class="table-store-list">
          <div class="store-list-row store-list-header">
            <div class="store-list-no">
              No.
            </div>
            <div class="store-list-name">
              Name
            </div>
            <div class="store-list-address">
              Address
            </div>
          </div>
          @foreach ($stores as $store)
          <div class="store-list-row{{ $loop->last ? ' last' : '' }}">
            <div class="store-list-no">
              {{ $loop->iteration }}
            </div>
            <div class="store-list-name">
              {{ $store->name }}
            </div>
            <div class="store-list-address">
              {{ $store->address }}
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
