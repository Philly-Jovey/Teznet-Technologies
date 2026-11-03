module.exports = {
    darkMode: "class",
    theme: {
      extend: {
        "colors": {
                "primary-container": "#0c24f3",
                "tertiary-fixed": "#d0e4ff",
                "on-error-container": "#93000a",
                "on-primary-container": "#b7bcff",
                "tertiary": "#003a63",
                "background": "#fbf8ff",
                "on-surface-variant": "#444557",
                "on-secondary": "#ffffff",
                "inverse-surface": "#2f303b",
                "secondary-fixed": "#e9ddff",
                "surface-tint": "#2c3eff",
                "error": "#ba1a1a",
                "on-secondary-container": "#e7dbff",
                "surface-container-lowest": "#ffffff",
                "secondary": "#5a00d7",
                "on-error": "#ffffff",
                "on-tertiary-fixed": "#001d35",
                "surface-container-low": "#f4f2ff",
                "primary": "#0014b9",
                "on-tertiary-fixed-variant": "#00497b",
                "surface-container": "#eeecfb",
                "primary-fixed-dim": "#bdc2ff",
                "on-primary": "#ffffff",
                "surface-variant": "#e3e1f0",
                "surface-container-high": "#e8e6f6",
                "tertiary-fixed-dim": "#9ccaff",
                "inverse-primary": "#bdc2ff",
                "tertiary-container": "#005288",
                "surface-dim": "#dad8e7",
                "on-primary-fixed-variant": "#001ae2",
                "on-secondary-fixed": "#22005c",
                "on-secondary-fixed-variant": "#5500cb",
                "primary-fixed": "#e0e0ff",
                "secondary-fixed-dim": "#cfbcff",
                "outline-variant": "#c5c5da",
                "on-tertiary": "#ffffff",
                "outline": "#757689",
                "error-container": "#ffdad6",
                "surface-container-highest": "#e3e1f0",
                "on-background": "#1a1b25",
                "on-primary-fixed": "#000767",
                "on-tertiary-container": "#91c5ff",
                "inverse-on-surface": "#f1effe",
                "on-surface": "#1a1b25",
                "secondary-container": "#7429fe",
                "surface": "#fbf8ff",
                "surface-bright": "#fbf8ff"
        },
        "borderRadius": {
                "DEFAULT": "0.25rem",
                "lg": "0.5rem",
                "xl": "0.75rem",
                "full": "9999px"
        },
        "spacing": {
                "unit": "8px",
                "margin-mobile": "20px",
                "gutter": "24px",
                "container-max": "1280px",
                "margin-desktop": "64px"
        },
        "fontFamily": {
                "label-sm": [
                        "Inter"
                ],
                "headline-lg-mobile": [
                        "Montserrat"
                ],
                "body-lg": [
                        "Inter"
                ],
                "display-lg": [
                        "Montserrat"
                ],
                "label-md": [
                        "Inter"
                ],
                "headline-md": [
                        "Montserrat"
                ],
                "body-md": [
                        "Inter"
                ],
                "headline-lg": [
                        "Montserrat"
                ]
        },
        "fontSize": {
                "label-sm": [
                        "12px",
                        {
                                "lineHeight": "16px",
                                "letterSpacing": "0.05em",
                                "fontWeight": "600"
                        }
                ],
                "headline-lg-mobile": [
                        "24px",
                        {
                                "lineHeight": "32px",
                                "fontWeight": "600"
                        }
                ],
                "body-lg": [
                        "18px",
                        {
                                "lineHeight": "28px",
                                "fontWeight": "400"
                        }
                ],
                "display-lg": [
                        "56px",
                        {
                                "lineHeight": "64px",
                                "letterSpacing": "-0.02em",
                                "fontWeight": "700"
                        }
                ],
                "label-md": [
                        "14px",
                        {
                                "lineHeight": "20px",
                                "letterSpacing": "0.01em",
                                "fontWeight": "500"
                        }
                ],
                "headline-md": [
                        "24px",
                        {
                                "lineHeight": "32px",
                                "fontWeight": "600"
                        }
                ],
                "body-md": [
                        "16px",
                        {
                                "lineHeight": "24px",
                                "fontWeight": "400"
                        }
                ],
                "headline-lg": [
                        "32px",
                        {
                                "lineHeight": "40px",
                                "fontWeight": "600"
                        }
                ]
        }
},
    },
  }
module.exports.corePlugins = Object.assign({}, module.exports.corePlugins || {}, { preflight: false });
