<?php

namespace Database\Seeders;

use App\Enums\AssetUnit;
use App\Models\Asset;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    /**
     * Seed the asset catalog used for development and testing.
     */
    public function run(): void
    {
        $assets = [
            ['name' => 'Capacete de segurança', 'description' => 'Capacete classe B para proteção da cabeça.', 'unit' => AssetUnit::Un],
            ['name' => 'Luva de raspa', 'description' => 'Par de luvas de raspa para trabalho pesado.', 'unit' => AssetUnit::Un],
            ['name' => 'Óculos de proteção', 'description' => 'Óculos ampla visão com lente antirrisco.', 'unit' => AssetUnit::Un],
            ['name' => 'Bota de segurança', 'description' => 'Bota com biqueira de aço.', 'unit' => AssetUnit::Un],
            ['name' => 'Cimento CP-II', 'description' => 'Saco de cimento Portland composto de 50 kg.', 'unit' => AssetUnit::Cx],
            ['name' => 'Areia média', 'description' => 'Areia lavada para argamassa e concreto.', 'unit' => AssetUnit::Kg],
            ['name' => 'Brita 1', 'description' => 'Pedra britada para concreto estrutural.', 'unit' => AssetUnit::Kg],
            ['name' => 'Tinta acrílica branca', 'description' => 'Lata de tinta acrílica fosca para paredes.', 'unit' => AssetUnit::Lt],
            ['name' => 'Thinner', 'description' => 'Solvente para limpeza e diluição de tintas.', 'unit' => AssetUnit::Lt],
            ['name' => 'Cabo elétrico 2,5mm', 'description' => 'Fio flexível 750V para instalações elétricas.', 'unit' => AssetUnit::M],
            ['name' => 'Tubo PVC 100mm', 'description' => 'Tubo de esgoto rígido.', 'unit' => AssetUnit::M],
            ['name' => 'Vergalhão CA-50 10mm', 'description' => 'Barra de aço para concreto armado.', 'unit' => AssetUnit::M],
            ['name' => 'Furadeira de impacto', 'description' => 'Furadeira 1/2" com maleta e brocas.', 'unit' => AssetUnit::Un],
            ['name' => 'Parafusadeira', 'description' => 'Parafusadeira a bateria 12V.', 'unit' => AssetUnit::Un],
            ['name' => 'Serra circular', 'description' => 'Serra circular 7.1/4" 1800W.', 'unit' => AssetUnit::Un],
            ['name' => 'Martelo unha', 'description' => 'Martelo de aço com cabo de madeira.', 'unit' => AssetUnit::Un],
            ['name' => 'Trena 5m', 'description' => 'Trena de aço com trava.', 'unit' => AssetUnit::Un],
            ['name' => 'Nível a laser', 'description' => 'Nível a laser autonivelante.', 'unit' => AssetUnit::Un],
            ['name' => 'Andaime tubular', 'description' => 'Módulo de andaime tubular de 1m.', 'unit' => AssetUnit::Un],
            ['name' => 'Escada de alumínio', 'description' => 'Escada extensível de 7 degraus.', 'unit' => AssetUnit::Un],
            ['name' => 'Notebook corporativo', 'description' => 'Notebook para equipe administrativa.', 'unit' => AssetUnit::Un],
            ['name' => 'Impressora multifuncional', 'description' => 'Impressora laser com scanner.', 'unit' => AssetUnit::Un],
            ['name' => 'Cadeira de escritório', 'description' => 'Cadeira giratória com apoio lombar.', 'unit' => AssetUnit::Un],
            ['name' => 'Resma de papel A4', 'description' => 'Caixa com resmas de papel sulfite 75g.', 'unit' => AssetUnit::Cx],
        ];

        foreach ($assets as $asset) {
            Asset::firstOrCreate(
                ['name' => $asset['name']],
                [
                    'description' => $asset['description'],
                    'unit' => $asset['unit']->value,
                    'active' => true,
                ],
            );
        }
    }
}
