jQuery(document).ready(function($) {
    // Add the quick delete button to the menu item
    function addQuickDeleteButton(target) {
        var $target = $(target);

        // Check if a quick-delete-menu-item already exists for the target. If so, do not add another one.
        if ($target.find('.quick-delete-menu-item').length > 0) {
            return;
        }

       
        $('<span class="quick-delete-menu-item"><a href="#" title="' + my_custom_menu_delete_plugin.quick_delete_title + '">' + my_custom_menu_delete_plugin.quick_delete_text + '</a></span>')
            .insertBefore($target.find('.item-title .is-submenu'))
            .on('click', 'a', function(event) {
                event.preventDefault();
                // Trigger a click event on the original delete button
                $target.find('.item-delete').trigger('click');
            });
    }

    // Add the quick delete button to the existing menu items
    $('#menu-to-edit .menu-item').each(function() {
        addQuickDeleteButton(this);
    });

    // Monitor the menu for changes and add the quick delete button to the new menu items (duplicates or manually added ones)
    var menuObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                $(mutation.addedNodes).each(function() {
                    if ($(this).is('.menu-item')) {
                        addQuickDeleteButton(this);
                    }
                });
            }
        });
    });

    menuObserver.observe($('#menu-to-edit')[0], { childList: true });
});