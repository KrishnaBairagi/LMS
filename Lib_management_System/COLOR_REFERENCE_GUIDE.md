# 🎨 Dark Theme Visual Reference Guide

## Color Palette Reference

### Primary Colors
```
┌─────────────────┬──────────┬─────────────────────┐
│ Name            │ Hex Code │ Usage               │
├─────────────────┼──────────┼─────────────────────┤
│ Primary Blue    │ #6c84ff  │ Main buttons, links  │
│ Primary Dark    │ #4969ff  │ Hover states        │
│ Secondary Gray  │ #a0a8c0  │ Secondary text      │
└─────────────────┴──────────┴─────────────────────┘
```

### Background Colors
```
┌──────────────────┬──────────┬────────────────────────────┐
│ Name             │ Hex Code │ Usage                      │
├──────────────────┼──────────┼────────────────────────────┤
│ Dark (Primary)   │ #0f1419  │ Page background            │
│ Dark BG          │ #1a1f2e  │ Secondary backgrounds      │
│ Dark Card        │ #252d3d  │ Cards, containers          │
│ Dark Border      │ #3a4555  │ Borders, dividers          │
└──────────────────┴──────────┴────────────────────────────┘
```

### Text Colors
```
┌──────────────────┬──────────┬────────────────────────────┐
│ Name             │ Hex Code │ Usage                      │
├──────────────────┼──────────┼────────────────────────────┤
│ Text Primary     │ #f0f0f0  │ Main text, headings        │
│ Text Secondary   │ #b0b0b0  │ Labels, descriptions       │
└──────────────────┴──────────┴────────────────────────────┘
```

### Status Colors
```
┌──────────────┬──────────┬─────────────────────────┐
│ Status       │ Hex Code │ Usage                   │
├──────────────┼──────────┼─────────────────────────┤
│ Success      │ #2dd4bf  │ Positive actions        │
│ Info         │ #06b6d4  │ Information messages    │
│ Warning      │ #f59e0b  │ Warning alerts          │
│ Danger       │ #ff5860  │ Errors, delete actions  │
└──────────────┴──────────┴─────────────────────────┘
```

## Component Appearance

### Sidebar Navigation
```
┌─────────────────────────────────┐
│  📚 Library Pro                 │
│                                 │ ← Dark Gradient (#0f1419 to #1a1f2e)
│  🏠 Dashboard                   │ ← Light text (#f0f0f0)
│  📚 Books                       │
│  ➡️  Issue Book                 │
│  📋 Issued Books                │
│  📊 Analytics                   │
│  👥 Users                       │
│  ─────────────────────         │
│  🚪 Logout                      │
└─────────────────────────────────┘
```

### Statistics Cards
```
┌──────────────────────────┐
│  📚 Total Books          │ ← Gradient: #6c84ff → #4969ff
│  125                      │ ← White text on gradient
│  Books in library        │ ← Secondary text
└──────────────────────────┘
```

### Card Components
```
┌────────────────────────────────┐
│ ◆ Card Header (Gradient)      │ ← Primary blue gradient background
│                                │
│ Card Content                   │ ← Dark card background (#252d3d)
│ • Text in light color         │ ← #f0f0f0
│ • Labels in secondary color   │ ← #b0b0b0
│                                │
└────────────────────────────────┘
```

### Form Input Fields
```
┌─────────────────────────────────┐ ┌─────────────────────────────────┐
│ Email Address (Label)           │ │ Email Address (Label)           │
│ ┌───────────────────────────────┤ │ ┌───────────────────────────────┤
│ │ Enter your email...           │ │ │ Enter your email...    │ Focused
│ └───────────────────────────────┤ │ └───────────────────────────────┤
│                                   │
│ Dark input: #1a1f2e             │ │ Focused: #252d3d + #6c84ff border
│ Border: #3a4555                 │ │ Glow: rgba(108, 132, 255, 0.25)
└─────────────────────────────────┘ └─────────────────────────────────┘
```

### Buttons
```
┌────────────────┐ ┌────────────────┐ ┌────────────────┐
│  ✓ PRIMARY    │ │  ⚠ WARNING    │ │  ✕ DANGER     │
│  Link Button   │ │  Hover State   │ │  Delete Action │
└────────────────┘ └────────────────┘ └────────────────┘

Normal State:    Gradient (#6c84ff → #4969ff)
Hover State:     Darker gradient + shadow + lift effect
```

### Book Cards
```
┌────────────────────────┐
│ ┌──────────────────┐   │
│ │                  │   │ ← Book Cover Image
│ │  📖 (or image)   │   │    or default emoji
│ │                  │   │
│ └──────────────────┘   │
│                        │
│ The Great Gatsby       │ ← Title (#f0f0f0)
│ by F. Scott Fitzgerald │ ← Author (#b0b0b0)
│ ◆ Fiction            │ ← Category badge
│                        │
│ Qty: 5 | Avail: 5     │ ← Quantity info
│                        │
│ [Issue] [Delete]      │ ← Action buttons
│                        │
└ Card Background: #252d3d ┘
  Border: #3a4555
  On Hover: Lifted effect + glow
```

### Tables
```
┌────────────────┬────────────────┬────────────────┐
│ TITLE          │ AUTHOR         │ CATEGORY       │ ← Header: Gradient
├────────────────┼────────────────┼────────────────┤
│ The Great...   │ F. Scott...    │ Fiction        │ ← Row: #252d3d
│ To Kill a...   │ Harper Lee     │ Fiction        │ ← Text: #f0f0f0
│ 1984           │ George Orwell  │ Dystopian      │ ← On hover: Glow
└────────────────┴────────────────┴────────────────┘
  Border: #3a4555
```

### Alerts & Messages
```
┌────────────────────────────────────┐
│ ✓ Success Message                  │ ← Success alert (green border)
│ Changes saved successfully         │    Background: rgba(45, 212, 191, 0.1)
└────────────────────────────────────┘

┌────────────────────────────────────┐
│ ⚠ Warning Message                 │ ← Warning alert (amber border)
│ Please review your input           │    Background: rgba(245, 158, 11, 0.1)
└────────────────────────────────────┘

┌────────────────────────────────────┐
│ ! Error Message                    │ ← Error alert (red border)
│ Something went wrong               │    Background: rgba(255, 88, 96, 0.1)
└────────────────────────────────────┘
```

## Page Layout Reference

### Dashboard
```
┌─────────────────────────────────────────────────────────────┐
│ 📊 Dashboard                                                 │
│ Welcome back! Here's your library overview.                 │
│                                                              │
│ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐           │
│ │ 📚 Books    │ │ 👥 Users    │ │ 📤 Issued   │           │
│ │ 125         │ │ 8           │ │ 45          │           │
│ └─────────────┘ └─────────────┘ └─────────────┘           │
│                                                              │
│ ⚡ Quick Actions                                            │
│ [Manage Books] [Issue Book] [View Issued] [Manage Users]   │
│                                                              │
│ 📖 Recently Added Books                                     │
│ ┌─────────────────┬──────────────┬──────────────┐          │
│ │ Title           │ Author       │ Available    │          │
│ ├─────────────────┼──────────────┼──────────────┤          │
│ │ ...             │ ...          │ 5/5          │          │
│ └─────────────────┴──────────────┴──────────────┘          │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### Books Page
```
┌─────────────────────────────────────────────────────────────┐
│ 📚 Manage Books                    [+ Add New Book]         │
│                                                              │
│ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐       │
│ │          │ │          │ │          │ │          │       │
│ │ 📖 Image │ │ 📖 Image │ │ 📖 Image │ │ 📖 Image │       │
│ │          │ │          │ │          │ │          │       │
│ ├──────────┤ ├──────────┤ ├──────────┤ ├──────────┤       │
│ │ Title    │ │ Title    │ │ Title    │ │ Title    │       │
│ │ Author   │ │ Author   │ │ Author   │ │ Author   │       │
│ │ ◆ Cat    │ │ ◆ Cat    │ │ ◆ Cat    │ │ ◆ Cat    │       │
│ │ Qty: 5   │ │ Qty: 4   │ │ Qty: 6   │ │ Qty: 7   │       │
│ │[Issue]   │ │[Issue]   │ │[Issue]   │ │[Issue]   │       │
│ │[Delete]  │ │[Delete]  │ │[Delete]  │ │[Delete]  │       │
│ └──────────┘ └──────────┘ └──────────┘ └──────────┘       │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### Login/Register
```
┌──────────────────────────┬──────────────────────────┐
│                          │                          │
│  📚 Library              │ ┌────────────────────┐  │
│  Management              │ │ Welcome Back       │  │
│                          │ │ Login to continue  │  │
│  Manage books and        │ │                    │  │
│  users easily with our   │ │ [Email field]      │  │
│  modern system.          │ │ [Password field]   │  │
│                          │ │                    │  │
│  ← Gradient Background   │ │ [Login Button]     │  │
│  (#6c84ff → #9099ff)     │ │                    │  │
│                          │ │ Create Account ➜   │  │
│                          │ └────────────────────┘  │
│                          │                          │
│ (Visible on desktop)     │ (Visible on all screens)│
│                          │ Dark (#252d3d)          │
└──────────────────────────┴──────────────────────────┘
```

## Hover & Focus States

### Button Hover
```
Before:  ┌──────────────┐
         │  Button      │ ← Gradient: #6c84ff → #4969ff
         └──────────────┘

After:   ┌──────────────┐
         │  Button      │ ↑  ← Lifted (-2px)
         └──────────────┘
         Shadow: Box shadow added
         Gradient: Darker (#4969ff → #3652d4)
```

### Input Focus
```
Before:  ┌─────────────────────┐
         │ placeholder text     │ ← Border: #3a4555
         └─────────────────────┘

After:   ┌─────────────────────┐
         │ cursor here         │ ← Border: #6c84ff (Bright)
         └─────────────────────┘    Glow: rgba(108, 132, 255, 0.25)
                                    Background: #252d3d (Lighter)
```

### Card Hover
```
Before:  ┌──────────────┐
         │ Book Card    │            Shadow: Standard
         │              │
         └──────────────┘

After:   ┌──────────────┐
         │ Book Card    │ ↑↑         Shadow: Enhanced
         │              │            Glow: Blue glow
         └──────────────┘            Transform: Lifted effect
```

## Typography

### Font Stack
`Segoe UI, Tahoma, Geneva, Verdana, sans-serif`

### Text Sizes
- H1: 32px - Page titles
- H2: 28px - Section headers
- H3: 24px - Subsection headers
- H4: 20px - Card titles
- H5: 16px - Labels
- Body: 14px - Regular text
- Small: 12px - Secondary info

### Font Weights
- Bold: 600-700 - Headers, labels
- Semi-bold: 600 - Emphasis
- Normal: 400 - Body text
- Light: 300 - Secondary text

## Shadows & Depth

### Shadow Levels
```
Default:  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
Hover:    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
Focus:    box-shadow: 0 0 0 0.2rem rgba(108, 132, 255, 0.25);
```

### Elevation Scale
- Level 0: No shadow
- Level 1: Small shadow (cards, buttons)
- Level 2: Medium shadow (on hover)
- Level 3: Large shadow (modals, dropdowns)

## Responsive Breakpoints

- **Desktop**: 1200px+ (3-4 column layouts)
- **Tablet**: 768px - 1199px (2-3 column layouts)
- **Mobile**: < 768px (Single column)

---

**Note**: All colors are carefully chosen for:
- ✅ WCAG AA contrast compliance
- ✅ Eye comfort in low-light
- ✅ Modern, professional appearance
- ✅ Easy visual hierarchy

