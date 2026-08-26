# Tech Portal — Design System

## Brand Identity

**Project:** Pakistan Technology News & Startup Media Portal
**Character:** Premium · Editorial · Technology-Focused · Authoritative · Modern · Fast · Content-Dense · Highly Readable

---

## Brand Colors

| Token | Hex | Usage |
|-------|-----|-------|
| `--tp-purple` | `#37215F` | Primary brand accent |
| `--tp-blue` | `#0881BE` | Primary interactive accent |
| `--tp-ink` | `#101114` | Deep black — backgrounds, footer |
| `--tp-soft-black` | `#18191D` | Dark backgrounds |
| `--tp-off-white` | `#F7F7F8` | Light section backgrounds |
| `--tp-white` | `#FFFFFF` | Card backgrounds, body |
| `--tp-border` | `#E3E5E8` | Dividers, borders |
| `--tp-muted` | `#6A6E76` | Secondary text, metadata |

**Usage rules:**
- Blue and purple are brand accents — used intentionally, not decoratively
- No excessive gradients
- Purple for branding, Blue for interactive elements

---

## Typography

**Font family:** Inter (system fallbacks: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto)

**Import:** Google Fonts — `Inter:wght@400;500;600;700;800`

### Editorial Hierarchy

| Level | Desktop | Mobile | Weight | Line Height |
|-------|---------|--------|--------|-------------|
| H1 (Major Feature) | clamp(2.75rem, 3.75vw, 4rem) | clamp(2.25rem, 3.5vw, 3.5rem) | 800 | 1.2 |
| H2 | clamp(2.25rem, 2.5vw, 3.5rem) | clamp(1.875rem, 3vw, 2.5rem) | 700 | 1.2 |
| H3 | clamp(1.875rem, 1.63vw, 2.5rem) | clamp(1.5rem, 2.5vw, 2rem) | 700 | 1.25 |
| H4 | clamp(1.5rem, 1.12vw, 2rem) | clamp(1.25rem, 2vw, 1.5rem) | 700 | 1.3 |
| Article Body | clamp(0.9375rem, 0.24vw, 1.0625rem) | clamp(0.9375rem, 0.24vw, 1.0625rem) | 400 | 1.65 |
| Metadata | clamp(0.694rem, 0.17vw, 0.75rem) | clamp(0.694rem, 0.17vw, 0.75rem) | 500–600 | 1.5 |

**Rules:**
- Prioritize readability
- Tight tracking on headlines (-0.01em)
- Wide tracking on uppercase labels (0.08em)
- Article body: 17–18px, line-height 1.6–1.7

---

## Spacing Scale (4px base)

| Token | Value |
|-------|-------|
| `--tp-space-1` | 4px |
| `--tp-space-2` | 8px |
| `--tp-space-3` | 12px |
| `--tp-space-4` | 16px |
| `--tp-space-5` | 20px |
| `--tp-space-6` | 24px |
| `--tp-space-8` | 32px |
| `--tp-space-10` | 40px |
| `--tp-space-12` | 48px |
| `--tp-space-16` | 64px |
| `--tp-space-20` | 80px |
| `--tp-space-24` | 96px |

---

## Grid System

| Breakpoint | Columns | Max Width | Gutter |
|------------|---------|-----------|--------|
| Desktop (>1024px) | 12 | 1400px | 24px |
| Tablet (641–1024px) | 8 | 100% | 24px |
| Mobile (≤640px) | 4 | 100% | 16px |

**Container widths:**
- Standard: `--tp-container-max: 1400px`
- Narrow (articles): `--tp-container-narrow: 720px`
- Wide: `--tp-container-wide: 1600px`

---

## Breakpoints

| Name | Width |
|------|-------|
| `sm` | 640px |
| `md` | 768px |
| `lg` | 1024px |
| `xl` | 1280px |
| `2xl` | 1440px |

---

## Component Philosophy

**Varied editorial composition.** Not every article uses the same card. Visual hierarchy communicates importance.

### Card Types

| Component | Purpose | Grid Span (Desktop) |
|-----------|---------|-------------------|
| `hero-story` | Lead story, maximum visual weight | 8 cols |
| `feature-story` | Important secondary stories | 4 cols |
| `standard-story-card` | Regular news articles | 4 cols |
| `horizontal-story` | Mid-priority with thumbnail | 12 cols (row) |
| `compact-story` | Side items, quick reads | row layout |
| `latest-news-item` | Chronological feed items | 12 cols (row) |
| `trending-item` | Ranked trending stories | row layout |
| `startup-card` | Startup profile cards | 4 cols |
| `founder-card` | Founder profile cards | 3 cols |
| `episode-card` | Video episode cards | 4 cols |
| `live-card` | Live show indicator | 8 cols |
| `section-header` | Section dividers | 12 cols |
| `category-label` | Topic/category badges | inline |
| `breaking-ticker` | Breaking news bar | full width |
| `sponsor-slot` | Advertisement placement | variable |
| `newsletter-block` | Email signup CTA | full width |

---

## Editorial Hierarchy

| Level | Purpose | Visual Weight |
|-------|---------|--------------|
| LEVEL 1 | Major feature | Hero card, largest headline |
| LEVEL 2 | Important stories | Feature cards, large headline |
| LEVEL 3 | Standard news | Standard cards, medium headline |
| LEVEL 4 | Rapid/latest feed | Compact items, small headline |

**User should understand what matters within seconds.**

---

## Navigation Behavior

### Desktop
- Sticky header with utility bar, masthead, category nav
- Category nav: horizontal list, underline hover animation
- Search + Sign In buttons in masthead
- Breaking news ticker below header (when active)

### Mobile
- Compact masthead with logo + hamburger
- Full-screen navigation drawer on toggle
- Touch-friendly 44px minimum targets
- Escape key closes drawer

---

## Homepage Composition (18 sections)

1. Utility header (date, quick links)
2. Primary masthead (logo, search, sign-in)
3. Category navigation
4. Breaking news ticker
5. Major hero story (8-col)
6. Curated top stories (feature cards)
7. Latest news feed (chronological)
8. Trending stories
9. Startup ecosystem showcase
10. Web Channel / Live area
11. Latest episodes
12. AI & Cloud section
13. Cybersecurity section
14. Sponsor inventory
15. Newsletter CTA
16. Footer

---

## Article Reading UX

- Narrow column: max 720px centered
- Clean typography, generous line-height (1.65)
- Large featured image with rounded corners
- Category label + title + meta (author, date, reading time)
- Blockquote: left blue border, italic
- Figure captions centered, muted text
- Tags section at bottom

---

## Mobile Rules

- Intentionally designed — not just stacked desktop
- Compact masthead
- Full-screen nav drawer
- Touch-friendly controls (44px minimum)
- Large readable headlines
- Chronological news feed
- Responsive 16:9 video
- Clean cards, minimal layout shifts
- No horizontal overflow

---

## Accessibility

- Semantic HTML5 throughout
- Keyboard navigation with visible focus states
- Sufficient color contrast (WCAG AA)
- Alt text on all images
- ARIA only where appropriate
- Reduced motion: all animations respect `prefers-reduced-motion`
- Readable line lengths (max 720px for articles)
- Skip-to-content link

---

## Motion

### Allowed
- Small image zoom on hover (scale 1.03)
- Headline color transition on hover
- Navigation underline animation (scaleX)
- Button movement (translateY -1px)
- Ticker scroll animation
- Live indicator pulse
- Card lift on hover (translateY -2px + shadow)

### Prohibited
- Large parallax effects
- 3D transforms
- Continuous decorative animation
- Heavy JavaScript effects

---

## Shadows

| Token | Usage |
|-------|-------|
| `--tp-shadow-xs` | Subtle depth |
| `--tp-shadow-sm` | Cards at rest |
| `--tp-shadow-md` | Cards on hover, sticky header |
| `--tp-shadow-lg` | Elevated cards |
| `--tp-shadow-xl` | Modals, dropdowns |

---

## Border Radius

| Token | Value | Usage |
|-------|-------|-------|
| `--tp-radius-sm` | 3px | Labels, small elements |
| `--tp-radius-md` | 6px | Cards, buttons, inputs |
| `--tp-radius-lg` | 10px | Large cards, newsletter block |
| `--tp-radius-xl` | 16px | Featured images |
| `--tp-radius-full` | 9999px | Avatars, pills |
