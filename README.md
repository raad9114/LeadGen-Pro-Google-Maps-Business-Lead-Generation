# LeadGen Pro — Google Maps Business Lead Generation

A complete production-ready web application for generating business leads using the **official Google Places API (New)**.

## Features

- **Lead Search** — Search businesses by category and location using Google Places Text Search API
- **Place Details** — Retrieve phone, website, address, rating, reviews for each business
- **Email Enrichment** — Automatically crawl public business websites to find contact emails
- **Duplicate Detection** — Prevents duplicates using Google Place ID
- **Mini CRM** — Track lead status, add notes, view activity timeline
- **Advanced Filters** — Filter by phone, email, website, rating, reviews, status, date range
- **Export** — CSV and XLSX export with filters
- **Search History** — View, re-run, and export past searches
- **Dashboard** — SaaS-style dashboard with stats and charts
- **API Usage Monitoring** — Track Google API requests and costs
- **User Management** — Admin and Staff roles with secure authentication
- **Responsive UI** — Professional design with Bootstrap 5

## Requirements

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache with `mod_rewrite` enabled
- PHP Extensions: `pdo_mysql`, `curl`, `json`, `mbstring`, `zip` (for XLSX export)
- Google Cloud project with **Places API (New)** enabled and billing configured

## Installation

### 1. Clone/Upload Files

Upload all project files to your web server's document root or a subdirectory.

### 2. Configure Apache

Ensure `mod_rewrite` is enabled:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Allow `.htaccess` overrides in your Apache config:
```apache
<Directory /var/www/html/google_map_data_scraping>
    AllowOverride All
</Directory>
```

### 3. Run Web Installer

Navigate to: `http://yourdomain.com/google_map_data_scraping/public/install`

The installer will:
- Check PHP version and required extensions
- Test MySQL connection
- Create the database and tables
- Seed default categories and locations
- Create your admin account
- Write the `.env` configuration file

### 4. Get Google Places API Key

1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Create or select a project
3. Navigate to **APIs & Services** → **Enable APIs**
4. Enable **Places API (New)**
5. Go to **Credentials** → **Create API Key**
6. Restrict the key to your server's IP address
7. Ensure billing is enabled on the project
8. Enter the API key in **Settings** → **Google API Configuration**

### 5. Login

Default credentials (change immediately after first login):
- **Username:** `admin`
- **Password:** `admin123`

## Usage

### Searching for Leads

1. Go to **Lead Search**
2. Select a **Business Category** (e.g., Restaurant) or type a custom one
3. Select **Country** → **City** → **Area** (or type a custom location)
4. Set **Maximum Leads** (default: 60)
5. Click **Search Leads**
6. Wait for the search to complete — the system will:
   - Search Google Places for matching businesses
   - Fetch detailed information for each result
   - Check for duplicates (by Place ID)
   - Optionally crawl business websites for email addresses

### Managing Leads

- View all leads with advanced filters
- Click a lead to see full details, add notes, change status
- Bulk operations: change status, find emails, export, delete
- Export to CSV or XLSX at any time

## Project Structure

```
├── config/             # Configuration (env loader, DB, router)
├── app/
│   ├── controllers/    # Request handlers
│   ├── models/         # Database models (PDO)
│   ├── services/       # Business logic (Places API, email enrichment, export)
│   └── helpers/        # Session, CSRF, validation, response
├── public/             # Web root (index.php, assets)
│   ├── assets/css/     # Stylesheets
│   └── assets/js/      # JavaScript
├── templates/
│   ├── layouts/        # Main layout (sidebar + header)
│   └── pages/          # Page templates
├── database/
│   ├── schema.sql      # Full database schema (13 tables)
│   └── seed.sql        # Default data
├── storage/logs/       # Application logs
├── install/            # Web installer
├── .env.example        # Environment template
└── README.md
```

## Security

- Passwords hashed with `password_hash()` (bcrypt, cost 12)
- Session fixation prevention via `session_regenerate_id()`
- CSRF tokens on all POST/PUT/DELETE requests
- All SQL via PDO prepared statements
- XSS prevention with `htmlspecialchars()` on all output
- API key stored server-side, never exposed to browser
- Role-based access control (Admin / Staff)
- No stack traces or sensitive data in error responses

## Google API Compliance

- Uses **official Google Places API (New)** exclusively
- No scraping of Google Maps website
- Implements rate limiting and exponential backoff
- Uses field masks to minimize API costs
- Respects quota limits and handles errors gracefully
- Email enrichment only crawls publicly accessible business websites
- Respects `robots.txt` and reasonable rate limits

## License

For personal and commercial use. Ensure compliance with Google Maps Platform Terms of Service.
