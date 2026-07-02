# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

Symfony 8.0 (PHP ≥ 8.4) marketplace application with API Platform 4, Doctrine ORM 3, and PostgreSQL 16. Frontend uses AssetMapper + Stimulus + Turbo (importmap-based, no Node/npm build step). Validation messages and UI text are in French.

## Development environment

Everything runs through Docker Compose (see `Makefile` and `compose.yaml`):

```sh
make install   # first run: build + start everything
make up        # start containers
make down      # stop containers
make sh        # shell into the PHP container
make cache     # clear Symfony cache
make logs      # tail PHP container logs
```

Services once running:
- App: http://localhost:8089 (PHP built-in server inside the container)
- Adminer (DB UI): http://localhost:8088
- Mailpit (mail catcher): http://localhost:8025
- PostgreSQL on host port **5439** (in-container DSN uses `database:5432`)

The container entrypoint (`docker/entrypoint.sh`) automatically runs `composer install`, warms the cache, and applies pending migrations on startup. A separate `messenger-worker` container consumes the `async` Messenger transport (Doctrine-backed).

## Common commands

Run Symfony console commands inside the PHP container:

```sh
docker compose exec php php bin/console <command>
```

Frequently used:
```sh
docker compose exec php php bin/console make:entity              # scaffold/update entity
docker compose exec php php bin/console doctrine:migrations:diff # generate migration from entity changes
docker compose exec php php bin/console doctrine:migrations:migrate
docker compose exec php php bin/console doctrine:fixtures:load
```

Tests (PHPUnit 12, config in `phpunit.dist.xml`, uses `.env.test`):
```sh
docker compose exec php vendor/bin/phpunit                      # all tests
docker compose exec php vendor/bin/phpunit --filter TestName    # single test
docker compose exec php vendor/bin/phpunit tests/Path/ToTest.php
```

## Architecture

### Domain model (`src/Entity/`)

Marketplace domain: users sell articles, buyers purchase and review them, and communicate via conversations.

- **User** — implements `UserInterface`; table is named `app_user` (not `user`, which is reserved in PostgreSQL). Default role `ROLE_CLIENT`. Owns articles (as seller), purchases (as customer), addresses, reviews, conversations.
- **Article** — belongs to a seller (User) and Category; many-to-many with Tag; one-to-many Images (ordered by `position`), Reviews, Conversations. Pricing rule: `price` is nullable, but an article without a price must set `alternativePayment` — enforced by an `#[Assert\Callback]` (`validatePricing`). This callback pattern is used for cross-field validation.
- **Address** — abstract base using Doctrine **single-table inheritance** with discriminator column `type` mapping to `ShippingAddress` / `BillingAddress`.
- **Purchase** — links customer (User) to Article, timestamps set in constructor (`purchasedAt`).
- **Conversation / Message** — messaging attached to an Article.

Entities set `createdAt`-style timestamps in their constructors with `\DateTimeImmutable`.

### Security (`config/packages/security.yaml`)

Form login (`app_login` route) with CSRF against the `users_in_database` provider (User entity, `email` property). Role hierarchy is central: `ROLE_PRESTATAIRE`, `ROLE_CLIENT`, `ROLE_BANNI`, `ROLE_VIP` all inherit `ROLE_USER`; `ROLE_ADMIN` inherits all of them plus `ROLE_SERVICE_CLIENT` and `ROLE_PREPARATEUR_COMMANDE`; `ROLE_SUPER_ADMIN` tops the hierarchy. `access_control` rules are currently commented out.

### API Platform

Configured stateless (`config/packages/api_platform.yaml`). `src/ApiResource/` exists for standalone API resource classes; entities can also be exposed with `#[ApiResource]` attributes.

### Directory notes

- `src/Controller/` and `src/Enum/` exist but are currently empty — controllers and enums go there.
- Migrations live in `migrations/`; the entrypoint applies them automatically, but generate them explicitly with `doctrine:migrations:diff` after entity changes.
- Frontend assets in `assets/` (Stimulus controllers in `assets/controllers/`), mapped via `importmap.php`.
