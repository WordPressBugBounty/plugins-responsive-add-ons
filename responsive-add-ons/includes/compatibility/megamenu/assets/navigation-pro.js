/**
 * File navigation-pro.js.
 *
 * Handles navigation for both desktop (mega menu) and mobile (off-canvas).
 * Desktop and mobile logic are completely separated.
 */

(function () {
	function initDesktopNavigation() {
		var desktopNav = document.getElementById('site-navigation');
		
		if (!desktopNav) {
			return;
		}
		
		// Safety check for responsive_breakpoint
		if (typeof responsive_breakpoint === 'undefined') {
			window.responsive_breakpoint = { mobileBreakpoint: 767 };
		}
		
		var button = desktopNav.querySelector('button');
		var menu = desktopNav.querySelector('ul');
		
		// Hide button if no menu
		if (!menu) {
			if (button) button.style.display = 'none';
			return;
		}
		
		// Set up menu
		menu.setAttribute('aria-expanded', 'false');
		if (menu.className.indexOf('nav-menu') === -1) {
			menu.className += ' nav-menu';
		}
		
		// Desktop toggle button handler
		if (button) {
			button.onclick = function () {
				if (desktopNav.className.indexOf('toggled') !== -1) {
					desktopNav.className = desktopNav.className.replace(' toggled', '');
					button.setAttribute('aria-expanded', 'false');
					menu.setAttribute('aria-expanded', 'false');
				} else {
					desktopNav.className += ' toggled';
					button.setAttribute('aria-expanded', 'true');
					menu.setAttribute('aria-expanded', 'true');
				}
				
				// Handle icon if exists
				var icon = button.querySelector('i');
				if (icon) {
					if (button.getAttribute('aria-expanded') === 'true') {
						icon.className = 'icon-times';
					} else {
						icon.className = 'icon-bars';
					}
				}
				
				// Handle sidebar overlay if applicable
				if (button.getAttribute('aria-expanded') === 'true') {
					if (document.body.classList.contains('mobile-menu-style-sidebar')) {
						var overlay = document.getElementById('sidebar-menu-overlay');
						if (overlay) overlay.style.display = 'block';
					}
				} else {
					if (document.body.classList.contains('mobile-menu-style-sidebar')) {
						var overlay = document.getElementById('sidebar-menu-overlay');
						if (overlay) overlay.style.display = 'none';
					}
				}
			};
		}
		
		// Desktop submenu toggle prevention (keeps mega menu horizontal)
		var mobile_breakpoint = responsive_breakpoint.mobileBreakpoint || 767;
		var parentLinks = desktopNav.querySelectorAll('.menu-item-has-children > .res-iconify, .page_item_has_children > .res-iconify');
		
		var responsiveToggleClass = function (el, className) {
			if (el.classList.contains(className)) {
				el.classList.remove(className);
			} else {
				el.classList.add(className);
			}
		};
		
		for (var i = 0; i < parentLinks.length; i++) {
			parentLinks[i].addEventListener('click', function (e) {
				// Check if we're on mobile viewport
				var isMobile = window.matchMedia('(max-width: ' + mobile_breakpoint + 'px)').matches;
				
				// DESKTOP: Don't toggle submenus (keeps horizontal mega menu layout)
				if (!isMobile) {
					return;
				}
				
				// MOBILE: Allow submenu toggle (only if viewport is mobile size)
				var parent_li = this.parentNode;
				
				if (parent_li.classList.contains('menu-item-has-children')) {
					responsiveToggleClass(parent_li, 'res-submenu-expanded');
					var subMenu = parent_li.querySelector('.sub-menu');
					if (subMenu) {
						if (parent_li.classList.contains('res-submenu-expanded')) {
							subMenu.style.display = 'block';
							parent_li.style.width = '100%';
						} else {
							subMenu.style.display = 'none';
							parent_li.style.width = 'auto';
						}
					}
				} else if (parent_li.classList.contains('page_item_has_children')) {
					responsiveToggleClass(parent_li, 'res-submenu-expanded');
					var children = parent_li.querySelector('.children');
					if (children) {
						if (parent_li.classList.contains('res-submenu-expanded')) {
							children.style.display = 'block';
							parent_li.style.width = '100%';
						} else {
							children.style.display = 'none';
							parent_li.style.width = 'auto';
						}
					}
				}
			});
		}
		
		// Site branding check
		var siteBranding = document.querySelector('.site-branding');
		if (!siteBranding) {
			document.body.classList.add('site-branding-off');
		}
	}
	
	function initOffCanvasPanel() {
		var offCanvasPanel = document.getElementById('responsive-off-canvas-panel');
		var mobileHeader = document.getElementById('masthead-mobile');
		
		if (!offCanvasPanel || !mobileHeader) {
			return;
		}
		
		// Find toggle button in mobile header
		var mobileToggleButton = mobileHeader.querySelector('.menu-toggle');
		
		if (!mobileToggleButton) {
			return;
		}
		
		// Mobile hamburger toggle handler
		mobileToggleButton.addEventListener('click', function (e) {
			e.preventDefault();
			e.stopPropagation();
			
			var offCanvasOverlay = document.querySelector('.responsive-off-canvas-overlay');
			var isExpanded = this.getAttribute('aria-expanded') === 'true';
			var isDropdown = offCanvasPanel.classList.contains('responsive-off-canvas-panel-dropdown');
			
			if (isExpanded) {
				// Close off-canvas panel
				offCanvasPanel.classList.remove('active');
				offCanvasPanel.setAttribute('aria-hidden', 'true');
				if (offCanvasOverlay && !isDropdown) {
					offCanvasOverlay.classList.remove('active');
					offCanvasOverlay.setAttribute('aria-hidden', 'true');
				}
				this.setAttribute('aria-expanded', 'false');
				document.body.classList.remove('off-canvas-open');
			} else {
				// Open off-canvas panel
				offCanvasPanel.classList.add('active');
				offCanvasPanel.setAttribute('aria-hidden', 'false');
				if (offCanvasOverlay && !isDropdown) {
					offCanvasOverlay.classList.add('active');
					offCanvasOverlay.setAttribute('aria-hidden', 'false');
				}
				this.setAttribute('aria-expanded', 'true');
				document.body.classList.add('off-canvas-open');
				
				// Reinitialize submenu toggles when panel opens
				setTimeout(function () {
					initOffCanvasSubmenuToggles();
				}, 100);
			}
		});
	}
	
	// Mobile submenu toggles
	var offCanvasSubmenuHandlers = [];
	
	function initOffCanvasSubmenuToggles() {
		var offCanvasPanel = document.getElementById('responsive-off-canvas-panel');
		
		if (!offCanvasPanel) {
			return;
		}
		
		// Remove existing event listeners
		for (var h = 0; h < offCanvasSubmenuHandlers.length; h++) {
			var handler = offCanvasSubmenuHandlers[h];
			if (handler.element && handler.fn) {
				handler.element.removeEventListener('click', handler.fn, false);
			}
		}
		offCanvasSubmenuHandlers = [];
		
		var dropdownTarget = offCanvasPanel.getAttribute('data-dropdown-target') || 'icon';
		var mobile_menu_breakpoint = typeof responsive_breakpoint !== 'undefined' ? responsive_breakpoint.mobileBreakpoint : 767;
		var breakpoint = window.matchMedia('(max-width: ' + mobile_menu_breakpoint + 'px)');
		
		var responsiveToggleClass = function (el, className) {
			if (el.classList.contains(className)) {
				el.classList.remove(className);
			} else {
				el.classList.add(className);
			}
		};
		
		// Toggle submenu function
		var toggleSubmenu = function (parentLi, e) {
			if (e) {
				e.preventDefault();
				e.stopPropagation();
			}
			
			responsiveToggleClass(parentLi, 'res-submenu-expanded');
			
			if (parentLi.classList.contains('menu-item-has-children')) {
				var subMenu = parentLi.querySelector('.sub-menu');
				if (subMenu) {
					if (parentLi.classList.contains('res-submenu-expanded')) {
						subMenu.style.display = 'block';
						if (breakpoint.matches) {
							parentLi.style.width = '100%';
						}
					} else {
						subMenu.style.display = 'none';
						if (breakpoint.matches) {
							parentLi.style.width = '100%';
						}
					}
				}
			} else if (parentLi.classList.contains('page_item_has_children')) {
				var children = parentLi.querySelector('.children');
				if (children) {
					if (parentLi.classList.contains('res-submenu-expanded')) {
						children.style.display = 'block';
						if (breakpoint.matches) {
							parentLi.style.width = '100%';
						}
					} else {
						children.style.display = 'none';
						if (breakpoint.matches) {
							parentLi.style.width = '100%';
						}
					}
				}
			}
		};
		
		// Get menu items with children within off-canvas panel
		var offCanvasMenu = offCanvasPanel.querySelector('#off-canvas-menu');
		if (!offCanvasMenu) {
			return;
		}
		
		var menuItemsWithChildren = offCanvasMenu.querySelectorAll('.menu-item-has-children, .page_item_has_children');
		
		for (var i = 0; i < menuItemsWithChildren.length; i++) {
			var menuItem = menuItemsWithChildren[i];
			var icon = menuItem.querySelector('.res-iconify');
			var link = menuItem.querySelector('a');
			
			if (dropdownTarget === 'link' && link) {
				// Link target: clicking the link toggles submenu
				var linkHandler = function (e) {
					var parentLi = this.closest('.menu-item-has-children, .page_item_has_children');
					if (parentLi) {
						toggleSubmenu(parentLi, e);
					}
				};
				link.addEventListener('click', linkHandler, false);
				offCanvasSubmenuHandlers.push({ element: link, fn: linkHandler });
			} else if (dropdownTarget === 'icon') {
				if (icon) {
					// Icon target: clicking the icon toggles submenu
					var iconHandler = function (e) {
						var parentLi = this.closest('.menu-item-has-children, .page_item_has_children');
						if (parentLi) {
							toggleSubmenu(parentLi, e);
						}
					};
					icon.addEventListener('click', iconHandler, false);
					offCanvasSubmenuHandlers.push({ element: icon, fn: iconHandler });
				} else if (link) {
					// Fallback: if icon not found, use link
					var linkHandler = function (e) {
						var parentLi = this.closest('.menu-item-has-children, .page_item_has_children');
						if (parentLi) {
							toggleSubmenu(parentLi, e);
						}
					};
					link.addEventListener('click', linkHandler, false);
					offCanvasSubmenuHandlers.push({ element: link, fn: linkHandler });
				}
			}
		}
	}
	
	// Off-canvas close handlers
	function initOffCanvasCloseHandlers() {
		var offCanvasPanel = document.getElementById('responsive-off-canvas-panel');
		if (!offCanvasPanel) {
			return;
		}
		
		var offCanvasOverlay = document.querySelector('.responsive-off-canvas-overlay');
		var offCanvasClose = document.querySelector('.responsive-off-canvas-panel-close');
		var mobileHeader = document.getElementById('masthead-mobile');
		var mobileToggleButton = mobileHeader ? mobileHeader.querySelector('.menu-toggle') : null;
		
		function closeOffCanvasPanel() {
			var isDropdown = offCanvasPanel.classList.contains('responsive-off-canvas-panel-dropdown');
			offCanvasPanel.classList.remove('active');
			offCanvasPanel.setAttribute('aria-hidden', 'true');
			if (offCanvasOverlay && !isDropdown) {
				offCanvasOverlay.classList.remove('active');
				offCanvasOverlay.setAttribute('aria-hidden', 'true');
			}
			if (mobileToggleButton) {
				mobileToggleButton.setAttribute('aria-expanded', 'false');
			}
			document.body.classList.remove('off-canvas-open');
		}
		
		// Close on overlay click
		if (offCanvasOverlay) {
			offCanvasOverlay.addEventListener('click', closeOffCanvasPanel);
		}
		
		// Close on close button click
		if (offCanvasClose) {
			offCanvasClose.addEventListener('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				closeOffCanvasPanel();
			});
		}
		
		// Close on Escape key
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && offCanvasPanel.classList.contains('active')) {
				closeOffCanvasPanel();
			}
		});
	}
	
	function initAll() {
		// Initialize desktop navigation (mega menu)
		initDesktopNavigation();
		// Initialize mobile off-canvas navigation
		initOffCanvasPanel();
		initOffCanvasSubmenuToggles();
		initOffCanvasCloseHandlers();
	}
	
	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			setTimeout(initAll, 50);
		});
	} else {
		setTimeout(initAll, 50);
	}
	
	// Reinitialize mobile submenu toggles when dropdown target changes in customizer
	document.addEventListener('responsive-dropdown-target-changed', function () {
		initOffCanvasSubmenuToggles();
	});
	
})();
