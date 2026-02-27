# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Vis Mon Métier - Pays de Blain** is a Symfony 7.4 application for managing career immersion matching between participants (job seekers) and employers. The core flow: participants and employers fill questionnaires → matching algorithm scores compatibility → admin validates pairings (appariements).

## Commands

All PHP commands run inside Docker via `docker compose exec php ...` or via Makefile shortcuts.

### Development Setup
```bash
docker compose build --pull --no-cache   # Build images
docker compose up --wait                 # Start containers
docker compose exec php bin/console doctrine:migration:migrate
docker compose exec php bin/console app:create-users        # Create admin users
docker compose exec php bin/console app:import-data-2024   # Load fixtures
```

### Daily Development
```bash
make up       # Start containers
make down     # Stop containers
make logs     # Follow logs
make sh       # Shell into PHP container
make cc       # Clear cache
make sf c="about"           # Run any Symfony command
make composer c="req pkg"   # Run any Composer command
```

### Code Quality (run before committing)
```bash
docker compose exec php vendor/bin/php-cs-fixer --no-interaction fix
docker compose exec php vendor/bin/phpstan analyse src/
```

### Testing
```bash
make test                                    # Run all tests
make test c="--group e2e --stop-on-failure"  # Run specific test group
```

### URLs (after `make up`)
- Homepage: `https://localhost/`
- Participant questionnaire: `https://localhost/questionnaire`
- Professional questionnaire: `https://localhost/questionnaire?pro`
- Admin panel: `https://localhost/admin`

## Architecture

### Tech Stack
- **Backend:** Symfony 7.4, PHP 8.4, FrankenPHP (Caddy built-in), PostgreSQL 16
- **Frontend:** Twig templates, Symfony UX, EasyAdmin 4
- **Docker:** Multi-stage builds (dev with Xdebug, prod optimized)

### Core Entities (`src/Entity/`)
- **Session** – A campaign period during which questionnaires are accepted (draft/open/closed status)
- **ModelQuestionnaire** – Template defining questions/scoring for a session
- **Questionnaire** – A single submission (either `participant` or `employeur` type); stores `reponsesDisposition` and `reponsesScore` as JSON arrays
- **Contact** – Personal info attached to a Questionnaire; cascade-deleted when Questionnaire is removed
- **Appariement** – A match record linking a participant Questionnaire to an employer Questionnaire
- **Referent** – Partner organization contact who manages participants
- **Commune** – Municipality entity for location-based filtering
- **User** – Authentication entity with roles: `ROLE_ADMIN > ROLE_REFERENT > ROLE_LECTEUR`

### Key Source Directories
- `src/Controller/` – HTTP controllers including `Admin/` for EasyAdmin overrides
- `src/EasyAdmin/` – Custom EasyAdmin field types and CRUD controllers
- `src/Form/Type/` – Symfony form types (multi-step questionnaire flow)
- `src/Form/Dto/` – DTOs for form data transformation
- `src/ValueObject/` – Enums (e.g., `Type` with `PARTICIPANT`/`EMPLOYEUR` constants)
- `src/Command/` – CLI commands (`CreateUsersCommand`, `ImportQuestionnaireCommand`)
- `src/Security/` – Voters and authentication handlers

### Questionnaire Flow
The questionnaire uses a multi-step form (Symfony Form Flow). The `?pro` query parameter switches between standard and professional modes, affecting which communes are shown and which validations apply. Responses are stored as JSON in `reponsesDisposition` (matching preferences) and `reponsesScore` (compatibility scoring data).

### Admin Panel
EasyAdmin-based at `/admin`. Custom CRUD controllers in `src/EasyAdmin/`. Access controlled by roles — only `ROLE_ADMIN` can write, `ROLE_LECTEUR` is read-only.

## Commit Convention

Commits must follow conventional commits format enforced by commitlint:

```
<type>(<scope>): <subject>
```

**Types:** `build`, `chore`, `ci`, `doc`, `feat`, `fix`, `perf`, `refactor`, `revert`, `style`, `test`

**Scopes:** `cicd`, `unit`, `integration`, `functional`, `e2e`, `import`, `export`, `contact`, `question`, `questionnaire`, `referent`, `appariement`, `modelQuestionnaire`, `session`, `user`, `stats`

Scope is optional. Subject must be lowercase (not sentence/start/pascal case) and no trailing period.

## Domain Vocabulary

The codebase uses French terminology throughout:
- **appariement** = matching/pairing
- **questionnaire** = survey/form submission
- **participant** = job seeker filling the questionnaire
- **employeur** = employer filling the questionnaire
- **session** = campaign period
- **referent** = partner organization contact
- **commune** = municipality
- **disposition** = availability/willingness responses
- **score** = compatibility score responses
