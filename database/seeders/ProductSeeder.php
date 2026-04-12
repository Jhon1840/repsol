<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed the products used to calculate rider points by purchased liters.
     */
    public function run(): void
    {
        $products = [
            ['RPP2190ZHC', 'RIDER TOWN 2T 12X1L', 'RIDER', 12, 1200, 100],
            ['RPP5140ZHA', 'SAILOR OUTBOARD & JET SKI 2T 12x1L', 'RIDER', 12, 1200, 100],
            ['RPP2130MHC', 'RIDER 4T 10W-40 12X1L', 'RIDER', 12, 1200, 100],
            ['RPP2130RHC', 'RIDER 4T 15W-50 12X1L', 'RIDER', 12, 1200, 100],
            ['RPP2131THA', 'RIDER TOWN 4T 20W-50 12X1L', 'RIDER', 12, 1200, 100],
            ['RPP2131TBA', 'RIDER TOWN 4T 20W-50 1X208L', 'RIDER', 208, 1200, 100],
            ['RPP2132VHC', 'RIDER HIGH MILEAGE 4T 25W-60 12X1L', 'RIDER', 12, 1200, 100],
            ['RPP2065LHC', 'SMARTER SPORT 4T 10W-30 12X1L', 'SMARTER SPORT', 12, 2400, 200],
            ['RPP2065LBA', 'SMARTER SPORT 4T 10W-30 1x208L', 'SMARTER SPORT', 208, 2400, 200],
            ['RPP2065MHC', 'SMARTER SPORT 4T 10W-40 12X1L', 'SMARTER SPORT', 12, 2400, 200],
            ['RPP2065RHC', 'SMARTER SPORT 4T 15W-50 12X1L', 'SMARTER SPORT', 12, 2400, 200],
            ['RPP2065RCA', 'SMARTER SPORT 4T 15W-50 1X60L', 'SMARTER SPORT', 60, 2400, 200],
            ['RPP2065THC', 'SMARTER SPORT 4T 20W-50 12X1L', 'SMARTER SPORT', 12, 2400, 200],
            ['RPP2062LHC', 'SMARTER HMEOC 4T 10W-30 12X1L', 'SMARTER SYNTHETIC', 12, 3600, 300],
            ['RPP2064MHC', 'SMARTER SYNTHETIC 4T 10W-40 12X1L', 'SMARTER SYNTHETIC', 12, 3600, 300],
            ['RPP2064MGB', 'SMARTER SYNTHETIC 4T 10W-40 5X4L', 'SMARTER SYNTHETIC', 20, 6000, 300],
            ['RPP2064NHC', 'SMARTER SYNTHETIC 4T 10W-50 12X1L', 'SMARTER SYNTHETIC', 12, 3600, 300],
            ['RPP2067THC', 'SMARTER V-TWIN CUSTOM 4T 20W-50 12X1L', 'SMARTER SYNTHETIC', 12, 3600, 300],
            ['RPP9000AHC', 'QUALIFIER FORK OIL SAE 5W 12X1L', 'QUALIFIER', 12, 3600, 300],
            ['RPP9000BHC', 'QUALIFIER FORK OIL SAE 10W 12X1L', 'QUALIFIER', 12, 3600, 300],
            ['RPP9001FHC', 'QUALIFIER TRANSMISSION SAE 75W 12X1L', 'QUALIFIER', 12, 3600, 300],
            ['RPP9004BPB', 'QUALIFIER CHAIN 12X400ml', 'QUALIFIER', 4.8, 3600, 300],
            ['RPP9004APB', 'QUALIFIER CHAIN DRY 12X400ml', 'QUALIFIER', 4.8, 3600, 300],
            ['RPP9007ZPC', 'QUALIFIER DEGREASER_ENGINE CLEANER 12X300ml', 'QUALIFIER', 3.6, 3600, 300],
            ['RPP2053ZHC', 'RACING SYNTH 2T 12X1L', 'RACING', 12, 6000, 500],
            ['RPP2000MHC', 'RACING 4T 10W-40 12X1L', 'RACING', 12, 6000, 500],
            ['RPP2000NHC', 'RACING 4T 10W-50 12X1L', 'RACING', 12, 6000, 500],
            ['RPP2000PHC', 'RACING 4T 10W-60 12X1L', 'RACING', 12, 6000, 500],
            ['RPP2000RHC', 'RACING 4T 15W-50 12X1L', 'RACING', 12, 6000, 500],
            ['RP160M51', 'RP MOTO RACING 4T 15W50 CP-1 D', 'RACING', 12, 6000, 500],
            ['RPP2006MHC', 'RACING OFF ROAD 4T 10W-40 12X1L', 'RACING', 12, 6000, 500],
            ['RPP2005MHC', 'RACING ATV 4T 10W-40 12X1L', 'RACING', 12, 6000, 500],
            ['RP167N51', 'RP MOTO ATV 4T 10W40 CP-1 D', 'RACING', 12, 6000, 500],
        ];

        foreach ($products as [$code, $name, $oilType, $liters, $pointsPerBox, $pointsPerLiter]) {
            Product::query()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'oil_type' => $oilType,
                    'liters' => $liters,
                    'points_per_box' => $pointsPerBox,
                    'points_per_liter' => $pointsPerLiter,
                ],
            );
        }
    }
}
