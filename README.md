✈️ AfterTravel — Esprit PIDEV 2024–2025

A full-stack travel management web application built with Symfony 7, covering everything from trip bookings and car rentals to documents, budgets, activities, and an AI-powered chatbot.

📌 Project Overview AfterTravel is a multi-module travel platform developed as part of the PIDEV project at Esprit School of Engineering. It serves two roles — travellers who browse and book trips, and admins who manage the entire catalog from a single unified dashboard.

🗂️ Modules user,trip & destinations,documents,activities &planning ,services,reservations, Dépenses / Budget .

🖥️ The Admin Dashboard All admin operations live in one single dashboard at /admin (admin/dashboard.html.twig), rendered by AdminController. It consolidates:

Reservation and payment tables with search, filter, sort Document management with AI category auto-detection User management Voyage & destination CRUD Activity management + PDF export Car fleet management and reservation stats Offer, service, and expense overviews Live charts (reservation timeline, monthly revenue, top destinations)

🛠️ Tech Stack

Backend: Symfony 7 (PHP 8.2+), Doctrine ORM Frontend: Twig, Bootstrap 5, custom CSS Database: MySQL File Storage: Cloudinary (with local fallback) Payments: Stripe Checkout AI / ML: Google Gemini API (chatbot, category detection, recommendations) PDF Generation: DomPDF, KnpSnappy QR Codes: Endroid QR Code Email: Mailjet Auth: Symfony Security, OAuth (Google/GitHub), face recognition Other: REST Countries API, KnpPaginator, VichUploader

⚙️ Installation Prerequisites

PHP 8.2+ Composer MySQL 8+ Node.js (for Vite assets, optional)

Steps bash# 1. Clone the repository git clone cd Esprit-PIDEV-3A24-2526

2. Install PHP dependencies
composer install

3. Configure environment
cp .env .env.local

Edit .env.local — set DATABASE_URL, STRIPE_, CLOUDINARY_, GEMINI_API_KEY, MAILER_DSN, etc.
4. Create the database and run migrations
php bin/console doctrine:database:create php bin/console doctrine:migrations:migrate

5. (Optional) Load fixtures
php bin/console doctrine:fixtures:load

6. Start the dev server
symfony server:start

or
php -S localhost:8000 -t public/

🔑 Environment Variables VariableDescriptionDATABASE_URLMySQL connection stringSTRIPE_SECRET_KEYStripe secret keySTRIPE_PUBLISHABLE_KEYStripe public keyCLOUDINARY_URLCloudinary upload URLGEMINI_API_KEYGoogle Gemini API keyMAILER_DSNMailjet or SMTP DSNOAUTH_GOOGLE_ID / _SECRETGoogle OAuth credentialsOAUTH_GITHUB_ID / _SECRETGitHub OAuth credentials

👥 Roles RoleAccessROLE_ADMINFull admin dashboard, all CRUD, user management, reportsROLE_USER (voyageur)Home page, trip browsing, booking, payments, profile

📁 Project Structure src/ ├── Controller/ # All controllers (Admin/, feature controllers) ├── Entity/ # Doctrine entities ├── Form/ # Symfony form types ├── Repository/ # Custom query repositories ├── Service/ # Business logic (Gemini, Stripe, Cloudinary, Mail…) ├── Security/ # Authenticators, OAuth, face auth └── Command/ # CLI commands (create admin, generate entities…)

templates/ ├── admin/ # Admin dashboard & sub-pages ├── voyage/ # Trip views ├── voiture/ # Car rental views ├── reservation/ # Booking views ├── dashboard/ # User dashboard └── … # One folder per module

👨‍💻 Team Developed by the NovaJourney team — Esprit School of Engineering, 3rd year, 2025–2026.

📄 License This project was built for academic purposes. All rights reserved © Esprit PIDEV 2025–2026.
