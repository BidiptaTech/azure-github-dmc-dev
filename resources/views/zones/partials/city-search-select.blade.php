{{--
  Zone city combobox — Tom Select (search types in the same control).

  Use twice on the page (assets first, then field):
  @include('zones.partials.city-search-select', ['section' => 'assets'])
  …
  @include('zones.partials.city-search-select', ['section' => 'field', 'cities' => $city, 'selectedValue' => old('city')])

  Field options: name, id, label, wrapperClass, placeholder, required
--}}
@if(($section ?? 'field') === 'assets')
    @once
        <link rel="stylesheet"
              href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
        <style>
            /* Align Tom Select with Bootstrap `.form-select` */
            select.zone-city-ts + .ts-wrapper.zone-city-ts-wrap {
                min-height: calc(1.5em + 0.75rem + 2px);
                padding: 0;
                border: 1px solid #d9dee3;
                border-radius: var(--bs-border-radius, 0.375rem);
                background-color: #fff;
            }
            select.zone-city-ts + .ts-wrapper.zone-city-ts-wrap .ts-control {
                border: none;
                border-radius: var(--bs-border-radius, 0.375rem);
                padding: 0.3125rem 2rem 0.3125rem 0.75rem;
                box-shadow: none;
                font-size: 0.9375rem;
                min-height: calc(1.5em + 0.75rem);
            }
            select.zone-city-ts + .ts-wrapper.zone-city-ts-wrap.focus .ts-control {
                border-color: #86b7fe !important;
                outline: 0;
                box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            }
            select.zone-city-ts.is-invalid + .ts-wrapper.zone-city-ts-wrap .ts-control {
                border-color: #dc3545 !important;
            }
            select.zone-city-ts.is-invalid + .ts-wrapper.zone-city-ts-wrap.focus .ts-control {
                border-color: #dc3545 !important;
                box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
            }
            select.zone-city-ts + .ts-wrapper.zone-city-ts-wrap.disabled {
                background-color: #eceef1;
                opacity: 1;
            }
            .ts-dropdown.zone-city-ts-dropdown {
                border-radius: var(--bs-border-radius, 0.375rem);
                border: 1px solid #d9dee3;
                box-shadow: 0 0.5rem 1.5rem rgba(67, 89, 113, 0.12);
            }
            .ts-dropdown.zone-city-ts-dropdown .option {
                padding: 0.5rem 0.85rem;
                font-size: 0.9375rem;
            }
            .ts-dropdown.zone-city-ts-dropdown .active {
                background-color: #696cff !important;
                color: #fff !important;
            }
        </style>
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
            <script>
                (function () {
                    function initZoneCityTomSelect() {
                        document.querySelectorAll('select.zone-city-ts:not([data-ts-zone-city-inited])').forEach(function (el) {
                            el.dataset.tsZoneCityInited = '1';
                            var dropdownParent = el.closest('.card-body') || document.body;
                            var tom = new TomSelect(el, {
                                create: false,
                                allowEmptyOption: true,
                                maxOptions: 10000,
                                sortField: { field: 'text', direction: 'asc' },
                                placeholder: el.getAttribute('data-placeholder') || 'Search or select a city…',
                                dropdownParent: dropdownParent,
                                hideSelected: true,
                                render: {
                                    no_results: function () {
                                        return '<div class="no-results p-2 text-muted">No cities match your search.</div>';
                                    }
                                }
                            });
                            tom.wrapper.classList.add('zone-city-ts-wrap');
                            tom.on('dropdown_open', function () {
                                if (tom.dropdown) {
                                    tom.dropdown.classList.add('zone-city-ts-dropdown');
                                }
                            });
                        });
                    }
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', initZoneCityTomSelect);
                    } else {
                        initZoneCityTomSelect();
                    }
                })();
            </script>
        @endpush
    @endonce
@endif

@if(($section ?? 'field') === 'field')
    @php
        $tsName = $name ?? 'city';
        $tsId = $id ?? 'city';
        $tsLabel = $label ?? 'City';
        $tsWrapperClass = $wrapperClass ?? 'col-md-3';
        $tsPlaceholder = $placeholder ?? 'Search or select a city…';
        $tsRequired = $required ?? true;
        $tsSelected = isset($selectedValue) ? (string) $selectedValue : '';
    @endphp
    <div class="{{ $tsWrapperClass }}">
        <label for="{{ $tsId }}" class="form-label">{{ $tsLabel }} <span class="text-danger">*</span></label>
        <select
            class="form-select zone-city-ts @error($tsName) is-invalid @enderror"
            id="{{ $tsId }}"
            name="{{ $tsName }}"
            @if($tsRequired) required @endif
            data-placeholder="{{ $tsPlaceholder }}"
        >
            <option value=""></option>
            @foreach($cities as $c)
                <option value="{{ $c->city_id }}" {{ $tsSelected === (string) $c->city_id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
        @error($tsName)
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
@endif
