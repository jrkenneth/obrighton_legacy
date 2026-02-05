"use strict";

(function(window) {
    function dzSettings(options) {
        this.options = options || {};
        this.apply();
    }

    dzSettings.prototype.apply = function() {
        var body = document.querySelector("body");
        if (!body) {
            return;
        }

        var map = {
            typography: "data-typography",
            version: "data-theme-version",
            layout: "data-layout",
            primary: "data-primary",
            headerBg: "data-headerbg",
            navheaderBg: "data-nav-headerbg",
            sidebarBg: "data-sidebarbg",
            sidebarStyle: "data-sidebar-style",
            sidebarPosition: "data-sidebar-position",
            headerPosition: "data-header-position",
            containerLayout: "data-container"
        };

        Object.keys(map).forEach(function(key) {
            if (Object.prototype.hasOwnProperty.call(this.options, key) && this.options[key] !== undefined && this.options[key] !== null && this.options[key] !== "") {
                body.setAttribute(map[key], this.options[key]);
            }
        }, this);
    };

    window.dzSettings = dzSettings;
})(window);
