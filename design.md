# UI/UX Design Specification (`design.md`)
**Project Name:** STTNI (Sekolah Tinggi Teologi Nazarene Indonesia) Web Portal  
**Scope:** Visual System, Design Tokens, Typography, Layout & UI Components  
**Target Platform:** Responsive Web (Desktop, Tablet, Mobile)  
**Version:** 1.0.0  

---

## 1. Design Tokens & Color Palette

The visual identity combines academic seriousness, spiritual holiness, and modern accessibility.

### 1.1 Core Color Tokens

| Token Name | Hex Code | Usage / Context |
| :--- | :--- | :--- |
| `--color-primary-navy` | `#1A2B4C` | Navigation headers, primary buttons, structural branding |
| `--color-primary-dark` | `#0F1A30` | Hover states for primary actions, dark accent panels |
| `--color-accent-burgundy`| `#8B0000` | CTA buttons, notification badges, active menu indicators |
| `--color-accent-gold` | `#D4AF37` | Highlights, subtle borders, decorative icons, badges |
| `--color-bg-light` | `#F8FAFC` | Main application background (Clean slate tone) |
| `--color-surface` | `#FFFFFF` | Cards, containers, modals, input fields |
| `--color-text-primary` | `#1E293B` | High contrast body text, headings |
| `--color-text-secondary`| `#64748B` | Subtitles, metadata, timestamps, secondary labels |
| `--color-border-subtle` | `#E2E8F0` | Card borders, table grid lines, dividers |

### 1.2 Color Palette Visual Rules
```css
:root {
  /* Color Variables */
  --color-primary-navy: #1A2B4C;
  --color-primary-dark: #0F1A30;
  --color-accent-burgundy: #8B0000;
  --color-accent-gold: #D4AF37;
  --color-bg-light: #F8FAFC;
  --color-surface: #FFFFFF;
  --color-text-primary: #1E293B;
  --color-text-secondary: #64748B;
  --color-border-subtle: #E2E8F0;

  /* Elevation Shadows */
  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
```

---

## 2. Typography System

### 2.1 Font Families
- **Primary Body & UI Font:** `Inter`, `-apple-system`, `BlinkMacSystemFont`, `sans-serif`  
  *Usage:* Buttons, navigation items, body text, form controls, data tables.
- **Secondary Display / Heading Font:** `Merriweather`, `Georgia`, `serif`  
  *Usage:* Hero titles, section headings (`h1`, `h2`), motto quotes, academic titles.

### 2.2 Scale & Hierarchy

| Element | Size | Line Height | Weight | Usage |
| :--- | :--- | :--- | :--- | :--- |
| **Hero Title (`h1`)** | `2.5rem` (40px) | `1.2` | 700 (Bold) | Main Hero Banner Heading |
| **Section Title (`h2`)** | `1.875rem` (30px) | `1.3` | 700 (Bold) | Major Page Sections |
| **Subsection (`h3`)** | `1.25rem` (20px) | `1.4` | 600 (SemiBold) | Card Titles, Subheaders |
| **Body Lead** | `1.125rem` (18px) | `1.6` | 400 (Regular) | Intro paragraphs, feature descriptions |
| **Body Standard** | `1.00rem` (16px) | `1.5` | 400 (Regular) | General paragraph text |
| **Caption / Small** | `0.875rem` (14px) | `1.4` | 500 (Medium) | Meta info, badges, timestamps, footer |

---

## 3. Grid, Layout & Spacing System

### 3.1 Layout Grid Structure
- **Max Container Width:** `1280px` (Centered with automatic horizontal margins)
- **Grid System:** 12-Column Responsive CSS Grid / Flexbox
- **Gutter Width:** `24px` (Desktop), `16px` (Mobile)

```
Breakpoint Breakdown:
- Mobile:   < 640px    (1-column layout)
- Tablet:   640px - 1023px (2 to 3-column layout)
- Desktop:  >= 1024px  (Multi-column 12-grid layout)
```

### 3.2 Spacing System (8pt Grid)
- `space-1`: `4px`
- `space-2`: `8px`
- `space-3`: `16px`
- `space-4`: `24px`
- `space-5`: `32px`
- `space-6`: `48px`
- `space-7`: `64px`

---

## 4. Component UI Specifications

### 4.1 Navigation Bar & Header Layout
```
+---------------------------------------------------------------------------------+
| TOP BAR: [Email: info@sttni.ac.id] | [Telp: (0274) 497045]  [eCampuz] [Jurnal]  |
+---------------------------------------------------------------------------------+
| LOGO (STTNI)   Home   Tentang STTNI   Program Studi   Layanan   [DAFTAR PMB]    |
+---------------------------------------------------------------------------------+
```
- **Top Utility Bar:** `--color-primary-dark` background, `--color-surface` text at `14px`.
- **Main Header:** `--color-surface` background, fixed/sticky top with subtle shadow (`--shadow-sm`).
- **Active Navigation Link:** `--color-accent-burgundy` font color with a `2px` bottom border in `--color-accent-gold`.

### 4.2 Buttons & CTA Variants
- **Primary Action (e.g., "Daftar PMB"):**
  - Background: `--color-accent-burgundy` (`#8B0000`)
  - Text: `#FFFFFF`, Bold `16px`
  - Border Radius: `8px`
  - Padding: `12px 24px`
  - Hover Effect: Brightness shift or slightly darker burgundy (`#6B0000`) with `--shadow-md`.
- **Secondary Action (e.g., "Lihat Program"):**
  - Background: Transparent
  - Border: `2px solid --color-primary-navy` (`#1A2B4C`)
  - Text: `--color-primary-navy`
  - Hover Effect: Background fills with `--color-primary-navy` and text becomes white.

### 4.3 Content Cards (4C Philosophy & News Cards)
```
+---------------------------------------------------+
| [ Image Thumbnail / Icon Badge ]                  |
| ------------------------------------------------- |
| Category Badge (e.g., "Giat Kampus")             |
| Title: "Mission Trip Tim Korea Ke Kampus STTNI"   |
| Date: April 8, 2026                               |
| Snippet: Pada 26-27 Januari 2026...               |
|                                                   |
| [ Read More -> ]                                  |
+---------------------------------------------------+
```
- **Card Container:** Background `--color-surface`, border `1px solid --color-border-subtle`, border radius `12px`, padding `20px`.
- **Hover State:** Translate Y `-4px` with transition `0.2s ease` and `--shadow-lg`.

---

## 5. UI Layout Structure (Landing Page Wireframe)

1. **Header Zone:** Sticky Navigation Header + Top Utility Bar.
2. **Hero Banner Section:** Full-width image slider with dark gradient overlay (`rgba(15, 26, 48, 0.65)`), centered title in serif font (`Merriweather`), and dual CTA buttons.
3. **4C Philosophy Grid:** 4-column card layout displaying *CONTENT*, *COMPETENCY*, *CHARACTER*, and *CONTEXT* with gold icon accents.
4. **Academic Programs Grid:** 3-card layout (S1 Teologi, S1 PAK, S2 Magister) featuring program duration badges and key learning focus.
5. **Giat Kampus & News Section:** 3-column news grid with pagination controls.
6. **Footer Zone:** Dark background (`--color-primary-dark`), 4-column layout containing Address/Contact info, Quick Links, Social Icons, and Copyright statement.
