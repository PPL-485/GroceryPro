import 'vuetify/styles'
import { createVuetify } from 'vuetify'

import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

export default createVuetify({
  components,
  directives,
  defaults: {
    global: {
      style: {
        fontFamily: '"Plus Jakarta Sans", sans-serif'
      }
    },
    VBtn: {
      rounded: 'lg',
      flat: true,
    },
    VTextField: {
      rounded: 'lg',
      flat: true,
      density: 'comfortable',
      variant: 'outlined',
      color: 'primary',
    },
    VChip: {
      rounded: 'lg',
      flat: true,
      variant: 'outlined',
      density: 'comfortable',
      color: 'primary',
      class: 'rounded-xl',
    },
    VCard: {
      rounded: 'lg',
      flat: true,
    },
    VAlert: {
      rounded: 'lg',
    },
    VToolbar: {
      rounded: 'lg',
    },
    VContainer: {
      rounded: 'lg',
    },
  },
  theme: {
    defaultTheme: 'brand',
    themes: {
      // light: {
      //   dark: false
      // },
      dark: {
        dark: true
      },
      brand: {
        dark: false,
        colors: {
          // ── Primary: deep forest green (matches landing page) ──
          primary: '#2E6B3B',
          'on-primary': '#FFFFFF',

          // ── Secondary: warm terracotta accent ──
          secondary: '#D38865',
          'on-secondary': '#FFFFFF',

          // ── Backgrounds ──
          background: '#FCFBF8',
          'on-background': '#1B1B1B',

          surface: '#FFFFFF',
          'on-surface': '#1B1B1B',

          // ── Semantic ──
          error: '#D32F2F',
          'on-error': '#FFFFFF',

          info: '#1976D2',
          success: '#2E7D32',
          warning: '#F59E0B',
        }
      }
    },
  },
})