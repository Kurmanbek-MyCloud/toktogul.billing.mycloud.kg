const defaultTheme = require('tailwindcss/defaultTheme');
const { colors } = defaultTheme;

module.exports = {
  future: {
    // removeDeprecatedGapUtilities: true,
    // purgeLayersByDefault: true,
  },
  purge: [],
  theme: {
    container: {
      center: true,
      padding: '1rem',
    },
    inset: {
      ...defaultTheme.inset,
      full: '100%',
    },
    screens: {
      // '2xl': '1200px',
      // 'xxl': {'max': '1450px'},
      // 'xl': {'max': '1382px'},
      // 'x': {'max': '1250px'},
      lg: '1024px',
      l: { max: '980px' },
      md: { max: '900px' },
      sm: { max: '768px' },
      xs: { max: '600px' },
      '2xs': { max: '480px' },
      '3xs': { max: '400px' },
      s: { max: '365px' },
    },
    extend: {
      spacing: {
        7: '1.75rem',
      },
      colors: {
        gray: {
          ...colors.gray,
          300: '#cfcfcf',
          500: '#9e9e9e',
          600: '#76818d',
          700: '#393939',
          800: '#2f2f2f',
        },
        indigo: {
          ...colors.indigo,
          400: '#5c6fe9',
          700: '#2C3E50',
        },
      },
      backgroundImage: {
        login:
          "url('/layouts/v7/modules/Mobile/mobile/resources/images/bk1.jpg')",
      },
    },
  },
  variants: {},
  plugins: [],
};
