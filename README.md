# LA_CHIENNETÉ — Guide d'installation

## Prérequis

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) ≥ 24
- [Docker Compose](https://docs.docker.com/compose/) ≥ 2.20 (inclus dans Docker Desktop)
- Git

---

## 1. Cloner le dépôt

```bash
git clone <url-du-depot>
cd lachiennete
```

---

## 2. Configurer l'environnement

Copier le fichier d'exemple :

```bash
cp .env.example .env
```

Générer un `APP_SECRET` et remplacer la valeur dans `.env` :

```bash
php -r "echo bin2hex(random_bytes(32));"
```

> La `DATABASE_URL` par défaut pointe vers le conteneur PostgreSQL local. Aucune modification n'est nécessaire pour un lancement local.

---

## 3. Démarrer les conteneurs

```bash
docker compose up --build
```

Cette commande construit l'image PHP et démarre tous les conteneurs (PHP, PostgreSQL, Adminer, Mailpit, Messenger worker). Attendre que la ligne suivante apparaisse dans les logs avant de continuer :

```
php-1  | ==> Démarrage du serveur PHP sur le port 8000...
```

---

## 4. Appliquer les migrations

```bash
docker compose exec php php bin/console doctrine:migrations:migrate
```

---

## 5. Charger les fixtures

```bash
docker compose exec php php bin/console doctrine:fixtures:load
```

> ⚠ Cette commande **efface toutes les données existantes** avant de les recharger.

---

## 6. Accéder à l'application

| Service | URL |
|---|---|
| Application | http://localhost:8089 |
| Adminer (interface BDD) | http://localhost:8088 |
| Mailpit (emails de test) | http://localhost:8025 |

**Connexion Adminer :**
- Système : PostgreSQL
- Serveur : `database`
- Utilisateur : `app`
- Mot de passe : `my-super-secret-password`
- Base de données : `app`

---

## 7. Commandes utiles

| Commande | Description |
|---|---|
| `docker compose up -d` | Démarrer les conteneurs en arrière-plan |
| `docker compose down` | Arrêter les conteneurs |
| `docker compose exec php php bin/console cache:clear` | Vider le cache Symfony |
| `docker compose exec php php bin/console doctrine:migrations:diff` | Générer une migration |
| `docker compose logs -f php` | Suivre les logs PHP |

---

## 8. Comptes de test

Tous les comptes ont le mot de passe : **`password`**

### ROLE_ADMIN
| Email | Prénom | Nom |
|---|---|---|
| admin@snapdeals.fr | Sophie | Lambert |

### ROLE_PRESTATAIRE (vendeur)
| Email | Prénom | Nom |
|---|---|---|
| thomas.lefevre@gmail.com | Thomas | Lefèvre |
| camille.roussel@outlook.fr | Camille | Roussel |
| nicolas.garnier@hotmail.fr | Nicolas | Garnier |

### ROLE_CLIENT (acheteur)
| Email | Prénom | Nom |
|---|---|---|
| julien.moreau@gmail.com | Julien | Moreau |
| emma.petit@gmail.com | Emma | Petit |
| lucas.bernard@outlook.fr | Lucas | Bernard |
| chloe.durand@gmail.com | Chloé | Durand |
| hugo.fontaine@yahoo.fr | Hugo | Fontaine |
| lea.marchand@gmail.com | Léa | Marchand |
| antoine.girard@hotmail.fr | Antoine | Girard |
| manon.chevalier@gmail.com | Manon | Chevalier |
| maxime.dupont@orange.fr | Maxime | Dupont |

### ROLE_BANNI
Aucun compte banni n'est créé par les fixtures. Pour tester ce rôle :
1. Se connecter en tant qu'**admin** (`admin@snapdeals.fr` / `password`)
2. Aller sur `/admin/utilisateurs`
3. Cliquer sur **"Bannir"** sur le compte souhaité
4. Se connecter avec ce compte — la page de bannissement s'affiche

---

## 9. Structure Docker

| Conteneur | Rôle |
|---|---|
| `php` | Serveur PHP built-in sur le port 8000 (exposé en 8089) |
| `database` | PostgreSQL 16 |
| `adminer` | Interface web pour la base de données |
| `mailer` | Mailpit — capture les emails envoyés en dev |
| `messenger-worker` | Consommateur de la file async Symfony Messenger |

---

## 10. Déploiement (production)

Le projet est déployé sur **Render** avec la base de données **Neon** (PostgreSQL managé).

- URL : https://lachiennete.onrender.com
- Branche de déploiement : `prod`
- Les variables d'environnement (`DATABASE_URL`, `APP_SECRET`, etc.) sont configurées dans le dashboard Render
- Les migrations et fixtures sont appliquées automatiquement au démarrage du conteneur via `docker/entrypoint.prod.sh`
