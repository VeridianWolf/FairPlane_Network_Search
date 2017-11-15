// ----------------------------------------
// Actual game code goes here.

// Global vars
fps = null; 
canvas = null;
ctx = null;

// ----------------------------------------

// Our 'game' variables



function GameTick(elapsed)
{
	alert('jj1');

	fps.update(elapsed);

	// --- Logic

	alert('jj2');



	// --- Rendering

	// Clear the screen
	ctx.fillStyle = "cyan";
	ctx.fillRect(0, 0, canvas.width, canvas.height);
	// Render objects
	ctx.strokeRect(posX, posY, sizeX, sizeY);
	ctx.fillStyle = "red";
	ctx.fillText("Hello World!", posX+10, posY+25);
}
	alert('jj');


canvas	=	document.getElementById("myCanvas");
ctx 	=	canvas.getContext("2d");
fps 	=	new FPSMeter("fpsmeter", document.getElementById("fpscontainer"));
GameLoopManager.run(GameTick);
