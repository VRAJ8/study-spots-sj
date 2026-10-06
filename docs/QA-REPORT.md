# QA & Accessibility Report: Study Spots SJ

**Scope:** 8 page types (home, explore, single spot, suggest form, contributor guide, accessibility statement, about, 404) in **day and night** themes, the WordPress admin for all three roles, and the static GitHub Pages build.
**Standard:** WCAG 2.2 Level AA.
**Date:** October 2026

---

## 1. Summary

| Check | Method | Result |
|---|---|---|
| Automated accessibility | axe-core 4.10.2 (WCAG 2.0/2.1/2.2 A + AA, best practices) | **0 violations** in 16 runs (8 pages × 2 themes) |
| Color contrast | 47 token pairs calculated with the WCAG relative-luminance formula | **47/47 pass** after fixes (6 failed initially) |
| Lighthouse | Live site (GitHub Pages), 4 page types, mobile (simulated 4G) + desktop | Accessibility **100**, Best Practices **100**, SEO **100**, Performance **94–100** (15 of 16 runs at 100) |
| Broken links | Crawler on the live site: 21 pages, 43 unique links/assets incl. external | **1 real broken link found and fixed** → 0 (sjsu.edu was down site-wide during the final run; the link was verified 200 earlier) |
| Responsive layout | 8 pages × 7 widths (320, 375, 390, 768, 1024, 1440, 1920 px) | **0 overflow issues** in 56 checks |
| PHP errors | Every template rendered, output scanned for notices/warnings/fatals | **0** |
| Browser console | Front end and editor | **0 errors** after fixes |
| Roles & workflow | Manual test as Admin, Spot Editor, Spot Scout | Pass (details in section 5) |

## 2. Accessibility: automated

**Harness validation.** Before trusting a clean result, I planted a low-contrast paragraph and an image without alt text. axe reported both (`color-contrast`, `image-alt`), with 49 rules passing. Elements axe couldn't measure because of layered backgrounds (25 "incomplete" contrast checks) were covered by the manual calculation in section 3.

| Page | Night | Day |
|---|---|---|
| `/` | 0 | 0 |
| `/spots/` | 0 | 0 |
| `/spots/dr-martin-luther-king-jr-library/` | 0 | 0 |
| `/suggest-a-spot/` | 0 | 0 |
| `/contributor-guide/` | 0 | 0 |
| `/accessibility/` | 0 | 0 |
| `/about/` | 0 | 0 |
| `/404` | 0 | 0 |

## 3. Color contrast

| Found | Ratio | Fix | After |
|---|---|---|---|
| Day-mode vibe tag text, hue 145 (green) | 4.24 | Darkened tag text lightness from 30% to 26% for all hues | 5.34 |
| Day-mode vibe tag text, hue 48 (amber) | 4.497 | Same | 5.63 |
| White text on gradient "Glow" button (orange) | 2.11 | Lighter gradient with dark ink text | 12.70 |
| White text on gradient button (pink) | 2.90 | Same | 9.18 |
| White text on gradient button (violet) | 4.36 | Same | 8.51 |
| "Details last verified" badge, day mode | 3.89 | `#0f8a5f` → `#0b6e4c` | 5.60 |

## 4. Manual accessibility checks

- [x] **Keyboard:** every control is reachable and operable. Filter chips are `<button aria-pressed>`, cards use one stretched link (no duplicate tab stops), and the focus ring is visible on every element (3px accent outline).
- [x] **Skip link** to main content (WordPress core, styled).
- [x] **Headings:** one `h1` per page, no skipped levels. The grid block's heading level is configurable so editors can keep the outline logical.
- [x] **Links:** descriptive text, with screen-reader-only context on repeated links ("Directions *to King Library (opens Google Maps)*").
- [x] **Non-color cues:** ratings shown as "4/5" text beside the bars; selected chips get a ✓.
- [x] **Live regions:** the filter result count is announced ("3 spots match.").
- [x] **Motion:** every animation and transition is disabled under `prefers-reduced-motion`. The rotating hero word is `aria-hidden`, with a stable screen-reader phrase.
- [x] **Map:** optional enhancement. Every pin is also a plain link in the list, markers have titles, and the caption says so.
- [x] **Forms:** visible labels, `fieldset`/`legend` for rating groups, required fields marked in text, errors linked to fields (`aria-invalid` + `aria-describedby`), focus moves to the first invalid field, a honeypot instead of a CAPTCHA.
- [x] **Tables:** caption, `<th scope>` for columns and rows.
- [ ] **VoiceOver + Safari pass:** to do manually (see section 7).

## 5. Roles & publishing workflow

| Test | Expected | Result |
|---|---|---|
| Public submits the form with empty fields | Inline errors, focus on first error, nothing lost | ✅ |
| Public submits a valid spot | "In the review queue" message; spot saved as **Pending** | ✅ |
| Admin dashboard | Review queue lists it; menu shows a pending-count badge | ✅ |
| Scout: Spots list | Only own spots; others' published spots read-only | ✅ |
| Scout: editor | Button reads **Submit for Review**, never Publish | ✅ |
| Scout: menu | No Pages, Appearance, Plugins, Users, Settings | ✅ |
| New spot | Opens with the locked template and the Spot details panel (12 fields) | ✅ |
| Content health | Flags Tower Lawn (verified Nov 2025) as stale; CSV export downloads | ✅ |

## 6. Bugs found & fixed during QA

| Bug | Cause | Fix |
|---|---|---|
| Any unknown URL 301-redirected to the homepage (no real 404s; broken links hidden) | Seed flushed rewrite rules while `$wp_rewrite` still held the old permalink structure | Set the structure on the live rewrite object before flushing; verified real 404s |
| Broken link: Student Union website | SJSU moved `/su/` → `/studentunion/` | Updated the URL, found by the link crawler |
| Map tiles showed "API KEY REQUIRED" | CARTO basemaps now need a key | Switched to OpenStreetMap tiles with a CSS filter for night mode |
| Hero background boxed to 1240px on wide screens | Constrained layout applies max-width to every child, including absolutely-positioned layers | Exempted background layers |
| Layout shift on static Suggest page (CLS 0.175) | Form swapped by JS after paint | Swap done at export time → CLS 0 |
| Console error: "Transition was aborted" | View Transitions reject when the tab is hidden | Rejection handled |
| Theme toggle announced conflicting state | Used both `aria-pressed` and a changing label | Kept the changing label only |
| Explore page mobile LCP 5.7s | Map tiles were the largest above-the-fold element | List first on phones; Leaflet self-hosted and lazy-loaded → LCP 3.0s, Performance 76 → 92 |

## 7. Still to check manually

Automated tools catch about a third of accessibility issues, so these are worth doing by hand before sharing widely:

- [ ] **Safari (macOS):** open the live site and check the home, explore (try the filters), a spot page, and day/night toggle.
- [ ] **Safari on iPhone:** same pages; check the mobile menu opens and closes.
- [ ] **VoiceOver** (Cmd+F5): tab through the explore filters and hear the result count announced.
- [ ] **Firefox:** not installed on the test machine.
- [ ] **Zoom to 200%:** text reflows with no horizontal scrolling.

## 8. Reports

Lighthouse HTML reports from the live site are in [`docs/reports/`](reports/).
