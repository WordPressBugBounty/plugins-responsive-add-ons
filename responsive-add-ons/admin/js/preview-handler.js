(function () {
    if (window.self === window.top) return;
  
    const logo = document.querySelector('.custom-logo');
    const logoUrl = logo ? logo.src : null;
    const logoWidth = logo ? logo.naturalWidth : null;
    const logoHeight = logo ? logo.naturalHeight : null;

    window.parent.postMessage(
      { 
          type: 'CC_PREVIEW_READY',
          logoUrl: logoUrl,
          logoWidth: logoWidth,
          logoHeight: logoHeight
      },
      '*'
    );
  

    window.addEventListener('message', (event) => {

        if (event.data?.type === 'SET_PALETTE') {
            const root = document.documentElement;
            const vars = event.data.payload;

            const prevPalette3 = getResolvedCSSVar('--responsive-global-palette3', root);
            const prevHeadingColor = getResolvedCSSVar('--responsive-global-headings-color', root);

            function getResolvedCSSVar(varName, element = document.documentElement) {
                const styles = getComputedStyle(element);
                let value = styles.getPropertyValue(varName).trim();

                if (!value.startsWith('var(')) {
                    return value;
                }

                const match = value.match(/var\((--[^)]+)\)/);
                if (!match) return value;

                return styles.getPropertyValue(match[1]).trim();
            }

            // Was heading color effectively linked to palette3?
            const headingWasFollowingPalette =
                prevPalette3 &&
                prevHeadingColor &&
                prevPalette3.toLowerCase() === prevHeadingColor.toLowerCase();

    
            Object.entries(vars).forEach(([key, value]) => {

                if(key !== '--responsive-global-headings-color') {
                    root.style.setProperty(key, value);
                }
                // Special handling for headings color
                if (
                    key === '--responsive-global-headings-color'
                    &&
                    headingWasFollowingPalette
                ) {
                    // Update ONLY because it was previously linked
                    root.style.setProperty(key, value);
                }
            });

        }

        if (event.data?.type === 'SET_FONT_PRESET') {
             const preset = event.data.payload;
             if (!preset) return;

             if (preset.id === 'default') {
                // Remove custom font styles and link
                const existingLink = document.getElementById('responsive-preview-fonts');
                if (existingLink) existingLink.remove();

                const existingStyle = document.getElementById('responsive-preview-font-styles');
                if (existingStyle) existingStyle.remove();
                return;
             }

             // Load Google Fonts
             loadGoogleFonts(preset);

             // Apply Styles
             updateFontStyles(preset);
        }

        if (event.data?.type === 'SET_LOGO') {
            const logo = event.data.payload;
            let logoImgs = document.querySelectorAll('.custom-logo');

            if (logoImgs.length === 0 && logo) {
                // Logo element doesn't exist, we will create a logo element in the preview
                // Try to find a container
                const siteLogoContainer = document.querySelector('.site-branding-wrapper');

                if (siteLogoContainer) {
                    const link = document.createElement('a');
                    link.className = 'custom-logo-link';
                    link.rel = 'home';
                    link.href = '/'; // Or actual site URL if available
                    link.itemProp = 'url';

                    const newLogoImg = document.createElement('img');
                    newLogoImg.className = 'custom-logo';
                    newLogoImg.itemProp = 'logo';
                    
                    link.appendChild(newLogoImg);
                    
                    if (document.querySelector('.site-logo')) {
                         siteLogoContainer.appendChild(link);
                    } else {
                        siteLogoContainer.prepend(link);
                     }
                     
                    logoImgs = document.querySelectorAll('.custom-logo');
                }
            }

            
            if (logoImgs.length > 0) {
                logoImgs.forEach((logoImg) => {
                    if(logo) {
                        logoImg.src = logo.url;
                        logoImg.srcset = ''; // Clear srcset to avoid browser selecting different size
                        // Remove fixed HTML attributes
                        logoImg.sizes = '';
                        logoImg.style.display = 'block';
                        logoImg.alt = logo.alt || 'Site Logo'; // Good practice to set alt
                    } else {
                         // If logo is removed, we can hide the image or set src to empty
                         logoImg.src = '';
                         logoImg.srcset = '';
                         logoImg.style.display = 'none';
                    }
                });
            }
        }
    });

    function loadGoogleFonts(preset) {
        const fonts = [];
        if (preset.bodyFont) {
            fonts.push(`${preset.bodyFont}:${preset.bodyWeight || '400'}`);
        }
        if (preset.headingFont) {
            fonts.push(`${preset.headingFont}:${preset.headingWeight || '400'}`);
        }

        if (fonts.length === 0) return;

        const fontQuery = fonts.join('|').replace(/ /g, '+');
        const href = `https://fonts.googleapis.com/css?family=${fontQuery}&display=swap`;

        let link = document.getElementById('responsive-preview-fonts');
        if (!link) {
            link = document.createElement('link');
            link.id = 'responsive-preview-fonts';
            link.rel = 'stylesheet';
            document.head.appendChild(link);
        }
        link.href = href;
    }

    function updateFontStyles(preset) {
        let style = document.getElementById('responsive-preview-font-styles');
        if (!style) {
            style = document.createElement('style');
            style.id = 'responsive-preview-font-styles';
            document.head.appendChild(style);
        }

        const css = `
            body, .post-meta {
                font-family: '${preset.bodyFont}', sans-serif;
                font-weight: ${preset.bodyWeight || '400'};
            }
            h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
                font-family: '${preset.headingFont}', sans-serif;
                font-weight: ${preset.headingWeight || '400'};
            }
        `;
        style.textContent = css;
    }

  })();
