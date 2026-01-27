

![CI](https://github.com/dunglas/symfony-docker/workflows/CI/badge.svg)

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --pull --no-cache` to build fresh images
3. Run `docker compose up --wait` to set up and start a fresh Symfony project
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

## Start project
1. First run migrations: `docker compose exec php bin/console doctrine:migration:migrate`
2. Run this command: `docker compose exec php bin/console app:create-users` to create back-office log
3. You can then load fixtures: `docker compose exec php bin/console app:import-data-2024`
<br>Or to set your own config, see `docs/guide-administration-vis-mon-metier.md`.
4. You can finally access the public form on: `/` and `/?pro`.

## Sanitize code
run: `docker compose exec php vendor/bin/php-cs-fixer --no-interaction fix`
run: `docker compose exec php vendor/bin/phpstan analyse src/`

## License

Symfony Docker is available under the MIT License.

## Credits

Created by [Kévin Dunglas](https://dunglas.dev), co-maintained by [Maxime Helias](https://twitter.com/maxhelias) and sponsored by [Les-Tilleuls.coop](https://les-tilleuls.coop).
