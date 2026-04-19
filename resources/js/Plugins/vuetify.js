import 'vuetify/styles'
import { createVuetify } from 'vuetify'

import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

export default createVuetify({
  components,
  directives,
  theme: {
    defaultTheme: 'brand',
    themes: {
      light: {
        dark: false
      },
      dark: {
        dark: true
      },
      brand: {
        dark: false,
        colors: {
          // 🌿 Brand / Accent
          primary: '#4CAF50',        // Green 500
          'on-primary': '#FFFFFF',

          secondary: '#81C784',      // Green 300
          'on-secondary': '#1B5E20',

          // 🧱 Base UI (HARUS netral biar clean)
          background: '#F9FAFB',     // light grey (clean, bukan putih polos)
          'on-background': '#1F2937',

          surface: '#FFFFFF',        // card, input, modal
          'on-surface': '#1F2937',

          // 🎯 State colors
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