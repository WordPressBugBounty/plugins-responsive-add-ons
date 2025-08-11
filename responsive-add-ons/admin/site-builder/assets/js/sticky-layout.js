function adjustStickyOffsets() {

    const header = document.querySelector('.responsive-site-builder-header');
    const footer = document.querySelector('.responsive-site-builder-footer');

    if ("enabled" === resposiveAddonsSB.sticky_header && header) {
        const headerHeight = header.offsetHeight;

        // Create wrapper
        const headerWrapper = document.createElement('div');
        headerWrapper.style.minHeight = headerHeight + 'px';

        // Wrap header
        header.parentNode.insertBefore(headerWrapper, header);
        headerWrapper.appendChild(header);
    }

    if ( "enabled" === resposiveAddonsSB.sticky_footer && footer) {
        const footerHeight = footer.offsetHeight;

        // Create wrapper
        const footerWrapper = document.createElement('div');
        footerWrapper.style.minHeight = footerHeight + 'px';

        // Wrap footer
        footer.parentNode.insertBefore(footerWrapper, footer);
        footerWrapper.appendChild(footer);
    }
}

// Run on DOM ready
document.addEventListener('DOMContentLoaded', adjustStickyOffsets);