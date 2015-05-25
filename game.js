var map = "";
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

    setTimeout(function() {Crafty.scene("main");},1000);
}

function main() {
    Crafty.background("white");
    loadMap();
}

function loadMap() {
    loadMapData(map);
}

function loadMapData(map) {
    (aObj=new XMLHttpRequest()).open("GET","maps/"+map+"/"+map+".map",true);
    aObj.send();
    aObj.onreadystatechange=function() {
        if(aObj.readyState == 4) {
            SOURCE = eval("("+aObj.responseText+")");
            drawMap();
        }
    }
}

function drawMap() {
    Crafty.e("2D, Canvas, TiledMapBuilder").setMapDataSource(SOURCE).createWorld(function(map) {
        for(i=0;i<map.getEntitiesInLayer("Collision").length;i++) {
            map.getEntitiesInLayer("Collision")
                .addComponent("HitBox");
        }
    });
}