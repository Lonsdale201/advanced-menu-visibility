jQuery(document).ready(function($) {
    function toggleRoleSelect(visibilitySelect) {
      
      var roleSelect = visibilitySelect
        .closest(".menu-item-settings")
        .find(".widefat[name^='menu-item-role']");
  
     
  
      if (visibilitySelect.val() === "logged_out") {
        roleSelect.prop("disabled", true);
      } else {
        roleSelect.prop("disabled", false);
      }
    }
  
    // Apply the toggleRoleSelect function to each visibility select on page load
    $(".widefat[name^='menu-item-visibility']").each(function() {
      toggleRoleSelect($(this));
    });
  
    // Listen for changes on the visibility select elements
    $(document).on("change", ".widefat[name^='menu-item-visibility']", function() {
     
      toggleRoleSelect($(this));
    });
  
    console.log("Disable.js loaded");
  });