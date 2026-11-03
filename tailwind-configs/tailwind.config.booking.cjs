module.exports = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface-bright": "#fbf8ff",
                        "secondary-fixed-dim": "#cfbcff",
                        "outline": "#757689",
                        "outline-variant": "#c5c5da",
                        "surface": "#fbf8ff",
                        "error": "#ba1a1a",
                        "on-secondary-fixed": "#22005c",
                        "secondary": "#5a00d7",
                        "on-tertiary-fixed-variant": "#00497b",
                        "on-primary-fixed-variant": "#001ae2",
                        "secondary-container": "#7429fe",
                        "inverse-on-surface": "#f1effe",
                        "error-container": "#ffdad6",
                        "surface-container-low": "#f4f2ff",
                        "on-surface": "#1a1b25",
                        "on-secondary-fixed-variant": "#5500cb",
                        "on-background": "#1a1b25",
                        "on-primary-container": "#b7bcff",
                        "on-surface-variant": "#444557",
                        "primary": "#0014b9",
                        "background": "#fbf8ff",
                        "surface-container-high": "#e8e6f6",
                        "surface-dim": "#dad8e7",
                        "primary-container": "#0c24f3",
                        "on-error": "#ffffff",
                        "on-error-container": "#93000a",
                        "on-secondary": "#ffffff",
                        "secondary-fixed": "#e9ddff",
                        "inverse-surface": "#2f303b",
                        "surface-tint": "#2c3eff",
                        "on-secondary-container": "#e7dbff",
                        "tertiary-container": "#005288",
                        "surface-container": "#eeecfb",
                        "surface-container-highest": "#e3e1f0",
                        "primary-fixed-dim": "#bdc2ff",
                        "tertiary-fixed-dim": "#9ccaff",
                        "on-primary": "#ffffff",
                        "on-primary-fixed": "#000767",
                        "tertiary": "#003a63",
                        "on-tertiary": "#ffffff",
                        "on-tertiary-fixed": "#001d35",
                        "surface-container-lowest": "#ffffff",
                        "inverse-primary": "#bdc2ff",
                        "primary-fixed": "#e0e0ff",
                        "surface-variant": "#e3e1f0",
                        "on-tertiary-container": "#91c5ff",
                        "tertiary-fixed": "#d0e4ff"
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
                        "label-sm": ["Inter"],
                        "body-lg": ["Inter"],
                        "headline-lg-mobile": ["Montserrat"],
                        "body-md": ["Inter"],
                        "label-md": ["Inter"],
                        "headline-md": ["Montserrat"],
                        "headline-lg": ["Montserrat"],
                        "display-lg": ["Montserrat"]
                    },
                    "fontSize": {
                        "label-sm": ["12px", { "lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600" }],
                        "body-lg": ["18px", { "lineHeight": "28px", "fontWeight": "400" }],
                        "headline-lg-mobile": ["24px", { "lineHeight": "32px", "fontWeight": "600" }],
                        "body-md": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
                        "label-md": ["14px", { "lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "500" }],
                        "headline-md": ["24px", { "lineHeight": "32px", "fontWeight": "600" }],
                        "headline-lg": ["32px", { "lineHeight": "40px", "fontWeight": "600" }],
                        "display-lg": ["56px", { "lineHeight": "64px", "letterSpacing": "-0.02em", "fontWeight": "700" }]
                    }
                }
            }
        }
module.exports.corePlugins = Object.assign({}, module.exports.corePlugins || {}, { preflight: false });
