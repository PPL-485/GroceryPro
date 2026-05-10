# GroceryPro Theme Documentation

## Overview
GroceryPro uses a modern, cohesive design system built with **Vue 3**, **Inertia.js**, **Vuetify**, and **Tailwind CSS**. The theme features a natural, earthy color palette designed for a grocery management system.

---

## Color Palette

### Primary Colors
- **Primary Green**: `#2E6B3B` - Deep forest green, used for primary actions, icons, and main branding
- **Secondary Orange**: `#D38865` - Warm terracotta accent, used for secondary actions and highlights

### Neutral Colors
- **Background**: `#FCFBF8` - Off-white, clean background color
- **Surface**: `#FFFFFF` - Pure white for cards and containers
- **Text Primary**: `#1B1B1B` - Deep charcoal for main text
- **Text Secondary**: `#666666` - Medium gray for secondary text
- **Text Tertiary**: `#999999` - Light gray for disabled or tertiary text
- **Border**: `#E5E5E5` - Light gray for borders

### Semantic Colors
- **Success**: `#2E7D32` - Green for positive actions and success states
- **Error**: `#D32F2F` - Red for errors and destructive actions
- **Warning**: `#F59E0B` - Amber for warnings and cautions
- **Info**: `#1976D2` - Blue for informational messages

---

## Typography

### Font Family
- **Primary**: `Plus Jakarta Sans` - Modern, clean sans-serif for headings and UI elements
- **Fallback**: System font stack

### Font Sizes & Weights
- **H1 (Display)**: 32px, Weight 700
- **H2 (Headline)**: 28px, Weight 700
- **H3 (Title)**: 24px, Weight 600
- **H4 (Subtitle)**: 20px, Weight 600
- **Body 1**: 16px, Weight 400
- **Body 2**: 14px, Weight 400
- **Caption**: 12px, Weight 500

---

## Component Styling

### Buttons
- **Primary Button**: Uses `#2E6B3B` background with white text
- **Secondary Button**: Uses `#D38865` background with white text
- **Default Rounded**: `12px` (lg)
- **Default Height**: `40px` for standard buttons, `48px` for form submission buttons
- **Box Shadow**: `0 4px 12px rgba(color, 0.2)`
- **Elevation**: `0` (flat) for most buttons

### Cards
- **Border Radius**: `12px` (lg)
- **Elevation**: `1-2` depending on hover state
- **Background**: White with subtle shadows
- **Padding**: `16px` to `24px` depending on content

### Text Fields
- **Variant**: `outlined`
- **Rounded**: `12px` (lg)
- **Density**: `comfortable`
- **Icon Color**: Primary green when focused

### Chips & Badges
- **Rounded**: `12px` (lg)
- **Variant**: `flat`
- **Density**: `comfortable`

---

## Layout Patterns

### Guest Layout (Auth Pages)
- **Background**: Gradient background with animated blobs
- **Animation**: Float animation on background elements (6-8 seconds)
- **Max Width**: `420px` for form containers
- **Header**: Logo and branding with app name "GroceryPro"
- **Footer**: Copyright information
- **Padding**: `16px` on mobile, `24px` on desktop

### Authenticated Layout
- **Structure**: 3-column layout
  - **Left Column**: Sidebar navigation
  - **Center Column**: AppBar + scrollable content area
  - **Right Column**: Transactions sidebar (togglable)
- **AppBar**: Scoped to center column only
- **Content Padding**: `24px` sides, `16px` top/bottom
- **Transitions**: Smooth slide animations for panels

### Main Content Area
- **Max Width**: Full width with contained padding
- **Grid System**: 12-column responsive grid using Vuetify
- **Spacing**: 6px, 12px, 16px, 24px increments
- **Breakpoints**: xs, sm, md, lg, xl

---

## Page-Specific Styles

### Authentication Pages (Login, Register, etc.)
- **Card Background**: White `#FFFFFF`
- **Card Elevation**: `2` with rounded corners
- **Icon Background**: Primary green `#2E6B3B` for most pages, secondary for recovery pages
- **Icon Size**: `24px`
- **Avatar Size**: `48px`
- **Form Fields**: Outlined style with icons
- **Button Color**: Primary green for registration/confirmation, secondary for recovery

### Dashboard
- **Stat Cards**: Minimal elevation, hover effect with translation
- **Icons**: Color-coded (green for positive, red for alerts)
- **Charts**: Uses theme colors for consistency
- **Table**: Hover state with subtle background change

### Category & Product Management
- **Grid Layout**: Responsive 3-4 columns
- **Hover Effects**: Elevation increase and slight translation
- **Action Buttons**: Floating in top-right
- **Search Field**: Outlined style with magnify icon

---

## Animation Principles

### Transitions
- **Duration**: 150-300ms for element changes
- **Easing**: `cubic-bezier(0.4, 0, 0.2, 1)` for smooth motion
- **Properties**: All, transform, opacity

### Animations
- **Float**: 6-8 second infinite loop for background blobs
- **Slide**: Left/right slide animations for content entry
- **Fade**: Opacity fade for content reveal
- **Page Transitions**: Handled by Inertia.js progress bar color `#4B5563`

---

## Responsive Breakpoints

| Breakpoint | Width | Usage |
|-----------|-------|-------|
| **xs** | < 600px | Mobile phones |
| **sm** | 600px - 959px | Small tablets |
| **md** | 960px - 1263px | Tablets |
| **lg** | 1264px - 1903px | Desktops |
| **xl** | ≥ 1904px | Large displays |

---

## Icon System

### Icon Library
- **Provider**: Material Design Icons (MDI)
- **Sizes**: small (16px), default (24px), large (32px), x-large (48px)
- **Colors**: Follow component color scheme

### Common Icons
- Shopping cart: `mdi-cart-outline`
- Products: `mdi-package-variant-closed`
- Categories: `mdi-shape-outline`
- Dashboard: `mdi-chart-bar`
- Users: `mdi-account-group-outline`
- Settings: `mdi-cog-outline`
- Lock: `mdi-lock-outline`, `mdi-lock-reset`
- Eye: `mdi-eye-outline`, `mdi-eye-off-outline`
- Email: `mdi-email-outline`, `mdi-email-check-outline`
- Account: `mdi-account-outline`, `mdi-account-plus-outline`

---

## Vuetify Theme Configuration

The Vuetify theme is configured in `resources/js/Plugins/vuetify.js`:

```javascript
brand: {
  dark: false,
  colors: {
    primary: '#2E6B3B',
    secondary: '#D38865',
    background: '#FCFBF8',
    surface: '#FFFFFF',
    error: '#D32F2F',
    success: '#2E7D32',
    warning: '#F59E0B',
    info: '#1976D2',
  }
}
```

---

## Component Defaults

Vuetify components have the following defaults applied:

```javascript
VBtn: {
  rounded: 'lg',
  flat: true,
}

VTextField: {
  rounded: 'lg',
  flat: true,
  density: 'comfortable',
  variant: 'outlined',
}

VCard: {
  rounded: 'lg',
  flat: true,
}

VAlert: {
  rounded: 'lg',
}

VChip: {
  rounded: 'lg',
  flat: true,
  variant: 'outlined',
  density: 'comfortable',
}
```

---

## Best Practices

### Colors
1. Use theme colors from Vuetify theme configuration instead of hardcoding hex values
2. For custom colors, use the defined color palette
3. Maintain sufficient contrast ratios (WCAG AA minimum)
4. Use semantic colors for success/error/warning states

### Spacing
1. Use Vuetify spacing utilities: `pa-`, `ma-`, `px-`, `my-`, etc.
2. Stick to defined spacing scale: 0, 1, 2, 3, 4, 6, 8, 12, 16, 24px
3. Use consistent padding/margin patterns across pages

### Typography
1. Use Vuetify text classes: `text-h1` through `text-body-2`
2. Use `font-weight-bold`, `font-weight-medium`, `font-weight-regular`
3. Keep line-height at 1.5 for body text for readability

### Components
1. Use Vuetify components instead of HTML elements
2. Apply consistent elevation and rounding
3. Use the `variant` prop for styling consistency
4. Leverage Vuetify's responsive utilities for mobile-first design

### Icons
1. Use `mdi-` prefixed icon names from Material Design Icons
2. Size icons proportionally to their context
3. Use color to indicate state or importance
4. Pair icons with text for clarity

---

## File Structure

```
resources/
├── js/
│   ├── Layouts/
│   │   ├── GuestLayout.vue       # Auth pages layout
│   │   └── AuthenticatedLayout.vue # Main app layout
│   ├── Pages/
│   │   ├── Auth/
│   │   │   ├── Login.vue
│   │   │   ├── Register.vue
│   │   │   ├── ForgotPassword.vue
│   │   │   ├── ResetPassword.vue
│   │   │   ├── VerifyEmail.vue
│   │   │   └── ConfirmPassword.vue
│   │   ├── Dashboard.vue
│   │   ├── Categories.vue
│   │   ├── Products.vue
│   │   ├── Transactions.vue
│   │   └── ...
│   ├── Components/          # Reusable UI components
│   ├── Plugins/
│   │   └── vuetify.js       # Theme configuration
│   └── app.js              # Main app entry point
└── css/
    └── app.css             # Global styles
```

---

## Common Customizations

### Adding a New Page
1. Create a new Vue file in `resources/js/Pages/`
2. Import `AuthenticatedLayout` (or `GuestLayout` for public pages)
3. Use theme colors: `color="primary"`, `color="secondary"`, `color="error"`, etc.
4. Follow existing component patterns

### Modifying Colors
1. Update `resources/js/Plugins/vuetify.js` theme configuration
2. Use theme color names instead of hex values in components
3. Use Vuetify color modifiers: `green-lighten-4`, `red-darken-2`, etc.

### Custom Styling
1. Use Tailwind classes for utility styling
2. Use Vuetify spacing utilities (`pa-4`, `my-6`, etc.)
3. Avoid inline styles; prefer class-based styling
4. For component-specific styles, use scoped `<style>` blocks

---

## Testing Checklist

When implementing new pages or features, verify:
- [ ] Colors match the palette
- [ ] Typography is consistent
- [ ] Spacing follows the grid system
- [ ] Icons are properly sized and colored
- [ ] Buttons have consistent styling
- [ ] Forms have proper error states
- [ ] Responsive behavior works on mobile/tablet/desktop
- [ ] Animations are smooth and not distracting
- [ ] Accessibility standards are met (contrast, labels, etc.)
- [ ] Theme works in light mode

---

## Future Enhancements

- [ ] Dark mode support
- [ ] Custom color picker for brand colors
- [ ] Additional animation library integration
- [ ] Accessibility audit and improvements
- [ ] Performance optimization for animations
- [ ] RTL (Right-to-Left) language support
