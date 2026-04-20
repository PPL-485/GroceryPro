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
    defaultTheme: 'light',
  },
})