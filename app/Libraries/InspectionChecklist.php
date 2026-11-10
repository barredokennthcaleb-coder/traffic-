<?php

namespace App\Libraries;

final class InspectionChecklist
{
    private const FIELDS = [
        'cert_attendance',
        'outside_route',
        'outside_route_8x8',
        'city_proper',
        'trash_can',
        'mtop_8x16_route',
        'mtop_8x8_route',
        'mtop_dashboard',
        'signal_light_left',
        'signal_light_right',
        'head_light',
        'stop_light',
        'side_mirror',
        'horn',
        'standard_muffler',
        'color_coding',
        'tarifa',
    ];

    public static function normalize(array $data): array
    {
        foreach (self::FIELDS as $field) {
            $data[$field] = isset($data[$field]) ? 1 : 0;
        }

        return $data;
    }
}
