<html>
	<head>
		<title>Swisssh</title>
		<script src="https://aframe.io/releases/0.8.0/aframe.min.js"></script>
		<style>
		html,body{
			background:#000;
			color:#fff;
			font-family: monospace;
		}
		#info{
			position: fixed;
			top:0;
			left:0;
			z-index: 2;
			padding:1rem;
		}
		#speed::before{
			content:"Speed: ";
		}
		#score::before{
			content:"Score: ";
		}
		#gameover{
			position: fixed;
			top:0;
			left:0;
			width:100vw;
			height:100vh;
			z-index:3;
			background-color:rgba(0,0,0,0.5);
			display: none;
		}
		#gameover .inner{
			position: absolute;
			top:50%;
			left:50%;
			transform: translate(-50%,-50%);
			text-align: center;
		}
		button{
			border:2px solid #fff;
			color:#fff;
			background:rgba(0,0,0,0.5);
			font-family: monospace;
			font-size:1.2rem;
			padding:0.7rem 2.5rem;
			outline:none;
			cursor:pointer;
		}
		</style>
	</head>
	<body onkeydown="handleKeypress(event)">
		<a-scene id="scene">
			<a-camera id="camera" wasd-controls-enabled="false" look-controls-enabled="false"></a-camera>
			<a-entity id="tunnel-base">
				<a-plane position="5 0 -25" rotation="0 -90 90" width="10" height="50" color="#002"></a-plane>
				<a-plane position="0 -5 -25" rotation="-90 0 0" width="10" height="50" color="#020"></a-plane>
				<a-plane position="0 5 -25" rotation="90 0 0" width="10" height="50" color="#200"></a-plane>
				<a-plane position="-5 0 -25" rotation="0 90 90" width="10" height="50" color="#220"></a-plane>
			</a-entity>

			<a-sky color="#105"></a-sky>
		</a-scene>
		<div id="info">
			<h3 id="speed"></h3>
			<h3 id="score"></h3>
		</div>
		<div id="gameover">
			<div class="inner">
				<h1>! GAME OVER !</h1>
				<button onclick="restart()">RESTART</button>
			</div>
		</div>
		<script type="text/javascript">
		var tunnelPiece=document.getElementById("tunnel-base"), camera=document.getElementById("camera"),scene=document.getElementById("scene"), speedCont=document.getElementById("speed"), scoreCont=document.getElementById("score");
		var position=0, speed=.11, campos={x:0,y:0,z:0}, waitFor=150, resetWaitFor=300,score=0, scoreUpdate=100;
		var tunnels=[tunnelPiece], obstacles=[];
		var colors=["#1abc9c","#2ecc71","#3498db","#9b59b6","#34495e","#f1c40f","#e67e22","#e74c3c","#ecf0f1","#95a5a6"];
		function stop(){
			clearInterval(fwd);
		}
		var fwd=setInterval(update,5);
		function update(){
			camera.setAttribute("position",campos.x+" "+campos.y+" "+campos.z);
			campos.z-=speed;
			speedCont.innerHTML=speed.toFixed(3);
			if(--waitFor<=0){
				waitFor=resetWaitFor;
				resetWaitFor=Math.random()*150+100;
				addRandomObstacle();
			}
			if(--scoreUpdate<=0){
				scoreUpdate=100;
				score++;
				scoreCont.innerHTML=score;
			}
			//check collisions
			for(var k=0;k<obstacles.length;++k){
				var pos=obstacles[k].getAttribute("position");
				var scale=obstacles[k].getAttribute("scale");
				if(Math.abs(pos.z-campos.z)<=scale.z/2 && obstacles[k].checkCollision(pos)){
					gameover();
					console.log(k,pos,scale,campos);
				}
			}


			// check for next tunnel piece
			if(Math.abs(campos.z-position)<=100){
				addPiece();
				speed*=1.007;
			}
		}
		function addPiece(){
			position-=50;
			var newPiece=tunnelPiece.cloneNode(1);
			newPiece.setAttribute("position","0 0 "+position);
			scene.append(newPiece);
			tunnels.push(newPiece);
			if(tunnels.length>4){
				scene.removeChild(tunnels[0]);
				tunnels.splice(0,1); // rimuovo il primo elemento
			}
		}
		function addRandomObstacle(){
			var side = parseInt(Math.random()*12);
			var dist = -parseInt(50-campos.z);
			var pos = "";
			var obs=null;
			switch(side){
				case 0: pos="-2.5 -2.5 "+dist; break;
				case 1: pos="2.5 -2.5 "+dist; break;
				case 2: pos="-2.5 2.5 "+dist; break;
				case 3: pos="2.5 2.5 "+dist; break;
				case 4: // lower side
					obs=document.createElement("a-box");
					obs.setAttribute("position","0 -2.5 "+dist);
					obs.setAttribute("scale","10 5 5");
					obs.setAttribute("color",colors[parseInt(Math.random()*10)]);
					obs.checkCollision=function(pos){return campos.y<=0};
					break;
				case 5: // upper side
					obs=document.createElement("a-box");
					obs.setAttribute("position","0 2.5 "+dist);
					obs.setAttribute("scale","10 5 5");
					obs.setAttribute("color",colors[parseInt(Math.random()*10)]);
					obs.checkCollision=function(pos){return campos.y>=0};
					break;
				case 6: // left side
					obs=document.createElement("a-box");
					obs.setAttribute("position","-2.5 0 "+dist);
					obs.setAttribute("scale","5 10 5");
					obs.setAttribute("color",colors[parseInt(Math.random()*10)]);
					obs.checkCollision=function(pos){return campos.x<=0};
					break;
				case 7: // right side
					obs=document.createElement("a-box");
					obs.setAttribute("position","2.5 0 "+dist);
					obs.setAttribute("scale","5 10 5");
					obs.setAttribute("color",colors[parseInt(Math.random()*10)]);
					obs.checkCollision=function(pos){return campos.x>=0};
					break;

				case 8: // missing lower left
					obs=document.createElement("a-entity");
					big=document.createElement("a-box");
					col=colors[parseInt(Math.random()*10)];
					big.setAttribute("position","0 2.5 0");
					big.setAttribute("scale","10 5 5");
					big.setAttribute("color",col);
					small=document.createElement("a-box");
					small.setAttribute("position","2.5 -2.5 0");
					small.setAttribute("scale","5 5 5");
					small.setAttribute("color",col);
					obs.appendChild(small);
					obs.appendChild(big);
					obs.setAttribute("position","0 0 "+dist);
					obs.checkCollision=function(pos){return campos.y>=0 || campos.y<=0 && campos.x>=0};
					break;
				case 9: // missing lower right
					obs=document.createElement("a-entity");
					big=document.createElement("a-box");
					col=colors[parseInt(Math.random()*10)];
					big.setAttribute("position","0 2.5 0");
					big.setAttribute("scale","10 5 5");
					big.setAttribute("color",col);
					small=document.createElement("a-box");
					small.setAttribute("position","-2.5 -2.5 0");
					small.setAttribute("scale","5 5 5");
					small.setAttribute("color",col);
					obs.appendChild(small);
					obs.appendChild(big);
					obs.setAttribute("position","0 0 "+dist);
					obs.checkCollision=function(pos){return campos.y>=0 || campos.y<=0 && campos.x<=0};
					break;
				case 10: // missing upper left
					obs=document.createElement("a-entity");
					big=document.createElement("a-box");
					col=colors[parseInt(Math.random()*10)];
					big.setAttribute("position","0 -2.5 0");
					big.setAttribute("scale","10 5 5");
					big.setAttribute("color",col);
					small=document.createElement("a-box");
					small.setAttribute("position","2.5 2.5 0");
					small.setAttribute("scale","5 5 5");
					small.setAttribute("color",col);
					obs.appendChild(small);
					obs.appendChild(big);
					obs.setAttribute("position","0 0 "+dist);
					obs.checkCollision=function(pos){return campos.y<=0 || campos.y>=0 && campos.x>=0};
					break;
				case 11: // missing upper right
					obs=document.createElement("a-entity");
					big=document.createElement("a-box");
					col=colors[parseInt(Math.random()*10)];
					big.setAttribute("position","0 -2.5 0");
					big.setAttribute("scale","10 5 5");
					big.setAttribute("color",col);
					small=document.createElement("a-box");
					small.setAttribute("position","-2.5 2.5 0");
					small.setAttribute("scale","5 5 5");
					small.setAttribute("color",col);
					obs.appendChild(small);
					obs.appendChild(big);
					obs.setAttribute("position","0 0 "+dist);
					obs.checkCollision=function(pos){return campos.y<=0 || campos.y>=0 && campos.x<=0};
					break;
			}
			if(side<=3){
				obs=document.createElement("a-box");
				obs.setAttribute("position",pos);
				obs.setAttribute("scale","5 5 5");
				obs.setAttribute("color",colors[parseInt(Math.random()*10)]);
				obs.checkCollision=function(pos){return Math.abs(pos.x-campos.x)<=2.5 && Math.abs(pos.y-campos.y)<=2.5;};
			}
			scene.append(obs);
			obstacles.push(obs);

			if(obstacles.length>20){
				scene.removeChild(obstacles[0]);
				obstacles.splice(0,1);
			}
		}
		function gameover(){
			console.log("gameover");
			stop();
			document.getElementById("gameover").style.display="block";
		}
		function restart(){
			location.href=location.href;
		}
		var ts={x:0,y:0};
		document.ontouchstart=function(e){
			console.log(e);
			ts.x=e.touches[0].clientX;
			ts.y=e.touches[0].clientY;
		};
		document.ontouchmove=function(e){
			console.log(e);
			var t={x:e.touches[0].clientX, y:e.changedTouches[0].clientY};
			if(ts.x>t.x)campos.x=-2.5;
			if(ts.x<t.x)campos.x=2.5;
			if(ts.y>t.y)campos.y=2.5;
			if(ts.y<t.y)campos.y=-2.5;
		}
		function handleKeypress(e){
			var key=e.key;
		    switch(key){
		        case "ArrowRight":
					campos.x=2.5;
					break;
		        case "ArrowDown":
		            campos.y=-2.5;
		            break;
		        case "ArrowLeft":
					campos.x=-2.5;
					break;
		        case "ArrowUp":
					campos.y=2.5;
		            break;
		    }
		}
		</script>
	</body>
</html>
