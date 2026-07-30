<?php

namespace App\Support;

class Currencies
{
    /**
     * ISO 4217 currencies in active circulation, with French labels.
     * Deliberately not a PHP enum — currency support should never require
     * a code change, just an entry in this list.
     *
     * @var array<string, string>
     */
    private const array LIST = [
        'XOF' => 'Franc CFA (BCEAO) — XOF',
        'XAF' => 'Franc CFA (BEAC) — XAF',
        'NGN' => 'Naira — NGN',
        'GHS' => 'Cedi — GHS',
        'GMD' => 'Dalasi — GMD',
        'GNF' => 'Franc guinéen — GNF',
        'LRD' => 'Dollar libérien — LRD',
        'SLE' => 'Leone — SLE',
        'CVE' => 'Escudo cap-verdien — CVE',
        'MRU' => 'Ouguiya — MRU',
        'AOA' => 'Kwanza — AOA',
        'CDF' => 'Franc congolais — CDF',
        'STN' => 'Dobra — STN',
        'MAD' => 'Dirham marocain — MAD',
        'DZD' => 'Dinar algérien — DZD',
        'TND' => 'Dinar tunisien — TND',
        'EGP' => 'Livre égyptienne — EGP',
        'LYD' => 'Dinar libyen — LYD',
        'KES' => 'Shilling kényan — KES',
        'UGX' => 'Shilling ougandais — UGX',
        'TZS' => 'Shilling tanzanien — TZS',
        'RWF' => 'Franc rwandais — RWF',
        'BIF' => 'Franc burundais — BIF',
        'ETB' => 'Birr éthiopien — ETB',
        'ZAR' => 'Rand sud-africain — ZAR',
        'ZMW' => 'Kwacha zambien — ZMW',
        'MZN' => 'Metical mozambicain — MZN',
        'MWK' => 'Kwacha malawite — MWK',
        'BWP' => 'Pula botswanais — BWP',
        'NAD' => 'Dollar namibien — NAD',
        'MGA' => 'Ariary malgache — MGA',
        'SDG' => 'Livre soudanaise — SDG',
        'SOS' => 'Shilling somalien — SOS',
        'DJF' => 'Franc djiboutien — DJF',
        'ERN' => 'Nakfa érythréen — ERN',
        'SSP' => 'Livre sud-soudanaise — SSP',
        'SZL' => 'Lilangeni — SZL',
        'LSL' => 'Loti — LSL',
        'SCR' => 'Roupie seychelloise — SCR',
        'MUR' => 'Roupie mauricienne — MUR',
        'KMF' => 'Franc comorien — KMF',
        'EUR' => 'Euro — EUR',
        'USD' => 'Dollar américain — USD',
        'GBP' => 'Livre sterling — GBP',
        'CHF' => 'Franc suisse — CHF',
        'CAD' => 'Dollar canadien — CAD',
        'AUD' => 'Dollar australien — AUD',
        'NZD' => 'Dollar néo-zélandais — NZD',
        'JPY' => 'Yen japonais — JPY',
        'CNY' => 'Yuan chinois — CNY',
        'INR' => 'Roupie indienne — INR',
        'BRL' => 'Réal brésilien — BRL',
        'MXN' => 'Peso mexicain — MXN',
        'AED' => 'Dirham des Émirats arabes unis — AED',
        'SAR' => 'Riyal saoudien — SAR',
        'QAR' => 'Riyal qatari — QAR',
        'TRY' => 'Livre turque — TRY',
        'RUB' => 'Rouble russe — RUB',
        'SEK' => 'Couronne suédoise — SEK',
        'NOK' => 'Couronne norvégienne — NOK',
        'DKK' => 'Couronne danoise — DKK',
        'PLN' => 'Zloty polonais — PLN',
        'CZK' => 'Couronne tchèque — CZK',
        'HUF' => 'Forint hongrois — HUF',
        'RON' => 'Leu roumain — RON',
        'BGN' => 'Lev bulgare — BGN',
        'UAH' => 'Hryvnia ukrainienne — UAH',
        'ILS' => 'Shekel israélien — ILS',
        'KRW' => 'Won sud-coréen — KRW',
        'IDR' => 'Roupie indonésienne — IDR',
        'MYR' => 'Ringgit malaisien — MYR',
        'SGD' => 'Dollar de Singapour — SGD',
        'THB' => 'Baht thaïlandais — THB',
        'PHP' => 'Peso philippin — PHP',
        'VND' => 'Dong vietnamien — VND',
        'PKR' => 'Roupie pakistanaise — PKR',
        'BDT' => 'Taka bangladais — BDT',
        'LKR' => 'Roupie srilankaise — LKR',
        'NPR' => 'Roupie népalaise — NPR',
        'AFN' => 'Afghani — AFN',
        'IQD' => 'Dinar irakien — IQD',
        'IRR' => 'Rial iranien — IRR',
        'JOD' => 'Dinar jordanien — JOD',
        'KWD' => 'Dinar koweïtien — KWD',
        'LBP' => 'Livre libanaise — LBP',
        'OMR' => 'Rial omanais — OMR',
        'SYP' => 'Livre syrienne — SYP',
        'YER' => 'Rial yéménite — YER',
        'BHD' => 'Dinar bahreïni — BHD',
        'ARS' => 'Peso argentin — ARS',
        'CLP' => 'Peso chilien — CLP',
        'COP' => 'Peso colombien — COP',
        'PEN' => 'Sol péruvien — PEN',
        'UYU' => 'Peso uruguayen — UYU',
        'BOB' => 'Boliviano — BOB',
        'PYG' => 'Guarani paraguayen — PYG',
        'VES' => 'Bolívar vénézuélien — VES',
        'GYD' => 'Dollar guyanien — GYD',
        'SRD' => 'Dollar surinamais — SRD',
        'HTG' => 'Gourde haïtienne — HTG',
        'DOP' => 'Peso dominicain — DOP',
        'JMD' => 'Dollar jamaïcain — JMD',
        'TTD' => 'Dollar de Trinité-et-Tobago — TTD',
        'BSD' => 'Dollar bahaméen — BSD',
        'BBD' => 'Dollar barbadien — BBD',
        'BZD' => 'Dollar bélizien — BZD',
        'GTQ' => 'Quetzal guatémaltèque — GTQ',
        'HNL' => 'Lempira hondurien — HNL',
        'NIO' => 'Cordoba nicaraguayen — NIO',
        'CRC' => 'Colón costaricien — CRC',
        'PAB' => 'Balboa panaméen — PAB',
        'CUP' => 'Peso cubain — CUP',
        'ISK' => 'Couronne islandaise — ISK',
        'ALL' => 'Lek albanais — ALL',
        'MKD' => 'Denar macédonien — MKD',
        'RSD' => 'Dinar serbe — RSD',
        'BAM' => 'Mark convertible — BAM',
        'MDL' => 'Leu moldave — MDL',
        'GEL' => 'Lari géorgien — GEL',
        'AMD' => 'Dram arménien — AMD',
        'AZN' => 'Manat azerbaïdjanais — AZN',
        'KZT' => 'Tenge kazakh — KZT',
        'UZS' => 'Sum ouzbek — UZS',
        'TJS' => 'Somoni tadjik — TJS',
        'TMT' => 'Manat turkmène — TMT',
        'KGS' => 'Som kirghize — KGS',
        'MNT' => 'Tugrik mongol — MNT',
        'HKD' => 'Dollar de Hong Kong — HKD',
        'TWD' => 'Nouveau dollar taïwanais — TWD',
        'FJD' => 'Dollar fidjien — FJD',
        'PGK' => 'Kina papouasien — PGK',
        'WST' => 'Tala samoan — WST',
        'TOP' => 'Paʻanga tongan — TOP',
        'XPF' => 'Franc CFP — XPF',
        'BND' => 'Dollar de Brunei — BND',
        'MMK' => 'Kyat birman — MMK',
        'KHR' => 'Riel cambodgien — KHR',
        'LAK' => 'Kip laotien — LAK',
        'BTN' => 'Ngultrum bhoutanais — BTN',
        'MVR' => 'Rufiyaa maldivienne — MVR',
    ];

    /**
     * ISO 3166-1 alpha-2 country code => ISO 4217 currency code. Used as a
     * fallback when a geolocation provider gives a country but no currency
     * directly (e.g. ip-api.com).
     *
     * @var array<string, string>
     */
    private const array COUNTRY_CURRENCY = [
        'BJ' => 'XOF', 'BF' => 'XOF', 'CI' => 'XOF', 'GW' => 'XOF', 'ML' => 'XOF', 'NE' => 'XOF', 'SN' => 'XOF', 'TG' => 'XOF',
        'CM' => 'XAF', 'CF' => 'XAF', 'TD' => 'XAF', 'CG' => 'XAF', 'GQ' => 'XAF', 'GA' => 'XAF',
        'NG' => 'NGN', 'GH' => 'GHS', 'GM' => 'GMD', 'GN' => 'GNF', 'LR' => 'LRD', 'SL' => 'SLE',
        'CV' => 'CVE', 'MR' => 'MRU', 'AO' => 'AOA', 'CD' => 'CDF', 'ST' => 'STN',
        'MA' => 'MAD', 'EH' => 'MAD', 'DZ' => 'DZD', 'TN' => 'TND', 'EG' => 'EGP', 'LY' => 'LYD',
        'KE' => 'KES', 'UG' => 'UGX', 'TZ' => 'TZS', 'RW' => 'RWF', 'BI' => 'BIF', 'ET' => 'ETB',
        'ZA' => 'ZAR', 'LS' => 'LSL', 'SZ' => 'SZL', 'NA' => 'NAD', 'BW' => 'BWP',
        'ZM' => 'ZMW', 'MZ' => 'MZN', 'MW' => 'MWK', 'MG' => 'MGA', 'SD' => 'SDG', 'SS' => 'SSP',
        'SO' => 'SOS', 'DJ' => 'DJF', 'ER' => 'ERN', 'SC' => 'SCR', 'MU' => 'MUR', 'KM' => 'KMF',
        'AT' => 'EUR', 'BE' => 'EUR', 'CY' => 'EUR', 'HR' => 'EUR', 'EE' => 'EUR', 'FI' => 'EUR',
        'FR' => 'EUR', 'DE' => 'EUR', 'GR' => 'EUR', 'IE' => 'EUR', 'IT' => 'EUR', 'LV' => 'EUR',
        'LT' => 'EUR', 'LU' => 'EUR', 'MT' => 'EUR', 'NL' => 'EUR', 'PT' => 'EUR', 'SK' => 'EUR',
        'SI' => 'EUR', 'ES' => 'EUR', 'AD' => 'EUR', 'MC' => 'EUR', 'SM' => 'EUR', 'VA' => 'EUR',
        'US' => 'USD', 'GB' => 'GBP', 'CH' => 'CHF', 'CA' => 'CAD', 'AU' => 'AUD', 'NZ' => 'NZD',
        'JP' => 'JPY', 'CN' => 'CNY', 'IN' => 'INR', 'BR' => 'BRL', 'MX' => 'MXN',
        'AE' => 'AED', 'SA' => 'SAR', 'QA' => 'QAR', 'TR' => 'TRY', 'RU' => 'RUB',
        'SE' => 'SEK', 'NO' => 'NOK', 'DK' => 'DKK', 'PL' => 'PLN', 'CZ' => 'CZK', 'HU' => 'HUF',
        'RO' => 'RON', 'BG' => 'BGN', 'UA' => 'UAH', 'IL' => 'ILS', 'KR' => 'KRW', 'ID' => 'IDR',
        'MY' => 'MYR', 'SG' => 'SGD', 'TH' => 'THB', 'PH' => 'PHP', 'VN' => 'VND', 'PK' => 'PKR',
        'BD' => 'BDT', 'LK' => 'LKR', 'NP' => 'NPR', 'AF' => 'AFN', 'IQ' => 'IQD', 'IR' => 'IRR',
        'JO' => 'JOD', 'KW' => 'KWD', 'LB' => 'LBP', 'OM' => 'OMR', 'SY' => 'SYP', 'YE' => 'YER',
        'BH' => 'BHD', 'AR' => 'ARS', 'CL' => 'CLP', 'CO' => 'COP', 'PE' => 'PEN', 'UY' => 'UYU',
        'BO' => 'BOB', 'PY' => 'PYG', 'VE' => 'VES', 'GY' => 'GYD', 'SR' => 'SRD', 'HT' => 'HTG',
        'DO' => 'DOP', 'JM' => 'JMD', 'TT' => 'TTD', 'BS' => 'BSD', 'BB' => 'BBD', 'BZ' => 'BZD',
        'GT' => 'GTQ', 'HN' => 'HNL', 'NI' => 'NIO', 'CR' => 'CRC', 'PA' => 'PAB', 'CU' => 'CUP',
        'IS' => 'ISK', 'AL' => 'ALL', 'MK' => 'MKD', 'RS' => 'RSD', 'BA' => 'BAM', 'MD' => 'MDL',
        'GE' => 'GEL', 'AM' => 'AMD', 'AZ' => 'AZN', 'KZ' => 'KZT', 'UZ' => 'UZS', 'TJ' => 'TJS',
        'TM' => 'TMT', 'KG' => 'KGS', 'MN' => 'MNT', 'HK' => 'HKD', 'TW' => 'TWD', 'FJ' => 'FJD',
        'PG' => 'PGK', 'WS' => 'WST', 'TO' => 'TOP', 'BN' => 'BND', 'MM' => 'MMK', 'KH' => 'KHR',
        'LA' => 'LAK', 'BT' => 'BTN', 'MV' => 'MVR',
    ];

    /**
     * @return array<string, string>
     */
    public static function list(): array
    {
        return self::LIST;
    }

    public static function isValid(string $code): bool
    {
        return array_key_exists(mb_strtoupper($code), self::LIST);
    }

    public static function label(string $code): string
    {
        return self::LIST[mb_strtoupper($code)] ?? $code;
    }

    public static function forCountry(string $countryCode): ?string
    {
        return self::COUNTRY_CURRENCY[mb_strtoupper($countryCode)] ?? null;
    }
}
