# 🧳 Global Travel Web - B2B Business Travel Management Platform

## Overview
A Symfony 6.4-powered platform built for **Global Travel Web project**, enabling company managers to integrate **flights, hotels, activities, users and private cars** in one intelligent interface. Developed as part of the **PIDEV 3A coursework at [Esprit School of Engineering](https://esprit.tn)**, this solution simplifies corporate travel planning by offering a centralized platform that streamlines booking, tracking, and managing business trips.

## Features
- 🔐 **Secure Authentication** with role-based access (Admin, Employee, Manager)
- 🛫 **Flight Management** with real-time delay calculation
- 🏨 **Hotel Booking** with room availability and PDF export
- 🚗 **Private Car Services** with dynamic route mapping
- 📅 **Google Calendar Integration** for activity scheduling
- 📈 **Dashboards & Analytics** for trip tracking
- 🧠 **AI Integration** for delay prediction and smart feedback translation
- 🗂️ **Admin Panel** to manage users, companies, and services

## Tech Stack

### Frontend
- Tailwind CSS
- Twig

### Backend
- Symfony 6.4 (PHP 8.4.5)
- Doctrine ORM
- PostgreSQL

### AI & APIs
- Azure AI (Delay prediction)
- Lingva Translate API
- OpenStreetMap + Geocoding API
- Google Calendar API
- MailJet, Abstract API, hCaptcha

### Other Tools
- Docker
- GitHub Actions
- Composer, npm

## Directory Structure

```
/src
    └── Controller/
    └── Entity/
    └── Form/
    └── Repository/
    └── Security/
/templates
    └── [Module-specific HTML.twig files]
/public
/config
```

## Getting Started

```bash
# Clone the repository
git clone https://github.com/esprit-devs/business-travel-platform.git
cd business-travel-platform

# Install dependencies
composer install
npm install

# Set up the environment
cp .env.example .env
# Configure your DB credentials

# Run the app locally
symfony server:start

# Run tailwind build command 
php bin/console tailwind:build --watch
```

## Acknowledgments

This project was completed under the guidance of the **Esprit School of Engineering**, as part of the PIDEV 3A engineering curriculum.
## Contributors

This project was developed as part of the PIDEV 3A coursework at Esprit School of Engineering by:

- [Aziz Amri](https://github.com/AzizX25) - Flight Management & AI Integration
- [Jassem Lazaar](https://github.com/jassem0072) - Hotel Booking System
- [Rayen Neji](https://github.com/RayenNeji) - User Authentication & Admin Panel
- [Amir Ben Ali](https://github.com/amir-benali) - Private Car Services & Maps
- [Mohamed Said Hachani](https://github.com/Hachani-mohamedsaid) - Activities & Calendar Integration

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request
## Topics Used 
```
symfony
tailwindcss
business-travel
b2b-platform
ai-prediction
web-development
esprit-school-of-engineering
```
