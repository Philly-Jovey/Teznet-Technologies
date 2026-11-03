module.exports = {
          darkMode: "class",
          theme: {
            extend: {
              "colors": {
                      "surface-container": "#e5eeff",
                      "on-secondary-container": "#fffbff",
                      "surface": "#f8f9ff",
                      "tertiary-container": "#001f26",
                      "on-error": "#ffffff",
                      "secondary": "#712ae2",
                      "surface-dim": "#cbdbf5",
                      "on-surface-variant": "#45464d",
                      "on-tertiary-fixed-variant": "#004e5c",
                      "on-surface": "#0b1c30",
                      "on-primary-fixed": "#131b2e",
                      "inverse-on-surface": "#eaf1ff",
                      "error": "#ba1a1a",
                      "tertiary-fixed": "#acedff",
                      "surface-container-lowest": "#ffffff",
                      "tertiary-fixed-dim": "#4cd7f6",
                      "primary": "#000000",
                      "on-background": "#0b1c30",
                      "on-primary-fixed-variant": "#3f465c",
                      "primary-container": "#131b2e",
                      "secondary-fixed-dim": "#d2bbff",
                      "surface-container-high": "#dce9ff",
                      "outline-variant": "#c6c6cd",
                      "surface-container-highest": "#d3e4fe",
                      "surface-tint": "#565e74",
                      "on-primary": "#ffffff",
                      "tertiary": "#000000",
                      "surface-variant": "#d3e4fe",
                      "inverse-primary": "#bec6e0",
                      "inverse-surface": "#213145",
                      "on-secondary-fixed-variant": "#5a00c6",
                      "on-secondary-fixed": "#25005a",
                      "on-secondary": "#ffffff",
                      "on-primary-container": "#7c839b",
                      "surface-container-low": "#eff4ff",
                      "background": "#f8f9ff",
                      "on-tertiary-container": "#0090a9",
                      "secondary-container": "#8a4cfc",
                      "secondary-fixed": "#eaddff",
                      "on-tertiary-fixed": "#001f26",
                      "primary-fixed-dim": "#bec6e0",
                      "outline": "#76777d",
                      "surface-bright": "#f8f9ff",
                      "primary-fixed": "#dae2fd",
                      "on-error-container": "#93000a",
                      "error-container": "#ffdad6",
                      "on-tertiary": "#ffffff"
              },
              "borderRadius": {
                      "DEFAULT": "0.25rem",
                      "lg": "0.5rem",
                      "xl": "0.75rem",
                      "full": "9999px"
              },
              "spacing": {
                      "container-max": "1280px",
                      "margin-mobile": "20px",
                      "unit": "8px",
                      "margin-desktop": "64px",
                      "gutter": "24px"
              },
              "fontFamily": {
                      "body-md": [
                              "Inter"
                      ],
                      "body-lg": [
                              "Inter"
                      ],
                      "label-md": [
                              "Inter"
                      ],
                      "headline-lg": [
                              "Montserrat"
                      ],
                      "headline-md": [
                              "Montserrat"
                      ],
                      "display-lg": [
                              "Montserrat"
                      ],
                      "label-sm": [
                              "Inter"
                      ],
                      "headline-lg-mobile": [
                              "Montserrat"
                      ]
              },
              "fontSize": {
                      "body-md": [
                              "16px",
                              {
                                      "lineHeight": "24px",
                                      "fontWeight": "400"
                              }
                      ],
                      "body-lg": [
                              "18px",
                              {
                                      "lineHeight": "28px",
                                      "fontWeight": "400"
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
                      "headline-lg": [
                              "32px",
                              {
                                      "lineHeight": "40px",
                                      "fontWeight": "600"
                              }
                      ],
                      "headline-md": [
                              "24px",
                              {
                                      "lineHeight": "32px",
                                      "fontWeight": "600"
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
                      ]
              }
      },
          },
        }
module.exports.corePlugins = Object.assign({}, module.exports.corePlugins || {}, { preflight: false });
