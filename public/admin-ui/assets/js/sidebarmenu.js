/*
Template Name: Admin Template
Author: Wrappixel

File: js
*/
// ==============================================================
// Auto select left navbar
// ==============================================================
$(function () {
    "use strict";
    var url = window.location + "";
    var path = url.replace(
        window.location.protocol + "//" + window.location.host + "/",
        ""
    );
    var element = $("ul#sidebarnav a").filter(function () {
        return this.href === url || this.href === path; // || url.href.indexOf(this.href) === 0;
    });


    element.addClass("active");
    $("#sidebarnav a").on("click", function (e) {
        if (!$(this).hasClass("active")) {
            // hide any open menus and remove all other classes
            $("a", $(this).parents("ul:first")).removeClass("active");

            // open our new menu and add the open class
            $(this).addClass("active");
        } else if ($(this).hasClass("active")) {
            // $(this).removeClass("active");
        }
    });

    
    var element = $(".nav-item").filter(function () {
        return this.href === url || this.href === path; // || url.href.indexOf(this.href) === 0;
    });

    element.addClass("active");

    $(".nav-item").on("click", function (e) {
        // Cek apakah elemen ini sudah memiliki kelas active
        if (!$(this).hasClass("active")) {
            // Hilangkan kelas active dari elemen lain yang berada di dalam ul pertama
            $(".nav-item").removeClass("active");
    
            // Tambahkan kelas active ke elemen yang diklik
            $(this).addClass("active");
        } else if ($(this).hasClass("active")) {
            // Jika elemen sudah active, kamu bisa menghapus active atau lakukan hal lain
            // $(this).removeClass("active");
        }
    });
});