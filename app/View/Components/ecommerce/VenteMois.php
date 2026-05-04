<?php

namespace App\View\Components\ecommerce;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class VenteMois extends Component
{
    public array $dailySales;
    public array $methods;
    public array $columnTotals;

    public function __construct()
    {
        $this->methods = ['Virement', 'Espèces', 'Chèque', 'Mobile Money'];

        $this->dailySales = [
            ['date' => '01 Avril', 'Virement' => 42000, 'Espèces' => 27000, 'Chèque' => 18000, 'Mobile Money' => 13000],
            ['date' => '02 Avril', 'Virement' => 38000, 'Espèces' => 25000, 'Chèque' => 15000, 'Mobile Money' => 12000],
            ['date' => '03 Avril', 'Virement' => 45000, 'Espèces' => 29000, 'Chèque' => 17000, 'Mobile Money' => 14000],
            ['date' => '04 Avril', 'Virement' => 41000, 'Espèces' => 26000, 'Chèque' => 16000, 'Mobile Money' => 12500],
            ['date' => '05 Avril', 'Virement' => 47000, 'Espèces' => 30000, 'Chèque' => 19000, 'Mobile Money' => 15000],
            ['date' => '06 Avril', 'Virement' => 44000, 'Espèces' => 28000, 'Chèque' => 17500, 'Mobile Money' => 13500],
            ['date' => '07 Avril', 'Virement' => 43000, 'Espèces' => 27500, 'Chèque' => 16800, 'Mobile Money' => 13200],
        ];

        $this->columnTotals = array_fill_keys(array_merge($this->methods, ['total']), 0);
        foreach ($this->dailySales as $sale) {
            foreach ($this->methods as $method) {
                $this->columnTotals[$method] += $sale[$method];
                $this->columnTotals['total'] += $sale[$method];
            }
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.ecommerce.vente-mois');
    }
}




