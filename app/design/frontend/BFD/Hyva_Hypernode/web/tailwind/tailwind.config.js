const {
  spacing
} = require('tailwindcss/defaultTheme');

const colors = require('tailwindcss/colors');

const hyvaModules = require('@hyva-themes/hyva-modules');

module.exports = hyvaModules.mergeTailwindConfig({
  theme: {
    extend: {
      screens: {
        'sm': '640px',
        // => @media (min-width: 640px) { ... }
        'md': '768px',
        // => @media (min-width: 768px) { ... }
        'lg': '1024px',
        // => @media (min-width: 1024px) { ... }
        'xl': '1280px',
        // => @media (min-width: 1280px) { ... }
        '2xl': '1280px' // => @media (min-width: 1536px) { ... }

      },
      fontFamily: {
        sans: ["Poppins", "Segoe UI", "Helvetica Neue", "Arial", "sans-serif"]
      },
      colors: {
        lego: {
          green: {
            "lighter": "#57bd1b10",
            "DEFAULT": "#57bd1b",
            darker: "#4ba617"
          },
          blue: {
            lighter: "#07adeb10",
            light: "#07adeb",
            dark: "#075397",
            darker: "#064279"
          },
          menu: {
            light: "#006ab2",
            dark: "#00539f"
          },
          yellow: {
            "DEFAULT": "#ffd500"
          }
        },
        primary: {
          lighter: colors.blue['300'],
          "DEFAULT": colors.blue['800'],
          darker: colors.blue['900']
        },
        secondary: {
          lighter: colors.blue['100'],
          "DEFAULT": colors.blue['200'],
          darker: colors.blue['300']
        },
        background: {
          lighter: colors.blue['100'],
          "DEFAULT": colors.blue['200'],
          darker: colors.blue['300']
        },
        green: colors.emerald,
        yellow: colors.amber,
        purple: colors.violet
      },
      textColor: {
        orange: colors.orange,
        red: { ...colors.red,
          "DEFAULT": colors.red['500']
        },
        primary: {
          lighter: colors.gray['700'],
          "DEFAULT": colors.gray['800'],
          darker: colors.gray['900']
        },
        secondary: {
          lighter: colors.gray['400'],
          "DEFAULT": colors.gray['600'],
          darker: colors.gray['800']
        }
      },
      backgroundColor: {
        primary: {
          lighter: colors.blue['600'],
          "DEFAULT": colors.blue['700'],
          darker: colors.blue['800']
        },
        secondary: {
          lighter: colors.blue['100'],
          "DEFAULT": colors.blue['200'],
          darker: colors.blue['300']
        },
        container: {
          lighter: '#ffffff',
          "DEFAULT": '#fafafa',
          darker: '#f5f5f5'
        }
      },
      borderColor: {
        primary: {
          lighter: colors.blue['600'],
          "DEFAULT": "#075397",
          darker: colors.blue['800']
        },
        secondary: {
          lighter: colors.blue['100'],
          "DEFAULT": colors.blue['200'],
          darker: colors.blue['300']
        },
        container: {
          lighter: '#f5f5f5',
          "DEFAULT": '#e7e7e7',
          darker: '#b6b6b6'
        }
      },
      minWidth: {
        8: spacing["8"],
        20: spacing["20"],
        40: spacing["40"],
        48: spacing["48"]
      },
      minHeight: {
        14: spacing["14"],
        a11y: '44px',
        'screen-25': '25vh',
        'screen-50': '50vh',
        'screen-75': '75vh'
      },
      maxHeight: {
        '0': '0',
        'screen-25': '25vh',
        'screen-50': '50vh',
        'screen-75': '75vh'
      },
      container: {
        center: true,
        padding: '1.5rem'
      },
      letterSpacing: {
        tightest: '-.175em'
      },
      borderRadius:{
        '4xl': '2rem'
      },
      typography: {
        DEFAULT: {
          css: {
            maxWidth: '100%'
          }
        }
      },
      animation: {
        'infinite-scroll': 'infinite-scroll 25s linear infinite',
      },
      keyframes: {
        'infinite-scroll': {
          from: { transform: 'translateSX(0)' },
          to: { transform: 'translateX(-100%)' },
        }
      }
    },
  },
  plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
  // Examples for excluding patterns from purge
  content: [
    // this theme's phtml and layout XML files
    '../../**/*.phtml',
    '../../*/layout/*.xml',
    '../../*/page_layout/override/base/*.xml',
    // parent theme in Vendor (if this is a child-theme)
    '../../../../../../../vendor/hyva-themes/magento2-default-theme/**/*.phtml',
    '../../../../../../../vendor/hyva-themes/magento2-default-theme/*/layout/*.xml',
    '../../../../../../../vendor/hyva-themes/magento2-default-theme/*/page_layout/override/base/*.xml',
    // app/code phtml files (if need tailwind classes from app/code modules)
    //'../../../../../../../app/code/**/*.phtml',
  ]
});
