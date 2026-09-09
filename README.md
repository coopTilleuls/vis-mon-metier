# Vis mon métier

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --pull --no-cache` to build fresh images
3. Run `docker compose up --wait` to set up and start a fresh Symfony project
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

## Start project
1. First run migrations: `docker compose exec php bin/console doctrine:migration:migrate`
2. Create the back-office accounts: `docker compose exec php bin/console app:create-users`
<br>A random password is generated for each account and printed once — store it. Pass `--password=…` to set your own.
3. You can then load fixtures: `docker compose exec php bin/console app:load-test-fixtures`
<br>Or to set your own config, see `docs/guide-administration-vis-mon-metier.md`.
4. You can finally access the homepage on: `/` and questionnaires on: `/questionnaire` and `/questionnaire?pro`.

## Sanitize code
run: `docker compose exec php vendor/bin/php-cs-fixer --no-interaction fix`
run: `docker compose exec php vendor/bin/phpstan analyse src/`

## License

The source code is available under the [PolyForm Noncommercial License 1.0.0](LICENSE):
you may use, modify and redistribute it for any noncommercial purpose.

The public authorities' logos, themes and graphic identities shipped with it are
**not** covered by that license and remain proprietary. See [NOTICE.md](NOTICE.md)
before reusing this repository.

## Credits

Developed by [Les-Tilleuls.coop](https://les-tilleuls.coop) for Pays de Blain Communauté
and the Communauté de Communes d'Erdre & Gesvres.

Built on [Symfony Docker](https://github.com/dunglas/symfony-docker) by
[Kévin Dunglas](https://dunglas.dev), available under the MIT License.
