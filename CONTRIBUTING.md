# Contribuer à GestSIS_API

## Environnement de développement

Ce service fait partie d'une architecture microservices. Pour obtenir un environnement de développement complet avec tous les services (API, Auth, APP, Alarm, base de données), utilise le dépôt [GestSIS_Dev_docker](https://github.com/GestSIS/GestSIS_Dev_docker) qui orchestre tout via Docker Compose en quelques commandes.

## Tests

Depuis le dossier `GestSIS_Dev_docker` avec les services démarrés (`make up`) :

```bash
docker compose exec api php artisan test
```

## Contribution

Pour le workflow de contribution, les conventions et le signalement de bugs, voir le [guide de contribution principal](https://github.com/GestSIS/GestSIS_Dev_docker/blob/main/CONTRIBUTING.md).
