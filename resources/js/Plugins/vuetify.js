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

          background: '#F9FAFB',
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