<?php

namespace App\Support;

class Cities
{
    public const MIN_QUERY_LENGTH = 3;

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            'Bar',
            'Banja Luka',
            'Beograd',
            'Berane',
            'Bihać',
            'Bijeljina',
            'Bitola',
            'Bor',
            'Brčko',
            'Budva',
            'Cetinje',
            'Čačak',
            'Doboj',
            'Dubrovnik',
            'Herceg Novi',
            'Jagodina',
            'Kikinda',
            'Kotor',
            'Kragujevac',
            'Kraljevo',
            'Kruševac',
            'Leskovac',
            'Loznica',
            'Mostar',
            'Negotin',
            'Nikšić',
            'Niš',
            'Novi Pazar',
            'Novi Sad',
            'Ohrid',
            'Osijek',
            'Pančevo',
            'Pirot',
            'Pljevlja',
            'Podgorica',
            'Podujevo',
            'Požarevac',
            'Prijedor',
            'Prilep',
            'Prokuplje',
            'Pula',
            'Rijeka',
            'Sarajevo',
            'Skopje',
            'Smederevo',
            'Sombor',
            'Split',
            'Sremska Mitrovica',
            'Subotica',
            'Šabac',
            'Tivat',
            'Trebinje',
            'Tuzla',
            'Ulcinj',
            'Užice',
            'Valjevo',
            'Vranje',
            'Vršac',
            'Zadar',
            'Zagreb',
            'Zaječar',
            'Zenica',
            'Zrenjanin',
        ];
    }

    /**
     * @param  list<string>  $extra
     * @return list<string>
     */
    public static function catalog(array $extra = []): array
    {
        return collect(array_merge(self::all(), $extra))
            ->map(fn (string $city) => trim($city))
            ->filter()
            ->unique(fn (string $city) => self::normalize($city))
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $catalog
     * @return list<string>
     */
    public static function suggest(string $query, array $catalog = [], int $limit = 8): array
    {
        $query = trim($query);

        if (mb_strlen($query) < self::MIN_QUERY_LENGTH) {
            return [];
        }

        $needle = self::normalize($query);
        $cities = $catalog === [] ? self::all() : $catalog;

        return collect($cities)
            ->filter(fn (string $city) => str_starts_with(self::normalize($city), $needle))
            ->values()
            ->take($limit)
            ->all();
    }

    public static function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));

        return strtr($value, [
            'č' => 'c',
            'ć' => 'c',
            'š' => 's',
            'ž' => 'z',
            'đ' => 'dj',
        ]);
    }
}
