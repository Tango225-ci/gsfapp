<?php

namespace App\Helpers;

class PhoneHelper
{
    /**
     * Convertit un numéro ivoirien (local à 10 chiffres, ou déjà préfixé 225,
     * ou déjà international) au format E.164 requis par les passerelles SMS.
     * La réforme de numérotation de 2021 en Côte d'Ivoire conserve les 10
     * chiffres locaux tels quels (y compris le 0 initial) : +225 0142595931.
     */
    public static function toE164Ci(string $number): string
    {
        $digits = preg_replace('/\D/', '', $number);

        // Déjà préfixé par l'indicatif ivoirien (225 + 10 chiffres locaux = 13 chiffres)
        if (str_starts_with($digits, '225') && strlen($digits) === 13) {
            return '+' . $digits;
        }

        // Numéro local à 10 chiffres (ex: 0100000566)
        if (strlen($digits) === 10) {
            return '+225' . $digits;
        }

        // Déjà au format international avec un autre indicatif
        if (str_starts_with($number, '+')) {
            return $number;
        }

        return '+' . $digits;
    }
}
