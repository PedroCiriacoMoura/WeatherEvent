<?php

namespace App\Services;

use App\Models\Event;

class RecommendationService
{
    private const SUPPORTED_LOCALES = ['en', 'pt', 'es'];

    private const MESSAGES = [
        'summary.excellent' => [
            'en' => 'Conditions look great for this event.',
            'pt' => 'As condições estão ótimas para este evento.',
            'es' => 'Las condiciones se ven excelentes para este evento.',
        ],
        'summary.good' => [
            'en' => 'Conditions are favorable, with minor points to watch.',
            'pt' => 'As condições são favoráveis, com pequenos pontos de atenção.',
            'es' => 'Las condiciones son favorables, con pequeños puntos a vigilar.',
        ],
        'summary.fair' => [
            'en' => 'Conditions are acceptable but require preparation.',
            'pt' => 'As condições são aceitáveis, mas exigem preparação.',
            'es' => 'Las condiciones son aceptables pero requieren preparación.',
        ],
        'summary.poor' => [
            'en' => 'Conditions are unfavorable for this event.',
            'pt' => 'As condições são desfavoráveis para este evento.',
            'es' => 'Las condiciones son desfavorables para este evento.',
        ],
        'summary.hazardous' => [
            'en' => 'Conditions are hazardous; reconsider holding this event.',
            'pt' => 'As condições são perigosas; reconsidere realizar este evento.',
            'es' => 'Las condiciones son peligrosas; reconsidere realizar este evento.',
        ],
        'move_indoors' => [
            'en' => 'Consider moving the event indoors or rescheduling.',
            'pt' => 'Considere mover o evento para um local fechado ou remarcar.',
            'es' => 'Considere trasladar el evento a un lugar cerrado o reprogramar.',
        ],
        'heat' => [
            'en' => 'High temperatures expected — provide water, shade and breaks.',
            'pt' => 'Altas temperaturas esperadas — ofereça água, sombra e pausas.',
            'es' => 'Se esperan altas temperaturas — proporcione agua, sombra y descansos.',
        ],
        'cold' => [
            'en' => 'Cold conditions expected — advise attendees to dress warmly.',
            'pt' => 'Condições de frio esperadas — oriente os participantes a se agasalharem.',
            'es' => 'Se esperan condiciones de frío — recomiende a los asistentes abrigarse.',
        ],
        'rain' => [
            'en' => 'Rain is likely — prepare covered areas and drainage.',
            'pt' => 'Chuva é provável — prepare áreas cobertas e drenagem.',
            'es' => 'Es probable que llueva — prepare áreas cubiertas y drenaje.',
        ],
        'wind' => [
            'en' => 'Strong winds expected — secure structures and signage.',
            'pt' => 'Ventos fortes esperados — fixe estruturas e sinalização.',
            'es' => 'Se esperan vientos fuertes — asegure estructuras y señalización.',
        ],
        'air_quality' => [
            'en' => 'Poor air quality — sensitive attendees should take precautions.',
            'pt' => 'Qualidade do ar ruim — participantes sensíveis devem tomar precauções.',
            'es' => 'Mala calidad del aire — los asistentes sensibles deben tomar precauciones.',
        ],
    ];

    public function for(Event $event, array $weather, array $healthScore): array
    {
        $locale = $this->resolveLocale($event);
        $conditions = $weather['forecast'][0] ?? $weather['current'] ?? [];
        $rating = $healthScore['rating'] ?? 'fair';

        $items = [];

        if ($event->is_outdoor && in_array($rating, ['poor', 'hazardous'], true)) {
            $items[] = $this->translate('move_indoors', $locale);
        }

        if (($conditions['temperature'] ?? null) !== null) {
            if ($conditions['temperature'] >= 30) {
                $items[] = $this->translate('heat', $locale);
            } elseif ($conditions['temperature'] <= 8) {
                $items[] = $this->translate('cold', $locale);
            }
        }

        if ($this->isRainy($conditions)) {
            $items[] = $this->translate('rain', $locale);
        }

        if (($conditions['wind_speed'] ?? 0) >= 8) {
            $items[] = $this->translate('wind', $locale);
        }

        if (($weather['air_quality']['aqi'] ?? 0) >= 4) {
            $items[] = $this->translate('air_quality', $locale);
        }

        return [
            'locale' => $locale,
            'rating' => $rating,
            'summary' => $this->translate('summary.'.$rating, $locale),
            'items' => array_values($items),
        ];
    }

    private function isRainy(array $conditions): bool
    {
        $probability = $conditions['precipitation_probability'] ?? null;

        if ($probability !== null) {
            return (float) $probability >= 0.4;
        }

        return (float) ($conditions['rain'] ?? 0) > 0;
    }

    private function resolveLocale(Event $event): string
    {
        $preferred = $event->user?->preferred_language ?? config('app.locale', 'en');

        return in_array($preferred, self::SUPPORTED_LOCALES, true) ? $preferred : 'en';
    }

    private function translate(string $key, string $locale): string
    {
        return self::MESSAGES[$key][$locale] ?? self::MESSAGES[$key]['en'];
    }
}
