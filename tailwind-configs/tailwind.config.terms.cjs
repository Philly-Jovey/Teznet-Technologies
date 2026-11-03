module.exports = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            background: '#fbf8ff',
            surface: '#fbf8ff',
            'surface-container': '#eeecfb',
            'surface-container-low': '#f4f2ff',
            'surface-container-high': '#e8e6f6',
            primary: '#0014b9',
            'primary-container': '#0c24f3',
            secondary: '#5a00d7',
            tertiary: '#003a63',
            'on-primary': '#ffffff',
            'on-surface': '#1a1b25',
            'on-surface-variant': '#444557',
            'outline-variant': '#c5c5da'
          },
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
            heading: ['Montserrat', 'sans-serif']
          }
        }
      }
    };
module.exports.corePlugins = Object.assign({}, module.exports.corePlugins || {}, { preflight: false });
