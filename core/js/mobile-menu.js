/*!
 * Mobile Menu
 */
(function ($) {
    var obj = {

        onClick: function () {
            $("#main-navigation").toggleClass("menu-open");
        }

    };

    $("button#mobile-nav-button").on("click", obj.onClick);
})(jQuery);
