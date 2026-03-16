# Multi-Brand Deployment

This application supports multiple branded instances from a single Docker image. Each brand has its own colors, logos, and text strings. Brand selection happens at runtime via the `APP_BRAND` environment variable.

## Supported brands

| Slug | Name | Domain |
|------|------|--------|
| `pays-de-blain` | Pays de Blain | vis-mon-metier.fr |
| `cceg` | CCEG Erdre & Gesvres | cceg.vis-mon-metier.fr |

## How it works

### BrandProvider service

`App\Brand\BrandProvider` reads `APP_BRAND` from the environment and exposes brand metadata (name, logo alt text, logo file extension). It is registered as a Twig global (`brand`), making it available in all templates:

```twig
{{ brand.slug }}     {# "pays-de-blain" or "cceg" #}
{{ brand.name }}     {# "Pays de Blain" or "CCEG Erdre & Gesvres" #}
{{ brand.altLogo }}  {# alt text for logo images #}
{{ brand.logoExt }}  {# "svg" or "png" #}
```

### CSS theming

Each brand defines its own `:root` CSS variables in `public/styles/brand/<slug>.css`. These files are loaded before any page-specific CSS, so all existing color references (e.g., `var(--green-color)`) resolve to the brand's palette automatically.

To modify a brand's colors, edit the corresponding file:
- `public/styles/brand/pays-de-blain.css`
- `public/styles/brand/cceg.css`

### Logos

Brand logos live in `public/images/brand/<slug>/`:
- `logo.<ext>` — colored logo for the home page header
- `logo-white.png` — white logo for sidebar/dark backgrounds

### Admin dashboard

The EasyAdmin dashboard title is set dynamically: "Vis mon métier - {brand name}".

## Adding a new brand

1. Add an entry to `BrandProvider::BRANDS` with `name`, `alt_logo`, and `logo_ext`.
2. Create `public/styles/brand/<new-slug>.css` with the brand's `:root` color variables.
3. Create `public/images/brand/<new-slug>/` with `logo.<ext>` and `logo-white.png`.
4. Add a deploy job in `.github/workflows/cd.yml` calling `deploy.yml` with the new brand/domain inputs.
5. Configure GitHub vars: `PROD_DOMAIN_<BRAND>`, `NONPROD_DOMAIN_<BRAND>`, and database secrets.

## Local development

Set the brand in `.env.local`:

```
APP_BRAND=cceg
```

Then clear the cache and reload:

```bash
php bin/console cache:clear
```

## Deployment

Each brand is deployed as a separate Helm release in the same namespace. The `deploy.yml` workflow accepts `brand`, `prod_domain`, and `nonprod_domain` inputs. The `cd.yml` workflow calls it twice in parallel — once per brand.

Each brand needs its own:
- Database (separate `PGUSER`, `PGHOST`, `PGDATABASE`, `PGPASSWORD` secrets)
- Domain name (configured via GitHub vars)

The Docker image is shared — built once, deployed twice with different `APP_BRAND` values.
