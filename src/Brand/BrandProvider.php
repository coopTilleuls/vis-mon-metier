<?php

declare(strict_types=1);

namespace App\Brand;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class BrandProvider
{
    private const BRANDS = [
        'pays-de-blain' => ['name' => 'Pays de Blain', 'alt_logo' => 'Logo Pays de Blain', 'logo_ext' => 'svg'],
        'cceg' => ['name' => 'CCEG Erdre & Gesvres', 'alt_logo' => 'Logo CCEG Erdre & Gesvres', 'logo_ext' => 'png'],
    ];

    public function __construct(#[Autowire('%env(APP_BRAND)%')] private readonly string $brand)
    {
    }

    public function getSlug(): string
    {
        return $this->brand;
    }

    public function getName(): string
    {
        return self::BRANDS[$this->brand]['name'] ?? $this->brand;
    }

    public function getAltLogo(): string
    {
        return self::BRANDS[$this->brand]['alt_logo'] ?? '';
    }

    public function getLogoExt(): string
    {
        return self::BRANDS[$this->brand]['logo_ext'] ?? 'png';
    }
}
