# Supercharged — Supermarktbeheersysteem

CRUD-applicatie voor het beheren van een supermarkt, gebouwd in PHP 8 met OOP, externe API-integratie en een MySQL-database. Ontwikkeld voor de SD-module _Website met beheer externe data_ (25998).

## Functionaliteiten

- **Dashboard** — Overzicht met statistieken (producten, voorraadwaarde, bestellingen)
- **Producten** — CRUD met zoeken, filteren op categorie en sorteren op prijs/voorraad
- **Categorieën** — CRUD op één pagina met producttelling
- **Leveranciers** — CRUD op één pagina
- **Bestellingen** — CRUD met statiussen, dynamische artikellijnen en statusupdates
- **Prijsvergelijking** — Productprijzen vergelijken met categoriegemiddelden (min/max/verschil)
- **API Zoeken** — Zoek via Open Food Facts (v1 API, Nederlandse locale) en importeer producten
- **JSON API** — Prijsvergelijkingdata op te vragen als JSON (`/api/prices.php`)
- **Authenticatie** — Inloggen met sessies (demo: `admin` / `password`)

## Tech Stack

| Laag | Technologie |
|------|-------------|
| Backend | PHP 8.2 (OOP: Models, Repositories, Services) |
| Database | MySQL 8.0 |
| Frontend | HTML5, CSS3 (design tokens), JavaScript |
| API | Open Food Facts (v1 endpoint, `lc=nl&cc=NL`) |
| Container | Docker + docker-compose |

## Snelstart

```bash
cp .env.example .env
docker compose up -d
```

Daarna beschikbaar op **http://localhost:8000**.

Inloggen: `admin` / `password`

## Projectstructuur

```
├── api/            # API-pagina's (Open Food Facts, prijsvergelijking JSON)
├── categories/     # Categorieën CRUD
├── config/         # Databaseconfiguratie
├── includes/       # Header, footer, authenticatie, init (autoloader, CSRF)
├── orders/         # Bestellingen CRUD
├── prices/         # Prijsvergelijking
├── products/       # Producten CRUD
├── public/         # Statische assets (icoon)
├── sql/            # Schema + seeddata
├── src/            # PHP OOP-klassen
│   ├── Model/      #    Category, Order, Product, Supplier
│   ├── Repository/ #    Category-, Order-, Product-, SupplierRepository
│   └── Service/    #    ApiService, AuthService
├── suppliers/      # Leveranciers CRUD
├── index.html      # Landingspagina (statisch ontwerp)
├── index.php       # Dashboard
├── login.php       # Inlogpagina
├── style.css       # Design tokens en componenten
└── docker-compose.yml
```

## Ontwikkeling

Alle bestanden in de projectroot worden live gemount in de Docker-container. Wijzigingen zijn direct zichtbaar.

```bash
# Container(s) herbouwen
docker compose build --no-cache

# Logs volgen
docker compose logs -f app

# Opnieuw opstarten
docker compose restart
```

## Database

- 6 tabellen: `categories`, `suppliers`, `products`, `orders`, `order_items`, `users`
- Foreign keys met `ON DELETE RESTRICT` en `ON DELETE SET NULL`
- Seeddata met 8 categorieën, 3 leveranciers, 16 producten en 5 bestellingen
- Database wordt automatisch gevuld bij eerste start via `docker-entrypoint-initdb.d`
