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
        fontFamily: 'comic-sans, sans-serif'
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
          primary: '#4CAF50',
          'on-primary': '#FFFFFF',

          secondary: '#81C784',
          'on-secondary': '#1B5E20',

          background: '#FFFFFF',
          'on-background': '#1F2937',

          surface: '#FFFFFF',
          'on-surface': '#1F2937',

          error: '#F44336',
          'on-error': '#FFFFFF',

          info: '#2196F3',
          success: '#4CAF50',
          warning: '#FFC107',
        }
      }
    },
  },
})