<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\FrontendTestimonialModel;
use App\Models\PackageModel;
use App\Services\PackagePricingService;

class HomeController extends BaseController
{
    public function index()
    {
        $packageModel = new PackageModel();
        $pricingService = new PackagePricingService();

        $featuredHajj = $packageModel
            ->where('is_active', 1)
            ->where('package_type', 'hajj')
            ->orderBy('id', 'DESC')
            ->findAll(3);

        $featuredUmrah = $packageModel
            ->where('is_active', 1)
            ->where('package_type', 'umrah')
            ->orderBy('id', 'DESC')
            ->findAll(3);

        $featuredHajj = $this->attachPriceSnippets($featuredHajj, $pricingService);
        $featuredUmrah = $this->attachPriceSnippets($featuredUmrah, $pricingService);

        $testimonials = $this->loadTestimonials();

        return view('frontend/home', [
            'title' => 'HJMS Travel | Hajj, Umrah & Travel Services',
            'activePage' => 'home',
            'featuredHajj' => $featuredHajj,
            'featuredUmrah' => $featuredUmrah,
            'testimonials' => $testimonials,
        ]);
    }

    private function attachPriceSnippets(array $rows, PackagePricingService $pricingService): array
    {
        foreach ($rows as &$row) {
            $summary = $pricingService->summarizePackage((int) ($row['id'] ?? 0));
            $priceAmount = 0.0;
            $priceMode = (string) ($summary['mode'] ?? 'tiered');

            if ($priceMode === 'flat') {
                $priceAmount = (float) ($summary['flat_price'] ?? 0);
                $row['price_snippet'] = $priceAmount > 0 ? 'Flat package rate' : 'Price on request';
            } else {
                $priceMap = (array) ($summary['price_map'] ?? []);
                if ($priceMap !== []) {
                    $priceAmount = min(array_map('floatval', $priceMap));
                }
                $row['price_snippet'] = $priceAmount > 0 ? 'Starting from' : 'Price on request';
            }

            if ($priceAmount <= 0) {
                $priceAmount = (float) ($row['selling_price'] ?? 0);
                if ($priceAmount > 0) {
                    $row['price_snippet'] = 'Estimated from package price';
                }
            }

            $row['price_amount'] = round($priceAmount, 2);
        }

        return $rows;
    }

    private function loadTestimonials(): array
    {
        try {
            if (! db_connect()->tableExists('frontend_testimonials')) {
                return [];
            }

            return (new FrontendTestimonialModel())
                ->where('is_active', 1)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->findAll(12);
        } catch (\Throwable $e) {
            return [];
        }
    }
}
