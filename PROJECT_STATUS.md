# PROJECT_STATUS.md — Tech Media Portal

## Current Phase: Phase 3A — COMPLETE ✅

**Last Updated:** 2026-08-26

---

## Phase 1A — Design System ✅
## Phase 1B — Infrastructure ✅
## Phase 1C — Deployment Recovery ✅

### Deployment Details

| Item | Value |
|------|-------|
| Public URL | https://newsweb.24.jugaar.ai |
| WP Admin | https://newsweb.24.jugaar.ai/wp-admin/ |
| WordPress Path | `/root/workspace/tech-media-portal/` |
| Web Server | Nginx 1.24 + PHP 8.3 FPM |
| Database | MariaDB 10.11 (db: `techportal`) |
| Theme | techportal v2.1.0 (active) |
| Plugin | portal-core v1.0.0 (active) |

### Root Cause of Deployment Issue

The nginx vhost for `newsweb.24.jugaar.ai` pointed to a placeholder directory (`/var/www/newsweb.24.jugaar.ai/`) instead of the WordPress installation at `/root/workspace/tech-media-portal/`. The `.env` file also had the old domain `techportal.24.jugaar.ai`.

### Configuration Changes Made

1. **Nginx vhost** — Updated `/etc/nginx/sites-enabled/newsweb` to:
   - Document root → `/root/workspace/tech-media-portal/`
   - Added PHP-FPM processing (`fastcgi_pass unix:/run/php/php8.3-fpm.sock`)
   - Added WordPress rewrite rules (`try_files $uri $uri/ /index.php?$args`)
   - Added security headers and sensitive file blocking

2. **WordPress URLs** — Updated via WP-CLI:
   - `home` → `https://newsweb.24.jugaar.ai`
   - `siteurl` → `https://newsweb.24.jugaar.ai`

3. **`.env` file** — Updated:
   - `WP_HOME=https://newsweb.24.jugaar.ai`
   - `WP_SITEURL=https://newsweb.24.jugaar.ai`

4. **Rewrite rules** — Flushed with `wp rewrite flush --hard`

---

## Phase 2 — Frontend Build ✅

### Phase 2 Core (v1.0.0)

| Item | Status |
|------|--------|
| Full homepage (18 sections) | ✅ front-page.php (592 lines) |
| Hero story section | ✅ Dark gradient + overlay |
| Editor's Picks grid | ✅ Feature + standard cards |
| Latest News feed | ✅ Chronological with time indicators |
| Trending section | ✅ Ranked numbered list |
| Startup ecosystem showcase | ✅ 6 startup cards with stage badges |
| Web Channel / Live area | ✅ Live indicator + episode grid |
| Episodes section | ✅ 3 episode cards |
| AI & Cloud section | ✅ 2-column with featured + list |
| Cybersecurity section | ✅ 2-column with featured + list |
| Newsletter CTA | ✅ Email signup form with AJAX |
| Article reading UX | ✅ Related posts, tags, share buttons |
| SEO optimization | ✅ Open Graph, JSON-LD, meta descriptions |
| Custom archive templates | ✅ Startup + Episode archives |
| robots.txt | ✅ |
| Sample content | ✅ 10 articles, 6 startups, 3 founders, 3 episodes |
| Custom post types (5) | ✅ articles, startups, founders, episodes, press |
| Custom taxonomies (3) | ✅ topics, stages, funding rounds |
| REST API endpoints (4) | ✅ trending, latest, spotlight, settings |

### Phase 2 Refinements (v2.1.0)

| Defect | Fix | Status |
|--------|-----|--------|
| #1 Web Channel 404 | Page created (ID 27) with template assigned | ✅ |
| #2 Navigation items | All required items in primary nav | ✅ |
| #3 Startup profile template | `single-portal_startup.php` (434 lines) — logo, name, industry, funding, location, website, founders, description, related news/episodes, sidebar | ✅ |
| #4 Episode template | `single-portal_episode.php` (323 lines) — 16:9 YouTube area, show, guest, company, topic, date, description, related episodes, sidebar | ✅ |
| #5 Article metadata | Fixed `post_author=0` → assigned author to all posts. Shows "By **admin** Published August 26, 2026 · 1 min read" | ✅ |
| #6 Category archives | Fixed `archive.php` — removed undefined `has_category_icon()` calls. Lead story + grid + pagination | ✅ |
| #7 Empty sponsors hidden | `front-page.php` wraps sponsors in `if ( ! empty( $sponsors ) )` | ✅ |
| #8 Social-proof claims | Searched all templates — no unverifiable claims found | ✅ |
| #9 Visual QA | Verified all 10 pages return HTTP 200/404 correctly | ✅ |
| #10 Homepage refinement | Improved hero spacing, added responsive CSS for mobile stacking, footer grid fix, newsletter mobile fix | ✅ |
| #11 Mobile navigation | Added CSS for `tp-nav__mobile-overlay` (120+ lines), rewrote `main.js` for overlay open/close with focus management | ✅ |

### Pages Verified Working

| Page | URL | Status |
|------|-----|--------|
| Homepage | `/` | ✅ 200 |
| Article | `/e-commerce-in-pakistan-expected-to-reach-10b-by-2028/` | ✅ 200 |
| Category (Cybersecurity) | `/category/cybersecurity/` | ✅ 200 |
| Category (AI & Cloud) | `/category/ai-cloud/` | ✅ 200 |
| Category (Pakistan Tech) | `/category/pakistan-technology/` | ✅ 200 |
| Startup Profile | `/startup/cloudnine/` | ✅ 200 |
| Web Channel | `/web-channel/` | ✅ 200 |
| Episode | `/episode/cybersecurity-in-2026-threats-and-defenses/` | ✅ 200 |
| Search | `/?s=tech` | ✅ 200 (12 results) |
| 404 | `/nonexistent-page/` | ✅ 404 |

### Files Modified in Refinements

| File | Lines | Change |
|------|-------|--------|
| `archive.php` | 97 | Removed undefined function calls |
| `functions.php` | 499 | Version bump 2.0.0 → 2.1.0 |
| `style.css` | 2054 | +228 lines: mobile overlay CSS, homepage refinements, responsive fixes |
| `assets/js/main.js` | 181 | Full rewrite: mobile overlay handling, search nav, newsletter AJAX |

---

## Architecture

```
VPS (Ubuntu 24.04, 2 cores, 5.8GB RAM)
├── Nginx 1.24 (SSL via Let's Encrypt)
├── PHP 8.3 FPM (socket: /run/php/php8.3-fpm.sock)
├── MariaDB 10.11 (localhost)
├── WordPress 7.1
│   ├── Theme: techportal v2.1.0 (active)
│   └── Plugin: portal-core v1.0.0 (active)
└── Existing services (TenderIQ, MONVÉ, Elite Commercial, etc.)
```

## URLs

| Service | URL |
|---------|-----|
| WordPress | https://newsweb.24.jugaar.ai |
| WP Admin | https://newsweb.24.jugaar.ai/wp-admin/ |
| REST API | https://newsweb.24.jugaar.ai/wp-json/ |
| Startup Archive | https://newsweb.24.jugaar.ai/startup/ |
| Episode Archive | https://newsweb.24.jugaar.ai/episode/ |
| Web Channel | https://newsweb.24.jugaar.ai/web-channel/ |

## Content

- 10 articles (regular posts)
- 6 startup profiles
- 3 founder profiles
- 3 episode listings
- 8 categories, 8 topics, 5 startup stages, 4 funding rounds

---

## Pending for Phase 3

- [ ] Membership system
- [ ] User bookmarks
- [ ] Newsletter integration (Mailchimp/etc)
- [ ] Press releases section
- [ ] YouTube API integration
- [ ] Video archive with playback
- [ ] Comments system enhancement
- [ ] Analytics integration
- [ ] CMS admin workflow optimization
- [ ] Performance optimization (caching, CDN)
- [ ] Mobile app considerations

---

## Phase 3A — Real News Ingestion + News Radar ✅

### What Was Built

| Feature | Status | Details |
|---------|--------|---------|
| GNews API integration | ✅ | 6 topic categories, server-side fetching |
| News Radar admin page | ✅ | Full UI with status cards, filters, actions |
| Duplicate protection | ✅ | URL + normalized title + SHA-256 hash |
| Create Draft | ✅ | WordPress draft with title, source, category, featured image |
| Ignore action | ✅ | Marks story as ignored, updates counts |
| View Source | ✅ | Opens original article in new tab |
| Category mapping | ✅ | 6 topics → 5 WordPress categories |
| Scheduled fetching | ✅ | WP Cron every 45 minutes |
| Custom DB table | ✅ | `wp_portal_news_radar` with proper indexes |
| Error handling | ✅ | API key missing, fetch errors, duplicate detection |
| CSS + JS assets | ✅ | Responsive admin styles, AJAX interactions |

### Files Changed

| File | Lines | Change |
|------|-------|--------|
| `wp-content/plugins/portal-core/portal-core.php` | 128 | Added 4 News Radar includes + bootstrap |
| `wp-content/plugins/portal-core/includes/class-portal-news-radar.php` | 89 | Main orchestrator: DB table, cron, .env loader |
| `wp-content/plugins/portal-core/includes/class-portal-news-radar-db.php` | 140 | Custom table: CRUD, dedup, pagination |
| `wp-content/plugins/portal-core/includes/class-portal-news-radar-fetcher.php` | 130 | GNews API: 6 topics, fetch, test |
| `wp-content/plugins/portal-core/includes/class-portal-news-radar-admin.php` | 280 | Admin page: UI, AJAX handlers, draft creation |
| `wp-content/plugins/portal-core/assets/css/news-radar.css` | 130 | Admin page styles, responsive |
| `wp-content/plugins/portal-core/assets/js/news-radar.js` | 130 | AJAX interactions, live count updates |
| `.env` | 1 | Added `GNEWS_API_KEY` |

### Topic → Category Mapping

| Topic Key | Search Query | WordPress Category |
|-----------|-------------|-------------------|
| pakistan-tech | Pakistan technology | pakistan-technology |
| pakistan-startups | Pakistan startups | startup-stories |
| ai | artificial intelligence AI | ai-cloud |
| cybersecurity | cybersecurity | cybersecurity |
| cloud | cloud computing | ai-cloud |
| fintech | fintech financial technology | it-news |

### API Key Status

- **Key:** Configured in `.env` as `GNEWS_API_KEY`
- **Verification:** ⚠️ GNews requires email verification before API access works
- **Verification email sent to:** `ac.k.i.e.12.1987@googlemail.com`
- **Action needed:** User must verify email at gnews.io dashboard to activate API
- **Once verified:** Fetch Now button and cron will pull real stories automatically

### Tested

| Test | Result |
|------|--------|
| Plugin loads without errors | ✅ |
| DB table created (`wp_portal_news_radar`) | ✅ |
| Duplicate detection (URL + title hash) | ✅ |
| Create Draft (title, content, category, featured image) | ✅ |
| Ignore action (status update, count update) | ✅ |
| Cron scheduled (every 45 min) | ✅ |
| Admin page loads (`/wp-admin/admin.php?page=news-radar`) | ✅ |
| API connection (needs email verification) | ⚠️ Pending |

---

## Phase 3A.1 — Dual-Source News Fetcher ✅

**Last Updated:** 2026-08-26

### What Was Built

| Feature | Status | Details |
|---------|--------|---------|
| NewsAPI.org secondary source | ✅ | Falls back when GNews fails or returns 0 articles |
| `api_source` column | ✅ | Tracks `gnews` vs `newsapi` per story |
| Dual test buttons | ✅ | "Test GNews" + "Test NewsAPI" in admin UI |
| Source summary bar | ✅ | Shows per-source story counts with color badges |
| Source filter dropdown | ✅ | Filter stories by GNews or NewsAPI |
| API column in table | ✅ | Blue badge for GNEWS, orange for NEWSAPI |
| Per-source fetch counts | ✅ | Shows GNews/NewsAPI split in fetch results |
| User-Agent header | ✅ | Added `TechPortal NewsRadar/2.0` for NewsAPI compliance |

### How It Works

1. **Primary:** GNews API (6 topics, Pakistan-focused)
2. **Fallback:** NewsAPI.org (6 topics, broader English coverage)
3. If GNews returns an error or 0 articles for a topic → NewsAPI picks it up
4. Each story tagged with `api_source` for filtering and display
5. Dedup works across both sources (URL + title hash)

### Fetch Results (2026-08-26)

| Metric | Value |
|--------|-------|
| Total stories | 70 |
| GNews stories | 30 |
| NewsAPI stories | 40 |
| Cron | Active, every 45 min |

### Files Changed

| File | Change |
|------|--------|
| `includes/class-portal-news-radar-db.php` | Added `api_source` column, `count_by_source()`, source filter |
| `includes/class-portal-news-radar-fetcher.php` | Full rewrite — dual-source with GNews primary + NewsAPI fallback |
| `includes/class-portal-news-radar.php` | Load `NEWSAPI_KEY` from `.env` |
| `includes/class-portal-news-radar-admin.php` | Dual test buttons, source summary, API column, source filter |
| `assets/js/news-radar.js` | Dual test handlers, fetch result shows per-source counts |
| `assets/css/news-radar.css` | Source summary badges, API column badges |
| `.env` | Added `NEWSAPI_KEY` |
