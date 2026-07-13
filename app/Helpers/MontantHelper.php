<?php

namespace App\Helpers;

class MontantHelper
{
    private static array $units = ['', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf', 'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf'];
    private static array $tens = ['', 'dix', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix'];

    /**
     * Convertit un montant en toutes lettres (français), pour les reçus de paiement.
     */
    public static function enLettres(float $montant): string
    {
        $nombre = (int) round($montant);

        if ($nombre === 0) {
            return 'Zéro franc CFA';
        }

        $result = '';
        $millions = intdiv($nombre, 1000000);
        $resteMillions = $nombre % 1000000;
        $milliers = intdiv($resteMillions, 1000);
        $unite = $resteMillions % 1000;

        if ($millions > 0) {
            $result .= self::enLettresSimple($millions) . ' million' . ($millions > 1 ? 's' : '');
            if ($resteMillions > 0) $result .= ' ';
        }

        if ($milliers > 0) {
            $result .= $milliers === 1 ? 'mille' : self::enLettresSimple($milliers) . ' mille';
            if ($unite > 0) $result .= ' ';
        }

        if ($unite > 0) {
            $result .= self::centainesEnLettres($unite);
        }

        return ucfirst($result) . ' francs CFA';
    }

    private static function enLettresSimple(int $n): string
    {
        return $n < 1000 ? self::centainesEnLettres($n) : (string) $n;
    }

    private static function centainesEnLettres(int $n): string
    {
        $result = '';
        $centaines = intdiv($n, 100);
        $reste = $n % 100;

        if ($centaines > 0) {
            $result .= ($centaines === 1 ? '' : self::$units[$centaines] . ' ') . 'cent' . ($centaines > 1 && $reste === 0 ? 's' : '');
            if ($reste > 0) $result .= ' ';
        }

        if ($reste > 0) {
            if ($reste < 20) {
                $result .= self::$units[$reste];
            } else {
                $d = intdiv($reste, 10);
                $u = $reste % 10;
                if ($d === 7 || $d === 9) {
                    $result .= self::$tens[$d - 1] . '-' . self::$units[$u + 10];
                } else {
                    $result .= self::$tens[$d];
                    if ($u > 0) {
                        $result .= ($u === 1 && $d !== 8 ? ' et ' : '-') . self::$units[$u];
                    }
                }
            }
        }

        return $result;
    }
}
