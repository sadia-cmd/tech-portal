# Tech Portal — Pakistan Technology News & Startup Media Portal

A professional, premium technology news and startup media portal focused on Pakistan's tech ecosystem.

## Tech Stack

- **CMS:** WordPress 7.1
- **Database:** MariaDB 10.11
- **Server:** Nginx + PHP 8.3 FPM
- **Theme:** techportal (custom)
- **Plugin:** portal-core (custom)

## Features

- IT News, Pakistan Technology, Startup Stories
- Founder Interviews, Startup Profiles
- Cybersecurity, AI & Cloud, Reviews
- Web Channel with live shows and video archive
- Membership, comments, bookmarks
- Newsletter, press releases
- Sponsor/advertising management
- SEO, analytics, secure CMS

## Development

```bash
# Access WordPress CLI
cd /root/workspace/tech-media-portal
wp --allow-root <command>

# Backup
./scripts/backup.sh

# Activate theme
wp theme activate techportal --allow-root

# Activate plugin
wp plugin activate portal-core --allow-root
```

## Project Structure

```
tech-media-portal/
├── .env                  # Environment variables (not committed)
├── .env.example          # Template
├── .gitignore
├── README.md
├── PROJECT_STATUS.md
├── docs/
│   └── DESIGN_SYSTEM.md
├── wp-content/
│   ├── themes/techportal/
│   └── plugins/portal-core/
├── scripts/
│   └── backup.sh
├── tests/
└── backups/
```

## Admin Access

- URL: https://techportal.24.jugaar.ai/wp-admin/

## License

GPL v2 or later
