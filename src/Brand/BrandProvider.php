<?php

declare(strict_types=1);

namespace App\Brand;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class BrandProvider
{
    private const BRANDS = [
        'pays-de-blain' => [
            'name' => 'Pays de Blain',
            'alt_logo' => 'Logo Pays de Blain',
            'logo_ext' => 'svg',
            'subtitle' => 'au Pays de Blain',
            'communaute' => 'Pays de Blain Communauté',
            'territoire' => 'Pays de Blain Communauté',
            'address' => '7 rue Victor Schoelcher – 44130 Blain',
            'phone' => '02.40.79.99.01',
            'email' => 'service.emploi@paysdeblain.fr',
            'website' => 'https://www.pays-de-blain.com',
            'privacy_url' => 'https://www.pays-de-blain.com/politique-de-confidentialite/',
            'partners' => [
                'Mission Locale Nord Atlantique antenne de Blain',
                'France Travail agence de Blain',
                'Unité Emploi de Blain du Conseil Départemental de Loire-Atlantique',
                'Structures d\'insertion par l\'activité économique ATRE et AIRE',
                'Organismes de formation intervenant sur le territoire de Pays de Blain Communauté',
                'Etablissements scolaires situés à Blain (collèges et lycées)',
                'CIO de l\'académie de Nantes',
                'Avenir Actifs (pour les personnes salariées)',
            ],
        ],
        'cceg' => [
            'name' => 'CCEG Erdre & Gesvres',
            'alt_logo' => 'Logo CCEG Erdre & Gesvres',
            'logo_ext' => 'png',
            'subtitle' => 'en Erdre et Gesvres',
            'communaute' => 'Communauté de Communes d\'Erdre & Gesvres',
            'territoire' => 'la CCEG',
            'address' => 'PA La Grand\'Haie, 1 Rue Marie Curie – 44119 Grandchamp-des-Fontaines',
            'phone' => '02 28 02 22 40',
            'email' => 'contact@cceg.fr',
            'website' => 'https://www.cceg.fr/',
            'privacy_url' => 'https://www.cceg.fr/informations-pratiques/politique-de-gestion-des-donnees-a-caractere-personnel',
            'partners' => [
                'Mission Locale Nord Atlantique',
                'France Travail',
                'Unité Emploi du Conseil Départemental de Loire-Atlantique',
                'Structures d\'insertion par l\'activité économique ATRE et AIRE',
                'Organismes de formation intervenant sur le territoire de Communauté de Communes d\'Erdre & Gesvres',
                'Etablissements scolaires',
                'CIO de l\'académie de Nantes',
                'Avenir Actifs (pour les personnes salariées)',
            ],
        ],
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

    public function getSubtitle(): string
    {
        return self::BRANDS[$this->brand]['subtitle'] ?? '';
    }

    public function getCommunaute(): string
    {
        return self::BRANDS[$this->brand]['communaute'] ?? '';
    }

    public function getTerritoire(): string
    {
        return self::BRANDS[$this->brand]['territoire'] ?? '';
    }

    public function getAddress(): string
    {
        return self::BRANDS[$this->brand]['address'] ?? '';
    }

    public function getPhone(): string
    {
        return self::BRANDS[$this->brand]['phone'] ?? '';
    }

    public function getEmail(): string
    {
        return self::BRANDS[$this->brand]['email'] ?? '';
    }

    public function getWebsite(): ?string
    {
        return self::BRANDS[$this->brand]['website'] ?? null;
    }

    public function getPrivacyUrl(): ?string
    {
        return self::BRANDS[$this->brand]['privacy_url'] ?? null;
    }

    /**
     * @return string[]
     */
    public function getPartners(): array
    {
        return self::BRANDS[$this->brand]['partners'] ?? [];
    }
}
