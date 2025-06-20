<?php

namespace App\Helpers;

class CountryHelper
{
    public static function getAllCountries()
    {
        return [
            ["name" => "Afghanistan", "country_code" => "93", "currency" => "AFN"],
            ["name" => "Albania", "country_code" => "355", "currency" => "ALL"],
            ["name" => "Algeria", "country_code" => "213", "currency" => "DZD"],
            ["name" => "Andorra", "country_code" => "376", "currency" => "EUR"],
            ["name" => "Angola", "country_code" => "244", "currency" => "AOA"],
            ["name" => "Argentina", "country_code" => "54", "currency" => "ARS"],
            ["name" => "Armenia", "country_code" => "374", "currency" => "AMD"],
            ["name" => "Australia", "country_code" => "61", "currency" => "AUD"],
            ["name" => "Austria", "country_code" => "43", "currency" => "EUR"],
            ["name" => "Azerbaijan", "country_code" => "994", "currency" => "AZN"],
            ["name" => "Bahamas", "country_code" => "1-242", "currency" => "BSD"],
            ["name" => "Bahrain", "country_code" => "973", "currency" => "BHD"],
            ["name" => "Bangladesh", "country_code" => "880", "currency" => "BDT"],
            ["name" => "Barbados", "country_code" => "1-246", "currency" => "BBD"],
            ["name" => "Belarus", "country_code" => "375", "currency" => "BYN"],
            ["name" => "Belgium", "country_code" => "32", "currency" => "EUR"],
            ["name" => "Belize", "country_code" => "501", "currency" => "BZD"],
            ["name" => "Benin", "country_code" => "229", "currency" => "XOF"],
            ["name" => "Bhutan", "country_code" => "975", "currency" => "BTN"],
            ["name" => "Bolivia", "country_code" => "591", "currency" => "BOB"],
            ["name" => "Bosnia and Herzegovina", "country_code" => "387", "currency" => "BAM"],
            ["name" => "Botswana", "country_code" => "267", "currency" => "BWP"],
            ["name" => "Brazil", "country_code" => "55", "currency" => "BRL"],
            ["name" => "Brunei", "country_code" => "673", "currency" => "BND"],
            ["name" => "Bulgaria", "country_code" => "359", "currency" => "BGN"],
            ["name" => "Burkina Faso", "country_code" => "226", "currency" => "XOF"],
            ["name" => "Burundi", "country_code" => "257", "currency" => "BIF"],
            ["name" => "Cambodia", "country_code" => "855", "currency" => "KHR"],
            ["name" => "Cameroon", "country_code" => "237", "currency" => "XAF"],
            ["name" => "Canada", "country_code" => "1", "currency" => "CAD"],
            ["name" => "Chile", "country_code" => "56", "currency" => "CLP"],
            ["name" => "China", "country_code" => "86", "currency" => "CNY"],
            ["name" => "Colombia", "country_code" => "57", "currency" => "COP"],
            ["name" => "Costa Rica", "country_code" => "506", "currency" => "CRC"],
            ["name" => "Croatia", "country_code" => "385", "currency" => "HRK"],
            ["name" => "Cuba", "country_code" => "53", "currency" => "CUP"],
            ["name" => "Cyprus", "country_code" => "357", "currency" => "EUR"],
            ["name" => "Czech Republic", "country_code" => "420", "currency" => "CZK"],
            ["name" => "Denmark", "country_code" => "45", "currency" => "DKK"],
            ["name" => "Dominican Republic", "country_code" => "1-809", "currency" => "DOP"],
            ["name" => "Ecuador", "country_code" => "593", "currency" => "USD"],
            ["name" => "Egypt", "country_code" => "20", "currency" => "EGP"],
            ["name" => "France", "country_code" => "33", "currency" => "EUR"],
            ["name" => "Germany", "country_code" => "49", "currency" => "EUR"],
            ["name" => "Greece", "country_code" => "30", "currency" => "EUR"],
            ["name" => "India", "country_code" => "91", "currency" => "INR"],
            ["name" => "Indonesia", "country_code" => "62", "currency" => "IDR"],
            ["name" => "Iran", "country_code" => "98", "currency" => "IRR"],
            ["name" => "Iraq", "country_code" => "964", "currency" => "IQD"],
            ["name" => "Ireland", "country_code" => "353", "currency" => "EUR"],
            ["name" => "Italy", "country_code" => "39", "currency" => "EUR"],
            ["name" => "Japan", "country_code" => "81", "currency" => "JPY"],
            ["name" => "Mexico", "country_code" => "52", "currency" => "MXN"],
            ["name" => "Netherlands", "country_code" => "31", "currency" => "EUR"],
            ["name" => "Pakistan", "country_code" => "92", "currency" => "PKR"],
            ["name" => "Saudi Arabia", "country_code" => "966", "currency" => "SAR"],
            ["name" => "South Africa", "country_code" => "27", "currency" => "ZAR"],
            ["name" => "South Korea", "country_code" => "82", "currency" => "KRW"],
            ["name" => "Spain", "country_code" => "34", "currency" => "EUR"],
            ["name" => "Turkey", "country_code" => "90", "currency" => "TRY"],
            ["name" => "United Kingdom", "country_code" => "44", "currency" => "GBP"],
            ["name" => "United States", "country_code" => "1", "currency" => "USD"],
            ["name" => "Vietnam", "country_code" => "84", "currency" => "VND"]
        ];
    }

    public static function getCountryDetails($name)
    {
        foreach (self::getAllCountries() as $country) {
            if ($country['name'] === $name) {
                return $country;
            }
        }
        return null;
    }
    
    public static function getCountryCodeMap()
    {
        return [
            "Afghanistan" => "AF",
            "Albania" => "AL",
            "Algeria" => "DZ",
            "Andorra" => "AD",
            "Angola" => "AO",
            "Argentina" => "AR",
            "Armenia" => "AM",
            "Australia" => "AU",
            "Austria" => "AT",
            "Azerbaijan" => "AZ",
            "Bahamas" => "BS",
            "Bahrain" => "BH",
            "Bangladesh" => "BD",
            "Barbados" => "BB",
            "Belarus" => "BY",
            "Belgium" => "BE",
            "Belize" => "BZ",
            "Benin" => "BJ",
            "Bhutan" => "BT",
            "Bolivia" => "BO",
            "Bosnia and Herzegovina" => "BA",
            "Botswana" => "BW",
            "Brazil" => "BR",
            "Brunei" => "BN",
            "Bulgaria" => "BG",
            "Burkina Faso" => "BF",
            "Burundi" => "BI",
            "Cambodia" => "KH",
            "Cameroon" => "CM",
            "Canada" => "CA",
            "Chile" => "CL",
            "China" => "CN",
            "Colombia" => "CO",
            "Costa Rica" => "CR",
            "Croatia" => "HR",
            "Cuba" => "CU",
            "Cyprus" => "CY",
            "Czech Republic" => "CZ",
            "Denmark" => "DK",
            "Dominican Republic" => "DO",
            "Ecuador" => "EC",
            "Egypt" => "EG",
            "France" => "FR",
            "Germany" => "DE",
            "Greece" => "GR",
            "India" => "IN",
            "Indonesia" => "ID",
            "Iran" => "IR",
            "Iraq" => "IQ",
            "Ireland" => "IE",
            "Italy" => "IT",
            "Japan" => "JP",
            "Mexico" => "MX",
            "Netherlands" => "NL",
            "Pakistan" => "PK",
            "Saudi Arabia" => "SA",
            "Singapore" => "SG",
            "South Africa" => "ZA",
            "South Korea" => "KR",
            "Spain" => "ES",
            "Thailand" => "TH",
            "Turkey" => "TR",
            "United Kingdom" => "GB",
            "United States" => "US",
            "Vietnam" => "VN",
            "Malaysia" => "MY",
            "Philippines" => "PH"
        ];
    }
    
    public static function getCountryCode($countryName)
    {
        $codeMap = self::getCountryCodeMap();
        return $codeMap[$countryName] ?? '';
    }
}
