{{-- Included inside a parent <script> block. Requires cityCountryMap and selectedDestinations. --}}
    function getSelectedCountriesFromCities() {
        if (typeof selectedDestinations === 'undefined' || !Array.isArray(selectedDestinations)) {
            return [];
        }
        const countries = selectedDestinations
            .map(function (city) { return (cityCountryMap && cityCountryMap[city]) ? cityCountryMap[city] : ''; })
            .filter(Boolean);
        return [...new Set(countries)];
    }

    function syncHeaderCitiesToServiceModals() {
        const cities = (typeof selectedDestinations !== 'undefined') ? [...selectedDestinations] : [];
        const modalIds = [
            'hotelDestination', 'tourDestination', 'guideDestination',
            'mealDestination', 'miscDestination', 'arrivalDestination', 'departureDestination'
        ];

        modalIds.forEach(function (id) {
            const sel = document.getElementById(id);
            if (!sel) return;

            sel.querySelectorAll('option').forEach(function (opt) {
                if (!opt.value) {
                    opt.style.display = '';
                    return;
                }
                opt.style.display = (cities.length === 0 || cities.includes(opt.value)) ? '' : 'none';
            });

            if (cities.length === 1) {
                sel.value = cities[0];
                sel.dispatchEvent(new Event('change'));
            } else if (cities.length === 0) {
                sel.value = '';
            } else if (sel.value && !cities.includes(sel.value)) {
                sel.value = '';
            }
        });
    }
