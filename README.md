# Crypto Portfolio Tracker

Mini-application Laravel de gestion d'un portefeuille de cryptomonnaies. Elle affiche les cryptos détenues, leur quantité, leur valeur actuelle en USD et en EUR, ainsi que la valeur totale du portefeuille — via une page web et une API JSON.

Une même cryptomonnaie peut être détenue sur plusieurs plateformes (ex. 0,1 BTC sur Binance et 0,02 BTC sur Kraken). Chaque ligne du portefeuille associe donc **une crypto**, **une plateforme** et **une quantité**.

## Stack

- **Back** : Laravel 13 / PHP 8.4, base SQLite
- **Front** : React 19 (SPA), Tailwind CSS 4, Vite
- Prix via l'API publique [CoinGecko](https://www.coingecko.com/en/api)

L'interface est une **application React monopage**. Laravel ne sert qu'une coquille HTML (`resources/views/app.blade.php`) ; toutes les données affichées proviennent de l'API JSON décrite plus bas.

## Architecture

```
resources/js/
├── app.jsx              point d'entrée, monte React sur #app
├── PortfolioApp.jsx     composant racine : état, chargement, CRUD
├── api.js               client fetch de l'API Laravel
├── format.js            formatage monnaies / quantités / dates (Intl)
└── components/
    ├── PortfolioTable.jsx
    ├── HoldingForm.jsx
    └── SummaryCards.jsx
```

Ce qui est **stocké** en base : les lignes du portefeuille (crypto, plateforme, quantité).
Ce qui est **calculé à chaque requête** : les prix, les valeurs et les totaux — jamais persistés.

## API

| Méthode | Route | Rôle |
|---|---|---|
| `GET` | `/api/portfolio` | portefeuille valorisé, trié par valeur décroissante, avec totaux |
| `GET` | `/api/holdings` | lignes stockées |
| `POST` | `/api/holdings` | créer une ligne (201) |
| `GET` | `/api/holdings/{id}` | une ligne |
| `PUT` | `/api/holdings/{id}` | modifier (200) |
| `DELETE` | `/api/holdings/{id}` | supprimer (204) |
| `GET` | `/api/cryptocurrencies` | référentiel des cryptos |
| `GET` | `/api/platforms` | référentiel des plateformes |

Les erreurs de validation renvoient un **422** avec le détail par champ.

## Installation

```bash
git clone https://github.com/Ruskofff/crypto-portfolio-tracker.git
cd crypto-portfolio-tracker
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
```

Puis lancer le serveur de développement :

```bash
composer run dev
```

## Configuration

Tous les réglages métier sont centralisés dans `config/portfolio.php` et pilotés par les variables d'environnement suivantes.

### Source des prix

| Variable | Défaut | Rôle |
|---|---|---|
| `PRICE_DRIVER` | `coingecko` | `coingecko` (prix réels) ou `fake` (prix simulés, sans réseau) |
| `PRICE_FALLBACK` | `true` | Bascule sur les prix simulés si l'API est indisponible ou rate-limitée |
| `PRICE_CACHE_TTL` | `60` | Durée de cache d'une cotation, en secondes |
| `COINGECKO_API_KEY` | *(vide)* | Optionnelle — relève les limites de l'API publique |

### Conversion USD → EUR

| Variable | Défaut | Rôle |
|---|---|---|
| `EXCHANGE_RATE_DRIVER` | `frankfurter` | `frankfurter` (taux du jour via API, par défaut) ou `fixed` (taux configurable) |
| `EXCHANGE_RATE_FALLBACK` | `true` | Bascule sur le taux fixe si l'API Frankfurter est indisponible |
| `EXCHANGE_RATE_USD_EUR` | `0.92` | Taux utilisé par le driver `fixed`, et par le repli quand Frankfurter échoue |
| `EXCHANGE_RATE_CACHE_TTL` | `3600` | Durée de cache du taux, en secondes |

> Les prix et le total sont recalculés **à chaque appel**, que ce soit sur la page web ou sur l'API. Le cache ne sert qu'à respecter les quotas des API externes.

## Licence

Projet réalisé dans un cadre pédagogique.
