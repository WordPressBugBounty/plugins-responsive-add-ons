(function($) {
    'use strict';

    var $window = $(window);

    $(document).ready(function() {
        // Mega menu
        var windowWidth = $window.width();
        if (windowWidth >= 768) {
            responsiveMegaMenu();
        }
    });

    function responsiveMegaMenu() {
        renderMegaMenuWidth();

        if ($window.width() < 767) {
            $('.megamenu').css({ display: 'block' });
            $('#site-navigation .megamenu-parent').find('.megamenu .children, .megamenu .sub-menu').css({ width: 'auto' });
        } else {
            $('.megamenu').css({ display: 'flex' });
            $('ul.megamenu > li').addClass('focus');
        }
    }

    function renderMegaMenuWidth() {
        let class_prefix = 'responsive-megamenu-width-';
        let $parent = $('#site-navigation .responsive-megamenu-parent');

        $parent.each(function() {
            var idValue = $(this).attr('id');
            let menuIDClass = '.' + idValue;

            $(menuIDClass).hover(
                function() {
                    // Content Width
                    if ($(menuIDClass + '.responsive-megamenu-parent').hasClass(class_prefix + 'content')) {
                        let $siteHeader = $('.responsive-site-header-wrap .container > .site-header-row'),
                            $menuWidth = $siteHeader.width(),
                            $menuPosition = $siteHeader.offset(),
                            $menuItemPosition = $(this).offset(),
                            $positionLeft = $menuItemPosition.left - $menuPosition.left;

                        $(this).find('.responsive-megamenu').css({ left: '-' + $positionLeft + 'px', width: $menuWidth });

                        let $megaSubMenuOffset = $(this).find('.responsive-megamenu').offset();
                        let $tabWidth = $(this).find('.responsive-megamenu > li').width();

                        $(this).find('.responsive-megamenu .children, .responsive-megamenu .sub-menu').offset({ top: $megaSubMenuOffset.top });
                        $(this).find('.responsive-megamenu .children, .responsive-megamenu .sub-menu').css({ width: $tabWidth });
                    }

                    // Menu Container
                    if ($(menuIDClass + '.responsive-megamenu-parent').hasClass(class_prefix + 'menu-container')) {
                        let $siteHeader = $('.responsive-site-header-wrap .container #header-menu'),
                            $menuWidth = $siteHeader.width(),
                            $menuPosition = $siteHeader.offset(),
                            $menuItemPosition = $(this).offset(),
                            $positionLeft = $menuItemPosition.left - $menuPosition.left;

                        $(this).find('.responsive-megamenu').css({ left: '-' + $positionLeft + 'px', width: $menuWidth });
                        $(this).find('.responsive-megamenu .children, .responsive-megamenu .sub-menu').css({ width: 'auto' });
                    }

                    // Full Width
                    if ($(menuIDClass + '.responsive-megamenu-parent').hasClass(class_prefix + 'full')) {
                        let $containerRow = $('.responsive-site-header-wrap .container > .site-header-row'),
                            $siteHeader = $('#masthead.site-header'),
                            $menuWidth = $siteHeader.width(),
                            $menuPosition = $siteHeader.offset(),
                            $menuItemPosition = $(this).offset(),
                            $positionLeft = $menuItemPosition.left - $menuPosition.left;

                        $(this).find('.responsive-full-megamenu-wrapper').css({ left: '-' + $positionLeft + 'px', width: $menuWidth });

                        let $tabWidth = $(this).find('.responsive-megamenu > li').width();

                        $(this).find('.responsive-megamenu.sub-menu').css({ position: 'unset', width: $containerRow.width(), margin: '0 auto' });
                        $(this).find('.responsive-megamenu .children, .responsive-megamenu .sub-menu').css({ width: $tabWidth });
                    }

                    // Full Stretched Width
                    if ($(menuIDClass + '.responsive-megamenu-parent').hasClass(class_prefix + 'full-stretched')) {
                        let $siteHeader = $('#masthead.site-header'),
                            $menuWidth = $siteHeader.width(),
                            $menuPosition = $siteHeader.offset(),
                            $menuItemPosition = $(this).offset(),
                            $positionLeft = $menuItemPosition.left - $menuPosition.left;

                        $(this).find('.responsive-megamenu').css({ left: '-' + $positionLeft + 'px', width: $menuWidth });

                        let $tabWidth = $(this).find('.responsive-megamenu > li').width();

                        $(this).find('.responsive-megamenu .children, .responsive-megamenu .sub-menu').css({ width: $tabWidth });
                    }

                    // Custom Width
                    if ($(menuIDClass + '.responsive-megamenu-parent').hasClass(class_prefix + 'custom')) {
                        let $siteHeader = $('.responsive-site-header-wrap .container #header-menu'),
                            $menuWidth = $siteHeader.width();

                        let customWidth = $('#site-navigation ' + menuIDClass + '.responsive-megamenu-parent').data('custom-width');
                        customWidth = (typeof customWidth !== 'undefined') ? customWidth : 600;

                        let renderedWidth = Math.abs(customWidth - $menuWidth);

                        $(this).find('.responsive-megamenu').css({ left: '-' + renderedWidth + 'px', width: customWidth });

                        let $tabWidth = $(this).find('.responsive-megamenu > li').width();

                        $(this).find('.responsive-megamenu .children, .responsive-megamenu .sub-menu').css({ width: $tabWidth });
                    }
                },
                function() {
                    if ($(menuIDClass + '.responsive-megamenu-parent').hasClass(class_prefix + 'full')) {
                        $(this).find('.responsive-megamenu.sub-menu').css({ position: 'absolute' });
                    }
                    $(this).find('.responsive-megamenu').css({ left: '-99999em' });
                }
            );
        });
    }

})(jQuery);
