/* === STP LITE: geo.js ===
 * Depends: STP_LITE_CONFIG / COUNTRY_CURRENCY_MAP / CITY_COUNTRY_MAP (bootstrap-config)
 * Owns: country↔currency helpers matching classic create.blade.php
 * === */
(function (window) {
    'use strict';

    function getTourCurrency() {
        var el = document.getElementById('tour_package_currency');
        return (el && el.value) ? el.value : (window.TOUR_PACKAGE_CURRENCY || 'SGD');
    }

    function getCurrencyForCountryName(countryName) {
        var name = String(countryName || '').trim();
        if (!name) return '';
        var map = window.COUNTRY_CURRENCY_MAP || {};
        if (map[name]) return String(map[name]).trim().toUpperCase();
        var lower = name.toLowerCase();
        for (var key of Object.keys(map)) {
            if (String(key).toLowerCase() === lower) {
                return String(map[key] || '').trim().toUpperCase();
            }
        }
        var fallback = {
            indonesia: 'IDR', india: 'INR', singapore: 'SGD', malaysia: 'MYR',
            thailand: 'THB', vietnam: 'VND', philippines: 'PHP', 'sri lanka': 'LKR',
            'united arab emirates': 'AED', uae: 'AED', dubai: 'AED'
        };
        return fallback[lower] || '';
    }

    function resolveCountryForCity(cityName) {
        var city = String(cityName || '').replace(/\s*\([^)]*\)\s*$/, '').trim();
        if (!city) return '';
        var geo = window.LITE_CITY_GEO && window.LITE_CITY_GEO[city];
        if (geo && geo.country) return String(geo.country).trim();
        var map = window.CITY_COUNTRY_MAP || {};
        if (map[city]) return String(map[city]).trim();
        var lower = city.toLowerCase();
        for (var key of Object.keys(map)) {
            if (String(key).toLowerCase() === lower) {
                return String(map[key] || '').trim();
            }
        }
        return '';
    }

    function resolveCurrencyForCityName(cityName, countryHint) {
        var city = String(cityName || '').replace(/\s*\([^)]*\)\s*$/, '').trim();
        var geo = city && window.LITE_CITY_GEO ? window.LITE_CITY_GEO[city] : null;
        if (geo && geo.currency) return String(geo.currency).trim().toUpperCase();
        var country = String(countryHint || (geo && geo.country) || '').trim();
        if (!country) country = resolveCountryForCity(cityName);
        var currency = country ? getCurrencyForCountryName(country) : '';
        if (!currency && !country) currency = getTourCurrency();
        return currency;
    }

    function rememberLiteCityGeo(cityName, country, currencyHint) {
        var city = String(cityName || '').replace(/\s*\([^)]*\)\s*$/, '').trim();
        if (!city || /^\d+$/.test(city)) return;
        var countryName = String(country || '').trim() || resolveCountryForCity(city);
        var currency = String(currencyHint || '').trim().toUpperCase()
            || (countryName ? getCurrencyForCountryName(countryName) : '');
        window.LITE_CITY_GEO = window.LITE_CITY_GEO || {};
        window.LITE_CITY_GEO[city] = { country: countryName, currency: currency };
    }

    function resolveSiblingDmcId(countryName) {
        if (isRestrictedThirdParty()) {
            return (window.STP_LITE_CONFIG && window.STP_LITE_CONFIG.dmcId) || 0;
        }
        var map = (window.STP_LITE_CONFIG && window.STP_LITE_CONFIG.siblingDmcCountryMap) || {};
        var name = String(countryName || '').trim();
        if (!name) return (window.STP_LITE_CONFIG && window.STP_LITE_CONFIG.dmcId) || 0;
        if (map[name]) return parseInt(map[name], 10) || 0;
        var lower = name.toLowerCase();
        for (var key of Object.keys(map)) {
            if (String(key).toLowerCase() === lower) return parseInt(map[key], 10) || 0;
        }
        return (window.STP_LITE_CONFIG && window.STP_LITE_CONFIG.dmcId) || 0;
    }

    function resolveDmcIdForCity(cityName, countryHint) {
        var city = String(cityName || '').replace(/\s*\([^)]*\)\s*$/, '').trim();
        var cityMap = (window.STP_LITE_CONFIG && window.STP_LITE_CONFIG.siblingDmcCityMap) || {};
        if (city) {
            if (cityMap[city]) return parseInt(cityMap[city], 10) || 0;
            var lower = city.toLowerCase();
            if (cityMap[lower]) return parseInt(cityMap[lower], 10) || 0;
            for (var key of Object.keys(cityMap)) {
                if (String(key).toLowerCase() === lower) return parseInt(cityMap[key], 10) || 0;
            }
        }
        var country = String(countryHint || '').trim() || resolveCountryForCity(city);
        return resolveSiblingDmcId(country);
    }

    /** Same query shape as backup buildInventoryDmcQuery → fetch-hotels-by-dmc */
    function buildInventoryDmcQuery(cityName, countryHint) {
        var city = String(cityName || '').replace(/\s*\([^)]*\)\s*$/, '').trim();
        var country = String(countryHint || '').trim() || resolveCountryForCity(city);
        var dmcId = resolveDmcIdForCity(city, country)
            || (window.STP_LITE_CONFIG && window.STP_LITE_CONFIG.dmcId)
            || 0;
        var qs = 'city=' + encodeURIComponent(city)
            + '&country=' + encodeURIComponent(country || '')
            + '&dmc_id=' + encodeURIComponent(dmcId);
        return { qs: qs, dmc_id: dmcId, city: city, country: country };
    }

    function isRestrictedThirdParty() {
        return !!(window.STP_LITE_CONFIG && window.STP_LITE_CONFIG.isRestrictedThirdParty);
    }

    function isOwnDmcCountry(country) {
        var n = String(country || '').trim().toLowerCase();
        if (!n) return false;
        var list = (window.STP_LITE_CONFIG && window.STP_LITE_CONFIG.ownDmcCountries) || [];
        return list.some(function (x) {
            return String(x || '').trim().toLowerCase() === n;
        });
    }

    function isForeignLockedCountry(country) {
        if (!isRestrictedThirdParty()) return false;
        var list = (window.STP_LITE_CONFIG && window.STP_LITE_CONFIG.ownDmcCountries) || [];
        if (!list.length) return false;
        return !isOwnDmcCountry(country);
    }

    window.getTourCurrency = getTourCurrency;
    window.getCurrencyForCountryName = getCurrencyForCountryName;
    window.resolveCurrencyForCountry = getCurrencyForCountryName;
    window.resolveCountryForCity = resolveCountryForCity;
    window.resolveCountryForCityName = resolveCountryForCity;
    window.resolveCurrencyForCityName = resolveCurrencyForCityName;
    window.rememberLiteCityGeo = rememberLiteCityGeo;
    window.resolveDmcIdForCity = resolveDmcIdForCity;
    window.getActiveServiceDmcId = function (cityName) {
        return resolveDmcIdForCity(cityName) || (window.STP_LITE_CONFIG && window.STP_LITE_CONFIG.dmcId) || 0;
    };
    window.buildInventoryDmcQuery = buildInventoryDmcQuery;

    window.isRestrictedThirdParty = isRestrictedThirdParty;
    window.isOwnDmcCountry = isOwnDmcCountry;
    window.isForeignLockedCountry = isForeignLockedCountry;

    window.StpLiteGeo = {
        getTourCurrency: getTourCurrency,
        getCurrencyForCountryName: getCurrencyForCountryName,
        resolveCountryForCity: resolveCountryForCity,
        resolveCurrencyForCityName: resolveCurrencyForCityName,
        rememberLiteCityGeo: rememberLiteCityGeo,
        resolveSiblingDmcId: resolveSiblingDmcId,
        resolveDmcIdForCity: resolveDmcIdForCity,
        buildInventoryDmcQuery: buildInventoryDmcQuery,
        isRestrictedThirdParty: isRestrictedThirdParty,
        isOwnDmcCountry: isOwnDmcCountry,
        isForeignLockedCountry: isForeignLockedCountry
    };
})(window);
/* === END geo.js === */
