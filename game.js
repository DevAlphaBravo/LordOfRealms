var map = "map-0";
var SOURCE;

window.onload = function() {
    Crafty.init(640,480);
    Crafty.canvas.init();

    //Loading Scene
    Crafty.scene("loading", function() {
        loading();
    });

    Crafty.scene("main", function() {
        main();
    });
    Crafty.scene("loading");
}

function loading() {
    var textSize = 20;

    Crafty.background("black");
    Crafty.e("2D, DOM, Text")
        .attr({x:0, y:232, w:640, h: 20})
        .css({"text-align": "center"})
        .textFont({size: "20px"})
        .textColor("white")
        .text("Loading...");

    var assets = [
        "assets/sprite.png"
        ];
    Crafty.load(assets,function() {
        Crafty.scene("main");
    });
}

function main() {
    alert("Loaded.");
}