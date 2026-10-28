# [TEMPLATE] Symfony hexagonal architecture

This template provides a Symfony (LTS version: 7.4) project setup with an hexagonal architecture and running entirely in docker containers ready for development, test and production.

Stack: FrankenPHP (Caddy) + PHP 8.4, PostgreSQL 16, Docker Compose and a Makefile. See [docs/docker.md](docs/docker.md) for the dockerisation details and [docs/architecture.md](docs/architecture.md) for the application layers.

## Requirements

- [Docker](https://docs.docker.com/engine/install/)
- Makefile (optional)

## Quick start

1. **Clone** this repository:
    ```bash
    git clone https://github.com/Koaden/symfony-hexa-archi-template.git project-name
    ```
2. **Remove** `.git` folder
3. Duplicate `.env.dist` and rename it `.env` (change `APP_SECRET` before deploying to production)
4. Prepare your project for GitHub
    ```bash
    git init project-name
    ```
5. Update this README.md to match your project
6. Make your first commit on the main branch
    ```bash
    git commit -m "chore: init"
    git push
    ```